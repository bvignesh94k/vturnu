<?php
/** Prints every page's banner text as JSON, for bin/build-og.mjs. */
declare(strict_types=1);

$_SERVER['REQUEST_URI'] = '/';
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/security.php';
require __DIR__ . '/../includes/currency.php';
require __DIR__ . '/../includes/lead-guard.php';
require __DIR__ . '/../includes/page-kit.php';
$slug = '';
require __DIR__ . '/../includes/data/menu.php';
require __DIR__ . '/../includes/data/pages.php';

$out = [];
foreach ($PAGES as $s => $p) {
    if ($s === '' || !empty($p['noindex']) || ($p['template'] ?? '') === 'legal') {
        continue;
    }
    $tpl = $p['template'] ?? 'service';
    $row = ['slug' => $s, 'title' => $p['h1'] ?? $s, 'tpl' => $tpl, 'cat' => $p['cat'] ?? '', 'section' => page_section($p, $s)];
    if ($tpl === 'blog-post') {
        $row['file'] = 'blog-' . $p['post'];
        $row['post'] = $p['post'];
        /* A post can name its own banner label; otherwise its category. */
        $row['kicker'] = $BLOG[$p['post']]['kicker'] ?? $BLOG[$p['post']]['category'] ?? 'Insight';
    } else {
        $row['file'] = 'page-' . str_replace('/', '-', $s);
        $row['kicker'] = match ($tpl) {
            'resource' => $RESOURCES[$p['resource']]['type'] === 'ebook' ? 'Free e-book' : 'Complete guide',
            'case-detail' => 'Case study',
            'landing' => 'Enterprise & IT',
            'content-landing' => 'Content services',
            default => '',
        };
        /* These pages keep a short name for breadcrumbs and cards; the banner
           carries the headline a visitor sees. */
        if ($tpl === 'content-landing') {
            $row['title'] = $CONTENT_LANDINGS[$s]['hero'];
        }
    }
    $out[] = $row;
}
echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
