// Usage: node cover.cjs spec.json out.jpg
// spec: {w,h,quality,font:{family, files:{400:path,700:path,900:path}}, c:{bg,text,muted,accent,card,cardText,cardSub,eyebrow}, eyebrow, title, sub, items:[[t,s]], footer?}
const { chromium } = require('playwright');
const fs = require('fs');
(async () => {
  const spec = JSON.parse(fs.readFileSync(process.argv[2], 'utf8'));
  const out = process.argv[3];
  const W = spec.w || 1200, H = spec.h || 675;
  const ff = Object.entries(spec.font.files).map(([wt, p]) => `@font-face{font-family:'${spec.font.family}';font-weight:${wt};src:url(data:font/woff2;base64,${fs.readFileSync(p).toString('base64')}) format('woff2');}`).join('\n');
  const c = spec.c;
  const esc = s => String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;');
  const items = (spec.items||[]).map((it,i) => `<div class="it"><b>${esc(it[0])}</b>${it[1]?`<span>${esc(it[1])}</span>`:''}</div>`).join('');
  const html = `<!doctype html><html><head><meta charset="utf-8"><style>${ff}
  *{box-sizing:border-box;margin:0;padding:0}
  body{width:${W}px;height:${H}px;background:${c.bg};font-family:'${spec.font.family}',sans-serif;color:${c.text};overflow:hidden;position:relative}
  ${spec.extraCss||''}
  .wrap{position:absolute;inset:0;display:flex;align-items:center;padding:0 ${Math.round(W*0.06)}px;gap:${Math.round(W*0.045)}px}
  .l{flex:1.12}
  .r{flex:1;display:flex;flex-direction:column;gap:${Math.round(H*0.018)}px}
  .eb{font-size:${Math.round(H*0.027)}px;letter-spacing:.2em;text-transform:uppercase;font-weight:700;color:${c.eyebrow||c.accent};margin-bottom:${Math.round(H*0.03)}px}
  h1{font-size:${spec.titleSize||Math.round(H*0.083)}px;line-height:1.07;font-weight:${spec.titleWeight||900};letter-spacing:-.01em;color:${c.text}}
  .sub{margin-top:${Math.round(H*0.035)}px;font-size:${Math.round(H*0.034)}px;line-height:1.42;color:${c.muted};font-weight:400}
  .it{background:${c.card};border-left:${Math.round(W*0.0055)}px solid ${c.accent};border-radius:${Math.round(H*0.012)}px;padding:${Math.round(H*0.019)}px ${Math.round(W*0.017)}px}
  .it b{display:block;font-size:${Math.round(H*0.03)}px;font-weight:700;color:${c.cardText||c.text};line-height:1.25}
  .it span{display:block;margin-top:4px;font-size:${Math.round(H*0.024)}px;color:${c.cardSub||c.muted};line-height:1.3}
  .ft{position:absolute;left:${Math.round(W*0.06)}px;bottom:${Math.round(H*0.06)}px;font-size:${Math.round(H*0.024)}px;color:${c.muted};letter-spacing:.04em}
  </style></head><body>${spec.decor||''}<div class="wrap"><div class="l"><div class="eb">${esc(spec.eyebrow)}</div><h1>${esc(spec.title)}</h1>${spec.sub?`<div class="sub">${esc(spec.sub)}</div>`:''}</div>${items?`<div class="r">${items}</div>`:''}</div>${spec.footer?`<div class="ft">${esc(spec.footer)}</div>`:''}</body></html>`;
  fs.writeFileSync(__dirname + '/../last-cover.html', html);
  const b = await chromium.launch(); const p = await b.newPage({ viewport: { width: W, height: H }, deviceScaleFactor: 1 });
  await p.setContent(html, { waitUntil: 'load' }); await p.evaluate(() => document.fonts.ready);
  const opts = { path: out, clip: { x: 0, y: 0, width: W, height: H } };
  if (/\.jpe?g$/.test(out)) { opts.type = 'jpeg'; opts.quality = spec.quality || 88; }
  await p.screenshot(opts); await b.close();
  console.log(out, fs.statSync(out).size, 'bytes');
})();
