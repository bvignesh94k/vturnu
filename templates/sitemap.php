<?php
/**
 * Human-readable sitemap.
 *
 * Laid out the way a visitor thinks about the business rather than as one
 * alphabetical list: each section is a card, and larger sections are split into
 * labelled groups. Built from $PAGES, the same array that generates
 * /sitemap.xml, so the two cannot drift apart and this page cannot list a URL
 * that does not exist. Expects: $page, $PAGES.
 */

$link = static fn(string $s): ?array => isset($PAGES[$s]) ? [$s, $PAGES[$s]['h1'] ?? $s] : null;
$links = static fn(array $slugs): array => array_values(array_filter(array_map($link, $slugs)));
$by = static function (callable $test) use ($PAGES): array {
    $out = [];
    foreach ($PAGES as $s => $p) {
        if ($s !== '' && empty($p['noindex']) && $test($s, $p)) {
            $out[] = [$s, $p['h1'] ?? $s];
        }
    }
    usort($out, static fn($a, $b) => strcasecmp($a[1], $b[1]));
    return $out;
};

$tree = [
    'Digital Marketing' => [
        'url' => 'digital-marketing',
        'groups' => [
            'SEO' => $links(['seo', 'seo-services', 'local-seo', 'ecommerce-seo', 'lead-generation-seo', 'enterprise-seo', 'franchise-seo']),
            'Paid Advertising' => $links(['paid-advertising', 'google-ads', 'facebook-ads', 'instagram-ads', 'enterprise-ppc']),
            'Brand & Content' => $links(['content-marketing', 'content-marketing-services', 'content-writing-services', 'blog-writing-services', 'website-content-writing-services', 'white-paper-writing-services', 'case-study-writing-services', 'product-description-writing-services', 'technical-content-writing-services', 'branding-services', 'social-media-marketing', 'seo-content-writing', 'copywriting-services', 'translation-services', 'email-marketing', 'reputation-management', 'outbound-marketing', 'online-marketing', 'digital-marketing-consulting']),
            'Ecommerce SEO by Industry' => $by(static fn($s) => str_starts_with($s, 'ecommerce-seo-for-')),
            'Lead Gen SEO by Industry' => $by(static fn($s) => str_starts_with($s, 'lead-gen-seo-for-')),
        ],
    ],
    'AI Search' => [
        'url' => 'ai',
        'groups' => [
            'Services' => $links(['ai-seo', 'google-ai-overviews-seo', 'chatgpt-seo', 'perplexity-seo', 'selling-on-chatgpt']),
            'Insight' => $links(['ai-blog-posts']),
        ],
    ],
    'Web Development' => [
        'url' => 'web-services',
        'groups' => [
            'By Goal' => $links(['web-design', 'ecommerce-web-design', 'lead-gen-web-design', 'headless-web-design']),
            'By Platform' => $links(['shopify-web-design', 'wordpress-web-design', 'woocommerce-web-design', 'magento-web-design', 'bigcommerce-web-design']),
            'Engineering' => $links(['custom-design-and-development', 'magento-development', 'ai-development']),
        ],
    ],
    'Enterprise & IT' => [
        'url' => 'enterprise-it-services',
        'groups' => [
            'Services' => $links(['sap-cpq-services', 'salesforce-services', 'odoo-erp-services', 'erpnext-services', 'it-staffing-services']),
        ],
    ],
    'Case Studies' => [
        'url' => 'case-studies',
        'groups' => [
            'Client Stories' => $by(static fn($s, $p) => ($p['template'] ?? '') === 'case-detail'),
            'By Topic' => $by(static fn($s, $p) => ($p['template'] ?? '') === 'case-list'),
        ],
    ],
    'Guides & E-books' => [
        'url' => null,
        'groups' => [
            'Guides' => array_merge($links(['guides']), $by(static fn($s) => str_starts_with($s, 'guides/'))),
            'E-books' => array_merge($links(['ebooks']), $by(static fn($s) => str_starts_with($s, 'ebooks/'))),
        ],
    ],
    'Blog' => [
        'url' => 'blog',
        'groups' => [],
    ],
    'Search Updates' => [
        'url' => 'updates',
        'groups' => [
            'Google Updates' => $by(static fn($s, $p) => ($p['template'] ?? '') === 'update-post'),
        ],
    ],
    'Company' => [
        'url' => null,
        'groups' => [
            'About' => $links(['about-us', 'how-we-work', 'our-clients', 'pricing', 'contact-us']),
            'Free Tool' => $links(['free-seo-audit']),
            'Policies' => $links(['ai-policy', 'privacy-policy', 'terms-and-conditions']),
        ],
    ],
];

foreach ($BLOG as $bslug => $b) {
    $tree['Blog']['groups'][$b['category'] ?? 'Articles'][] = ['blog/' . $bslug, $b['h1']];
}
ksort($tree['Blog']['groups']);

$count = static function (array $node): int {
    return array_sum(array_map('count', $node['groups'])) + (!empty($node['url']) ? 1 : 0);
};
$total = array_sum(array_map($count, $tree)) + 1;
?>
<?php render_hero([
    'k' => 'Site map', 'h1' => $page['h1'], 'lede' => $page['lede'], 'tone' => 'stone', 'layout' => 'split',
    'extra' => '<p class="sm-count">' . $total . ' pages in ' . count($tree) . ' sections</p>',
    'visual' => hero_visual(['tree', array_keys($tree)]),
]); ?>

<section class="section sm-jump-sec">
    <div class="container">
        <nav class="sm-jump" aria-label="Jump to a section">
            <?php foreach ($tree as $heading => $node): ?>
            <a href="#<?= e(strtolower(preg_replace('/[^a-z]+/i', '-', $heading))) ?>"><?= e($heading) ?> <span><?= $count($node) ?></span></a>
            <?php endforeach; ?>
        </nav>

        <?php foreach ($tree as $heading => $node): ?>
        <section class="sm-block" id="<?= e(strtolower(preg_replace('/[^a-z]+/i', '-', $heading))) ?>" aria-label="<?= e($heading) ?>">
            <h2>
                <?php if (!empty($node['url'])): ?>
                <a href="<?= e(page_url($node['url'])) ?>"><?= e($heading) ?></a>
                <?php else: ?>
                <?= e($heading) ?>
                <?php endif; ?>
                <span class="sm-n"><?= $count($node) ?> pages</span>
            </h2>
            <div class="sm-cols">
                <?php foreach ($node['groups'] as $group => $items): if (!$items) { continue; } ?>
                <div class="sm-group">
                    <h3><?= e($group) ?></h3>
                    <ul>
                        <?php foreach ($items as [$s, $label]): ?>
                        <li><a href="<?= e(page_url($s)) ?>"><?= e($label) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endforeach; ?>

        <p class="sm-foot">
            Looking for the machine-readable version? The XML sitemap is at
            <a href="/sitemap.xml">/sitemap.xml</a>, and a plain-text map for AI
            answer engines is at <a href="/llms.txt">/llms.txt</a>.
        </p>
    </div>
</section>
