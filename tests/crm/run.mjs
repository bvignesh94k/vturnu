// CRM tests: PHP 8.5 (WebAssembly) runs includes/crm.php and the admin views
// against an in-memory stand-in for Postgres, with email captured, not sent.
//
// There is no PHP on the dev machine, so install the runtime once, outside
// OneDrive (its WebAssembly files are large):
//   cd %TEMP% && mkdir php-wasm && cd php-wasm && npm i @php-wasm/node@3.1.62 @php-wasm/universal@3.1.62
// then, from Git Bash at the repo root:
//   PHP_WASM_DIR="$TEMP/php-wasm" MSYS_NO_PATHCONV=1 node tests/crm/run.mjs
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';

const here = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(here, '../..');
/* Git Bash hands over /c/Users/...; Node on Windows wants C:/Users/... */
const base = (process.env.PHP_WASM_DIR ?? '').replace(/^\/([a-z])\//i, (_, d) => `${d.toUpperCase()}:/`);
if (!base) {
  console.error('Set PHP_WASM_DIR to the folder where @php-wasm/node is installed (see the top of this file).');
  process.exit(2);
}
const mod = (name) => import(pathToFileURL(path.join(base, 'node_modules', name, 'index.js')).href);
const { PHP, SupportedPHPVersions } = await mod('@php-wasm/universal');
const { loadNodeRuntime, createNodeFsMountHandler } = await mod('@php-wasm/node');
const php = new PHP(await loadNodeRuntime(SupportedPHPVersions.includes('8.5') ? '8.5' : '8.4', { emscriptenOptions: { processId: 1 } }));
php.mkdir('/site');
await php.mount('/site', createNodeFsMountHandler(root));
php.mkdir('/t');
await php.mount('/t', createNodeFsMountHandler(here));
php.chdir('/site');

let failed = 0;
const unit = await php.run({ code: `<?php require '/t/crm-test.php';` });
process.stdout.write(unit.text);
if (unit.errors || !/ 0 failed/.test(unit.text)) failed++;

/* Book a Meeting: slots, time zones, the Google flow (against a stand-in), emails and pages. */
const booking = await php.run({ code: `<?php require '/t/booking-test.php';` });
process.stdout.write(booking.text);
if (booking.errors || !/ 0 failed/.test(booking.text)) { failed++; if (booking.errors) console.log(String(booking.errors).slice(0, 1500)); }

/* Vee, the AI live chat: redaction, cleaning, model fallback chain, replies, the lead and email pipeline.
   Unlike the suites above, this one deliberately drives failing models (a 429,
   a bad answer, a server error) to prove the chain falls through correctly,
   and chat.php logs each of those with error_log() by design. So a clean run
   here still carries stderr text; only " 0 failed" in the output says whether
   the checks themselves passed. A real crash still shows as a failed check,
   since done() never prints " 0 failed" when a PHP fatal cut the script short. */
const chat = await php.run({ code: `<?php require '/t/chat-test.php';` });
process.stdout.write(chat.text);
if (!/ 0 failed/.test(chat.text)) { failed++; if (chat.errors) console.log(String(chat.errors).slice(0, 1500)); }

/* Every admin view must render without a single PHP warning. */
const views = ['view=dashboard', 'view=leads', 'view=leads&channel=Organic+search&grade=A&proposal=none', 'view=lead&id=42', 'view=lead&id=43', 'view=proposal&id=42',
  'view=proposal&id=43&kind=followup', 'view=meeting&id=42', 'view=meeting&id=43', 'view=meetings', 'view=pipeline', 'view=reports', 'view=settings', 'view=junk', 'view=deleted', 'view=audits', 'view=downloads', 'view=subscribers',
  'view=updates', 'view=update-edit', 'view=update-edit&slug=november-core-update-2026', 'view=chats', 'view=chat&id=1', 'view=outreach'];
for (const v of views) {
  const get = JSON.stringify(Object.fromEntries(new URLSearchParams(v))).replace(/'/g, "\\'");
  try {
    const r = await php.run({ code: `<?php
$_GET = json_decode('${get}', true);
$_SERVER['REQUEST_METHOD'] = 'GET';
ob_start();
require '/t/boot.php';
require '/t/fixtures.php';
$exp = time() + 3600; $payload = 'admin|' . $exp;
$_COOKIE['vt_admin'] = rtrim(strtr(base64_encode($payload . '|' . hash_hmac('sha256', $payload, SECURITY_SECRET)), '+/', '-_'), '=');
require '/site/includes/data/menu.php';
require '/site/includes/data/pages.php';
require '/site/includes/admin.php';
` });
    const bad = r.errors || /Fatal error|Warning:|Notice:|Deprecated:|Uncaught/.test(r.text) || !r.text.includes('</html>');
    console.log(`${bad ? 'FAIL' : 'ok  '} admin ${v}`);
    if (bad) { failed++; console.log(String(r.errors || r.text.slice(0, 600))); }
  } catch (e) {
    failed++;
    console.log(`FAIL admin ${v}\n${String(e.response?.errors ?? e.message).slice(0, 1200)}`);
  }
}
console.log(failed ? `\n${failed} problem(s)` : '\nAll CRM tests passed and every admin view rendered cleanly.');
process.exit(failed ? 1 : 0);
