<?php /** Shared footer. Expects: $FOOTER (label => [[label, url], …]) */ ?>
</main>

<?php if ($slug !== ''): /* home has its own #quote conversion section */ ?>
<section class="cta-band">
    <div class="container cta-band-inner">
        <div>
            <h2>Let's Talk Possibilities.</h2>
            <p>Whether you're scaling your brand, launching a new product, or reimagining your digital presence: we're here to listen, think, and build with you.</p>
        </div>
        <a class="btn btn-dark" href="/contact-us/">Drop Your Enquiry</a>
    </div>
</section>
<?php endif; ?>

<footer class="site-footer">
    <div class="container">
        <div class="footer-top">
            <div class="footer-brand-col">
                <img src="/assets/img/vturnu-logo-horizontal.png" alt="<?= e(SITE_NAME) ?>, <?= e(SITE_TAGLINE) ?>" width="200" height="41" loading="lazy" decoding="async">
                <p class="footer-blurb">Performance-driven digital marketing, web &amp; AI development from Chennai: serving growth-serious brands across India, the US, UK and Australia. Every engagement maps to a revenue metric.</p>
                <div class="footer-ratings" aria-label="Ratings">
                    <span><span class="stars" aria-hidden="true">★★★★★</span> 5.0 on Google</span>
                    <span>Clutch · GoodFirms listed</span>
                </div>
                <div class="footer-social">
                    <?php $icons = ['LinkedIn' => 'in', 'Facebook' => 'f', 'Twitter' => '𝕏', 'Instagram' => 'ig']; ?>
                    <?php foreach (SOCIAL_LINKS as $name => $url): ?>
                    <a href="<?= e($url) ?>" target="_blank" rel="noopener" aria-label="<?= e($name) ?>"><?= e($icons[$name] ?? substr($name, 0, 2)) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>

            <nav class="footer-links" aria-label="Footer">
                <?php foreach ($FOOTER as $heading => $links): ?>
                <div class="footer-col">
                    <span class="footer-heading"><?= e($heading) ?></span>
                    <ul>
                        <?php foreach ($links as [$label, $url]): ?>
                        <li><a href="<?= e($url) ?>"><?= e($label) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endforeach; ?>
            </nav>

            <div class="footer-contact-col">
                <span class="footer-heading">Talk to us</span>
                <ul class="footer-contact-list">
                    <li><a href="<?= e(CONTACT_PHONE_HREF) ?>"><?= e(CONTACT_PHONE) ?></a><small>Mon–Sat · reply within 24h</small></li>
                    <li><a href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a><small>Enquiries &amp; proposals</small></li>
                    <li><a href="https://wa.me/<?= e(CONTACT_WHATSAPP) ?>" target="_blank" rel="noopener">WhatsApp us</a><small>Fastest way to reach the team</small></li>
                </ul>
                <span class="footer-heading">Monthly playbooks</span>
                <form class="footer-news" method="post" action="/contact-us/" aria-label="Newsletter signup">
                    <?= csrf_field() ?>
                    <input type="hidden" name="recaptcha_token" class="js-recaptcha-token">
                    <input type="email" name="email" placeholder="you@company.com" required autocomplete="email" aria-label="Email address">
                    <input type="hidden" name="name" value="Newsletter subscriber">
                    <input type="hidden" name="service" value="Newsletter signup">
                    <div class="honeypot" aria-hidden="true"><input name="website" type="text" tabindex="-1" autocomplete="off"></div>
                    <button class="btn btn-grad" type="submit">Join</button>
                </form>
                <p class="footer-news-note">One useful email a month. No spam, ever.</p>
                <p class="footer-news-done" hidden>✓ You're in, see you in the next issue.</p>
            </div>
        </div>

        <div class="footer-bottom">
            <?php /* The brand name links home: standard on agency sites, and it
                     gives every page one more internal link to the homepage. */ ?>
            <p>Copyright &copy; <?= date('Y') ?> <a class="copy-brand" href="/">VTurnU</a>. All rights reserved.</p>
            <ul class="footer-legal-links">
                <li><a href="/privacy-policy/">Privacy Policy</a></li>
                <li><a href="/terms-and-conditions/">Terms &amp; Conditions</a></li>
                <?php /* Points at the readable sitemap, not /sitemap.xml.
                         The XML file still exists for crawlers and is still
                         declared in robots.txt; it just is not something to
                         hand a visitor. */ ?>
                <li><a href="/sitemap/">Sitemap</a></li>
            </ul>
        </div>
    </div>
</footer>

<a class="float-cta" id="float-cta" href="/contact-us/">
    <span class="float-dot" aria-hidden="true"></span>
    Get a Free Quote
</a>

<!-- Desktop-only quick actions: WhatsApp + live chat. The mobile bar below covers phones/tablets. -->
<div class="desktop-quick-actions">
    <a class="dqa-btn dqa-wa" href="https://wa.me/919363731498?text=Hi%20VTurnU%2C%20I%27d%20like%20to%20grow%20my%20business.%20Can%20we%20talk%3F" target="_blank" rel="noopener" aria-label="Chat on WhatsApp">
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 18.2c-1.6 0-3.1-.4-4.4-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2zm4.6-6.1c-.3-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1-.2.3-.6.8-.8 1-.1.2-.3.2-.5.1a6.7 6.7 0 0 1-3.4-3c-.3-.4 0-.5.1-.7l.4-.5c.1-.2.2-.3.3-.5s0-.4 0-.5l-.8-1.9c-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3-.2.3-.9.9-.9 2.2s.9 2.5 1 2.7c.1.2 1.8 2.8 4.4 3.9.6.3 1.1.4 1.5.6.6.2 1.2.2 1.6.1.5-.1 1.5-.6 1.7-1.2.2-.6.2-1.1.2-1.2l-.4-.2z"/></svg>
    </a>
    <button class="dqa-btn dqa-chat js-live-chat" type="button" aria-label="Open live chat">
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 3C6.5 3 2 6.9 2 11.7c0 2.7 1.4 5.1 3.7 6.7L5 22l4-1.8c1 .2 2 .3 3 .3 5.5 0 10-3.9 10-8.8S17.5 3 12 3zm-4 10a1.3 1.3 0 1 1 0-2.6 1.3 1.3 0 0 1 0 2.6zm4 0a1.3 1.3 0 1 1 0-2.6 1.3 1.3 0 0 1 0 2.6zm4 0a1.3 1.3 0 1 1 0-2.6 1.3 1.3 0 0 1 0 2.6z"/></svg>
    </button>
</div>

<!-- Sticky mobile action bar: WhatsApp · Get a Quote · Live chat -->
<div class="mobile-bar" role="navigation" aria-label="Quick actions">
    <a class="mb-btn mb-wa" href="https://wa.me/919363731498?text=Hi%20VTurnU%2C%20I%27d%20like%20to%20grow%20my%20business.%20Can%20we%20talk%3F" target="_blank" rel="noopener" aria-label="Chat on WhatsApp">
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 18.2c-1.6 0-3.1-.4-4.4-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2zm4.6-6.1c-.3-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1-.2.3-.6.8-.8 1-.1.2-.3.2-.5.1a6.7 6.7 0 0 1-3.4-3c-.3-.4 0-.5.1-.7l.4-.5c.1-.2.2-.3.3-.5s0-.4 0-.5l-.8-1.9c-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3-.2.3-.9.9-.9 2.2s.9 2.5 1 2.7c.1.2 1.8 2.8 4.4 3.9.6.3 1.1.4 1.5.6.6.2 1.2.2 1.6.1.5-.1 1.5-.6 1.7-1.2.2-.6.2-1.1.2-1.2l-.4-.2z"/></svg>
        <span>WhatsApp</span>
    </a>
    <a class="mb-btn mb-quote" href="/contact-us/">
        <span class="float-dot" aria-hidden="true"></span>
        Get a Quote
    </a>
    <button class="mb-btn mb-chat js-live-chat" type="button">
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 3C6.5 3 2 6.9 2 11.7c0 2.7 1.4 5.1 3.7 6.7L5 22l4-1.8c1 .2 2 .3 3 .3 5.5 0 10-3.9 10-8.8S17.5 3 12 3zm-4 10a1.3 1.3 0 1 1 0-2.6 1.3 1.3 0 0 1 0 2.6zm4 0a1.3 1.3 0 1 1 0-2.6 1.3 1.3 0 0 1 0 2.6zm4 0a1.3 1.3 0 1 1 0-2.6 1.3 1.3 0 0 1 0 2.6z"/></svg>
        <span>Live Chat</span>
    </button>
</div>

<dialog class="quote-modal" id="quote-modal" aria-labelledby="qm-title">
    <button class="qm-close" type="button" aria-label="Close dialog">×</button>
    <div class="qm-body">
        <div class="qm-intro">
            <p class="eyebrow">Get a quote</p>
            <h2 id="qm-title">Let's Talk Possibilities.</h2>
            <p class="qm-sub">Share a few details and we'll reply within one business day, with a free mini-audit and honest next steps.</p>
        </div>
        <form class="contact-form qm-form" method="post" action="/contact-us/">
            <?= csrf_field() ?>
            <input type="hidden" name="recaptcha_token" class="js-recaptcha-token">
            <div class="form-grid">
                <div class="form-row"><label for="qm-name">Your name *</label><input id="qm-name" name="name" type="text" placeholder="Full name" required autocomplete="name"></div>
                <div class="form-row"><label for="qm-email">Email *</label><input id="qm-email" name="email" type="email" placeholder="you@company.com" required autocomplete="email"></div>
            </div>
            <div class="form-grid">
                <div class="form-row"><label for="qm-phone">Phone / WhatsApp</label>
                    <div class="phone-group"><?= country_code_select('country_code', 'qm-cc') ?><input id="qm-phone" name="phone" type="tel" inputmode="tel" placeholder="98765 43210" autocomplete="tel-national"></div>
                </div>
                <div class="form-row"><label for="qm-company">Company</label><input id="qm-company" name="company" type="text" placeholder="Business name" autocomplete="organization"></div>
            </div>
            <div class="form-grid">
                <div class="form-row">
                    <label for="qm-service">What do you need?</label>
                    <select id="qm-service" name="service">
                        <option>SEO / AEO</option>
                        <option>Paid advertising (Google / Meta)</option>
                        <option>Social media marketing</option>
                        <option>Web development</option>
                        <option>AI development</option>
                        <option>Design &amp; branding</option>
                        <option>Full-stack growth (multiple)</option>
                    </select>
                </div>
                <div class="form-row">
                    <label for="qm-budget">Monthly budget range</label>
                    <select id="qm-budget" name="budget">
                        <option>Under ₹50k / $600</option>
                        <option>₹50k–₹1.5L / $600–$1,800</option>
                        <option>₹1.5L–₹5L / $1,800–$6,000</option>
                        <option>₹5L+ / $6,000+</option>
                        <option>One-time project, need a quote</option>
                    </select>
                </div>
            </div>
            <div class="form-row"><label for="qm-message">Tell us about your goals</label><textarea id="qm-message" name="message" rows="3" placeholder="e.g. We want more qualified leads from Google in the next 6 months…"></textarea></div>
            <div class="form-row honeypot" aria-hidden="true"><label for="qm-website">Website</label><input id="qm-website" name="website" type="text" tabindex="-1" autocomplete="off"></div>
            <div class="alert alert-error qm-error" role="alert" hidden>Please fill in your name and a valid email address, then try again.</div>
            <button class="btn btn-grad btn-block" type="submit">Get My Custom Quote</button>
            <p class="form-privacy">We reply within 1 business day. Your details stay private.</p>
            <p class="recaptcha-disclosure">This site is protected by reCAPTCHA and the Google <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Privacy Policy</a> and <a href="https://policies.google.com/terms" target="_blank" rel="noopener">Terms of Service</a> apply.</p>
        </form>
        <div class="quote-done qm-success" hidden>
            <span class="quote-done-icon" aria-hidden="true">✓</span>
            <h3>Request received</h3>
            <p>We'll review your details and reply within one business day with your mini-audit and quote.</p>
            <button class="btn btn-ghost qm-done-close" type="button">Close</button>
        </div>
    </div>
</dialog>

<script src="/assets/js/main.js" defer></script>
<?php if ($slug === ""): $home_js_v = @filemtime(BASE_PATH . "/assets/js/home.js") ?: time(); ?>
<script src="/assets/js/home.js?v=<?= $home_js_v ?>" defer></script>
<?php endif; ?>

<!--
    Tawk.to live chat. Replace PROPERTY_ID/WIDGET_ID below with the values from
    your Tawk dashboard (Administration > Channels > Chat Widget > embed code),
    then remove this comment wrapper so the script tag is live.

<script type="text/javascript">
var Tawk_API = Tawk_API || {};
// Hide Tawk's own floating bubble: our WhatsApp/Live Chat buttons drive it instead,
// so visitors don't see two overlapping chat launchers.
Tawk_API.onLoad = function () { Tawk_API.hideWidget(); };
(function () {
    var s1 = document.createElement("script"), s0 = document.getElementsByTagName("script")[0];
    s1.async = true;
    s1.src = 'https://embed.tawk.to/PROPERTY_ID/WIDGET_ID';
    s1.charset = 'UTF-8';
    s1.setAttribute('crossorigin', '*');
    s0.parentNode.insertBefore(s1, s0);
})();
</script>
-->

</body>
</html>
