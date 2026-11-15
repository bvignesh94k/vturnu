const { chromium } = require('playwright');
const [home, listUrl, re] = process.argv.slice(2);
(async () => {
const b = await chromium.launch(); const ctx = await b.newContext({ userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36', viewport:{width:1280,height:900} });
let page = await ctx.newPage();
await page.goto(home, { waitUntil: 'networkidle', timeout: 60000 }).catch(()=>{}); await page.waitForTimeout(2000);
const brand = await page.evaluate(() => {
  const cnt = (m,k,w)=>{m[k]=(m[k]||0)+w};
  const bg={}, fg={}, ff={}, hf={};
  for (const el of document.querySelectorAll('body *')) { const r=el.getBoundingClientRect(); if (r.width*r.height<300||r.top>2500) continue; const cs=getComputedStyle(el);
    if (cs.backgroundColor && !/rgba\(0, 0, 0, 0\)|rgb\(255, 255, 255\)/.test(cs.backgroundColor)) cnt(bg,cs.backgroundColor,Math.round(r.width*r.height/1000));
    if (el.tagName==='A') cnt(fg,cs.color,1);
    if (/^H[1-3]$/.test(el.tagName)) cnt(hf,cs.fontFamily+' | '+cs.fontWeight+' | '+cs.color,1);
    if (el.tagName==='P') cnt(ff,cs.fontFamily,1); }
  const top=(m,n)=>Object.entries(m).sort((a,b)=>b[1]-a[1]).slice(0,n);
  const logo=Array.from(document.querySelectorAll('img,svg')).filter(i=>/logo/i.test((i.getAttribute('src')||'')+(i.getAttribute('alt')||'')+(i.getAttribute('class')||''))).slice(0,2).map(i=>(i.getAttribute('src')||'svg')+' '+(i.getAttribute('alt')||''));
  const meta=document.querySelector('meta[name="theme-color"]'); return {bg:top(bg,8), links:top(fg,4), headings:top(hf,4), body:top(ff,2), logo, theme: meta&&meta.content};
});
console.log('BRAND', JSON.stringify(brand));
await page.goto(listUrl, { waitUntil: 'domcontentloaded', timeout: 60000 }).catch(()=>{}); await page.waitForTimeout(2500);
const arts = await page.evaluate((re) => { const rx=new RegExp(re); const seen=new Set(); return Array.from(document.querySelectorAll('a')).map(a=>({t:a.innerText.trim().replace(/\s+/g,' '),h:a.href})).filter(l=>rx.test(l.h)&&l.t.length>25&&!seen.has(l.h)&&seen.add(l.h)).slice(0,14); }, re);
console.log('RECENT', JSON.stringify(arts));
for (const a of arts.slice(0,2)) {
  await page.goto(a.h, { waitUntil: 'domcontentloaded', timeout: 60000 }).catch(()=>{}); await page.waitForTimeout(2500);
  const info = await page.evaluate(() => {
    const art=document.querySelector('article')||document.querySelector('.entry-content')||document.querySelector('main')||document.body;
    const text=art.innerText; const words=text.split(/\s+/).length;
    const h2=Array.from(art.querySelectorAll('h2,h3')).map(h=>h.tagName+': '+h.innerText.trim()).slice(0,12);
    const host=location.hostname.replace('www.','');
    const ext=Array.from(art.querySelectorAll('a')).filter(x=>x.href.startsWith('http')&&!x.href.includes(host)).map(x=>({t:x.innerText.trim().slice(0,40),h:x.href.slice(0,90),rel:x.getAttribute('rel')})).slice(0,10);
    const og=(document.querySelector('meta[property="og:image"]')||{}).content; const ow=(document.querySelector('meta[property="og:image:width"]')||{}).content; const oh=(document.querySelector('meta[property="og:image:height"]')||{}).content;
    const date=(document.querySelector('meta[property="article:published_time"]')||{}).content;
    return {words,h2,ext,og,ow,oh,date,tail:text.slice(-700).replace(/\s+/g,' ')};
  });
  console.log('ARTICLE', a.h, JSON.stringify(info));
}
await b.close();
})();
