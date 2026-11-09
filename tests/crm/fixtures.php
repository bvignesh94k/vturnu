<?php
/* Sample leads and proposals for rendering the admin: a fresh tracked lead,
   one with a read and accepted proposal, an untracked old lead, and other funnels. */
$db = $GLOBALS['FAKE_DB'];
$db->leads = [
    fixture_lead(),
    fixture_lead(['id' => '43', 'created_at' => date('Y-m-d H:i:sP', strtotime('-6 days')), 'name' => 'Dan Cole', 'email' => 'dan@brightcrm.com', 'mobile' => '+1 415 555 0132',
        'company' => 'BrightCRM', 'service' => 'Google Ads and paid media', 'status' => 'proposal', 'priority' => 'high', 'value' => '180000', 'followup' => date('Y-m-d'),
        'tags' => 'proposal accepted', 'channel' => 'Google Ads', 'channel_group' => 'Paid ads', 'grade' => 'B', 'score' => 5, 'country' => 'US', 'contacted_at' => date('Y-m-d H:i:sP', strtotime('-5 days')),
        'attribution' => ['channel' => 'Google Ads', 'group' => 'Paid ads', 'detail' => 'crm-search, crm for agencies', 'first' => ['at' => gmdate('Y-m-d\TH:i:s\Z', strtotime('-9 days')), 'landing' => '/google-ads/?gclid=xyz&utm_campaign=crm-search', 'ref' => '', 'q' => ['gclid' => 'xyz', 'utm_campaign' => 'crm-search', 'utm_term' => 'crm for agencies']],
            'last' => null, 'first_channel' => 'Google Ads', 'pages' => 2, 'form_page' => '/google-ads/', 'placement' => 'service-page', 'device' => 'Desktop', 'country' => 'US', 'tracked' => true],
        'message' => "Lead grade: B (5/10)\nServices: Google Ads and paid media\nVisitor country: US\nTimeline: Within 1 to 3 months\n\nOur cost per lead on Google Ads is too high and we want more demo bookings."]),
    fixture_lead(['id' => '44', 'created_at' => '2026-09-12 10:00:00+05:30', 'name' => 'Old Lead', 'email' => 'old@example.com', 'mobile' => '9876543210', 'company' => 'Legacy Co', 'service' => 'Branding',
        'status' => 'won', 'value' => '75000', 'channel' => '', 'channel_group' => '', 'attribution' => [], 'grade' => '', 'score' => null, 'form_page' => '', 'country' => '',
        'message' => 'Need a new logo and brand for our bakery.']),
    fixture_lead(['id' => '45', 'source' => 'newsletter', 'name' => 'Sub Scriber', 'email' => 'sub@example.com', 'mobile' => '', 'company' => '', 'service' => 'Newsletter signup', 'message' => '',
        'channel' => 'Direct', 'channel_group' => 'Direct', 'grade' => '', 'attribution' => ['channel' => 'Direct', 'group' => 'Direct', 'detail' => '', 'first' => null, 'last' => null, 'pages' => 1, 'form_page' => '/', 'placement' => 'newsletter', 'device' => 'Desktop', 'country' => 'IN', 'tracked' => false]]),
    fixture_lead(['id' => '46', 'source' => 'audit-consultation', 'name' => 'Audit Person', 'email' => 'audit@example.com', 'service' => 'Free SEO Audit Page consultation', 'message' => 'Site score 48 for example.com',
        'channel' => 'ChatGPT', 'channel_group' => 'AI assistants', 'grade' => 'C']),
];
$db->proposals = [
    ['id' => 1, 'enquiry_id' => '43', 'parent_id' => null, 'token' => str_repeat('a', 32), 'kind' => 'proposal', 'subject' => 'BrightCRM: a plan to lower your cost per enquiry',
        'body' => "Hi Dan,\n\n## What we heard\n- You want more demos.\n\nBest regards,\nKalpana V", 'to_email' => 'dan@brightcrm.com', 'sent_by' => 'admin', 'resend_id' => 're_1',
        'sent_at' => date('Y-m-d H:i:sP', strtotime('-4 days')), 'opened_at' => date('Y-m-d H:i:sP', strtotime('-4 days')), 'open_count' => 3, 'viewed_at' => date('Y-m-d H:i:sP', strtotime('-3 days')),
        'view_count' => 2, 'read_seconds' => 260, 'accepted_at' => date('Y-m-d H:i:sP', strtotime('-1 days')), 'accept_note' => "Good time to talk: Thursday 4 pm\nCan we include LinkedIn too?", 'last_viewed_at' => date('Y-m-d H:i:sP', strtotime('-1 days'))],
    ['id' => 2, 'enquiry_id' => '43', 'parent_id' => 1, 'token' => str_repeat('b', 32), 'kind' => 'followup', 'subject' => 'Re: BrightCRM: a plan to lower your cost per enquiry',
        'body' => "Hi Dan,\n\nChecking in.\n\nBest regards,\nKalpana V", 'to_email' => 'dan@brightcrm.com', 'sent_by' => 'admin', 'resend_id' => 're_2',
        'sent_at' => date('Y-m-d H:i:sP', strtotime('-2 days')), 'opened_at' => null, 'open_count' => 0, 'viewed_at' => null, 'view_count' => 0, 'read_seconds' => 0, 'accepted_at' => null, 'accept_note' => '', 'last_viewed_at' => null],
];

/* Meetings: an upcoming call Dan moved once and accepted, a request Priya has not booked yet, and a call she cancelled. */
$meeting = static function (array $o): array {
    $now = date('Y-m-d H:i:sP');
    return $o + ['enquiry_id' => null, 'token' => '', 'status' => 'requested', 'name' => '', 'email' => '', 'phone' => '', 'company' => '', 'notes' => '', 'time_zone' => '',
        'start_at' => null, 'end_at' => null, 'event_id' => '', 'ical_uid' => '', 'event_link' => '', 'meet_url' => '', 'sequence' => 0, 'rsvp' => '', 'reschedules' => 0,
        'reminder_ids' => '[]', 'request_subject' => '', 'request_body' => '', 'requested_by' => '', 'requested_at' => null, 'request_mail_id' => '', 'opened_at' => null,
        'viewed_at' => null, 'booked_at' => null, 'cancelled_at' => null, 'cancelled_by' => '', 'cancel_reason' => '', 'created_at' => $now, 'updated_at' => $now];
};
$soon = intdiv(time() + 2 * 86400, 1800) * 1800;
$db->meetings = [
    $meeting(['id' => 1, 'enquiry_id' => 43, 'token' => str_repeat('c', 32), 'status' => 'booked', 'name' => 'Dan Cole', 'email' => 'dan@brightcrm.com', 'phone' => '+1 415 555 0132',
        'company' => 'BrightCRM', 'notes' => 'Can we include LinkedIn too?', 'time_zone' => 'America/Los_Angeles', 'start_at' => date('Y-m-d H:i:sP', $soon), 'end_at' => date('Y-m-d H:i:sP', $soon + 1800),
        'event_id' => 'ev9', 'ical_uid' => 'ev9@google.com', 'event_link' => 'https://www.google.com/calendar/event?eid=ev9', 'meet_url' => 'https://meet.google.com/xyz-abcd-efg', 'sequence' => 1,
        'rsvp' => 'accepted', 'reschedules' => 1, 'request_subject' => 'Dan, a quick call about Google Ads?', 'requested_by' => 'admin', 'requested_at' => date('Y-m-d H:i:sP', strtotime('-3 days')),
        'opened_at' => date('Y-m-d H:i:sP', strtotime('-3 days')), 'viewed_at' => date('Y-m-d H:i:sP', strtotime('-3 days')), 'booked_at' => date('Y-m-d H:i:sP', strtotime('-3 days'))]),
    $meeting(['id' => 2, 'enquiry_id' => 42, 'token' => str_repeat('d', 32), 'status' => 'requested', 'name' => 'priya raman', 'email' => 'priya@acmelabels.in', 'company' => 'Acme Labels',
        'request_subject' => 'Priya, a quick call about SEO?', 'requested_by' => 'admin', 'requested_at' => date('Y-m-d H:i:sP', strtotime('-1 days')), 'opened_at' => date('Y-m-d H:i:sP', strtotime('-20 hours'))]),
    $meeting(['id' => 3, 'enquiry_id' => 42, 'token' => str_repeat('e', 32), 'status' => 'cancelled', 'name' => 'priya raman', 'email' => 'priya@acmelabels.in', 'company' => 'Acme Labels', 'time_zone' => 'Asia/Kolkata',
        'start_at' => date('Y-m-d H:i:sP', strtotime('-5 days 11:00')), 'end_at' => date('Y-m-d H:i:sP', strtotime('-5 days 11:30')), 'cancelled_by' => 'lead', 'cancel_reason' => 'Found another agency',
        'cancelled_at' => date('Y-m-d H:i:sP', strtotime('-6 days')), 'created_at' => date('Y-m-d H:i:sP', strtotime('-8 days'))]),
];
