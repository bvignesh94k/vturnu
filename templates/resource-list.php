<?php /** E-books / Guides hub. Expects: $page, $slug, $RESOURCES */
$rtype = $page['rtype'] ?? 'ebook';
$hub = $rtype === 'guide' ? 'guides' : 'ebooks';
$items = array_filter($RESOURCES, fn($r) => $r['type'] === $rtype);
?>
<section class="page-hero">
    <div class="container">
        <p class="eyebrow"><?= $rtype === 'ebook' ? 'Download & share' : 'Bookmark & return' ?></p>
        <h1><?= e($page['h1']) ?></h1>
        <p class="lede"><?= e($page['lede']) ?></p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="card-grid cols-3">
            <?php foreach ($items as $rslug => $r): ?>
            <a class="card post-card" href="/<?= e($hub) ?>/<?= e($rslug) ?>/" data-reveal="card">
                <span class="card-tag"><?= e(($rtype === 'ebook' ? 'E-book · ' : 'Guide · ') . $r['size']) ?></span>
                <h3><?= e($r['h1']) ?></h3>
                <p><?= e(mb_substr($r['lede'], 0, 140)) ?>…</p>
                <span class="post-card-foot"><span>Free</span><em aria-hidden="true"><?= $rtype === 'ebook' ? 'Download →' : 'Read →' ?></em></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php render_next_steps(); ?>
