<?php
/**
 * Listicle: nine schema markup types for B2B websites, with copy-ready JSON-LD
 * and a page-type picker. Loaded by templates/blog-post.php through the 'rich'
 * key; the interactive parts come from includes/listicle-kit.php and
 * assets/js/listicle.js. Google's rules are paraphrased from the pages linked in
 * each sentence (checked 8 Nov 2026), including the FAQ rich result ending on
 * 7 May 2026. All code samples use example.com. External links are nofollow.
 */

$d = 'https://developers.google.com/search/docs/appearance/';
$g = [
    'intro' => $d . 'structured-data/intro-structured-data',
    'policies' => $d . 'structured-data/sd-policies',
    'org' => $d . 'structured-data/organization',
    'sitenames' => $d . 'site-names',
    'crumbs' => $d . 'structured-data/breadcrumb',
    'crumbsblog' => 'https://developers.google.com/search/blog/2025/01/simplifying-breadcrumbs',
    'article' => $d . 'structured-data/article',
    'dates' => $d . 'publication-dates',
    'local' => $d . 'structured-data/local-business',
    'video' => $d . 'structured-data/video',
    'software' => $d . 'structured-data/software-app',
    'reviews' => $d . 'structured-data/review-snippet#self-serving',
    'event' => $d . 'structured-data/event',
    'faqgone' => 'https://developers.google.com/search/updates#faq-deprecation',
    'howto' => 'https://developers.google.com/search/blog/2023/08/howto-faq-changes',
    'retired' => 'https://developers.google.com/search/blog/2025/06/simplifying-search-results',
    'rrt' => 'https://search.google.com/test/rich-results',
    'validator' => 'https://validator.schema.org/',
];

return [

'body' => static function () use ($g): void { ?>
<p class="bx-open">Schema markup will not lift a weak page up the rankings. What it does is remove guesswork: it tells search engines and AI systems exactly who you are, what a page is and who stands behind it.</p>
<p>For a B2B website, that clarity is worth an afternoon of work. Most sites need only a handful of types, added once to each page template. The trouble is that advice online still recommends types Google no longer shows, so this list is current to November 2026 and says plainly which types earn a visible result and which only add clarity.</p>
<p>Use the finder below to see which types your page needs, copy the code for each, and tick the ones you already have.</p>

<div class="answer-box post-answer">
    <p class="answer-text"><?= e($GLOBALS['BLOG']['schema-markup-for-b2b-websites']['lede'] ?? '') ?></p>
</div>

<?php lx_glance([
    ['organization', 'Organization', 'Home or About page'],
    ['website', 'WebSite', 'Home page only'],
    ['breadcrumbs', 'BreadcrumbList', 'Every inner page'],
    ['article', 'Article and BlogPosting', 'Posts and case studies'],
    ['local-business', 'LocalBusiness', 'Only with a real address'],
    ['service', 'Service', 'Service pages'],
    ['video', 'VideoObject', 'Pages with a video'],
    ['software', 'SoftwareApplication', 'Software and SaaS pages'],
    ['person', 'Person', 'Author and team pages'],
], 'The 9 types at a glance'); ?>

<div class="lx-tool lx-js" data-lx-tool="schema" id="schema-finder">
    <p class="lx-tool-k">Quick finder</p>
    <p class="lx-tool-h">Which schema does this page need?</p>
    <p>Pick the kind of page you are working on. The types it needs are marked in the list below.</p>
    <div class="lx-pick" role="group" aria-label="Page type">
        <button type="button" data-page="home" data-label="your home page" data-note="WebSite markup belongs on the home page only. Keep the Organization facts identical everywhere they appear." aria-pressed="false">Home page</button>
        <button type="button" data-page="about" data-label="an About page" data-note="If your Organization markup lives on the home page, the About page needs only breadcrumbs." aria-pressed="false">About page</button>
        <button type="button" data-page="service" data-label="a service page" data-note="Describe the service in the visible copy too. Markup must match what people can read." aria-pressed="false">Service page</button>
        <button type="button" data-page="blog" data-label="a blog post or case study" data-note="Link the author to a real author page, and keep the visible date and the markup date the same." aria-pressed="false">Blog post or case study</button>
        <button type="button" data-page="location" data-label="a location or contact page" data-note="Use LocalBusiness only for an address customers can visit or that you actually operate from." aria-pressed="false">Location or contact page</button>
        <button type="button" data-page="product" data-label="a software or product page" data-note="Ratings in the markup must be genuine and shown on the page." aria-pressed="false">Software or product page</button>
        <button type="button" data-page="video" data-label="a page with a video" data-note="Each video needs a name, a thumbnail and an upload date at the very least." aria-pressed="false">Page with a video</button>
        <button type="button" data-page="team" data-label="a team or author page" data-note="Person markup here is what an article's author link points to." aria-pressed="false">Team or author page</button>
    </div>
    <div class="lx-pick-out" data-pick-out aria-live="polite"><p>Choose a page type to see its list.</p></div>
</div>

<?php lx_board('schema-b2b', [
    'label' => 'types in place',
    'filters' => ['core' => 'Core types', 'if' => 'Only if they fit'],
    'msgs' => [
        [0, 'Tick each type your site already has. The count is saved in this browser.'],
        [3, 'A start. Organization, WebSite and BreadcrumbList are the base most B2B sites build on.'],
        [6, 'Solid coverage. Check the remaining types against the pages you actually publish.'],
        [9, 'All nine in place. Test a sample of pages after every template change.'],
    ],
]); ?>

<?php lx_item(1, 'organization', 'Organization', ['type' => 'core', 'pages' => 'home about', 'tags' => ['Core', 'Home or About page'], 'mark' => 'We have this'], static function () use ($g): void { ?>
<p>Organization markup states the basic facts about your company: name, website, logo, profiles and contact details. Google says it helps it <?= ext_link($g['org'], 'understand and tell your organization apart') ?> from others, and some properties can influence the logo shown in results and details in the knowledge panel.</p>
<p>No property is required, but the useful ones are name, url, logo (at least 112 by 112 pixels), description, sameAs links to your official profiles, and contact details. Put it on the home page or the About page, and keep the facts identical to what your pages and profiles say.</p>
<?php lx_code(<<<'JSON'
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "Example Ltd",
  "url": "https://www.example.com/",
  "logo": "https://www.example.com/images/logo-512.png",
  "description": "Industrial sensors and calibration services.",
  "sameAs": [
    "https://www.linkedin.com/company/example",
    "https://www.youtube.com/@example"
  ],
  "contactPoint": {
    "@type": "ContactPoint",
    "contactType": "sales",
    "email": "sales@example.com",
    "telephone": "+1-555-0100"
  }
}
</script>
JSON, 'JSON-LD'); ?>
<?php }); ?>

<?php lx_item(2, 'website', 'WebSite', ['type' => 'core', 'pages' => 'home', 'tags' => ['Core', 'Home page only'], 'mark' => 'We have this'], static function () use ($g): void { ?>
<p>WebSite markup is the main signal Google reads to choose the <?= ext_link($g['sitenames'], 'site name shown in search results') ?>. It belongs on the home page of the domain or subdomain, not on inner pages, and it needs only a name and a URL. Add alternateName for shorter or older versions of your name, in order of preference.</p>
<p>One catch: the Rich Results Test does not check site names. Use the <?= ext_link($g['validator'], 'Schema Markup Validator') ?> instead.</p>
<?php lx_code(<<<'JSON'
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "name": "Example",
  "alternateName": ["Example Ltd", "EX Sensors"],
  "url": "https://www.example.com/"
}
</script>
JSON, 'JSON-LD'); ?>
<?php }); ?>

<?php lx_item(3, 'breadcrumbs', 'BreadcrumbList', ['type' => 'core', 'pages' => 'about service blog location product video team', 'tags' => ['Core', 'Every inner page'], 'mark' => 'We have this'], static function () use ($g): void { ?>
<p>Breadcrumb markup shows where a page sits in your site, and Google can display that trail instead of the raw URL. Since January 2025 the trail appears <?= ext_link($g['crumbsblog'], 'on desktop results only') ?>, but it still helps Google read your site's structure.</p>
<p>Per Google's <?= ext_link($g['crumbs'], 'breadcrumb guidelines') ?>, you need at least two items, each with a name, a position and a URL (the last item can leave out the URL). Follow the path a visitor would take, which is not always the same as your folder structure.</p>
<?php lx_code(<<<'JSON'
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    { "@type": "ListItem", "position": 1, "name": "Services", "item": "https://www.example.com/services/" },
    { "@type": "ListItem", "position": 2, "name": "Calibration", "item": "https://www.example.com/services/calibration/" }
  ]
}
</script>
JSON, 'JSON-LD'); ?>
<?php }); ?>

<?php lx_item(4, 'article', 'Article and BlogPosting', ['type' => 'core', 'pages' => 'blog', 'tags' => ['Core', 'Posts and case studies'], 'mark' => 'We have this'], static function () use ($g): void { ?>
<p>Article markup, or its more specific form BlogPosting, tells Google who wrote a piece and when. Google's <?= ext_link($g['article'], 'article documentation') ?> requires no properties but recommends a headline, images, the published and modified dates, and an author with a name and a URL that points to a page about that person.</p>
<p>Dates deserve care. Google's guidance on <?= ext_link($g['dates'], 'byline dates') ?> asks for a visible, labelled date that matches the markup, and no future dates. Change dateModified only when the content really changes.</p>
<?php lx_code(<<<'JSON'
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BlogPosting",
  "headline": "How to Choose a Calibration Partner",
  "image": ["https://www.example.com/images/calibration-16x9.jpg"],
  "datePublished": "2026-11-09T09:00:00+00:00",
  "dateModified": "2026-11-09T09:00:00+00:00",
  "author": [{
    "@type": "Person",
    "name": "Alex Morgan",
    "url": "https://www.example.com/team/alex-morgan/"
  }]
}
</script>
JSON, 'JSON-LD'); ?>
<?php }); ?>

<?php lx_item(5, 'local-business', 'LocalBusiness', ['type' => 'if', 'pages' => 'location', 'tags' => ['Only if it fits', 'Only with a real address'], 'mark' => 'We have this'], static function () use ($g): void { ?>
<p>LocalBusiness markup describes a physical place of business. Google's <?= ext_link($g['local'], 'local business guidelines') ?> require a name and the address of that location, and recommend a phone number, URL and opening hours. If you have several offices, give each one its own page and its own markup.</p>
<p>Two cautions. First, use it only for an address people can visit or that you actually work from. Second, do not default to the old ProfessionalService type: <?= ext_link('https://schema.org/ProfessionalService', 'schema.org now flags it as deprecated') ?> because it was confused with Service. Plain LocalBusiness, or a specific subtype that truly fits, is safer.</p>
<?php lx_code(<<<'JSON'
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "LocalBusiness",
  "name": "Example Ltd, Springfield office",
  "url": "https://www.example.com/locations/springfield/",
  "telephone": "+1-555-0100",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "1 Example Street",
    "addressLocality": "Springfield",
    "postalCode": "00000",
    "addressCountry": "US"
  },
  "openingHoursSpecification": [{
    "@type": "OpeningHoursSpecification",
    "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
    "opens": "09:00",
    "closes": "17:30"
  }]
}
</script>
JSON, 'JSON-LD'); ?>
<?php }); ?>

<?php lx_item(6, 'service', 'Service', ['type' => 'core', 'pages' => 'service', 'tags' => ['Core', 'Service pages'], 'mark' => 'We have this'], static function (): void { ?>
<p>Google has no rich result for <?= ext_link('https://schema.org/Service', 'the Service type') ?>, and we would rather say so than pretend otherwise. We still add it to every service page, because it states your offer in terms no system has to guess: what the service is, who provides it and where it is available.</p>
<p>Keep it honest and close to the visible copy. If the page says you serve clients worldwide, say the same in areaServed. If it names a specific region, use that.</p>
<?php lx_code(<<<'JSON'
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Service",
  "name": "Sensor calibration",
  "serviceType": "Calibration service",
  "description": "On-site and lab calibration for pressure and temperature sensors.",
  "url": "https://www.example.com/services/calibration/",
  "provider": {
    "@type": "Organization",
    "name": "Example Ltd",
    "url": "https://www.example.com/"
  },
  "areaServed": "Worldwide"
}
</script>
JSON, 'JSON-LD'); ?>
<?php }); ?>

<aside class="bx-cta bx-cta--audit" aria-label="Free website audit">
    <svg class="bx-cta-art" viewBox="0 0 120 120" width="104" height="104" aria-hidden="true" focusable="false"><circle cx="60" cy="60" r="46" fill="none" stroke="#DAD5F2" stroke-width="12"/><path d="M60 14a46 46 0 1 1-40 69" fill="none" stroke="#FF3D8A" stroke-width="12" stroke-linecap="round"/><path d="M42 61l13 13 25-29" fill="none" stroke="#1D1936" stroke-width="9" stroke-linecap="round" stroke-linejoin="round"/></svg>
    <div class="bx-cta-copy">
        <p class="bx-cta-h">See What Search Engines Read on Your Site</p>
        <p>The free audit checks your page's search basics, including whether structured data is present.</p>
        <a class="bx-btn bx-btn--accent" href="/free-seo-audit/" data-cta="blog-schema-audit">Run My Free Website Audit <span aria-hidden="true">&rarr;</span></a>
        <p class="bx-cta-note">For a full check of each type, use Google's tools listed at the end of this article.</p>
    </div>
</aside>

<?php lx_item(7, 'video', 'VideoObject', ['type' => 'if', 'pages' => 'video', 'tags' => ['Only if it fits', 'Pages with a video'], 'mark' => 'We have this'], static function () use ($g): void { ?>
<p>If you publish product demos, webinar recordings or lab tours, VideoObject helps Google find and present them. Its <?= ext_link($g['video'], 'video guidelines') ?> require three properties: a name, a thumbnail URL and an upload date. A description, the duration and a content or embed URL are recommended.</p>
<?php lx_code(<<<'JSON'
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "VideoObject",
  "name": "Calibration lab tour",
  "description": "A four-minute walk through our calibration lab.",
  "thumbnailUrl": ["https://www.example.com/video/lab-tour-thumb.jpg"],
  "uploadDate": "2026-10-20T08:00:00+00:00",
  "duration": "PT4M12S",
  "contentUrl": "https://www.example.com/video/lab-tour.mp4"
}
</script>
JSON, 'JSON-LD'); ?>
<?php }); ?>

<?php lx_item(8, 'software', 'SoftwareApplication', ['type' => 'if', 'pages' => 'product', 'tags' => ['Only if it fits', 'Software and SaaS pages'], 'mark' => 'We have this'], static function () use ($g): void { ?>
<p>For SaaS and other software products, SoftwareApplication can show a rating and price in results. The bar is higher than for other types: Google's <?= ext_link($g['software'], 'software app guidelines') ?> require a name, a price (0 if free) and a rating or review. Only use it when real ratings from real users appear on the page.</p>
<p>The values below are placeholders. Replace every one with your own.</p>
<?php lx_code(<<<'JSON'
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "SoftwareApplication",
  "name": "Example Monitor",
  "applicationCategory": "BusinessApplication",
  "operatingSystem": "Web",
  "offers": { "@type": "Offer", "price": "49.00", "priceCurrency": "USD" },
  "aggregateRating": { "@type": "AggregateRating", "ratingValue": "4.6", "ratingCount": "120" }
}
</script>
JSON, 'JSON-LD'); ?>
<?php }); ?>

<?php lx_item(9, 'person', 'Person', ['type' => 'core', 'pages' => 'team blog', 'tags' => ['Core', 'Author and team pages'], 'mark' => 'We have this'], static function () use ($g): void { ?>
<p>Article markup recommends an author with a URL. That URL should land on a real page about the person, and Person markup on that page completes the link: name, role, employer and official profiles. There is no rich result for it. The value is that your authors' expertise is stated once, clearly, and connected to everything they write.</p>
<?php lx_code(<<<'JSON'
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Person",
  "name": "Alex Morgan",
  "jobTitle": "Head of Engineering",
  "url": "https://www.example.com/team/alex-morgan/",
  "worksFor": {
    "@type": "Organization",
    "name": "Example Ltd",
    "url": "https://www.example.com/"
  },
  "sameAs": ["https://www.linkedin.com/in/example"]
}
</script>
JSON, 'JSON-LD'); ?>
<?php }); ?>

<h2 id="skip-these">Types to Skip, or Stop Expecting Results From</h2>
<p>Plenty of schema advice online is out of date. These are the types worth leaving off your list, or keeping without expecting a rich result.</p>
<dl class="bx-dl">
    <dt>FAQPage</dt>
    <dd>Google stopped showing FAQ rich results on 7 May 2026 and <?= ext_link($g['faqgone'], 'removed the documentation') ?> the following month. Existing markup does no harm, but adding it now earns nothing in Google.</dd>
    <dt>HowTo</dt>
    <dd>HowTo rich results were <?= ext_link($g['howto'], 'withdrawn in 2023') ?>, at the same time FAQ results were limited.</dd>
    <dt>Review stars about yourself</dt>
    <dd>Testimonials on your own site are self-serving reviews: pages with LocalBusiness or Organization markup are <?= ext_link($g['reviews'], 'not eligible for review stars') ?>, even when the reviews come from an embedded widget.</dd>
    <dt>Event, for online-only webinars</dt>
    <dd>Google's <?= ext_link($g['event'], 'event guidelines') ?> now cover events at a physical location only, so a purely virtual webinar does not qualify.</dd>
    <dt>Retired types</dt>
    <dd>Google <?= ext_link($g['retired'], 'phased out several rich results in 2025') ?>, including Course Info, Claim Review, Estimated Salary, Learning Video, Special Announcement and Vehicle Listing.</dd>
</dl>

<h2 id="test-it">How to Add and Test Your Markup</h2>
<ol class="bx-steps">
    <li><strong>Use JSON-LD.</strong> Google marks it as the <?= ext_link($g['intro'], 'recommended format') ?>, because a script block is easier to maintain than tags woven through your HTML.</li>
    <li><strong>Generate it from your page data.</strong> Build markup into each template from the same fields the page displays, so the two can never disagree.</li>
    <li><strong>Mark up only what people can see.</strong> Google's <?= ext_link($g['policies'], 'general guidelines') ?> rule out describing content that is hidden from readers.</li>
    <li><strong>Test before and after release.</strong> Use the <?= ext_link($g['rrt'], 'Rich Results Test') ?> for types with rich results and the <?= ext_link($g['validator'], 'Schema Markup Validator') ?> for everything else.</li>
    <li><strong>Watch Search Console.</strong> Its enhancement reports flag errors across the whole site, not just the pages you tested.</li>
</ol>
<?php },

'closing' => static function (): void { ?>
<h2 id="final-thoughts">Final Thoughts</h2>
<p>Good schema is boring in the right way: the same facts, in the same words, on every page and every profile. Start with Organization, WebSite and BreadcrumbList, add Article and Person to your content, then add the conditional types only where a page genuinely fits them.</p>
<p>Structured data is also part of being understood by AI search. If that is your next question, read our guides on <a href="/blog/what-is-answer-engine-optimization/">answer engine optimization</a> and <a href="/blog/how-to-rank-in-google-ai-overviews/">how to rank in Google AI Overviews</a>. Running more than one language? Keep the markup consistent across versions with our <a href="/blog/international-seo-checklist/">international SEO checklist</a>.</p>

<aside class="bx-cta bx-cta--main" aria-label="Start a conversation with VTurnU">
    <p class="bx-cta-h">Want Structured Data Done Properly?</p>
    <p>We audit your markup, fix the errors and build the right types into each page template, so new pages get it automatically.</p>
    <div class="bx-cta-acts">
        <a class="bx-btn bx-btn--light" href="/contact-us/" data-cta="blog-schema-audit-main">Audit My Schema <span aria-hidden="true">&rarr;</span></a>
        <a class="bx-link bx-link--light" href="/seo-services/" data-cta="blog-schema-seo">See Our SEO Services</a>
    </div>
    <ul class="bx-cta-trust">
        <li>A senior strategist replies within one working day</li>
        <li>Projects start from <?= e(min_budget()) ?></li>
        <li>Your details stay private</li>
    </ul>
</aside>

<aside class="bx-author" aria-label="About the author">
    <p class="bx-author-h">Written by the VTurnU strategy team</p>
    <p>VTurnU is a B2B digital marketing and branding agency based in Chennai, working across SEO, AI search, paid media, content and websites for clients worldwide. Read more <a href="/about-us/">about the team</a>, <a href="/how-we-work/">how we work</a> and <a href="/ai-policy/">how we use AI</a>.</p>
</aside>
<?php },

];
