/* Book a Meeting (/book-a-meeting/).
 *
 * The page works without this script: a zone drop-down and a list of times.
 * This turns it into a Calendly-style flow:
 * - the meeting on the left, a month calendar with the active time zone under
 *   it, and once a day is picked, that day's times in a third column; picking
 *   a time shows Next, which opens the details step (phones get one step per
 *   screen);
 * - the zone starts as the one the visitor's IP points to (Vercel's geo-IP is
 *   city level, so San Jose is Pacific Time), named the way people say it,
 *   with its clock; a searchable picker covers every zone by city, country,
 *   zone name, abbreviation or GMT offset, and when the device clock is in
 *   another zone a quiet hint offers to switch;
 * - every zone gets the working hours on its own clock (9:30 AM to 6:30 PM,
 *   Monday to Friday by default), from the next half hour on, worked out here
 *   from the free grid the server sends. The server checks the same rule
 *   again when the booking is made (booking_zone_slots in includes/booking.php).
 */
(function () {
    'use strict';

    /* ------------------------------------------------------------------
       Time zone maths (no DOM; tests/booking/parity.mjs runs these in Node)
       ------------------------------------------------------------------ */
    var ALIAS = {
        'Asia/Calcutta': 'Asia/Kolkata', 'Asia/Saigon': 'Asia/Ho_Chi_Minh', 'Asia/Katmandu': 'Asia/Kathmandu', 'Asia/Rangoon': 'Asia/Yangon',
        'Asia/Dacca': 'Asia/Dhaka', 'Asia/Thimbu': 'Asia/Thimphu', 'Asia/Ujung_Pandang': 'Asia/Makassar', 'Asia/Chongqing': 'Asia/Shanghai',
        'Asia/Harbin': 'Asia/Shanghai', 'Europe/Kiev': 'Europe/Kyiv', 'Europe/Uzhgorod': 'Europe/Kyiv', 'Europe/Zaporozhye': 'Europe/Kyiv',
        'America/Buenos_Aires': 'America/Argentina/Buenos_Aires', 'America/Indianapolis': 'America/Indiana/Indianapolis',
        'America/Louisville': 'America/Kentucky/Louisville', 'America/Montreal': 'America/Toronto', 'America/Godthab': 'America/Nuuk',
        'Atlantic/Faeroe': 'Atlantic/Faroe', 'Pacific/Truk': 'Pacific/Chuuk', 'Pacific/Ponape': 'Pacific/Pohnpei',
        'US/Pacific': 'America/Los_Angeles', 'US/Mountain': 'America/Denver', 'US/Central': 'America/Chicago', 'US/Eastern': 'America/New_York',
        'US/Arizona': 'America/Phoenix', 'US/Hawaii': 'Pacific/Honolulu', 'US/Alaska': 'America/Anchorage',
        'Etc/UTC': 'UTC', 'Etc/GMT': 'UTC', 'GMT': 'UTC', 'Universal': 'UTC', 'Zulu': 'UTC', 'Etc/Universal': 'UTC'
    };
    var canon = function (z) { return ALIAS[z] || z; };
    var validZone = function (z) {
        if (!z) { return false; }
        try { new Intl.DateTimeFormat('en-US', { timeZone: z }); return true; } catch (e) { return false; }
    };
    var WD = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    var partsFmt = {};
    /* The wall clock in a zone at a moment (ms): {y, m, d, h, mi, s, wd}, wd 1 = Monday. */
    var wall = function (z, ms) {
        var f = partsFmt[z] || (partsFmt[z] = new Intl.DateTimeFormat('en-US', { timeZone: z, hourCycle: 'h23', weekday: 'short', year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit' }));
        var p = {};
        f.formatToParts(new Date(ms)).forEach(function (x) { p[x.type] = x.value; });
        return { y: +p.year, m: +p.month, d: +p.day, h: +p.hour % 24, mi: +p.minute, s: +p.second, wd: WD.indexOf(p.weekday) + 1 };
    };
    /* Minutes east of UTC for a zone at a moment (ms). */
    var offsetMin = function (z, ms) {
        var w = wall(z, ms);
        return Math.round((Date.UTC(w.y, w.m - 1, w.d, w.h, w.mi, w.s) - Math.floor(ms / 1000) * 1000) / 60000);
    };
    /* The moment (ms) a wall-clock time happens in a zone, or NaN when the clock skips it (daylight saving). */
    var zoned = function (z, y, m, d, h, mi) {
        var guess = Date.UTC(y, m - 1, d, h, mi);
        var t = guess - offsetMin(z, guess) * 60000;
        var t2 = guess - offsetMin(z, t) * 60000;
        if (t2 !== t) { t = t2; }
        var w = wall(z, t);
        return w.y === y && w.m === m && w.d === d && w.h === h && w.mi === mi ? t : NaN;
    };
    var pad = function (n) { return (n < 10 ? '0' : '') + n; };
    var ymd = function (y, m, d) { return y + '-' + pad(m) + '-' + pad(d); };
    var addDays = function (day, n) {
        var t = new Date(Date.UTC(day.y, day.m - 1, day.d + n));
        return { y: t.getUTCFullYear(), m: t.getUTCMonth() + 1, d: t.getUTCDate() };
    };
    var isoWeekday = function (day) { return (new Date(Date.UTC(day.y, day.m - 1, day.d)).getUTCDay() + 6) % 7 + 1; };

    /**
     * Every start time in the working hours of zone z still ahead, as
     * [{t: unix seconds, free: bool}]: their own clock, their working days,
     * after now and the notice, inside the window. free is false where the
     * call would overlap something already on the calendar (someone booked
     * it, or the host is busy), and the page shows those struck through;
     * times kept clear only as the buffer around a call are left out.
     * R = {grid, len, step, hours, days, notice}.
     */
    var zoneTimes = function (R, z, nowSec) {
        var g = R.grid, out = [], seen = {};
        var from = nowSec + Math.max(1, (R.notice || 0) * 60);
        var first = wall(z, nowSec * 1000), last = wall(z, g.until * 1000);
        var lastKey = ymd(last.y, last.m, last.d);
        for (var day = { y: first.y, m: first.m, d: first.d }, n = 0; ymd(day.y, day.m, day.d) <= lastKey && n < 400; day = addDays(day, 1), n++) {
            if (R.days.indexOf(isoWeekday(day)) === -1) { continue; }
            for (var r = 0; r < R.hours.length; r++) {
                for (var mn = R.hours[r][0]; mn + R.len <= R.hours[r][1]; mn += R.step) {
                    var ms = zoned(z, day.y, day.m, day.d, Math.floor(mn / 60), mn % 60);
                    if (isNaN(ms)) { continue; }
                    var t = Math.round(ms / 1000);
                    if (t < from || t < g.t0 || t >= g.until || (t - g.t0) % g.step !== 0 || seen[t]) { continue; }
                    var bit = g.bits.charAt((t - g.t0) / g.step);
                    /* '2': only the buffer around another call is in the way; not offered and not shown. */
                    if (bit === '2') { continue; }
                    seen[t] = 1;
                    out.push({ t: t, free: bit === '1' });
                }
            }
        }
        return out.sort(function (a, b) { return a.t - b.t; });
    };
    /* The times that can be booked, as the server's booking_zone_slots(). */
    var zoneSlots = function (R, z, nowSec) {
        return zoneTimes(R, z, nowSec).filter(function (x) { return x.free; }).map(function (x) { return x.t; });
    };

    var api = { zoneSlots: zoneSlots, zoneTimes: zoneTimes, zoned: zoned, wall: wall, offsetMin: offsetMin, canon: canon };
    if (typeof window !== 'undefined') { window.VTBooking = api; }
    if (typeof document === 'undefined') { return; }

    var dataEl = document.getElementById('bk-data');
    if (!dataEl) { return; }
    var D = JSON.parse(dataEl.textContent || '{}');

    /* ------------------------------------------------------------------
       Naming zones the way people say them
       ------------------------------------------------------------------ */
    var ZONES = D.zones || {};
    var NAMES = {
        'America/Los_Angeles': 'Pacific Time (US & Canada)',
        'America/Denver': 'Mountain Time (US & Canada)',
        'America/Chicago': 'Central Time (US & Canada)',
        'America/New_York': 'Eastern Time (US & Canada)',
        'America/Phoenix': 'Arizona Time',
        'America/Anchorage': 'Alaska Time',
        'Pacific/Honolulu': 'Hawaii Time',
        'America/Halifax': 'Atlantic Time (Canada)',
        'America/St_Johns': 'Newfoundland Time',
        'America/Regina': 'Saskatchewan Time',
        'Asia/Kolkata': 'India Standard Time (IST)',
        'Europe/London': 'UK Time (London)',
        'UTC': 'UTC (Coordinated Universal Time)'
    };
    var US_CA = ['America/Los_Angeles', 'America/Denver', 'America/Chicago', 'America/New_York', 'America/Phoenix', 'America/Anchorage', 'Pacific/Honolulu', 'America/Halifax', 'America/St_Johns', 'America/Regina'];
    /* Extra words people search with: abbreviations, states, big cities that share a zone. */
    var WORDS = {
        'America/Los_Angeles': 'pst pdt pt pacific california san jose san francisco silicon valley los angeles seattle portland las vegas san diego vancouver',
        'America/Denver': 'mst mdt mt mountain colorado utah salt lake albuquerque calgary edmonton',
        'America/Phoenix': 'mst arizona tucson scottsdale',
        'America/Chicago': 'cst cdt ct central texas dallas houston austin san antonio minneapolis kansas city nashville new orleans winnipeg',
        'America/New_York': 'est edt et eastern boston miami atlanta washington dc philadelphia charlotte orlando detroit toronto montreal ottawa',
        'America/Anchorage': 'akst akdt alaska',
        'Pacific/Honolulu': 'hst hawaii',
        'America/Halifax': 'ast adt atlantic nova scotia',
        'America/St_Johns': 'nst ndt newfoundland',
        'America/Regina': 'saskatchewan saskatoon',
        'Asia/Kolkata': 'ist india mumbai bombay delhi new delhi bangalore bengaluru chennai madras hyderabad pune kolkata calcutta ahmedabad',
        'Europe/London': 'gmt bst uk britain great britain england scotland wales manchester birmingham edinburgh',
        'Europe/Dublin': 'ireland', 'Europe/Lisbon': 'wet west portugal', 'Europe/Paris': 'cet cest france', 'Europe/Berlin': 'cet cest germany frankfurt munich',
        'Europe/Madrid': 'cet cest spain barcelona', 'Europe/Rome': 'cet cest italy milan', 'Europe/Amsterdam': 'cet cest netherlands holland',
        'Europe/Brussels': 'cet cest belgium', 'Europe/Zurich': 'cet cest switzerland geneva', 'Europe/Vienna': 'cet cest austria',
        'Europe/Stockholm': 'cet cest sweden', 'Europe/Oslo': 'cet cest norway', 'Europe/Copenhagen': 'cet cest denmark', 'Europe/Warsaw': 'cet cest poland',
        'Europe/Prague': 'cet cest czech', 'Europe/Athens': 'eet eest greece', 'Europe/Helsinki': 'eet eest finland', 'Europe/Kyiv': 'eet eest ukraine kiev',
        'Europe/Bucharest': 'eet eest romania', 'Europe/Istanbul': 'trt turkey ankara', 'Europe/Moscow': 'msk russia saint petersburg',
        'Asia/Dubai': 'gst gulf uae emirates abu dhabi sharjah', 'Asia/Riyadh': 'ast arabia ksa saudi jeddah', 'Asia/Qatar': 'ast doha',
        'Asia/Kuwait': 'ast', 'Asia/Bahrain': 'ast manama', 'Asia/Muscat': 'gst oman', 'Asia/Jerusalem': 'israel tel aviv', 'Asia/Tehran': 'irst iran',
        'Asia/Karachi': 'pkt pakistan lahore islamabad', 'Asia/Dhaka': 'bangladesh', 'Asia/Kathmandu': 'npt nepal', 'Asia/Colombo': 'sri lanka',
        'Asia/Bangkok': 'ict thailand', 'Asia/Ho_Chi_Minh': 'ict vietnam saigon hanoi', 'Asia/Jakarta': 'wib indonesia', 'Asia/Kuala_Lumpur': 'myt malaysia',
        'Asia/Singapore': 'sgt', 'Asia/Manila': 'pht philippines', 'Asia/Hong_Kong': 'hkt', 'Asia/Shanghai': 'cst china beijing shenzhen guangzhou',
        'Asia/Taipei': 'taiwan', 'Asia/Tokyo': 'jst japan osaka', 'Asia/Seoul': 'kst korea', 'Australia/Sydney': 'aest aedt new south wales canberra',
        'Australia/Melbourne': 'aest aedt victoria', 'Australia/Brisbane': 'aest queensland', 'Australia/Adelaide': 'acst acdt south australia',
        'Australia/Darwin': 'acst northern territory', 'Australia/Perth': 'awst western australia', 'Pacific/Auckland': 'nzst nzdt new zealand wellington',
        'Africa/Johannesburg': 'sast south africa cape town', 'Africa/Lagos': 'wat nigeria abuja', 'Africa/Nairobi': 'eat kenya', 'Africa/Cairo': 'eet egypt',
        'Africa/Casablanca': 'morocco', 'America/Sao_Paulo': 'brt brazil rio', 'America/Argentina/Buenos_Aires': 'art argentina',
        'America/Mexico_City': 'mexico', 'America/Bogota': 'cot colombia', 'America/Lima': 'pet peru', 'America/Santiago': 'clt chile',
        'America/Toronto': 'eastern ontario', 'America/Vancouver': 'pacific british columbia'
    };
    var CC_WORDS = { US: 'usa us america united states', GB: 'uk', AE: 'uae', SA: 'ksa', KR: 'south korea', CZ: 'czechia', NL: 'holland', CH: 'swiss' };
    var region = null;
    try { region = new Intl.DisplayNames(['en'], { type: 'region' }); } catch (e) { region = null; }
    var countryName = function (cc) {
        if (!cc) { return ''; }
        try { return region ? region.of(cc) || cc : cc; } catch (e) { return cc; }
    };
    var cityOf = function (z) { return z === 'UTC' ? 'UTC' : z.split('/').pop().replace(/_/g, ' '); };
    var zoneName = function (z, ms, style) {
        try {
            var p = new Intl.DateTimeFormat('en-US', { timeZone: z, timeZoneName: style }).formatToParts(new Date(ms));
            for (var i = 0; i < p.length; i++) { if (p[i].type === 'timeZoneName') { return p[i].value; } }
        } catch (e) { /* this browser does not know the style */ }
        return '';
    };
    var gmt = function (z, ms) {
        var o = offsetMin(z, ms);
        if (o === 0) { return 'GMT'; }
        var a = Math.abs(o);
        return 'GMT' + (o < 0 ? '-' : '+') + Math.floor(a / 60) + (a % 60 ? ':' + pad(a % 60) : '');
    };
    /* "Pacific Time (US & Canada)", "Eastern Time (Detroit)", "Dubai, United Arab Emirates", "Singapore". */
    var label = function (z, ms) {
        z = canon(z);
        if (NAMES[z]) { return NAMES[z]; }
        var cc = ZONES[z] || '', city = cityOf(z);
        if (cc === 'US' || cc === 'CA') {
            var gen = zoneName(z, ms, 'longGeneric');
            if (gen && gen.indexOf('GMT') !== 0) { return gen + ' (' + city + ')'; }
        }
        var cn = countryName(cc);
        return !cn || cn === city || cn.indexOf(city) === 0 ? city : city + ', ' + cn;
    };
    /* 12-hour clock unless the visitor switched to 24 hours in the zone picker. */
    var h24 = false;
    try { h24 = window.localStorage && localStorage.getItem('bk_h24') === '1'; } catch (e) { h24 = false; }
    var timeFmt = {};
    var timeOf = function (z, ms) {
        var k = z + (h24 ? '|24' : '|12');
        var f = timeFmt[k] || (timeFmt[k] = new Intl.DateTimeFormat(h24 ? 'en-GB' : 'en-US', { timeZone: z, hour: 'numeric', minute: '2-digit', hour12: !h24 }));
        return f.format(new Date(ms));
    };
    var dateOf = function (z, ms, opts) { return new Intl.DateTimeFormat('en-US', Object.assign({ timeZone: z }, opts)).format(new Date(ms)); };

    var el = function (tag, cls, text) {
        var n = document.createElement(tag);
        if (cls) { n.className = cls; }
        if (text !== undefined) { n.textContent = text; }
        return n;
    };
    var ico = function (name) { var s = el('span', 'bk-ico bk-ico--' + name); s.setAttribute('aria-hidden', 'true'); return s; };

    var deviceZone = '';
    try { deviceZone = canon(Intl.DateTimeFormat().resolvedOptions().timeZone || ''); } catch (e) { deviceZone = ''; }
    if (!validZone(deviceZone)) { deviceZone = ''; }

    /* ------------------------------------------------------------------
       The booked page: a countdown, and the time where this device is
       ------------------------------------------------------------------ */
    if (D.countdown) {
        var when = document.querySelector('.bk-when');
        if (!when) { return; }
        var start = +when.getAttribute('data-start') * 1000, end = +when.getAttribute('data-end') * 1000, zone = when.getAttribute('data-tz');
        var cd = when.querySelector('.bk-countdown');
        var tick = function () {
            var now = Date.now(), s = Math.round((start - now) / 60000), text;
            if (now >= end) { text = 'This call has finished.'; }
            else if (now >= start) { text = 'Happening now. Join with the button below.'; }
            else if (s < 60) { text = 'Starts in ' + s + (s === 1 ? ' minute' : ' minutes'); }
            else if (s < 48 * 60) { var h = Math.floor(s / 60), mm = s % 60; text = 'Starts in ' + h + (h === 1 ? ' hour' : ' hours') + (mm ? ' ' + mm + ' min' : ''); }
            else { text = 'Starts in ' + Math.floor(s / 1440) + ' days'; }
            cd.textContent = text;
            cd.hidden = false;
        };
        tick();
        setInterval(tick, 30000);
        /* Only when it adds something: not for the booking's own zone, nor for India, which the Chennai line covers. */
        if (deviceZone && zone && validZone(zone) && offsetMin(deviceZone, start) !== offsetMin(zone, start)
            && !(when.querySelector('.bk-when-host') && offsetMin(deviceZone, start) === offsetMin('Asia/Kolkata', start))) {
            var here = el('p', 'bk-when-host', 'That is ' + dateOf(deviceZone, start, { weekday: 'short' }) + ' ' + timeOf(deviceZone, start) + ' where you are now (' + label(deviceZone, start) + ')');
            when.insertBefore(here, cd);
        }
        return;
    }

    /* ------------------------------------------------------------------
       Picking a time
       ------------------------------------------------------------------ */
    var form = document.getElementById('bk-form');
    var app = document.getElementById('bk-app');
    var card = document.getElementById('bk-card');
    var tzVal = document.getElementById('bk-tzval');
    var details = document.getElementById('bk-details');
    var summary = document.getElementById('bk-summary');
    var fallback = document.getElementById('bk-slot');
    var submit = document.getElementById('bk-submit');
    if (!form || !app || !card || !tzVal || !D.grid) { return; }
    document.documentElement.classList.add('js');

    /* The script's own hidden slot field replaces the drop-down's. */
    var slotField = el('input');
    slotField.type = 'hidden';
    slotField.name = 'slot';
    form.appendChild(slotField);
    if (fallback) { fallback.removeAttribute('name'); fallback.required = false; }

    var R = { grid: D.grid, len: D.len, step: D.step || 30, hours: D.hours || [[570, 1110]], days: D.days || [1, 2, 3, 4, 5], notice: D.notice || 0 };
    var loadedAt = Date.now();
    var nowSec = function () { return D.now + Math.floor((Date.now() - loadedAt) / 1000); };
    var tz = D.tz && validZone(D.tz) ? canon(D.tz) : (deviceZone || D.hostTz || 'UTC');
    var tzSource = D.tz && validZone(D.tz) ? D.tzSource : (deviceZone ? 'device' : 'host');
    var ipZone = D.tzSource === 'ip' && validZone(D.tz) ? canon(D.tz) : '';

    var cache = {}, cachedAt = 0;
    /* Every working-hours time ahead in a zone, booked ones included: [{t, free}]. */
    var timesFor = function (z) {
        if (nowSec() - cachedAt > 60) { cache = {}; cachedAt = nowSec(); }
        return cache[z] || (cache[z] = zoneTimes(R, z, nowSec()));
    };
    /* Only the times that can be booked. */
    var slotsFor = function (z) { return timesFor(z).filter(function (x) { return x.free; }).map(function (x) { return x.t; }); };
    var dayKey = function (z, t) { var w = wall(z, t * 1000); return ymd(w.y, w.m, w.d); };
    var byDay = function () {
        var map = {};
        timesFor(tz).forEach(function (x) { var k = dayKey(tz, x.t); (map[k] = map[k] || []).push(x); });
        return map;
    };
    var hasFree = function (list) { return !!list && list.some(function (x) { return x.free; }); };
    var openKeys = function (days) { return Object.keys(days).filter(function (k) { return hasFree(days[k]); }).sort(); };

    var selDay = '', selTime = 0, month = '', chosen = 0;
    var phone = window.matchMedia ? window.matchMedia('(max-width: 760px)') : { matches: false };

    /* ---- the skeleton: calendar column, times column ---- */
    var pick = el('div', 'bk-pick');
    pick.setAttribute('data-view', 'cal');
    var calCol = el('div', 'bk-calcol');
    var heading = el('h2', 'bk-h bk-pick-h', D.heading || 'Select a date and time');
    var calHead = el('div', 'bk-cal-head');
    var prevBtn = el('button', 'bk-nav');
    prevBtn.type = 'button';
    prevBtn.setAttribute('aria-label', 'Previous month');
    prevBtn.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg>';
    var monthLabel = el('strong', 'bk-month');
    monthLabel.setAttribute('aria-live', 'polite');
    var nextBtn = el('button', 'bk-nav');
    nextBtn.type = 'button';
    nextBtn.setAttribute('aria-label', 'Next month');
    nextBtn.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>';
    calHead.appendChild(prevBtn);
    calHead.appendChild(monthLabel);
    calHead.appendChild(nextBtn);
    var grid = el('div', 'bk-grid7');
    grid.setAttribute('role', 'group');
    grid.setAttribute('aria-label', 'Days with open times');

    var zoneBox = el('div', 'bk-zone');
    zoneBox.appendChild(el('p', 'bk-zone-label', 'Time zone'));
    var zoneBtn = el('button', 'bk-zone-btn');
    zoneBtn.type = 'button';
    zoneBtn.setAttribute('aria-haspopup', 'listbox');
    zoneBtn.setAttribute('aria-expanded', 'false');
    var zoneNameEl = el('span', 'bk-zone-name');
    /* The clock and the arrow stay together when the name wraps on a phone. */
    var zoneTimeBox = el('span', 'bk-zone-time');
    var zoneTimeEl = el('span', 'bk-zone-clock');
    zoneTimeBox.appendChild(zoneTimeEl);
    zoneTimeBox.appendChild(el('span', 'bk-caret'));
    zoneBtn.appendChild(ico('globe'));
    zoneBtn.appendChild(zoneNameEl);
    zoneBtn.appendChild(zoneTimeBox);
    var hint = el('p', 'bk-zone-hint');
    hint.hidden = true;
    zoneBox.appendChild(zoneBtn);
    zoneBox.appendChild(hint);

    calCol.appendChild(heading);
    calCol.appendChild(calHead);
    calCol.appendChild(grid);
    calCol.appendChild(zoneBox);

    var timesCol = el('div', 'bk-timescol');
    var backBtn = el('button', 'bk-back', 'Back to dates');
    backBtn.type = 'button';
    var timesHead = el('h3', 'bk-times-h');
    timesHead.setAttribute('tabindex', '-1');
    var timesZone = el('p', 'bk-times-zone');
    var timesList = el('div', 'bk-times-list');
    timesList.setAttribute('role', 'list');
    timesCol.appendChild(backBtn);
    timesCol.appendChild(timesHead);
    timesCol.appendChild(timesZone);
    timesCol.appendChild(timesList);

    pick.appendChild(calCol);
    pick.appendChild(timesCol);
    app.appendChild(pick);
    app.hidden = false;

    /* ---- the calendar ---- */
    var monthOf = function (key) { return key.slice(0, 7); };
    var addMonth = function (m, n) {
        var d = new Date(Date.UTC(+m.slice(0, 4), +m.slice(5, 7) - 1 + n, 1));
        return d.getUTCFullYear() + '-' + pad(d.getUTCMonth() + 1);
    };
    var todayKey = function () { var w = wall(tz, nowSec() * 1000); return ymd(w.y, w.m, w.d); };
    var renderCalendar = function () {
        var days = byDay();
        var keys = openKeys(days);
        var endW = wall(tz, R.grid.until * 1000);
        var today = todayKey();
        var firstMonth = monthOf(today), lastMonth = monthOf(ymd(endW.y, endW.m, endW.d));
        if (!month) { month = keys.length ? monthOf(keys[0]) : firstMonth; }
        if (month < firstMonth) { month = firstMonth; }
        if (month > lastMonth) { month = lastMonth; }
        if (selDay && !hasFree(days[selDay])) { selDay = ''; selTime = 0; }
        var y = +month.slice(0, 4), mo = +month.slice(5, 7);
        monthLabel.textContent = new Intl.DateTimeFormat('en-US', { month: 'long', year: 'numeric', timeZone: 'UTC' }).format(new Date(Date.UTC(y, mo - 1, 1)));
        prevBtn.disabled = month <= firstMonth;
        nextBtn.disabled = month >= lastMonth;
        grid.innerHTML = '';
        WD.forEach(function (d) { grid.appendChild(el('div', 'bk-dow', d)); });
        var lead = (new Date(Date.UTC(y, mo - 1, 1)).getUTCDay() + 6) % 7;
        for (var i = 0; i < lead; i++) { grid.appendChild(el('span', 'bk-blank')); }
        var count = new Date(Date.UTC(y, mo, 0)).getUTCDate();
        for (var d = 1; d <= count; d++) {
            var k = month + '-' + pad(d);
            var open = hasFree(days[k]);
            var full = !!days[k] && !open;
            var b = el(open ? 'button' : 'span', 'bk-day' + (open ? ' is-open' : '') + (full ? ' is-full' : '') + (k === selDay ? ' is-sel' : '') + (k === today ? ' is-today' : ''), String(d));
            if (open) {
                var free = days[k].filter(function (x) { return x.free; }).length;
                b.type = 'button';
                b.setAttribute('aria-label', (k === today ? 'Today, ' : '') + new Intl.DateTimeFormat('en-US', { weekday: 'long', month: 'long', day: 'numeric', timeZone: 'UTC' }).format(new Date(Date.UTC(y, mo - 1, d)))
                    + ', ' + free + (free === 1 ? ' time' : ' times') + ' open');
                b.setAttribute('aria-pressed', k === selDay ? 'true' : 'false');
                b.setAttribute('data-day', k);
            } else if (full) {
                b.setAttribute('title', 'Fully booked');
                b.setAttribute('aria-label', 'Fully booked');
            } else if (k === today) {
                b.setAttribute('title', 'Today: no times left');
                b.setAttribute('aria-label', 'Today, no times left');
            } else {
                b.setAttribute('aria-hidden', 'true');
            }
            grid.appendChild(b);
        }
        if (!keys.length) {
            var none = el('p', 'bk-empty', 'There are no open times in the next few weeks in this time zone. Try another zone, or email connect@vturnu.com and we will find a time.');
            grid.appendChild(none);
        }
    };
    grid.addEventListener('click', function (e) {
        var b = e.target.closest ? e.target.closest('button[data-day]') : null;
        if (!b) { return; }
        selDay = b.getAttribute('data-day');
        selTime = 0;
        render();
        if (phone.matches) {
            pick.setAttribute('data-view', 'times');
            card.scrollIntoView({ behavior: 'smooth', block: 'start' });
            setTimeout(function () { try { timesHead.focus({ preventScroll: true }); } catch (x) { timesHead.focus(); } }, 50);
        }
    });
    prevBtn.addEventListener('click', function () { month = addMonth(month, -1); renderCalendar(); });
    nextBtn.addEventListener('click', function () { month = addMonth(month, 1); renderCalendar(); });
    backBtn.addEventListener('click', function () { pick.setAttribute('data-view', 'cal'); selTime = 0; render(); });

    /* ---- the day's times: one column, Next beside the one picked ---- */
    var renderTimes = function () {
        var days = byDay();
        var list = selDay && hasFree(days[selDay]) ? days[selDay] : [];
        card.classList.toggle('has-day', !!list.length);
        timesList.innerHTML = '';
        if (!list.length) { return; }
        timesHead.textContent = '';
        if (selDay === todayKey()) { timesHead.appendChild(el('span', 'bk-today', 'Today')); }
        timesHead.appendChild(document.createTextNode(dateOf(tz, list[0].t * 1000, { weekday: 'long', month: 'long', day: 'numeric' })));
        timesZone.textContent = label(tz, list[0].t * 1000) + ' · ' + timeOf(tz, nowSec() * 1000);
        list.forEach(function (x) {
            var t = x.t;
            var row = el('div', 'bk-time' + (t === selTime ? ' is-sel' : '') + (x.free ? '' : ' is-taken'));
            row.setAttribute('role', 'listitem');
            if (!x.free) {
                /* Already booked: shown struck through, so the day reads true, but it cannot be picked. */
                var s = el('button', 'bk-time-btn');
                s.type = 'button';
                s.disabled = true;
                s.setAttribute('aria-label', timeOf(tz, t * 1000) + ', booked');
                s.title = 'Booked';
                s.appendChild(el('s', '', timeOf(tz, t * 1000)));
                row.appendChild(s);
                timesList.appendChild(row);
                return;
            }
            var b = el('button', 'bk-time-btn', timeOf(tz, t * 1000));
            b.type = 'button';
            b.setAttribute('data-t', String(t));
            b.setAttribute('aria-pressed', t === selTime ? 'true' : 'false');
            row.appendChild(b);
            if (t === selTime) {
                var nx = el('button', 'bk-next', 'Next');
                nx.type = 'button';
                nx.setAttribute('data-next', String(t));
                nx.setAttribute('aria-label', 'Next: ' + timeOf(tz, t * 1000) + ', ' + timesHead.textContent);
                row.appendChild(nx);
            }
            timesList.appendChild(row);
        });
    };
    timesList.addEventListener('click', function (e) {
        var nx = e.target.closest ? e.target.closest('[data-next]') : null;
        if (nx) { choose(+nx.getAttribute('data-next'), true); return; }
        var b = e.target.closest ? e.target.closest('[data-t]') : null;
        if (!b) { return; }
        selTime = +b.getAttribute('data-t');
        renderTimes();
        var again = timesList.querySelector('[data-next]');
        if (again) { again.focus(); }
    });

    /* ---- the active zone, its clock, and the device hint ---- */
    var renderZone = function () {
        var ms = nowSec() * 1000;
        zoneNameEl.textContent = label(tz, ms);
        zoneTimeEl.textContent = timeOf(tz, ms);
        zoneBtn.setAttribute('aria-label', 'Time zone: ' + label(tz, ms) + ', now ' + timeOf(tz, ms) + '. Change');
        var show = deviceZone && deviceZone !== tz && (tzSource === 'ip' || tzSource === 'stored') && offsetMin(deviceZone, ms) !== offsetMin(tz, ms);
        hint.hidden = !show;
        if (show) {
            hint.innerHTML = '';
            hint.appendChild(document.createTextNode('Your device is set to ' + label(deviceZone, ms) + '. '));
            var use = el('button', 'bk-hint-btn', 'Use it');
            use.type = 'button';
            use.addEventListener('click', function () { setZone(deviceZone, 'device'); });
            hint.appendChild(use);
        }
    };

    var render = function () {
        renderCalendar();
        renderTimes();
        renderZone();
        if (!selDay) { pick.setAttribute('data-view', 'cal'); }
    };

    var setZone = function (z, source) {
        z = canon(z);
        if (!validZone(z)) { return; }
        tz = z;
        tzSource = source || 'picked';
        tzVal.value = z;
        var days = byDay();
        if (selDay && !hasFree(days[selDay])) { selDay = ''; }
        if (selTime && slotsFor(tz).indexOf(selTime) === -1) { selTime = 0; }
        var keys = openKeys(days);
        if (!selDay && keys.length && (!month || !keys.some(function (k) { return monthOf(k) === month; }))) { month = monthOf(keys[0]); }
        render();
    };

    /* ---- the zone picker: search by city, country, zone name, abbreviation or offset ---- */
    var pop = null, popInput = null, popList = null, entries = null, active = -1, shown = [];
    var norm = function (s) { return String(s).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[_/]/g, ' '); };
    var buildEntries = function () {
        var ms = nowSec() * 1000;
        var ids = Object.keys(ZONES);
        if (ids.indexOf('UTC') === -1) { ids.push('UTC'); }
        [tz, deviceZone, ipZone, D.hostTz].forEach(function (z) { if (z && ids.indexOf(z) === -1 && validZone(z)) { ids.push(z); } });
        entries = [];
        ids.forEach(function (z) {
            if (!validZone(z)) { return; }
            var off = offsetMin(z, ms), cc = ZONES[z] || '';
            var a = Math.abs(off), hh = Math.floor(a / 60), mm = a % 60, sign = off < 0 ? '-' : '+';
            var offs = off === 0 ? 'gmt utc +0 gmt+0 utc+0' : ['gmt' + sign + hh + (mm ? ':' + pad(mm) : ''), 'utc' + sign + hh + (mm ? ':' + pad(mm) : ''), sign + hh + (mm ? ':' + pad(mm) : ''), sign + pad(hh) + ':' + pad(mm), 'gmt' + sign + pad(hh) + ':' + pad(mm)].join(' ');
            var lab = label(z, ms), ln = zoneName(z, ms, 'long');
            entries.push({
                z: z, label: lab, off: off, cc: cc,
                sub: (ln && ln.indexOf('GMT') !== 0 ? ln + ', ' : '') + gmt(z, ms),
                key: norm([lab, z, cityOf(z), countryName(cc), cc, CC_WORDS[cc] || '', WORDS[z] || '', ln, zoneName(z, ms, 'longGeneric'), zoneName(z, ms, 'short'), offs].join(' ')),
                city: norm(cityOf(z)), lab: norm(lab)
            });
        });
    };
    var groupOf = function (z) {
        if (US_CA.indexOf(z) !== -1) { return 0; }
        var r = z.split('/')[0];
        return r === 'America' ? 1 : r === 'Europe' ? 2 : r === 'Asia' ? 3 : r === 'Africa' ? 4 : (r === 'Australia' || r === 'Pacific') ? 5 : (r === 'Atlantic' || r === 'Indian') ? 6 : 7;
    };
    var GROUPS = ['US & Canada', 'Americas', 'Europe', 'Asia & Middle East', 'Africa', 'Australia & Pacific', 'Atlantic & Indian Ocean', 'Other'];
    var optionFor = function (en, ms) {
        var o = el('button', 'bk-zopt' + (en.z === tz ? ' is-cur' : ''));
        o.type = 'button';
        o.id = 'bkz-' + shown.length;
        o.setAttribute('role', 'option');
        o.setAttribute('aria-selected', en.z === tz ? 'true' : 'false');
        o.setAttribute('data-zone', en.z);
        var txt = el('span', 'bk-zopt-txt');
        txt.appendChild(el('span', 'bk-zopt-name', en.label));
        txt.appendChild(el('span', 'bk-zopt-sub', en.sub));
        o.appendChild(txt);
        o.appendChild(el('span', 'bk-zopt-time', timeOf(en.z, ms)));
        shown.push(o);
        return o;
    };
    var fillList = function () {
        var ms = nowSec() * 1000;
        var q = norm(popInput.value.trim());
        popList.innerHTML = '';
        shown = [];
        active = -1;
        var byZone = {};
        entries.forEach(function (en) { byZone[en.z] = en; });
        if (q) {
            var words = q.split(/\s+/);
            var hits = entries.filter(function (en) { return words.every(function (w) { return en.key.indexOf(w) !== -1; }); });
            /* A whole-word match first (IST is India before Istanbul), then a name or a city starting with it. */
            var score = function (en) {
                return ((' ' + en.key + ' ').indexOf(' ' + q + ' ') !== -1 ? 0 : en.lab.indexOf(q) === 0 ? 1 : en.city.indexOf(q) === 0 ? 2 : 3) - (NAMES[en.z] ? 0.5 : 0);
            };
            hits.sort(function (a, b) { return score(a) - score(b) || a.off - b.off || (a.label < b.label ? -1 : 1); });
            if (!hits.length) {
                popList.appendChild(el('p', 'bk-zempty', 'No time zone matches "' + popInput.value.trim() + '". Try a city, a country, or an offset such as GMT+4.'));
                return;
            }
            hits.slice(0, 80).forEach(function (en) { popList.appendChild(optionFor(en, ms)); });
            return;
        }
        var head = function (t) { var h = el('p', 'bk-zgroup', t); h.setAttribute('role', 'presentation'); popList.appendChild(h); };
        var sug = [];
        [tz, ipZone, deviceZone, D.hostTz, 'UTC'].forEach(function (z) { if (z && byZone[z] && sug.indexOf(z) === -1) { sug.push(z); } });
        head('Suggested');
        sug.forEach(function (z) { popList.appendChild(optionFor(byZone[z], ms)); });
        var groups = GROUPS.map(function () { return []; });
        entries.forEach(function (en) { if (en.z !== 'UTC') { groups[groupOf(en.z)].push(en); } });
        groups.forEach(function (list, gi) {
            if (!list.length) { return; }
            head(GROUPS[gi]);
            if (gi === 0) { list.sort(function (a, b) { return US_CA.indexOf(a.z) - US_CA.indexOf(b.z); }); }
            else { list.sort(function (a, b) { return a.off - b.off || (a.label < b.label ? -1 : 1); }); }
            list.forEach(function (en) { popList.appendChild(optionFor(en, ms)); });
        });
    };
    var setActive = function (i) {
        if (!shown.length) { return; }
        if (active >= 0 && shown[active]) { shown[active].classList.remove('is-active'); }
        active = (i + shown.length) % shown.length;
        shown[active].classList.add('is-active');
        shown[active].scrollIntoView({ block: 'nearest' });
        popInput.setAttribute('aria-activedescendant', shown[active].id);
    };
    var scrim = null;
    var closePop = function (focusBack) {
        if (!pop || pop.hidden) { return; }
        pop.hidden = true;
        if (scrim) { scrim.hidden = true; }
        zoneBtn.setAttribute('aria-expanded', 'false');
        card.classList.remove('is-zone-open');
        document.removeEventListener('mousedown', outside, true);
        if (focusBack) { zoneBtn.focus(); }
    };
    var outside = function (e) { if (pop && !pop.contains(e.target) && !zoneBtn.contains(e.target)) { closePop(false); } };
    var buildPop = function () {
        /* On phones the picker is a sheet over a dimmed page. */
        scrim = el('div', 'bk-scrim');
        scrim.hidden = true;
        document.body.appendChild(scrim);
        pop = el('div', 'bk-zone-pop');
        pop.hidden = true;
        var top = el('div', 'bk-zone-top');
        var t = el('p', 'bk-zone-title', 'Time zone');
        var fmtBox = el('div', 'bk-clockfmt');
        fmtBox.setAttribute('role', 'group');
        fmtBox.setAttribute('aria-label', 'Clock');
        [['12', 'am/pm'], ['24', '24h']].forEach(function (f) {
            var b = el('button', 'bk-clockbtn' + ((f[0] === '24') === h24 ? ' is-on' : ''), f[1]);
            b.type = 'button';
            b.setAttribute('aria-pressed', (f[0] === '24') === h24 ? 'true' : 'false');
            b.addEventListener('click', function () {
                h24 = f[0] === '24';
                try { if (window.localStorage) { localStorage.setItem('bk_h24', h24 ? '1' : '0'); } } catch (x) { /* storage blocked */ }
                Array.prototype.forEach.call(fmtBox.children, function (c, i) { var on = (i === 1) === h24; c.classList.toggle('is-on', on); c.setAttribute('aria-pressed', on ? 'true' : 'false'); });
                fillList();
                render();
            });
            fmtBox.appendChild(b);
        });
        var x = el('button', 'bk-zone-close');
        x.type = 'button';
        x.setAttribute('aria-label', 'Close');
        x.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>';
        x.addEventListener('click', function () { closePop(true); });
        top.appendChild(t);
        top.appendChild(fmtBox);
        top.appendChild(x);
        var search = el('div', 'bk-zone-search');
        search.appendChild(ico('search'));
        popInput = el('input', 'bk-zone-input');
        popInput.type = 'search';
        popInput.placeholder = 'Search a city, country or time zone';
        popInput.setAttribute('aria-label', 'Search time zones');
        popInput.setAttribute('autocomplete', 'off');
        popInput.setAttribute('spellcheck', 'false');
        search.appendChild(popInput);
        popList = el('div', 'bk-zone-list');
        popList.setAttribute('role', 'listbox');
        popList.setAttribute('aria-label', 'Time zones');
        pop.appendChild(top);
        pop.appendChild(search);
        pop.appendChild(popList);
        zoneBox.appendChild(pop);
        popInput.addEventListener('input', fillList);
        popInput.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowDown') { e.preventDefault(); setActive(active + 1); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); setActive(active - 1); }
            else if (e.key === 'Enter') { e.preventDefault(); var o = shown[active >= 0 ? active : 0]; if (o) { o.click(); } }
            else if (e.key === 'Escape') { e.preventDefault(); closePop(true); }
        });
        pop.addEventListener('keydown', function (e) { if (e.key === 'Escape') { e.preventDefault(); closePop(true); } });
        popList.addEventListener('click', function (e) {
            var o = e.target.closest ? e.target.closest('[data-zone]') : null;
            if (!o) { return; }
            setZone(o.getAttribute('data-zone'), 'picked');
            closePop(true);
        });
    };
    var openPop = function () {
        if (!pop) { buildPop(); }
        if (!entries) { buildEntries(); }
        popInput.value = '';
        fillList();
        /* Open upward when the window has no room below the button. */
        var rect = zoneBtn.getBoundingClientRect();
        pop.classList.toggle('is-up', !phone.matches && window.innerHeight - rect.bottom < 470 && rect.top > 470);
        pop.hidden = false;
        scrim.hidden = false;
        zoneBtn.setAttribute('aria-expanded', 'true');
        card.classList.add('is-zone-open');
        document.addEventListener('mousedown', outside, true);
        var cur = popList.querySelector('.is-cur');
        if (cur) { cur.scrollIntoView({ block: 'center' }); popList.scrollTop = 0; }
        setTimeout(function () { try { popInput.focus({ preventScroll: true }); } catch (x) { popInput.focus(); } }, 30);
    };
    zoneBtn.addEventListener('click', function () { if (pop && !pop.hidden) { closePop(true); } else { openPop(); } });

    /* ---- the details step ---- */
    var renderSummary = function (t) {
        var ms = t * 1000, endMs = ms + R.len * 60000;
        summary.innerHTML = '';
        var back = el('button', 'bk-back bk-back--details', 'Back');
        back.type = 'button';
        back.addEventListener('click', function () {
            card.classList.remove('is-details');
            render();
            var again = timesList.querySelector('[data-next]') || timesList.querySelector('button');
            if (again) { again.focus(); }
        });
        summary.appendChild(back);
        var box = el('div', 'bk-sumbox');
        var l1 = el('p', 'bk-sumline bk-sumline--when');
        l1.appendChild(ico('cal'));
        l1.appendChild(el('span', '', timeOf(tz, ms) + ' to ' + timeOf(tz, endMs) + ', ' + dateOf(tz, ms, { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' })));
        var l2 = el('p', 'bk-sumline');
        l2.appendChild(ico('globe'));
        l2.appendChild(el('span', '', label(tz, ms)));
        var l3 = el('p', 'bk-sumline');
        l3.appendChild(ico('video'));
        l3.appendChild(el('span', '', 'Google Meet, ' + R.len + ' minutes'));
        box.appendChild(l1);
        box.appendChild(l2);
        box.appendChild(l3);
        summary.appendChild(box);
        summary.hidden = false;
    };
    var choose = function (t, focus) {
        chosen = t;
        selTime = t;
        selDay = dayKey(tz, t);
        slotField.value = String(t);
        tzVal.value = tz;
        renderSummary(t);
        card.classList.add('is-details');
        closePop(false);
        if (D.recaptcha) { loadRecaptcha(); }
        if (focus) {
            var first = form.querySelector('#bk-name');
            var target = first && !first.value ? first : submit;
            card.scrollIntoView({ behavior: 'smooth', block: 'start' });
            setTimeout(function () { try { target.focus({ preventScroll: true }); } catch (x) { target.focus(); } }, 300);
        }
    };

    /* The list stays true while the page is open: past times drop off, the clock moves. */
    setInterval(function () {
        renderZone();
        if (card.classList.contains('is-details')) { return; }
        var before = (cache[tz] || []).length;
        if (nowSec() - cachedAt > 60) {
            cache = {};
            if (slotsFor(tz).length !== before) { render(); }
        }
    }, 20000);

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
            card.classList.remove('is-details');
            render();
            card.scrollIntoView({ behavior: 'smooth', block: 'start' });
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
        tzVal.value = tz;
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
        tzVal.value = tz;
        render();
        var again = D.chosen && slotsFor(tz).indexOf(D.chosen) !== -1 ? D.chosen : 0;
        if (again && D.fieldErrors) {
            month = monthOf(dayKey(tz, again));
            choose(again, false);
        } else if (D.chosen && D.taken) {
            var k = dayKey(tz, D.chosen);
            if (hasFree(byDay()[k])) { selDay = k; month = monthOf(k); render(); }
        }
    } catch (err) {
        /* Something in this browser failed: back to the plain form, which works on its own. */
        document.documentElement.classList.remove('js');
        app.hidden = true;
        card.classList.remove('is-details', 'has-day');
        if (fallback) { fallback.setAttribute('name', 'slot'); fallback.required = true; }
        if (slotField.parentNode) { slotField.parentNode.removeChild(slotField); }
        if (window.console) { console.error('[booking]', err); }
    }
})();
