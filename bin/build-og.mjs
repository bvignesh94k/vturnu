/**
 * Builds every banner and social share image.
 *
 *   php bin/og-data.php > og-data.json
 *   node bin/build-og.mjs og-data.json
 *
 * One fixed brand layout: the exact logo, a kicker and the headline on the left,
 * a concept illustration on the right. Only the words and the concept change,
 * so every share looks like VTurnU. No stock photos. Uses Chrome through the
 * DevTools protocol, so it needs nothing from npm.
 *
 * Output: assets/img/og/<name>.jpg (1200x630, for og:image) and, for blog posts,
 * assets/img/blog/<slug>.webp plus <slug>-sm.webp for listing cards.
 */
import { spawn } from 'node:child_process';
import { mkdirSync, readFileSync, writeFileSync, rmSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { tmpdir } from 'node:os';
import { fileURLToPath } from 'node:url';

const ROOT = join(dirname(fileURLToPath(import.meta.url)), '..');
const CHROME = process.env.CHROME || 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const pages = JSON.parse(readFileSync(process.argv[2], 'utf8'));
const only = process.argv[3] || '';

const esc = (s) => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

/* ------------------------------ concept art, drawn on a 480x500 stage ------ */

const DEFS = `<defs>
<linearGradient id="gV" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#00b8d9"/><stop offset="1" stop-color="#4338ca"/></linearGradient>
<linearGradient id="gU" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ff3d8a"/><stop offset="1" stop-color="#ff9100"/></linearGradient>
<linearGradient id="gB" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#00b8d9"/><stop offset=".42" stop-color="#4338ca"/><stop offset=".72" stop-color="#ff3d8a"/><stop offset="1" stop-color="#ff9100"/></linearGradient>
<linearGradient id="gL" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ffffff"/><stop offset="1" stop-color="#ecebfb"/></linearGradient>
<filter id="sh" x="-30%" y="-30%" width="160%" height="170%"><feDropShadow dx="0" dy="14" stdDeviation="14" flood-color="#2b2670" flood-opacity=".2"/></filter>
</defs>`;

/* An isometric box: x,y on the ground plane, z up. */
const iso = (x, y, z = 0) => [240 + (x - y) * 0.866, 330 + (x + y) * 0.5 - z];
const pts = (a) => a.map((p) => p.map((n) => n.toFixed(1)).join(',')).join(' ');
function box(x, y, w, d, h, top, left, right, z = 0) {
    const A = iso(x, y, z + h), B = iso(x + w, y, z + h), C = iso(x + w, y + d, z + h), D = iso(x, y + d, z + h);
    const E = iso(x + w, y, z), F = iso(x + w, y + d, z), G = iso(x, y + d, z);
    return `<polygon points="${pts([D, C, F, G])}" fill="${left}"/><polygon points="${pts([B, C, F, E])}" fill="${right}"/><polygon points="${pts([A, B, C, D])}" fill="${top}"/>`;
}
const PAL = [['#7be8f7', '#00b8d9', '#0a8fc4'], ['#8f8cf0', '#5b56c9', '#4338ca'], ['#ff8db8', '#ff3d8a', '#e5267a'], ['#ffc777', '#ff9100', '#ef7a00']];
const pal = (i) => PAL[i % PAL.length];
const base = (s = 130) => box(-s, -s, s * 2, s * 2, 14, '#ffffff', '#dcdaf5', '#c9c6ef');
const sparkle = (x, y, r, f = 'url(#gU)') => `<path d="M${x} ${y - r}Q${x} ${y} ${x + r} ${y}Q${x} ${y} ${x} ${y + r}Q${x} ${y} ${x - r} ${y}Q${x} ${y} ${x} ${y - r}Z" fill="${f}"/>`;
const panel = (x, y, w, h, r = 18, f = 'url(#gL)') => `<rect x="${x}" y="${y}" width="${w}" height="${h}" rx="${r}" fill="${f}" stroke="#e1dff6" stroke-width="1.5" filter="url(#sh)"/>`;
const bar = (x, y, w, f = '#d9d6f4', h = 10) => `<rect x="${x}" y="${y}" width="${w}" height="${h}" rx="${h / 2}" fill="${f}"/>`;
const tick = (x, y, s = 1, c = '#fff') => `<path d="M${x - 9 * s} ${y}l${6 * s} ${7 * s} ${13 * s}-${15 * s}" fill="none" stroke="${c}" stroke-width="${5 * s}" stroke-linecap="round" stroke-linejoin="round"/>`;
const cross = (x, y, s = 1, c = '#fff') => `<path d="M${x - 8 * s} ${y - 8 * s}l${16 * s} ${16 * s}M${x + 8 * s} ${y - 8 * s}l-${16 * s} ${16 * s}" stroke="${c}" stroke-width="${5 * s}" stroke-linecap="round"/>`;

const ART = {
    rank() {
        let s = base();
        [[-95, 40, 0], [-40, 80, 1], [15, 125, 2], [70, 180, 3]].forEach(([x, h, i], n) => {
            const c = pal(i); s += box(x, -30 - n * 4, 42, 60, h, c[0], c[1], c[2], 14);
        });
        s += `<g filter="url(#sh)"><circle cx="360" cy="120" r="56" fill="#fff" stroke="url(#gV)" stroke-width="13"/><path d="M398 162l44 46" stroke="url(#gU)" stroke-width="16" stroke-linecap="round"/></g>`;
        return s + sparkle(105, 95, 18);
    },
    answer() {
        let s = panel(36, 70, 320, 150, 26) + `<circle cx="84" cy="118" r="20" fill="url(#gV)"/>` + bar(118, 104, 200) + bar(118, 128, 170) + bar(118, 152, 120, '#ffc0d8') + bar(60, 178, 250, '#eceaf9');
        s += panel(130, 250, 310, 136, 26, 'url(#gB)') + bar(162, 284, 220, 'rgba(255,255,255,.9)') + bar(162, 310, 170, 'rgba(255,255,255,.7)') + bar(162, 336, 110, 'rgba(255,255,255,.55)');
        return s + sparkle(410, 120, 34) + sparkle(380, 62, 16, 'url(#gV)') + sparkle(70, 300, 22, 'url(#gV)');
    },
    funnel() {
        let s = '';
        [[150, 66, 0], [116, 110, 1], [84, 148, 2], [54, 180, 3]].forEach(([rx, y, i]) => {
            const c = pal(i);
            s = `<ellipse cx="240" cy="${y + 40}" rx="${rx}" ry="${rx * 0.34}" fill="${c[2]}"/><rect x="${240 - rx}" y="${y + 22}" width="${rx * 2}" height="18" fill="${c[2]}"/><ellipse cx="240" cy="${y + 22}" rx="${rx}" ry="${rx * 0.34}" fill="${c[0]}"/>` + s;
        });
        s = `<g filter="url(#sh)">${s}</g>`;
        [[170, 60], [240, 40], [310, 62], [205, 20], [275, 18]].forEach(([x, y], i) => { s += `<circle cx="${x}" cy="${y}" r="10" fill="${pal(i)[1]}"/>`; });
        s += `<g filter="url(#sh)"><circle cx="240" cy="330" r="52" fill="url(#gB)"/>${tick(240, 332, 1.6)}</g>`;
        return s + bar(120, 420, 240, '#e2e0f6', 12) + bar(170, 446, 140, '#efeefb', 12);
    },
    globe() {
        let s = `<g filter="url(#sh)"><circle cx="240" cy="230" r="165" fill="url(#gL)" stroke="url(#gV)" stroke-width="5"/></g>`;
        s += `<g fill="none" stroke="#b8b4ee" stroke-width="2"><ellipse cx="240" cy="230" rx="165" ry="58"/><ellipse cx="240" cy="230" rx="165" ry="118"/><ellipse cx="240" cy="230" rx="58" ry="165"/><ellipse cx="240" cy="230" rx="118" ry="165"/><path d="M75 230h330M240 65v330"/></g>`;
        [[170, 150, 0], [312, 182, 2], [276, 300, 3], [150, 280, 1]].forEach(([x, y, i]) => {
            const c = pal(i); s += `<g filter="url(#sh)"><circle cx="${x}" cy="${y}" r="22" fill="${c[1]}"/><circle cx="${x}" cy="${y}" r="8" fill="#fff"/></g>`;
        });
        s += `<path d="M170 150Q240 90 312 182M312 182Q330 260 276 300M150 280Q190 340 276 300" fill="none" stroke="url(#gB)" stroke-width="4" stroke-dasharray="8 8"/>`;
        return s;
    },
    compare() {
        let s = base(150);
        s += box(-115, -40, 80, 80, 90, PAL[0][0], PAL[0][1], PAL[0][2], 14) + box(35, -40, 80, 80, 150, PAL[2][0], PAL[2][1], PAL[2][2], 14);
        s += `<g filter="url(#sh)"><circle cx="240" cy="95" r="44" fill="url(#gB)"/><text x="240" y="108" text-anchor="middle" font-family="Poppins" font-weight="800" font-size="34" fill="#fff">VS</text></g>`;
        return s + sparkle(420, 150, 22) + sparkle(60, 170, 18, 'url(#gV)');
    },
    price() {
        let s = `<g filter="url(#sh)"><path d="M120 110h170l110 110a26 26 0 0 1 0 37L290 367a26 26 0 0 1-37 0L120 240a28 28 0 0 1-8-20V118a8 8 0 0 1 8-8z" fill="url(#gB)"/><circle cx="170" cy="160" r="20" fill="#fff"/></g>`;
        s += `<text x="270" y="262" text-anchor="middle" font-family="Poppins" font-weight="800" font-size="84" fill="#fff" transform="rotate(44 270 240)">%</text>`;
        [[0, 96], [1, 74], [2, 52]].forEach(([i, h]) => { s += `<ellipse cx="96" cy="${430 - i * 22}" rx="58" ry="20" fill="${pal(3 - i)[1]}" stroke="#fff" stroke-width="3"/>`; });
        return s + sparkle(410, 90, 26);
    },
    browser() {
        let s = panel(40, 70, 400, 300, 22);
        s += `<path d="M40 112v-20a22 22 0 0 1 22-22h356a22 22 0 0 1 22 22v20z" fill="url(#gV)"/><circle cx="70" cy="91" r="7" fill="#fff"/><circle cx="94" cy="91" r="7" fill="#fff" opacity=".7"/><circle cx="118" cy="91" r="7" fill="#fff" opacity=".45"/>`;
        s += `<rect x="66" y="134" width="170" height="120" rx="12" fill="url(#gU)"/>` + bar(256, 140, 160) + bar(256, 166, 130) + bar(256, 192, 150) + `<rect x="256" y="222" width="104" height="32" rx="16" fill="url(#gV)"/>`;
        s += bar(66, 280, 348, '#ecebfa', 14) + bar(66, 310, 280, '#ecebfa', 14);
        s += `<g filter="url(#sh)"><circle cx="388" cy="372" r="62" fill="#fff"/><path d="M340 392a50 50 0 1 1 96 0" fill="none" stroke="url(#gB)" stroke-width="13" stroke-linecap="round"/><path d="M388 382l26-34" stroke="#1d1936" stroke-width="7" stroke-linecap="round"/><circle cx="388" cy="384" r="9" fill="#1d1936"/></g>`;
        return s;
    },
    map() {
        let s = base(150);
        for (let i = 0; i < 4; i++) { for (let j = 0; j < 4; j++) { if ((i + j) % 2 === 0) { const c = pal(i + j); s += box(-140 + i * 72, -140 + j * 72, 62, 62, 10 + ((i * 3 + j * 5) % 3) * 16, c[0], c[1], c[2], 14); } } }
        s += `<g filter="url(#sh)"><path d="M240 60c-52 0-92 40-92 92 0 70 92 168 92 168s92-98 92-168c0-52-40-92-92-92z" fill="url(#gU)"/><circle cx="240" cy="150" r="36" fill="#fff"/></g>`;
        return s;
    },
    mail() {
        let s = `<g filter="url(#sh)"><rect x="60" y="130" width="360" height="240" rx="22" fill="url(#gL)" stroke="#e1dff6" stroke-width="2"/><path d="M60 150l164 124a26 26 0 0 0 32 0l164-124" fill="url(#gB)" opacity=".92"/></g>`;
        s += `<g filter="url(#sh)"><circle cx="396" cy="150" r="40" fill="url(#gU)"/><text x="396" y="166" text-anchor="middle" font-family="Poppins" font-weight="800" font-size="42" fill="#fff">3</text></g>`;
        return s + sparkle(90, 90, 24, 'url(#gV)');
    },
    shield() {
        let s = `<g filter="url(#sh)"><path d="M240 50l150 52v120c0 104-66 176-150 212-84-36-150-108-150-212V102z" fill="url(#gB)"/></g>`;
        s += tick(240, 240, 4.4);
        return s + sparkle(420, 90, 22) + sparkle(50, 330, 18, 'url(#gV)');
    },
    modules() {
        let s = base(150);
        [[0, 0], [1, 1], [2, 2], [3, 3]].forEach(([i], n) => {
            const c = pal(i); s += box(-90, -90, 180, 180, 40, c[0], c[1], c[2], 18 + n * 58);
        });
        s += `<g filter="url(#sh)"><circle cx="96" cy="130" r="30" fill="#fff"/><circle cx="96" cy="130" r="12" fill="url(#gU)"/><circle cx="392" cy="150" r="30" fill="#fff"/><circle cx="392" cy="150" r="12" fill="url(#gV)"/></g>`;
        return s + `<path d="M126 130h46M362 150h-46" stroke="#b8b4ee" stroke-width="4" stroke-dasharray="6 6"/>`;
    },
    people() {
        let s = base(150);
        const av = (x, y, r, i) => {
            const c = pal(i);
            return `<g filter="url(#sh)"><circle cx="${x}" cy="${y}" r="${r}" fill="${c[1]}"/><circle cx="${x}" cy="${y - r * .24}" r="${r * .32}" fill="#fff"/><path d="M${x - r * .55} ${y + r * .6}a${r * .55} ${r * .46} 0 0 1 ${r * 1.1} 0z" fill="#fff"/></g>`;
        };
        s += av(240, 150, 76, 1) + av(116, 230, 54, 0) + av(364, 230, 54, 2);
        s += `<g filter="url(#sh)"><circle cx="330" cy="86" r="30" fill="url(#gU)"/>${tick(330, 88, 1.1)}</g>`;
        return s;
    },
    cart() {
        let s = `<g filter="url(#sh)"><path d="M70 110h54l44 200h190l40-150H150" fill="none" stroke="url(#gB)" stroke-width="22" stroke-linecap="round" stroke-linejoin="round"/><circle cx="196" cy="366" r="28" fill="url(#gV)"/><circle cx="330" cy="366" r="28" fill="url(#gU)"/></g>`;
        s += `<rect x="186" y="188" width="60" height="60" rx="10" fill="${PAL[0][1]}"/><rect x="258" y="170" width="70" height="78" rx="10" fill="${PAL[2][1]}"/>`;
        return s + sparkle(410, 90, 26);
    },
    dashboard() {
        let s = panel(36, 66, 408, 310, 24);
        [[64, 'url(#gV)'], [192, 'url(#gU)'], [320, 'url(#gB)']].forEach(([x, f]) => { s += `<rect x="${x}" y="92" width="96" height="66" rx="12" fill="${f}"/>`; });
        s += `<path d="M66 340l70-54 62 26 74-78 62 30 74-74" fill="none" stroke="url(#gB)" stroke-width="9" stroke-linecap="round" stroke-linejoin="round"/>`;
        [[136, 286], [272, 234], [408, 190]].forEach(([x, y]) => { s += `<circle cx="${x}" cy="${y}" r="10" fill="#fff" stroke="#4338ca" stroke-width="5"/>`; });
        return s + bar(66, 396, 200, '#e2e0f6', 12);
    },
    book() {
        let s = base(150);
        [[0, 64], [1, 58], [2, 66], [3, 54]].forEach(([i, w], n) => {
            const c = pal(i); s += box(-120 + n * 8, -90, 240 - n * 16, 180, 34, c[0], '#fff', c[2], 16 + n * 36);
        });
        s += `<g filter="url(#sh)"><circle cx="378" cy="120" r="46" fill="url(#gU)"/>${tick(378, 122, 1.7)}</g>`;
        return s + sparkle(92, 130, 22, 'url(#gV)');
    },
    target() {
        let s = '<g filter="url(#sh)">';
        [[170, '#ffffff'], [132, '#5b56c9'], [94, '#ffffff'], [56, '#ff3d8a']].forEach(([r, f]) => { s += `<circle cx="228" cy="246" r="${r}" fill="${f}"/>`; });
        s += `</g><path d="M228 246L420 70" stroke="#1d1936" stroke-width="9" stroke-linecap="round"/><path d="M396 60l30 4 4 30 34-34-30-4-4-30z" fill="url(#gU)"/>`;
        return s;
    },
    filter() {
        let s = `<g filter="url(#sh)"><path d="M70 90h340a14 14 0 0 1 11 23L290 262v110a14 14 0 0 1-7 12l-68 38a14 14 0 0 1-21-12V262L59 113a14 14 0 0 1 11-23z" fill="url(#gB)"/></g>`;
        [[132, 50, 2], [238, 34, 0], [344, 52, 3]].forEach(([x, y, i]) => { s += `<circle cx="${x}" cy="${y}" r="22" fill="${pal(i)[1]}"/>`; });
        s += `<g filter="url(#sh)"><circle cx="390" cy="360" r="42" fill="#fff"/><circle cx="390" cy="360" r="33" fill="#e5267a"/>${cross(390, 360, 1.1)}</g>`;
        s += `<g filter="url(#sh)"><circle cx="110" cy="360" r="42" fill="#fff"/><circle cx="110" cy="360" r="33" fill="#0a8fc4"/>${tick(110, 362, 1.2)}</g>`;
        return s;
    },
    quote() {
        let s = panel(92, 46, 296, 390, 18);
        s += `<rect x="92" y="46" width="296" height="74" rx="18" fill="url(#gV)"/>` + bar(120, 74, 150, 'rgba(255,255,255,.9)', 14);
        [150, 190, 230, 270].forEach((y, i) => { s += `<rect x="120" y="${y}" width="24" height="24" rx="6" fill="${pal(i)[1]}"/>` + bar(156, y + 7, 130) + bar(306, y + 7, 54, '#c9c6ef'); });
        s += `<rect x="120" y="326" width="240" height="2" fill="#e1dff6"/>` + bar(120, 346, 90, '#1d1936', 14) + `<rect x="250" y="340" width="110" height="28" rx="14" fill="url(#gU)"/>`;
        s += `<g filter="url(#sh)"><circle cx="400" cy="396" r="46" fill="url(#gB)"/>${tick(400, 398, 1.7)}</g>`;
        return s;
    },
    orbit() {
        let s = `<g fill="none" stroke-width="4"><ellipse cx="240" cy="240" rx="205" ry="82" stroke="#00b8d9" transform="rotate(-24 240 240)"/><ellipse cx="240" cy="240" rx="190" ry="72" stroke="#ff3d8a" transform="rotate(32 240 240)"/><ellipse cx="240" cy="240" rx="168" ry="60" stroke="#5b56c9" transform="rotate(90 240 240)"/></g>`;
        s += `<g filter="url(#sh)"><circle cx="240" cy="240" r="72" fill="url(#gB)"/></g>`;
        [[62, 322, 0], [430, 160, 2], [240, 66, 3], [242, 414, 1], [90, 136, 3], [392, 340, 0]].forEach(([x, y, i]) => { s += `<g filter="url(#sh)"><circle cx="${x}" cy="${y}" r="22" fill="${pal(i)[1]}"/><circle cx="${x}" cy="${y}" r="8" fill="#fff"/></g>`; });
        return s;
    },
    brand() {
        let s = `<g filter="url(#sh)"><circle cx="196" cy="180" r="116" fill="#00b8d9" opacity=".9"/><circle cx="302" cy="180" r="116" fill="#ff3d8a" opacity=".82"/><circle cx="248" cy="282" r="116" fill="#ff9100" opacity=".82"/></g>`;
        return s + sparkle(420, 80, 28, 'url(#gV)') + sparkle(60, 340, 20);
    },
    aidata() {
        /* A data stack and an AI chip feeding one growth line. */
        let s = `<path d="M122 300C122 196 200 170 244 150M362 268C362 190 288 170 252 150" fill="none" stroke="#b8b4ee" stroke-width="5" stroke-dasharray="9 9"/>`;
        s += '<g filter="url(#sh)">';
        [0, 1, 2].forEach((i) => {
            const y = 356 - i * 46;
            s += `<path d="M42 ${y}v34a80 26 0 0 0 160 0v-34z" fill="${PAL[i % 2 ? 1 : 0][2]}"/><ellipse cx="122" cy="${y}" rx="80" ry="26" fill="${PAL[i % 2 ? 1 : 0][i === 2 ? 0 : 1]}"/>`;
        });
        s += '</g>';
        s += `<g filter="url(#sh)"><rect x="288" y="252" width="150" height="150" rx="30" fill="url(#gU)"/><text x="363" y="345" text-anchor="middle" font-family="Poppins" font-weight="800" font-size="62" fill="#fff">AI</text></g>`;
        [278, 327, 376].forEach((y) => { s += `<path d="M276 ${y}h12M438 ${y}h12` + `" stroke="#ff7a3d" stroke-width="7" stroke-linecap="round"/>`; });
        [314, 363, 412].forEach((x) => { s += `<path d="M${x} 240v12M${x} 402v12" stroke="#ff7a3d" stroke-width="7" stroke-linecap="round"/>`; });
        s += `<g filter="url(#sh)"><circle cx="248" cy="104" r="74" fill="url(#gB)"/><path d="M206 134l32-34 20 18 36-40" fill="none" stroke="#fff" stroke-width="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M270 76h26v26" fill="none" stroke="#fff" stroke-width="10" stroke-linecap="round" stroke-linejoin="round"/></g>`;
        return s + sparkle(420, 90, 22) + sparkle(70, 150, 18, 'url(#gV)');
    },
    social() {
        let s = panel(120, 40, 240, 400, 34);
        s += `<rect x="142" y="86" width="196" height="150" rx="16" fill="url(#gB)"/>` + bar(142, 254, 170) + bar(142, 278, 130);
        s += `<circle cx="168" cy="330" r="20" fill="#ffd9e6"/><path d="M168 340s-13-8-13-17a7 7 0 0 1 13-3 7 7 0 0 1 13 3c0 9-13 17-13 17z" fill="#ff3d8a"/>`;
        s += `<g filter="url(#sh)"><rect x="310" y="130" width="130" height="70" rx="22" fill="url(#gU)"/>${bar(332, 158, 86, 'rgba(255,255,255,.85)', 12)}</g><g filter="url(#sh)"><rect x="40" y="250" width="120" height="66" rx="22" fill="url(#gV)"/>${bar(62, 276, 76, 'rgba(255,255,255,.85)', 12)}</g>`;
        return s;
    },
};

/* Concept per blog post, chosen for what the article is about. */
const BLOG_ART = {
    'ai-first-vs-data-first-marketing': 'aidata', 'how-to-stop-junk-leads-on-your-website': 'filter', 'seo-vs-aeo-vs-geo-explained-for-business-leaders': 'compare', 'how-to-measure-ai-search-visibility': 'dashboard',
    'what-is-sap-cpq-and-when-do-you-need-it': 'quote', 'odoo-vs-erpnext-which-erp-fits-your-business': 'modules', 'salesforce-implementation-checklist-for-growing-teams': 'orbit',
    'it-staffing-models-contract-vs-direct-hire': 'people', 'account-based-marketing-for-mid-market-companies': 'target', 'marketing-attribution-for-long-sales-cycles': 'dashboard',
    'linkedin-ads-vs-google-ads-for-lead-generation': 'compare', 'affordable-seo-service-providers': 'price', 'what-is-answer-engine-optimization': 'answer',
    'seo-vs-ppc-which-drives-better-roi': 'compare', 'how-to-rank-in-google-ai-overviews': 'answer', 'how-to-choose-a-digital-marketing-agency': 'target',
    'digital-marketing-agency-pricing-guide': 'price', 'why-your-website-gets-traffic-but-no-leads': 'funnel', 'local-seo-checklist-for-small-businesses': 'map',
    'ai-search-trends-marketers-cannot-ignore': 'globe', 'content-marketing-use-cases-that-drive-revenue': 'book', 'benefits-of-hiring-an-seo-agency': 'rank',
    'google-ads-mistakes-draining-your-budget': 'funnel', 'seo-best-practices-for-ecommerce-stores': 'cart', 'how-we-doubled-organic-leads-for-a-clinic': 'rank',
    'chatgpt-seo-how-brands-get-recommended': 'answer', 'website-redesign-without-losing-seo': 'browser', 'email-marketing-vs-social-media-marketing': 'mail',
    'digital-marketing-faqs-answered-by-experts': 'answer', 'marketing-automation-trends-and-innovations': 'orbit', 'core-web-vitals-guide-for-business-owners': 'browser',
    'social-media-content-strategy-for-business': 'social', 'landing-page-optimization-guide': 'browser', 'ecommerce-conversion-rate-optimization': 'cart', 'branding-vs-performance-marketing': 'brand',
};
const SLUG_ART = [
    [/sap-cpq/, 'quote'], [/salesforce/, 'orbit'], [/odoo|erpnext/, 'modules'], [/staffing/, 'people'], [/enterprise-it/, 'orbit'], [/branding|brand-identity/, 'brand'],
    [/pricing/, 'price'], [/local|franchise|multi-location|restaurant/, 'map'], [/ecommerce|shopify|woocommerce|magento|bigcommerce|jewelry|fashion|beauty|firstchoice/, 'cart'],
    [/google-ads|ppc|paid|facebook|instagram/, 'funnel'], [/social|reputation/, 'social'], [/email|whatsapp|outbound/, 'mail'], [/ai-|chatgpt|perplexity|^ai$/, 'answer'],
    [/analytics|attribution|cafy/, 'dashboard'], [/web|speed|lead-gen-website|floweazy/, 'browser'], [/contact|about|clients|how-we-work/, 'globe'], [/case-studies|sai-impression/, 'rank'],
    [/ebooks|guides|content|writing|copy|translation/, 'book'], [/seo/, 'rank'], [/digital-marketing|consulting|online/, 'target'],
];
function concept(p) {
    if (p.post && BLOG_ART[p.post]) return BLOG_ART[p.post];
    for (const [rx, a] of SLUG_ART) if (rx.test(p.slug)) return a;
    return { ai: 'answer', web: 'browser', ppc: 'funnel', social: 'social', content: 'book', consulting: 'target' }[p.cat] || 'rank';
}

/* ---------------------------------- the one banner layout ------------------- */

const MARK = `<svg width="86" height="76" viewBox="199 214 667 591" xmlns="http://www.w3.org/2000/svg"><defs><linearGradient id="mv" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#00b8d9"/><stop offset="1" stop-color="#4338ca"/></linearGradient><linearGradient id="mu" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ff3d8a"/><stop offset="1" stop-color="#ff9100"/></linearGradient></defs><g transform="translate(130 130) scale(7.74)"><path d="M16 18 L38 80 L60 18" stroke="url(#mv)" stroke-width="14" stroke-linecap="round" stroke-linejoin="round" fill="none" style="mix-blend-mode:multiply"/><path d="M54 24 L54 56 Q54 80 71 80 Q88 80 88 56 L88 24" stroke="url(#mu)" stroke-width="14" stroke-linecap="round" fill="none" style="mix-blend-mode:multiply"/></g></svg>`;

function html(p) {
    const title = p.title;
    const n = title.length;
    const size = n <= 26 ? 60 : n <= 40 ? 54 : n <= 58 ? 47 : n <= 74 ? 42 : 37;
    const kicker = p.kicker || { marketing: 'Digital marketing', ai: 'AI search', web: 'Web development', enterprise: 'Enterprise & IT', resources: 'Resources', blog: 'Insight', cases: 'Case studies' }[p.section] || 'VTurnU';
    return `<!doctype html><html><head><meta charset="utf-8">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Caprasimo&family=Figtree:wght@600&display=swap" rel="stylesheet">
<style>*{box-sizing:border-box;margin:0}html,body{width:1200px;height:630px;overflow:hidden}
body{position:relative;font-family:Poppins,sans-serif;color:#1d1936;background:
radial-gradient(ellipse 620px 460px at 92% 8%,rgba(0,184,217,.2),transparent 62%),
radial-gradient(ellipse 560px 420px at 78% 100%,rgba(255,61,138,.17),transparent 62%),
radial-gradient(ellipse 520px 360px at 0% 100%,rgba(255,145,0,.11),transparent 62%),#fcfbff}
.left{position:absolute;left:58px;top:46px;bottom:46px;width:640px;display:flex;flex-direction:column}
.lock{display:flex;align-items:center;gap:15px}
.word{font-family:Caprasimo,serif;font-size:45px;line-height:1;letter-spacing:-.01em}
.word .v{background:linear-gradient(180deg,#00b8d9,#4338ca);-webkit-background-clip:text;color:transparent}
.word .u{background:linear-gradient(180deg,#ff3d8a,#ff9100);-webkit-background-clip:text;color:transparent}
.tag{font-family:Figtree,sans-serif;font-weight:600;font-size:13.5px;letter-spacing:.17em;color:#5b56c9;margin-top:5px}
.mid{flex:1;display:flex;flex-direction:column;justify-content:center;padding:14px 0}
.kick{align-self:flex-start;padding:8px 18px;border-radius:999px;color:#fff;font-weight:700;font-size:17px;letter-spacing:.02em;background:linear-gradient(100deg,#00b8d9,#4338ca 45%,#ff3d8a 78%,#ff9100);margin-bottom:20px}
h1{font-size:${size}px;line-height:1.14;font-weight:800;letter-spacing:-.022em;text-wrap:balance}
.nb{white-space:nowrap}
.foot{font-weight:700;font-size:20px;color:#3b3560}
.art{position:absolute;right:26px;top:66px;width:480px;height:500px}
.strip{position:absolute;left:0;right:0;bottom:0;height:12px;background:linear-gradient(90deg,#00b8d9,#4338ca 42%,#ff3d8a 72%,#ff9100)}
</style></head><body>
<div class="left"><div class="lock">${MARK}<div><div class="word"><span class="v">V</span>Turn<span class="u">U</span></div><div class="tag">TOGETHER WE TURN HEADS</div></div></div>
<div class="mid"><div class="kick">${esc(kicker)}</div><h1>${esc(title).replace(/[^ ]+-[^ ]+/g, (w) => `<span class="nb">${w}</span>`)}</h1></div><div class="foot">vturnu.com</div></div>
<svg class="art" viewBox="0 0 480 500" xmlns="http://www.w3.org/2000/svg">${DEFS}${ART[concept(p)]()}</svg>
<div class="strip"></div></body></html>`;
}

/* ------------------------------ Chrome over the DevTools protocol ----------- */

const PORT = 9333;
const profile = join(tmpdir(), 'vt-og-chrome');
const chrome = spawn(CHROME, ['--headless=new', `--remote-debugging-port=${PORT}`, `--user-data-dir=${profile}`, '--hide-scrollbars', '--force-device-scale-factor=1', 'about:blank'], { stdio: 'ignore' });
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
await send('Emulation.setDeviceMetricsOverride', { width: 1200, height: 630, deviceScaleFactor: 1, mobile: false });
const frameId = (await send('Page.getFrameTree')).frameTree.frame.id;

async function shot(format, quality, scale = 1) {
    const r = await send('Page.captureScreenshot', { format, quality, optimizeForSpeed: false, clip: { x: 0, y: 0, width: 1200, height: 630, scale } });
    return Buffer.from(r.data, 'base64');
}

const ogDir = join(ROOT, 'assets/img/og');
const blogDir = join(ROOT, 'assets/img/blog');
mkdirSync(ogDir, { recursive: true });
mkdirSync(blogDir, { recursive: true });

const extra = [
    { slug: 'site', file: 'site', title: 'Get found. Get chosen. Get pipeline.', section: '', kicker: 'Digital marketing and branding' },
    { slug: 'digital-marketing', file: 'section-marketing', title: 'Digital marketing that reports in revenue', section: 'marketing' },
    { slug: 'ai-seo', file: 'section-ai', title: 'Be the brand AI recommends', section: 'ai' },
    { slug: 'web-services', file: 'section-web', title: 'Websites built to grow your business', section: 'web' },
    { slug: 'enterprise-it-services', file: 'section-enterprise', title: 'Enterprise applications and technology talent', section: 'enterprise' },
    { slug: 'guides', file: 'section-resources', title: 'Free playbooks and guides', section: 'resources' },
    { slug: 'blog', file: 'section-blog', title: 'Growth playbooks and insights', section: 'blog' },
    { slug: 'case-studies', file: 'section-cases', title: 'Real clients, measured results', section: 'cases' },
];

const list = extra.concat(pages).filter((p) => !only || p.file.includes(only));
let done = 0;
for (const p of list) {
    await send('Page.setDocumentContent', { frameId, html: html(p) });
    await send('Runtime.evaluate', { expression: 'document.fonts.ready.then(()=>new Promise(r=>requestAnimationFrame(()=>requestAnimationFrame(r))))', awaitPromise: true });
    if (done === 0) { await sleep(1500); }
    writeFileSync(join(ogDir, p.file + '.jpg'), await shot('jpeg', 84));
    if (p.post) {
        writeFileSync(join(blogDir, p.post + '.webp'), await shot('webp', 82));
        writeFileSync(join(blogDir, p.post + '-sm.webp'), await shot('webp', 76, 640 / 1200));
    }
    if (p.tpl === 'resource') {
        writeFileSync(join(blogDir, 'res-' + p.slug.split('/').pop() + '-sm.webp'), await shot('webp', 76, 640 / 1200));
    }
    done++;
}
console.log('built', done);
ws.close();
chrome.kill();
await sleep(400);
try { rmSync(profile, { recursive: true, force: true }); } catch { /* Windows may hold the profile briefly */ }
process.exit(0);
