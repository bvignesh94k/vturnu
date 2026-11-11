<?php
/**
 * Content landing pages: the building blocks behind templates/content-landing.php.
 * The words for each page live in includes/data/content-landings.php.
 *
 * The family has one idea of its own: show the edit. Every page puts a weak
 * line beside the line an editor would write instead, because that is the
 * service. The hero visuals (cx_*) draw the thing each page sells, a marked-up
 * draft, an editorial calendar, a page plan, and carry no result claims: like
 * the other heroes they are illustrations, hidden from assistive tech.
 *
 * The sections (cl_*) follow a buyer's decision: what it is, is this my
 * problem, what good looks like, what I get, how it runs, has it worked, is it
 * for me, how do I start. Styles: CONTENT LANDING PAGES in assets/css/style.css.
 */

declare(strict_types=1);

/* ------------------------------------------------------------------ */
/* Hero visuals                                                        */
/* ------------------------------------------------------------------ */

/** The hero visual named by a page's data: [type, ...args]. Falls back to hero-kit types. */
function cx_visual(array $v): string
{
    $type = (string) ($v[0] ?? '');
    $a = array_slice($v, 1);
    return match ($type) {
        'edit' => cx_edit((string) $a[0], (array) $a[1], (string) ($a[2] ?? 'Specific beats impressive')),
        'calendar' => cx_calendar((string) $a[0], (array) $a[1]),
        'outline' => cx_outline((string) $a[0], (array) $a[1], (string) $a[2]),
        'pages' => cx_pages((array) $a[0]),
        'story' => cx_story((array) $a[0], (string) ($a[1] ?? 'Approved by the client')),
        'pdp' => cx_pdp((string) $a[0], (array) $a[1], (array) $a[2]),
        'pair' => cx_pair((string) $a[0], (string) $a[1], (string) $a[2], (string) $a[3], (string) ($a[4] ?? 'Checked by your engineer')),
        default => hero_visual($v),
    };
}

/** A draft with an editor's marks: each weak line struck out above its replacement. */
function cx_edit(string $title, array $rows, string $tag): string
{
    $body = '<b class="hx-dt">' . e($title) . '</b>';
    foreach (array_slice($rows, 0, 2) as [$old, $new]) {
        $body .= '<p class="cx-cut">' . e((string) $old) . '</p><p class="cx-new">' . e((string) $new) . '</p>';
    }
    return hx_win('Draft, edited', $body . hx_lines([84, 56])) . hx_tag($tag, 'hx-tag--a') . hx_tag('Filler cut', 'hx-tag--b');
}

/** A month of content: four weeks, each with the pieces planned for it. */
function cx_calendar(string $title, array $weeks): string
{
    $body = '<b class="hx-dt">' . e($title) . '</b><div class="cx-cal">';
    foreach (array_slice($weeks, 0, 4) as $i => [$week, $items]) {
        $body .= '<div><small>' . e((string) $week) . '</small>';
        foreach (array_slice((array) $items, 0, 2) as $k => $it) {
            $body .= '<span class="hx-g' . (($i + $k) % 4) . '">' . e((string) $it) . '</span>';
        }
        $body .= '</div>';
    }
    return hx_win('Editorial calendar', $body . '</div>') . hx_tag('Planned from buyer questions', 'hx-tag--a') . hx_tag('One monthly review', 'hx-tag--b');
}

/** An article outline, with the question from the interview that started it. */
function cx_outline(string $title, array $items, string $bubble): string
{
    $body = '<p class="hx-you">' . e($bubble) . '</p><b class="hx-dt">' . e($title) . '</b><ol class="cx-ol">';
    foreach (array_slice($items, 0, 4) as $it) {
        $body .= '<li>' . e((string) $it) . '</li>';
    }
    return hx_win('Article outline', $body . '</ol>') . hx_tag('From a 30-minute interview', 'hx-tag--a');
}

/** Four pages of a site, each with its headline and where it stands. */
function cx_pages(array $pages): string
{
    $out = '<div class="cx-pages">';
    foreach (array_slice($pages, 0, 4) as $i => [$name, $head, $state]) {
        $out .= '<div class="cx-page cx-p' . $i . '"><small>' . e((string) $name) . '</small><b>' . e((string) $head) . '</b>' . hx_lines([88, 62]) . '<em>' . e((string) $state) . '</em></div>';
    }
    return $out . '</div>' . hx_tag('Every page has one job', 'hx-tag--a');
}

/** A case study in three cards, with the client's sign-off stamped on it. */
function cx_story(array $rows, string $stamp): string
{
    $out = '<div class="cx-story">';
    foreach (array_slice($rows, 0, 3) as $i => [$k, $t]) {
        $out .= '<div class="cx-s' . $i . '"><small>' . e((string) $k) . '</small><p>' . e((string) $t) . '</p></div>';
    }
    return $out . '<span class="cx-stamp">' . e($stamp) . '</span></div>';
}

/** A product page: name, what it does for the buyer, and two rows of specification. */
function cx_pdp(string $name, array $bullets, array $specs): string
{
    $body = '<div class="cx-pdp"><i class="hx-g1"></i><div><b class="hx-dt">' . e($name) . '</b><ul>';
    foreach (array_slice($bullets, 0, 3) as $b) {
        $body .= '<li>' . e((string) $b) . '</li>';
    }
    $body .= '</ul></div></div>';
    foreach (array_slice($specs, 0, 2) as [$k, $v]) {
        $body .= '<div class="hx-mail"><b>' . e((string) $k) . '</b><small>' . e((string) $v) . '</small></div>';
    }
    return hx_win('Product page', $body) . hx_tag('Written for this product', 'hx-tag--a');
}

/** The engineer's note and the page a buyer reads, side by side. */
function cx_pair(string $a, string $at, string $b, string $bt, string $tag): string
{
    return '<div class="hx-win hx-tr1 cx-eng"><div class="hx-body"><small>' . e($a) . '</small><p>' . e($at) . '</p></div></div>'
        . '<div class="hx-win hx-tr2"><div class="hx-body"><small>' . e($b) . '</small><p>' . e($bt) . '</p></div></div>'
        . hx_tag($tag, 'hx-tag--a');
}

/* ------------------------------------------------------------------ */
/* Sections                                                            */
/* ------------------------------------------------------------------ */

/** Three short promises under the hero buttons. */
function cl_chips(array $chips): string
{
    $out = '<ul class="cl-chips">';
    foreach (array_slice($chips, 0, 3) as $c) {
        $out .= '<li>' . e((string) $c) . '</li>';
    }
    return $out . '</ul>';
}

/** The dark strip under the hero: measured work, each figure as published in a case study. */
function cl_proofbar(array $items): void
{
    if (!$items) {
        return;
    }
    ?>
<section class="mp-proofbar" aria-label="Work you can check">
    <div class="container">
        <ul>
            <?php foreach (array_slice($items, 0, 3) as [$n, $l]): ?>
            <li><b><?= e((string) $n) ?></b><?= e((string) $l) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
    <?php
}

/**
 * The direct answer and the facts a buyer checks first. The answer is the
 * page's first FAQ too, so the text here and in the FAQ markup is the same.
 */
function cl_answer(array $answer, array $facts): void
{
    ?>
<section class="mp-sec cl-answer-sec">
    <div class="container cl-answer">
        <div class="cl-answer-main">
            <p class="mp-k">The short answer</p>
            <h2 class="cl-answer-q"><?= e($answer[0]) ?></h2>
            <p class="answer-text"><?= e($answer[1]) ?></p>
        </div>
        <dl class="cl-facts">
            <?php foreach ($facts as [$k, $v]): ?>
            <div><dt><?= e((string) $k) ?></dt><dd><?= e((string) $v) ?></dd></div>
            <?php endforeach; ?>
        </dl>
    </div>
</section>
    <?php
}

/** The edit: what usually gets written, what we write instead, and why. */
function cl_edit(array $edit, string $btn): void
{
    [$title, $sub, $rows] = $edit;
    ?>
<section class="mp-sec cl-edit-sec">
    <div class="container">
        <div class="cl-head">
            <p class="mp-k">The edit</p>
            <h2><?= e($title) ?></h2>
            <p class="cl-sub"><?= e($sub) ?></p>
        </div>
        <ol class="cl-edit">
            <?php foreach ($rows as [$before, $after, $why]): ?>
            <li data-reveal="card">
                <div class="cl-before"><span class="cl-lbl">What usually gets written</span><p><?= e((string) $before) ?></p></div>
                <div class="cl-after"><span class="cl-lbl">What we write instead</span><p><?= e((string) $after) ?></p></div>
                <p class="cl-why"><b>Why</b> <?= e((string) $why) ?></p>
            </li>
            <?php endforeach; ?>
        </ol>
        <p class="cl-inline">
            <a class="cl-btn cl-btn--grad" href="#enquire"><?= e($btn) ?> <span aria-hidden="true">&rarr;</span></a>
            <span>A senior strategist replies within one working day.</span>
        </p>
    </div>
</section>
    <?php
}

/** What is included: [title, text, optional link] cards. */
function cl_scope(array $scope): void
{
    [$title, $items] = $scope;
    ?>
<section class="mp-sec cl-scope-sec" id="scope">
    <div class="container">
        <div class="cl-head">
            <p class="mp-k">What you get</p>
            <h2><?= e($title) ?></h2>
        </div>
        <ul class="cl-scope">
            <?php foreach ($items as $i => $it): [$t, $d] = $it; $url = (string) ($it[2] ?? ''); ?>
            <li class="cl-a<?= $i % 6 ?>" data-reveal="card">
                <span class="cl-n" aria-hidden="true"><?= sprintf('%02d', $i + 1) ?></span>
                <h3><?php if ($url !== ''): ?><a href="<?= e($url) ?>"><?= e((string) $t) ?></a><?php else: ?><?= e((string) $t) ?><?php endif; ?></h3>
                <p><?= e((string) $d) ?></p>
                <?php if ($url !== ''): ?><a class="cl-more" href="<?= e($url) ?>" aria-label="<?= e((string) $t) ?>: see the service">See the service <span aria-hidden="true">&rarr;</span></a><?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
    <?php
}

/**
 * A call to action between sections. $tone: warm, cool or ink.
 * $cta = [title, text, button label, optional href, optional [alt label, alt href]].
 */
function cl_cta(string $tone, array $cta): void
{
    [$title, $text, $btn] = $cta;
    $href = (string) ($cta[3] ?? '#enquire');
    $alt = $cta[4] ?? ['or message us on WhatsApp', 'https://wa.me/' . CONTACT_WHATSAPP];
    $ext = str_starts_with((string) $alt[1], 'http');
    ?>
<section class="cl-cta cl-cta--<?= e($tone) ?>" aria-label="<?= e($title) ?>">
    <div class="container cl-cta-in">
        <div class="cl-cta-copy">
            <h2><?= e($title) ?></h2>
            <p><?= e($text) ?></p>
        </div>
        <div class="cl-cta-act">
            <a class="cl-btn" href="<?= e($href) ?>"><?= e($btn) ?> <span aria-hidden="true">&rarr;</span></a>
            <a class="cl-cta-alt" href="<?= e((string) $alt[1]) ?>"<?= $ext ? ' target="_blank" rel="noopener nofollow"' : '' ?>><?= e((string) $alt[0]) ?></a>
        </div>
    </div>
</section>
    <?php
}

/** How the work runs, step by step. */
function cl_steps(array $steps): void
{
    [$title, $rows] = $steps;
    ?>
<section class="mp-sec cl-steps-sec" id="how">
    <div class="container">
        <div class="cl-head">
            <p class="mp-k">How it runs</p>
            <h2><?= e($title) ?></h2>
        </div>
        <ol class="cl-steps cl-steps--<?= count($rows) ?>">
            <?php foreach ($rows as $i => [$t, $d]): ?>
            <li data-reveal="card"><b aria-hidden="true"><?= $i + 1 ?></b><h3><?= e((string) $t) ?></h3><p><?= e((string) $d) ?></p></li>
            <?php endforeach; ?>
        </ol>
    </div>
</section>
    <?php
}

/**
 * A decision table. On a phone each row becomes a card (the .bx-table rules).
 * $table = [caption, head[], rows[][], note?] where note is a string or
 * [text before, link label, url, text after].
 */
function cl_table(array $table): void
{
    [$caption, $head, $rows] = $table;
    $note = $table[3] ?? '';
    ?>
<section class="mp-sec cl-table-sec">
    <div class="container">
        <div class="cl-head">
            <p class="mp-k">At a glance</p>
            <h2><?= e($caption) ?></h2>
        </div>
        <div class="bx-table-wrap">
            <table class="bx-table<?= count($head) > 3 ? ' bx-table--4' : '' ?>" role="table" aria-label="<?= e($caption) ?>">
                <thead role="rowgroup"><tr role="row"><?php foreach ($head as $th): ?><th scope="col" role="columnheader"><?= e((string) $th) ?></th><?php endforeach; ?></tr></thead>
                <tbody role="rowgroup">
                    <?php foreach ($rows as $row): ?>
                    <tr role="row">
                        <th scope="row" role="rowheader"><?= e((string) $row[0]) ?></th>
                        <?php foreach (array_slice($row, 1) as $i => $cell): ?><td role="cell" data-label="<?= e((string) $head[$i + 1]) ?>"><?= e((string) $cell) ?></td><?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (is_array($note)): ?>
        <p class="cl-note"><?= e((string) $note[0]) ?><a href="<?= e((string) $note[2]) ?>"><?= e((string) $note[1]) ?></a><?= e((string) ($note[3] ?? '')) ?></p>
        <?php elseif ($note !== ''): ?>
        <p class="cl-note"><?= e((string) $note) ?></p>
        <?php endif; ?>
    </div>
</section>
    <?php
}

/**
 * Proof. Either a named client case study ('slug', with the figure and the
 * story as published there) or our own published work ('links').
 */
function cl_case(array $case): void
{
    $c = !empty($case['slug']) ? ($GLOBALS['CASES'][$case['slug']] ?? null) : null;
    if (!empty($case['slug']) && !$c) {
        return;
    }
    ?>
<section class="mp-sec cl-case-sec">
    <div class="container">
        <div class="mp-proof" data-reveal="card">
            <div>
                <p class="mp-k"><?= e((string) ($case['k'] ?? 'Real client, real number')) ?></p>
                <div class="mp-proof-n"><?= e((string) $case['n']) ?></div>
                <p class="mp-proof-l"><?= e((string) $case['l']) ?></p>
            </div>
            <div>
                <p class="mp-story"><?= e((string) $case['story']) ?></p>
                <?php if ($c): ?>
                <a href="/case-studies/<?= e((string) $case['slug']) ?>/">Read the <?= e((string) $c['client']) ?> case study &rarr;</a>
                <small>Measured data, with the source and dates on the case study.</small>
                <?php else: ?>
                <p class="cl-case-links"><?php foreach ((array) ($case['links'] ?? []) as $i => [$label, $url]): ?><?= $i ? ' ' : '' ?><a href="<?= e((string) $url) ?>"><?= e((string) $label) ?> &rarr;</a><?php endforeach; ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
    <?php
}

/** Where to go next: the other content services, then things to read. */
function cl_related(string $slug, array $reading): void
{
    $all = $GLOBALS['CONTENT_LANDINGS'] ?? [];
    $cards = [];
    foreach ($all as $s => $l) {
        if ($s !== $slug) {
            $cards[] = [$l['name'], page_url($s), $l['card']];
        }
    }
    foreach ([['SEO Content Writing', '/seo-content-writing/', 'Search-led briefs, optimisation and refreshes.'], ['Copywriting', '/copywriting-services/', 'Sales pages, ads and email sequences.']] as $extra) {
        $cards[] = $extra;
    }
    ?>
<section class="mp-sec cl-rel-sec" aria-labelledby="cl-rel-h">
    <div class="container">
        <div class="cl-head">
            <p class="mp-k">More from the content team</p>
            <h2 id="cl-rel-h">Other things we write</h2>
        </div>
        <ul class="cl-rel">
            <?php foreach ($cards as [$label, $url, $desc]): ?>
            <li><a href="<?= e((string) $url) ?>"><b><?= e((string) $label) ?></b><span><?= e((string) $desc) ?></span></a></li>
            <?php endforeach; ?>
        </ul>
        <?php if ($reading): ?>
        <p class="cl-read"><b>Worth reading first:</b>
            <?php foreach ($reading as $i => [$label, $url]): ?><?= $i ? '<span aria-hidden="true"> · </span>' : '' ?><a href="<?= e((string) $url) ?>"><?= e((string) $label) ?></a><?php endforeach; ?>
        </p>
        <?php endif; ?>
        <p class="mp-rel-h">Part of our <a href="/content-marketing/">content marketing services</a>.</p>
    </div>
</section>
    <?php
}

/** Service schema for a content landing page, with what is included as an offer catalogue. */
function cl_jsonld(array $page, string $slug, array $l): string
{
    $offers = array_map(
        static fn(array $it): array => ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => (string) $it[0], 'description' => (string) $it[1]]],
        $l['scope'][1]
    );
    $url = abs_url(page_url($slug));
    return jsonld_script([
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        '@id' => $url . '#service',
        'name' => $page['h1'],
        'serviceType' => $l['name'],
        'description' => $page['meta'],
        'url' => $url,
        'provider' => ['@id' => SITE_URL . '/#organization'],
        'areaServed' => array_map(static fn($c) => ['@type' => 'Country', 'name' => $c], TARGET_MARKETS),
        'audience' => ['@type' => 'BusinessAudience', 'audienceType' => 'B2B companies'],
        'offers' => [
            '@type' => 'Offer', 'url' => $url . '#enquire',
            'priceSpecification' => ['@type' => 'PriceSpecification', 'minPrice' => 50000, 'priceCurrency' => 'INR'],
        ],
        'hasOfferCatalog' => ['@type' => 'OfferCatalog', 'name' => $l['name'] . ': what is included', 'itemListElement' => $offers],
    ]) . "\n" . jsonld_script([
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        '@id' => $url . '#webpage',
        'name' => $page['title'],
        'url' => $url,
        'description' => $page['meta'],
        'inLanguage' => 'en',
        'isPartOf' => ['@id' => SITE_URL . '/#website'],
        'about' => ['@id' => $url . '#service'],
        'dateModified' => $l['updated'] ?? SITE_UPDATED,
        'speakable' => ['@type' => 'SpeakableSpecification', 'cssSelector' => ['h1', '.lede', '.answer-text']],
    ]);
}
