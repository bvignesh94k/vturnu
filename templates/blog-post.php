<?php /** Blog article. Expects: $page, $slug, $BLOG, $PAGES */
$post = $BLOG[$page['post']] ?? null;
if (!$post) { http_response_code(404); echo '<section class="section"><div class="container"><h1>Post not found</h1></div></section>'; return; }

[$offer_title, $offer_sub, $offer_btn] = ['', '', ''];
$cta = $post['cta'] ?? ['Ready to grow?', 'Get a free audit and an honest conversation about what would move your numbers.', 'Get My Free Audit'];

/* Related: same category first, topped up from the rest of the feed.
   The immediate next post always keeps a slot: a post that is the only one in
   its category is otherwise never anyone's category match, so it would end up
   with no inbound internal links at all. */
$related = related_ring($BLOG, $page['post'], 1)
         + related_ring($BLOG, $page['post'], 3, fn($r) => $r['category'] === $post['category']);
$related = array_slice($related, 0, 3, true);

/* Rough word count from the rendered body, used as an article-depth signal. */
$post_words = 0;
foreach (($post['sections'] ?? []) as $sec) {
    $post_words += str_word_count(implode(' ', $sec[1] ?? [])) + str_word_count(implode(' ', $sec[2] ?? []));
}

echo jsonld_script([
    '@context' => 'https://schema.org',
    '@type' => 'BlogPosting',
    '@id' => abs_url('/blog/' . $page['post'] . '/') . '#article',
    'headline' => $post['h1'],
    'description' => $post['meta'],
    'articleSection' => $post['category'] ?? 'Strategy',
    'datePublished' => $post['date'],
    'dateModified' => $post['modified'] ?? $post['date'],
    'inLanguage' => 'en',
    'wordCount' => $post_words,
    'url' => abs_url('/blog/' . $page['post'] . '/'),
    'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => abs_url('/blog/' . $page['post'] . '/')],
    /* E-E-A-T: a named, credentialed author plus a reviewer beats a bare byline. */
    'author' => [
        '@type' => 'Organization',
        'name' => SITE_NAME . ' Strategy Team',
        'url' => SITE_URL . '/about-us/',
        'knowsAbout' => ['SEO', 'Answer Engine Optimization', 'Paid Media', 'Content Strategy', 'Web Development'],
    ],
    'publisher' => ['@id' => SITE_URL . '/#organization'],
    'isPartOf' => ['@id' => SITE_URL . '/#website'],
    'image' => abs_url('/assets/img/vturnu-logo-dark.png'),
    /* Tells voice assistants which parts are safe to read aloud. */
    'speakable' => ['@type' => 'SpeakableSpecification', 'cssSelector' => ['h1', '.answer-text']],
]);
if (!empty($post['faqs'])) { echo jsonld_script(jsonld_faq($post['faqs'])); }
?>
<article>
<section class="page-hero post-hero">
    <div class="container narrow">
        <div class="post-card-meta">
            <span class="card-tag"><?= e($post['category']) ?></span>
            <span class="post-intent"><?= e($post['intent']) ?></span>
        </div>
        <h1><?= e($post['h1']) ?></h1>
        <p class="post-byline">By the VTurnU strategy team · <?= e(date('j F Y', strtotime($post['date']))) ?> · <?= e($post['read']) ?> read</p>
    </div>
</section>

<section class="section post-body-wrap">
    <div class="container narrow">
        <!-- AEO answer box: the quotable summary -->
        <div class="answer-box post-answer">
            <p class="answer-text"><?= e($post['lede']) ?></p>
        </div>

        <div class="post-body">
            <?php $si = 0; foreach ($post['sections'] as $section): $si++;
                $h2 = $section[0]; $paras = $section[1] ?? []; $list = $section[2] ?? null;
                $table = $section[3] ?? null; ?>
            <h2><?= e($h2) ?></h2>
            <?php foreach ($paras as $p): ?><p><?= e($p) ?></p><?php endforeach; ?>
            <?php if ($list): ?>
            <ul class="post-list">
                <?php foreach ($list as $li): ?><li><?= e($li) ?></li><?php endforeach; ?>
            </ul>
            <?php endif; ?>
            <?php if ($table): ?>
            <?php /* Side-by-side comparison. Answer engines lift tabular
                     comparisons directly, and a comparison article without one
                     gives them nothing structured to quote. */ ?>
            <figure class="post-table-wrap">
                <table class="post-table">
                    <?php if (!empty($table['caption'])): ?>
                    <caption><?= e($table['caption']) ?></caption>
                    <?php endif; ?>
                    <thead>
                        <tr><?php foreach ($table['head'] as $th): ?><th scope="col"><?= e($th) ?></th><?php endforeach; ?></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($table['rows'] as $row): ?>
                        <tr>
                            <?php foreach ($row as $ci => $cell): ?>
                            <?php if ($ci === 0): ?><th scope="row"><?= e($cell) ?></th>
                            <?php else: ?><td><?= e($cell) ?></td><?php endif; ?>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </figure>
            <?php endif; ?>

            <?php if ($si === 2): ?>
            <?php /* Mid-article CTA. A reader here is still learning, not buying,
                     so the offer is a self-serve tool rather than a sales call.
                     Which tool depends on what they are reading: someone in an
                     AI Search piece wants to know what the engines say about
                     them, which the free HTML checker cannot answer, so those
                     posts point at VTurnAI instead. */
                  $ai_post = ($post['category'] ?? '') === 'AI Search'; ?>
            <aside class="post-cta-inline" aria-label="Check your own site">
                <?php if ($ai_post): ?>
                <div>
                    <strong>Curious what AI engines say about your brand?</strong>
                    <span><?= e(PRODUCT_NAME) ?> tracks your mentions across ChatGPT, Gemini, Perplexity and five more. Free for <?= e(PRODUCT_TRIAL_DAYS) ?> days.</span>
                </div>
                <a class="btn btn-grad" href="<?= e(PRODUCT_PAGE) ?>">Check My AI Visibility</a>
                <?php else: ?>
                <div>
                    <strong>Curious how your own site scores on this?</strong>
                    <span>Run the free checker. 22 checks, results in seconds, no call required.</span>
                </div>
                <a class="btn btn-grad" href="/free-seo-audit/">Check My Site</a>
                <?php endif; ?>
            </aside>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($post['takeaways'])): ?>
        <div class="takeaways" data-reveal="card">
            <h2>Key takeaways</h2>
            <ul class="check-list">
                <?php foreach ($post['takeaways'] as $t): ?><li><?= e($t) ?></li><?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php if (!empty($post['faqs'])): ?>
        <div class="post-faqs">
            <h2>Frequently asked questions</h2>
            <div class="faq-list">
                <?php foreach ($post['faqs'] as [$q, $a]): ?>
                <details class="faq"><summary><?= e($q) ?></summary><p><?= e($a) ?></p></details>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Post-specific conversion band -->
<section class="cta-mid">
    <div class="container cta-mid-inner">
        <div>
            <h2><?= e($cta[0]) ?></h2>
            <p><?= e($cta[1]) ?></p>
        </div>
        <div class="cta-mid-actions">
            <a class="btn btn-dark" href="/contact-us/"><?= e($cta[2]) ?></a>
            <a class="cta-phone" href="<?= e(CONTACT_PHONE_HREF) ?>">or call <?= e(CONTACT_PHONE) ?></a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title center">Keep reading</h2>
        <div class="card-grid cols-3">
            <?php foreach ($related as $rslug => $r): ?>
            <a class="card post-card" href="/blog/<?= e($rslug) ?>/">
                <span class="card-tag"><?= e($r['category']) ?></span>
                <h3><?= e($r['h1']) ?></h3>
                <span class="post-card-foot"><span><?= e($r['read']) ?> read</span><em aria-hidden="true">Read →</em></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
</article>
