/* VTurnU: navigation & small enhancements */
(function () {
    'use strict';

    /**
     * Parse a JSON response that may have junk printed before it.
     *
     * Every lead form on the site posts to /enquiry/ and reads back JSON. A
     * plain r.json() throws if anything at all precedes the payload, and the
     * catch branch then tells the visitor their enquiry failed. That happened
     * for real: PHP 8.5 on Vercel emitted a deprecation warning ahead of the
     * JSON, so leads were being saved while every visitor was shown an error.
     *
     * The server side is fixed, but this stays as a safety net, because the
     * failure mode is invisible to us and expensive: the lead is captured and
     * the visitor is told to go away. Falling back to the trailing JSON object
     * keeps the form honest even if some future notice slips through.
     */
    function readJsonTolerantly(response) {
        return response.text().then(function (body) {
            try {
                return JSON.parse(body);
            } catch (e) {
                var match = body.match(/\{[\s\S]*\}\s*$/);
                if (match) {
                    try { return JSON.parse(match[0]); } catch (e2) { /* fall through */ }
                }
                return { ok: false };
            }
        });
    }

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Scroll-reveal with per-batch stagger. Content stays visible without JS.
    var reveals = document.querySelectorAll('[data-reveal]');
    if (reveals.length && !reduceMotion && 'IntersectionObserver' in window) {
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

    /* Desktop mega menu. CSS :hover alone closed the panel the moment the
       pointer crossed the gap under the header, or drifted past the panel edge
       on its way to a link. Opening and closing now run through a short grace
       period, so the panel stays put while the visitor moves to it, and only
       one panel is ever open. */
    (function megaMenu() {
        var items = document.querySelectorAll('.nav-item.has-mega');
        if (!items.length) { return; }
        var desktop = window.matchMedia('(min-width: 921px)');
        var openItem = null, closeTimer = null, openTimer = null;

        function shut(item) {
            item.classList.remove('is-open');
            if (openItem === item) { openItem = null; }
        }
        function show(item) {
            clearTimeout(closeTimer);
            if (openItem && openItem !== item) { shut(openItem); }
            item.classList.add('is-open');
            openItem = item;
        }
        function queueClose() {
            clearTimeout(closeTimer);
            clearTimeout(openTimer);
            closeTimer = setTimeout(function () { if (openItem) { shut(openItem); } }, 320);
        }

        items.forEach(function (item) {
            item.addEventListener('pointerenter', function (e) {
                if (!desktop.matches || e.pointerType === 'touch') { return; }
                clearTimeout(closeTimer);
                clearTimeout(openTimer);
                // Already open somewhere: switch at once. Otherwise pause briefly,
                // so a pointer just passing over the bar opens nothing.
                if (openItem) { show(item); } else { openTimer = setTimeout(function () { show(item); }, 90); }
            });
            item.addEventListener('pointerleave', function (e) {
                if (!desktop.matches || e.pointerType === 'touch') { return; }
                queueClose();
            });
            // Touch on a tablet at desktop width: first tap opens, second follows the link.
            var link = item.querySelector('.nav-link');
            link.addEventListener('click', function (e) {
                if (!desktop.matches || !('ontouchstart' in window)) { return; }
                if (openItem !== item) { e.preventDefault(); show(item); }
            });
            item.addEventListener('focusin', function () { if (desktop.matches) { show(item); } });
            item.addEventListener('focusout', function (e) {
                if (desktop.matches && !item.contains(e.relatedTarget)) { queueClose(); }
            });
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && openItem) {
                var link = openItem.querySelector('.nav-link');
                shut(openItem);
                if (link) { link.focus(); }
            }
        });
        document.addEventListener('pointerdown', function (e) {
            if (openItem && !openItem.contains(e.target)) { shut(openItem); }
        });
    })();

    // Close mobile nav when resizing to desktop
    window.addEventListener('resize', function () {
        if (window.innerWidth > 920 && nav) {
            nav.classList.remove('open');
            if (toggle) toggle.setAttribute('aria-expanded', 'false');
        }
    });

    /* reCAPTCHA v3, loaded only once a visitor starts a form. On every page view
       it cost 400 KB of script that most visitors never needed. */
    var RECAPTCHA_SITE_KEY = '6LfgqIMtAAAAAOM2_Z4QgkIqg6JPWG3sJ9QpWhhg';
    var recaptchaLoading = false;
    var loadRecaptcha = function () {
        if (recaptchaLoading || window.grecaptcha) { return; }
        recaptchaLoading = true;
        var s = document.createElement('script');
        s.src = 'https://www.google.com/recaptcha/api.js?render=' + RECAPTCHA_SITE_KEY;
        s.async = true;
        document.head.appendChild(s);
    };
    var withRecaptcha = function (action, cb) {
        var done = false;
        var finish = function (token) { if (!done) { done = true; cb(token || ''); } };
        loadRecaptcha();
        setTimeout(function () { finish(''); }, 4000);
        (function wait(tries) {
            if (window.grecaptcha && grecaptcha.execute) {
                grecaptcha.ready(function () {
                    grecaptcha.execute(RECAPTCHA_SITE_KEY, { action: action }).then(finish, function () { finish(''); });
                });
            } else if (tries > 0) {
                setTimeout(function () { wait(tries - 1); }, 100);
            }
        })(35);
    };
    window.vtRecaptcha = withRecaptcha;

    /* What a person does and a script does not: time spent, keys pressed, points
       clicked. Sent with every lead as "ms.keys.points" and read by lead_screen()
       on the server. A bot posting straight to the endpoint sends nothing. */
    var armForm = function (form) {
        if (form.dataset.armed) { return; }
        form.dataset.armed = '1';
        var field = document.createElement('input');
        field.type = 'hidden';
        field.name = '_hx';
        form.appendChild(field);
        var t0 = 0, keys = 0, points = 0;
        var mark = function () {
            if (!t0) { t0 = Date.now(); loadRecaptcha(); }
            field.value = (Date.now() - t0) + '.' + keys + '.' + points;
        };
        form.addEventListener('focusin', mark);
        form.addEventListener('keydown', function () { keys++; mark(); });
        form.addEventListener('pointerdown', function () { points++; mark(); });
        form.addEventListener('input', mark);
        form.addEventListener('submit', mark, true);
    };
    document.querySelectorAll('form').forEach(function (f) {
        if (f.querySelector('input[name="csrf_token"]')) { armForm(f); }
    });

    // Quote pop-up: every CTA opens the modal instead of navigating/scrolling.
    // Without JS (or without <dialog> support) links fall back to normal navigation.
    var modal = document.getElementById('quote-modal');
    if (modal && typeof modal.showModal === 'function') {
        /* .js-open-quote is the explicit opt-in used by the homepage CTAs,
           which point at the #start form section: without it they only
           smooth-scrolled to the bottom of the page, so the visitor had to
           travel the whole page before they could type anything. Opening the
           form where they already are removes that step. The href stays a
           real anchor so the CTA still works with JS disabled. */
        var openers = document.querySelectorAll(
            'a.btn[href="/contact-us/"], a[href="#quote"], a[href="/#quote"], ' +
            'a.btn[href^="/contact-us/#"], a.float-cta, a.mb-quote, .js-open-quote'
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
            if (!qmForm.reportValidity()) { return; }
            var btn = qmForm.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.textContent = 'Sending…';
            withRecaptcha('submit', function (token) {
                var tokenField = qmForm.querySelector('.js-recaptcha-token');
                if (tokenField) tokenField.value = token;
                fetch('/enquiry/', { method: 'POST', body: new FormData(qmForm) })
                    .then(readJsonTolerantly)
                    .then(function (data) {
                        if (data.ok) {
                            /* Go to the real thank-you page. The old behaviour
                               revealed a success panel while leaving the form
                               and its submit button on screen above it, which
                               read as though nothing had happened. */
                            window.location.href = data.redirect || '/thank-you/';
                        } else {
                            if (data.error) { qmError.textContent = data.error; }
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

    /* Tawk arrives through Tag Manager and may finish loading after its own
       onLoad hook has run, so check until the launcher is confirmed hidden. */
    var tawkTries = 0;
    var tawkHide = setInterval(function () {
        var api = window.Tawk_API;
        if (api && api.isChatHidden && api.hideWidget) {
            if (!api.isChatHidden()) { api.hideWidget(); }
            clearInterval(tawkHide);
        } else if (++tawkTries > 40) {
            clearInterval(tawkHide);
        }
    }, 500);

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
                    .then(readJsonTolerantly)
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

    // Enterprise landing page form: background submit, same thank-you page.
    var lpForm = document.querySelector('.lp-form');
    if (lpForm && window.fetch) {
        lpForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var box = lpForm.querySelector('.lp-error');
            var btn = lpForm.querySelector('button[type="submit"]');
            var label = btn.textContent;
            box.hidden = true;
            btn.disabled = true;
            btn.textContent = 'Sending…';
            withRecaptcha('submit', function (token) {
                var tokenField = lpForm.querySelector('.js-recaptcha-token');
                if (tokenField) tokenField.value = token;
                fetch('/enquiry/', { method: 'POST', body: new FormData(lpForm) })
                    .then(readJsonTolerantly)
                    .then(function (data) {
                        if (data.ok) {
                            window.location.href = data.redirect || '/thank-you/';
                        } else {
                            box.textContent = data.error || 'Please check the form and try again, or email connect@vturnu.com.';
                            box.hidden = false;
                        }
                    })
                    .catch(function () {
                        box.textContent = 'That did not go through. Please try again, or email connect@vturnu.com.';
                        box.hidden = false;
                    })
                    .finally(function () { btn.disabled = false; btn.textContent = label; });
            });
        });
    }

    // Real (non-AJAX) lead forms: home page inline quote + contact page.
    // Validate, fetch a reCAPTCHA token, then submit for real so the
    // server-rendered success/error page still works exactly as before.
    document.querySelectorAll('.raw-lead-form').forEach(function (rform) {
        rform.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!rform.reportValidity()) { return; }
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
                    .then(readJsonTolerantly)
                    .then(function (data) {
                        if (data.ok) {
                            window.location.href = data.redirect || '/thank-you/?s=audit-consultation';
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

    // Currency switcher: apply on change, so the Set button is only the
    // no-JavaScript fallback.
    document.querySelectorAll('.cur-switch').forEach(function (f) {
        var sel = f.querySelector('.js-cur-select');
        var go = f.querySelector('.js-cur-go');
        if (!sel) return;
        if (go) go.hidden = true;
        sel.addEventListener('change', function () { f.submit(); });
    });

    // Pointer tilt on cards: a little physical depth, desktop only.
    if (!reduceMotion && window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
        document.querySelectorAll('.card, .tilt, .price-card, .client-card').forEach(function (card) {
            if (card.closest('.hp')) { return; }
            var raf = 0;
            card.addEventListener('pointermove', function (e) {
                if (raf) { return; }
                raf = requestAnimationFrame(function () {
                    raf = 0;
                    var r = card.getBoundingClientRect();
                    var x = (e.clientX - r.left) / r.width - 0.5;
                    var y = (e.clientY - r.top) / r.height - 0.5;
                    card.style.setProperty('--ry', (x * 6).toFixed(2) + 'deg');
                    card.style.setProperty('--rx', (y * -5).toFixed(2) + 'deg');
                });
            });
            card.addEventListener('pointerleave', function () {
                card.style.removeProperty('--ry');
                card.style.removeProperty('--rx');
            });
        });
    }

    /* Hero 3D scene. Added after load and idle, as decoration: the heading is
       already painted, so it cannot affect LCP or block the first tap. */
    var sceneName = document.body.getAttribute('data-scene');
    var sceneHost = document.querySelector('.page-hero');
    if (sceneName && sceneHost) {
        var startScene = function () {
            var s = document.createElement('script');
            s.src = document.body.getAttribute('data-scene-src') || '/assets/js/scene3d.js';
            s.async = true;
            document.head.appendChild(s);
        };
        window.addEventListener('load', function () {
            if ('requestIdleCallback' in window) { requestIdleCallback(startScene, { timeout: 1800 }); } else { setTimeout(startScene, 900); }
        });
    }

    // Reading progress and chapter highlight on e-book and guide pages.
    var toc = document.querySelector('.reader-toc');
    if (toc && 'IntersectionObserver' in window) {
        var links = {};
        toc.querySelectorAll('a[href^="#"]').forEach(function (a) { links[a.getAttribute('href').slice(1)] = a; });
        var tocIo = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) {
                if (!en.isIntersecting || !links[en.target.id]) { return; }
                Object.keys(links).forEach(function (k) { links[k].classList.remove('is-here'); });
                links[en.target.id].classList.add('is-here');
            });
        }, { rootMargin: '-20% 0px -70% 0px' });
        Object.keys(links).forEach(function (id) {
            var el = document.getElementById(id);
            if (el) { tocIo.observe(el); }
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
