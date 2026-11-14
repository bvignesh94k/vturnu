/**
 * Listicle kit: progress, filters, copy buttons and the tools inside the
 * numbered list articles (includes/listicle-kit.php). Loaded only on blog
 * posts whose record sets 'listicle' => true.
 *
 * Every control is printed with class lx-js and stays hidden until this file
 * adds lx-on to <html>, so a reader without JavaScript gets a plain article.
 * Progress and answers stay in the reader's own browser (localStorage, one
 * key per article). Nothing is sent anywhere.
 */
(() => {
    'use strict';

    const doc = document;
    doc.documentElement.classList.add('lx-on');

    const $ = (sel, el = doc) => el.querySelector(sel);
    const $$ = (sel, el = doc) => Array.from(el.querySelectorAll(sel));

    const store = {
        get(key) { try { return JSON.parse(localStorage.getItem(key) || 'null'); } catch (e) { return null; } },
        set(key, val) { try { localStorage.setItem(key, JSON.stringify(val)); } catch (e) { /* private mode: state is simply not kept */ } },
        drop(key) { try { localStorage.removeItem(key); } catch (e) { /* nothing to drop */ } },
    };

    /* One polite live region for short confirmations. */
    let live = null;
    function say(msg) {
        if (!live) {
            live = doc.createElement('p');
            live.className = 'sr-only';
            live.setAttribute('aria-live', 'polite');
            doc.body.appendChild(live);
        }
        live.textContent = '';
        window.setTimeout(() => { live.textContent = msg; }, 40);
    }

    async function copyText(text) {
        try { await navigator.clipboard.writeText(text); return true; } catch (e) { /* fall back below */ }
        const ta = doc.createElement('textarea');
        ta.value = text;
        ta.setAttribute('readonly', '');
        ta.style.cssText = 'position:fixed;top:0;left:0;opacity:0';
        doc.body.appendChild(ta);
        ta.select();
        let ok = false;
        try { ok = doc.execCommand('copy'); } catch (e) { ok = false; }
        ta.remove();
        return ok;
    }

    /* Swap a button's text for a moment ("Copied"), then put it back. */
    function flash(btn, text, ms = 1800) {
        if (!btn.dataset.lxLabel) btn.dataset.lxLabel = btn.textContent;
        btn.textContent = text;
        window.clearTimeout(btn.lxTimer);
        btn.lxTimer = window.setTimeout(() => { btn.textContent = btn.dataset.lxLabel; }, ms);
    }

    const itemTitle = (item) => (($('h2', item) || {}).textContent || '').replace(/^\s*\d+\.\s*/, '').trim();

    /* ------------------------------------------------ code samples */
    doc.addEventListener('click', async (e) => {
        const btn = e.target.closest('[data-lx-copy]');
        if (!btn) return;
        const code = btn.closest('.lx-code') && btn.closest('.lx-code').querySelector('code');
        if (!code) return;
        const ok = await copyText(code.textContent);
        flash(btn, ok ? 'Copied' : 'Select and copy');
        say(ok ? 'Copied to the clipboard' : 'Copy did not work. Select the code and copy it.');
    });

    /* ------------------------------------------------ progress board */
    $$('[data-lx-board]').forEach((board) => {
        const article = board.closest('.post-rich') || doc;
        const items = $$('.lx-item', article);
        const mode = board.dataset.lxMode || 'mark';
        const key = 'lx:' + board.dataset.lxBoard;
        let state = store.get(key);
        if (!state || typeof state !== 'object' || Array.isArray(state)) state = {};
        const save = () => store.set(key, state);
        const total = items.length;
        const glance = $('.lx-glance', article);
        const fixList = $('[data-lx-fixlist]', board);

        const totalEl = $('[data-lx-total]', board);
        if (totalEl) totalEl.textContent = String(total);

        const answered = (id) => typeof state[id] === 'string' && state[id].trim() !== '';
        const isDone = (id) => (mode === 'answer' ? answered(id) : Boolean(state[id]));

        items.forEach((item) => {
            const id = item.dataset.lxItem;
            const mark = $('[data-lx-mark]', item);
            if (mark) {
                mark.addEventListener('click', () => {
                    if (state[id]) delete state[id]; else state[id] = 1;
                    save();
                    paint();
                });
            }
            $$('[data-lx-val]', item).forEach((btn) => btn.addEventListener('click', () => {
                if (state[id] === btn.dataset.lxVal) delete state[id]; else state[id] = btn.dataset.lxVal;
                save();
                paint();
            }));
            const box = $('[data-lx-answer]', item);
            if (box) {
                if (typeof state[id] === 'string') box.value = state[id];
                let timer;
                box.addEventListener('input', () => {
                    window.clearTimeout(timer);
                    timer = window.setTimeout(() => {
                        if (box.value.trim() === '') delete state[id]; else state[id] = box.value;
                        save();
                        paint();
                    }, 300);
                });
            }
        });

        /* Free-text fields that belong to the whole article, such as a project name. */
        $$('[data-lx-field]', article).forEach((field) => {
            const k = '_f_' + field.dataset.lxField;
            if (typeof state[k] === 'string') field.value = state[k];
            field.addEventListener('input', () => {
                if (field.value.trim() === '') delete state[k]; else state[k] = field.value;
                save();
            });
        });

        function paint() {
            let done = 0;
            const tally = { pass: 0, fail: 0, unsure: 0 };
            const fails = [];
            items.forEach((item) => {
                const id = item.dataset.lxItem;
                const v = state[id];
                const d = isDone(id);
                if (d) done += 1;
                const mark = $('[data-lx-mark]', item);
                if (mark) mark.setAttribute('aria-pressed', d ? 'true' : 'false');
                $$('[data-lx-val]', item).forEach((btn) => btn.setAttribute('aria-pressed', v === btn.dataset.lxVal ? 'true' : 'false'));
                if (mode === 'tri' && Object.prototype.hasOwnProperty.call(tally, v)) tally[v] += 1;
                if (mode === 'tri' && v === 'fail') fails.push(item);
                const good = mode === 'tri' ? v === 'pass' : d;
                item.classList.toggle('is-done', good);
                item.classList.toggle('is-fail', mode === 'tri' && v === 'fail');
                item.classList.toggle('is-unsure', mode === 'tri' && v === 'unsure');
                if (glance) {
                    const link = $('a[href="#' + id + '"]', glance);
                    if (link) {
                        link.parentElement.classList.toggle('is-done', good);
                        link.parentElement.classList.toggle('is-fail', mode === 'tri' && v === 'fail');
                    }
                }
            });
            const doneEl = $('[data-lx-done]', board);
            if (doneEl) doneEl.textContent = String(done);
            const bar = $('.lx-bar span', board);
            if (bar) bar.style.width = (total ? Math.round((done / total) * 100) : 0) + '%';
            let shown = false;
            $$('.lx-msgs [data-upto]', board).forEach((p) => {
                const hit = !shown && done <= Number(p.dataset.upto);
                p.hidden = !hit;
                if (hit) shown = true;
            });
            if (mode === 'tri') {
                Object.keys(tally).forEach((k) => {
                    const el = $('[data-lx-n="' + k + '"]', board);
                    if (el) el.textContent = String(tally[k]);
                });
                if (fixList) {
                    const ol = $('ol', fixList);
                    ol.textContent = '';
                    fails.forEach((item) => {
                        const li = doc.createElement('li');
                        const a = doc.createElement('a');
                        a.href = '#' + item.id;
                        a.textContent = itemTitle(item);
                        li.appendChild(a);
                        ol.appendChild(li);
                    });
                    fixList.hidden = fails.length === 0;
                }
            }
        }

        /* Filters: a type chip, everything, or what is still open. */
        const chips = $$('[data-lx-filter]', board);
        function applyFilter(f, quiet) {
            chips.forEach((c) => c.setAttribute('aria-pressed', c.dataset.lxFilter === f ? 'true' : 'false'));
            let n = 0;
            items.forEach((item) => {
                const id = item.dataset.lxItem;
                const types = (item.dataset.lxType || '').split(/\s+/);
                const open = mode === 'tri' ? !state[id] : !isDone(id);
                const show = f === 'all' || (f === 'todo' ? open : types.includes(f));
                item.hidden = !show;
                if (show) n += 1;
            });
            if (!quiet) say(n + ' of ' + total + ' shown');
        }
        chips.forEach((c) => c.addEventListener('click', () => applyFilter(c.dataset.lxFilter)));

        /* A jump link to an item the filter is hiding shows everything again. */
        article.addEventListener('click', (e) => {
            const a = e.target.closest('a[href^="#"]');
            if (!a) return;
            const target = doc.getElementById(a.getAttribute('href').slice(1));
            if (target && target.hidden && target.classList.contains('lx-item')) applyFilter('all', true);
        });

        /* Start over: a second click within a few seconds confirms. */
        const reset = $('[data-lx-reset]', board);
        if (reset) {
            reset.addEventListener('click', () => {
                if (reset.dataset.armed !== '1') {
                    reset.dataset.armed = '1';
                    flash(reset, 'Click again to clear', 3000);
                    window.setTimeout(() => { reset.dataset.armed = ''; }, 3000);
                    return;
                }
                reset.dataset.armed = '';
                state = {};
                store.drop(key);
                $$('[data-lx-answer]', article).forEach((b) => { b.value = ''; });
                $$('[data-lx-field]', article).forEach((f) => { f.value = ''; });
                applyFilter('all', true);
                paint();
                say('Cleared');
            });
        }

        if (mode === 'answer') wireBrief(board, article, items, () => state);

        paint();
    });

    /* ------------------------------------------------ brief builder (answer mode) */
    function wireBrief(board, article, items, getState) {
        const text = () => {
            const state = getState();
            const name = typeof state._f_project === 'string' ? state._f_project.trim() : '';
            const lines = [board.dataset.lxTitle || 'Website brief'];
            if (name) lines.push('Project: ' + name);
            lines.push('Prepared: ' + new Date().toLocaleDateString(undefined, { day: 'numeric', month: 'long', year: 'numeric' }));
            lines.push('');
            items.forEach((item, i) => {
                const v = state[item.dataset.lxItem];
                lines.push((i + 1) + '. ' + itemTitle(item));
                lines.push(typeof v === 'string' && v.trim() !== '' ? v.trim() : '(Not answered yet)');
                lines.push('');
            });
            return lines.join('\n');
        };
        /* The buttons can sit in the board and again after the last question. */
        $$('[data-lx-brief-copy]', article).forEach((copyBtn) => {
            copyBtn.addEventListener('click', async () => {
                const ok = await copyText(text());
                flash(copyBtn, ok ? 'Copied' : 'Copy did not work');
                say(ok ? 'Your brief is copied' : 'Copy did not work');
            });
        });
        $$('[data-lx-brief-download]', article).forEach((dlBtn) => {
            dlBtn.addEventListener('click', () => {
                const state = getState();
                const base = (typeof state._f_project === 'string' ? state._f_project : '')
                    .toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 40) || board.dataset.lxFile || 'website';
                const blob = new Blob([text().replace(/\n/g, '\r\n')], { type: 'text/plain;charset=utf-8' });
                const url = URL.createObjectURL(blob);
                const a = doc.createElement('a');
                a.href = url;
                a.download = base + '-brief.txt';
                doc.body.appendChild(a);
                a.click();
                a.remove();
                window.setTimeout(() => URL.revokeObjectURL(url), 1500);
                say('Your brief is downloaded');
            });
        });
    }

    /* ------------------------------------------------ article tools */
    const TOOLS = {};

    /* Hreflang builder: rows of URL, language and optional region, checked and
       turned into link tags and sitemap entries. Codes are checked by format and,
       where the browser supports Intl.DisplayNames, against the names it knows. */
    TOOLS.hreflang = (el) => {
        const rowsBox = $('[data-hl-rows]', el);
        const xd = $('[data-hl-xdefault]', el);
        const outHtml = $('[data-hl-html]', el);
        const outXml = $('[data-hl-xml]', el);
        const msgs = $('[data-hl-msgs]', el);
        const add = $('[data-hl-add]', el);
        if (!rowsBox || !outHtml) return;

        let names = null;
        try {
            names = {
                lang: new Intl.DisplayNames(['en'], { type: 'language', fallback: 'none' }),
                region: new Intl.DisplayNames(['en'], { type: 'region', fallback: 'none' }),
                full: new Intl.DisplayNames(['en'], { type: 'language', languageDisplay: 'standard' }),
            };
        } catch (e) { names = null; }
        const known = (type, code) => {
            if (!names) return true;
            try {
                const n = names[type].of(code);
                return Boolean(n) && n.toLowerCase() !== code.toLowerCase() && n !== 'Unknown Region';
            } catch (e) { return false; }
        };
        const nameOf = (tag) => {
            if (!names) return '';
            try { return names.full.of(tag) || ''; } catch (e) { return ''; }
        };
        const attr = (s) => s.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

        function addRow(url, lang, region) {
            const row = doc.createElement('div');
            row.className = 'lx-hl-row';
            row.innerHTML = '<label class="lx-hl-url"><span>Page URL</span><input type="url" inputmode="url" autocomplete="off" spellcheck="false" placeholder="https://www.example.com/fr/" data-hl="url"></label>'
                + '<label class="lx-hl-lang"><span>Language</span><input type="text" autocomplete="off" spellcheck="false" maxlength="12" placeholder="fr" list="lx-hl-langs" data-hl="lang"></label>'
                + '<label class="lx-hl-reg"><span>Region (optional)</span><input type="text" autocomplete="off" spellcheck="false" maxlength="3" placeholder="Any" data-hl="region"></label>'
                + '<button type="button" class="lx-hl-del" data-hl-del>Remove</button>'
                + '<p class="lx-hl-name" data-hl-name></p>';
            $('[data-hl="url"]', row).value = url;
            $('[data-hl="lang"]', row).value = lang;
            $('[data-hl="region"]', row).value = region;
            rowsBox.appendChild(row);
            return row;
        }

        /* One row as {url, tag}, or null with the problem added to out.err. */
        function read(row, n, out) {
            const url = $('[data-hl="url"]', row).value.trim();
            const langIn = $('[data-hl="lang"]', row).value.trim().replace(/_/g, '-');
            const regIn = $('[data-hl="region"]', row).value.trim();
            const nameEl = $('[data-hl-name]', row);
            nameEl.textContent = '';
            if (!url && !langIn && !regIn) return null;
            const bad = (m) => { out.err.push('Version ' + n + ': ' + m); return null; };

            const parts = langIn.split('-').filter(Boolean);
            const lang = (parts[0] || '').toLowerCase();
            let script = '';
            let region = '';
            for (const p of parts.slice(1)) {
                if (!script && /^[a-z]{4}$/i.test(p)) script = p[0].toUpperCase() + p.slice(1).toLowerCase();
                else if (!region && /^([a-z]{2}|\d{3})$/i.test(p)) region = p.toUpperCase();
                else return bad('"' + langIn + '" is not a code Google accepts.');
            }
            if (regIn) region = regIn.toUpperCase();

            if (!lang) return bad('add a language code such as en or fr. A region on its own is not allowed.');
            if (!/^[a-z]{2}$/.test(lang)) return bad('use the two-letter ISO 639-1 language code, such as de for German.');
            if (!known('lang', lang)) return bad('"' + lang + '" is not a recognised language code.');
            if (region) {
                if (/^\d+$/.test(region)) return bad('Google does not support region codes like 419 (es-419); use a country code or leave the region empty.');
                if (region === 'UK' || region === 'EU' || region === 'UN') return bad('use GB for the United Kingdom; Google ignores reserved codes such as UK, EU and UN.');
                if (!/^[A-Z]{2}$/.test(region)) return bad('a region is a two-letter country code, such as GB or MX.');
                if (!known('region', region)) return bad('"' + region + '" is not a recognised country code.');
            }
            if (!url) return bad('add the full URL of this version.');
            let parsed = null;
            try { parsed = new URL(url); } catch (e) { parsed = null; }
            if (!parsed || !/^https?:$/.test(parsed.protocol)) return bad('use the full address, starting with https://');

            const tag = lang + (script ? '-' + script : '') + (region ? '-' + region : '');
            const nm = nameOf(tag);
            nameEl.textContent = nm ? tag + ': ' + nm : '';
            return { url, tag };
        }

        function render() {
            const out = { err: [], tip: [] };
            const list = [];
            $$('.lx-hl-row', rowsBox).forEach((row, i) => {
                $('[data-hl-del]', row).setAttribute('aria-label', 'Remove version ' + (i + 1));
                const r = read(row, i + 1, out);
                if (r) list.push(r);
            });
            const tags = new Set();
            const urls = new Set();
            list.forEach((r) => {
                const t = r.tag.toLowerCase();
                if (tags.has(t)) out.err.push(r.tag + ' is used twice. Each language and region pair should point to one URL.');
                if (urls.has(r.url)) out.err.push('The same URL appears twice. Each version needs its own address.');
                tags.add(t);
                urls.add(r.url);
            });
            let x = xd ? xd.value.trim() : '';
            if (x) {
                let ok = false;
                try { ok = /^https?:$/.test(new URL(x).protocol); } catch (e) { ok = false; }
                if (!ok) { out.err.push('x-default: use the full address, starting with https://'); x = ''; }
            }
            if (list.length < 2) out.tip.push('Add at least two versions. Hreflang describes alternates, so a page with no other version needs no tags.');

            const lines = list.map((r) => '<link rel="alternate" hreflang="' + r.tag + '" href="' + attr(r.url) + '" />');
            if (x) lines.push('<link rel="alternate" hreflang="x-default" href="' + attr(x) + '" />');
            outHtml.textContent = lines.length ? lines.join('\n') : '<!-- Add your versions above -->';

            if (outXml) {
                const links = list.map((r) => '    <xhtml:link rel="alternate" hreflang="' + r.tag + '" href="' + attr(r.url) + '"/>');
                if (x) links.push('    <xhtml:link rel="alternate" hreflang="x-default" href="' + attr(x) + '"/>');
                const entries = list.map((r) => '  <url>\n    <loc>' + attr(r.url) + '</loc>\n' + links.join('\n') + '\n  </url>');
                outXml.textContent = '<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"\n        xmlns:xhtml="http://www.w3.org/1999/xhtml">\n'
                    + entries.join('\n') + '\n</urlset>';
            }

            if (msgs) {
                msgs.textContent = '';
                const note = (cls, text) => {
                    const li = doc.createElement('li');
                    li.className = cls;
                    li.textContent = text;
                    msgs.appendChild(li);
                };
                out.err.forEach((m) => note('lx-err', m));
                out.tip.forEach((m) => note('lx-tip', m));
                if (!out.err.length && !out.tip.length) {
                    note('lx-ok', 'Looks right. Put the same block on every version listed, including the line that points to the page itself.');
                }
            }
        }

        rowsBox.addEventListener('input', render);
        rowsBox.addEventListener('click', (e) => {
            const del = e.target.closest('[data-hl-del]');
            if (!del) return;
            const rows = $$('.lx-hl-row', rowsBox);
            if (rows.length <= 1) $$('input', rows[0]).forEach((i) => { i.value = ''; });
            else del.closest('.lx-hl-row').remove();
            render();
            if (add) add.focus();
            say('Version removed');
        });
        if (add) {
            add.addEventListener('click', () => {
                const row = addRow('', '', '');
                render();
                $('input', row).focus();
            });
        }
        if (xd) xd.addEventListener('input', render);

        addRow('https://www.example.com/en/', 'en', '');
        addRow('https://www.example.com/en-gb/', 'en', 'GB');
        addRow('https://www.example.com/fr/', 'fr', '');
        render();
    };

    /* Schema picker: choose a page type, and the items it needs are marked and
       listed as jump links. Each page button can carry a one-line tip. */
    TOOLS.schema = (el) => {
        const article = el.closest('.post-rich') || doc;
        const items = $$('.lx-item[data-lx-pages]', article);
        const out = $('[data-pick-out]', el);
        const btns = $$('[data-page]', el);
        if (!out || !btns.length) return;
        const intro = out.innerHTML;

        btns.forEach((b) => b.addEventListener('click', () => {
            const on = b.getAttribute('aria-pressed') !== 'true';
            btns.forEach((x) => x.setAttribute('aria-pressed', x === b && on ? 'true' : 'false'));
            const page = on ? b.dataset.page : '';
            const hits = [];
            items.forEach((it) => {
                const match = page !== '' && it.dataset.lxPages.split(/\s+/).includes(page);
                it.classList.toggle('is-hit', match);
                if (match) hits.push(it);
            });
            if (!page) {
                out.innerHTML = intro;
                return;
            }
            out.textContent = '';
            const p = doc.createElement('p');
            p.append('For ' + (b.dataset.label || b.textContent.trim().toLowerCase()) + ', add ' + hits.length + (hits.length === 1 ? ' type:' : ' types:'));
            out.appendChild(p);
            const ol = doc.createElement('ol');
            hits.forEach((it) => {
                const li = doc.createElement('li');
                const a = doc.createElement('a');
                a.href = '#' + it.id;
                a.textContent = itemTitle(it);
                li.appendChild(a);
                ol.appendChild(li);
            });
            out.appendChild(ol);
            if (b.dataset.note) {
                const tip = doc.createElement('p');
                tip.className = 'lx-pick-note';
                tip.textContent = b.dataset.note;
                out.appendChild(tip);
            }
            say(hits.length + ' types marked for this page');
        }));
    };

    /* Refresh helper: five yes or no questions about one page lead to merge,
       retire, park, rewrite, refresh or keep. The advice for each outcome is
       written in the article (data-rf blocks); this only picks which to show. */
    TOOLS.refresh = (el) => {
        const outs = $$('[data-rf]', el);
        if (!outs.length) return;
        const ans = (name) => {
            const checked = el.querySelector('input[name="' + name + '"]:checked');
            return checked ? checked.value : '';
        };
        const nextQ = $('[data-rf-next]', el);

        function decide() {
            const steps = ['rf-merge', 'rf-topic', 'rf-value', 'rf-intent', 'rf-facts'];
            const a = {};
            steps.forEach((s) => { a[s] = ans(s); });
            const ask = (name) => ({ res: 'ask', next: steps.indexOf(name) + 1 });
            if (!a['rf-merge']) return ask('rf-merge');
            if (a['rf-merge'] === 'y') return { res: 'merge' };
            if (!a['rf-topic']) return ask('rf-topic');
            if (!a['rf-value']) return ask('rf-value');
            if (a['rf-topic'] === 'n') return { res: a['rf-value'] === 'y' ? 'park' : 'retire' };
            if (a['rf-value'] === 'n') return { res: 'rewrite' };
            if (!a['rf-intent']) return ask('rf-intent');
            if (a['rf-intent'] === 'n') return { res: 'rewrite' };
            if (!a['rf-facts']) return ask('rf-facts');
            return { res: a['rf-facts'] === 'n' ? 'refresh' : 'keep' };
        }

        function show() {
            const d = decide();
            outs.forEach((o) => { o.hidden = o.dataset.rf !== d.res; });
            if (nextQ) nextQ.textContent = d.res === 'ask' ? String(d.next) : '';
            if (d.res !== 'ask') {
                const h = $('[data-rf="' + d.res + '"] .lx-result-h', el);
                say('Recommendation: ' + (h ? h.textContent : d.res));
            }
        }
        el.addEventListener('change', show);
        const clear = $('[data-rf-reset]', el);
        if (clear) {
            clear.addEventListener('click', () => {
                $$('input[type="radio"]', el).forEach((r) => { r.checked = false; });
                show();
                const first = $('input[type="radio"]', el);
                if (first) first.focus();
            });
        }
        show();
    };

    /* Contrast checker: the WCAG contrast ratio of two colours. The ratio is cut,
       never rounded up, to two decimals, so 4.499 shows as 4.49 and fails 4.5. */
    TOOLS.contrast = (el) => {
        const text = { fg: $('[data-cc="fg"]', el), bg: $('[data-cc="bg"]', el) };
        const pick = { fg: $('[data-cc-pick="fg"]', el), bg: $('[data-cc-pick="bg"]', el) };
        const ratioEl = $('[data-cc-ratio]', el);
        const preview = $('[data-cc-preview]', el);
        const err = $('[data-cc-err]', el);
        const rows = $$('[data-cc-min]', el);
        if (!text.fg || !text.bg || !ratioEl) return;

        const parse = (v) => {
            let s = String(v).trim().replace(/^#/, '');
            if (/^[0-9a-f]{3}$/i.test(s)) s = s.split('').map((c) => c + c).join('');
            return /^[0-9a-f]{6}$/i.test(s) ? '#' + s.toUpperCase() : null;
        };
        const lum = (hex) => {
            const c = [1, 3, 5].map((i) => parseInt(hex.slice(i, i + 2), 16) / 255)
                .map((v) => (v <= 0.04045 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4)));
            return 0.2126 * c[0] + 0.7152 * c[1] + 0.0722 * c[2];
        };

        function update(src) {
            ['fg', 'bg'].forEach((k) => {
                if (src === pick[k] && pick[k]) text[k].value = pick[k].value.toUpperCase();
            });
            const f = parse(text.fg.value);
            const b = parse(text.bg.value);
            if (err) err.hidden = Boolean(f && b);
            if (!f || !b) return;
            if (pick.fg && src !== pick.fg) pick.fg.value = f.toLowerCase();
            if (pick.bg && src !== pick.bg) pick.bg.value = b.toLowerCase();
            const l1 = lum(f);
            const l2 = lum(b);
            const r = (Math.max(l1, l2) + 0.05) / (Math.min(l1, l2) + 0.05);
            ratioEl.textContent = (Math.floor(r * 100) / 100).toFixed(2);
            if (preview) {
                preview.style.color = f;
                preview.style.backgroundColor = b;
            }
            rows.forEach((row) => {
                const ok = r >= Number(row.dataset.ccMin);
                row.classList.toggle('is-pass', ok);
                row.classList.toggle('is-fail', !ok);
                const v = $('[data-cc-verdict]', row);
                if (v) v.textContent = ok ? 'Pass' : 'Fail';
            });
        }

        [text.fg, text.bg, pick.fg, pick.bg].forEach((i) => {
            if (i) i.addEventListener('input', () => update(i));
        });
        const swap = $('[data-cc-swap]', el);
        if (swap) {
            swap.addEventListener('click', () => {
                const t = text.fg.value;
                text.fg.value = text.bg.value;
                text.bg.value = t;
                update(null);
                say('Colours swapped');
            });
        }
        update(null);
    };

    /* Calculators: number boxes in, rates out. data-calc names the set of sums,
       and each sum is also written out in the article beside the tool. The
       numbers are worked out in the page and are not stored or sent. */
    TOOLS.calc = (el) => {
        const num = (name) => {
            const i = $('[data-calc-in="' + name + '"]', el);
            if (!i || String(i.value).trim() === '') return NaN;
            const v = parseFloat(String(i.value).replace(/,/g, ''));
            return Number.isFinite(v) && v >= 0 ? v : NaN;
        };
        const fmt = (v, d) => v.toLocaleString('en', { maximumFractionDigits: d, minimumFractionDigits: 0 });
        const put = (name, v, suffix = '', d = 1) => {
            const o = $('[data-calc-out="' + name + '"]', el);
            if (!o) return;
            const ok = Number.isFinite(v);
            o.textContent = ok ? fmt(v, d) + suffix : 'Add the numbers above';
            o.classList.toggle('is-empty', !ok);
        };
        const ratio = (a, b, k = 100) => (Number.isFinite(a) && Number.isFinite(b) && b > 0 ? a / b * k : NaN);
        /* A part cannot be larger than its whole: show no rate rather than one above 100%. */
        const part = (a, b) => (a <= b ? ratio(a, b) : NaN);
        const verdict = $('[data-calc-verdict]', el);

        const SUMS = {
            /* Organic lead ledger: six rates from eight counts. */
            leads() {
                const visits = num('visits'), forms = num('forms'), valid = num('valid'), qual = num('qual');
                const all = num('all'), opps = num('opps'), pipe = num('pipe'), cost = num('cost');
                put('valid', part(valid, forms), '%');
                put('qualrate', part(qual, valid), '%');
                put('share', part(qual, all), '%');
                put('cpl', ratio(cost, qual, 1), '', 0);
                put('opprate', part(opps, qual), '%');
                put('pipe', ratio(pipe, visits, 1000), '', 0);
            },
            /* Two brands counted over the same number of answers: is the gap
               bigger than the 95 percent margin of error for a difference? */
            sample() {
                const n = num('n'), a = num('a'), b = num('b');
                const ok = Number.isFinite(n) && n >= 1 && Number.isFinite(a) && Number.isFinite(b) && a <= n && b <= n;
                const pa = ok ? a / n : NaN, pb = ok ? b / n : NaN;
                const gap = ok ? Math.abs(pa - pb) * 100 : NaN;
                const moe = ok ? 1.96 * Math.sqrt(pa * (1 - pa) / n + pb * (1 - pb) / n) * 100 : NaN;
                put('ra', pa * 100, '%');
                put('rb', pb * 100, '%');
                put('gap', gap, ' points');
                put('moe', moe, ' points');
                if (!verdict) return;
                if (!ok) {
                    verdict.textContent = Number.isFinite(n) && (a > n || b > n)
                        ? 'A brand cannot be named in more answers than you counted. Check the three numbers.'
                        : 'Type the three numbers to see whether the gap is larger than the margin of error.';
                } else if (n < 30) {
                    verdict.textContent = 'Fewer than 30 answers is too small a sample for this test. Count more answers before comparing the two brands.';
                } else if (gap > moe) {
                    verdict.textContent = 'The gap is larger than the margin of error, so it is unlikely to be noise alone. Check that it holds next month before you act on it.';
                } else {
                    verdict.textContent = 'The gap is inside the margin of error. Treat the two brands as level until you have counted more answers.';
                }
            },
        };
        const run = SUMS[el.dataset.calc];
        if (!run) return;
        el.addEventListener('input', run);
        const clear = $('[data-calc-reset]', el);
        if (clear) {
            clear.addEventListener('click', () => {
                $$('[data-calc-in]', el).forEach((i) => { i.value = ''; });
                run();
                const first = $('[data-calc-in]', el);
                if (first) first.focus();
            });
        }
        run();
    };

    /* TOOLS:END */

    $$('[data-lx-tool]').forEach((el) => {
        const fn = TOOLS[el.dataset.lxTool];
        if (fn) fn(el);
    });
})();
