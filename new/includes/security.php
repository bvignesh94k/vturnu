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

/** Hidden input to drop into any form. */
function csrf_field(): string
{
    $ts = time();
    $sig = hash_hmac('sha256', (string) $ts, SECURITY_SECRET);
    return '<input type="hidden" name="csrf_token" value="' . $ts . '.' . $sig . '">';
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
/* Rate limiting: generic per-IP token bucket, JSON file backed.       */
/* ------------------------------------------------------------------ */

function security_client_ip(): string
{
    return $_SERVER['HTTP_CF_CONNECTING_IP']
        ?? $_SERVER['HTTP_X_FORWARDED_FOR']
        ?? $_SERVER['REMOTE_ADDR']
        ?? '0.0.0.0';
}

/** True if this IP is still under $max attempts inside $windowSeconds for $bucket. */
function security_rate_ok(string $bucket, int $max, int $windowSeconds): bool
{
    $safeBucket = preg_replace('/[^a-z0-9_-]/', '', strtolower($bucket));
    $file = BASE_PATH . '/storage/rate-' . $safeBucket . '.json';
    $now = time();
    $data = is_file($file) ? (json_decode((string) file_get_contents($file), true) ?: []) : [];

    foreach ($data as $k => $stamps) {
        $data[$k] = array_values(array_filter($stamps, fn($t) => $now - $t < $windowSeconds));
        if (!$data[$k]) unset($data[$k]);
    }

    $key = hash('sha256', security_client_ip());
    $mine = $data[$key] ?? [];
    if (count($mine) >= $max) {
        @file_put_contents($file, json_encode($data), LOCK_EX);
        return false;
    }
    $mine[] = $now;
    $data[$key] = $mine;
    @file_put_contents($file, json_encode($data), LOCK_EX);
    return true;
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
 */
function security_recaptcha_ok(string $token): bool
{
    if (RECAPTCHA_SECRET_KEY === '' || str_starts_with(RECAPTCHA_SECRET_KEY, 'YOUR_')) {
        return true;
    }
    if ($token === '') {
        return false;
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
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    $data = json_decode((string) $res, true);
    // 0.5 is Google's own suggested default cutoff: below it, treat as a bot.
    return !empty($data['success']) && (float) ($data['score'] ?? 0) >= 0.5;
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
    $data = security_login_data();
    $key = hash('sha256', security_client_ip());
    $now = time();
    $rec = $data[$key] ?? ['count' => 0, 'last' => 0];
    // A lockout that has fully expired starts counting fresh.
    if ($rec['count'] >= ADMIN_LOGIN_MAX_FAILS && ($now - $rec['last']) >= ADMIN_LOGIN_LOCK_SECONDS) {
        $rec = ['count' => 0, 'last' => 0];
    }
    $rec['count']++;
    $rec['last'] = $now;
    $data[$key] = $rec;
    security_login_save($data);
}

function security_login_clear(): void
{
    $data = security_login_data();
    unset($data[hash('sha256', security_client_ip())]);
    security_login_save($data);
}

function security_login_record(): ?array
{
    $data = security_login_data();
    return $data[hash('sha256', security_client_ip())] ?? null;
}

function security_login_data(): array
{
    $file = BASE_PATH . '/storage/admin-lockout.json';
    return is_file($file) ? (json_decode((string) file_get_contents($file), true) ?: []) : [];
}

function security_login_save(array $data): void
{
    // Drop anything that's aged out so the file doesn't grow forever.
    $now = time();
    foreach ($data as $k => $rec) {
        if (($now - ($rec['last'] ?? 0)) > ADMIN_LOGIN_LOCK_SECONDS * 4) {
            unset($data[$k]);
        }
    }
    @file_put_contents(BASE_PATH . '/storage/admin-lockout.json', json_encode($data), LOCK_EX);
}
