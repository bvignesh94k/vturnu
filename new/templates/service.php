<?php /** Generic service page. Expects: $page, $slug, $PAGES */
$features = service_features($page);
$steps = service_process($page);
$faqs = service_faqs($page);
$related = related_pages($PAGES, $slug);
[$aeo_q, $aeo_a] = answer_box($page);
$why = why_choose($page);
[$offer_title, $offer_sub, $offer_btn] = cta_offer($page);
?>
<section class="page-hero">
    <div class="container">
        <p class="eyebrow eyebrow-line">Chennai · India · US · Canada</p>
        <h1><?= e($page['h1']) ?></h1>
        <p class="lede"><?= e($page['lede']) ?></p>
        <div class="hero-actions">
            <a class="btn btn-grad" href="/contact-us/"><?= e($offer_btn) ?></a>
            <a class="btn btn-outline" href="/case-studies/">See Real Results</a>
        </div>
        <ul class="hero-chips">
            <li>Free audit &amp; proposal</li>
            <li>Reply within 24 hours</li>
            <li>No long-term lock-ins</li>
        </ul>
        <p class="hero-rating"><span class="stars" aria-hidden="true">★★★★★</span> Rated 5.0 · Trusted by 150+ brands worldwide</p>
    </div>
</section>

<div class="trust-strip" aria-label="Why businesses choose VTurnU">
    <div class="container trust-strip-inner">
        <span><strong>150+</strong> projects delivered</span>
        <span><strong>10+</strong> industries served</span>
        <span><strong>3.2×</strong> average ROI improvement</span>
        <span><strong>24h</strong> guaranteed response</span>
    </div>
</div>

<section class="section">
    <div class="container split">
        <div class="answer-box">
            <h2><?= e($aeo_q) ?></h2>
            <p class="answer-text"><?= e($aeo_a) ?></p>
            <a class="btn btn-primary" href="/contact-us/">Talk to a Specialist</a>
        </div>
        <div class="panel panel-accent">
            <h3>Why businesses choose VTurnU</h3>
            <ul class="check-list">
                <?php foreach ($why as $point): ?>
                <li><?= e($point) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</section>

<section class="section section-tint">
    <div class="container">
        <h2 class="section-title center">What's Included in Our <?= e($page['h1']) ?></h2>
        <div class="card-grid cols-3">
            <?php foreach ($features as [$title, $desc]): ?>
            <div class="card">
                <h3><?= e($title) ?></h3>
                <p><?= e($desc) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="cta-mid">
    <div class="container cta-mid-inner">
        <div>
            <h2><?= e($offer_title) ?></h2>
            <p><?= e($offer_sub) ?></p>
        </div>
        <div class="cta-mid-actions">
            <a class="btn btn-dark" href="/contact-us/"><?= e($offer_btn) ?></a>
            <a class="cta-phone" href="<?= e(CONTACT_PHONE_HREF) ?>">or call <?= e(CONTACT_PHONE) ?></a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title center">How Our <?= e($page['h1']) ?> Work</h2>
        <ol class="process">
            <?php foreach ($steps as [$title, $desc]): ?>
            <li><h3><?= e($title) ?></h3><p><?= e($desc) ?></p></li>
            <?php endforeach; ?>
        </ol>
    </div>
</section>

<?php render_compare(); ?>

<section class="section section-tint">
    <div class="container narrow">
        <h2 class="section-title center"><?= e($page['h1']) ?>, Frequently Asked Questions</h2>
        <div class="faq-list">
            <?php foreach ($faqs as [$q, $a]): ?>
            <details class="faq">
                <summary><?= e($q) ?></summary>
                <p><?= e($a) ?></p>
            </details>
            <?php endforeach; ?>
        </div>
        <p class="center faq-cta">Still have questions? <a href="/contact-us/">Ask a specialist</a>: free, no obligation.</p>
    </div>
</section>

<?php render_next_steps(); ?>

<?php if ($related): ?>
<section class="section">
    <div class="container">
        <h2 class="section-title center">Related Services</h2>
        <div class="card-grid cols-4">
            <?php foreach ($related as $rslug => $rpage): ?>
            <a class="card card-compact" href="<?= e(page_url($rslug)) ?>">
                <h3><?= e($rpage['h1']) ?></h3>
                <span class="card-more">Learn more →</span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
