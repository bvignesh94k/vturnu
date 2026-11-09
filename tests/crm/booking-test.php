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

/** Google Calendar, as far as the booking code talks to it. Records every call. */
final class FakeGoogle
{
    public array $calls = [];
    public array $busy = [];
    public array $events = [];
    public int $n = 0;
    public bool $hideOwn = false;
    public bool $failFreeBusy = false;
    public function __invoke(string $method, string $url, ?array $body): array
    {
        $this->calls[] = [$method, $url, $body];
        $path = (string) parse_url($url, PHP_URL_PATH);
        parse_str((string) parse_url($url, PHP_URL_QUERY), $q);
        if (str_contains($url, 'oauth2.googleapis.com/token')) {
            return ($body['refresh_token'] ?? '') === 'revoked' ? [400, ['error' => 'invalid_grant', 'error_description' => 'Token has been expired or revoked.']] : [200, ['access_token' => 'ya29.test', 'expires_in' => 3599]];
        }
        if ($path === '/calendar/v3/freeBusy' && $this->failFreeBusy) {
            return [503, ['error' => ['message' => 'Backend Error']]];
        }
        if ($path === '/calendar/v3/freeBusy') {
            $from = strtotime($body['timeMin']);
            $to = strtotime($body['timeMax']);
            $busy = [];
            foreach (array_merge($this->busy, $this->hideOwn ? [] : $this->blocks()) as [$s, $e]) {
                if ($s < $to && $e > $from) {
                    $busy[] = ['start' => gmdate('Y-m-d\TH:i:s\Z', max($s, $from)), 'end' => gmdate('Y-m-d\TH:i:s\Z', min($e, $to))];
                }
            }
            return [200, ['calendars' => ['primary' => ['busy' => $busy]]]];
        }
        if ($path === '/calendar/v3/calendars/primary/events' && $method === 'POST') {
            $id = 'ev' . (++$this->n);
            $ev = $body + ['id' => $id, 'status' => 'confirmed', 'iCalUID' => $id . '@google.com', 'htmlLink' => 'https://www.google.com/calendar/event?eid=' . $id, 'sequence' => 0,
                'hangoutLink' => 'https://meet.google.com/abc-defg-' . str_pad((string) $this->n, 3, '0', STR_PAD_LEFT)];
            $ev['attendees'] = array_map(static fn(array $a): array => $a + ['responseStatus' => 'needsAction'], $body['attendees'] ?? []);
            $this->events[$id] = $ev;
            return [200, $ev];
        }
        if ($path === '/calendar/v3/calendars/primary/events' && $method === 'GET') {
            $from = strtotime($q['timeMin']);
            $to = strtotime($q['timeMax']);
            $items = $this->hideOwn ? [] : array_values(array_filter($this->events, static fn(array $e): bool => strtotime($e['start']['dateTime']) < $to && strtotime($e['end']['dateTime']) > $from));
            foreach ($this->busy as [$s, $e]) {
                if ($s < $to && $e > $from) {
                    $items[] = ['id' => 'other', 'status' => 'confirmed', 'start' => ['dateTime' => gmdate('c', $s)], 'end' => ['dateTime' => gmdate('c', $e)]];
                }
            }
            return [200, ['timeZone' => 'Asia/Kolkata', 'items' => $items]];
        }
        if (preg_match('#^/calendar/v3/calendars/primary/events/([^/]+)$#', $path, $m)) {
            $id = rawurldecode($m[1]);
            if (!isset($this->events[$id])) {
                return [404, ['error' => ['message' => 'Not Found']]];
            }
            if ($method === 'GET') {
                return [200, $this->events[$id]];
            }
            if ($method === 'PATCH') {
                $this->events[$id] = array_merge($this->events[$id], $body);
                $this->events[$id]['sequence']++;
                return [200, $this->events[$id]];
            }
            if ($method === 'DELETE') {
                unset($this->events[$id]);
                return [204, []];
            }
        }
        return [500, ['error' => ['message' => 'FakeGoogle does not know ' . $method . ' ' . $url]]];
    }
    private function blocks(): array
    {
        return array_map(static fn(array $e): array => [strtotime($e['start']['dateTime']), strtotime($e['end']['dateTime'])], array_values($this->events));
    }
    public function last(string $method, string $needle = ''): ?array
    {
        foreach (array_reverse($this->calls) as $c) {
            if ($c[0] === $method && ($needle === '' || str_contains($c[1], $needle))) {
                return $c;
            }
        }
        return null;
    }
}

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
check('owner defaults', $cfg['host_name'] === 'B. Vignesh' && $cfg['host_title'] === 'Founder & CEO' && $cfg['core'] === '09:30-18:30' && $cfg['overseas'] === '19:00-23:00' && $cfg['days'] === [1, 2, 3, 4, 5] && $cfg['length'] === 30);
$bad = booking_clean_settings(['enabled' => '1', 'length' => 33, 'buffer' => 500, 'notice_hours' => -3, 'window_days' => 0, 'days' => ['9', 'x'], 'core' => 'all day', 'overseas' => '', 'host_name' => '']);
check('settings are checked', $bad['length'] === 30 && $bad['buffer'] === 60 && $bad['notice_hours'] === 0 && $bad['window_days'] === 1 && $bad['days'] === [1, 2, 3, 4, 5] && $bad['core'] === '09:30-18:30' && $bad['overseas'] === '' && $bad['host_name'] === 'B. Vignesh', json_encode($bad));

$ist = new DateTimeZone('Asia/Kolkata');
$at = static fn(string $s): int => (new DateTimeImmutable($s, $ist))->getTimestamp();
$now = $at('2026-11-09 06:00');           // a Monday morning in Chennai
$slots = booking_slots($cfg, $now, []);
$times = array_map(static fn(array $s): string => (new DateTimeImmutable('@' . $s[0]))->setTimezone($ist)->format('D H:i'), $slots);
check('first slot after 4 hours notice', $times[0] === 'Mon 10:00', $times[0]);
check('last core slot ends at 18:30', in_array('Mon 18:00', $times, true) && !in_array('Mon 18:30', $times, true));
check('overseas evening slots', in_array('Mon 19:00', $times, true) && in_array('Mon 22:30', $times, true) && !in_array('Mon 23:00', $times, true));
check('weekends off', !array_filter($times, static fn(string $t): bool => str_starts_with($t, 'Sat') || str_starts_with($t, 'Sun')));
$overFlag = array_values(array_filter($slots, static fn(array $s): bool => (new DateTimeImmutable('@' . $s[0]))->setTimezone($ist)->format('H:i') === '19:00'))[0][1] ?? null;
check('evening slots marked overseas', $overFlag === 1 && $slots[0][1] === 0);
check('window of 21 days', max(array_column($slots, 0)) < $now + 21 * 86400);
check('30 minute grid', !array_filter($slots, static fn(array $s): bool => $s[0] % 1800 !== 0));
$busy = [[$at('2026-11-10 12:00'), $at('2026-11-10 13:00')]];
$tue = array_values(array_map(static fn(array $s): string => (new DateTimeImmutable('@' . $s[0]))->setTimezone($ist)->format('H:i'),
    array_filter(booking_slots($cfg, $now, $busy), static fn(array $s): bool => (new DateTimeImmutable('@' . $s[0]))->setTimezone($ist)->format('Y-m-d') === '2026-11-10')));
check('15 minute buffer around busy time', in_array('11:00', $tue, true) && !in_array('11:30', $tue, true) && !in_array('12:00', $tue, true) && !in_array('13:00', $tue, true) && in_array('13:30', $tue, true), implode(' ', $tue));
check('overseas only outside India', booking_slot_for([1, 1], 'America/Chicago') && !booking_slot_for([1, 1], 'Asia/Kolkata') && !booking_slot_for([1, 1], 'Asia/Calcutta') && booking_slot_for([1, 0], 'Asia/Kolkata'));
check('Chicago morning is daytime', booking_daytime($at('2026-11-10 19:30'), 30, 'America/Chicago') && !booking_daytime($at('2026-11-10 10:00'), 30, 'America/Chicago'));
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
check('personal page greets and prefills', str_contains($html, 'Bauer, let us find a time') && str_contains($html, 'value="Bauer"') && str_contains($html, 'value="bauer@cocopat.us"'), substr($html, 0, 300));
check('personal page agenda from their words', str_contains($html, 'Your goals for Google Ads and Meta Ads'));
check('page carries slots and the IP zone', str_contains($html, 'id="bk-data"') && str_contains($html, '"ipTz":"America/Chicago"') && str_contains($html, '"mode":"book"') && str_contains($html, '"recaptcha":""'));
check('no-JS list in the visitor zone', str_contains($html, '<select id="bk-slot" name="slot"') && str_contains($html, 'Time (Chicago time (GMT-'));
check('page is private', str_contains($html, '<meta name="robots" content="noindex, nofollow">'));
check('no dashes on the page', !$dash($html));
booking_track_view($token);
check('page view on the timeline', $has($timeline(), 'Opened the booking page'));

/* ---------------- booking ---------------- */
$cfgNow = booking_settings();
$avail = booking_availability($cfgNow);
$pick = static function (array $avail, string $tz, int $skip = 0, ?int $after = null) {
    foreach ($avail['slots'] as $s) {
        if (booking_slot_for($s, $tz) && booking_daytime($s[0], 30, $tz) && ($after === null || $s[0] > $after) && $skip-- <= 0) {
            return $s;
        }
    }
    return null;
};
$slot = $pick($avail, 'America/Chicago');
check('a Chicago morning slot is offered', $slot !== null && $slot[1] === 1, json_encode($slot));
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
$again = booking_book(null, ['slot' => (string) $slot[0], 'tz' => 'America/Chicago', 'name' => 'Dana Smith', 'email' => 'dana.smith@gmail.com']);
check('same time refused to the next person', !$again['ok'] && $again['taken'], json_encode($again));
$india = $pick($avail, 'America/Chicago', 1);
$asIndia = booking_book(null, ['slot' => (string) $india[0], 'tz' => 'Asia/Kolkata', 'name' => 'Dana Smith', 'email' => 'dana.smith@gmail.com']);
check('evening hours refused to an India time zone', !$asIndia['ok'] && $asIndia['taken']);
$soon = booking_book(null, ['slot' => (string) (intdiv(time(), 1800) * 1800 + 1800), 'tz' => 'America/Chicago', 'name' => 'Dana Smith', 'email' => 'dana.smith@gmail.com']);
check('inside the minimum notice refused', !$soon['ok']);
check('nothing sent for refused bookings', count($GLOBALS['captured_mail']) === 0);
/* Two people press Book at the same moment: both pages offered the time; the database's no-overlap rule stops the second, and its calendar event is taken back. */
$db->hideBusy = true;
$G->hideOwn = true;
$eventsBefore = count($G->events);
$race = booking_book(null, ['slot' => (string) $slot[0], 'tz' => 'America/Chicago', 'name' => 'Dana Smith', 'email' => 'dana.smith@gmail.com']);
$db->hideBusy = false;
$G->hideOwn = false;
check('race lost to the no-overlap rule', !$race['ok'] && $race['taken'], json_encode($race));
check('race: the stray event is removed', count($G->events) === $eventsBefore && $G->last('DELETE') !== null);
check('race: the first booking is untouched', booking_by_token($token)['status'] === 'booked' && booking_by_token($token)['start'] === $slot[0]);
$busyNow = $slot[0] + 7200;
$G->busy = [[$busyNow - 600, $busyNow + 600]];
$avail2 = booking_availability($cfgNow);
check('a busy calendar block is never offered', !array_filter($avail2['slots'], static fn(array $s): bool => $s[0] >= $busyNow - 1800 - 900 && $s[0] < $busyNow + 600 + 900));
$G->busy = [];

/* ---------------- a public booking files a lead ---------------- */
$GLOBALS['captured_mail'] = [];
$ps = $pick(booking_availability($cfgNow), 'Europe/London', 3);
$pub = booking_book(null, ['slot' => (string) $ps[0], 'tz' => 'Europe/London', 'name' => 'Dana Smith', 'email' => 'Dana.Smith@gmail.com', 'company' => 'Northwind', 'service' => 'SEO',
    'notes' => 'We want more qualified leads from search.', 'attribution' => json_encode(['first' => ['at' => gmdate('Y-m-d\TH:i:s.000\Z'), 'landing' => '/book-a-meeting/', 'ref' => 'https://www.linkedin.com/', 'q' => []], 'pages' => 1])]);
check('public booking', $pub['ok'], json_encode($pub));
$pm = booking_by_token($pub['token']);
$newLead = crm_lead((int) $pm['enquiry_id']);
check('public booking creates a lead', $newLead && $newLead['source'] === 'booking' && $newLead['email'] === 'dana.smith@gmail.com' && $newLead['service'] === 'SEO' && $newLead['channel'] === 'LinkedIn', json_encode($newLead));
check('public booking: London times', str_contains(($byTo('dana.smith@gmail.com', 'Booked:')[0]['html'] ?? ''), 'London time (GMT)'));
$pub2 = booking_book(null, ['slot' => (string) $pick(booking_availability($cfgNow), 'Europe/London', 6)[0], 'tz' => 'Europe/London', 'name' => 'Dana Smith', 'email' => 'dana.smith@gmail.com']);
check('the same person again joins their lead', $pub2['ok'] && booking_by_token($pub2['token'])['enquiry_id'] === $pm['enquiry_id']);
$junk = booking_book(null, ['slot' => (string) $pick(booking_availability($cfgNow), 'Europe/London', 9)[0], 'tz' => 'Europe/London', 'name' => 'ABCDEFGHIJKL', 'email' => 'x@mailinator.com', 'notes' => 'SEO backlinks guest post da 90+ casino']);
check('junk refused', !$junk['ok'] && !$junk['taken']);
$missing = booking_book(null, ['slot' => '0', 'tz' => 'Europe/London', 'name' => 'A', 'email' => 'nope']);
check('field errors', !$missing['ok'] && isset($missing['errors']['name'], $missing['errors']['email'], $missing['errors']['slot']));

/* ---------------- moving the call ---------------- */
$GLOBALS['captured_mail'] = [];
$GLOBALS['cancelled_mail'] = [];
$oldReminders = booking_by_token($token)['reminder_ids'];
$next = $slot[0] + 1800;
$offeredNext = (bool) array_filter(booking_availability($cfgNow, booking_by_token($token))['slots'], static fn(array $s): bool => $s[0] === $next);
$moveTo = $offeredNext ? $next : $pick(booking_availability($cfgNow, booking_by_token($token)), 'America/Chicago', 0, $slot[0])[0];
check('the half hour after its own call is offered when moving', $offeredNext || booking_ranges('19:00-23:00') === [], (string) $offeredNext);
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
check('public page', str_contains($html, '<title>Book a call with VTurnU | VTurnU</title>') && str_contains($html, 'Talk with B. Vignesh') && str_contains($html, 'Choose a topic')
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
check('after cancelling, the link offers new times', str_contains($html, 'Your call is cancelled') && str_contains($html, 'Confirm the booking'));
$re = booking_book(booking_by_token($token), ['slot' => (string) $pick(booking_availability($cfgNow), 'America/Chicago', 2)[0], 'tz' => 'America/Chicago', 'name' => 'Bauer', 'email' => 'bauer@cocopat.us']);
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
$failBook = booking_book(null, ['slot' => (string) $slot[0], 'tz' => 'America/Chicago', 'name' => 'Dana Smith', 'email' => 'dana.smith@gmail.com']);
check('booking refuses politely while the calendar is unreadable', !$failBook['ok'] && str_contains($failBook['error'], 'could not check the calendar'));
$G->failFreeBusy = false;

/* ---------------- settings, schema, signature ---------------- */
booking_save_settings(['enabled' => '1', 'host_name' => 'B. Vignesh', 'host_title' => 'Founder & CEO', 'core' => '10:00-13:00, 14:00-18:00', 'overseas' => '', 'days' => ['1', '3'], 'length' => '30', 'buffer' => '10', 'notice_hours' => '2', 'window_days' => '14', 'reminders' => '1']);
$saved = booking_settings(true);
check('settings saved', $saved['core'] === '10:00-13:00, 14:00-18:00' && $saved['overseas'] === '' && $saved['days'] === [1, 3] && $saved['buffer'] === 10 && $saved['window_days'] === 14, json_encode($saved));
$db->bookingSettings = [];
booking_settings(true);
$schema = (string) file_get_contents('/site/db/schema.sql');
$own = (string) file_get_contents('/site/includes/booking-schema.sql');
$from = static fn(string $s): string => trim(substr($s, (int) strpos($s, 'create table if not exists meetings')));
check('includes/booking-schema.sql matches db/schema.sql', $from($schema) === $from($own) && str_contains($own, 'exclude using gist'));
$sig = crm_signature_html(crm_settings(), 'IN') . crm_email_html('x', 'Hi there,', crm_settings(), str_repeat('a', 32), str_repeat('a', 32));
check('CRM signature and proposal button are Gmail-safe', $gmailSafe($sig) && str_contains($sig, 'bgcolor="#4338CA"'));
check('signature shows the WhatsApp logo (owner, 9 Nov 2026)', str_contains($sig, '/assets/img/email/sig-whatsapp-logo.png'));
check('booking page shows the professional photo, not the selfie', str_contains($page('book-a-meeting'), '/assets/img/booking/host-b-vignesh-192.jpg') && !is_file('/site/assets/img/booking/host-vignesh-192.jpg'));
$logged = (string) @file_get_contents('/tmp/booking-test-errors.log');
check('failures were logged for the owner', str_contains($logged, 'exclusion constraint') && str_contains($logged, 'refused as junk') && str_contains($logged, 'free/busy failed'));

done();
