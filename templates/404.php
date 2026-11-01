<?php /** 404 page. Expects: $page */ ?>
<?php render_hero([
    'k' => '404', 'h1' => $page['h1'], 'lede' => $page['lede'], 'layout' => 'stack',
    'cta' => ['Back to home', '/'], 'alt' => ['Contact us', '/contact-us/'],
]); ?>

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
