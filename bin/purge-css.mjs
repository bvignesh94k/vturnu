/**
 * Removes CSS rules whose class names appear nowhere in the code.
 *
 *   node bin/purge-css.mjs assets/css/style.css
 *
 * Deliberately cautious: a rule goes only if every selector in it names a class
 * that does not exist. Classes assembled in code, such as 'badge-' . $n, are kept
 * when their prefix appears in a source file. Element selectors, keyframes and
 * anything it cannot parse stay.
 */
import { readFileSync, writeFileSync, readdirSync, statSync } from 'node:fs';
import { join } from 'node:path';

const file = process.argv[2];
const walk = (d) => readdirSync(d).flatMap((f) => {
    const p = join(d, f);
    return statSync(p).isDirectory() ? walk(p) : [p];
});
const sources = ['templates', 'includes', 'api', 'assets/js']
    .flatMap(walk)
    .filter((f) => /\.(php|js)$/.test(f) && !/hero3d\.min\.js$/.test(f))
    .map((f) => readFileSync(f, 'utf8'))
    .join('\n');
const words = new Set(sources.match(/[A-Za-z][\w-]*/g));

function classUsed(c) {
    if (words.has(c)) { return true; }
    // Built in code: 'badge-' . $i, 'cols-' . $n and the like.
    const prefix = c.replace(/-[^-]+$/, '-');
    return prefix !== c && sources.includes(prefix);
}
function selectorUsed(sel) {
    const classes = sel.replace(/:(not|is|has|where)\([^)]*\)/g, '').match(/\.[A-Za-z_][\w-]*/g);
    if (!classes) { return true; }
    return classes.every((c) => classUsed(c.slice(1)));
}

/* Splits top-level blocks, keeping @media and @supports for a recursive pass. */
function purge(css) {
    let out = '', i = 0, removed = 0;
    while (i < css.length) {
        const open = css.indexOf('{', i);
        if (open < 0) { out += css.slice(i); break; }
        let depth = 1, j = open + 1;
        while (j < css.length && depth > 0) {
            if (css[j] === '{') { depth++; } else if (css[j] === '}') { depth--; }
            j++;
        }
        const prelude = css.slice(i, open);
        const body = css.slice(open + 1, j - 1);
        const head = prelude.replace(/\/\*[\s\S]*?\*\//g, '').trim();
        if (/^@(media|supports)/.test(head)) {
            const inner = purge(body);
            removed += inner.removed;
            if (inner.css.trim().replace(/\/\*[\s\S]*?\*\//g, '').trim()) { out += prelude + '{' + inner.css + '}'; }
        } else if (head.startsWith('@')) {
            out += css.slice(i, j);
        } else {
            const parts = head.split(',').map((s) => s.trim());
            const keep = parts.filter(selectorUsed);
            if (keep.length === 0) {
                removed++;
                out += prelude.match(/\/\*[\s\S]*?\*\//g)?.join('\n') ?? '';
            } else if (keep.length === parts.length) {
                out += css.slice(i, j);
            } else {
                out += keep.join(', ') + ' {' + body + '}';
            }
        }
        i = j;
    }
    return { css: out, removed };
}

const before = readFileSync(file, 'utf8');
const { css, removed } = purge(before);
writeFileSync(file, css.replace(/\n{3,}/g, '\n\n'));
console.log(file, 'removed', removed, 'rules,', before.length, 'to', css.length, 'bytes');
