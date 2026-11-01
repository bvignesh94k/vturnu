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
<?php if ($related): ?>
<div class="container">
    <ul class="mp-rel" aria-label="Related services">
        <?php foreach ($related as $rslug => $rpage): ?>
        <li><a href="<?= e(page_url($rslug)) ?>"><?= e($rpage['h1']) ?></a></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>
