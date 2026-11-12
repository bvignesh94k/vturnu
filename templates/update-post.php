<?php
/**
 * One search update at /updates/<slug>/. Expects: $page, $slug, $UPDATES.
 *
 * A record with 'rich' brings its own article from includes/data/updates/;
 * one written in the admin panel renders its lede, facts, sections, takeaways
 * and CTA here. Both get the same hero, FAQs, schema and onward links.
 */
$uslug = (string) ($page['update'] ?? '');
$u = $UPDATES[$uslug] ?? null;
if (!$u) {
    http_response_code(404);
    echo '<section class="section"><div class="container"><h1>Update not found</h1></div></section>';
    return;
}

$rich_file = !empty($u['rich']) ? BASE_PATH . '/includes/data/updates/' . basename((string) $u['rich']) . '.php' : '';
$rich = $rich_file !== '' && is_file($rich_file) ? require $rich_file : null;
$body_html = $closing_html = '';
if ($rich) {
    ob_start();
    $rich['body']();
    $body_html = (string) ob_get_clean();
    ob_start();
    $rich['closing']();
    $closing_html = (string) ob_get_clean();
}
$words = $rich
    ? str_word_count(strip_tags($body_html . ' ' . $closing_html))
    : str_word_count(implode(' ', array_map(static fn ($s) => implode(' ', $s[1] ?? []) . ' ' . implode(' ', $s[2] ?? []), $u['sections'] ?? [])));

echo jsonld_script(update_jsonld($u, $uslug, $page, $slug, $words));

$cta = array_values(array_filter((array) ($u['cta'] ?? []), static fn ($v) => trim((string) $v) !== ''));
$cta = count($cta) === 3 ? $cta : ['Traffic Changed After an Update?', 'Send us your site and the dates. A senior strategist replies within one working day.', 'Ask a Strategist'];

/* Onward reading: the other updates first, then the articles readers check after one. */
$more = array_slice(array_filter($UPDATES, static fn ($k) => $k !== $uslug, ARRAY_FILTER_USE_KEY), 0, 3, true);
$reading = [];
foreach ($more as $mslug => $m) {
    $reading[] = ['/updates/' . $mslug . '/', $m['type'] ?? 'Search update', $m['h1'], update_line($m)];
}
foreach (['content-refresh-checklist', 'does-google-penalise-ai-content', 'how-to-measure-ai-search-visibility', 'why-your-website-gets-traffic-but-no-leads'] as $b) {
    if (count($reading) >= 3) {
        break;
    }
    if (isset($BLOG[$b])) {
        $reading[] = ['/blog/' . $b . '/', $BLOG[$b]['category'] ?? 'Insight', $BLOG[$b]['h1'], ($BLOG[$b]['read'] ?? '') . ' read'];
    }
}
?>
<article>
<?php render_hero([
    'k' => ($u['type'] ?? 'Search update') . ' · ' . update_status_label($u),
    'h1' => $u['h1'],
    'tone' => 'paper',
    'layout' => 'split',
    'extra' => '<p class="post-byline">By the VTurnU strategy team · Published ' . e(update_day($u['date'], 'j F Y'))
        . (($u['modified'] ?? $u['date']) !== $u['date'] ? ' · Updated ' . e(update_day($u['modified'], 'j F Y')) : '')
        . (!empty($u['read']) ? ' · ' . e($u['read']) . ' read' : '') . '</p>',
    'visual' => ux_status_card($u),
    'class' => 'hx--post hx--update',
]); ?>

<section class="section post-body-wrap">
    <div class="container narrow">
        <div class="post-body post-rich">
            <?php if ($rich): ?>
                <?= $body_html ?>
            <?php else: ?>
                <div class="answer-box post-answer">
                    <p class="bx-answer-k">The short answer</p>
                    <p class="answer-text"><?= e($u['lede']) ?></p>
                </div>
                <?php ux_facts($u, !empty($u['source']) ? [['Official source', ext_link((string) $u['source'], 'Google\'s announcement')]] : []); ?>
                <?php render_update_sections($u['sections'] ?? []); ?>
                <?php if (!empty($u['takeaways'])): ?>
                <div class="takeaways">
                    <h2>Key takeaways</h2>
                    <ul><?php foreach ($u['takeaways'] as $t): ?><li><?= update_inline((string) $t) ?></li><?php endforeach; ?></ul>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php render_faqs($u['faqs'] ?? [], 'Frequently Asked Questions'); ?>

<section class="section post-body-wrap post-closing">
    <div class="container narrow">
        <div class="post-body post-rich">
            <?php if ($rich): ?>
                <?= $closing_html ?>
            <?php else: ?>
            <aside class="bx-cta bx-cta--main" aria-label="Start a conversation with VTurnU">
                <p class="bx-cta-h"><?= e($cta[0]) ?></p>
                <p><?= e($cta[1]) ?></p>
                <div class="bx-cta-acts">
                    <a class="bx-btn bx-btn--light" href="/contact-us/" data-cta="update-contact"><?= e($cta[2]) ?> <span aria-hidden="true">&rarr;</span></a>
                    <a class="bx-link bx-link--light" href="/free-seo-audit/" data-cta="update-audit">Run a Free SEO Audit</a>
                </div>
                <ul class="bx-cta-trust">
                    <li>A senior strategist replies within one working day</li>
                    <li>Projects start from <?= e(min_budget()) ?></li>
                    <li>Your details stay private</li>
                </ul>
            </aside>
            <aside class="bx-author" aria-label="About the author">
                <p class="bx-author-h">Written by the VTurnU strategy team</p>
                <p>We follow every confirmed Google update and explain what it means for businesses, with the sources linked. Read more <a href="/about-us/">about the team</a> and <a href="/how-we-work/">how we work</a>.</p>
            </aside>
            <?php endif; ?>
            <?php render_related_service(['/seo-services/', 'SEO services'], 'is where we turn updates like this into a plan for your site.'); ?>
        </div>
    </div>
</section>

<?php if ($reading): ?>
<section class="section">
    <div class="container">
        <h2 class="section-title center">Read next</h2>
        <div class="card-grid cols-3">
            <?php foreach ($reading as [$href, $tag, $h, $foot]): ?>
            <a class="card post-card" href="<?= e($href) ?>">
                <span class="card-tag"><?= e((string) $tag) ?></span>
                <h3><?= e((string) $h) ?></h3>
                <span class="post-card-foot"><span><?= e((string) $foot) ?></span><em aria-hidden="true">Read →</em></span>
            </a>
            <?php endforeach; ?>
        </div>
        <p class="ux-all"><a class="hx-link" href="/updates/">All search updates</a></p>
    </div>
</section>
<?php endif; ?>
</article>
