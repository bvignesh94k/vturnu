<?php
/**
 * Lead quality screen.
 *
 * Every submission that gets past the bot checks in security.php is scored
 * here. Patterns come from real junk this site received in 2026:
 *
 *   gibberish bots     NAERTREGE2248782NERTHRRTH, company "google", throwaway mail
 *   adult link spam    "intimate photos" + tinyurl, company "google"
 *   name-only bots     ThomasabanI, message "Hello!"
 *   brand impersonators  "Milton" from miltonin.com, "KitchenAid" from
 *                        kitchenaidwork.com, offering an "agency partnership"
 *
 * The last group is the dangerous one: fluent English, a real-sounding budget
 * and a lookalike domain that exists only to get a reply. No captcha stops it,
 * because a person fills in the form. Only the content gives it away.
 *
 * Nothing is thrown away. A junk verdict stores the lead in the admin panel's
 * Junk view and skips the sales email; one click restores it. The visitor sees
 * the normal thank-you page either way, so a bot learns nothing.
 */

declare(strict_types=1);

const LEAD_JUNK_SCORE   = 60;
const LEAD_REVIEW_SCORE = 30;

/** Sources that collect no message (or only an optional one, like the booking page), so the "say what you need" rules do not apply. */
const LEAD_NO_MESSAGE_SOURCES = ['newsletter', 'seo-audit', 'booking'];

const LEAD_DISPOSABLE_DOMAINS = [
    'lettersboxmail.com', 'belettersmail.com', 'mailinator.com', 'guerrillamail.com', 'guerrillamail.net',
    'sharklasers.com', 'grr.la', 'yopmail.com', 'yopmail.net', 'temp-mail.org', 'tempmail.com', 'tempmail.net',
    '10minutemail.com', '10minutemail.net', 'trashmail.com', 'trashmail.de', 'dispostable.com', 'maildrop.cc',
    'getnada.com', 'nada.email', 'mintemail.com', 'throwawaymail.com', 'fakeinbox.com', 'mailnesia.com',
    'emailondeck.com', 'moakt.com', 'tmpmail.org', 'tmpmail.net', 'mohmal.com', 'spamgourmet.com',
    'mail.ru', 'bk.ru', 'inbox.ru', 'list.ru', 'rambler.ru', 'yandex.ru',
];

/** Company field values that are never a real prospect's company name. */
const LEAD_FAKE_COMPANIES = [
    'google', 'facebook', 'instagram', 'youtube', 'linkedin', 'yahoo', 'bing', 'microsoft', 'amazon',
    'test', 'testing', 'none', 'na', 'n/a', 'nil', 'no', 'xyz', 'abc', 'asdf', 'company', 'business', 'home',
];

/** National number lengths by dial code, for the countries bots most often fake. */
const LEAD_PHONE_LENGTHS = [
    '1' => [10], '44' => [9, 10], '61' => [9], '65' => [8], '971' => [8, 9], '966' => [8, 9], '974' => [8],
    '60' => [9, 10], '62' => [9, 10, 11, 12], '234' => [10], '254' => [9], '27' => [9], '261' => [9],
    '7' => [10], '49' => [10, 11], '33' => [9], '39' => [9, 10], '34' => [9], '31' => [9], '64' => [8, 9, 10],
];

/**
 * @return array{verdict:string,score:int,reasons:string[]}
 *   verdict is 'ok', 'review' (delivered, flagged) or 'junk' (Junk view only).
 */
function lead_screen(array $entry, string $source, bool $network = true, array $signals = []): array
{
    $name    = trim((string) ($entry['name'] ?? ''));
    $email   = strtolower(trim((string) ($entry['email'] ?? '')));
    $company = trim((string) ($entry['company'] ?? ''));
    $role    = trim((string) ($entry['designation'] ?? ''));
    $mobile  = trim((string) ($entry['mobile'] ?? ''));
    /* The homepage qualifier prepends a machine summary; only what the person
       actually typed counts as their message. */
    $message = trim((string) ($entry['raw_message'] ?? ($entry['message'] ?? '')));

    [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');

    $score = 0;
    $reasons = [];
    $flag = static function (int $points, string $why) use (&$score, &$reasons): void {
        $score += $points;
        $reasons[] = $why;
    };

    /* ---- Bot-generated identity ---- */
    if (preg_match('/\d{3,}/', $name)) {
        $flag(70, 'Digits in the name');
    } elseif (preg_match('/^[A-Z]{9,}$/', $name)) {
        $flag(50, 'Name is one long capitalised string');
    } elseif (preg_match('/^[A-Z][a-z]{2,}[a-z]*[A-Z]{1,2}$/', $name) || preg_match('/^[A-Z][a-z]{2,}[A-Z][a-z]{2,}$/', $name)) {
        // ThomasabanI, RobertBuh: a template fragment glued onto a first name.
        $flag(35, 'Generated name pattern');
    } elseif (preg_match('/^[a-z]{8,}$/', $name)) {
        $flag(20, 'Single lowercase name');
    }
    if ($message !== '' && !str_contains($message, ' ') && preg_match('/^[A-Z0-9]{10,}$/', $message)) {
        $flag(70, 'Message is a random string');
    }
    if (preg_match('/\d{5,}/', $name, $m) && str_contains($message, $m[0])) {
        $flag(30, 'Same generated number in name and message');
    }
    if (preg_match('/^[a-z0-9]{6,}$/', $local) && preg_match('/\d/', $local) && !preg_match('/[aeiou]{1}[a-z]{1,3}[aeiou]/', $local)) {
        $flag(15, 'Random-looking email address');
    }

    /* ---- Email domain ---- */
    if ($domain !== '') {
        if (in_array($domain, LEAD_DISPOSABLE_DOMAINS, true) || preg_match('/letters?(box)?mail|tempmail|throwaway|discard/', $domain)) {
            $flag(65, 'Disposable email service');
        } elseif ($network && !lead_domain_has_mail($domain)) {
            $flag(60, 'Email domain cannot receive mail');
        }
    }

    /* ---- Company ---- */
    $co = strtolower(preg_replace('/\s*\(.*$/', '', $company));
    if ($co !== '' && in_array($co, LEAD_FAKE_COMPANIES, true) && !str_starts_with($domain, $co . '.')) {
        $flag(40, 'Placeholder company name');
    }
    if ($company !== '' && $role !== '' && strcasecmp($co, $role) === 0) {
        $flag(20, 'Role repeats the company name');
    }

    /* ---- Spam content ---- */
    $hay = strtolower($name . ' ' . $company . ' ' . $message);
    if (preg_match('~\b(tinyurl\.com|bit\.ly|t\.co|goo\.gl|rb\.gy|cutt\.ly|shorturl|is\.gd|ow\.ly|tiny\.cc)\b~', $hay)) {
        $flag(70, 'Link shortener in the message');
    }
    if (preg_match('/\b(intimate photos?|nude|porn|sex|escort|casino|betting|mostbet|1xbet|viagra|cialis|crypto ?(wallet|profit|invest)|forex signal|payday loan|essay writing)\b/', $hay)) {
        $flag(80, 'Adult, gambling or scam wording');
    }
    if (preg_match('/[\x{0400}-\x{04FF}\x{4E00}-\x{9FFF}]/u', $message . $name)) {
        $flag(40, 'Cyrillic or Chinese text');
    }
    if (preg_match_all('~https?://|www\.~i', $message) >= 2) {
        $flag(25, 'Several links in the message');
    }
    if (preg_match('/\b(guest post|backlinks?|link[- ]building (service|package)|rank (your|on) (website|site|google)|page one of google|domain authority|da \d{2}\+|we (offer|provide) (seo|web|app|digital marketing)|outsourc\w+ (your|to)|white[- ]label (partner|service)|dedicated developers? at|our pricing starts|seo proposal|lead list|email list|contact database)\b/', $hay)) {
        $flag(60, 'Vendor sales pitch, not a buyer');
    }

    if (preg_match('/\b(we can (help|improve|increase|boost) your (website|ranking|traffic|sales)|i (was|am) (on|browsing|looking at) your (website|site)|your website (is|has) (not|some|many|several)|our (agency|company|team) (offers?|provides?|specializes?)|outsourc\w+ (partner|your)|virtual assistants?|email (list|database) of|buy (leads|traffic|followers)|white[- ]label (seo|partner))\b/', $hay)) {
        $flag(60, 'Cold sales outreach, not a buyer');
    }

    /* ---- Brand impersonation ---- */
    $pitch = 0;
    foreach ([
        '/agency partnerships?/', '/partnership overview/', '/reply to the email address provided/',
        '/email address provided in this form/', '/no (detailed|formal) proposal/', '/shortlisting agencies/',
        '/potential (agency )?partnership/', '/(broader|long-term) (,\s*)?(long-term )?partnership/',
        '/initial total marketing investment/', '/on behalf of [a-z]+ to explore/',
    ] as $rx) {
        if (preg_match($rx, $hay)) {
            $pitch++;
        }
    }
    if ($pitch >= 2) {
        $flag(65, 'Matches the fake brand partnership scam');
    } elseif ($pitch === 1) {
        $flag(25, 'Uses partnership scam wording');
    }
    $coToken = preg_replace('/[^a-z0-9]/', '', $co);
    $dom = strtolower((string) strtok($domain, '.'));
    if (strlen($coToken) >= 4 && $dom !== '' && $dom !== $coToken && str_contains(str_replace('-', '', $dom), $coToken)
        && !in_array($domain, FREE_EMAIL_DOMAINS, true)) {
        $flag(30, 'Lookalike domain for the claimed company');
    }

    /* ---- Phone ---- */
    if ($mobile !== '' && !lead_phone_plausible($mobile)) {
        $flag(30, 'Phone number cannot be valid');
    }

    /* ---- Behaviour, recorded in the browser by main.js ---- */
    if (isset($signals['hx'])) {
        $parts = explode('.', (string) $signals['hx']);
        $ms = (int) ($parts[0] ?? 0);
        $keys = (int) ($parts[1] ?? 0);
        $points = (int) ($parts[2] ?? 0);
        $auto = (int) ($parts[3] ?? 0);
        if ($auto > 0) {
            $flag(60, 'Automated browser detected');
        } elseif ($signals['hx'] === '') {
            $flag(40, 'No browser interaction recorded');
        } elseif ($ms > 0 && $ms < 4000) {
            $flag(30, 'Form completed too fast');
        } elseif ($keys === 0 && $points === 0) {
            $flag(25, 'Form filled without typing or clicking');
        }
    }
    /* reCAPTCHA never refuses a form (owner, 9 Nov 2026); its score is weighed
       here instead, so a likely bot lands in the Junk view, one click from
       being restored if it was a person. */
    $captcha = $signals['captcha'] ?? null;
    if ($captcha === 'missing') {
        $flag(20, 'No reCAPTCHA token');
    } elseif (is_float($captcha) && $captcha < 0.3) {
        $flag(60, 'reCAPTCHA score ' . $captcha . ', likely a bot');
    } elseif (is_float($captcha) && $captcha < 0.5) {
        $flag(30, 'Low reCAPTCHA score (' . $captcha . ')');
    } elseif (is_float($captcha) && $captcha < 0.7) {
        $flag(20, 'Low reCAPTCHA score (' . $captcha . ')');
    }

    /* ---- Did they say what they need? ---- */
    if (!in_array($source, LEAD_NO_MESSAGE_SOURCES, true)) {
        $words = str_word_count(preg_replace('/[^\p{L}\s]/u', ' ', $message));
        if ($message === '' && $mobile === '' && $company === '') {
            $flag(40, 'Name and email only');
        } elseif ($message !== '' && $words < 3) {
            $flag(25, 'Message too short to act on');
        }
    }

    /* Only reached once the checks above say so: a young domain alone proves
       nothing, but it turns a suspicious lead into a confirmed one. Skipped
       for clean leads so a real buyer never waits on this lookup. */
    if ($network && $score >= 20 && $score < LEAD_JUNK_SCORE && !in_array($domain, FREE_EMAIL_DOMAINS, true)) {
        $age = lead_domain_age_days($domain);
        if ($age !== null && $age < 365) {
            $flag(35, 'Email domain registered ' . $age . ' days ago');
        }
    }

    $verdict = $score >= LEAD_JUNK_SCORE ? 'junk' : ($score >= LEAD_REVIEW_SCORE ? 'review' : 'ok');
    return ['verdict' => $verdict, 'score' => $score, 'reasons' => $reasons];
}

/** DNS check. Unknown (lookup failure) counts as yes: never punish a real lead for a resolver hiccup. */
function lead_domain_has_mail(string $domain): bool
{
    if (!function_exists('checkdnsrr') || in_array($domain, FREE_EMAIL_DOMAINS, true)) {
        return true;
    }
    return @checkdnsrr($domain . '.', 'MX') || @checkdnsrr($domain . '.', 'A') || @checkdnsrr($domain . '.', 'AAAA');
}

/** Age in days from public RDAP records, or null when it cannot be determined in time. */
function lead_domain_age_days(string $domain): ?int
{
    if ($domain === '' || !function_exists('curl_init')) {
        return null;
    }
    $ch = curl_init('https://rdap.org/domain/' . rawurlencode($domain));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 2,
        CURLOPT_TIMEOUT => 2,
        CURLOPT_CONNECTTIMEOUT => 1,
        CURLOPT_HTTPHEADER => ['Accept: application/rdap+json'],
    ]);
    $body = curl_exec($ch);
    if ($body === false || (int) curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 200) {
        return null;
    }
    $data = json_decode((string) $body, true);
    foreach (($data['events'] ?? []) as $event) {
        if (($event['eventAction'] ?? '') === 'registration' && !empty($event['eventDate'])) {
            $ts = strtotime((string) $event['eventDate']);
            return $ts ? (int) floor((time() - $ts) / 86400) : null;
        }
    }
    return null;
}

/** False only for numbers that cannot exist for the dial code they arrived with. */
function lead_phone_plausible(string $mobile): bool
{
    $digits = preg_replace('/\D/', '', $mobile);
    $len = strlen($digits);
    if ($len < 7 || $len > 15) {
        return false;
    }
    if (preg_match('/^(\d)\1{6,}$/', $digits)) {
        return false;
    }
    if (!str_starts_with(trim($mobile), '+')) {
        return true;
    }
    if (str_starts_with($digits, '91')) {
        return $len === 12;
    }
    foreach ([3, 2, 1] as $cc) {
        $code = substr($digits, 0, $cc);
        if (isset(LEAD_PHONE_LENGTHS[$code])) {
            return in_array($len - $cc, LEAD_PHONE_LENGTHS[$code], true);
        }
    }
    return true;
}

/**
 * Required fields per form, checked on the server so a bot cannot skip them.
 * Returns an error for the visitor, or '' when the lead is complete.
 */
function lead_missing(array $post, string $source): string
{
    if (in_array($source, LEAD_NO_MESSAGE_SOURCES, true)) {
        return '';
    }
    $msg = trim((string) ($post['raw_message'] ?? ($post['message'] ?? '')));
    $phone = preg_replace('/\D/', '', (string) ($post['mobile'] ?? ($post['phone'] ?? '')));
    if ($source === 'home-quote') {
        return '';   // The homepage qualifier has its own required steps.
    }
    if (trim((string) ($post['company'] ?? '')) === '') {
        return 'Please add your company name, so we can prepare for the conversation.';
    }
    if (strlen($phone) < 7) {
        return 'Please add a phone or WhatsApp number we can reach you on.';
    }
    if (mb_strlen($msg) < 15) {
        return 'Please tell us a little about what you need, one or two lines is enough.';
    }
    return '';
}
