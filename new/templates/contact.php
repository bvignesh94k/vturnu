<?php /** Contact page, form fields per "VTurnU - New Menu Structure.xlsx". Expects: $page, $form_status */ ?>
<section class="page-hero">
    <div class="container">
        <h1><?= e($page['h1']) ?></h1>
        <p class="lede"><?= e($page['lede']) ?></p>
    </div>
</section>

<section class="section">
    <div class="container split contact-split">
        <div class="contact-info">
            <h2>Reach out to us</h2>
            <p>For collaborations, projects, or just a conversation to get started.</p>
            <h3>Our Coordinates</h3>
            <ul class="contact-list">
                <li><strong>Phone:</strong> <a href="<?= e(CONTACT_PHONE_HREF) ?>"><?= e(CONTACT_PHONE) ?></a></li>
                <li><strong>Sales:</strong> <a href="<?= e(CONTACT_PHONE_HREF) ?>"><?= e(CONTACT_PHONE) ?></a></li>
                <li><strong>Email:</strong> <a href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a></li>
            </ul>
            <div class="panel panel-accent">
                <h3>What happens next?</h3>
                <ol class="mini-steps">
                    <li>We reply within 24 hours.</li>
                    <li>A short call to understand your goals.</li>
                    <li>A free audit and a clear, fixed proposal.</li>
                </ol>
            </div>
        </div>

        <div class="contact-form-wrap">
            <h2>Drop Your Enquiry</h2>

            <?php if ($form_status === 'success'): ?>
                <div class="alert alert-success" role="status">
                    <strong>Thank you!</strong> Your enquiry has been received. We'll get back to you within 24 hours.
                </div>
            <?php elseif ($form_status === 'error'): ?>
                <div class="alert alert-error" role="alert">
                    Please fill in your name and a valid email address, then try again.
                </div>
            <?php endif; ?>

            <form class="contact-form raw-lead-form" method="post" action="/contact-us/" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="recaptcha_token" class="js-recaptcha-token">
                <div class="form-row">
                    <label for="f-name">Name *</label>
                    <input id="f-name" name="name" type="text" placeholder="Enter Name" required autocomplete="name">
                </div>
                <div class="form-row">
                    <label for="f-email">Email *</label>
                    <input id="f-email" name="email" type="email" placeholder="Enter Email" required autocomplete="email">
                </div>
                <div class="form-grid">
                    <div class="form-row">
                        <label for="f-mobile">Mobile Number</label>
                        <input id="f-mobile" name="mobile" type="tel" placeholder="Mobile Number" value="<?= e($visitor_dial_code) ?> " autocomplete="tel">
                    </div>
                    <div class="form-row">
                        <label for="f-company">Company Name</label>
                        <input id="f-company" name="company" type="text" placeholder="Enter Company Name" autocomplete="organization">
                    </div>
                </div>
                <div class="form-row">
                    <label for="f-designation">Designation</label>
                    <input id="f-designation" name="designation" type="text" placeholder="Enter Designation" autocomplete="organization-title">
                </div>
                <div class="form-grid">
                    <div class="form-row">
                        <label for="f-service">Services Required</label>
                        <select id="f-service" name="service">
                            <option value="">Select a service</option>
                            <option>Search Engine Optimization</option>
                            <option>AI SEO / AI Search Optimization</option>
                            <option>Paid Advertising (PPC)</option>
                            <option>Social Media Marketing</option>
                            <option>Content Marketing</option>
                            <option>Email Marketing</option>
                            <option>Web Design &amp; Development</option>
                            <option>AI Development</option>
                            <option>Other / Not sure yet</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label for="f-budget">Your Budget</label>
                        <select id="f-budget" name="budget">
                            <option value="">Select your budget</option>
                            <option>Under ₹50,000 / month</option>
                            <option>₹50,000 – ₹1,50,000 / month</option>
                            <option>₹1,50,000 – ₹5,00,000 / month</option>
                            <option>₹5,00,000+ / month</option>
                            <option>One-time project</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <label for="f-message">Message</label>
                    <textarea id="f-message" name="message" rows="5" placeholder="Let Us Know What You Need Help With"></textarea>
                </div>
                <div class="form-row honeypot" aria-hidden="true">
                    <label for="f-website">Website</label>
                    <input id="f-website" name="website" type="text" tabindex="-1" autocomplete="off">
                </div>
                <button class="btn btn-primary btn-block" type="submit">Submit an Enquiry</button>
                <p class="recaptcha-disclosure">This site is protected by reCAPTCHA and the Google <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Privacy Policy</a> and <a href="https://policies.google.com/terms" target="_blank" rel="noopener">Terms of Service</a> apply.</p>
            </form>
        </div>
    </div>
</section>
