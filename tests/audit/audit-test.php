<?php
/**
 * Tests for includes/audit.php: the addresses the free audit refuses, on the
 * submitted URL and on every redirect (the guard against SSRF), the fetch
 * limits, and run_site_audit() end to end against the local test sites in
 * sites.mjs. run.mjs routes site-a.example and site-b.example to those sites
 * and every private address to a stand-in internal site whose pages say
 * INTERNAL SECRET, so a guard that failed would show it here.
 * Run with: PHP_WASM_DIR=... node tests/audit/run.mjs
 */
declare(strict_types=1);

const SITE_URL = 'https://vturnu.com';
require '/site/includes/audit.php';

$pass = 0;
$fail = 0;
function check(string $name, bool $ok, string $got = ''): void
{
    global $pass, $fail;
    if ($ok) {
        $pass++;
    } else {
        $fail++;
        echo "FAIL $name" . ($got !== '' ? " | got: $got" : '') . "\n";
    }
}
function leaked(array $r): bool
{
    return str_contains(json_encode($r, JSON_INVALID_UTF8_SUBSTITUTE), 'INTERNAL SECRET');
}

/* Resolves like gethostbynamel(): an IPv4 address stands for itself. flip.example
   answers with a public address once and a private one after that, which is
   what a DNS rebinding attack looks like. */
$DNS = json_decode($_SERVER['AUDIT_TEST_DNS'], true);
$flips = 0;
$resolve = static function (string $host) use ($DNS, &$flips) {
    if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        return [$host];
    }
    if ($host === 'flip.example') {
        return $flips++ === 0 ? ['93.184.216.34'] : ['10.0.0.1'];
    }
    return $DNS[$host] ?? false;
};
$get = static fn(string $url, int $timeout = 5, bool $head = false): array => audit_get($url, $timeout, $head, $resolve);
$REDIRECT_REFUSED = 'That site redirects to an address we cannot audit, such as a private network or a non-standard port.';
$PRIVATE = 'That address resolves to a private network and cannot be audited.';

/* ---------- submitted addresses ---------- */
[$u, $e] = audit_safe_url('ftp://example.com/');   // read as https://ftp://..., refused as a bare host
check('refuse submitted ftp://', $u === null, $e);
foreach (['' => 'Please enter', 'https://user:pw@example.com/' => 'username',
          'https://example.com:8080/' => 'standard web ports', 'localhost' => 'full domain', 'http://127.0.0.1/' => 'private network',
          'http://10.1.2.3/' => 'private network', 'http://169.254.169.254/' => 'private network',
          'http://100.64.0.1/' => 'private network', 'http://198.18.0.1/' => 'private network'] as $in => $msg) {
    [$u, $e] = audit_safe_url((string) $in);
    check("refuse submitted '$in'", $u === null && str_contains($e, $msg), $e);
}
[$u, $e] = audit_safe_url('8.8.8.8');
check('public IPv4 address accepted', $u === 'https://8.8.8.8' && $e === '', $e);

/* ---------- first address refused before any connection ---------- */
foreach (['http://127.0.0.1/', 'http://169.254.169.254/latest/meta-data/', 'http://localhost/'] as $u) {
    $r = audit_get($u, 5);   // gethostbynamel() answers these without a DNS server
    check("refuse $u (system resolver)", $r['blocked'] && $r['status'] === 0 && $r['error'] === $PRIVATE, $r['error']);
}
foreach (['http://intranet.example/', 'http://INTRANET.example./', 'http://mixed.example/', 'http://cgnat.example/', 'http://10.0.0.1/'] as $u) {
    $r = $get($u);
    check("refuse $u", $r['blocked'] && $r['status'] === 0 && $r['error'] === $PRIVATE, $r['error']);
}
foreach (['http://[::1]/', 'ftp://site-a.example/', 'http://site-a.example:8080/', 'http://u:p@site-a.example/', 'file:///etc/passwd', 'not a url'] as $u) {
    $r = $get($u);
    check("refuse $u", $r['blocked'] && $r['status'] === 0 && str_starts_with($r['error'], 'Only public http and https'), $r['error']);
}
$r = $get('http://nowhere.example/');
check('unknown host is an ordinary failure', !$r['blocked'] && $r['status'] === 0 && $r['error'] === 'Could not resolve host: nowhere.example', $r['error']);

/* ---------- every redirect checked again ---------- */
foreach (['/to-private', '/to-metadata', '/to-loopback', '/to-localhost', '/to-intranet', '/to-mixed', '/to-cgnat',
          '/to-port', '/to-ftp', '/to-mailto', '/to-userinfo', '/to-ipv6'] as $path) {
    $r = $get('http://site-a.example' . $path);
    check("refuse redirect $path", $r['blocked'] && $r['status'] === 0 && $r['error'] === $REDIRECT_REFUSED && $r['body'] === '' && !leaked($r),
        $r['status'] . ' ' . $r['error']);
}
$r = $get('http://site-a.example/to-b');
check('follow a redirect to another public site', $r['status'] === 200 && $r['url'] === 'http://site-b.example/landing' && str_contains($r['body'], 'Site B landing'), $r['status'] . ' ' . $r['url']);
$r = $get('http://site-a.example/to-b-dot');
check('follow a redirect with a trailing dot', $r['status'] === 200 && str_contains($r['body'], 'Site B landing'), $r['status'] . ' ' . $r['error']);
$r = $get('http://site-a.example/to-b-relative');
check('follow a scheme-relative redirect', $r['status'] === 200 && $r['url'] === 'http://site-b.example/landing', $r['status'] . ' ' . $r['url']);
$r = $get('http://site-a.example/relative');
check('follow a relative redirect', $r['status'] === 200 && $r['url'] === 'http://site-a.example/landed', $r['status'] . ' ' . $r['url']);
$r = $get('http://site-a.example/loop');
check('stop after five redirects', $r['status'] === 0 && !$r['blocked'] && $r['error'] === 'Too many redirects', $r['status'] . ' ' . $r['error']);

/* ---------- DNS rebinding: one answer per host, and curl never asks again ---------- */
$flips = 0;
$r = $get('http://flip.example/to-flip');
check('a host is resolved once per fetch', $r['status'] === 200 && $r['url'] === 'http://flip.example/landed' && $flips === 1, $r['status'] . ' ' . $r['error'] . " flips $flips");
$r = $get('http://flip.example/');
check('a host that now resolves privately is refused', $r['blocked'] && $r['error'] === $PRIVATE && !leaked($r), $r['error']);

/* ---------- limits and measurements ---------- */
$r = $get('http://site-a.example/slow', 1);
check('the time limit covers the fetch', $r['status'] === 0 && stripos($r['error'], 'timed out') !== false && $r['ms'] >= 900 && $r['ms'] < 2400, $r['ms'] . ' ms ' . $r['error']);
$r = $get('http://site-a.example/big', 10);
check('a large page stops at the byte cap', $r['status'] === 200 && strlen($r['body']) > AUDIT_MAX_BYTES && strlen($r['body']) < AUDIT_MAX_BYTES + 262144, $r['status'] . ' ' . strlen($r['body']));
$r = $get('http://site-a.example/gzip');
check('compressed size is measured', $r['status'] === 200 && $r['size'] > 0 && $r['size'] < strlen($r['body']), $r['size'] . ' < ' . strlen($r['body']));
$r = $get('http://site-a.example/landed');
check('plain size is measured', $r['status'] === 200 && $r['size'] === strlen($r['body']), $r['size'] . ' vs ' . strlen($r['body']));
$r = $get('http://site-a.example/sitemap.xml', 4, true);
check('HEAD request', $r['status'] === 200 && $r['body'] === '', (string) $r['status']);
check('audit_exists: found', audit_exists('http://site-a.example/sitemap.xml', $get));
check('audit_exists: missing', !audit_exists('http://site-a.example/missing.xml', $get));
check('audit_exists: redirect to a private address', !audit_exists('http://site-b.example/sitemap.xml', $get));

/* ---------- the whole audit ---------- */
$labels = static fn(array $a): array => array_column(array_merge(...array_values($a['groups'])), 'label');
$a = run_site_audit('http://site-a.example/', $get);
check('audit runs', $a['ok'] && $a['url'] === 'http://site-a.example/', $a['error']);
if ($a['ok']) {
    $l = $labels($a);
    check('audit reads robots.txt, sitemap and llms.txt', in_array('robots.txt found', $l, true) && in_array('XML sitemap found', $l, true) && in_array('No llms.txt (optional)', $l, true), implode(', ', $l));
}
$a = run_site_audit('http://site-a.example/to-b', $get);
check('audit follows a public redirect', $a['ok'] && $a['url'] === 'http://site-b.example/landing', $a['error']);
if ($a['ok']) {
    $l = $labels($a);
    check('audit refuses redirects of robots.txt, sitemap and llms.txt', in_array('No robots.txt', $l, true) && in_array('No XML sitemap', $l, true) && in_array('No llms.txt (optional)', $l, true) && !leaked($a), implode(', ', $l));
}
foreach (['/to-metadata', '/to-private', '/to-loopback'] as $path) {
    $a = run_site_audit('http://site-a.example' . $path, $get);
    check("audit refuses redirect $path", !$a['ok'] && $a['error'] === $REDIRECT_REFUSED && !leaked($a), $a['error']);
}
$flips = 0;
$a = run_site_audit('http://flip.example/to-flip', $get);
check('audit survives DNS rebinding', $a['ok'] && !leaked($a) && in_array('No robots.txt', $labels($a), true), $a['error']);

/* The page escapes the error with e(), so it must arrive unescaped. */
$sslError = "SSL: no alternative certificate subject name matches target host name 'x.example'";
$a = run_site_audit('https://x.example/', static fn(string $u, int $t, bool $h = false): array =>
    ['status' => 0, 'url' => $u, 'body' => '', 'ms' => 5, 'size' => 0, 'error' => $sslError, 'blocked' => false]);
check('fetch error shown once escaped', !$a['ok'] && str_contains($a['error'], "'x.example'") && !str_contains($a['error'], '&#039;'), $a['error']);

echo "\n$pass passed, $fail failed\n";
