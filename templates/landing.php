<?php
/**
 * Enterprise & IT landing page. Expects: $page, $slug, $LANDINGS.
 * One page, one offer: every action leads to the enquiry form at #enquire.
 */
$l = $LANDINGS[$slug];
$siblings = array_diff_key($LANDINGS, [$slug => 1]);
$offers = array_map(static fn($c) => ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => $c[0], 'description' => $c[1]]], $l['capabilities']);
echo jsonld_script([
    '@context' => 'https://schema.org',
    '@type' => 'Service',
    'name' => $page['h1'],
    'serviceType' => $l['service'],
    'description' => $page['meta'],
    'url' => abs_url(page_url($slug)),
    'provider' => ['@id' => SITE_URL . '/#organization'],
    'areaServed' => array_map(static fn($c) => ['@type' => 'Country', 'name' => $c], TARGET_MARKETS),
    'hasOfferCatalog' => ['@type' => 'OfferCatalog', 'name' => $l['service'] . ' services', 'itemListElement' => $offers],
]);
echo jsonld_script([
    '@context' => 'https://schema.org', '@type' => 'WebPage', 'name' => $page['title'], 'url' => abs_url(page_url($slug)),
    'speakable' => ['@type' => 'SpeakableSpecification', 'cssSelector' => ['h1', '.answer-text']],
]);
?>
<?php
$m = money_data($slug);
render_hero([
    'k' => $l['eyebrow'] . ' · VTurnU Enterprise & IT', 'h1' => $page['h1'], 'lede' => $page['lede'],
    'cta' => ['Talk to a consultant', '#enquire'], 'alt' => ['See how we work', '#how'],
    'visual' => hero_visual($m['v'] ?? []), 'tone' => 'stone', 'layout' => hero_layout($slug),
]);
?>

<section class="section section-tint">
    <div class="container">
        <div class="center-text" data-reveal="head">
            <p class="eyebrow">Sound familiar?</p>
            <h2 class="section-title">The problems we are called in to fix</h2>
        </div>
        <div class="lp-pains">
            <?php foreach ($l['pains'] as $i => [$t, $d]): ?>
            <div class="lp-pain tilt" data-reveal="card">
                <span class="lp-pain-n" aria-hidden="true"><?= sprintf('%02d', $i + 1) ?></span>
                <h3><?= e($t) ?></h3>
                <p><?= e($d) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="center-text" data-reveal="head">
            <p class="eyebrow">What we deliver</p>
            <h2 class="section-title"><?= e($l['service']) ?> services, end to end</h2>
        </div>
        <div class="card-grid cols-3 svc-grid">
            <?php $ac = ['ac-yellow', 'ac-terra', 'ac-purple', 'ac-olive', 'ac-cyan', 'ac-pink']; foreach ($l['capabilities'] as $i => [$t, $d]): ?>
            <div class="card svc-card <?= $ac[$i % 6] ?>" data-reveal="card">
                <span class="svc-bar" aria-hidden="true"></span>
                <span class="svc-num"><?= sprintf('%02d', $i + 1) ?></span>
                <h3><?= e($t) ?></h3>
                <p><?= e($d) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="cta-mid">
    <div class="container cta-mid-inner">
        <div>
            <h2>Have a project in mind?</h2>
            <p>Share the scope and timeline. You will hear from a consultant, not a call center, within one working day.</p>
        </div>
        <div class="cta-mid-actions">
            <a class="btn btn-dark" href="#enquire">Send Your Requirement</a>
            <a class="cta-phone" href="<?= e(CONTACT_PHONE_HREF) ?>">or call <?= e(CONTACT_PHONE) ?></a>
        </div>
    </div>
</section>

<section class="section" id="how">
    <div class="container">
        <div class="center-text" data-reveal="head">
            <p class="eyebrow">How we work</p>
            <h2 class="section-title">A clear path from first call to go-live</h2>
        </div>
        <ol class="lp-steps">
            <?php foreach ($l['process'] as $i => [$t, $d]): ?>
            <li class="tilt" data-reveal="card">
                <span class="lp-step-n" aria-hidden="true"><?= $i + 1 ?></span>
                <h3><?= e($t) ?></h3>
                <p><?= e($d) ?></p>
            </li>
            <?php endforeach; ?>
        </ol>
    </div>
</section>

<section class="section section-tint">
    <div class="container">
        <h2 class="section-title center"><?= e($l['table']['caption']) ?></h2>
        <figure class="post-table-wrap">
            <table class="post-table">
                <thead><tr><?php foreach ($l['table']['head'] as $th): ?><th scope="col"><?= e($th) ?></th><?php endforeach; ?></tr></thead>
                <tbody>
                    <?php foreach ($l['table']['rows'] as $row): ?>
                    <tr><?php foreach ($row as $ci => $cell): ?><?php if ($ci === 0): ?><th scope="row"><?= e($cell) ?></th><?php else: ?><td><?= e($cell) ?></td><?php endif; ?><?php endforeach; ?></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </figure>
    </div>
</section>

<?php render_money_fit($l['who']); ?>

<?php render_faqs(page_faqs($page, $slug), $l['service'] . ': questions buyers ask'); ?>

<?php render_lead_form($l['service'], $l['form'][0], $l['form'][1]); ?>

<section class="section section-tint">
    <div class="container">
        <h2 class="section-title center">Explore the Enterprise &amp; IT practice</h2>
        <div class="card-grid cols-4">
            <?php foreach ($siblings as $s => $sib): ?>
            <a class="card card-compact" href="<?= e(page_url($s)) ?>">
                <h3><?= e($sib['service']) ?></h3>
                <span class="card-more">Learn more →</span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
