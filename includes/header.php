<?php /** Shared header + <head>. Expects: $page, $slug, $trail, $canonical, $NAV, $PAGES */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php /* Tawk.to is injected by Google Tag Manager, not by this codebase (the
         embed in footer.php is commented out and still holds placeholder IDs),
         so its own green launcher bubble appeared alongside our black Live Chat
         button: two chat launchers stacked in the same corner.

         Tawk_API has to exist before GTM loads Tawk, because Tawk reads this
         object on init. hideWidget() suppresses only the launcher bubble; the
         black button's Tawk_API.toggle() still opens the chat window normally. */ ?>
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
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-K68BCJ69');</script>
<!-- End Google Tag Manager -->
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($page['title']) ?></title>
<meta name="description" content="<?= e($page['meta']) ?>">
<link rel="canonical" href="<?= e($canonical) ?>">
<meta name="robots" content="<?= $slug === '__404__' ? 'noindex, follow' : 'index, follow' ?>">
<meta property="og:type" content="<?= ($page['template'] ?? '') === 'blog-post' ? 'article' : 'website' ?>">
<meta property="og:site_name" content="<?= e(SITE_NAME) ?>">
<meta property="og:title" content="<?= e($page['og_title'] ?? $page['title']) ?>">
<meta property="og:description" content="<?= e($page['og_meta'] ?? $page['meta']) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:image" content="<?= e(abs_url('/assets/img/vturnu-logo-dark.png')) ?>">
<meta property="og:image:width" content="1584">
<meta property="og:image:height" content="392">
<meta property="og:image:alt" content="<?= e(SITE_NAME) ?>, <?= e(SITE_TAGLINE) ?>">
<meta property="og:locale" content="en_US">
<meta property="og:locale:alternate" content="en_IN">
<meta property="og:locale:alternate" content="en_CA">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($page['og_title'] ?? $page['title']) ?>">
<meta name="twitter:description" content="<?= e($page['og_meta'] ?? $page['meta']) ?>">
<meta name="twitter:image" content="<?= e(abs_url('/assets/img/vturnu-logo-dark.png')) ?>">
<meta name="geo.region" content="IN-TN">
<meta name="geo.placename" content="Chennai">
<meta name="format-detection" content="telephone=no">
<meta name="theme-color" content="#5B56C9">
<meta name="author" content="<?= e(SITE_NAME) ?>">
<meta name="publisher" content="<?= e(SITE_NAME) ?>">
<?php /* Bing, Yandex and Baidu read these; Google uses the generic robots tag.
         max-image-preview:large is what lets rich results show a full image. */ ?>
<meta name="googlebot" content="<?= $slug === '__404__' ? 'noindex, follow' : 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1' ?>">
<meta name="bingbot" content="<?= $slug === '__404__' ? 'noindex, follow' : 'index, follow, max-snippet:-1, max-image-preview:large' ?>">
<meta name="rating" content="general">
<meta name="revisit-after" content="7 days">
<link rel="icon" type="image/svg+xml" href="/assets/img/vturnu-icon-mark.svg">
<link rel="icon" type="image/png" href="/assets/img/vturnu-icon-mark.png">
<link rel="apple-touch-icon" href="/assets/img/vturnu-icon-mark.png">
<link rel="manifest" href="/manifest.webmanifest">
<link rel="alternate" type="application/rss+xml" title="<?= e(SITE_NAME) ?> Blog" href="/feed.xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<?php /* The stylesheet is render-blocking by design, so tell the browser to
         start fetching it before the parser reaches the <link>. */ ?>
<link rel="preload" as="style" href="/assets/css/style.css?v=<?= @filemtime(BASE_PATH . '/assets/css/style.css') ?: time() ?>">
<?php /* One family for the whole site (Poppins) plus Caprasimo for the logo
         wordmark. Loaded in a single request; display=swap so text paints
         immediately in the fallback rather than blocking first render. */ ?>
<?php /* The homepage adds Figtree, its body face, to the same request. */
$font_url = "https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800"
    . ($slug === "" ? "&family=Figtree:wght@400;500;600;700" : "")
    . "&family=Caprasimo&display=swap"; ?>
<link rel="stylesheet" href="<?= e($font_url) ?>" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="<?= e($font_url) ?>"></noscript>
<?php /* External + versioned so the browser caches it once instead of re-downloading
         the whole sheet inside every page's HTML. ?v= busts the cache on edit. */
$css_v = @filemtime(BASE_PATH . '/assets/css/style.css') ?: time(); ?>
<link rel="stylesheet" href="/assets/css/style.css?v=<?= $css_v ?>">
<?php /* Homepage-only sheet. Every rule inside is scoped under .hp, so it
         cannot reach any other template. */
if ($slug === ""):
    $home_css_v = @filemtime(BASE_PATH . "/assets/css/home.css") ?: time(); ?>
<link rel="stylesheet" href="/assets/css/home.css?v=<?= $home_css_v ?>">
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
<?php if (($page['template'] ?? 'service') === 'service'): ?>
<?= jsonld_script(jsonld_service($page, $slug)) . "\n" ?>
<?= jsonld_script(jsonld_faq(service_faqs($page))) . "\n" ?>
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
<script src="https://www.google.com/recaptcha/api.js?render=6LfgqIMtAAAAAOM2_Z4QgkIqg6JPWG3sJ9QpWhhg" async defer></script>
</head>
<body<?= $slug === "" ? " class=\"hp\"" : "" ?>>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-K68BCJ69"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<div class="scroll-progress" aria-hidden="true"><span id="scroll-progress-bar"></span></div>
<a class="skip-link" href="#main">Skip to content</a>


<header class="site-header" id="site-header">
    <div class="container header-inner">
        <a class="brand" href="/" aria-label="<?= e(SITE_NAME) ?>, <?= e(SITE_TAGLINE) ?>, Home">
            <img src="/assets/img/vturnu-icon-mark.svg" alt="" width="44" height="42" class="brand-mark" fetchpriority="high">
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
                    <li class="nav-item<?= $hasMenu ? ' has-mega' : '' ?>">
                        <a class="nav-link" href="<?= e($item['url']) ?>"><?= e($item['label']) ?><?php if ($hasMenu): ?><span class="caret" aria-hidden="true"></span><?php endif; ?></a>
                        <?php if ($hasMenu): ?>
                        <button class="submenu-toggle" aria-expanded="false" aria-label="Open <?= e($item['label']) ?> menu">▾</button>
                        <div class="mega" role="menu">
                            <?php if (!empty($item['desc'])): ?>
                            <p class="mega-tagline"><?= e($item['desc']) ?></p>
                            <?php endif; ?>
                            <div class="mega-grid cols-<?= count($item['columns']) + ($hasPromo ? 1 : 0) ?>">
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
