/**
 * Crawls the sitemap and checks every page: status, title, description, headings,
 * canonical, structured data, FAQ match, dashes, links and client follow rules.
 *
 *   node bin/audit-site.mjs https://vturnu.com
 */
const base = process.argv[2] || 'http://127.0.0.1:8100';
const UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/130 Safari/537.36';
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
const dec = (s) => s.replace(/&amp;/g, '&').replace(/&#0?39;/g, "'").replace(/&quot;/g, '"');
const CLIENTS = /saiimpression|firstchoiceautopart|floweazy|cafy\.careers/;

async function get(u, redirect = 'manual') {
    for (let i = 0; i < 4; i++) {
        try {
            const r = await fetch(base + u, { redirect, headers: { 'User-Agent': UA, Connection: 'close' } });
            return { status: r.status, text: await r.text() };
        } catch { await sleep(1500); }
    }
    return { status: 0, text: '' };
}

const problems = {};
const add = (k, v) => { (problems[k] ||= []).push(v); };
const xml = (await get('/sitemap.xml')).text;
const pages = [...xml.matchAll(/<loc>([^<]+)<\/loc>/g)].map((m) => m[1].replace('https://vturnu.com', ''));
const links = new Map(), titles = {}, metas = {};
let faqPages = 0;

for (const u of pages) {
    const { status, text: h } = await get(u);
    if (status !== 200) { add('status', `${u} ${status}`); continue; }
    if (/<b>(Fatal|Warning|Notice|Deprecated)/i.test(h)) add('php', u);
    const t = dec((h.match(/<title>([^<]*)<\/title>/) || [])[1] || '');
    const d = dec((h.match(/<meta name="description" content="([^"]*)"/) || [])[1] || '');
    if (t.length > 63) add('titleLong', `${u} ${t.length}`);
    if (t.length < 50) add('titleShort', `${u} ${t.length}`);
    if (u !== '/' && /vturnu/i.test(t)) add('brandInTitle', u);
    if (d.length > 160 || d.length < 140) add('metaLen', `${u} ${d.length}`);
    (titles[t] ||= []).push(u); (metas[d] ||= []).push(u);
    if ((h.match(/<h1[\s>]/g) || []).length !== 1) add('h1', u);
    const canon = (h.match(/rel="canonical" href="([^"]*)"/) || [])[1] || '';
    if (canon !== 'https://vturnu.com' + u) add('canonical', `${u} ${canon}`);
    if (/content="noindex/.test(h)) add('noindexInSitemap', u);
    for (const m of h.matchAll(/<script type="application\/ld\+json">([\s\S]*?)<\/script>/g)) {
        try { JSON.parse(m[1]); } catch { add('jsonld', u); }
    }
    if (/"@type":"FAQPage"/.test(h)) {
        faqPages++;
        const n = (h.match(/"@type":"Question"/g) || []).length;
        const shown = (h.match(/class="faq2"|class="hp3-faq-i"/g) || []).length;
        if (n > 8) add('faqMany', `${u} ${n}`);
        if (shown !== n) add('faqMismatch', `${u} schema ${n} shown ${shown}`);
    }
    const visible = h.replace(/<script[\s\S]*?<\/script>/g, '').replace(/<style[\s\S]*?<\/style>/g, '');
    if (/[—–]/.test(visible)) add('dash', u);
    if (/vturnai/i.test(h)) add('vturnai', u);
    if (/<img(?![^>]*\balt=)[^>]*>/.test(visible)) add('imgNoAlt', u);
    for (const m of h.matchAll(/<a\b[^>]*href="([^"#]*)(#[^"]*)?"[^>]*>/g)) {
        const href = m[1];
        if (!href) continue;
        if (href.startsWith('/') && !href.startsWith('//')) links.set(href.split('?')[0], u);
        else if (/^https?:/.test(href) && !/vturnu\.com/.test(href) && !/wa\.me/.test(href)) {
            const nf = /rel="[^"]*nofollow/.test(m[0]), client = CLIENTS.test(href);
            if (client && nf) add('clientNofollow', `${u} ${href}`);
            if (!client && !nf) add('extFollow', `${u} ${href}`);
        }
    }
    await sleep(40);
}
for (const [l, from] of links) {
    if (/^\/(admin|presentations)/.test(l)) continue;
    const r = await get(l);
    if (r.status >= 400 || r.status === 0) add('brokenLink', `${l} ${r.status} from ${from}`);
    else if (r.status >= 300) add('redirectLink', `${l} ${r.status} from ${from}`);
    await sleep(20);
}
for (const [t, us] of Object.entries(titles)) if (new Set(us).size > 1) add('dupTitle', `${t.slice(0, 50)} ${us.slice(0, 3).join(' ')}`);
for (const [t, us] of Object.entries(metas)) if (new Set(us).size > 1) add('dupMeta', `${t.slice(0, 50)} x${us.length}`);

console.log(JSON.stringify({ pages: pages.length, faqPages, internalLinks: links.size }));
const keys = Object.keys(problems);
if (!keys.length) console.log('No problems found.');
for (const k of keys) { console.log('##', k, problems[k].length); problems[k].slice(0, 25).forEach((x) => console.log('  ', x)); }
