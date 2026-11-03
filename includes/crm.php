<?php
/**
 * The CRM engine: where each lead came from, what they want, and the
 * proposal, follow-up and acknowledgement emails written for them.
 *
 * Proposals are drafted from the lead's own words and choices, then read and
 * edited by a person in the admin before anything is sent. They go out from
 * connect@vturnu.com through Resend, with a private web copy of the proposal
 * that records when it is read and lets the client say yes in one click.
 *
 * Every figure quoted as proof comes from includes/data/cases-real.php, where
 * each number is measured and dated. Nothing here invents results, and nothing
 * sent to a client carries an em dash (the owner's rule).
 */

declare(strict_types=1);

/* ------------------------------------------------------------------ */
/* Text hygiene                                                        */
/* ------------------------------------------------------------------ */

/** No em or en dashes in anything a client reads: ranges become "to", the rest commas. */
function crm_no_dashes(string $s): string
{
    $s = (string) preg_replace('/(\d)\s*[\x{2013}\x{2014}]\s*(\d)/u', '$1 to $2', $s);
    $s = (string) preg_replace('/\s*[\x{2014}\x{2015}]\s*/u', ', ', $s);
    $s = (string) preg_replace('/\s+\x{2013}\s+/u', ', ', $s);
    return str_replace("\u{2013}", '-', $s);
}

/** One tidy line: no control characters, single spaces, cut at a word boundary. */
function crm_clean_line(string $s, int $max): string
{
    $s = trim((string) preg_replace('/\s+/u', ' ', (string) preg_replace('/[\x00-\x1F\x7F]/u', ' ', $s)));
    if (mb_strlen($s) <= $max) {
        return $s;
    }
    $cut = mb_substr($s, 0, $max);
    $space = mb_strrpos($cut, ' ');
    return rtrim($space !== false && $space > $max * 0.6 ? mb_substr($cut, 0, $space) : $cut, ' ,.;:') . '...';
}

/** The host of a URL without "www.", lower case, or ''. */
function crm_host(string $url): string
{
    $h = strtolower((string) (parse_url($url, PHP_URL_HOST) ?: ''));
    return (string) preg_replace('/^www\./', '', $h);
}

/** A site path as the browser reported it, or '' if it is not one. */
function crm_path(string $p): string
{
    $p = trim($p);
    return preg_match('#^/[^\s<>"\'\\\\]{0,300}$#', $p) ? $p : '';
}

/** A full http(s) URL as the browser reported it, or '' if it is not one. */
function crm_url(string $u): string
{
    $u = trim($u);
    return preg_match('#^https?://[^\s<>"\'\\\\]{1,300}$#i', $u) ? $u : '';
}

/* ------------------------------------------------------------------ */
/* Where a lead came from                                              */
/* ------------------------------------------------------------------ */

/** Campaign parameters and click IDs kept from a visitor's landing URL. */
const CRM_TRACKED_PARAMS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'gbraid', 'wbraid', 'msclkid', 'fbclid', 'li_fat_id', 'ttclid'];

/** Click IDs that name the ad platform on their own. */
const CRM_CLICK_IDS = ['gclid' => 'Google Ads', 'gbraid' => 'Google Ads', 'wbraid' => 'Google Ads', 'msclkid' => 'Microsoft Ads', 'li_fat_id' => 'LinkedIn Ads', 'ttclid' => 'TikTok Ads'];

/**
 * Referring sites and what each means. Checked in order, so AI assistants come
 * before the search engines that host them (gemini.google.com is not Google search).
 */
const CRM_REFERRERS = [
    ['/(^|\.)(chatgpt\.com|chat\.openai\.com|openai\.com)$/', 'ChatGPT', 'AI assistants'],
    ['/(^|\.)perplexity\.ai$/', 'Perplexity', 'AI assistants'],
    ['/(^|\.)(gemini\.google\.com|bard\.google\.com)$/', 'Gemini', 'AI assistants'],
    ['/(^|\.)(copilot\.microsoft\.com|copilot\.cloud\.microsoft)$/', 'Copilot', 'AI assistants'],
    ['/(^|\.)claude\.ai$/', 'Claude', 'AI assistants'],
    ['/(^|\.)(deepseek\.com|chat\.deepseek\.com)$/', 'DeepSeek', 'AI assistants'],
    ['/(^|\.)(you\.com|phind\.com|meta\.ai|grok\.com|chat\.mistral\.ai)$/', 'Other AI assistant', 'AI assistants'],
    ['/(^|\.)google\.[a-z.]{2,7}$/', 'Google search', 'Organic search'],
    ['/(^|\.)bing\.com$/', 'Bing search', 'Organic search'],
    ['/(^|\.)duckduckgo\.com$/', 'DuckDuckGo', 'Organic search'],
    ['/(^|\.)yahoo\.com$/', 'Yahoo search', 'Organic search'],
    ['/(^|\.)(yandex\.[a-z]{2,3}|baidu\.com|ecosia\.org|search\.brave\.com|startpage\.com|naver\.com|qwant\.com)$/', 'Other search engine', 'Organic search'],
    ['/(^|\.)(linkedin\.com|lnkd\.in)$/', 'LinkedIn', 'Social'],
    ['/(^|\.)(facebook\.com|fb\.com|fb\.me)$/', 'Facebook', 'Social'],
    ['/(^|\.)instagram\.com$/', 'Instagram', 'Social'],
    ['/(^|\.)(t\.co|twitter\.com|x\.com)$/', 'X (Twitter)', 'Social'],
    ['/(^|\.)(youtube\.com|youtu\.be)$/', 'YouTube', 'Social'],
    ['/(^|\.)reddit\.com$/', 'Reddit', 'Social'],
    ['/(^|\.)quora\.com$/', 'Quora', 'Social'],
    ['/(^|\.)(whatsapp\.com|wa\.me)$/', 'WhatsApp', 'Social'],
    ['/(^|\.)(mail\.google\.com|outlook\.live\.com|outlook\.office\.com|outlook\.office365\.com|mail\.yahoo\.com|mail\.zoho\.(com|in))$/', 'Email', 'Email'],
    ['/(^|\.)(clutch\.co|goodfirms\.co|designrush\.com|sortlist\.[a-z]{2,3}|justdial\.com|indiamart\.com|sulekha\.com|g2\.com|trustpilot\.com|yelp\.com|upcity\.com|themanifest\.com|agencyspotter\.com)$/', 'Directory', 'Referral'],
];

/** Readable names for common utm_source values. */
const CRM_SOURCE_NAMES = [
    'google' => 'Google', 'bing' => 'Bing', 'microsoft' => 'Microsoft', 'facebook' => 'Facebook', 'fb' => 'Facebook', 'meta' => 'Meta',
    'instagram' => 'Instagram', 'ig' => 'Instagram', 'linkedin' => 'LinkedIn', 'youtube' => 'YouTube', 'twitter' => 'X (Twitter)', 'x' => 'X (Twitter)',
    'whatsapp' => 'WhatsApp', 'newsletter' => 'Newsletter', 'email' => 'Email', 'quora' => 'Quora', 'reddit' => 'Reddit',
];

/** Where each form sits, in words. */
const CRM_PLACEMENTS = [
    'home-quote' => 'home page form', 'contact-page' => 'contact page form', 'popup' => 'quote pop-up', 'service-page' => 'service page form',
    'landing-page' => 'landing page form', 'audit-consultation' => 'audit page form', 'resource' => 'e-book download',
    'newsletter' => 'newsletter signup', 'seo-audit' => 'free SEO audit',
];

/** [channel, group] for a referring host, or null when it is not one we know. */
function crm_referrer_channel(string $host): ?array
{
    foreach (CRM_REFERRERS as [$re, $channel, $group]) {
        if (preg_match($re, $host)) {
            return [$channel, $group];
        }
    }
    return null;
}

/**
 * Name the channel behind one visit: [channel, group, detail].
 * Click IDs and campaign tags win over the referrer, because they are set on
 * purpose; ChatGPT tags the links it cites with utm_source=chatgpt.com.
 */
function crm_classify_touch(array $t): array
{
    $q = is_array($t['q'] ?? null) ? $t['q'] : [];
    $host = crm_host((string) ($t['ref'] ?? ''));
    $src = strtolower(trim((string) ($q['utm_source'] ?? '')));
    $med = strtolower(trim((string) ($q['utm_medium'] ?? '')));
    $camp = crm_clean_line((string) ($q['utm_campaign'] ?? ''), 80);
    $term = crm_clean_line((string) ($q['utm_term'] ?? ''), 80);
    $detail = $camp . ($camp !== '' && $term !== '' ? ', ' : '') . $term;

    foreach (CRM_CLICK_IDS as $key => $name) {
        if (!empty($q[$key])) {
            return [$name, 'Paid ads', $detail];
        }
    }
    if ($src !== '' || $med !== '') {
        $named = $src !== '' ? crm_referrer_channel(str_contains($src, '.') ? $src : $src . '.com') : null;
        $name = CRM_SOURCE_NAMES[$src] ?? ($named[0] ?? ucwords(str_replace(['_', '-'], ' ', $src !== '' ? $src : $med)));
        if (preg_match('/^(cpc|ppc|paid|paid[ _-]?(search|social|media)|display|cpm|cpv|banner|retargeting|remarketing|ads?)$/', $med)) {
            return [preg_replace('/ search$/', '', $name) . ' Ads', 'Paid ads', $detail];
        }
        if (preg_match('/^(e-?mail|newsletter)$/', $med) || in_array($src, ['newsletter', 'email', 'mailchimp', 'brevo', 'hubspot'], true)) {
            return ['Email', 'Email', $detail !== '' ? $detail : $name];
        }
        if (in_array($src, ['google', 'gmb', 'gbp'], true) && preg_match('/gmb|gbp|business|maps|local/', $camp . ' ' . $med)) {
            return ['Google Business Profile', 'Organic search', $detail];
        }
        if ($named) {
            return [$named[0] === 'Directory' ? 'Directory: ' . $src : $named[0], $named[1], $detail];
        }
        return [$name, str_contains($med, 'social') ? 'Social' : 'Campaigns', $detail];
    }
    if (!empty($q['fbclid'])) {
        return ['Facebook', 'Social', ''];
    }
    if ($host !== '') {
        $named = crm_referrer_channel($host);
        if ($named) {
            return [$named[0] === 'Directory' ? 'Directory: ' . $host : $named[0], $named[1], ''];
        }
        return ['Referral', 'Referral', $host];
    }
    return ['Direct', 'Direct', ''];
}

/** A visit as the browser stored it, checked field by field, or null. */
function crm_touch($t): ?array
{
    if (!is_array($t)) {
        return null;
    }
    $q = [];
    $in = is_array($t['q'] ?? null) ? $t['q'] : [];
    foreach (CRM_TRACKED_PARAMS as $k) {
        if (is_string($in[$k] ?? null) && $in[$k] !== '') {
            $q[$k] = crm_clean_line($in[$k], 150);
        }
    }
    $at = (string) ($t['at'] ?? '');
    return [
        'at' => preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}(:\d{2}(\.\d+)?)?Z$/', $at) ? $at : '',
        'landing' => crm_path((string) ($t['landing'] ?? '')),
        'ref' => crm_url((string) ($t['ref'] ?? '')),
        'q' => $q,
    ];
}

/** Desktop, Mobile or Tablet from a user agent. */
function crm_device(string $ua): string
{
    if ($ua === '') {
        return '';
    }
    if (preg_match('/iPad|Tablet|PlayBook|Silk|Kindle/i', $ua)) {
        return 'Tablet';
    }
    return preg_match('/Mobi|Android|iPhone|iPod/i', $ua) ? 'Mobile' : 'Desktop';
}

/**
 * Everything known about how a lead found the site, from the visit record the
 * browser kept (assets/js/main.js) and the form post itself. The channel is
 * the last campaign or referral before the enquiry; the first visit is kept
 * alongside, because the two often differ and both matter.
 */
function crm_attribution(array $post): array
{
    $raw = (string) ($post['attribution'] ?? '');
    $data = $raw !== '' && strlen($raw) <= 8000 ? json_decode($raw, true) : null;
    $data = is_array($data) ? $data : [];
    $first = crm_touch($data['first'] ?? null);
    $last = crm_touch($data['last'] ?? null);
    $main = $last ?? $first;
    [$channel, $group, $detail] = $main ? crm_classify_touch($main) : ['Unknown', 'Unknown', ''];
    return [
        'channel' => $channel,
        'group' => $group,
        'detail' => $detail,
        'first' => $first,
        'last' => $last,
        'first_channel' => $first ? crm_classify_touch($first)[0] : '',
        'pages' => max(0, min(9999, (int) ($data['pages'] ?? 0))),
        'form_page' => crm_path((string) ($post['from_page'] ?? '')),
        'placement' => preg_match('/^[a-z-]{2,40}$/', (string) ($post['lead_source'] ?? '')) ? (string) $post['lead_source'] : '',
        'device' => crm_device((string) ($_SERVER['HTTP_USER_AGENT'] ?? '')),
        'country' => function_exists('security_visitor_country') ? security_visitor_country() : '',
        'tracked' => $first !== null,
    ];
}

/** One readable line about where a lead came from, for emails and lists. */
function crm_source_line(array $a): string
{
    $parts = [($a['channel'] ?? '') !== '' ? $a['channel'] . (($a['detail'] ?? '') !== '' ? ' (' . $a['detail'] . ')' : '') : 'Not tracked'];
    $first = $a['first'] ?? null;
    if (is_array($first) && ($first['at'] ?? '') !== '') {
        $since = date('j M', (int) strtotime($first['at']));
        $parts[] = 'first visit ' . $since . (($first['landing'] ?? '') !== '' ? ' on ' . strtok($first['landing'], '?') : '')
            . (($a['first_channel'] ?? '') !== '' && $a['first_channel'] !== ($a['channel'] ?? '') ? ' via ' . $a['first_channel'] : '');
    }
    if (($a['pages'] ?? 0) > 0) {
        $parts[] = $a['pages'] . ' ' . ($a['pages'] === 1 ? 'page' : 'pages') . ' viewed';
    }
    $placement = CRM_PLACEMENTS[$a['placement'] ?? ''] ?? '';
    if ($placement !== '' || ($a['form_page'] ?? '') !== '') {
        $parts[] = 'sent from the ' . ($placement !== '' ? $placement : 'form') . (($a['form_page'] ?? '') !== '' ? ' on ' . strtok($a['form_page'], '?') : '');
    }
    return implode(' · ', $parts);
}

/* ------------------------------------------------------------------ */
/* What the lead wants                                                 */
/* ------------------------------------------------------------------ */

/**
 * The facts a proposal is written from. The stored message starts with the
 * summary enquiry_qualify() adds ("Lead grade: ...", "Website: ..."), then the
 * visitor's own words after a blank line; both halves are used.
 */
function crm_lead_facts(array $l): array
{
    $msg = str_replace("\r\n", "\n", (string) ($l['message'] ?? ''));
    $summary = [];
    $note = $msg;
    if (preg_match('/^Lead grade: /', $msg)) {
        $halves = preg_split("/\n\s*\n/", $msg, 2) ?: [$msg];
        $note = $halves[1] ?? '';
        foreach (explode("\n", $halves[0]) as $line) {
            if (preg_match('/^([^:]{2,30}):\s*(.*)$/', $line, $m)) {
                $summary[strtolower(trim($m[1]))] = trim($m[2]);
            }
        }
    }
    $given = static fn(string $k): string => in_array($summary[$k] ?? '', ['', 'Not given', 'Not specified', 'Not recorded'], true) ? '' : $summary[$k];
    $companyRaw = (string) ($l['company'] ?? '');
    $website = preg_match('/\((https?:\/\/[^)\s]+)\)\s*$/', $companyRaw, $wm) ? $wm[1] : $given('website');
    $company = trim((string) preg_replace('/\s*\((https?:\/\/[^)\s]+)\)\s*$/', '', $companyRaw));
    if (preg_match('/^(n\/?a|none|na|-|self|personal|individual|freelancer?)$/i', $company)) {
        $company = '';
    }

    $name = crm_clean_line((string) ($l['name'] ?? ''), 80);
    $first = '';
    foreach (preg_split('/\s+/', $name) ?: [] as $w) {
        $w = trim($w, '.,');
        if ($w !== '' && !preg_match('/^(mr|mrs|ms|miss|dr|er|prof|sri|shri|smt)$/i', $w) && mb_strlen($w) > 1) {
            $first = $w;
            break;
        }
    }
    if ($first !== '' && ($first === mb_strtolower($first) || $first === mb_strtoupper($first))) {
        $first = mb_convert_case(mb_strtolower($first), MB_CASE_TITLE);
    }
    $country = strtoupper((string) ($l['country'] ?? ''));
    if (!preg_match('/^[A-Z]{2}$/', $country) && preg_match('/^([A-Z]{2})\b/', $given('visitor country'), $cm)) {
        $country = $cm[1];
    }
    return [
        'id' => (string) ($l['id'] ?? ''),
        'name' => $name,
        'first' => $first !== '' ? $first : 'there',
        'email' => (string) ($l['email'] ?? ''),
        'mobile' => (string) ($l['mobile'] ?? ''),
        'company' => crm_clean_line($company, 60),
        'website' => $website,
        'domain' => crm_host($website),
        'service' => (string) ($l['service'] ?? ''),
        'budget' => (string) ($l['budget'] ?? ''),
        'timeline' => $given('timeline'),
        'size' => $given('company size'),
        'markets' => $given('target markets'),
        'country' => preg_match('/^[A-Z]{2}$/', $country) ? $country : '',
        'note' => trim($note),
        'grade' => (string) ($l['grade'] ?? (preg_match('/Lead grade: ([ABC])/', $msg, $g) ? $g[1] : '')),
    ];
}

/** Goals people describe, the words that give them away, and how a proposal says it back. */
const CRM_GOALS = [
    'ai'          => ['/chat ?gpt|perplexity|gemini|copilot|\bai (search|overviews?|answers?|visibility|results)|answer engines?|\bgeo\b|\baeo\b|\bllms?\b|generative (search|engine)/', 'show up when buyers ask ChatGPT, Perplexity and Google\'s AI for a recommendation'],
    'leads'       => ['/\bleads?\b|enquir|inquir|prospects?|\bdemos?\b|appointments?|bookings?|pipeline|\brfqs?\b|quote requests?|more (calls|clients|customers)/', 'win more qualified enquiries'],
    'sales'       => ['/\bsales\b|revenue|\borders?\b|\bsell\b|selling|conversions?|\broas\b/', 'turn more of your visitors into paying customers'],
    'rank'        => ['/\brank(s|ing|ings)?\b|first page|page one|top of google|\bserps?\b|organic|search engines?|\bseo\b|keywords?/', 'rank on the first page for the searches your buyers make'],
    'traffic'     => ['/traffic|visitors|visibility|\breach\b|impressions/', 'bring more of the right people to your website'],
    'website_new' => ['/new website|build (a |an |our |my )?(new )?(website|site)|website from scratch|(don\'?t|do not) have a (website|site)|no website|launch(ing)? (a |our |the )?(website|site|store|brand)/', 'launch a website that wins business from day one'],
    'website_fix' => ['/redesign|re-?design|revamp|outdated|old (website|site)|slow|speed|loading|mobile friendly|bounce|user experience|\bux\b/', 'turn your website into a fast, modern sales tool'],
    'ecommerce'   => ['/e-?commerce|online (store|shop)|\bshopify\b|woocommerce|\bd2c\b|\bcart\b|products? online|sell online/', 'grow your online orders'],
    'brand'       => ['/\bbrand(ing)?\b|\blogo\b|identity|positioning|awareness|rebrand/', 'build a brand your buyers remember and trust'],
    'ads'         => ['/google ads|\bppc\b|\bads\b|adwords|meta ads|facebook ads|cost per (lead|click|acquisition)|\bcpl\b|\bcpc\b|\bcpa\b|ad spend|paid (media|campaigns?)/', 'get more enquiries from the same ad budget'],
    'social'      => ['/social media|instagram|linkedin|facebook|followers|engagement|\breels?\b|youtube/', 'build a social presence that brings in enquiries'],
    'content'     => ['/\bcontent\b|\bblogs?\b|articles?|copywrit|case stud/', 'publish content that ranks and convinces'],
    'global'      => ['/\b(usa|u\.s\.a?|united states|america|uk|united kingdom|uae|dubai|saudi|ksa|qatar|singapore|malaysia|europe|australia|canada|africa|middle east|gcc|global|international|overseas|exports?)\b/', 'reach buyers in new markets'],
    'erp'         => ['/\berp\b|odoo|erpnext|\bsap\b|\bcpq\b|salesforce|\bcrm\b|implementation|migrat|integrat/', 'get your systems working the way your business works'],
    'staff'       => ['/\bhir(e|ing)\b|\bstaff(ing)?\b|developers?|\bresources\b|team augmentation|contractors?|recruit/', 'add skilled people to your team quickly'],
    'automation'  => ['/chat ?bots?|automat|ai agents?|machine learning|\bml\b|workflows?|ai tools?|custom ai/', 'put AI to work on a real business task'],
];

/** Industries, the words that suggest them, and who their buyers are. */
const CRM_INDUSTRIES = [
    'manufacturing' => ['/manufactur|factory|industrial|machin|fabricat|packag|\blabels?\b|chemicals?|plastics?|\bsteel\b|textiles?|engineering|\boem\b|components?/', 'buyers and procurement teams'],
    'automotive'    => ['/automotive|\bauto\b|\bcars?\b|vehicles?|spare parts|used parts|tyres?|\bgarages?\b/', 'buyers'],
    'saas'          => ['/\bsaas\b|software|\bapps?\b|platform|start-?up|\btech\b|it services|\bcloud\b|\bapi\b|\berp\b|odoo/', 'software buyers'],
    'healthcare'    => ['/clinic|hospital|health|dental|pharma|medical|diagnostic|wellness|physio/', 'patients'],
    'realestate'    => ['/real estate|propert(y|ies)|\bbuilders?\b|realty|construction|interiors?|architect/', 'property buyers'],
    'education'     => ['/school|college|universit|institute|edtech|coaching|academy|\bcourses?\b/', 'students and parents'],
    'retail'        => ['/retail|fashion|apparel|jewel|cosmetic|beauty|furniture|\bd2c\b|consumer brand/', 'shoppers'],
    'hospitality'   => ['/restaurant|hotel|resort|\bcafe\b|catering|travel|\btours?\b/', 'guests'],
    'professional'  => ['/\blaw\b|legal|\bca firm|chartered|accounting|consult|finance|insurance|fintech|advis/', 'clients'],
    'logistics'     => ['/logistics|transport|shipping|freight|courier|warehous/', 'shippers'],
];

/** The drop-down choice, as a playbook key. */
function crm_service_key(string $service): string
{
    $map = [
        'SEO' => 'seo', 'AI search visibility' => 'aiseo', 'Google Ads and paid media' => 'ads', 'Social media marketing' => 'social',
        'Content marketing' => 'content', 'Email and outbound' => 'outbound', 'Branding' => 'brand', 'Website design and development' => 'web',
        'Ecommerce store' => 'ecom', 'Landing pages' => 'landing', 'AI development' => 'aidev', 'SAP CPQ' => 'erp', 'Salesforce' => 'erp',
        'Odoo ERP' => 'erp', 'ERPNext' => 'erp', 'IT staffing' => 'staffing', 'Full growth program' => 'growth', 'I need advice on where to start' => 'growth',
    ];
    foreach ($map as $label => $key) {
        if (strcasecmp(trim($service), $label) === 0 || str_starts_with($service, $label . ',')) {
            return $key;
        }
    }
    return function_exists('lead_service_option') && ($opt = lead_service_option($service)) !== '' && $opt !== $service ? crm_service_key($opt) : 'growth';
}

/**
 * Read intent from the visitor's own words, the drop-downs and the company:
 * goals in the order they matter, industry, horizon, targets and markets.
 */
function crm_intent(array $f): array
{
    $note = (string) $f['note'];
    $text = strtolower($note . ' ' . $f['service'] . ' ' . $f['company'] . ' ' . $f['domain'] . ' ' . $f['markets']);
    $words = strtolower($note);
    $key = crm_service_key($f['service']);

    $goals = [];
    foreach (CRM_GOALS as $g => [$re, $phrase]) {
        if (preg_match($re, $words)) {
            $goals[] = $g;
        }
    }
    $default = [
        'seo' => 'rank', 'aiseo' => 'ai', 'ads' => 'ads', 'social' => 'social', 'content' => 'content', 'outbound' => 'leads', 'brand' => 'brand',
        'web' => in_array('website_new', $goals, true) ? 'website_new' : 'website_fix', 'ecom' => 'ecommerce', 'landing' => 'leads', 'aidev' => 'automation',
        'erp' => 'erp', 'staffing' => 'staff', 'growth' => 'leads',
    ][$key];
    /* Business outcomes lead, then the service's own goal, then the rest. */
    $order = ['leads', 'sales', 'ecommerce', $default, 'ai', 'rank', 'traffic', 'website_new', 'website_fix', 'ads', 'brand', 'social', 'content', 'global', 'erp', 'staff', 'automation'];
    $goals = array_values(array_unique(array_merge(array_intersect($order, $goals), [$default])));
    if (in_array('website_new', $goals, true)) {
        $goals = array_values(array_diff($goals, ['website_fix']));
    }

    $industry = '';
    foreach (CRM_INDUSTRIES as $ind => [$re, $buyers]) {
        if (preg_match($re, $text)) {
            $industry = $ind;
            break;
        }
    }

    $horizon = '';
    $nums = ['one' => 1, 'two' => 2, 'three' => 3, 'four' => 4, 'six' => 6, 'nine' => 9, 'twelve' => 12];
    if (preg_match('/(?:in|within|next|over|by)\s+(?:the\s+)?(?:next\s+)?(\d{1,2}|one|two|three|four|six|nine|twelve)\s+(months?|weeks?)/', $words, $hm)) {
        $n = ctype_digit($hm[1]) ? (int) $hm[1] : ($nums[$hm[1]] ?? 0);
        if ($n > 0 && $n <= 24) {
            $unit = str_starts_with($hm[2], 'week') ? 'week' : 'month';
            $horizon = 'the next ' . ($n === 1 ? $unit : crm_number_word($n) . ' ' . $unit . 's');
        }
    }
    $target = '';
    if (preg_match('/(\d[\d,]*\+?)\s+((?:(?:more|new|qualified|monthly|good|genuine)\s+)*)(leads|enquiries|inquiries|orders|sales|customers|clients|visitors|users|bookings|appointments|demos|meetings|downloads)(\s+(?:a|per|every|each)\s+(?:month|week|day))?/', $words, $tm)) {
        $target = $tm[1] . ' ' . $tm[2] . $tm[3] . (isset($tm[4]) ? (string) preg_replace('/\s+(per|every|each)\s+/', ' a ', $tm[4]) : '');
    }

    /* Named markets only: "us" is almost always the pronoun, so it never counts. */
    $markets = [];
    $known = ['usa' => 'the US', 'u.s.' => 'the US', 'united states' => 'the US', 'america' => 'the US', 'uk' => 'the UK', 'united kingdom' => 'the UK',
        'uae' => 'the UAE', 'dubai' => 'the UAE', 'saudi' => 'Saudi Arabia', 'ksa' => 'Saudi Arabia', 'qatar' => 'Qatar', 'singapore' => 'Singapore',
        'malaysia' => 'Malaysia', 'indonesia' => 'Indonesia', 'australia' => 'Australia', 'canada' => 'Canada', 'europe' => 'Europe',
        'south africa' => 'South Africa', 'nigeria' => 'Nigeria', 'kenya' => 'Kenya', 'middle east' => 'the Middle East', 'gcc' => 'the GCC'];
    foreach ($known as $w => $label) {
        if (preg_match('/\b' . preg_quote($w, '/') . '\b/', $words . ' ' . strtolower($f['markets']))) {
            $markets[$label] = $label;
        }
    }

    return [
        'service' => $key,
        'goals' => $goals,
        'industry' => $industry,
        'buyers' => CRM_INDUSTRIES[$industry][1] ?? 'buyers',
        'horizon' => $horizon,
        'target' => $target,
        'markets' => array_values($markets),
        'quote' => crm_best_quote($note),
        'no_website' => (bool) preg_match('/(don\'?t|do not) have a (website|site)|no website|without a website/', $words),
        'urgent' => $f['timeline'] === 'As soon as possible' || (bool) preg_match('/\burgent|asap|immediately|right away\b/', $words),
    ];
}

/** 2 to "two", for prose. */
function crm_number_word(int $n): string
{
    return [1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five', 6 => 'six', 7 => 'seven', 8 => 'eight', 9 => 'nine', 10 => 'ten', 12 => 'twelve'][$n] ?? (string) $n;
}

/** The sentence from their note that says most about what they want, ready to quote back. */
function crm_best_quote(string $note): string
{
    $note = trim((string) preg_replace('/(https?:\/\/|www\.)\S+|\S+@\S+|\+?\d[\d\s-]{8,}\d/u', '', $note));
    $sentences = preg_split('/(?<=[.!?])\s+|\n+/u', $note) ?: [];
    $best = '';
    $bestScore = -1;
    foreach ($sentences as $i => $s) {
        $s = trim($s);
        $wordCount = count(preg_split('/\s+/', $s) ?: []);
        if ($wordCount < 5 || preg_match('/^(hi|hello|dear|thanks|thank you|regards)\b/i', $s)) {
            continue;
        }
        $score = 0;
        foreach (CRM_GOALS as [$re]) {
            $score += preg_match($re, strtolower($s)) ? 2 : 0;
        }
        $score += preg_match('/\b(want|need|looking|goal|aim|plan|help|grow|increase|improve)\b/i', $s) ? 2 : 0;
        $score -= $i;
        if ($score > $bestScore) {
            $best = $s;
            $bestScore = $score;
        }
    }
    $best = crm_clean_line(crm_no_dashes($best), 180);
    return rtrim($best, ' ');
}

/** A choice that stays the same for one lead but varies between leads, so no two proposals read alike. */
function crm_pick(array $options, string $seed): string
{
    return $options[abs(crc32($seed)) % count($options)];
}

/* ------------------------------------------------------------------ */
/* The playbooks behind each proposal                                  */
/* ------------------------------------------------------------------ */

/**
 * What VTurnU would do for each service: approach, the first months, how
 * progress is measured, which case studies prove it, and a tip for follow-ups.
 * Every promise here matches what the website already says.
 */
function crm_playbook(string $key): array
{
    $p = [
        'seo' => [
            'headline' => 'a plan to win more enquiries from Google',
            'approach' => [
                ['Technical clean-up', 'we fix what stops Google from crawling, understanding and ranking your pages: speed, structure, indexing and structured data.'],
                ['A buyer-intent keyword map', 'we find the searches {buyers} make when they are ready to act, and match each one to a page built to answer it.'],
                ['Pages that convert', 'service, product and location pages written to rank and to turn a visit into an enquiry.'],
                ['Authority', 'consistent business listings, mentions and links that show Google you are the trusted choice.'],
            ],
            'phases_title' => 'Your first 90 days',
            'phases' => [
                ['Weeks 1 to 2', 'a full audit of {site}, the keyword map, and the quick technical fixes.'],
                ['Weeks 3 to 8', 'new and improved pages, internal links and structured data, published in order of revenue potential.'],
                ['Weeks 9 to 12', 'content and authority building, and a review of what is moving so we double down on it.'],
            ],
            'measures' => [
                'Rankings and impressions for the searches that matter to your sales, not vanity keywords.',
                'Organic enquiries and calls, tracked back to the page they started on.',
                'A plain-language report every month: what we did, what moved and what comes next.',
            ],
            'proof' => ['cafy-careers-uk-job-platform', 'sai-impression-label-manufacturer', 'firstchoice-used-auto-parts'],
            'proof_words' => ['clicks', 'impressions'],
            'monthly' => true,
            'tip' => 'the fastest early gains in SEO usually come from pages that already sit on page two, so that is where we start.',
        ],
        'aiseo' => [
            'headline' => 'a plan to be the answer in ChatGPT and Google\'s AI',
            'approach' => [
                ['An AI visibility check', 'we ask ChatGPT, Perplexity, Gemini and Google AI Overviews the questions your {buyers} ask, and record where {company} appears and who appears instead.'],
                ['One clear set of facts', 'consistent details about {company} across your site, listings and profiles, so AI tools describe you correctly.'],
                ['Answer-ready pages', 'clear, quotable answers, FAQs and comparisons that AI assistants can cite.'],
                ['Structured data and crawler access', 'the markup and settings that let AI crawlers read and trust your pages.'],
            ],
            'phases_title' => 'Your first 90 days',
            'phases' => [
                ['Weeks 1 to 2', 'a baseline of how {company} shows up across the major AI assistants, and the fix list.'],
                ['Weeks 3 to 8', 'entity, structured data and content work on the pages that matter most.'],
                ['Weeks 9 to 12', 'building citations and re-testing the same questions every month.'],
            ],
            'measures' => [
                'How often {company} is named or cited for your core questions, tested the same way every month.',
                'Visits from ChatGPT, Perplexity, Copilot and Gemini, which we track for every enquiry.',
                'Google rankings and impressions, which move with the same work.',
            ],
            'proof' => ['firstchoice-used-auto-parts', 'sai-impression-label-manufacturer', 'cafy-careers-uk-job-platform'],
            'proof_words' => ['AI', 'Citations', 'impressions'],
            'monthly' => true,
            'tip' => 'AI assistants repeat the facts they find most consistently across the web, which is why we start by making your details agree everywhere.',
        ],
        'ads' => [
            'headline' => 'a plan to lower your cost per enquiry',
            'approach' => [
                ['Tracking first', 'conversion tracking that counts real enquiries and calls, so every decision is based on leads rather than clicks.'],
                ['An account built on buyer intent', 'campaigns structured around what people search when they are ready to buy, with negative keywords that stop you paying for the wrong searches.'],
                ['Landing pages that match the ad', 'one message from the search to the form, which is where most of the cost per lead is won or lost.'],
                ['Weekly optimisation', 'bids, search terms, ads and audiences reviewed every week.'],
            ],
            'phases_title' => 'Your first 90 days',
            'phases' => [
                ['Week 1', 'an account and tracking audit, a look at what competitors are running, and the campaign plan.'],
                ['Weeks 2 to 4', 'the launch or rebuild, landing page fixes and the first clean-up of search terms.'],
                ['Weeks 5 to 12', 'weekly optimisation and testing, then more budget behind what works.'],
            ],
            'measures' => [
                'Cost per qualified enquiry and the number of enquiries, campaign by campaign.',
                'Search terms and placements that waste budget, cut every week.',
                'A weekly report in plain language: what we spent, what came back and what we changed.',
            ],
            'proof' => [],
            'proof_words' => [],
            'monthly' => true,
            'ad_spend' => true,
            'tip' => 'the search terms report usually shows budget going to searches that will never buy, and cutting those is the first thing we do.',
        ],
        'social' => [
            'headline' => 'a social media plan that brings in enquiries',
            'approach' => [
                ['The right channels', 'we focus on the one or two platforms where your {buyers} actually spend time, rather than spreading thin.'],
                ['Content pillars', 'proof of your work, your expertise and the people behind {company}, planned a month ahead.'],
                ['Creative in your brand', 'designed posts, carousels and short videos that look unmistakably like you.'],
                ['From attention to enquiry', 'profiles, links and offers set up so a follower always has a clear next step.'],
            ],
            'phases_title' => 'Your first 90 days',
            'phases' => [
                ['Weeks 1 to 2', 'an audit of your profiles and competitors, the content plan and your brand templates.'],
                ['Weeks 3 to 8', 'regular publishing, replies to your community and a monthly review.'],
                ['Weeks 9 to 12', 'more of the formats that bring enquiries, with optional paid boosts behind the best posts.'],
            ],
            'measures' => [
                'Enquiries and profile visits that come from social.',
                'Engagement from the people who buy, not just likes.',
                'A plain-language report every month.',
            ],
            'proof' => [],
            'proof_words' => [],
            'monthly' => true,
            'tip' => 'in B2B, posts that show real projects and the people behind them tend to start more conversations than polished stock content.',
        ],
        'content' => [
            'headline' => 'a content plan that ranks and sells',
            'approach' => [
                ['Topics from real demand', 'subjects chosen from what your {buyers} search for and ask, not guesswork.'],
                ['Expert articles', 'in-depth pieces written with input from your team, so they sound like {company} and earn trust.'],
                ['Built to rank and to be cited', 'structure, internal links and FAQs that help both Google and AI assistants pick them up.'],
                ['Content that sells', 'case studies, comparisons and guides your sales team can send to prospects.'],
            ],
            'phases_title' => 'Your first 90 days',
            'phases' => [
                ['Weeks 1 to 2', 'a content audit, the topic map and the editorial calendar.'],
                ['Weeks 3 to 8', 'the first articles and pages published on a steady schedule.'],
                ['Weeks 9 to 12', 'refreshing and expanding what performs, with supporting pieces around it.'],
            ],
            'measures' => [
                'Impressions, rankings and clicks for every piece we publish.',
                'Enquiries that started on a content page.',
                'A plain-language report every month.',
            ],
            'proof' => ['floweazy-odoo-partner-website', 'cafy-careers-uk-job-platform'],
            'proof_words' => ['Blog', 'clicks', 'pages'],
            'monthly' => true,
            'tip' => 'one page that answers a buyer\'s question completely tends to do better than several thin ones, so we write fewer, deeper pieces.',
        ],
        'outbound' => [
            'headline' => 'an outbound plan to book more meetings',
            'approach' => [
                ['Your ideal customer profile', 'a sharp definition of who buys from {company}: industry, size, role and region.'],
                ['Clean, verified lists', 'contacts that match the profile, checked before anyone is emailed.'],
                ['Messages that sound human', 'short, relevant emails and LinkedIn messages that start conversations instead of pitching.'],
                ['Deliverability', 'domain and inbox setup with sensible sending limits, so your emails land in the inbox.'],
            ],
            'phases_title' => 'Your first 90 days',
            'phases' => [
                ['Weeks 1 to 2', 'the customer profile, the offer and messaging, and domain and inbox setup.'],
                ['Weeks 3 to 6', 'the first campaigns live, with replies routed straight to your team.'],
                ['Weeks 7 to 12', 'testing new angles, refining the list and scaling what earns replies.'],
            ],
            'measures' => [
                'Replies and booked meetings, by campaign.',
                'Inbox placement and sender reputation.',
                'A short report every week.',
            ],
            'proof' => [],
            'proof_words' => [],
            'monthly' => true,
            'tip' => 'short first emails about the buyer\'s problem get more replies than long introductions, so that is how every sequence we write begins.',
        ],
        'brand' => [
            'headline' => 'a brand your buyers will remember',
            'approach' => [
                ['Positioning', 'what {company} stands for, who it is for and why buyers should choose you, in a few clear sentences.'],
                ['Identity', 'logo, colours, typography and visual style built from that positioning.'],
                ['Messaging', 'taglines, key messages and proof points your team can use everywhere.'],
                ['Guidelines and assets', 'a brand guide and ready-to-use templates for the web, social media and sales.'],
            ],
            'phases_title' => 'How it would run',
            'phases' => [
                ['Weeks 1 to 2', 'a discovery workshop and a review of your market and competitors.'],
                ['Weeks 3 to 5', 'positioning and identity concepts, refined with your feedback.'],
                ['Weeks 6 to 8', 'the final identity, messaging and brand guidelines.'],
            ],
            'measures' => [
                'A brand your whole team uses the same way, everywhere.',
                'Stronger response to your website, campaigns and proposals.',
                'Clear guidelines, so every new piece looks and sounds like {company}.',
            ],
            'proof' => ['sai-impression-label-manufacturer'],
            'proof_words' => ['impressions', 'Pages'],
            'monthly' => false,
            'tip' => 'a clear one-line positioning statement does more for sales than a new logo on its own, which is why we start there.',
        ],
        'web' => [
            'headline' => 'a plan to turn your website into a sales tool',
            'headline_new' => 'a website built to win enquiries from day one',
            'approach' => [
                ['Discovery and structure', 'we map what your {buyers} need to see before they enquire, then plan the pages around it.'],
                ['Design that builds trust', 'a clean, modern design in your brand, with proof and a clear next step on every page.'],
                ['Built for speed and search', 'fast on mobile, technically sound for Google, and readable by AI search tools from launch.'],
                ['Easy for you to run', 'an admin your team can update, and enquiry forms that reach your inbox with spam filtered out.'],
            ],
            'phases_title' => 'How it would run',
            'phases' => [
                ['Weeks 1 to 2', 'discovery, the sitemap and wireframes.'],
                ['Weeks 3 to 6', 'design and development, with your review at each stage.'],
                ['Weeks 7 to 8', 'content, testing, speed checks and launch.'],
            ],
            'measures' => [
                'Mobile speed and Core Web Vitals, checked before launch.',
                'Enquiries from the website, tracked from the page they started on.',
                'How quickly visitors find what they came for.',
            ],
            'proof' => ['sai-impression-label-manufacturer', 'floweazy-odoo-partner-website'],
            'proof_words' => ['performance', 'Pages', 'PageSpeed'],
            'monthly' => false,
            'tip' => 'most enquiries are lost on mobile, so we design and test every page on a phone first.',
        ],
        'ecom' => [
            'headline' => 'an online store built to sell',
            'approach' => [
                ['A store built to sell', 'fast product pages, clear pricing and a short checkout.'],
                ['Catalogue and search', 'products organised the way your customers shop, with filters and rich product content.'],
                ['Payments, shipping and tax', 'set up for the markets you sell to, with order emails your customers trust.'],
                ['Ready to grow', 'a search-friendly structure, analytics and marketing integrations from day one.'],
            ],
            'phases_title' => 'How it would run',
            'phases' => [
                ['Weeks 1 to 2', 'the platform choice, catalogue structure and design direction.'],
                ['Weeks 3 to 7', 'design, build and product setup.'],
                ['Weeks 8 to 10', 'payment and shipping tests, speed checks and launch.'],
            ],
            'measures' => [
                'Conversion rate and average order value.',
                'Organic visits to product and category pages.',
                'Checkout completion on mobile and desktop.',
            ],
            'proof' => ['sai-impression-label-manufacturer'],
            'proof_words' => ['performance', 'Pages'],
            'monthly' => false,
            'tip' => 'most abandoned orders happen at checkout on a phone, so we keep it short and test it on real devices before launch.',
        ],
        'landing' => [
            'headline' => 'landing pages that turn clicks into enquiries',
            'approach' => [
                ['One page, one goal', 'each page built around a single offer and a single action.'],
                ['Message match', 'the headline repeats what the visitor searched for or clicked, so they know they are in the right place.'],
                ['Proof up front', 'results, client names and a clear reason to act now.'],
                ['Test and improve', 'tests on headlines, forms and offers, guided by real data.'],
            ],
            'phases_title' => 'How it would run',
            'phases' => [
                ['Week 1', 'the offer, the audience and the message.'],
                ['Weeks 2 to 3', 'copy, design and build, with tracking in place.'],
                ['Weeks 4 to 8', 'launch, then weekly tests and improvements.'],
            ],
            'measures' => [
                'Conversion rate and cost per enquiry.',
                'Form starts and completions.',
                'Results of every test, shared as we go.',
            ],
            'proof' => ['firstchoice-used-auto-parts', 'sai-impression-label-manufacturer'],
            'proof_words' => ['impressions', 'performance'],
            'monthly' => false,
            'tip' => 'the biggest lift on a landing page usually comes from making the headline match the ad that brought the visitor, so that is the first test we run.',
        ],
        'aidev' => [
            'headline' => 'a practical plan to put AI to work',
            'approach' => [
                ['Use case first', 'we start with the business task you want AI to handle and the result that would make it worth it.'],
                ['A fast prototype', 'a working proof of concept on your real data, so you can judge it before investing more.'],
                ['Safe integration', 'connected to the tools you already use, with sensible access controls and human review where it matters.'],
                ['Clear measures', 'success defined up front, such as time saved or response quality.'],
            ],
            'phases_title' => 'How it would run',
            'phases' => [
                ['Weeks 1 to 2', 'discovery, a review of your data and the solution design.'],
                ['Weeks 3 to 6', 'the prototype, tested with your team.'],
                ['Weeks 7 to 10', 'hardening, integration and rollout.'],
            ],
            'measures' => [
                'Time saved or tasks handled, measured against how it works today.',
                'Accuracy checked on real examples before rollout.',
                'How much your team actually uses it.',
            ],
            'proof' => [],
            'proof_words' => [],
            'monthly' => false,
            'tip' => 'the AI projects that pay back fastest automate one narrow task end to end, so we keep the first version tightly scoped.',
        ],
        'erp' => [
            'headline' => 'your {product} implementation plan',
            'approach' => [
                ['Process discovery', 'we map how your team quotes, sells, buys, stocks and invoices today, and where it hurts.'],
                ['Configured around your business', '{product} set up to fit how {company} works, not the other way round.'],
                ['Data migration and integrations', 'clean data moved across, and connections to the tools you already use.'],
                ['Training and support', 'your team trained on real scenarios, with support after go-live.'],
            ],
            'phases_title' => 'How it would run',
            'phases' => [
                ['Weeks 1 to 3', 'discovery and the solution design.'],
                ['Weeks 4 to 9', 'configuration, migration and testing in stages.'],
                ['Weeks 10 to 12', 'training, go-live and close support in the first weeks after.'],
            ],
            'measures' => [
                'Go-live on the date we agree together.',
                'How fully your team uses the system.',
                'Time saved in your key processes.',
            ],
            'proof' => [],
            'proof_words' => [],
            'monthly' => false,
            'tip' => 'most implementation delays come from data clean-up, so we start the migration checks early.',
        ],
        'staffing' => [
            'headline' => 'a plan to add the right people to your team',
            'approach' => [
                ['A clear role profile', 'the skills, experience, location and engagement model you need, agreed up front.'],
                ['A screened shortlist', 'profiles evaluated technically before they reach you.'],
                ['Flexible engagement', 'contract, contract-to-hire or project teams, scaled up or down as you need.'],
                ['Ongoing management', 'onboarding support and regular check-ins so the engagement keeps delivering.'],
            ],
            'phases_title' => 'How it would run',
            'phases' => [
                ['First', 'a requirement call and the role profiles.'],
                ['Then', 'a screened shortlist and interviews with your team.'],
                ['After selection', 'onboarding and regular check-ins.'],
            ],
            'measures' => [
                'Time to shortlist and time to start.',
                'Quality of fit, judged by your team.',
                'Retention through the engagement.',
            ],
            'proof' => [],
            'proof_words' => [],
            'monthly' => false,
            'tip' => 'a role profile that separates must-have from nice-to-have skills is what makes the first shortlist accurate.',
        ],
        'growth' => [
            'headline' => 'a 90-day growth plan',
            'approach' => [
                ['A growth audit', 'your website, search, ads, social and sales process, to find where enquiries are being lost.'],
                ['A prioritised roadmap', 'the few moves that will make the biggest difference, in order, with clear owners and dates.'],
                ['One team to deliver it', 'SEO, AI search, paid media and content handled together, so the channels support each other.'],
                ['Reporting you can act on', 'one view of results and a plain-language review every month.'],
            ],
            'phases_title' => 'Your first 90 days',
            'phases' => [
                ['Weeks 1 to 2', 'the audit and your 90-day roadmap.'],
                ['Weeks 3 to 8', 'quick wins and the foundations: tracking, the website and search.'],
                ['Weeks 9 to 12', 'scaling the channels that are working.'],
            ],
            'measures' => [
                'Qualified enquiries and cost per enquiry, across every channel.',
                'Progress against the roadmap, step by step.',
                'A plain-language review every month.',
            ],
            'proof' => ['sai-impression-label-manufacturer', 'firstchoice-used-auto-parts'],
            'proof_words' => ['impressions', 'clicks', 'AI'],
            'monthly' => true,
            'tip' => 'the biggest early gain usually comes from fixing tracking and the website before spending more on traffic.',
        ],
    ];
    return $p[$key] ?? $p['growth'];
}

/** The real case studies, cached. */
function crm_cases(): array
{
    static $cases = null;
    if ($cases === null) {
        $file = dirname(__DIR__) . '/includes/data/cases-real.php';
        $cases = is_file($file) ? (require $file) : [];
        $cases = is_array($cases) ? $cases : [];
    }
    return $cases;
}

/**
 * Up to two case studies that prove the plan, preferring the lead's own
 * industry, each with the two results that fit the service best. Figures are
 * quoted exactly as published.
 */
function crm_proof(array $intent): array
{
    $book = crm_playbook($intent['service']);
    $cases = crm_cases();
    $byIndustry = [
        'manufacturing' => 'sai-impression-label-manufacturer', 'automotive' => 'firstchoice-used-auto-parts', 'saas' => 'floweazy-odoo-partner-website',
    ];
    $order = $book['proof'];
    if (isset($byIndustry[$intent['industry']]) && ($order !== [] || in_array($intent['service'], ['growth', 'seo', 'aiseo', 'web', 'content'], true))) {
        array_unshift($order, $byIndustry[$intent['industry']]);
    }
    $out = [];
    foreach (array_unique($order) as $slug) {
        $c = $cases[$slug] ?? null;
        if (!is_array($c) || empty($c['results'])) {
            continue;
        }
        $results = $c['results'];
        usort($results, static function ($a, $b) use ($book): int {
            $score = static fn(array $r): int => array_sum(array_map(static fn(string $w): int => stripos($r[0] . ' ' . $r[1], $w) !== false ? 1 : 0, $book['proof_words'] ?: ['impressions']));
            return $score($b) <=> $score($a);
        });
        /* "92.9K Google impressions", "10.7K citations in Bing": names keep their capitals. */
        $facts = array_map(static fn(array $r): string => $r[0] . ' ' . (preg_match('/^(Google|Bing|Copilot|ChatGPT|PageSpeed)\b/', $r[1]) ? $r[1] : lcfirst($r[1])), array_slice($results, 0, 2));
        $out[] = [
            'client' => (string) $c['client'],
            'industry' => trim((string) preg_replace('/\s*·.*$/u', '', (string) ($c['industry'] ?? ''))),
            'facts' => $facts,
            'url' => (function_exists('abs_url') ? abs_url('/case-studies/' . $slug . '/') : 'https://vturnu.com/case-studies/' . $slug . '/'),
        ];
        if (count($out) === 2) {
            break;
        }
    }
    return $out;
}

/* ------------------------------------------------------------------ */
/* Drafts                                                              */
/* ------------------------------------------------------------------ */

/** The CRM's settings, merged over sensible defaults. */
function crm_setting_defaults(): array
{
    return [
        'sender_name' => defined('FOUNDER_NAME') ? FOUNDER_NAME : 'VTurnU',
        'sender_title' => defined('FOUNDER_TITLE') ? FOUNDER_TITLE : 'Strategist',
        'sender_phone' => defined('CONTACT_PHONE') ? CONTACT_PHONE : '',
        'auto_ack' => true,
        'digest' => true,
        'followup_days' => 3,
    ];
}

function crm_settings(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $saved = [];
    try {
        $row = db()->query("SELECT value FROM crm_settings WHERE key = 'crm'")->fetch();
        $saved = $row ? (json_decode((string) $row['value'], true) ?: []) : [];
    } catch (Throwable $e) {
        error_log('crm_settings: ' . $e->getMessage());
    }
    return $cache = array_merge(crm_setting_defaults(), array_intersect_key($saved, crm_setting_defaults()));
}

function crm_save_settings(array $s): void
{
    $clean = [
        'sender_name' => crm_clean_line((string) ($s['sender_name'] ?? ''), 60),
        'sender_title' => crm_clean_line((string) ($s['sender_title'] ?? ''), 60),
        'sender_phone' => crm_clean_line((string) ($s['sender_phone'] ?? ''), 30),
        'auto_ack' => !empty($s['auto_ack']),
        'digest' => !empty($s['digest']),
        'followup_days' => max(0, min(30, (int) ($s['followup_days'] ?? 3))),
    ];
    if ($clean['sender_name'] === '') {
        $clean['sender_name'] = crm_setting_defaults()['sender_name'];
    }
    db()->prepare("INSERT INTO crm_settings (key, value, updated_at) VALUES ('crm', CAST(? AS jsonb), now())
                   ON CONFLICT (key) DO UPDATE SET value = excluded.value, updated_at = now()")
        ->execute([json_encode($clean, JSON_UNESCAPED_UNICODE)]);
}

/** "Kalpana" from "Kalpana V". */
function crm_first_name(string $name): string
{
    return (string) (preg_split('/\s+/', trim($name))[0] ?? $name);
}

/** How the service reads inside a sentence. */
function crm_service_phrase(array $f, array $intent): string
{
    $s = trim((string) preg_replace('/,.*$/', '', $f['service']));
    return match (true) {
        $s === 'Website design and development' => $intent['no_website'] || in_array('website_new', $intent['goals'], true) ? 'a new website' : 'your website',
        $s === 'Ecommerce store' => 'an online store',
        $s === 'Landing pages' => 'landing pages',
        $s === 'I need advice on where to start', $s === '' => 'growing your business online',
        $s === 'Full growth program' => 'a full growth program',
        $s === 'Google Ads and paid media' => 'Google Ads and paid media',
        in_array($s, ['SEO', 'SAP CPQ', 'Salesforce', 'Odoo ERP', 'ERPNext'], true) => $s,
        default => lcfirst($s),
    };
}

/** "₹50,000" for an Indian lead, "$550" for an American one: the minimum plan in their currency. */
function crm_minimum(string $country): string
{
    if (!defined('BUDGET_BANDS') || !function_exists('money')) {
        return '₹50,000';
    }
    $cur = defined('COUNTRY_CURRENCY') ? (COUNTRY_CURRENCY[$country] ?? ($country === '' ? 'INR' : 'USD')) : 'INR';
    $bands = BUDGET_BANDS[$cur] ?? BUDGET_BANDS['USD'];
    return money((int) $bands[0], isset(BUDGET_BANDS[$cur]) ? $cur : 'USD');
}

/** Their budget band as it reads in a sentence: "₹50,000 to ₹1,50,000 a month". */
function crm_budget_phrase(string $budget): string
{
    $b = trim((string) preg_replace('/\s*\(about [^)]*\)\s*$/', '', $budget));
    return trim((string) preg_replace('/\s*\/\s*month$/', ' a month', $b));
}

/**
 * The first draft of a proposal, in the simple markup the composer edits:
 * "## " headings, "- " bullets and **bold**. Written to read like a person
 * wrote it after reading the enquiry, because a person will check it before
 * it is sent.
 *
 * @return array{subject:string,body:string,intent:array,proof:array}
 */
function crm_proposal_draft(array $lead, ?array $settings = null): array
{
    $settings ??= crm_settings();
    $f = crm_lead_facts($lead);
    $in = crm_intent($f);
    $book = crm_playbook($in['service']);
    $seed = $f['email'] . $f['id'];
    $company = $f['company'];
    $product = trim((string) preg_replace('/,.*$/', '', $f['service']));
    $vars = [
        '{company}' => $company !== '' ? $company : 'your business',
        '{buyers}' => $in['buyers'],
        '{site}' => $f['domain'] !== '' ? $f['domain'] : 'your website',
        '{product}' => in_array($product, ['SAP CPQ', 'Salesforce', 'Odoo ERP', 'ERPNext'], true) ? $product : 'the system',
    ];
    $fill = static fn(string $s): string => strtr($s, $vars);
    $servicePhrase = crm_service_phrase($f, $in);
    $for = $company !== '' ? ' for ' . $company : '';

    $headline = $fill($in['service'] === 'web' && ($in['no_website'] || in_array('website_new', $in['goals'], true)) ? $book['headline_new'] : $book['headline']);
    if ($in['service'] === 'seo' && !array_intersect(['leads', 'sales'], $in['goals'])) {
        $headline = 'a plan to rank for the searches your buyers make';
    }
    $subject = ($company !== '' && mb_strlen($company) <= 40 ? $company : $f['first']) . ': ' . $headline;

    $L = [];
    $L[] = 'Hi ' . $f['first'] . ',';
    $L[] = '';
    $L[] = crm_pick([
        'Thank you for reaching out about ' . $servicePhrase . $for . '. I read through what you shared, and I have put together a plan that fits where you are today.',
        'Thanks for getting in touch about ' . $servicePhrase . $for . '. Based on what you told us, here is how I would approach it.',
        'Thank you for your enquiry about ' . $servicePhrase . $for . '. I have gone through your note, and below is the plan I would recommend.',
    ], $seed . 'open');
    $L[] = '';
    $L[] = crm_pick([
        'Treat it as a starting point rather than a fixed scope. We will shape it with you on a short call.',
        'It is a first version, and we will refine it together once we have spoken.',
    ], $seed . 'bridge');
    $L[] = '';

    /* What we heard: their words first, then what the drop-downs told us. */
    $L[] = '## What we heard';
    if ($in['quote'] !== '') {
        $L[] = '- In your words: "' . $in['quote'] . '"';
    }
    /* Named markets say more than "new markets", so they replace that goal when we have them. */
    $goalKeys = $in['markets'] ? array_values(array_diff($in['goals'], ['global'])) : $in['goals'];
    $goalPhrases = array_map(static fn(string $g): string => $fill(CRM_GOALS[$g][1]), array_slice($goalKeys, 0, 2));
    $goalLine = 'You want to ' . implode(' and ', $goalPhrases);
    if ($in['markets']) {
        $goalLine .= ', with buyers in ' . crm_list($in['markets']);
    }
    if ($in['horizon'] !== '' && $in['target'] === '') {
        $goalLine .= ', within ' . $in['horizon'];
    }
    $L[] = '- ' . $goalLine . '.';
    if ($in['target'] !== '') {
        $L[] = '- Your target is ' . $in['target'] . ($in['horizon'] !== '' ? ' within ' . $in['horizon'] : '') . ', so the plan is built around that number.';
    }
    $timing = [
        'As soon as possible' => 'You would like to start as soon as possible, so the plan front-loads the quick wins.',
        'Within 1 to 3 months' => 'You are planning to start within the next one to three months.',
        'In 3 to 6 months' => 'You are planning three to six months ahead, which leaves room to do the groundwork properly.',
        'Just researching' => 'You are still exploring options, so treat this as a practical outline to compare.',
    ][$f['timeline']] ?? '';
    if ($timing !== '') {
        $L[] = '- ' . $timing;
    }
    $size = $f['size'] !== '' ? 'a team of ' . $f['size'] : '';
    if ($in['no_website']) {
        $L[] = '- You do not have a website yet, so we would start from a clean slate.';
    } elseif ($size !== '' && $f['domain'] !== '') {
        $L[] = '- ' . ($company !== '' ? $company : 'You') . ($company !== '' ? ' is ' : ' are ') . $size . ', and your current website is ' . $f['domain'] . '.';
    } elseif ($f['domain'] !== '') {
        $L[] = '- Your current website is ' . $f['domain'] . ', and we will review it closely before we speak.';
    } elseif ($size !== '') {
        $L[] = '- ' . ($company !== '' ? $company . ' is ' : 'You are ') . $size . '.';
    }
    $L[] = '';

    $L[] = '## How we would approach it';
    foreach ($book['approach'] as [$title, $text]) {
        $L[] = '- **' . $title . ':** ' . $fill($text);
    }
    $L[] = '';
    $L[] = '## ' . $book['phases_title'];
    foreach ($book['phases'] as [$when, $text]) {
        $L[] = '- **' . $when . ':** ' . $fill($text);
    }
    $L[] = '';
    $L[] = '## How you will know it is working';
    foreach ($book['measures'] as $m) {
        $L[] = '- ' . $fill($m);
    }
    $L[] = '';

    $proof = crm_proof($in);
    if ($proof) {
        $L[] = '## Proof from similar work';
        foreach ($proof as $p) {
            $L[] = '- **' . $p['client'] . '**' . ($p['industry'] !== '' ? ' (' . $p['industry'] . ')' : '') . ': ' . crm_list($p['facts']) . '. The full story: ' . $p['url'];
        }
        $L[] = '';
    }

    $L[] = '## Investment';
    $min = crm_minimum($f['country']);
    $budget = crm_budget_phrase($f['budget']);
    if (str_starts_with($budget, 'Under ')) {
        $L[] = 'Our plans start from ' . $min . ' a month. Within the budget you mentioned, I would start with the ' . lcfirst($book['approach'][0][0]) . ', since it moves the needle most, and add the rest as results come in.';
    } elseif ($budget !== '' && $budget !== 'Not decided yet') {
        $L[] = 'You mentioned a budget of ' . $budget . '. This plan is designed to fit within it, and we will confirm the exact scope and a fixed quote on our call.';
    } else {
        $L[] = 'Once we agree the scope on our call, you will get a clear, fixed quote with no surprises. Our plans start from ' . $min . ' a month.';
    }
    $extra = [];
    if (!empty($book['ad_spend'])) {
        $extra[] = 'Ad spend is separate and paid directly to the ad platform.';
    }
    if ($book['monthly']) {
        $extra[] = 'There is no lock-in after the first 90 days.';
    }
    if ($extra) {
        $L[] = '';
        $L[] = implode(' ', $extra);
    }
    $L[] = '';

    $L[] = '## Next step';
    $phone = (string) $settings['sender_phone'];
    $L[] = crm_pick([
        'The quickest way to turn this into a precise plan is a 20-minute call. Reply with two times that suit you this week' . ($phone !== '' ? ', or message me on WhatsApp at ' . $phone : '') . '.',
        'If this looks like the right direction, let us take 20 minutes to go through it together. Just reply with a time that works for you' . ($phone !== '' ? ', or WhatsApp me at ' . $phone : '') . '.',
    ], $seed . 'cta');
    if ($in['urgent']) {
        $L[] = '';
        $L[] = 'Since you want to move quickly, I will keep my calendar flexible this week to fit around yours.';
    }
    /* No sign-off: the signature block under the email closes it. */

    return [
        'subject' => crm_no_dashes($subject),
        'body' => crm_no_dashes(implode("\n", $L)),
        'intent' => $in,
        'proof' => $proof,
    ];
}

/** "a, b and c". */
function crm_list(array $items): string
{
    $items = array_values(array_filter(array_map('strval', $items), 'strlen'));
    if (count($items) < 2) {
        return $items[0] ?? '';
    }
    $last = array_pop($items);
    return implode(', ', $items) . ' and ' . $last;
}

/** The next two working days after today, by name: ["Thursday", "Friday"]. */
function crm_next_workdays(?int $now = null): array
{
    $t = $now ?? time();
    $out = [];
    while (count($out) < 2) {
        $t += 86400;
        if ((int) date('N', $t) <= 5) {
            $out[] = date('l', $t);
        }
    }
    return $out;
}

/**
 * A short, polite follow-up to a proposal that has not had a reply, with one
 * new piece of value rather than a bare "just checking in".
 *
 * @return array{subject:string,body:string}
 */
function crm_followup_draft(array $lead, array $proposal, ?array $settings = null): array
{
    $settings ??= crm_settings();
    $f = crm_lead_facts($lead);
    $in = crm_intent($f);
    $book = crm_playbook($in['service']);
    $seed = $f['email'] . $f['id'] . 'f';
    $sent = date('j F', (int) strtotime((string) ($proposal['sent_at'] ?? 'now')));
    [$d1, $d2] = crm_next_workdays();
    $L = [
        'Hi ' . $f['first'] . ',',
        '',
        crm_pick([
            'I wanted to follow up on the plan I sent on ' . $sent . ($f['company'] !== '' ? ' for ' . $f['company'] : '') . '.',
            'Just checking in on the proposal I shared on ' . $sent . '.',
        ], $seed),
        '',
        'One thing worth knowing while you think it over: ' . $book['tip'],
        '',
        'If the timing or scope needs to change, tell me and I will adjust it. Or, if it helps, we can walk through it together in 20 minutes. Would ' . $d1 . ' or ' . $d2 . ' work for you?',
    ];
    $subject = (string) ($proposal['subject'] ?? '');
    return [
        'subject' => crm_no_dashes(str_starts_with($subject, 'Re: ') ? $subject : 'Re: ' . $subject),
        'body' => crm_no_dashes(implode("\n", $L)),
    ];
}

/**
 * The instant acknowledgement a new lead receives: who will reply, by when,
 * and how to reach a person sooner.
 *
 * @return array{subject:string,body:string}
 */
function crm_ack_draft(array $lead, ?array $settings = null): array
{
    $settings ??= crm_settings();
    $f = crm_lead_facts($lead);
    $in = crm_intent($f);
    $me = crm_first_name((string) $settings['sender_name']);
    $phone = (string) $settings['sender_phone'];
    $L = [
        'Hi ' . $f['first'] . ',',
        '',
        'Thank you for contacting VTurnU about ' . crm_service_phrase($f, $in) . ($f['company'] !== '' ? ' for ' . $f['company'] : '') . '. I am ' . $me
            . ', and I will go through what you shared' . ($f['domain'] !== '' ? ' and look at ' . $f['domain'] : '') . ' before I reply.',
        '',
        'You will hear from me within one working day with a clear plan and the next steps. If anything is urgent, '
            . ($phone !== '' ? 'message me on WhatsApp at ' . $phone . ' or ' : '') . 'simply reply to this email.',
    ];
    return [
        'subject' => crm_no_dashes($f['first'] !== 'there' ? $f['first'] . ', thank you for your enquiry' : 'Thank you for your enquiry'),
        'body' => crm_no_dashes(implode("\n", $L)),
    ];
}

/* ------------------------------------------------------------------ */
/* Rendering the markup                                                */
/* ------------------------------------------------------------------ */

/** Inline markup: escape, then **bold** and bare links (trailing punctuation and quotes stay outside the link). */
function crm_inline_html(string $s, string $linkStyle = ''): string
{
    $h = htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    $h = (string) preg_replace('/\*\*(.+?)\*\*/u', '<strong>$1</strong>', $h);
    return (string) preg_replace_callback('#https?://[^\s<]+#i', static function (array $m) use ($linkStyle): string {
        $url = $m[0];
        $tail = '';
        while (preg_match('/(&quot;|&#039;|[.,;:!?)])$/', $url, $t)) {
            $tail = $t[1] . $tail;
            $url = substr($url, 0, -strlen($t[1]));
        }
        return '<a href="' . $url . '"' . ($linkStyle !== '' ? ' style="' . $linkStyle . '"' : '') . '>' . $url . '</a>' . $tail;
    }, $h);
}

/**
 * The composer's markup as HTML. Styles are inline so mail clients keep them;
 * $style chooses the look (email or web page).
 */
function crm_markup_html(string $markup, string $style = 'email'): string
{
    $email = $style === 'email';
    $css = $email ? [
        'p' => 'margin:0 0 14px;font:15px/1.65 Arial,Helvetica,sans-serif;color:#1D1936',
        'h2' => 'margin:22px 0 8px;font:bold 16px/1.4 Arial,Helvetica,sans-serif;color:#4338CA',
        'ul' => 'margin:0 0 14px;padding:0 0 0 20px',
        'li' => 'margin:0 0 7px;font:15px/1.6 Arial,Helvetica,sans-serif;color:#1D1936',
        'a' => 'color:#4338CA;text-decoration:underline',
    ] : ['p' => '', 'h2' => '', 'ul' => '', 'li' => '', 'a' => ''];
    $attr = static fn(string $tag): string => $css[$tag] !== '' ? ' style="' . $css[$tag] . '"' : '';
    $out = [];
    $list = [];
    $para = [];
    $flushList = static function () use (&$list, &$out, $attr): void {
        if ($list) {
            $out[] = '<ul' . $attr('ul') . '>' . implode('', $list) . '</ul>';
            $list = [];
        }
    };
    $flushPara = static function () use (&$para, &$out, $attr): void {
        if ($para) {
            $out[] = '<p' . $attr('p') . '>' . implode('<br>', $para) . '</p>';
            $para = [];
        }
    };
    foreach (preg_split('/\r\n|\r|\n/', $markup) ?: [] as $line) {
        $t = rtrim($line);
        if (str_starts_with($t, '## ')) {
            $flushPara();
            $flushList();
            $out[] = '<h2' . $attr('h2') . '>' . crm_inline_html(substr($t, 3), $css['a']) . '</h2>';
        } elseif (preg_match('/^\s*[-*] (.+)$/u', $t, $m)) {
            $flushPara();
            $list[] = '<li' . $attr('li') . '>' . crm_inline_html($m[1], $css['a']) . '</li>';
        } elseif (trim($t) === '') {
            $flushPara();
            $flushList();
        } else {
            $flushList();
            $para[] = crm_inline_html($t, $css['a']);
        }
    }
    $flushPara();
    $flushList();
    return implode("\n", $out);
}

/** The composer's markup as plain text, for the text part of the email. */
function crm_markup_text(string $markup): string
{
    $lines = [];
    foreach (preg_split('/\r\n|\r|\n/', $markup) ?: [] as $line) {
        $t = str_replace('**', '', rtrim($line));
        if (str_starts_with($t, '## ')) {
            $lines[] = '';
            $lines[] = mb_strtoupper(substr($t, 3));
        } elseif (preg_match('/^\s*[-*] (.+)$/u', $t, $m)) {
            $lines[] = '  * ' . $m[1];
        } else {
            $lines[] = $t;
        }
    }
    return trim((string) preg_replace("/\n{3,}/", "\n\n", implode("\n", $lines)));
}

/** "Kalpana V <connect@vturnu.com>": proposals come from a person at the shared address. */
function crm_from(array $settings): string
{
    $name = str_replace(['"', '<', '>', "\r", "\n"], '', (string) $settings['sender_name']);
    return $name . ' from VTurnU <' . CONTACT_EMAIL . '>';
}

/* The signature, as the owner designed it: services under the company name,
   a tagline at the foot, and the social profiles in this order. */
const CRM_SIG_SERVICES = ['Digital Marketing', 'SEO', 'AEO', 'GEO', 'Web Development', 'UI/UX', 'AI Marketing', 'PPC', 'ORM', 'Branding', 'IT Consulting'];
const CRM_SIG_TAGLINE = ['Strategic Marketing', 'Smarter Technology', 'Better Growth'];
const CRM_SIG_SOCIAL = ['LinkedIn' => 'sig-linkedin.png', 'Instagram' => 'sig-instagram.png', 'Facebook' => 'sig-facebook.png', 'Twitter' => 'sig-x.png'];

/** A WhatsApp chat link for a phone number written with its country code, or ''. */
function crm_whatsapp_url(string $phone): string
{
    $digits = (string) preg_replace('/\D/', '', $phone);
    return strlen($digits) >= 10 && str_starts_with(trim($phone), '+') ? 'https://wa.me/' . $digits : '';
}

/** The GSTIN goes to Indian clients only, as on the website. Unknown country counts as India, where most leads come from. */
function crm_show_gstin(string $country): bool
{
    return defined('GSTIN') && in_array($country, ['IN', ''], true);
}

/**
 * The signature block under every client email, laid out the way the owner
 * designed it: logo, social profiles and the website button on the left, a
 * divider, then company, services, email, WhatsApp, GSTIN and the tagline on
 * the right. One fixed 600px row that never stacks, so it reads the same in
 * every inbox (phones scale it down whole). Images are PNG (mail apps do not
 * show SVG) and every link carries readable anchor text or alt text.
 */
function crm_signature_html(array $settings, string $country = ''): string
{
    $h = static fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    $site = defined('SITE_URL') ? SITE_URL : 'https://vturnu.com';
    $img = $site . '/assets/img/email/';
    $font = 'Arial,Helvetica,sans-serif';
    $link = 'color:#1A56DB;text-decoration:underline';
    $phone = (string) $settings['sender_phone'];
    $wa = crm_whatsapp_url($phone);

    $socials = [];
    foreach (CRM_SIG_SOCIAL as $key => $file) {
        $url = (string) (SOCIAL_LINKS[$key] ?? '');
        if ($url === '') {
            continue;
        }
        $label = $key === 'Twitter' ? 'X' : $key;
        $socials[] = '<td style="padding:0;width:44px"><a href="' . $h($url) . '" title="VTurnU on ' . $h($label) . '" style="text-decoration:none">'
            . '<img src="' . $img . $file . '" width="44" height="44" alt="' . $h($label) . '" style="display:block;border:0;width:44px;height:44px"></a></td>';
    }
    $gap = '<td style="padding:0;width:8px;font-size:0;line-height:0">&nbsp;</td>';

    $items = [['sig-email.png', '<a href="mailto:' . CONTACT_EMAIL . '" style="' . $link . ';font-weight:bold">' . $h(CONTACT_EMAIL) . '</a>']];
    if ($phone !== '') {
        $items[] = ['sig-whatsapp.png', '<a href="' . $h($wa !== '' ? $wa : 'tel:' . preg_replace('/[^\d+]/', '', $phone)) . '" title="' . ($wa !== '' ? 'Chat on WhatsApp' : 'Call') . '" style="' . $link . '">' . $h($phone) . '</a>'
            . ($wa !== '' ? '&nbsp; <a href="' . $h($wa) . '" style="font:bold 12px ' . $font . ';color:#22A55B;text-decoration:none">WhatsApp</a>' : '')];
    }
    if (crm_show_gstin($country)) {
        $items[] = ['sig-gstin.png', '<strong>GSTIN: ' . $h(GSTIN) . '</strong>'];
    }
    $rows = '';
    foreach ($items as $i => [$icon, $content]) {
        $pad = $i === count($items) - 1 ? '0' : '10px';
        $rows .= '<tr><td style="padding:0 12px ' . $pad . ' 0;width:32px;vertical-align:middle"><img src="' . $img . $icon . '" width="32" height="32" alt="" style="display:block;border:0;width:32px;height:32px"></td>'
            . '<td style="padding:0 0 ' . $pad . ';vertical-align:middle;white-space:nowrap;font:15px/1.3 ' . $font . ';color:#1D1936">' . $content . '</td></tr>';
    }
    /* Items separated by pipes, each line kept whole: the services sit on two lines, as designed. */
    $line = static fn(array $parts): string => '<span style="white-space:nowrap">' . implode(' &nbsp;|&nbsp; ', array_map($h, $parts)) . '</span>';

    return '<table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px;border-collapse:collapse;margin:18px 0 0">'
        . '<tr>'
        . '<td width="206" valign="top" style="width:206px;padding:4px 22px 4px 0;vertical-align:top">'
        . '<a href="' . $site . '/" title="VTurnU" style="text-decoration:none"><img src="' . $img . 'sig-logo.png" width="200" height="55" alt="VTurnU, together we turn heads" style="display:block;border:0;width:200px;height:55px"></a>'
        . ($socials ? '<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;margin:26px 0 0"><tr>' . implode($gap, $socials) . '</tr></table>' : '')
        . '<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;margin:28px 0 0"><tr><td style="padding:0;border-radius:999px;background:#4F46E5">'
        . '<a href="' . $site . '/" style="display:inline-block;padding:13px 18px;font:bold 14px/16px ' . $font . ';color:#ffffff;text-decoration:none;border-radius:999px;white-space:nowrap">&#127760;&nbsp; www.vturnu.com &nbsp;&rarr;</a>'
        . '</td></tr></table>'
        . '</td>'
        . '<td valign="top" style="padding:4px 0 4px 22px;border-left:2px solid #DCD9F2;vertical-align:top">'
        . '<div style="font:bold 20px/1.25 ' . $font . ';color:#1D1936;margin:0 0 8px;white-space:nowrap">' . $h(defined('LEGAL_NAME') ? LEGAL_NAME : 'VTurnU') . '</div>'
        . '<div style="font:12px/1.75 ' . $font . ';color:#6F6992;margin:0 0 14px">' . $line(array_slice(CRM_SIG_SERVICES, 0, 5)) . '<br>' . $line(array_slice(CRM_SIG_SERVICES, 5)) . '</div>'
        . '<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse">' . $rows . '</table>'
        . '<div style="border-top:1px solid #DCD9F2;margin:14px 0 0;padding:10px 0 0;white-space:nowrap;font:12.5px/1.5 ' . $font . ';color:#6F6992">' . $line(CRM_SIG_TAGLINE) . '</div>'
        . '</td>'
        . '</tr></table>';
}

/** The same signature as plain text, every link spelled out, for the text part of the email. */
function crm_signature_text(array $settings, string $country = ''): string
{
    $phone = (string) $settings['sender_phone'];
    $wa = crm_whatsapp_url($phone);
    $lines = [defined('LEGAL_NAME') ? LEGAL_NAME : 'VTurnU', implode(' | ', CRM_SIG_SERVICES), '', 'Email: ' . CONTACT_EMAIL];
    if ($phone !== '') {
        $lines[] = 'WhatsApp: ' . $phone . ($wa !== '' ? ' (' . $wa . ')' : '');
    }
    $lines[] = 'Website: ' . (defined('SITE_URL') ? SITE_URL : 'https://vturnu.com');
    foreach (CRM_SIG_SOCIAL as $key => $file) {
        if (!empty(SOCIAL_LINKS[$key])) {
            $lines[] = ($key === 'Twitter' ? 'X' : $key) . ': ' . SOCIAL_LINKS[$key];
        }
    }
    if (crm_show_gstin($country)) {
        $lines[] = 'GSTIN: ' . GSTIN;
    }
    $lines[] = '';
    $lines[] = implode(' | ', CRM_SIG_TAGLINE);
    return implode("\n", $lines);
}

/** The private web copy of a proposal. */
function crm_proposal_url(string $token): string
{
    return (defined('SITE_URL') ? SITE_URL : 'https://vturnu.com') . '/proposal/' . $token . '/';
}

/** A signed link that opens a proposal without counting as the client reading it. */
function crm_preview_url(string $token): string
{
    return crm_proposal_url($token) . '?preview=' . substr(hash_hmac('sha256', 'preview|' . $token, SECURITY_SECRET), 0, 24);
}

/**
 * The whole email: a personal letter, a button to the web copy, a signature
 * and the open pixel. Kept deliberately plain so it reads as one person
 * writing to another, not as a newsletter.
 */
function crm_email_html(string $subject, string $markup, array $settings, string $linkToken, string $pixelToken, bool $button = true, string $country = ''): string
{
    $site = defined('SITE_URL') ? SITE_URL : 'https://vturnu.com';
    $font = 'Arial,Helvetica,sans-serif';
    $sig = crm_signature_html($settings, $country);
    $cta = $button
        ? '<table role="presentation" cellpadding="0" cellspacing="0" style="margin:6px 0 18px"><tr><td style="border-radius:999px;background:#4338CA">'
            . '<a href="' . crm_proposal_url($linkToken) . '" style="display:inline-block;padding:12px 24px;font:bold 14px ' . $font . ';color:#ffffff;text-decoration:none;border-radius:999px">Open your proposal</a>'
            . '</td></tr></table><p style="margin:0 0 18px;font:13px/1.6 ' . $font . ';color:#6F6992">The web version keeps every section in one place, and you can tell us you are ready to go ahead from there.</p>'
        : '';
    return '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'
        . htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') . '</title></head>'
        . '<body style="margin:0;padding:0;background:#ffffff">'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#ffffff"><tr><td align="center" style="padding:20px 14px">'
        . '<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px"><tr><td style="text-align:left">'
        . crm_markup_html($markup) . $cta . $sig
        . '</td></tr></table></td></tr></table>'
        . ($pixelToken !== '' ? '<img src="' . $site . '/p/o/' . $pixelToken . '.gif" width="1" height="1" alt="" style="display:block;width:1px;height:1px;border:0">' : '')
        . '</body></html>';
}

/* ------------------------------------------------------------------ */
/* Sending and recording                                               */
/* ------------------------------------------------------------------ */

/** A lead row by id, or null. */
function crm_lead(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM enquiries WHERE id = ? AND deleted_at IS NULL');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

/** Proposals and follow-ups sent to one lead, newest first. */
function crm_proposals(int $leadId): array
{
    try {
        $stmt = db()->prepare('SELECT * FROM proposals WHERE enquiry_id = ? ORDER BY sent_at DESC');
        $stmt->execute([$leadId]);
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        error_log('crm_proposals: ' . $e->getMessage());
        return [];
    }
}

/** The latest proposal for every lead, keyed by lead id, with how often each lead has been written to. */
function crm_proposal_index(): array
{
    try {
        $rows = db()->query(
            "SELECT DISTINCT ON (enquiry_id) enquiry_id, token, kind, subject, sent_at, opened_at, open_count, viewed_at, view_count, read_seconds, accepted_at,
                    (SELECT count(*) FROM proposals p2 WHERE p2.enquiry_id = p.enquiry_id) AS sent_count,
                    (SELECT max(viewed_at) FROM proposals p3 WHERE p3.enquiry_id = p.enquiry_id) AS any_viewed,
                    (SELECT max(accepted_at) FROM proposals p4 WHERE p4.enquiry_id = p.enquiry_id) AS any_accepted
             FROM proposals p WHERE kind = 'proposal' ORDER BY enquiry_id, sent_at DESC"
        )->fetchAll();
    } catch (Throwable $e) {
        error_log('crm_proposal_index: ' . $e->getMessage());
        return [];
    }
    $out = [];
    foreach ($rows as $r) {
        $out[(string) $r['enquiry_id']] = $r;
    }
    return $out;
}

/** Where a proposal stands, as one word the list and badges use. */
function crm_proposal_state(?array $p): string
{
    if (!$p) {
        return 'none';
    }
    if (!empty($p['any_accepted'] ?? $p['accepted_at'] ?? null)) {
        return 'accepted';
    }
    if (!empty($p['any_viewed'] ?? $p['viewed_at'] ?? null)) {
        return 'viewed';
    }
    return !empty($p['opened_at']) ? 'opened' : 'sent';
}

function crm_proposal_by_token(string $token): ?array
{
    if (!preg_match('/^[a-f0-9]{32}$/', $token)) {
        return null;
    }
    $stmt = db()->prepare('SELECT * FROM proposals WHERE token = ?');
    $stmt->execute([$token]);
    $row = $stmt->fetch();
    return $row ?: null;
}

/** Append a line to a lead's activity timeline, as the admin does. */
function crm_log(int $leadId, string $text): void
{
    try {
        $stmt = db()->prepare('SELECT activity FROM enquiries WHERE id = ?');
        $stmt->execute([$leadId]);
        $row = $stmt->fetch();
        $log = $row ? (json_decode((string) ($row['activity'] ?? '[]'), true) ?: []) : [];
        $log[] = ['date' => date('Y-m-d H:i'), 'text' => $text];
        if (count($log) > 200) {
            $log = array_slice($log, -200);
        }
        db()->prepare('UPDATE enquiries SET activity = CAST(? AS jsonb) WHERE id = ?')->execute([json_encode($log, JSON_UNESCAPED_UNICODE), $leadId]);
    } catch (Throwable $e) {
        error_log('crm_log: ' . $e->getMessage());
    }
}

/**
 * Send a proposal or a follow-up from connect@vturnu.com, record it, and move
 * the lead along: stage to Proposal, first contact time, and a follow-up date.
 *
 * @param array{stage?:bool,remind_days?:int,copy?:bool,link_token?:string,parent_id?:int} $o
 * @return array{ok:bool,error:string,token:string}
 */
function crm_send(array $lead, string $kind, string $subject, string $body, array $o = []): array
{
    $fail = static fn(string $e): array => ['ok' => false, 'error' => $e, 'token' => ''];
    $settings = crm_settings();
    $subject = crm_clean_line(crm_no_dashes($subject), 200);
    $body = trim(crm_no_dashes(str_replace("\r\n", "\n", $body)));
    $to = strtolower(trim((string) ($lead['email'] ?? '')));
    if ($subject === '') {
        return $fail('Add a subject line.');
    }
    if (mb_strlen($body) < 40) {
        return $fail('The message is too short to send.');
    }
    if (mb_strlen($body) > 20000) {
        return $fail('The message is too long. Please keep it under 20,000 characters.');
    }
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return $fail('This lead has no valid email address.');
    }
    $kind = in_array($kind, ['proposal', 'followup'], true) ? $kind : 'proposal';
    $token = bin2hex(random_bytes(16));
    $linkToken = $kind === 'proposal' ? $token : ((string) ($o['link_token'] ?? '') ?: $token);
    $country = crm_lead_facts($lead)['country'];
    $withLink = $kind === 'proposal' || !empty($o['link_token']);
    $text = crm_markup_text($body) . ($withLink ? "\n\nYour proposal online: " . crm_proposal_url($linkToken) : '') . "\n\n" . crm_signature_text($settings, $country);
    $payload = [
        'from' => crm_from($settings),
        'to' => [$to],
        'subject' => $subject,
        'html' => crm_email_html($subject, $body, $settings, $linkToken, $token, $withLink, $country),
        'text' => $text,
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
        db()->prepare('INSERT INTO proposals (enquiry_id, parent_id, token, kind, subject, body, to_email, sent_by, resend_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute([$leadId, ($o['parent_id'] ?? null) ?: null, $token, $kind, $subject, $body, $to, (string) ($o['by'] ?? ''), $sent['id']]);
        $sets = ['contacted_at = coalesce(contacted_at, now())'];
        $args = [];
        if (!empty($o['stage']) && in_array((string) ($lead['status'] ?? ''), ['new', 'contacted', 'qualified'], true)) {
            $sets[] = "status = 'proposal'";
        }
        $days = (int) ($o['remind_days'] ?? 0);
        if ($days > 0) {
            $sets[] = 'followup = ?';
            $args[] = date('Y-m-d', strtotime('+' . $days . ' days'));
        }
        $args[] = $leadId;
        db()->prepare('UPDATE enquiries SET ' . implode(', ', $sets) . ' WHERE id = ?')->execute($args);
    } catch (Throwable $e) {
        /* The email went out; losing the record must not hide that. */
        error_log('crm_send: sent but not recorded for lead ' . $leadId . ': ' . $e->getMessage());
        return ['ok' => true, 'error' => 'Sent, but it could not be recorded: ' . $e->getMessage(), 'token' => $token];
    }
    if (!empty($o['stage']) && in_array((string) ($lead['status'] ?? ''), ['new', 'contacted', 'qualified'], true)) {
        crm_log($leadId, 'Status: ' . ucfirst((string) $lead['status']) . ' to Proposal');
    }
    crm_log($leadId, ($kind === 'proposal' ? 'Proposal sent' : 'Follow-up sent') . ': "' . $subject . '"' . ($days > 0 ? ', follow-up set for ' . date('j M', strtotime('+' . $days . ' days')) : ''));
    return ['ok' => true, 'error' => '', 'token' => $token];
}

/**
 * The instant acknowledgement, sent as the lead is captured. Only real sales
 * enquiries get one (the quality screen passed them, and they came through a
 * sales form), and a failure is logged rather than shown to the visitor.
 */
function crm_auto_ack(array $lead): void
{
    try {
        $settings = crm_settings();
        if (empty($settings['auto_ack']) || !in_array((string) ($lead['source'] ?? ''), ['home-quote', 'contact-page', 'popup', 'service-page', 'landing-page', 'audit-consultation'], true)) {
            return;
        }
        $draft = crm_ack_draft($lead, $settings);
        $country = crm_lead_facts($lead)['country'];
        $sent = resend_send([
            'from' => crm_from($settings),
            'to' => [(string) $lead['email']],
            'subject' => $draft['subject'],
            'html' => crm_email_html($draft['subject'], $draft['body'], $settings, '', '', false, $country),
            'text' => crm_markup_text($draft['body']) . "\n\n" . crm_signature_text($settings, $country),
            'reply_to' => CONTACT_EMAIL,
        ]);
        if (!empty($lead['id']) && $sent['ok']) {
            crm_log((int) $lead['id'], 'Acknowledgement email sent automatically');
        }
    } catch (Throwable $e) {
        error_log('crm_auto_ack: ' . $e->getMessage());
    }
}

/* ------------------------------------------------------------------ */
/* The client side: open pixel, web proposal, go-ahead                 */
/* ------------------------------------------------------------------ */

/** Ask the inbox to tell us an email was opened. */
function crm_track_open(string $token): void
{
    try {
        $p = crm_proposal_by_token($token);
        if (!$p) {
            return;
        }
        db()->prepare('UPDATE proposals SET open_count = open_count + 1, opened_at = coalesce(opened_at, now()), last_opened_at = now() WHERE id = ?')->execute([(int) $p['id']]);
        if (empty($p['opened_at'])) {
            crm_log((int) $p['enquiry_id'], ($p['kind'] === 'followup' ? 'Opened the follow-up email' : 'Opened the proposal email'));
        }
    } catch (Throwable $e) {
        error_log('crm_track_open: ' . $e->getMessage());
    }
}

/**
 * Record a read of the web proposal. The page reports itself only after a few
 * seconds on screen, so link scanners that pre-open emails do not count, and
 * again when it is closed, with how long it was read.
 */
function crm_track_view(string $token, bool $first, int $seconds): void
{
    try {
        $p = crm_proposal_by_token($token);
        if (!$p) {
            return;
        }
        $seconds = max(0, min(7200, $seconds));
        if ($first) {
            db()->prepare('UPDATE proposals SET view_count = view_count + 1, viewed_at = coalesce(viewed_at, now()), last_viewed_at = now(), read_seconds = greatest(read_seconds, ?) WHERE id = ?')
                ->execute([$seconds, (int) $p['id']]);
            if (empty($p['viewed_at'])) {
                $lead = crm_lead((int) $p['enquiry_id']);
                crm_log((int) $p['enquiry_id'], 'Read the proposal online');
                if ($lead) {
                    send_email(ENQUIRY_TO, ($lead['name'] ?? 'A lead') . ' is reading your proposal now', implode("\n", [
                        ($lead['name'] ?? '') . ($lead['company'] !== '' ? ' (' . $lead['company'] . ')' : '') . ' opened the proposal "' . $p['subject'] . '" just now.',
                        '',
                        'A quick call or WhatsApp while it is fresh tends to land well.',
                        '',
                        'Phone: ' . ($lead['mobile'] ?: 'not given'),
                        'Open the lead: ' . SITE_URL . '/admin/?view=lead&id=' . (int) $p['enquiry_id'],
                    ]), ['reply_to' => (string) ($lead['email'] ?? '')]);
                }
            }
        } elseif ($seconds > 0) {
            db()->prepare('UPDATE proposals SET read_seconds = greatest(read_seconds, ?), last_viewed_at = now() WHERE id = ?')->execute([$seconds, (int) $p['id']]);
        }
    } catch (Throwable $e) {
        error_log('crm_track_view: ' . $e->getMessage());
    }
}

/** The client says yes: record it, raise the lead to the top, and tell the owner straight away. */
function crm_accept(array $p, string $when, string $note): void
{
    $when = crm_clean_line($when, 120);
    $note = trim(mb_substr(str_replace("\r\n", "\n", $note), 0, 1500));
    $first = empty($p['accepted_at']);
    db()->prepare('UPDATE proposals SET accepted_at = coalesce(accepted_at, now()), accept_note = ? WHERE id = ?')
        ->execute([trim(($when !== '' ? 'Good time to talk: ' . $when . "\n" : '') . $note), (int) $p['id']]);
    $leadId = (int) $p['enquiry_id'];
    $lead = crm_lead($leadId);
    if (!$lead) {
        return;
    }
    $tags = array_filter(array_map('trim', explode(',', (string) $lead['tags'])));
    if (!in_array('proposal accepted', $tags, true)) {
        $tags[] = 'proposal accepted';
    }
    db()->prepare("UPDATE enquiries SET priority = 'high', followup = current_date, tags = ? WHERE id = ?")->execute([implode(', ', $tags), $leadId]);
    crm_log($leadId, ($first ? 'Accepted the proposal online' : 'Sent another go-ahead note') . ($when !== '' ? ', good time to talk: ' . $when : ''));
    send_email(ENQUIRY_TO, ($lead['name'] ?? 'A lead') . ' wants to go ahead' . ($lead['company'] !== '' ? ' (' . $lead['company'] . ')' : ''), implode("\n", [
        ($lead['name'] ?? '') . ' accepted the proposal "' . $p['subject'] . '".',
        '',
        'Good time to talk: ' . ($when !== '' ? $when : 'not given'),
        'Their note: ' . ($note !== '' ? $note : 'none'),
        '',
        'Phone: ' . ($lead['mobile'] ?: 'not given'),
        'Email: ' . ($lead['email'] ?? ''),
        'Open the lead: ' . SITE_URL . '/admin/?view=lead&id=' . $leadId,
    ]), ['reply_to' => (string) ($lead['email'] ?? '')]);
}

/**
 * Public routes the CRM owns: /p/o/<token>.gif (open pixel), /proposal/<token>/
 * (the web proposal, its read beacon and the go-ahead form) and
 * /cron/crm-daily/ (the morning digest). Returns true when it answered.
 */
function crm_route(string $slug): bool
{
    if (preg_match('#^p/o/([a-f0-9]{32})\.gif$#', $slug, $m)) {
        crm_track_open($m[1]);
        header('Content-Type: image/gif');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('X-Robots-Tag: noindex');
        echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        return true;
    }
    if (preg_match('#^proposal/([a-f0-9]{32})(/seen)?$#', $slug, $m)) {
        header('X-Robots-Tag: noindex, nofollow');
        header('Cache-Control: private, no-store');
        $token = $m[1];
        if (!empty($m[2])) {
            if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && security_rate_ok('proposal-seen', 60, 3600, $token)) {
                crm_track_view($token, ($_GET['first'] ?? '') === '1', (int) ($_GET['t'] ?? 0));
            }
            http_response_code(204);
            return true;
        }
        $p = null;
        try {
            $p = crm_proposal_by_token($token);
        } catch (Throwable $e) {
            error_log('crm_route proposal: ' . $e->getMessage());
        }
        $lead = $p ? crm_lead((int) $p['enquiry_id']) : null;
        if (!$p || !$lead || $p['kind'] !== 'proposal') {
            http_response_code(404);
            echo '<!DOCTYPE html><meta charset="utf-8"><meta name="robots" content="noindex"><title>Proposal not found</title><p style="font:16px system-ui;padding:40px">This proposal link is not valid. Please reply to the email it came in, or write to ' . htmlspecialchars(CONTACT_EMAIL, ENT_QUOTES, 'UTF-8') . '.</p>';
            return true;
        }
        $preview = hash_equals(substr(hash_hmac('sha256', 'preview|' . $token, SECURITY_SECRET), 0, 24), (string) ($_GET['preview'] ?? ''));
        $state = !empty($p['accepted_at']) ? 'accepted' : 'open';
        if (!$preview && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['action'] ?? '') === 'accept') {
            if (!csrf_verify((string) ($_POST['csrf_token'] ?? '')) || !security_rate_ok('proposal-accept', 5, 3600, $token)) {
                $state = 'error';
            } else {
                try {
                    crm_accept($p, (string) ($_POST['when'] ?? ''), (string) ($_POST['note'] ?? ''));
                    $state = 'thanks';
                } catch (Throwable $e) {
                    error_log('crm_accept: ' . $e->getMessage());
                    $state = 'error';
                }
            }
        }
        crm_render_proposal_page($p, $lead, crm_settings(), $state, $preview);
        return true;
    }
    if ($slug === 'cron/crm-daily') {
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store');
        $secret = (string) env_var('CRON_SECRET', '');
        $auth = (string) ($_SERVER['HTTP_AUTHORIZATION'] ?? '');
        if ($secret === '' || !hash_equals('Bearer ' . $secret, $auth)) {
            http_response_code(401);
            echo '{"ok":false}';
            return true;
        }
        echo json_encode(crm_daily_digest(), JSON_UNESCAPED_UNICODE);
        return true;
    }
    return false;
}

/** The web proposal: the same letter, laid out as a document, with the go-ahead form beside it. */
function crm_render_proposal_page(array $p, array $lead, array $settings, string $state, bool $preview): void
{
    $f = crm_lead_facts($lead);
    $h = static fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    $who = $f['company'] !== '' ? $f['company'] : $f['name'];
    $wa = 'https://wa.me/' . CONTACT_WHATSAPP . '?text=' . rawurlencode('Hi, this is ' . $f['name'] . ' about the proposal for ' . $who . '.');
    $token = (string) $p['token'];
    header('Content-Type: text/html; charset=UTF-8');
    ?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= $h('Proposal for ' . $who . ' | VTurnU') ?></title>
<link rel="icon" type="image/svg+xml" href="/assets/img/vturnu-icon-mark.svg">
<style>
:root { --ink:#1D1936; --soft:#4A4470; --muted:#6F6992; --line:#E5E3F5; --bg:#F7F6FD; --v:#4338CA; --p:#FF3D8A; --grad:linear-gradient(120deg,#00B8D9,#4338CA 40%,#FF3D8A 72%,#FF9100 100%); }
* { box-sizing:border-box; }
body { margin:0; background:var(--bg); color:var(--ink); font:16px/1.7 'Segoe UI',system-ui,-apple-system,Arial,sans-serif; }
.bar { height:5px; background:var(--grad); }
.wrap { max-width:1080px; margin:0 auto; padding:28px 18px 60px; display:grid; grid-template-columns:minmax(0,1fr) 320px; gap:26px; align-items:start; }
header { max-width:1080px; margin:0 auto; padding:26px 18px 0; display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap; }
header img { display:block; height:50px; width:auto; }
header p { margin:0; color:var(--muted); font-size:.86rem; }
.doc { background:#fff; border:1px solid var(--line); border-radius:16px; padding:34px 38px; box-shadow:0 18px 50px rgba(29,25,54,.06); }
.doc .eyebrow { font-size:.74rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--v); margin:0 0 4px; }
.doc h1 { font-size:1.6rem; line-height:1.25; margin:0 0 20px; letter-spacing:-.01em; }
.doc h2 { font-size:1.06rem; margin:26px 0 8px; color:var(--v); }
.doc p { margin:0 0 14px; }
.doc ul { margin:0 0 14px; padding-left:20px; }
.doc li { margin:0 0 8px; }
.doc a { color:var(--v); }
aside { position:sticky; top:20px; display:grid; gap:14px; }
.card { background:#fff; border:1px solid var(--line); border-radius:16px; padding:20px; }
.card h2 { font-size:1.02rem; margin:0 0 6px; }
.card p { margin:0 0 12px; color:var(--soft); font-size:.92rem; }
label { display:block; font-size:.78rem; font-weight:700; color:var(--soft); margin:10px 0 4px; }
input, textarea { width:100%; font:inherit; font-size:.92rem; padding:10px 12px; border:1px solid var(--line); border-radius:10px; }
textarea { min-height:80px; resize:vertical; }
.btn { display:block; width:100%; text-align:center; margin-top:12px; padding:12px 16px; border-radius:999px; border:0; font:inherit; font-weight:700; cursor:pointer; text-decoration:none; }
.btn.go { background:var(--v); color:#fff; }
.btn.wa { background:#E7F8EE; color:#11733E; }
.btn.call { background:var(--bg); color:var(--ink); border:1px solid var(--line); }
.ok { background:#E7F8EE; border-color:#B9E8CB; }
.warn { background:#FFF4E5; border-color:#FFD8A8; }
.who { display:flex; gap:12px; align-items:center; }
.who img { width:44px; height:44px; border-radius:10px; }
.who b { display:block; }
.who span { color:var(--muted); font-size:.86rem; }
.social { display:flex; gap:8px; align-items:center; margin-top:14px; }
.social img { display:block; }
.social .site { margin-left:auto; font-weight:700; font-size:.86rem; color:var(--v); text-decoration:none; }
footer { text-align:center; color:var(--muted); font-size:.8rem; padding:0 18px 30px; }
@media (max-width:860px) { .wrap { grid-template-columns:1fr; } aside { position:static; } .doc { padding:24px 20px; } }
@media print { aside, .bar { display:none; } .wrap { display:block; } .doc { border:0; box-shadow:none; } }
</style>
</head>
<body>
<div class="bar"></div>
<header>
    <a href="/" title="VTurnU"><img src="/assets/img/email/sig-logo.png" alt="VTurnU, together we turn heads" width="181" height="50"></a>
    <p>Prepared for <?= $h($f['name']) ?><?= $f['company'] !== '' ? ', ' . $h($f['company']) : '' ?> · <?= $h(date('j F Y', (int) strtotime((string) $p['sent_at']))) ?></p>
</header>
<div class="wrap">
    <article class="doc">
        <p class="eyebrow">Proposal</p>
        <h1><?= $h(ucfirst((string) preg_replace('/^[^:]{1,60}:\s*/', '', (string) $p['subject']))) ?></h1>
        <?= crm_markup_html((string) $p['body'], 'web') ?>
    </article>
    <aside>
        <div class="card">
            <div class="who"><img src="/assets/img/vturnu-icon-180.png" alt="" width="44" height="44"><div><b><?= $h((string) $settings['sender_name']) ?></b><span><?= $h((string) $settings['sender_title']) ?>, VTurnU</span></div></div>
            <div class="social">
                <?php foreach (CRM_SIG_SOCIAL as $key => $file): if (empty(SOCIAL_LINKS[$key])) { continue; } $label = $key === 'Twitter' ? 'X' : $key; ?>
                <a href="<?= $h((string) SOCIAL_LINKS[$key]) ?>" target="_blank" rel="noopener" title="VTurnU on <?= $h($label) ?>"><img src="/assets/img/email/<?= $h($file) ?>" width="32" height="32" alt="<?= $h($label) ?>"></a>
                <?php endforeach; ?>
                <a class="site" href="/">www.vturnu.com</a>
            </div>
        </div>
        <?php if ($preview): ?>
        <div class="card warn"><h2>Preview</h2><p>This is how <?= $h($f['first']) ?> sees the proposal. Opening it from the admin does not count as a read.</p></div>
        <?php endif; ?>
        <?php if ($state === 'accepted' || $state === 'thanks'): ?>
        <div class="card ok">
            <h2><?= $state === 'thanks' ? 'Thank you, we have your go-ahead' : 'You have already said yes' ?></h2>
            <p><?= $h(crm_first_name((string) $settings['sender_name'])) ?> will be in touch shortly to set up the call. If you would rather talk now, WhatsApp or call below.</p>
        </div>
        <?php else: ?>
        <div class="card">
            <h2>Ready to go ahead?</h2>
            <p>Tell us a good time to talk and we will set up a 20-minute call to confirm the scope and a fixed quote.</p>
            <?php if ($state === 'error'): ?><p style="color:#C22349">That did not go through. Please try again, or use WhatsApp below.</p><?php endif; ?>
            <form method="post" action="/proposal/<?= $h($token) ?>/">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="accept">
                <label for="pr-when">A good time to talk</label>
                <input id="pr-when" name="when" type="text" maxlength="120" placeholder="For example: Thursday after 3 pm">
                <label for="pr-note">Anything to add <span style="font-weight:400">(optional)</span></label>
                <textarea id="pr-note" name="note" maxlength="1500" placeholder="Questions, changes or who else should join the call"></textarea>
                <button class="btn go" type="submit"<?= $preview ? ' disabled' : '' ?>>Yes, let's talk</button>
            </form>
        </div>
        <?php endif; ?>
        <div class="card">
            <a class="btn wa" href="<?= $h($wa) ?>" target="_blank" rel="noopener">WhatsApp us</a>
            <a class="btn call" href="<?= $h(CONTACT_PHONE_HREF) ?>">Call <?= $h(CONTACT_PHONE) ?></a>
        </div>
    </aside>
</div>
<footer>This proposal was prepared for you by VTurnU Digital Solutions LLP and is private to you. <a href="/" style="color:inherit">vturnu.com</a></footer>
<?php if (!$preview): ?>
<script>
(function () {
    var url = '/proposal/<?= $h($token) ?>/seen';
    var start = Date.now(), sent = false;
    function seconds() { return Math.round((Date.now() - start) / 1000); }
    setTimeout(function () {
        if (document.visibilityState === 'hidden') { return; }
        sent = true;
        fetch(url + '?first=1&t=' + seconds(), { method: 'POST', keepalive: true });
    }, 3000);
    document.addEventListener('visibilitychange', function () {
        if (sent && document.visibilityState === 'hidden') { navigator.sendBeacon(url + '?t=' + seconds()); }
    });
})();
</script>
<?php endif; ?>
</body>
</html>
<?php
}

/* ------------------------------------------------------------------ */
/* The morning digest                                                  */
/* ------------------------------------------------------------------ */

/**
 * One email each morning with what needs a person today: new leads, leads
 * still waiting for a first reply, follow-ups due, proposals being read and
 * proposals accepted. Nothing to report means no email.
 *
 * @return array{ok:bool,sent:bool,counts:array<string,int>}
 */
function crm_daily_digest(bool $force = false): array
{
    $settings = crm_settings();
    if (empty($settings['digest']) && !$force) {
        return ['ok' => true, 'sent' => false, 'counts' => []];
    }
    $q = static function (string $sql): array {
        try {
            return db()->query($sql)->fetchAll();
        } catch (Throwable $e) {
            error_log('crm_daily_digest: ' . $e->getMessage());
            return [];
        }
    };
    $sales = "source NOT IN ('newsletter', 'resource', 'seo-audit')";
    $sections = [
        'New in the last 24 hours' => $q("SELECT id, name, company, service, channel, grade FROM enquiries WHERE deleted_at IS NULL AND status <> 'junk' AND $sales AND created_at > now() - interval '24 hours' ORDER BY created_at DESC LIMIT 25"),
        'Still waiting for a first reply' => $q("SELECT id, name, company, service, channel, grade, created_at FROM enquiries WHERE deleted_at IS NULL AND status = 'new' AND $sales AND created_at <= now() - interval '24 hours' ORDER BY created_at LIMIT 25"),
        'Follow-ups due' => $q("SELECT id, name, company, service, channel, grade, followup FROM enquiries WHERE deleted_at IS NULL AND followup <= current_date AND status NOT IN ('won', 'lost', 'junk') ORDER BY followup LIMIT 25"),
        'Proposals read in the last 24 hours' => $q("SELECT e.id, e.name, e.company, e.service, e.channel, e.grade FROM proposals p JOIN enquiries e ON e.id = p.enquiry_id WHERE p.last_viewed_at > now() - interval '24 hours' AND p.accepted_at IS NULL AND e.deleted_at IS NULL ORDER BY p.last_viewed_at DESC LIMIT 25"),
        'Accepted in the last 24 hours' => $q("SELECT e.id, e.name, e.company, e.service, e.channel, e.grade FROM proposals p JOIN enquiries e ON e.id = p.enquiry_id WHERE p.accepted_at > now() - interval '24 hours' AND e.deleted_at IS NULL ORDER BY p.accepted_at DESC LIMIT 25"),
    ];
    $counts = array_map('count', $sections);
    if (array_sum($counts) === 0 && !$force) {
        return ['ok' => true, 'sent' => false, 'counts' => $counts];
    }
    $lines = ['Good morning. Here is what needs you today.', ''];
    foreach ($sections as $title => $rows) {
        if (!$rows) {
            continue;
        }
        $lines[] = mb_strtoupper($title) . ' (' . count($rows) . ')';
        foreach ($rows as $r) {
            $lines[] = '  * ' . $r['name'] . ($r['company'] !== '' ? ', ' . $r['company'] : '') . ' · ' . ($r['service'] ?: 'no service chosen')
                . ($r['channel'] !== '' ? ' · ' . $r['channel'] : '') . ($r['grade'] !== '' ? ' · grade ' . $r['grade'] : '')
                . "\n    " . SITE_URL . '/admin/?view=lead&id=' . $r['id'];
        }
        $lines[] = '';
    }
    if (array_sum($counts) === 0) {
        $lines[] = 'Nothing needs you today.';
    }
    $lines[] = 'Open the CRM: ' . SITE_URL . '/admin/';
    $parts = array_filter([
        $counts['New in the last 24 hours'] ? $counts['New in the last 24 hours'] . ' new' : '',
        $counts['Follow-ups due'] ? $counts['Follow-ups due'] . ' to follow up' : '',
        $counts['Accepted in the last 24 hours'] ? $counts['Accepted in the last 24 hours'] . ' accepted' : '',
    ]);
    $sent = send_email(ENQUIRY_TO, 'Leads today: ' . ($parts ? implode(', ', $parts) : 'your daily summary'), implode("\n", $lines));
    return ['ok' => true, 'sent' => $sent, 'counts' => $counts];
}
