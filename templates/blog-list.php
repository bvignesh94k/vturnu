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
$q = strtolower(trim((string) ($_GET['q'] ?? '')));
if ($q !== '') {
    $posts = array_filter($posts, fn($b) => str_contains(strtolower($b['h1'] . ' ' . $b['category'] . ' ' . $b['intent']), $q));
}
uasort($posts, fn($a, $b) => strcmp($b['date'], $a['date']));
$base_url = '/' . ($slug === 'ai-blog-posts' ? 'ai-blog-posts' : 'blog') . '/';
?>
<?php
$hero_imgs = [];
foreach ($posts as $pslug => $hp) {
    if (is_file(BASE_PATH . '/assets/img/blog/' . $pslug . '-sm.webp') && count($hero_imgs) < 3) {
        $hero_imgs[] = '/assets/img/blog/' . $pslug . '-sm.webp';
    }
}
render_hero([
    'k' => 'Free knowledge, applied daily', 'h1' => $page['h1'], 'lede' => $page['lede'],
    'tone' => $slug === 'ai-blog-posts' ? 'lilac' : 'paper', 'layout' => $slug === 'ai-blog-posts' ? 'flip' : 'split',
    'visual' => hero_visual(['covers', $hero_imgs]),
]);
?>

<section class="section">
    <div class="container">
        <?php if (!empty($GLOBALS['UPDATES'])): $lu_slug = (string) array_key_first($GLOBALS['UPDATES']); $lu = $GLOBALS['UPDATES'][$lu_slug]; ?>
        <a class="ux-ribbon ux-st--<?= e((string) ($lu['status'] ?? 'info')) ?>" href="/updates/<?= e($lu_slug) ?>/">
            <span class="ux-live"><i></i><?= e(update_status_label($lu)) ?></span>
            <span><b>Latest Google update:</b> <?= e(update_short($lu)) ?>, explained</span>
            <em aria-hidden="true">Read it →</em>
        </a>
        <?php endif; ?>
        <?php /* One sentence in place of a row of topic buttons: pick a topic, or
                 type a few letters. It works without the script too, through the
                 Show button. */ ?>
        <form class="bf" method="get" action="<?= e($base_url) ?>" role="search" aria-label="Find an article" data-bf>
            <p class="bf-line">
                <label for="bf-cat">Show me</label>
                <span class="bf-pick"><select id="bf-cat" name="cat" data-bf-cat>
                    <option value="">everything</option>
                    <?php foreach ($cats as $c): ?><option value="<?= e($c) ?>"<?= $cat_filter === $c ? ' selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?>
                </select></span>
                <label for="bf-q">that mentions</label>
                <span class="bf-q"><input id="bf-q" type="search" name="q" autocomplete="off" enterkeyhint="search" placeholder="any word, e.g. leads" data-bf-q></span>
                <button class="bf-go" type="submit">Show</button>
            </p>
            <p class="bf-count" aria-live="polite" data-bf-count><?= count($posts) ?> articles, newest first</p>
        </form>

        <div class="card-grid cols-3 blog-grid">
            <?php $i = 0; foreach ($posts as $bslug => $b): $i++; ?>
            <a class="card post-card" href="/blog/<?= e($bslug) ?>/" data-reveal="card" data-bf-card data-cat="<?= e($b['category']) ?>" data-text="<?= e(strtolower($b['h1'] . ' ' . $b['category'] . ' ' . $b['intent'] . ' ' . ($b['kicker'] ?? ''))) ?>">
                <?php if (is_file(BASE_PATH . '/assets/img/blog/' . $bslug . '-sm.webp')): ?>
                <img class="card-img" src="/assets/img/blog/<?= e($bslug) ?>-sm.webp" alt="" width="640" height="336" loading="<?= $i <= 3 ? 'eager' : 'lazy' ?>" decoding="async">
                <?php endif; ?>
                <div class="post-card-meta">
                    <span class="card-tag"><?= e($b['category']) ?></span>
                    <span class="post-intent"><?= e($b['intent']) ?></span>
                </div>
                <h2 class="card-h"><?= e($b['h1']) ?></h2>
                <p><?= e(mb_substr($b['lede'], 0, 150)) ?>…</p>
                <span class="post-card-foot"><span><?= e(date('j M Y', strtotime($b['date']))) ?> · <?= e($b['read']) ?></span><em aria-hidden="true">Read →</em></span>
            </a>
            <?php if ($i === 6): ?>
            <div class="card blog-cta-card" data-reveal="card" data-bf-extra>
                <p class="eyebrow">Skip the reading list</p>
                <h2 class="card-h">Want this thinking applied to your business?</h2>
                <p>Every article here comes from real client work. Get the same strategists looking at your growth, starting with a free audit.</p>
                <a class="btn btn-grad" href="/free-seo-audit/">Run My Free Site Audit</a>
            </div>
            <?php endif; endforeach; ?>
        </div>
        <p class="bf-none" data-bf-none hidden>Nothing matches that yet. Try a shorter word, or <a href="/contact-us/">ask us the question directly</a>.</p>
        <script src="/assets/js/blog-list.js?v=<?= e(asset_v('/assets/js/blog-list.js')) ?>" defer></script>
    </div>
</section>

