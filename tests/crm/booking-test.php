<?php
/* Book a Meeting: the slot maths, time zones, the full book / move / sync /
   cancel flow against a stand-in for Google Calendar, the double-booking
   guards, the emails and the calendar file, and every page the lead sees. */
declare(strict_types=1);
ob_start();
require '/t/boot.php';
/* The failure cases below log on purpose; keep those lines out of the runner's error stream. */
ini_set('error_log', '/tmp/booking-test-errors.log');

$dash = static fn(string $s): bool => (bool) preg_match('/[\x{2013}\x{2014}]/u', $s);
$gmailSafe = static fn(string $html): bool => !preg_match('/background\s*:\s*(#|url|linear|rgb)/i', $html) && !str_contains($html, 'gradient');

require "/t/fake-google.php";

$db = $GLOBALS['FAKE_DB'];
$timeline = static function () use ($db): array {
    $out = [];
    foreach ($db->log as [$sql, $p]) {
        if (str_starts_with(trim($sql), 'UPDATE enquiries SET activity')) {
            $log = json_decode((string) $p[0], true) ?: [];
            $out[] = end($log)['text'] ?? '';
        }
    }
    return $out;
};
$has = static fn(array $lines, string $needle): bool => (bool) array_filter($lines, static fn(string $l): bool => str_contains($l, $needle));
$page = static function (string $slug, array $get = [], string $method = 'GET', array $post = []): string {
    $_GET = $get;
    $_POST = $post;
    $_SERVER['REQUEST_METHOD'] = $method;
    $_SERVER['REQUEST_URI'] = '/' . $slug . '/' . ($get ? '?' . http_build_query($get) : '');
    ob_start();
    booking_route($slug);
    return (string) ob_get_clean();
};

/* ---------------- hours and slots ---------------- */
check('ranges', booking_ranges('09:30-18:30') === [[570, 1110]] && booking_ranges('09:30-13:00, 14:00-18:30') === [[570, 780], [840, 1110]], json_encode(booking_ranges('09:30-13:00, 14:00-18:30')));
check('range past midnight', booking_ranges('19:00-01:00') === [[1140, 1500]]);
check('bad ranges dropped', booking_ranges('9-5, 25:00-26:00, nonsense') === []);
$cfg = booking_clean_settings(booking_setting_defaults());
check('owner defaults', $cfg['host_name'] === 'B. Vignesh' && $cfg['host_title'] === 'Founder & CEO' && $cfg['hours'] === '09:30-19:30' && $cfg['today_hours'] === '08:00-23:00'
    && $cfg['notice_minutes'] === 0 && $cfg['days'] === [1, 2, 3, 4, 5] && $cfg['length'] === 30 && !isset($cfg['core']) && !isset($cfg['overseas']) && !isset($cfg['same_day']), json_encode($cfg));
$bad = booking_clean_settings(['enabled' => '1', 'length' => 33, 'buffer' => 500, 'notice_minutes' => -3, 'window_days' => 0, 'days' => ['9', 'x'], 'hours' => 'all day', 'today_hours' => 'nights', 'core' => '01:00-02:00', 'host_name' => '']);
check('settings are checked', $bad['length'] === 30 && $bad['buffer'] === 60 && $bad['notice_minutes'] === 0 && $bad['window_days'] === 1 && $bad['days'] === [1, 2, 3, 4, 5]
    && $bad['hours'] === '09:30-19:30' && $bad['today_hours'] === '08:00-23:00' && !isset($bad['core']) && $bad['host_name'] === 'B. Vignesh', json_encode($bad));
check('an empty same-day box means no calls on the day itself', booking_clean_settings(['today_hours' => ''])['today_hours'] === '');
check('working hours stay inside one day', booking_hours('09:30-19:30') === [[570, 1170]] && booking_hours('19:00-01:00') === [] && booking_hours('09:30-13:00, 14:00-19:30') === [[570, 780], [840, 1170]]);

$ist = new DateTimeZone('Asia/Kolkata');
$at = static fn(string $s, string $tz = 'Asia/Kolkata'): int => (new DateTimeImmutable($s, new DateTimeZone($tz)))->getTimestamp();
/* Each slot as the visitor reads it: "Wed 13:30" on their own clock. */
$local = static fn(array $slots, string $tz, string $f = 'D H:i'): array => array_map(static fn(int $t): string => (new DateTimeImmutable('@' . $t))->setTimezone(new DateTimeZone($tz))->format($f), $slots);
/* Just today's times, as "H:i". */
$todays = static function (array $cfg, int $now, string $z) use ($local): array {
    $d = (new DateTimeImmutable('@' . $now))->setTimezone(new DateTimeZone($z))->format('Y-m-d');
    return array_values(array_map(static fn(string $x): string => substr($x, 11), array_filter($local(booking_slots($cfg, $now, [], $z), $z, 'Y-m-d H:i'), static fn(string $x): bool => str_starts_with($x, $d))));
};
$hhmm = static fn(int $a, int $b): array => array_map(static fn(int $m): string => sprintf('%02d:%02d', intdiv($m, 60), $m % 60), range($a, $b, 30));

/* The owner's rule (9 Nov 2026, night): on the day itself only calls wholly between 8:00 AM and 11:00 PM India time; from the next day, every time 9:30 AM to 7:30 PM their clock. */
$la = 'America/Los_Angeles';
$pt = $local(booking_slots($cfg, $at('2026-11-11 13:00', $la), [], $la), $la);
check('Pacific at 1:00 PM: today only 6:30 and 7:00 PM (8:00 and 8:30 AM in India), then Thursday from 9:30 AM', array_slice($pt, 0, 3) === ['Wed 18:30', 'Wed 19:00', 'Thu 09:30'], implode(' ', array_slice($pt, 0, 4)));
check('the next day is whole: 9:30 AM to the last call at 7:00 PM', array_slice($pt, 2, 20) === array_map(static fn(string $x): string => 'Thu ' . $x, $hhmm(570, 1140)) && $pt[22] === 'Fri 09:30', implode(' ', array_slice($pt, 20, 4)));
check('New York at 8:00 AM: today 9:30 AM to 12:00 PM (8:00 to 10:30 PM in India)', $todays($cfg, $at('2026-11-11 08:00', 'America/New_York'), 'America/New_York') === $hhmm(570, 720));
check('London at 9:00 AM: today 9:30 AM to 5:00 PM (to 11:00 PM in India)', $todays($cfg, $at('2026-11-11 09:00', 'Europe/London'), 'Europe/London') === $hhmm(570, 1020));
check('Singapore at 8:00 AM: today from 10:30 AM (8:00 AM in India) to 7:00 PM', $todays($cfg, $at('2026-11-11 08:00', 'Asia/Singapore'), 'Asia/Singapore') === $hhmm(630, 1140));
check('Sydney at 8:00 AM: today from 1:30 PM', ($todays($cfg, $at('2026-11-11 08:00', 'Australia/Sydney'), 'Australia/Sydney')[0] ?? '') === '13:30');
check('Hawaii at 8:00 AM: today 4:30 to 7:00 PM (8:00 to 10:30 AM in India)', $todays($cfg, $at('2026-11-11 08:00', 'Pacific/Honolulu'), 'Pacific/Honolulu') === $hhmm(990, 1140));
check('Dubai and India: the whole working day today', count($todays($cfg, $at('2026-11-11 08:00', 'Asia/Dubai'), 'Asia/Dubai')) === 20 && count($todays($cfg, $at('2026-11-09 06:00'), 'Asia/Kolkata')) === 20);
check('Pacific after midnight: today only the evening times inside India hours', $todays($cfg, $at('2026-11-11 00:05', $la), $la) === ['18:30', '19:00']);
check('Pacific on Friday at 6:05 PM: 6:30 and 7:00 PM, then Monday', array_slice($local(booking_slots($cfg, $at('2026-11-13 18:05', $la), [], $la), $la), 0, 3) === ['Fri 18:30', 'Fri 19:00', 'Mon 09:30']);
check('weekdays on the visitor clock: Friday afternoon Pacific yes, weekends no', in_array('Fri 17:30', $pt, true) && !array_filter($pt, static fn(string $t): bool => str_starts_with($t, 'Sat') || str_starts_with($t, 'Sun')));
/* Over two weeks of moments and many zones: every time offered for the visitor's today sits inside 8:00 AM to 11:00 PM India time, and the next working day is always whole. */
$badToday = 0;
$shortNext = 0;
for ($m = $at('2026-11-09 00:00', 'UTC'); $m < $at('2026-11-23 00:00', 'UTC'); $m += 97 * 60) {
    $g = booking_free_grid($cfg, $m, []);
    foreach (['Pacific/Kiritimati', 'Pacific/Pago_Pago', 'Pacific/Honolulu', 'America/Los_Angeles', 'America/New_York', 'America/St_Johns', 'Europe/London', 'Asia/Dubai', 'Asia/Kolkata', 'Asia/Kathmandu', 'Asia/Singapore', 'Australia/Sydney', 'Pacific/Chatham'] as $z) {
        $zt = new DateTimeZone($z);
        $today = (new DateTimeImmutable('@' . $m))->setTimezone($zt)->format('Y-m-d');
        $offered = booking_zone_slots($cfg, $m, $g, $z);
        foreach ($offered as $t) {
            if ((new DateTimeImmutable('@' . $t))->setTimezone($zt)->format('Y-m-d') === $today) {
                $h = (new DateTimeImmutable('@' . $t))->setTimezone($ist);
                $hm = (int) $h->format('G') * 60 + (int) $h->format('i');
                $badToday += ($hm < 480 || $hm + 30 > 1380) ? 1 : 0;
            }
        }
        $next = (new DateTimeImmutable($today, new DateTimeZone('UTC')))->modify('+1 day');
        if ((int) $next->format('N') <= 5) {
            $count = count(array_filter($local($offered, $z, 'Y-m-d'), static fn(string $d): bool => $d === $next->format('Y-m-d')));
            $shortNext += $count === 20 ? 0 : 1;
        }
    }
}
check('same-day times always inside India hours; the next working day always whole', $badToday === 0 && $shortNext === 0, 'outside: ' . $badToday . ', short next days: ' . $shortNext);
/* Settings: no same-day calls at all, or the whole day. */
check('same-day box empty: nothing today', $local(booking_slots(array_merge($cfg, ['today_hours' => '']), $at('2026-11-11 13:00', $la), [], $la), $la)[0] === 'Thu 09:30');
$allDay = array_merge($cfg, ['today_hours' => '00:00-24:00']);
check('same-day all day: from the next half hour', $local(booking_slots($allDay, $at('2026-11-11 13:00', $la), [], $la), $la)[0] === 'Wed 13:30'
    && $local(booking_slots($allDay, $at('2026-11-11 12:59', $la), [], $la), $la)[0] === 'Wed 13:00' && $local(booking_slots($allDay, $at('2026-11-11 13:10', $la), [], $la), $la)[0] === 'Wed 13:30');
check('a minimum notice pushes the first time', $local(booking_slots(array_merge($allDay, ['notice_minutes' => 60]), $at('2026-11-11 13:00', $la), [], $la), $la)[0] === 'Wed 14:00');

/* Every zone gets 9:30 AM to 7:00 PM starts on its own clock: 20 a day, on its half hours. */
$now = $at('2026-11-09 06:00');           // a Monday morning in Chennai
foreach (['Pacific/Honolulu', 'America/Los_Angeles', 'America/Denver', 'America/Phoenix', 'America/Chicago', 'America/New_York', 'America/St_Johns', 'Europe/London', 'Asia/Dubai', 'Asia/Kolkata', 'Asia/Kathmandu', 'Asia/Singapore', 'Australia/Sydney', 'Pacific/Chatham'] as $z) {
    $s = booking_slots($cfg, $now, [], $z);
    $hm = $local($s, $z, 'H:i');
    $perDay = array_count_values($local($s, $z, 'Y-m-d'));
    $full = array_filter($perDay, static fn(int $n): bool => $n === 20);
    check('zone ' . $z . ': 9:30 AM to 7:00 PM starts, weekdays, 20 a full day',
        min($hm) === '09:30' && max($hm) === '19:00' && !array_filter($hm, static fn(string $x): bool => !str_ends_with($x, ':00') && !str_ends_with($x, ':30'))
        && !array_filter($local($s, $z, 'N'), static fn(string $n): bool => (int) $n > 5) && count($full) >= 10 && max($perDay) === 20
        && !array_filter($s, static fn(int $t): bool => $t % 900 !== 0) && max($s) < $now + 21 * 86400 && min($s) > $now,
        $z . ' ' . min($hm) . '..' . max($hm) . ' days ' . count($full));
}
check('India on Monday morning: today from 9:30 AM', $local(booking_slots($cfg, $now, [], 'Asia/Kolkata'), 'Asia/Kolkata')[0] === 'Mon 09:30');

/* Daylight saving: 9:30 AM stays 9:30 AM on the visitor's clock across the change. */
$dst = booking_slots(array_merge($cfg, ['window_days' => 7]), $at('2026-10-29 08:00', $la), [], $la);
$fri = array_values(array_filter($dst, static fn(int $t): bool => (new DateTimeImmutable('@' . $t))->setTimezone(new DateTimeZone('America/Los_Angeles'))->format('Y-m-d H:i') === '2026-10-30 09:30'))[0] ?? 0;
$mon = array_values(array_filter($dst, static fn(int $t): bool => (new DateTimeImmutable('@' . $t))->setTimezone(new DateTimeZone('America/Los_Angeles'))->format('Y-m-d H:i') === '2026-11-02 09:30'))[0] ?? 0;
check('US fall back: 9:30 AM PDT then 9:30 AM PST', $fri > 0 && $mon - $fri === 3 * 86400 + 3600 && gmdate('H:i', $fri) === '16:30' && gmdate('H:i', $mon) === '17:30', gmdate('c', $fri) . ' ' . gmdate('c', $mon));
$uk = booking_slots(array_merge($cfg, ['window_days' => 7]), $at('2027-03-25 08:00', 'Europe/London'), [], 'Europe/London');
$ukAt = static fn(string $when): int => array_values(array_filter($uk, static fn(int $t): bool => (new DateTimeImmutable('@' . $t))->setTimezone(new DateTimeZone('Europe/London'))->format('D H:i') === $when))[0] ?? 0;
check('UK spring forward: 9:30 GMT then 9:30 BST', gmdate('D H:i', $ukAt('Fri 09:30')) === 'Fri 09:30' && gmdate('D H:i', $ukAt('Mon 09:30')) === 'Mon 08:30', gmdate('c', $ukAt('Fri 09:30')) . ' / ' . gmdate('c', $ukAt('Mon 09:30')));
$gap = booking_slots(array_merge($cfg, ['hours' => '01:00-04:00', 'days' => [7], 'window_days' => 3]), $at('2027-03-13 12:00', $la), [], $la);
check('a time the clock skips is left out', $local($gap, $la, 'H:i') === ['01:00', '01:30', '03:00', '03:30'], implode(' ', $local($gap, $la, 'H:i')));

$busy = [[$at('2026-11-10 12:00'), $at('2026-11-10 13:00')]];
$tue = array_values(array_filter($local(booking_slots($cfg, $now, $busy, 'Asia/Kolkata'), 'Asia/Kolkata', 'Y-m-d H:i'), static fn(string $t): bool => str_starts_with($t, '2026-11-10')));
$tue = array_map(static fn(string $t): string => substr($t, 11), $tue);
check('15 minute buffer around busy time', in_array('11:00', $tue, true) && !in_array('11:30', $tue, true) && !in_array('12:00', $tue, true) && !in_array('13:00', $tue, true) && in_array('13:30', $tue, true), implode(' ', $tue));
$grid = booking_free_grid($cfg, $now, $busy);
$bitAt = static fn(string $hm): string => $grid['bits'][intdiv($at('2026-11-10 ' . $hm) - $grid['t0'], 900)] ?? '?';
check('booked times are marked for striking, buffer-only times for hiding', $bitAt('11:00') === '1' && $bitAt('11:30') === '2' && $bitAt('12:00') === '0' && $bitAt('12:30') === '0'
    && $bitAt('13:00') === '2' && $bitAt('13:30') === '1', implode(' ', array_map($bitAt, ['11:00', '11:30', '12:00', '12:30', '13:00', '13:30'])));
check('one free grid serves every zone', $grid['t0'] > $now && $grid['t0'] % 900 === 0 && strlen($grid['bits']) === intdiv($grid['until'] - $grid['t0'] + 899, 900)
    && booking_zone_slots($cfg, $now, $grid, 'Europe/London') === booking_slots($cfg, $now, $busy, 'Europe/London'));
$cc = booking_zone_countries();
check('zone countries for the picker', ($cc['America/Los_Angeles'] ?? '') === 'US' && ($cc['Asia/Kolkata'] ?? '') === 'IN' && ($cc['UTC'] ?? 'x') === '' && count($cc) > 300);

/* ---------------- the phone: every country, its code from the IP, required ---------------- */
check('every country has a calling code, a name and a flag', count(COUNTRY_DIAL_CODES) === 245 && !array_diff_key(COUNTRY_DIAL_CODES, country_names())
    && COUNTRY_DIAL_CODES['SG'] === '+65' && COUNTRY_DIAL_CODES['AU'] === '+61' && COUNTRY_DIAL_CODES['XK'] === '+383'
    && !array_filter(array_keys(COUNTRY_DIAL_CODES), static fn(string $c): bool => !is_file('/site/assets/img/flags/' . strtolower($c) . '.svg')));
check('phone: Singapore', booking_phone('SG', '8123 4567') === ['+65 81234567', 'SG', '']);
check('phone: the trunk 0 goes (UK), Italy keeps it', booking_phone('GB', '07400 123456')[0] === '+44 7400123456' && booking_phone('IT', '06 1234 5678')[0] === '+39 0612345678');
check('phone: an international number picks its own country', booking_phone('IN', '+65 8123 4567') === ['+65 81234567', 'SG', ''] && booking_phone('US', '0044 7400 123456')[0] === '+44 7400123456');
check('phone: a US leading 1 and a Russian leading 8 go', booking_phone('US', '1 (415) 555-0132')[0] === '+1 4155550132' && booking_phone('RU', '8 912 345 67 89')[0] === '+7 9123456789');
check('phone: India takes 10 digits from 6 to 9', booking_phone('IN', '98765 43210')[0] === '+91 9876543210' && booking_phone('IN', '12345')[2] !== '' && booking_phone('IN', '99999 99999')[2] !== '');
check('phone: required, with a real code', booking_phone('SG', '')[2] !== '' && booking_phone('ZZ', '8123 4567')[2] !== '' && booking_phone('SG', '+999 1234')[2] !== '');
check('phone: a shared code keeps the right country', booking_dial_country('+1', 'CA') === 'CA' && booking_dial_country('+1', 'IN') === 'US' && booking_dial_country('+44', 'JE') === 'JE' && booking_dial_country('+7', 'KZ') === 'KZ');
check('phone: stored numbers split for the form', booking_phone_split('+1 2103294540', 'IN') === ['US', '2103294540'] && booking_phone_split('98765 43210', 'IN') === ['IN', '98765 43210'] && booking_phone_split('', 'SG') === ['SG', '']);
$field = booking_phone_field('SG', '', '');
check('phone field: every country, the visitor one selected with its flag and example, required', str_contains($field, '<option value="SG" data-dial="+65" selected>Singapore (+65)</option>')
    && substr_count($field, 'data-dial="') === 245 && str_contains($field, 'placeholder="8123 4567"') && str_contains($field, ' required ') && str_contains($field, 'src="/assets/img/flags/sg.svg"'));
check('zones', booking_zone_name('Asia/Calcutta') === 'Asia/Calcutta' && booking_zone_name('America/Argentina/Buenos_Aires') === 'America/Argentina/Buenos_Aires'
    && booking_zone_name('../etc/passwd') === '' && booking_zone_name('Mars/Olympus') === '' && booking_zone_name('UTC') === 'UTC');
check('zone labels', booking_zone_label('America/Chicago', $at('2026-11-10 20:00')) === 'Chicago time (GMT-6)' && booking_zone_label('Asia/Kolkata', $now) === 'India time (IST)'
    && booking_gmt('Asia/Kathmandu', $now) === 'GMT+5:45', booking_zone_label('America/Chicago', $at('2026-11-10 20:00')));
check('subtract own call', booking_subtract([[100, 400]], 200, 300) === [[100, 200], [300, 400]] && booking_subtract([[100, 200]], 300, 400) === [[100, 200]]);

/* ---------------- the Cocopat-style request ---------------- */
foreach (['GOOGLE_CLIENT_ID', 'GOOGLE_CLIENT_SECRET', 'GOOGLE_REFRESH_TOKEN'] as $k) {
    $_SERVER[$k] = 'test-' . strtolower($k);
}
$G = new FakeGoogle();
$GLOBALS['GCAL_FAKE'] = $G;
$db->leads = [fixture_lead(['id' => '70', 'name' => 'Bauer', 'email' => 'bauer@cocopat.us', 'mobile' => '+1 2103294540', 'company' => 'Cocopat', 'service' => 'ERPNext',
    'budget' => '$3,000 to $10,000 / month', 'country' => 'US', 'status' => 'new', 'channel' => 'Google search', 'message' => "Lead grade: B (6/10)\nServices: ERPNext\nVisitor country: US\n\n"
    . 'We are interested in Google Ads and Meta Ads services to increase brand awareness, generate leads, and drive sales. Please share your services and pricing.'])];
check('topic follows their words, not the drop-down', booking_topic(crm_lead(70)) === 'Google Ads and Meta Ads', booking_topic(crm_lead(70)));
$d = booking_request_draft(crm_lead(70));
check('request subject', $d['subject'] === 'Bauer, a quick call about Google Ads and Meta Ads?', $d['subject']);
check('request body', str_contains($d['body'], 'Thank you for reaching out about Google Ads and Meta Ads for Cocopat.') && str_contains($d['body'], 'anything else you would like help with')
    && str_contains($d['body'], '30-minute video call'), $d['body']);
$plain = booking_request_draft(fixture_lead(['id' => '71', 'name' => 'Sam Lee', 'company' => 'Northwind', 'service' => 'I need advice on where to start', 'message' => 'Hello, please call me.']));
check('a request without a clear topic stays natural', $plain['subject'] === 'Sam, a quick call?' && str_contains($plain['body'], 'Thank you for getting in touch. Before we suggest anything') && !str_contains($plain['body'], 'for Northwind'), $plain['subject'] . ' / ' . $plain['body']);
check('no prices, no ERPNext, no dashes, no superlatives',!preg_match('/pric|cost|\$|₹|rate|ERPNext|\b(best|leading|top|#1|world.class|unmatched)\b/i', $d['subject'] . $d['body']) && !$dash($d['subject'] . $d['body']), $d['body']);

$GLOBALS['captured_mail'] = [];
$r = booking_send_request(crm_lead(70), $d['subject'], $d['body'], ['copy' => true, 'by' => 'admin', 'stage' => true]);
check('request sent', $r['ok'] && $r['error'] === '' && preg_match('/^[a-f0-9]{32}$/', $r['token']) === 1, json_encode($r));
$token = $r['token'];
$mail = $GLOBALS['captured_mail'][0] ?? [];
check('request from the host at connect@', ($mail['from'] ?? '') === 'B. Vignesh from VTurnU <connect@vturnu.com>' && ($mail['to'] ?? []) === ['bauer@cocopat.us'] && ($mail['bcc'] ?? []) === ['connect@vturnu.com'], (string) ($mail['from'] ?? ''));
check('request has the personal link, button and pixel', str_contains($mail['html'] ?? '', 'https://vturnu.com/book-a-meeting/' . $token . '/') && str_contains($mail['html'] ?? '', '>Pick a time for our call</a>')
    && str_contains($mail['html'] ?? '', '/p/m/' . $token . '.gif') && str_contains($mail['text'] ?? '', 'Pick a time: https://vturnu.com/book-a-meeting/' . $token . '/'));
check('request is Gmail-safe', $gmailSafe($mail['html'] ?? '') && str_contains($mail['html'] ?? '', 'bgcolor="#4338CA"') && str_contains($mail['html'] ?? '', 'bgcolor="#4F46E5"'));
check('no GSTIN for a US lead', !str_contains(($mail['html'] ?? '') . ($mail['text'] ?? ''), 'GSTIN'));
$m = booking_by_token($token);
check('request recorded', $m && $m['status'] === 'requested' && $m['enquiry_id'] === 70 && $m['email'] === 'bauer@cocopat.us' && $m['name'] === 'Bauer' && $m['company'] === 'Cocopat');
$tl = $timeline();
check('timeline: request and stage', $has($tl, 'Status: New to Contacted') && $has($tl, 'Meeting request sent: "Bauer, a quick call about Google Ads and Meta Ads?"'), json_encode($tl));
booking_track_open($token);
check('email open lands on the timeline once', $has($timeline(), 'Opened the meeting request email') && !empty(booking_by_token($token)['opened_at']));

/* ---------------- the personal page ---------------- */
$_SERVER['HTTP_X_VERCEL_IP_TIMEZONE'] = 'America/Chicago';
$html = $page('book-a-meeting/' . $token);
check('personal page greets and prefills', str_contains($html, 'Bauer, pick a date and time') && str_contains($html, 'value="Bauer"') && str_contains($html, 'value="bauer@cocopat.us"'), substr($html, 0, 300));
check('personal page agenda from their words', str_contains($html, 'Your goals for Google Ads and Meta Ads'));
check('personal page: their number, in their country', str_contains($html, '<option value="US" data-dial="+1" selected>United States (+1)</option>') && str_contains($html, 'value="2103294540"'));
check('page carries the free grid and the IP zone', str_contains($html, 'id="bk-data"') && str_contains($html, '"tz":"America/Chicago","tzSource":"ip"') && str_contains($html, '"mode":"book"')
    && str_contains($html, '"recaptcha":""') && str_contains($html, '"todayHours":[[480,1380]]') && str_contains($html, '"ipCountry":"IN"') && str_contains($html, '"grid":{"t0":') && str_contains($html, '"hours":[[570,1170]]') && str_contains($html, '"America/Los_Angeles":"US"'));
check('no "times shown in your time zone" wording', !str_contains($html, 'Times shown in your time zone'));
check('no-JS list in the visitor zone', str_contains($html, '<select id="bk-slot" name="slot"') && str_contains($html, 'Time (Chicago time (GMT-'));
$tzPage = $page('book-a-meeting/' . $token, ['tz' => 'Europe/London']);
check('a zone in the link wins over the IP', str_contains($tzPage, '"tz":"Europe/London","tzSource":"url"') && str_contains($tzPage, 'Time (London time (GMT'));
check('page is private', str_contains($html, '<meta name="robots" content="noindex, nofollow">'));
check('no dashes on the page', !$dash($html));
booking_track_view($token);
check('page view on the timeline', $has($timeline(), 'Opened the booking page'));

/* ---------------- booking ---------------- */
$cfgNow = booking_settings();
$avail = booking_availability($cfgNow);
/* The nth time offered to a visitor in $tz (after $after), as [unix]. */
$pick = static function (array $avail, string $tz, int $skip = 0, ?int $after = null) use ($cfgNow): ?array {
    foreach (booking_zone_slots($cfgNow, $avail['now'], $avail['grid'], $tz) as $t) {
        if (($after === null || $t > $after) && $skip-- <= 0) {
            return [$t];
        }
    }
    return null;
};
$slot = $pick($avail, 'America/Chicago');
$chiAt = $slot ? (new DateTimeImmutable('@' . $slot[0]))->setTimezone(new DateTimeZone('America/Chicago')) : null;
check('a Chicago working-hours slot is offered', $chiAt !== null && $chiAt->format('H:i') >= '09:30' && $chiAt->format('H:i') <= '18:00' && (int) $chiAt->format('N') <= 5, $chiAt ? $chiAt->format('c') : 'none');
$GLOBALS['captured_mail'] = [];
$post = ['action' => 'book', 'slot' => (string) $slot[0], 'tz' => 'America/Chicago', 'name' => 'Bauer', 'email' => 'bauer@cocopat.us', 'company' => 'Cocopat', 'phone' => '+1 2103294540',
    'notes' => 'We also run LinkedIn ads, happy to cover those too.'];
$b = booking_book(booking_by_token($token), $post);
check('booked', $b['ok'] && $b['token'] === $token, json_encode($b));
$ins = $G->last('POST', '/calendar/v3/calendars/primary/events');
parse_str((string) parse_url($ins[1] ?? '', PHP_URL_QUERY), $q);
$ev = $ins[2] ?? [];
check('event: Meet link requested, guests told', ($q['conferenceDataVersion'] ?? '') === '1' && ($q['sendUpdates'] ?? '') === 'all' && ($ev['conferenceData']['createRequest']['conferenceSolutionKey']['type'] ?? '') === 'hangoutsMeet');
check('event: lead invited as a guest', ($ev['attendees'][0]['email'] ?? '') === 'bauer@cocopat.us' && ($ev['attendees'][0]['displayName'] ?? '') === 'Bauer');
$chi = (new DateTimeImmutable('@' . $slot[0]))->setTimezone(new DateTimeZone('America/Chicago'));
check('event: in the lead zone, at the right moment', ($ev['start']['timeZone'] ?? '') === 'America/Chicago' && ($ev['start']['dateTime'] ?? '') === $chi->format('Y-m-d\TH:i:sP')
    && strtotime((string) $ev['end']['dateTime']) - strtotime((string) $ev['start']['dateTime']) === 1800, json_encode($ev['start'] ?? null));
check('event: title and notes', ($ev['summary'] ?? '') === 'Cocopat x VTurnU: 30-minute call' && str_contains($ev['description'] ?? '', 'https://vturnu.com/book-a-meeting/' . $token . '/')
    && !str_contains($ev['description'] ?? '', '/admin') && str_contains($ev['description'] ?? '', 'B. Vignesh, Founder & CEO') && !$dash($ev['description'] ?? ''), (string) ($ev['description'] ?? ''));
$m = booking_by_token($token);
check('meeting row booked', $m['status'] === 'booked' && $m['start'] === $slot[0] && $m['end'] === $slot[0] + 1800 && $m['event_id'] === 'ev1' && $m['meet_url'] === 'https://meet.google.com/abc-defg-001'
    && $m['ical_uid'] === 'ev1@google.com' && $m['time_zone'] === 'America/Chicago' && $m['notes'] !== '', json_encode($m));
$byTo = static function (string $to, string $prefix = ''): array {
    return array_values(array_filter($GLOBALS['captured_mail'], static fn(array $x): bool => ($x['to'] ?? []) === [$to] && empty($x['scheduled_at']) && ($prefix === '' || str_starts_with((string) $x['subject'], $prefix))));
};
$conf = $byTo('bauer@cocopat.us', 'Booked:')[0] ?? [];
check('lead confirmation', str_starts_with((string) ($conf['subject'] ?? ''), 'Booked: your call with B. Vignesh of VTurnU, ') && ($conf['from'] ?? '') === 'B. Vignesh from VTurnU <connect@vturnu.com>', (string) ($conf['subject'] ?? ''));
check('confirmation has the Meet link, both times and manage link', str_contains($conf['html'] ?? '', 'https://meet.google.com/abc-defg-001') && str_contains($conf['html'] ?? '', '>Join with Google Meet</a>')
    && str_contains($conf['html'] ?? '', 'Chicago time (GMT-') && str_contains($conf['html'] ?? '', 'in Chennai, India') && str_contains($conf['html'] ?? '', 'https://vturnu.com/book-a-meeting/' . $token . '/'));
check('confirmation is Gmail-safe and dash free', $gmailSafe($conf['html'] ?? '') && !$dash(($conf['html'] ?? '') . ($conf['text'] ?? '') . ($conf['subject'] ?? '')));
$att = $conf['attachments'][0] ?? [];
$ics = base64_decode((string) ($att['content'] ?? ''));
check('confirmation carries invite.ics', ($att['filename'] ?? '') === 'invite.ics' && str_starts_with((string) ($att['content_type'] ?? ''), 'text/calendar') && str_contains($ics, "UID:ev1@google.com\r\n")
    && str_contains($ics, 'DTSTART:' . gmdate('Ymd\THis\Z', $slot[0])) && str_contains($ics, 'LOCATION:https://meet.google.com/abc-defg-001'));
check('ics lines folded and CRLF', !array_filter(explode("\r\n", $ics), static fn(string $l): bool => strlen($l) > 75) && !preg_match("/[^\r]\n/", $ics));
$owner = array_values(array_filter($GLOBALS['captured_mail'], static fn(array $x): bool => ($x['to'] ?? []) === ['connect@vturnu.com'] && str_starts_with((string) $x['subject'], 'Meeting booked: ')))[0] ?? [];
check('owner alert', str_starts_with((string) ($owner['subject'] ?? ''), 'Meeting booked: Bauer, Cocopat · ') && str_contains($owner['text'] ?? '', 'Meet:      https://meet.google.com/abc-defg-001')
    && str_contains($owner['text'] ?? '', '/admin/?view=lead&id=70') && str_contains($owner['text'] ?? '', 'Their enquiry: We are interested in Google Ads') && ($owner['reply_to'] ?? '') === 'Bauer <bauer@cocopat.us>'
    && ($owner['attachments'][0]['filename'] ?? '') === 'invite.ics', (string) ($owner['text'] ?? ''));
$rem = array_values(array_filter($GLOBALS['captured_mail'], static fn(array $x): bool => !empty($x['scheduled_at'])));
$expect = ($slot[0] - time() > 26 * 3600 ? 1 : 0) + ($slot[0] - time() > 2 * 3600 ? 1 : 0);
check('reminders scheduled', count($rem) === $expect && count(booking_by_token($token)['reminder_ids']) === $expect, count($rem) . ' vs ' . $expect);
check('reminder an hour before', $expect === 0 || strtotime((string) end($rem)['scheduled_at']) === $slot[0] - 3600);
$tl = $timeline();
check('timeline: booked with both times and the Meet link', $has($tl, 'Booked a meeting: ' . booking_ist($slot[0]) . ' (') && $has($tl, 'Meet: https://meet.google.com/abc-defg-001'), json_encode($tl));
$fup = array_values(array_filter($db->log, static fn(array $x): bool => str_starts_with(trim($x[0]), 'UPDATE enquiries SET followup')));
check('follow-up date set to the call, tag added', $fup && $fup[0][1][0] === booking_fmt($slot[0], 'Asia/Kolkata', 'date') && str_contains((string) $fup[0][1][1], 'meeting booked'), json_encode($fup));

/* ---------------- nobody is double booked ---------------- */
$GLOBALS['captured_mail'] = [];
$again = booking_book(null, ['slot' => (string) $slot[0], 'tz' => 'America/Chicago', 'name' => 'Dana Smith', 'email' => 'dana.smith@gmail.com', 'phone_cc' => 'GB', 'phone' => '07400 123456']);
check('same time refused to the next person', !$again['ok'] && $again['taken'], json_encode($again));
/* Hours are the visitor's own: a Chicago morning is late evening in India, so it is refused to someone booking in India time. */
$morning = null;
foreach (booking_zone_slots($cfgNow, $avail['now'], $avail['grid'], 'America/Chicago') as $t) {
    if ($t !== $slot[0] && (new DateTimeImmutable('@' . $t))->setTimezone(new DateTimeZone('America/Chicago'))->format('H:i') < '12:00') {
        $morning = $t;
        break;
    }
}
$asIndia = booking_book(null, ['slot' => (string) $morning, 'tz' => 'Asia/Kolkata', 'name' => 'Dana Smith', 'email' => 'dana.smith@gmail.com', 'phone_cc' => 'GB', 'phone' => '07400 123456']);
check('a time outside the visitor zone hours is refused', $morning !== null && !$asIndia['ok'] && $asIndia['taken'], (string) $morning);
$soon = booking_book(null, ['slot' => (string) (intdiv(time(), 1800) * 1800), 'tz' => 'America/Chicago', 'name' => 'Dana Smith', 'email' => 'dana.smith@gmail.com', 'phone_cc' => 'GB', 'phone' => '07400 123456']);
check('a time already started is refused, and says so', !$soon['ok'] && $soon['taken'] && str_contains($soon['error'], 'already started'), $soon['error']);
/* A crafted request for today outside the India-time window is refused like any taken time; one inside it is on offer (fixed moment: Pacific, Wednesday 1:00 PM). */
$GLOBALS['BOOKING_NOW'] = $at('2026-11-11 13:00', $la);
$sameDay = booking_book(null, ['slot' => (string) $at('2026-11-11 13:30', $la), 'tz' => $la, 'name' => 'Dana Smith', 'email' => 'dana.smith@gmail.com', 'phone_cc' => 'GB', 'phone' => '07400 123456']);
$aNow = booking_availability($cfgNow);
$offeredToday = booking_zone_slots($cfgNow, $aNow['now'], $aNow['grid'], $la);
unset($GLOBALS['BOOKING_NOW']);
check('today outside India hours is refused; inside them is on offer', !$sameDay['ok'] && $sameDay['taken'] && !preg_match('/same.day/i', $sameDay['error'])
    && in_array($at('2026-11-11 18:30', $la), $offeredToday, true) && !in_array($at('2026-11-11 13:30', $la), $offeredToday, true), $sameDay['error']);
check('nothing sent for refused bookings', count($GLOBALS['captured_mail']) === 0);
/* Two people press Book at the same moment: both pages offered the time; the database's no-overlap rule stops the second, and its calendar event is taken back. */
$db->hideBusy = true;
$G->hideOwn = true;
$eventsBefore = count($G->events);
$race = booking_book(null, ['slot' => (string) $slot[0], 'tz' => 'America/Chicago', 'name' => 'Dana Smith', 'email' => 'dana.smith@gmail.com', 'phone_cc' => 'GB', 'phone' => '07400 123456']);
$db->hideBusy = false;
$G->hideOwn = false;
check('race lost to the no-overlap rule', !$race['ok'] && $race['taken'], json_encode($race));
check('race: the stray event is removed', count($G->events) === $eventsBefore && $G->last('DELETE') !== null);
check('race: the first booking is untouched', booking_by_token($token)['status'] === 'booked' && booking_by_token($token)['start'] === $slot[0]);
$busyNow = $slot[0] + 7200;
$G->busy = [[$busyNow - 600, $busyNow + 600]];
$avail2 = booking_availability($cfgNow);
$offered2 = [];
foreach (['America/Chicago', 'Asia/Kolkata', 'Europe/London', 'Asia/Kathmandu'] as $z) {
    $offered2 = array_merge($offered2, booking_zone_slots($cfgNow, $avail2['now'], $avail2['grid'], $z));
}
check('a busy calendar block is never offered', $offered2 && !array_filter($offered2, static fn(int $t): bool => $t >= $busyNow - 1800 - 900 && $t < $busyNow + 600 + 900));
$G->busy = [];

/* ---------------- a public booking files a lead ---------------- */
$GLOBALS['captured_mail'] = [];
$ps = $pick(booking_availability($cfgNow), 'Europe/London', 3);
$pub = booking_book(null, ['slot' => (string) $ps[0], 'tz' => 'Europe/London', 'name' => 'Dana Smith', 'email' => 'Dana.Smith@gmail.com', 'phone_cc' => 'GB', 'phone' => '+44 7400 123456', 'company' => 'Northwind', 'service' => 'SEO',
    'notes' => 'We want more qualified leads from search.', 'attribution' => json_encode(['first' => ['at' => gmdate('Y-m-d\TH:i:s.000\Z'), 'landing' => '/book-a-meeting/', 'ref' => 'https://www.linkedin.com/', 'q' => []], 'pages' => 1])]);
check('public booking', $pub['ok'], json_encode($pub));
$pm = booking_by_token($pub['token']);
$newLead = crm_lead((int) $pm['enquiry_id']);
check('public booking creates a lead', $newLead && $newLead['source'] === 'booking' && $newLead['email'] === 'dana.smith@gmail.com' && $newLead['service'] === 'SEO' && $newLead['channel'] === 'LinkedIn', json_encode($newLead));
check('the phone is stored in international form', $pm['phone'] === '+44 7400123456' && ($newLead['mobile'] ?? '') === '+44 7400123456', $pm['phone'] . ' / ' . ($newLead['mobile'] ?? ''));
check('public booking: London times', str_contains(($byTo('dana.smith@gmail.com', 'Booked:')[0]['html'] ?? ''), 'London time (GMT)'));
$pub2 = booking_book(null, ['slot' => (string) $pick(booking_availability($cfgNow), 'Europe/London', 6)[0], 'tz' => 'Europe/London', 'name' => 'Dana Smith', 'email' => 'dana.smith@gmail.com', 'phone_cc' => 'GB', 'phone' => '07400 123456']);
check('the same person again joins their lead', $pub2['ok'] && booking_by_token($pub2['token'])['enquiry_id'] === $pm['enquiry_id']);
$junk = booking_book(null, ['slot' => (string) $pick(booking_availability($cfgNow), 'Europe/London', 9)[0], 'tz' => 'Europe/London', 'name' => 'ABCDEFGHIJKL', 'email' => 'x@mailinator.com', 'phone_cc' => 'US', 'phone' => '212 555 0199', 'notes' => 'SEO backlinks guest post da 90+ casino']);
check('junk gets no call', !$junk['ok'] && !$junk['taken'] && str_contains($junk['error'], 'we will email you'), $junk['error']);
$junkLead = array_values(array_filter($db->leads, static fn(array $l): bool => $l['email'] === 'x@mailinator.com'))[0] ?? null;
check('junk booking kept in the Junk view with the time they wanted', $junkLead && $junkLead['status'] === 'junk' && $junkLead['tags'] === 'auto-junk'
    && str_contains((string) $junkLead['message'], 'Tried to book a call for ') && str_contains((string) $junkLead['message'], 'no call was booked'), json_encode($junkLead));
check('junk booking made no calendar event', !array_filter($G->events, static fn(array $e): bool => ($e['attendees'][0]['email'] ?? '') === 'x@mailinator.com'));

/* reCAPTCHA never refuses a form (owner, 9 Nov 2026): the score is weighed by the junk filter. */
$clean = ['name' => 'Dana Smith', 'email' => 'dana.smith@gmail.com', 'company' => 'Northwind', 'mobile' => '', 'message' => 'We want more qualified leads from search.'];
$scored = static fn($signal): array => lead_screen($clean, 'contact-page', false, ['captcha' => $signal]);
check('reCAPTCHA below 0.3 lands in Junk', $scored(0.1)['verdict'] === 'junk', json_encode($scored(0.1)));
check('reCAPTCHA below 0.5 is flagged for review', $scored(0.4)['verdict'] === 'review', json_encode($scored(0.4)));
check('a good reCAPTCHA score passes clean', $scored(0.9) === ['verdict' => 'ok', 'score' => 0, 'reasons' => []], json_encode($scored(0.9)));
check('a missing token is only noted', $scored('missing')['verdict'] === 'ok' && in_array('No reCAPTCHA token', $scored('missing')['reasons'], true));
$missing = booking_book(null, ['slot' => '0', 'tz' => 'Europe/London', 'name' => 'A', 'email' => 'nope']);
check('field errors', !$missing['ok'] && isset($missing['errors']['name'], $missing['errors']['email'], $missing['errors']['slot'], $missing['errors']['phone']));

/* ---------------- moving the call ---------------- */
$GLOBALS['captured_mail'] = [];
$GLOBALS['cancelled_mail'] = [];
$oldReminders = booking_by_token($token)['reminder_ids'];
$next = $slot[0] + 1800;
$availMove = booking_availability($cfgNow, booking_by_token($token));
$offeredNext = in_array($next, booking_zone_slots($cfgNow, $availMove['now'], $availMove['grid'], 'America/Chicago'), true);
$moveTo = $offeredNext ? $next : $pick($availMove, 'America/Chicago', 0, $slot[0])[0];
$nextLocal = (new DateTimeImmutable('@' . $next))->setTimezone(new DateTimeZone('America/Chicago'))->format('H:i');
check('the half hour after its own call is offered when moving', $offeredNext || $nextLocal > '18:00', $nextLocal . ' ' . (int) $offeredNext);
$mv = booking_book(booking_by_token($token), ['slot' => (string) $moveTo, 'tz' => 'America/Chicago'], 'move');
check('moved', $mv['ok'], json_encode($mv));
$patch = $G->last('PATCH', '/events/ev1');
parse_str((string) parse_url($patch[1] ?? '', PHP_URL_QUERY), $pq);
check('move patches the same event and tells the lead', $patch !== null && ($pq['sendUpdates'] ?? '') === 'all' && strtotime((string) $patch[2]['start']['dateTime']) === $moveTo);
$m = booking_by_token($token);
check('move recorded, same Meet link', $m['start'] === $moveTo && $m['reschedules'] === 1 && $m['meet_url'] === 'https://meet.google.com/abc-defg-001' && $m['status'] === 'booked');
check('old reminders called off', $oldReminders === [] || array_values(array_intersect($oldReminders, $GLOBALS['cancelled_mail'] ?? [])) === array_values($oldReminders), json_encode($GLOBALS['cancelled_mail'] ?? []));
$moved = $byTo('bauer@cocopat.us', 'Moved:')[0] ?? [];
check('lead told of the move with a fresh ics', $moved !== [] && str_contains(base64_decode((string) ($moved['attachments'][0]['content'] ?? '')), 'SEQUENCE:1'));
check('owner told of the move', (bool) array_filter($GLOBALS['captured_mail'], static fn(array $x): bool => str_starts_with((string) $x['subject'], 'Meeting moved: Bauer')));
check('timeline: rescheduled', $has($timeline(), 'Rescheduled the meeting from ' . booking_ist($slot[0]) . ' to ' . booking_ist($moveTo)));

/* ---------------- what the lead sees once booked ---------------- */
$html = $page('book-a-meeting/' . $token, ['done' => 'booked']);
check('booked page', str_contains($html, 'You are booked') && str_contains($html, 'href="https://meet.google.com/abc-defg-001"') && str_contains($html, '/invite.ics') && str_contains($html, 'Open in Google Calendar')
    && str_contains($html, '?change=1') && str_contains($html, '?cancel=1') && str_contains($html, 'in Chennai, India'), substr(strip_tags($html), 0, 400));
$html = $page('book-a-meeting/' . $token, ['change' => '1']);
check('reschedule page', str_contains($html, 'Move my call to this time') && str_contains($html, 'Now booked:') && str_contains($html, '"mode":"move"'));
$html = $page('book-a-meeting/' . $token, ['cancel' => '1']);
check('cancel page', str_contains($html, 'Cancel your call?') && str_contains($html, 'name="action" value="cancel"') && str_contains($html, 'move it instead'));
$icsOut = $page('book-a-meeting/' . $token . '/invite.ics');
check('ics download', str_starts_with($icsOut, 'BEGIN:VCALENDAR') && str_contains($icsOut, 'UID:ev1@google.com'));
$html = $page('book-a-meeting/' . str_repeat('0', 32));
check('unknown link', str_contains($html, 'This booking link is not valid'));
$html = $page('book-a-meeting');
check('public page', str_contains($html, '<title>Book a call with VTurnU | VTurnU</title>') && str_contains($html, '30-minute video call') && str_contains($html, 'Select a date and time') && str_contains($html, 'Choose a topic')
    && str_contains($html, '"recaptcha":"' . RECAPTCHA_SITE_KEY . '"') && !$dash($html));

/* ---------------- Google Calendar changes come back ---------------- */
$G->events['ev1']['attendees'][0]['responseStatus'] = 'accepted';
$s1 = booking_sync(booking_by_token($token));
check('lead accepted the invitation', $s1['rsvp'] === 'accepted' && booking_by_token($token)['rsvp'] === 'accepted' && $has($timeline(), 'Accepted the calendar invitation'));
$shifted = $moveTo + 3600;
$G->events['ev1']['start']['dateTime'] = gmdate('c', $shifted);
$G->events['ev1']['end']['dateTime'] = gmdate('c', $shifted + 1800);
$s2 = booking_sync(booking_by_token($token));
check('moved in Google Calendar', $s2['start'] === $shifted && booking_by_token($token)['start'] === $shifted && $has($timeline(), 'Meeting moved in Google Calendar from'));

/* ---------------- cancelling ---------------- */
$GLOBALS['captured_mail'] = [];
$c = booking_cancel(booking_by_token($token), 'The timing changed on our side.', 'lead');
$del = $G->last('DELETE', '/events/ev1');
parse_str((string) parse_url($del[1] ?? '', PHP_URL_QUERY), $dq);
check('cancelled', $c['ok'] && booking_by_token($token)['status'] === 'cancelled' && booking_by_token($token)['cancel_reason'] === 'The timing changed on our side.');
check('Google withdraws the invitation', $del !== null && ($dq['sendUpdates'] ?? '') === 'all' && !isset($G->events['ev1']));
check('both sides told', (bool) $byTo('bauer@cocopat.us', 'Cancelled:') && (bool) array_filter($GLOBALS['captured_mail'], static fn(array $x): bool => str_starts_with((string) $x['subject'], 'Meeting cancelled: Bauer')));
check('timeline: cancelled with the reason', $has($timeline(), 'Cancelled the meeting (') && $has($timeline(), 'The timing changed on our side.'));
$html = $page('book-a-meeting/' . $token, ['done' => 'cancelled']);
check('after cancelling, the link offers new times', str_contains($html, 'Your call is cancelled') && str_contains($html, 'Schedule the call'));
$re = booking_book(booking_by_token($token), ['slot' => (string) $pick(booking_availability($cfgNow), 'America/Chicago', 2)[0], 'tz' => 'America/Chicago', 'name' => 'Bauer', 'email' => 'bauer@cocopat.us', 'phone' => '+1 2103294540']);
check('book again on the same link', $re['ok'] && $re['token'] === $token && booking_by_token($token)['status'] === 'booked');
unset($G->events[booking_by_token($token)['event_id']]);
check('deleted in Google Calendar', booking_sync(booking_by_token($token))['status'] === 'cancelled' && booking_by_token($token)['cancelled_by'] === 'calendar' && $has($timeline(), 'Meeting removed from the Google Calendar'));

/* ---------------- breakage is reported, never guessed ---------------- */
$GLOBALS['captured_mail'] = [];
$G->failFreeBusy = true;
$failed = false;
try {
    booking_availability($cfgNow);
} catch (Throwable $e) {
    $failed = str_contains($e->getMessage(), 'free/busy');
}
check('a calendar that cannot be read throws rather than offering times', $failed);
$html = $page('book-a-meeting');
check('the page says so and offers email and WhatsApp', str_contains($html, 'We could not read our calendar just now.') && str_contains($html, 'wa.me/919363731498') && !str_contains($html, 'id="bk-data"'));
check('the owner is alerted', (bool) array_filter($GLOBALS['captured_mail'], static fn(array $x): bool => ($x['to'] ?? []) === ['connect@vturnu.com'] && str_starts_with((string) $x['subject'], 'Booking page problem')));
$failBook = booking_book(null, ['slot' => (string) $slot[0], 'tz' => 'America/Chicago', 'name' => 'Dana Smith', 'email' => 'dana.smith@gmail.com', 'phone_cc' => 'GB', 'phone' => '07400 123456']);
check('booking refuses politely while the calendar is unreadable', !$failBook['ok'] && str_contains($failBook['error'], 'could not check the calendar'));
$G->failFreeBusy = false;

/* ---------------- deleted leads: hidden first, erased only on request ---------------- */
$db->bookingSettings = [];
booking_settings(true);
$when = static fn(string $s): string => date('Y-m-d H:i:sP', strtotime($s));
$db->leads = [
    fixture_lead(['id' => '801', 'name' => 'Vignesh', 'email' => 'vignesh@singapore.com', 'mobile' => '+61 98338383', 'source' => 'booking', 'deleted_at' => $when('-2 hours')]),
    fixture_lead(['id' => '802', 'name' => 'Live Person', 'email' => 'live@example.com', 'deleted_at' => null]),
    fixture_lead(['id' => '803', 'name' => 'Upcoming Test', 'email' => 'upcoming@example.com', 'deleted_at' => $when('-1 hour')]),
];
$db->proposals = [
    ['id' => 1, 'enquiry_id' => 801, 'parent_id' => null, 'token' => 'p1', 'kind' => 'proposal'], ['id' => 2, 'enquiry_id' => 801, 'parent_id' => 1, 'token' => 'p2', 'kind' => 'followup'],
    ['id' => 3, 'enquiry_id' => 802, 'parent_id' => null, 'token' => 'p3', 'kind' => 'proposal'],
];
$meetingRow = static function (array $o) use ($db): array {
    $r = ['id' => count($db->meetings) + 1, 'enquiry_id' => null, 'token' => bin2hex(random_bytes(16)), 'status' => 'cancelled', 'name' => '', 'email' => '', 'phone' => '', 'company' => '', 'notes' => '',
        'time_zone' => '', 'start_at' => $o['start_at'] ?? null, 'end_at' => $o['end_at'] ?? null, 'event_id' => '', 'ical_uid' => '', 'event_link' => '', 'meet_url' => '', 'sequence' => 0, 'rsvp' => '',
        'reschedules' => 0, 'reminder_ids' => '[]', 'request_subject' => '', 'request_body' => '', 'requested_by' => '', 'requested_at' => null, 'request_mail_id' => '', 'opened_at' => null,
        'viewed_at' => null, 'booked_at' => null, 'cancelled_at' => null, 'cancelled_by' => '', 'cancel_reason' => '', 'created_at' => date('Y-m-d H:i:sP'), 'updated_at' => date('Y-m-d H:i:sP')];
    return array_merge($r, $o);
};
$db->meetings = [
    $meetingRow(['id' => 1, 'enquiry_id' => 801, 'email' => 'vignesh@singapore.com', 'status' => 'cancelled', 'start_at' => $when('+9 days'), 'end_at' => $when('+9 days +30 minutes')]),
    $meetingRow(['id' => 2, 'enquiry_id' => 802, 'email' => 'live@example.com', 'status' => 'cancelled', 'start_at' => $when('+8 days'), 'end_at' => $when('+8 days +30 minutes')]),
    $meetingRow(['id' => 3, 'enquiry_id' => 803, 'email' => 'upcoming@example.com', 'status' => 'booked', 'start_at' => $when('+3 days'), 'end_at' => $when('+3 days +30 minutes')]),
];
$listed = array_map(static fn(array $l): string => (string) $l['id'], crm_deleted_leads());
check('deleted leads list: only the hidden ones, newest deletion first', $listed === ['803', '801'], implode(',', $listed));
check('a live lead cannot be erased', crm_purge_lead(802) !== '' && count($db->leads) === 3 && count($db->meetings) === 3 && count($db->proposals) === 3);
check('a lead with a call still booked cannot be erased', str_contains(crm_purge_lead(803), 'still has a call booked') && count($db->leads) === 3 && count($db->meetings) === 3);
check('an unknown lead cannot be erased', crm_purge_lead(999) !== '' && count($db->leads) === 3);
$db->failOn = 'proposals';
check('a failure half way changes nothing', str_contains(crm_purge_lead(801), 'Nothing was changed') && count($db->leads) === 3 && count($db->meetings) === 3 && count($db->proposals) === 3);
$db->failOn = null;
check('erasing a deleted lead removes it, its meetings and its proposals', crm_purge_lead(801) === ''
    && !array_filter($db->leads, static fn(array $l): bool => (int) $l['id'] === 801)
    && !array_filter($db->meetings, static fn(array $m): bool => (int) $m['enquiry_id'] === 801)
    && !array_filter($db->proposals, static fn(array $p): bool => (int) $p['enquiry_id'] === 801));
check('everything of other leads is untouched', count($db->leads) === 2 && count($db->meetings) === 2 && count($db->proposals) === 1 && $db->proposals[0]['enquiry_id'] === 802);
crm_undelete_lead(803);
$listed = array_map(static fn(array $l): string => (string) $l['id'], crm_deleted_leads());
check('a deleted lead can be put back', $listed === [] && empty(array_values(array_filter($db->leads, static fn(array $l): bool => (int) $l['id'] === 803))[0]['deleted_at']));
$db->leads = [];
$db->meetings = [];
$db->proposals = [];

/* ---------------- settings, schema, signature ---------------- */
booking_save_settings(['enabled' => '1', 'host_name' => 'B. Vignesh', 'host_title' => 'Founder & CEO', 'hours' => '10:00-13:00, 14:00-18:00', 'days' => ['1', '3'], 'length' => '30', 'buffer' => '10', 'notice_minutes' => '30', 'window_days' => '14', 'reminders' => '1']);
$saved = booking_settings(true);
check('settings saved', $saved['hours'] === '10:00-13:00, 14:00-18:00' && $saved['notice_minutes'] === 30 && $saved['days'] === [1, 3] && $saved['buffer'] === 10 && $saved['window_days'] === 14, json_encode($saved));
$db->bookingSettings = ['hours' => '09:30-18:30', 'core' => '09:30-18:30', 'overseas' => '19:00-23:00', 'notice_hours' => 4, 'days' => [1, 2, 3, 4, 5]];
$legacy = booking_settings(true);
check('settings saved under the old rule fall back to the new defaults', $legacy['notice_minutes'] === 0 && !isset($legacy['core']) && !isset($legacy['overseas']) && !isset($legacy['notice_hours']), json_encode($legacy));
$db->bookingSettings = [];
booking_settings(true);
$schema = (string) file_get_contents('/site/db/schema.sql');
$own = (string) file_get_contents('/site/includes/booking-schema.sql');
/* The booking block in db/schema.sql ends where the next feature's own block
   begins (today, Vee's chat tables); bound the slice there instead of running
   to end of file, so appending another feature after it never breaks this. */
$bookingStart = (int) strpos($schema, 'create table if not exists meetings');
$nextBlock = strpos($schema, "\n-- Vee, the AI chat assistant", $bookingStart);
$bookingSlice = $nextBlock !== false ? substr($schema, $bookingStart, $nextBlock - $bookingStart) : substr($schema, $bookingStart);
$ownSlice = trim(substr($own, (int) strpos($own, 'create table if not exists meetings')));
check('includes/booking-schema.sql matches db/schema.sql', trim($bookingSlice) === $ownSlice && str_contains($own, 'exclude using gist'));
$sig = crm_signature_html(crm_settings(), 'IN') . crm_email_html('x', 'Hi there,', crm_settings(), str_repeat('a', 32), str_repeat('a', 32));
check('CRM signature and proposal button are Gmail-safe', $gmailSafe($sig) && str_contains($sig, 'bgcolor="#4338CA"'));
check('signature shows the WhatsApp logo (owner, 9 Nov 2026)', str_contains($sig, '/assets/img/email/sig-whatsapp-logo.png'));
check('booking page shows the professional photo, not the selfie', str_contains($page('book-a-meeting'), '/assets/img/booking/host-b-vignesh-192.jpg') && !is_file('/site/assets/img/booking/host-vignesh-192.jpg'));
$logged = (string) @file_get_contents('/tmp/booking-test-errors.log');
check('failures were logged for the owner', str_contains($logged, 'exclusion constraint') && str_contains($logged, 'held back as junk') && str_contains($logged, 'free/busy failed'));

done();
