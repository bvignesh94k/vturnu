/* ============================================================================
   VTurnU: Conversion-focused homepage behaviour
   Simple, fast, focused on form conversion and progressive enhancement.
   ============================================================================ */
(function () {
    'use strict';

    var hp = document.querySelector('.hp');
    if (!hp) { return; }

    document.documentElement.classList.add('hp-js');

    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var supportsIO = 'IntersectionObserver' in window;

    /* ---------------------------------------------------------------- reveal */
    (function reveal() {
        var items = hp.querySelectorAll('[data-rise]');
        if (!items.length) { return; }

        function showAll() {
            for (var i = 0; i < items.length; i++) { items[i].classList.add('in'); }
        }

        if (!supportsIO || reduce) { showAll(); return; }

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

        window.setTimeout(function () {
            if (hp.querySelectorAll('[data-rise].in').length === 0) { io.disconnect(); showAll(); }
        }, 2500);
    })();

    /* ----------------------------------------------------------------- form */
    (function form() {
        var form = document.getElementById('hp-form');
        if (!form) { return; }
        var nameField = document.getElementById('f-name');
        var emailField = document.getElementById('f-email');
        if (!nameField || !emailField) { return; }

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
            if (!v) { err(input, 'Please tell us your name.'); return false; }
            err(input, '');
            return true;
        }

        function checkEmail(input) {
            var v = input.value.trim();
            if (!v) { err(input, 'We need an email address.'); return false; }
            if (!/^[^@\s]+@[^@\s]+\.[^@\s]{2,}$/.test(v)) {
                err(input, 'That doesn\'t look like a complete email address.');
                return false;
            }
            err(input, '');
            return true;
        }

        [[nameField, checkName], [emailField, checkEmail]].forEach(function (pair) {
            var field = pair[0], check = pair[1];
            if (!field) { return; }
            field.addEventListener('blur', function () { check(field); });
            field.addEventListener('input', function () {
                if (field.getAttribute('aria-invalid') === 'true') { check(field); }
            });
        });

        form.addEventListener('submit', function (e) {
            var okName = checkName(nameField);
            var okEmail = checkEmail(emailField);
            if (!okName || !okEmail) {
                e.preventDefault();
                var bad = !okName ? nameField : emailField;
                if (bad) { bad.focus(); }
            }
        });
    })();

    /* ----------------------------------------------- reCAPTCHA token injection */
    if (window.grecaptcha && document.getElementById('recaptcha-response')) {
        window.grecaptcha.ready(function () {
            window.grecaptcha.execute('6LfgqIMtAAAAAOM2_Z4QgkIqg6JPWG3sJ9QpWhhg', { action: 'submit' }).then(function (token) {
                document.getElementById('recaptcha-response').value = token;
            });
        });
    }

    /* ------------------------------------------------------ anchor smooth scroll */
    hp.addEventListener('click', function (e) {
        var link = e.target.closest ? e.target.closest('a[href^="#"]') : null;
        if (!link) { return; }
        var id = link.getAttribute('href').slice(1);
        if (!id) { return; }
        var target = document.getElementById(id);
        if (!target) { return; }
        e.preventDefault();
        target.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' });
        target.setAttribute('tabindex', '-1');
        target.focus({ preventScroll: true });
    });
})();
