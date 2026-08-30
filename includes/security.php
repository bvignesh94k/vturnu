<?php
/**
 * Form + admin protection: stateless CSRF, rate limiting, bot heuristics,
 * reCAPTCHA v3 verification, and geo-IP dial-code detection.
 *
 * No PHP sessions here on purpose: the public site stays fully stateless
 * (cache-friendly, no cookies for anonymous visitors). CSRF is a signed,
 * timestamped token instead of a session-bound one. Admin auth (which
 * does need a session) is handled separately in admin.php.
 */

declare(strict_types=1);

/* ------------------------------------------------------------------ */
/* CSRF: stateless, HMAC-signed, timestamp doubles as an anti-speed-bot
   and anti-replay signal.                                             */
/* ------------------------------------------------------------------ */

/** The bare "$ts.$sig" value, for callers that need the token itself rather
 *  than a full form field (admin.php's own CSRF field uses this). */
function csrf_token(): string
{
    $ts = time();
    $sig = hash_hmac('sha256', (string) $ts, SECURITY_SECRET);
    return $ts . '.' . $sig;
}

/** Hidden input to drop into any form. */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

/** True if the token is authentic and inside its validity window. */
function csrf_verify(?string $token): bool
{
    if (!$token || !str_contains($token, '.')) {
        return false;
    }
    [$ts, $sig] = explode('.', $token, 2);
    if (!ctype_digit($ts)) {
        return false;
    }
    $age = time() - (int) $ts;
    // 4 hours: generous for a slow visitor, tight enough to stop tokens
    // scraped once and replayed by a script long after the page changed.
    if ($age < 0 || $age > 14400) {
        return false;
    }
    $expected = hash_hmac('sha256', $ts, SECURITY_SECRET);
    return hash_equals($expected, $sig);
}

/** Seconds since the token was issued (used for the too-fast-to-be-human check). */
function csrf_age(string $token): int
{
    [$ts] = explode('.', $token, 2) + [0 => '0'];
    return ctype_digit($ts) ? time() - (int) $ts : 0;
}

/* ------------------------------------------------------------------ */
/* Rate limiting: generic per-IP sliding-window token bucket, Postgres  */
/* backed (rate_limits table, see db/schema.sql).                      */
/* ------------------------------------------------------------------ */

function security_client_ip(): string
{
    return $_SERVER['HTTP_CF_CONNECTING_IP']
        ?? $_SERVER['HTTP_X_FORWARDED_FOR']
        ?? $_SERVER['REMOTE_ADDR']
        ?? '0.0.0.0';
}

/**
 * True if this IP is still under $max attempts inside $windowSeconds for
 * $bucket. Same sliding-window semantics as the old file-backed version:
 * every hit is timestamped, and only hits inside the window still count.
 *
 * Fails OPEN on any database error, exactly like the old version's @-silenced
 * file write: a persistence hiccup must never be the thing that blocks a
 * real customer's enquiry.
 */
function security_rate_ok(string $bucket, int $max, int $windowSeconds): bool
{
    $safeBucket = preg_replace('/[^a-z0-9_-]/', '', strtolower($bucket));
    $key = hash('sha256', security_client_ip());
    $now = time();
    try {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT hits FROM rate_limits WHERE bucket = ? AND key_hash = ?');
        $stmt->execute([$safeBucket, $key]);
        $row = $stmt->fetch();
        $hits = $row ? (json_decode($row['hits'], true) ?: []) : [];
        $hits = array_values(array_filter($hits, fn($t) => $now - $t < $windowSeconds));

        if (count($hits) >= $max) {
            return false;
        }
        $hits[] = $now;
        $pdo->prepare(
            'INSERT INTO rate_limits (bucket, key_hash, hits, updated_at) VALUES (?, ?, ?::jsonb, now())
             ON CONFLICT (bucket, key_hash) DO UPDATE SET hits = excluded.hits, updated_at = now()'
        )->execute([$safeBucket, $key, json_encode($hits)]);
        return true;
    } catch (Throwable $e) {
        error_log('security_rate_ok(' . $safeBucket . '): ' . $e->getMessage());
        return true;
    }
}

/* ------------------------------------------------------------------ */
/* Bot heuristics                                                      */
/* ------------------------------------------------------------------ */

/**
 * True for known scripted clients (curl, requests libraries, headless
 * browsers, generic "bot/spider/crawler" strings) and missing UAs.
 * Only ever call this on POST form handlers, never on page GETs, or it
 * would also catch legitimate search engine crawlers.
 */
function security_ua_is_bot(): bool
{
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (trim($ua) === '') {
        return true;
    }
    $blocked = '/curl|wget|python-requests|python-urllib|scrapy|go-http-client|'
        . 'libwww-perl|httpclient|okhttp|axios\/|node-fetch|phantomjs|'
        . 'headlesschrome|selenium|puppeteer|playwright|bot|spider|crawl|scan/i';
    return (bool) preg_match($blocked, $ua);
}

/* ------------------------------------------------------------------ */
/* reCAPTCHA v3                                                        */
/* ------------------------------------------------------------------ */

/**
 * Verifies a reCAPTCHA v3 token server-side. Passes every submission
 * through untouched until real keys replace the placeholders, so the
 * site never silently blocks real customers on an unconfigured key.
 *
 * Fails OPEN on anything that is not an explicit, positive "this is a bot"
 * signal from Google: a missing token, a network/cURL failure talking to
 * Google, a malformed response, or an error code on Google's side (wrong
 * secret, expired token, etc.) all let the submission through. Only a real
 * response with success=true and a low score blocks it.
 *
 * This matters because reCAPTCHA is one of five independent layers here
 * (honeypot, CSRF+timing, User-Agent blocklist, rate limiting, this). If
 * this one layer fails closed on its own errors, a Google outage, a
 * network hiccup on this host, or a visitor's ad-blocker stripping the
 * script would silently block every lead on the site, a single point of
 * failure worse than the bot traffic it is meant to stop. The other four
 * layers still run regardless of what happens here.
 */
function security_recaptcha_ok(string $token): bool
{
    if (RECAPTCHA_SECRET_KEY === '' || str_starts_with(RECAPTCHA_SECRET_KEY, 'YOUR_')) {
        return true;
    }
    if ($token === '') {
        // No token reached us: script blocked, ad-blocker, slow network, or
        // the visitor submitted before it loaded. Not proof of a bot, and
        // the other four layers already ran before this function is called.
        security_recaptcha_log('empty token');
        return true;
    }

    $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'secret' => RECAPTCHA_SECRET_KEY,
            'response' => $token,
            'remoteip' => security_client_ip(),
        ]),
        CURLOPT_TIMEOUT => 5,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $res = curl_exec($ch);
    $curlErr = curl_error($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($res === false || $curlErr !== '') {
        security_recaptcha_log('curl error: ' . $curlErr);
        return true; // could not reach Google: do not penalise the visitor for that
    }
    if ($httpCode !== 200) {
        security_recaptcha_log('http ' . $httpCode . ' from siteverify');
        return true;
    }

    $data = json_decode((string) $res, true);
    if (!is_array($data)) {
        security_recaptcha_log('unparseable response: ' . substr((string) $res, 0, 200));
        return true;
    }
    if (empty($data['success'])) {
        // A real answer from Google, but not a pass: token expired/reused,
        // wrong secret, action mismatch, etc. Log it (this is the one case
        // worth investigating) but still let the visitor through, since
        // "verification broke" is a config problem, not a bot signal.
        security_recaptcha_log('success=false: ' . implode(',', $data['error-codes'] ?? ['unknown']));
        return true;
    }

    // Genuine signal at this point: Google validated the token and returned
    // a score. 0.5 is Google's own suggested default cutoff.
    $score = (float) ($data['score'] ?? 0);
    if ($score < 0.5) {
        security_recaptcha_log('low score: ' . $score);
        return false;
    }
    return true;
}

/** Diagnostic log so a recurring verification problem is visible without
 *  ever surfacing to the public site. Vercel's function logs are the
 *  equivalent of the old self-trimming local file: nothing to prune here. */
function security_recaptcha_log(string $msg): void
{
    error_log('[recaptcha] [' . security_client_ip() . '] ' . $msg);
}

/* ------------------------------------------------------------------ */
/* Combined submission gate for every lead-capture form                */
/* ------------------------------------------------------------------ */

/**
 * Runs honeypot, CSRF, speed, User-Agent, rate-limit and reCAPTCHA checks
 * in one call. Call this before save_enquiry() at every form entry point.
 *
 * @return array{ok:bool,error:string}
 */
function security_check_submission(array $post): array
{
    if (trim($post['website'] ?? '') !== '') {
        // Honeypot tripped: a real visitor never fills a hidden field.
        return ['ok' => false, 'error' => 'Something went wrong. Please try again.'];
    }

    $token = (string) ($post['csrf_token'] ?? '');
    if (!csrf_verify($token)) {
        return ['ok' => false, 'error' => 'Your session expired. Please refresh the page and try again.'];
    }

    if (csrf_age($token) < 2) {
        // Filled and submitted in under 2 seconds: no human does that.
        return ['ok' => false, 'error' => 'Please try again.'];
    }

    if (security_ua_is_bot()) {
        return ['ok' => false, 'error' => 'Something went wrong. Please try again.'];
    }

    if (!security_rate_ok('forms', 10, 3600)) {
        return ['ok' => false, 'error' => 'Too many submissions from your network. Please try again in an hour, or email ' . CONTACT_EMAIL . '.'];
    }

    if (!security_recaptcha_ok((string) ($post['recaptcha_token'] ?? ''))) {
        return ['ok' => false, 'error' => 'We could not verify you are human. Please refresh the page and try again.'];
    }

    return ['ok' => true, 'error' => ''];
}

/* ------------------------------------------------------------------ */
/* Geo-IP dial code (Cloudflare's CF-IPCountry header, no external API) */
/* ------------------------------------------------------------------ */

/** Two-letter country code from Cloudflare's edge, or 'IN' off-Cloudflare (local dev). */
function security_visitor_country(): string
{
    $cc = strtoupper($_SERVER['HTTP_CF_IPCOUNTRY'] ?? '');
    return ($cc !== '' && $cc !== 'XX' && $cc !== 'T1') ? $cc : 'IN';
}

/** Dial code for the visitor's detected country, defaulting to +91 (business HQ). */
function security_visitor_dial_code(): string
{
    return COUNTRY_DIAL_CODES[security_visitor_country()] ?? '+91';
}

/**
 * Renders the country-code <select> that sits in front of every phone input.
 *
 * A select rather than a text box: the visitor cannot mistype the code, and
 * the value that reaches the CRM is always a real dial code. The visitor's
 * own country is preselected from the edge geo-IP header, but they can still
 * pick another, which matters for anyone travelling or on a VPN.
 */
function country_code_select(string $name, string $id, string $selected = ''): string
{
    $selected = $selected !== '' ? $selected : security_visitor_dial_code();
    $cc = security_visitor_country();

    // Label each option with the country so "+1" is not ambiguous between
    // the US and Canada. Sorted by country name for scannability.
    $names = country_names();
    $rows = [];
    foreach (COUNTRY_DIAL_CODES as $iso => $dial) {
        $rows[] = [$names[$iso] ?? $iso, $iso, $dial];
    }
    usort($rows, fn($a, $b) => strcmp($a[0], $b[0]));

    $out = '<select class="cc-select" name="' . e($name) . '" id="' . e($id) . '" aria-label="Country dialling code">';
    foreach ($rows as [$label, $iso, $dial]) {
        // Match on the visitor's actual country first so shared codes (+1)
        // select the right row, then fall back to matching the dial code.
        $isSel = ($iso === $cc) || ($cc === '' && $dial === $selected);
        // Dial code first: a <select> shows the same text open and closed, so
        // leading with the code keeps it readable in the narrow closed state
        // while the country name still makes the open list scannable.
        $out .= '<option value="' . e($dial) . '"' . ($isSel ? ' selected' : '') . '>'
              . e($dial . '  ' . $label) . '</option>';
    }
    return $out . '</select>';
}

/** ISO alpha-2 to country name, for the dial-code select labels. */
function country_names(): array
{
    static $n = null;
    if ($n !== null) return $n;
    return $n = [
        'AE'=>'United Arab Emirates','AF'=>'Afghanistan','AL'=>'Albania','AM'=>'Armenia','AR'=>'Argentina',
        'AT'=>'Austria','AU'=>'Australia','AZ'=>'Azerbaijan','BA'=>'Bosnia and Herzegovina','BD'=>'Bangladesh',
        'BE'=>'Belgium','BF'=>'Burkina Faso','BG'=>'Bulgaria','BH'=>'Bahrain','BJ'=>'Benin','BN'=>'Brunei',
        'BO'=>'Bolivia','BR'=>'Brazil','BT'=>'Bhutan','BW'=>'Botswana','BY'=>'Belarus','BZ'=>'Belize',
        'CA'=>'Canada','CH'=>'Switzerland','CL'=>'Chile','CM'=>'Cameroon','CN'=>'China','CO'=>'Colombia',
        'CR'=>'Costa Rica','CU'=>'Cuba','CY'=>'Cyprus','CZ'=>'Czechia','DE'=>'Germany','DK'=>'Denmark',
        'DO'=>'Dominican Republic','DZ'=>'Algeria','EC'=>'Ecuador','EE'=>'Estonia','EG'=>'Egypt','ES'=>'Spain',
        'ET'=>'Ethiopia','FI'=>'Finland','FJ'=>'Fiji','FR'=>'France','GB'=>'United Kingdom','GE'=>'Georgia',
        'GH'=>'Ghana','GR'=>'Greece','GT'=>'Guatemala','HK'=>'Hong Kong','HR'=>'Croatia','HU'=>'Hungary',
        'ID'=>'Indonesia','IE'=>'Ireland','IL'=>'Israel','IN'=>'India','IQ'=>'Iraq','IR'=>'Iran','IS'=>'Iceland',
        'IT'=>'Italy','JM'=>'Jamaica','JO'=>'Jordan','JP'=>'Japan','KE'=>'Kenya','KG'=>'Kyrgyzstan',
        'KH'=>'Cambodia','KR'=>'South Korea','KW'=>'Kuwait','KZ'=>'Kazakhstan','LA'=>'Laos','LB'=>'Lebanon',
        'LI'=>'Liechtenstein','LK'=>'Sri Lanka','LT'=>'Lithuania','LU'=>'Luxembourg','LV'=>'Latvia',
        'LY'=>'Libya','MA'=>'Morocco','MC'=>'Monaco','MD'=>'Moldova','ME'=>'Montenegro','MG'=>'Madagascar',
        'MK'=>'North Macedonia','ML'=>'Mali','MM'=>'Myanmar','MN'=>'Mongolia','MO'=>'Macao','MT'=>'Malta',
        'MU'=>'Mauritius','MV'=>'Maldives','MX'=>'Mexico','MY'=>'Malaysia','NA'=>'Namibia','NE'=>'Niger',
        'NG'=>'Nigeria','NI'=>'Nicaragua','NL'=>'Netherlands','NO'=>'Norway','NP'=>'Nepal','NZ'=>'New Zealand',
        'OM'=>'Oman','PA'=>'Panama','PE'=>'Peru','PG'=>'Papua New Guinea','PH'=>'Philippines','PK'=>'Pakistan',
        'PL'=>'Poland','PT'=>'Portugal','PY'=>'Paraguay','QA'=>'Qatar','RO'=>'Romania','RS'=>'Serbia',
        'RU'=>'Russia','RW'=>'Rwanda','SA'=>'Saudi Arabia','SC'=>'Seychelles','SD'=>'Sudan','SE'=>'Sweden',
        'SG'=>'Singapore','SI'=>'Slovenia','SK'=>'Slovakia','SL'=>'Sierra Leone','SV'=>'El Salvador',
        'SY'=>'Syria','TH'=>'Thailand','TJ'=>'Tajikistan','TM'=>'Turkmenistan','TN'=>'Tunisia','TR'=>'Turkey',
        'TT'=>'Trinidad and Tobago','TW'=>'Taiwan','TZ'=>'Tanzania','UA'=>'Ukraine','UG'=>'Uganda',
        'US'=>'United States','UY'=>'Uruguay','UZ'=>'Uzbekistan','VE'=>'Venezuela','VN'=>'Vietnam',
        'YE'=>'Yemen','ZA'=>'South Africa','ZM'=>'Zambia','ZW'=>'Zimbabwe',
    ];
}

/* ------------------------------------------------------------------ */
/* Admin login lockout                                                 */
/* ------------------------------------------------------------------ */

const ADMIN_LOGIN_MAX_FAILS = 5;
const ADMIN_LOGIN_LOCK_SECONDS = 900; // 15 minutes

/** True if this IP may attempt a login right now. */
function security_login_ok(): bool
{
    $rec = security_login_record();
    if ($rec === null) {
        return true;
    }
    if ($rec['count'] < ADMIN_LOGIN_MAX_FAILS) {
        return true;
    }
    return (time() - $rec['last']) >= ADMIN_LOGIN_LOCK_SECONDS;
}

/** Seconds remaining on the current lockout, or 0 if not locked. */
function security_login_lock_remaining(): int
{
    $rec = security_login_record();
    if ($rec === null || $rec['count'] < ADMIN_LOGIN_MAX_FAILS) {
        return 0;
    }
    $left = ADMIN_LOGIN_LOCK_SECONDS - (time() - $rec['last']);
    return max(0, $left);
}

function security_login_fail(): void
{
    $key = hash('sha256', security_client_ip());
    $now = time();
    $rec = security_login_record() ?? ['count' => 0, 'last' => 0];
    // A lockout that has fully expired starts counting fresh.
    if ($rec['count'] >= ADMIN_LOGIN_MAX_FAILS && ($now - $rec['last']) >= ADMIN_LOGIN_LOCK_SECONDS) {
        $rec = ['count' => 0, 'last' => 0];
    }
    $rec['count']++;
    $rec['last'] = $now;
    security_login_save($key, $rec);
}

function security_login_clear(): void
{
    try {
        db()->prepare('DELETE FROM rate_limits WHERE bucket = ? AND key_hash = ?')
            ->execute(['admin-lockout', hash('sha256', security_client_ip())]);
    } catch (Throwable $e) {
        error_log('security_login_clear: ' . $e->getMessage());
    }
}

/** {count,last} for this IP's failed-login history, stored under the
 *  'admin-lockout' bucket in the same rate_limits table the form/audit
 *  throttles use, just with a different shape in `hits`. */
function security_login_record(): ?array
{
    try {
        $stmt = db()->prepare('SELECT hits FROM rate_limits WHERE bucket = ? AND key_hash = ?');
        $stmt->execute(['admin-lockout', hash('sha256', security_client_ip())]);
        $row = $stmt->fetch();
        return $row ? json_decode($row['hits'], true) : null;
    } catch (Throwable $e) {
        error_log('security_login_record: ' . $e->getMessage());
        // Fail open: a database hiccup must not lock every admin out.
        return null;
    }
}

function security_login_save(string $key, array $rec): void
{
    try {
        db()->prepare(
            'INSERT INTO rate_limits (bucket, key_hash, hits, updated_at) VALUES (?, ?, ?::jsonb, now())
             ON CONFLICT (bucket, key_hash) DO UPDATE SET hits = excluded.hits, updated_at = now()'
        )->execute(['admin-lockout', $key, json_encode($rec)]);
    } catch (Throwable $e) {
        error_log('security_login_save: ' . $e->getMessage());
    }
}
