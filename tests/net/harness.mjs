// The test network for the php-wasm suites in tests/audit and tests/aic.
//
// php-wasm's curl reaches the network through a proxy inside this Node
// process, which calls dns.lookup() for a host name and then
// net.createConnection(port, ip). startTestNet() replaces both:
//  - the pretend public addresses below reach the local test sites in
//    sites.mjs, and every private address reaches a stand-in internal site
//    whose pages say INTERNAL SECRET;
//  - a test name (anything under .example) that curl looks up itself gets
//    10.0.0.1, the answer a DNS rebinding attacker would give. Code that pins
//    its connections never triggers that lookup;
//  - any other address is refused, so the suites never reach the internet.
// problems() lists what went wrong: a private connection, a name curl looked
// up itself, an address outside the test, or a hook that never ran.
// tests/net/test-net.php is the PHP side: a resolver for the same names.
import { spawn } from 'node:child_process';
import { createRequire, syncBuiltinESMExports } from 'node:module';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const here = path.dirname(fileURLToPath(import.meta.url));
const require = createRequire(import.meta.url);
const net = require('node:net');
const dns = require('node:dns');

/* What the PHP side's resolver answers, as gethostbynamel() would. Pass it to
   PHP as $_SERVER['TEST_DNS'] (JSON). */
export const DNS = {
  'site-a.example': ['93.184.216.34'],
  'site-b.example': ['93.184.216.35'],
  'intranet.example': ['10.0.0.1'],
  'mixed.example': ['93.184.216.35', '192.168.1.5'],
  'cgnat.example': ['100.64.3.2'],
  'localhost': ['127.0.0.1'],
};
const PRIVATE = ['10.0.0.1:80', '169.254.169.254:80', '127.0.0.1:80', '192.168.1.5:80', '100.64.3.2:80'];

export async function startTestNet() {
  /* The sites run in their own process, so PHP's blocking curl never waits on them. */
  const sites = spawn(process.execPath, [path.join(here, 'sites.mjs')], { stdio: ['ignore', 'pipe', 'inherit'] });
  const ports = await new Promise((ok, bad) => {
    sites.stdout.once('data', (d) => ok(JSON.parse(String(d))));
    sites.on('exit', () => bad(new Error('tests/net/sites.mjs exited')));
  });
  const routes = { '93.184.216.34:80': ports.a, '93.184.216.35:80': ports.b };
  for (const key of PRIVATE) routes[key] = ports.internal;
  const closed = await new Promise((ok) => {
    const s = net.createServer();
    s.listen(0, '127.0.0.1', () => { const p = s.address().port; s.close(() => ok(p)); });
  });

  const attempts = [];
  const lookups = [];
  const realConnect = net.createConnection;
  const realLookup = dns.lookup;
  net.createConnection = function (...args) {
    if (typeof args[0] === 'number' && typeof args[1] === 'string') {
      const [port, ip, cb] = args;
      attempts.push(`${ip}:${port}`);
      return realConnect.call(net, routes[`${ip}:${port}`] ?? closed, '127.0.0.1', cb);
    }
    return realConnect.apply(net, args);
  };
  dns.lookup = function (host, opts, cb) {
    if (typeof opts === 'function') { cb = opts; opts = {}; }
    if (/\.example\.?$/i.test(String(host))) {
      lookups.push(String(host));
      process.nextTick(() => (opts?.all ? cb(null, [{ address: '10.0.0.1', family: 4 }]) : cb(null, '10.0.0.1', 4)));
      return;
    }
    return realLookup.call(dns, host, opts, cb);
  };
  syncBuiltinESMExports();

  return {
    count: () => attempts.length,
    problems() {
      const out = [];
      const reached = [...new Set(attempts.filter((a) => PRIVATE.includes(a)))];
      const strays = [...new Set(attempts.filter((a) => !(a in routes)))];
      if (reached.length) out.push(`connected to private addresses: ${reached.join(', ')}`);
      if (lookups.length) out.push(`curl looked these names up itself instead of using the checked address: ${[...new Set(lookups)].join(', ')}`);
      if (strays.length) out.push(`tried addresses outside the test: ${strays.join(', ')}`);
      if (!attempts.length) out.push('the connection hook never ran, so nothing reached the test sites');
      return out;
    },
    stop() {
      sites.kill();
      net.createConnection = realConnect;
      dns.lookup = realLookup;
      syncBuiltinESMExports();
    },
  };
}
