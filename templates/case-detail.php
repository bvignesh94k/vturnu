<?php /** Case study detail. Expects: $page, $slug, $CASES, $PAGES */
$case = $CASES[$page['case']] ?? null;
if (!$case) { http_response_code(404); echo '<section class="section"><div class="container"><h1>Case study not found</h1></div></section>'; return; }

$related = related_ring($CASES, $page['case'], 3);

echo jsonld_script([
    '@context' => 'https://schema.org',
    '@type' => 'Article',
    'headline' => $case['h1'],
    'description' => $case['meta'],
    'author' => ['@type' => 'Organization', 'name' => SITE_NAME, 'url' => SITE_URL],
    'publisher' => ['@type' => 'Organization', 'name' => SITE_NAME],
    'mainEntityOfPage' => abs_url('/case-studies/' . $page['case'] . '/'),
]);
?>
<section class="page-hero case-hero">
    <div class="container">
        <p class="eyebrow"><?= e($case['industry']) ?> · <?= e($case['duration']) ?></p>
        <h1><?= e($case['h1']) ?></h1>
        <p class="lede"><?= e($case['lede']) ?></p>
        <div class="case-facts">
            <span><strong>Client</strong><?= e($case['client']) ?></span>
            <span><strong>Location</strong><?= e($case['location']) ?></span>
            <span><strong>Services</strong>
                <?php foreach ($case['services'] as $i => [$sl, $su]): ?><a href="<?= e($su) ?>"><?= e($sl) ?></a><?= $i < count($case['services']) - 1 ? ', ' : '' ?><?php endforeach; ?>
            </span>
        </div>
    </div>
</section>

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

<section class="section">
    <div class="container narrow">
        <figure class="case-quote" data-reveal="quote">
            <blockquote>"<?= e($case['quote'][0]) ?>"</blockquote>
            <figcaption><?= e($case['quote'][1]) ?></figcaption>
        </figure>
        <h2 class="section-title">The outcome</h2>
        <p class="case-para"><?= e($case['outcome']) ?></p>
    </div>
</section>

<section class="cta-mid">
    <div class="container cta-mid-inner">
        <div>
            <h2>Want your version of these numbers?</h2>
            <p>Every engagement starts the same way: a free audit and an honest conversation about whether we can move your needle.</p>
        </div>
        <div class="cta-mid-actions">
            <a class="btn btn-dark" href="/free-seo-audit/">Check My Site Free</a>
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
