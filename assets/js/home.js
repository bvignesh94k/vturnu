/* ==========================================================================
   VTurnU homepage behaviour. Loaded only on "/".

   Everything here is an enhancement. With this file blocked, or with
   reduced motion, the page renders every scene as normal content and the
   lead form submits as one long form.

   1. Pinned hero: scroll inside the hero plays four scenes while the
      section holds the viewport.
   2. Globe: a dependency-free canvas globe with market arcs.
   3. Depth: scroll reveals, pointer tilt and count-up numbers.
   4. Lead form: a three-step B2B qualifier with inline validation.
   ========================================================================== */
(function () {
    'use strict';

    var hp = document.querySelector('.hp');
    if (!hp) { return; }
    document.documentElement.classList.add('hp-js');

    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    var supportsIO = 'IntersectionObserver' in window;
    var raf = window.requestAnimationFrame || function (f) { return setTimeout(f, 16); };
    window.dataLayer = window.dataLayer || [];

    function clamp(v, a, b) { return v < a ? a : (v > b ? b : v); }
    function easeOut(t) { return 1 - Math.pow(1 - t, 3); }
    function track(event, data) {
        var o = { event: event };
        for (var k in data) { if (Object.prototype.hasOwnProperty.call(data, k)) { o[k] = data[k]; } }
        window.dataLayer.push(o);
    }

    /* The sticky stage sits under the sticky site header. */
    var header = document.querySelector('.site-header');
    function headerHeight() {
        var h = header ? header.getBoundingClientRect().height : 0;
        document.body.style.setProperty('--hp3-hdr', Math.round(h) + 'px');
        return h;
    }
    headerHeight();

    /* ----------------------------------------------------------- counters */

    var COUNT_RE = /^([^0-9]*)([0-9]+(?:\.[0-9]+)?)(.*)$/;
    function countUp(el) {
        if (el.getAttribute('data-counted')) { return; }
        el.setAttribute('data-counted', '1');
        var final = el.getAttribute('data-count') || el.textContent;
        var m = COUNT_RE.exec(final);
        if (!m || reduce) { el.textContent = final; return; }
        var target = parseFloat(m[2]);
        var decimals = (m[2].split('.')[1] || '').length;
        var start = null;
        function step(ts) {
            if (start === null) { start = ts; }
            var t = clamp((ts - start) / 1200, 0, 1);
            el.textContent = m[1] + (target * easeOut(t)).toFixed(decimals) + m[3];
            if (t < 1) { raf(step); } else { el.textContent = final; }
        }
        raf(step);
    }

    /* ------------------------------------------------------------- reveal */

    (function reveal() {
        var items = hp.querySelectorAll('.hp3-sec [data-rise], .hp3-final [data-rise], .hp3-stage-i, .hp3-sec [data-count]');
        if (!items.length) { return; }
        function show(el) {
            el.classList.add('in');
            if (el.hasAttribute('data-count')) { countUp(el); }
        }
        if (!supportsIO || reduce) { for (var i = 0; i < items.length; i++) { show(items[i]); } return; }
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) { return; }
                show(entry.target);
                io.unobserve(entry.target);
            });
        }, { rootMargin: '0px 0px -8% 0px', threshold: 0 });
        for (var j = 0; j < items.length; j++) { io.observe(items[j]); }
    })();

    /* --------------------------------------------------------------- tilt */

    (function tilt() {
        if (!finePointer || reduce) { return; }
        var cards = hp.querySelectorAll('[data-tilt]');
        Array.prototype.forEach.call(cards, function (card) {
            card.addEventListener('pointermove', function (e) {
                var r = card.getBoundingClientRect();
                var x = (e.clientX - r.left) / r.width - 0.5;
                var y = (e.clientY - r.top) / r.height - 0.5;
                card.style.setProperty('--ry', (x * 9).toFixed(2) + 'deg');
                card.style.setProperty('--rx', (y * -7).toFixed(2) + 'deg');
            });
            card.addEventListener('pointerleave', function () {
                card.style.setProperty('--ry', '0deg');
                card.style.setProperty('--rx', '0deg');
            });
        });
    })();

    /* -------------------------------------------------------------- globe */

    function Globe(canvas) {
        var ctx = canvas.getContext && canvas.getContext('2d');
        if (!ctx) { return null; }
        var markers = [];
        try { markers = JSON.parse(canvas.getAttribute('data-markers') || '[]'); } catch (e) { markers = []; }

        var RAD = Math.PI / 180;
        var N = 1100;
        var STOPS = [[34, 227, 255], [129, 140, 248], [255, 94, 160], [255, 176, 32]];
        var pts = new Float32Array(N * 3);
        var cols = [];
        var golden = Math.PI * (3 - Math.sqrt(5));
        for (var i = 0; i < N; i++) {
            var y = 1 - (i / (N - 1)) * 2;
            var rr = Math.sqrt(1 - y * y);
            var th = golden * i;
            pts[i * 3] = Math.cos(th) * rr;
            pts[i * 3 + 1] = y;
            pts[i * 3 + 2] = Math.sin(th) * rr;
            var t = (Math.atan2(pts[i * 3], pts[i * 3 + 2]) + Math.PI) / (2 * Math.PI) * 3;
            var k = Math.min(2, Math.floor(t));
            var f = t - k;
            var a = STOPS[k], b = STOPS[k + 1];
            cols.push('rgb(' + Math.round(a[0] + (b[0] - a[0]) * f) + ',' + Math.round(a[1] + (b[1] - a[1]) * f) + ',' + Math.round(a[2] + (b[2] - a[2]) * f) + ')');
        }
        function vec(lat, lon) {
            var p = lat * RAD, l = lon * RAD;
            return [Math.cos(p) * Math.sin(l), Math.sin(p), Math.cos(p) * Math.cos(l)];
        }
        var mk = markers.map(function (m) { return { label: m[0], v: vec(m[1], m[2]), pos: m[3] || '' }; });
        var hub = mk.length ? mk[0].v : vec(13.08, 80.27);

        /* Great-circle arcs from the hub (Chennai) to every market, lifted
           off the surface in the middle so they read as flight paths. */
        var arcs = mk.slice(1).map(function (m) {
            var a = hub, b = m.v;
            var dot = clamp(a[0] * b[0] + a[1] * b[1] + a[2] * b[2], -1, 1);
            var om = Math.acos(dot), so = Math.sin(om) || 1;
            var path = [];
            for (var s = 0; s <= 36; s++) {
                var u = s / 36;
                var w1 = Math.sin((1 - u) * om) / so, w2 = Math.sin(u * om) / so;
                var lift = 1 + Math.sin(Math.PI * u) * Math.min(0.14, om * 0.1);
                path.push([(a[0] * w1 + b[0] * w2) * lift, (a[1] * w1 + b[1] * w2) * lift, (a[2] * w1 + b[2] * w2) * lift]);
            }
            return path;
        });

        var W = 0, H = 0, dpr = 1;
        var state = { cx: 0, cy: 0, r: 0, alpha: 0, labels: 0 };
        var progress = 0;

        function resize() {
            dpr = Math.min(window.devicePixelRatio || 1, 1.75);
            W = canvas.clientWidth; H = canvas.clientHeight;
            canvas.width = Math.round(W * dpr); canvas.height = Math.round(H * dpr);
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            var tg = target();
            state.cx = tg.cx; state.cy = tg.cy; state.r = tg.r; state.alpha = tg.alpha; state.labels = tg.labels;
        }

        /* Where the globe sits for the current scene. */
        function target() {
            var mobile = W < 900;
            var scene = Math.min(3, Math.floor(progress * 4 + 1e-4));
            var base = mobile ? Math.min(W * 0.62, H * 0.42) : Math.min(W * 0.25, H * 0.4);
            var tg = mobile
                ? { cx: W * 0.5, cy: H * 0.84, r: base, alpha: 0.5, labels: 0 }
                : { cx: W * 0.75, cy: H * 0.5, r: base, alpha: 1, labels: 0 };
            if (scene === 1) { tg.alpha = mobile ? 0.18 : 0.28; tg.cx = mobile ? tg.cx : W * 0.8; }
            if (scene === 2) {
                tg.r = base * (mobile ? 1.05 : 1.06); tg.alpha = 1; tg.labels = 1;
                if (mobile) { tg.cy = H * 0.8; } else { tg.cx = W * 0.73; }
            }
            if (scene === 3) { tg.r = base * 0.9; tg.alpha = mobile ? 0.35 : 0.8; }
            return tg;
        }

        function rot(v, ca, sa, cb, sb) {
            var x = v[0] * ca + v[2] * sa;
            var z = -v[0] * sa + v[2] * ca;
            var y = v[1] * cb - z * sb;
            return [x, y, v[1] * sb + z * cb];
        }

        function draw(time) {
            var tg = target();
            var e = reduce ? 1 : 0.075;
            state.cx += (tg.cx - state.cx) * e; state.cy += (tg.cy - state.cy) * e;
            state.r += (tg.r - state.r) * e; state.alpha += (tg.alpha - state.alpha) * e;
            state.labels += (tg.labels - state.labels) * e;

            ctx.clearRect(0, 0, W, H);
            var R = state.r, cx = state.cx, cy = state.cy, A = state.alpha;
            if (A < 0.01 || R < 4) { return; }

            /* Centred near 72°E (Gulf to Southeast Asia) in the markets scene. */
            var ang = -1.25 + (progress - 0.62) * 1.6 + (reduce ? 0 : Math.sin(time * 0.00025) * 0.12);
            var ca = Math.cos(ang), sa = Math.sin(ang), cb = Math.cos(0.36), sb = Math.sin(0.36);

            var glow = ctx.createRadialGradient(cx, cy, R * 0.2, cx, cy, R * 1.45);
            glow.addColorStop(0, 'rgba(67,56,202,' + (0.32 * A) + ')');
            glow.addColorStop(0.6, 'rgba(255,61,138,' + (0.1 * A) + ')');
            glow.addColorStop(1, 'rgba(11,8,32,0)');
            ctx.fillStyle = glow;
            ctx.fillRect(cx - R * 1.5, cy - R * 1.5, R * 3, R * 3);

            ctx.beginPath();
            ctx.arc(cx, cy, R, 0, Math.PI * 2);
            ctx.strokeStyle = 'rgba(129,140,248,' + (0.28 * A) + ')';
            ctx.lineWidth = 1;
            ctx.stroke();

            for (var i = 0; i < N; i++) {
                var p = rot([pts[i * 3], pts[i * 3 + 1], pts[i * 3 + 2]], ca, sa, cb, sb);
                if (p[2] < -0.05) { continue; }
                var depth = (p[2] + 1) / 2;
                var s = 0.7 + depth * 1.5;
                ctx.globalAlpha = A * (0.18 + depth * 0.8);
                ctx.fillStyle = cols[i];
                ctx.fillRect(cx + p[0] * R - s / 2, cy - p[1] * R - s / 2, s, s);
            }
            ctx.globalAlpha = 1;

            /* Arcs, drawn only on the visible hemisphere, with a travelling pulse. */
            ctx.lineWidth = 1.4;
            for (var a = 0; a < arcs.length; a++) {
                var path = arcs[a];
                var prev = null;
                ctx.beginPath();
                for (var q = 0; q < path.length; q++) {
                    var r3 = rot(path[q], ca, sa, cb, sb);
                    var vis = r3[2] > -0.02;
                    var X = cx + r3[0] * R, Y = cy - r3[1] * R;
                    if (vis && prev) { ctx.lineTo(X, Y); } else if (vis) { ctx.moveTo(X, Y); }
                    prev = vis;
                }
                ctx.strokeStyle = 'rgba(255,94,160,' + (0.5 * A) + ')';
                ctx.stroke();
                if (!reduce) {
                    var ph = ((time * 0.00035) + a * 0.137) % 1;
                    var pt = rot(path[Math.floor(ph * (path.length - 1))], ca, sa, cb, sb);
                    if (pt[2] > 0) {
                        ctx.beginPath();
                        ctx.arc(cx + pt[0] * R, cy - pt[1] * R, 2.4, 0, Math.PI * 2);
                        ctx.fillStyle = 'rgba(255,176,32,' + A + ')';
                        ctx.fill();
                    }
                }
            }

            /* Market markers and, in the markets scene, their labels. */
            ctx.font = '600 12px Poppins, Figtree, system-ui, sans-serif';
            ctx.textBaseline = 'middle';
            for (var m = 0; m < mk.length; m++) {
                var v = rot(mk[m].v, ca, sa, cb, sb);
                if (v[2] < 0.05) { continue; }
                var mx = cx + v[0] * R, my = cy - v[1] * R;
                var pulse = reduce ? 0.5 : (Math.sin(time * 0.004 + m) + 1) / 2;
                ctx.beginPath();
                ctx.arc(mx, my, 4 + pulse * 5, 0, Math.PI * 2);
                ctx.fillStyle = (m === 0 ? 'rgba(34,227,255,' : 'rgba(255,94,160,') + (0.22 * A * (1 - pulse * 0.6)) + ')';
                ctx.fill();
                ctx.beginPath();
                ctx.arc(mx, my, m === 0 ? 4 : 3, 0, Math.PI * 2);
                ctx.fillStyle = m === 0 ? '#22E3FF' : '#FFFFFF';
                ctx.globalAlpha = A;
                ctx.fill();
                if (state.labels > 0.05 && v[2] > 0.25 && mk[m].pos !== 'n') {
                    ctx.globalAlpha = state.labels * A * Math.min(1, (v[2] - 0.25) * 3);
                    ctx.fillStyle = '#F4F2FF';
                    ctx.textAlign = mk[m].pos === 'l' ? 'right' : 'left';
                    ctx.fillText(mk[m].label, mk[m].pos === 'l' ? mx - 8 : mx + 8, my);
                    ctx.textAlign = 'left';
                }
                ctx.globalAlpha = 1;
            }
        }

        return {
            resize: resize,
            draw: draw,
            setProgress: function (p) { progress = p; }
        };
    }

    /* ---------------------------------------------------------- hero pin */

    (function hero() {
        var heroEl = document.getElementById('hp3-hero');
        var stage = document.getElementById('hp3-stage');
        if (!heroEl || !stage) { return; }
        var scenes = Array.prototype.slice.call(stage.querySelectorAll('.hp3-scene'));
        var rail = stage.querySelector('.hp3-rail');
        var dots = rail ? Array.prototype.slice.call(rail.querySelectorAll('.hp3-rail-dot')) : [];
        var cue = stage.querySelector('.hp3-cue');
        var bar = stage.querySelector('.hp3-progress');
        var canvas = document.getElementById('hp3-globe');
        var globe = canvas ? Globe(canvas) : null;

        var pinned = false, top = 0, total = 1, current = -1, ticking = false, progress = 0;
        var proofDone = false, visible = true, running = false, seen = {};

        function canPin() {
            return !reduce && window.innerHeight >= 520 && window.CSS && CSS.supports && CSS.supports('position', 'sticky');
        }

        function setScene(i) {
            if (i === current) { return; }
            current = i;
            scenes.forEach(function (s, k) {
                s.classList.toggle('is-active', k === i);
                s.classList.toggle('is-past', k < i);
            });
            dots.forEach(function (d, k) {
                if (k === i) { d.setAttribute('aria-current', 'step'); } else { d.removeAttribute('aria-current'); }
            });
            if (i === 3 && !proofDone) {
                proofDone = true;
                Array.prototype.forEach.call(stage.querySelectorAll('[data-count]'), countUp);
            }
            if (!seen[i]) { seen[i] = true; track('hero_scene', { hero_scene: i + 1 }); }
        }

        function measure() {
            headerHeight();
            var r = heroEl.getBoundingClientRect();
            top = r.top + window.pageYOffset;
            total = Math.max(1, heroEl.offsetHeight - stage.offsetHeight);
            if (globe) { globe.resize(); }
        }

        function update() {
            ticking = false;
            if (!pinned) { return; }
            progress = clamp((window.pageYOffset - top) / total, 0, 1);
            stage.style.setProperty('--p', progress.toFixed(4));
            var fan = easeOut(clamp((progress * 4 - 0.85) * 1.7, 0, 1));
            stage.style.setProperty('--f', fan.toFixed(3));
            setScene(Math.min(3, Math.floor(progress * 4 + 1e-4)));
            if (cue) { cue.classList.toggle('is-gone', progress > 0.015); }
            if (globe) { globe.setProgress(progress); }
        }

        function onScroll() {
            if (!ticking) { ticking = true; raf(update); }
        }

        function loop(time) {
            if (!running) { return; }
            globe.draw(time || 0);
            raf(loop);
        }
        function startLoop() {
            if (!globe || running || reduce || !visible || document.hidden) { return; }
            running = true;
            raf(loop);
        }
        function stopLoop() { running = false; }

        function setup() {
            pinned = canPin();
            heroEl.classList.toggle('hp3-pin', pinned);
            heroEl.style.setProperty('--hp3-len', (window.innerWidth < 900 ? 380 : 440) + 'svh');
            [rail, cue, bar].forEach(function (el) { if (el) { el.hidden = !pinned; } });
            if (globe && pinned) { heroEl.classList.add('hp3-on'); }
            if (!pinned) {
                heroEl.classList.remove('hp3-on');
                scenes.forEach(function (s) { s.classList.add('is-active'); s.classList.remove('is-past'); });
                current = -1;
            }
            measure();
            update();
            if (globe && pinned) {
                if (reduce) { globe.draw(0); } else { startLoop(); }
            }
        }

        function goTo(i, smooth) {
            var y = i === 0 ? top : top + total * ((i + 0.12) / 4);
            window.scrollTo({ top: Math.round(y), behavior: smooth && !reduce ? 'smooth' : 'auto' });
        }

        dots.forEach(function (d) {
            d.addEventListener('click', function () { goTo(parseInt(d.getAttribute('data-go'), 10) || 0, true); });
        });

        /* Keyboard users tabbing into a later scene are scrolled to it, so the
           focused link is never inside an invisible scene. */
        stage.addEventListener('focusin', function (e) {
            if (!pinned) { return; }
            var sc = e.target.closest ? e.target.closest('.hp3-scene') : null;
            if (!sc) { return; }
            var i = scenes.indexOf(sc);
            if (i > -1 && i !== current) { goTo(i, false); }
        });

        if (finePointer && !reduce) {
            stage.addEventListener('pointermove', function (e) {
                var r = stage.getBoundingClientRect();
                stage.style.setProperty('--mx', (((e.clientX - r.left) / r.width) - 0.5).toFixed(3));
                stage.style.setProperty('--my', (((e.clientY - r.top) / r.height) - 0.5).toFixed(3));
            });
        }

        if (supportsIO) {
            new IntersectionObserver(function (entries) {
                visible = entries[0].isIntersecting;
                if (visible) { startLoop(); } else { stopLoop(); }
            }).observe(heroEl);
        }
        document.addEventListener('visibilitychange', function () {
            if (document.hidden) { stopLoop(); } else { startLoop(); }
        });

        var resizeTimer;
        window.addEventListener('resize', function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function () {
                var was = pinned;
                if (canPin() !== was) { setup(); } else { measure(); update(); }
            }, 150);
        });
        window.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('load', function () { measure(); update(); });
        if (document.fonts && document.fonts.ready) { document.fonts.ready.then(function () { measure(); update(); }); }

        setup();
    })();

    /* --------------------------------------------------------- lead form */

    (function leadForm() {
        var form = document.getElementById('hp-form');
        if (!form) { return; }
        var steps = Array.prototype.slice.call(form.querySelectorAll('.hp3-step'));
        var nextBtn = form.querySelector('.js-next');
        var backBtn = form.querySelector('.js-back');
        var submitBtn = form.querySelector('.js-submit');
        var meter = form.querySelector('.hp3-steps');
        var bar = form.querySelector('.hp3-steps-bar i');
        var stepN = form.querySelector('.js-step-n');
        var idx = 0, started = false;

        var FREE = ['gmail.com', 'googlemail.com', 'yahoo.com', 'yahoo.co.in', 'yahoo.co.uk', 'ymail.com', 'hotmail.com',
            'outlook.com', 'live.com', 'msn.com', 'icloud.com', 'me.com', 'mac.com', 'aol.com', 'proton.me', 'protonmail.com',
            'zoho.com', 'zohomail.in', 'gmx.com', 'mail.com', 'yandex.com', 'rediffmail.com', 'qq.com', '163.com'];

        function $(id) { return document.getElementById(id); }
        function err(key, el, msg) {
            var slot = form.querySelector('[data-err-for="' + key + '"]');
            if (slot) { slot.textContent = msg || ''; }
            if (el) { if (msg) { el.setAttribute('aria-invalid', 'true'); } else { el.removeAttribute('aria-invalid'); } }
            return !msg;
        }
        function filled(id) { var el = $(id); return el && el.value.trim() !== ''; }

        var checks = [
            function () {
                var any = form.querySelector('input[name="services[]"]:checked');
                return err('services', null, any ? '' : 'Pick at least one, so we prepare the right plan.') ? null : form.querySelector('input[name="services[]"]');
            },
            function () {
                var bad = null;
                var site = $('f-site');
                if (!err('f-company', $('f-company'), filled('f-company') ? '' : 'Please add your company name.')) { bad = bad || $('f-company'); }
                var siteOk = /^(https?:\/\/)?[a-z0-9-]+(\.[a-z0-9-]+)+(\/.*)?$/i.test(site.value.trim());
                if (!err('f-site', site, siteOk ? '' : 'Please add your company website, for example acme.com.')) { bad = bad || site; }
                if (!err('f-size', $('f-size'), filled('f-size') ? '' : 'Please choose your company size.')) { bad = bad || $('f-size'); }
                if (!err('f-budget', $('f-budget'), filled('f-budget') ? '' : 'Please choose a budget range. It helps us recommend the right channels.')) { bad = bad || $('f-budget'); }
                if (!err('f-when', $('f-when'), filled('f-when') ? '' : 'Please choose a timeline.')) { bad = bad || $('f-when'); }
                return bad;
            },
            function () {
                var bad = null;
                if (!err('f-name', $('f-name'), filled('f-name') ? '' : 'Please tell us who we are writing to.')) { bad = bad || $('f-name'); }
                var email = $('f-email'), v = email.value.trim().toLowerCase(), msg = '';
                if (!v) { msg = 'We need your work email to send the plan.'; }
                else if (!/^[^@\s]+@[^@\s]+\.[^@\s]{2,}$/.test(v)) { msg = 'That does not look like a complete email address.'; }
                else if (FREE.indexOf(v.split('@')[1]) > -1) { msg = 'Please use your company email, so we can research your business before we reply. No company email? Write to sales@vturnu.com.'; }
                if (!err('f-email', email, msg)) { bad = bad || email; }
                return bad;
            }
        ];

        function show(i, focus) {
            idx = i;
            steps.forEach(function (s, k) { s.classList.toggle('is-current', k === i); });
            if (bar) { bar.style.width = ((i + 1) / steps.length * 100).toFixed(2) + '%'; }
            if (stepN) { stepN.textContent = String(i + 1); }
            backBtn.hidden = i === 0;
            nextBtn.hidden = i === steps.length - 1;
            submitBtn.hidden = i !== steps.length - 1;
            if (focus) {
                var first = steps[i].querySelector('input:not([type="hidden"]), select, textarea');
                if (first) { first.focus({ preventScroll: true }); }
                var r = form.getBoundingClientRect();
                if (r.top < 0 || r.top > window.innerHeight * 0.6) {
                    form.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' });
                }
            }
        }

        function next() {
            var bad = checks[idx]();
            if (bad) { bad.focus(); return; }
            track('lead_form_step', { form_step: idx + 2 });
            show(idx + 1, true);
        }

        /* Stepped mode needs all three pieces; otherwise it stays one form. */
        if (steps.length === 3 && nextBtn && backBtn && submitBtn) {
            form.classList.add('hp3-stepped');
            if (meter) { meter.hidden = false; }
            show(0, false);
            nextBtn.addEventListener('click', next);
            backBtn.addEventListener('click', function () { show(Math.max(0, idx - 1), true); });
        } else {
            steps.forEach(function (s) { s.classList.add('is-current'); });
            idx = steps.length - 1;
        }

        form.addEventListener('input', function (e) {
            if (!started) { started = true; track('lead_form_start', {}); }
            if (e.target.getAttribute('aria-invalid') === 'true' || e.target.name === 'services[]') { checks[idx](); }
        });

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (form.classList.contains('hp3-stepped') && idx < steps.length - 1) { next(); return; }
            for (var k = 0; k < checks.length; k++) {
                var bad = checks[k]();
                if (bad) {
                    if (form.classList.contains('hp3-stepped')) { show(k, false); }
                    bad.focus();
                    return;
                }
            }
            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending your request…';
            var budget = $('f-budget') ? $('f-budget').value : '';
            var sent = false;
            function send(token) {
                if (sent) { return; }
                sent = true;
                var field = form.querySelector('.js-recaptcha-token');
                if (field) { field.value = token || ''; }
                track('generate_lead', { form_id: 'b2b-qualifier', lead_budget: budget });
                form.submit();
            }
            var key = form.getAttribute('data-recaptcha') || '';
            setTimeout(function () { send(''); }, 4000);
            if (key && window.grecaptcha && grecaptcha.ready) {
                grecaptcha.ready(function () {
                    grecaptcha.execute(key, { action: 'submit' }).then(send, function () { send(''); });
                });
            } else {
                send('');
            }
        });
    })();

    /* ------------------------------------------------------ CTA routing */

    /* On the homepage the shared floating "Get a quote" buttons go to the
       qualifying form instead of the short pop-up. Capture phase, so this
       runs before main.js opens the modal. */
    document.addEventListener('click', function (e) {
        var link = e.target.closest ? e.target.closest('a.float-cta, a.mb-quote') : null;
        var form = document.getElementById('start');
        if (!link || !form) { return; }
        e.preventDefault();
        e.stopPropagation();
        form.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' });
    }, true);

    hp.addEventListener('click', function (e) {
        var link = e.target.closest ? e.target.closest('a[href^="#"]') : null;
        if (!link) { return; }
        var id = link.getAttribute('href').slice(1);
        var target = id ? document.getElementById(id) : null;
        if (!target) { return; }
        e.preventDefault();
        if (id === 'start') { track('cta_click', { cta_text: (link.textContent || '').trim().slice(0, 60) }); }
        target.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' });
        target.setAttribute('tabindex', '-1');
        target.focus({ preventScroll: true });
    });
})();
