/* ==========================================================================
   VTurnU homepage behaviour. Loaded only on "/".

   Everything here is an enhancement. With this file blocked, or with
   reduced motion, the page is complete and the lead form submits as one
   long form.

   1. Hero: starts the 3D crowd (assets/js/hero-crowd.js) once a visitor
      engages. A poster of the same scene is on screen until then.
   2. Depth: scroll reveals, pointer tilt, count-up numbers, flip cards,
      the isometric growth stack and the Turn Method timeline.
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

    /* --------------------------------------------------------------- hero */

    /* The crowd script waits for the first sign of a visitor, then the next idle
       moment, so it never competes with the page loading. With a mouse it also
       starts by itself after a few seconds. */
    (function hero() {
        var canvas = document.getElementById('tt-crowd');
        if (!canvas || reduce) { return; }
        var started = false, EVENTS = ['pointermove', 'pointerdown', 'scroll', 'keydown', 'touchstart'];
        function load() {
            if (started) { return; }
            started = true;
            EVENTS.forEach(function (e) { window.removeEventListener(e, soon); });
            var s = document.createElement('script');
            s.src = canvas.getAttribute('data-src');
            s.async = true;
            document.head.appendChild(s);
            track('hero_crowd', { hero_crowd: 'on' });
        }
        function soon() {
            if ('requestIdleCallback' in window) { window.requestIdleCallback(load, { timeout: 600 }); } else { setTimeout(load, 200); }
        }
        EVENTS.forEach(function (e) { window.addEventListener(e, soon, { passive: true }); });
        if (finePointer) {
            window.addEventListener('load', function () { setTimeout(soon, 3500); });
        }
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
        /* One demonstration turn when the cards first appear, so visitors
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
