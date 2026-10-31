<?php /** E-books / Guides hub. Expects: $page, $slug, $RESOURCES */
$rtype = $page['rtype'] ?? 'ebook';
$hub = $rtype === 'guide' ? 'guides' : 'ebooks';
$items = array_filter($RESOURCES, fn($r) => $r['type'] === $rtype);
echo jsonld_script([
    '@context' => 'https://schema.org',
    '@type' => 'ItemList',
    'name' => $page['h1'],
    'numberOfItems' => count($items),
    'itemListElement' => array_map(static fn($k, $i) => ['@type' => 'ListItem', 'position' => $i + 1, 'url' => abs_url('/' . $hub . '/' . $k . '/')], array_keys($items), array_keys(array_keys($items))),
]);
?>
<section class="page-hero">
    <div class="container">
        <p class="eyebrow eyebrow-line"><?= $rtype === 'ebook' ? 'Read free, no form' : 'Bookmark and return' ?></p>
        <h1><?= e($page['h1']) ?></h1>
        <p class="lede"><?= e($page['lede']) ?></p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="card-grid cols-3 res-grid">
            <?php foreach ($items as $rslug => $r): ?>
            <a class="card post-card res-card" href="/<?= e($hub) ?>/<?= e($rslug) ?>/" data-reveal="card">
                <?php if (is_file(BASE_PATH . '/assets/img/blog/res-' . $rslug . '-sm.webp')): ?>
                <img class="card-img" src="/assets/img/blog/res-<?= e($rslug) ?>-sm.webp" alt="" width="640" height="336" loading="lazy" decoding="async">
                <?php endif; ?>
                <span class="card-tag"><?= $rtype === 'ebook' ? 'E-book' : 'Guide' ?></span>
                <h2 class="card-h"><?= e($r['h1']) ?></h2>
                <p><?= e(mb_substr($r['lede'], 0, 130)) ?>...</p>
                <span class="post-card-foot"><span>Free to read</span><em aria-hidden="true">Read →</em></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php render_faqs(page_faqs($page, $slug), $rtype === 'ebook' ? 'About these e-books' : 'About these guides'); ?>

<?php render_next_steps(); ?>
