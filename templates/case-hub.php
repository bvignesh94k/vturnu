<?php
/**
 * Case studies hub. Expects: $page, $slug, $CASES, $PAGES.
 * Named clients lead, with their live site and the source of every number.
 */
$real = array_filter($CASES, static fn($c) => !empty($c['real']));
$other = array_filter($CASES, static fn($c) => empty($c['real']));
?>
<?php
$hero_rows = [];
foreach ($CASES as $hc) {
    if (!empty($hc['real'])) {
        $hero_rows[] = [$hc['results'][0][0], $hc['results'][0][1]];
    }
}
render_hero([
    'k' => 'Case studies', 'h1' => $page['h1'], 'lede' => $page['lede'], 'tone' => 'peach', 'layout' => 'split',
    'cta' => ['Start your own story', '/contact-us/'], 'alt' => ['See the results', '#clients'],
    'visual' => hero_visual(['metrics', $hero_rows, 'Measured, with sources']),
]);
?>

<section class="section" id="clients">
    <div class="container">
        <div class="center-text" data-reveal="head">
            <p class="eyebrow">Named clients</p>
            <h2 class="section-title">Real businesses, measured results</h2>
            <p class="pricing-intro center">Every figure below comes from the client's own Search Console, Bing Webmaster Tools or PageSpeed data, with the source and date on each case study.</p>
        </div>
        <div class="case-feature-grid">
            <?php foreach ($real as $cslug => $c): ?>
            <article class="case-feature tilt" data-reveal="card">
                <div class="case-feature-top">
                    <strong class="case-feature-name"><?= e($c['client']) ?></strong>
                    <span class="card-tag"><?= e($c['industry']) ?></span>
                </div>
                <h3><a href="/case-studies/<?= e($cslug) ?>/"><?= e($c['h1']) ?></a></h3>
                <div class="case-metrics">
                    <?php foreach (array_slice($c['results'], 0, 3) as [$m, $l]): ?>
                    <div><strong><?= e($m) ?></strong><span><?= e($l) ?></span></div>
                    <?php endforeach; ?>
                </div>
                <div class="case-feature-foot">
                    <a class="btn btn-grad" href="/case-studies/<?= e($cslug) ?>/">Read the Case Study</a>
                    <?= ext_link($c['site'], 'Visit ' . $c['site_label'], true, 'case-site') ?>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if ($other): ?>
<section class="section section-tint">
    <div class="container">
        <h2 class="section-title center">More engagements</h2>
        <p class="pricing-intro center">Earlier work, shown without client names.</p>
        <div class="card-grid cols-3">
            <?php foreach ($other as $cslug => $c): ?>
            <a class="card post-card" href="/case-studies/<?= e($cslug) ?>/" data-reveal="card">
                <span class="card-tag"><?= e($c['industry']) ?></span>
                <h3><?= e($c['h1']) ?></h3>
                <span class="post-card-foot"><span><?= e($c['results'][0][0]) ?> <?= e($c['results'][0][1]) ?></span><em aria-hidden="true">Read →</em></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php render_faqs(page_faqs($page, $slug), 'About our case studies'); ?>

