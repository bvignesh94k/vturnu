<?php
/**
 * Page heroes. Every page calls render_hero() with its own words and its own
 * visual, so no two pages open the same way.
 *
 * The visuals are plain HTML and CSS 3D, drawn from the page's own text: a search
 * page shows its real query, an ERP page its real modules. They cost no JavaScript,
 * which keeps the headline as the LCP element. All of them are illustrations of the
 * service, hidden from assistive tech, and carry no result claims.
 */

declare(strict_types=1);

/**
 * @param array{k?:string,h1:string,lede?:string,cta?:array,alt?:array,visual?:string,
 *              tone?:string,layout?:string,extra?:string,class?:string} $h
 */
function render_hero(array $h): void
{
    $tone = $h['tone'] ?? 'paper';
    $layout = $h['layout'] ?? 'split';
    $class = trim('hx hx--' . $tone . ' hx--' . $layout . ' ' . ($h['class'] ?? ''));
    ?>
<section class="<?= e($class) ?>">
    <div class="container hx-grid">
        <div class="hx-copy">
            <?php if (!empty($h['k'])): ?><p class="hx-k"><?= e($h['k']) ?></p><?php endif; ?>
            <h1><?= e($h['h1']) ?></h1>
            <?php if (!empty($h['lede'])): ?><p class="lede"><?= e($h['lede']) ?></p><?php endif; ?>
            <?php if (!empty($h['cta']) || !empty($h['alt'])): ?>
            <div class="hx-actions">
                <?php if (!empty($h['cta'])): ?><a class="hx-cta" href="<?= e($h['cta'][1]) ?>"><?= e($h['cta'][0]) ?> <span aria-hidden="true">&rarr;</span></a><?php endif; ?>
                <?php if (!empty($h['alt'])): ?><a class="hx-link" href="<?= e($h['alt'][1]) ?>"><?= e($h['alt'][0]) ?></a><?php endif; ?>
            </div>
            <?php endif; ?>
            <?= $h['extra'] ?? '' ?>
        </div>
        <?php if (!empty($h['visual'])): ?>
        <div class="hx-stage tilt" aria-hidden="true"><div class="hx-scene"><?= $h['visual'] ?></div></div>
        <?php endif; ?>
    </div>
</section>
    <?php
}

/** Hero tone by part of the business. */
function hero_tone(array $page, string $slug): string
{
    return [
        'marketing' => 'paper', 'ai' => 'lilac', 'web' => 'mint', 'enterprise' => 'stone',
        'cases' => 'peach', 'blog' => 'paper', 'resources' => 'lilac',
    ][page_section($page, $slug)] ?? 'paper';
}

/** Four compositions, walked through page by page so neighbours always differ. */
function hero_layout(string $slug): string
{
    $all = ['split', 'flip', 'stack', 'tight'];
    return $all[abs(crc32($slug)) % 4];
}

/** One line of text in a visual. */
function hx_t(string $s, string $class = ''): string
{
    return '<span' . ($class !== '' ? ' class="' . e($class) . '"' : '') . '>' . e($s) . '</span>';
}

/** Grey placeholder lines, for text that does not need to be read. */
function hx_lines(array $widths): string
{
    $out = '<span class="hx-lines">';
    foreach ($widths as $w) {
        $out .= '<i style="width:' . (int) $w . '%"></i>';
    }
    return $out . '</span>';
}

/** A small label floating in front of the main panel. */
function hx_tag(string $text, string $class = ''): string
{
    return '<span class="hx-tag ' . e($class) . '"><i></i>' . e($text) . '</span>';
}

/** Browser or app window chrome around a body of HTML. */
function hx_win(string $bar, string $body, string $class = ''): string
{
    return '<div class="hx-win ' . e($class) . '"><div class="hx-bar"><i></i><i></i><i></i>' . hx_t($bar) . '</div><div class="hx-body">' . $body . '</div></div>';
}

/** The hero visual named by a page's data: [type, ...args]. */
function hero_visual(array $v): string
{
    $type = (string) array_shift($v);
    $a = array_values($v);
    return match ($type) {
        'serp' => hx_serp((string) $a[0], (string) ($a[1] ?? 'yourcompany.com'), (string) ($a[2] ?? 'Your company')),
        'answer' => hx_answer((string) $a[0], (string) $a[1], (string) $a[2], (string) ($a[3] ?? 'yourcompany.com')),
        'ad' => hx_ad((string) $a[0], (string) $a[1], (string) $a[2]),
        'social' => hx_social((string) $a[0], (string) $a[1], (string) ($a[2] ?? 'New enquiry')),
        'browser' => hx_browser((string) $a[0], (string) $a[1], (array) ($a[2] ?? ['Fast', 'Findable', 'Converting'])),
        'store' => hx_store((string) $a[0], (array) $a[1], (string) ($a[2] ?? 'Order placed')),
        'inbox' => hx_inbox((string) $a[0], (array) $a[1]),
        'map' => hx_map((string) $a[0], (string) ($a[1] ?? 'Your business')),
        'doc' => hx_doc((string) $a[0], (array) $a[1], (string) ($a[2] ?? 'Answers the buyer')),
        'brand' => hx_brand((string) $a[0]),
        'lang' => hx_lang((string) $a[0], (string) $a[1], (string) $a[2], (string) $a[3]),
        'reviews' => hx_reviews((string) $a[0], (array) $a[1]),
        'pipeline' => hx_pipeline((array) $a[0]),
        'quote' => hx_quote((string) $a[0], (array) $a[1], (string) $a[2]),
        'modules' => hx_modules((string) $a[0], (array) $a[1]),
        'talent' => hx_talent((array) $a[0]),
        'code' => hx_code((string) $a[0], (array) $a[1]),
        'chart' => hx_chart((string) $a[0], (array) $a[1]),
        'metrics' => hx_metrics((array) $a[0], (string) ($a[1] ?? '')),
        'book' => hx_book((string) $a[0], (string) $a[1]),
        'covers' => hx_covers((array) $a[0]),
        'reply' => hx_reply((string) $a[0]),
        'tree' => hx_tree((array) $a[0]),
        'gauge' => hx_gauge((array) $a[0]),
        'steps' => hx_steps((array) $a[0]),
        'logos' => hx_logos((array) $a[0]),
        'routes' => hx_routes((array) $a[0]),
        default => '',
    };
}

function hx_serp(string $query, string $site, string $name): string
{
    $body = '<div class="hx-query">' . hx_t($query) . '</div>'
        . '<div class="hx-res is-you"><b class="hx-rank">1</b><span>' . hx_t($site, 'hx-url') . hx_t($name . ': ' . $query, 'hx-ttl') . hx_lines([92, 70]) . '</span></div>'
        . '<div class="hx-res"><b class="hx-rank">2</b>' . hx_lines([46, 84, 60]) . '</div>'
        . '<div class="hx-res"><b class="hx-rank">3</b>' . hx_lines([38, 76, 66]) . '</div>';
    return hx_win('Search', $body) . hx_tag('New enquiry', 'hx-tag--a') . hx_tag('Buying intent', 'hx-tag--b');
}

function hx_answer(string $engine, string $prompt, string $reply, string $site): string
{
    $body = '<p class="hx-you">' . e($prompt) . '</p>'
        . '<p class="hx-bot">' . e($reply) . ' <b class="hx-cite">' . e($site) . '</b></p>' . hx_lines([88, 64]);
    return hx_win($engine, $body, 'hx-chat') . hx_tag('Your brand is named', 'hx-tag--a') . hx_tag('Cited as a source', 'hx-tag--b');
}

function hx_ad(string $keyword, string $head, string $desc): string
{
    $body = '<div class="hx-query">' . hx_t($keyword) . '</div>'
        . '<div class="hx-adcard"><small>Sponsored</small>' . hx_t($head, 'hx-ttl') . '<p>' . e($desc) . '</p></div>'
        . '<div class="hx-meter"><span>Wasted spend</span><i class="is-down"></i><span>Qualified leads</span><i class="is-up"></i></div>';
    return hx_win('Campaign', $body) . hx_tag('Conversion tracked', 'hx-tag--a') . hx_tag('Search term blocked', 'hx-tag--b');
}

function hx_social(string $handle, string $post, string $tag): string
{
    return '<div class="hx-phone"><div class="hx-ph-top">' . hx_t($handle) . '</div><div class="hx-ph-img"></div><p>' . e($post) . '</p>' . hx_lines([80, 56]) . '</div>'
        . hx_tag($tag, 'hx-tag--a') . hx_tag('Saved and shared', 'hx-tag--b');
}

function hx_browser(string $url, string $head, array $chips): string
{
    $body = '<div class="hx-site"><b>' . e($head) . '</b>' . hx_lines([70, 52]) . '<em></em></div><div class="hx-tiles"><i></i><i></i><i></i></div>';
    $tags = '';
    foreach (array_slice($chips, 0, 2) as $i => $c) {
        $tags .= hx_tag((string) $c, $i ? 'hx-tag--b' : 'hx-tag--a');
    }
    return hx_win($url, $body) . $tags;
}

function hx_store(string $name, array $items, string $tag): string
{
    $body = '<div class="hx-shop">';
    foreach (array_slice($items, 0, 4) as $i => $it) {
        $body .= '<div><i class="hx-g' . ($i % 4) . '"></i>' . hx_t((string) $it) . '</div>';
    }
    return hx_win($name, $body . '</div>') . hx_tag($tag, 'hx-tag--a') . hx_tag('Ranks for the category', 'hx-tag--b');
}

function hx_inbox(string $title, array $rows): string
{
    $body = '';
    foreach (array_slice($rows, 0, 4) as [$subject, $state]) {
        $body .= '<div class="hx-mail"><b>' . e((string) $subject) . '</b><small>' . e((string) $state) . '</small></div>';
    }
    return hx_win($title, $body) . hx_tag('Meeting booked', 'hx-tag--a');
}

function hx_map(string $query, string $name): string
{
    $body = '<div class="hx-query">' . hx_t($query) . '</div><div class="hx-mapbox"><i class="p1"></i><i class="p2"></i><i class="p3"></i></div>'
        . '<div class="hx-res is-you"><b class="hx-rank">A</b><span>' . hx_t($name, 'hx-ttl') . hx_t('★★★★★  Open now', 'hx-url') . '</span></div>';
    return hx_win('Maps', $body) . hx_tag('Directions requested', 'hx-tag--a');
}

function hx_doc(string $title, array $lines, string $tag): string
{
    $body = '<b class="hx-dt">' . e($title) . '</b>';
    foreach (array_slice($lines, 0, 3) as $i => $l) {
        $body .= '<p class="hx-dl' . ($i === 1 ? ' is-mark' : '') . '">' . e((string) $l) . '</p>';
    }
    return hx_win('Draft', $body . hx_lines([86, 58])) . hx_tag($tag, 'hx-tag--a');
}

function hx_brand(string $name): string
{
    return '<div class="hx-board"><b class="hx-aa">Aa</b><span class="hx-sw"><i></i><i></i><i></i><i></i></span><span class="hx-mark">' . e($name) . '</span></div>'
        . '<div class="hx-bizcard"><b>' . e($name) . '</b>' . hx_lines([70, 44]) . '</div>' . hx_tag('One recognisable brand', 'hx-tag--a');
}

function hx_lang(string $a, string $at, string $b, string $bt): string
{
    return '<div class="hx-win hx-tr1"><div class="hx-body"><small>' . e($a) . '</small><p>' . e($at) . '</p></div></div>'
        . '<div class="hx-win hx-tr2"><div class="hx-body"><small>' . e($b) . '</small><p lang="und">' . e($bt) . '</p></div></div>'
        . hx_tag('Reviewed by a native speaker', 'hx-tag--a');
}

function hx_reviews(string $name, array $rows): string
{
    $body = '<b class="hx-dt">' . e($name) . '</b>';
    foreach (array_slice($rows, 0, 3) as $r) {
        $body .= '<div class="hx-rev"><span class="hx-stars">★★★★★</span><p>' . e((string) $r) . '</p></div>';
    }
    return hx_win('Reviews', $body) . hx_tag('Replied within a day', 'hx-tag--a');
}

function hx_pipeline(array $cols): string
{
    $out = '<div class="hx-kan">';
    foreach ($cols as $i => [$h, $n]) {
        $out .= '<div><small>' . e((string) $h) . '</small>';
        for ($k = 0; $k < (int) $n; $k++) {
            $out .= '<i class="' . ($i === count($cols) - 1 ? 'is-won' : '') . '"></i>';
        }
        $out .= '</div>';
    }
    return $out . '</div>' . hx_tag('Qualified lead', 'hx-tag--a');
}

function hx_quote(string $title, array $rows, string $tag): string
{
    $body = '';
    foreach (array_slice($rows, 0, 4) as [$l, $s]) {
        $body .= '<div class="hx-mail"><b>' . e((string) $l) . '</b><small>' . e((string) $s) . '</small></div>';
    }
    return hx_win($title, $body) . hx_tag($tag, 'hx-tag--a');
}

function hx_modules(string $title, array $mods): string
{
    $body = '<div class="hx-mods">';
    foreach (array_slice($mods, 0, 9) as $i => $m) {
        $body .= '<span class="hx-g' . ($i % 4) . '">' . e((string) $m) . '</span>';
    }
    return hx_win($title, $body . '</div>') . hx_tag('One shared record', 'hx-tag--a');
}

function hx_talent(array $people): string
{
    $out = '<div class="hx-people">';
    foreach (array_slice($people, 0, 3) as $i => [$role, $skill]) {
        $out .= '<div class="hx-person hx-p' . $i . '"><i class="hx-g' . $i . '"></i><b>' . e((string) $role) . '</b><small>' . e((string) $skill) . '</small></div>';
    }
    return $out . '</div>' . hx_tag('Screened and interviewed', 'hx-tag--a');
}

function hx_code(string $title, array $lines): string
{
    $body = '<pre class="hx-pre">';
    foreach (array_slice($lines, 0, 6) as $l) {
        $body .= e((string) $l) . "\n";
    }
    return hx_win($title, $body . '</pre>', 'hx-dark') . hx_tag('Shipped and tested', 'hx-tag--a');
}

function hx_chart(string $title, array $rows): string
{
    $body = '<b class="hx-dt">' . e($title) . '</b>';
    foreach (array_slice($rows, 0, 4) as $i => [$l, $w]) {
        $body .= '<div class="hx-barrow">' . hx_t((string) $l) . '<i class="hx-g' . ($i % 4) . '" style="width:' . (int) $w . '%"></i></div>';
    }
    return hx_win('Report', $body) . hx_tag('One version of the truth', 'hx-tag--a');
}

/** Real figures only: callers pass measured results with their labels. */
function hx_metrics(array $rows, string $note): string
{
    $out = '<div class="hx-wall">';
    foreach (array_slice($rows, 0, 4) as $i => [$n, $l]) {
        $out .= '<div class="hx-m' . $i . '"><b>' . e((string) $n) . '</b><small>' . e((string) $l) . '</small></div>';
    }
    return $out . '</div>' . ($note !== '' ? hx_tag($note, 'hx-tag--a') : '');
}

function hx_book(string $title, string $label): string
{
    return '<div class="hx-book"><div class="hx-spine"></div><div class="hx-cover"><small>' . e($label) . '</small><b>' . e($title) . '</b><em>VTurnU</em></div><div class="hx-pages"></div></div>';
}

function hx_covers(array $imgs): string
{
    $out = '<div class="hx-covers">';
    foreach (array_slice($imgs, 0, 3) as $i => $src) {
        $out .= '<img class="hx-c' . $i . '" src="' . e((string) $src) . '" alt="" width="480" height="252" loading="eager" decoding="async">';
    }
    return $out . '</div>';
}

function hx_reply(string $who): string
{
    $body = '<p class="hx-you">Here is what we need help with.</p><p class="hx-bot">' . e($who) . '</p>' . hx_lines([78, 50]);
    return hx_win('Your enquiry', $body, 'hx-chat') . hx_tag('A person reads every enquiry', 'hx-tag--a');
}

function hx_tree(array $groups): string
{
    $out = '<div class="hx-tree"><b>vturnu.com</b><div>';
    foreach (array_slice($groups, 0, 6) as $g) {
        $out .= hx_t((string) $g);
    }
    return $out . '</div></div>';
}

function hx_gauge(array $checks): string
{
    $body = '<div class="hx-dial"><i></i></div>';
    foreach (array_slice($checks, 0, 4) as $c) {
        $body .= '<div class="hx-mail"><b>' . e((string) $c) . '</b><small>Checked</small></div>';
    }
    return hx_win('Live audit', $body);
}

function hx_steps(array $rows): string
{
    $out = '<ol class="hx-path">';
    foreach (array_slice($rows, 0, 5) as $i => $r) {
        $out .= '<li><b>' . ($i + 1) . '</b>' . hx_t((string) $r) . '</li>';
    }
    return $out . '</ol>';
}

function hx_logos(array $files): string
{
    $out = '<div class="hx-logos">';
    foreach (array_slice($files, 0, 6) as $f) {
        $out .= '<span>' . client_logo_img((string) $f, '', true, 120, 46, 2800) . '</span>';
    }
    return $out . '</div>';
}

function hx_routes(array $rows): string
{
    $out = '<div class="hx-routes">';
    foreach (array_slice($rows, 0, 5) as $i => $r) {
        $out .= '<span class="hx-g' . ($i % 4) . '">' . e((string) $r) . '</span>';
    }
    return $out . '</div>';
}
