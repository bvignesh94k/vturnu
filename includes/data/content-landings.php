<?php
/**
 * Content writing and content marketing landing pages, rendered by
 * templates/content-landing.php with the blocks in includes/content-kit.php.
 *
 * One file per page in content-landings/, so a page can be edited without
 * scrolling past the other seven. The order below is the order they appear in
 * "Other things we write" on every page.
 *
 * Rules these pages keep:
 * - Every figure is one already published in a named case study
 *   (includes/data/cases-real.php) or counted from this site's own content.
 *   The before and after lines in each 'edit' block are illustrations, and the
 *   section says so.
 * - No price list: only the minimum budget, in the visitor's currency.
 * - No promise of rankings or AI citations.
 * - Two of the URLs existed on the old site and were indexed
 *   (/content-writing-services/ and /content-marketing-services/), so those
 *   slugs are kept exactly.
 *
 * Fields: name (short label for breadcrumbs, cards and schema), card (one line
 * for cards), title + meta (search; includes/data/seo-meta.php overrides them),
 * eyebrow, hero (the visible H1), lede, layout, chips, visual, cta, alt, proof,
 * answer, facts, pain, edit, scope, cta1, steps, table, case, cta2, fit, faqs,
 * form, reading, updated.
 */

$cl_min = min_budget();
$cl_ebooks = count(array_filter($RESOURCES ?? [], static fn(array $r): bool => ($r['type'] ?? '') === 'ebook'));
$cl_guides = count(array_filter($RESOURCES ?? [], static fn(array $r): bool => ($r['type'] ?? '') === 'guide'));
$cl_it = count(array_filter($BLOG ?? [], static fn(array $b): bool => ($b['category'] ?? '') === 'Enterprise IT'));

$CONTENT_LANDINGS = [];
foreach ([
    'content-writing-services', 'content-marketing-services', 'blog-writing-services',
    'website-content-writing-services', 'white-paper-writing-services', 'case-study-writing-services',
    'product-description-writing-services', 'technical-content-writing-services',
] as $cl_slug) {
    $CONTENT_LANDINGS[$cl_slug] = require __DIR__ . '/content-landings/' . $cl_slug . '.php';
}

foreach ($CONTENT_LANDINGS as $cl_slug => $cl) {
    $PAGES[$cl_slug] = [
        'template' => 'content-landing',
        'title' => $cl['title'], 'meta' => $cl['meta'], 'h1' => $cl['name'], 'lede' => $cl['lede'],
        'card' => $cl['card'],
        'parent' => $cl['parent'] ?? 'content-writing-services',
        /* The direct answer leads the FAQ list, as on every service page. */
        'faqs' => array_merge([$cl['answer']], $cl['faqs']),
        /* Read only by the XML sitemap, for an accurate lastmod. */
        'date' => $cl['updated'],
    ];
}
unset($cl_slug, $cl, $cl_min, $cl_ebooks, $cl_guides, $cl_it);
