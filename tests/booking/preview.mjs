// Local preview of Book a Meeting: the real booking pages and admin views,
// run by PHP in WebAssembly with the in-memory database of tests/crm (kept in a
// JSON file between requests) and emails saved as files, not sent.
//
//   node tests/booking/preview.mjs [port] [--env=<file> | --fake-google] [--state=<folder>]
//
// --env is a file of GOOGLE_CLIENT_ID=..., GOOGLE_CLIENT_SECRET=..., GOOGLE_REFRESH_TOKEN=...
// lines (bin/google-connect.mjs --env-out writes one). With it, times come from
// the real connect@vturnu.com calendar and bookings create real events with
// Google Meet links. --fake-google answers with the stand-in calendar of
// tests/crm/fake-google.php instead (a few busy blocks, invented Meet links),
// for trying the pages without touching the real calendar. With neither, the
// pages show booking as paused.
// Open /book-a-meeting/, /admin/ and /mail/ (the captured emails). Add
// ?_ip_tz=America/Chicago to any page to pose as a visitor in another zone.
import fs from 'node:fs';
import http from 'node:http';
import os from 'node:os';
import path from 'node:path';
import tls from 'node:tls';
import { fileURLToPath, pathToFileURL } from 'node:url';

const here = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(here, '../..');
const args = process.argv.slice(2);
const port = Number(args.find((a) => /^\d+$/.test(a)) || 8110);
const opt = (name) => (args.find((a) => a.startsWith(`--${name}=`)) || '').slice(name.length + 3);
const stateDir = opt('state') || path.join(os.tmpdir(), 'vturnu-booking-preview');
fs.mkdirSync(path.join(stateDir, 'mail'), { recursive: true });

const env = { MAIL_TRANSPORT: 'capture' };
if (args.includes('--fake-google')) {
  Object.assign(env, { FAKE_GOOGLE: '1', GOOGLE_CLIENT_ID: 'fake', GOOGLE_CLIENT_SECRET: 'fake', GOOGLE_REFRESH_TOKEN: 'fake' });
}
if (opt('env')) {
  for (const line of fs.readFileSync(opt('env'), 'utf8').split(/\r?\n/)) {
    const m = line.match(/^(GOOGLE_[A-Z_]+)=(.*)$/);
    if (m) env[m[1]] = m[2];
  }
}

const base = (process.env.PHP_WASM_DIR || path.join(os.tmpdir(), 'php-wasm')).replace(/^\/([a-z])\//i, (_, d) => `${d.toUpperCase()}:/`);
const mod = (name) => import(pathToFileURL(path.join(base, 'node_modules', name, 'index.js')).href);
const { PHP, SupportedPHPVersions } = await mod('@php-wasm/universal');
const { loadNodeRuntime, createNodeFsMountHandler } = await mod('@php-wasm/node');
const php = new PHP(await loadNodeRuntime(SupportedPHPVersions.includes('8.5') ? '8.5' : '8.4', { emscriptenOptions: { processId: 1 } }));
for (const [at, dir] of [['/site', root], ['/t', path.join(root, 'tests/crm')], ['/tb', here], ['/state', stateDir]]) {
  php.mkdir(at);
  await php.mount(at, createNodeFsMountHandler(dir));
}
/* curl inside PHP looks for its CA bundle here; give it Node's. */
php.mkdir('/etc/ssl/certs');
php.writeFile('/etc/ssl/certs/ca-certificates.crt', tls.rootCertificates.join('\n'));
php.chdir('/site');

const MIME = { css: 'text/css', js: 'text/javascript', svg: 'image/svg+xml', png: 'image/png', jpg: 'image/jpeg', webp: 'image/webp', woff2: 'font/woff2', ico: 'image/x-icon', html: 'text/html; charset=utf-8', json: 'application/json' };
let queue = Promise.resolve();

http.createServer((req, res) => {
  const pathname = decodeURIComponent(new URL(req.url, 'http://localhost').pathname);
  if (pathname === '/mail' || pathname === '/mail/') {
    const files = fs.readdirSync(path.join(stateDir, 'mail')).filter((f) => f.endsWith('.html')).sort().reverse();
    res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
    res.end('<!doctype html><meta charset="utf-8"><title>Captured emails</title><body style="font:15px system-ui;padding:24px"><h1>Captured emails</h1><ol>'
      + files.map((f) => `<li><a href="/mail/${encodeURIComponent(f)}">${f}</a></li>`).join('') + '</ol>');
    return;
  }
  const file = pathname.startsWith('/mail/') ? path.join(stateDir, 'mail', path.basename(pathname)) : path.join(root, pathname);
  if ((pathname.startsWith('/assets/') || pathname.startsWith('/mail/')) && fs.existsSync(file) && fs.statSync(file).isFile()) {
    res.writeHead(200, { 'Content-Type': MIME[path.extname(file).slice(1).toLowerCase()] || 'application/octet-stream' });
    fs.createReadStream(file).pipe(res);
    return;
  }
  const chunks = [];
  req.on('data', (c) => chunks.push(c));
  req.on('end', () => {
    queue = queue.then(async () => {
      try {
        const headers = {};
        for (const [k, v] of Object.entries(req.headers)) if (typeof v === 'string') headers[k] = v;
        const r = await php.run({ scriptPath: '/tb/preview.php', relativeUri: req.url, method: req.method, headers, body: chunks.length ? Buffer.concat(chunks) : undefined, env });
        res.writeHead(r.httpStatusCode || 200, r.headers || {});
        res.end(Buffer.from(r.bytes));
        console.log(`${r.httpStatusCode || 200} ${req.method} ${req.url}`);
        if (r.errors) console.log(String(r.errors).slice(0, 2000));
      } catch (e) {
        res.writeHead(500, { 'Content-Type': 'text/plain; charset=utf-8' });
        res.end(String(e?.response?.errors ?? e?.message ?? e));
        console.log(`500 ${req.method} ${req.url}: ${String(e?.response?.errors ?? e?.message ?? e).slice(0, 1500)}`);
      }
    });
  });
}).listen(port, () => console.log(`Booking preview on http://localhost:${port}/book-a-meeting/ (state in ${stateDir})${env.FAKE_GOOGLE ? ', stand-in Google Calendar' : env.GOOGLE_REFRESH_TOKEN ? ', real Google Calendar' : ', no Google'}`));
