<?php
/**
 * Pricing page. Expects: $page, $slug.
 *
 * Prices come from PRICE_BOOK in includes/currency.php, so each visitor sees a
 * fixed figure in their own currency. Plan contents live here as data: the
 * comparison tables and the cards read the same lists, so they cannot disagree.
 */

$growth_plans = [
    [
        'name' => 'Starter', 'key' => 'starter', 'accent' => 'ac-cyan',
        'for' => 'For small businesses that need one channel working properly before adding more.',
        'best' => 'Local businesses and early-stage firms',
        'gets' => ['SEO for up to 10 priority pages', 'Google Business Profile management', 'One paid channel: Google or Meta', '4 content pieces a month', 'Monthly report and review call'],
    ],
    [
        'name' => 'Growth', 'key' => 'growth', 'accent' => 'ac-yellow', 'featured' => 'Most popular',
        'for' => 'For businesses making digital their main source of qualified enquiries.',
        'best' => 'Established SMEs and B2B firms',
        'gets' => ['SEO for up to 30 pages, plus AI search visibility', 'Google and Meta ads managed together', '10 content pieces a month', '12 social posts a month', '1 landing page improved each month', 'Live dashboard and a dedicated strategist'],
    ],
    [
        'name' => 'Enterprise', 'key' => null, 'accent' => 'ac-purple',
        'for' => 'For multi-location brands, multiple markets and larger media budgets.',
        'best' => 'Large sites and multi-market brands',
        'gets' => ['Full-funnel, multi-channel program', 'Enterprise SEO and PPC governance', 'Multi-language and multi-market rollouts', 'CRM and analytics integration', 'Quarterly executive reviews', 'Agreed service levels'],
    ],
];

/* [row, Starter, Growth, Enterprise]. true and false render as a tick or a dash. */
$growth_compare = [
    'Search' => [
        ['Pages optimized', 'Up to 10', 'Up to 30', 'Agreed per site'],
        ['Technical SEO audit', 'Once, then quarterly', 'Monthly', 'Continuous'],
        ['Google Business Profile', '1 location', 'Up to 3 locations', 'All locations'],
        ['AI search visibility (AEO and GEO)', 'Foundations', true, true],
        ['Buyer prompts tracked in AI answers', false, '25 a month', 'Custom set'],
    ],
    'Paid media' => [
        ['Ad platforms managed', '1', '2', 'Multi-channel'],
        ['Conversion tracking setup', true, true, true],
        ['Ad creative refresh', 'Quarterly', 'Monthly', 'Continuous'],
        ['Landing page improvements', false, '1 a month', 'Agreed plan'],
    ],
    'Content and brand' => [
        ['Content pieces a month', '4', '10', 'Agreed plan'],
        ['Social media posts a month', false, '12', 'Agreed plan'],
        ['Email campaigns', false, '2 a month', 'Agreed plan'],
    ],
    'Service' => [
        ['Reporting', 'Monthly report', 'Live dashboard and report', 'Executive dashboards'],
        ['Review calls', 'Monthly', 'Fortnightly', 'Weekly, plus quarterly reviews'],
        ['Your contact', 'Shared specialist', 'Dedicated strategist', 'Named team'],
        ['Reply time', 'Within 1 working day', 'Within 1 working day', 'Agreed service level'],
    ],
];

$web_plans = [
    [
        'name' => 'Business Website', 'key' => 'website', 'accent' => 'ac-olive',
        'for' => 'A fast, search-ready website that presents your business and captures enquiries.',
        'best' => 'Service businesses and SMEs',
        'gets' => ['Up to 10 custom-designed pages', 'Mobile-first responsive design', 'On-page SEO and schema built in', 'Enquiry forms and WhatsApp', '30 days of support after launch'],
    ],
    [
        'name' => 'Ecommerce Store', 'key' => 'store', 'accent' => 'ac-terra', 'featured' => 'Best value',
        'for' => 'A conversion-first store on Shopify, WooCommerce or a custom build.',
        'best' => 'Brands selling online',
        'gets' => ['Custom storefront design', 'Up to 50 products loaded', 'Payments, shipping and tax setup', 'Product schema and ecommerce SEO base', 'Analytics and conversion tracking', '30 days of support after launch'],
    ],
    [
        'name' => 'Custom Web App', 'key' => null, 'accent' => 'ac-pink',
        'for' => 'Portals, platforms, headless builds and AI-powered products.',
        'best' => 'Products and complex workflows',
        'gets' => ['Discovery and UX architecture', 'Custom design system', 'Modern, scalable stack', 'Integrations and automation', 'Support plan after launch'],
    ],
];

$web_compare = [
    ['Pages or products', 'Up to 10 pages', 'Up to 50 products', 'Scoped'],
    ['Design', 'Custom, 2 rounds of changes', 'Custom, 2 rounds of changes', 'Full design system'],
    ['Content management', true, true, 'Where needed'],
    ['SEO setup and schema', true, true, true],
    ['PageSpeed target', '90+', '90+', '90+ on key journeys'],
    ['Typical timeline', '3 to 4 weeks', '5 to 8 weeks', 'Agreed after discovery'],
    ['Support after launch', '30 days', '30 days', 'Support plan'],
];

$pill = static function ($v): string {
    if ($v === true) { return '<span class="cmp-yes" aria-label="Included">✓</span>'; }
    if ($v === false) { return '<span class="cmp-no" aria-label="Not included">-</span>'; }
    return e((string) $v);
};

$priced = array_filter(array_merge($growth_plans, $web_plans), static fn($p) => $p['key'] !== null);
echo jsonld_script([
    '@context' => 'https://schema.org',
    '@type' => 'OfferCatalog',
    'name' => 'VTurnU marketing and website packages',
    'url' => abs_url('/pricing/'),
    'itemListElement' => array_values(array_map(static fn($p) => [
        '@type' => 'Offer',
        'name' => $p['name'],
        'description' => $p['for'],
        'price' => (string) (PRICE_BOOK[visitor_currency()][$p['key']] ?? PRICE_BOOK['USD'][$p['key']]),
        'priceCurrency' => isset(PRICE_BOOK[visitor_currency()]) ? visitor_currency() : 'USD',
        'seller' => ['@id' => SITE_URL . '/#organization'],
    ], $priced)),
]);
?>
<section class="page-hero">
    <div class="container">
        <p class="eyebrow eyebrow-line">Pricing</p>
        <h1><?= e($page['h1']) ?></h1>
        <p class="lede"><?= e($page['lede']) ?></p>
        <ul class="hero-chips">
            <li>Clear prices, in your currency</li>
            <li>Websites billed once</li>
            <li>No lock-in after 90 days</li>
        </ul>
        <div class="hero-cur"><?= currency_switcher('cur-pricing') ?></div>
    </div>
</section>

<section class="section" id="marketing">
    <div class="container">
        <div class="center-text" data-reveal="head">
            <p class="eyebrow">Digital marketing</p>
            <h2 class="section-title">Monthly growth plans <span class="price-tag-recurring">Recurring</span></h2>
            <p class="pricing-intro center">You pay for the team and the work. Ad spend is separate, paid directly to Google or Meta, and stays in your own accounts.</p>
        </div>
        <div class="card-grid cols-3 pricing-grid">
            <?php foreach ($growth_plans as $p): ?>
            <div class="card price-card <?= e($p['accent']) ?><?= !empty($p['featured']) ? ' price-featured' : '' ?>" data-reveal="card">
                <?php if (!empty($p['featured'])): ?><p class="price-badge"><?= e($p['featured']) ?></p><?php endif; ?>
                <h3><?= e($p['name']) ?></h3>
                <p class="price"><span><?= $p['key'] ? e(price($p['key'])) : 'Custom' ?></span><?= $p['key'] ? '/month' : '' ?></p>
                <p class="price-note"><?= e($p['for']) ?></p>
                <p class="price-best"><strong>Best for:</strong> <?= e($p['best']) ?></p>
                <ul class="check-list">
                    <?php foreach ($p['gets'] as $g): ?><li><?= e($g) ?></li><?php endforeach; ?>
                </ul>
                <a class="btn <?= !empty($p['featured']) ? 'btn-primary' : 'btn-ghost' ?> btn-block" href="/contact-us/"><?= $p['key'] ? 'Get Started' : 'Request a Quote' ?></a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section-tint">
    <div class="container">
        <h2 class="section-title center">Compare the plans</h2>
        <div class="cmp-wrap" tabindex="0" role="region" aria-label="Plan comparison table">
            <table class="cmp-table">
                <thead>
                    <tr>
                        <th scope="col">What you get</th>
                        <?php foreach ($growth_plans as $p): ?>
                        <th scope="col"<?= !empty($p['featured']) ? ' class="cmp-feat"' : '' ?>><?= e($p['name']) ?><small><?= $p['key'] ? e(price($p['key'])) . '/mo' : 'Custom' ?></small></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <?php foreach ($growth_compare as $group => $rows): ?>
                <tbody>
                    <tr class="cmp-group"><th colspan="4" scope="colgroup"><?= e($group) ?></th></tr>
                    <?php foreach ($rows as [$label, $a, $b, $c]): ?>
                    <tr>
                        <th scope="row"><?= e($label) ?></th>
                        <td><?= $pill($a) ?></td>
                        <td class="cmp-feat"><?= $pill($b) ?></td>
                        <td><?= $pill($c) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</section>

<section class="section" id="websites">
    <div class="container">
        <div class="center-text" data-reveal="head">
            <p class="eyebrow">Web development</p>
            <h2 class="section-title">Website packages <span class="price-tag-onetime">One-time payment</span></h2>
            <p class="pricing-intro center">Design and development are billed once. After launch the site is yours, with no recurring development fee.</p>
        </div>
        <div class="card-grid cols-3 pricing-grid">
            <?php foreach ($web_plans as $p): ?>
            <div class="card price-card <?= e($p['accent']) ?><?= !empty($p['featured']) ? ' price-featured' : '' ?>" data-reveal="card">
                <?php if (!empty($p['featured'])): ?><p class="price-badge"><?= e($p['featured']) ?></p><?php endif; ?>
                <h3><?= e($p['name']) ?></h3>
                <p class="price"><span><?= $p['key'] ? e(price($p['key'])) : 'Custom' ?></span><?= $p['key'] ? ' one-time' : '' ?></p>
                <p class="price-note"><?= e($p['for']) ?></p>
                <p class="price-best"><strong>Best for:</strong> <?= e($p['best']) ?></p>
                <ul class="check-list">
                    <?php foreach ($p['gets'] as $g): ?><li><?= e($g) ?></li><?php endforeach; ?>
                </ul>
                <a class="btn <?= !empty($p['featured']) ? 'btn-primary' : 'btn-ghost' ?> btn-block" href="/contact-us/"><?= $p['key'] ? 'Get a Build Quote' : 'Scope My Project' ?></a>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="cmp-wrap cmp-web" tabindex="0" role="region" aria-label="Website package comparison table">
            <table class="cmp-table">
                <thead>
                    <tr>
                        <th scope="col">What you get</th>
                        <?php foreach ($web_plans as $p): ?><th scope="col"<?= !empty($p['featured']) ? ' class="cmp-feat"' : '' ?>><?= e($p['name']) ?></th><?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($web_compare as [$label, $a, $b, $c]): ?>
                    <tr><th scope="row"><?= e($label) ?></th><td><?= $pill($a) ?></td><td class="cmp-feat"><?= $pill($b) ?></td><td><?= $pill($c) ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="center price-disclaimer">Hosting, domains, paid themes and third-party licenses are billed at cost, so you always see what you are paying for.</p>
    </div>
</section>

<section class="section section-tint" id="enterprise">
    <div class="container">
        <div class="center-text" data-reveal="head">
            <p class="eyebrow">Enterprise &amp; IT</p>
            <h2 class="section-title">Enterprise projects are scoped, not packaged</h2>
            <p class="pricing-intro center">SAP CPQ, Salesforce, Odoo, ERPNext and IT staffing depend on scope, so a fixed list price would be a guess. Here is how each one is priced.</p>
        </div>
        <div class="card-grid cols-3">
            <div class="card" data-reveal="card"><h3>Fixed-scope project</h3><p>A fixed fee for an agreed phase, such as a first rollout. Best when the requirements are clear.</p></div>
            <div class="card" data-reveal="card"><h3>Time and materials</h3><p>Agreed rates for evolving work, with weekly reporting of hours and progress.</p></div>
            <div class="card" data-reveal="card"><h3>Staffing</h3><p>An agreed rate for contract roles, and a percentage of annual salary for direct hires.</p></div>
        </div>
        <p class="center pricing-cta"><a class="btn btn-grad" href="/enterprise-it-services/">Explore Enterprise &amp; IT</a></p>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title center">What moves a price up or down</h2>
        <div class="card-grid cols-4">
            <div class="card" data-reveal="card"><h3>Competition</h3><p>A national keyword costs more to win than a local one.</p></div>
            <div class="card" data-reveal="card"><h3>Starting point</h3><p>A site with no technical debt needs less work to begin.</p></div>
            <div class="card" data-reveal="card"><h3>Markets</h3><p>Each extra country or language adds work and reporting.</p></div>
            <div class="card" data-reveal="card"><h3>Speed</h3><p>Faster results mean more hours each month.</p></div>
        </div>
    </div>
</section>

<section class="section section-tint">
    <div class="container">
        <h2 class="section-title center">How billing works</h2>
        <div class="steps-line billing-line">
            <div class="step-card">
                <span class="step-time">Month 1</span>
                <h3>Build and setup</h3>
                <p>A website build is charged once. Audits, tracking and campaign builds happen under your monthly plan, with no setup fee.</p>
            </div>
            <div class="step-card">
                <span class="step-time">Month 2 onward</span>
                <h3>Marketing only</h3>
                <p>Only the monthly plan continues. There are no further development charges.</p>
            </div>
            <div class="step-card">
                <span class="step-time">After 90 days</span>
                <h3>Month to month</h3>
                <p>Scale up, scale down or pause a channel. Ninety days is the minimum honest window to judge a search program.</p>
            </div>
        </div>
        <?php if (gst_visible()): ?>
        <p class="center price-gst">Prices exclude GST at 18%. <?= e(LEGAL_NAME) ?>, GSTIN <?= e(GSTIN) ?>. A proper tax invoice is issued for every payment.</p>
        <?php endif; ?>
    </div>
</section>

<?php render_faqs(page_faqs($page, $slug), 'Pricing questions, answered'); ?>
