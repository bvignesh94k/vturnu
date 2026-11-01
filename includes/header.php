<?php /** Shared header + <head>. Expects: $page, $slug, $trail, $canonical, $NAV, $PAGES */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php /* Tawk.to is injected by Google Tag Manager, not by this codebase, so its
         own launcher bubble appeared alongside our Live Chat button. Tawk_API has
         to exist before GTM loads Tawk, because Tawk reads this object on init.
         hideWidget() suppresses only the bubble; Tawk_API.toggle() still opens
         the chat window normally. */ ?>
<script>
window.Tawk_API = window.Tawk_API || {};
(function () {
    var prev = window.Tawk_API.onLoad;
    window.Tawk_API.onLoad = function () {
        if (typeof prev === 'function') { prev(); }
        if (typeof window.Tawk_API.hideWidget === 'function') { window.Tawk_API.hideWidget(); }
    };
})();
</script>
<!-- Google Tag Manager -->
<?php /* Tag Manager carries analytics, ads and chat, and was the heaviest thing on
         every page. It now starts on the first interaction, or after ten
         seconds for a visitor who only reads, so it no longer competes with the
         first paint and first tap (Core Web Vitals). A shorter wait landed inside
         the page-load window and cost more than it gained. Sessions that end
         within ten seconds with no interaction are the only ones not counted. */ ?>
<script>
(function(w,d){
    var done=false,id='GTM-K68BCJ69';
    function go(){
        if(done){return;}done=true;
        w.dataLayer=w.dataLayer||[];w.dataLayer.push({'gtm.start':new Date().getTime(),event:'gtm.js'});
        var f=d.getElementsByTagName('script')[0],j=d.createElement('script');
        j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+id;f.parentNode.insertBefore(j,f);
    }
    ['pointerdown','keydown','scroll','touchstart'].forEach(function(e){w.addEventListener(e,go,{once:true,passive:true});});
    w.addEventListener('load',function(){setTimeout(go,10000);});
})(window,document);
</script>
<!-- End Google Tag Manager -->
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php /* Set before anything paints. Adding these from main.js forced a full style
         recalculation after the page had rendered, about half a second on a phone. */ ?>
<script>
(function (d) {
    d.classList.add('js-nav');
    if ('IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) { d.classList.add('js'); }
})(document.documentElement);
</script>
<title><?= e($page['title']) ?></title>
<meta name="description" content="<?= e($page['meta']) ?>">
<link rel="canonical" href="<?= e($canonical) ?>">
<?php /* Pages can opt out of indexing via 'noindex' in includes/data/pages.php.
         The thank-you page uses it: a confirmation URL has no business in
         search results, and an indexed one gets hit directly by people who
         never submitted anything. */
$noindex = $slug === '__404__' || !empty($page['noindex']); ?>
<meta name="robots" content="<?= $noindex ? 'noindex, follow' : 'index, follow' ?>">
<?php [$og_img, $og_w, $og_h] = page_share_image($page, $slug); $is_article = in_array($page['template'] ?? '', ['blog-post', 'resource', 'case-detail'], true); ?>
<meta property="og:type" content="<?= $is_article ? 'article' : 'website' ?>">
<meta property="og:site_name" content="<?= e(SITE_NAME) ?>">
<meta property="og:title" content="<?= e($page['og_title'] ?? $page['title']) ?>">
<meta property="og:description" content="<?= e($page['og_meta'] ?? $page['meta']) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:image" content="<?= e(abs_url($og_img)) ?>">
<meta property="og:image:secure_url" content="<?= e(abs_url($og_img)) ?>">
<meta property="og:image:type" content="image/jpeg">
<meta property="og:image:width" content="<?= $og_w ?>">
<meta property="og:image:height" content="<?= $og_h ?>">
<meta property="og:image:alt" content="<?= e($page['og_title'] ?? $page['h1'] ?? SITE_NAME) ?>">
<?php if (($page['template'] ?? '') === 'blog-post' && !empty($BLOG[$page['post']]['date'])): $bp = $BLOG[$page['post']]; ?>
<meta property="article:published_time" content="<?= e(date('c', strtotime($bp['date']))) ?>">
<meta property="article:modified_time" content="<?= e(date('c', strtotime($bp['modified'] ?? $bp['date']))) ?>">
<meta property="article:section" content="<?= e($bp['category'] ?? 'Strategy') ?>">
<?php endif; ?>
<meta property="og:locale" content="en_US">
<meta property="og:locale:alternate" content="en_IN">
<meta property="og:locale:alternate" content="en_CA">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($page['og_title'] ?? $page['title']) ?>">
<meta name="twitter:description" content="<?= e($page['og_meta'] ?? $page['meta']) ?>">
<meta name="twitter:image" content="<?= e(abs_url($og_img)) ?>">
<meta name="twitter:image:alt" content="<?= e($page['og_title'] ?? $page['h1'] ?? SITE_NAME) ?>">
<meta name="twitter:site" content="@VTurnU_Digital">
<meta name="geo.region" content="IN-TN">
<meta name="geo.placename" content="Chennai">
<meta name="format-detection" content="telephone=no">
<meta name="theme-color" content="#5B56C9">
<meta name="author" content="<?= e(SITE_NAME) ?>">
<meta name="publisher" content="<?= e(SITE_NAME) ?>">
<?php /* Bing, Yandex and Baidu read these; Google uses the generic robots tag.
         max-image-preview:large is what lets rich results show a full image. */ ?>
<meta name="googlebot" content="<?= $noindex ? 'noindex, follow' : 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1' ?>">
<meta name="bingbot" content="<?= $noindex ? 'noindex, follow' : 'index, follow, max-snippet:-1, max-image-preview:large' ?>">
<meta name="rating" content="general">
<meta name="revisit-after" content="7 days">
<link rel="icon" type="image/svg+xml" href="/assets/img/vturnu-icon-mark.svg">
<link rel="icon" type="image/png" sizes="48x48" href="/assets/img/vturnu-icon-48.png">
<link rel="apple-touch-icon" href="/assets/img/vturnu-icon-180.png">
<link rel="manifest" href="/manifest.webmanifest">
<link rel="alternate" type="application/rss+xml" title="<?= e(SITE_NAME) ?> Blog" href="/feed.xml">
<?php /* The stylesheet is render-blocking by design, so tell the browser to
         start fetching it before the parser reaches the <link>. */ ?>
<link rel="preload" as="style" href="/assets/css/style.css?v=<?= asset_v('/assets/css/style.css') ?>">
<?php /* Fonts are served from this site. Coming from Google they arrived late,
         which reflowed the page (layout shift) and delayed the largest text.
         The two most used files are preloaded, and the fallback is scaled to
         Poppins' metrics so the swap moves nothing. */ ?>
<link rel="preload" as="font" type="font/woff2" href="/assets/fonts/poppins-400.woff2" crossorigin>
<link rel="preload" as="font" type="font/woff2" href="/assets/fonts/poppins-800.woff2" crossorigin>
<?php if ($slug === ''): ?>
<link rel="preload" as="font" type="font/woff2" href="/assets/fonts/figtree.woff2" crossorigin>
<?php endif; ?>
<link rel="preload" as="font" type="font/woff2" href="/assets/fonts/caprasimo.woff2" crossorigin>
<style>
@font-face{font-family:Poppins;font-style:normal;font-weight:400;font-display:swap;src:url(/assets/fonts/poppins-400.woff2) format("woff2")}
@font-face{font-family:Poppins;font-style:normal;font-weight:500;font-display:swap;src:url(/assets/fonts/poppins-500.woff2) format("woff2")}
@font-face{font-family:Poppins;font-style:normal;font-weight:600;font-display:swap;src:url(/assets/fonts/poppins-600.woff2) format("woff2")}
@font-face{font-family:Poppins;font-style:normal;font-weight:700;font-display:swap;src:url(/assets/fonts/poppins-700.woff2) format("woff2")}
@font-face{font-family:Poppins;font-style:normal;font-weight:800;font-display:swap;src:url(/assets/fonts/poppins-800.woff2) format("woff2")}
@font-face{font-family:Caprasimo;font-style:normal;font-weight:400;font-display:swap;src:url(/assets/fonts/caprasimo.woff2) format("woff2")}
<?php if ($slug === ''): ?>
@font-face{font-family:Figtree;font-style:normal;font-weight:300 900;font-display:swap;src:url(/assets/fonts/figtree.woff2) format("woff2")}
<?php endif; ?>
@font-face{font-family:"Poppins Fallback";src:local("Arial"),local("Helvetica"),local("Roboto"),local("Liberation Sans");size-adjust:112.2%;ascent-override:93.6%;descent-override:31.2%;line-gap-override:8.9%}
</style>
<?php /* External + versioned so the browser caches it once instead of re-downloading
         the whole sheet inside every page's HTML. ?v= busts the cache on edit. */
$css_v = asset_v('/assets/css/style.css'); ?>
<link rel="stylesheet" href="/assets/css/style.css?v=<?= $css_v ?>">
<?php /* Homepage-only sheet. Every rule inside is scoped under .hp, so it
         cannot reach any other template. */
if ($slug === ""):
    $home_css_v = asset_v('/assets/css/home.css'); ?>
<link rel="stylesheet" href="/assets/css/home.css?v=<?= $home_css_v ?>">
<?php endif; ?>
<?php if (($page['template'] ?? '') === 'blog-post' && is_file(BASE_PATH . '/assets/img/blog/' . $page['post'] . '.webp')): ?>
<link rel="preload" as="image" type="image/webp" imagesrcset="/assets/img/blog/<?= e($page['post']) ?>-sm.webp 640w, /assets/img/blog/<?= e($page['post']) ?>.webp 1200w" imagesizes="(max-width: 860px) 100vw, 820px">
<?php endif; ?>
<?php /* The homepage emits its own richer Organization graph (ProfessionalService,
         a service catalogue and the FAQ), so the shared one is skipped there
         rather than defining the same @id twice. Every other page keeps it. */
if ($slug !== ''): ?>
<?= jsonld_script(jsonld_site()) . "\n" ?>
<?php endif; ?>
<?php if ($slug !== '__404__' && count($trail) > 1): ?>
<?= jsonld_script(jsonld_breadcrumbs($trail)) . "\n" ?>
<?php endif; ?>
<?php $faq_page = $slug === '__404__' ? [] : page_faqs($page, $slug);
if ($faq_page && $slug !== ''): ?>
<?= jsonld_script(jsonld_faq($faq_page)) . "\n" ?>
<?php endif; ?>
<?php if (($page['template'] ?? 'service') === 'service'): ?>
<?= jsonld_script(jsonld_service($page, $slug)) . "\n" ?>
<?= jsonld_script([
    '@context' => 'https://schema.org',
    '@type' => 'WebPage',
    'name' => $page['title'],
    'url' => $canonical,
    'speakable' => ['@type' => 'SpeakableSpecification', 'cssSelector' => ['h1', '.answer-text']],
]) . "\n" ?>
<?php /* Every other template: case studies, About, Contact, pricing, legal and
         all listing pages. Without this they carried no page-level node at
         all, only Organization/WebSite/BreadcrumbList. */
elseif ($slug !== '__404__' && ($node = jsonld_page($page, $slug, $canonical, $page['template'] ?? 'service')) !== null): ?>
<?= jsonld_script($node) . "\n" ?>
<?php endif; ?>
</head>
<body<?= $slug === "" ? " class=\"hp\"" : "" ?>>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-K68BCJ69"
height="0" width="0" style="display:none;visibility:hidden" title="Tag Manager"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<div class="scroll-progress" aria-hidden="true"><span id="scroll-progress-bar"></span></div>
<a class="skip-link" href="#main">Skip to content</a>


<header class="site-header" id="site-header">
    <div class="container header-inner">
        <a class="brand" href="/" aria-label="<?= e(SITE_NAME) ?>, <?= e(SITE_TAGLINE) ?>, Home">
            <img src="/assets/img/vturnu-icon-mark.svg?v=<?= asset_v('/assets/img/vturnu-icon-mark.svg') ?>" alt="" width="50" height="44" class="brand-mark">
            <span class="brand-lock" aria-hidden="true">
                <span class="brand-word"><span class="bw-v">V</span>Turn<span class="bw-u">U</span></span>
                <span class="brand-tag"><?= e(strtoupper(SITE_TAGLINE)) ?></span>
            </span>
        </a>

        <button class="nav-toggle" id="nav-toggle" aria-expanded="false" aria-controls="primary-nav">
            <span class="nav-toggle-bar"></span><span class="nav-toggle-bar"></span><span class="nav-toggle-bar"></span>
            <span class="sr-only">Menu</span>
        </button>

        <nav class="primary-nav" id="primary-nav" aria-label="Primary">
            <ul class="nav-list">
                <?php foreach ($NAV as $item): ?>
                    <?php $hasMenu = !empty($item['columns']); $hasPromo = !empty($item['promo']); ?>
                    <li class="nav-item<?= $hasMenu ? ' has-mega' : '' ?><?= $item['label'] === 'Enterprise & IT' ? ' nav-enterprise' : '' ?>">
                        <a class="nav-link" href="<?= e($item['url']) ?>"><?= e($item['label']) ?><?php if ($hasMenu): ?><span class="caret" aria-hidden="true"></span><?php endif; ?></a>
                        <?php if ($hasMenu): ?>
                        <button class="submenu-toggle" type="button" aria-expanded="false" aria-label="Open <?= e($item['label']) ?> menu"><span aria-hidden="true">▾</span></button>
                        <div class="mega">
                            <?php if (!empty($item['desc'])): ?>
                            <p class="mega-tagline"><?= e($item['desc']) ?></p>
                            <?php endif; ?>
                            <div class="mega-grid cols-<?= count($item['columns']) + ($hasPromo ? 1 : 0) ?><?= $hasPromo ? ' has-promo' : '' ?>">
                                <?php foreach ($item['columns'] as $col): ?>
                                <div class="mega-col">
                                    <?php if (!empty($col['url'])): ?>
                                        <a class="mega-heading" href="<?= e($col['url']) ?>"><?= e($col['heading']) ?> <span aria-hidden="true">→</span></a>
                                    <?php else: ?>
                                        <span class="mega-heading"><?= e($col['heading']) ?></span>
                                    <?php endif; ?>
                                    <ul>
                                        <?php foreach ($col['items'] as $mi): ?>
                                        <li>
                                            <a href="<?= e($mi[1]) ?>">
                                                <span class="mega-label"><?= e($mi[0]) ?></span>
                                                <?php if (!empty($mi[2])): ?><small class="mega-desc"><?= e($mi[2]) ?></small><?php endif; ?>
                                            </a>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <?php endforeach; ?>
                                <?php if ($hasPromo): [$p_eyebrow, $p_title, $p_text, $p_btn, $p_url] = $item['promo']; ?>
                                <div class="mega-promo">
                                    <p class="eyebrow"><?= e($p_eyebrow) ?></p>
                                    <h3><?= e($p_title) ?></h3>
                                    <p><?= e($p_text) ?></p>
                                    <a class="btn btn-grad" href="<?= e($p_url) ?>"><?= e($p_btn) ?></a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <a class="btn btn-primary nav-cta" href="/contact-us/">Get a Proposal</a>
        </nav>
    </div>
</header>

<main id="main">
<?php if ($slug !== '' && $slug !== '__404__' && count($trail) > 1): ?>
<nav class="breadcrumbs" aria-label="Breadcrumb">
    <div class="container">
        <ol>
            <?php foreach ($trail as $i => [$label, $url]): ?>
                <?php if ($i === count($trail) - 1): ?>
                    <li aria-current="page"><?= e($label) ?></li>
                <?php else: ?>
                    <li><a href="<?= e($url) ?>"><?= e($label) ?></a></li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ol>
    </div>
</nav>
<?php endif; ?>
