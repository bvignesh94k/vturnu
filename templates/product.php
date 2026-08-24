<?php /**
 * VTurnAI product landing page. Expects: $page, $slug.
 *
 * This sells our own SaaS from the service site. The page deliberately ends on
 * a fork rather than a single CTA: self-serve buyers go to the trial, and
 * anyone who would rather not run it themselves goes to the agency contact
 * form. Both are wins, and keeping the second door open is what stops a
 * product page from cannibalising the service intent of the rest of the site.
 */

/* Weighting comes from the product's own scoring model. Kept as data so the
   bars, the table and the copy can never disagree with each other. */
$score_parts = [
    ['SEO', 30, 'Search fundamentals', 'Crawlability, indexation, speed and the ranking signals Google still runs on.'],
    ['AEO', 20, 'Answer readiness', 'Whether your pages are written so an engine can lift a direct answer out of them.'],
    ['GEO', 35, 'Generative visibility', 'How well AI engines understand your brand as an entity, and how likely they are to cite you.'],
    ['HEO', 15, 'Human & authority', 'Combined experience, expertise and trust signals behind the brand.'],
];

$engines = [
    ['ChatGPT', 'OpenAI'], ['Gemini', 'Google'], ['Claude', 'Anthropic'],
    ['Perplexity', 'Perplexity AI'], ['Grok', 'xAI'], ['Copilot', 'Microsoft'],
    ['AI Overviews', 'Google Search'], ['Bing', 'Microsoft'],
];

$features = [
    ['AI visibility monitoring', 'Track every mention, citation and recommendation your brand earns, broken out engine by engine, so you know where you are strong and where you do not exist at all.'],
    ['Prompt tracking by intent', 'Group the prompts you care about by purchase intent. Watch what an engine answers when someone is comparing, and when someone is ready to buy.'],
    ['Technical SEO audit', 'A full crawl with severity scoring, so the list you get back is ordered by what actually costs you visibility, not by what is easiest to detect.'],
    ['Citation readiness score', 'Measures whether your pages are structured for an engine to quote them, which is a different problem from ranking, and needs a different fix.'],
    ['Entity consistency checks', 'AI engines resolve you as an entity before they recommend you. Inconsistent names, descriptions and facts across the web quietly suppress that.'],
    ['Competitor share of voice', 'Who is being recommended instead of you, on the prompts that matter, and how that gap moves month over month.'],
];

$faqs = [
    ['What is the difference between this and the free audit on this site?',
     'The free audit is a one-time snapshot of a single page. It reads the HTML your homepage serves and scores what it finds, right now. ' . PRODUCT_NAME . ' runs continuously: it crawls the whole site, tracks your prompts across eight engines, watches competitors and shows you the trend over months. Use the free audit to find out whether you have a problem. Use ' . PRODUCT_NAME . ' to fix it and prove it stayed fixed.'],
    ['Why does AI visibility need its own tool? Is rank tracking not enough?',
     'Rank tracking answers "where do I sit on a results page". That question is getting less useful every quarter, because a growing share of searches now end in an answer rather than a list of links. If ChatGPT recommends three vendors and you are not one of them, no rank tracker will tell you, because there was no ranking involved. That is the gap this measures.'],
    ['What does it cost?',
     PRODUCT_PRICE . ' per month, which covers one website, 500 crawled URLs, 25 tracked prompts, 5 competitors and two AI scans a month. There is a ' . PRODUCT_TRIAL_DAYS . '-day free trial first, so you can see your own numbers before you decide whether they are worth paying for.'],
    ['Do I need to be technical to use it?',
     'No. It is built for owners, freelancers, in-house marketers and small agencies. The output is a prioritised list of fixes in plain language, ordered by visibility impact. If a fix needs a developer, it says so and tells you exactly what to hand them.'],
    ['Can VTurnU run it for me instead?',
     'Yes. If you would rather not run the tool and work the list yourself, that is what our AI SEO service is for: we run the platform, do the work and report on it. Plenty of clients start on the tool, find the gap is bigger than they want to handle in-house, and move across.'],
    ['Is my data shared with anyone?',
     'No. Your crawl data, tracked prompts and competitor set stay in your own account. We do not sell, pool or resell customer data, and nothing from your account is used to inform anyone else\'s reporting.'],
];

echo jsonld_script([
    '@context' => 'https://schema.org',
    '@type' => 'SoftwareApplication',
    'name' => PRODUCT_NAME,
    'applicationCategory' => 'BusinessApplication',
    'operatingSystem' => 'Web',
    'url' => PRODUCT_URL,
    'description' => $page['meta'],
    'publisher' => ['@type' => 'Organization', 'name' => SITE_NAME, 'url' => SITE_URL],
    'offers' => [
        '@type' => 'Offer',
        'price' => '499',
        'priceCurrency' => 'INR',
        'url' => PRODUCT_URL,
        'availability' => 'https://schema.org/InStock',
    ],
]);
echo jsonld_script(jsonld_faq($faqs));
?>
<section class="page-hero product-hero">
    <div class="container">
        <p class="eyebrow eyebrow-line">A VTurnU product · <?= e(PRODUCT_TRIAL_DAYS) ?>-day free trial</p>
        <h1><?= e($page['h1']) ?></h1>
        <p class="lede"><?= e($page['lede']) ?></p>
        <div class="hero-actions">
            <a class="btn btn-grad" href="<?= e(product_url('product-page-hero')) ?>" target="_blank" rel="noopener">Start Free Trial</a>
            <a class="btn btn-outline" href="#how-it-scores">See How It Scores</a>
        </div>
        <ul class="hero-chips">
            <li><?= e(PRODUCT_PRICE) ?>/month after trial</li>
            <li>No card for the trial</li>
            <li>8 engines tracked</li>
        </ul>
    </div>
</section>

<div class="trust-strip" aria-label="What <?= e(PRODUCT_NAME) ?> tracks">
    <div class="container trust-strip-inner">
        <span><strong>8</strong> search &amp; AI engines</span>
        <span><strong>25</strong> tracked prompts</span>
        <span><strong>500</strong> URLs crawled</span>
        <span><strong>5</strong> competitors watched</span>
    </div>
</div>

<section class="section">
    <div class="container split">
        <div class="answer-box">
            <h2>What is an AI visibility tool?</h2>
            <p class="answer-text">An AI visibility tool measures how often AI answer engines such as ChatGPT, Gemini, Perplexity and Google AI Overviews mention, cite or recommend your brand when someone asks a question in your category. Traditional rank trackers cannot see this, because an AI answer has no result page and no position number. <?= e(PRODUCT_NAME) ?> tracks it directly and scores what to fix.</p>
            <a class="btn btn-primary" href="<?= e(product_url('product-page-answer')) ?>" target="_blank" rel="noopener">Check My Visibility</a>
        </div>
        <div class="panel panel-accent">
            <h3>You are probably already being asked about</h3>
            <ul class="check-list">
                <li>"Best <em>[your category]</em> company in <em>[your city]</em>"</li>
                <li>"Who should I use for <em>[what you sell]</em>?"</li>
                <li>"<em>[Competitor]</em> vs <em>[you]</em>, which is better?"</li>
                <li>"Is <em>[your brand]</em> any good?"</li>
            </ul>
            <p class="seo-note">Every one of those questions gets answered about you today, with or without your input. The only question is whether you know what the answer says.</p>
        </div>
    </div>
</section>

<section class="section section-tint" id="how-it-scores">
    <div class="container">
        <h2 class="section-title center">One score, four disciplines</h2>
        <p class="center section-lede">Visibility is not one number in one place. <?= e(PRODUCT_NAME) ?> scores four separate disciplines and weights them into a single V Score, so you can see at a glance which one is dragging you down.</p>
        <div class="vscore-grid">
            <?php foreach ($score_parts as [$abbr, $weight, $label, $desc]): ?>
            <div class="vscore-card" data-reveal="card">
                <div class="vscore-head">
                    <strong><?= e($abbr) ?></strong>
                    <span><?= (int) $weight ?>% of V Score</span>
                </div>
                <span class="vscore-bar" aria-hidden="true"><i style="width:<?= (int) $weight ?>%"></i></span>
                <h3><?= e($label) ?></h3>
                <p><?= e($desc) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title center">Engines it watches</h2>
        <p class="center section-lede">Your customers do not all ask the same assistant. <?= e(PRODUCT_NAME) ?> queries each one separately, because they disagree with each other far more often than people expect.</p>
        <ul class="engine-row">
            <?php foreach ($engines as [$name, $maker]): ?>
            <li><strong><?= e($name) ?></strong><span><?= e($maker) ?></span></li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

<section class="section section-tint">
    <div class="container">
        <h2 class="section-title center">What you get</h2>
        <div class="card-grid cols-3">
            <?php foreach ($features as [$title, $desc]): ?>
            <div class="card" data-reveal="card">
                <h3><?= e($title) ?></h3>
                <p><?= e($desc) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php /* The honest three-way comparison. This exists as much to protect the
         service business as to sell the product: it tells a visitor plainly
         when the tool is the wrong answer for them, which is the only way a
         page like this stays credible. Answer engines also lift tabular
         comparisons directly, so it doubles as citable structure. */ ?>
<section class="section">
    <div class="container">
        <h2 class="section-title center">Free audit, <?= e(PRODUCT_NAME) ?>, or done-for-you?</h2>
        <p class="center section-lede">Three different problems. Pick the row that sounds like you.</p>
        <figure class="post-table-wrap">
            <table class="post-table">
                <caption class="sr-only">Comparison of the free audit tool, <?= e(PRODUCT_NAME) ?> and VTurnU's managed AI SEO service</caption>
                <thead>
                    <tr>
                        <th scope="col">What you need</th>
                        <th scope="col">Free audit</th>
                        <th scope="col"><?= e(PRODUCT_NAME) ?></th>
                        <th scope="col">VTurnU service</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><th scope="row">Cost</th><td>Free, no signup</td><td><?= e(PRODUCT_PRICE) ?>/month</td><td>Quoted per scope</td></tr>
                    <tr><th scope="row">How often it runs</th><td>Once, on demand</td><td>Continuously</td><td>Continuously, managed</td></tr>
                    <tr><th scope="row">What it looks at</th><td>Your homepage HTML</td><td>Up to 500 URLs</td><td>Whole site, plus off-site</td></tr>
                    <tr><th scope="row">AI engine tracking</th><td>Blocked-or-not check</td><td>8 engines, 25 prompts</td><td>8 engines, unlimited scope</td></tr>
                    <tr><th scope="row">Competitor tracking</th><td>No</td><td>5 competitors</td><td>Full landscape analysis</td></tr>
                    <tr><th scope="row">Who does the fixing</th><td>You</td><td>You, from a ranked list</td><td>We do</td></tr>
                    <tr><th scope="row">Best for</th><td>Finding out if you have a problem</td><td>Owners and marketers who will act on the list themselves</td><td>Teams without the hours to run it in-house</td></tr>
                </tbody>
            </table>
        </figure>
        <p class="center faq-cta">Not sure which fits? <a href="/contact-us/">Ask a specialist</a>: we will tell you honestly, including when the answer is the free one.</p>
    </div>
</section>

<section class="section section-tint">
    <div class="container narrow">
        <div class="panel product-price-card">
            <p class="eyebrow">Simple pricing</p>
            <p class="product-price"><strong><?= e(PRODUCT_PRICE) ?></strong><span>/month</span></p>
            <p class="product-price-sub">Everything below is included. No tiers to compare, no per-seat maths, no annual lock-in.</p>
            <ul class="check-list">
                <li>1 website, fully crawled</li>
                <li>500 URLs per crawl</li>
                <li>25 tracked prompts, grouped by intent</li>
                <li>5 competitors monitored</li>
                <li>2 full AI scans every month</li>
                <li>Client-ready reports you can send on</li>
            </ul>
            <a class="btn btn-grad btn-block" href="<?= e(product_url('product-page-pricing')) ?>" target="_blank" rel="noopener">Start My <?= e(PRODUCT_TRIAL_DAYS) ?>-Day Free Trial</a>
            <p class="form-privacy">Free for <?= e(PRODUCT_TRIAL_DAYS) ?> days. See your own visibility numbers before you pay anything.</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container narrow">
        <h2 class="section-title center"><?= e(PRODUCT_NAME) ?>, frequently asked questions</h2>
        <div class="faq-list">
            <?php foreach ($faqs as [$q, $a]): ?>
            <details class="faq">
                <summary><?= e($q) ?></summary>
                <p><?= e($a) ?></p>
            </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="cta-mid">
    <div class="container cta-mid-inner">
        <div>
            <h2>Find out what AI says about you</h2>
            <p>Start the free trial and see your first V Score today, or talk to us if you would rather we handled the whole thing.</p>
        </div>
        <div class="cta-mid-actions">
            <a class="btn btn-dark" href="<?= e(product_url('product-page-footer')) ?>" target="_blank" rel="noopener">Start Free Trial</a>
            <a class="cta-phone" href="/ai-seo/">or see our managed AI SEO service</a>
        </div>
    </div>
</section>
