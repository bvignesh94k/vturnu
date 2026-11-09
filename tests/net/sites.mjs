// Test sites for tests/net/harness.mjs: plain HTTP servers on 127.0.0.1.
// The harness routes the pretend public addresses to "a" and "b", and every
// private address to "internal", whose pages carry INTERNAL SECRET so a
// guard that failed shows up in the results. Prints the ports as JSON once
// every server listens.
import http from 'node:http';
import zlib from 'node:zlib';

const page = (title) => `<!doctype html><html lang="en"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>${title}: a test page for the free SEO audit</title>
<meta name="description" content="A test page for the VTurnU free SEO audit, with a title, a description, headings, an image and enough words.">
</head><body><h1>${title}</h1><h2>One</h2><h2>Two</h2><img src="/a.png" alt="A photo">
<p>${'Plain words for the audit to count. '.repeat(120)}</p></body></html>`;

const send = (req, res, code, body, headers = {}) => {
  res.writeHead(code, { 'Content-Type': 'text/html; charset=utf-8', ...headers });
  res.end(req.method === 'HEAD' ? '' : body);
};
const moved = (res, code, location) => {
  res.writeHead(code, { Location: location, 'Content-Length': '0' });
  res.end();
};

/* Site A: the site the visitor enters. Most paths redirect somewhere. */
const A_REDIRECTS = {
  '/to-private': [302, 'http://10.0.0.1/admin'],
  '/to-metadata': [301, 'http://169.254.169.254/latest/meta-data/'],
  '/to-loopback': [307, 'http://127.0.0.1/'],
  '/to-localhost': [302, 'http://localhost/'],
  '/to-intranet': [308, 'http://intranet.example/'],
  '/to-mixed': [302, 'http://mixed.example/'],
  '/to-cgnat': [302, 'http://cgnat.example/'],
  '/to-port': [302, 'http://site-b.example:8080/landing'],
  '/to-ftp': [302, 'ftp://site-b.example/landing'],
  '/to-mailto': [302, 'mailto:hello@site-b.example'],
  '/to-userinfo': [302, 'http://user:pw@site-b.example/landing'],
  '/to-ipv6': [302, 'http://[::1]/'],
  '/to-b': [301, 'http://site-b.example/landing'],
  '/to-b-dot': [302, 'http://SITE-B.example./landing'],
  '/to-b-relative': [302, '//site-b.example/landing'],
  '/relative': [302, '/landed'],
  '/loop': [302, '/loop'],
  '/to-flip': [302, 'http://flip.example/landed'],
};
const siteA = (req, res) => {
  const p = new URL(req.url, 'http://x').pathname;
  if (A_REDIRECTS[p]) return moved(res, ...A_REDIRECTS[p]);
  if (p === '/' || p === '/landed') return send(req, res, 200, page(p === '/' ? 'Site A home' : 'Site A landed'));
  if (p === '/robots.txt') return send(req, res, 200, 'User-agent: *\nAllow: /\n', { 'Content-Type': 'text/plain' });
  if (p === '/sitemap.xml') return send(req, res, 200, '<urlset/>', { 'Content-Type': 'application/xml' });
  if (p === '/slow') return setTimeout(() => send(req, res, 200, page('Slow')), 2500);
  if (p === '/gzip') {
    const gz = zlib.gzipSync(page('Compressed'));
    return send(req, res, 200, /gzip/.test(req.headers['accept-encoding'] ?? '') ? gz : page('Compressed'),
      /gzip/.test(req.headers['accept-encoding'] ?? '') ? { 'Content-Encoding': 'gzip' } : {});
  }
  if (p === '/big') { /* 4 MB with no Content-Length, so only a byte cap can stop it */
    res.writeHead(200, { 'Content-Type': 'text/html' });
    const chunk = '<p>' + 'x'.repeat(65530) + '</p>\n';
    let left = 64;
    const more = () => { while (left > 0) { left--; if (!res.write(chunk)) return res.once('drain', more); } res.end(); };
    return more();
  }
  return send(req, res, 404, page('Not found'));
};

/* Site B: a public site that site A redirects to. Its robots.txt, sitemap and
   llms.txt redirect to private addresses. */
const siteB = (req, res) => {
  const p = new URL(req.url, 'http://x').pathname;
  if (p === '/landing') return send(req, res, 200, page('Site B landing'));
  if (p === '/robots.txt') return moved(res, 302, 'http://10.0.0.1/robots.txt');
  if (p === '/sitemap.xml') return moved(res, 302, 'http://169.254.169.254/sitemap.xml');
  if (p === '/llms.txt') return moved(res, 302, 'http://127.0.0.1/llms.txt');
  return send(req, res, 404, page('Not found'));
};

/* What a private network would answer. Nothing in the tests may ever see it. */
const internal = (req, res) => {
  const p = new URL(req.url, 'http://x').pathname;
  if (p === '/robots.txt') return send(req, res, 200, 'User-agent: *\nAllow: /\nSitemap: http://10.0.0.1/INTERNAL-SECRET.xml\n', { 'Content-Type': 'text/plain' });
  if (p === '/llms.txt') return send(req, res, 200, 'INTERNAL SECRET', { 'Content-Type': 'text/plain' });
  return send(req, res, 200, page('INTERNAL SECRET'));
};

const ports = {};
await Promise.all(Object.entries({ a: siteA, b: siteB, internal }).map(([name, handler]) => new Promise((ok) => {
  const server = http.createServer(handler);
  server.listen(0, '127.0.0.1', () => { ports[name] = server.address().port; ok(); });
})));
process.stdout.write(JSON.stringify(ports) + '\n');
