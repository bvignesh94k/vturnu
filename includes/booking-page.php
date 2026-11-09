<?php
/**
 * The booking pages: pick a time, see your booking, move it or cancel it.
 *
 * Everything works as plain HTML forms (a zone drop-down and a drop-down of
 * times, grouped by day). assets/js/booking.js turns the picking page into a
 * Calendly-style flow: the meeting on the left, a month calendar with the
 * active time zone under it, the chosen day's times in a third column, then
 * the details. The zone starts as the one the visitor's IP points to and can
 * be changed in a searchable picker; every zone gets the working hours on its
 * own clock, worked out from the free grid the server sends.
 */

declare(strict_types=1);

/** A one-message page: a bad link, or booking paused. */
function booking_render_message(string $title, string $text, string $link): void
{
    header('Content-Type: text/html; charset=UTF-8');
    booking_head($title . ' | VTurnU');
    echo '<main class="bk bk--narrow"><section class="bk-card bk-card--msg"><div class="bk-panel">'
        . '<h1 class="bk-h">' . e($title) . '</h1><p class="bk-p">' . e($text) . '</p>'
        . ($link !== '' ? '<p><a class="bk-btn" href="' . e($link) . '">Book a call</a></p>' : '')
        . '<p class="bk-small">Email <a href="mailto:' . e(CONTACT_EMAIL) . '">' . e(CONTACT_EMAIL) . '</a> or WhatsApp <a href="https://wa.me/' . e(CONTACT_WHATSAPP) . '">' . e(CONTACT_PHONE) . '</a></p>'
        . '</div></section></main>';
    booking_foot(null);
}

function booking_head(string $title): void
{
    ?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?></title>
<link rel="icon" type="image/svg+xml" href="/assets/img/vturnu-icon-mark.svg">
<link rel="preload" as="font" type="font/woff2" href="/assets/fonts/poppins-400.woff2" crossorigin>
<link rel="preload" as="font" type="font/woff2" href="/assets/fonts/poppins-700.woff2" crossorigin>
<link rel="stylesheet" href="/assets/css/booking.css?v=<?= e(asset_v('/assets/css/booking.css')) ?>">
</head>
<body>
<div class="bk-bar"></div>
<header class="bk-top">
    <a href="/" title="VTurnU home"><img src="/assets/img/email/sig-logo.png" alt="VTurnU, together we turn heads" width="181" height="50"></a>
    <p class="bk-top-note"><span class="bk-lock" aria-hidden="true"></span>Booked straight onto our calendar</p>
</header>
<?php
}

function booking_foot(?array $data): void
{
    ?>
<footer class="bk-foot">VTurnU Digital Solutions LLP · <a href="/">vturnu.com</a> · <a href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a></footer>
<?php if ($data !== null): ?>
<script type="application/json" id="bk-data"><?= json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES) ?></script>
<script src="/assets/js/booking.js?v=<?= e(asset_v('/assets/js/booking.js')) ?>" defer></script>
<?php endif; ?>
</body>
</html>
<?php
}

/** The time zone drop-down for browsers without the script: the zones that matter first, then every zone by region. */
function booking_zone_select(string $id, string $name, string $selected, array $suggest, int $now): string
{
    $label = static fn(string $tz): string => (booking_is_india($tz) ? 'India' : booking_zone_city($tz)) . ' (' . booking_gmt($tz, $now) . ')';
    $out = '<select id="' . e($id) . '" name="' . e($name) . '" class="bk-select" autocomplete="off">';
    $suggest = array_values(array_unique(array_filter(array_merge([$selected], $suggest), 'strlen')));
    $out .= '<optgroup label="Suggested">';
    foreach ($suggest as $tz) {
        $out .= '<option value="' . e($tz) . '"' . ($tz === $selected ? ' selected' : '') . '>' . e($label($tz)) . '</option>';
    }
    $out .= '</optgroup>';
    $groups = [];
    foreach (DateTimeZone::listIdentifiers() as $tz) {
        $groups[strtok($tz, '/')][] = $tz;
    }
    foreach ($groups as $region => $zones) {
        $out .= '<optgroup label="' . e($region === 'UTC' ? 'Universal' : $region) . '">';
        foreach ($zones as $tz) {
            if (!in_array($tz, $suggest, true)) {
                $out .= '<option value="' . e($tz) . '">' . e($tz === 'UTC' ? 'UTC (GMT)' : $label($tz)) . '</option>';
            }
        }
        $out .= '</optgroup>';
    }
    return $out . '</select>';
}

/**
 * The plain list of times for one zone, grouped by day: the form's fallback.
 *
 * @param list<int> $slots
 */
function booking_slot_select(array $slots, string $tz, int $chosen): string
{
    $out = '<select id="bk-slot" name="slot" class="bk-select" required><option value="">Choose a time</option>';
    $day = '';
    foreach ($slots as $t) {
        $d = booking_fmt($t, $tz, 'day');
        if ($d !== $day) {
            $out .= ($day !== '' ? '</optgroup>' : '') . '<optgroup label="' . e($d) . '">';
            $day = $d;
        }
        $out .= '<option value="' . $t . '"' . ($t === $chosen ? ' selected' : '') . '>' . e(booking_fmt($t, $tz, 'time')) . '</option>';
    }
    return $out . ($day !== '' ? '</optgroup>' : '') . '</select>';
}

/**
 * The page for a link: picking a time (to book, to book again or to move), a
 * booked call with its Meet link and calendar buttons, or the cancel form.
 */
function booking_render_page(?array $m, array $cfg, array $state): void
{
    $now = booking_now();
    $done = (string) ($_GET['done'] ?? '');
    $lead = $m && $m['enquiry_id'] > 0 ? crm_lead($m['enquiry_id']) : null;
    if ($m && $m['status'] === 'booked' && $m['end'] > $now && $done === '') {
        $m = booking_sync($m);
    }
    $booked = $m && $m['status'] === 'booked';
    $past = $booked && $m['end'] <= $now;
    $view = 'pick';
    $mode = 'book';
    if ($booked && !$past) {
        $view = isset($_GET['change']) || ($state['old']['action'] ?? '') === 'move' ? 'pick' : (isset($_GET['cancel']) ? 'cancel' : 'booked');
        $mode = $view === 'pick' ? 'move' : 'book';
    }
    $first = $m ? crm_lead_facts(['name' => $m['name']])['first'] : '';
    $greet = $first !== '' && $first !== 'there' ? $first : '';
    $topic = $lead ? booking_topic($lead) : '';
    $agenda = booking_agenda(['topic' => $topic]);

    /* The zone: one the visitor chose (the link or their last try) first, then where their IP is, then where they booked before. */
    $tzParam = booking_zone_name((string) ($_GET['tz'] ?? ($state['old']['tz'] ?? '')));
    $ipTz = booking_ip_zone();
    $stored = $m && $m['time_zone'] !== '' ? booking_zone_name($m['time_zone']) : '';
    [$tz, $tzSource] = $tzParam !== '' ? [$tzParam, 'url'] : ($ipTz !== '' ? [$ipTz, 'ip'] : ($stored !== '' ? [$stored, 'stored'] : ['', '']));
    $tzShown = $tz !== '' ? $tz : BOOKING_HOST_TZ;

    $avail = null;
    $unavailable = '';
    if ($view === 'pick') {
        if (empty($cfg['enabled']) || !gcal_configured()) {
            $unavailable = 'Online booking is paused right now.';
        } else {
            try {
                $avail = booking_availability($cfg, $mode === 'move' ? $m : null);
            } catch (Throwable $e) {
                error_log('booking_render_page: ' . $e->getMessage());
                booking_alert_owner('Booking page could not read the calendar', $e->getMessage());
                $unavailable = 'We could not read our calendar just now.';
            }
        }
    }

    $title = $view === 'booked' ? 'Your call with VTurnU' : ($view === 'cancel' ? 'Cancel your call' : ($mode === 'move' ? 'Move your call' : ($greet !== '' ? $greet . ', pick a time to talk' : 'Book a call with VTurnU')));
    header('Content-Type: text/html; charset=UTF-8');
    booking_head($title . ' | VTurnU');
    $action = $m ? '/' . BOOKING_PATH . '/' . $m['token'] . '/' : '/' . BOOKING_PATH . '/';
    $old = $state['old'];
    $val = static fn(string $k, string $fallback = ''): string => e((string) ($old[$k] ?? $fallback));
    $err = static fn(string $k): string => isset($state['errors'][$k]) ? '<p class="bk-err" id="bk-' . $k . '-err">' . e($state['errors'][$k]) . '</p>' : '';
    $pickHeading = $mode === 'move' ? 'Pick a new date and time' : ($greet !== '' ? $greet . ', pick a date and time' : 'Select a date and time');
    ?>
<main class="bk">
<section class="bk-card bk-card--<?= e($view) ?>" id="bk-card">
    <aside class="bk-info">
        <div class="bk-host">
            <img class="bk-photo" src="/assets/img/booking/host-b-vignesh-192.jpg" alt="<?= e($cfg['host_name']) ?>" width="64" height="64">
            <div>
                <p class="bk-host-name"><?= e($cfg['host_name']) ?></p>
                <p class="bk-host-role"><?= e($cfg['host_title'] !== '' ? $cfg['host_title'] . ', VTurnU' : 'VTurnU') ?></p>
            </div>
        </div>
        <h1 class="bk-title"><?= (int) $cfg['length'] ?>-minute video call</h1>
        <ul class="bk-facts">
            <li><span class="bk-ico bk-ico--clock" aria-hidden="true"></span><?= (int) $cfg['length'] ?> min</li>
            <li><span class="bk-ico bk-ico--video" aria-hidden="true"></span>Google Meet, the link comes with your invitation</li>
        </ul>
        <div class="bk-agenda">
            <h2 class="bk-h3">What we will cover</h2>
            <ul><?php foreach ($agenda as $a): ?><li><?= e($a) ?></li><?php endforeach; ?></ul>
        </div>
        <p class="bk-small bk-host-note">No sales script and no obligation. If we are not the right fit, we will say so and point you to someone who is.</p>
    </aside>

    <div class="bk-main">
    <?php if ($state['error'] !== ''): ?>
        <div class="bk-alert" role="alert"><?= e($state['error']) ?></div>
    <?php endif; ?>

    <?php if ($view === 'booked'):
        $tzm = $m['time_zone'] !== '' ? $m['time_zone'] : BOOKING_HOST_TZ; ?>
        <div class="bk-panel bk-done">
            <?php if ($done === 'booked' || $done === 'moved'): ?>
            <div class="bk-check" aria-hidden="true"></div>
            <h2 class="bk-h"><?= $done === 'moved' ? 'Your call is moved' : 'You are booked' ?></h2>
            <p class="bk-p">A confirmation is on its way to <strong><?= e($m['email']) ?></strong>, and Google is <?= $done === 'moved' ? 'updating the calendar invitation' : 'sending the calendar invitation' ?> from <?= e(CONTACT_EMAIL) ?>.</p>
            <?php else: ?>
            <h2 class="bk-h">Your call<?= $greet !== '' ? ', ' . e($greet) : '' ?></h2>
            <?php endif; ?>
            <div class="bk-when" data-start="<?= $m['start'] ?>" data-end="<?= $m['end'] ?>" data-tz="<?= e($tzm) ?>">
                <p class="bk-when-day"><?= e(booking_fmt($m['start'], $tzm, 'day')) ?></p>
                <p class="bk-when-time"><?= e(booking_fmt($m['start'], $tzm, 'time') . ' to ' . booking_fmt($m['end'], $tzm, 'time')) ?></p>
                <p class="bk-when-zone"><?= e(booking_zone_label($tzm, $m['start'])) ?></p>
                <?php if (!booking_is_india($tzm)): ?><p class="bk-when-host"><?= e(booking_fmt($m['start'], BOOKING_HOST_TZ, 'time')) ?> in Chennai, India, where our team is</p><?php endif; ?>
                <p class="bk-countdown" hidden></p>
            </div>
            <?php if ($m['meet_url'] !== ''): ?>
            <a class="bk-btn bk-btn--big" href="<?= e($m['meet_url']) ?>" target="_blank" rel="noopener">Join with Google Meet</a>
            <p class="bk-small bk-meet"><?= e(preg_replace('#^https?://#', '', $m['meet_url'])) ?></p>
            <?php endif; ?>
            <div class="bk-add">
                <a class="bk-chip" href="/<?= BOOKING_PATH ?>/<?= e($m['token']) ?>/invite.ics">Add to Outlook or Apple Calendar (.ics)</a>
                <?php if ($m['event_link'] !== ''): ?><a class="bk-chip" href="<?= e($m['event_link']) ?>" target="_blank" rel="noopener">Open in Google Calendar</a><?php endif; ?>
            </div>
            <div class="bk-manage">
                <a class="bk-link" href="?change=1">Reschedule</a>
                <a class="bk-link bk-link--quiet" href="?cancel=1">Cancel the call</a>
            </div>
        </div>

    <?php elseif ($view === 'cancel'):
        $tzm = $m['time_zone'] !== '' ? $m['time_zone'] : BOOKING_HOST_TZ; ?>
        <form class="bk-panel" method="post" action="<?= e($action) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="cancel">
            <div class="bk-hp" aria-hidden="true"><label for="bk-website">Leave this empty</label><input id="bk-website" name="website" type="text" tabindex="-1" autocomplete="off"></div>
            <h2 class="bk-h">Cancel your call?</h2>
            <p class="bk-p"><?= e(booking_fmt($m['start'], $tzm, 'day') . ', ' . booking_fmt($m['start'], $tzm, 'time') . ' ' . booking_zone_label($tzm, $m['start'])) ?></p>
            <p class="bk-p">If another time would work better, <a class="bk-link" href="?change=1">move it instead</a>. It keeps the same Meet link.</p>
            <label class="bk-label" for="bk-reason">Anything we should know? <span>(optional)</span></label>
            <textarea class="bk-input" id="bk-reason" name="reason" rows="3" maxlength="1000" placeholder="For example: we found another partner, or the timing changed"></textarea>
            <div class="bk-row">
                <button class="bk-btn bk-btn--danger" type="submit">Cancel the call</button>
                <a class="bk-link" href="./">Keep it</a>
            </div>
        </form>

    <?php elseif ($unavailable !== ''): ?>
        <div class="bk-panel">
            <h2 class="bk-h"><?= e($unavailable) ?></h2>
            <p class="bk-p">Please email <a class="bk-link" href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a> or message us on <a class="bk-link" href="https://wa.me/<?= e(CONTACT_WHATSAPP) ?>">WhatsApp at <?= e(CONTACT_PHONE) ?></a>, and we will set up a time with you.</p>
        </div>

    <?php else:
        $slots = booking_zone_slots($cfg, $avail['now'], $avail['grid'], $tzShown);
        $chosen = (int) ($old['slot'] ?? 0);
        $public = $m === null;
        $current = $mode === 'move' ? $m : null;
        $tzm = $current && $current['time_zone'] !== '' ? $current['time_zone'] : $tzShown;
        ?>
        <?php if ($done === 'cancelled'): ?><div class="bk-alert bk-alert--ok" role="status">Your call is cancelled and the invitation is withdrawn. If you would like to talk at another time, pick one below.</div><?php endif; ?>
        <?php if ($m && $m['status'] === 'cancelled' && $done !== 'cancelled'): ?><div class="bk-alert bk-alert--ok">Your earlier call was cancelled. Pick a new time below whenever it suits you.</div><?php endif; ?>
        <?php if ($past): ?><div class="bk-alert bk-alert--ok">Our call on <?= e(booking_fmt($m['start'], $tzm, 'day')) ?> has taken place. Pick a time below to talk again.</div><?php endif; ?>
        <?php if ($current): ?>
        <div class="bk-current">Now booked: <strong><?= e(booking_fmt($current['start'], $tzm, 'day') . ', ' . booking_fmt($current['start'], $tzm, 'time')) ?></strong> <?= e(booking_zone_label($tzm, $current['start'])) ?>. Pick the new time; the Meet link stays the same. <a class="bk-link" href="./">Keep this time</a></div>
        <?php endif; ?>

        <form class="bk-tzform bk-nojs" method="get" action="<?= e($action) ?>">
            <?php if ($mode === 'move'): ?><input type="hidden" name="change" value="1"><?php endif; ?>
            <label class="bk-label" for="bk-tz">Time zone</label>
            <div class="bk-tzrow">
                <?= booking_zone_select('bk-tz', 'tz', $tzShown, array_filter([$ipTz, $stored, BOOKING_HOST_TZ]), $now) ?>
                <button class="bk-btn bk-btn--ghost" type="submit">Show times</button>
            </div>
        </form>

        <form id="bk-form" class="bk-form" method="post" action="<?= e($action) ?>" novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="<?= $mode === 'move' ? 'move' : 'book' ?>">
            <input type="hidden" name="tz" id="bk-tzval" value="<?= e($tzShown) ?>">
            <input type="hidden" name="recaptcha_token" id="bk-rc" value="">
            <input type="hidden" name="attribution" id="bk-attr" value="">
            <div class="bk-hp" aria-hidden="true"><label for="bk-website">Leave this empty</label><input id="bk-website" name="website" type="text" tabindex="-1" autocomplete="off"></div>

            <div id="bk-app" class="bk-app" hidden></div>
            <div class="bk-fallback bk-nojs">
                <h2 class="bk-h"><?= e($pickHeading) ?></h2>
                <?php if (!$slots): ?>
                <p class="bk-p">There are no open times in the next <?= (int) $cfg['window_days'] ?> days. Please email <a class="bk-link" href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a> and we will find one.</p>
                <?php endif; ?>
                <label class="bk-label" for="bk-slot">Time (<?= e(booking_zone_label($tzShown, $now)) ?>)</label>
                <?= booking_slot_select($slots, $tzShown, $chosen) ?>
                <?= $err('slot') ?>
            </div>

            <div class="bk-details" id="bk-details">
                <div class="bk-summary" id="bk-summary" hidden></div>
                <?php if ($mode === 'book'): ?>
                <h2 class="bk-h bk-h--details">Enter your details</h2>
                <div class="bk-grid">
                    <div class="bk-field">
                        <label class="bk-label" for="bk-name">Full name</label>
                        <input class="bk-input" id="bk-name" name="name" type="text" maxlength="80" autocomplete="name" required value="<?= $val('name', $m['name'] ?? '') ?>" aria-describedby="bk-name-err">
                        <?= $err('name') ?>
                    </div>
                    <div class="bk-field">
                        <label class="bk-label" for="bk-email">Work email</label>
                        <input class="bk-input" id="bk-email" name="email" type="email" maxlength="120" autocomplete="email" required value="<?= $val('email', $m['email'] ?? '') ?>" aria-describedby="bk-email-err">
                        <?= $err('email') ?>
                    </div>
                    <div class="bk-field">
                        <label class="bk-label" for="bk-company">Company <span>(optional)</span></label>
                        <input class="bk-input" id="bk-company" name="company" type="text" maxlength="80" autocomplete="organization" value="<?= $val('company', $m['company'] ?? '') ?>">
                    </div>
                    <div class="bk-field">
                        <label class="bk-label" for="bk-phone">Phone or WhatsApp <span>(optional)</span></label>
                        <input class="bk-input" id="bk-phone" name="phone" type="tel" maxlength="30" autocomplete="tel" value="<?= $val('phone', $m['phone'] ?? '') ?>" aria-describedby="bk-phone-err" placeholder="With country code, e.g. +1 415 555 0132">
                        <?= $err('phone') ?>
                    </div>
                    <?php if ($public): ?>
                    <div class="bk-field bk-field--wide">
                        <label class="bk-label" for="bk-service">What would you like to talk about? <span>(optional)</span></label>
                        <select class="bk-select" id="bk-service" name="service">
                            <option value="">Choose a topic</option>
                            <?php foreach (LEAD_SERVICES as $group => $items): ?>
                            <optgroup label="<?= e($group) ?>"><?php foreach ($items as $it): ?><option<?= ($old['service'] ?? '') === $it ? ' selected' : '' ?>><?= e($it) ?></option><?php endforeach; ?></optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div class="bk-field bk-field--wide">
                        <label class="bk-label" for="bk-notes">Anything we should prepare? <span>(optional)</span></label>
                        <textarea class="bk-input" id="bk-notes" name="notes" rows="3" maxlength="1500" placeholder="Your goals, your website, or anything else you would like help with"><?= $val('notes', $m['notes'] ?? '') ?></textarea>
                    </div>
                </div>
                <?php endif; ?>
                <button class="bk-btn bk-btn--big bk-submit" type="submit" id="bk-submit"><?= $mode === 'move' ? 'Move my call to this time' : 'Schedule the call' ?></button>
                <p class="bk-small bk-fine"><?= $mode === 'move' ? 'Google updates the invitation in your calendar, and the Google Meet link stays the same.' : 'You will get a calendar invitation with a Google Meet link, and you can reschedule or cancel at any time from your confirmation email.' ?></p>
            </div>
        </form>
    <?php endif; ?>
    </div>
</section>
</main>
<?php
    $data = null;
    if ($avail !== null && $view === 'pick') {
        $data = [
            'now' => $avail['now'],
            'grid' => $avail['grid'],
            'len' => (int) $cfg['length'],
            'step' => BOOKING_STEP,
            'hours' => booking_hours((string) $cfg['hours']),
            'days' => array_map('intval', (array) $cfg['days']),
            'notice' => (int) $cfg['notice_minutes'],
            'tz' => $tz,
            'tzSource' => $tzSource,
            'hostTz' => BOOKING_HOST_TZ,
            'zones' => booking_zone_countries(),
            'mode' => $mode,
            'heading' => $pickHeading,
            'chosen' => $chosen,
            'taken' => !empty($state['taken']),
            'fieldErrors' => !empty($state['errors']) && empty($state['errors']['slot']),
            'recaptcha' => $m === null ? RECAPTCHA_SITE_KEY : '',
            'seen' => $m && $m['status'] === 'requested' ? '/' . BOOKING_PATH . '/' . $m['token'] . '/seen' : '',
        ];
    } elseif ($view === 'booked') {
        $data = ['countdown' => true];
    }
    booking_foot($data);
}
