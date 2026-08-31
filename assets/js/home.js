/* ==========================================================================
   VTurnU homepage behaviour.
   Loaded only on "/". Everything here is an enhancement: with this file
   blocked the page still renders, reads and submits correctly.
   ========================================================================== */
(function () {
    'use strict';

    var hp = document.querySelector('.hp');
    if (!hp) { return; }

    document.documentElement.classList.add('hp-js');

    var reduceQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    var reduce = reduceQuery.matches;
    var supportsIO = 'IntersectionObserver' in window;

    /* ---------------------------------------------------------------- rise */

    (function reveal() {
        var items = hp.querySelectorAll('[data-rise]');
        if (!items.length) { return; }

        function showAll() {
            for (var i = 0; i < items.length; i++) { items[i].classList.add('in'); }
        }

        if (!supportsIO || reduce) { showAll(); return; }

        /* Anything already on screen is shown at once, so the top of the page
           is never blank while the observer warms up. */
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
        }, { rootMargin: '0px 0px -10% 0px', threshold: 0 });
        for (var j = 0; j < items.length; j++) { io.observe(items[j]); }

        /* Failsafe. If the observer never fires the page must not be left with
           invisible content, so reveal everything after a short wait. */
        window.setTimeout(function () {
            if (hp.querySelectorAll('[data-rise].in').length === 0) { io.disconnect(); showAll(); }
        }, 2500);
    })();

    /* ---------------------------------------------------------------- form */

    (function form() {
        var form = document.getElementById('hp-form');
        if (!form) { return; }

        var name = document.getElementById('f-name');
        var email = document.getElementById('f-email');

        function err(input, message) {
            var slot = form.querySelector('[data-err-for="' + input.id + '"]');
            if (slot) { slot.textContent = message || ''; }
            if (message) {
                input.setAttribute('aria-invalid', 'true');
            } else {
                input.removeAttribute('aria-invalid');
            }
        }

        function checkName() {
            if (!name) { return true; }
            if (!name.value.trim()) { err(name, 'Please tell us your name so we know who we are replying to.'); return false; }
            err(name, '');
            return true;
        }

        function checkEmail() {
            if (!email) { return true; }
            var v = email.value.trim();
            if (!v) { err(email, 'We need an email address to send the audit to.'); return false; }
            if (!/^[^@\s]+@[^@\s]+\.[^@\s]{2,}$/.test(v)) {
                err(email, 'That does not look like a complete email address.');
                return false;
            }
            err(email, '');
            return true;
        }

        [[name, checkName], [email, checkEmail]].forEach(function (pair) {
            var field = pair[0], check = pair[1];
            if (!field) { return; }
            field.addEventListener('blur', check);
            field.addEventListener('input', function () {
                if (field.getAttribute('aria-invalid') === 'true') { check(); }
            });
        });

        form.addEventListener('submit', function (e) {
            var okName = checkName();
            var okEmail = checkEmail();
            if (okName && okEmail) { return; }
            e.preventDefault();
            var bad = !okName ? name : email;
            if (bad) { bad.focus(); }
        });
    })();

    /* ------------------------------------------------------ anchor scrolls */

    hp.addEventListener('click', function (e) {
        var link = e.target.closest ? e.target.closest('a[href^="#"]') : null;
        if (!link) { return; }
        /* Modal CTAs are handled in main.js, which opens the quote pop-up.
           Without this guard both listeners fire on the same click and the
           page scrolls to the form behind the dialog that just opened. */
        if (link.classList.contains('js-open-quote')) { return; }
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
