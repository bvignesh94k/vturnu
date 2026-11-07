<?php /** Contact page. Expects: $page, $form_status, $slug */ ?>
<?php render_hero([
    'k' => 'Contact', 'h1' => $page['h1'], 'lede' => $page['lede'], 'tone' => 'peach', 'layout' => 'flip',
    'visual' => hero_visual(['reply', 'A senior strategist replies within one working day.']),
]); ?>

<section class="section">
    <div class="container split contact-split">
        <div class="contact-info">
            <p class="contact-budget">Projects start from <strong><?= e(min_budget()) ?></strong>.</p>
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
            <?php render_trust_badges('card', 'Rated and listed on'); ?>
            <div class="panel panel-accent">
                <h3>What happens next?</h3>
                <ol class="mini-steps">
                    <li>We reply within one working day.</li>
                    <li>A short call to understand your goals.</li>
                    <li>A free audit and a clear, fixed proposal.</li>
                </ol>
            </div>
        </div>

        <div class="contact-form-wrap lf-card">
            <h2>Tell us what you need</h2>
            <p class="form-lead">A few details help us send a useful reply the first time. Projects start from <?= e(min_budget()) ?>.</p>

            <?php if ($form_status === 'error'): ?>
                <div class="alert alert-error" role="alert">
                    <?= e(($form_error ?? '') !== '' ? $form_error : 'Please check the fields marked with a star, then try again.') ?>
                </div>
            <?php endif; ?>

            <?php render_lead_form_card(['uid' => 'ct', 'source' => 'contact-page', 'service' => lf_old('service', ''), 'button' => 'Send my enquiry']); ?>
        </div>
    </div>
</section>

<?php render_faqs(page_faqs($page, $slug), 'Before you get in touch'); ?>
