<?php
/**
 * VTurnU homepage: an immersive B2B lead-generation page.
 *
 * Expects: $page, $slug, $PAGES, $form_status, $visitor_dial_code
 *
 * The hero is a pinned WebGL stage (bin/hero3d/hero3d.js, built to
 * assets/js/hero3d.min.js). Scroll plays four scenes while the section holds
 * the viewport: the glass VU logo, a lead funnel, an interactive opportunity
 * globe and a portal ring. Every scene, funnel stage and market insight is
 * real HTML in source order, so crawlers, screen readers and visitors without
 * WebGL or JavaScript get the full content as ordinary sections.
 *
 * Nothing here is invented: service links render only when the page exists,
 * results are read live from includes/data/cases.php, and the market cards
 * state time zones, languages and our recommended starting channels, not
 * statistics we cannot source.
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

$HERO_PROOF = array_values(array_filter([
    $hp_result('saas-ppc-demo-pipeline'),
    $hp_result('manufacturer-rfq-seo-program'),
    $hp_result('law-firm-lead-gen-website'),
]));

/* Funnel stages, anchored to heights on the 3D funnel (1 = rim, 0 = tip). */
$FUNNEL = [
    ['0.92', 'l', 'Search',             'Rank for the queries that carry budget'],
    ['0.68', 'r', 'AI answers',         'Get named when buyers ask for a shortlist'],
    ['0.45', 'l', 'Paid demand',        'Reach decision makers who are in market now'],
    ['0.22', 'r', 'Website',            'Turn the visit into a booked call'],
    ['0.02', 'r', 'Qualified pipeline', 'Tracked in your CRM, reported in revenue'],
];

/* Opportunity map. [code, country, city, lat, lon, label side, time vs India,
   languages, the opening, where we would start]. Time differences are
   against IST (UTC+5:30); ranges cover daylight saving. */
$MARKETS = [
    ['IND', 'India',          'Chennai HQ',   13.08,   80.27, 'r', 'Home base, UTC+5:30', 'English, Hindi, Tamil and regional languages', 'Our home market, with deep work across manufacturing, SaaS, healthcare, real estate and services.', 'Full growth program'],
    ['UAE', 'United Arab Emirates', 'Dubai', 25.20,  55.27, 'r', '1.5 hours behind India', 'Arabic, English', 'High-value B2B categories where search results are still thin and mostly English-only.', 'Google Ads and bilingual SEO'],
    ['KSA', 'Saudi Arabia',   'Riyadh',       24.71,   46.68, 'l', '2.5 hours behind India', 'Arabic, English', 'Fast-growing B2B demand with few vendors optimizing seriously for Arabic search.', 'Arabic and English SEO, then paid search'],
    ['QAT', 'Qatar',          'Doha',         25.29,   51.53, 't', '2.5 hours behind India', 'Arabic, English', 'A compact, high-value market where a handful of strong pages can lead a category.', 'SEO and targeted outbound'],
    ['SGP', 'Singapore',      'Singapore',     1.35,  103.82, 'r', '2.5 hours ahead of India', 'English, Mandarin, Malay, Tamil', 'Regional headquarters that buy for the whole of Asia Pacific.', 'SEO, AI visibility and paid social'],
    ['MYS', 'Malaysia',       'Kuala Lumpur',  3.14,  101.69, 't', '2.5 hours ahead of India', 'Malay, English', 'Manufacturing and services buyers who research in both English and Malay.', 'SEO and Google Ads'],
    ['IDN', 'Indonesia',      'Jakarta',      -6.20,  106.85, 'b', '1.5 hours ahead of India', 'Bahasa Indonesia, English', 'Southeast Asia\'s largest economy, where local-language B2B search is still under-served.', 'Local-language SEO and Google Ads'],
    ['AUS', 'Australia',      'Sydney',      -33.87,  151.21, 'r', '4.5 to 5.5 hours ahead of India', 'English', 'Mature buyers who expect proof, and reward the brands that show it.', 'SEO, AI visibility and conversion'],
    ['UK',  'United Kingdom', 'London',       51.51,   -0.13, 'l', '4.5 to 5.5 hours behind India', 'English', 'Careful buyers who compare vendors in depth before they ever book a call.', 'Content, SEO and AI visibility'],
    ['USA', 'United States',  'New York',     40.71,  -74.00, 'r', '9.5 to 10.5 hours behind India', 'English, Spanish', 'The most competitive search market. We win on technical SEO, AI visibility and conversion.', 'SEO, Google Ads and CRO'],
    ['CAN', 'Canada',         'Toronto',      43.65,  -79.38, 't', '9.5 to 10.5 hours behind India', 'English, French', 'Bilingual buyers and, in many B2B niches, lighter competition than the US.', 'Bilingual SEO and Google Ads'],
    ['ZAF', 'South Africa',   'Johannesburg', -26.20,  28.05, 'r', '3.5 hours behind India', 'English and ten other official languages', 'Africa\'s most developed B2B market, still light on AI visibility work.', 'SEO and Google Ads'],
    ['NGA', 'Nigeria',        'Lagos',         6.52,    3.38, 'l', '4.5 hours behind India', 'English', 'Fast-growing digital adoption, with few B2B brands investing in search yet.', 'SEO and content'],
    ['KEN', 'Kenya',          'Nairobi',      -1.29,   36.82, 'r', '2.5 hours behind India', 'English, Swahili', 'East Africa\'s technology and services hub, early in search competition.', 'SEO and paid social'],
];

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
    $hp_result('saas-ppc-demo-pipeline'),
    $hp_result('manufacturer-rfq-seo-program'),
    $hp_result('law-firm-lead-gen-website'),
    $hp_result('clinic-local-seo-patient-growth'),
    $hp_result('real-estate-lead-generation-engine'),
    $hp_result('edutech-content-engine-signups'),
]));

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

$cta_label = 'Start my growth plan';
?>

<?php /* ================================================================ HERO */ ?>
<section class="hp3-hero" id="hp3-hero" aria-labelledby="hp-h1">
  <div class="hp3-stage" id="hp3-stage">
    <div class="hp3-bg" aria-hidden="true">
      <span class="hp3-orb hp3-orb--v"></span>
      <span class="hp3-orb hp3-orb--u"></span>
    </div>
    <div class="hp3-gl" id="hp3-gl" aria-hidden="true" data-src="/assets/js/hero3d.min.js?v=<?= @filemtime(BASE_PATH . '/assets/js/hero3d.min.js') ?: 1 ?>"></div>

    <div class="hp3-scenes">
      <?php /* Scene 1. The H1 paints immediately: it is the LCP element. */ ?>
      <div class="hp3-scene hp3-scene--1 is-active" data-scene="0">
        <div class="hp3-copy">
          <p class="hp3-eyebrow"><span class="hp3-dot" aria-hidden="true"></span> Chennai built. Globally deployed.</p>
          <h1 class="hp3-h1" id="hp-h1"><span class="hp3-h1-k">B2B digital marketing agency</span> Get found. Get chosen. Get <span class="hp3-grad">pipeline</span>.</h1>
          <p class="hp3-lead">VTurnU makes B2B companies visible on Google and inside AI answers, then turns that attention into <strong>qualified enquiries your sales team wants</strong>. For growing firms in India, the UAE, Singapore, the US, Canada and beyond.</p>
          <div class="hp3-actions">
            <a class="hp3-btn hp3-btn--primary" href="#start"><?= e($cta_label) ?> <span aria-hidden="true">&rarr;</span></a>
            <?php if (isset($PAGES['case-studies'])): ?>
            <a class="hp3-btn hp3-btn--ghost" href="/case-studies/">See the results</a>
            <?php endif; ?>
          </div>
          <ul class="hp3-ticks">
            <li>Senior strategists on every account</li>
            <li>Reported in pipeline, not rankings</li>
            <li>Reply within 24 hours</li>
          </ul>
        </div>
        <img class="hp3-mark" src="/assets/img/vturnu-icon-mark.svg" alt="" width="420" height="420" aria-hidden="true">
      </div>

      <?php /* Scene 2. The 3D funnel; the stages below are anchored to it. */ ?>
      <div class="hp3-scene hp3-scene--2" data-scene="1">
        <div class="hp3-copy">
          <p class="hp3-eyebrow"><span class="hp3-dot" aria-hidden="true"></span> Attention in. Pipeline out.</p>
          <h2 class="hp3-h2">We turn scattered attention into a sales pipeline.</h2>
          <p class="hp3-lead">Your buyers bounce between Google, ChatGPT, LinkedIn and your website for weeks before they talk to anyone. We engineer every touchpoint to pull them one step closer to a sales call.</p>
        </div>
        <ol class="hp3-funnel" aria-label="How attention becomes pipeline">
          <?php foreach ($FUNNEL as [$h, $side, $t, $d]): ?>
          <li class="hp3-ftag" data-h="<?= e($h) ?>" data-side="<?= e($side) ?>"><b><?= e($t) ?></b> <span><?= e($d) ?></span></li>
          <?php endforeach; ?>
        </ol>
      </div>

      <?php /* Scene 3. The opportunity globe. Each market is a hotspot with a real insight card. */ ?>
      <div class="hp3-scene hp3-scene--3" data-scene="2">
        <div class="hp3-copy">
          <p class="hp3-eyebrow"><span class="hp3-dot" aria-hidden="true"></span> Opportunity map</p>
          <h2 class="hp3-h2">Your next big client is not in your city.</h2>
          <p class="hp3-lead">Across the Gulf, Southeast Asia and Africa, many B2B categories still have thin search results and almost no AI visibility work. <span class="hp3-hint-gl">Select a market on the globe, or drag to spin it.</span></p>
        </div>
        <ul class="hp3-mkts" aria-label="Markets we grow B2B companies in">
          <?php foreach ($MARKETS as [$code, $country, $city, $lat, $lon, $side, $tz, $langs, $opening, $start]): ?>
          <li class="hp3-mkt hp3-mkt--<?= e($side) ?>" data-lat="<?= e((string) $lat) ?>" data-lon="<?= e((string) $lon) ?>">
            <button type="button" class="hp3-mpin" aria-label="<?= e($country) ?>: show market insight"><i aria-hidden="true"></i><span><?= e($code) ?></span></button>
            <div class="hp3-mcard">
              <p class="hp3-mcard-h"><b><?= e($country) ?></b> <span><?= e($city) ?></span></p>
              <p class="hp3-mcard-o"><?= e($opening) ?></p>
              <dl>
                <div><dt>Where we start</dt><dd><?= e($start) ?></dd></div>
                <div><dt>Search languages</dt><dd><?= e($langs) ?></dd></div>
                <div><dt>Working hours</dt><dd><?= e($tz) ?></dd></div>
              </dl>
            </div>
          </li>
          <?php endforeach; ?>
        </ul>
        <div class="hp3-mpanel" aria-live="polite"></div>
      </div>

      <?php /* Scene 4. The portal ring, proof and the ask. */ ?>
      <div class="hp3-scene hp3-scene--4" data-scene="3">
        <div class="hp3-copy">
          <p class="hp3-eyebrow"><span class="hp3-dot" aria-hidden="true"></span> The turn, in numbers</p>
          <h2 class="hp3-h2">Results our clients took to the board.</h2>
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
            <a class="hp3-btn hp3-btn--ghost" href="#stack">Explore the stack</a>
          </div>
        </div>
      </div>
    </div>

    <nav class="hp3-rail" aria-label="Hero chapters" hidden>
      <button type="button" class="hp3-rail-dot" data-go="0"><span>Get found</span></button>
      <button type="button" class="hp3-rail-dot" data-go="1"><span>The funnel</span></button>
      <button type="button" class="hp3-rail-dot" data-go="2"><span>Opportunity map</span></button>
      <button type="button" class="hp3-rail-dot" data-go="3"><span>Results</span></button>
    </nav>
    <p class="hp3-cue" aria-hidden="true" hidden><span></span> Scroll to turn</p>
    <span class="hp3-progress" aria-hidden="true" hidden><i></i></span>
  </div>
</section>

<?php /* ============================================================ LOGOS */ ?>
<?php if ($LOGOS): ?>
<section class="hp3-logos" aria-label="Clients we work with">
  <p class="hp3-logos-h">Growing brands that turned with us</p>
  <div class="hp3-marquee">
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

<?php /* ================================================== SIGNAL OVER NOISE */ ?>
<section class="hp3-sec hp3-signal" aria-labelledby="hp3-sig-h">
  <div class="hp3-wrap">
    <div class="hp3-head" data-rise>
      <p class="hp3-kicker">Signal over noise</p>
      <h2 class="hp3-title" id="hp3-sig-h">Most B2B brands are loud in <span class="hp3-grad">the wrong rooms.</span></h2>
      <p class="hp3-sub">Three habits quietly drain B2B budgets. Flip each card to see the turn we make.</p>
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
            <span class="hp3-face-go" aria-hidden="true">Flip &#8635;</span>
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
      <p class="hp3-sub">Six programs, six industries, one discipline: measure what reaches the pipeline. Every case study includes what did not work.</p>
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
      <p class="hp3-kicker">Start the turn</p>
      <h2 class="hp3-title" id="hp-form-h">Get a growth plan built for your market.</h2>
      <p class="hp3-sub">Two minutes, three steps. Within one working day a senior strategist sends a specific read on your visibility, your competitors and the fastest route to qualified pipeline.</p>
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
            data-recaptcha="<?= e(RECAPTCHA_SITE_KEY) ?>" data-inbox="<?= e(ENQUIRY_TO) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="recaptcha_token" class="js-recaptcha-token">
        <input type="hidden" name="form_variant" value="b2b-qualifier-v2">
        <div class="hp3-hp" aria-hidden="true">
          <label for="f-website">Website</label>
          <input id="f-website" name="website" type="text" tabindex="-1" autocomplete="off">
        </div>

        <div class="hp3-steps" hidden>
          <span class="hp3-steps-bar"><i></i></span>
          <p class="hp3-steps-t" aria-live="polite">Step <b class="js-step-n">1</b> of 3</p>
        </div>

        <?php if ($form_status === 'error'): ?>
        <p class="hp3-form-bad" role="alert">Something did not go through. Check your name and work email, then send it again, or email <?= e(ENQUIRY_TO) ?>.</p>
        <?php endif; ?>

        <fieldset class="hp3-step" data-step="1">
          <legend>What do you need?</legend>
          <div class="hp3-chipset" role="group" aria-label="Services you are interested in">
            <?php foreach ($FORM_SERVICES as $s): ?>
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

<?php /* ================================================= STRAIGHT ANSWERS */ ?>
<section class="hp3-sec" aria-labelledby="hp3-faq-h">
  <div class="hp3-wrap hp3-faqwrap">
    <div class="hp3-head" data-rise>
      <p class="hp3-kicker">Straight answers</p>
      <h2 class="hp3-title" id="hp3-faq-h">What founders and CMOs ask us first</h2>
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
            'areaServed' => array_map(static function (array $m): array {
                return ['@type' => 'Country', 'name' => $m[1]];
            }, $MARKETS),
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
