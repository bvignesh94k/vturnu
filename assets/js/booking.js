/* Book a Meeting (/book-a-meeting/).
 *
 * The page works without this script: a drop-down of times in the zone the
 * server guessed from the visitor's IP. This turns it into a calendar with
 * the times in the visitor's own zone:
 * - the zone is the device's own clock, cross-checked with the IP guess; when
 *   they disagree the device wins and the other one is a click away;
 * - times outside 7 AM to 9 PM in that zone wait behind "Show all times";
 * - the evening hours meant for visitors outside India are offered only to
 *   zones outside India (the server checks this again);
 * - the soonest times sit on top, a live clock confirms the zone is right,
 *   and the chosen time is shown in India time too.
 */
(function () {
    'use strict';
    var dataEl = document.getElementById('bk-data');
    if (!dataEl) { return; }
    var D = JSON.parse(dataEl.textContent || '{}');
    document.documentElement.classList.add('js');

    var INDIA = D.india || ['Asia/Kolkata', 'Asia/Calcutta'];
    var isIndia = function (z) { return INDIA.indexOf(z) !== -1; };
    var validZone = function (z) {
        try { new Intl.DateTimeFormat('en-US', { timeZone: z }); return true; } catch (e) { return false; }
    };
    var deviceZone = '';
    try { deviceZone = Intl.DateTimeFormat().resolvedOptions().timeZone || ''; } catch (e) { deviceZone = ''; }

    /* Minutes east of UTC for a zone at a moment (ms). */
    var offsetMin = function (z, at) {
        var parts = {};
        new Intl.DateTimeFormat('en-US', { timeZone: z, hourCycle: 'h23', year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit' })
            .formatToParts(new Date(at)).forEach(function (p) { parts[p.type] = p.value; });
        var asUtc = Date.UTC(+parts.year, +parts.month - 1, +parts.day, +parts.hour % 24, +parts.minute, +parts.second);
        return Math.round((asUtc - Math.floor(at / 1000) * 1000) / 60000);
    };
    var gmt = function (z, at) {
        var o = offsetMin(z, at);
        if (o === 0) { return 'GMT'; }
        var a = Math.abs(o);
        return 'GMT' + (o < 0 ? '-' : '+') + Math.floor(a / 60) + (a % 60 ? ':' + String(a % 60).padStart(2, '0') : '');
    };
    var cityOf = function (z) { return isIndia(z) ? 'India' : z.split('/').pop().replace(/_/g, ' '); };
    var longName = function (z, at) {
        try {
            var p = new Intl.DateTimeFormat('en-US', { timeZone: z, timeZoneName: 'long' }).formatToParts(new Date(at));
            for (var i = 0; i < p.length; i++) { if (p[i].type === 'timeZoneName') { return p[i].value; } }
        } catch (e) { /* older browsers */ }
        return '';
    };
    var zoneLabel = function (z, at) {
        var ln = longName(z, at), city = cityOf(z);
        if (ln && ln.indexOf('GMT') !== 0 && ln.indexOf(city) === 0) { return ln + ' (' + gmt(z, at) + ')'; }
        return city + (ln && ln.indexOf('GMT') !== 0 ? ', ' + ln : '') + ' (' + gmt(z, at) + ')';
    };

    /* ---------------- The booked page: a countdown, and the time in this device's zone ---------------- */
    if (D.countdown) {
        var when = document.querySelector('.bk-when');
        if (!when) { return; }
        var start = +when.getAttribute('data-start') * 1000, end = +when.getAttribute('data-end') * 1000, zone = when.getAttribute('data-tz');
        var cd = when.querySelector('.bk-countdown');
        var tick = function () {
            var now = Date.now(), s = Math.round((start - now) / 60000);
            var text;
            if (now >= end) { text = 'This call has finished.'; }
            else if (now >= start) { text = 'Happening now. Join with the button below.'; }
            else if (s < 60) { text = 'Starts in ' + s + (s === 1 ? ' minute' : ' minutes'); }
            else if (s < 48 * 60) { var h = Math.floor(s / 60), m = s % 60; text = 'Starts in ' + h + (h === 1 ? ' hour' : ' hours') + (m ? ' ' + m + ' min' : ''); }
            else { var d = Math.floor(s / 1440); text = 'Starts in ' + d + ' days'; }
            cd.textContent = text;
            cd.hidden = false;
        };
        tick();
        setInterval(tick, 30000);
        /* Shown only when it adds something: not for the booking's own zone, nor for India, which the Chennai line covers. */
        if (deviceZone && zone && validZone(zone) && offsetMin(deviceZone, start) !== offsetMin(zone, start)
            && !(when.querySelector('.bk-when-host') && offsetMin(deviceZone, start) === offsetMin('Asia/Kolkata', start))) {
            var here = document.createElement('p');
            here.className = 'bk-when-host';
            here.textContent = 'That is ' + new Intl.DateTimeFormat(undefined, { timeZone: deviceZone, weekday: 'short', hour: 'numeric', minute: '2-digit' }).format(new Date(start))
                + ' where you are now (' + zoneLabel(deviceZone, start) + ')';
            when.insertBefore(here, cd);
        }
        return;
    }

    /* ---------------- Picking a time ---------------- */
    var form = document.getElementById('bk-form');
    var app = document.getElementById('bk-app');
    var tzSel = document.getElementById('bk-tz');
    var tzVal = document.getElementById('bk-tzval');
    var tzNote = document.getElementById('bk-tznote');
    var details = document.getElementById('bk-details');
    var picked = document.getElementById('bk-picked');
    var fallback = document.getElementById('bk-slot');
    var submit = document.getElementById('bk-submit');
    if (!form || !app || !tzSel) { return; }

    /* The script's own hidden slot field replaces the drop-down's. */
    var slotField = document.createElement('input');
    slotField.type = 'hidden';
    slotField.name = 'slot';
    form.appendChild(slotField);
    if (fallback) { fallback.removeAttribute('name'); fallback.required = false; }

    var tz = validZone(D.tz) ? D.tz : 'Asia/Kolkata';
    var showAll = false;
    var chosen = 0;
    var dayShown = '';
    var monthShown = '';
    var slots = (D.slots || []).map(function (s) { return { t: s[0], o: s[1] }; });

    var fmt = function (ts, opts, locale) { return new Intl.DateTimeFormat(locale, Object.assign({ timeZone: tz }, opts)).format(new Date(ts * 1000)); };
    var dayKey = function (ts) { return fmt(ts, { year: 'numeric', month: '2-digit', day: '2-digit' }, 'en-CA'); };
    var minutesOfDay = function (ts) {
        var p = {};
        new Intl.DateTimeFormat('en-US', { timeZone: tz, hourCycle: 'h23', hour: '2-digit', minute: '2-digit' }).formatToParts(new Date(ts * 1000)).forEach(function (x) { p[x.type] = x.value; });
        return (+p.hour % 24) * 60 + (+p.minute);
    };
    var timeText = function (ts) { return fmt(ts, { hour: 'numeric', minute: '2-digit' }); };
    var inDay = function (s) {
        var a = minutesOfDay(s.t), b = minutesOfDay(s.t + D.len * 60);
        return a >= D.day[0] * 60 && b <= D.day[1] * 60 && b > a;
    };
    var offered = function (s) { return s.o === 0 || !isIndia(tz); };
    var visible = function () { return slots.filter(function (s) { return offered(s) && (showAll || inDay(s)); }); };
    var hiddenCount = function () { return slots.filter(function (s) { return offered(s) && !inDay(s); }).length; };
    var byDay = function (list) {
        var map = {};
        list.forEach(function (s) { var k = dayKey(s.t); (map[k] = map[k] || []).push(s); });
        return map;
    };
    var nowTs = function () { return Math.floor(Date.now() / 1000); };
    var relDay = function (ts) {
        var k = dayKey(ts), today = dayKey(nowTs()), tomorrow = dayKey(nowTs() + 86400);
        if (k === today) { return 'Today'; }
        if (k === tomorrow) { return 'Tomorrow'; }
        return fmt(ts, { weekday: 'short', day: 'numeric', month: 'short' });
    };

    /* ---- the time zone: device first, IP as the second opinion ---- */
    var ensureOption = function (z) {
        for (var i = 0; i < tzSel.options.length; i++) { if (tzSel.options[i].value === z) { return; } }
        var group = tzSel.querySelector('optgroup');
        var opt = document.createElement('option');
        opt.value = z;
        opt.textContent = cityOf(z) + ' (' + gmt(z, Date.now()) + ')';
        (group || tzSel).insertBefore(opt, group ? group.firstChild : tzSel.firstChild);
    };
    var setZone = function (z) {
        tz = z;
        ensureOption(z);
        tzSel.value = z;
        tzVal.value = z;
        render();
        clock();
    };
    var note = function (html) {
        tzNote.innerHTML = html;
        tzNote.hidden = !html;
    };
    var esc = function (s) { return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); };

    if (!D.tzFromUrl && deviceZone && validZone(deviceZone) && deviceZone !== tz) {
        var now0 = Date.now();
        var ip = D.ipTz && validZone(D.ipTz) ? D.ipTz : '';
        tz = deviceZone;
        if (ip && offsetMin(ip, now0) !== offsetMin(deviceZone, now0)) {
            note('Times are in <strong>' + esc(zoneLabel(deviceZone, now0)) + '</strong>, from your device. Your connection looks like '
                + esc(zoneLabel(ip, now0)) + '. <button type="button" data-zone="' + esc(ip) + '">Use that instead</button>');
        }
    }
    tzSel.addEventListener('change', function () { note(''); setZone(tzSel.value); });
    tzNote.addEventListener('click', function (e) {
        var z = e.target && e.target.getAttribute && e.target.getAttribute('data-zone');
        if (z) { note(''); setZone(z); }
    });

    /* A live clock under the zone, so a wrong zone is obvious at a glance. */
    var clockEl = document.createElement('p');
    clockEl.className = 'bk-clock';
    tzSel.closest('.bk-tzform').appendChild(clockEl);
    var clock = function () { clockEl.textContent = 'Your time now: ' + fmt(nowTs(), { weekday: 'short', hour: 'numeric', minute: '2-digit' }); };
    setInterval(clock, 30000);

    /* ---- drawing the calendar and the times ---- */
    var el = function (tag, cls, text) {
        var n = document.createElement(tag);
        if (cls) { n.className = cls; }
        if (text !== undefined) { n.textContent = text; }
        return n;
    };

    var choose = function (s, focus) {
        chosen = s.t;
        slotField.value = String(s.t);
        dayShown = dayKey(s.t);
        monthShown = dayShown.slice(0, 7);
        render();
        details.classList.remove('is-waiting');
        var endTs = s.t + D.len * 60;
        var line = fmt(s.t, { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) + ', ' + timeText(s.t) + ' to ' + timeText(endTs);
        var sub = zoneLabel(tz, s.t * 1000) + (isIndia(tz) ? '' : ' · ' + new Intl.DateTimeFormat('en-US', { timeZone: 'Asia/Kolkata', hour: 'numeric', minute: '2-digit' }).format(new Date(s.t * 1000)) + ' in Chennai, India, where our team is');
        picked.innerHTML = '<strong>' + esc(line) + '</strong>' + esc(sub) + '<br><button type="button" id="bk-change">Change the time</button>';
        picked.hidden = false;
        document.getElementById('bk-change').addEventListener('click', function () {
            app.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
        if (D.recaptcha) { loadRecaptcha(); }
        if (focus) {
            var first = form.querySelector('#bk-name');
            var target = first && !first.value ? first : submit;
            details.scrollIntoView({ behavior: 'smooth', block: 'start' });
            setTimeout(function () { try { target.focus({ preventScroll: true }); } catch (e) { target.focus(); } }, 350);
        }
    };

    var render = function () {
        app.innerHTML = '';
        app.hidden = false;
        var list = visible();
        if (!list.length && !showAll && hiddenCount() > 0) {
            showAll = true;
            list = visible();
        }
        if (!list.length) {
            app.appendChild(el('p', 'bk-empty', 'There are no open times in the coming weeks. Please email connect@vturnu.com and we will find one.'));
            return;
        }
        var days = byDay(list);
        var keys = Object.keys(days).sort();
        if (!days[dayShown]) { dayShown = chosen && days[dayKey(chosen)] ? dayKey(chosen) : keys[0]; }
        if (monthShown && !keys.some(function (k) { return k.slice(0, 7) === monthShown; })) { monthShown = ''; }
        if (chosen && !list.some(function (s) { return s.t === chosen; })) {
            chosen = 0;
            slotField.value = '';
            details.classList.add('is-waiting');
            picked.hidden = true;
        }

        /* soonest times */
        var quick = el('div', 'bk-quick');
        quick.appendChild(el('span', '', 'Soonest:'));
        list.slice(0, 3).forEach(function (s) {
            var b = el('button', 'bk-chip', relDay(s.t) + ', ' + timeText(s.t));
            b.type = 'button';
            b.addEventListener('click', function () { choose(s, true); });
            quick.appendChild(b);
        });
        app.appendChild(quick);

        /* the month */
        var cal = el('div', 'bk-calendar');
        var month = monthShown || dayShown.slice(0, 7);
        var firstMonth = keys[0].slice(0, 7), lastMonth = keys[keys.length - 1].slice(0, 7);
        if (month < firstMonth) { month = firstMonth; }
        if (month > lastMonth) { month = lastMonth; }
        var y = +month.slice(0, 4), mo = +month.slice(5, 7);
        var head = el('div', 'bk-cal-head');
        var prev = el('button', 'bk-nav', '‹');
        prev.type = 'button';
        prev.setAttribute('aria-label', 'Previous month');
        prev.disabled = month <= firstMonth;
        var next = el('button', 'bk-nav', '›');
        next.type = 'button';
        next.setAttribute('aria-label', 'Next month');
        next.disabled = month >= lastMonth;
        var shift = function (n) {
            var d = new Date(Date.UTC(y, mo - 1 + n, 1));
            monthShown = d.getUTCFullYear() + '-' + String(d.getUTCMonth() + 1).padStart(2, '0');
            var inMonth = keys.filter(function (k) { return k.slice(0, 7) === monthShown; });
            if (inMonth.length) { dayShown = inMonth[0]; }
            render();
        };
        prev.addEventListener('click', function () { shift(-1); });
        next.addEventListener('click', function () { shift(1); });
        head.appendChild(prev);
        head.appendChild(el('strong', '', new Intl.DateTimeFormat(undefined, { month: 'long', year: 'numeric', timeZone: 'UTC' }).format(new Date(Date.UTC(y, mo - 1, 1)))));
        head.appendChild(next);
        cal.appendChild(head);
        var grid = el('div', 'bk-grid7');
        grid.setAttribute('role', 'group');
        grid.setAttribute('aria-label', 'Days with open times');
        ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'].forEach(function (d) { grid.appendChild(el('div', 'bk-dow', d)); });
        var lead = (new Date(Date.UTC(y, mo - 1, 1)).getUTCDay() + 6) % 7;
        for (var i = 0; i < lead; i++) { grid.appendChild(el('span', '')); }
        var daysIn = new Date(Date.UTC(y, mo, 0)).getUTCDate();
        var today = dayKey(nowTs());
        for (var d = 1; d <= daysIn; d++) {
            var k = month + '-' + String(d).padStart(2, '0');
            var b = el('button', 'bk-day' + (days[k] ? ' is-open' : '') + (k === dayShown ? ' is-sel' : '') + (k === today ? ' is-today' : ''), String(d));
            b.type = 'button';
            if (days[k]) {
                var label = new Intl.DateTimeFormat(undefined, { weekday: 'long', day: 'numeric', month: 'long', timeZone: 'UTC' }).format(new Date(Date.UTC(y, mo - 1, d)));
                b.setAttribute('aria-label', label + ', ' + days[k].length + (days[k].length === 1 ? ' time' : ' times') + ' open');
                b.setAttribute('aria-pressed', k === dayShown ? 'true' : 'false');
                (function (key) { b.addEventListener('click', function () { dayShown = key; monthShown = key.slice(0, 7); render(); }); })(k);
            } else {
                b.disabled = true;
                b.setAttribute('aria-hidden', 'true');
            }
            grid.appendChild(b);
        }
        cal.appendChild(grid);
        app.appendChild(cal);

        /* the day's times */
        var times = el('div', 'bk-times');
        var dayList = days[dayShown] || [];
        var heading = el('h3', '', dayList.length ? fmt(dayList[0].t, { weekday: 'long', day: 'numeric', month: 'long' }, undefined) : '');
        heading.setAttribute('aria-live', 'polite');
        times.appendChild(heading);
        var tl = el('div', 'bk-time-list');
        dayList.forEach(function (s) {
            var b = el('button', 'bk-slot' + (s.t === chosen ? ' is-sel' : ''));
            b.type = 'button';
            b.appendChild(document.createTextNode(timeText(s.t)));
            if (!inDay(s)) { b.appendChild(el('small', '', minutesOfDay(s.t) < D.day[0] * 60 ? 'early' : 'late')); }
            b.setAttribute('aria-pressed', s.t === chosen ? 'true' : 'false');
            b.addEventListener('click', function () { choose(s, true); });
            tl.appendChild(b);
        });
        times.appendChild(tl);
        var extra = hiddenCount();
        if (extra > 0) {
            var t = el('label', 'bk-toggle');
            var cb = el('input');
            cb.type = 'checkbox';
            cb.checked = showAll;
            cb.addEventListener('change', function () { showAll = cb.checked; render(); });
            t.appendChild(cb);
            t.appendChild(document.createTextNode(showAll ? 'Showing times outside 7 AM to 9 PM too' : 'Show all times (' + extra + ' more outside 7 AM to 9 PM your time)'));
            times.appendChild(t);
        }
        app.appendChild(times);
    };

    /* ---- sending ---- */
    var rcLoading = false;
    var loadRecaptcha = function () {
        if (rcLoading || window.grecaptcha || !D.recaptcha) { return; }
        rcLoading = true;
        var s = document.createElement('script');
        s.src = 'https://www.google.com/recaptcha/api.js?render=' + encodeURIComponent(D.recaptcha);
        s.async = true;
        document.head.appendChild(s);
    };
    var withRecaptcha = function (cb) {
        if (!D.recaptcha) { cb(''); return; }
        var done = false;
        var finish = function (t) { if (!done) { done = true; cb(t || ''); } };
        loadRecaptcha();
        setTimeout(function () { finish(''); }, 5000);
        (function wait(n) {
            if (window.grecaptcha && window.grecaptcha.execute) {
                window.grecaptcha.ready(function () { window.grecaptcha.execute(D.recaptcha, { action: 'booking' }).then(finish, function () { finish(''); }); });
            } else if (n > 0) {
                setTimeout(function () { wait(n - 1); }, 100);
            }
        })(50);
    };
    var sending = false;
    form.addEventListener('submit', function (e) {
        if (sending) { e.preventDefault(); return; }
        if (!slotField.value) {
            e.preventDefault();
            app.scrollIntoView({ behavior: 'smooth', block: 'start' });
            return;
        }
        var bad = null;
        ['bk-name', 'bk-email'].forEach(function (id) {
            var f = document.getElementById(id);
            if (f && !bad && (!f.value.trim() || (f.type === 'email' && !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(f.value.trim())))) { bad = f; }
        });
        if (bad) {
            e.preventDefault();
            bad.focus();
            bad.setAttribute('aria-invalid', 'true');
            return;
        }
        e.preventDefault();
        sending = true;
        submit.disabled = true;
        submit.textContent = D.mode === 'move' ? 'Moving your call...' : 'Booking your call...';
        withRecaptcha(function (token) {
            document.getElementById('bk-rc').value = token;
            form.submit();
        });
    });

    /* Where this visit came from, for the CRM: the site's own record if there is one, else this visit. */
    try {
        var attr = window.localStorage ? localStorage.getItem('vt_src') : '';
        if (!attr) {
            var q = {};
            new URLSearchParams(location.search).forEach(function (v, k) {
                if (/^(utm_(source|medium|campaign|term|content)|gclid|gbraid|wbraid|msclkid|fbclid|li_fat_id|ttclid)$/.test(k)) { q[k] = v.slice(0, 150); }
            });
            var touch = { at: new Date().toISOString(), landing: location.pathname, ref: document.referrer && document.referrer.indexOf(location.origin) !== 0 ? document.referrer : '', q: q };
            attr = JSON.stringify({ first: touch, last: touch, pages: 1 });
        }
        document.getElementById('bk-attr').value = attr.slice(0, 7900);
    } catch (e) { /* storage blocked */ }

    /* A personal link reports that it was looked at, after a few seconds on screen (link scanners do not stay). */
    if (D.seen) {
        setTimeout(function () {
            if (document.visibilityState !== 'hidden' && window.fetch) { fetch(D.seen, { method: 'POST', keepalive: true }); }
        }, 3000);
    }

    try {
        details.classList.add('is-waiting');
        setZone(tz);
        if (D.chosen) {
            var again = slots.filter(function (s) { return s.t === D.chosen; })[0];
            if (again && offered(again)) { choose(again, false); }
        }
    } catch (err) {
        /* Something in this browser failed: fall back to the plain form, which works on its own. */
        document.documentElement.classList.remove('js');
        app.hidden = true;
        details.classList.remove('is-waiting');
        if (fallback) { fallback.setAttribute('name', 'slot'); fallback.required = true; }
        slotField.parentNode.removeChild(slotField);
        if (window.console) { console.error('[booking]', err); }
    }
})();
