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
    interview() {
        /* A microphone and two speech bubbles: an interview, with the answer that landed. */
        let s = panel(150, 40, 300, 190, 30);
        s += `<path d="M212 226l-8 50 52-50z" fill="#fff"/>`;
        s += `<text x="178" y="138" font-family="Caprasimo, serif" font-size="110" fill="url(#gU)">&#8220;</text>`;
        s += bar(262, 86, 160) + bar(262, 114, 132) + bar(186, 156, 236, '#e2e0f6') + bar(186, 184, 180, '#ecebfa');
        s += panel(236, 268, 214, 98, 26, 'url(#gB)') + bar(264, 302, 128, 'rgba(255,255,255,.92)') + bar(264, 328, 88, 'rgba(255,255,255,.72)');
        s += `<path d="M414 366l10 40-44-40z" fill="#e8436b"/>`;
        s += `<g filter="url(#sh)"><circle cx="424" cy="268" r="31" fill="#fff"/><circle cx="424" cy="268" r="24" fill="#0a8fc4"/>${tick(424, 270, 1.05)}</g>`;
        s += `<g filter="url(#sh)"><rect x="56" y="210" width="96" height="156" rx="48" fill="url(#gV)"/>`;
        [250, 276, 302, 328].forEach((y) => { s += `<path d="M74 ${y}h60" stroke="rgba(255,255,255,.5)" stroke-width="6" stroke-linecap="round"/>`; });
        s += `</g><path d="M38 318a66 66 0 0 0 132 0" fill="none" stroke="#1d1936" stroke-width="10" stroke-linecap="round"/><path d="M104 386v48M66 440h76" stroke="#1d1936" stroke-width="10" stroke-linecap="round"/>`;
        return s + sparkle(436, 440, 24) + sparkle(104, 132, 22, 'url(#gV)') + sparkle(196, 452, 14);
    },
    langs() {
        /* A globe with three language versions that point to each other. */
        let s = `<g filter="url(#sh)"><circle cx="220" cy="250" r="150" fill="url(#gL)" stroke="url(#gV)" stroke-width="5"/></g>`;
        s += `<g fill="none" stroke="#c9c5f0" stroke-width="2"><ellipse cx="220" cy="250" rx="150" ry="54"/><ellipse cx="220" cy="250" rx="54" ry="150"/><ellipse cx="220" cy="250" rx="108" ry="150"/><path d="M70 250h300M220 100v300"/></g>`;
        s += `<path d="M336 92Q440 190 372 300M336 92Q220 30 108 120M108 120Q70 330 372 300" fill="none" stroke="url(#gB)" stroke-width="4" stroke-dasharray="8 8"/>`;
        const chip = (x, y, w, txt, fill) => `<g filter="url(#sh)"><rect x="${x}" y="${y}" width="${w}" height="56" rx="28" fill="${fill}"/><text x="${x + w / 2}" y="${y + 38}" text-anchor="middle" font-family="Poppins" font-weight="800" font-size="26" fill="#fff">${txt}</text></g>`;
        s += chip(290, 64, 100, 'en', 'url(#gV)') + chip(30, 92, 140, 'en-GB', '#5b56c9') + chip(320, 272, 100, 'fr', 'url(#gU)');
        s += `<g filter="url(#sh)"><rect x="120" y="408" width="236" height="62" rx="18" fill="#1d1936"/><text x="238" y="448" text-anchor="middle" font-family="Poppins" font-weight="700" font-size="24" fill="#fff">hreflang</text></g>`;
        return s + sparkle(446, 150, 20) + sparkle(54, 300, 16, 'url(#gV)');
    },
    schema() {
        /* A JSON-LD card feeding a small graph of connected entities. */
        let s = panel(30, 60, 270, 300, 24, '#1d1936');
        s += `<text x="56" y="124" font-family="Poppins" font-weight="800" font-size="56" fill="#ff9100">{</text><text x="56" y="342" font-family="Poppins" font-weight="800" font-size="56" fill="#ff9100">}</text>`;
        [[92, 150, 150, '#7be8f7'], [112, 182, 120, '#8f8cf0'], [112, 214, 160, '#ff8db8'], [92, 246, 130, '#ffc777'], [112, 278, 100, '#7be8f7']].forEach(([x, y, w, c]) => { s += bar(x, y, w, c, 12); });
        s += `<path d="M300 210H350M350 210L404 128M350 210L412 300M404 128L412 300" stroke="#b8b4ee" stroke-width="5" fill="none"/>`;
        s += `<g filter="url(#sh)"><circle cx="350" cy="210" r="30" fill="url(#gB)"/><circle cx="404" cy="128" r="26" fill="url(#gV)"/><circle cx="412" cy="300" r="26" fill="url(#gU)"/></g>`;
        s += `<g filter="url(#sh)"><rect x="150" y="392" width="250" height="66" rx="20" fill="#fff" stroke="#e1dff6" stroke-width="2"/><text x="275" y="434" text-anchor="middle" font-family="Poppins" font-weight="800" font-size="26" fill="#1d1936">JSON-LD</text></g>`;
        return s + sparkle(444, 58, 22) + sparkle(60, 424, 16, 'url(#gV)');
    },
    brief() {
        /* A clipboard brief with questions ticked off, and a pencil. */
        let s = `<g filter="url(#sh)"><rect x="60" y="60" width="300" height="390" rx="26" fill="url(#gL)" stroke="#e1dff6" stroke-width="2"/></g>`;
        s += `<g filter="url(#sh)"><rect x="140" y="38" width="140" height="50" rx="16" fill="url(#gU)"/></g><circle cx="210" cy="62" r="9" fill="#fff"/>`;
        [130, 196, 262, 328].forEach((y, i) => {
            const done = i < 3;
            s += `<rect x="92" y="${y}" width="40" height="40" rx="11" fill="${done ? PAL[i % 2 ? 2 : 1][1] : '#fff'}" stroke="${done ? 'none' : '#cfcaf0'}" stroke-width="3"/>`;
            if (done) s += tick(112, y + 21, 0.95);
            s += bar(150, y + 8, 170 - i * 18, '#d9d6f4', 10) + bar(150, y + 26, 120 - i * 10, '#eceaf9', 8);
        });
        s += `<g filter="url(#sh)" transform="rotate(32 400 290)"><rect x="380" y="150" width="40" height="220" rx="10" fill="url(#gV)"/><path d="M380 370h40l-20 46z" fill="#ffc777"/><path d="M393 400h14l-7 16z" fill="#1d1936"/><rect x="380" y="150" width="40" height="30" rx="10" fill="#ff8db8"/></g>`;
        return s + sparkle(440, 70, 22) + sparkle(40, 380, 16, 'url(#gV)');
    },
    refresh() {
        /* A page being refreshed: two arrows circling a document whose line turns up. */
        let s = `<g filter="url(#sh)"><rect x="130" y="110" width="220" height="280" rx="22" fill="url(#gL)" stroke="#e1dff6" stroke-width="2"/></g>`;
        s += bar(158, 146, 140, '#1d1936', 14) + bar(158, 180, 160) + bar(158, 204, 130) + bar(158, 228, 150);
        s += `<path d="M160 340l40-30 30 18 50-52 34 16" fill="none" stroke="url(#gB)" stroke-width="9" stroke-linecap="round" stroke-linejoin="round"/>`;
        s += `<path d="M204.4 48.1A205 205 0 0 1 441.9 285.6" fill="none" stroke="url(#gV)" stroke-width="16" stroke-linecap="round"/><path d="M457.7 288.4L437.4 311.2 426.1 282.8Z" fill="#4338ca"/>`;
        s += `<path d="M275.6 451.9A205 205 0 0 1 38.1 214.4" fill="none" stroke="url(#gU)" stroke-width="16" stroke-linecap="round"/><path d="M22.3 211.6L42.6 188.8 53.9 217.2Z" fill="#ff3d8a"/>`;
        return s + sparkle(430, 440, 22) + sparkle(70, 90, 18, 'url(#gV)');
    },
    access() {
        /* The accessibility figure, a contrast swatch that passes, and a Tab key. */
        let s = `<g filter="url(#sh)"><circle cx="190" cy="200" r="130" fill="url(#gV)"/></g>`;
        s += `<circle cx="190" cy="130" r="20" fill="#fff"/><path d="M118 168l72 18 72-18" fill="none" stroke="#fff" stroke-width="16" stroke-linecap="round" stroke-linejoin="round"/><path d="M190 186v52M190 238l-30 64M190 238l30 64" fill="none" stroke="#fff" stroke-width="16" stroke-linecap="round" stroke-linejoin="round"/>`;
        s += `<g filter="url(#sh)"><rect x="286" y="250" width="164" height="120" rx="20" fill="#fff" stroke="#e1dff6" stroke-width="2"/></g><text x="314" y="332" font-family="Poppins" font-weight="800" font-size="58" fill="#1d1936">Aa</text><circle cx="420" cy="284" r="20" fill="#12805c"/>${tick(420, 286, 0.8)}`;
        s += `<g filter="url(#sh)"><rect x="70" y="374" width="170" height="78" rx="18" fill="url(#gU)"/><text x="155" y="424" text-anchor="middle" font-family="Poppins" font-weight="800" font-size="32" fill="#fff">Tab</text></g>`;
        return s + sparkle(430, 92, 24) + sparkle(330, 450, 16, 'url(#gV)');
    },
    social() {
        let s = panel(120, 40, 240, 400, 34);
        s += `<rect x="142" y="86" width="196" height="150" rx="16" fill="url(#gB)"/>` + bar(142, 254, 170) + bar(142, 278, 130);
        s += `<circle cx="168" cy="330" r="20" fill="#ffd9e6"/><path d="M168 340s-13-8-13-17a7 7 0 0 1 13-3 7 7 0 0 1 13 3c0 9-13 17-13 17z" fill="#ff3d8a"/>`;
        s += `<g filter="url(#sh)"><rect x="310" y="130" width="130" height="70" rx="22" fill="url(#gU)"/>${bar(332, 158, 86, 'rgba(255,255,255,.85)', 12)}</g><g filter="url(#sh)"><rect x="40" y="250" width="120" height="66" rx="22" fill="url(#gV)"/>${bar(62, 276, 76, 'rgba(255,255,255,.85)', 12)}</g>`;
        return s;
    },

    /* ---- Content writing pages and articles (November 2026). They share one
       idea with the pages themselves: show the edit, not a stack of books. ---- */
    edit() {
        /* A weak line struck out, the rewrite highlighted, and the pencil that did it. */
        let s = panel(50, 64, 330, 392, 26) + bar(82, 100, 150, '#1d1936', 16);
        [[150, 236, 190], [252, 214, 226]].forEach(([y, cut, kept]) => {
            s += bar(82, y, cut, '#d9d6f4', 12) + `<path d="M76 ${y + 6}h${cut + 12}" stroke="#ff3d8a" stroke-width="5" stroke-linecap="round"/>`;
            s += `<rect x="76" y="${y + 26}" width="${kept + 34}" height="40" rx="11" fill="#ffe2a8"/>` + bar(90, y + 40, kept, '#1d1936', 12);
        });
        s += bar(82, 362, 232) + bar(82, 388, 180) + bar(82, 414, 206);
        s += `<g filter="url(#sh)" transform="rotate(32 400 290)"><rect x="380" y="150" width="40" height="220" rx="10" fill="url(#gV)"/><path d="M380 370h40l-20 46z" fill="#ffc777"/><path d="M393 400h14l-7 16z" fill="#1d1936"/><rect x="380" y="150" width="40" height="30" rx="10" fill="#ff8db8"/></g>`;
        return s + sparkle(438, 76, 22) + sparkle(30, 430, 16, 'url(#gV)');
    },
    calendar() {
        /* An editorial calendar: four weeks, each with its planned pieces. */
        let s = panel(36, 76, 408, 348, 26) + bar(66, 108, 136, '#1d1936', 16);
        s += `<circle cx="398" cy="116" r="12" fill="url(#gU)"/><circle cx="364" cy="116" r="12" fill="url(#gV)"/>`;
        [0, 1, 2, 3].forEach((c) => {
            const x = 62 + c * 92;
            s += `<rect x="${x}" y="152" width="80" height="244" rx="14" fill="#f3f1fc"/>` + bar(x + 12, 166, 40, '#b9b4e6', 8);
            ['url(#gV)', 'url(#gU)', '#ffc777'].forEach((f, i) => {
                if (i && (c + i) % 3 === 2) return;
                s += `<rect x="${x + 8}" y="${190 + i * 66}" width="64" height="54" rx="12" fill="${f}"/>` + bar(x + 18, 211 + i * 66, 38, 'rgba(255,255,255,.88)', 9);
            });
        });
        s += `<g filter="url(#sh)"><circle cx="418" cy="414" r="46" fill="#1d1936"/>${tick(418, 416, 1.7, '#ffc94d')}</g>`;
        return s + sparkle(52, 44, 20, 'url(#gV)') + sparkle(444, 52, 16);
    },
    article() {
        /* A blog article: label, headline, the answer picked out, text and a picture. */
        let s = panel(64, 44, 320, 420, 26);
        s += `<rect x="92" y="72" width="78" height="20" rx="10" fill="url(#gV)"/>` + bar(92, 110, 252, '#1d1936', 18) + bar(92, 138, 176, '#1d1936', 18);
        s += `<rect x="92" y="176" width="264" height="76" rx="14" fill="#fff1d6"/><rect x="92" y="176" width="9" height="76" rx="4" fill="#ff9100"/>` + bar(116, 197, 212, '#e2b25f', 11) + bar(116, 220, 160, '#e2b25f', 11);
        s += bar(92, 278, 264) + bar(92, 300, 236) + bar(92, 322, 252);
        s += `<rect x="92" y="350" width="118" height="86" rx="14" fill="url(#gB)"/>` + bar(228, 360, 128) + bar(228, 382, 104) + bar(228, 404, 120);
        s += `<g filter="url(#sh)"><rect x="336" y="250" width="124" height="74" rx="24" fill="url(#gU)"/>${bar(360, 272, 76, 'rgba(255,255,255,.9)', 11)}${bar(360, 294, 50, 'rgba(255,255,255,.9)', 11)}</g>`;
        return s + sparkle(430, 90, 24, 'url(#gV)') + sparkle(34, 420, 16);
    },
    pages() {
        /* Four pages of one website, each with its own job and its own button. */
        let s = '';
        [[30, 56, 'url(#gV)'], [244, 30, 'url(#gU)'], [52, 270, '#ffb347'], [266, 244, '#5b56c9']].forEach(([x, y, f]) => {
            s += panel(x, y, 192, 200, 20) + `<rect x="${x + 18}" y="${y + 20}" width="56" height="12" rx="6" fill="${f}"/>`;
            s += bar(x + 18, y + 48, 150, '#1d1936', 14) + bar(x + 18, y + 72, 104, '#1d1936', 14) + bar(x + 18, y + 106, 156) + bar(x + 18, y + 126, 128);
            s += `<rect x="${x + 18}" y="${y + 152}" width="86" height="28" rx="14" fill="${f}"/>`;
        });
        return s + sparkle(238, 250, 20) + sparkle(456, 470, 16, 'url(#gV)');
    },
    story() {
        /* A customer story in three steps, ending on a number that was checked. */
        let s = '';
        [[30, 60, 262, 'url(#gV)'], [78, 186, 262, 'url(#gU)'], [126, 312, 300, '#ffc94d']].forEach(([x, y, w, f], i) => {
            const dark = i === 2;
            s += `<g filter="url(#sh)"><rect x="${x}" y="${y}" width="${w}" height="104" rx="22" fill="${dark ? '#1d1936' : 'url(#gL)'}" stroke="${dark ? 'none' : '#e1dff6'}" stroke-width="1.5"/></g>`;
            s += `<rect x="${x + 24}" y="${y + 22}" width="62" height="12" rx="6" fill="${f}"/>` + bar(x + 24, y + 48, w - 118, dark ? '#ffffff' : '#1d1936', 15) + bar(x + 24, y + 74, w - 160, dark ? 'rgba(255,255,255,.45)' : '#d9d6f4', 10);
        });
        s += `<g filter="url(#sh)"><circle cx="404" cy="318" r="52" fill="url(#gU)"/>${tick(404, 321, 1.9)}</g>`;
        return s + sparkle(396, 104, 26, 'url(#gV)') + sparkle(54, 430, 16);
    },
    pdp() {
        /* A product page: the picture, a precise title, three points and the specification. */
        let s = panel(36, 66, 408, 368, 26);
        s += `<rect x="62" y="94" width="152" height="172" rx="18" fill="url(#gB)"/>` + sparkle(138, 180, 36, '#ffffff');
        s += bar(234, 100, 176, '#1d1936', 16) + bar(234, 128, 124, '#1d1936', 16);
        ['#00b8d9', '#ff3d8a', '#ff9100'].forEach((c, i) => { s += `<circle cx="242" cy="${181 + i * 30}" r="7" fill="${c}"/>` + bar(260, 176 + i * 30, 148 - i * 20); });
        s += `<rect x="62" y="290" width="356" height="2" fill="#e1dff6"/>`;
        [62, 184, 306].forEach((x) => { s += `<rect x="${x}" y="312" width="112" height="92" rx="14" fill="#f3f1fc"/>` + bar(x + 14, 330, 46, '#b9b4e6', 8) + bar(x + 14, 354, 78, '#1d1936', 12) + bar(x + 14, 378, 54); });
        s += `<g filter="url(#sh)"><circle cx="420" cy="74" r="44" fill="url(#gU)"/>${tick(420, 76, 1.6)}</g>`;
        return s + sparkle(30, 456, 18, 'url(#gV)');
    },
    pair() {
        /* The engineer's note on the left becomes the buyer's page on the right. */
        let s = `<g filter="url(#sh)"><rect x="24" y="84" width="206" height="262" rx="22" fill="#1d1936"/></g>`;
        s += `<circle cx="52" cy="112" r="6" fill="#ff3d8a"/><circle cx="72" cy="112" r="6" fill="#ffc94d"/><circle cx="92" cy="112" r="6" fill="#00b8d9"/>`;
        [[148, 0, 124, '#7be8f7'], [176, 16, 138, '#ffffff'], [204, 16, 92, '#ff8db8'], [232, 32, 110, '#ffffff'], [260, 16, 126, '#ffc777'], [288, 0, 84, '#ffffff']].forEach(([y, dx, w, c]) => { s += bar(52 + dx, y, w, c, 10); });
        s += panel(250, 150, 206, 262, 22) + `<rect x="274" y="176" width="64" height="12" rx="6" fill="url(#gU)"/>`;
        s += bar(274, 204, 156, '#1d1936', 14) + bar(274, 228, 112, '#1d1936', 14) + bar(274, 264, 160) + bar(274, 286, 132) + bar(274, 308, 150);
        s += `<rect x="274" y="346" width="100" height="34" rx="17" fill="url(#gV)"/>`;
        s += `<g filter="url(#sh)"><circle cx="240" cy="248" r="36" fill="url(#gU)"/><path d="M225 248h28m-11-12 12 12-12 12" fill="none" stroke="#fff" stroke-width="6.5" stroke-linecap="round" stroke-linejoin="round"/></g>`;
        return s + sparkle(424, 84, 24, 'url(#gV)') + sparkle(60, 420, 16);
    },
    pulse() {
        /* A search update: rankings moving inside a shaded rollout window, with
           the "before" and "after" weeks marked on the timeline below. */
        let s = panel(28, 58, 424, 300, 26);
        s += bar(58, 92, 150, '#1d1936', 15) + `<rect x="300" y="84" width="122" height="30" rx="15" fill="url(#gU)"/>` + bar(318, 95, 86, 'rgba(255,255,255,.9)', 9);
        s += `<rect x="190" y="132" width="150" height="190" rx="12" fill="#ffe7f0"/>`;
        [170, 214, 258, 302].forEach((y) => { s += `<rect x="58" y="${y}" width="364" height="1.5" fill="#eceaf9"/>`; });
        s += `<path d="M62 238 L100 230 L138 236 L176 226 L206 262 L236 214 L266 282 L296 236 L326 250 L356 222 L392 214 L418 208" fill="none" stroke="url(#gB)" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"/>`;
        s += `<circle cx="418" cy="208" r="9" fill="#4338ca" stroke="#fff" stroke-width="3"/>`;
        s += `<rect x="40" y="392" width="112" height="22" rx="11" fill="#5b56c9"/><rect x="158" y="392" width="180" height="22" rx="11" fill="#e5267a"/><rect x="344" y="392" width="96" height="22" rx="11" fill="#0a8fc4"/>`;
        s += bar(48, 428, 70) + bar(196, 428, 100) + bar(352, 428, 60);
        s += `<g filter="url(#sh)"><circle cx="420" cy="62" r="40" fill="#1d1936"/><path d="M402 62h11l6-15 9 30 6-15h8" fill="none" stroke="#ffc94d" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/></g>`;
        return s + sparkle(56, 466, 18, 'url(#gV)');
    },
    review() {
        /* A page drafted with AI, then read and approved by a person. */
        let s = panel(70, 54, 304, 400, 26);
        s += `<g filter="url(#sh)"><rect x="98" y="82" width="76" height="38" rx="19" fill="url(#gV)"/></g><text x="136" y="108" text-anchor="middle" font-family="Poppins" font-weight="800" font-size="21" fill="#fff">AI</text>`;
        s += bar(98, 146, 232, '#1d1936', 16) + bar(98, 186, 248) + bar(98, 210, 216) + bar(98, 234, 238);
        s += `<rect x="92" y="262" width="262" height="36" rx="10" fill="#ffe2a8"/>` + bar(106, 275, 200, '#1d1936', 10);
        s += bar(98, 322, 240) + bar(98, 346, 196) + bar(98, 370, 222);
        s += `<g filter="url(#sh)"><circle cx="372" cy="384" r="64" fill="url(#gU)"/>${tick(372, 388, 2.3)}</g>`;
        return s + sparkle(432, 96, 24) + sparkle(40, 424, 16, 'url(#gV)');
    },
    /* ---- November 2026 authority series ---- */
    layers() {
        /* Six layers between a crawler and the content. The request gets through three and stops at the fourth. */
        let s = '';
        const fills = ['url(#gV)', 'url(#gV)', 'url(#gV)', 'url(#gU)', '#e4e1f7', '#e4e1f7'];
        fills.forEach((f, i) => {
            const y = 52 + i * 68, x = 60 + (i % 2) * 14;
            s += `<g filter="url(#sh)"><rect x="${x}" y="${y}" width="330" height="50" rx="16" fill="${i < 4 ? 'url(#gL)' : '#f3f1fc'}" stroke="#e1dff6" stroke-width="1.5"/></g>`;
            s += `<rect x="${x + 16}" y="${y + 13}" width="24" height="24" rx="8" fill="${f}"/>` + bar(x + 54, y + 20, 150 - i * 12, i < 4 ? '#1d1936' : '#c9c5ea', 11);
            if (i < 3) s += `<circle cx="${x + 296}" cy="${y + 25}" r="15" fill="#1d1936"/>` + tick(x + 296, y + 26, 0.62, '#ffc94d');
        });
        s += `<g filter="url(#sh)"><circle cx="376" cy="281" r="34" fill="url(#gU)"/>${cross(376, 281, 1.15)}</g>`;
        s += `<path d="M438 60v176" stroke="url(#gV)" stroke-width="7" stroke-linecap="round" stroke-dasharray="2 15"/><path d="M426 232l12 14 12-14" fill="none" stroke="#4338ca" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"/>`;
        return s + sparkle(36, 40, 18) + sparkle(444, 440, 20, 'url(#gV)');
    },
    gauge() {
        /* A speed gauge reading high, beside an enquiry form that is failing. */
        let s = panel(40, 50, 400, 250, 28);
        s += `<path d="M110 250a130 130 0 0 1 260 0" fill="none" stroke="#e4e1f7" stroke-width="26" stroke-linecap="round"/>`;
        s += `<path d="M110 250a130 130 0 0 1 238-72" fill="none" stroke="url(#gB)" stroke-width="26" stroke-linecap="round"/>`;
        s += `<path d="M240 250L322 168" stroke="#1d1936" stroke-width="9" stroke-linecap="round"/><circle cx="240" cy="250" r="17" fill="#1d1936"/><circle cx="240" cy="250" r="6" fill="#ffc94d"/>`;
        s += `<g filter="url(#sh)"><rect x="96" y="330" width="288" height="124" rx="22" fill="#1d1936"/></g>`;
        s += bar(122, 354, 120, '#ffffff', 13) + `<rect x="122" y="382" width="170" height="22" rx="8" fill="rgba(255,255,255,.16)"/><rect x="122" y="414" width="118" height="22" rx="8" fill="rgba(255,255,255,.16)"/>`;
        s += `<g filter="url(#sh)"><circle cx="372" cy="342" r="38" fill="url(#gU)"/>${cross(372, 342, 1.3)}</g>`;
        return s + sparkle(430, 40, 20, 'url(#gV)') + sparkle(50, 420, 16);
    },
    ledger() {
        /* A ledger of leads: each stage narrower than the one before, ending in a checked total. */
        let s = panel(44, 44, 392, 412, 26);
        s += bar(72, 74, 150, '#1d1936', 16) + `<rect x="72" y="106" width="336" height="2" fill="#e1dff6"/>`;
        [[300, 'url(#gV)'], [236, 'url(#gV)'], [176, 'url(#gB)'], [122, 'url(#gU)'], [78, 'url(#gU)']].forEach(([w, f], i) => {
            const y = 128 + i * 50;
            s += `<rect x="72" y="${y}" width="${w}" height="30" rx="10" fill="${f}"/>` + bar(388 - 42, y + 10, 42, '#c9c5ea', 10);
        });
        s += `<rect x="60" y="382" width="360" height="56" rx="16" fill="#1d1936"/>` + bar(82, 403, 130, '#ffffff', 14) + bar(318, 403, 80, '#ffc94d', 14);
        s += `<g filter="url(#sh)"><circle cx="424" cy="64" r="40" fill="url(#gU)"/>${tick(424, 66, 1.5)}</g>`;
        return s + sparkle(34, 470, 18, 'url(#gV)');
    },
    ladder() {
        /* Six rungs from mention to revenue: the lower ones counted (solid), the upper ones inferred (dotted). */
        let s = `<path d="M96 462L176 36M304 462L384 36" stroke="#1d1936" stroke-width="12" stroke-linecap="round"/>`;
        for (let i = 0; i < 6; i++) {
            const y = 420 - i * 66, dx = (462 - y) * (80 / 426);
            const x1 = 96 + dx, x2 = 304 + dx;
            s += i < 3
                ? `<path d="M${x1.toFixed(1)} ${y}H${x2.toFixed(1)}" stroke="${i === 2 ? 'url(#gB)' : 'url(#gV)'}" stroke-width="20" stroke-linecap="round"/>`
                : `<path d="M${x1.toFixed(1)} ${y}H${x2.toFixed(1)}" stroke="#ff3d8a" stroke-width="12" stroke-linecap="round" stroke-dasharray="3 22"/>`;
        }
        s += `<g filter="url(#sh)"><circle cx="412" cy="96" r="42" fill="#1d1936"/><text x="412" y="112" text-anchor="middle" font-family="Poppins" font-weight="800" font-size="46" fill="#ffc94d">?</text></g>`;
        s += `<g filter="url(#sh)"><circle cx="70" cy="390" r="36" fill="url(#gU)"/>${tick(70, 392, 1.35)}</g>`;
        return s + sparkle(440, 300, 20, 'url(#gV)') + sparkle(60, 80, 16);
    },
    split() {
        /* One bar in two colours: the larger part is people who already knew the brand. */
        let s = panel(30, 70, 420, 360, 26);
        s += bar(60, 104, 170, '#1d1936', 16) + bar(60, 134, 110);
        s += `<rect x="60" y="188" width="360" height="74" rx="20" fill="url(#gU)"/><rect x="60" y="188" width="262" height="74" rx="20" fill="url(#gV)"/><rect x="302" y="188" width="20" height="74" fill="url(#gV)"/><rect x="320" y="182" width="5" height="86" rx="2.5" fill="#fff"/>`;
        s += `<path d="M64 286v14h254v-14" fill="none" stroke="#4338ca" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/><path d="M330 286v14h86v-14" fill="none" stroke="#e5267a" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>`;
        s += `<rect x="96" y="318" width="190" height="34" rx="17" fill="#1d1936"/>` + bar(114, 330, 154, '#ffffff', 10) + `<rect x="330" y="318" width="86" height="34" rx="17" fill="#ffe7f0"/>` + bar(344, 330, 58, '#e5267a', 10);
        s += bar(60, 384, 300) + bar(60, 404, 220);
        s += `<g filter="url(#sh)"><circle cx="418" cy="80" r="40" fill="#1d1936"/><path d="M400 92l12-14 10 8 14-18" fill="none" stroke="#ffc94d" stroke-width="5.5" stroke-linecap="round" stroke-linejoin="round"/></g>`;
        return s + sparkle(40, 464, 18, 'url(#gV)') + sparkle(440, 452, 16);
    },
    agent() {
        /* A browser window an agent is working in: it has found the button and is about to press it. */
        let s = panel(30, 56, 420, 330, 24);
        s += `<rect x="30" y="56" width="420" height="52" rx="24" fill="#1d1936"/><rect x="30" y="86" width="420" height="22" fill="#1d1936"/>`;
        s += `<circle cx="60" cy="82" r="7" fill="#ff3d8a"/><circle cx="82" cy="82" r="7" fill="#ffc94d"/><circle cx="104" cy="82" r="7" fill="#00b8d9"/><rect x="130" y="70" width="230" height="24" rx="12" fill="rgba(255,255,255,.16)"/>`;
        s += bar(60, 138, 200, '#1d1936', 16) + bar(60, 170, 250) + bar(60, 192, 210);
        s += `<rect x="60" y="226" width="250" height="34" rx="10" fill="#f3f1fc" stroke="#d9d6f4" stroke-width="2"/><rect x="60" y="272" width="250" height="34" rx="10" fill="#f3f1fc" stroke="#d9d6f4" stroke-width="2"/>`;
        s += `<rect x="54" y="322" width="150" height="48" rx="24" fill="none" stroke="#ff9100" stroke-width="4" stroke-dasharray="9 8"/><rect x="60" y="328" width="138" height="36" rx="18" fill="url(#gV)"/>` + bar(86, 341, 86, '#ffffff', 10);
        s += `<path d="M196 346l6 62 16-20 26 4z" fill="#1d1936" stroke="#fff" stroke-width="4" stroke-linejoin="round"/>`;
        s += `<g filter="url(#sh)"><rect x="318" y="300" width="132" height="132" rx="34" fill="url(#gU)"/></g><rect x="346" y="336" width="76" height="56" rx="16" fill="#fff"/><circle cx="369" cy="362" r="8" fill="#1d1936"/><circle cx="399" cy="362" r="8" fill="#1d1936"/><path d="M384 336v-16" stroke="#fff" stroke-width="6" stroke-linecap="round"/><circle cx="384" cy="314" r="8" fill="#fff"/><path d="M370 408h28" stroke="#fff" stroke-width="6" stroke-linecap="round"/>`;
        return s + sparkle(440, 44, 20, 'url(#gV)') + sparkle(44, 440, 16);
    },
    launch() {
        /* A launch checklist, with the switch moving from staging to live. */
        let s = panel(46, 40, 300, 420, 26);
        s += bar(76, 72, 150, '#1d1936', 16);
        for (let i = 0; i < 6; i++) {
            const y = 112 + i * 54, ok = i < 4;
            s += `<rect x="76" y="${y}" width="32" height="32" rx="10" fill="${ok ? 'url(#gV)' : '#fff'}" stroke="${ok ? 'none' : '#c9c5ea'}" stroke-width="3"/>` + (ok ? tick(92, y + 17, 0.72) : '');
            s += bar(124, y + 5, 170 - (i % 3) * 26, ok ? '#1d1936' : '#c9c5ea', 10) + bar(124, y + 21, 110 - (i % 2) * 20);
        }
        s += `<g filter="url(#sh)"><rect x="286" y="188" width="170" height="86" rx="43" fill="url(#gU)"/><circle cx="413" cy="231" r="32" fill="#fff"/></g>` + tick(413, 233, 1.05, '#e5267a');
        s += `<path d="M308 231h54" stroke="#fff" stroke-width="7" stroke-linecap="round" stroke-dasharray="2 14"/>`;
        s += `<g filter="url(#sh)"><rect x="318" y="306" width="120" height="40" rx="20" fill="#1d1936"/></g>` + bar(340, 321, 76, '#ffc94d', 10);
        return s + sparkle(410, 96, 26, 'url(#gV)') + sparkle(400, 430, 18);
    },
    scorecard() {
        /* Four brands scored on four things, one column picked out. */
        let s = panel(28, 56, 424, 388, 26);
        s += bar(54, 86, 150, '#1d1936', 16);
        s += `<g filter="url(#sh)"><rect x="232" y="118" width="68" height="302" rx="18" fill="#1d1936"/></g>`;
        const cols = [164, 266, 334, 402];
        ['url(#gV)', '#ffc94d', 'url(#gU)', 'url(#gV)'].forEach((f, i) => { s += `<circle cx="${cols[i]}" cy="148" r="15" fill="${f}"/>`; });
        const marks = [[1, 1, 0, 1], [1, 1, 1, 0], [0, 1, 0, 1], [1, 1, 0, 0]];
        marks.forEach((row, r) => {
            const y = 204 + r * 58;
            s += bar(54, y - 5, 74, '#c9c5ea', 10) + (r < 3 ? `<rect x="54" y="${y + 28}" width="372" height="1.5" fill="#eceaf9"/>` : '');
            row.forEach((m, c) => {
                const onDark = c === 1;
                s += m ? `<circle cx="${cols[c]}" cy="${y}" r="17" fill="${onDark ? '#ffc94d' : '#e9f8fc'}"/>` + tick(cols[c], y + 1, 0.7, onDark ? '#1d1936' : '#0a8fc4')
                    : `<circle cx="${cols[c]}" cy="${y}" r="17" fill="#ffe7f0"/>` + cross(cols[c], y, 0.62, '#e5267a');
            });
        });
        return s + sparkle(436, 44, 22) + sparkle(40, 468, 18, 'url(#gV)');
    },
    signal() {
        /* Many reactions, fading out, and the one account card worth a call. */
        let s = '';
        const dots = [[58, 96, 30, 1], [132, 70, 26, .85], [200, 104, 22, .7], [262, 76, 18, .55], [318, 108, 15, .4], [366, 84, 12, .28], [408, 110, 9, .18],
            [92, 172, 22, .7], [160, 158, 18, .55], [224, 178, 14, .4], [280, 160, 11, .28], [330, 180, 8, .18]];
        dots.forEach(([x, y, r, o], i) => {
            s += `<circle cx="${x}" cy="${y}" r="${r}" fill="${i % 3 === 0 ? 'url(#gU)' : i % 3 === 1 ? 'url(#gV)' : '#ffc94d'}" opacity="${o}"/>`;
            if (r > 20) s += `<path d="M${x - r * 0.42} ${y + r * 0.08}l${r * 0.3} ${r * 0.32} ${r * 0.56}-${r * 0.62}" fill="none" stroke="#fff" stroke-width="${r * 0.2}" stroke-linecap="round" stroke-linejoin="round" opacity="${o}"/>`;
        });
        s += `<path d="M240 214v40" stroke="#4338ca" stroke-width="6" stroke-linecap="round" stroke-dasharray="2 13"/><path d="M228 250l12 14 12-14" fill="none" stroke="#4338ca" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>`;
        s += panel(54, 276, 372, 176, 26);
        s += `<rect x="80" y="302" width="76" height="76" rx="20" fill="#1d1936"/><rect x="100" y="322" width="14" height="36" rx="4" fill="#ffc94d"/><rect x="122" y="334" width="14" height="24" rx="4" fill="#7be8f7"/>`;
        s += bar(176, 308, 170, '#1d1936', 16) + bar(176, 338, 120) + bar(176, 360, 150);
        [0, 1, 2].forEach((i) => { s += `<circle cx="${96 + i * 30}" cy="414" r="15" fill="${['url(#gV)', 'url(#gU)', '#ffc94d'][i]}" stroke="#fff" stroke-width="3"/>`; });
        s += `<rect x="286" y="396" width="116" height="36" rx="18" fill="url(#gV)"/>` + bar(306, 409, 76, '#ffffff', 10);
        s += `<g filter="url(#sh)"><circle cx="418" cy="284" r="36" fill="url(#gU)"/>${tick(418, 286, 1.3)}</g>`;
        return s + sparkle(446, 196, 18, 'url(#gV)') + sparkle(36, 250, 14);
    },
    matrix() {
        /* Search phrases on the left, each joined to the page that answers it on the right. */
        let s = '';
        const rows = [78, 150, 222, 294, 366, 438];
        const to = [1, 0, 2, 4, 3, 5];
        rows.forEach((y, i) => {
            const y2 = rows[to[i]];
            s += `<path d="M196 ${y}C246 ${y} 250 ${y2} 300 ${y2}" fill="none" stroke="${i % 2 ? '#ff3d8a' : '#4338ca'}" stroke-width="4" stroke-linecap="round" opacity=".75"/>`;
        });
        rows.forEach((y, i) => {
            s += `<g filter="url(#sh)"><rect x="20" y="${y - 22}" width="176" height="44" rx="22" fill="#1d1936"/></g><circle cx="46" cy="${y}" r="9" fill="none" stroke="#ffc94d" stroke-width="3.5"/><path d="M53 ${y + 7}l6 6" stroke="#ffc94d" stroke-width="3.5" stroke-linecap="round"/>` + bar(70, y - 5, 104 - (i % 3) * 16, '#ffffff', 10);
            s += `<g filter="url(#sh)"><rect x="300" y="${y - 26}" width="160" height="52" rx="14" fill="url(#gL)" stroke="#e1dff6" stroke-width="1.5"/></g><rect x="314" y="${y - 13}" width="26" height="26" rx="8" fill="${i % 2 ? 'url(#gU)' : 'url(#gV)'}"/>` + bar(352, y - 10, 86 - (i % 2) * 14, '#1d1936', 9) + bar(352, y + 5, 60, '#d9d6f4', 8);
        });
        return s + sparkle(248, 36, 18) + sparkle(250, 476, 14, 'url(#gV)');
    },
};

/* Concept per blog post, chosen for what the article is about. */
const BLOG_ART = {
    'content-writing-vs-copywriting-vs-content-marketing': 'compare', 'how-to-brief-a-content-writer': 'brief', 'how-to-write-a-b2b-case-study': 'story',
    'does-google-penalise-ai-content': 'review', 'what-to-write-on-every-page-of-a-b2b-website': 'pages',
    'international-seo-checklist': 'langs', 'schema-markup-for-b2b-websites': 'schema', 'website-design-brief-questions': 'brief',
    'content-refresh-checklist': 'refresh', 'website-accessibility-checklist': 'access',
    'selectedfirms-interview-qualified-leads-beat-traffic': 'interview',
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
    'ai-crawler-access-problems': 'layers', 'pagespeed-score-vs-qualified-leads': 'gauge', 'measure-qualified-leads-from-organic-search': 'ledger',
    'ai-citations-vs-website-traffic': 'ladder', 'search-visibility-vs-revenue-demand': 'split', 'how-ai-agents-evaluate-b2b-websites': 'agent',
    'wordpress-seo-launch-checklist': 'launch', 'ai-search-competitor-benchmark': 'scorecard', 'linkedin-engagement-vs-buyer-intent': 'signal',
    'industrial-buyer-search-intent': 'matrix',
    'social-media-content-strategy-for-business': 'social', 'landing-page-optimization-guide': 'browser', 'ecommerce-conversion-rate-optimization': 'cart', 'branding-vs-performance-marketing': 'brand',
};
const SLUG_ART = [
    [/^updates(\/|$)/, 'pulse'],
    [/sap-cpq/, 'quote'], [/salesforce/, 'orbit'], [/odoo|erpnext/, 'modules'], [/staffing/, 'people'], [/enterprise-it/, 'orbit'], [/branding|brand-identity/, 'brand'],
    [/pricing/, 'price'], [/local|franchise|multi-location|restaurant/, 'map'], [/ecommerce|shopify|woocommerce|magento|bigcommerce|jewelry|fashion|beauty|firstchoice/, 'cart'],
    [/google-ads|ppc|paid|facebook|instagram/, 'funnel'], [/social|reputation/, 'social'], [/email|whatsapp|outbound/, 'mail'], [/ai-|chatgpt|perplexity|^ai$/, 'answer'],
    [/analytics|attribution|cafy/, 'dashboard'], [/web|speed|lead-gen-website|floweazy/, 'browser'], [/contact|about|clients|how-we-work/, 'globe'], [/case-studies|sai-impression/, 'rank'],
    [/ebooks|guides|content|writing|copy|translation/, 'book'], [/seo/, 'rank'], [/digital-marketing|consulting|online/, 'target'],
];
/* Pages with a concept of their own, checked before the patterns above. */
const PAGE_ART = {
    'content-writing-services': 'edit', 'content-marketing-services': 'calendar', 'blog-writing-services': 'article',
    'website-content-writing-services': 'pages', 'white-paper-writing-services': 'book', 'case-study-writing-services': 'story',
    'product-description-writing-services': 'pdp', 'technical-content-writing-services': 'pair',
};
function concept(p) {
    if (p.post && BLOG_ART[p.post]) return BLOG_ART[p.post];
    if (PAGE_ART[p.slug]) return PAGE_ART[p.slug];
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
