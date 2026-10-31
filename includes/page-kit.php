<?php
/**
 * Page-level building blocks shared by every template: the share image, the
 * 3D scene choice and the two-column FAQ block.
 */

declare(strict_types=1);

/**
 * Social share image for a page: [path, width, height].
 *
 * Images are built by bin/build-og.mjs into assets/img/og/ at 1200x630, the
 * size LinkedIn, Facebook, X and WhatsApp all render as a full preview. A page
 * with no image of its own uses its section's card, then the site card.
 */
function page_share_image(array $page, string $slug): array
{
    $name = str_replace('/', '-', trim($slug, '/'));
    $tpl = $page['template'] ?? 'service';
    $candidates = [];
    if ($slug === '') {
        $candidates[] = 'site';
    } elseif ($tpl === 'blog-post' && !empty($page['post'])) {
        $candidates[] = 'blog-' . $page['post'];
    } else {
        $candidates[] = 'page-' . $name;
    }
    $candidates[] = 'section-' . page_section($page, $slug);
    $candidates[] = 'site';
    foreach ($candidates as $c) {
        $path = '/assets/img/og/' . $c . '.jpg';
        if (is_file(BASE_PATH . $path)) {
            return [$path, 1200, 630];
        }
    }
    return ['/assets/img/vturnu-logo-dark.png', 1584, 392];
}

/** Which part of the business a page belongs to. */
function page_section(array $page, string $slug): string
{
    $tpl = $page['template'] ?? 'service';
    $cat = $page['cat'] ?? '';
    if ($tpl === 'landing' || $slug === 'enterprise-it-services') {
        return 'enterprise';
    }
    if ($tpl === 'resource' || $tpl === 'resource-list') {
        return 'resources';
    }
    if ($tpl === 'blog-list' || $tpl === 'blog-post') {
        return 'blog';
    }
    if ($tpl === 'case-detail' || $tpl === 'case-list' || $slug === 'case-studies') {
        return 'cases';
    }
    if ($cat === 'ai' || $slug === 'ai') {
        return 'ai';
    }
    if ($cat === 'web' || $slug === 'web-services') {
        return 'web';
    }
    return 'marketing';
}

/** The inner-page hero scene, read by assets/js/scene3d.js from <body data-scene>. */
function page_scene(array $page, string $slug): string
{
    if ($slug === '') {
        return '';   // The homepage has its own WebGL stage.
    }
    if (!empty($page['scene'])) {
        return (string) $page['scene'];
    }
    $tpl = $page['template'] ?? 'service';
    return match (true) {
        $tpl === 'landing' || $slug === 'enterprise-it-services' => 'orbit',
        $tpl === 'pricing' => 'rings',
        $tpl === 'resource' || $tpl === 'resource-list' => 'book',
        $tpl === 'case-detail' || $tpl === 'case-list' || $slug === 'case-studies' => 'bars',
        $tpl === 'blog-list' || $tpl === 'blog-post' => 'nodes',
        $tpl === 'contact' || $tpl === 'about' || $tpl === 'sitemap' => 'globe',
        default => match ($page['cat'] ?? '') {
            'ai' => 'globe',
            'ppc' => 'funnel',
            'web' => 'cube',
            'social' => 'nodes',
            'content' => 'nodes',
            'consulting' => 'rings',
            default => 'bars',
        },
    };
}

/**
 * The FAQs a page carries, at most eight. header.php emits them as FAQPage
 * JSON-LD, so a template must render the same list with render_faqs().
 *
 * @return array<int,array{0:string,1:string}>
 */
function page_faqs(array $page, string $slug): array
{
    $tpl = $page['template'] ?? 'service';
    $faqs = [];
    if (!empty($page['faqs'])) {
        $faqs = $page['faqs'];
    } elseif ($tpl === 'service') {
        $faqs = service_faqs($page);
    } elseif ($tpl === 'blog-post') {
        $faqs = $GLOBALS['BLOG'][$page['post']]['faqs'] ?? [];
    } elseif ($tpl === 'resource') {
        $faqs = $GLOBALS['RESOURCES'][$page['resource']]['faqs'] ?? [];
    } elseif ($tpl === 'case-detail') {
        $faqs = $GLOBALS['CASES'][$page['case']]['faqs'] ?? [];
    } elseif ($tpl === 'landing') {
        $faqs = $GLOBALS['LANDINGS'][$slug]['faqs'] ?? [];
    }
    return array_slice($faqs, 0, 8);
}

/** Two independent columns, so opening an answer never stretches its neighbour. */
function render_faqs(array $faqs, string $title = 'Frequently asked questions', string $eyebrow = 'Straight answers', string $class = ''): void
{
    if (!$faqs) {
        return;
    }
    $faqs = array_slice($faqs, 0, 8);
    $half = (int) ceil(count($faqs) / 2);
    $cols = [array_slice($faqs, 0, $half, true), array_slice($faqs, $half, null, true)];
    ?>
<section class="section faq-sec <?= e($class) ?>" aria-labelledby="faq-title">
    <div class="container">
        <div class="faq-head" data-reveal="head">
            <p class="eyebrow"><?= e($eyebrow) ?></p>
            <h2 class="section-title" id="faq-title"><?= e($title) ?></h2>
        </div>
        <div class="faq-cols">
            <?php foreach ($cols as $c => $col): if (!$col) continue; ?>
            <div class="faq-col">
                <?php foreach ($col as $i => [$q, $a]): ?>
                <details class="faq2"<?= $i === 0 ? ' open' : '' ?> data-reveal="card">
                    <summary><span class="faq2-n" aria-hidden="true"><?= sprintf('%02d', $i + 1) ?></span><span class="faq2-q"><?= e($q) ?></span><span class="faq2-x" aria-hidden="true"></span></summary>
                    <p class="answer-text"><?= e($a) ?></p>
                </details>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
    <?php
}

/** External link. Only client sites we built or grew are followed; see render callers. */
function ext_link(string $url, string $label, bool $follow = false, string $class = ''): string
{
    $rel = $follow ? 'noopener' : 'nofollow noopener';
    return '<a href="' . e($url) . '" target="_blank" rel="' . $rel . '"' . ($class !== '' ? ' class="' . e($class) . '"' : '') . '>' . e($label) . '</a>';
}

/** Full text of an e-book or guide, from includes/data/ebooks/<slug>.php. */
function resource_content(string $slug): array
{
    $file = BASE_PATH . '/includes/data/ebooks/' . basename($slug) . '.php';
    return is_file($file) ? (array) require $file : [];
}

/**
 * The light markup used by the e-book and guide files, as page HTML.
 * Supports ## and ### headings, lists, > quotes, pipe tables, !NOTE callouts,
 * !STAT figures, and **bold**, *italic* and `code`.
 */
function reader_html(string $raw): string
{
    $inline = static function (string $t): string {
        $t = e($t);
        $t = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $t);
        $t = preg_replace('/(?<!\*)\*(?!\s)(.+?)(?<!\s)\*(?!\*)/s', '<em>$1</em>', $t);
        return preg_replace('/`(.+?)`/s', '<code>$1</code>', $t);
    };
    $out = [];
    $para = [];
    $list = null;
    $table = false;
    $flush = static function () use (&$para, &$out, $inline): void {
        if ($para) {
            $out[] = '<p>' . $inline(implode(' ', $para)) . '</p>';
            $para = [];
        }
    };
    $close = static function () use (&$list, &$table, &$out): void {
        if ($list) {
            $out[] = '</' . $list . '>';
            $list = null;
        }
        if ($table) {
            $out[] = '</tbody></table></div>';
            $table = false;
        }
    };
    foreach (preg_split('/\r\n|\r|\n/', $raw) as $line) {
        $t = trim($line);
        if ($t === '') {
            $flush();
            $close();
            continue;
        }
        if (preg_match('/^(#{2,3})\s+(.*)$/', $t, $m)) {
            $flush();
            $close();
            // One level below the chapter heading, which is already an h2.
            $level = strlen($m[1]) + 1;
            $out[] = '<h' . $level . '>' . $inline($m[2]) . '</h' . $level . '>';
        } elseif (str_starts_with($t, '!STAT ')) {
            $flush();
            $close();
            $parts = array_map('trim', explode('|', substr($t, 6), 2));
            $out[] = '<div class="reader-stat tilt"><span class="stat-v">' . $inline($parts[0]) . '</span><span class="stat-l">' . $inline($parts[1] ?? '') . '</span></div>';
        } elseif (str_starts_with($t, '!NOTE ')) {
            $flush();
            $close();
            $out[] = '<div class="reader-note">' . $inline(substr($t, 6)) . '</div>';
        } elseif (str_starts_with($t, '> ')) {
            $flush();
            $close();
            $out[] = '<blockquote><p>' . $inline(substr($t, 2)) . '</p></blockquote>';
        } elseif (str_starts_with($t, '|') && substr_count($t, '|') >= 2) {
            $flush();
            if ($list) {
                $close();
            }
            if (preg_match('/^[\s\-|:]+$/', $t)) {
                continue;
            }
            $cells = array_map('trim', explode('|', trim($t, '|')));
            if (!$table) {
                $out[] = '<div class="post-table-wrap"><table class="post-table"><thead><tr>';
                foreach ($cells as $c) {
                    $out[] = '<th scope="col">' . $inline($c) . '</th>';
                }
                $out[] = '</tr></thead><tbody>';
                $table = true;
            } else {
                $out[] = '<tr>';
                foreach ($cells as $c) {
                    $out[] = '<td>' . $inline($c) . '</td>';
                }
                $out[] = '</tr>';
            }
        } elseif (preg_match('/^(-|\d+\.)\s+(.*)$/', $t, $m)) {
            $flush();
            $want = $m[1] === '-' ? 'ul' : 'ol';
            if ($list !== $want || $table) {
                $close();
                $out[] = '<' . $want . '>';
                $list = $want;
            }
            $out[] = '<li>' . $inline($m[2]) . '</li>';
        } else {
            $close();
            $para[] = $t;
        }
    }
    $flush();
    $close();
    return implode("\n", $out);
}

/** The four named client projects, with a followed link to each live site. */
function render_work_showcase(string $title = 'Websites we built and grew', string $intro = 'Live client sites you can visit today, with the measured result of the work.'): void
{
    $real = array_filter($GLOBALS['CASES'] ?? [], static fn($c) => !empty($c['real']));
    if (!$real) {
        return;
    }
    $scope = [
        'sai-impression-label-manufacturer' => 'Website build, SEO, branding',
        'firstchoice-used-auto-parts' => '99 pages rebuilt, SEO, AI visibility',
        'floweazy-odoo-partner-website' => '101-page build, speed, AEO',
        'cafy-careers-uk-job-platform' => 'SEO and performance engineering',
    ];
    ?>
<section class="section work-sec" id="work" aria-labelledby="work-h">
    <div class="container">
        <div class="center-text" data-reveal="head">
            <p class="eyebrow">Our work</p>
            <h2 class="section-title" id="work-h"><?= e($title) ?></h2>
            <p class="pricing-intro center"><?= e($intro) ?></p>
        </div>
        <div class="work-grid">
            <?php foreach ($real as $cslug => $c): ?>
            <article class="client-card" data-reveal="card">
                <span class="client-browser" aria-hidden="true"><i></i><i></i><i></i><b><?= e($c['site_label']) ?></b></span>
                <h3><?= e($c['client']) ?></h3>
                <p class="client-scope"><?= e($scope[$cslug] ?? '') ?></p>
                <p class="client-metric"><strong><?= e($c['results'][0][0]) ?></strong> <span><?= e($c['results'][0][1]) ?></span></p>
                <div class="client-links">
                    <?= ext_link($c['site'], 'Visit ' . $c['site_label'], true, 'case-site') ?>
                    <a href="/case-studies/<?= e($cslug) ?>/">Case study →</a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
    <?php
}
