/**
 * Builds the inline diagrams for long-form articles, in the site's banner style.
 *
 *   node bin/build-post-figures.mjs
 *
 * Each diagram is one HTML layout rendered twice: 1600x900 for desktop (plus an
 * 820 wide copy) and a stacked phone layout, 800 pixels wide, so the words stay
 * readable instead of shrinking. Uses Chrome through the DevTools protocol.
 *
 * Output: assets/img/blog/fig/<name>-1600.webp, -820.webp and -mobile.webp
 */
import { spawn } from 'node:child_process';
import { mkdirSync, writeFileSync, rmSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { tmpdir } from 'node:os';
import { fileURLToPath } from 'node:url';

const ROOT = join(dirname(fileURLToPath(import.meta.url)), '..');
const CHROME = process.env.CHROME || 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const OUT = join(ROOT, 'assets/img/blog/fig');

const MARK = `<svg width="52" height="46" viewBox="199 214 667 591" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><defs><linearGradient id="mv" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#00b8d9"/><stop offset="1" stop-color="#4338ca"/></linearGradient><linearGradient id="mu" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ff3d8a"/><stop offset="1" stop-color="#ff9100"/></linearGradient></defs><g transform="translate(130 130) scale(7.74)"><path d="M16 18 L38 80 L60 18" stroke="url(#mv)" stroke-width="14" stroke-linecap="round" stroke-linejoin="round" fill="none" style="mix-blend-mode:multiply"/><path d="M54 24 L54 56 Q54 80 71 80 Q88 80 88 56 L88 24" stroke="url(#mu)" stroke-width="14" stroke-linecap="round" fill="none" style="mix-blend-mode:multiply"/></g></svg>`;

const BASE = `*{box-sizing:border-box;margin:0;padding:0}
html,body{width:100%}
body{position:relative;font-family:Poppins,sans-serif;color:#1d1936;background:
radial-gradient(ellipse 760px 520px at 94% 4%,rgba(0,184,217,.16),transparent 62%),
radial-gradient(ellipse 700px 480px at 82% 100%,rgba(255,61,138,.13),transparent 62%),
radial-gradient(ellipse 620px 420px at 0% 100%,rgba(255,145,0,.1),transparent 62%),#fcfbff}
.f{position:relative;width:1600px;height:900px;padding:44px 60px 42px;display:flex;flex-direction:column;overflow:hidden}
.top{display:flex;align-items:center;justify-content:space-between;gap:32px;margin-bottom:28px}
h1{font-size:46px;line-height:1.1;font-weight:800;letter-spacing:-.022em}
.lock{display:flex;align-items:center;gap:10px;flex:none}
.word{font-family:Caprasimo,serif;font-size:30px;line-height:1}
.word .v{background:linear-gradient(180deg,#00b8d9,#4338ca);-webkit-background-clip:text;color:transparent}
.word .u{background:linear-gradient(180deg,#ff3d8a,#ff9100);-webkit-background-clip:text;color:transparent}
.strip{position:absolute;left:0;right:0;bottom:0;height:12px;background:linear-gradient(90deg,#00b8d9,#4338ca 42%,#ff3d8a 72%,#ff9100)}
@media (max-width:600px){
.f{width:400px;height:auto;padding:22px 18px 30px;display:block}
.top{flex-direction:column-reverse;align-items:flex-start;gap:12px;margin-bottom:16px}
h1{font-size:25px}
.lock svg{width:32px;height:28px}.word{font-size:19px}
.strip{height:7px}
}`;

const shell = (css, title, body) => `<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700;800&family=Caprasimo&display=swap" rel="stylesheet">
<style>${BASE}${css}</style></head><body><div class="f"><div class="top"><h1>${title}</h1><div class="lock">${MARK}<div class="word"><span class="v">V</span>Turn<span class="u">U</span></div></div></div>${body}<div class="strip"></div></div></body></html>`;

/* ---- 1. the comparison ---- */
const rows = [
    ['Starts with', 'Where can AI do the work?', 'What can we trust?'],
    ['Optimizes', 'Speed and capacity', 'Decision quality'],
    ['Fails when', 'Inputs are weak', 'Insight never reaches action'],
    ['Depends on', 'Human review, redesigned workflows', 'Clear ownership, shared definitions'],
];
const comparison = shell(`
.cmp{flex:1;display:grid;grid-template-columns:1fr 210px 1fr;grid-template-rows:84px repeat(4,1fr);gap:0 0;background:#fff;border:2px solid #e4e0ef;border-radius:26px;overflow:hidden}
.hd{display:flex;align-items:center;justify-content:center;font-size:36px;font-weight:800}
.hd.a{background:linear-gradient(95deg,#ff7cb0,#ffb347);color:#1d1936}
.hd.b{background:linear-gradient(95deg,#0a7388,#4338ca);color:#fff}
.hd.m{background:#1d1936}
.c{display:flex;align-items:center;justify-content:center;text-align:center;padding:10px 30px;font-size:31px;font-weight:600;line-height:1.2;border-top:2px solid #eeebf6}
.l{background:#1d1936;color:#ffc94d;font-size:25px;font-weight:700;border-top:2px solid #3a3560;padding:10px 12px}
.band{margin-top:22px;height:84px;border-radius:20px;background:linear-gradient(95deg,#0a7388,#4338ca 40%,#b3175a 78%,#c75b00);color:#fff;display:flex;align-items:center;justify-content:center;font-size:35px;font-weight:800}
.mob{display:none}
@media (max-width:600px){
.cmp,.bandw{display:none}.mob{display:block}
.card{border:1.5px solid #e4e0ef;border-radius:16px;background:#fff;overflow:hidden;margin-bottom:12px}
.card h2{font-size:21px;font-weight:800;padding:11px 16px}
.card.a h2{background:linear-gradient(95deg,#ff7cb0,#ffb347);color:#1d1936}
.card.b h2{background:linear-gradient(95deg,#0a7388,#4338ca);color:#fff}
.it{padding:9px 16px;border-top:1px solid #eeebf6}
.it small{display:block;font-size:12.5px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:#3b35b8}
.it b{font-size:17px;font-weight:600;line-height:1.3}
.band{height:auto;margin-top:0;padding:14px 16px;font-size:19px;border-radius:14px;text-align:center}
}`, 'Two starting points, one growth system',
`<div class="cmp"><div class="hd a">AI-first</div><div class="hd m"></div><div class="hd b">Data-first</div>
${rows.map(([l, a, b]) => `<div class="c">${a}</div><div class="c l">${l}</div><div class="c">${b}</div>`).join('')}</div>
<div class="mob"><div class="card a"><h2>AI-first</h2>${rows.map(([l, a]) => `<div class="it"><small>${l}</small><b>${a}</b></div>`).join('')}</div>
<div class="card b"><h2>Data-first</h2>${rows.map(([l, , b]) => `<div class="it"><small>${l}</small><b>${b}</b></div>`).join('')}</div></div>
<div class="band">Together: trusted decisions, made faster</div>`);

/* ---- 2. the GROUND framework ---- */
const stages = [
    ['G', 'Goal', 'One revenue outcome', 'a'], ['R', 'Reliable data', 'Trusted fields, clear owners', 'a'], ['O', 'Observed buyers', 'What buyers actually do', 'a'],
    ['U', 'Use AI', 'One redesigned workflow', 'b'], ['N', 'No unreviewed output', 'Approvals and data rules', 'b'], ['D', 'Decide on evidence', 'Keep, change or stop', 'b'],
];
const stage = ([k, t, d, g], cls = '') => `<div class="s ${cls}"><b class="k ${g}">${k}</b><div><strong>${t}</strong><span>${d}</span></div></div>`;
const ground = shell(`
.track{position:relative;flex:1;padding:0 96px;height:735px}
.grid{display:grid;grid-template-columns:repeat(3,1fr);grid-template-rows:1fr 104px 1fr;gap:18px 66px;height:100%}
.s{display:flex;align-items:center;gap:20px;padding:18px 22px;background:#fff;border:2px solid #e4e0ef;border-radius:22px;box-shadow:0 18px 34px -24px rgba(43,38,112,.5)}
.k{flex:none;display:grid;place-items:center;width:78px;height:78px;border-radius:50%;font-size:40px;font-weight:800;color:#fff}
.k.a{background:linear-gradient(135deg,#0a7388,#4338ca)}
.k.b{background:linear-gradient(135deg,#b3175a,#c75b00)}
.s strong{display:block;font-size:31px;font-weight:800;line-height:1.14}
.s span{display:block;margin-top:6px;font-size:26px;font-weight:500;line-height:1.22;color:#3b3560}
.mid{grid-column:1/-1;align-self:center;height:88px;border-radius:20px;background:#1d1936;color:#fff;display:flex;align-items:center;justify-content:center;font-size:33px;font-weight:800}
.mid em{font-style:normal;color:#ffc94d;margin-right:14px}
.ov{position:absolute;inset:0;width:100%;height:100%;pointer-events:none}
.mob{display:none}
@media (max-width:600px){
.track{padding:0;height:auto}.grid,.ov{display:none}.mob{display:block}
.s{gap:12px;padding:11px 13px;border-width:1.5px;border-radius:14px;box-shadow:none}
.k{width:46px;height:46px;font-size:23px}
.s strong{font-size:18px}.s span{font-size:15px;margin-top:1px}
.dn{display:block;width:2px;height:15px;margin:0 0 0 35px;background:#b8b4ee;position:relative}
.dn::after{content:"";position:absolute;left:-5px;bottom:-1px;border:6px solid transparent;border-top-color:#8f8cf0;border-bottom:0}
.mid{height:auto;margin-top:14px;padding:13px 14px;border-radius:13px;font-size:17px;text-align:center;display:block}
.mid em{display:block;margin:0 0 2px}
.re{margin-top:10px;font-size:15.5px;font-weight:700;color:#3b35b8;text-align:center}
}`, 'The GROUND framework for B2B growth',
`<div class="track"><svg class="ov" viewBox="0 0 1480 735" aria-hidden="true"><defs><marker id="ar" markerUnits="userSpaceOnUse" markerWidth="24" markerHeight="24" refX="20" refY="12" orient="auto"><path d="M0 1L22 12L0 23z" fill="#5b56c9"/></marker></defs><g fill="none" stroke="#8f8cf0" stroke-width="5" marker-end="url(#ar)"><path d="M488 149H540"/><path d="M939 149H992"/><path d="M1386 149C1486 149 1486 586 1392 586"/><path d="M992 586H939"/><path d="M540 586H488"/><path d="M94 586C-6 586 -6 149 88 149"/></g><text x="0" y="0" transform="translate(66 367) rotate(-90)" text-anchor="middle" font-family="Poppins" font-weight="700" font-size="24" fill="#3b35b8">Results reset the next goal</text></svg>
<div class="grid">${stage(stages[0])}${stage(stages[1])}${stage(stages[2])}<div class="mid"><em>At the center:</em>qualified pipeline and revenue</div>${stage(stages[5])}${stage(stages[4])}${stage(stages[3])}</div></div>
<div class="mob">${stages.map((s, i) => stage(s) + (i < 5 ? '<i class="dn"></i>' : '')).join('')}<div class="mid"><em>At the center</em>qualified pipeline and revenue</div><p class="re">Results reset the next goal</p></div>`);

/* ---- 3. the 90-day roadmap ---- */
const phases = [
    ['Days 1 to 30', 'Assess', ['Audit data and CRM', 'Map lead workflows', 'List AI already in use'], 'Goal, audit, use-case shortlist', 'Record accuracy', 'p1'],
    ['Days 31 to 60', 'Pilot', ['Fix data capture', 'Launch 1 or 2 AI tests', 'Publish a governance note'], 'Pilots and a locked baseline', 'Acceptance and error rates', 'p2'],
    ['Days 61 to 90', 'Measure and scale', ['Compare with the baseline', 'Review quality with sales', 'Keep, change or stop'], 'Test readout and next plan', 'Lead-to-opportunity rate', 'p3'],
];
const roadmap = shell(`
.road{flex:1;display:grid;grid-template-columns:repeat(3,1fr);gap:46px}
.ph{position:relative;display:flex;flex-direction:column;background:#fff;border:2px solid #e4e0ef;border-radius:24px}
.ph:not(:last-child)::after{content:"";position:absolute;right:-37px;top:52px;border:17px solid transparent;border-left:24px solid #5b56c9;border-right:0}
.ph header{position:relative;padding:18px 26px 18px;color:#fff;border-radius:22px 22px 0 0}
.p1 header{background:linear-gradient(95deg,#0a7388,#2f6dba)}
.p2 header{background:linear-gradient(95deg,#4338ca,#7a2ba8)}
.p3 header{background:linear-gradient(95deg,#b3175a,#c75b00)}
.ph header small{display:block;font-size:25px;font-weight:600}
.ph header b{display:block;font-size:35px;font-weight:800;line-height:1.14}
.bd{flex:1;display:flex;flex-direction:column;padding:18px 26px 20px;gap:12px}
.bd ul{list-style:none;flex:1;display:flex;flex-direction:column;justify-content:space-evenly}
.bd li{position:relative;padding-left:34px;font-size:29px;font-weight:600;line-height:1.2}
.bd li::before{content:"";position:absolute;left:0;top:10px;width:18px;height:18px;border-radius:50%;background:linear-gradient(135deg,#ff3d8a,#ff9100)}
.m{padding-top:10px;border-top:2px solid #eeebf6}
.m small{display:block;font-size:22px;font-weight:700;letter-spacing:.03em;text-transform:uppercase;color:#3b35b8}
.m span{display:block;font-size:26px;font-weight:600;line-height:1.2}
@media (max-width:600px){
.road{display:block}
.ph{border-width:1.5px;border-radius:15px;margin-bottom:22px}
.ph:not(:last-child)::after{right:auto;left:50%;top:auto;bottom:-20px;margin-left:-10px;border:10px solid transparent;border-top:13px solid #5b56c9;border-bottom:0}
.ph header{padding:10px 15px;border-radius:13px 13px 0 0}
.ph header small{font-size:14px}.ph header b{font-size:21px}
.bd{padding:12px 15px 13px;gap:8px}
.bd ul{display:block}
.bd li{padding-left:21px;margin-bottom:5px;font-size:16.5px}
.bd li::before{top:6px;width:11px;height:11px}
.m{padding-top:7px;border-top-width:1px}
.m small{font-size:12.5px}.m span{font-size:16px}
}`, 'A 90-day path from data to decisions',
`<div class="road">${phases.map(([d, t, l, o, k, c]) => `<section class="ph ${c}"><header><small>${d}</small><b>${t}</b></header><div class="bd"><ul>${l.map((x) => `<li>${x}</li>`).join('')}</ul><div class="m"><small>Deliverable</small><span>${o}</span></div><div class="m"><small>Track</small><span>${k}</span></div></div></section>`).join('')}</div>`);

const FIGS = [['ai-first-vs-data-first-comparison', comparison], ['ground-framework-b2b-growth', ground], ['90-day-ai-data-marketing-roadmap', roadmap]];

/* ---- Chrome over the DevTools protocol ---- */
const PORT = 9334;
const profile = join(tmpdir(), 'vt-fig-chrome');
const chrome = spawn(CHROME, ['--headless=new', `--remote-debugging-port=${PORT}`, `--user-data-dir=${profile}`, '--hide-scrollbars', 'about:blank'], { stdio: 'ignore' });
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
let target;
for (let i = 0; i < 60 && !target; i++) {
    await sleep(250);
    try { target = (await (await fetch(`http://127.0.0.1:${PORT}/json/list`)).json()).find((t) => t.type === 'page'); } catch { /* still starting */ }
}
const ws = new WebSocket(target.webSocketDebuggerUrl);
await new Promise((r) => { ws.onopen = r; });
let id = 0;
const waiting = new Map();
ws.onmessage = (e) => {
    const m = JSON.parse(e.data);
    if (m.id && waiting.has(m.id)) { waiting.get(m.id)(m.result ?? m); waiting.delete(m.id); }
};
const send = (method, params = {}) => new Promise((r) => { const n = ++id; waiting.set(n, r); ws.send(JSON.stringify({ id: n, method, params })); });

await send('Page.enable');
await send('Runtime.enable');
const frameId = (await send('Page.getFrameTree')).frameTree.frame.id;
mkdirSync(OUT, { recursive: true });

async function render(html, w, h, dpr) {
    await send('Emulation.setDeviceMetricsOverride', { width: w, height: h, deviceScaleFactor: dpr, mobile: false });
    await send('Page.setDocumentContent', { frameId, html });
    await send('Runtime.evaluate', { expression: 'document.fonts.ready.then(()=>new Promise(r=>setTimeout(r,500)))', awaitPromise: true });
}
const shot = async (w, h, scale, q) => Buffer.from((await send('Page.captureScreenshot', { format: 'webp', quality: q, optimizeForSpeed: false, clip: { x: 0, y: 0, width: w, height: h, scale } })).data, 'base64');

let first = true;
for (const [name, html] of FIGS) {
    await render(html, 1600, 900, 1);
    if (first) { await sleep(1800); first = false; }
    const over = (await send('Runtime.evaluate', { expression: "(()=>{const f=document.querySelector('.f');return f.scrollHeight-f.clientHeight})()", returnByValue: true })).result.value;
    writeFileSync(join(OUT, `${name}-1600.webp`), await shot(1600, 900, 1, 86));
    writeFileSync(join(OUT, `${name}-820.webp`), await shot(1600, 900, 820 / 1600, 82));
    await render(html, 400, 800, 2);
    const h = (await send('Runtime.evaluate', { expression: "document.querySelector('.f').getBoundingClientRect().height", returnByValue: true })).result.value;
    const H = Math.ceil(h);
    await send('Emulation.setDeviceMetricsOverride', { width: 400, height: H, deviceScaleFactor: 2, mobile: false });
    await sleep(200);
    writeFileSync(join(OUT, `${name}-mobile.webp`), await shot(400, H, 2, 70));
    console.log(name, 'desktop overflow', over, 'mobile 800x' + H * 2);
}
ws.close();
chrome.kill();
await sleep(400);
try { rmSync(profile, { recursive: true, force: true }); } catch { /* Windows may hold the profile briefly */ }
process.exit(0);
