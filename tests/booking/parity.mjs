// The booking page and the server must offer exactly the same times: the page
// draws them (assets/js/booking.js, zoneSlots) and the server checks them again
// when a booking is made (includes/booking.php, booking_zone_slots). This runs
// both on the same free grids for many zones, moments and settings, including
// daylight saving changes, and fails on the first difference.
//
//   PHP_WASM_DIR="$TEMP/php-wasm" MSYS_NO_PATHCONV=1 node tests/booking/parity.mjs
import fs from 'node:fs';
import path from 'node:path';
import vm from 'node:vm';
import { fileURLToPath, pathToFileURL } from 'node:url';

const here = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(here, '../..');
const base = (process.env.PHP_WASM_DIR ?? '').replace(/^\/([a-z])\//i, (_, d) => `${d.toUpperCase()}:/`);
if (!base) {
  console.error('Set PHP_WASM_DIR to the folder where @php-wasm/node is installed.');
  process.exit(2);
}
const mod = (name) => import(pathToFileURL(path.join(base, 'node_modules', name, 'index.js')).href);
const { PHP, SupportedPHPVersions } = await mod('@php-wasm/universal');
const { loadNodeRuntime, createNodeFsMountHandler } = await mod('@php-wasm/node');
const php = new PHP(await loadNodeRuntime(SupportedPHPVersions.includes('8.5') ? '8.5' : '8.4', { emscriptenOptions: { processId: 1 } }));
for (const [at, dir] of [['/site', root], ['/t', path.join(root, 'tests/crm')]]) {
  php.mkdir(at);
  await php.mount(at, createNodeFsMountHandler(dir));
}

const zones = ['Pacific/Honolulu', 'America/Anchorage', 'America/Los_Angeles', 'America/Denver', 'America/Phoenix', 'America/Chicago', 'America/New_York',
  'America/Halifax', 'America/St_Johns', 'America/Sao_Paulo', 'Europe/London', 'Europe/Berlin', 'Asia/Dubai', 'Asia/Kolkata', 'Asia/Kathmandu',
  'Asia/Singapore', 'Asia/Tokyo', 'Australia/Sydney', 'Australia/Adelaide', 'Australia/Eucla', 'Pacific/Auckland', 'Pacific/Chatham', 'Pacific/Kiritimati', 'Pacific/Pago_Pago', 'UTC'];
/* [moment, settings changes]: ordinary days, the US and EU autumn and spring changes, the southern ones, odd settings. */
const cases = [
  ['2026-11-11T21:00:00Z', {}], ['2026-11-11T20:59:00Z', {}], ['2026-11-09T00:30:00Z', {}],
  ['2026-10-30T15:00:00Z', {}], ['2026-10-23T09:00:00Z', {}], ['2027-03-12T10:00:00Z', {}], ['2027-03-26T07:00:00Z', {}],
  ['2026-09-30T23:00:00Z', {}], ['2027-04-01T23:00:00Z', {}],
  ['2026-11-11T21:00:00Z', { notice_minutes: 60 }], ['2026-11-11T21:07:00Z', { hours: '09:30-13:00, 14:00-18:30', length: 45, buffer: 0 }],
  ['2026-11-11T21:00:00Z', { days: [1, 3, 6], window_days: 9 }],
  ['2026-11-11T21:00:00Z', { same_day: true }], ['2026-11-11T20:59:00Z', { same_day: true }], ['2027-03-12T10:00:00Z', { same_day: true }],
];

const r = await php.run({ code: `<?php
require '/t/boot.php';
$zones = json_decode('${JSON.stringify(zones)}', true);
$cases = json_decode('${JSON.stringify(cases)}', true);
$out = [];
foreach ($cases as $i => [$at, $over]) {
    $cfg = booking_clean_settings(array_merge(booking_setting_defaults(), $over));
    $now = strtotime($at);
    /* A few busy blocks scattered over the window. */
    $busy = [];
    for ($k = 1; $k <= 9; $k++) {
        $s = $now + $k * 61 * 3600 + ($k * 1300) % 5400;
        $busy[] = [$s, $s + 1800 + ($k % 3) * 1800];
    }
    $grid = booking_free_grid($cfg, $now, $busy);
    $slots = [];
    foreach ($zones as $z) {
        $slots[$z] = booking_zone_slots($cfg, $now, $grid, $z);
    }
    $out[] = ['now' => $now, 'R' => ['grid' => $grid, 'len' => $cfg['length'], 'step' => BOOKING_STEP, 'hours' => booking_hours($cfg['hours']), 'days' => $cfg['days'], 'notice' => $cfg['notice_minutes'], 'sameDay' => $cfg['same_day']], 'slots' => $slots];
}
echo json_encode($out);` });
if (r.errors) console.error(r.errors);
const data = JSON.parse(r.text.slice(r.text.indexOf('[')));

const ctx = vm.createContext({ window: {}, Intl, Date, Math, JSON, String, Object, Array, Number, isNaN, console });
vm.runInContext(fs.readFileSync(path.join(root, 'assets/js/booking.js'), 'utf8'), ctx);
const B = ctx.window.VTBooking;

let checked = 0, failed = 0, total = 0;
data.forEach((c, i) => {
  for (const z of zones) {
    const js = B.zoneSlots(c.R, z, c.now);
    const want = c.slots[z];
    checked++;
    total += want.length;
    if (JSON.stringify(js) !== JSON.stringify(want)) {
      failed++;
      const extra = js.filter((t) => !want.includes(t)).slice(0, 3).map((t) => new Date(t * 1000).toISOString());
      const missing = want.filter((t) => !js.includes(t)).slice(0, 3).map((t) => new Date(t * 1000).toISOString());
      console.log(`FAIL case ${i} (${cases[i][0]} ${JSON.stringify(cases[i][1])}) ${z}: page ${js.length}, server ${want.length}; page only ${extra.join(' ')}; server only ${missing.join(' ')}`);
    }
  }
});
console.log(`${checked - failed} of ${checked} zone and moment pairs match (${total} times compared)`);
process.exit(failed ? 1 : 0);
