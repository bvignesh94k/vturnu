<?php
/**
 * VTurnU homepage: an immersive, B2B lead-generation page.
 *
 * Expects: $page, $slug, $PAGES, $form_status, $visitor_dial_code
 *
 * Structure
 *   1. Pinned 3D hero. The section holds the viewport while scrolling plays
 *      four scenes inside it (positioning, buyer journey, markets, proof).
 *      Every scene is real HTML in source order, so crawlers, screen readers
 *      and visitors without JavaScript get the whole story as normal content.
 *   2. Client logos, the B2B problem, services, process, markets, proof,
 *      facts, the qualifying lead form, FAQ and a closing call to action.
 *
 * Nothing here is invented: service links render only when the page exists,
 * and every result is read live from includes/data/cases.php.
 */

require_once BASE_PATH . '/includes/data/cases.php';

/** Keep only entries whose first element is an existing page slug. */
$hp_existing = static function (array $rows) use ($PAGES): array {
    return array_values(array_filter($rows, static function (array $r) use ($PAGES): bool {
        return isset($PAGES[$r[0]]);
    }));
};

/** A case result as [metric, label, client, industry, slug], or null. */
$hp_result = static function (string $cslug, int $i = 0) use ($CASES): ?array {
    if (!isset($CASES[$cslug]['results'][$i])) {
        return null;
    }
    $c = $CASES[$cslug];
    return [$c['results'][$i][0], $c['results'][$i][1], $c['client'] ?? '', $c['industry'] ?? '', $cslug];
};

/* Client logos, single animated row. */
$LOGOS = array_values(array_filter([
    ['shineprints.png',            'Shineprints'],
    ['masaami.webp',               'Masaami'],
    ['ateliers-gym.png',           'Ateliers Gym'],
    ['sai-impression.webp',        'Sai Impression'],
    ['black-sheep-collective.png', 'The Black Sheep Collective'],
    ['firstchoice-autoparts.webp', 'FirstChoice Used Auto Parts'],
    ['pemotech.png',               'Pemotech'],
    ['boosterio.png',              'Boosterio'],
    ['yodgy.png',                  'Yodgy'],
], static function (array $l): bool {
    return is_file(BASE_PATH . '/assets/img/clients/' . $l[0]);
}));

/* Hero proof: the three B2B outcomes, read from the case studies. */
$HERO_PROOF = array_values(array_filter([
    $hp_result('saas-ppc-demo-pipeline'),
    $hp_result('manufacturer-rfq-seo-program'),
    $hp_result('law-firm-lead-gen-website'),
]));

/* Three service pillars. Each link renders only if its page exists. */
$PILLARS = [
    [
        'key' => 'search', 'accent' => 'v',
        'title' => 'Search & AI visibility',
        'desc' => 'Rank for the searches that carry buying intent, and get named when buyers ask ChatGPT, Perplexity or Google AI for a shortlist.',
        'links' => $hp_existing([
            ['seo-services', 'SEO services'],
            ['ai-seo', 'AI search optimization'],
            ['google-ai-overviews-seo', 'Google AI Overviews SEO'],
            ['chatgpt-seo', 'ChatGPT SEO'],
            ['lead-generation-seo', 'Lead generation SEO'],
            ['enterprise-seo', 'Enterprise SEO'],
        ]),
        'hub' => 'seo',
    ],
    [
        'key' => 'demand', 'accent' => 'u',
        'title' => 'Demand generation',
        'desc' => 'Paid search, paid social, outbound, email and content, built around your ideal customer profile and tracked to qualified pipeline.',
        'links' => $hp_existing([
            ['google-ads', 'Google Ads management'],
            ['enterprise-ppc', 'Enterprise PPC'],
            ['outbound-marketing', 'Outbound marketing'],
            ['email-marketing', 'Email marketing'],
            ['content-marketing', 'B2B content marketing'],
            ['social-media-marketing', 'Social media marketing'],
        ]),
        'hub' => 'digital-marketing',
    ],
    [
        'key' => 'web', 'accent' => 'mix',
        'title' => 'Web development & CRO',
        'desc' => 'Fast, secure, search-ready websites and landing pages engineered to turn the right visitors into sales conversations.',
        'links' => $hp_existing([
            ['web-design', 'Website design'],
            ['lead-gen-web-design', 'Lead generation websites'],
            ['custom-design-and-development', 'Custom development'],
            ['headless-web-design', 'Headless websites'],
            ['ecommerce-web-design', 'Ecommerce websites'],
            ['ai-development', 'AI development & automation'],
        ]),
        'hub' => 'web-services',
    ],
];

/* The five-stage engine. */
$STAGES = [
    ['Diagnose', 'Audit your search, AI and paid presence against the competitors winning your deals, and define the ideal customer profile we will target.'],
    ['Position', 'Sharpen the offer and messaging for each market, then build landing pages that speak to a buying committee, not a casual browser.'],
    ['Get visible', 'Technical SEO, content and entity work that earn rankings on Google and citations inside AI answers for the questions your buyers ask.'],
    ['Create demand', 'Paid search, paid social, outbound and email that reach decision makers who are in market now, at a cost per lead that makes sense.'],
    ['Convert & attribute', 'Conversion optimization, CRM integration and reporting that tie every channel to qualified leads, pipeline and revenue.'],
];

/* Markets. Region cards plus the country list used by the hero globe. */
$REGIONS = [
    ['Middle East', 'UAE · Saudi Arabia · Qatar', 'High-value B2B categories where search competition is still thin. Multilingual SEO, localized landing pages and Google Ads built for GCC buyers.'],
    ['Southeast Asia', 'Singapore · Malaysia · Indonesia', 'Regional headquarters markets with research-heavy, mobile-first buyers. English-first search with local-language reach where it pays.'],
    ['India', 'Pan-India, HQ in Chennai', 'Our home market. Deep experience across manufacturing, SaaS, healthcare, real estate and professional services.'],
    ['North America', 'United States · Canada', 'Mature, crowded search results. We win on technical SEO, AI visibility, sharper offers and conversion rate, not on bigger budgets alone.'],
    ['UK & Europe', 'United Kingdom · EU', 'Compliance-aware campaigns and content for buyers who compare vendors carefully before they ever book a call.'],
    ['Africa & Oceania', 'South Africa · Nigeria · Kenya · Australia', 'Fast-growing digital adoption in Africa with few B2B competitors investing in search yet, plus established Australian markets.'],
];

/* Hero globe markers: [label, latitude, longitude]. Decorative only; the same
   markets are listed as text in the hero and in the regions section. */
$GLOBE = [
    /* Fourth value: label placement. 'l' puts it on the left, 'n' hides it
       where neighbouring cities would overlap. */
    ['Chennai', 13.08, 80.27, ''], ['Dubai', 25.2, 55.27, ''], ['Riyadh', 24.71, 46.68, 'l'],
    ['Doha', 25.29, 51.53, 'n'], ['Singapore', 1.35, 103.82, ''], ['Kuala Lumpur', 3.14, 101.69, 'n'],
    ['Jakarta', -6.2, 106.85, ''], ['Sydney', -33.87, 151.21, ''], ['London', 51.51, -0.13, ''],
    ['New York', 40.71, -74.0, ''], ['Toronto', 43.65, -79.38, 'n'], ['San Francisco', 37.77, -122.42, ''],
    ['Johannesburg', -26.2, 28.05, ''], ['Lagos', 6.52, 3.38, ''], ['Nairobi', -1.29, 36.82, ''],
];

$HERO_MARKETS = ['UAE', 'Saudi Arabia', 'Qatar', 'Singapore', 'Malaysia', 'Indonesia', 'India', 'Australia', 'United Kingdom', 'United States', 'Canada', 'South Africa', 'Nigeria', 'Kenya'];

/* Case studies shown in the proof grid, B2B-leaning first. */
$CASE_CARDS = array_values(array_filter([
    $hp_result('saas-ppc-demo-pipeline'),
    $hp_result('manufacturer-rfq-seo-program'),
    $hp_result('law-firm-lead-gen-website'),
    $hp_result('clinic-local-seo-patient-growth'),
    $hp_result('real-estate-lead-generation-engine'),
    $hp_result('edutech-content-engine-signups'),
]));

/* Lead form options. Values are what reaches the CRM, so keep them readable. */
$FORM_SERVICES = ['SEO', 'AI search visibility', 'Google Ads / PPC', 'Paid social', 'Outbound & email', 'Content marketing', 'Website / landing pages', 'Full growth program'];
$FORM_MARKETS  = ['India', 'UAE & GCC', 'Southeast Asia', 'United States', 'Canada', 'UK & Europe', 'Australia', 'Africa', 'Global'];
$FORM_SIZE     = ['1 to 10 people', '11 to 50 people', '51 to 200 people', '201 to 1,000 people', '1,000+ people'];
$FORM_BUDGET   = ['Under USD 1,000 / month', 'USD 1,000 to 3,000 / month', 'USD 3,000 to 10,000 / month', 'USD 10,000 to 25,000 / month', 'USD 25,000+ / month', 'Not decided yet'];
$FORM_WHEN     = ['As soon as possible', 'Within 1 to 3 months', 'In 3 to 6 months', 'Just researching'];

$hq_names = country_names();
$hq_selected = $hq_names[security_visitor_country()] ?? 'India';
asort($hq_names);

$FAQS = [
    ['Do you work with companies outside India?',
     'Yes. We are headquartered in Chennai and work with B2B companies across the UAE, Saudi Arabia, Qatar, Singapore, Malaysia, Indonesia, Australia, the UK, the US, Canada and Africa. Strategy, reporting and calls run in English, and our working hours overlap with the Gulf, Southeast Asia and Australia, with early or late calls for Europe and North America.'],
    ['What kind of companies do you work best with?',
     'B2B companies with a clear offer and a sales team that needs better conversations: manufacturers and exporters, SaaS and technology firms, professional services, healthcare groups and real estate developers, typically between 10 and 1,000 people.'],
    ['How do you make sure the leads are good quality?',
     'We start from your ideal customer profile, target buying-intent searches and decision-maker audiences, qualify enquiries at the form with company, budget and timeline questions, and connect everything to your CRM. We report on qualified leads and pipeline, not on clicks or raw form fills.'],
    ['How soon will we see results?',
     'Paid campaigns can produce qualified enquiries in the first month. SEO usually shows movement by week eight and compounds from there. AI search visibility often moves fastest, because far fewer competitors are working on it yet.'],
    ['What does it cost?',
     'Growth programs start from ₹35,000 a month (about USD 420), and most B2B engagements sit higher depending on markets and channels. We scope it after one call and quote a fixed monthly fee. No setup fees and no surprise line items. Ad spend is paid directly to the platforms and stays in your accounts.'],
    ['What is AI search optimization, and why does it matter for B2B?',
     'Buyers now ask ChatGPT, Perplexity, Gemini and Google AI Overviews for vendor shortlists. AI search optimization makes your company one of the names those answers cite, using clean entity signals, structured data, third-party mentions and content written the way people ask questions.'],
    ['Can you build or rebuild our website as well?',
     'Yes. We design and develop fast, secure, search-ready websites and landing pages, including custom, headless, WordPress and ecommerce builds, so marketing and the site are planned together instead of fighting each other.'],
    ['Do we have to sign a long contract?',
     'No. After an initial ninety day window, which is the minimum honest time to judge a search program, we work month to month. You keep ownership of every account, asset and piece of content.'],
];

$cta_label = 'Get my B2B growth plan';
?>

<?php /* ================================================================ HERO */ ?>
<section class="hp3-hero" id="hp3-hero" aria-labelledby="hp-h1">
  <div class="hp3-stage" id="hp3-stage">
    <div class="hp3-bg" aria-hidden="true">
      <span class="hp3-orb hp3-orb--v"></span>
      <span class="hp3-orb hp3-orb--u"></span>
      <span class="hp3-grid"></span>
    </div>
    <canvas class="hp3-globe" id="hp3-globe" aria-hidden="true"
            data-markers="<?= e(json_encode($GLOBE, JSON_UNESCAPED_UNICODE)) ?>"></canvas>

    <div class="hp3-scenes">
      <?php /* Scene 1: positioning. The H1 paints immediately: it is the LCP
               element, so it is never hidden waiting for JavaScript. */ ?>
      <div class="hp3-scene hp3-scene--1 is-active" data-scene="0">
        <div class="hp3-copy">
          <p class="hp3-eyebrow"><span class="hp3-dot" aria-hidden="true"></span> B2B digital marketing &amp; web development</p>
          <h1 class="hp3-h1" id="hp-h1"><span class="hp3-h1-k">B2B digital marketing agency</span> Get found. Get chosen. Get <span class="hp3-grad">pipeline</span>.</h1>
          <p class="hp3-lead">VTurnU makes B2B companies visible on Google and inside AI answers, then turns that attention into <strong>qualified enquiries your sales team wants</strong>. For growing firms in India, the UAE, Singapore, the US, Canada and beyond.</p>
          <div class="hp3-actions">
            <a class="hp3-btn hp3-btn--primary" href="#start"><?= e($cta_label) ?> <span aria-hidden="true">&rarr;</span></a>
            <?php if (isset($PAGES['case-studies'])): ?>
            <a class="hp3-btn hp3-btn--ghost" href="/case-studies/">See client results</a>
            <?php endif; ?>
          </div>
          <ul class="hp3-ticks">
            <li>Senior strategists, never handed to juniors</li>
            <li>Reported on pipeline, not rankings</li>
            <li>Reply within 24 hours</li>
          </ul>
        </div>
      </div>

      <?php /* Scene 2: how B2B buying works now, with the 3D journey cards. */ ?>
      <div class="hp3-scene hp3-scene--2" data-scene="1">
        <div class="hp3-copy">
          <p class="hp3-eyebrow"><span class="hp3-dot" aria-hidden="true"></span> How B2B buying works now</p>
          <h2 class="hp3-h2">Buyers research for weeks before they talk to sales.</h2>
          <p class="hp3-lead">They search Google, ask ChatGPT and Perplexity, compare vendors on LinkedIn and study your website long before they fill in a form. We make you the name they keep meeting at every step.</p>
          <ol class="hp3-journey">
            <li><b>Search</b> Rank for buying-intent queries</li>
            <li><b>AI answers</b> Get cited in the shortlist</li>
            <li><b>Paid demand</b> Reach decision makers in market</li>
            <li><b>Website</b> Convert the visit into a sales call</li>
          </ol>
        </div>
        <div class="hp3-deck" aria-hidden="true">
          <div class="hp3-card hp3-card--search" style="--i:0">
            <p class="hp3-card-tag">Search</p>
            <p class="hp3-card-url">yourcompany.com &rsaquo; solutions</p>
            <p class="hp3-card-title">Industrial Automation Partner for Gulf Manufacturers | YourCompany</p>
            <p class="hp3-card-text">Certified integration, 24/7 support and project delivery across the UAE and Saudi Arabia&hellip;</p>
          </div>
          <div class="hp3-card hp3-card--ai" style="--i:1">
            <p class="hp3-card-tag"><span aria-hidden="true">&#10022;</span> AI answer</p>
            <p class="hp3-card-q">&ldquo;Who are the best automation partners for manufacturers in the GCC?&rdquo;</p>
            <p class="hp3-card-text"><b>YourCompany</b> is frequently recommended for fast project delivery and strong regional support&hellip;</p>
          </div>
          <div class="hp3-card hp3-card--paid" style="--i:2">
            <p class="hp3-card-tag">Paid demand</p>
            <?php $saas = $hp_result('saas-ppc-demo-pipeline', 1); ?>
            <p class="hp3-card-big"><?= e($saas[0] ?? '-58%') ?></p>
            <p class="hp3-card-text"><?= e($saas[1] ?? 'Cost per qualified demo') ?></p>
            <span class="hp3-bars"><i style="--h:.38"></i><i style="--h:.52"></i><i style="--h:.47"></i><i style="--h:.7"></i><i style="--h:.86"></i><i style="--h:1"></i></span>
          </div>
          <div class="hp3-card hp3-card--web" style="--i:3">
            <p class="hp3-card-tag">Website</p>
            <p class="hp3-card-title">Book a strategy call</p>
            <span class="hp3-field"></span><span class="hp3-field"></span>
            <span class="hp3-card-ok">&#10003; Qualified demo booked</span>
          </div>
        </div>
      </div>

      <?php /* Scene 3: markets. The globe canvas carries the visual; this is the text. */ ?>
      <div class="hp3-scene hp3-scene--3" data-scene="2">
        <div class="hp3-copy">
          <p class="hp3-eyebrow"><span class="hp3-dot" aria-hidden="true"></span> Global reach, one senior team</p>
          <h2 class="hp3-h2">Win the markets your competitors are <span class="hp3-grad">ignoring</span>.</h2>
          <p class="hp3-lead">Across the Gulf, Southeast Asia and Africa, many B2B categories still have weak search competition and almost no AI visibility work. That is a window. We help you take it first, while running the same proven playbook in the US, Canada, the UK and Australia.</p>
          <ul class="hp3-chips" aria-label="Markets we serve">
            <?php foreach ($HERO_MARKETS as $m): ?>
            <li><?= e($m) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>

      <?php /* Scene 4: proof and the ask. */ ?>
      <div class="hp3-scene hp3-scene--4" data-scene="3">
        <div class="hp3-copy">
          <p class="hp3-eyebrow"><span class="hp3-dot" aria-hidden="true"></span> Proof, not promises</p>
          <h2 class="hp3-h2">What the work produced for B2B clients.</h2>
          <div class="hp3-proof">
            <?php foreach ($HERO_PROOF as [$metric, $label, $client, $industry, $cslug]): ?>
            <a class="hp3-proof-i" href="<?= e(page_url('case-studies/' . $cslug)) ?>">
              <span class="hp3-proof-n" data-count="<?= e($metric) ?>"><?= e($metric) ?></span>
              <span class="hp3-proof-l"><?= e($label) ?></span>
              <span class="hp3-proof-w"><?= e($client) ?></span>
            </a>
            <?php endforeach; ?>
          </div>
          <div class="hp3-actions">
            <a class="hp3-btn hp3-btn--primary" href="#start"><?= e($cta_label) ?> <span aria-hidden="true">&rarr;</span></a>
            <a class="hp3-btn hp3-btn--ghost" href="#services">Explore services</a>
          </div>
        </div>
      </div>
    </div>

    <?php /* Scene navigation: only shown while the hero is pinned. */ ?>
    <nav class="hp3-rail" aria-label="Hero sections" hidden>
      <button type="button" class="hp3-rail-dot" data-go="0" aria-label="Get found, get chosen"></button>
      <button type="button" class="hp3-rail-dot" data-go="1" aria-label="How B2B buying works"></button>
      <button type="button" class="hp3-rail-dot" data-go="2" aria-label="Markets we serve"></button>
      <button type="button" class="hp3-rail-dot" data-go="3" aria-label="Client results"></button>
    </nav>
    <p class="hp3-cue" aria-hidden="true" hidden><span></span> Scroll to explore</p>
    <span class="hp3-progress" aria-hidden="true" hidden><i></i></span>
  </div>
</section>

<?php /* ============================================================ LOGOS */ ?>
<?php if ($LOGOS): ?>
<section class="hp3-logos" aria-label="Clients we work with">
  <p class="hp3-logos-h">Trusted by growing brands</p>
  <div class="hp3-marquee">
    <?php /* Printed twice so the loop meets itself; the copy is hidden from assistive tech. */ ?>
    <div class="hp3-marquee-track">
      <?php for ($pass = 0; $pass < 2; $pass++): ?>
        <?php foreach ($LOGOS as [$file, $name]): ?>
        <img src="/assets/img/clients/<?= e($file) ?>"
             <?= $pass === 0 ? 'alt="' . e($name) . '"' : 'alt="" aria-hidden="true"' ?>
             width="140" height="48" loading="lazy" decoding="async">
        <?php endforeach; ?>
      <?php endfor; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php /* ========================================================== PROBLEM */ ?>
<section class="hp3-sec" aria-labelledby="hp3-why-h">
  <div class="hp3-wrap">
    <div class="hp3-head" data-rise>
      <p class="hp3-kicker">Why B2B marketing stalls</p>
      <h2 class="hp3-title" id="hp3-why-h">More traffic is not the problem. <span class="hp3-grad">The wrong traffic is.</span></h2>
      <p class="hp3-sub">Most B2B companies we audit are not short of activity. They are short of a system that connects what marketing does to the conversations sales needs.</p>
    </div>
    <div class="hp3-pains" data-rise>
      <article class="hp3-pain" data-tilt>
        <span class="hp3-pain-n" aria-hidden="true">01</span>
        <h3>Visible to the wrong people</h3>
        <p>Rankings for broad keywords that bring students, job seekers and competitors. We target the searches and audiences that carry buying intent.</p>
      </article>
      <article class="hp3-pain" data-tilt>
        <span class="hp3-pain-n" aria-hidden="true">02</span>
        <h3>Invisible inside AI answers</h3>
        <p>Buyers ask AI assistants for shortlists now. If ChatGPT, Perplexity and Google AI do not mention you, you are not on the list being compared.</p>
      </article>
      <article class="hp3-pain" data-tilt>
        <span class="hp3-pain-n" aria-hidden="true">03</span>
        <h3>Leads sales will not touch</h3>
        <p>Form fills with no budget, no fit and no timeline. We qualify at the source and connect every enquiry to your CRM, so quality is visible.</p>
      </article>
    </div>
  </div>
</section>

<?php /* ========================================================= SERVICES */ ?>
<section class="hp3-sec hp3-sec--tint" id="services" aria-labelledby="hp3-svc-h">
  <div class="hp3-wrap">
    <div class="hp3-head" data-rise>
      <p class="hp3-kicker">What we do</p>
      <h2 class="hp3-title" id="hp3-svc-h">One team for the whole B2B growth engine</h2>
      <p class="hp3-sub">Digital marketing services and web development planned as one strategy, so every channel feeds the next instead of competing for budget.</p>
    </div>
    <div class="hp3-pillars" data-rise>
      <?php foreach ($PILLARS as $p): ?>
      <article class="hp3-pillar hp3-pillar--<?= e($p['accent']) ?>" data-tilt>
        <div class="hp3-pillar-in">
          <span class="hp3-pillar-ico hp3-ico--<?= e($p['key']) ?>" aria-hidden="true"></span>
          <h3 class="hp3-pillar-t">
            <?php if (isset($PAGES[$p['hub']])): ?>
            <a href="<?= e(page_url($p['hub'])) ?>"><?= e($p['title']) ?></a>
            <?php else: ?>
            <?= e($p['title']) ?>
            <?php endif; ?>
          </h3>
          <p class="hp3-pillar-d"><?= e($p['desc']) ?></p>
          <?php if ($p['links']): ?>
          <ul class="hp3-pillar-l">
            <?php foreach ($p['links'] as [$lslug, $label]): ?>
            <li><a href="<?= e(page_url($lslug)) ?>"><?= e($label) ?></a></li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <?php if (isset($PAGES['ai-visibility-tool'])): ?>
    <div class="hp3-band" data-rise>
      <p><b>Does ChatGPT recommend you, or your competitor?</b> Run the free AI visibility check and see which answer engines name your brand today.</p>
      <a class="hp3-btn hp3-btn--dark" href="/ai-visibility-tool/">Check my AI visibility</a>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php /* ========================================================== PROCESS */ ?>
<section class="hp3-sec hp3-sec--ink" aria-labelledby="hp3-eng-h">
  <div class="hp3-wrap">
    <div class="hp3-head" data-rise>
      <p class="hp3-kicker">How we work</p>
      <h2 class="hp3-title" id="hp3-eng-h">The pipeline engine: five stages, one accountable team</h2>
      <p class="hp3-sub">A repeatable system for B2B growth. You always know what is being done, why, and what it is worth.</p>
    </div>
    <ol class="hp3-stages">
      <?php foreach ($STAGES as $i => [$t, $d]): ?>
      <li class="hp3-stage-i" data-depth style="--k:<?= $i ?>">
        <span class="hp3-stage-n" aria-hidden="true"><?= sprintf('%02d', $i + 1) ?></span>
        <h3><?= e($t) ?></h3>
        <p><?= e($d) ?></p>
      </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>

<?php /* ========================================================== MARKETS */ ?>
<section class="hp3-sec" id="markets" aria-labelledby="hp3-mkt-h">
  <div class="hp3-wrap">
    <div class="hp3-head" data-rise>
      <p class="hp3-kicker">Where we grow B2B companies</p>
      <h2 class="hp3-title" id="hp3-mkt-h">Local strategy for every market, <span class="hp3-grad">one senior team</span></h2>
      <p class="hp3-sub">Search behaviour, competition and buying committees differ by country. Each program is planned for the market it has to win.</p>
    </div>
    <div class="hp3-regions" data-rise>
      <?php foreach ($REGIONS as [$name, $countries, $desc]): ?>
      <article class="hp3-region" data-tilt>
        <h3><?= e($name) ?></h3>
        <p class="hp3-region-c"><?= e($countries) ?></p>
        <p><?= e($desc) ?></p>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php /* ============================================================ PROOF */ ?>
<?php if ($CASE_CARDS): ?>
<section class="hp3-sec hp3-sec--tint" aria-labelledby="hp3-res-h">
  <div class="hp3-wrap">
    <div class="hp3-head" data-rise>
      <p class="hp3-kicker">Results</p>
      <h2 class="hp3-title" id="hp3-res-h">Outcomes our clients can take to the board</h2>
      <p class="hp3-sub">Different industries, different channels, the same discipline: measure what reaches the pipeline. Full write-ups, including what did not work, are on each case study.</p>
    </div>
    <div class="hp3-cases" data-rise>
      <?php foreach ($CASE_CARDS as [$metric, $label, $client, $industry, $cslug]): ?>
      <a class="hp3-case" href="<?= e(page_url('case-studies/' . $cslug)) ?>" data-tilt>
        <span class="hp3-case-ind"><?= e($industry) ?></span>
        <span class="hp3-case-n" data-count="<?= e($metric) ?>"><?= e($metric) ?></span>
        <span class="hp3-case-l"><?= e($label) ?></span>
        <span class="hp3-case-c"><?= e($client) ?></span>
        <span class="hp3-case-go">Read the case study <span aria-hidden="true">&rarr;</span></span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php /* ======================================================= WHY + FACTS */ ?>
<section class="hp3-sec" aria-labelledby="hp3-fit-h">
  <div class="hp3-wrap hp3-fit">
    <div data-rise>
      <p class="hp3-kicker">Why VTurnU</p>
      <h2 class="hp3-title" id="hp3-fit-h">Built for B2B teams that are measured on revenue</h2>
      <ul class="hp3-why">
        <li><b>Senior people on your account.</b> The strategists who pitch are the ones who do the work.</li>
        <li><b>AI search, native.</b> We optimize for ChatGPT, Perplexity, Gemini and Google AI Overviews alongside classic SEO.</li>
        <li><b>Pipeline reporting.</b> Live dashboards and monthly reviews tied to qualified leads and revenue.</li>
        <li><b>Marketing and web in one team.</b> No hand-offs between an agency and a separate developer.</li>
        <li><b>No lock-in.</b> Month to month after ninety days, and you own every account and asset.</li>
      </ul>
    </div>
    <aside class="hp3-facts" data-rise data-tilt aria-labelledby="hp3-facts-h">
      <h3 id="hp3-facts-h">VTurnU at a glance</h3>
      <dl>
        <div><dt>What we are</dt><dd>B2B digital marketing and web development agency</dd></div>
        <div><dt>Founded</dt><dd><?= e(FOUNDING_YEAR) ?></dd></div>
        <div><dt>Headquarters</dt><dd>Chennai, India</dd></div>
        <div><dt>Team</dt><dd>25+ specialists</dd></div>
        <div><dt>Delivered</dt><dd>150+ projects in 10+ industries</dd></div>
        <div><dt>Markets</dt><dd>India, UAE, Saudi Arabia, Qatar, Singapore, Malaysia, Indonesia, Australia, UK, US, Canada, Africa</dd></div>
        <div><dt>Engagements</dt><dd>From ₹35,000 / month, no setup fees</dd></div>
        <div><dt>Response time</dt><dd>Within 24 hours</dd></div>
      </dl>
    </aside>
  </div>
</section>

<?php /* ============================================================= FORM */ ?>
<section class="hp3-sec hp3-sec--ink hp3-lead-sec" id="start" aria-labelledby="hp-form-h">
  <div class="hp3-wrap hp3-formwrap">
    <div class="hp3-form-intro" data-rise>
      <p class="hp3-kicker">Free B2B growth plan</p>
      <h2 class="hp3-title" id="hp-form-h">Tell us where you want to grow. We will show you how.</h2>
      <p class="hp3-sub">Answer a few questions, about two minutes. Within one working day a senior strategist sends you a specific read on your visibility, your competitors and the fastest route to qualified pipeline.</p>
    </div>
    <div class="hp3-form-aside" data-rise>
      <ul class="hp3-get">
        <li>Where you rank and get cited today, against the competitors winning your deals</li>
        <li>Which channels and markets will produce qualified leads fastest</li>
        <li>The first three moves we would make, and what each is worth</li>
      </ul>
      <p class="hp3-fitnote"><b>Best fit:</b> B2B companies with a clear offer and the ability to invest from ₹35,000 (about USD 420) a month in growth.</p>
    </div>

    <div class="hp3-form-card" data-rise>
      <?php if ($form_status === 'success'): ?>
      <div class="hp3-form-ok" role="status">
        <p><strong>Thank you.</strong> Your request is in. A strategist will reply within one working day.</p>
      </div>
      <?php else: ?>
      <form class="hp3-form" id="hp-form" method="post" action="/#start" novalidate
            data-recaptcha="<?= e(RECAPTCHA_SITE_KEY) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="recaptcha_token" class="js-recaptcha-token">
        <input type="hidden" name="form_variant" value="b2b-qualifier-v1">
        <div class="hp3-hp" aria-hidden="true">
          <label for="f-website">Website</label>
          <input id="f-website" name="website" type="text" tabindex="-1" autocomplete="off">
        </div>

        <div class="hp3-steps" hidden>
          <span class="hp3-steps-bar"><i></i></span>
          <p class="hp3-steps-t" aria-live="polite">Step <b class="js-step-n">1</b> of 3</p>
        </div>

        <?php if ($form_status === 'error'): ?>
        <p class="hp3-form-bad" role="alert">Something did not go through. Check your name and work email, then send it again, or email <?= e(CONTACT_EMAIL) ?>.</p>
        <?php endif; ?>

        <fieldset class="hp3-step" data-step="1">
          <legend>What do you need?</legend>
          <div class="hp3-chipset" role="group" aria-label="Services you are interested in">
            <?php foreach ($FORM_SERVICES as $i => $s): ?>
            <label class="hp3-chip"><input type="checkbox" name="services[]" value="<?= e($s) ?>"><span><?= e($s) ?></span></label>
            <?php endforeach; ?>
          </div>
          <span class="hp3-err" data-err-for="services"></span>
          <p class="hp3-q">Which markets do you want to win?</p>
          <div class="hp3-chipset" role="group" aria-label="Target markets">
            <?php foreach ($FORM_MARKETS as $m): ?>
            <label class="hp3-chip"><input type="checkbox" name="markets[]" value="<?= e($m) ?>"><span><?= e($m) ?></span></label>
            <?php endforeach; ?>
          </div>
        </fieldset>

        <fieldset class="hp3-step" data-step="2">
          <legend>About your company</legend>
          <div class="hp3-row">
            <div class="hp3-f">
              <label for="f-company">Company name</label>
              <input id="f-company" name="company" type="text" autocomplete="organization" placeholder="Acme Industries" required>
              <span class="hp3-err" data-err-for="f-company"></span>
            </div>
            <div class="hp3-f">
              <label for="f-site">Company website</label>
              <input id="f-site" name="company_website" type="text" inputmode="url" autocomplete="url" placeholder="acme.com" required>
              <span class="hp3-err" data-err-for="f-site"></span>
            </div>
          </div>
          <div class="hp3-row">
            <div class="hp3-f">
              <label for="f-hq">Headquartered in</label>
              <select id="f-hq" name="hq_country">
                <?php foreach ($hq_names as $iso => $cname): ?>
                <option value="<?= e($cname) ?>"<?= $cname === $hq_selected ? ' selected' : '' ?>><?= e($cname) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="hp3-f">
              <label for="f-size">Company size</label>
              <select id="f-size" name="company_size" required>
                <option value="">Select</option>
                <?php foreach ($FORM_SIZE as $o): ?><option><?= e($o) ?></option><?php endforeach; ?>
              </select>
              <span class="hp3-err" data-err-for="f-size"></span>
            </div>
          </div>
          <div class="hp3-row">
            <div class="hp3-f">
              <label for="f-budget">Monthly marketing budget</label>
              <select id="f-budget" name="budget" required>
                <option value="">Select</option>
                <?php foreach ($FORM_BUDGET as $o): ?><option><?= e($o) ?></option><?php endforeach; ?>
              </select>
              <span class="hp3-err" data-err-for="f-budget"></span>
            </div>
            <div class="hp3-f">
              <label for="f-when">When do you want to start?</label>
              <select id="f-when" name="timeline" required>
                <option value="">Select</option>
                <?php foreach ($FORM_WHEN as $o): ?><option><?= e($o) ?></option><?php endforeach; ?>
              </select>
              <span class="hp3-err" data-err-for="f-when"></span>
            </div>
          </div>
        </fieldset>

        <fieldset class="hp3-step" data-step="3">
          <legend>Where should we send your plan?</legend>
          <div class="hp3-row">
            <div class="hp3-f">
              <label for="f-name">Your name</label>
              <input id="f-name" name="name" type="text" autocomplete="name" placeholder="Priya Raman" required>
              <span class="hp3-err" data-err-for="f-name"></span>
            </div>
            <div class="hp3-f">
              <label for="f-role">Your role</label>
              <input id="f-role" name="designation" type="text" autocomplete="organization-title" placeholder="Head of Marketing">
            </div>
          </div>
          <div class="hp3-f">
            <label for="f-email">Work email</label>
            <input id="f-email" name="email" type="email" autocomplete="email" placeholder="priya@acme.com" required>
            <span class="hp3-err" data-err-for="f-email"></span>
          </div>
          <div class="hp3-f">
            <label for="f-mobile">Phone or WhatsApp <span class="hp3-opt">Optional</span></label>
            <div class="hp3-phone">
              <?= country_code_select('country_code', 'f-cc') ?>
              <input id="f-mobile" name="mobile" type="tel" autocomplete="tel-national" inputmode="tel" placeholder="Phone number">
            </div>
          </div>
          <div class="hp3-f">
            <label for="f-message">Your biggest growth challenge <span class="hp3-opt">Optional</span></label>
            <textarea id="f-message" name="message" rows="3" placeholder="We want more qualified RFQs from the GCC, but most enquiries today are small domestic orders."></textarea>
          </div>
        </fieldset>

        <div class="hp3-nav">
          <button type="button" class="hp3-btn hp3-btn--ghost js-back" hidden>Back</button>
          <button type="button" class="hp3-btn hp3-btn--primary js-next" hidden>Continue <span aria-hidden="true">&rarr;</span></button>
          <button type="submit" class="hp3-btn hp3-btn--primary js-submit">Send my growth plan request</button>
        </div>
        <p class="hp3-fine">No spam and no sales sequence. Your details are used only to prepare your plan. <a href="/privacy-policy/">Privacy policy</a></p>
      </form>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php /* ============================================================== FAQ */ ?>
<section class="hp3-sec" aria-labelledby="hp3-faq-h">
  <div class="hp3-wrap hp3-faqwrap">
    <div class="hp3-head" data-rise>
      <p class="hp3-kicker">Questions</p>
      <h2 class="hp3-title" id="hp3-faq-h">What B2B teams ask before they start</h2>
    </div>
    <div class="hp3-faq" data-rise>
      <?php foreach ($FAQS as $k => [$q, $a]): ?>
      <details class="hp3-faq-i"<?= $k === 0 ? ' open' : '' ?>>
        <summary><?= e($q) ?></summary>
        <p class="answer-text"><?= e($a) ?></p>
      </details>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php /* ======================================================== FINAL CTA */ ?>
<section class="hp3-final" aria-labelledby="hp3-final-h">
  <div class="hp3-wrap" data-rise>
    <h2 id="hp3-final-h">Your competitors are one good quarter away from owning your market's search results. <span class="hp3-grad">Make it you.</span></h2>
    <div class="hp3-actions">
      <a class="hp3-btn hp3-btn--primary" href="#start"><?= e($cta_label) ?> <span aria-hidden="true">&rarr;</span></a>
      <a class="hp3-btn hp3-btn--ghost" href="https://wa.me/<?= e(CONTACT_WHATSAPP) ?>?text=<?= rawurlencode('Hi VTurnU, I would like to discuss B2B growth.') ?>" target="_blank" rel="noopener">Talk on WhatsApp</a>
    </div>
  </div>
</section>

<?php
/* Homepage schema. The shared jsonld_site() is skipped in header.php when the
   slug is empty, so Organization is defined exactly once, here. */
$org_id = abs_url('/') . '#organization';
$service_offers = [];
foreach ($PILLARS as $p) {
    foreach ($p['links'] as [$lslug, $label]) {
        $service_offers[] = [
            '@type' => 'Offer',
            'itemOffered' => ['@type' => 'Service', 'name' => $label, 'url' => abs_url(page_url($lslug)), 'category' => $p['title']],
        ];
    }
}
echo jsonld_script([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => ['Organization', 'ProfessionalService'],
            '@id' => $org_id,
            'name' => SITE_NAME,
            'url' => abs_url('/'),
            'logo' => abs_url('/assets/img/vturnu-logo-dark.png'),
            'image' => abs_url('/assets/img/vturnu-logo-dark.png'),
            'description' => 'VTurnU is a B2B digital marketing and web development agency headquartered in Chennai, India. It provides SEO, AI search optimization, Google Ads, demand generation and conversion-focused websites for B2B companies in India, the Middle East, Southeast Asia, North America, Europe, Africa and Australia.',
            'slogan' => SITE_TAGLINE,
            'email' => CONTACT_EMAIL,
            'telephone' => CONTACT_PHONE,
            'priceRange' => '₹35,000+ per month',
            'areaServed' => array_map(static function (string $c): array {
                return ['@type' => 'Country', 'name' => $c];
            }, ['India', 'United Arab Emirates', 'Saudi Arabia', 'Qatar', 'Singapore', 'Malaysia', 'Indonesia', 'Australia', 'United Kingdom', 'United States', 'Canada', 'South Africa', 'Nigeria', 'Kenya']),
            'address' => ['@type' => 'PostalAddress', 'addressLocality' => 'Chennai', 'addressRegion' => 'Tamil Nadu', 'addressCountry' => 'IN'],
            'contactPoint' => [[
                '@type' => 'ContactPoint',
                'contactType' => 'sales',
                'email' => CONTACT_EMAIL,
                'telephone' => CONTACT_PHONE,
                'availableLanguage' => ['English', 'Tamil', 'Hindi'],
                'areaServed' => 'Worldwide',
            ]],
            'hasOfferCatalog' => [
                '@type' => 'OfferCatalog',
                'name' => 'B2B digital marketing and web development services',
                'itemListElement' => $service_offers,
            ],
        ] + org_entity_facts(),
        [
            '@type' => 'WebSite',
            '@id' => abs_url('/') . '#website',
            'url' => abs_url('/'),
            'name' => SITE_NAME,
            'inLanguage' => 'en',
            'publisher' => ['@id' => $org_id],
        ],
        [
            '@type' => 'WebPage',
            '@id' => abs_url('/') . '#webpage',
            'url' => abs_url('/'),
            'name' => $page['title'],
            'description' => $page['meta'],
            'inLanguage' => 'en',
            'isPartOf' => ['@id' => abs_url('/') . '#website'],
            'about' => ['@id' => $org_id],
            'primaryImageOfPage' => abs_url('/assets/img/vturnu-logo-dark.png'),
            'speakable' => ['@type' => 'SpeakableSpecification', 'cssSelector' => ['.hp3-h1', '.hp3-scene--1 .hp3-lead', '.hp3-facts']],
        ],
        [
            '@type' => 'FAQPage',
            '@id' => abs_url('/') . '#faq',
            'mainEntity' => array_map(static function (array $f): array {
                return [
                    '@type' => 'Question',
                    'name' => $f[0],
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]],
                ];
            }, $FAQS),
        ],
    ],
]) . "\n";
?>
