<?php
/**
 * Listicle kit: the numbered, interactive list articles in includes/data/posts/.
 *
 * A post opts in with 'listicle' => true on its record in the blog data, and
 * templates/blog-post.php then loads assets/js/listicle.js. That script wires
 * up the controls these helpers print: done toggles, pass or fail buttons,
 * answer boxes, the progress board, filters and copy buttons. Each article's
 * own tool (an hreflang builder, a contrast checker and so on) is plain markup
 * in the post file with a data-lx-tool attribute, also run by listicle.js.
 *
 * Without JavaScript every item, tip and code sample still reads as a normal
 * article. Only the controls carry the class lx-js, and they stay hidden until
 * the script adds lx-on to <html>, so nothing on the page half-works.
 *
 * Styles: LISTICLES in assets/css/style.css.
 */

declare(strict_types=1);

/**
 * The "at a glance" list: every item as a jump link with a short note
 * (time needed, level, page type). Doubles as the table of contents.
 *
 * @param array<int, array{0: string, 1: string, 2?: string}> $items [id, title, note]
 */
function lx_glance(array $items, string $heading = 'At a glance'): void
{
    ?>
<nav class="lx-glance" aria-label="<?= e($heading) ?>">
    <p class="lx-glance-h"><?= e($heading) ?></p>
    <ol>
        <?php foreach ($items as $it): ?>
        <li><a href="#<?= e($it[0]) ?>"><?= e($it[1]) ?></a><?php if (!empty($it[2])): ?> <small><?= e($it[2]) ?></small><?php endif; ?></li>
        <?php endforeach; ?>
    </ol>
</nav>
    <?php
}

/**
 * The progress board for one article. Its key names the saved state in the
 * reader's browser, so give each article its own.
 *
 * @param array{
 *   mode?: string,        mark (one toggle per item), tri (pass, fail, not sure) or answer (a text box per item)
 *   label?: string,       what the count measures, as in "3 of 10 {label}"
 *   filters?: array<string, string>, filter key => chip label; 'all' and 'todo' are built in
 *   msgs?: array<int, array{0: int, 1: string}>, [up to this count, message]
 *   tools?: string        extra controls, as HTML (the brief builder's buttons)
 * } $opt
 */
function lx_board(string $key, array $opt = []): void
{
    $mode = $opt['mode'] ?? 'mark';
    $filters = $opt['filters'] ?? [];
    ?>
<div class="lx-board lx-js" data-lx-board="<?= e($key) ?>" data-lx-mode="<?= e($mode) ?>" role="region" aria-label="Your progress">
    <div class="lx-board-top">
        <p class="lx-count" aria-live="polite"><strong data-lx-done>0</strong> of <span data-lx-total>0</span> <?= e($opt['label'] ?? 'done') ?></p>
        <button class="lx-reset" type="button" data-lx-reset>Start over</button>
    </div>
    <div class="lx-bar" aria-hidden="true"><span></span></div>
    <?php if (!empty($opt['msgs'])): ?>
    <div class="lx-msgs">
        <?php foreach ($opt['msgs'] as [$upto, $msg]): ?><p data-upto="<?= (int) $upto ?>" hidden><?= $msg ?></p><?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php if ($mode === 'tri'): ?>
    <p class="lx-tally"><span class="lx-t-pass"><b data-lx-n="pass">0</b> pass</span><span class="lx-t-fail"><b data-lx-n="fail">0</b> fail</span><span class="lx-t-unsure"><b data-lx-n="unsure">0</b> not sure</span></p>
    <div class="lx-fixlist" data-lx-fixlist hidden>
        <p class="lx-fixlist-h">Fix these first</p>
        <ol></ol>
    </div>
    <?php endif; ?>
    <?= $opt['tools'] ?? '' ?>
    <div class="lx-filters" role="group" aria-label="Show">
        <button class="lx-chip" type="button" data-lx-filter="all" aria-pressed="true">All</button>
        <?php foreach ($filters as $fk => $fl): ?><button class="lx-chip" type="button" data-lx-filter="<?= e($fk) ?>" aria-pressed="false"><?= e($fl) ?></button><?php endforeach; ?>
        <button class="lx-chip" type="button" data-lx-filter="todo" aria-pressed="false"><?= e($mode === 'answer' ? 'Unanswered' : ($mode === 'tri' ? 'Not checked yet' : 'Still to do')) ?></button>
    </div>
</div>
    <?php
}

/**
 * One numbered item. The body is a closure that prints the item's content.
 *
 * @param array{
 *   type?: string,        filter keys this item matches, space separated
 *   pages?: string,       page types for the schema picker, space separated
 *   tags?: list<string>,  short facts shown under the title
 *   mark?: string,        label of the done toggle (mark mode)
 *   tri?: bool,           show pass, fail and not sure (tri mode)
 *   answer?: array{label: string, placeholder?: string} a text box (answer mode)
 * } $opt
 */
function lx_item(int $n, string $id, string $title, array $opt, callable $body): void
{
    $type = trim((string) ($opt['type'] ?? ''));
    $pages = trim((string) ($opt['pages'] ?? ''));
    ?>
<section class="lx-item" id="<?= e($id) ?>" data-lx-item="<?= e($id) ?>"<?= $type !== '' ? ' data-lx-type="' . e($type) . '"' : '' ?><?= $pages !== '' ? ' data-lx-pages="' . e($pages) . '"' : '' ?>>
    <div class="lx-head">
        <span class="lx-num" aria-hidden="true"><?= $n ?></span>
        <div class="lx-head-text">
            <h2><span class="sr-only"><?= $n ?>. </span><?= e($title) ?></h2>
            <?php if (!empty($opt['tags'])): ?>
            <ul class="lx-tags" aria-label="About this item">
                <?php foreach ($opt['tags'] as $i => $tag): ?><li<?= $i === 0 && $type !== '' ? ' class="lx-tag--' . e(explode(' ', $type)[0]) . '"' : '' ?>><?= e($tag) ?></li><?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
    </div>
    <div class="lx-body"><?php $body(); ?></div>
    <?php if (!empty($opt['mark']) || !empty($opt['tri']) || !empty($opt['answer'])): ?>
    <div class="lx-foot lx-js">
        <?php if (!empty($opt['mark'])): ?>
        <button class="lx-mark" type="button" data-lx-mark aria-pressed="false"><span class="lx-box" aria-hidden="true"></span><?= e($opt['mark']) ?><span class="sr-only">: <?= e($title) ?></span></button>
        <?php endif; ?>
        <?php if (!empty($opt['tri'])): ?>
        <div class="lx-tri" role="group" aria-label="Result for test <?= $n ?>">
            <button type="button" data-lx-val="pass" aria-pressed="false">Pass</button>
            <button type="button" data-lx-val="fail" aria-pressed="false">Fail</button>
            <button type="button" data-lx-val="unsure" aria-pressed="false">Not sure</button>
        </div>
        <?php endif; ?>
        <?php if (!empty($opt['answer'])): ?>
        <div class="lx-answer">
            <label for="lx-a-<?= e($id) ?>"><?= e($opt['answer']['label']) ?></label>
            <textarea id="lx-a-<?= e($id) ?>" data-lx-answer rows="4" placeholder="<?= e($opt['answer']['placeholder'] ?? '') ?>"></textarea>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</section>
    <?php
}

/** Two side-by-side panels, such as "How to check" and "How to fix". */
function lx_split(string $h1, string $html1, string $h2, string $html2): void
{
    ?>
<div class="lx-split">
    <div class="lx-panel"><p class="lx-panel-h"><?= e($h1) ?></p><?= $html1 ?></div>
    <div class="lx-panel lx-panel--fix"><p class="lx-panel-h"><?= e($h2) ?></p><?= $html2 ?></div>
</div>
    <?php
}

/** A code sample with a copy button. The code is printed as text, never as markup. */
function lx_code(string $code, string $lang = 'HTML'): void
{
    ?>
<div class="lx-code">
    <div class="lx-code-bar"><span><?= e($lang) ?></span><button class="lx-copy lx-js" type="button" data-lx-copy>Copy</button></div>
    <pre tabindex="0"><code><?= e(trim($code, "\n")) ?></code></pre>
</div>
    <?php
}
