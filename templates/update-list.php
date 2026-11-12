<?php
/**
 * Search updates index at /updates/. Expects: $page, $slug, $UPDATES
 * (newest first; pages.php sorts them). New updates come from the admin panel.
 */
$latest_slug = (string) (array_key_first($UPDATES) ?? '');
$latest = $latest_slug !== '' ? $UPDATES[$latest_slug] : null;

render_hero([
    'k' => 'Search updates',
    'h1' => $page['h1'],
    'lede' => $page['lede'],
    'cta' => $latest ? ['Read the latest update', '/updates/' . $latest_slug . '/'] : ['Get a free SEO audit', '/free-seo-audit/'],
    'alt' => ['Run a free SEO audit', '/free-seo-audit/'],
    'visual' => $latest ? ux_status_card($latest) : '',
    'tone' => 'paper',
    'layout' => 'split',
    'class' => 'hx--updates',
]);
?>

<section class="section ux-list-sec">
    <div class="container">
        <div class="ux-list-head">
            <p class="eyebrow">Newest first</p>
            <h2 class="section-title">Every update we have covered</h2>
            <p>Each one says what Google announced, when it started and finished, what it can mean for your traffic and what to check next.</p>
        </div>
        <?php if ($UPDATES): ?>
        <ol class="ux-list">
            <?php $first = true; foreach ($UPDATES as $uslug => $u): ?>
            <li>
                <a class="ux-item ux-st--<?= e((string) ($u['status'] ?? 'info')) ?><?= $first ? ' is-first' : '' ?>" href="/updates/<?= e($uslug) ?>/">
                    <span class="ux-item-top">
                        <span class="ux-pill"><?= e((string) ($u['type'] ?? 'Search update')) ?></span>
                        <span class="ux-live"><i></i><?= e(update_status_label($u)) ?></span>
                    </span>
                    <h3><?= e($u['h1']) ?></h3>
                    <?php /* The newest update shows its whole short answer; older ones a preview. */ ?>
                    <p><?= e($first || mb_strlen($u['lede']) <= 220 ? $u['lede'] : rtrim(mb_substr($u['lede'], 0, 210)) . '…') ?></p>
                    <?php $first = false; ?>
                    <span class="ux-item-foot">
                        <span><?= !empty($u['started']) ? 'Started ' . e(update_day($u['started'])) : 'Published ' . e(update_day($u['date'])) ?><?= !empty($u['read']) ? ' · ' . e($u['read']) . ' read' : '' ?></span>
                        <em aria-hidden="true">Read the update →</em>
                    </span>
                </a>
            </li>
            <?php endforeach; ?>
        </ol>
        <?php else: ?>
        <p>No updates yet. The next confirmed Google update will appear here.</p>
        <?php endif; ?>
    </div>
</section>

<section class="section ux-method-sec">
    <div class="container">
        <div class="ux-list-head">
            <p class="eyebrow">How we cover updates</p>
            <h2 class="section-title">Facts from Google first, then what they mean for you</h2>
        </div>
        <ol class="ux-method">
            <li><b>Confirmed facts only</b><span>Dates, durations and statements come from Google's Search Status Dashboard and Google Search Central, linked in every update. We name the publication whenever we cite news coverage.</span></li>
            <li><b>Plain words and an example</b><span>Each update explains what changed, what it can mean for your traffic and what to check, with a worked example you can follow.</span></li>
            <li><b>Kept current</b><span>When Google confirms a rollout is complete, we update the page, its status and its date.</span></li>
        </ol>
    </div>
</section>

<?php render_faqs(page_faqs($page, $slug), 'Questions about Google updates'); ?>

<?php render_cta_band('Traffic changed after an update?', 'Send us your site and the dates your numbers moved. A senior strategist replies within one working day.', 'Ask a Strategist', '/contact-us/'); ?>
