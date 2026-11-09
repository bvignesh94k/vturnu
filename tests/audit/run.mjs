// Free SEO audit tests: PHP 8.5 (WebAssembly) runs tests/audit/audit-test.php
// against includes/audit.php. No internet and no database.
//
// php-wasm's curl reaches the network through a proxy inside this process
// that calls net.createConnection(port, ip). This runner replaces that call,
// so the pretend public addresses below reach the local test sites in
// sites.mjs, every private address reaches a stand-in "internal" site, and
// anything else is refused. The run fails if the audit ever connects to a
// private address.
//
// Same runtime as tests/crm/run.mjs. From Git Bash at the repo root:
//   PHP_WASM_DIR="$TEMP/php-wasm" MSYS_NO_PATHCONV=1 node tests/audit/run.mjs
import { spawn } from 'node:child_process';
import { createRequire, syncBuiltinESMExports } from 'node:module';
import path from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';

const here = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(here, '../..');
const base = (process.env.PHP_WASM_DIR ?? '').replace(/^\/([a-z])\//i, (_, d) => `${d.toUpperCase()}:/`);
if (!base) {
  console.error('Set PHP_WASM_DIR to the folder where @php-wasm/node is installed (see tests/crm/run.mjs).');
  process.exit(2);
}

/* Host names the PHP side resolves (audit_get()'s $resolve), as gethostbynamel() would. */
const DNS = {
  'site-a.example': ['93.184.216.34'],
  'site-b.example': ['93.184.216.35'],
  'intranet.example': ['10.0.0.1'],
  'mixed.example': ['93.184.216.35', '192.168.1.5'],
  'cgnat.example': ['100.64.3.2'],
  'localhost': ['127.0.0.1'],
};
const PRIVATE = ['10.0.0.1:80', '169.254.169.254:80', '127.0.0.1:80', '192.168.1.5:80', '100.64.3.2:80'];

/* The test sites run in their own process, so PHP's blocking curl never waits on them. */
const sites = spawn(process.execPath, [path.join(here, 'sites.mjs')], { stdio: ['ignore', 'pipe', 'inherit'] });
const ports = await new Promise((ok, bad) => {
  sites.stdout.once('data', (d) => ok(JSON.parse(String(d))));
  sites.on('exit', () => bad(new Error('sites.mjs exited')));
});
const ROUTES = { '93.184.216.34:80': ports.a, '93.184.216.35:80': ports.b };
for (const key of PRIVATE) ROUTES[key] = ports.internal;

const require = createRequire(import.meta.url);
const net = require('node:net');
const realConnect = net.createConnection;
const attempts = [];
const closed = await new Promise((ok) => { const s = net.createServer(); s.listen(0, '127.0.0.1', () => { const p = s.address().port; s.close(() => ok(p)); }); });
net.createConnection = function (...args) {
  if (typeof args[0] === 'number' && typeof args[1] === 'string') {
    const [port, ip, cb] = args;
    attempts.push(`${ip}:${port}`);
    return realConnect.call(net, ROUTES[`${ip}:${port}`] ?? closed, '127.0.0.1', cb);
  }
  return realConnect.apply(net, args);
};
syncBuiltinESMExports();

const mod = (name) => import(pathToFileURL(path.join(base, 'node_modules', name, 'index.js')).href);
const { PHP, SupportedPHPVersions } = await mod('@php-wasm/universal');
const { loadNodeRuntime, createNodeFsMountHandler } = await mod('@php-wasm/node');
const php = new PHP(await loadNodeRuntime(SupportedPHPVersions.includes('8.5') ? '8.5' : '8.4', { emscriptenOptions: { processId: 1 } }));
php.mkdir('/site');
await php.mount('/site', createNodeFsMountHandler(root));
php.mkdir('/t');
await php.mount('/t', createNodeFsMountHandler(here));

const json = JSON.stringify(DNS).replace(/\\/g, '\\\\').replace(/'/g, "\\'");
let r;
try {
  r = await php.run({ code: `<?php $_SERVER['AUDIT_TEST_DNS'] = '${json}'; require '/t/audit-test.php';` });
} finally {
  sites.kill();
}
process.stdout.write(r.text);
if (r.errors) console.log(String(r.errors));

const reached = attempts.filter((a) => PRIVATE.includes(a));
const strays = attempts.filter((a) => !(a in ROUTES));
if (reached.length) console.log(`FAIL connected to private addresses: ${[...new Set(reached)].join(', ')}`);
if (strays.length) console.log(`FAIL tried addresses outside the test: ${[...new Set(strays)].join(', ')}`);
if (!attempts.length) console.log('FAIL the connection hook never ran, so nothing above reached the test sites');
const ok = !r.errors && / 0 failed/.test(r.text) && !/Fatal error|Warning:|Deprecated:|Notice:/.test(r.text)
  && !reached.length && !strays.length && attempts.length > 0;
console.log(ok ? `All free audit tests passed (${attempts.length} connections, none to a private address).` : 'Free audit tests FAILED.');
process.exit(ok ? 0 : 1);
