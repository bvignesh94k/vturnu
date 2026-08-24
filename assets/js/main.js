/* VTurnU: navigation & small enhancements */
(function () {
    'use strict';

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Scroll-reveal with per-batch stagger. Content stays visible without JS.
    var reveals = document.querySelectorAll('[data-reveal]');
    if (reveals.length && !reduceMotion && 'IntersectionObserver' in window) {
        document.documentElement.classList.add('js');
        var io = new IntersectionObserver(function (entries) {
            var i = 0;
            entries.forEach(function (e) {
                if (!e.isIntersecting) return;
                var el = e.target;
                // Elements entering together cascade in, 80ms apart (capped at 400ms)
                el.style.setProperty('--rd', Math.min(i * 80, 400) + 'ms');
                i++;
                el.classList.add('revealed');
                io.unobserve(el);
                // Once the entrance ends, hand transitions back to hover styles
                el.addEventListener('transitionend', function done(ev) {
                    if (ev.propertyName !== 'opacity') return;
                    el.classList.add('reveal-done');
                    el.style.removeProperty('--rd');
                    el.removeEventListener('transitionend', done);
                });
            });
        }, { threshold: 0.12 });
        reveals.forEach(function (el) { io.observe(el); });
        // Failure-safe: force-reveal anything left after 4s
        setTimeout(function () {
            reveals.forEach(function (el) { el.classList.add('revealed', 'reveal-done'); });
        }, 4000);
    }

    // Scroll progress bar + floating CTA (transform/class-only: cheap enough to run per event).
    // Where CSS scroll-driven animations exist, the bar is driven on the compositor
    // instead and JS leaves it alone.
    var cssScrollTimeline = window.CSS && CSS.supports && CSS.supports('animation-timeline: scroll()');
    var progressBar = document.getElementById('scroll-progress-bar');
    var floatCta = document.getElementById('float-cta');
    if (progressBar || floatCta) {
        var onScroll = function () {
            var y = window.scrollY;
            if (progressBar && !reduceMotion && !cssScrollTimeline) {
                var max = document.documentElement.scrollHeight - window.innerHeight;
                progressBar.style.transform = 'scaleX(' + (max > 0 ? Math.min(y / max, 1) : 0) + ')';
            }
            if (floatCta) {
                floatCta.classList.toggle('show', y > 560);
            }
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    var counters = document.querySelectorAll('[data-count]');
    if (counters.length && !reduceMotion && 'IntersectionObserver' in window) {
        var cio = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (!e.isIntersecting) return;
                cio.unobserve(e.target);
                var target = parseInt(e.target.getAttribute('data-count'), 10);
                var suffix = e.target.getAttribute('data-suffix') || '';
                var prefix = e.target.getAttribute('data-prefix') || '';
                var t0 = performance.now(), dur = 1400;
                var tick = function (now) {
                    var p = Math.min(1, (now - t0) / dur);
                    var eased = 1 - Math.pow(1 - p, 3);
                    e.target.textContent = prefix + Math.round(target * eased) + suffix;
                    if (p < 1) requestAnimationFrame(tick);
                };
                requestAnimationFrame(tick);
            });
        }, { threshold: 0.5 });
        counters.forEach(function (el) { cio.observe(el); });
    }

    // Mobile nav toggle
    var toggle = document.getElementById('nav-toggle');
    var nav = document.getElementById('primary-nav');
    if (toggle && nav) {
        toggle.addEventListener('click', function () {
            var open = nav.classList.toggle('open');
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }

    // Mobile submenu accordions
    document.querySelectorAll('.submenu-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var item = btn.closest('.nav-item');
            var open = item.classList.toggle('submenu-open');
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    });

    // Close mobile nav when resizing to desktop
    window.addEventListener('resize', function () {
        if (window.innerWidth > 920 && nav) {
            nav.classList.remove('open');
            if (toggle) toggle.setAttribute('aria-expanded', 'false');
        }
    });

    // reCAPTCHA v3: fetches a fresh token before any form actually submits.
    // Inert (calls back with an empty token) until a real site key replaces
    // the placeholder and the script tag in header.php is uncommented, so
    // forms keep working normally while CAPTCHA is still being set up.
    var RECAPTCHA_SITE_KEY = '6LfgqIMtAAAAAOM2_Z4QgkIqg6JPWG3sJ9QpWhhg';
    var recaptchaReady = RECAPTCHA_SITE_KEY.indexOf('YOUR_') !== 0;
    var withRecaptcha = function (action, cb) {
        if (recaptchaReady && window.grecaptcha && grecaptcha.execute) {
            grecaptcha.ready(function () {
                grecaptcha.execute(RECAPTCHA_SITE_KEY, { action: action }).then(cb).catch(function () { cb(''); });
            });
        } else {
            cb('');
        }
    };

    // Quote pop-up: every CTA opens the modal instead of navigating/scrolling.
    // Without JS (or without <dialog> support) links fall back to normal navigation.
    var modal = document.getElementById('quote-modal');
    if (modal && typeof modal.showModal === 'function') {
        var openers = document.querySelectorAll(
            'a.btn[href="/contact-us/"], a[href="#quote"], a[href="/#quote"], a.btn[href^="/contact-us/#"], a.float-cta, a.mb-quote'
        );
        openers.forEach(function (a) {
            a.addEventListener('click', function (e) {
                e.preventDefault();
                modal.showModal();
            });
        });

        var closeModal = function () { modal.close(); };
        modal.querySelector('.qm-close').addEventListener('click', closeModal);
        var doneClose = modal.querySelector('.qm-done-close');
        if (doneClose) doneClose.addEventListener('click', closeModal);
        // Click on the backdrop closes
        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeModal();
        });

        var qmForm = modal.querySelector('.qm-form');
        var qmError = modal.querySelector('.qm-error');
        var qmSuccess = modal.querySelector('.qm-success');
        qmForm.addEventListener('submit', function (e) {
            e.preventDefault();
            qmError.hidden = true;
            var btn = qmForm.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.textContent = 'Sending…';
            withRecaptcha('submit', function (token) {
                var tokenField = qmForm.querySelector('.js-recaptcha-token');
                if (tokenField) tokenField.value = token;
                fetch('/enquiry/', { method: 'POST', body: new FormData(qmForm) })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.ok) {
                            qmForm.hidden = true;
                            modal.querySelector('.qm-intro').hidden = true;
                            qmSuccess.hidden = false;
                        } else {
                            qmError.hidden = false;
                        }
                    })
                    .catch(function () { qmError.hidden = false; })
                    .finally(function () {
                        btn.disabled = false;
                        btn.textContent = 'Get My Custom Quote';
                    });
            });
        });
    }

    // Live chat: every trigger (desktop buttons + mobile bar) opens the real
    // Tawk.to widget. If Tawk hasn't finished loading yet, queue the toggle
    // for the moment it does rather than doing nothing on a fast click.
    var liveChatButtons = document.querySelectorAll('.js-live-chat');
    if (liveChatButtons.length) {
        var openLiveChat = function () {
            if (window.Tawk_API && typeof Tawk_API.toggle === 'function') {
                Tawk_API.toggle();
            } else if (window.Tawk_API) {
                var prevOnLoad = Tawk_API.onLoad;
                Tawk_API.onLoad = function () {
                    if (typeof prevOnLoad === 'function') prevOnLoad();
                    Tawk_API.toggle();
                };
            }
        };
        liveChatButtons.forEach(function (btn) {
            btn.addEventListener('click', openLiveChat);
        });
    }

    // Footer newsletter: background submit, inline confirmation
    var newsForm = document.querySelector('.footer-news');
    if (newsForm && window.fetch) {
        newsForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var btn = newsForm.querySelector('button');
            btn.disabled = true;
            withRecaptcha('submit', function (token) {
                var tokenField = newsForm.querySelector('.js-recaptcha-token');
                if (tokenField) tokenField.value = token;
                fetch('/enquiry/', { method: 'POST', body: new FormData(newsForm) })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.ok) {
                            newsForm.hidden = true;
                            var note = document.querySelector('.footer-news-note');
                            if (note) note.hidden = true;
                            var done = document.querySelector('.footer-news-done');
                            if (done) done.hidden = false;
                        } else { btn.disabled = false; }
                    })
                    .catch(function () { btn.disabled = false; });
            });
        });
    }

    // Resource (e-book / guide) lead capture: background submit, success state
    var resForm = document.querySelector('.resource-form');
    if (resForm && window.fetch) {
        resForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var rErr = document.querySelector('.r-error');
            var btn = resForm.querySelector('button[type="submit"]');
            var btnLabel = btn.textContent;
            if (rErr) rErr.hidden = true;
            btn.disabled = true;
            btn.textContent = 'Sending…';
            withRecaptcha('submit', function (token) {
                var tokenField = resForm.querySelector('.js-recaptcha-token');
                if (tokenField) tokenField.value = token;
                fetch('/enquiry/', { method: 'POST', body: new FormData(resForm) })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.ok) {
                            resForm.hidden = true;
                            var ok = document.querySelector('.r-success');
                            if (ok) ok.hidden = false;
                        } else if (rErr) { rErr.hidden = false; }
                    })
                    .catch(function () { if (rErr) rErr.hidden = false; })
                    .finally(function () { btn.disabled = false; btn.textContent = btnLabel; });
            });
        });
    }

    // Real (non-AJAX) lead forms: home page inline quote + contact page.
    // Validate, fetch a reCAPTCHA token, then submit for real so the
    // server-rendered success/error page still works exactly as before.
    document.querySelectorAll('.raw-lead-form').forEach(function (rform) {
        rform.addEventListener('submit', function (e) {
            e.preventDefault();
            var name = rform.querySelector('[name="name"]');
            var email = rform.querySelector('[name="email"]');
            var valid = true;
            [name, email].forEach(function (field) {
                if (!field) return;
                field.style.borderColor = '';
                if (!field.value.trim() || (field.type === 'email' && !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(field.value))) {
                    field.style.borderColor = '#E8365D';
                    valid = false;
                }
            });
            if (!valid) return;
            var btn = rform.querySelector('button[type="submit"]');
            if (btn) btn.disabled = true;
            withRecaptcha('submit', function (token) {
                var tokenField = rform.querySelector('.js-recaptcha-token');
                if (tokenField) tokenField.value = token;
                rform.submit();
            });
        });
    });

    // Free SEO audit page: separate lead-capture form (distinct from the audit
    // checker above it). Background submit via the shared /enquiry/ endpoint.
    var auditLeadForm = document.getElementById('audit-lead-form');
    if (auditLeadForm && window.fetch) {
        auditLeadForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var errBox = auditLeadForm.querySelector('.al-error');
            var success = document.querySelector('.al-success');
            var btn = auditLeadForm.querySelector('button[type="submit"]');
            if (errBox) errBox.hidden = true;
            btn.disabled = true;
            btn.textContent = 'Sending…';
            withRecaptcha('submit', function (token) {
                var tokenField = auditLeadForm.querySelector('.js-recaptcha-token');
                if (tokenField) tokenField.value = token;
                fetch('/enquiry/', { method: 'POST', body: new FormData(auditLeadForm) })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.ok) {
                            auditLeadForm.hidden = true;
                            if (success) success.hidden = false;
                        } else if (errBox) { errBox.hidden = false; }
                    })
                    .catch(function () { if (errBox) errBox.hidden = false; })
                    .finally(function () { btn.disabled = false; btn.textContent = 'Get My Free Consultation'; });
            });
        });
    }

    // Free SEO audit: the request fetches an external site, so it can take a few
    // seconds. Show progress instead of an apparently dead button.
    var auditForm = document.querySelector('.audit-form');
    if (auditForm) {
        auditForm.addEventListener('submit', function (e) {
            var btn = auditForm.querySelector('button[type="submit"]');
            if (!btn || btn.disabled) return;
            e.preventDefault();
            btn.disabled = true;
            btn.classList.add('is-loading');

            var steps = [
                'Fetching your site…',
                'Reading your tags…',
                'Checking robots and sitemap…',
                'Testing AI crawler access…',
                'Scoring your results…'
            ];
            var i = 0;
            btn.textContent = steps[0];
            var timer = setInterval(function () {
                i++;
                if (i >= steps.length) { clearInterval(timer); return; }
                btn.textContent = steps[i];
            }, 1800);

            var tokenField = auditForm.querySelector('.js-recaptcha-token');
            withRecaptcha('submit', function (token) {
                if (tokenField) tokenField.value = token;
                auditForm.submit();
            });
        });
    }

    // Service worker: offline support + faster repeat visits.
    // Registered after load so it never competes with first paint.
    if ('serviceWorker' in navigator && (location.protocol === 'https:' || location.hostname === 'localhost')) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js').catch(function () {
                /* Offline support is a bonus: never break the page over it. */
            });
        });
    }
})();
