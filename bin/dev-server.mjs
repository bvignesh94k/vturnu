// Local preview without PHP installed: serves the site from this folder using
// PHP in WebAssembly (@php-wasm/node, the same runtime as tests/crm/run.mjs).
//
// Install the runtime once, outside OneDrive (its files are large):
//   cd %TEMP% && mkdir php-wasm && cd php-wasm && npm i @php-wasm/node@3.1.62 @php-wasm/universal@3.1.62
// Then:
//   node bin/dev-server.mjs [port]          (default port 8100)
// PHP_WASM_DIR overrides where the runtime lives (default: %TEMP%/php-wasm).
//
// Static files are served straight from disk, everything else runs
// api/index.php the way vercel.json routes it. The database is not reachable
// here, so admin, CRM and lead submission do not work; the public pages do.
import fs from 'node:fs';
import http from 'node:http';
import os from 'node:os';
import path from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
/* Git Bash hands over /c/Users/...; Node on Windows wants C:/Users/... */
const base = (process.env.PHP_WASM_DIR || path.join(os.tmpdir(), 'php-wasm')).replace(/^\/([a-z])\//i, (_, d) => `${d.toUpperCase()}:/`);
const mod = (name) => import(pathToFileURL(path.join(base, 'node_modules', name, 'index.js')).href);
const { PHP, SupportedPHPVersions } = await mod('@php-wasm/universal');
const { loadNodeRuntime, createNodeFsMountHandler } = await mod('@php-wasm/node');
const version = SupportedPHPVersions.includes('8.5') ? '8.5' : '8.4';
const php = new PHP(await loadNodeRuntime(version, { emscriptenOptions: { processId: 1 } }));
php.mkdir('/site');
await php.mount('/site', createNodeFsMountHandler(root));
php.chdir('/site');

const MIME = {
  html: 'text/html; charset=utf-8', css: 'text/css', js: 'text/javascript', mjs: 'text/javascript', json: 'application/json',
  svg: 'image/svg+xml', png: 'image/png', jpg: 'image/jpeg', jpeg: 'image/jpeg', webp: 'image/webp', avif: 'image/avif',
  gif: 'image/gif', ico: 'image/x-icon', woff2: 'font/woff2', woff: 'font/woff', txt: 'text/plain; charset=utf-8',
  xml: 'application/xml', webmanifest: 'application/manifest+json', pdf: 'application/pdf', mp4: 'video/mp4',
};
/* Same folders vercel.json keeps off the web. */
const hidden = /^\/(storage|includes|templates|bin|db|new|tests)(\/|$)/;

/* One PHP instance, so requests run one at a time. */
let queue = Promise.resolve();

const server = http.createServer((req, res) => {
  const pathname = decodeURIComponent(new URL(req.url, 'http://localhost').pathname);
  const file = path.join(root, pathname);
  if (pathname !== '/' && !hidden.test(pathname) && !pathname.endsWith('.php') && file.startsWith(root)
      && fs.existsSync(file) && fs.statSync(file).isFile()) {
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
        for (const [k, v] of Object.entries(req.headers)) {
          if (typeof v === 'string') headers[k] = v;
        }
        const r = await php.run({
          scriptPath: '/site/api/index.php',
          relativeUri: req.url,
          method: req.method,
          headers,
          body: chunks.length ? Buffer.concat(chunks) : undefined,
        });
        const out = {};
        for (const [k, v] of Object.entries(r.headers || {})) out[k] = v;
        res.writeHead(r.httpStatusCode || 200, out);
        res.end(Buffer.from(r.bytes));
        console.log(`${r.httpStatusCode || 200} ${req.method} ${req.url}`);
      } catch (e) {
        res.writeHead(500, { 'Content-Type': 'text/plain; charset=utf-8' });
        res.end(String(e?.response?.errors ?? e?.message ?? e));
        console.log(`500 ${req.method} ${req.url}: ${String(e?.response?.errors ?? e?.message ?? e).slice(0, 400)}`);
      }
    });
  });
});

const port = Number(process.argv[2] || process.env.PORT || 8100);
server.listen(port, '127.0.0.1', () => console.log(`vturnu preview on http://127.0.0.1:${port}/ (PHP ${version} in WebAssembly)`));
