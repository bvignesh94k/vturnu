<?php
/**
 * VTurnU homepage: an immersive B2B lead-generation page.
 *
 * Expects: $page, $slug, $PAGES, $form_status, $visitor_dial_code
 *
 * The hero is the tagline made literal: a crowd of 3D busts that turn toward the
 * visitor (assets/js/hero-crowd.js). A prerendered poster of the same scene shows
 * first, so the page is complete with or without JavaScript or WebGL.
 *
 * Nothing here is invented: service links render only when the page exists, and
 * results are read live from includes/data/cases.php.
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

$LOGOS = array_values(array_filter([
    ['shineprints.webp',           'Shine Prints'],
    ['masaami.webp',               'Masaami'],
    ['ateliers-gym.png',           'Ateliers Gym'],
    ['sai-impression.webp',        'Sai Impression'],
    ['black-sheep-collective.png', 'The Black Sheep Collective'],
    ['firstchoice-autoparts.webp', 'FirstChoice Used Auto Parts'],
    ['pemotech.png',               'Pemotech'],
], static function (array $l): bool {
    return is_file(BASE_PATH . '/assets/img/clients/' . $l[0]);
}));

$FLIPS = [
    ['Traffic that never buys', 'Broad keywords bring students, job seekers and competitors to your site. The graphs go up. Revenue does not.',
     'Buyers who book calls', 'We target the searches and audiences that carry budget and intent, then measure success in meetings, not sessions.'],
    ['Missing from AI shortlists', 'Buyers now ask ChatGPT and Perplexity who to hire. If the answer names three competitors, you were never in the running.',
     'Named in the answer', 'Entity signals, structured data, third-party citations and content written the way buyers ask, so assistants recommend you.'],
    ['Leads sales ignores', 'Form fills with no budget, no fit and no timeline. Sales stops trusting marketing, and marketing stops getting budget.',
     'Leads sales fights over', 'Qualified at the source, graded on arrival and tracked to revenue in your CRM. Quality becomes visible, and fundable.'],
];

$ENGINES = [
    [
        'key' => 'search', 'title' => 'Search & AI visibility', 'tag' => 'Be the answer',
        'desc' => 'Technical SEO, content and entity work that earn rankings on Google and citations inside ChatGPT, Perplexity, Gemini and Google AI Overviews.',
        'links' => $hp_existing([
            ['seo-services', 'SEO services'], ['ai-seo', 'AI search optimization'], ['google-ai-overviews-seo', 'AI Overviews SEO'],
            ['chatgpt-seo', 'ChatGPT SEO'], ['lead-generation-seo', 'Lead generation SEO'], ['enterprise-seo', 'Enterprise SEO'],
        ]),
    ],
    [
        'key' => 'demand', 'title' => 'Demand generation', 'tag' => 'Be in front of buyers',
        'desc' => 'Paid search, paid social, outbound and email built around your ideal customer profile, with every rupee and dollar traced to qualified pipeline.',
        'links' => $hp_existing([
            ['google-ads', 'Google Ads management'], ['enterprise-ppc', 'Enterprise PPC'], ['outbound-marketing', 'Outbound marketing'],
            ['email-marketing', 'Email marketing'], ['content-marketing', 'B2B content marketing'], ['social-media-marketing', 'Social media marketing'],
        ]),
    ],
    [
        'key' => 'web', 'title' => 'Web development & CRO', 'tag' => 'Be the obvious choice',
        'desc' => 'Fast, secure, search-ready websites and landing pages engineered to convert a buying committee, not just a casual visitor.',
        'links' => $hp_existing([
            ['web-design', 'Website design'], ['lead-gen-web-design', 'Lead generation websites'], ['custom-design-and-development', 'Custom development'],
            ['headless-web-design', 'Headless websites'], ['ecommerce-web-design', 'Ecommerce websites'], ['ai-development', 'AI development & automation'],
        ]),
    ],
];

$MOVES = [
    ['Decode', 'We map who actually buys, how they search and which competitors are winning your deals, in search results and in AI answers.'],
    ['Position', 'We sharpen the offer for each market and build pages that answer a buying committee\'s objections before sales has to.'],
    ['Surface', 'Technical SEO, content and entity work put you on page one and inside the AI shortlist for the questions that matter.'],
    ['Ignite', 'Paid search, paid social, outbound and email reach decision makers in market now, at a cost per lead that makes sense.'],
    ['Compound', 'Conversion work, CRM attribution and monthly reviews move budget to what produces pipeline, so results stack every quarter.'],
];

$CASE_CARDS = array_values(array_filter([
    $hp_result('firstchoice-used-auto-parts'),
    $hp_result('sai-impression-label-manufacturer'),
    $hp_result('floweazy-odoo-partner-website', 1),
    $hp_result('cafy-careers-uk-job-platform'),
]));


$hq_names = country_names();
$hq_selected = $hq_names[security_visitor_country()] ?? 'India';
asort($hq_names);

$FAQS = [
    ['Do you work with companies outside India?',
     'Yes. We are headquartered in Chennai and work with B2B companies around the world. Strategy, reporting and calls run in English, and we schedule calls in your time zone.'],
    ['What kind of companies do you work best with?',
     'B2B companies with a clear offer and a sales team that needs better conversations: manufacturers and exporters, SaaS and technology firms, professional services, healthcare groups and real estate developers, typically between 10 and 1,000 people.'],
    ['How do you make sure the leads are good quality?',
     'We start from your ideal customer profile, target buying-intent searches and decision-maker audiences, qualify enquiries at the form with company, budget and timeline questions, and connect everything to your CRM. We report on qualified leads and pipeline, not on clicks or raw form fills.'],
    ['How soon will we see results?',
     'Paid campaigns can produce qualified enquiries in the first month. SEO usually shows movement by week eight and compounds from there. AI search visibility often moves fastest, because far fewer competitors are working on it yet.'],
    ['What does it cost?',
     'Projects start from ' . min_budget() . '. We confirm the exact scope after a free audit, because the work depends on your market and starting point.'],
    ['What is AI search optimization, and why does it matter for B2B?',
     'Buyers now ask ChatGPT, Perplexity, Gemini and Google AI Overviews for vendor shortlists. AI search optimization makes your company one of the names those answers cite, using clean entity signals, structured data, third-party mentions and content written the way people ask questions.'],
    ['Can you build or rebuild our website as well?',
     'Yes. We design and develop fast, secure, search-ready websites and landing pages, including custom, headless, WordPress and ecommerce builds, so marketing and the site are planned together instead of fighting each other.'],
    ['Do we have to sign a long contract?',
     'No. After an initial ninety day window, which is the minimum honest time to judge a search program, we work month to month. You keep ownership of every account, asset and piece of content.'],
];

$cta_label = 'Start my growth plan';
?>

<?php /* ================================================================ HERO */ ?>
<section class="tt-hero" id="tt-hero" aria-labelledby="hp-h1">
  <picture class="tt-poster" aria-hidden="true">
    <source media="(max-width: 760px)" srcset="/assets/img/hero-crowd-m.webp" width="780" height="1374">
    <img src="/assets/img/hero-crowd.webp" alt="" width="1800" height="965" fetchpriority="high">
  </picture>
  <canvas class="tt-crowd" id="tt-crowd" aria-hidden="true" data-src="/assets/js/hero-crowd.js?v=<?= asset_v('/assets/js/hero-crowd.js') ?>"></canvas>

  <div class="tt-copy">
    <h1 class="tt-h1" id="hp-h1"><span class="tt-k">B2B digital marketing agency</span> Make your market <span class="tt-turn">turn its head.</span></h1>
    <p class="tt-lead">VTurnU makes B2B companies visible on Google and inside AI answers, then turns that attention into <strong>qualified enquiries your sales team wants</strong>.</p>
    <div class="tt-actions">
      <a class="tt-cta hp3-btn--primary" href="#start" data-crowd-look><?= e($cta_label) ?> <span aria-hidden="true">&rarr;</span></a>
      <?php if (isset($PAGES['case-studies'])): ?>
      <a class="tt-link" href="/case-studies/">See the results</a>
      <?php endif; ?>
    </div>
    <?php render_trust_badges('hero', 'Rated and listed on', true); ?>
  </div>

  <p class="tt-score" aria-hidden="true">
    <span class="tt-tally"><b id="tt-n">0</b> of <span id="tt-total">0</span> heads turned</span>
    <span class="tt-hint tt-hint-pointer">Move your cursor</span>
    <span class="tt-hint tt-hint-touch">Tap or scroll</span>
  </p>
</section>

<?php /* ============================================================ LOGOS */ ?>
<?php if ($LOGOS): ?>
<section class="hp3-logos" aria-label="Clients we work with">
  <p class="hp3-logos-h">Growing brands that turned with us</p>
  <div class="hp3-marquee">
    <div class="hp3-marquee-track">
      <?php for ($pass = 0; $pass < 2; $pass++): ?>
        <?php foreach ($LOGOS as [$file, $name]): ?>
        <span class="hp3-logo"><?= client_logo_img($file, $name, $pass !== 0, 164, 56, 6000) ?></span>
        <?php endforeach; ?>
      <?php endfor; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php /* ================================================== SIGNAL OVER NOISE */ ?>
<section class="hp3-sec hp3-signal" aria-labelledby="hp3-sig-h">
  <div class="hp3-wrap">
    <div class="hp3-head" data-rise>
      <p class="hp3-kicker">Signal over noise</p>
      <h2 class="hp3-title" id="hp3-sig-h">Most B2B brands are loud in <span class="hp3-grad">the wrong rooms.</span></h2>
      <p class="hp3-sub">Three habits quietly drain B2B budgets. Hover or tap a card to see the turn we make.</p>
    </div>
    <div class="hp3-flips" data-rise>
      <?php foreach ($FLIPS as $i => [$ft, $fd, $bt, $bd]): ?>
      <div class="hp3-flip" style="--i:<?= $i ?>">
        <button type="button" class="hp3-flip-in" aria-pressed="false">
          <span class="hp3-face hp3-face--front">
            <span class="hp3-face-n" aria-hidden="true">0<?= $i + 1 ?></span>
            <span class="hp3-face-k">The leak</span>
            <span class="hp3-face-t"><?= e($ft) ?></span>
            <span class="hp3-face-d"><?= e($fd) ?></span>
            <svg class="hp3-face-go" viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path d="M20 12a8 8 0 1 1-2.6-5.9M20 4v5h-5" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </span>
          <span class="hp3-face hp3-face--back">
            <span class="hp3-face-k">The turn</span>
            <span class="hp3-face-t"><?= e($bt) ?></span>
            <span class="hp3-face-d"><?= e($bd) ?></span>
          </span>
        </button>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php /* ===================================================== GROWTH STACK */ ?>
<section class="hp3-sec hp3-sec--ink hp3-stack-sec" id="stack" aria-labelledby="hp3-stack-h">
  <div class="hp3-wrap">
    <div class="hp3-head" data-rise>
      <p class="hp3-kicker">The growth stack</p>
      <h2 class="hp3-title" id="hp3-stack-h">Three engines. One pipeline.</h2>
      <p class="hp3-sub">Search, demand and the website, run by one team on one plan, so every layer feeds the next instead of fighting for budget.</p>
    </div>
    <div class="hp3-stack">
      <div class="hp3-iso" aria-hidden="true">
        <div class="hp3-iso-in">
          <?php foreach (array_reverse($ENGINES, true) as $i => $en): ?>
          <div class="hp3-slab hp3-slab--<?= e($en['key']) ?>" data-slab="<?= e($en['key']) ?>" style="--k:<?= $i ?>">
            <span><?= e($en['tag']) ?></span>
          </div>
          <?php endforeach; ?>
          <div class="hp3-slab-core"></div>
        </div>
      </div>
      <div class="hp3-engines">
        <?php foreach ($ENGINES as $i => $en): ?>
        <article class="hp3-engine" data-engine="<?= e($en['key']) ?>" data-rise>
          <p class="hp3-engine-k">Layer 0<?= $i + 1 ?> &middot; <?= e($en['tag']) ?></p>
          <h3><?= e($en['title']) ?></h3>
          <p><?= e($en['desc']) ?></p>
          <?php if ($en['links']): ?>
          <ul>
            <?php foreach ($en['links'] as [$lslug, $label]): ?>
            <li><a href="<?= e(page_url($lslug)) ?>"><?= e($label) ?></a></li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>
        </article>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<?php /* ===================================================== TURN METHOD */ ?>
<section class="hp3-sec hp3-method" aria-labelledby="hp3-method-h">
  <div class="hp3-wrap">
    <div class="hp3-head" data-rise>
      <p class="hp3-kicker">The Turn Method</p>
      <h2 class="hp3-title" id="hp3-method-h">Five moves from invisible to <span class="hp3-grad">inevitable.</span></h2>
    </div>
    <ol class="hp3-moves">
      <?php foreach ($MOVES as $i => [$t, $d]): ?>
      <li class="hp3-move" style="--k:<?= $i ?>">
        <span class="hp3-move-n" aria-hidden="true"><?= sprintf('%02d', $i + 1) ?></span>
        <h3><?= e($t) ?></h3>
        <p><?= e($d) ?></p>
      </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>

<?php /* ========================================================= RECEIPTS */ ?>
<?php if ($CASE_CARDS): ?>
<section class="hp3-sec hp3-sec--tint" aria-labelledby="hp3-res-h">
  <div class="hp3-wrap">
    <div class="hp3-head" data-rise>
      <p class="hp3-kicker">Receipts</p>
      <h2 class="hp3-title" id="hp3-res-h">Numbers we would happily show your CFO.</h2>
      <p class="hp3-sub">Named clients and live websites. Each case study shows where the number comes from and what it does not prove.</p>
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

<?php /* ===================================================== SHORT VERSION */ ?>
<section class="hp3-sec" aria-labelledby="hp3-fit-h">
  <div class="hp3-wrap hp3-fit">
    <div data-rise>
      <p class="hp3-kicker">The short version</p>
      <h2 class="hp3-title" id="hp3-fit-h">Senior people. Real pipeline. No lock-in.</h2>
      <ul class="hp3-why">
        <li><b>The people who pitch do the work.</b> No hand-off to a junior team after the contract is signed.</li>
        <li><b>AI search is native, not an add-on.</b> ChatGPT, Perplexity, Gemini and AI Overviews are planned alongside classic SEO.</li>
        <li><b>Marketing and web, one team.</b> No finger-pointing between an agency and a separate developer.</li>
        <li><b>Live dashboards, monthly reviews.</b> Reported in qualified leads and revenue, not vanity metrics.</li>
        <li><b>Month to month after ninety days.</b> You own every account, asset and piece of content.</li>
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
        <div><dt>Minimum budget</dt><dd><?= e(min_budget()) ?></dd></div>
        <div><dt>Response time</dt><dd>Within 24 hours</dd></div>
      </dl>
    </aside>
  </div>
</section>

<?php /* ============================================================= FORM */ ?>
<section class="hp3-sec hp3-sec--ink hp3-lead-sec" id="start" aria-labelledby="hp-form-h">
  <div class="hp3-wrap hp3-formwrap">
    <div class="hp3-form-intro" data-rise>
      <p class="hp3-kicker">Start the turn</p>
      <h2 class="hp3-title" id="hp-form-h">Get a growth plan built for your market.</h2>
      <p class="hp3-sub">One short form. Within one working day a senior strategist sends a specific read on your visibility, your competitors and the fastest route to qualified pipeline.</p>
    </div>
    <div class="hp3-form-aside" data-rise>
      <ul class="hp3-get">
        <li>Where you rank and get cited today, against the competitors winning your deals</li>
        <li>Which channels and markets will produce qualified leads fastest</li>
        <li>The first three moves we would make, and what each is worth</li>
      </ul>
      <p class="hp3-fitnote"><b>Best fit:</b> B2B companies with a clear offer and the ability to invest from <?= e(min_budget()) ?> a month in growth.</p>
    </div>

    <div class="hp3-form-card" data-rise>
      <h3 class="lf-card-h">Tell us about your project</h3>
      <?php render_lead_form_card(['uid' => 'hp', 'source' => 'home-quote', 'service' => lf_old('service', ''), 'button' => $cta_label]); ?>
    </div>
  </div>
</section>

<?php /* ================================================= STRAIGHT ANSWERS */ ?>
<section class="hp3-sec" aria-labelledby="hp3-faq-h">
  <div class="hp3-wrap hp3-faqwrap">
    <div class="hp3-head" data-rise>
      <p class="hp3-kicker">Straight answers</p>
      <h2 class="hp3-title" id="hp3-faq-h">What founders and CMOs ask us first</h2>
    </div>
    <div class="hp3-faq" data-rise>
      <?php foreach ($FAQS as $k => [$q, $a]): ?>
      <details class="hp3-faq-i" name="faq"<?= $k === 0 ? ' open' : '' ?>>
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
      <a class="hp3-btn hp3-btn--ghost" href="https://wa.me/<?= e(CONTACT_WHATSAPP) ?>?text=<?= rawurlencode('Hi VTurnU, I would like to discuss B2B growth.') ?>" target="_blank" rel="noopener nofollow">Talk on WhatsApp</a>
    </div>
  </div>
</section>

<?php
/* Homepage schema. The shared jsonld_site() is skipped in header.php when the
   slug is empty, so Organization is defined exactly once, here. */
$org_id = abs_url('/') . '#organization';
$service_offers = [];
foreach ($ENGINES as $en) {
    foreach ($en['links'] as [$lslug, $label]) {
        $service_offers[] = [
            '@type' => 'Offer',
            'itemOffered' => ['@type' => 'Service', 'name' => $label, 'url' => abs_url(page_url($lslug)), 'category' => $en['title']],
        ];
    }
}
echo jsonld_script([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            /* Plain Organization, the type every other page uses (owner, 9 Nov
               2026). Not ProfessionalService, which schema.org deprecated, and
               not LocalBusiness, which would need the street address. That is
               also why there is no priceRange: it belongs to LocalBusiness. */
            '@type' => 'Organization',
            '@id' => $org_id,
            'name' => SITE_NAME,
            'url' => abs_url('/'),
            'logo' => abs_url('/assets/img/vturnu-logo-dark.png'),
            'image' => abs_url('/assets/img/vturnu-logo-dark.png'),
            'description' => 'VTurnU is a B2B digital marketing and web development agency headquartered in Chennai, India. It provides SEO, AI search optimization, Google Ads, demand generation and conversion-focused websites for B2B companies worldwide.',
            'slogan' => SITE_TAGLINE,
            'email' => CONTACT_EMAIL,
            'telephone' => CONTACT_PHONE,
            'areaServed' => array_map(static fn($c) => ['@type' => 'Country', 'name' => $c], TARGET_MARKETS),
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
            'speakable' => ['@type' => 'SpeakableSpecification', 'cssSelector' => ['.tt-h1', '.tt-lead', '.hp3-facts']],
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
