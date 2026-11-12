<?php
/**
 * Search updates (/updates/): helpers for templates/update-list.php and
 * templates/update-post.php, plus the figures the update articles use.
 *
 * Records ship in includes/data/updates.php and are added or edited in the
 * admin panel (Search Updates), stored in content_overrides as type 'update'
 * and laid over the shipped ones in includes/data/pages.php. A long article
 * with its own layout lives in includes/data/updates/<slug>.php, like a rich
 * blog post in includes/data/posts/.
 *
 * Facts rule: dates and durations come from Google's Search Status Dashboard
 * and Google's documentation, linked where they are used. Figures are drawn
 * from data passed in, never typed into markup, so a chart and its table can
 * never disagree. Styles: SEARCH UPDATES in assets/css/style.css.
 */

declare(strict_types=1);

const UPDATE_TYPES = ['Core update', 'Spam update', 'Discover update', 'Search feature', 'AI search', 'Search Console', 'Policy change', 'Other'];
const UPDATE_STATUSES = ['rolling' => 'Rolling out', 'complete' => 'Rollout complete', 'announced' => 'Announced', 'info' => 'For information'];

function update_status_label(array $u): string
{
    return UPDATE_STATUSES[$u['status'] ?? 'info'] ?? UPDATE_STATUSES['info'];
}

/** A Y-m-d date as "10 Nov 2026" (or another format), '' when unset or invalid. */
function update_day(?string $ymd, string $fmt = 'j M Y'): string
{
    $t = $ymd ? strtotime($ymd) : false;
    return $t ? date($fmt, $t) : '';
}

/** Short name for menus and cards: the record's own, else the headline up to its colon. */
function update_short(array $u): string
{
    if (!empty($u['short'])) {
        return (string) $u['short'];
    }
    $h = (string) ($u['h1'] ?? '');
    $cut = strpos($h, ':');
    return $cut !== false ? trim(substr($h, 0, $cut)) : $h;
}

/** One line for a menu or card: status and start date. */
function update_line(array $u): string
{
    $when = update_day($u['started'] ?? '') ?: update_day($u['date'] ?? '');
    return update_status_label($u) . ($when !== '' ? ' · ' . $when : '');
}

/** When the rollout ended, or Google's latest estimate for it, as Y-m-d ('' if neither is known). */
function update_end(array $u): string
{
    if (!empty($u['finished'])) {
        return (string) $u['finished'];
    }
    $days = (int) ($u['days'] ?? 0);
    return (!empty($u['started']) && $days > 0) ? date('Y-m-d', strtotime($u['started'] . ' +' . $days . ' days')) : '';
}

/**
 * Body text written in the admin panel, as safe HTML. Everything is escaped,
 * then two marks become markup: **bold**, and [label](url) links. A link to
 * another site opens in a new tab with nofollow (ext_link); a link that starts
 * with a single / stays on this site. Nothing else can produce a tag.
 */
function update_inline(string $text): string
{
    $bold = static fn (string $html): string => (string) preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $html);
    $out = '';
    $pos = 0;
    if (preg_match_all('~\[([^\]\n]{1,160})\]\(((?:https?://|/)[^\s()<>"]{0,500})\)~', $text, $m, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
        foreach ($m as $hit) {
            $out .= $bold(e(substr($text, $pos, $hit[0][1] - $pos)));
            [$label, $url] = [$hit[1][0], $hit[2][0]];
            $out .= (str_starts_with($url, '/') && !str_starts_with($url, '//'))
                ? '<a href="' . e($url) . '">' . e($label) . '</a>'
                : ext_link($url, $label);
            $pos = $hit[0][1] + strlen($hit[0][0]);
        }
    }
    return $out . $bold(e(substr($text, $pos)));
}

/** Sections from the admin editor: [heading, paragraphs[], bullets[]]. */
function render_update_sections(array $sections): void
{
    $seen = [];
    foreach ($sections as $s) {
        $h = (string) ($s[0] ?? '');
        $id = trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower($h)), '-') ?: 'section';
        $id = isset($seen[$id]) ? $id . '-' . (++$seen[$id]) : $id;
        $seen[$id] = $seen[$id] ?? 1;
        echo '<h2 id="' . e($id) . '">' . e($h) . "</h2>\n";
        foreach (($s[1] ?? []) as $p) {
            echo '<p>' . update_inline((string) $p) . "</p>\n";
        }
        if (!empty($s[2])) {
            echo '<ul class="post-list">';
            foreach ($s[2] as $li) {
                echo '<li>' . update_inline((string) $li) . '</li>';
            }
            echo "</ul>\n";
        }
    }
}

/** The facts a reader checks first, as a definition list (the accessible twin of the hero card). */
function ux_facts(array $u, array $extra = []): void
{
    $rows = [];
    $rows[] = ['Update', update_short($u)];
    if (!empty($u['started'])) {
        $rows[] = ['Started', update_day($u['started'], 'j F Y') . (!empty($u['started_time']) ? ', ' . $u['started_time'] : '')];
    }
    if (!empty($u['scope'])) {
        $rows[] = ['Where it applies', (string) $u['scope']];
    }
    if (!empty($u['rollout'])) {
        $rows[] = ['How long it may take', (string) $u['rollout']];
    }
    $status = update_status_label($u);
    if (($u['status'] ?? '') === 'complete' && !empty($u['finished'])) {
        $status = 'Complete on ' . update_day($u['finished'], 'j F Y');
    } elseif (!empty($u['checked'])) {
        $status .= ' (checked ' . update_day($u['checked'], 'j F Y') . ')';
    }
    $rows[] = ['Status', $status];
    foreach ($extra as $r) {
        $rows[] = $r;
    }
    ?>
<div class="ux-facts">
    <p class="ux-facts-h">The update at a glance</p>
    <dl>
        <?php foreach ($rows as [$k, $v]): ?>
        <div><dt><?= e($k) ?></dt><dd><?= str_starts_with((string) $v, '<a ') ? $v : e((string) $v) ?></dd></div>
        <?php endforeach; ?>
    </dl>
</div>
    <?php
}

/**
 * Hero card for an update. Decorative (render_hero marks the stage aria-hidden),
 * so it holds no links; ux_facts() carries the same facts in the article.
 */
function ux_status_card(array $u): string
{
    $st = (string) ($u['status'] ?? 'info');
    $rows = [];
    if (!empty($u['started'])) {
        $rows[] = ['Started', update_day($u['started'])];
    }
    if (!empty($u['rollout'])) {
        $rows[] = ['Google says', (string) $u['rollout']];
    }
    if (!empty($u['scope'])) {
        $rows[] = ['Applies to', (string) $u['scope']];
    }
    $html = '<div class="ux-cv ux-st--' . e($st) . '">'
        . '<div class="ux-cv-top"><span class="ux-pill">' . e((string) ($u['type'] ?? 'Google update')) . '</span>'
        . '<span class="ux-live"><i></i>' . e(update_status_label($u)) . '</span></div>'
        . '<b class="ux-cv-name">' . e(update_short($u)) . '</b><dl class="ux-cv-dl">';
    foreach ($rows as [$k, $v]) {
        $html .= '<div><dt>' . e($k) . '</dt><dd>' . e($v) . '</dd></div>';
    }
    $html .= '</dl>';
    $end = update_end($u);
    if (!empty($u['started']) && $end !== '') {
        $done = $st === 'complete' && !empty($u['finished']);
        $html .= '<div class="ux-cv-win' . ($done ? ' is-done' : '') . '"><span class="ux-cv-track"><i></i></span>'
            . '<span class="ux-cv-dates"><small>' . e(update_day($u['started'], 'j M')) . '</small><small>'
            . e(($done ? '' : 'up to ') . update_day($end, 'j M')) . '</small></span></div>';
    }
    return $html . '</div>';
}

/**
 * Horizontal bar chart: how long each update took to roll out.
 *
 * @param array<int, array{label: string, hours: int, text: string, estimate?: bool, label_on?: bool}> $rows
 *        hours sets the bar length; text is the duration as Google words it.
 *        estimate draws the bar as a projection; label_on prints the value at
 *        the bar's tip (keep it to the few that tell the story; every value is
 *        in the table under the chart and in each bar's tooltip).
 */
function ux_duration_chart(string $id, string $title, string $sub, array $rows, int $max_days, string $source_html): void
{
    $max_h = $max_days * 24;
    $ticks = range(0, $max_days, 7);
    $has_est = (bool) array_filter($rows, static fn ($r) => !empty($r['estimate']));
    ?>
<figure class="ux-chart" id="<?= e($id) ?>" aria-labelledby="<?= e($id) ?>-t">
    <div class="ux-chart-head">
        <p class="ux-chart-t" id="<?= e($id) ?>-t"><?= e($title) ?></p>
        <p class="ux-chart-s"><?= e($sub) ?></p>
        <?php if ($has_est): ?>
        <ul class="ux-legend" aria-label="Key">
            <li><i class="ux-sw ux-sw--done"></i>Finished, as recorded by Google</li>
            <li><i class="ux-sw ux-sw--est"></i>Still rolling out: Google's estimate</li>
        </ul>
        <?php endif; ?>
    </div>
    <div class="ux-plot" style="--ux-ticks: <?= count($ticks) - 1 ?>">
        <div class="ux-grid" aria-hidden="true">
            <?php foreach ($ticks as $t): ?><span style="--x: <?= round($t / $max_days * 100, 3) ?>%"><b><?= $t ?> days</b></span><?php endforeach; ?>
        </div>
        <ol class="ux-rows">
            <?php foreach ($rows as $r):
                $w = max(0.0, min(100.0, $r['hours'] / $max_h * 100));
                $est = !empty($r['estimate']);
            ?>
            <li class="ux-row<?= $est ? ' is-est' : '' ?>" tabindex="0" aria-label="<?= e($r['label'] . ': ' . $r['text']) ?>">
                <span class="ux-row-l"><?= e($r['label']) ?></span>
                <span class="ux-track">
                    <span class="ux-bar" style="--w: <?= round($w, 3) ?>%"></span>
                    <?php if (!empty($r['label_on'])): ?><span class="ux-val" style="--w: <?= round($w, 3) ?>%"><?= e($r['text']) ?></span><?php endif; ?>
                    <span class="ux-tip" role="presentation" style="--w: <?= round($w, 3) ?>%"><b><?= e($r['text']) ?></b><?= e($r['label']) ?></span>
                </span>
            </li>
            <?php endforeach; ?>
        </ol>
    </div>
    <div class="bx-table-wrap ux-chart-table">
        <table class="bx-table" role="table">
            <caption>The same figures as a table</caption>
            <thead role="rowgroup"><tr role="row"><th scope="col" role="columnheader">Update</th><th scope="col" role="columnheader">Rollout time</th></tr></thead>
            <tbody role="rowgroup">
                <?php foreach ($rows as $r): ?>
                <tr role="row"><th scope="row" role="rowheader"><?= e($r['label']) ?></th><td role="cell" data-label="Rollout time"><?= e($r['text']) ?></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <figcaption class="ux-src"><?= $source_html ?></figcaption>
</figure>
    <?php
}

/**
 * The measurement window around an update: the week before it, the rollout,
 * the first full week after it, and the day a fair comparison can start.
 * Built from the record, so it redraws itself once a finish date is saved.
 *
 * @param array<int, array{0: string, 1: string}> $marks [Y-m-d, label] days to flag (holidays)
 */
function ux_window(string $id, array $u, array $marks, string $note_html): void
{
    $start = (string) ($u['started'] ?? '');
    $end = update_end($u);
    if ($start === '' || $end === '') {
        return;
    }
    $done = ($u['status'] ?? '') === 'complete' && !empty($u['finished']);
    $d = static fn (string $ymd, int $add): string => date('Y-m-d', strtotime($ymd . ' ' . ($add >= 0 ? '+' : '') . $add . ' days'));
    $base_from = $d($start, -7);
    $base_to = $d($start, -1);
    $after_from = $d($end, 1);
    $after_to = $d($end, 7);
    $compare = $d($end, 8);
    $first = strtotime($base_from);
    $last = strtotime($compare);
    $span = (int) round(($last - $first) / 86400) + 1;
    $col = static fn (string $ymd): int => (int) round((strtotime($ymd) - $first) / 86400) + 1;
    $segs = [
        ['base', 'Your "before" week', $base_from, $base_to],
        ['roll', $done ? 'The rollout' : 'The rollout, if it runs the full two weeks', $start, $end],
        ['after', 'First full week after it ends', $after_from, $after_to],
    ];
    $range = static fn (string $a, string $b): string => update_day($a, 'j M') . ' to ' . update_day($b, 'j M');
    ?>
<figure class="ux-win" id="<?= e($id) ?>" aria-labelledby="<?= e($id) ?>-t">
    <div class="ux-chart-head">
        <p class="ux-chart-t" id="<?= e($id) ?>-t">When to measure: a calendar for this update</p>
        <p class="ux-chart-s"><?= $done ? 'Built from the confirmed start and finish dates.' : 'Built from the start date and Google\'s estimate of up to two weeks. It redraws once Google confirms the finish.' ?></p>
    </div>
    <div class="ux-cal" style="--ux-days: <?= $span ?>" aria-hidden="true">
        <?php foreach ($segs as [$k, $label, $a, $b]): ?>
        <span class="ux-seg ux-seg--<?= e($k) ?>" style="grid-column: <?= $col($a) ?> / <?= $col($b) + 1 ?>"><b><?= e(update_day($a, 'j M')) ?></b></span>
        <?php endforeach; ?>
        <span class="ux-go" style="grid-column: <?= $col($compare) ?> / span 1"><b><?= e(update_day($compare, 'j M')) ?></b></span>
        <?php foreach ($marks as [$day, $label]): if ($day < $base_from || $day > $compare) { continue; } ?>
        <span class="ux-pin" style="grid-column: <?= $col($day) ?> / span 1"><b><?= e(update_day($day, 'j')) ?></b><i></i></span>
        <?php endforeach; ?>
    </div>
    <ol class="ux-key">
        <?php foreach ($segs as [$k, $label, $a, $b]): ?>
        <li><i class="ux-sw ux-sw--<?= e($k) ?>"></i><span><b><?= e($label) ?></b> <?= e($range($a, $b)) ?></span></li>
        <?php endforeach; ?>
        <li><i class="ux-sw ux-sw--go"></i><span><b>Compare from</b> <?= e(update_day($compare, 'j F')) ?>: the first full week after the rollout against the week before it</span></li>
        <?php foreach ($marks as [$day, $label]): if ($day < $base_from || $day > $compare) { continue; } ?>
        <li><i class="ux-sw ux-sw--pin"></i><span><b><?= e($label) ?></b> <?= e(update_day($day, 'j F')) ?></span></li>
        <?php endforeach; ?>
    </ol>
    <figcaption class="ux-src"><?= $note_html ?></figcaption>
</figure>
    <?php
}

/** NewsArticle markup for one update page. */
function update_jsonld(array $u, string $uslug, array $page, string $slug, int $words): array
{
    $url = abs_url('/updates/' . $uslug . '/');
    $node = [
        '@context' => 'https://schema.org',
        '@type' => 'NewsArticle',
        '@id' => $url . '#article',
        'headline' => (string) $u['h1'],
        'description' => (string) ($u['meta'] ?? ''),
        'articleSection' => 'Search updates',
        'datePublished' => (string) $u['date'],
        'dateModified' => (string) ($u['modified'] ?? $u['date']),
        'inLanguage' => 'en',
        'url' => $url,
        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $url],
        'author' => [
            '@type' => 'Organization',
            'name' => SITE_NAME . ' Strategy Team',
            'url' => SITE_URL . '/about-us/',
        ],
        'publisher' => ['@id' => SITE_URL . '/#organization'],
        'isPartOf' => ['@id' => SITE_URL . '/#website'],
        'image' => [
            '@type' => 'ImageObject',
            'url' => abs_url(page_share_image($page, $slug)[0]),
            'width' => 1200,
            'height' => 630,
        ],
        'about' => ['@type' => 'Thing', 'name' => 'Google Search ' . strtolower((string) ($u['type'] ?? 'update'))],
        'speakable' => ['@type' => 'SpeakableSpecification', 'cssSelector' => ['h1', '.answer-text']],
    ];
    if ($words > 0) {
        $node['wordCount'] = $words;
    }
    if (!empty($u['source'])) {
        $node['citation'] = (string) $u['source'];
    }
    return $node;
}
