<?php /** Blog index with category filters. Expects: $page, $slug, $BLOG */
$cat_filter = $_GET['cat'] ?? ($page['blog_cat'] ?? '');
$cats = [];
foreach ($BLOG as $b) { $cats[$b['category']] = true; }
$cats = array_keys($cats);
sort($cats);

$posts = $BLOG;
if ($cat_filter !== '' && in_array($cat_filter, $cats, true)) {
    $posts = array_filter($BLOG, fn($b) => $b['category'] === $cat_filter);
}
uasort($posts, fn($a, $b) => strcmp($b['date'], $a['date']));
$base_url = '/' . ($slug === 'ai-blog-posts' ? 'ai-blog-posts' : 'blog') . '/';
?>
<section class="page-hero">
    <div class="container">
        <p class="eyebrow">Free knowledge, applied daily</p>
        <h1><?= e($page['h1']) ?></h1>
        <p class="lede"><?= e($page['lede']) ?></p>
    </div>
</section>

<section class="section">
    <div class="container">
        <nav class="blog-filters" aria-label="Filter articles by topic">
            <a class="chip<?= $cat_filter === '' ? ' active' : '' ?>" href="<?= e($base_url) ?>">All topics</a>
            <?php foreach ($cats as $c): ?>
            <a class="chip<?= $cat_filter === $c ? ' active' : '' ?>" href="<?= e($base_url) ?>?cat=<?= e(rawurlencode($c)) ?>"><?= e($c) ?></a>
            <?php endforeach; ?>
        </nav>

        <div class="card-grid cols-3 blog-grid">
            <?php $i = 0; foreach ($posts as $bslug => $b): $i++; ?>
            <a class="card post-card" href="/blog/<?= e($bslug) ?>/" data-reveal="card">
                <div class="post-card-meta">
                    <span class="card-tag"><?= e($b['category']) ?></span>
                    <span class="post-intent"><?= e($b['intent']) ?></span>
                </div>
                <h3><?= e($b['h1']) ?></h3>
                <p><?= e(mb_substr($b['lede'], 0, 150)) ?>…</p>
                <span class="post-card-foot"><span><?= e(date('j M Y', strtotime($b['date']))) ?> · <?= e($b['read']) ?></span><em aria-hidden="true">Read →</em></span>
            </a>
            <?php if ($i === 6): ?>
            <div class="card blog-cta-card" data-reveal="card">
                <p class="eyebrow">Skip the reading list</p>
                <h3>Want this thinking applied to your business?</h3>
                <p>Every article here comes from real client work. Get the same strategists looking at your growth, starting with a free audit.</p>
                <a class="btn btn-grad" href="/free-seo-audit/">Run My Free Site Audit</a>
            </div>
            <?php endif; endforeach; ?>
        </div>
    </div>
</section>

<?php render_next_steps(); ?>
