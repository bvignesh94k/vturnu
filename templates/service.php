<?php
/**
 * Service money page. Expects: $page, $slug, $PAGES.
 * The order follows a buyer's decision, and all wording is written per page in
 * includes/data/money-*.php.
 */
$m = money_data($slug);
$cat = $m['pcat'] ?? ($page['cat'] ?? 'seo');
$related = related_pages($PAGES, $slug);
[$offer_title, $offer_sub, $offer_btn] = cta_offer($page);

render_hero([
    'k' => $m['k'] ?? '',
    'h1' => $m['h'] ?? $page['h1'],
    'lede' => $page['lede'],
    'cta' => [$offer_btn, '#enquire'],
    'alt' => ['See real results', '/case-studies/'],
    'visual' => hero_visual($m['v'] ?? []),
    'tone' => hero_tone($page, $slug),
    'layout' => hero_layout($slug),
]);
render_money_bar($cat);
render_money_pain($m['pain']);
render_money_gets($m['gets']);
render_money_proof($cat);
render_cta_band('Want results like this for your company?', 'Projects start from ' . min_budget() . '. Tell us your goal and a senior strategist replies within one working day.', $offer_btn);
render_money_plan($cat);
render_money_fit($m['fit']);
render_faqs(page_faqs($page, $slug), $page['h1'] . ': questions buyers ask');
render_lead_form($page['h1'], $offer_title, $offer_sub, 'enquire', $offer_btn);
?>
<?php $hub = (string) ($page['parent'] ?? ''); $hub_label = isset($PAGES[$hub]) ? hub_anchor($hub) : ''; ?>
<?php if ($related || $hub_label !== ''): ?>
<div class="container">
    <?php if ($hub_label !== ''): ?>
    <p class="mp-rel-h">More from our <a href="<?= e(page_url($hub)) ?>"><?= e($hub_label) ?></a>:</p>
    <?php endif; ?>
    <ul class="mp-rel" aria-label="Related services">
        <?php foreach ($related as $rslug => $rpage): ?>
        <li><a href="<?= e(page_url($rslug)) ?>"><?= e($rpage['h1']) ?></a></li>
        <?php endforeach; ?>
    </ul>
    <?php if ($extra = related_extra($PAGES, $slug)): /* the writing this service usually needs */ ?>
    <p class="mp-rel-x">Related content services: <?php foreach ($extra as $i => $xslug): ?><?= $i ? ' · ' : '' ?><a href="<?= e(page_url($xslug)) ?>"><?= e($PAGES[$xslug]['h1']) ?></a><?php endforeach; ?></p>
    <?php endif; ?>
</div>
<?php endif; ?>
