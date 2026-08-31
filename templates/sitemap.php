<?php
/**
 * Human-readable sitemap.
 *
 * Built from the same $PAGES array that generates /sitemap.xml, so the two can
 * never drift apart and this page cannot list a URL that does not exist. The
 * XML file stays where crawlers expect it and is no longer linked in the
 * footer: it is a machine file, and visitors who clicked it got raw XML.
 *
 * Expects: $page, $PAGES.
 */

/* Group every indexable page by section. Order here is the order rendered. */
$groups = [
    'Services'      => [],
    'AI Search'     => [],
    'Case Studies'  => [],
    'Guides & E-books' => [],
    'Insight'       => [],
    'Company'       => [],
];

$aiSlugs = ['ai', 'ai-seo', 'ai-development', 'ai-visibility-tool', 'chatgpt-seo',
            'perplexity-seo', 'google-ai-overviews-seo', 'selling-on-chatgpt', 'ai-blog-posts'];
$companySlugs = ['about-us', 'contact-us', 'pricing', 'privacy-policy',
                 'terms-and-conditions', 'ai-policy', 'free-seo-audit', 'sitemap'];

foreach ($PAGES as $slug => $p) {
    // Skip the homepage (linked from the logo everywhere) and anything noindexed.
    if ($slug === '' || !empty($p['noindex'])) {
        continue;
    }
    $tpl = $p['template'] ?? 'service';
    $label = $p['h1'] ?? $slug;

    if (in_array($slug, $companySlugs, true) || $tpl === 'legal') {
        $groups['Company'][] = [$slug, $label];
    } elseif (in_array($slug, $aiSlugs, true)) {
        $groups['AI Search'][] = [$slug, $label];
    } elseif (str_starts_with($slug, 'case-studies')) {
        $groups['Case Studies'][] = [$slug, $label];
    } elseif (str_starts_with($slug, 'ebooks') || str_starts_with($slug, 'guides')) {
        $groups['Guides & E-books'][] = [$slug, $label];
    } elseif (str_starts_with($slug, 'blog')) {
        $groups['Insight'][] = [$slug, $label];
    } elseif (in_array($tpl, ['service', 'hub', 'product'], true)) {
        $groups['Services'][] = [$slug, $label];
    } else {
        $groups['Company'][] = [$slug, $label];
    }
}

foreach ($groups as $k => $v) {
    usort($groups[$k], fn($a, $b) => strcasecmp($a[1], $b[1]));
}
$total = array_sum(array_map('count', $groups));
?>
<section class="page-hero">
    <div class="container">
        <h1><?= e($page['h1']) ?></h1>
        <p class="lede"><?= e($page['lede']) ?></p>
        <p class="sm-count"><?= $total ?> pages across <?= count(array_filter($groups)) ?> sections.</p>
    </div>
</section>

<section class="section sm-section">
    <div class="container">
        <div class="sm-grid">
            <?php foreach ($groups as $heading => $items): if (!$items) continue; ?>
            <div class="sm-group">
                <h2><?= e($heading) ?> <span class="sm-n"><?= count($items) ?></span></h2>
                <ul>
                    <?php foreach ($items as [$slug, $label]): ?>
                    <li><a href="<?= e(page_url($slug)) ?>"><?= e($label) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endforeach; ?>
        </div>

        <p class="sm-foot">
            Looking for the machine-readable version? The XML sitemap is at
            <a href="/sitemap.xml">/sitemap.xml</a>, and a plain-text map for AI
            answer engines is at <a href="/llms.txt">/llms.txt</a>.
        </p>
    </div>
</section>
