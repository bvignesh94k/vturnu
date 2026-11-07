// Live check of the AI Crawler Checker logic against real websites, for
// manual review. Uses the network, so it is not part of the unit tests.
//
// php-wasm fakes DNS (every host resolves to 172.29.1.0), which the checker's
// own address guard rightly refuses, so this harness passes aic_check() a
// test-only fetcher without that guard. Production always uses aic_get().
//
//   PHP_WASM_DIR="$TEMP/php-wasm" MSYS_NO_PATHCONV=1 node tests/aic/live.mjs [url ...] [--preview=file.html]
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import tls from 'node:tls';
import { fileURLToPath, pathToFileURL } from 'node:url';

const here = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(here, '../..');
const base = (process.env.PHP_WASM_DIR ?? '').replace(/^\/([a-z])\//i, (_, d) => `${d.toUpperCase()}:/`);
const args = process.argv.slice(2);
const preview = (args.find((a) => a.startsWith('--preview=')) || '').slice(10);
const urls = args.filter((a) => !a.startsWith('--'));
if (!urls.length) urls.push('https://vturnu.com/');

const mod = (name) => import(pathToFileURL(path.join(base, 'node_modules', name, 'index.js')).href);
const { PHP } = await mod('@php-wasm/universal');
const { loadNodeRuntime, createNodeFsMountHandler } = await mod('@php-wasm/node');
const php = new PHP(await loadNodeRuntime('8.5', { emscriptenOptions: { processId: 1 } }));
php.mkdir('/site');
await php.mount('/site', createNodeFsMountHandler(root));
const caDir = fs.mkdtempSync(path.join(os.tmpdir(), 'aic-ca-'));
fs.writeFileSync(path.join(caDir, 'ca.pem'), tls.rootCertificates.join('\n'));
php.mkdir('/ca');
await php.mount('/ca', createNodeFsMountHandler(caDir));

const code = `<?php
require '/site/includes/config.php';
require '/site/includes/helpers.php';
require '/site/includes/ai-crawlers.php';
function live_get(string $url, int $timeout): array {
    $hops = [];
    for ($i = 0; $i <= 5; $i++) {
        $headers = []; $body = '';
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => $timeout, CURLOPT_CONNECTTIMEOUT => 6,
            CURLOPT_ENCODING => '', CURLOPT_CAINFO => '/ca/ca.pem', CURLOPT_USERAGENT => AIC_UA,
            CURLOPT_HEADERFUNCTION => function ($c, $h) use (&$headers) { $kv = explode(':', $h, 2); if (count($kv) === 2) { $headers[strtolower(trim($kv[0]))][] = trim($kv[1]); } return strlen($h); },
            CURLOPT_WRITEFUNCTION => function ($c, $d) use (&$body) { $body .= $d; return strlen($body) > AIC_MAX_BYTES ? 0 : strlen($d); }]);
        curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        if ($status >= 300 && $status < 400 && !empty($headers['location'])) { $hops[] = [$status, $url]; $url = aic_abs_url(end($headers['location']), $url); continue; }
        if ($status === 0) { return ['status' => 0, 'url' => $url, 'hops' => $hops, 'headers' => [], 'body' => '', 'error' => curl_error($ch)]; }
        return ['status' => $status, 'url' => $url, 'hops' => $hops, 'headers' => $headers, 'body' => $body, 'error' => ''];
    }
    return ['status' => 0, 'url' => $url, 'hops' => $hops, 'headers' => [], 'body' => '', 'error' => 'Too many redirects.'];
}
$urls = json_decode(${JSON.stringify(JSON.stringify(urls))}, true);
$html = '';
foreach ($urls as $u) {
    $t0 = microtime(true);
    $r = aic_check($u, 'live_get');
    if (!$r['ok']) { echo "\\n== $u: ERROR {$r['error']}\\n"; continue; }
    $blocked = array_column(array_filter($r['crawlers'], fn($c) => $c['verdict'] === 'blocked'), 'token');
    printf("\\n== %s -> %s (HTTP %d, %.1fs)\\n   robots: %s (%d) | words %d%s | llms %s | metas %s | x-robots %s\\n   allowed: search %d/9, user %d/6, training %d/8\\n   blocked: %s\\n",
        $u, $r['url'], $r['status'], microtime(true) - $t0, $r['robots']['mode'], $r['robots']['status'], $r['page']['words'], $r['page']['shell'] ? ' SHELL' : '',
        $r['llms'] ? 'yes' : 'no', json_encode($r['page']['metas']), json_encode($r['page']['x_robots']),
        $r['tally']['search']['allowed'] ?? 0, $r['tally']['user']['allowed'] ?? 0, $r['tally']['training']['allowed'] ?? 0, implode(', ', $blocked) ?: 'none');
    foreach (array_slice(aic_next_steps($r), 0, 3) as $s) { echo "   next: $s\\n"; }
    if ($html === '') { ob_start(); aic_render_results($r); $html = ob_get_clean(); }
}
echo "\\n@@HTML@@" . $html;
`;
const r = await php.run({ code });
const [text, html] = r.text.split('@@HTML@@');
process.stdout.write(text);
if (r.errors) console.log(String(r.errors));
if (preview && html) {
  fs.writeFileSync(path.resolve(root, preview), `<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex"><title>AI crawler checker preview</title><link rel="stylesheet" href="/assets/css/style.css"></head><body><main><section class="section aic-tool"><div class="container narrow">${html}</div></section></main></body></html>`);
  console.log(`\npreview written to ${preview}`);
}
fs.rmSync(caDir, { recursive: true, force: true });
process.exit(0);
