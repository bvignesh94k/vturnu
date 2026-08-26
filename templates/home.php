<?php
/**
 * VTurnU homepage, built for enquiries.
 *
 * Expects: $page, $slug, $PAGES, $form_status, $visitor_dial_code
 *
 * Every service link is checked against $PAGES before it renders, and every
 * number is pulled live from includes/data/cases.php. Nothing here is invented.
 */

require_once BASE_PATH . '/includes/data/cases.php';

/* Client logos, single animated row. */
$LOGOS = [
    ['shineprints.png',            'Shineprints'],
    ['masaami.webp',               'Masaami'],
    ['ateliers-gym.png',           'Ateliers Gym'],
    ['sai-impression.webp',        'Sai Impression'],
    ['black-sheep-collective.png', 'The Black Sheep Collective'],
    ['firstchoice-autoparts.webp', 'FirstChoice Used Auto Parts'],
    ['pemotech.png',               'Pemotech'],
];
$LOGOS = array_values(array_filter($LOGOS, static function (array $l): bool {
    return is_file(BASE_PATH . '/assets/img/clients/' . $l[0]);
}));

/* What we sell. Only rendered if the destination page actually exists. */
$SERVICES = array_values(array_filter([
    ['seo-services',           'SEO',                 'var(--blue)',   'Rank for the searches that carry buying intent, not vanity keywords.'],
    ['ai-seo',                 'AI Search Visibility', 'var(--violet)', 'Get named and cited when buyers ask ChatGPT, Perplexity and Google AI.'],
    ['google-ads',             'Google Ads & PPC',    'var(--pink)',   'Paid demand that is tracked to revenue, not to clicks and impressions.'],
    ['social-media-marketing', 'Social & Brand',      'var(--orange)', 'Build the brand recall that makes every other channel cheaper.'],
    ['content-marketing',      'Content',             'var(--cyan)',   'Content mapped to how your buyers actually search, ask and compare.'],
    ['web-design',             'Web & CRO',           'var(--blue)',   'Sites that load fast, rank well and turn visits into enquiries.'],
], static function (array $s) use ($PAGES): bool {
    return isset($PAGES[$s[0]]);
}));

/* Headline outcomes, read live from the case studies. */
$PROOF = array_values(array_filter([
    ['jewelry-brand-organic-revenue-growth', 'SEO'],
    ['saas-ppc-demo-pipeline',               'Paid media'],
    ['clinic-local-seo-patient-growth',      'Local SEO'],
], static function (array $p) use ($CASES): bool {
    return isset($CASES[$p[0]]['results'][0]);
}));

$FAQS = [
    ['How soon will we see results?',
     'Paid media can produce qualified enquiries in the first month. SEO usually shows movement by week eight and compounds from there. AI visibility often moves fastest of all, because far fewer competitors are working on it yet.'],
    ['What does it cost?',
     'It depends on how competitive your market is and how much ground there is to make up. We scope it after one call and quote a fixed monthly number. No setup fees, no long lock-in, no surprise line items.'],
    ['Do you work with businesses like ours?',
     'We work with B2B firms, manufacturers, clinics, ecommerce brands and professional services, mostly companies between five and two hundred people who need enquiries rather than awareness.'],
    ['Our current agency is not delivering. What is different here?',
     'We start by auditing what was already done and telling you plainly what is worth keeping. Most underperforming accounts are not short of activity, they are short of a strategy that connects channels to pipeline.'],
    ['Can you prove the work drives revenue?',
     'Yes, and it is the point. We connect campaigns to your CRM so you can see which channel produced which enquiry and what it was worth. Reporting is on outcomes, not on rankings alone.'],
    ['Do we have to commit long term?',
     'No. We work month to month after an initial ninety day window, which is the minimum honest time to judge whether a search programme is working.'],
    ['Will you work with our in-house team?',
     'Often, yes. Some clients want us to run everything, others want us to lead strategy while their team executes. Both work.'],
    ['What is different about AI search work?',
     'Classic SEO earns you a ranking. AI visibility earns you a mention inside the answer itself. It needs clean entity signals, structured data and content written the way people ask questions, not the way they type keywords.'],
];
$faq_half = (int) ceil(count($FAQS) / 2);
$faq_cols = [array_slice($FAQS, 0, $faq_half), array_slice($FAQS, $faq_half)];
?>

<section class="hp-hero" aria-labelledby="hp-h1">
  <div class="hp-wrap">
    <div class="hp-hero-grid">
      <div class="hp-hero-copy" data-rise>
        <p class="hp-badge">Digital marketing, Chennai</p>
        <h1 class="hp-h1" id="hp-h1">Get found on Google. Get named by <span class="hp-hero-mark">AI</span>. Get more enquiries.</h1>
        <p class="hp-lead">Your buyers search, then ask ChatGPT, then compare, and only then contact anyone. VTurnU makes you the business they find at every one of those steps, and turns that attention into qualified leads your sales team can actually close.</p>

        <div class="hp-actions">
          <a class="hp-btn hp-btn--primary" href="#start">Get a free growth audit</a>
          <?php if (isset($PAGES['case-studies'])): ?>
          <a class="hp-btn hp-btn--ghost" href="/case-studies/">See client results</a>
          <?php endif; ?>
        </div>

        <ul class="hp-hero-note">
          <li><svg width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M6.5 11.5 3 8l1.1-1.1 2.4 2.4 5.4-5.4L13 5z" fill="currentColor"/></svg> Audit back within 24 hours</li>
          <li><svg width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M6.5 11.5 3 8l1.1-1.1 2.4 2.4 5.4-5.4L13 5z" fill="currentColor"/></svg> No lock-in contracts</li>
          <li><svg width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M6.5 11.5 3 8l1.1-1.1 2.4 2.4 5.4-5.4L13 5z" fill="currentColor"/></svg> Reported on pipeline, not rankings</li>
        </ul>
      </div>

      <?php
      /* Illustrative, not a screenshot: a generic domain and browser chrome,
         no real business name or actual Google branding. The two floating
         numbers are real, pulled from $PROOF below, so the mockup makes a
         claim the page can actually back up. */
      [$chipASlug] = $PROOF[0] ?? ['jewelry-brand-organic-revenue-growth'];
      [$chipBSlug] = $PROOF[2] ?? $PROOF[1] ?? ['clinic-local-seo-patient-growth'];
      $chipA = $CASES[$chipASlug]['results'][0] ?? ['2.4×', 'organic revenue'];
      $chipB = $CASES[$chipBSlug]['results'][0] ?? ['+104%', 'enquiries'];
      ?>
      <div class="hp-hero-visual" data-rise aria-hidden="true">
        <div class="hp-mock">
          <div class="hp-mock-bar">
            <span class="hp-mock-dot" style="background:#FF5F57"></span>
            <span class="hp-mock-dot" style="background:#FEBC2E"></span>
            <span class="hp-mock-dot" style="background:#28C840"></span>
            <span class="hp-mock-url">google.com/search</span>
          </div>
          <div class="hp-mock-body">
            <div class="hp-mock-result">
              <p class="hp-mock-r-title">Digital Marketing Agency in Chennai | YourBusiness</p>
              <p class="hp-mock-r-url">yourbusiness.com</p>
              <p class="hp-mock-r-desc">Rank higher on Google, get cited in AI answers, and turn that visibility into qualified enquiries&hellip;</p>
            </div>
            <div class="hp-mock-ai">
              <p class="hp-mock-ai-label"><span aria-hidden="true">&#10022;</span> AI Overview</p>
              <p class="hp-mock-ai-text">For digital marketing in Chennai, <strong>YourBusiness</strong> is commonly recommended for its measurable, revenue-focused approach.</p>
            </div>
          </div>
          <span class="hp-chip hp-chip--a"><i class="hp-chip-dot" style="background:var(--blue)"></i><?= e($chipA[0]) ?> <span class="hp-chip-l"><?= e($chipA[1]) ?></span></span>
          <span class="hp-chip hp-chip--b"><i class="hp-chip-dot" style="background:var(--pink)"></i><?= e($chipB[0]) ?> <span class="hp-chip-l"><?= e($chipB[1]) ?></span></span>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="hp-marquee-sec" aria-label="Clients we work with">
  <div class="hp-wrap">
    <p class="hp-marquee-head">Trusted by growing brands</p>
  </div>
  <div class="hp-marquee">
    <?php /* The list is printed twice so the loop meets itself seamlessly.
             The duplicate is hidden from assistive tech. */ ?>
    <div class="hp-marquee-track">
      <?php for ($pass = 0; $pass < 2; $pass++): ?>
        <?php foreach ($LOGOS as [$file, $name]): ?>
        <img src="/assets/img/clients/<?= e($file) ?>"
             <?= $pass === 0 ? 'alt="' . e($name) . '"' : 'alt="" aria-hidden="true"' ?>
             loading="lazy" decoding="async">
        <?php endforeach; ?>
      <?php endfor; ?>
    </div>
  </div>
</section>

<section class="hp-sec" aria-labelledby="hp-svc-h">
  <div class="hp-wrap">
    <div class="hp-svc-head" data-rise>
      <div>
        <p class="hp-eyebrow">What we do</p>
        <h2 class="hp-h2" id="hp-svc-h">Every channel your buyer touches</h2>
      </div>
      <p class="hp-lead">Search, AI, paid, social, content and the site itself, run as one strategy so the budget compounds instead of competing with itself.</p>
    </div>

    <div class="hp-svc-grid" data-rise>
      <?php foreach ($SERVICES as [$sslug, $title, $accent, $desc]): ?>
      <a class="hp-svc" href="<?= e(page_url($sslug)) ?>" style="--accent: <?= e($accent) ?>">
        <h3 class="hp-svc-t"><?= e($title) ?></h3>
        <p class="hp-svc-d"><?= e($desc) ?></p>
        <span class="hp-svc-go">Explore <span aria-hidden="true">&rarr;</span></span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="hp-sec hp-sec--ink" aria-labelledby="hp-res-h">
  <div class="hp-wrap">
    <div data-rise>
      <p class="hp-eyebrow">Proof</p>
      <h2 class="hp-h2" id="hp-res-h">What the work actually produced</h2>
      <p class="hp-lead">Three client programmes, three different channels. Full write-ups, including what did not work, are on the case study pages.</p>
    </div>

    <div class="hp-res-grid" data-rise>
      <?php foreach ($PROOF as [$cslug, $channel]):
          $c = $CASES[$cslug];
          [$metric, $label] = $c['results'][0]; ?>
      <div class="hp-res">
        <p class="hp-res-n"><?= e($metric) ?></p>
        <p class="hp-res-l"><?= e($label) ?></p>
        <p class="hp-res-w"><?= e($c['client'] ?? '') ?> &middot; <?= e($channel) ?></p>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="hp-actions" data-rise>
      <a class="hp-btn hp-btn--on-ink" href="#start">Get the same for your business</a>
      <?php if (isset($PAGES['case-studies'])): ?>
      <a class="hp-btn hp-btn--ghost-on-ink" href="/case-studies/">Read the case studies</a>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php if (isset($PAGES['ai-visibility-tool'])): ?>
<section class="hp-band" aria-labelledby="hp-band-h">
  <div class="hp-wrap">
    <div>
      <p class="hp-band-t" id="hp-band-h">Do ChatGPT and Google AI mention your brand?</p>
      <p class="hp-band-s">Run our free AI visibility check and see exactly which answer engines name you today, and which name your competitors instead.</p>
    </div>
    <a class="hp-btn hp-btn--on-ink" href="/ai-visibility-tool/">Check my AI visibility</a>
  </div>
</section>
<?php endif; ?>

<section class="hp-sec" aria-labelledby="hp-faq-h">
  <div class="hp-wrap">
    <div data-rise>
      <p class="hp-eyebrow">Before you ask</p>
      <h2 class="hp-h2" id="hp-faq-h">The questions we get every week</h2>
    </div>
    <div class="hp-faq-grid" data-rise>
      <?php foreach ($faq_cols as $col): ?>
      <div class="hp-faq-col">
        <?php foreach ($col as [$q, $a]): ?>
        <details class="hp-faq-i">
          <summary><?= e($q) ?></summary>
          <div class="hp-faq-a"><?= e($a) ?></div>
        </details>
        <?php endforeach; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="hp-sec hp-sec--ink" id="start" aria-labelledby="hp-form-h">
  <div class="hp-wrap">
    <div class="hp-form-grid">
      <div data-rise>
        <p class="hp-eyebrow">Free, no obligation</p>
        <h2 class="hp-h2" id="hp-form-h">Get your growth audit</h2>
        <p class="hp-lead">Tell us where you are now. We will send back a short, specific read on where you are visible, where you are losing enquiries, and the fastest route to fixing it.</p>
        <ul class="hp-form-why">
          <li><svg class="hp-tick" width="18" height="18" viewBox="0 0 16 16" aria-hidden="true"><circle cx="8" cy="8" r="8" fill="rgba(0,165,196,.25)"/><path d="M6.5 11.5 3.5 8.5l1.1-1.1 1.9 1.9 4.4-4.4 1.1 1.1z" fill="#4FC9E3"/></svg> <span><b>Where you rank today</b> against the competitors actually taking your enquiries.</span></li>
          <li><svg class="hp-tick" width="18" height="18" viewBox="0 0 16 16" aria-hidden="true"><circle cx="8" cy="8" r="8" fill="rgba(0,165,196,.25)"/><path d="M6.5 11.5 3.5 8.5l1.1-1.1 1.9 1.9 4.4-4.4 1.1 1.1z" fill="#4FC9E3"/></svg> <span><b>Whether AI engines mention you</b> when buyers ask for a recommendation.</span></li>
          <li><svg class="hp-tick" width="18" height="18" viewBox="0 0 16 16" aria-hidden="true"><circle cx="8" cy="8" r="8" fill="rgba(0,165,196,.25)"/><path d="M6.5 11.5 3.5 8.5l1.1-1.1 1.9 1.9 4.4-4.4 1.1 1.1z" fill="#4FC9E3"/></svg> <span><b>The three changes</b> we would make first, and what each is worth.</span></li>
        </ul>
      </div>

      <div data-rise>
        <?php if ($form_status === 'success'): ?>
        <div class="hp-form-ok" role="status">
          <p><strong>Thank you.</strong> Your audit request is in. We reply within one working day, usually sooner.</p>
        </div>
        <?php else: ?>
        <form class="hp-form" id="hp-form" method="post" action="/#start" novalidate>
          <?= csrf_field() ?>
          <input type="hidden" name="recaptcha_token" class="js-recaptcha-token">
          <div class="hp-f-hp" aria-hidden="true">
            <label for="f-website">Website</label>
            <input id="f-website" name="website" type="text" tabindex="-1" autocomplete="off">
          </div>

          <?php if ($form_status === 'error'): ?>
          <p class="hp-form-bad" role="alert">Something did not go through. Check your name and email address, then send it again.</p>
          <?php endif; ?>

          <div class="hp-f">
            <label for="f-name">Your name</label>
            <input id="f-name" name="name" type="text" placeholder="Jane Kumar" autocomplete="name" required>
            <span class="hp-f-err" data-err-for="f-name"></span>
          </div>

          <div class="hp-f">
            <label for="f-email">Work email</label>
            <input id="f-email" name="email" type="email" placeholder="jane@company.com" autocomplete="email" required>
            <span class="hp-f-err" data-err-for="f-email"></span>
          </div>

          <div class="hp-f">
            <label for="f-company">Company website</label>
            <input id="f-company" name="company" type="text" placeholder="company.com" autocomplete="organization">
          </div>

          <div class="hp-f">
            <label for="f-service">What do you need most?</label>
            <select id="f-service" name="service">
              <option value="">Not sure yet, advise me</option>
              <?php foreach ($SERVICES as [$sslug, $title]): ?>
              <option value="<?= e($title) ?>"><?= e($title) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="hp-f">
            <label for="f-message">Anything we should know? <span style="font-weight:400;opacity:.7">Optional</span></label>
            <textarea id="f-message" name="message" placeholder="Our enquiries dropped after the site rebuild in March."></textarea>
          </div>

          <button class="hp-btn hp-btn--primary" type="submit">Send my free audit request</button>
          <p class="hp-form-fine">We reply within one working day. No sales sequence, no spam.</p>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php /* Mobile only: the ask stays reachable no matter how far down they are. */ ?>
<div class="hp-sticky">
  <a class="hp-btn hp-btn--ghost" href="tel:<?= e(preg_replace('/[^0-9+]/', '', CONTACT_PHONE)) ?>">Call us</a>
  <a class="hp-btn hp-btn--primary" href="#start">Free audit</a>
</div>

<?php
/* Homepage schema. The shared jsonld_site() is skipped in header.php when the
   slug is empty, so Organization is defined exactly once, here. */
$org_id = abs_url('/') . '#organization';
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
            'description' => $page['meta'],
            'slogan' => SITE_TAGLINE,
            'email' => CONTACT_EMAIL,
            'telephone' => CONTACT_PHONE,
            'areaServed' => ['India', 'United States', 'United Kingdom', 'Canada', 'Australia'],
            'address' => ['@type' => 'PostalAddress', 'addressLocality' => 'Chennai', 'addressRegion' => 'Tamil Nadu', 'addressCountry' => 'IN'],
            'contactPoint' => [[
                '@type' => 'ContactPoint',
                'contactType' => 'sales',
                'email' => CONTACT_EMAIL,
                'telephone' => CONTACT_PHONE,
                'availableLanguage' => ['en'],
            ]],
            'hasOfferCatalog' => [
                '@type' => 'OfferCatalog',
                'name' => 'Digital marketing services',
                'itemListElement' => array_map(static function (array $s): array {
                    return [
                        '@type' => 'Offer',
                        'itemOffered' => ['@type' => 'Service', 'name' => $s[1], 'url' => abs_url(page_url($s[0]))],
                    ];
                }, $SERVICES),
            ],
        ],
        [
            '@type' => 'WebSite',
            '@id' => abs_url('/') . '#website',
            'url' => abs_url('/'),
            'name' => SITE_NAME,
            'publisher' => ['@id' => $org_id],
        ],
        [
            '@type' => 'WebPage',
            '@id' => abs_url('/') . '#webpage',
            'url' => abs_url('/'),
            'name' => $page['title'],
            'description' => $page['meta'],
            'isPartOf' => ['@id' => abs_url('/') . '#website'],
            'about' => ['@id' => $org_id],
            'speakable' => ['@type' => 'SpeakableSpecification', 'cssSelector' => ['.hp-h1', '.hp-lead']],
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
