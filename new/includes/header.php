<?php /** Shared header + <head>. Expects: $page, $slug, $trail, $canonical, $NAV, $PAGES */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
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
<meta property="og:title" content="<?= e($page['title']) ?>">
<meta property="og:description" content="<?= e($page['meta']) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:image" content="<?= e(abs_url('/assets/img/vturnu-mark.jpg')) ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="<?= e(SITE_NAME) ?>, <?= e(SITE_TAGLINE) ?>">
<meta property="og:locale" content="en_US">
<meta property="og:locale:alternate" content="en_IN">
<meta property="og:locale:alternate" content="en_CA">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($page['title']) ?>">
<meta name="twitter:description" content="<?= e($page['meta']) ?>">
<meta name="twitter:image" content="<?= e(abs_url('/assets/img/vturnu-mark.jpg')) ?>">
<meta name="geo.region" content="IN-TN">
<meta name="geo.placename" content="Chennai">
<meta name="format-detection" content="telephone=no">
<meta name="theme-color" content="#F7B500">
<link rel="icon" type="image/png" href="/assets/img/favicon.png">
<link rel="apple-touch-icon" href="/assets/img/favicon.png">
<link rel="manifest" href="/manifest.webmanifest">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@700;800&family=Manrope:wght@400;600;700;800&display=swap" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@700;800&family=Manrope:wght@400;600;700;800&display=swap"></noscript>
<?php if ($slug === ''): /* Caprasimo + Figtree are only used by the home hero */ ?>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Caprasimo&family=Figtree:wght@400;500;600;700&display=swap" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Caprasimo&family=Figtree:wght@400;500;600;700&display=swap"></noscript>
<?php endif; ?>
<?php /* External + versioned so the browser caches it once instead of re-downloading
         the whole sheet inside every page's HTML. ?v= busts the cache on edit. */
$css_v = @filemtime(BASE_PATH . '/assets/css/style.css') ?: time(); ?>
<link rel="stylesheet" href="/assets/css/style.css?v=<?= $css_v ?>">
<?= jsonld_script(jsonld_site()) . "\n" ?>
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
<?php endif; ?>
<!--
    reCAPTCHA v3. Replace YOUR_RECAPTCHA_SITE_KEY below with the site key
    from https://www.google.com/recaptcha/admin, set the matching values
    in includes/config.php (RECAPTCHA_SITE_KEY) and assets/js/main.js
    (RECAPTCHA_SITE_KEY at the top of the IIFE), and RECAPTCHA_SECRET_KEY
    in config.php, then remove this comment wrapper so the script is live.

<script src="https://www.google.com/recaptcha/api.js?render=YOUR_RECAPTCHA_SITE_KEY" async defer></script>
-->
</head>
<body>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-K68BCJ69"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<div class="scroll-progress" aria-hidden="true"><span id="scroll-progress-bar"></span></div>
<a class="skip-link" href="#main">Skip to content</a>


<header class="site-header" id="site-header">
    <div class="container header-inner">
        <a class="brand" href="/" aria-label="<?= e(SITE_NAME) ?>, Home">
            <img src="/assets/img/vturnu-logo-transparent.png" alt="<?= e(SITE_NAME) ?>, <?= e(SITE_TAGLINE) ?>" width="56" height="56" fetchpriority="high">
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
