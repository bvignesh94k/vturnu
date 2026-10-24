/* ==========================================================================
   VTurnU homepage behaviour. Loaded only on "/".

   Everything here is an enhancement. With this file blocked, or with
   reduced motion, the page renders every scene as normal content and the
   lead form submits as one long form.

   1. Pinned hero: scroll inside the hero plays four scenes while the
      section holds the viewport, and drives the WebGL scene
      (assets/js/hero3d.min.js, loaded after first paint).
   2. Depth: scroll reveals, pointer tilt, count-up numbers, flip cards,
      the isometric growth stack and the Turn Method timeline.
   3. Lead form: a three-step B2B qualifier with inline validation.
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
        var items = hp.querySelectorAll('.hp3-sec [data-rise], .hp3-final [data-rise], .hp3-sec [data-count]');
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
        var mount = document.getElementById('hp3-gl');

        var pinned = false, top = 0, total = 1, current = -1, ticking = false;
        var state = { p: 0, scene: 0, visible: true, pinned: false };
        var gl = null, glTried = false, proofDone = false, seen = {};

        function canPin() {
            return !reduce && window.innerHeight >= 520 && window.CSS && CSS.supports && CSS.supports('position', 'sticky');
        }
        function webglOk() {
            try {
                var c = document.createElement('canvas');
                return !!(window.WebGLRenderingContext && (c.getContext('webgl2') || c.getContext('webgl')));
            } catch (e) { return false; }
        }

        function setScene(i) {
            if (i === current) { return; }
            current = i;
            state.scene = i;
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
        }

        function update() {
            ticking = false;
            if (!pinned) { return; }
            state.p = clamp((window.pageYOffset - top) / total, 0, 1);
            stage.style.setProperty('--p', state.p.toFixed(4));
            setScene(Math.min(3, Math.floor(state.p * 4 + 1e-4)));
            if (cue) { cue.classList.toggle('is-gone', state.p > 0.015); }
        }
        function onScroll() { if (!ticking) { ticking = true; raf(update); } }

        /* The WebGL module is fetched only after the page has painted, only
           when the hero is pinned and only where WebGL works. Until then, and
           if it fails, the hero shows the CSS backdrop and the SVG logo. */
        function loadGL() {
            if (glTried || !mount || !pinned || reduce || !webglOk()) { return; }
            glTried = true;
            var src = mount.getAttribute('data-src');
            if (!src) { return; }
            import(src).then(function (mod) {
                gl = mod.start({
                    mount: mount,
                    stage: stage,
                    markets: stage.querySelectorAll('.hp3-mkt'),
                    funnel: stage.querySelectorAll('.hp3-ftag'),
                    panel: stage.querySelector('.hp3-mpanel'),
                    getState: function () { return state; }
                });
                heroEl.classList.add('hp3-gl-on');
                track('hero_3d', { hero_3d: 'on' });
            }).catch(function () {
                heroEl.classList.remove('hp3-gl-on');
            });
        }
        function whenIdle(fn) {
            if ('requestIdleCallback' in window) { window.requestIdleCallback(fn, { timeout: 1500 }); } else { setTimeout(fn, 400); }
        }

        function setup() {
            pinned = canPin();
            state.pinned = pinned;
            heroEl.classList.toggle('hp3-pin', pinned);
            heroEl.style.setProperty('--hp3-len', (window.innerWidth < 900 ? 380 : 440) + 'svh');
            [rail, cue, bar].forEach(function (el) { if (el) { el.hidden = !pinned; } });
            if (!pinned) {
                heroEl.classList.remove('hp3-gl-on');
                if (gl) { gl.stop(); }
                scenes.forEach(function (s) { s.classList.add('is-active'); s.classList.remove('is-past'); });
                current = -1;
            } else if (gl) {
                heroEl.classList.add('hp3-gl-on');
                gl.resume();
            }
            measure();
            update();
        }

        function goTo(i, smooth) {
            var y = i === 0 ? top : top + total * ((i + 0.15) / 4);
            window.scrollTo({ top: Math.round(y), behavior: smooth && !reduce ? 'smooth' : 'auto' });
        }
        dots.forEach(function (d) {
            d.addEventListener('click', function () { goTo(parseInt(d.getAttribute('data-go'), 10) || 0, true); });
        });

        /* Keyboard users tabbing into a later scene are scrolled to it. */
        stage.addEventListener('focusin', function (e) {
            if (!pinned) { return; }
            var sc = e.target.closest ? e.target.closest('.hp3-scene') : null;
            var i = sc ? scenes.indexOf(sc) : -1;
            if (i > -1 && i !== current) { goTo(i, false); }
        });

        /* Without WebGL, market pins open their card in place. */
        Array.prototype.forEach.call(stage.querySelectorAll('.hp3-mkt .hp3-mpin'), function (btn) {
            btn.addEventListener('click', function () {
                if (heroEl.classList.contains('hp3-gl-on')) { return; }
                var li = btn.parentNode;
                var on = !li.classList.contains('is-active');
                Array.prototype.forEach.call(stage.querySelectorAll('.hp3-mkt.is-active'), function (x) { x.classList.remove('is-active'); });
                li.classList.toggle('is-active', on);
            });
        });

        if (supportsIO) {
            new IntersectionObserver(function (entries) {
                state.visible = entries[0].isIntersecting;
                if (!gl) { return; }
                if (state.visible && pinned) { gl.resume(); } else { gl.stop(); }
            }).observe(heroEl);
        }
        document.addEventListener('visibilitychange', function () {
            if (!gl) { return; }
            if (document.hidden) { gl.stop(); } else if (state.visible && pinned) { gl.resume(); }
        });

        var resizeTimer;
        window.addEventListener('resize', function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function () {
                if (canPin() !== pinned) { setup(); if (pinned) { whenIdle(loadGL); } } else { measure(); update(); }
            }, 150);
        });
        window.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('load', function () { measure(); update(); whenIdle(loadGL); });
        if (document.fonts && document.fonts.ready) { document.fonts.ready.then(function () { measure(); update(); }); }
        if (document.readyState === 'complete') { whenIdle(loadGL); }

        setup();
    })();

    /* ------------------------------------------------------- flip cards */

    (function flips() {
        var cards = Array.prototype.slice.call(hp.querySelectorAll('.hp3-flip'));
        if (!cards.length) { return; }
        cards.forEach(function (card) {
            var btn = card.querySelector('.hp3-flip-in');
            btn.addEventListener('click', function () {
                var on = !card.classList.contains('is-flipped');
                card.classList.toggle('is-flipped', on);
                btn.setAttribute('aria-pressed', on ? 'true' : 'false');
            });
        });
        /* One demonstration flip when the cards first appear, so visitors
           on touch screens know the cards turn. */
        if (!supportsIO || reduce) { return; }
        var io = new IntersectionObserver(function (entries) {
            if (!entries[0].isIntersecting) { return; }
            io.disconnect();
            cards.forEach(function (card, i) {
                setTimeout(function () { card.classList.add('is-flipped'); }, 500 + i * 380);
                setTimeout(function () { card.classList.remove('is-flipped'); }, 2300 + i * 380);
            });
        }, { threshold: 0.6 });
        io.observe(cards[0]);
    })();

    /* ---------------------------------------------- growth stack + method */

    (function scrollScenes() {
        var iso = hp.querySelector('.hp3-iso');
        var isoIn = hp.querySelector('.hp3-iso-in');
        var stack = hp.querySelector('.hp3-stack');
        var engines = Array.prototype.slice.call(hp.querySelectorAll('.hp3-engine'));
        var moves = hp.querySelector('.hp3-moves');
        var moveItems = moves ? Array.prototype.slice.call(moves.querySelectorAll('.hp3-move')) : [];
        var pending = false;

        function activate(key) {
            if (isoIn) { isoIn.setAttribute('data-active', key); }
            engines.forEach(function (en) { en.classList.toggle('is-active', en.getAttribute('data-engine') === key); });
        }
        engines.forEach(function (en) {
            en.addEventListener('mouseenter', function () { activate(en.getAttribute('data-engine')); });
            en.addEventListener('focusin', function () { activate(en.getAttribute('data-engine')); });
        });

        function tick() {
            pending = false;
            var vh = window.innerHeight;
            if (stack && iso && !reduce) {
                var r = stack.getBoundingClientRect();
                iso.style.setProperty('--x', clamp((vh - r.top) / (vh * 0.9), 0, 1).toFixed(3));
                if (r.top < vh && r.bottom > 0) {
                    var best = null, bestD = 1e9;
                    engines.forEach(function (en) {
                        var er = en.getBoundingClientRect();
                        var d = Math.abs((er.top + er.bottom) / 2 - vh * 0.5);
                        if (d < bestD) { bestD = d; best = en; }
                    });
                    if (best && finePointer === false) { activate(best.getAttribute('data-engine')); }
                    else if (best && !isoIn.getAttribute('data-hovering')) { activate(best.getAttribute('data-engine')); }
                }
            }
            if (moves) {
                var mr = moves.getBoundingClientRect();
                var m = reduce ? 1 : clamp((vh * 0.85 - mr.top) / (mr.height + vh * 0.25), 0, 1);
                moves.style.setProperty('--m', m.toFixed(3));
                moveItems.forEach(function (it, i) {
                    it.classList.toggle('is-lit', m >= (i / Math.max(1, moveItems.length - 1)) * 0.92 - 0.001);
                });
            }
        }
        function onScroll() { if (!pending) { pending = true; raf(tick); } }
        window.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('resize', onScroll);
        tick();
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
                else if (FREE.indexOf(v.split('@')[1]) > -1) { msg = 'Please use your company email, so we can research your business before we reply. No company email? Write to ' + (form.getAttribute('data-inbox') || 'connect@vturnu.com') + '.'; }
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
