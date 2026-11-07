<?php
/**
 * Unit tests for includes/ai-crawlers.php: the robots.txt engine (RFC 9309),
 * address safety checks, HTML reading and the whole check with a fake fetcher.
 * No network. Run with: PHP_WASM_DIR=... node tests/aic/run.mjs
 */
declare(strict_types=1);

require '/site/includes/ai-crawlers.php';

$pass = 0;
$fail = 0;
function check(string $name, bool $ok): void
{
    global $pass, $fail;
    if ($ok) {
        $pass++;
    } else {
        $fail++;
        echo "FAIL $name\n";
    }
}
function allowed(string $robots, string $token, string $path): bool
{
    $set = aic_rules_for(aic_robots_parse($robots), $token);
    return aic_decide($set['rules'], $path)['allowed'];
}

/* ---------- robots.txt engine ---------- */
check('empty file allows', allowed('', 'GPTBot', '/'));
check('star disallow blocks', !allowed("User-agent: *\nDisallow: /", 'GPTBot', '/x'));
check('own group beats star', !allowed("User-agent: GPTBot\nDisallow: /\n\nUser-agent: *\nAllow: /", 'GPTBot', '/'));
check('others fall back to star', allowed("User-agent: GPTBot\nDisallow: /\n\nUser-agent: *\nAllow: /", 'ClaudeBot', '/'));
check('agent match ignores case', !allowed("user-agent: gptbot\ndisallow: /", 'GPTBot', '/'));
check('agent version suffix', !allowed("User-agent: GPTBot/1.0\nDisallow: /", 'GPTBot', '/'));
check('shared group, first agent', !allowed("User-agent: GPTBot\nUser-agent: ClaudeBot\nDisallow: /private/", 'GPTBot', '/private/a'));
check('shared group, second agent', !allowed("User-agent: GPTBot\nUser-agent: ClaudeBot\nDisallow: /private/", 'ClaudeBot', '/private/a'));
check('shared group, other path', allowed("User-agent: GPTBot\nUser-agent: ClaudeBot\nDisallow: /private/", 'ClaudeBot', '/public'));
check('blank line does not split agents', !allowed("User-agent: GPTBot\n\nUser-agent: *\nDisallow: /", 'GPTBot', '/'));
check('own empty group allows all', allowed("User-agent: *\nDisallow: /\n\nUser-agent: GPTBot\n", 'GPTBot', '/'));
check('longest match wins', allowed("User-agent: *\nDisallow: /\nAllow: /blog/", 'GPTBot', '/blog/post'));
check('longest match blocks rest', !allowed("User-agent: *\nDisallow: /\nAllow: /blog/", 'GPTBot', '/about'));
check('allow wins a tie', allowed("User-agent: *\nDisallow: /page\nAllow: /page", 'GPTBot', '/page'));
check('dollar anchors end', !allowed("User-agent: *\nDisallow: /*.pdf$", 'GPTBot', '/file.pdf'));
check('dollar with query', allowed("User-agent: *\nDisallow: /*.pdf$", 'GPTBot', '/file.pdf?x=1'));
check('wildcard query', !allowed("User-agent: *\nDisallow: /*?", 'GPTBot', '/a?b=1'));
check('wildcard no query', allowed("User-agent: *\nDisallow: /*?", 'GPTBot', '/a'));
check('empty disallow allows', allowed("User-agent: *\nDisallow:", 'GPTBot', '/'));
check('robots.txt always allowed', allowed("User-agent: *\nDisallow: /", 'GPTBot', '/robots.txt'));
check('bom, crlf, comments', !allowed("\xEF\xBB\xBFUser-agent: * # all\r\nDisallow: /secret # hidden\r\n", 'GPTBot', '/secret/x'));
check('bom, crlf, comments allow', allowed("\xEF\xBB\xBFUser-agent: * # all\r\nDisallow: /secret # hidden\r\n", 'GPTBot', '/'));
check('rules before any agent ignored', allowed("Disallow: /\nUser-agent: *\nAllow: /", 'GPTBot', '/'));
check('own groups merge', !allowed("User-agent: GPTBot\nDisallow: /a\n\nUser-agent: *\nDisallow: /\n\nUser-agent: GPTBot\nDisallow: /b", 'GPTBot', '/b'));
check('own groups merge, star ignored', allowed("User-agent: GPTBot\nDisallow: /a\n\nUser-agent: *\nDisallow: /\n\nUser-agent: GPTBot\nDisallow: /b", 'GPTBot', '/c'));
check('crawl-delay keeps the agent run', !allowed("User-agent: a\nCrawl-delay: 5\nUser-agent: GPTBot\nDisallow: /", 'a', '/'));
check('percent and unicode compare', !allowed("User-agent: *\nDisallow: /caf%c3%a9", 'GPTBot', '/café'));
check('star with text is global', !allowed("User-agent: * everyone\nDisallow: /", 'GPTBot', '/'));
check('prefix match only', allowed("User-agent: *\nDisallow: /admin", 'GPTBot', '/blog/admin'));
$p = aic_robots_parse("User-agent: *\nAllow: /\nSitemap: https://x.com/sitemap.xml\nsitemap: https://x.com/s2.xml");
check('sitemaps collected', $p['sitemaps'] === ['https://x.com/sitemap.xml', 'https://x.com/s2.xml']);
$d = aic_decide(aic_rules_for(aic_robots_parse("User-agent: *\nAllow: /\n\nUser-agent: GPTBot\nDisallow: /"), 'GPTBot')['rules'], '/');
check('decision reports rule and line', $d['rule'] === ['disallow', '/', 5]);

/* ---------- address safety ---------- */
foreach (['8.8.8.8', '1.1.1.1', '172.32.0.1', '100.128.0.1'] as $ip) {
    check("public $ip", aic_ip_public($ip));
}
foreach (['10.0.0.1', '172.16.5.4', '172.31.255.255', '192.168.1.1', '127.0.0.1', '169.254.169.254', '100.64.0.1', '0.0.0.0', '224.0.0.1', '255.255.255.255', '198.18.0.1', '192.0.2.10', '::1', 'fd00::1', '::ffff:127.0.0.1', 'not-an-ip'] as $ip) {
    check("not public $ip", !aic_ip_public($ip));
}
foreach (['' => 'Please enter', 'http://127.0.0.1/' => 'IP address', 'http://[::1]/' => 'IP address', 'https://user:pw@example.com/' => 'username',
          'https://example.com:8080/' => 'ports', 'localhost' => 'full domain', 'https://printer.local/' => 'full domain', 'ftp://example.com/' => 'http and https', 'javascript://x.com/%0aalert(1)' => 'http and https'] as $in => $msg) {
    [$u, $e] = aic_safe_url((string) $in);
    check("unsafe '$in'", $u === null && str_contains($e, $msg));
}
check('relative location', aic_abs_url('/x', 'https://a.com/b/c') === 'https://a.com/x');
check('sibling location', aic_abs_url('y', 'https://a.com/b/c') === 'https://a.com/b/y');
check('scheme-relative location', aic_abs_url('//cdn.com/z', 'https://a.com/') === 'https://cdn.com/z');
check('absolute location', aic_abs_url('http://b.com/', 'https://a.com/') === 'http://b.com/');

/* ---------- reading HTML ---------- */
$h = aic_read_html('<html><head><title>T &amp; Co</title><meta name="robots" content="noindex, nofollow"><meta name="description" content="x"></head><body><h1>Hello</h1><p>one two three</p><script>var x = "many words here";</script><style>p{}</style></body></html>');
check('title', $h['title'] === 'T & Co');
check('h1', $h['h1'] === 'Hello');
check('words skip scripts', $h['words'] === 4);
check('robots meta', $h['metas'] === [['robots', 'noindex, nofollow']]);
check('not a shell', !$h['shell']);
$s = aic_read_html('<html><body><div id="root"></div><script src="/app.js"></script></body></html>');
check('shell detected', $s['shell'] && $s['words'] === 0);
check('entities count once', aic_read_html('<body><p>Caf&eacute; &amp; bar</p></body>')['words'] === 3);
check('tags do not glue words', aic_read_html('<body><p>one</p><p>two</p></body>')['words'] === 2);
$x = aic_x_robots(['x-robots-tag' => ['noindex, nofollow', 'googlebot: nosnippet', 'max-snippet:0', 'unavailable_after: 25 Jun 2030']]);
check('x-robots parsed', $x === [['', 'noindex, nofollow'], ['googlebot', 'nosnippet'], ['', 'max-snippet:0'], ['', 'unavailable_after: 25 Jun 2030']]);

/* ---------- the whole check, fake network ---------- */
function fake(array $map): callable
{
    return static function (string $url, int $timeout) use ($map): array {
        $r = $map[$url] ?? ['status' => 404, 'body' => 'Not found'];
        return ['status' => $r['status'], 'url' => $r['url'] ?? $url, 'hops' => $r['hops'] ?? [], 'headers' => $r['headers'] ?? [], 'body' => $r['body'] ?? '', 'error' => $r['error'] ?? ''];
    };
}
$page = '<html><head><title>Home</title></head><body><h1>Welcome</h1><p>' . str_repeat('word ', 300) . '</p></body></html>';
$r = aic_check('https://x.com/', fake([
    'https://x.com/' => ['status' => 200, 'body' => $page],
    'https://x.com/robots.txt' => ['status' => 200, 'body' => "User-agent: GPTBot\nDisallow: /\n\nUser-agent: *\nAllow: /\nSitemap: https://x.com/sitemap.xml"],
]));
$by = array_column($r['crawlers'], null, 'token');
check('check ok', $r['ok'] === true);
check('GPTBot blocked with reason', $by['GPTBot']['verdict'] === 'blocked' && str_contains($by['GPTBot']['why'], 'Disallow: / (line 2'));
check('ClaudeBot allowed via star', $by['ClaudeBot']['verdict'] === 'allowed' && str_contains($by['ClaudeBot']['why'], 'User-agent: *'));
check('training tally', ($r['tally']['training']['blocked'] ?? 0) === 1);
check('search all allowed', ($r['tally']['search']['blocked'] ?? 0) === 0);
check('robots parsed', $r['robots']['mode'] === 'parsed' && $r['robots']['sitemaps'] === ['https://x.com/sitemap.xml']);
check('readable page', $r['page']['words'] >= 300 && $r['page']['h1'] === 'Welcome');
check('no llms.txt', $r['llms'] === false);
check('every crawler reported', count($r['crawlers']) === count(aic_crawlers()));
check('next steps mention training choice', (bool) array_filter(aic_next_steps($r), static fn($s) => str_contains($s, 'firewall')));

$r404 = aic_check('https://x.com/', fake(['https://x.com/' => ['status' => 200, 'body' => $page]]));
check('404 robots allows all', $r404['robots']['mode'] === 'allow-all' && ($r404['tally']['training']['blocked'] ?? 0) === 0);

$r503 = aic_check('https://x.com/', fake(['https://x.com/' => ['status' => 200, 'body' => $page], 'https://x.com/robots.txt' => ['status' => 503]]));
check('503 robots blocks all', $r503['robots']['mode'] === 'disallow-all' && ($r503['tally']['search']['allowed'] ?? 0) === 0);

$r429 = aic_check('https://x.com/', fake(['https://x.com/' => ['status' => 200, 'body' => $page], 'https://x.com/robots.txt' => ['status' => 429]]));
check('429 treated like a server error', $r429['robots']['mode'] === 'disallow-all');

$rnet = aic_check('https://x.com/', fake(['https://x.com/' => ['status' => 200, 'body' => $page], 'https://x.com/robots.txt' => ['status' => 0, 'error' => 'timeout']]));
check('network error is unknown', $rnet['robots']['mode'] === 'unknown' && ($rnet['tally']['search']['unknown'] ?? 0) === 9);

$seen = [];
$spy = static function (string $url, int $t) use (&$seen, $page): array {
    $seen[] = $url;
    if ($url === 'https://x.com/') {
        return ['status' => 200, 'url' => 'https://www.x.com/home?a=1', 'hops' => [[301, 'https://x.com/']], 'headers' => [], 'body' => $page, 'error' => ''];
    }
    return ['status' => 200, 'url' => $url, 'hops' => [], 'headers' => [], 'body' => "User-agent: *\nDisallow: /home", 'error' => ''];
};
$rr = aic_check('https://x.com/', $spy);
check('robots from final origin', in_array('https://www.x.com/robots.txt', $seen, true));
check('rules use final path', ($rr['tally']['search']['blocked'] ?? 0) === 9);

$dead = aic_check('https://x.com/', fake(['https://x.com/' => ['status' => 0, 'error' => 'timeout']]));
check('dead page fails cleanly', $dead['ok'] === false && str_contains($dead['error'], 'could not load'));

$shellPage = aic_check('https://x.com/', fake(['https://x.com/' => ['status' => 200, 'body' => '<body><div id="__next"></div></body>'], 'https://x.com/llms.txt' => ['status' => 200, 'body' => "# X\n"]]));
check('shell flagged in steps', (bool) array_filter(aic_next_steps($shellPage), static fn($s) => str_contains($s, 'JavaScript')));
check('llms.txt found', $shellPage['llms'] === true);

$noidx = aic_check('https://x.com/', fake(['https://x.com/' => ['status' => 200, 'body' => $page, 'headers' => ['x-robots-tag' => ['noindex']]]]));
check('noindex flagged', (bool) array_filter(aic_next_steps($noidx), static fn($s) => str_contains($s, 'not to index')));

$jsStep = static fn(array $r) => (bool) array_filter(aic_next_steps($r), static fn($s) => str_contains($s, 'before JavaScript'));
$short = aic_check('https://x.com/', fake(['https://x.com/' => ['status' => 200, 'body' => '<body><h1>Example</h1><p>A short page with no scripts at all.</p></body>']]));
check('short page without scripts is not a JS problem', !$jsStep($short) && aic_text_verdict($short)[1] === 'Very little text');
$spa = aic_check('https://x.com/', fake(['https://x.com/' => ['status' => 200, 'body' => '<body><section id="main"></section><script src="/bundle.js"></script><script type="application/ld+json">{}</script></body>']]));
check('few words plus scripts asks about JS', $jsStep($spa) && aic_text_verdict($spa)[1] === 'Very little text' && $spa['page']['scripts'] === 1);
$mount = aic_check('https://x.com/', fake(['https://x.com/' => ['status' => 200, 'body' => '<body><section class="todoapp" id="root"></section><script src="/app.js"></script></body>']]));
check('empty mount point needs JS', aic_text_verdict($mount)[1] === 'Needs JavaScript'
    && (bool) array_filter(aic_next_steps($mount), static fn($s) => str_contains($s, 'empty shell')));
$nos = aic_check('https://x.com/', fake(['https://x.com/' => ['status' => 200, 'body' => '<body><noscript>You need to enable JavaScript to run this app.</noscript><div id="x"></div></body>']]));
check('noscript message needs JS', aic_text_verdict($nos)[1] === 'Needs JavaScript');
$forbidden = aic_check('https://x.com/', fake(['https://x.com/' => ['status' => 403, 'body' => '<body><script src="/c.js"></script>Denied</body>']]));
check('non-200 page is not judged for JavaScript', !$jsStep($forbidden) && aic_text_verdict($forbidden)[1] === 'Not measured');
check('non-200 page gets a status step', (bool) array_filter(aic_next_steps($forbidden), static fn($s) => str_contains($s, 'HTTP 403')));

echo "\n$pass passed, $fail failed\n";
