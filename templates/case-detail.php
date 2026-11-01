<?php /** Case study detail. Expects: $page, $slug, $CASES, $PAGES */
$case = $CASES[$page['case']] ?? null;
if (!$case) { http_response_code(404); echo '<section class="section"><div class="container"><h1>Case study not found</h1></div></section>'; return; }

$real_pool = array_filter($CASES, static fn($c) => !empty($c['real']));
$related = related_ring(!empty($case['real']) ? $real_pool : $CASES, $page['case'], 3);

/* The Article node for this page is emitted by jsonld_page() from
   includes/header.php. It used to be duplicated here: a second, thinner
   Article with no @id and no link to the Organization or WebSite entities,
   leaving two competing descriptions of the same page in the markup. */
?>
<?php
ob_start();
?>
<div class="hx-facts">
    <span><strong>Client</strong><?= e($case['client']) ?></span>
    <span><strong>Location</strong><?= e($case['location']) ?></span>
    <?php if (!empty($case['site'])): ?><span><strong>Website</strong><?= ext_link($case['site'], $case['site_label'], true) ?></span><?php endif; ?>
    <span><strong>Services</strong><?php foreach ($case['services'] as $i => [$sl, $su]): ?><a href="<?= e($su) ?>"><?= e($sl) ?></a><?= $i < count($case['services']) - 1 ? ', ' : '' ?><?php endforeach; ?></span>
</div>
<?php
render_hero([
    'k' => $case['industry'] . ' · ' . $case['duration'], 'h1' => $case['h1'], 'lede' => $case['lede'],
    'tone' => 'peach', 'layout' => hero_layout($slug), 'extra' => ob_get_clean(),
    'visual' => hero_visual(['metrics', $case['results'], !empty($case['real']) ? 'Measured, with sources' : '']),
    'class' => 'hx--case',
]);
?>

<!-- Results band up top: numbers earn attention -->
<div class="case-results-band">
    <div class="container case-results-inner">
        <?php foreach ($case['results'] as [$metric, $label]): ?>
        <div class="case-metric" data-reveal="stat">
            <span class="grad-text"><?= e($metric) ?></span>
            <small><?= e($label) ?></small>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<section class="section">
    <div class="container narrow">
        <h2 class="section-title">The challenge</h2>
        <?php foreach ($case['challenge'] as $p): ?><p class="case-para"><?= e($p) ?></p><?php endforeach; ?>
    </div>
</section>

<section class="section section-tint">
    <div class="container">
        <h2 class="section-title center">What we did</h2>
        <div class="card-grid cols-2">
            <?php $i = 0; $accents = ['ac-yellow', 'ac-pink', 'ac-cyan', 'ac-purple']; foreach ($case['approach'] as [$t, $d]): ?>
            <div class="card svc-card <?= $accents[$i % 4] ?>" data-reveal="card">
                <span class="svc-bar" aria-hidden="true"></span>
                <span class="svc-num"><?= sprintf('%02d', ++$i) ?></span>
                <h3><?= e($t) ?></h3>
                <p><?= e($d) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if (!empty($case['outcomes'])): ?>
<section class="section">
    <div class="container">
        <div class="center-text" data-reveal="head">
            <p class="eyebrow">What the client actually got</p>
            <h2 class="section-title">Results by area</h2>
        </div>
        <div class="outcome-grid">
            <?php foreach ($case['outcomes'] as [$area, $text]): ?>
            <div class="outcome tilt" data-reveal="card">
                <h3><?= e($area) ?></h3>
                <p><?= e($text) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section">
    <div class="container narrow">
        <?php if (!empty($case['quote'])): ?>
        <figure class="case-quote" data-reveal="quote">
            <blockquote>"<?= e($case['quote'][0]) ?>"</blockquote>
            <figcaption><?= e($case['quote'][1]) ?></figcaption>
        </figure>
        <?php endif; ?>
        <h2 class="section-title">The outcome</h2>
        <p class="case-para"><?= e($case['outcome']) ?></p>
        <?php if (!empty($case['source'])): ?>
        <p class="case-source"><strong>Where these numbers come from:</strong> <?= e($case['source']) ?></p>
        <?php endif; ?>
    </div>
</section>

<?php render_faqs($case['faqs'] ?? [], 'Questions about this project'); ?>

<section class="cta-mid">
    <div class="container cta-mid-inner">
        <div>
            <h2>Want results you can measure?</h2>
            <p>Every engagement starts the same way: a free audit and an honest conversation about whether we can move your needle.</p>
        </div>
        <div class="cta-mid-actions">
            <a class="btn btn-dark" href="/contact-us/">Get My Growth Plan</a>
            <a class="cta-phone" href="<?= e(CONTACT_PHONE_HREF) ?>">or call <?= e(CONTACT_PHONE) ?></a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title center">More client stories</h2>
        <div class="card-grid cols-3">
            <?php foreach ($related as $rslug => $r): ?>
            <a class="card post-card" href="/case-studies/<?= e($rslug) ?>/">
                <span class="card-tag"><?= e($r['industry']) ?></span>
                <h3><?= e($r['h1']) ?></h3>
                <span class="post-card-foot"><span><?= e($r['results'][0][0]) ?> <?= e($r['results'][0][1]) ?></span><em aria-hidden="true">Read →</em></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
