<?php
/**
 * E-book or guide, read in full on the page.
 *
 * Nothing sits behind a form. A reader gets the whole resource immediately, and
 * search and AI engines can read and quote every chapter, which a downloadable
 * file behind an email gate never allowed. Expects: $page, $slug, $RESOURCES.
 */
$rslug = $page['resource'];
$res = $RESOURCES[$rslug] ?? null;
if (!$res) { http_response_code(404); echo '<section class="section"><div class="container"><h1>Resource not found</h1></div></section>'; return; }
$is_ebook = $res['type'] === 'ebook';
$hub = $is_ebook ? 'ebooks' : 'guides';
$type_label = $is_ebook ? 'Free e-book' : 'Complete guide';
$content = resource_content($rslug);
$chapters = $content['chapters'] ?? [];
$words = 0;
foreach ($chapters as [, $md]) { $words += str_word_count(strip_tags($md)); }
$minutes = max(3, (int) ceil($words / 220));
$url = abs_url('/' . $hub . '/' . $rslug . '/');
$related = related_ring($RESOURCES, $rslug, 3, fn($r) => $r['type'] === $res['type']);

echo jsonld_script([
    '@context' => 'https://schema.org',
    '@type' => $is_ebook ? 'Book' : 'TechArticle',
    '@id' => $url . '#resource',
    'name' => $res['h1'],
    'headline' => $res['h1'],
    'description' => $res['meta'],
    'url' => $url,
    'inLanguage' => 'en',
    'wordCount' => $words,
    'timeRequired' => 'PT' . $minutes . 'M',
    'isAccessibleForFree' => true,
    'author' => ['@id' => SITE_URL . '/#organization'],
    'publisher' => ['@id' => SITE_URL . '/#organization'],
    'image' => abs_url(page_share_image($page, $slug)[0]),
    'mainEntityOfPage' => $url,
    'hasPart' => array_map(static fn($c, $i) => ['@type' => 'WebPageElement', 'name' => $c[0], 'url' => $url . '#chapter-' . ($i + 1)], $chapters, array_keys($chapters)),
    'speakable' => ['@type' => 'SpeakableSpecification', 'cssSelector' => ['h1', '.answer-text']],
]);
?>
<article class="reader">
<?php render_hero([
    'k' => $type_label . ' · ' . $minutes . ' minute read', 'h1' => $page['h1'], 'lede' => $page['lede'],
    'tone' => 'lilac', 'layout' => hero_layout($slug),
    'cta' => ['Start reading', '#chapter-1'], 'alt' => ['All ' . ($is_ebook ? 'e-books' : 'guides'), '/' . $hub . '/'],
    'visual' => hero_visual(['book', $page['h1'], $type_label]),
]); ?>

<section class="section reader-intro">
    <div class="container split">
        <?php if (!empty($res['answer'])): ?>
        <div class="answer-box">
            <h2><?= e($res['answer'][0]) ?></h2>
            <p class="answer-text"><?= e($res['answer'][1]) ?></p>
        </div>
        <?php endif; ?>
        <div class="panel panel-accent">
            <h2 class="panel-h">What you will take away</h2>
            <ul class="check-list">
                <?php foreach (($res['takeaways'] ?? $res['learn']) as $l): ?><li><?= e($l) ?></li><?php endforeach; ?>
            </ul>
        </div>
    </div>
</section>

<section class="section reader-wrap">
    <div class="container reader-grid">
        <aside class="reader-side">
            <nav class="reader-toc" aria-label="Chapters">
                <p class="reader-toc-h">In this <?= $is_ebook ? 'e-book' : 'guide' ?></p>
                <ol>
                    <?php foreach ($chapters as $i => [$ct]): ?>
                    <li><a href="#chapter-<?= $i + 1 ?>"><?= e($ct) ?></a></li>
                    <?php endforeach; ?>
                </ol>
            </nav>
            <div class="reader-cta">
                <p><strong>Want this done for you?</strong></p>
                <p>Our team applies this work for clients every week.</p>
                <a class="btn btn-grad btn-block" href="/contact-us/">Talk to a Strategist</a>
            </div>
        </aside>
        <div class="reader-body">
            <?php foreach ($chapters as $i => [$ct, $md]): ?>
            <section id="chapter-<?= $i + 1 ?>" class="reader-chapter" aria-labelledby="ch-<?= $i + 1 ?>">
                <p class="reader-kicker">Chapter <?= $i + 1 ?></p>
                <h2 id="ch-<?= $i + 1 ?>"><?= e($ct) ?></h2>
                <?= reader_html($md) ?>
            </section>
            <?php if ($i === 1 && count($chapters) > 3): ?>
            <aside class="post-cta-inline" aria-label="Check your own site">
                <div>
                    <strong>See how your own site measures up</strong>
                    <span>Run the free checker. 22 checks, results in seconds, no call required.</span>
                </div>
                <a class="btn btn-grad" href="/free-seo-audit/">Check My Site</a>
            </aside>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
</article>

<?php if (!empty($res['table'])): ?>
<section class="section section-tint">
    <div class="container">
        <h2 class="section-title center"><?= e($res['table']['caption'] ?? 'At a glance') ?></h2>
        <figure class="post-table-wrap">
            <table class="post-table">
                <thead><tr><?php foreach ($res['table']['head'] as $th): ?><th scope="col"><?= e($th) ?></th><?php endforeach; ?></tr></thead>
                <tbody>
                    <?php foreach ($res['table']['rows'] as $row): ?>
                    <tr><?php foreach ($row as $ci => $cell): ?><?php if ($ci === 0): ?><th scope="row"><?= e($cell) ?></th><?php else: ?><td><?= e($cell) ?></td><?php endif; ?><?php endforeach; ?></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </figure>
    </div>
</section>
<?php endif; ?>

<?php render_faqs(array_slice($res['faqs'] ?? [], 0, 8), 'Questions readers ask'); ?>

<section class="cta-mid">
    <div class="container cta-mid-inner">
        <div>
            <h2>Ready to put this to work?</h2>
            <p>Tell us your goal. A senior strategist replies within one working day with an honest plan.</p>
        </div>
        <div class="cta-mid-actions">
            <a class="btn btn-dark" href="/contact-us/">Get My Growth Plan</a>
            <a class="cta-phone" href="<?= e(CONTACT_PHONE_HREF) ?>">or call <?= e(CONTACT_PHONE) ?></a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title center">Keep reading</h2>
        <div class="card-grid cols-3">
            <?php foreach ($related as $k => $r): ?>
            <a class="card post-card" href="/<?= e($hub) ?>/<?= e($k) ?>/">
                <span class="card-tag"><?= $is_ebook ? 'E-book' : 'Guide' ?></span>
                <h3><?= e($r['h1']) ?></h3>
                <p><?= e(mb_substr($r['lede'], 0, 120)) ?>...</p>
                <span class="post-card-foot"><span>Free to read</span><em aria-hidden="true">Read →</em></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
