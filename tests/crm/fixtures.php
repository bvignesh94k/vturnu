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
