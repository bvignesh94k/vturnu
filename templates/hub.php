<?php
/**
 * Category hub. Expects: $page, $slug, $PAGES.
 * A hub helps a buyer find the right service, so the service list comes early.
 */
$m = money_data($slug);
$cat = [
    'digital-marketing' => 'seo', 'seo' => 'seo', 'ai' => 'ai', 'content-marketing' => 'content',
    'web-services' => 'web', 'paid-advertising' => 'ppc', 'online-marketing' => 'social',
][$slug] ?? '';
[$offer_title, $offer_sub, $offer_btn] = cta_offer($page);
$children = array_values(array_filter($page['children'] ?? [], fn($c) => isset($PAGES[$c])));

render_hero([
    'k' => $m['k'] ?? '',
    'h1' => $m['h'] ?? $page['h1'],
    'lede' => $page['lede'],
    'cta' => [$offer_btn, '#enquire'],
    'alt' => ['Find the right service', '#services'],
    'visual' => hero_visual($m['v'] ?? []),
    'tone' => hero_tone($page, $slug),
    'layout' => hero_layout($slug),
]);
if ($cat !== '') {
    render_money_bar($cat);
}
render_money_pain($m['pain']);
?>
<?php if ($children): ?>
<section class="mp-sec mp-gets" id="services">
    <div class="container">
        <div class="mp-gets-h">
            <p class="mp-k">Start with your goal</p>
            <h2>Pick what you need most right now</h2>
        </div>
        <ul class="mp-routes">
            <?php foreach ($children as $child): $c = $PAGES[$child]; $cm = money_data($child); ?>
            <li data-reveal="card"><a href="<?= e(page_url($child)) ?>"><b><?= e($c['h1']) ?></b><span><?= e($cm['h'] ?? $c['lede']) ?></span></a></li>
            <?php endforeach; ?>
        </ul>
        <?php if (!empty($m['note'])): [$note_pre, $note_label, $note_url, $note_post] = $m['note']; ?>
        <p class="mp-note"><?= e($note_pre) ?><?= ext_link($note_url, $note_label, true) ?><?= e($note_post) ?></p>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>
<?php
if ($slug === 'web-services') {
    render_work_showcase();
}
if ($cat !== '') {
    render_money_proof($cat);
}
render_cta_band('Not sure which service fits?', 'Tell us your goal and we will recommend the right mix. Projects start from ' . min_budget() . '.', $offer_btn);
render_money_plan($cat !== '' ? $cat : 'enterprise');
render_money_fit($m['fit']);
render_faqs(page_faqs($page, $slug), 'Questions buyers ask');
render_lead_form($page['h1'], $offer_title, $offer_sub, 'enquire', $offer_btn);
