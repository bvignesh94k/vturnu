/* VTurnU lead form: validation, country picker and submit.
 *
 * One script for every form on the site (form.lf). The server re-checks every
 * rule, so this exists to tell a visitor what is wrong while they are still
 * looking at the field, not to be trusted.
 */
(function () {
    'use strict';
    if (window.vtLeadInit) { window.vtLeadInit(); return; }

    /* National number lengths by dial code. Mirrors LEAD_PHONE_LENGTHS in
       includes/lead-guard.php, which the server enforces. */
    var PHONE = {
        '+91': [10, 10, /^[6-9]/], '+1': [10, 10, /^[2-9]/], '+44': [9, 10], '+61': [9, 9], '+65': [8, 8],
        '+971': [8, 9], '+966': [8, 9], '+974': [8, 8], '+60': [9, 10], '+62': [9, 12], '+234': [10, 10],
        '+254': [9, 9], '+27': [9, 9], '+261': [9, 9], '+7': [10, 10], '+49': [10, 11], '+33': [9, 9],
        '+39': [9, 10], '+34': [9, 9], '+31': [9, 9], '+64': [8, 10]
    };
    var EXAMPLES = {
        IN: '98765 43210', US: '415 555 0132', CA: '416 555 0132', GB: '7400 123456', AE: '50 123 4567', SA: '50 123 4567',
        QA: '3312 3456', SG: '8123 4567', MY: '12 345 6789', ID: '812 3456 7890', AU: '412 345 678', ZA: '71 234 5678',
        NG: '802 123 4567', KE: '712 345 678'
    };
    var TYPOS = { 'gmial.com': 'gmail.com', 'gmai.com': 'gmail.com', 'gnail.com': 'gmail.com', 'gmail.co': 'gmail.com', 'yaho.com': 'yahoo.com', 'yahooo.com': 'yahoo.com', 'hotmial.com': 'hotmail.com', 'outlok.com': 'outlook.com' };

    var track = function (event, data) {
        try { (window.dataLayer = window.dataLayer || []).push(Object.assign({ event: event }, data || {})); } catch (e) { /* analytics must never break a form */ }
    };

    function digitsOf(form) {
        var cc = form.elements.country_code.value;
        var raw = form.elements.mobile.value.trim();
        var d = raw.replace(/\D/g, '');
        var dd = cc.replace(/\D/g, '');
        if (raw.charAt(0) === '+' && dd && d.indexOf(dd) === 0) { d = d.slice(dd.length); }
        return d.replace(/^0+/, '');
    }

    /* One rule per field: returns an error message, or '' when the field is fine. */
    var RULES = {
        name: function (v) {
            if (v.length < 2 || !/[A-Za-zÀ-ɏऀ-෿؀-ۿ一-鿿]/.test(v)) { return 'Please enter your full name.'; }
            if (/\d|@|https?:|www\./i.test(v)) { return 'Please enter your name without numbers or links.'; }
            return '';
        },
        email: function (v) {
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v) || v.length > 120) { return 'Please enter a valid email address, for example you@company.com.'; }
            var domain = v.split('@')[1].toLowerCase();
            if (TYPOS[domain]) { return 'Did you mean ' + v.split('@')[0] + '@' + TYPOS[domain] + '?'; }
            return '';
        },
        mobile: function (v, form) {
            var d = digitsOf(form), dial = form.elements.country_code.value || '+91';
            if (!d) { return 'Please enter a phone or WhatsApp number we can reach you on.'; }
            var r = PHONE[dial] || [6, 13];
            if (d.length < r[0] || d.length > r[1] || (r[2] && !r[2].test(d)) || /^(\d)\1{5,}$/.test(d)) {
                return dial === '+91' ? 'Please enter a 10 digit mobile number starting with 6, 7, 8 or 9.' : 'That phone number does not look right for ' + dial + '. Please check it.';
            }
            return '';
        },
        company: function (v) {
            if (v.length < 2 || !/[A-Za-zÀ-ɏऀ-෿؀-ۿ一-鿿]/.test(v)) { return 'Please enter your company name, so we can prepare for the conversation.'; }
            return '';
        },
        service: function (v) { return v ? '' : 'Please choose what you need help with.'; },
        budget: function (v) { return v ? '' : 'Please choose a budget range.'; },
        message: function (v) {
            if (v.length > 2000) { return 'That message is a little long. Please keep it under 2,000 characters.'; }
            return '';
        },
        company_website: function (v) {
            if (!v) { return ''; }
            return /^(https?:\/\/)?([a-z0-9-]+\.)+[a-z]{2,}(\/\S*)?$/i.test(v) ? '' : 'Please enter a website address like company.com.';
        }
    };

    function fieldBox(form, name) { return form.querySelector('[data-field="' + name + '"]'); }

    function setError(form, name, msg) {
        var box = fieldBox(form, name);
        if (!box) { return; }
        var err = box.querySelector('.lf-err');
        var control = box.querySelector('input:not([type=hidden]), select, textarea');
        err.textContent = msg;
        box.classList.toggle('has-error', !!msg);
        if (control) {
            if (msg) { control.setAttribute('aria-invalid', 'true'); } else { control.removeAttribute('aria-invalid'); }
        }
        if (msg) {
            var more = box.closest('.lf-more');
            if (more && !more.open) { more.open = true; }
        }
    }

    function check(form, name) {
        var rule = RULES[name];
        if (!rule) { return ''; }
        var el = form.elements[name];
        if (!el) { return ''; }
        var msg = rule((el.value || '').trim(), form);
        setError(form, name, msg);
        return msg;
    }

    /* ------------------------------ country picker ------------------------------ */

    function buildPicker(form) {
        var wrap = form.querySelector('[data-cc]');
        if (!wrap) { return; }
        var btn = wrap.querySelector('[data-cc-btn]');
        var select = wrap.querySelector('[data-cc-select]');
        var isoField = wrap.querySelector('input[name=country_iso]');
        var flag = btn.querySelector('.lf-flag');
        var dialEl = btn.querySelector('.lf-dial');
        var phone = form.elements.mobile;
        select.tabIndex = -1;
        select.setAttribute('aria-hidden', 'true');
        var list = null, search = null, items = [], active = -1;

        var rows = Array.prototype.map.call(select.options, function (o, i) {
            return { index: i, dial: o.value, iso: o.getAttribute('data-iso'), name: o.text.replace(/^\+\d+\s*/, '') };
        });

        function choose(iso, silent) {
            var row = rows.filter(function (r) { return r.iso === iso; })[0];
            if (!row) { return; }
            select.selectedIndex = row.index;
            isoField.value = iso;
            flag.src = '/assets/img/flags/' + iso.toLowerCase() + '.svg';
            dialEl.textContent = row.dial;
            btn.setAttribute('aria-label', 'Country calling code, now ' + row.name + ' ' + row.dial);
            phone.placeholder = EXAMPLES[iso] || 'Phone number';
            if (!silent && phone.value) { check(form, 'mobile'); }
        }

        function close() {
            if (!list) { return; }
            list.hidden = true;
            btn.setAttribute('aria-expanded', 'false');
            document.removeEventListener('pointerdown', outside, true);
        }
        function outside(e) { if (!wrap.contains(e.target)) { close(); } }

        function render(filter) {
            var q = (filter || '').toLowerCase().replace(/^\+/, '');
            var frag = document.createDocumentFragment();
            items = [];
            var ul = list.querySelector('ul');
            ul.textContent = '';
            rows.forEach(function (r) {
                if (q && r.name.toLowerCase().indexOf(q) === -1 && r.dial.replace('+', '').indexOf(q) !== 0 && r.iso.toLowerCase() !== q) { return; }
                var li = document.createElement('li');
                li.setAttribute('role', 'option');
                li.id = form.id + '-cc-' + r.iso;
                li.setAttribute('data-iso', r.iso);
                if (r.iso === isoField.value) { li.setAttribute('aria-selected', 'true'); }
                var img = document.createElement('img');
                img.src = '/assets/img/flags/' + r.iso.toLowerCase() + '.svg';
                img.width = 22; img.height = 15; img.alt = ''; img.loading = 'lazy'; img.decoding = 'async';
                var nm = document.createElement('span'); nm.textContent = r.name;
                var dl = document.createElement('b'); dl.textContent = r.dial;
                li.appendChild(img); li.appendChild(nm); li.appendChild(dl);
                frag.appendChild(li);
                items.push(li);
            });
            ul.appendChild(frag);
            setActive(items.length ? Math.max(0, items.map(function (li) { return li.getAttribute('data-iso'); }).indexOf(isoField.value)) : -1, true);
        }

        function setActive(i, scroll) {
            if (active > -1 && items[active]) { items[active].classList.remove('is-active'); }
            active = i;
            if (i > -1 && items[i]) {
                items[i].classList.add('is-active');
                search.setAttribute('aria-activedescendant', items[i].id);
                if (scroll) { items[i].scrollIntoView({ block: 'nearest' }); }
            }
        }

        function open() {
            if (!list) {
                list = document.createElement('div');
                list.className = 'lf-cc-list';
                search = document.createElement('input');
                search.type = 'text'; search.className = 'lf-cc-search'; search.placeholder = 'Search country or code';
                search.setAttribute('aria-label', 'Search countries'); search.autocomplete = 'off';
                search.setAttribute('role', 'combobox'); search.setAttribute('aria-expanded', 'true');
                var ul = document.createElement('ul');
                ul.setAttribute('role', 'listbox'); ul.setAttribute('aria-label', 'Countries');
                list.appendChild(search); list.appendChild(ul);
                wrap.appendChild(list);
                search.addEventListener('input', function () { render(search.value); });
                search.addEventListener('keydown', function (e) {
                    if (e.key === 'ArrowDown') { e.preventDefault(); setActive(Math.min(items.length - 1, active + 1), true); }
                    else if (e.key === 'ArrowUp') { e.preventDefault(); setActive(Math.max(0, active - 1), true); }
                    else if (e.key === 'Enter') { e.preventDefault(); if (items[active]) { pick(items[active]); } }
                    else if (e.key === 'Escape') { e.preventDefault(); e.stopPropagation(); close(); btn.focus(); }
                    else if (e.key === 'Tab') { close(); }
                });
                ul.addEventListener('click', function (e) {
                    var li = e.target.closest('li');
                    if (li) { pick(li); }
                });
            }
            render('');
            search.value = '';
            list.hidden = false;
            btn.setAttribute('aria-expanded', 'true');
            document.addEventListener('pointerdown', outside, true);
            setTimeout(function () { search.focus(); }, 0);
        }

        function pick(li) {
            choose(li.getAttribute('data-iso'));
            close();
            btn.focus();
            phone.focus();
        }

        btn.addEventListener('click', function () { if (list && !list.hidden) { close(); } else { open(); } });
        btn.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') { e.preventDefault(); open(); }
        });
        select.addEventListener('change', function () {
            var o = select.options[select.selectedIndex];
            if (o) { choose(o.getAttribute('data-iso'), true); }
        });

        /* A pasted "+44 7400 123456" picks the country for the visitor. */
        phone.addEventListener('input', function () {
            var v = phone.value.trim();
            if (v.charAt(0) !== '+') { return; }
            var d = v.replace(/\D/g, '');
            var best = null;
            rows.forEach(function (r) {
                var rd = r.dial.replace(/\D/g, '');
                if (d.indexOf(rd) === 0 && (!best || rd.length > best.dial.replace(/\D/g, '').length)) { best = r; }
            });
            if (best && best.dial !== select.value) { choose(best.iso, true); }
        });
    }

    /* --------------------------------- the form --------------------------------- */

    function showAlert(form, msg) {
        var a = form.querySelector('.lf-alert');
        a.textContent = msg;
        a.hidden = !msg;
    }

    function firstInvalid(form) {
        return form.querySelector('.has-error input:not([type=hidden]), .has-error select, .has-error textarea');
    }

    function init(form) {
        if (form.dataset.lfReady) { return; }
        form.dataset.lfReady = '1';
        form.noValidate = true;
        var started = false;
        var touched = {};

        buildPicker(form);

        Object.keys(RULES).forEach(function (name) {
            var el = form.elements[name];
            if (!el) { return; }
            el.addEventListener('blur', function () { touched[name] = true; if (el.value.trim() || name !== 'company_website') { check(form, name); } });
            el.addEventListener('input', function () {
                if (touched[name] || fieldBox(form, name).classList.contains('has-error')) { check(form, name); }
            });
        });
        ['service', 'budget'].forEach(function (name) {
            form.elements[name].addEventListener('change', function () { check(form, name); });
        });
        form.addEventListener('focusin', function () {
            if (!started) { started = true; track('lead_form_start', { form_source: form.elements.lead_source.value }); }
        });

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (form.dataset.busy) { return; }
            showAlert(form, '');
            var bad = 0;
            Object.keys(RULES).forEach(function (name) { if (form.elements[name] && check(form, name)) { bad++; } });
            if (bad) {
                showAlert(form, bad === 1 ? 'One field needs your attention.' : bad + ' fields need your attention.');
                var f = firstInvalid(form);
                if (f) { f.focus({ preventScroll: false }); }
                track('lead_form_error', { form_source: form.elements.lead_source.value, fields: bad });
                return;
            }
            var btn = form.querySelector('.lf-submit');
            var label = btn.querySelector('.lf-submit-t');
            var original = label.textContent;
            form.dataset.busy = '1';
            btn.disabled = true;
            btn.classList.add('is-busy');
            label.textContent = 'Sending';

            var done = function () {
                delete form.dataset.busy;
                btn.disabled = false;
                btn.classList.remove('is-busy');
                label.textContent = original;
            };
            var send = function (token) {
                var tokenField = form.querySelector('.js-recaptcha-token');
                if (tokenField) { tokenField.value = token || ''; }
                var body = new FormData(form);
                /* How the visitor found us, kept by main.js, so the lead arrives with its source. */
                try { body.set('attribution', localStorage.getItem('vt_src') || ''); } catch (err) { /* storage blocked */ }
                fetch('/enquiry/', { method: 'POST', body: body, headers: { 'X-Requested-With': 'fetch' } })
                    .then(function (r) { return r.text(); })
                    .then(function (body) {
                        var data;
                        try { data = JSON.parse(body); } catch (err) {
                            var m = body.match(/\{[\s\S]*\}\s*$/);
                            try { data = m ? JSON.parse(m[0]) : { ok: false }; } catch (e2) { data = { ok: false }; }
                        }
                        if (data.ok) {
                            track('lead_form_success', { form_source: form.elements.lead_source.value });
                            window.location.href = data.redirect || '/thank-you/';
                            return;
                        }
                        done();
                        if (data.errors && Object.keys(data.errors).length) {
                            Object.keys(data.errors).forEach(function (n) { setError(form, n, data.errors[n]); });
                            showAlert(form, 'Please check the highlighted fields.');
                            var f = firstInvalid(form);
                            if (f) { f.focus(); }
                        } else {
                            showAlert(form, data.error || 'That did not go through. Please try again, or email connect@vturnu.com.');
                        }
                    })
                    .catch(function () {
                        done();
                        showAlert(form, 'We could not reach the server. Please check your connection and try again, or email connect@vturnu.com.');
                    });
            };
            if (window.vtRecaptcha) { window.vtRecaptcha('submit', send); } else { send(''); }
        });
    }

    function initAll() { document.querySelectorAll('form[data-lf]').forEach(init); }
    window.vtLeadInit = initAll;

    /* A form is set up the moment someone touches it, so nothing waits on idle time. */
    ['pointerdown', 'focusin', 'touchstart', 'keydown'].forEach(function (name) {
        document.addEventListener(name, function (e) {
            var f = e.target && e.target.closest && e.target.closest('form[data-lf]');
            if (f) { init(f); }
        }, true);
    });
    (window.requestIdleCallback || function (fn) { return setTimeout(fn, 2500); })(initAll, { timeout: 5000 });
})();
