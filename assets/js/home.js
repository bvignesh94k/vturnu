/* ==========================================================================
   VTurnU homepage behaviour.
   Loaded only on "/". Everything here is an enhancement: with this file
   blocked the page still renders, reads and submits correctly.
   ========================================================================== */
(function () {
    'use strict';

    var hp = document.querySelector('.hp');
    if (!hp) { return; }

    /* Tell the stylesheet that JS is available. Reveal-on-scroll and the
       progressive form only switch on behind this class, so a failed script
       leaves every section visible and the form fully submittable. */
    document.documentElement.classList.add('hp-js');

    var reduceQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    var reduce = reduceQuery.matches;
    if (reduceQuery.addEventListener) {
        reduceQuery.addEventListener('change', function (e) { reduce = e.matches; });
    }

    var supportsIO = 'IntersectionObserver' in window;

    /* ---------------------------------------------------------------- rise */

    (function reveal() {
        var items = hp.querySelectorAll('[data-rise]');
        if (!items.length) { return; }

        function showAll() {
            for (var i = 0; i < items.length; i++) { items[i].classList.add('in'); }
        }

        if (!supportsIO || reduce) { showAll(); return; }

        /* Anything already on screen is shown immediately rather than waiting
           for the first observer callback, so the top of the page is never
           blank while the reveal warms up. */
        for (var i = 0; i < items.length; i++) {
            var box = items[i].getBoundingClientRect();
            if (box.top < window.innerHeight && box.bottom > 0) { items[i].classList.add('in'); }
        }

        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) { return; }
                entry.target.classList.add('in');
                io.unobserve(entry.target);
            });
        }, { rootMargin: '0px 0px -12% 0px', threshold: 0.08 });
        for (var j = 0; j < items.length; j++) { io.observe(items[j]); }

        /* Failsafe. If the observer never fires, for any reason, the page must
           not be left with invisible content. Verified as a real failure mode:
           an environment where IntersectionObserver exists but never delivers a
           callback leaves every section at opacity zero without this. */
        window.setTimeout(function () {
            var seen = hp.querySelectorAll('[data-rise].in').length;
            if (seen === 0) { io.disconnect(); showAll(); }
        }, 2500);
    })();

    /* -------------------------------------------------------------- header */

    (function header() {
        var el = document.getElementById('site-header');
        if (!el) { return; }
        var ticking = false;
        function apply() {
            el.classList.toggle('is-stuck', window.scrollY > 24);
            ticking = false;
        }
        window.addEventListener('scroll', function () {
            if (ticking) { return; }
            ticking = true;
            window.requestAnimationFrame(apply);
        }, { passive: true });
        apply();
    })();

    /* -------------------------------------------------------------- signal */

    /* One buyer signal walked from a typed query to a pipeline entry. The
       phrases are stacked in a single grid cell, so swapping them never
       reflows the page. */
    (function signal() {
        var root = document.getElementById('signal');
        if (!root) { return; }
        var phrases = root.querySelectorAll('[data-phrase]');
        var stages = root.querySelectorAll('[data-stage]');
        var rail = root.querySelector('.signal-rail');
        if (!phrases.length || !stages.length) { return; }

        function paint(step) {
            for (var i = 0; i < stages.length; i++) {
                stages[i].classList.toggle('on', i <= step);
            }
            if (rail) {
                var pct = ((step + 1) / stages.length) * 100;
                rail.style.setProperty('--signal-progress', pct + '%');
            }
        }

        // Static final state when motion is unwelcome: everything lit, first
        // phrase shown, nothing moving.
        if (reduce) { paint(stages.length - 1); return; }

        paint(0);

        var step = 0;
        var phrase = 0;
        var timer = null;

        function tick() {
            step = (step + 1) % stages.length;
            paint(step);

            // The query only rewrites on the first two turns: search becoming a
            // question, then a question becoming a request for a recommendation.
            if (step === 1 || step === 2) {
                phrase = step;
                for (var i = 0; i < phrases.length; i++) {
                    phrases[i].classList.toggle('on', i === phrase);
                }
            } else if (step === 0) {
                phrase = 0;
                for (var k = 0; k < phrases.length; k++) {
                    phrases[k].classList.toggle('on', k === 0);
                }
            }
        }

        function start() {
            if (timer) { return; }
            // Slow on purpose. The phrases are long enough to need reading time.
            timer = window.setInterval(tick, 3200);
        }
        function stop() {
            if (!timer) { return; }
            window.clearInterval(timer);
            timer = null;
        }

        // Only animate while the hero is actually on screen, and never while
        // the tab is in the background.
        if (supportsIO) {
            new IntersectionObserver(function (entries) {
                entries[0].isIntersecting ? start() : stop();
            }, { threshold: 0.25 }).observe(root);
        } else {
            start();
        }
        document.addEventListener('visibilitychange', function () {
            document.hidden ? stop() : start();
        });
    })();

    /* -------------------------------------------------------------- system */

    /* The growth system is pinned while its seven stages move sideways. The
       page itself keeps scrolling vertically the whole time, so nothing is
       hijacked: the reader can leave at any moment by scrolling on. */
    (function system() {
        var pin = document.getElementById('system');
        var track = document.getElementById('system-track');
        var bar = document.getElementById('sys-bar');
        if (!pin || !track) { return; }
        var stages = track.querySelectorAll('[data-sys]');

        var travel = 0;
        var active = false;

        function measure() {
            // Below 860px the section is a plain vertical list, and reduced
            // motion drops the pin entirely. Both cases release the height.
            active = window.innerWidth > 860 && !reduce;
            if (!active) {
                pin.style.height = '';
                track.style.setProperty('--track-x', '0px');
                for (var i = 0; i < stages.length; i++) { stages[i].classList.add('on'); }
                return;
            }
            travel = Math.max(0, track.scrollWidth - window.innerWidth);
            // Vertical distance to spend on the horizontal move, plus one
            // viewport so the last stage can be read before the pin releases.
            pin.style.height = (window.innerHeight + travel) + 'px';
            paint();
        }

        function paint() {
            if (!active) { return; }
            var box = pin.getBoundingClientRect();
            var scrolled = Math.min(Math.max(-box.top, 0), travel);
            var progress = travel > 0 ? scrolled / travel : 0;

            track.style.setProperty('--track-x', (-scrolled) + 'px');
            if (bar) { bar.style.width = (progress * 100) + '%'; }

            // Light each stage as the signal reaches it.
            var reached = Math.round(progress * (stages.length - 1));
            for (var i = 0; i < stages.length; i++) {
                stages[i].classList.toggle('on', i <= reached);
            }
        }

        var ticking = false;
        window.addEventListener('scroll', function () {
            if (ticking || !active) { return; }
            ticking = true;
            window.requestAnimationFrame(function () { paint(); ticking = false; });
        }, { passive: true });

        var resizeTimer;
        window.addEventListener('resize', function () {
            window.clearTimeout(resizeTimer);
            resizeTimer = window.setTimeout(measure, 150);
        });

        measure();
        window.addEventListener('load', measure);
    })();

    /* ------------------------------------------------------------ services */

    (function services() {
        var tabs = Array.prototype.slice.call(hp.querySelectorAll('.svc-tab'));
        if (!tabs.length) { return; }

        function select(tab, focus) {
            tabs.forEach(function (t) {
                var on = t === tab;
                t.setAttribute('aria-selected', on ? 'true' : 'false');
                t.setAttribute('tabindex', on ? '0' : '-1');
                var panel = document.getElementById(t.getAttribute('aria-controls'));
                if (!panel) { return; }
                panel.classList.toggle('on', on);
                if (on) { panel.removeAttribute('hidden'); } else { panel.setAttribute('hidden', ''); }
            });
            if (focus) { tab.focus(); }
        }

        tabs.forEach(function (tab, i) {
            tab.addEventListener('click', function () { select(tab, false); });
            tab.addEventListener('keydown', function (e) {
                var next = null;
                if (e.key === 'ArrowDown' || e.key === 'ArrowRight') { next = tabs[(i + 1) % tabs.length]; }
                else if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') { next = tabs[(i - 1 + tabs.length) % tabs.length]; }
                else if (e.key === 'Home') { next = tabs[0]; }
                else if (e.key === 'End') { next = tabs[tabs.length - 1]; }
                if (!next) { return; }
                e.preventDefault();
                select(next, true);
            });
        });
    })();

    /* ------------------------------------------------------------- process */

    (function process() {
        var el = document.getElementById('proc');
        if (!el) { return; }
        if (!supportsIO || reduce) { el.classList.add('on'); return; }
        var io = new IntersectionObserver(function (entries) {
            if (!entries[0].isIntersecting) { return; }
            el.classList.add('on');
            io.disconnect();
        }, { threshold: 0.3 });
        io.observe(el);
    })();

    /* ---------------------------------------------------------------- form */

    /* Progressive disclosure. Without this script both steps are visible and
       the form submits in one go, which is why step two carries no hidden
       attribute in the markup. */
    (function form() {
        var form = document.getElementById('hp-form');
        if (!form) { return; }
        var one = form.querySelector('[data-step="1"]');
        var two = form.querySelector('[data-step="2"]');
        var next = document.getElementById('hp-next');
        var back = document.getElementById('hp-back');
        if (!one || !two || !next) { return; }

        two.setAttribute('hidden', '');

        function err(input, message) {
            var slot = form.querySelector('[data-err-for="' + input.id + '"]');
            if (slot) { slot.textContent = message || ''; }
            if (message) {
                input.setAttribute('aria-invalid', 'true');
            } else {
                input.removeAttribute('aria-invalid');
            }
        }

        function checkName(input) {
            var v = input.value.trim();
            if (!v) { err(input, 'Please tell us your name so we know who we are replying to.'); return false; }
            err(input, '');
            return true;
        }

        function checkEmail(input) {
            var v = input.value.trim();
            if (!v) { err(input, 'We need an email address to send the review to.'); return false; }
            if (!/^[^@\s]+@[^@\s]+\.[^@\s]{2,}$/.test(v)) {
                err(input, 'That does not look like a complete email address. Check for a typo.');
                return false;
            }
            err(input, '');
            return true;
        }

        var nameField = document.getElementById('f-name');
        var emailField = document.getElementById('f-email');

        // Validate on the way out of a field, then keep it honest as they fix it.
        [[nameField, checkName], [emailField, checkEmail]].forEach(function (pair) {
            var field = pair[0], check = pair[1];
            if (!field) { return; }
            field.addEventListener('blur', function () { check(field); });
            field.addEventListener('input', function () {
                if (field.getAttribute('aria-invalid') === 'true') { check(field); }
            });
        });

        next.addEventListener('click', function () {
            var okName = nameField ? checkName(nameField) : true;
            var okEmail = emailField ? checkEmail(emailField) : true;
            if (!okName || !okEmail) {
                var bad = !okName ? nameField : emailField;
                if (bad) { bad.focus(); }
                return;
            }
            one.setAttribute('hidden', '');
            two.removeAttribute('hidden');
            var first = document.getElementById('f-service');
            if (first) { first.focus(); }
        });

        if (back) {
            back.addEventListener('click', function () {
                two.setAttribute('hidden', '');
                one.removeAttribute('hidden');
                if (nameField) { nameField.focus(); }
            });
        }
    })();

    /* ------------------------------------------------------ anchor scrolls */

    hp.addEventListener('click', function (e) {
        var link = e.target.closest ? e.target.closest('a[href^="#"]') : null;
        if (!link) { return; }
        var id = link.getAttribute('href').slice(1);
        if (!id) { return; }
        var target = document.getElementById(id);
        if (!target) { return; }
        e.preventDefault();
        target.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' });
        // Move focus too, or keyboard users are left where they clicked.
        target.setAttribute('tabindex', '-1');
        target.focus({ preventScroll: true });
    });
})();
