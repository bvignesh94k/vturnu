<?php
/**
 * Free SEO / AEO audit.
 *
 * Fetches a visitor-supplied URL, reads the HTML and reports on indexability,
 * on-page SEO and AI-search readiness. No database, no third-party API.
 *
 * Deliberate limits: it reads the served HTML only. It cannot see
 * JavaScript-rendered content, backlinks, rankings or field Core Web Vitals,
 * and it never claims to. Those are named as "checked manually" instead.
 */

declare(strict_types=1);

const AUDIT_TIMEOUT      = 12;        // seconds for the whole fetch
const AUDIT_CONNECT      = 6;
const AUDIT_MAX_BYTES    = 3145728;   // 3 MB, plenty for HTML
const AUDIT_RATE_MAX     = 6;         // audits per IP
const AUDIT_RATE_WINDOW  = 3600;      // per hour

/**
 * Normalise and safety-check a submitted URL.
 *
 * Blocks anything that is not public http(s). Without this the endpoint would
 * happily fetch localhost, private LAN hosts or cloud metadata services on
 * behalf of a stranger, turning the site into an SSRF proxy.
 *
 * @return array{0:?string,1:string} [safe_url|null, error]
 */
function audit_safe_url(string $raw): array
{
    $raw = trim($raw);
    if ($raw === '') {
        return [null, 'Please enter your website address.'];
    }
    if (!preg_match('#^https?://#i', $raw)) {
        $raw = 'https://' . $raw;
    }
    if (!filter_var($raw, FILTER_VALIDATE_URL)) {
        return [null, 'That does not look like a valid website address.'];
    }

    $parts = parse_url($raw);
    $scheme = strtolower($parts['scheme'] ?? '');
    $host = $parts['host'] ?? '';

    if (!in_array($scheme, ['http', 'https'], true)) {
        return [null, 'Only http and https addresses can be audited.'];
    }
    if ($host === '' || !str_contains($host, '.')) {
        return [null, 'Please include the full domain, for example example.com.'];
    }
    if (isset($parts['port']) && !in_array((int) $parts['port'], [80, 443], true)) {
        return [null, 'Only standard web ports can be audited.'];
    }

    // Resolve and reject anything pointing inside the network.
    $ips = gethostbynamel($host);
    if (!$ips) {
        return [null, 'We could not find that domain. Please check the spelling.'];
    }
    foreach ($ips as $ip) {
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return [null, 'That address resolves to a private network and cannot be audited.'];
        }
    }

    return [$raw, ''];
}

/** Simple per-IP throttle so the endpoint cannot be used to hammer other sites.
 *  Same 'forms'/'admin-lockout' rate_limits table as security.php, under its
 *  own 'audit' bucket. Fails open on a database error: an audit-tool hiccup
 *  must not block a real visitor. */
function audit_rate_ok(string $ip): bool
{
    $key = hash('sha256', $ip);
    $now = time();
    try {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT hits FROM rate_limits WHERE bucket = ? AND key_hash = ?');
        $stmt->execute(['audit', $key]);
        $row = $stmt->fetch();
        $hits = $row ? (json_decode($row['hits'], true) ?: []) : [];
        $hits = array_values(array_filter($hits, fn($t) => $now - $t < AUDIT_RATE_WINDOW));

        if (count($hits) >= AUDIT_RATE_MAX) {
            return false;
        }
        $hits[] = $now;
        $pdo->prepare(
            'INSERT INTO rate_limits (bucket, key_hash, hits, updated_at) VALUES (?, ?, ?::jsonb, now())
             ON CONFLICT (bucket, key_hash) DO UPDATE SET hits = excluded.hits, updated_at = now()'
        )->execute(['audit', $key, json_encode($hits)]);
        return true;
    } catch (Throwable $e) {
        error_log('audit_rate_ok: ' . $e->getMessage());
        return true;
    }
}

/** Fetch a URL with size and time caps. */
function audit_fetch(string $url, ?int $timeout = null): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 5,
        CURLOPT_TIMEOUT        => $timeout ?? AUDIT_TIMEOUT,
        CURLOPT_CONNECTTIMEOUT => AUDIT_CONNECT,
        CURLOPT_ENCODING       => '',       // advertise gzip so we can detect it
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_USERAGENT      => 'VTurnU-SEO-Audit/1.0 (+' . SITE_URL . ')',
        CURLOPT_NOPROGRESS     => false,
        CURLOPT_PROGRESSFUNCTION => fn($r, $dl) => $dl > AUDIT_MAX_BYTES ? 1 : 0,
    ]);
    $t0 = microtime(true);
    $body = curl_exec($ch);
    $ms = (int) round((microtime(true) - $t0) * 1000);
    $info = curl_getinfo($ch);
    $err = curl_error($ch);
    // Deprecated in PHP 8.5 (the version Vercel runs) and a no-op since 8.0.

    return ['body' => $body === false ? '' : $body, 'ms' => $ms, 'info' => $info, 'error' => $err];
}

/** Head-only check that a URL exists (used for sitemap / llms.txt). */
function audit_exists(string $url): bool
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        // Kept short: these are secondary checks and must not stall the report.
        CURLOPT_NOBODY => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_MAXREDIRS => 2,
        CURLOPT_TIMEOUT => 4, CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_USERAGENT => 'VTurnU-SEO-Audit/1.0',
    ]);
    curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    // Deprecated in PHP 8.5 (the version Vercel runs) and a no-op since 8.0.
    return $code >= 200 && $code < 400;
}

/** One report line. */
function audit_check(string $status, string $label, string $detail, string $fix = ''): array
{
    return ['status' => $status, 'label' => $label, 'detail' => $detail, 'fix' => $fix];
}

/**
 * Run the audit.
 *
 * @return array{ok:bool,error:string,url:string,score:int,grade:string,groups:array,summary:array}
 */
function run_site_audit(string $url): array
{
    $fail = fn(string $m) => ['ok' => false, 'error' => $m, 'url' => $url, 'score' => 0,
                              'grade' => '', 'groups' => [], 'summary' => []];

    $res = audit_fetch($url);
    if ($res['body'] === '') {
        return $fail($res['error'] !== ''
            ? 'We could not load that site (' . htmlspecialchars($res['error'], ENT_QUOTES) . '). It may be offline or blocking automated requests.'
            : 'We could not load that site. It may be offline or blocking automated requests.');
    }

    $html  = $res['body'];
    $info  = $res['info'];
    $final = $info['url'] ?? $url;
    $code  = (int) ($info['http_code'] ?? 0);

    $scheme = parse_url($final, PHP_URL_SCHEME) ?: 'http';
    $host   = parse_url($final, PHP_URL_HOST) ?: '';
    $port   = parse_url($final, PHP_URL_PORT);
    $origin = $scheme . '://' . $host . ($port && !in_array($port, [80, 443], true) ? ':' . $port : '');

    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);
    libxml_clear_errors();
    $xp = new DOMXPath($doc);

    $attr = function (string $q, string $a = 'content') use ($xp): string {
        $n = $xp->query($q);
        return $n && $n->length ? trim($n->item(0)->getAttribute($a)) : '';
    };
    $nodes = fn(string $q) => $xp->query($q) ?: new DOMNodeList();
    $num   = fn(string $q) => ($n = $xp->query($q)) ? $n->length : 0;

    $groups = [];

    /* ---------- 1. Indexability and delivery ---------- */
    $g = [];

    $g[] = $code === 200
        ? audit_check('pass', 'Page loads correctly', 'Returns HTTP 200.')
        : audit_check('fail', 'Page does not return 200', 'The homepage returns HTTP ' . $code . '.', 'Any status other than 200 stops search engines indexing the page.');

    $isHttps = str_starts_with($final, 'https://');
    $g[] = $isHttps
        ? audit_check('pass', 'Secure (HTTPS)', 'The site is served over HTTPS.')
        : audit_check('fail', 'No HTTPS', 'This site is served over plain HTTP.', 'Google treats HTTPS as a ranking signal and browsers mark HTTP pages "Not secure". Install an SSL certificate and force the redirect.');

    $robotsMeta = strtolower($attr('//meta[@name="robots"]'));
    if (str_contains($robotsMeta, 'noindex')) {
        $g[] = audit_check('fail', 'Page is set to noindex', 'A robots meta tag is telling search engines not to index this page.', 'This single tag removes the page from Google entirely. It is the first thing to fix.');
    } else {
        $g[] = audit_check('pass', 'Indexable', 'No noindex tag blocking search engines.');
    }

    $canonical = $attr('//link[@rel="canonical"]', 'href');
    $g[] = $canonical !== ''
        ? audit_check('pass', 'Canonical tag set', 'Points to ' . $canonical)
        : audit_check('warn', 'No canonical tag', 'This page has no canonical URL.', 'Canonicals prevent duplicate-content dilution when the same page is reachable at several URLs.');

    $robotsTxt = '';
    $rt = audit_fetch($origin . '/robots.txt', 5);
    if (($rt['info']['http_code'] ?? 0) === 200) { $robotsTxt = $rt['body']; }
    $g[] = $robotsTxt !== ''
        ? audit_check('pass', 'robots.txt found', 'Crawlers have clear instructions.')
        : audit_check('warn', 'No robots.txt', 'No robots.txt at ' . $origin . '/robots.txt', 'Add one so you control what gets crawled and can point to your sitemap.');

    $sitemapRef = preg_match('/Sitemap:\s*(\S+)/i', $robotsTxt, $m) ? $m[1] : '';
    $hasSitemap = $sitemapRef !== '' || audit_exists($origin . '/sitemap.xml');
    $g[] = $hasSitemap
        ? audit_check('pass', 'XML sitemap found', $sitemapRef !== '' ? 'Referenced in robots.txt.' : 'Found at /sitemap.xml')
        : audit_check('warn', 'No XML sitemap', 'No sitemap found.', 'A sitemap helps search engines discover every page, especially on larger sites.');

    $ms = $res['ms'];
    $g[] = $ms < 800
        ? audit_check('pass', 'Fast server response', $ms . ' ms to load the HTML.')
        : ($ms < 2000
            ? audit_check('warn', 'Server response is slow', $ms . ' ms to load the HTML.', 'Aim for under 800 ms. Slow responses hurt both rankings and conversion.')
            : audit_check('fail', 'Server response is very slow', $ms . ' ms to load the HTML.', 'Over 2 seconds before the page even starts rendering. Caching or better hosting usually fixes this.'));

    $kb = strlen($html) / 1024;
    $g[] = $kb < 150
        ? audit_check('pass', 'Reasonable page weight', sprintf('%.0f KB of HTML.', $kb))
        : audit_check('warn', 'Heavy HTML', sprintf('%.0f KB of HTML.', $kb), 'Large HTML delays first paint, especially on mobile networks.');

    $compressed = ($info['size_download'] ?? 0) > 0 && ($info['size_download'] ?? 0) < strlen($html);
    $g[] = $compressed
        ? audit_check('pass', 'Compression enabled', 'Text is served gzip/brotli compressed.')
        : audit_check('warn', 'No compression detected', 'The HTML does not appear to be compressed.', 'Enabling gzip or brotli typically cuts text transfer by 60-80%.');

    $groups['Indexability and speed'] = $g;

    /* ---------- 2. On-page SEO ---------- */
    $g = [];

    $titleNode = $nodes('//title');
    $title = $titleNode->length ? trim($titleNode->item(0)->textContent) : '';
    $tl = mb_strlen($title);
    if ($title === '') {
        $g[] = audit_check('fail', 'Missing title tag', 'This page has no title.', 'The title is the single strongest on-page signal and is what users click in search results.');
    } elseif ($tl > 65) {
        $g[] = audit_check('warn', 'Title too long', $tl . ' characters. Google truncates around 60.', 'Trim to 55-63 characters so the whole message shows in results.');
    } elseif ($tl < 30) {
        $g[] = audit_check('warn', 'Title too short', $tl . ' characters.', 'You are leaving room unused. Add the primary keyword and a benefit.');
    } else {
        $g[] = audit_check('pass', 'Title length is good', $tl . ' characters.');
    }

    $desc = $attr('//meta[@name="description"]');
    $dl = mb_strlen($desc);
    if ($desc === '') {
        $g[] = audit_check('fail', 'Missing meta description', 'No meta description found.', 'Google writes its own when this is missing, and it is usually worse at selling the click.');
    } elseif ($dl > 165) {
        $g[] = audit_check('warn', 'Meta description too long', $dl . ' characters.', 'Keep under 160 so it is not cut off.');
    } elseif ($dl < 70) {
        $g[] = audit_check('warn', 'Meta description too short', $dl . ' characters.', 'Use 140-160 characters and include a reason to click.');
    } else {
        $g[] = audit_check('pass', 'Meta description length is good', $dl . ' characters.');
    }

    $h1 = $nodes('//h1');
    if ($h1->length === 0) {
        $g[] = audit_check('fail', 'No H1 heading', 'This page has no H1.', 'The H1 tells search engines and readers what the page is about.');
    } elseif ($h1->length > 1) {
        $g[] = audit_check('warn', 'Multiple H1 headings', $h1->length . ' H1 tags found.', 'Use exactly one H1 per page so the topic is unambiguous.');
    } else {
        $g[] = audit_check('pass', 'Single clear H1', '"' . mb_substr(trim($h1->item(0)->textContent), 0, 70) . '"');
    }

    $h2 = $num('//h2');
    $g[] = $h2 >= 2
        ? audit_check('pass', 'Good heading structure', $h2 . ' H2 subheadings.')
        : audit_check('warn', 'Thin heading structure', $h2 . ' H2 subheadings.', 'Subheadings help both readers and AI engines understand and quote your content.');

    $text = trim(preg_replace('/\s+/', ' ', strip_tags(preg_replace('#<(script|style|noscript)[^>]*>.*?</\1>#is', ' ', $html))));
    $words = str_word_count($text);
    $g[] = $words >= 600
        ? audit_check('pass', 'Enough content to rank', '~' . number_format($words) . ' words.')
        : ($words >= 250
            ? audit_check('warn', 'Content is thin', '~' . number_format($words) . ' words.', 'Pages under about 600 words rarely compete for commercial terms.')
            : audit_check('fail', 'Very little content', '~' . number_format($words) . ' words.', 'There is not enough here for search engines to judge relevance. This may also mean the content is rendered by JavaScript, which limits how AI engines read it.'));

    $imgs = $nodes('//img');
    $noAlt = 0;
    foreach ($imgs as $img) { if (trim($img->getAttribute('alt')) === '') $noAlt++; }
    if ($imgs->length === 0) {
        $g[] = audit_check('warn', 'No images found', 'This page has no images.', 'Images improve engagement and give you another route into image search.');
    } elseif ($noAlt === 0) {
        $g[] = audit_check('pass', 'All images have alt text', $imgs->length . ' images, all described.');
    } else {
        $g[] = audit_check($noAlt > $imgs->length / 2 ? 'fail' : 'warn', 'Images missing alt text',
            $noAlt . ' of ' . $imgs->length . ' images have no alt attribute.',
            'Alt text is needed for accessibility and is a ranking signal for image search.');
    }

    $g[] = $attr('//meta[@name="viewport"]') !== ''
        ? audit_check('pass', 'Mobile viewport set', 'The page is configured for mobile devices.')
        : audit_check('fail', 'No mobile viewport', 'No viewport meta tag.', 'Google indexes mobile-first. Without this the site is judged as not mobile-friendly.');

    $groups['On-page SEO'] = $g;

    /* ---------- 3. AI search readiness (AEO / GEO) ---------- */
    $g = [];

    $schemaTypes = [];
    foreach ($nodes('//script[@type="application/ld+json"]') as $s) {
        if (preg_match_all('/"@type"\s*:\s*"([A-Za-z]+)"/', $s->textContent, $mm)) {
            $schemaTypes = array_merge($schemaTypes, $mm[1]);
        }
    }
    $schemaTypes = array_values(array_unique($schemaTypes));

    $g[] = $schemaTypes
        ? audit_check('pass', 'Structured data present', implode(', ', array_slice($schemaTypes, 0, 8)) . (count($schemaTypes) > 8 ? '...' : ''))
        : audit_check('fail', 'No structured data', 'No JSON-LD schema found.', 'Schema is how you tell Google and AI engines what your business is and does. Without it you are relying on them guessing.');

    $hasOrg = (bool) array_intersect($schemaTypes, ['Organization', 'LocalBusiness', 'ProfessionalService', 'Corporation']);
    $g[] = $hasOrg
        ? audit_check('pass', 'Business entity defined', 'Organization schema found.')
        : audit_check('warn', 'No Organization schema', 'Your business entity is not described in structured data.', 'This is what links your brand to an entity AI engines can recognise and cite.');

    $hasFaq = (bool) array_intersect($schemaTypes, ['FAQPage', 'QAPage', 'Question']);
    $g[] = $hasFaq
        ? audit_check('pass', 'FAQ schema found', 'Question and answer content is marked up.')
        : audit_check('warn', 'No FAQ schema', 'No question-and-answer markup.', 'FAQ blocks are among the most frequently quoted formats in AI Overviews and ChatGPT answers.');

    $blockedAi = [];
    foreach (['GPTBot' => 'ChatGPT', 'ClaudeBot' => 'Claude', 'PerplexityBot' => 'Perplexity',
              'Google-Extended' => 'Google AI', 'CCBot' => 'Common Crawl', 'anthropic-ai' => 'Anthropic'] as $bot => $friendly) {
        if (preg_match('/User-agent:\s*' . preg_quote($bot, '/') . '\b[^\n]*\n(?:\s*(?:#[^\n]*)?\n)*\s*Disallow:\s*\/\s*(?:\n|$)/i', $robotsTxt)) {
            $blockedAi[] = $friendly;
        }
    }
    $g[] = $blockedAi
        ? audit_check('fail', 'Blocking AI search engines', 'robots.txt blocks: ' . implode(', ', $blockedAi) . '.', 'These crawlers feed ChatGPT, Perplexity and Google AI Overviews. While they are blocked your brand cannot be cited in AI answers at all.')
        : audit_check('pass', 'AI crawlers allowed', 'ChatGPT, Claude, Perplexity and Google AI can read this site.');

    $g[] = audit_exists($origin . '/llms.txt')
        ? audit_check('pass', 'llms.txt published', 'An AI-readable site summary is available.')
        : audit_check('warn', 'No llms.txt', 'No AI-readable summary of the site.', 'An emerging standard that gives AI engines a clean map of what you offer and which pages matter.');

    $ogCount = $num('//meta[starts-with(@property,"og:")]');
    $g[] = $ogCount >= 4
        ? audit_check('pass', 'Social sharing tags set', $ogCount . ' Open Graph tags.')
        : audit_check('warn', 'Weak social sharing tags', $ogCount . ' Open Graph tags found.', 'Without these, links shared on LinkedIn, WhatsApp and Slack render as bare URLs.');

    $groups['AI search readiness'] = $g;

    /* ---------- Score ---------- */
    $weights = ['pass' => 1.0, 'warn' => 0.5, 'fail' => 0.0];
    $total = 0; $max = 0; $counts = ['pass' => 0, 'warn' => 0, 'fail' => 0];
    foreach ($groups as $checks) {
        foreach ($checks as $c) {
            $total += $weights[$c['status']];
            $max++;
            $counts[$c['status']]++;
        }
    }
    $score = $max ? (int) round($total / $max * 100) : 0;
    $grade = $score >= 85 ? 'Strong' : ($score >= 65 ? 'Decent, with gaps' : ($score >= 45 ? 'Needs work' : 'Urgent attention'));

    return [
        'ok' => true, 'error' => '', 'url' => $final, 'score' => $score, 'grade' => $grade,
        'groups' => $groups, 'summary' => $counts,
        'checked' => date('j M Y, g:i a'),
    ];
}

/** Email the audit report to the visitor. Lead recording/sales notification happens separately via save_enquiry(). */
function audit_send_mail(array $audit, string $name, string $email): bool
{
    $lines = [];
    $lines[] = 'Hi ' . ($name !== '' ? $name : 'there') . ',';
    $lines[] = '';
    $lines[] = 'Here is your free SEO and AI-search audit for ' . $audit['url'];
    $lines[] = '';
    $lines[] = 'OVERALL SCORE: ' . $audit['score'] . '/100 (' . $audit['grade'] . ')';
    $lines[] = sprintf('%d passed, %d need attention, %d critical',
        $audit['summary']['pass'], $audit['summary']['warn'], $audit['summary']['fail']);
    $lines[] = '';

    foreach ($audit['groups'] as $heading => $checks) {
        $lines[] = strtoupper($heading);
        $lines[] = str_repeat('-', strlen($heading));
        foreach ($checks as $c) {
            $mark = ['pass' => '[OK]  ', 'warn' => '[!]   ', 'fail' => '[FIX] '][$c['status']];
            $lines[] = $mark . $c['label'];
            $lines[] = '      ' . $c['detail'];
            if ($c['fix'] !== '' && $c['status'] !== 'pass') {
                $lines[] = '      Why it matters: ' . $c['fix'];
            }
            $lines[] = '';
        }
    }

    $lines[] = 'WHAT THIS AUDIT DOES NOT COVER';
    $lines[] = 'This is an automated read of your homepage HTML. Backlink profile,';
    $lines[] = 'keyword rankings, competitor gaps and Core Web Vitals field data need';
    $lines[] = 'manual review. We cover those in the full audit, free, on a short call.';
    $lines[] = '';
    $lines[] = 'Reply to this email or call ' . CONTACT_PHONE . ' and we will walk you';
    $lines[] = 'through the fixes in priority order.';
    $lines[] = '';
    $lines[] = SITE_NAME . ' | ' . SITE_URL;

    $subject = 'Your SEO audit: ' . $audit['score'] . '/100 for ' . parse_url($audit['url'], PHP_URL_HOST);
    return send_email($email, $subject, implode("\n", $lines), ['reply_to' => CONTACT_EMAIL]);
}
