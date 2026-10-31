<?php /** Contact page. Expects: $page, $form_status, $slug */ ?>
<section class="page-hero">
    <div class="container">
        <p class="eyebrow eyebrow-line">Contact</p>
        <h1><?= e($page['h1']) ?></h1>
        <p class="lede"><?= e($page['lede']) ?></p>
    </div>
</section>

<section class="section">
    <div class="container split contact-split">
        <div class="contact-info">
            <h2>Reach us directly</h2>
            <p>For projects, partnerships or a first conversation.</p>
            <ul class="contact-list">
                <li><strong>Phone:</strong> <a href="<?= e(CONTACT_PHONE_HREF) ?>"><?= e(CONTACT_PHONE) ?></a></li>
                <li><strong>WhatsApp:</strong> <a href="https://wa.me/<?= e(CONTACT_WHATSAPP) ?>" target="_blank" rel="noopener nofollow">Message us</a></li>
                <li><strong>Email:</strong> <a href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a></li>
                <li><strong>Office:</strong> Chennai, Tamil Nadu, India</li>
                <?php if (gst_visible()): ?>
                <li><strong>GSTIN:</strong> <?= e(GSTIN) ?></li>
                <?php endif; ?>
            </ul>
            <div class="panel panel-accent">
                <h3>What happens next?</h3>
                <ol class="mini-steps">
                    <li>We reply within one working day.</li>
                    <li>A short call to understand your goals.</li>
                    <li>A free audit and a clear, fixed proposal.</li>
                </ol>
            </div>
        </div>

        <div class="contact-form-wrap">
            <h2>Tell us what you need</h2>
            <p class="form-lead">A few details help us send a useful reply the first time.</p>

            <?php if ($form_status === 'error'): ?>
                <div class="alert alert-error" role="alert">
                    <?= e(($form_error ?? '') !== '' ? $form_error : 'Please check the fields marked with a star, then try again.') ?>
                </div>
            <?php endif; ?>

            <form class="contact-form raw-lead-form" method="post" action="/contact-us/">
                <?= csrf_field() ?>
                <input type="hidden" name="recaptcha_token" class="js-recaptcha-token">
                <div class="form-grid">
                    <div class="form-row">
                        <label for="f-name">Name *</label>
                        <input id="f-name" name="name" type="text" required autocomplete="name">
                    </div>
                    <div class="form-row">
                        <label for="f-email">Work email *</label>
                        <input id="f-email" name="email" type="email" placeholder="you@company.com" required autocomplete="email">
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-row">
                        <label for="f-mobile">Phone / WhatsApp *</label>
                        <div class="phone-group">
                            <?= country_code_select('country_code', 'f-cc') ?>
                            <input id="f-mobile" name="mobile" type="tel" inputmode="tel" placeholder="98765 43210" required autocomplete="tel-national">
                        </div>
                    </div>
                    <div class="form-row">
                        <label for="f-company">Company *</label>
                        <input id="f-company" name="company" type="text" required autocomplete="organization">
                    </div>
                </div>
                <div class="form-row">
                    <label for="f-designation">Your role</label>
                    <input id="f-designation" name="designation" type="text" autocomplete="organization-title">
                </div>
                <div class="form-grid">
                    <div class="form-row">
                        <label for="f-service">What do you need? *</label>
                        <select id="f-service" name="service" required>
                            <option value="">Select a service</option>
                            <optgroup label="Digital marketing">
                                <option>Search Engine Optimization</option>
                                <option>AI Search Visibility</option>
                                <option>Paid Advertising (PPC)</option>
                                <option>Social Media Marketing</option>
                                <option>Branding</option>
                                <option>Content Marketing</option>
                                <option>Email Marketing</option>
                            </optgroup>
                            <optgroup label="Web development">
                                <option>Website Design and Development</option>
                                <option>AI Development</option>
                            </optgroup>
                            <optgroup label="Enterprise and IT">
                                <option>SAP CPQ</option>
                                <option>Salesforce</option>
                                <option>Odoo ERP</option>
                                <option>ERPNext</option>
                                <option>IT Staffing</option>
                            </optgroup>
                            <option>Not sure yet</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label for="f-budget">Budget</label>
                        <select id="f-budget" name="budget">
                            <option value="">Select your budget</option>
                            <?= budget_options(['One-time project']) ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <label for="f-message">How can we help? *</label>
                    <textarea id="f-message" name="message" rows="5" required minlength="15" placeholder="Tell us about your goal and any deadline"></textarea>
                </div>
                <div class="form-row honeypot" aria-hidden="true">
                    <label for="f-website">Website</label>
                    <input id="f-website" name="website" type="text" tabindex="-1" autocomplete="off">
                </div>
                <button class="btn btn-primary btn-block" type="submit">Send My Enquiry</button>
                <p class="recaptcha-disclosure">Protected by reCAPTCHA. The Google <a href="https://policies.google.com/privacy" target="_blank" rel="noopener nofollow">Privacy Policy</a> and <a href="https://policies.google.com/terms" target="_blank" rel="noopener nofollow">Terms of Service</a> apply.</p>
            </form>
        </div>
    </div>
</section>

<?php render_faqs(page_faqs($page, $slug), 'Before you get in touch'); ?>
