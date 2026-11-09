// The service worker must answer only for the site's own pages and assets.
// A fetch made from inside the worker is checked against the site's
// connect-src, which does not list other sites, so a worker that answers for
// them breaks them: the TechBehemoths badge once, and reCAPTCHA (its main
// script lives on www.gstatic.com) until sw.js v15.
//
//   node tests/sw/sw-test.mjs
import fs from 'node:fs';
import path from 'node:path';
import vm from 'node:vm';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
const listeners = {};
const self = {
  location: new URL('https://vturnu.com/sw.js'),
  addEventListener: (type, fn) => { listeners[type] = fn; },
  skipWaiting: () => {},
  clients: { claim: () => Promise.resolve() },
};
const caches = { match: () => Promise.resolve(undefined), open: () => Promise.resolve({ put: () => {}, add: () => Promise.resolve() }), keys: () => Promise.resolve([]) };
vm.runInNewContext(fs.readFileSync(path.join(root, 'sw.js'), 'utf8'), { self, caches, fetch: () => Promise.resolve({ ok: true, clone() { return this; } }), URL, Promise, console });

/** Whether the worker takes over a request (calls respondWith). */
const handles = (url, mode = 'no-cors', method = 'GET') => {
  let answered = false;
  listeners.fetch({ request: { url, mode, method }, respondWith: () => { answered = true; } });
  return answered;
};

const checks = [
  ['reCAPTCHA main script goes to the network', !handles('https://www.gstatic.com/recaptcha/releases/abc/recaptcha__en.js')],
  ['reCAPTCHA loader goes to the network', !handles('https://www.google.com/recaptcha/api.js?render=key')],
  ['other sites\' images go to the network', !handles('https://cdn.techbehemoths.com/public/static/validation/tb-verified-logo.svg')],
  ['Google APIs go to the network', !handles('https://fonts.googleapis.com/css2?family=Poppins')],
  ['own assets are cached', handles('https://vturnu.com/assets/js/main.js')],
  ['pages are network-first', handles('https://vturnu.com/seo-services/', 'navigate')],
  ['admin is left alone', !handles('https://vturnu.com/admin/', 'navigate')],
  ['booking pages are left alone', !handles('https://vturnu.com/book-a-meeting/abc/', 'navigate')],
  ['form posts are left alone', !handles('https://vturnu.com/enquiry', 'cors', 'POST')],
];
let failed = 0;
for (const [name, ok] of checks) {
  if (!ok) { failed++; console.log('FAIL: ' + name); }
}
console.log(`${checks.length - failed} passed, ${failed} failed`);
process.exit(failed ? 1 : 0);
