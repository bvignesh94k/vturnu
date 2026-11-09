<?php
/**
 * Book a Meeting: 30-minute video calls on the connect@vturnu.com Google
 * Calendar, each with its own Google Meet link.
 *
 * Two ways in:
 * - the CRM's "Book a Meeting" button emails a lead a personal link
 *   (/book-a-meeting/<token>/) that already knows who they are;
 * - anyone can book at /book-a-meeting/, and the booking is filed as a lead.
 *
 * Times are offered in the visitor's own time zone (the edge's geo-IP guess,
 * checked against the browser's clock by assets/js/booking.js), only where the
 * connect@ calendar is free, with a buffer around every busy block, a minimum
 * notice and a booking window (Settings > Booking in the admin). The chosen
 * time is checked with Google again at the moment of booking, under a database
 * lock, and the meetings table refuses two overlapping calls outright, so
 * nobody is ever double booked.
 *
 * A booking creates the event with a Meet link and the lead as a guest, so
 * Google sends them the invitation and it blocks their calendar too. Both
 * sides also get a confirmation with an .ics file, the lead gets links to
 * reschedule or cancel, and reminders go out a day and an hour before. Each
 * step lands on the lead's timeline in the CRM.
 *
 * Google access: an OAuth client of the "VTurnU Website" Cloud project
 * (Internal app) with a refresh token for connect@vturnu.com, in the Vercel
 * variables GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET and GOOGLE_REFRESH_TOKEN.
 * bin/google-connect.mjs sets them up again if the connection ever breaks.
 */

declare(strict_types=1);

const BOOKING_PATH = 'book-a-meeting';
/** Where the hours in Settings are counted. */
const BOOKING_HOST_TZ = 'Asia/Kolkata';
/** Zones that count as India: they are offered the main hours only. */
const BOOKING_INDIA_TZ = ['Asia/Kolkata', 'Asia/Calcutta'];
/** Local hours shown by default; times outside them sit behind "Show all times". */
const BOOKING_DAY_HOURS = [7, 21];
/** One booking at a time takes this lock while it checks the slot and writes the event. */
const BOOKING_LOCK = 724001;

/* ------------------------------------------------------------------ */
/* Settings                                                            */
/* ------------------------------------------------------------------ */

/** The owner's choices, 9 Nov 2026: B. Vignesh hosts; 9:30 to 6:30 IST for everyone, 7 to 11 PM IST for visitors outside India. */
function booking_setting_defaults(): array
{
    return [
        'enabled' => true,
        'host_name' => 'B. Vignesh',
        'host_title' => 'Founder & CEO',
        'length' => 30,
        'buffer' => 15,
        'notice_hours' => 4,
        'window_days' => 21,
        'days' => [1, 2, 3, 4, 5],
        'core' => '09:30-18:30',
        'overseas' => '19:00-23:00',
        'reminders' => true,
    ];
}

function booking_settings(bool $fresh = false): array
{
    static $cache = null;
    if ($cache !== null && !$fresh) {
        return $cache;
    }
    $saved = [];
    try {
        $row = db()->query("SELECT value FROM crm_settings WHERE key = 'booking'")->fetch();
        $saved = $row ? (json_decode((string) $row['value'], true) ?: []) : [];
    } catch (Throwable $e) {
        error_log('booking_settings: ' . $e->getMessage());
    }
    return $cache = booking_clean_settings(array_merge(booking_setting_defaults(), array_intersect_key($saved, booking_setting_defaults())));
}

/** Settings as they may be stored: every value checked, anything odd back to its default. */
function booking_clean_settings(array $s): array
{
    $d = booking_setting_defaults();
    $days = array_values(array_unique(array_filter(array_map('intval', (array) ($s['days'] ?? [])), static fn(int $n): bool => $n >= 1 && $n <= 7)));
    sort($days);
    $range = static fn($v, string $key): string => is_string($v) && booking_ranges($v) ? crm_clean_line($v, 120) : ($key === 'overseas' && is_string($v) && trim($v) === '' ? '' : $d[$key]);
    return [
        'enabled' => !empty($s['enabled']),
        'host_name' => crm_clean_line((string) ($s['host_name'] ?? ''), 60) ?: $d['host_name'],
        'host_title' => crm_clean_line((string) ($s['host_title'] ?? ''), 60),
        'length' => in_array((int) ($s['length'] ?? 0), [15, 20, 30, 45, 60], true) ? (int) $s['length'] : $d['length'],
        'buffer' => max(0, min(60, (int) ($s['buffer'] ?? $d['buffer']))),
        'notice_hours' => max(0, min(168, (int) ($s['notice_hours'] ?? $d['notice_hours']))),
        'window_days' => max(1, min(60, (int) ($s['window_days'] ?? $d['window_days']))),
        'days' => $days ?: $d['days'],
        'core' => $range($s['core'] ?? null, 'core'),
        'overseas' => $range($s['overseas'] ?? null, 'overseas'),
        'reminders' => !empty($s['reminders']),
    ];
}

function booking_save_settings(array $in): void
{
    $in['days'] = (array) ($in['days'] ?? []);
    $clean = booking_clean_settings($in);
    db()->prepare("INSERT INTO crm_settings (key, value, updated_at) VALUES ('booking', CAST(? AS jsonb), now())
                   ON CONFLICT (key) DO UPDATE SET value = excluded.value, updated_at = now()")
        ->execute([json_encode($clean, JSON_UNESCAPED_UNICODE)]);
    booking_settings(true);
}

/**
 * "09:30-13:00, 14:00-18:30" as minutes after midnight: [[570, 780], [840, 1110]].
 * A range that ends before it starts runs past midnight ("19:00-01:00").
 *
 * @return list<array{0:int,1:int}>
 */
function booking_ranges(string $spec): array
{
    $out = [];
    foreach (preg_split('/[,;]+/', $spec) ?: [] as $part) {
        if (!preg_match('/^\s*(\d{1,2})[:.](\d{2})\s*(?:-|to)\s*(\d{1,2})[:.](\d{2})\s*$/i', $part, $m)) {
            continue;
        }
        [$h1, $m1, $h2, $m2] = [(int) $m[1], (int) $m[2], (int) $m[3], (int) $m[4]];
        if ($h1 > 23 || $h2 > 24 || $m1 > 59 || $m2 > 59) {
            continue;
        }
        $a = $h1 * 60 + $m1;
        $b = $h2 * 60 + $m2;
        if ($b <= $a) {
            $b += 1440;
        }
        if ($b - $a >= 15 && $b - $a <= 1440) {
            $out[] = [$a, $b];
        }
    }
    return $out;
}

/** Who the lead meets, as one line: "B. Vignesh, Founder & CEO". */
function booking_host_line(array $cfg): string
{
    return $cfg['host_name'] . ($cfg['host_title'] !== '' ? ', ' . $cfg['host_title'] : '');
}

/** "B. Vignesh from VTurnU <connect@vturnu.com>": booking emails come from the host at the shared address. */
function booking_from(array $cfg): string
{
    return str_replace(['"', '<', '>', "\r", "\n"], '', (string) $cfg['host_name']) . ' from VTurnU <' . CONTACT_EMAIL . '>';
}

/* ------------------------------------------------------------------ */
/* Time zones                                                          */
/* ------------------------------------------------------------------ */

/** A time zone name we can use, or ''. Accepts the old aliases browsers still report (Asia/Calcutta). */
function booking_zone_name(string $tz): string
{
    $tz = trim($tz);
    if ($tz === 'UTC' || $tz === 'Etc/UTC') {
        return 'UTC';
    }
    if (!preg_match('#^[A-Za-z]+(?:/[A-Za-z0-9_+\-]+){1,2}$#', $tz)) {
        return '';
    }
    try {
        new DateTimeZone($tz);
        return $tz;
    } catch (Throwable $e) {
        return '';
    }
}

function booking_zone(string $tz): DateTimeZone
{
    $name = booking_zone_name($tz);
    return new DateTimeZone($name !== '' ? $name : BOOKING_HOST_TZ);
}

function booking_is_india(string $tz): bool
{
    return in_array($tz, BOOKING_INDIA_TZ, true);
}

/** The zone the edge thinks the visitor is in (Vercel's geo-IP), or ''. */
function booking_ip_zone(): string
{
    return booking_zone_name((string) ($_SERVER['HTTP_X_VERCEL_IP_TIMEZONE'] ?? ''));
}

/** "Chicago" from America/Chicago, "Ho Chi Minh" from Asia/Ho_Chi_Minh. */
function booking_zone_city(string $tz): string
{
    if (booking_is_india($tz)) {
        return 'India';
    }
    $parts = explode('/', $tz);
    return str_replace('_', ' ', (string) end($parts));
}

/** "GMT-6" or "GMT+5:30" for a zone at a moment. */
function booking_gmt(string $tz, int $at): string
{
    $off = booking_zone($tz)->getOffset(new DateTimeImmutable('@' . $at));
    if ($off === 0) {
        return 'GMT';
    }
    $sign = $off < 0 ? '-' : '+';
    $off = abs($off);
    return 'GMT' . $sign . intdiv($off, 3600) . ($off % 3600 ? ':' . str_pad((string) intdiv($off % 3600, 60), 2, '0', STR_PAD_LEFT) : '');
}

/** "Chicago time (GMT-6)": how a zone is named in emails. */
function booking_zone_label(string $tz, int $at): string
{
    return booking_is_india($tz) ? 'India time (IST)' : booking_zone_city($tz) . ' time (' . booking_gmt($tz, $at) . ')';
}

/** A moment in a zone: format 'day' = "Tuesday, 11 November 2026", 'time' = "9:00 AM", 'short' = "Tue 11 Nov, 9:00 AM". */
function booking_fmt(int $ts, string $tz, string $what): string
{
    $d = (new DateTimeImmutable('@' . $ts))->setTimezone(booking_zone($tz));
    return match ($what) {
        'day' => $d->format('l, j F Y'),
        'time' => $d->format('g:i A'),
        'date' => $d->format('Y-m-d'),
        'short' => $d->format('D j M, g:i A'),
        default => $d->format('D j M Y, g:i A'),
    };
}

/** "Tue 11 Nov 2026, 8:30 PM IST": the host's view, for the CRM and the owner's emails. */
function booking_ist(int $ts): string
{
    return booking_fmt($ts, BOOKING_HOST_TZ, 'full') . ' IST';
}

/* ------------------------------------------------------------------ */
/* Availability                                                        */
/* ------------------------------------------------------------------ */

/**
 * Every free start time between the minimum notice and the end of the booking
 * window. A time is free when the call plus the buffer on both sides touches
 * no busy block. The second value is 1 for a time from the overseas hours,
 * which only visitors outside India are offered.
 *
 * @param list<array{0:int,1:int}> $busy busy blocks as unix [start, end)
 * @return list<array{0:int,1:int}>
 */
function booking_slots(array $cfg, int $now, array $busy): array
{
    $len = (int) $cfg['length'] * 60;
    $buf = (int) $cfg['buffer'] * 60;
    $from = $now + (int) $cfg['notice_hours'] * 3600;
    $until = $now + (int) $cfg['window_days'] * 86400;
    $tz = new DateTimeZone(BOOKING_HOST_TZ);
    $windows = [];
    foreach (booking_ranges((string) $cfg['core']) as [$a, $b]) {
        $windows[] = [$a, $b, 0];
    }
    foreach (booking_ranges((string) $cfg['overseas']) as [$a, $b]) {
        $windows[] = [$a, $b, 1];
    }
    $days = array_map('intval', (array) $cfg['days']);
    $out = [];
    /* Start a day early: a window that runs past midnight spills into today. */
    $day = (new DateTimeImmutable('@' . $now))->setTimezone($tz)->setTime(0, 0)->modify('-1 day');
    $last = (new DateTimeImmutable('@' . $until))->setTimezone($tz)->setTime(0, 0);
    for (; $day <= $last; $day = $day->modify('+1 day')) {
        if (!in_array((int) $day->format('N'), $days, true)) {
            continue;
        }
        foreach ($windows as [$a, $b, $overseas]) {
            for ($m = $a; $m + (int) $cfg['length'] <= $b; $m += (int) $cfg['length']) {
                $t = $day->modify('+' . $m . ' minutes')->getTimestamp();
                if ($t < $from || $t >= $until) {
                    continue;
                }
                $free = true;
                foreach ($busy as [$bs, $be]) {
                    if ($t - $buf < $be && $t + $len + $buf > $bs) {
                        $free = false;
                        break;
                    }
                }
                if ($free && (!isset($out[$t]) || $out[$t] === 1)) {
                    $out[$t] = $overseas;
                }
            }
        }
    }
    ksort($out);
    $list = [];
    foreach ($out as $t => $o) {
        $list[] = [(int) $t, $o];
    }
    return $list;
}

/** Whether a time from booking_slots() is offered to someone in this zone. */
function booking_slot_for(array $slot, string $tz): bool
{
    return $slot[1] === 0 || !booking_is_india($tz);
}

/** Within the visitor's usual day (7 AM to 9 PM their time)? Other times show on request. */
function booking_daytime(int $ts, int $len, string $tz): bool
{
    $z = booking_zone($tz);
    $s = (new DateTimeImmutable('@' . $ts))->setTimezone($z);
    $e = (new DateTimeImmutable('@' . ($ts + $len * 60)))->setTimezone($z);
    $sm = (int) $s->format('G') * 60 + (int) $s->format('i');
    $em = (int) $e->format('G') * 60 + (int) $e->format('i');
    return $sm >= BOOKING_DAY_HOURS[0] * 60 && $em <= BOOKING_DAY_HOURS[1] * 60 && $em > $sm;
}

/** Remove one interval from busy blocks (a call being moved should not block itself). */
function booking_subtract(array $busy, int $s, int $e): array
{
    $out = [];
    foreach ($busy as [$bs, $be]) {
        if ($be <= $s || $bs >= $e) {
            $out[] = [$bs, $be];
            continue;
        }
        if ($bs < $s) {
            $out[] = [$bs, $s];
        }
        if ($be > $e) {
            $out[] = [$e, $be];
        }
    }
    return $out;
}

/** Calls already booked through the site, as busy blocks. */
function booking_db_busy(int $from, int $to, int $exceptId = 0): array
{
    $st = db()->prepare("SELECT id, extract(epoch from start_at)::bigint AS s, extract(epoch from end_at)::bigint AS e FROM meetings
                          WHERE status = 'booked' AND end_at > to_timestamp(?) AND start_at < to_timestamp(?) AND id <> ?");
    $st->execute([$from, $to, $exceptId]);
    $out = [];
    foreach ($st->fetchAll() as $r) {
        $out[] = [(int) $r['s'], (int) $r['e']];
    }
    return $out;
}

/**
 * What can be booked right now: the free times plus what the page needs to
 * draw them. Throws when Google cannot be asked, because guessing would offer
 * times that are not free.
 *
 * @return array{slots:list<array{0:int,1:int}>,now:int}
 */
function booking_availability(array $cfg, ?array $moving = null): array
{
    $now = time();
    $to = $now + (int) $cfg['window_days'] * 86400 + 86400;
    $busy = gcal_freebusy($now, $to);
    if ($moving && !empty($moving['start'])) {
        $busy = booking_subtract($busy, (int) $moving['start'], (int) $moving['end']);
    }
    $busy = array_merge($busy, booking_db_busy($now, $to, (int) ($moving['id'] ?? 0)));
    return ['slots' => booking_slots($cfg, $now, $busy), 'now' => $now];
}

/* ------------------------------------------------------------------ */
/* Google Calendar                                                     */
/* ------------------------------------------------------------------ */

function gcal_configured(): bool
{
    return env_var('GOOGLE_CLIENT_ID', '') !== '' && env_var('GOOGLE_CLIENT_SECRET', '') !== '' && env_var('GOOGLE_REFRESH_TOKEN', '') !== '';
}

/**
 * One HTTPS call to Google. Tests answer through $GLOBALS['GCAL_FAKE'] instead
 * of the network.
 *
 * @return array{0:int,1:array}
 */
function gcal_http(string $method, string $url, ?array $json = null, ?array $form = null, string $bearer = ''): array
{
    if (isset($GLOBALS['GCAL_FAKE']) && is_callable($GLOBALS['GCAL_FAKE'])) {
        return ($GLOBALS['GCAL_FAKE'])($method, $url, $json ?? $form);
    }
    $headers = ['Accept: application/json'];
    if ($bearer !== '') {
        $headers[] = 'Authorization: Bearer ' . $bearer;
    }
    $opts = [CURLOPT_RETURNTRANSFER => true, CURLOPT_CUSTOMREQUEST => $method, CURLOPT_TIMEOUT => 12, CURLOPT_CONNECTTIMEOUT => 5];
    if ($json !== null) {
        $headers[] = 'Content-Type: application/json';
        $opts[CURLOPT_POSTFIELDS] = json_encode($json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } elseif ($form !== null) {
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        $opts[CURLOPT_POSTFIELDS] = http_build_query($form);
    }
    $opts[CURLOPT_HTTPHEADER] = $headers;
    $ch = curl_init($url);
    curl_setopt_array($ch, $opts);
    $res = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    if ($res === false || $err !== '') {
        error_log('gcal_http ' . $method . ' ' . strtok($url, '?') . ': ' . $err);
        return [0, ['error' => ['message' => $err !== '' ? $err : 'no response']]];
    }
    $data = $res === '' ? [] : json_decode((string) $res, true);
    return [$code, is_array($data) ? $data : []];
}

/** What Google said went wrong, in one line. */
function gcal_error(array $data): string
{
    $e = $data['error'] ?? null;
    if (is_array($e)) {
        return (string) ($e['message'] ?? 'unknown error');
    }
    return (string) ($data['error_description'] ?? $e ?? 'unknown error');
}

/** A short-lived access token for connect@vturnu.com, from the refresh token. */
function gcal_token(): string
{
    static $token = null;
    static $until = 0;
    if ($token !== null && time() < $until) {
        return $token;
    }
    if (!gcal_configured()) {
        throw new RuntimeException('Google Calendar is not connected (GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET and GOOGLE_REFRESH_TOKEN are not all set).');
    }
    [$code, $data] = gcal_http('POST', 'https://oauth2.googleapis.com/token', null, [
        'client_id' => (string) env_var('GOOGLE_CLIENT_ID', ''),
        'client_secret' => (string) env_var('GOOGLE_CLIENT_SECRET', ''),
        'refresh_token' => (string) env_var('GOOGLE_REFRESH_TOKEN', ''),
        'grant_type' => 'refresh_token',
    ]);
    if ($code !== 200 || empty($data['access_token'])) {
        throw new RuntimeException('Google sign-in failed (HTTP ' . $code . '): ' . gcal_error($data));
    }
    $token = (string) $data['access_token'];
    $until = time() + max(60, (int) ($data['expires_in'] ?? 3600) - 120);
    return $token;
}

/** @return array{0:int,1:array} */
function gcal_api(string $method, string $path, array $query = [], ?array $body = null): array
{
    $url = 'https://www.googleapis.com/calendar/v3/' . $path . ($query ? '?' . http_build_query($query) : '');
    return gcal_http($method, $url, $body, null, gcal_token());
}

/** "2026-11-10T14:30:00Z". */
function gcal_utc(int $ts): string
{
    return gmdate('Y-m-d\TH:i:s\Z', $ts);
}

/**
 * Busy blocks on the connect@ calendar between two moments, as Google itself
 * counts them (events shown as free, declined invitations and working
 * locations do not count).
 *
 * @return list<array{0:int,1:int}>
 */
function gcal_freebusy(int $from, int $to): array
{
    [$code, $data] = gcal_api('POST', 'freeBusy', [], [
        'timeMin' => gcal_utc($from), 'timeMax' => gcal_utc($to), 'items' => [['id' => 'primary']],
    ]);
    $cal = $data['calendars']['primary'] ?? null;
    if ($code !== 200 || !is_array($cal) || !empty($cal['errors'])) {
        throw new RuntimeException('Google free/busy failed (HTTP ' . $code . '): ' . ($code === 200 ? json_encode($cal['errors'] ?? 'no calendar') : gcal_error($data)));
    }
    $out = [];
    foreach ((array) ($cal['busy'] ?? []) as $b) {
        $s = strtotime((string) ($b['start'] ?? ''));
        $e = strtotime((string) ($b['end'] ?? ''));
        if ($s !== false && $e !== false && $e > $s) {
            $out[] = [$s, $e];
        }
    }
    return $out;
}

/** Whether Google counts the calendar busy at any moment in [from, to). */
function gcal_is_busy(int $from, int $to): bool
{
    foreach (gcal_freebusy($from, $to) as [$s, $e]) {
        if ($s < $to && $e > $from) {
            return true;
        }
    }
    return false;
}

/**
 * Whether anything other than one event (the call being moved) makes the
 * calendar busy between two moments. Reads the events themselves, so the call
 * being moved can be left out, which free/busy cannot do.
 */
function gcal_busy_except(int $from, int $to, string $exceptId): bool
{
    [$code, $data] = gcal_api('GET', 'calendars/primary/events', [
        'timeMin' => gcal_utc($from), 'timeMax' => gcal_utc($to), 'singleEvents' => 'true', 'maxResults' => 100,
    ]);
    if ($code !== 200) {
        throw new RuntimeException('Google could not list events (HTTP ' . $code . '): ' . gcal_error($data));
    }
    $calTz = booking_zone((string) ($data['timeZone'] ?? BOOKING_HOST_TZ));
    foreach ((array) ($data['items'] ?? []) as $ev) {
        if (($ev['id'] ?? '') === $exceptId || ($ev['status'] ?? '') === 'cancelled'
            || ($ev['transparency'] ?? 'opaque') === 'transparent' || ($ev['eventType'] ?? '') === 'workingLocation') {
            continue;
        }
        foreach ((array) ($ev['attendees'] ?? []) as $a) {
            if (!empty($a['self']) && ($a['responseStatus'] ?? '') === 'declined') {
                continue 2;
            }
        }
        if (isset($ev['start']['date'])) {
            $s = (new DateTimeImmutable((string) $ev['start']['date'], $calTz))->getTimestamp();
            $e = (new DateTimeImmutable((string) ($ev['end']['date'] ?? $ev['start']['date']), $calTz))->getTimestamp();
        } else {
            $s = (int) strtotime((string) ($ev['start']['dateTime'] ?? ''));
            $e = (int) strtotime((string) ($ev['end']['dateTime'] ?? ''));
        }
        if ($s < $to && $e > $from) {
            return true;
        }
    }
    return false;
}

/** The Meet link on an event, or ''. */
function gcal_meet_url(array $ev): string
{
    if (!empty($ev['hangoutLink'])) {
        return (string) $ev['hangoutLink'];
    }
    foreach ((array) ($ev['conferenceData']['entryPoints'] ?? []) as $ep) {
        if (($ep['entryPointType'] ?? '') === 'video' && !empty($ep['uri'])) {
            return (string) $ep['uri'];
        }
    }
    return '';
}

function gcal_get(string $eventId): ?array
{
    [$code, $ev] = gcal_api('GET', 'calendars/primary/events/' . rawurlencode($eventId));
    if ($code === 404 || $code === 410) {
        return ['id' => $eventId, 'status' => 'cancelled'];
    }
    return $code === 200 ? $ev : null;
}

/** Meet links are usually ready at once; when Google says "pending", ask again briefly. */
function gcal_wait_for_meet(array $ev): array
{
    for ($i = 0; $i < 4 && gcal_meet_url($ev) === '' && ($ev['conferenceData']['createRequest']['status']['statusCode'] ?? '') === 'pending'; $i++) {
        usleep(600000);
        $again = gcal_get((string) $ev['id']);
        if ($again) {
            $ev = $again;
        }
    }
    return $ev;
}

/** The start and end of an event as the API wants them, in the lead's zone so Google's invitation shows their time. */
function gcal_when(int $start, int $end, string $tz): array
{
    $z = booking_zone($tz);
    $f = static fn(int $t): string => (new DateTimeImmutable('@' . $t))->setTimezone($z)->format('Y-m-d\TH:i:sP');
    return ['start' => ['dateTime' => $f($start), 'timeZone' => $z->getName()], 'end' => ['dateTime' => $f($end), 'timeZone' => $z->getName()]];
}

/** Create the call on the connect@ calendar with a Meet link, inviting the lead (Google emails them). */
function gcal_insert(array $m, array $cfg): array
{
    $body = gcal_when((int) $m['start'], (int) $m['end'], (string) $m['time_zone']) + [
        'summary' => booking_event_title($m),
        'description' => booking_event_description($m, $cfg),
        'attendees' => [['email' => (string) $m['email'], 'displayName' => (string) $m['name']]],
        'guestsCanModify' => false,
        'guestsCanInviteOthers' => true,
        'reminders' => ['useDefault' => true],
        'conferenceData' => ['createRequest' => ['requestId' => substr((string) $m['token'], 0, 16) . '-' . bin2hex(random_bytes(4)), 'conferenceSolutionKey' => ['type' => 'hangoutsMeet']]],
        'extendedProperties' => ['private' => ['vturnuMeeting' => (string) $m['token'], 'vturnuLead' => (string) ($m['enquiry_id'] ?? '')]],
    ];
    [$code, $ev] = gcal_api('POST', 'calendars/primary/events', ['conferenceDataVersion' => 1, 'sendUpdates' => 'all'], $body);
    if ($code !== 200 || empty($ev['id'])) {
        throw new RuntimeException('Google could not create the event (HTTP ' . $code . '): ' . gcal_error($ev));
    }
    return gcal_wait_for_meet($ev);
}

/** Move the call; the Meet link stays the same and Google tells the lead. */
function gcal_move(string $eventId, int $start, int $end, string $tz): array
{
    [$code, $ev] = gcal_api('PATCH', 'calendars/primary/events/' . rawurlencode($eventId), ['conferenceDataVersion' => 1, 'sendUpdates' => 'all'], gcal_when($start, $end, $tz));
    if ($code !== 200 || empty($ev['id'])) {
        throw new RuntimeException('Google could not move the event (HTTP ' . $code . '): ' . gcal_error($ev));
    }
    return $ev;
}

/** Remove the call; Google sends the lead a cancellation. Already gone counts as done. */
function gcal_cancel(string $eventId, bool $notify = true): bool
{
    [$code, $data] = gcal_api('DELETE', 'calendars/primary/events/' . rawurlencode($eventId), ['sendUpdates' => $notify ? 'all' : 'none']);
    if (!in_array($code, [200, 204, 404, 410], true)) {
        error_log('gcal_cancel ' . $eventId . ': HTTP ' . $code . ' ' . gcal_error($data));
        return false;
    }
    return true;
}

/** "Cocopat x VTurnU: 30-minute call". */
function booking_event_title(array $m): string
{
    $who = trim((string) ($m['company'] ?? '')) !== '' ? (string) $m['company'] : (string) $m['name'];
    return crm_clean_line($who, 60) . ' x VTurnU: ' . (int) ((((int) $m['end']) - ((int) $m['start'])) / 60) . '-minute call';
}

/** The event's notes. Guests see them, so nothing internal goes here. */
function booking_event_description(array $m, array $cfg): string
{
    $L = ['A ' . (int) ((((int) $m['end']) - ((int) $m['start'])) / 60) . '-minute video call with ' . booking_host_line($cfg) . ' of VTurnU.', ''];
    $agenda = booking_agenda($m);
    if ($agenda) {
        $L[] = 'What we will cover:';
        foreach ($agenda as $a) {
            $L[] = '- ' . $a;
        }
        $L[] = '';
    }
    if (trim((string) ($m['notes'] ?? '')) !== '') {
        $L[] = 'You mentioned: ' . crm_clean_line((string) $m['notes'], 600);
        $L[] = '';
    }
    $L[] = 'Join with the Google Meet link in this invitation.';
    $L[] = 'Need another time? Reschedule or cancel here: ' . booking_url((string) $m['token']);
    $L[] = '';
    $L[] = 'Questions before the call: ' . CONTACT_EMAIL . ' or WhatsApp ' . CONTACT_PHONE . '.';
    return crm_no_dashes(implode("\n", $L));
}

/** Three plain points for the call, from what the lead asked about. */
function booking_agenda(array $m): array
{
    $topic = trim((string) ($m['topic'] ?? ''));
    return [
        $topic !== '' ? 'Your goals for ' . $topic . ', and what a good result looks like for you' : 'Your goals, and what a good result looks like for you',
        'What you have tried so far and what is working today',
        'Whether we are a good fit, and the next steps if we are',
    ];
}

/* ------------------------------------------------------------------ */
/* Meetings in the database                                            */
/* ------------------------------------------------------------------ */

/** The meetings table, created on first use (the same statements as db/schema.sql), so a deploy needs no manual step. */
function booking_ready(): bool
{
    static $ok = null;
    if ($ok !== null) {
        return $ok;
    }
    try {
        $row = db()->query("SELECT to_regclass('public.meetings') AS t")->fetch();
        if (empty($row['t'])) {
            booking_migrate();
        }
        return $ok = true;
    } catch (Throwable $e) {
        error_log('booking_ready: ' . $e->getMessage());
        return $ok = false;
    }
}

function booking_migrate(): void
{
    $sql = (string) file_get_contents(__DIR__ . '/booking-schema.sql');
    if ($sql === '') {
        throw new RuntimeException('includes/booking-schema.sql is missing');
    }
    db()->exec($sql);
}

/** A row with times as unix seconds and the JSON columns decoded. */
function booking_row(array $r): array
{
    $r['id'] = (int) $r['id'];
    $r['enquiry_id'] = isset($r['enquiry_id']) && $r['enquiry_id'] !== null ? (int) $r['enquiry_id'] : 0;
    $r['start'] = !empty($r['start_at']) ? (int) strtotime((string) $r['start_at']) : 0;
    $r['end'] = !empty($r['end_at']) ? (int) strtotime((string) $r['end_at']) : 0;
    $r['reminder_ids'] = is_array($r['reminder_ids'] ?? null) ? $r['reminder_ids'] : (json_decode((string) ($r['reminder_ids'] ?? '[]'), true) ?: []);
    foreach (['name', 'email', 'phone', 'company', 'notes', 'time_zone', 'event_id', 'ical_uid', 'event_link', 'meet_url', 'rsvp', 'cancel_reason', 'cancelled_by', 'request_subject', 'status', 'token'] as $k) {
        $r[$k] = (string) ($r[$k] ?? '');
    }
    $r['sequence'] = (int) ($r['sequence'] ?? 0);
    $r['reschedules'] = (int) ($r['reschedules'] ?? 0);
    return $r;
}

function booking_by_token(string $token): ?array
{
    if (!preg_match('/^[a-f0-9]{32}$/', $token)) {
        return null;
    }
    $st = db()->prepare('SELECT * FROM meetings WHERE token = ?');
    $st->execute([$token]);
    $r = $st->fetch();
    return $r ? booking_row($r) : null;
}

/** Every meeting for a lead, newest first. */
function booking_for_lead(int $leadId): array
{
    try {
        if (!booking_ready()) {
            return [];
        }
        $st = db()->prepare('SELECT * FROM meetings WHERE enquiry_id = ? ORDER BY created_at DESC');
        $st->execute([$leadId]);
        return array_map('booking_row', $st->fetchAll());
    } catch (Throwable $e) {
        error_log('booking_for_lead: ' . $e->getMessage());
        return [];
    }
}

/** Booked calls from now on, soonest first, with the lead's name and company. */
function booking_upcoming(int $days = 30, int $limit = 50): array
{
    try {
        if (!booking_ready()) {
            return [];
        }
        $st = db()->prepare("SELECT * FROM meetings WHERE status = 'booked' AND end_at > now() AND start_at < now() + make_interval(days => ?) ORDER BY start_at LIMIT " . max(1, $limit));
        $st->execute([$days]);
        return array_map('booking_row', $st->fetchAll());
    } catch (Throwable $e) {
        error_log('booking_upcoming: ' . $e->getMessage());
        return [];
    }
}

/** The latest meetings of every kind, for the admin's Meetings list. */
function booking_recent(int $limit = 100): array
{
    try {
        if (!booking_ready()) {
            return [];
        }
        return array_map('booking_row', db()->query('SELECT * FROM meetings ORDER BY coalesce(start_at, created_at) DESC LIMIT ' . max(1, $limit))->fetchAll());
    } catch (Throwable $e) {
        error_log('booking_recent: ' . $e->getMessage());
        return [];
    }
}

/** Where a meeting stands, as the CRM shows it. */
function booking_state(array $m): string
{
    if ($m['status'] === 'booked') {
        if ($m['end'] > 0 && $m['end'] < time()) {
            return 'done';
        }
        return $m['reschedules'] > 0 ? 'rescheduled' : 'booked';
    }
    return $m['status'];
}

function booking_url(string $token = ''): string
{
    return (defined('SITE_URL') ? SITE_URL : 'https://vturnu.com') . '/' . BOOKING_PATH . '/' . ($token !== '' ? $token . '/' : '');
}

/** A short, safe text field from a form. */
function booking_field(array $in, string $key, int $max): string
{
    return crm_clean_line(crm_no_dashes((string) ($in[$key] ?? '')), $max);
}

/* ------------------------------------------------------------------ */
/* The CRM side: a personal meeting request                             */
/* ------------------------------------------------------------------ */

/** What the lead asked about, as a phrase for emails: their words beat a mismatched drop-down. */
function booking_topic(array $lead): string
{
    $f = crm_lead_facts($lead);
    $words = strtolower($f['note']);
    $picked = [];
    $map = [
        '/google ads|adwords|\bppc\b|search ads/' => 'Google Ads', '/meta ads|facebook ads|instagram ads/' => 'Meta Ads',
        '/linkedin ads/' => 'LinkedIn Ads', '/\bseo\b|search engine/' => 'SEO', '/chat ?gpt|perplexity|ai search|\baeo\b|\bgeo\b/' => 'AI search',
        '/website|web site|redesign/' => 'your website', '/social media/' => 'social media', '/\bbranding\b|\brebrand|\blogo\b|brand identity/' => 'branding',
        '/odoo/' => 'Odoo', '/erpnext/' => 'ERPNext', '/salesforce/' => 'Salesforce', '/sap cpq|\bcpq\b/' => 'SAP CPQ',
    ];
    foreach ($map as $re => $label) {
        if (preg_match($re, $words) && !in_array($label, $picked, true)) {
            $picked[] = $label;
        }
    }
    if ($picked) {
        return crm_list(array_slice($picked, 0, 3));
    }
    $svc = trim((string) preg_replace('/,.*$/', '', $f['service']));
    return in_array($svc, ['', 'I need advice on where to start', 'Full growth program', 'Newsletter signup'], true) ? '' : crm_service_phrase($f, crm_intent($f));
}

/**
 * The first draft of the meeting request: short, warm, about them, with the
 * booking link as the one thing to do. No proposal and no prices: the call is
 * where the requirements get understood.
 *
 * @return array{subject:string,body:string}
 */
function booking_request_draft(array $lead, ?array $cfg = null): array
{
    $cfg ??= booking_settings();
    $f = crm_lead_facts($lead);
    $topic = booking_topic($lead);
    $for = $f['company'] !== '' ? ' for ' . $f['company'] : '';
    $L = [
        'Hi ' . $f['first'] . ',',
        '',
        'Thank you for reaching out' . ($topic !== '' ? ' about ' . $topic : '') . $for . '. Before we suggest anything, I would like to understand what you are aiming for: who your customers are, what is working today and what a good result would look like for you.',
        '',
        'Could we have a 30-minute video call? Pick any time that suits you below. The invitation and a Google Meet link go straight to your calendar, and the times are shown in your own time zone.',
        '',
        'If there is anything else you would like help with, just mention it when you book and I will come prepared.',
        '',
        'Looking forward to speaking with you.',
    ];
    return [
        'subject' => crm_no_dashes($f['first'] !== 'there' ? $f['first'] . ', a quick call' . ($topic !== '' ? ' about ' . $topic : '') . '?' : 'A quick call about your enquiry?'),
        'body' => crm_no_dashes(implode("\n", $L)),
    ];
}

/**
 * Email a lead their personal booking link and record it. A new lead moves
 * to Contacted, because this is their first reply.
 *
 * @param array{copy?:bool,by?:string,stage?:bool} $o
 * @return array{ok:bool,error:string,token:string}
 */
function booking_send_request(array $lead, string $subject, string $body, array $o = []): array
{
    $fail = static fn(string $e): array => ['ok' => false, 'error' => $e, 'token' => ''];
    if (!booking_ready()) {
        return $fail('The meetings table could not be set up. Check the database connection.');
    }
    $cfg = booking_settings();
    $subject = crm_clean_line(crm_no_dashes($subject), 200);
    $body = trim(crm_no_dashes(str_replace("\r\n", "\n", $body)));
    $to = strtolower(trim((string) ($lead['email'] ?? '')));
    if ($subject === '') {
        return $fail('Add a subject line.');
    }
    if (mb_strlen($body) < 40) {
        return $fail('The message is too short to send.');
    }
    if (mb_strlen($body) > 8000) {
        return $fail('The message is too long. Please keep it under 8,000 characters.');
    }
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return $fail('This lead has no valid email address.');
    }
    $f = crm_lead_facts($lead);
    $token = bin2hex(random_bytes(16));
    $crm = crm_settings();
    $link = booking_url($token);
    $payload = [
        'from' => booking_from($cfg),
        'to' => [$to],
        'subject' => $subject,
        'html' => booking_letter_html($subject, $body, $crm, $f['country'], ['label' => 'Pick a time for our call', 'url' => $link,
            'note' => '30 minutes on Google Meet with ' . booking_host_line($cfg) . '. Times are shown in your time zone.'], SITE_URL . '/p/m/' . $token . '.gif'),
        'text' => crm_markup_text($body) . "\n\nPick a time: " . $link . "\n\n" . crm_signature_text($crm, $f['country']),
        'reply_to' => CONTACT_EMAIL,
    ];
    if (!empty($o['copy'])) {
        $payload['bcc'] = [ENQUIRY_TO];
    }
    $sent = resend_send($payload);
    if (!$sent['ok']) {
        return $fail('The email could not be sent: ' . $sent['error']);
    }
    $leadId = (int) $lead['id'];
    try {
        db()->prepare("INSERT INTO meetings (enquiry_id, token, status, name, email, phone, company, request_subject, request_body, requested_by, requested_at, request_mail_id)
                       VALUES (?, ?, 'requested', ?, ?, ?, ?, ?, ?, ?, now(), ?)")
            ->execute([$leadId, $token, $f['name'], $to, (string) ($lead['mobile'] ?? ''), $f['company'], $subject, $body, (string) ($o['by'] ?? ''), $sent['id']]);
        $sets = ['contacted_at = coalesce(contacted_at, now())'];
        $moved = !empty($o['stage']) && (string) ($lead['status'] ?? '') === 'new';
        if ($moved) {
            $sets[] = "status = 'contacted'";
        }
        db()->prepare('UPDATE enquiries SET ' . implode(', ', $sets) . ' WHERE id = ?')->execute([$leadId]);
        if ($moved) {
            crm_log($leadId, 'Status: New to Contacted');
        }
    } catch (Throwable $e) {
        error_log('booking_send_request: sent but not recorded for lead ' . $leadId . ': ' . $e->getMessage());
        return ['ok' => true, 'error' => 'Sent, but it could not be recorded: ' . $e->getMessage(), 'token' => $token];
    }
    crm_log($leadId, 'Meeting request sent: "' . $subject . '", booking link ' . $link);
    return ['ok' => true, 'error' => '', 'token' => $token];
}

/* ------------------------------------------------------------------ */
/* Booking, moving and cancelling                                      */
/* ------------------------------------------------------------------ */

/**
 * The lead behind a booking from the public page: the latest enquiry from the
 * same address, or a new one filed as a lead from the booking page.
 */
function booking_lead_for(array $in, string $email, string $name, string $phone, string $company, string $service, string $notes, string $verdict, array $screen): int
{
    $st = db()->prepare("SELECT id FROM enquiries WHERE lower(email) = ? AND deleted_at IS NULL AND status <> 'junk' ORDER BY created_at DESC LIMIT 1");
    $st->execute([$email]);
    $found = $st->fetch();
    if ($found) {
        return (int) $found['id'];
    }
    $attr = crm_attribution($in + ['from_page' => '/' . BOOKING_PATH . '/', 'lead_source' => 'booking']);
    $message = "Booked a call from the booking page\n\n" . ($notes !== '' ? $notes : 'No message.');
    $activity = json_encode([['date' => date('Y-m-d H:i'), 'text' => 'Quality check: ' . $verdict . ' (' . $screen['score'] . ')' . ($screen['reasons'] ? ': ' . implode('; ', $screen['reasons']) : '')]], JSON_UNESCAPED_UNICODE);
    $st = db()->prepare(
        "INSERT INTO enquiries (source, name, email, mobile, company, designation, service, budget, message, status, tags, activity,
                                channel, channel_group, attribution, form_page, country, grade, score)
         VALUES ('booking', ?, ?, ?, ?, '', ?, '', ?, 'new', ?, CAST(? AS jsonb), ?, ?, CAST(? AS jsonb), ?, ?, '', NULL)
         RETURNING id"
    );
    $st->execute([
        $name, $email, $phone, $company, $service, $message, $verdict === 'ok' ? '' : 'check-first', $activity,
        (string) $attr['channel'], (string) $attr['group'], json_encode($attr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), (string) $attr['form_page'], (string) $attr['country'],
    ]);
    return (int) $st->fetchColumn();
}

/**
 * Book a call, or move one. $m is the meeting behind a personal link (null on
 * the public page); $mode is 'book' or 'move'.
 *
 * @return array{ok:bool,error:string,token:string,taken:bool,errors:array<string,string>}
 */
function booking_book(?array $m, array $in, string $mode = 'book'): array
{
    $res = static fn(bool $ok, string $error, string $token = '', bool $taken = false, array $errors = []): array
        => ['ok' => $ok, 'error' => $error, 'token' => $token, 'taken' => $taken, 'errors' => $errors];
    $cfg = booking_settings();
    if (empty($cfg['enabled']) || !gcal_configured()) {
        return $res(false, 'Online booking is paused right now. Please email ' . CONTACT_EMAIL . ' or WhatsApp ' . CONTACT_PHONE . ' and we will set up a time.');
    }
    if (!booking_ready()) {
        return $res(false, 'Something went wrong on our side. Please try again in a minute.');
    }
    $moving = $mode === 'move';
    if ($moving && (!$m || $m['status'] !== 'booked')) {
        return $res(false, 'There is no booked call to move.');
    }
    /* A link whose call has taken place books a new call for the same lead; one still ahead can only be moved. */
    $knownLead = 0;
    if (!$moving && $m && $m['status'] === 'booked') {
        if ($m['end'] > time()) {
            return $res(false, 'This call is already booked. Use Reschedule to move it.');
        }
        $knownLead = $m['enquiry_id'];
        $m = null;
    }
    $tz = booking_zone_name((string) ($in['tz'] ?? '')) ?: BOOKING_HOST_TZ;
    $start = (int) ($in['slot'] ?? 0);
    $len = (int) $cfg['length'] * 60;
    $end = $start + $len;

    /* The person: kept from the link when moving, otherwise from the form. */
    $errors = [];
    if ($moving) {
        [$name, $email, $phone, $company, $notes] = [$m['name'], $m['email'], $m['phone'], $m['company'], $m['notes']];
    } else {
        $name = booking_field($in, 'name', 80);
        $email = strtolower(trim((string) ($in['email'] ?? '')));
        $phone = booking_field($in, 'phone', 30);
        $company = booking_field($in, 'company', 80);
        $notes = trim(mb_substr(crm_no_dashes(str_replace("\r\n", "\n", (string) ($in['notes'] ?? ''))), 0, 1500));
        if (mb_strlen($name) < 2) {
            $errors['name'] = 'Please enter your name.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 120) {
            $errors['email'] = 'Please enter a valid email address, so we can send the invitation.';
        }
        if ($phone !== '' && !preg_match('/^\+?[0-9 ()\-.]{6,24}$/', $phone)) {
            $errors['phone'] = 'Please check the phone number, or leave it empty.';
        }
    }
    $service = in_array((string) ($in['service'] ?? ''), lead_service_values(), true) ? (string) $in['service'] : '';
    if ($start <= 0) {
        $errors['slot'] = 'Please pick a time.';
    }
    if ($errors) {
        return $res(false, 'Please check the highlighted fields.', '', false, $errors);
    }

    /* Is the time still on offer to this visitor? */
    try {
        $avail = booking_availability($cfg, $moving ? $m : null);
    } catch (Throwable $e) {
        error_log('booking_book availability: ' . $e->getMessage());
        booking_alert_owner('Booking page could not read the calendar', $e->getMessage());
        return $res(false, 'We could not check the calendar just now. Please try again in a minute, or email ' . CONTACT_EMAIL . '.');
    }
    $offered = false;
    foreach ($avail['slots'] as $s) {
        if ($s[0] === $start && booking_slot_for($s, $tz)) {
            $offered = true;
            break;
        }
    }
    if (!$offered) {
        return $res(false, 'That time has just been taken. Please pick another one.', '', true);
    }

    /* The public page files the booking as a lead, after the same quality screen as the site's forms. */
    $screen = ['verdict' => 'ok', 'score' => 0, 'reasons' => []];
    if (!$m && $knownLead === 0) {
        $screen = lead_screen(['name' => $name, 'email' => $email, 'mobile' => $phone, 'company' => $company, 'message' => $notes, 'raw_message' => $notes], 'booking', true, [
            'captcha' => $GLOBALS['recaptcha_signal'] ?? null,
        ]);
        if ($screen['verdict'] === 'junk') {
            error_log('booking_book: refused as junk: ' . $email . ' ' . implode('; ', $screen['reasons']));
            return $res(false, 'We could not book that call. Please email ' . CONTACT_EMAIL . ' and we will set it up with you.');
        }
    }

    $pdo = db();
    $event = null;
    $buf = (int) $cfg['buffer'] * 60;
    $before = $m;
    try {
        $pdo->beginTransaction();
        $pdo->query('SELECT pg_advisory_xact_lock(' . BOOKING_LOCK . ')');
        /* Under the lock: nothing booked through the site in the way, and Google still says free. */
        if (booking_db_busy($start - $buf, $end + $buf, $moving ? (int) $m['id'] : 0)) {
            $pdo->rollBack();
            return $res(false, 'That time has just been taken. Please pick another one.', '', true);
        }
        $googleBusy = $moving ? gcal_busy_except($start - $buf, $end + $buf, $m['event_id']) : gcal_is_busy($start - $buf, $end + $buf);
        if ($googleBusy) {
            $pdo->rollBack();
            return $res(false, 'That time has just been taken. Please pick another one.', '', true);
        }

        $leadId = $m ? (int) $m['enquiry_id'] : ($knownLead > 0 ? $knownLead : booking_lead_for($in, $email, $name, $phone, $company, $service, $notes, $screen['verdict'], $screen));
        $token = $m ? $m['token'] : bin2hex(random_bytes(16));
        $topic = '';
        if ($leadId > 0 && ($lead = crm_lead($leadId))) {
            $topic = booking_topic($lead);
        }
        if ($topic === '' && $service !== '') {
            $topic = crm_service_phrase(['service' => $service, 'note' => ''], ['no_website' => false, 'goals' => []]);
        }
        $row = ['token' => $token, 'enquiry_id' => $leadId, 'name' => $name, 'email' => $email, 'company' => $company, 'notes' => $notes,
            'start' => $start, 'end' => $end, 'time_zone' => $tz, 'topic' => $topic];

        if ($moving) {
            $event = gcal_move($m['event_id'], $start, $end, $tz);
            $pdo->prepare('UPDATE meetings SET start_at = to_timestamp(?), end_at = to_timestamp(?), time_zone = ?, sequence = ?, reschedules = reschedules + 1, rsvp = ?, updated_at = now() WHERE id = ?')
                ->execute([$start, $end, $tz, (int) ($event['sequence'] ?? $m['sequence'] + 1), '', $m['id']]);
        } else {
            $event = gcal_insert($row, $cfg);
            $vals = [$leadId ?: null, $name, $email, $phone, $company, $notes, $tz, $start, $end, (string) $event['id'], (string) ($event['iCalUID'] ?? ''),
                (string) ($event['htmlLink'] ?? ''), gcal_meet_url($event), (int) ($event['sequence'] ?? 0)];
            if ($m) {
                $pdo->prepare("UPDATE meetings SET enquiry_id = ?, name = ?, email = ?, phone = ?, company = ?, notes = ?, time_zone = ?, start_at = to_timestamp(?), end_at = to_timestamp(?),
                               event_id = ?, ical_uid = ?, event_link = ?, meet_url = ?, sequence = ?, status = 'booked', booked_at = now(), cancelled_at = NULL, cancelled_by = '', cancel_reason = '', rsvp = '', updated_at = now()
                               WHERE id = ?")->execute([...$vals, $m['id']]);
            } else {
                $pdo->prepare("INSERT INTO meetings (enquiry_id, name, email, phone, company, notes, time_zone, start_at, end_at, event_id, ical_uid, event_link, meet_url, sequence, status, booked_at, token)
                               VALUES (?, ?, ?, ?, ?, ?, ?, to_timestamp(?), to_timestamp(?), ?, ?, ?, ?, ?, 'booked', now(), ?)")->execute([...$vals, $token]);
            }
        }
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $overlap = $e instanceof PDOException && (string) $e->getCode() === '23P01';
        /* The event exists but the booking was not saved: take it back off the calendar. */
        if ($event && !$moving && !empty($event['id'])) {
            gcal_cancel((string) $event['id']);
        } elseif ($event && $moving) {
            try {
                gcal_move($m['event_id'], $before['start'], $before['end'], $before['time_zone']);
            } catch (Throwable $e2) {
                error_log('booking_book: could not move back ' . $m['event_id'] . ': ' . $e2->getMessage());
            }
        }
        error_log('booking_book: ' . $e->getMessage());
        if ($overlap) {
            return $res(false, 'That time has just been taken. Please pick another one.', '', true);
        }
        booking_alert_owner('A booking failed', $e->getMessage() . "\n\nWho: " . $name . ' <' . $email . '>' . "\nWhen: " . booking_ist($start));
        return $res(false, 'We could not finish the booking. Please try again, or email ' . CONTACT_EMAIL . ' and we will set it up with you.');
    }

    $saved = booking_by_token($token);
    if ($saved) {
        $saved['topic'] = $row['topic'];
        booking_after($saved, $moving ? 'moved' : 'booked', $before);
    }
    return $res(true, '', $token);
}

/**
 * Everything that follows a booking or a move: the lead's confirmation, the
 * owner's alert, reminders and the CRM timeline. Failures here are logged,
 * never shown, because the call itself is already on the calendar.
 */
function booking_after(array $m, string $kind, ?array $before = null): void
{
    $cfg = booking_settings();
    $when = booking_ist($m['start']) . ($m['time_zone'] !== '' && !booking_is_india($m['time_zone']) ? ' (' . booking_fmt($m['start'], $m['time_zone'], 'time') . ' ' . booking_zone_label($m['time_zone'], $m['start']) . ')' : '');
    try {
        booking_cancel_reminders($m);
        $ids = booking_schedule_reminders($m, $cfg);
        db()->prepare('UPDATE meetings SET reminder_ids = CAST(? AS jsonb) WHERE id = ?')->execute([json_encode($ids), $m['id']]);
        $m['reminder_ids'] = $ids;
    } catch (Throwable $e) {
        error_log('booking_after reminders: ' . $e->getMessage());
    }
    booking_mail_lead($m, $kind, $cfg);
    booking_mail_owner($m, $kind, $cfg, $before);
    if ($m['enquiry_id'] > 0) {
        $lid = $m['enquiry_id'];
        if ($kind === 'moved' && $before) {
            crm_log($lid, 'Rescheduled the meeting from ' . booking_ist($before['start']) . ' to ' . $when . '. Meet: ' . $m['meet_url']);
        } else {
            crm_log($lid, 'Booked a meeting: ' . $when . '. Meet: ' . $m['meet_url']);
        }
        try {
            $lead = crm_lead($lid);
            $tags = array_filter(array_map('trim', explode(',', (string) ($lead['tags'] ?? ''))));
            if (!in_array('meeting booked', $tags, true)) {
                $tags[] = 'meeting booked';
            }
            db()->prepare("UPDATE enquiries SET followup = ?, tags = ?, contacted_at = coalesce(contacted_at, now()), status = CASE WHEN status = 'new' THEN 'contacted' ELSE status END WHERE id = ?")
                ->execute([booking_fmt($m['start'], BOOKING_HOST_TZ, 'date'), implode(', ', $tags), $lid]);
            if ($lead && (string) $lead['status'] === 'new') {
                crm_log($lid, 'Status: New to Contacted');
            }
        } catch (Throwable $e) {
            error_log('booking_after lead: ' . $e->getMessage());
        }
    }
}

/**
 * Cancel a booked call. $by is 'lead' (from their link) or the admin user.
 *
 * @return array{ok:bool,error:string}
 */
function booking_cancel(array $m, string $reason, string $by = 'lead', bool $notifyLead = true): array
{
    if ($m['status'] !== 'booked') {
        return ['ok' => false, 'error' => 'This call is not booked, so there is nothing to cancel.'];
    }
    $reason = trim(mb_substr(crm_no_dashes(str_replace("\r\n", "\n", $reason)), 0, 1000));
    try {
        if ($m['event_id'] !== '' && !gcal_cancel($m['event_id'], $notifyLead)) {
            return ['ok' => false, 'error' => 'Google did not accept the cancellation. Please try again in a minute.'];
        }
        db()->prepare("UPDATE meetings SET status = 'cancelled', cancelled_at = now(), cancelled_by = ?, cancel_reason = ?, updated_at = now() WHERE id = ?")
            ->execute([$by, $reason, $m['id']]);
    } catch (Throwable $e) {
        error_log('booking_cancel: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'The call could not be cancelled just now. Please try again.'];
    }
    booking_cancel_reminders($m);
    $m['status'] = 'cancelled';
    $m['cancel_reason'] = $reason;
    $m['cancelled_by'] = $by;
    $cfg = booking_settings();
    if ($by === 'lead') {
        booking_mail_owner($m, 'cancelled', $cfg);
    }
    if ($notifyLead) {
        booking_mail_lead($m, 'cancelled', $cfg);
    }
    if ($m['enquiry_id'] > 0) {
        crm_log($m['enquiry_id'], ($by === 'lead' ? 'Cancelled the meeting' : 'Meeting cancelled by ' . $by) . ' (' . booking_ist($m['start']) . ')' . ($reason !== '' ? ': ' . crm_clean_line($reason, 200) : ''));
    }
    return ['ok' => true, 'error' => ''];
}

/**
 * Bring a booked call in line with the calendar: the owner may have moved or
 * deleted it in Google Calendar, and the lead may have accepted or declined
 * the invitation. Each change goes on the timeline once.
 */
function booking_sync(array $m): array
{
    if ($m['status'] !== 'booked' || $m['event_id'] === '' || !gcal_configured()) {
        return $m;
    }
    try {
        $ev = gcal_get($m['event_id']);
    } catch (Throwable $e) {
        error_log('booking_sync: ' . $e->getMessage());
        return $m;
    }
    if (!$ev) {
        return $m;
    }
    $lid = $m['enquiry_id'];
    try {
        if (($ev['status'] ?? '') === 'cancelled') {
            db()->prepare("UPDATE meetings SET status = 'cancelled', cancelled_at = now(), cancelled_by = 'calendar', updated_at = now() WHERE id = ?")->execute([$m['id']]);
            booking_cancel_reminders($m);
            if ($lid > 0) {
                crm_log($lid, 'Meeting removed from the Google Calendar (' . booking_ist($m['start']) . ')');
            }
            $m['status'] = 'cancelled';
            $m['cancelled_by'] = 'calendar';
            return $m;
        }
        $s = (int) strtotime((string) ($ev['start']['dateTime'] ?? ''));
        $e = (int) strtotime((string) ($ev['end']['dateTime'] ?? ''));
        if ($s > 0 && $e > $s && ($s !== $m['start'] || $e !== $m['end'])) {
            $from = $m['start'];
            db()->prepare('UPDATE meetings SET start_at = to_timestamp(?), end_at = to_timestamp(?), sequence = ?, updated_at = now() WHERE id = ?')
                ->execute([$s, $e, (int) ($ev['sequence'] ?? $m['sequence']), $m['id']]);
            $m['start'] = $s;
            $m['end'] = $e;
            booking_cancel_reminders($m);
            $ids = booking_schedule_reminders($m, booking_settings());
            db()->prepare('UPDATE meetings SET reminder_ids = CAST(? AS jsonb) WHERE id = ?')->execute([json_encode($ids), $m['id']]);
            $m['reminder_ids'] = $ids;
            if ($lid > 0) {
                crm_log($lid, 'Meeting moved in Google Calendar from ' . booking_ist($from) . ' to ' . booking_ist($s));
            }
        }
        $rsvp = '';
        foreach ((array) ($ev['attendees'] ?? []) as $a) {
            if (strcasecmp((string) ($a['email'] ?? ''), $m['email']) === 0) {
                $rsvp = (string) ($a['responseStatus'] ?? '');
            }
        }
        if ($rsvp !== '' && $rsvp !== $m['rsvp']) {
            db()->prepare('UPDATE meetings SET rsvp = ?, updated_at = now() WHERE id = ?')->execute([$rsvp, $m['id']]);
            $words = ['accepted' => 'Accepted the calendar invitation', 'declined' => 'Declined the calendar invitation', 'tentative' => 'Replied maybe to the calendar invitation'];
            if ($lid > 0 && isset($words[$rsvp])) {
                crm_log($lid, $words[$rsvp]);
            }
            $m['rsvp'] = $rsvp;
        }
        $meet = gcal_meet_url($ev);
        if ($meet !== '' && $meet !== $m['meet_url']) {
            db()->prepare('UPDATE meetings SET meet_url = ? WHERE id = ?')->execute([$meet, $m['id']]);
            $m['meet_url'] = $meet;
        }
    } catch (Throwable $e) {
        error_log('booking_sync update: ' . $e->getMessage());
    }
    return $m;
}

/** Lines for the morning digest: each call today and tomorrow (India time), checked with Google first. */
function booking_digest_lines(): array
{
    $out = [];
    $endOfTomorrow = (new DateTimeImmutable('tomorrow', new DateTimeZone(BOOKING_HOST_TZ)))->modify('+1 day')->getTimestamp();
    foreach (booking_upcoming(3, 20) as $m) {
        if ($m['start'] >= $endOfTomorrow) {
            continue;
        }
        $m = booking_sync($m);
        if ($m['status'] !== 'booked') {
            continue;
        }
        $out[] = '  * ' . booking_fmt($m['start'], BOOKING_HOST_TZ, 'short') . ' IST · ' . $m['name'] . ($m['company'] !== '' ? ', ' . $m['company'] : '')
            . ($m['rsvp'] === 'declined' ? ' · DECLINED the invitation' : ($m['rsvp'] === 'accepted' ? ' · accepted the invitation' : ''))
            . ($m['meet_url'] !== '' ? "\n    " . $m['meet_url'] : '')
            . ($m['enquiry_id'] > 0 ? "\n    " . SITE_URL . '/admin/?view=lead&id=' . $m['enquiry_id'] : '');
    }
    return $out;
}

/* ------------------------------------------------------------------ */
/* Calendar file                                                       */
/* ------------------------------------------------------------------ */

/** One iCalendar line: escaped, folded at 75 octets, CRLF. */
function booking_ics_line(string $name, string $value, bool $text = true): string
{
    if ($text) {
        $value = str_replace(['\\', ';', ',', "\r\n", "\n"], ['\\\\', '\\;', '\\,', '\\n', '\\n'], $value);
    }
    $line = $name . ':' . $value;
    $out = '';
    while (strlen($line) > 75) {
        $cut = 75;
        while ($cut > 0 && (ord($line[$cut]) & 0xC0) === 0x80) {
            $cut--;
        }
        $out .= substr($line, 0, $cut) . "\r\n";
        $line = ' ' . substr($line, $cut);
    }
    return $out . $line . "\r\n";
}

/**
 * The call as an .ics file. It carries the Google event's own UID, so a
 * calendar that already has the invitation updates it instead of adding a copy.
 */
function booking_ics(array $m, array $cfg): string
{
    $utc = static fn(int $t): string => gmdate('Ymd\THis\Z', $t);
    $desc = 'Video call with ' . booking_host_line($cfg) . ' of VTurnU.' . ($m['meet_url'] !== '' ? "\nJoin with Google Meet: " . $m['meet_url'] : '')
        . "\nReschedule or cancel: " . booking_url($m['token']);
    $ics = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//VTurnU//Book a Meeting//EN\r\nCALSCALE:GREGORIAN\r\nMETHOD:PUBLISH\r\nBEGIN:VEVENT\r\n";
    $ics .= booking_ics_line('UID', $m['ical_uid'] !== '' ? $m['ical_uid'] : $m['token'] . '@vturnu.com', false);
    $ics .= booking_ics_line('SEQUENCE', (string) $m['sequence'], false);
    $ics .= booking_ics_line('DTSTAMP', $utc(time()), false);
    $ics .= booking_ics_line('DTSTART', $utc($m['start']), false);
    $ics .= booking_ics_line('DTEND', $utc($m['end']), false);
    $ics .= booking_ics_line('SUMMARY', booking_event_title($m));
    $ics .= booking_ics_line('DESCRIPTION', crm_no_dashes($desc));
    if ($m['meet_url'] !== '') {
        $ics .= booking_ics_line('LOCATION', $m['meet_url']);
        $ics .= booking_ics_line('URL', $m['meet_url'], false);
    }
    $ics .= booking_ics_line('ORGANIZER;CN=VTurnU', 'mailto:' . CONTACT_EMAIL, false);
    $ics .= booking_ics_line('STATUS', $m['status'] === 'cancelled' ? 'CANCELLED' : 'CONFIRMED', false);
    $ics .= "BEGIN:VALARM\r\nACTION:DISPLAY\r\nDESCRIPTION:Call with VTurnU\r\nTRIGGER:-PT15M\r\nEND:VALARM\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n";
    return $ics;
}

/* ------------------------------------------------------------------ */
/* Emails                                                              */
/* ------------------------------------------------------------------ */

/**
 * A letter in the CRM's style: the text, an optional details card, one button
 * and the signature. Gmail-safe by the owner's rule (8 Nov 2026): every
 * coloured block uses bgcolor plus background-color, never the background
 * shorthand or a gradient, because Gmail drops those and leaves white text on white.
 *
 * @param array{label:string,url:string,note?:string}|null $cta
 */
function booking_letter_html(string $subject, string $markup, array $crm, string $country, ?array $cta = null, string $pixel = '', string $card = '', string $after = ''): string
{
    $font = 'Arial,Helvetica,sans-serif';
    $h = static fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    $button = $cta ? '<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:6px 0 10px;border-collapse:separate"><tr>'
        . '<td bgcolor="#4338CA" style="background-color:#4338CA;border-radius:999px;padding:0">'
        . '<a href="' . $h($cta['url']) . '" style="display:inline-block;padding:13px 26px;font:bold 15px/18px ' . $font . ';color:#ffffff;text-decoration:none;border-radius:999px">' . $h($cta['label']) . '</a>'
        . '</td></tr></table>'
        . (($cta['note'] ?? '') !== '' ? '<p style="margin:0 0 18px;font:13px/1.6 ' . $font . ';color:#6F6992">' . $h((string) $cta['note']) . '</p>' : '')
        : '';
    return '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . $h($subject) . '</title></head>'
        . '<body bgcolor="#ffffff" style="margin:0;padding:0;background-color:#ffffff">'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" bgcolor="#ffffff" style="background-color:#ffffff"><tr><td align="center" style="padding:20px 14px">'
        . '<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px"><tr><td style="text-align:left">'
        . crm_markup_html($markup) . $card . $button . ($after !== '' ? crm_markup_html($after) : '') . crm_signature_html($crm, $country)
        . '</td></tr></table></td></tr></table>'
        . ($pixel !== '' ? '<img src="' . $h($pixel) . '" width="1" height="1" alt="" style="display:block;width:1px;height:1px;border:0">' : '')
        . '</body></html>';
}

/** The when-and-where card in the lead's emails: their time first, India time under it, the Meet link. */
function booking_card_html(array $m, bool $struck = false): string
{
    $font = 'Arial,Helvetica,sans-serif';
    $h = static fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    $tz = $m['time_zone'] !== '' ? $m['time_zone'] : BOOKING_HOST_TZ;
    $deco = $struck ? 'text-decoration:line-through;' : '';
    $rows = '<div style="font:bold 12px/1.4 ' . $font . ';letter-spacing:.06em;text-transform:uppercase;color:#4338CA;margin:0 0 6px">' . $h(booking_fmt($m['start'], $tz, 'day')) . '</div>'
        . '<div style="' . $deco . 'font:bold 20px/1.35 ' . $font . ';color:#1D1936;margin:0 0 4px">' . $h(booking_fmt($m['start'], $tz, 'time') . ' to ' . booking_fmt($m['end'], $tz, 'time')) . '</div>'
        . '<div style="font:14px/1.5 ' . $font . ';color:#4A4470;margin:0 0 4px">' . $h(booking_zone_label($tz, $m['start'])) . '</div>'
        . (!booking_is_india($tz) ? '<div style="font:13px/1.5 ' . $font . ';color:#6F6992;margin:0 0 4px">' . $h(booking_fmt($m['start'], BOOKING_HOST_TZ, 'time') . ' in Chennai, India, where our team is') . '</div>' : '')
        . ($m['meet_url'] !== '' && !$struck ? '<div style="font:14px/1.5 ' . $font . ';color:#1D1936;margin:10px 0 0">Google Meet: <a href="' . $h($m['meet_url']) . '" style="color:#4338CA;font-weight:bold;text-decoration:underline">' . $h(preg_replace('#^https?://#', '', $m['meet_url'])) . '</a></div>' : '');
    return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:4px 0 16px;border-collapse:separate">'
        . '<tr><td bgcolor="#F5F4FE" style="background-color:#F5F4FE;border:1px solid #E5E3F5;border-radius:14px;padding:18px 20px">' . $rows . '</td></tr></table>';
}

/**
 * The lead's country for the signature (the GSTIN goes to Indian clients only):
 * the lead's own when known, otherwise India only if they booked in India's zone.
 */
function booking_country(array $m): string
{
    if ($m['enquiry_id'] > 0 && ($lead = crm_lead($m['enquiry_id']))) {
        $c = crm_lead_facts($lead)['country'];
        if ($c !== '') {
            return $c;
        }
    }
    return booking_is_india($m['time_zone']) ? 'IN' : 'ZZ';
}

/** The lead's own email for a booking, a move or a cancellation. */
function booking_mail_lead(array $m, string $kind, array $cfg): void
{
    try {
        $crm = crm_settings();
        $first = crm_lead_facts(['name' => $m['name']])['first'];
        $tz = $m['time_zone'] !== '' ? $m['time_zone'] : BOOKING_HOST_TZ;
        $day = booking_fmt($m['start'], $tz, 'short');
        $manage = booking_url($m['token']);
        $country = booking_country($m);
        if ($kind === 'cancelled') {
            $subject = 'Cancelled: our call on ' . $day;
            $body = "Hi $first,\n\nOur call on " . booking_fmt($m['start'], $tz, 'day') . ' at ' . booking_fmt($m['start'], $tz, 'time') . ' is cancelled, and the invitation has been removed from the calendar.'
                . "\n\nIf you would like to talk at another time, you can pick a new slot with the same link whenever it suits you.";
            $html = booking_letter_html($subject, $body, $crm, $country, ['label' => 'Pick a new time', 'url' => $manage]);
            $text = crm_markup_text($body) . "\n\nPick a new time: $manage\n\n" . crm_signature_text($crm, $country);
            $attach = [];
        } else {
            $moved = $kind === 'moved';
            $subject = ($moved ? 'Moved: ' : 'Booked: ') . 'your call with ' . $cfg['host_name'] . ' of VTurnU, ' . $day . ' (' . booking_zone_city($tz) . ' time)';
            $body = "Hi $first,\n\n" . ($moved ? 'Your call is moved to the new time below.' : 'Your call with ' . booking_host_line($cfg) . ' of VTurnU' . ($cfg['host_title'] !== '' ? ',' : '') . ' is booked.')
                . ' A calendar invitation from ' . CONTACT_EMAIL . ' ' . ($moved ? 'has been updated' : 'is on its way') . ', and the file attached here adds the call to any calendar.';
            $after = "Need another time? Reschedule or cancel here: $manage";
            if (!$moved) {
                $after .= "\n\n## What we will cover\n- " . implode("\n- ", booking_agenda($m));
            }
            $html = booking_letter_html($subject, $body, $crm, $country, $m['meet_url'] !== '' ? ['label' => 'Join with Google Meet', 'url' => $m['meet_url']] : null, '', booking_card_html($m), $after);
            $text = crm_markup_text($body) . "\n\n" . booking_fmt($m['start'], $tz, 'day') . ', ' . booking_fmt($m['start'], $tz, 'time') . ' to ' . booking_fmt($m['end'], $tz, 'time')
                . ' ' . booking_zone_label($tz, $m['start']) . ($m['meet_url'] !== '' ? "\nGoogle Meet: " . $m['meet_url'] : '') . "\n\n" . crm_markup_text($after) . "\n\n" . crm_signature_text($crm, $country);
            $attach = [['filename' => 'invite.ics', 'content' => base64_encode(booking_ics($m, $cfg)), 'content_type' => 'text/calendar; charset=UTF-8; method=PUBLISH']];
        }
        $payload = ['from' => booking_from($cfg), 'to' => [$m['email']], 'subject' => crm_no_dashes($subject), 'html' => $html, 'text' => $text, 'reply_to' => CONTACT_EMAIL];
        if ($attach) {
            $payload['attachments'] = $attach;
        }
        $sent = resend_send($payload);
        if (!$sent['ok']) {
            error_log('booking_mail_lead: ' . $sent['error']);
        }
    } catch (Throwable $e) {
        error_log('booking_mail_lead: ' . $e->getMessage());
    }
}

/** The owner's alert at connect@: who, when in both zones, what they said, and where to see the lead. */
function booking_mail_owner(array $m, string $kind, array $cfg, ?array $before = null): void
{
    try {
        $tz = $m['time_zone'] !== '' ? $m['time_zone'] : BOOKING_HOST_TZ;
        $who = $m['name'] . ($m['company'] !== '' ? ', ' . $m['company'] : '');
        $subject = ['booked' => 'Meeting booked', 'moved' => 'Meeting moved', 'cancelled' => 'Meeting cancelled'][$kind] . ': ' . $who . ' · ' . booking_fmt($m['start'], BOOKING_HOST_TZ, 'short') . ' IST';
        $L = [
            ['booked' => $who . ' booked a call.', 'moved' => $who . ' moved their call.', 'cancelled' => $who . ' cancelled their call.'][$kind],
            '',
            'When:      ' . booking_ist($m['start']),
            'Their time: ' . booking_fmt($m['start'], $tz, 'full') . ' ' . booking_zone_label($tz, $m['start']),
        ];
        if ($kind === 'moved' && $before) {
            $L[] = 'Was:       ' . booking_ist($before['start']);
        }
        if ($kind !== 'cancelled' && $m['meet_url'] !== '') {
            $L[] = 'Meet:      ' . $m['meet_url'];
        }
        if ($kind === 'cancelled' && $m['cancel_reason'] !== '') {
            $L[] = 'Reason:    ' . $m['cancel_reason'];
        }
        $L[] = '';
        $L[] = 'Email:     ' . $m['email'];
        $L[] = 'Phone:     ' . ($m['phone'] !== '' ? $m['phone'] : 'not given');
        if ($m['notes'] !== '') {
            $L[] = '';
            $L[] = 'They wrote: ' . $m['notes'];
        }
        if ($m['enquiry_id'] > 0 && ($lead = crm_lead($m['enquiry_id']))) {
            $f = crm_lead_facts($lead);
            if ($f['note'] !== '' && $f['note'] !== $m['notes']) {
                $L[] = '';
                $L[] = 'Their enquiry: ' . crm_clean_line($f['note'], 600);
            }
            $L[] = 'Service: ' . ($f['service'] !== '' ? $f['service'] : 'not chosen') . ' · Budget: ' . ($f['budget'] !== '' ? $f['budget'] : 'not given')
                . (($lead['channel'] ?? '') !== '' ? ' · Came from: ' . $lead['channel'] : '');
            $L[] = '';
            $L[] = 'Open the lead: ' . SITE_URL . '/admin/?view=lead&id=' . $m['enquiry_id'];
        }
        $L[] = 'Google Calendar: ' . ($m['event_link'] !== '' ? $m['event_link'] : 'https://calendar.google.com/');
        $opts = ['reply_to' => $m['name'] . ' <' . $m['email'] . '>'];
        if ($kind !== 'cancelled') {
            $opts['attachment'] = ['filename' => 'invite.ics', 'content' => booking_ics($m, $cfg)];
        }
        send_email(ENQUIRY_TO, crm_no_dashes($subject), crm_no_dashes(implode("\n", $L)), $opts);
    } catch (Throwable $e) {
        error_log('booking_mail_owner: ' . $e->getMessage());
    }
}

/** A plain alert to connect@ when booking breaks, at most once an hour per kind. */
function booking_alert_owner(string $what, string $detail): void
{
    if (!security_rate_ok('booking-alert', 1, 3600, $what)) {
        return;
    }
    send_email(ENQUIRY_TO, 'Booking page problem: ' . $what, $detail . "\n\nIf this says the Google sign-in failed, run bin/google-connect.mjs again and redeploy.\nSettings: " . SITE_URL . '/admin/?view=settings');
}

/**
 * Reminders, scheduled with Resend when the call is booked: a day before (if
 * the call is more than a day and a bit away) and an hour before.
 *
 * @return list<string> Resend ids, so a move or cancellation can call them off
 */
function booking_schedule_reminders(array $m, array $cfg): array
{
    if (empty($cfg['reminders']) || $m['status'] !== 'booked') {
        return [];
    }
    $crm = crm_settings();
    $first = crm_lead_facts(['name' => $m['name']])['first'];
    $tz = $m['time_zone'] !== '' ? $m['time_zone'] : BOOKING_HOST_TZ;
    $country = booking_country($m);
    $ids = [];
    $plan = [];
    if ($m['start'] - time() > 26 * 3600) {
        $plan[] = [$m['start'] - 24 * 3600, 'Tomorrow: your call with ' . $cfg['host_name'] . ' of VTurnU at ' . booking_fmt($m['start'], $tz, 'time') . ' (' . booking_zone_city($tz) . ' time)',
            "Hi $first,\n\nA quick reminder of our call tomorrow. If anything has changed, you can move it or cancel it with one click."];
    }
    if ($m['start'] - time() > 2 * 3600) {
        $plan[] = [$m['start'] - 3600, 'In one hour: your call with ' . $cfg['host_name'] . ' of VTurnU',
            "Hi $first,\n\nOur call starts in one hour. The Google Meet link is below, and it works in any browser."];
    }
    foreach ($plan as [$at, $subject, $body]) {
        $after = 'Need another time? Reschedule or cancel here: ' . booking_url($m['token']);
        $sent = resend_send([
            'from' => booking_from($cfg),
            'to' => [$m['email']],
            'subject' => crm_no_dashes($subject),
            'html' => booking_letter_html($subject, $body, $crm, $country, $m['meet_url'] !== '' ? ['label' => 'Join with Google Meet', 'url' => $m['meet_url']] : null, '', booking_card_html($m), $after),
            'text' => crm_markup_text($body) . "\n\n" . booking_fmt($m['start'], $tz, 'day') . ', ' . booking_fmt($m['start'], $tz, 'time') . ' ' . booking_zone_label($tz, $m['start'])
                . ($m['meet_url'] !== '' ? "\nGoogle Meet: " . $m['meet_url'] : '') . "\n\n" . $after . "\n\n" . crm_signature_text($crm, $country),
            'reply_to' => CONTACT_EMAIL,
            'scheduled_at' => gmdate('Y-m-d\TH:i:s\Z', $at),
        ]);
        if ($sent['ok'] && $sent['id'] !== '') {
            $ids[] = $sent['id'];
        } elseif (!$sent['ok']) {
            error_log('booking_schedule_reminders: ' . $sent['error']);
        }
    }
    return $ids;
}

/** Call off scheduled reminders (a move or cancellation). Ones already sent cannot be, which is fine. */
function booking_cancel_reminders(array $m): void
{
    foreach ((array) ($m['reminder_ids'] ?? []) as $id) {
        if (!is_string($id) || !preg_match('/^[A-Za-z0-9-]{8,64}$/', $id)) {
            continue;
        }
        if (env_var('MAIL_TRANSPORT', '') === 'capture') {
            $GLOBALS['cancelled_mail'][] = $id;
            continue;
        }
        $key = env_var('RESEND_API_KEY', '');
        if ($key === '') {
            continue;
        }
        $ch = curl_init('https://api.resend.com/emails/' . $id . '/cancel');
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_POSTFIELDS => '', CURLOPT_TIMEOUT => 8,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $key]]);
        curl_exec($ch);
    }
}

/* ------------------------------------------------------------------ */
/* Tracking                                                            */
/* ------------------------------------------------------------------ */

/** The meeting request email was opened (its image loaded). */
function booking_track_open(string $token): void
{
    try {
        $m = booking_by_token($token);
        if (!$m || !empty($m['opened_at'])) {
            return;
        }
        db()->prepare('UPDATE meetings SET opened_at = now() WHERE id = ?')->execute([$m['id']]);
        if ($m['enquiry_id'] > 0) {
            crm_log($m['enquiry_id'], 'Opened the meeting request email');
        }
    } catch (Throwable $e) {
        error_log('booking_track_open: ' . $e->getMessage());
    }
}

/** The lead looked at their booking page for a few seconds (link scanners do not). */
function booking_track_view(string $token): void
{
    try {
        $m = booking_by_token($token);
        if (!$m || !empty($m['viewed_at'])) {
            return;
        }
        db()->prepare('UPDATE meetings SET viewed_at = now() WHERE id = ?')->execute([$m['id']]);
        if ($m['enquiry_id'] > 0 && $m['status'] === 'requested') {
            crm_log($m['enquiry_id'], 'Opened the booking page');
        }
    } catch (Throwable $e) {
        error_log('booking_track_view: ' . $e->getMessage());
    }
}

/* ------------------------------------------------------------------ */
/* Routes                                                              */
/* ------------------------------------------------------------------ */

/**
 * /book-a-meeting/ (public), /book-a-meeting/<token>/ (personal: book, move,
 * cancel), /book-a-meeting/<token>/invite.ics, its view beacon, and the
 * request email's open pixel /p/m/<token>.gif. Returns true when it answered.
 */
function booking_route(string $slug): bool
{
    if (preg_match('#^p/m/([a-f0-9]{32})\.gif$#', $slug, $mm)) {
        booking_track_open($mm[1]);
        header('Content-Type: image/gif');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('X-Robots-Tag: noindex');
        echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        return true;
    }
    if ($slug !== BOOKING_PATH && !str_starts_with($slug, BOOKING_PATH . '/')) {
        return false;
    }
    header('X-Robots-Tag: noindex, nofollow');
    header('Cache-Control: private, no-store');
    header('Referrer-Policy: no-referrer');
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $uri = (string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
    if (!preg_match('#^' . BOOKING_PATH . '(?:/([a-f0-9]{32}))?(?:/(invite\.ics|seen))?$#', $slug, $r)) {
        http_response_code(404);
        booking_render_message('This link is not valid', 'Please use the link in your email, or book a new time.', booking_url());
        return true;
    }
    $token = (string) ($r[1] ?? '');
    $extra = (string) ($r[2] ?? '');
    if ($extra === '' && in_array($method, ['GET', 'HEAD'], true) && !str_ends_with($uri, '/')) {
        $qs = (string) ($_SERVER['QUERY_STRING'] ?? '');
        header('Location: /' . $slug . '/' . ($qs !== '' ? '?' . $qs : ''), true, 308);
        return true;
    }
    if (!booking_ready()) {
        booking_render_message('Booking is not available right now', 'Please email ' . CONTACT_EMAIL . ' or WhatsApp ' . CONTACT_PHONE . ' and we will set up a time with you.', '');
        return true;
    }
    $m = null;
    if ($token !== '') {
        try {
            $m = booking_by_token($token);
        } catch (Throwable $e) {
            error_log('booking_route: ' . $e->getMessage());
        }
        if (!$m) {
            http_response_code(404);
            booking_render_message('This booking link is not valid', 'It may have been mistyped. You can still book a call here.', booking_url());
            return true;
        }
    }
    if ($extra === 'seen') {
        if ($method === 'POST' && $m && security_rate_ok('booking-seen', 30, 3600, $token)) {
            booking_track_view($token);
        }
        http_response_code(204);
        return true;
    }
    if ($extra === 'invite.ics') {
        if (!$m || $m['status'] !== 'booked') {
            http_response_code(404);
            return true;
        }
        header('Content-Type: text/calendar; charset=UTF-8');
        header('Content-Disposition: attachment; filename="vturnu-call.ics"');
        echo booking_ics($m, booking_settings());
        return true;
    }

    $cfg = booking_settings();
    $state = ['error' => '', 'errors' => [], 'old' => [], 'notice' => ''];
    if ($method === 'POST') {
        $action = (string) ($_POST['action'] ?? '');
        $post = $_POST;
        $guard = booking_guard($post, $m === null);
        if ($guard !== '') {
            $state['error'] = $guard;
            $state['old'] = $post;
        } elseif ($action === 'cancel' && $m) {
            $c = booking_cancel($m, (string) ($post['reason'] ?? ''), 'lead');
            if ($c['ok']) {
                header('Location: /' . BOOKING_PATH . '/' . $m['token'] . '/?done=cancelled', true, 303);
                return true;
            }
            $state['error'] = $c['error'];
        } elseif (in_array($action, ['book', 'move'], true)) {
            $b = booking_book($m, $post, $action === 'move' ? 'move' : 'book');
            if ($b['ok']) {
                header('Location: /' . BOOKING_PATH . '/' . $b['token'] . '/?done=' . ($action === 'move' ? 'moved' : 'booked'), true, 303);
                return true;
            }
            $state['error'] = $b['error'];
            $state['errors'] = $b['errors'];
            $state['old'] = $post;
            $state['taken'] = $b['taken'];
        }
        if ($m) {
            $m = booking_by_token($m['token']) ?? $m;
        }
    }
    booking_render_page($m, $cfg, $state);
    return true;
}

/** The checks every booking form post passes: a real browser, a fresh form, not too many tries. Returns an error or ''. */
function booking_guard(array $post, bool $public): string
{
    if (trim((string) ($post['website'] ?? '')) !== '' || security_ua_is_bot()) {
        return 'Something went wrong. Please try again.';
    }
    if (!csrf_verify((string) ($post['csrf_token'] ?? ''))) {
        return 'This page was open for a long time. Please pick your time again.';
    }
    if (!security_rate_ok('booking', $public ? 6 : 12, 3600)) {
        return 'Too many attempts from your network. Please try again in an hour, or email ' . CONTACT_EMAIL . '.';
    }
    if ($public && !security_recaptcha_ok((string) ($post['recaptcha_token'] ?? ''))) {
        return 'We could not verify you are human. Please refresh the page and try again.';
    }
    return '';
}

require_once __DIR__ . '/booking-page.php';
