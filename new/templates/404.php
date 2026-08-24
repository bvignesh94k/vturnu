<?php /** 404 page. Expects: $page */ ?>
<section class="page-hero">
    <div class="container center-text">
        <p class="eyebrow">404</p>
        <h1><?= e($page['h1']) ?></h1>
        <p class="lede"><?= e($page['lede']) ?></p>
        <div class="hero-actions center">
            <a class="btn btn-primary" href="/">Back to Home</a>
            <a class="btn btn-ghost" href="/contact-us/">Contact Us</a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title center">Popular Pages</h2>
        <div class="card-grid cols-4">
            <a class="card card-compact" href="/seo-services/"><h3>SEO Services</h3><span class="card-more">Visit →</span></a>
            <a class="card card-compact" href="/ai/"><h3>AI Services</h3><span class="card-more">Visit →</span></a>
            <a class="card card-compact" href="/web-services/"><h3>Web Services</h3><span class="card-more">Visit →</span></a>
            <a class="card card-compact" href="/case-studies/"><h3>Case Studies</h3><span class="card-more">Visit →</span></a>
        </div>
    </div>
</section>
