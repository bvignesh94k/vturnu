<?php /** Case study listing page. Expects: $page, $slug, $CASES */
$cs_type = $page['cs_type'] ?? '';

/* Featured: full case studies matching this list's type, then the rest */
$featured = [];
foreach ($CASES as $cslug => $c) {
    if ($c['cs_type'] === $cs_type) { $featured[$cslug] = $c; }
}
if (!$featured) { $featured = array_slice($CASES, 0, 3, true); }

$cards = case_cards($cs_type ?: 'seo');
?>
<section class="page-hero">
    <div class="container">
        <h1><?= e($page['h1']) ?></h1>
        <p class="lede"><?= e($page['lede']) ?></p>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title">Full case studies</h2>
        <div class="card-grid cols-3">
            <?php foreach ($featured as $cslug => $c): ?>
            <a class="card post-card" href="/case-studies/<?= e($cslug) ?>/" data-reveal="card">
                <span class="card-tag"><?= e($c['industry']) ?></span>
                <h3><?= e($c['h1']) ?></h3>
                <p><?= e(mb_substr($c['lede'], 0, 130)) ?>…</p>
                <div class="case-metrics">
                    <div><strong><?= e($c['results'][0][0]) ?></strong><span><?= e($c['results'][0][1]) ?></span></div>
                    <div><strong><?= e($c['results'][1][0]) ?></strong><span><?= e($c['results'][1][1]) ?></span></div>
                </div>
                <span class="post-card-foot"><span>Full story</span><em aria-hidden="true">Read →</em></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section-tint">
    <div class="container">
        <h2 class="section-title">More results in this category</h2>
        <div class="card-grid cols-3">
            <?php foreach ($cards as [$client, $headline, $desc, $m1, $m2]): ?>
            <article class="card case-card" data-reveal="card">
                <p class="case-client"><?= e($client) ?></p>
                <h3><?= e($headline) ?></h3>
                <p><?= e($desc) ?></p>
                <div class="case-metrics">
                    <div><strong><?= e($m1[0]) ?></strong><span><?= e($m1[1]) ?></span></div>
                    <div><strong><?= e($m2[0]) ?></strong><span><?= e($m2[1]) ?></span></div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="cta-mid">
    <div class="container cta-mid-inner">
        <div>
            <h2>Want results like these?</h2>
            <p>Every engagement starts with a free audit and a clear plan. Let's find your growth opportunity.</p>
        </div>
        <div class="cta-mid-actions">
            <a class="btn btn-dark" href="/free-seo-audit/">Check My Site Free</a>
            <a class="cta-phone" href="<?= e(CONTACT_PHONE_HREF) ?>">or call <?= e(CONTACT_PHONE) ?></a>
        </div>
    </div>
</section>
