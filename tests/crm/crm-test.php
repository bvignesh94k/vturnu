<?php
declare(strict_types=1);
ob_start();
require '/t/boot.php';

$dash = static fn(string $s): bool => (bool) preg_match('/[\x{2013}\x{2014}]/u', $s);

/* ---------------- dashes ---------------- */
check('em dash becomes comma', crm_no_dashes("fast \u{2014} clear") === 'fast, clear', crm_no_dashes("fast \u{2014} clear"));
check('en dash range becomes to', crm_no_dashes("10\u{2013}20 days") === '10 to 20 days', crm_no_dashes("10\u{2013}20 days"));

/* ---------------- channels ---------------- */
$c = static fn(array $t): array => crm_classify_touch($t + ['ref' => '', 'q' => []]);
check('gclid is Google Ads', $c(['q' => ['gclid' => 'abc']])[0] === 'Google Ads');
check('chatgpt utm is ChatGPT', $c(['q' => ['utm_source' => 'chatgpt.com']]) === ['ChatGPT', 'AI assistants', ''], json_encode($c(['q' => ['utm_source' => 'chatgpt.com']])));
check('google referrer is organic', $c(['ref' => 'https://www.google.co.in/'])[0] === 'Google search');
check('gemini before google', $c(['ref' => 'https://gemini.google.com/app'])[0] === 'Gemini');
check('perplexity', $c(['ref' => 'https://www.perplexity.ai/search?q=x'])[1] === 'AI assistants');
check('linkedin social', $c(['ref' => 'https://www.linkedin.com/feed/']) === ['LinkedIn', 'Social', '']);
check('facebook cpc', $c(['q' => ['utm_source' => 'facebook', 'utm_medium' => 'cpc', 'utm_campaign' => 'Diwali']]) === ['Facebook Ads', 'Paid ads', 'Diwali'], json_encode($c(['q' => ['utm_source' => 'facebook', 'utm_medium' => 'cpc', 'utm_campaign' => 'Diwali']])));
check('google cpc', $c(['q' => ['utm_source' => 'google', 'utm_medium' => 'cpc']])[0] === 'Google Ads');
check('email medium', $c(['q' => ['utm_source' => 'newsletter', 'utm_medium' => 'email']])[0] === 'Email');
check('business profile', $c(['q' => ['utm_source' => 'google', 'utm_medium' => 'organic', 'utm_campaign' => 'gbp']])[0] === 'Google Business Profile');
check('directory', $c(['ref' => 'https://clutch.co/profile/vturnu'])[0] === 'Directory: clutch.co');
check('referral keeps host', $c(['ref' => 'https://example.org/blog/post']) === ['Referral', 'Referral', 'example.org']);
check('direct', $c([]) === ['Direct', 'Direct', '']);

/* ---------------- attribution from a post ---------------- */
$json = json_encode([
    'first' => ['at' => '2026-10-30T10:00:00.000Z', 'landing' => '/seo-services/?utm_source=x', 'ref' => 'https://www.google.com/', 'q' => []],
    'last' => ['at' => '2026-11-02T08:00:00.000Z', 'landing' => '/pricing/', 'ref' => 'https://www.linkedin.com/', 'q' => ['evil' => '<script>']],
    'pages' => 6,
]);
$a = crm_attribution(['attribution' => $json, 'from_page' => '/contact-us/', 'lead_source' => 'popup']);
check('channel is the latest source', $a['channel'] === 'LinkedIn' && $a['first_channel'] === 'Google search', json_encode($a));
check('unknown params dropped', $a['last']['q'] === []);
check('form page, placement, device, country', $a['form_page'] === '/contact-us/' && $a['placement'] === 'popup' && $a['device'] === 'Mobile' && $a['country'] === 'IN');
$bad = crm_attribution(['attribution' => json_encode(['first' => ['landing' => 'javascript:alert(1)', 'ref' => 'javascript:x', 'at' => 'nope']]), 'from_page' => '<x>']);
check('bad values rejected', $bad['first']['landing'] === '' && $bad['first']['ref'] === '' && $bad['first']['at'] === '' && $bad['form_page'] === '', json_encode($bad));
check('no record is Unknown', crm_attribution([])['channel'] === 'Unknown');
check('source line reads well', str_contains(crm_source_line($a), 'LinkedIn · first visit 30 Oct on /seo-services/ via Google search · 6 pages viewed · sent from the quote pop-up on /contact-us/'), crm_source_line($a));

/* ---------------- facts and intent ---------------- */
$lead = fixture_lead();
$f = crm_lead_facts($lead);
check('first name capitalised', $f['first'] === 'Priya', $f['first']);
check('company and domain split', $f['company'] === 'Acme Labels' && $f['domain'] === 'acmelabels.in', $f['company'] . ' / ' . $f['domain']);
check('summary fields', $f['timeline'] === 'As soon as possible' && $f['size'] === '51 to 200 people' && $f['country'] === 'IN');
check('their words', str_starts_with($f['note'], 'Hi team.'), $f['note']);
$in = crm_intent($f);
check('service key', $in['service'] === 'seo');
check('leads goal first', $in['goals'][0] === 'leads', json_encode($in['goals']));
check('industry', $in['industry'] === 'manufacturing', $in['industry']);
check('horizon', $in['horizon'] === 'the next six months', $in['horizon']);
check('target', $in['target'] === '50 qualified leads a month', $in['target']);
check('no market from the pronoun us', $in['markets'] === [] && !in_array('global', $in['goals'], true), json_encode($in));
check('quote', str_starts_with($in['quote'], 'We manufacture self adhesive labels'), $in['quote']);
check('urgent', $in['urgent'] === true);

/* ---------------- the proposal ---------------- */
$d = crm_proposal_draft($lead);
$b = $d['body'];
check('subject', $d['subject'] === 'Acme Labels: a plan to win more enquiries from Google', $d['subject']);
foreach (['Hi Priya,', '## What we heard', 'In your words: "We manufacture', 'win more qualified enquiries', 'within the next six months', 'Your target is 50 qualified leads',
          'as soon as possible', '51 to 200 people', 'acmelabels.in', '## How we would approach it', 'buyers and procurement teams', '## Proof from similar work',
          '**Sai Impression and Packaging Solutions** (Manufacturing)', 'https://vturnu.com/case-studies/sai-impression-label-manufacturer/', '## Investment',
          'You mentioned a budget of ₹50,000 to ₹1,50,000 a month', 'no lock-in after the first 90 days', '## Next step', 'Since you want to move quickly'] as $needle) {
    check('proposal has: ' . $needle, str_contains($b, $needle), $needle);
}
check('no dashes in proposal', !$dash($d['subject'] . $b));
check('no placeholders left', !str_contains($b, '{'), $b);
check('proof figures exact', str_contains($b, '92.9K Google impressions in 3 months') || str_contains($b, '1.32K Google clicks in 3 months'), $b);

$ads = crm_proposal_draft(fixture_lead(['id' => '43', 'email' => 'dan@brightcrm.com', 'name' => 'Dan Cole', 'country' => 'US', 'company' => 'BrightCRM', 'service' => 'Google Ads and paid media', 'budget' => 'Not decided yet',
    'message' => "Lead grade: B (5/10)\nServices: Google Ads and paid media\nVisitor country: US\nTimeline: Just researching\n\nOur cost per lead on Google Ads is too high and we want more demo bookings."]));
check('ads: no proof claimed', !str_contains($ads['body'], '## Proof'), $ads['body']);
check('ads: ad spend note', str_contains($ads['body'], 'Ad spend is separate and paid directly to the ad platform.'));
check('ads: dollar minimum', str_contains($ads['body'], 'Our plans start from US$550 a month'), $ads['body']);
check('ads: researching line', str_contains($ads['body'], 'still exploring options'));
check('ads: weekly report promise', str_contains($ads['body'], 'A weekly report in plain language: what we spent, what came back and what we changed.'));

$web = crm_proposal_draft(fixture_lead(['id' => '44', 'service' => 'Website design and development', 'company' => 'Kaveri Clinic',
    'message' => "Lead grade: B (5/10)\nServices: Website design and development\n\nWe do not have a website yet and need one for our dental clinic before the launch next month."]));
check('web: new site headline', str_ends_with($web['subject'], 'a website built to win enquiries from day one'), $web['subject']);
check('web: clean slate', str_contains($web['body'], 'You do not have a website yet'));
check('web: patients', str_contains($web['body'], 'what your patients need to see'), $web['body']);

$ai = crm_proposal_draft(fixture_lead(['id' => '45', 'service' => 'AI search visibility', 'company' => 'Parts Hub', 'message' => "Lead grade: B (5/10)\n\nWe want ChatGPT and Perplexity to recommend us when people ask for used car parts."]));
check('ai: FirstChoice proof first', str_contains($ai['body'], '**FirstChoice Used Auto Parts**'), $ai['body']);
check('ai: citations figure', str_contains($ai['body'], '10.7K citations in Bing and Copilot AI answers'), $ai['body']);

$erp = crm_proposal_draft(fixture_lead(['id' => '46', 'service' => 'Odoo ERP', 'company' => 'Metro Traders', 'message' => "Lead grade: C (2/10)\n\nWe need Odoo implemented for inventory and invoicing across two warehouses."]));
check('erp: product headline', $erp['subject'] === 'Metro Traders: your Odoo ERP implementation plan', $erp['subject']);
check('erp: product in plan', str_contains($erp['body'], 'Odoo ERP set up to fit how Metro Traders works'));
check('erp: no proof claimed', !str_contains($erp['body'], '## Proof'));

$low = crm_proposal_draft(fixture_lead(['id' => '47', 'budget' => 'Under ₹50,000 / month (about $550)']));
check('low budget: starts small', str_contains($low['body'], 'Our plans start from ₹50,000 a month. Within the budget you mentioned, I would start with the technical clean-up'), $low['body']);

$variants = array_unique(array_map(static fn(int $i): string => explode("\n", crm_proposal_draft(fixture_lead(['id' => (string) $i, 'email' => "p$i@x.com"]))['body'])[2], range(1, 12)));
check('openings vary between leads', count($variants) >= 2, (string) count($variants));

/* ---------------- rendering ---------------- */
$html = crm_markup_html("## Plan\n- **Bold:** see https://vturnu.com/x.\n\nA <script> line");
check('heading', str_contains($html, '<h2 style="'), $html);
check('bullet with bold', str_contains($html, '<li style="') && str_contains($html, '<strong>Bold:</strong>'));
check('link keeps the period outside', str_contains($html, 'https://vturnu.com/x</a>.'), $html);
check('escaped', str_contains($html, 'A &lt;script&gt; line'));
$txt = crm_markup_text("## Investment\n- One\n**Two**");
check('text version', $txt === "INVESTMENT\n  * One\nTwo", json_encode($txt));

/* ---------------- sending ---------------- */
$GLOBALS['captured_mail'] = [];
$GLOBALS['FAKE_DB']->leads = [fixture_lead()];
$r = crm_send(crm_lead(42), 'proposal', $d['subject'], $d['body'] . " \u{2014} end", ['stage' => true, 'remind_days' => 3, 'copy' => true, 'by' => 'admin']);
check('send ok', $r['ok'] && preg_match('/^[a-f0-9]{32}$/', $r['token']) === 1, json_encode($r));
$m = $GLOBALS['captured_mail'][0] ?? [];
check('from a person at connect@', ($m['from'] ?? '') === 'Kalpana V from VTurnU <connect@vturnu.com>', (string) ($m['from'] ?? ''));
check('to, reply-to, copy', ($m['to'] ?? []) === ['priya@acmelabels.in'] && ($m['reply_to'] ?? '') === 'connect@vturnu.com' && ($m['bcc'] ?? []) === ['connect@vturnu.com']);
check('html links the web proposal and pixel', str_contains($m['html'] ?? '', '/proposal/' . $r['token'] . '/') && str_contains($m['html'] ?? '', '/p/o/' . $r['token'] . '.gif'));
check('text has the link', str_contains($m['text'] ?? '', 'Your proposal online: https://vturnu.com/proposal/' . $r['token'] . '/'));
check('dash removed on send', !$dash(($m['html'] ?? '') . ($m['text'] ?? '') . ($m['subject'] ?? '')));
$stored = end($GLOBALS['FAKE_DB']->proposals);
check('proposal recorded', ($stored['token'] ?? '') === $r['token'] && $stored['kind'] === 'proposal');
$upd = array_values(array_filter($GLOBALS['FAKE_DB']->log, static fn($x) => str_starts_with(trim($x[0]), 'UPDATE enquiries SET contacted_at')));
check('lead moved to proposal with a follow-up date', $upd && str_contains($upd[0][0], "status = 'proposal'") && $upd[0][1][0] === date('Y-m-d', strtotime('+3 days')), json_encode($upd));

$fu = crm_followup_draft(fixture_lead(), $stored);
check('follow-up subject', $fu['subject'] === 'Re: ' . $d['subject'], $fu['subject']);
check('follow-up has a tip and two days', str_contains($fu['body'], 'page two') && preg_match('/Would (Monday|Tuesday|Wednesday|Thursday|Friday) or (Monday|Tuesday|Wednesday|Thursday|Friday) work/', $fu['body']) === 1, $fu['body']);

$ack = crm_ack_draft(fixture_lead());
check('ack', $ack['subject'] === 'Priya, thank you for your enquiry' && str_contains($ack['body'], 'within one working day') && str_contains($ack['body'], 'look at acmelabels.in'), $ack['body']);
$GLOBALS['captured_mail'] = [];
crm_auto_ack(fixture_lead());
crm_auto_ack(fixture_lead(['source' => 'newsletter']));
check('ack only for sales forms', count($GLOBALS['captured_mail']) === 1 && $GLOBALS['captured_mail'][0]['to'] === ['priya@acmelabels.in'] && !str_contains($GLOBALS['captured_mail'][0]['html'], '/p/o/'));

/* ---------------- the signature, as the owner designed it ---------------- */
$sig = $m['html'] ?? '';
foreach (['/assets/img/email/sig-logo.png', 'href="https://www.linkedin.com/company/vturnu"', 'href="https://www.instagram.com/vturnu"', 'href="https://www.facebook.com/VTurnUDigital"',
          'href="https://x.com/VTurnU_Digital"', 'alt="LinkedIn"', 'alt="X"', 'href="https://wa.me/919363731498"', '>+91 93637 31498</a>', 'href="mailto:connect@vturnu.com"',
          'www.vturnu.com &nbsp;&rarr;</a>', 'VTurnU Digital Solutions LLP', 'Web Development</span><br><span', 'IT Consulting</span>', 'Better Growth</span>', 'GSTIN: 33ABAFV8139L2ZS'] as $needle) {
    check('signature has: ' . $needle, str_contains($sig, $needle), $needle);
}
check('signature is one horizontal row that never stacks', str_contains($sig, '<table role="presentation" width="600"') && str_contains($sig, '<td width="206" valign="top"') && !str_contains($sig, 'display:inline-block;vertical-align:top;width:100%'));
foreach (['WhatsApp: +91 93637 31498 (https://wa.me/919363731498)', 'LinkedIn: https://www.linkedin.com/company/vturnu', 'X: https://x.com/VTurnU_Digital', 'Website: https://vturnu.com', 'GSTIN: 33ABAFV8139L2ZS'] as $needle) {
    check('text signature has: ' . $needle, str_contains($m['text'] ?? '', $needle), $needle);
}
/* The owner's call: no "Warm regards, name, title" above the signature in any draft. */
foreach (['proposal' => $d['body'], 'follow-up' => crm_followup_draft(fixture_lead(), $stored)['body'], 'acknowledgement' => crm_ack_draft(fixture_lead())['body']] as $kind => $text) {
    check("no sign-off in the $kind", !preg_match('/regards,|Looking forward to it,|\nKalpana V\b|\nFounder$/i', $text), substr($text, -80));
}
check('no GSTIN for clients abroad', !str_contains(crm_signature_html(crm_settings(), 'US'), 'GSTIN') && !str_contains(crm_signature_text(crm_settings(), 'US'), 'GSTIN'));
check('whatsapp needs a country code', crm_whatsapp_url('+91 93637 31498') === 'https://wa.me/919363731498' && crm_whatsapp_url('93637 31498') === '');

/* ---------------- the owner's notification ---------------- */
$GLOBALS['captured_mail'] = [];
mail_enquiry(['date' => date('c'), 'id' => '42', 'attribution' => $a] + fixture_lead());
$n = $GLOBALS['captured_mail'][0] ?? [];
check('notification subject has the channel', str_ends_with((string) ($n['subject'] ?? ''), 'via LinkedIn'), (string) ($n['subject'] ?? ''));
check('notification body has the source and links', str_contains((string) ($n['text'] ?? ''), 'Came from:') && str_contains((string) ($n['text'] ?? ''), '/admin/?view=proposal&id=42') && str_contains((string) ($n['text'] ?? ''), 'Form:             quote pop-up'), (string) ($n['text'] ?? ''));

/* ---------------- client routes ---------------- */
ob_start();
$handled = crm_route('p/o/' . $r['token'] . '.gif');
$gif = ob_get_clean();
check('pixel answers with a gif', $handled && str_starts_with($gif, 'GIF89a'));
check('pixel counts the open', (bool) array_filter($GLOBALS['FAKE_DB']->log, static fn($x) => str_contains($x[0], 'open_count = open_count + 1')));
ob_start();
crm_route('proposal/' . $r['token']);
$page = ob_get_clean();
check('web proposal renders', str_contains($page, 'Ready to go ahead?') && str_contains($page, 'What we heard') && str_contains($page, 'navigator.sendBeacon'), substr($page, 0, 300));
check('web proposal is noindex', str_contains($page, 'noindex, nofollow'));
$_GET['preview'] = substr(hash_hmac('sha256', 'preview|' . $r['token'], SECURITY_SECRET), 0, 24);
ob_start();
crm_route('proposal/' . $r['token']);
$prev = ob_get_clean();
unset($_GET['preview']);
check('preview does not track', str_contains($prev, 'does not count as a read') && !str_contains($prev, 'sendBeacon'));
ob_start();
crm_route('proposal/' . str_repeat('0', 32));
$nf = ob_get_clean();
check('unknown token is a 404 page', str_contains($nf, 'not valid'));
check('other paths pass through', crm_route('seo-services') === false);
ob_start();
crm_route('cron/crm-daily');
$cron = ob_get_clean();
check('cron needs the secret', $cron === '{"ok":false}', $cron);

/* ---------------- digest ---------------- */
$GLOBALS['captured_mail'] = [];
$dg = crm_daily_digest();
check('quiet day sends nothing', $dg['sent'] === false && !$GLOBALS['captured_mail']);
$dg = crm_daily_digest(true);
check('forced digest sends', $dg['sent'] === true && str_starts_with((string) $GLOBALS['captured_mail'][0]['subject'], 'Leads today'));

done();
ob_end_flush();
