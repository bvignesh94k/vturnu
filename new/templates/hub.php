<?php /** Hub / category landing page. Expects: $page, $slug, $PAGES */
[$offer_title, $offer_sub, $offer_btn] = cta_offer($page);
[$aeo_q, $aeo_a] = answer_box($page);
$why = why_choose($page);
$steps = service_process($page);
$children = array_values(array_filter($page['children'] ?? [], fn($c) => isset($PAGES[$c])));
$accents = ['ac-yellow', 'ac-terra', 'ac-purple', 'ac-olive', 'ac-cyan', 'ac-pink'];
?>
<section class="page-hero hub-hero">
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
        <div class="answer-box" data-reveal>
            <h2><?= e($aeo_q) ?></h2>
            <p class="answer-text"><?= e($aeo_a) ?></p>
            <a class="btn btn-primary" href="/contact-us/">Talk to a Specialist</a>
        </div>
        <div class="panel panel-accent" data-reveal>
            <h3>Why businesses choose VTurnU</h3>
            <ul class="check-list">
                <?php foreach ($why as $point): ?>
                <li><?= e($point) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</section>

<?php if ($children): ?>
<section class="section section-tint" id="services">
    <div class="container">
        <div class="sec-head" data-reveal>
            <div>
                <p class="eyebrow">Explore</p>
                <h2 class="section-title">Every service under <span class="grad-text"><?= e($page['h1']) ?></span></h2>
            </div>
            <p class="sec-head-note">Pick the one closest to your goal, or tell us the goal and we'll recommend the mix.</p>
        </div>
        <div class="card-grid cols-3 svc-grid">
            <?php foreach ($children as $i => $child): $c = $PAGES[$child]; ?>
            <a class="card svc-card <?= $accents[$i % count($accents)] ?>" href="<?= e(page_url($child)) ?>" data-reveal>
                <span class="svc-bar" aria-hidden="true"></span>
                <span class="svc-num"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
                <h3><?= e($c['h1']) ?></h3>
                <p><?= e($c['lede']) ?></p>
                <span class="svc-go">Learn more <em aria-hidden="true">→</em></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

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
        <div data-reveal>
            <p class="eyebrow">How we work</p>
            <h2 class="section-title">From first call to first result</h2>
        </div>
        <div class="proc-grid">
            <?php foreach ($steps as $i => [$title, $desc]): ?>
            <div class="proc-card <?= ['accent-yellow', 'accent-cyan', 'accent-pink', 'accent-purple'][$i % 4] ?>" data-reveal>
                <span class="proc-step">STEP <?= $i + 1 ?></span>
                <h3><?= e($title) ?></h3>
                <p><?= e($desc) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php render_compare(); ?>

<?php render_next_steps(); ?>
