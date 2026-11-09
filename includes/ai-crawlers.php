<?php
/**
 * AI Crawler Checker (/ai-crawler-checker/).
 *
 * A free tool: it reads a site's robots.txt the way crawlers are told to
 * (RFC 9309) and reports, crawler by crawler, whether the major AI crawlers
 * may fetch a page. It also reads the page's HTML without running JavaScript,
 * because several AI crawlers do not execute it.
 *
 * What each crawler is for comes from the vendor's own documentation, checked
 * on AIC_VERIFIED. Vendors add and rename crawlers often: recheck the list
 * when that date is a few months old.
 *
 * Deliberate limits, also stated on the page: it cannot see firewall or CDN
 * bot blocking, whether a crawler really visited, or what a crawler that does
 * run JavaScript would render.
 */

declare(strict_types=1);

const AIC_VERIFIED = '2026-11-07';
const AIC_UA = 'VTurnU-AI-Crawler-Checker/1.0 (+https://vturnu.com/ai-crawler-checker/)';
const AIC_CONNECT = 4;          // seconds to connect
const AIC_MAX_BYTES = 2097152;  // 2 MB per response is plenty for HTML
const AIC_ROBOTS_MAX = 512000;  // Google reads the first 500 KiB of robots.txt
const AIC_RATE_MAX = 20;        // checks per visitor IP
const AIC_RATE_WINDOW = 3600;   // per hour

/** Crawler groups in display order: [heading, one-line explanation]. */
function aic_groups(): array
{
    return [
        'search' => ['Search and AI answers', 'Crawlers that decide whether search engines and AI assistants can find and cite your pages.'],
        'user' => ['Fetches when a person asks', 'Fetchers that load a page because someone asked an assistant about it.'],
        'training' => ['AI training and datasets', 'Crawlers and controls that decide whether your content can be used to build AI models.'],
    ];
}

/**
 * The crawlers checked: [robots.txt token, company, group, what it does,
 * the company's note on robots.txt where it matters, official docs].
 * Every line restates the vendor's own page; nothing here is inferred.
 */
function aic_crawlers(): array
{
    $openai = 'https://developers.openai.com/api/docs/bots';
    $anthropic = 'https://support.claude.com/en/articles/8896518-does-anthropic-crawl-data-from-the-web-and-how-can-site-owners-block-the-crawler';
    $perplexity = 'https://docs.perplexity.ai/guides/bots';
    $google_ai = 'https://developers.google.com/search/docs/appearance/ai-features';
    $google = 'https://developers.google.com/search/docs/crawling-indexing/google-common-crawlers';
    $bing = 'https://www.bing.com/webmasters/help/which-crawlers-does-bing-use-8c184ec0';
    $apple = 'https://support.apple.com/en-us/119829';
    $ddg = 'https://duckduckgo.com/duckduckgo-help-pages/results/duckassistbot';
    $amazon = 'https://developer.amazon.com/amazonbot';
    $meta = 'https://developers.facebook.com/docs/sharing/webmasters/web-crawlers';
    $cc = 'https://commoncrawl.org/ccbot';
    $mistral = 'https://docs.mistral.ai/robots';

    return [
        ['OAI-SearchBot', 'OpenAI', 'search', 'Finds pages for ChatGPT search results.', '', $openai],
        ['Claude-SearchBot', 'Anthropic', 'search', 'Crawls to improve the quality of Claude\'s search results.', '', $anthropic],
        ['PerplexityBot', 'Perplexity', 'search', 'Surfaces and links websites in Perplexity search results. Not used to train AI models.', '', $perplexity],
        ['Googlebot', 'Google', 'search', 'Google Search\'s crawler. A page must be indexed by Google Search, with a snippet allowed, to appear as a link in AI Overviews and AI Mode.', '', $google_ai],
        ['Bingbot', 'Microsoft', 'search', 'Bing\'s standard crawler, which handles most of Bing\'s crawling.', '', $bing],
        ['Applebot', 'Apple', 'search', 'Powers search in Spotlight, Siri and Safari. Apple says the data may also help train its foundation models.', '', $apple],
        ['DuckAssistBot', 'DuckDuckGo', 'search', 'Fetches pages in real time for DuckDuckGo\'s AI-assisted answers. Not used to train AI models.', '', $ddg],
        ['Amzn-SearchBot', 'Amazon', 'search', 'Improves search in Amazon products and services. Not used for generative AI training.', '', $amazon],
        ['MistralAI-Index', 'Mistral', 'search', 'Indexes pages for Mistral. Not used for training.', '', $mistral],

        ['ChatGPT-User', 'OpenAI', 'user', 'Visits a page when a ChatGPT user\'s request needs it. Not used for automatic crawling.', 'OpenAI says robots.txt rules may not apply.', $openai],
        ['Claude-User', 'Anthropic', 'user', 'Fetches pages to support Claude users\' requests.', '', $anthropic],
        ['Perplexity-User', 'Perplexity', 'user', 'Fetches pages for actions a Perplexity user takes.', 'Perplexity says it generally ignores robots.txt rules.', $perplexity],
        ['meta-externalfetcher', 'Meta', 'user', 'Fetches individual links at a user\'s request.', 'Meta says it may bypass robots.txt rules.', $meta],
        ['Amzn-User', 'Amazon', 'user', 'Fetches live information from the web for Amazon users.', 'Amazon says it may not follow all robots.txt directives.', $amazon],
        ['MistralAI-User', 'Mistral', 'user', 'Fetches pages for users of Mistral\'s assistant. Not used for automatic crawling or training.', '', $mistral],

        ['GPTBot', 'OpenAI', 'training', 'Collects content that may be used to train OpenAI\'s generative AI foundation models.', '', $openai],
        ['ClaudeBot', 'Anthropic', 'training', 'Collects web content that could contribute to training Anthropic\'s models.', '', $anthropic],
        ['Google-Extended', 'Google', 'training', 'A robots.txt control, not a separate crawler. It decides whether content Google crawls may train Gemini models and ground answers in Gemini Apps and Vertex AI. It does not affect Google Search.', '', $google],
        ['Applebot-Extended', 'Apple', 'training', 'A robots.txt control, not a crawler. It decides whether Applebot\'s data may train Apple\'s generative foundation models.', '', $apple],
        ['meta-externalagent', 'Meta', 'training', 'Used to train foundation AI models or improve products by indexing content.', '', $meta],
        ['Amazonbot', 'Amazon', 'training', 'Improves Amazon products and services. Amazon says it may be used to train Amazon AI models.', '', $amazon],
        ['CCBot', 'Common Crawl', 'training', 'Builds Common Crawl\'s open repository of web crawl data.', '', $cc],
        ['MistralAI-Training', 'Mistral', 'training', 'Builds datasets for training Mistral\'s generative AI models.', '', $mistral],
    ];
}

/* ------------------------------------------------------------------ */
/* robots.txt, read the RFC 9309 way                                   */
/* ------------------------------------------------------------------ */

/** The product token a user-agent line names, lowercased; '*' for the catch-all. */
function aic_agent_token(string $value): string
{
    $value = trim($value);
    if ($value !== '' && $value[0] === '*') {
        return '*';
    }
    /* RFC 9309 tokens use letters, underscores and hyphens, so "GPTBot/1.0" means GPTBot. */
    return preg_match('/^[A-Za-z_-]+/', $value, $m) ? strtolower($m[0]) : '';
}

/**
 * Parse robots.txt into groups. User-agent lines open a group (consecutive
 * ones share it), allow and disallow lines belong to the current group, and
 * other lines such as Sitemap or Crawl-delay do not affect grouping.
 *
 * @return array{groups: list<array{agents: list<string>, rules: list<array{0:string,1:string,2:int}>}>, sitemaps: list<string>}
 */
function aic_robots_parse(string $txt): array
{
    $txt = substr($txt, 0, AIC_ROBOTS_MAX);
    if (str_starts_with($txt, "\xEF\xBB\xBF")) {
        $txt = substr($txt, 3);
    }
    $groups = [];
    $sitemaps = [];
    $cur = null;
    $inAgents = false;
    foreach (preg_split('/\r\n|\r|\n/', $txt) as $i => $raw) {
        $hash = strpos($raw, '#');
        $line = trim($hash === false ? $raw : substr($raw, 0, $hash));
        if ($line === '' || !str_contains($line, ':')) {
            continue;
        }
        [$key, $value] = explode(':', $line, 2);
        $key = strtolower(trim($key));
        $value = trim($value);
        if ($key === 'user-agent') {
            if (!$inAgents || $cur === null) {
                $groups[] = ['agents' => [], 'rules' => []];
                $cur = array_key_last($groups);
            }
            $token = aic_agent_token($value);
            if ($token !== '') {
                $groups[$cur]['agents'][] = $token;
            }
            $inAgents = true;
        } elseif ($key === 'allow' || $key === 'disallow') {
            if ($cur !== null) {
                $groups[$cur]['rules'][] = [$key, $value, $i + 1];
            }
            $inAgents = false;
        } elseif ($key === 'sitemap' && $value !== '') {
            $sitemaps[] = $value;
        }
    }
    return ['groups' => $groups, 'sitemaps' => $sitemaps];
}

/**
 * The rules one crawler obeys: every group naming its token, merged, or else
 * the '*' groups, or else none. A group naming the crawler wins even if it has
 * no rules, so "User-agent: GPTBot" with nothing under it allows everything.
 *
 * @return array{rules: list<array{0:string,1:string,2:int}>, agent: ?string}
 */
function aic_rules_for(array $parsed, string $token): array
{
    $t = strtolower($token);
    $specific = $global = [];
    $hasSpecific = $hasGlobal = false;
    foreach ($parsed['groups'] as $g) {
        if (in_array($t, $g['agents'], true)) {
            $hasSpecific = true;
            array_push($specific, ...$g['rules']);
        } elseif (in_array('*', $g['agents'], true)) {
            $hasGlobal = true;
            array_push($global, ...$g['rules']);
        }
    }
    if ($hasSpecific) {
        return ['rules' => $specific, 'agent' => $token];
    }
    if ($hasGlobal) {
        return ['rules' => $global, 'agent' => '*'];
    }
    return ['rules' => [], 'agent' => null];
}

/** Upper-case percent escapes and encode non-ASCII, so patterns and paths compare alike. */
function aic_norm(string $s): string
{
    $s = preg_replace_callback('/%[0-9a-f]{2}/i', static fn($m) => strtoupper($m[0]), $s);
    return preg_replace_callback('/[^\x21-\x7E]/', static fn($m) => rawurlencode($m[0]), $s);
}

/** Does a robots.txt path pattern match? '*' is any run of characters, a final '$' anchors the end. */
function aic_match(string $pattern, string $path): bool
{
    $anchored = str_ends_with($pattern, '$');
    if ($anchored) {
        $pattern = substr($pattern, 0, -1);
    }
    $re = implode('.*', array_map(static fn($p) => preg_quote($p, '#'), explode('*', $pattern)));
    return (bool) preg_match('#^' . $re . ($anchored ? '$' : '') . '#s', $path);
}

/**
 * Allow or disallow one path: the longest matching pattern wins, and an allow
 * wins a tie (RFC 9309, section 2.2.2). An empty Disallow blocks nothing, and
 * /robots.txt itself is always allowed.
 *
 * @return array{allowed: bool, rule: ?array{0:string,1:string,2:int}}
 */
function aic_decide(array $rules, string $path): array
{
    $path = aic_norm($path === '' ? '/' : $path);
    if ($path === '/robots.txt') {
        return ['allowed' => true, 'rule' => null];
    }
    $best = null;
    $bestLen = -1;
    foreach ($rules as $rule) {
        [$type, $pattern] = $rule;
        if ($pattern === '') {
            continue;
        }
        $p = aic_norm($pattern);
        if (!aic_match($p, $path)) {
            continue;
        }
        $len = strlen($p);
        if ($len > $bestLen || ($len === $bestLen && $type === 'allow')) {
            $best = $rule;
            $bestLen = $len;
        }
    }
    return ['allowed' => $best === null || $best[0] === 'allow', 'rule' => $best];
}

/* ------------------------------------------------------------------ */
/* Safe fetching                                                       */
/* ------------------------------------------------------------------ */

/** True only for a public, routable IPv4 address. */
function aic_ip_public(string $ip): bool
{
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        return false;
    }
    $n = ip2long($ip);
    $blocked = [
        ['0.0.0.0', 8], ['10.0.0.0', 8], ['100.64.0.0', 10], ['127.0.0.0', 8], ['169.254.0.0', 16],
        ['172.16.0.0', 12], ['192.0.0.0', 24], ['192.0.2.0', 24], ['192.168.0.0', 16], ['198.18.0.0', 15],
        ['198.51.100.0', 24], ['203.0.113.0', 24], ['224.0.0.0', 4], ['240.0.0.0', 4],
    ];
    foreach ($blocked as [$net, $bits]) {
        $mask = (-1 << (32 - $bits)) & 0xFFFFFFFF;
        if (($n & $mask) === (ip2long($net) & $mask)) {
            return false;
        }
    }
    return true;
}

/** One public IPv4 address for a host, or null when it has none or any of its
 *  addresses is private. $resolve returns the host's IPv4 addresses,
 *  gethostbynamel() by default; tests pass their own because php-wasm fakes DNS. */
function aic_resolve(string $host, ?callable $resolve = null): ?string
{
    $ips = ($resolve ?? 'gethostbynamel')($host) ?: [];
    if (!$ips) {
        return null;
    }
    foreach ($ips as $ip) {
        if (!aic_ip_public($ip)) {
            return null;
        }
    }
    return $ips[0];
}

/**
 * Normalise a submitted address and refuse anything that is not a public
 * http(s) site on a standard port. Without this the endpoint would fetch
 * localhost, private networks or cloud metadata for a stranger.
 *
 * @return array{0:?string,1:string} [url|null, error]
 */
function aic_safe_url(string $raw): array
{
    $raw = trim($raw);
    if ($raw === '') {
        return [null, 'Please enter a website address.'];
    }
    if (strlen($raw) > 2000) {
        return [null, 'That address is too long.'];
    }
    if (preg_match('#^[a-z][a-z0-9+.-]*://#i', $raw) && !preg_match('#^https?://#i', $raw)) {
        return [null, 'Only http and https addresses can be checked.'];
    }
    if (!preg_match('#^https?://#i', $raw)) {
        $raw = 'https://' . $raw;
    }
    if (!filter_var($raw, FILTER_VALIDATE_URL)) {
        return [null, 'That does not look like a valid website address.'];
    }
    $p = parse_url($raw);
    $scheme = strtolower($p['scheme'] ?? '');
    $host = strtolower(rtrim($p['host'] ?? '', '.'));
    if (!in_array($scheme, ['http', 'https'], true)) {
        return [null, 'Only http and https addresses can be checked.'];
    }
    if (isset($p['user']) || isset($p['pass'])) {
        return [null, 'Please enter the address without a username or password.'];
    }
    if (filter_var(trim($host, '[]'), FILTER_VALIDATE_IP)) {
        return [null, 'Please enter a domain name rather than an IP address.'];
    }
    if ($host === '' || !str_contains($host, '.') || $host === 'localhost' || str_ends_with($host, '.local') || str_ends_with($host, '.internal')) {
        return [null, 'Please include the full domain, for example example.com.'];
    }
    if (isset($p['port']) && !in_array((int) $p['port'], [80, 443], true)) {
        return [null, 'Only standard web ports can be checked.'];
    }
    if (gethostbynamel($host) === false) {
        return [null, 'We could not find that domain. Please check the spelling.'];
    }
    if (aic_resolve($host) === null) {
        return [null, 'That address points to a private network and cannot be checked.'];
    }
    $url = $scheme . '://' . $host . (isset($p['port']) ? ':' . $p['port'] : '') . ($p['path'] ?? '/');
    if (isset($p['query']) && $p['query'] !== '') {
        $url .= '?' . $p['query'];
    }
    return [$url, ''];
}

/** Resolve a Location header against the URL that sent it. */
function aic_abs_url(string $loc, string $base): string
{
    $loc = trim($loc);
    if (preg_match('#^https?://#i', $loc)) {
        return $loc;
    }
    $b = parse_url($base);
    $origin = $b['scheme'] . '://' . $b['host'] . (isset($b['port']) ? ':' . $b['port'] : '');
    if (str_starts_with($loc, '//')) {
        return $b['scheme'] . ':' . $loc;
    }
    if (str_starts_with($loc, '/')) {
        return $origin . $loc;
    }
    $dir = preg_replace('#/[^/]*$#', '/', $b['path'] ?? '/');
    return $origin . $dir . $loc;
}

/**
 * GET a URL, following up to five redirects by hand. Every hop is checked
 * again and the connection goes only to the public IPv4 address that was
 * checked (CURLOPT_CONNECT_TO), so neither a redirect nor a DNS change can
 * reach a private host. The CURLOPT_RESOLVE pin used here until 9 Nov 2026
 * was skipped for a host written with a trailing dot ("example.com.") in
 * php-wasm's curl (7.69.1), which then looked the name up itself.
 *
 * $resolve: see aic_resolve().
 *
 * @return array{status:int, url:string, hops:list<array{0:int,1:string}>, headers:array<string,list<string>>, body:string, error:string}
 */
function aic_get(string $url, int $timeout, ?callable $resolve = null): array
{
    $hops = [];
    for ($i = 0; $i <= 5; $i++) {
        $p = parse_url($url) ?: [];
        $scheme = strtolower($p['scheme'] ?? '');
        $host = strtolower(rtrim($p['host'] ?? '', '.'));
        $port = (int) ($p['port'] ?? ($scheme === 'https' ? 443 : 80));
        $fail = static fn(string $e) => ['status' => 0, 'url' => $url, 'hops' => $hops, 'headers' => [], 'body' => '', 'error' => $e];
        if (!in_array($scheme, ['http', 'https'], true) || !in_array($port, [80, 443], true)
            || isset($p['user']) || $host === '' || filter_var(trim($host, '[]'), FILTER_VALIDATE_IP)) {
            return $fail('The site redirected to an address we do not check.');
        }
        $ip = aic_resolve($host, $resolve);
        if ($ip === null) {
            return $fail('The site redirected to an address we could not check.');
        }
        $headers = [];
        $body = '';
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            /* Empty host and port match any request: connect only to the checked IP. */
            CURLOPT_CONNECT_TO => ['::' . $ip . ':' . $port],
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            CURLOPT_PROXY => '',   // a proxy would resolve the name itself
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => AIC_CONNECT,
            CURLOPT_ENCODING => '',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => AIC_UA,
            CURLOPT_HTTPHEADER => ['Accept: text/html,text/plain;q=0.9,*/*;q=0.5'],
            CURLOPT_HEADERFUNCTION => static function ($ch, string $h) use (&$headers): int {
                $kv = explode(':', $h, 2);
                if (count($kv) === 2) {
                    $headers[strtolower(trim($kv[0]))][] = trim($kv[1]);
                }
                return strlen($h);
            },
            CURLOPT_WRITEFUNCTION => static function ($ch, string $chunk) use (&$body): int {
                $body .= $chunk;
                /* Past the cap, stop reading: the first 2 MB is all any check needs. */
                return strlen($body) > AIC_MAX_BYTES ? 0 : strlen($chunk);
            },
        ]);
        curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        if ($status >= 300 && $status < 400 && !empty($headers['location'])) {
            $hops[] = [$status, $url];
            $url = aic_abs_url(end($headers['location']), $url);
            continue;
        }
        if ($status === 0) {
            return $fail($error !== '' ? 'No response: ' . $error : 'No response.');
        }
        return ['status' => $status, 'url' => $url, 'hops' => $hops, 'headers' => $headers, 'body' => $body, 'error' => ''];
    }
    return ['status' => 0, 'url' => $url, 'hops' => $hops, 'headers' => [], 'body' => '', 'error' => 'Too many redirects.'];
}

/* ------------------------------------------------------------------ */
/* Reading the page without JavaScript                                  */
/* ------------------------------------------------------------------ */

/**
 * What a crawler that does not run JavaScript gets from the HTML: the title,
 * the first H1, the visible word count, robots meta tags and whether the page
 * looks like an empty shell that a script fills in later.
 */
function aic_read_html(string $html): array
{
    $title = preg_match('#<title[^>]*>(.*?)</title>#is', $html, $m) ? trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8')) : '';

    $metas = [];
    if (preg_match_all('#<meta\b[^>]*>#i', $html, $tags)) {
        foreach ($tags[0] as $tag) {
            $name = preg_match('#\bname\s*=\s*(["\']?)([^"\'\s>]+)\1#i', $tag, $nm) ? strtolower($nm[2]) : '';
            $content = preg_match('#\bcontent\s*=\s*(["\'])(.*?)\1#is', $tag, $cm) ? trim(html_entity_decode($cm[2], ENT_QUOTES | ENT_HTML5, 'UTF-8')) : '';
            if ($name !== '' && $content !== '' && preg_match('/^(robots|googlebot|bingbot|gptbot|oai-searchbot|chatgpt-user|claudebot|perplexitybot|google-extended|applebot)$/', $name)) {
                $metas[] = [$name, $content];
            }
        }
    }

    $body = preg_match('#<body\b[^>]*>(.*)</body>#is', $html, $bm) ? $bm[1] : $html;
    $body = preg_replace('#<(script|style|template|svg|head)\b[^>]*>.*?</\1>#is', ' ', $body);
    $h1 = preg_match('#<h1\b[^>]*>(.*?)</h1>#is', $body, $hm) ? trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($hm[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'))) : '';
    $text = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags(preg_replace('#<[^>]+>#', ' $0 ', $body)), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    $words = $text === '' ? 0 : count(preg_split('/\s+/u', $text));

    /* Strong signs the content arrives later: a framework mount point that is
       empty, or a noscript message asking the visitor to turn JavaScript on. */
    $shell = (bool) preg_match('#<([a-z][a-z0-9-]*)\b[^>]*\bid\s*=\s*["\'](root|app|__next|__nuxt|svelte|___gatsby)["\'][^>]*>\s*</\1>|<app-root\b[^>]*>\s*</app-root>#i', $html)
        || (bool) preg_match('#<noscript\b[^>]*>(?:(?!</noscript>).){0,400}\b(enable|turn on|requires?)\b(?:(?!</noscript>).){0,80}javascript#is', $html);
    /* Scripts that can change the page; JSON-LD and other data blocks do not. */
    $scripts = preg_match_all('#<script\b(?![^>]*\btype\s*=\s*["\']?application/(?:ld\+)?json)[^>]*>#i', $html);

    return ['title' => $title, 'h1' => $h1, 'words' => $words, 'metas' => $metas, 'shell' => $shell, 'scripts' => (int) $scripts];
}

/**
 * How readable the page is before JavaScript runs: [css class, label, note].
 * Only an empty app shell or a "turn on JavaScript" message counts as proof
 * that content arrives later. Few words alone may just be a short page, so
 * that case is worded as a question for the owner, not a finding.
 */
function aic_text_verdict(array $r): array
{
    $words = (int) $r['page']['words'];
    $scripts = (int) ($r['page']['scripts'] ?? 0);
    if ($r['status'] !== 200) {
        return ['unknown', 'Not measured', 'The server answered HTTP ' . $r['status'] . ', so we could not read the page itself.'];
    }
    if ($r['page']['shell']) {
        return ['blocked', 'Needs JavaScript', 'The page looks like an app shell that JavaScript fills in.'];
    }
    if ($words < 50) {
        return ['thin', 'Very little text', $scripts > 0 ? 'If your main content is added by JavaScript, crawlers that skip JavaScript will not see it.' : 'The page itself has very little text.'];
    }
    if ($words < 200) {
        return ['thin', 'Short', $scripts > 0 ? 'Check that the main content is in the HTML the server sends.' : 'The page itself is short.'];
    }
    return ['allowed', 'Readable', ''];
}

/** Directives from X-Robots-Tag headers, each as [bot or '', directive list]. */
function aic_x_robots(array $headers): array
{
    $out = [];
    foreach ($headers['x-robots-tag'] ?? [] as $value) {
        $bot = '';
        if (preg_match('/^\s*([A-Za-z_-]+)\s*:\s*(?!\s*\d)(.*)$/', $value, $m) && !preg_match('/^(max-snippet|max-image-preview|max-video-preview|unavailable_after)$/i', $m[1])) {
            $bot = strtolower($m[1]);
            $value = $m[2];
        }
        $out[] = [$bot, trim($value)];
    }
    return $out;
}

/* ------------------------------------------------------------------ */
/* The check                                                            */
/* ------------------------------------------------------------------ */

/**
 * Check one page. $get fetches a URL and returns aic_get()'s shape; tests
 * pass their own so the logic runs without the network.
 */
function aic_check(string $url, ?callable $get = null): array
{
    $get ??= 'aic_get';
    $page = $get($url, 10);
    if ($page['status'] === 0) {
        return ['ok' => false, 'error' => 'We could not load that page. ' . $page['error'] . ' If the site blocks unknown visitors, AI crawlers may be blocked too.'];
    }

    /* robots.txt belongs to the origin the crawler finally lands on. */
    $final = $page['url'];
    $fp = parse_url($final);
    $origin = $fp['scheme'] . '://' . $fp['host'] . (isset($fp['port']) ? ':' . $fp['port'] : '');
    $path = ($fp['path'] ?? '/') . (isset($fp['query']) ? '?' . $fp['query'] : '');

    $robots = $get($origin . '/robots.txt', 8);
    $rs = $robots['status'];
    if ($rs >= 200 && $rs < 300) {
        $mode = 'parsed';
        $note = 'Found. The rules below come from this file.';
    } elseif ($rs >= 400 && $rs < 500 && $rs !== 429) {
        $mode = 'allow-all';
        $note = 'Not found (HTTP ' . $rs . '). Under RFC 9309 crawlers then treat everything as allowed.';
    } elseif ($rs === 0) {
        $mode = 'unknown';
        $note = 'We could not fetch it (' . $robots['error'] . '). Crawlers may get a different answer than we did.';
    } else {
        $mode = 'disallow-all';
        $note = 'The server answered HTTP ' . $rs . '. Under RFC 9309 crawlers then assume the whole site is off-limits until it recovers.';
    }
    $parsed = $mode === 'parsed' ? aic_robots_parse($robots['body']) : ['groups' => [], 'sitemaps' => []];

    $crawlers = [];
    $tally = [];
    foreach (aic_crawlers() as [$token, $vendor, $group, $does, $robotsNote, $docs]) {
        $verdict = 'allowed';
        $why = '';
        if ($mode === 'unknown') {
            $verdict = 'unknown';
            $why = 'robots.txt could not be read.';
        } elseif ($mode === 'disallow-all') {
            $verdict = 'blocked';
            $why = 'robots.txt returned a server error.';
        } elseif ($mode === 'allow-all') {
            $why = 'No robots.txt, so nothing is blocked.';
        } else {
            $set = aic_rules_for($parsed, $token);
            $d = aic_decide($set['rules'], $path);
            $verdict = $d['allowed'] ? 'allowed' : 'blocked';
            $group_label = $set['agent'] === null ? 'no group applies' : ($set['agent'] === '*' ? 'the "User-agent: *" group' : 'its own "User-agent: ' . $token . '" group');
            $why = $d['rule'] === null
                ? 'No rule in ' . $group_label . ' blocks this page.'
                : ucfirst($d['rule'][0]) . ': ' . $d['rule'][1] . ' (line ' . $d['rule'][2] . ', ' . $group_label . ').';
        }
        $crawlers[] = compact('token', 'vendor', 'group', 'does', 'robotsNote', 'docs', 'verdict', 'why');
        $tally[$group][$verdict] = ($tally[$group][$verdict] ?? 0) + 1;
    }

    $html = aic_read_html($page['body']);
    $llms = $get($origin . '/llms.txt', 5);
    $llmsFound = $llms['status'] === 200 && !preg_match('#<html#i', substr($llms['body'], 0, 2000));

    return [
        'ok' => true,
        'error' => '',
        'input' => $url,
        'url' => $final,
        'redirects' => $page['hops'],
        'status' => $page['status'],
        'checked' => gmdate('j M Y, H:i') . ' UTC',
        'robots' => ['url' => $origin . '/robots.txt', 'status' => $rs, 'mode' => $mode, 'note' => $note, 'sitemaps' => $parsed['sitemaps']],
        'crawlers' => $crawlers,
        'tally' => $tally,
        'page' => $html + ['x_robots' => aic_x_robots($page['headers'])],
        'llms' => $llmsFound,
    ];
}

/* ------------------------------------------------------------------ */
/* The report                                                           */
/* ------------------------------------------------------------------ */

/** Plain-English next steps for a result, most important first. */
function aic_next_steps(array $r): array
{
    $steps = [];
    $t = $r['tally'];
    $words = (int) $r['page']['words'];
    $meta = strtolower(implode(' ', array_map(static fn($m) => in_array($m[0], ['robots', 'googlebot'], true) ? $m[1] : '', $r['page']['metas'])));
    $xr = strtolower(implode(' ', array_map(static fn($x) => in_array($x[0], ['', 'googlebot'], true) ? $x[1] : '', $r['page']['x_robots'])));
    $tags = $meta . ' ' . $xr;

    if ($r['robots']['mode'] === 'disallow-all') {
        $steps[] = 'Fix the server error on robots.txt first. Until it loads, crawlers that follow RFC 9309 treat the whole site as off-limits.';
    }
    if ($r['status'] !== 200) {
        $steps[] = 'This page answered HTTP ' . $r['status'] . ' to our checker. If crawlers get the same answer, they cannot read it.';
    }
    if (($t['search']['blocked'] ?? 0) > 0) {
        $steps[] = 'Some search and AI answer crawlers are blocked. To let ChatGPT, Claude, Perplexity or Google find and cite this page, remove the rules shown above for them.';
    }
    if (preg_match('/\b(noindex|none)\b/', $tags)) {
        $steps[] = 'This page asks search engines not to index it. It cannot appear in Google Search, or as a link in AI Overviews and AI Mode, while that tag is there.';
    } elseif (preg_match('/\bnosnippet\b|max-snippet\s*:\s*0\b/', $tags)) {
        $steps[] = 'A snippet control stops Google from quoting this page. Google only links pages it may show with a snippet in AI Overviews and AI Mode.';
    }
    [, $textLabel] = aic_text_verdict($r);
    $scripts = (int) ($r['page']['scripts'] ?? 0);
    if ($textLabel === 'Needs JavaScript') {
        $steps[] = 'This page arrives as an empty shell that JavaScript fills in. Crawlers that do not run JavaScript see almost nothing, so render the main content on the server.';
    } elseif ($scripts > 0 && in_array($textLabel, ['Very little text', 'Short'], true)) {
        $steps[] = 'Only ' . number_format($words) . ' words arrive before JavaScript runs. If your main content is added by JavaScript, crawlers that skip JavaScript will not see it, so check the HTML your server sends.';
    }
    if (($t['training']['blocked'] ?? 0) === 0 && $r['robots']['mode'] !== 'unknown') {
        $steps[] = 'Every training crawler may use this page. That is a business choice: the example below opts out of training and keeps AI search open.';
    }
    if (!$steps) {
        $steps[] = 'Search and AI answer crawlers can reach this page, and its text is readable without JavaScript. Recheck after any change to robots.txt, your CMS or your CDN.';
    }
    $steps[] = 'robots.txt is not the only gate: a firewall or CDN rule can still block AI crawlers that robots.txt allows. Check those settings too.';
    return $steps;
}

/** Render a result. Every value is escaped: it all comes from someone else's site. */
function aic_render_results(array $r): void
{
    $label = ['allowed' => 'Allowed', 'blocked' => 'Blocked', 'unknown' => 'Unknown'];
    $words = (int) $r['page']['words'];
    [$wClass, $wLabel, $wNote] = aic_text_verdict($r);
    ?>
<div class="aic-report" id="results">
    <div class="aic-head">
        <p class="eyebrow">Checked <?= e($r['checked']) ?></p>
        <h2>Results for <span class="aic-url"><?= e($r['url']) ?></span></h2>
        <?php if ($r['redirects']): ?>
        <p class="aic-sub">The address you entered redirected here, so these results use this page and its robots.txt.</p>
        <?php endif; ?>
        <ul class="aic-tally">
            <?php foreach (aic_groups() as $g => [$heading]): $t = $r['tally'][$g] ?? []; ?>
            <li class="<?= ($t['blocked'] ?? 0) ? 'is-blocked' : (($t['unknown'] ?? 0) ? 'is-unknown' : 'is-clear') ?>">
                <strong><?= (int) ($t['allowed'] ?? 0) ?> of <?= (int) array_sum($t) ?></strong>
                <span><?= e($heading) ?>: allowed</span>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <p class="aic-robots aic-mode-<?= e($r['robots']['mode']) ?>"><strong>robots.txt</strong> <?= e($r['robots']['note']) ?></p>

    <?php foreach (aic_groups() as $g => [$heading, $intro]): ?>
    <div class="aic-group">
        <h3><?= e($heading) ?></h3>
        <p class="aic-group-intro"><?= e($intro) ?></p>
        <div class="aic-table-wrap">
            <table class="aic-table">
                <thead><tr><th scope="col">Crawler</th><th scope="col">Result</th><th scope="col">Why</th></tr></thead>
                <tbody>
                    <?php foreach ($r['crawlers'] as $c): if ($c['group'] !== $g) { continue; } ?>
                    <tr>
                        <th scope="row"><code><?= e($c['token']) ?></code><small><?= e($c['vendor']) ?></small></th>
                        <td><span class="aic-pill aic-<?= e($c['verdict']) ?>"><?= e($label[$c['verdict']]) ?></span></td>
                        <td><?= e($c['why']) ?><?php if ($c['robotsNote'] !== ''): ?> <em><?= e($c['robotsNote']) ?></em><?php endif; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endforeach; ?>

    <div class="aic-group">
        <h3>What a crawler gets without JavaScript</h3>
        <dl class="aic-facts">
            <div><dt>Page status</dt><dd>HTTP <?= (int) $r['status'] ?></dd></div>
            <div><dt>Words before JavaScript</dt><dd><?= $r['status'] === 200 ? number_format($words) . ' ' : '' ?><span class="aic-pill aic-<?= e($wClass) ?>"><?= e($wLabel) ?></span><?php if ($wNote !== ''): ?> <small><?= e($wNote) ?></small><?php endif; ?></dd></div>
            <div><dt>Title</dt><dd><?= $r['page']['title'] !== '' ? e(mb_strimwidth($r['page']['title'], 0, 120, '...')) : 'None found' ?></dd></div>
            <div><dt>Main heading (H1)</dt><dd><?= $r['page']['h1'] !== '' ? e(mb_strimwidth($r['page']['h1'], 0, 120, '...')) : 'None in the HTML' ?></dd></div>
            <div><dt>Robots meta tags</dt><dd><?php if ($r['page']['metas']): foreach ($r['page']['metas'] as [$n, $v]): ?><code><?= e($n) ?>: <?= e(mb_strimwidth($v, 0, 80, '...')) ?></code> <?php endforeach; else: ?>None<?php endif; ?></dd></div>
            <div><dt>X-Robots-Tag header</dt><dd><?php if ($r['page']['x_robots']): foreach ($r['page']['x_robots'] as [$b, $v]): ?><code><?= e(($b !== '' ? $b . ': ' : '') . mb_strimwidth($v, 0, 80, '...')) ?></code> <?php endforeach; else: ?>None<?php endif; ?></dd></div>
            <div><dt>Sitemaps in robots.txt</dt><dd><?= count($r['robots']['sitemaps']) ?: 'None listed' ?></dd></div>
            <div><dt>llms.txt</dt><dd><?= $r['llms'] ? 'Found' : 'Not found (optional)' ?></dd></div>
        </dl>
    </div>

    <div class="panel panel-accent aic-next">
        <h3>What to do next</h3>
        <ul class="check-list">
            <?php foreach (aic_next_steps($r) as $s): ?><li><?= e($s) ?></li><?php endforeach; ?>
        </ul>
    </div>
</div>
    <?php
}

/**
 * POST handler: validates the form, throttles, and runs the check.
 *
 * @return array{0:?array,1:string,2:string} [result|null, error, submitted url]
 */
function aic_handle_post(): array
{
    $input = trim((string) ($_POST['site_url'] ?? ''));
    $token = (string) ($_POST['csrf_token'] ?? '');
    if (trim((string) ($_POST['website'] ?? '')) !== '' || security_ua_is_bot()) {
        return [null, 'Something went wrong. Please try again.', $input];
    }
    if (!csrf_verify($token)) {
        return [null, 'Your session expired. Please refresh the page and try again.', $input];
    }
    if (csrf_age($token) < 2) {
        return [null, 'Please try again.', $input];
    }
    [$url, $err] = aic_safe_url($input);
    if ($url === null) {
        return [null, $err, $input];
    }
    if (!security_rate_ok('aic', AIC_RATE_MAX, AIC_RATE_WINDOW)) {
        return [null, 'You have run a lot of checks in the last hour. Please try again later.', $input];
    }
    $result = aic_check($url);
    return $result['ok'] ? [$result, '', $input] : [null, $result['error'], $input];
}
