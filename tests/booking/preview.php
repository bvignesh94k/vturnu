<?php
/* Local preview of Book a Meeting, run by tests/booking/preview.mjs: the site's
   real code with the in-memory database from tests/crm/boot.php, kept in a
   JSON file between requests, emails saved as files instead of sent, and the
   admin signed in. Google Calendar is the real one when the GOOGLE_* variables
   are passed in, or a stand-in with --fake-google. Never deployed (tests/ is in .vercelignore). */
declare(strict_types=1);

$uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
$cookies = $_COOKIE;
$get = $_GET;
$post = $_POST;
$method = (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET');
$ua = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
require '/t/boot.php';
restore_error_handler();
set_error_handler(static function (int $no, string $str, string $file, int $line): bool {
    if (!(error_reporting() & $no)) {
        return false;
    }
    throw new ErrorException($str, 0, $no, $file, $line);
});
$_SERVER['REQUEST_URI'] = $uri;
$_SERVER['REQUEST_METHOD'] = $method;
/* The desktop app's browser pane says "Claude/x.y" in its user agent, which the site's form guard refuses (as it should for AI agents). */
$_SERVER['HTTP_USER_AGENT'] = $ua !== '' ? (string) preg_replace('#\s?Claude/[\d.]+#', '', $ua) : 'Mozilla/5.0';
$_COOKIE = $cookies;
$_GET = $get;
$_POST = $post;
/* Pretend to be a visitor elsewhere: ?_ip_tz=America/Chicago (remembered in a cookie). */
$ipTz = (string) ($_GET['_ip_tz'] ?? ($_COOKIE['_ip_tz'] ?? ''));
if ($ipTz !== '') {
    setcookie('_ip_tz', $ipTz, ['path' => '/']);
    $_SERVER['HTTP_X_VERCEL_IP_TIMEZONE'] = $ipTz;
}
/* The IP country goes with it (?_ip_country=SG to set it alone), as Vercel's X-Vercel-IP-Country would. */
$ipCountry = strtoupper((string) ($_GET['_ip_country'] ?? ($_COOKIE['_ip_country'] ?? '')));
if (isset($_GET['_ip_country'])) {
    setcookie('_ip_country', $ipCountry, ['path' => '/']);
} elseif (isset($_GET['_ip_tz'])) {
    try {
        $loc = (new DateTimeZone($ipTz))->getLocation();
        $ipCountry = is_array($loc) ? (string) ($loc['country_code'] ?? '') : '';
    } catch (Throwable $e) {
        $ipCountry = '';
    }
    setcookie('_ip_country', $ipCountry, ['path' => '/']);
}
if (preg_match('/^[A-Z]{2}$/', $ipCountry)) {
    $_SERVER['HTTP_X_VERCEL_IP_COUNTRY'] = $ipCountry;
}

/* Pretend it is another moment: ?_now=2026-11-10T06:00:00Z (that page view only). */
if (isset($_GET['_now']) && strtotime((string) $_GET['_now'])) {
    $GLOBALS['BOOKING_NOW'] = strtotime((string) $_GET['_now']);
}

$db = $GLOBALS['FAKE_DB'];
$stateFile = '/state/db.json';
$state = is_file($stateFile) ? (json_decode((string) file_get_contents($stateFile), true) ?: []) : [];
if (!$state) {
    require '/t/fixtures.php';
    /* A stand-in for the Cocopat lead whose address is the owner's own inbox, so real invitations reach connect@ only. */
    $db->leads[] = fixture_lead(['id' => '90', 'name' => 'Bauer (test)', 'email' => 'connect+bookingtest@vturnu.com', 'mobile' => '+1 2103294540', 'company' => 'Cocopat (test)', 'service' => 'ERPNext',
        'budget' => '$3,000 to $10,000 / month', 'country' => 'US', 'status' => 'new', 'channel' => 'Google search', 'channel_group' => 'Organic search', 'grade' => 'B',
        'created_at' => date('Y-m-d H:i:sP', strtotime('-2 hours')), 'message' => "Lead grade: B (6/10)\nServices: ERPNext\nVisitor country: US\n\n"
        . 'We are interested in Google Ads and Meta Ads services to increase brand awareness, generate leads, and drive sales. Please share your services and pricing.']);
    $db->meetings = [];
} else {
    foreach (['leads', 'meetings', 'proposals', 'settings', 'bookingSettings'] as $k) {
        $db->$k = $state[$k] ?? [];
    }
}
/* --fake-google: the stand-in calendar of tests/crm/fake-google.php, with something
   already booked most weekdays so the page shows real gaps, and its events kept
   in the state file between requests. */
$fakeG = null;
if ((string) getenv('FAKE_GOOGLE') === '1') {
    require_once '/t/fake-google.php';
    $fakeG = new FakeGoogle();
    $fakeG->events = $state['google']['events'] ?? [];
    $fakeG->n = (int) ($state['google']['n'] ?? 0);
    $day = new DateTimeImmutable('today', new DateTimeZone('UTC'));
    for ($i = 0; $i < 28; $i++) {
        $d = $day->modify('+' . $i . ' days');
        $n = (int) $d->format('N');
        if ($n === 2) {
            $fakeG->busy[] = [$d->setTime(17, 0)->getTimestamp(), $d->setTime(18, 0)->getTimestamp()];
        } elseif ($n === 3) {
            $fakeG->busy[] = [$d->setTime(5, 0)->getTimestamp(), $d->setTime(6, 30)->getTimestamp()];
        } elseif ($n === 4) {
            $fakeG->busy[] = [$d->setTime(14, 0)->getTimestamp(), $d->setTime(15, 0)->getTimestamp()];
        }
    }
    $GLOBALS['GCAL_FAKE'] = $fakeG;
}
$GLOBALS['captured_mail'] = [];
register_shutdown_function(static function () use ($db, $stateFile, $fakeG): void {
    $state = [];
    foreach (['leads', 'meetings', 'proposals', 'settings', 'bookingSettings'] as $k) {
        $state[$k] = $db->$k;
    }
    if ($fakeG) {
        $state['google'] = ['events' => $fakeG->events, 'n' => $fakeG->n];
    }
    file_put_contents($stateFile, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    /* Every email the code would have sent, as a file to open in a browser. */
    foreach ($GLOBALS['captured_mail'] ?? [] as $i => $mail) {
        $name = date('His') . '-' . $i . '-' . trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower((string) ($mail['subject'] ?? 'mail'))), '-');
        if (!is_dir('/state/mail')) {
            mkdir('/state/mail');
        }
        file_put_contents('/state/mail/' . $name . '.json', json_encode($mail, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $html = (string) ($mail['html'] ?? ('<pre>' . htmlspecialchars((string) ($mail['text'] ?? '')) . '</pre>'));
        file_put_contents('/state/mail/' . $name . '.html', '<!-- To: ' . implode(', ', (array) ($mail['to'] ?? [])) . ' | From: ' . ($mail['from'] ?? '') . ' | Subject: ' . ($mail['subject'] ?? '') . ' -->' . $html);
    }
});

require '/site/includes/data/menu.php';
require '/site/includes/data/pages.php';
$slug = trim((string) parse_url($uri, PHP_URL_PATH), '/');
if (str_starts_with($slug, 'admin')) {
    $exp = time() + 3600;
    $payload = 'admin|' . $exp;
    $_COOKIE['vt_admin'] = rtrim(strtr(base64_encode($payload . '|' . hash_hmac('sha256', $payload, SECURITY_SECRET)), '+/', '-_'), '=');
    require '/site/includes/admin.php';
    return;
}
if (booking_route($slug)) {
    return;
}
http_response_code(404);
echo '<p>Preview serves /book-a-meeting/ and /admin/ only.</p>';
