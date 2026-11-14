<?php
/**
 * Post kit: shared parts for the long-form articles in includes/data/posts/,
 * from the November 2026 authority series onward. Older rich posts carry their
 * own copies of the table closure and the calls to action; new ones call these.
 *
 * Everything here prints plain HTML that reads without JavaScript. Diagrams
 * and charts are markup, not images, so the words in them reach people using
 * screen readers and crawlers that do not run scripts. Every chart repeats
 * its figures in a table underneath.
 *
 * Styles: POST KIT in assets/css/style.css. Charts reuse the ux-chart rules
 * written for includes/update-kit.php.
 */

/**
 * The answer box, filled from the lede on the post's record. It carries no
 * heading: the box itself is the signal.
 */
function px_answer(string $slug, string $label = ''): void
{
    ?>
<div class="answer-box post-answer">
    <p class="answer-text"><?= e($GLOBALS['BLOG'][$slug]['lede'] ?? '') ?></p>
</div>
    <?php
}

/**
 * Kept so article files need no change: the contents list is now the rail
 * beside the article, built in templates/blog-post.php from the headings.
 *
 * @param array<string, string> $items heading id => label
 */
function px_toc(array $items, string $heading = 'In this article'): void
{
}

/**
 * A data table. The first cell of each row is its row heading. Cells are
 * escaped unless 'html' is set; then the post file is trusted to pass safe
 * markup (links, strong).
 *
 * @param list<string> $head
 * @param list<list<string>> $rows
 * @param array{html?: bool, class?: string} $opt
 */
function px_table(string $caption, array $head, array $rows, array $opt = []): void
{
    $raw = !empty($opt['html']);
    $cell = static fn ($v): string => $raw ? (string) $v : e((string) $v);
    ?>
<div class="bx-table-wrap<?= !empty($opt['class']) ? ' ' . e($opt['class']) : '' ?>">
    <table class="bx-table" role="table">
        <caption><?= e($caption) ?></caption>
        <thead role="rowgroup"><tr role="row"><?php foreach ($head as $th): ?><th scope="col" role="columnheader"><?= e($th) ?></th><?php endforeach; ?></tr></thead>
        <tbody role="rowgroup">
            <?php foreach ($rows as $row): ?>
            <tr role="row">
                <th scope="row" role="rowheader"><?= $cell($row[0]) ?></th>
                <?php foreach (array_slice($row, 1) as $i => $c): ?><td role="cell" data-label="<?= e($head[$i + 1] ?? '') ?>"><?= $cell($c) ?></td><?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
    <?php
}

/**
 * A numbered diagram: steps in order, each with a label, a line of text and an
 * optional tag (who owns the step, or how far the number can be trusted).
 * 'layout' is stack (one under another, the default) or row (side by side on
 * wide screens). A tag's tone picks its marker: a solid, b half, c hollow, so
 * the difference never rests on colour alone.
 *
 * @param list<array{0: string, 1: string, 2?: string, 3?: string}> $steps [label, text, tag, tone]
 * @param array{sub?: string, layout?: string, cap?: string, key?: list<array{0: string, 1: string}>, start?: int} $opt
 */
function px_flow(string $id, string $title, array $steps, array $opt = []): void
{
    $layout = ($opt['layout'] ?? 'stack') === 'row' ? 'row' : 'stack';
    $n = (int) ($opt['start'] ?? 1);
    ?>
<figure class="pf pf--<?= $layout ?>" id="<?= e($id) ?>" aria-labelledby="<?= e($id) ?>-t">
    <div class="pf-head">
        <p class="pf-t" id="<?= e($id) ?>-t"><?= e($title) ?></p>
        <?php if (!empty($opt['sub'])): ?><p class="pf-s"><?= e($opt['sub']) ?></p><?php endif; ?>
        <?php if (!empty($opt['key'])): ?>
        <ul class="pf-key" aria-label="Key">
            <?php foreach ($opt['key'] as $k): ?><li><i class="pf-dot pf-dot--<?= e($k[0]) ?>" aria-hidden="true"></i><?= e($k[1]) ?></li><?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
    <ol class="pf-steps">
        <?php foreach ($steps as $s): ?>
        <li class="pf-step">
            <span class="pf-n" aria-hidden="true"><?= $n++ ?></span>
            <div class="pf-b">
                <p class="pf-h"><?= e($s[0]) ?></p>
                <p class="pf-d"><?= e($s[1]) ?></p>
            </div>
            <?php if (!empty($s[2])): ?><span class="pf-tag"><?php if (!empty($s[3])): ?><i class="pf-dot pf-dot--<?= e($s[3]) ?>" aria-hidden="true"></i><?php endif; ?><?= e($s[2]) ?></span><?php endif; ?>
        </li>
        <?php endforeach; ?>
    </ol>
    <?php if (!empty($opt['cap'])): ?><figcaption class="pf-cap"><?= $opt['cap'] ?></figcaption><?php endif; ?>
</figure>
    <?php
}

/**
 * A horizontal chart with the same figures in a table underneath. Mode 'bar'
 * draws each row from zero to its value. Mode 'range' draws a line from a
 * before value to an after value, with an open marker at the start and a
 * filled one at the end. Leave room past the largest value in 'max' so the
 * label beside each mark has somewhere to sit.
 *
 * @param list<array{label: string, text: string, value?: float|int, from?: float|int, to?: float|int}> $rows
 * @param array{
 *   mode?: string, max: float|int, ticks: list<float|int>, unit?: string,
 *   head: list<string>, cells?: list<list<string>>,
 *   legend?: list<array{0: string, 1: string}>, source?: string, table_caption?: string,
 *   wide?: bool           a wider label column, for long row names
 * } $opt
 */
function px_chart(string $id, string $title, string $sub, array $rows, array $opt): void
{
    $range = ($opt['mode'] ?? 'bar') === 'range';
    $max = (float) $opt['max'];
    $pc = static fn ($v): float => round(max(0.0, min(100.0, (float) $v / $max * 100)), 3);
    $unit = (string) ($opt['unit'] ?? '');
    ?>
<figure class="ux-chart" id="<?= e($id) ?>" aria-labelledby="<?= e($id) ?>-t">
    <div class="ux-chart-head">
        <p class="ux-chart-t" id="<?= e($id) ?>-t"><?= e($title) ?></p>
        <p class="ux-chart-s"><?= e($sub) ?></p>
        <?php if (!empty($opt['legend'])): ?>
        <ul class="ux-legend" aria-label="Key">
            <?php foreach ($opt['legend'] as $l): ?><li><i class="ux-sw ux-sw--<?= e($l[0]) ?>"></i><?= e($l[1]) ?></li><?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
    <div class="ux-plot<?= !empty($opt['wide']) ? ' ux-plot--wide' : '' ?>">
        <div class="ux-grid" aria-hidden="true">
            <?php foreach ($opt['ticks'] as $t): ?><span style="--x: <?= $pc($t) ?>%"><b><?= e($t . $unit) ?></b></span><?php endforeach; ?>
        </div>
        <ol class="ux-rows">
            <?php foreach ($rows as $r):
                $end = $pc($range ? $r['to'] : $r['value']);
            ?>
            <li class="ux-row<?= $range ? ' is-range' : '' ?>" tabindex="0" aria-label="<?= e($r['label'] . ': ' . $r['text']) ?>">
                <span class="ux-row-l"><?= e($r['label']) ?></span>
                <span class="ux-track">
                    <?php if ($range): ?>
                    <span class="ux-seg" style="--a: <?= $pc($r['from']) ?>%; --b: <?= $end ?>%"></span>
                    <span class="ux-dot ux-dot--from" style="--x: <?= $pc($r['from']) ?>%"></span>
                    <span class="ux-dot ux-dot--to" style="--x: <?= $end ?>%"></span>
                    <?php else: ?>
                    <span class="ux-bar" style="--w: <?= $end ?>%"></span>
                    <?php endif; ?>
                    <span class="ux-val" style="--w: <?= $end ?>%"><?= e($r['text']) ?></span>
                    <span class="ux-tip" role="presentation" style="--w: <?= $end ?>%"><b><?= e($r['text']) ?></b><?= e($r['label']) ?></span>
                </span>
            </li>
            <?php endforeach; ?>
        </ol>
    </div>
    <div class="bx-table-wrap ux-chart-table">
        <table class="bx-table" role="table">
            <caption><?= e($opt['table_caption'] ?? 'The same figures as a table') ?></caption>
            <thead role="rowgroup"><tr role="row"><?php foreach ($opt['head'] as $th): ?><th scope="col" role="columnheader"><?= e($th) ?></th><?php endforeach; ?></tr></thead>
            <tbody role="rowgroup">
                <?php foreach (($opt['cells'] ?? array_map(static fn ($r) => [$r['label'], $r['text']], $rows)) as $row): ?>
                <tr role="row"><th scope="row" role="rowheader"><?= e($row[0]) ?></th><?php foreach (array_slice($row, 1) as $i => $c): ?><td role="cell" data-label="<?= e($opt['head'][$i + 1] ?? '') ?>"><?= e($c) ?></td><?php endforeach; ?></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if (!empty($opt['source'])): ?><figcaption class="ux-src"><?= $opt['source'] ?></figcaption><?php endif; ?>
</figure>
    <?php
}

/**
 * A box for an invented example or a made-up set of numbers. The label says
 * so, in words, so nobody reads it as client data.
 */
function px_example(string $label, callable $body): void
{
    ?>
<aside class="px-eg" aria-label="<?= e($label) ?>">
    <p class="px-eg-k"><?= e($label) ?></p>
    <?php $body(); ?>
</aside>
    <?php
}

/**
 * The call to action in the middle of an article.
 *
 * @param array{h: string, p: string, href: string, btn: string, cta: string, note?: string, art?: string, label?: string} $o
 */
function px_cta_mid(array $o): void
{
    $art = [
        'doc' => '<rect x="16" y="18" width="88" height="84" rx="14" fill="#fff" stroke="#DAD5F2" stroke-width="6"/><path d="M32 42h56M32 60h40" stroke="#C9C5EA" stroke-width="7" stroke-linecap="round"/><circle cx="82" cy="82" r="20" fill="#1D1936"/><path d="M73 82l6 6 12-13" fill="none" stroke="#FFC94D" stroke-width="5.5" stroke-linecap="round" stroke-linejoin="round"/>',
        'scan' => '<rect x="14" y="20" width="92" height="66" rx="12" fill="#fff" stroke="#DAD5F2" stroke-width="6"/><path d="M28 40h40M28 54h28M28 68h34" stroke="#C9C5EA" stroke-width="7" stroke-linecap="round"/><circle cx="84" cy="74" r="19" fill="#1D1936"/><circle cx="82" cy="72" r="7.5" fill="none" stroke="#FFC94D" stroke-width="4.5"/><path d="M88 78l8 8" stroke="#FFC94D" stroke-width="5" stroke-linecap="round"/>',
        'chat' => '<path d="M18 26h84a8 8 0 0 1 8 8v40a8 8 0 0 1-8 8H58l-20 16V82H18a8 8 0 0 1-8-8V34a8 8 0 0 1 8-8z" fill="#fff" stroke="#DAD5F2" stroke-width="6" stroke-linejoin="round"/><path d="M30 46h60M30 62h38" stroke="#C9C5EA" stroke-width="7" stroke-linecap="round"/><circle cx="92" cy="30" r="16" fill="#1D1936"/><path d="M92 21v18M83 30h18" stroke="#FFC94D" stroke-width="5" stroke-linecap="round"/>',
    ][$o['art'] ?? 'doc'];
    ?>
<aside class="bx-cta bx-cta--audit" aria-label="<?= e($o['label'] ?? 'Next step') ?>">
    <svg class="bx-cta-art" viewBox="0 0 120 120" width="104" height="104" aria-hidden="true" focusable="false"><?= $art ?></svg>
    <div class="bx-cta-copy">
        <p class="bx-cta-h"><?= e($o['h']) ?></p>
        <p><?= e($o['p']) ?></p>
        <a class="bx-btn bx-btn--accent" href="<?= e($o['href']) ?>" data-cta="<?= e($o['cta']) ?>"><?= e($o['btn']) ?> <span aria-hidden="true">&rarr;</span></a>
        <?php if (!empty($o['note'])): ?><p class="bx-cta-note"><?= $o['note'] ?></p><?php endif; ?>
    </div>
</aside>
    <?php
}

/**
 * The closing call to action.
 *
 * @param array{h: string, p: string, btn: string, cta: string, href?: string, link?: array{0: string, 1: string, 2: string}, trust?: list<string>} $o
 */
function px_cta_main(array $o): void
{
    $trust = $o['trust'] ?? ['A senior strategist replies within one working day', 'Projects start from ' . min_budget(), 'Your details stay private'];
    ?>
<aside class="bx-cta bx-cta--main" aria-label="Start a conversation with VTurnU">
    <p class="bx-cta-h"><?= e($o['h']) ?></p>
    <p><?= e($o['p']) ?></p>
    <div class="bx-cta-acts">
        <a class="bx-btn bx-btn--light" href="<?= e($o['href'] ?? '/contact-us/') ?>" data-cta="<?= e($o['cta']) ?>"><?= e($o['btn']) ?> <span aria-hidden="true">&rarr;</span></a>
        <?php if (!empty($o['link'])): ?><a class="bx-link bx-link--light" href="<?= e($o['link'][0]) ?>" data-cta="<?= e($o['link'][2]) ?>"><?= e($o['link'][1]) ?></a><?php endif; ?>
    </div>
    <ul class="bx-cta-trust">
        <?php foreach ($trust as $t): ?><li><?= e($t) ?></li><?php endforeach; ?>
    </ul>
</aside>
    <?php
}

/** The author box under every article. */
function px_author(): void
{
    ?>
<aside class="bx-author" aria-label="About the author">
    <p class="bx-author-h">Written by the VTurnU strategy team</p>
    <p>VTurnU is a B2B digital marketing and branding agency based in Chennai, working across SEO, AI search, paid media, content and websites for clients worldwide. Read more <a href="/about-us/">about the team</a>, <a href="/how-we-work/">how we work</a> and <a href="/ai-policy/">how we use AI</a>.</p>
</aside>
    <?php
}
