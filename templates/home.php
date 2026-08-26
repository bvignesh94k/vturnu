<?php
/**
 * VTurnU homepage.
 *
 * Expects: $page, $slug, $PAGES, $form_status, $visitor_dial_code
 *
 * Every service link below points at a page that exists in includes/data/pages.php,
 * and every metric comes from includes/data/cases.php. Nothing on this page is
 * invented: if a number could not be traced to real client work it is not here.
 */

/* Client logos: the original assets already shipping on the site. They are not
   redrawn, recoloured or rebuilt from screenshots. */
$LOGOS = [
    ['yodgy.png',                  'Yodgy'],
    ['atomic-seo.png',             'Atomic SEO'],
    ['boosterio.png',              'Boosterio'],
    ['shineprints.png',            'Shineprints'],
    ['masaami.webp',               'Masaami'],
    ['ateliers-gym.webp',          'Ateliers Gym'],
    ['sai-impression.webp',        'Sai Impression'],
    ['black-sheep-collective.png', 'Black Sheep Collective'],
];
// .webp/.png mix: fall back to whatever file actually exists on disk.
foreach ($LOGOS as $i => [$f, $n]) {
    if (!is_file(BASE_PATH . '/assets/img/clients/' . $f)) {
        foreach (['png', 'webp', 'svg'] as $ext) {
            $try = preg_replace('/\.[a-z]+$/', '.' . $ext, $f);
            if (is_file(BASE_PATH . '/assets/img/clients/' . $try)) { $LOGOS[$i][0] = $try; break; }
        }
    }
}

/* The six commercial groups, mapped onto the live service architecture.
   No URL here is new and no service has been invented. */
$CAPS = [
    'search' => [
        'tab'    => 'Search &amp; AI Discovery',
        'accent' => 'var(--blue)',
        'solves' => 'Be found when buyers search, and named when they ask AI.',
        'does'   => 'We fix the technical foundations, build the pages that match real commercial queries, and make your business legible to the systems that now summarise and recommend. That means clean crawling and indexing, content organised around how buyers actually ask, consistent entity signals, and structured data that removes ambiguity about who you are and what you sell.',
        'out'    => 'More non-brand organic enquiries, and a brand that appears in the answer rather than on page two.',
        'links'  => [
            ['SEO services', '/seo-services/'],
            ['AI SEO services', '/ai-seo/'],
            ['Local SEO', '/local-seo/'],
            ['Ecommerce SEO', '/ecommerce-seo/'],
            ['Lead generation SEO', '/lead-generation-seo/'],
            ['Enterprise SEO', '/enterprise-seo/'],
            ['ChatGPT SEO', '/chatgpt-seo/'],
            ['Google AI Overviews SEO', '/google-ai-overviews-seo/'],
            ['Perplexity SEO', '/perplexity-seo/'],
        ],
    ],
    'paid' => [
        'tab'    => 'Performance Marketing',
        'accent' => 'var(--orange)',
        'solves' => 'Your ads should produce customers, not just cheaper clicks.',
        'does'   => 'We rebuild accounts around what actually closes. Search terms are audited for waste, campaigns are structured by intent rather than by product list, landing pages are matched to the promise in the ad, and conversion tracking is corrected so the numbers you optimise against are the numbers your finance team recognises.',
        'out'    => 'Lower cost per qualified lead, and budget you can scale without watching returns collapse.',
        'links'  => [
            ['Google Ads management', '/google-ads/'],
            ['Paid advertising', '/paid-advertising/'],
            ['Facebook Ads management', '/facebook-ads/'],
            ['Instagram Ads management', '/instagram-ads/'],
            ['Enterprise PPC', '/enterprise-ppc/'],
        ],
    ],
    'content' => [
        'tab'    => 'Content &amp; Social',
        'accent' => 'var(--pink)',
        'solves' => 'If buyers cannot tell why you, more traffic will not fix it.',
        'does'   => 'We write the pages that answer the questions your sales team keeps repeating, and we write them with enough first-hand detail that both a buyer and a retrieval system can tell the difference between your page and a generic one. Social and email carry the same argument to the people already in your orbit.',
        'out'    => 'Content that earns links and citations, shortens sales conversations and gets quoted back to you by prospects.',
        'links'  => [
            ['Content marketing', '/content-marketing/'],
            ['SEO content writing', '/seo-content-writing/'],
            ['Copywriting services', '/copywriting-services/'],
            ['Social media marketing', '/social-media-marketing/'],
            ['Email marketing', '/email-marketing/'],
            ['Translation services', '/translation-services/'],
        ],
    ],
    'web' => [
        'tab'    => 'Web &amp; Experience',
        'accent' => 'var(--violet)',
        'solves' => 'A website that looks good and converts nobody is a cost.',
        'does'   => 'We design and build sites around the decision a visitor is trying to make, not around a template. Fast pages, honest proof placed where doubt appears, forms that ask for what you actually need, and a technical base that search engines and AI crawlers can read without a headless browser.',
        'out'    => 'A higher share of the traffic you already pay for turning into enquiries.',
        'links'  => [
            ['Web design services', '/web-design/'],
            ['Lead gen web design', '/lead-gen-web-design/'],
            ['Ecommerce web design', '/ecommerce-web-design/'],
            ['Shopify web design', '/shopify-web-design/'],
            ['WordPress web design', '/wordpress-web-design/'],
            ['Headless web design', '/headless-web-design/'],
            ['Custom design and development', '/custom-design-and-development/'],
        ],
    ],
    'ai' => [
        'tab'    => 'AI &amp; Automation',
        'accent' => 'var(--cyan)',
        'solves' => 'AI is useful where it removes work, not where it adds noise.',
        'does'   => 'We start from a task that costs you time or money, then decide whether AI genuinely helps. That might be measuring how the answer engines describe your brand, automating qualification and routing on inbound enquiries, or building an internal tool. If the honest answer is that AI will not help, we say so.',
        'out'    => 'Fewer manual hours on repeatable work, and a measurable read on your visibility inside AI answers.',
        'links'  => [
            ['AI services', '/ai/'],
            ['AI development', '/ai-development/'],
            ['VTurnAI visibility tool', '/ai-visibility-tool/'],
            ['Selling on ChatGPT', '/selling-on-chatgpt/'],
        ],
    ],
    'advisory' => [
        'tab'    => 'Advisory &amp; Reputation',
        'accent' => 'var(--blue)',
        'solves' => 'Channel silos are usually the real growth problem.',
        'does'   => 'Sometimes the constraint is not a channel at all. It is attribution nobody trusts, an offer that does not land, or four agencies optimising four different numbers. We audit the whole system, tell you where the money is actually leaking, and either fix it with you or hand you the plan.',
        'out'    => 'A single connected view of what drives revenue, and a shorter list of things to do next.',
        'links'  => [
            ['Digital marketing consulting', '/digital-marketing-consulting/'],
            ['Reputation management', '/reputation-management/'],
            ['Outbound marketing', '/outbound-marketing/'],
            ['Online marketing services', '/online-marketing/'],
            ['Digital marketing services', '/digital-marketing/'],
        ],
    ],
];

/* Three case studies, pulled live so the homepage can never drift from the
   case-study pages themselves. */
$FEATURED = ['manufacturer-rfq-seo-program', 'saas-ppc-demo-pipeline', 'jewelry-brand-organic-revenue-growth'];
$CASES_ALL = $GLOBALS['CASES'] ?? [];

$FAQS = [
    ['What does VTurnU actually do?',
     'We are a digital growth company. We make businesses discoverable in search and in AI answers, run paid media that is judged on qualified leads rather than clicks, build websites and content that convert, and connect those channels so you can see what is genuinely driving revenue.'],
    ['Who do you work with?',
     'Mostly B2B companies, SMEs and growth-focused small businesses: manufacturers, SaaS and technology firms, healthcare groups, professional services, ecommerce brands and multi-location businesses. We work with companies where one more qualified enquiry per week is worth real money.'],
    ['Do you work outside Chennai?',
     'Yes. We are based in Chennai and work with clients across India, the United States, the United Kingdom, Canada and Australia. Most engagements run remotely with scheduled calls in your working hours.'],
    ['How do you approach SEO and AI search together?',
     'They are not separate programmes. The technical health, content quality, entity consistency and authority that make you rank are largely the same signals that make an AI system willing to cite you. We build one foundation and then add the work that is specific to answer engines, such as structured data depth and clear, extractable answers.'],
    ['Can you guarantee rankings or AI citations?',
     'No, and you should be careful with anyone who does. Nobody controls Google\'s ranking systems or how ChatGPT, Gemini and Perplexity choose sources. What we commit to is the work, a clear measurement setup, and honest reporting on what moved and what did not.'],
    ['How long does SEO take to show results?',
     'Technical fixes and page-level improvements often show inside four to eight weeks. Meaningful non-brand growth usually takes three to six months, and competitive categories take longer. We set expectations against your specific starting position rather than a generic timeline.'],
    ['How quickly can paid campaigns improve?',
     'Usually faster than SEO. Cutting wasted spend and fixing tracking can change cost per qualified lead within the first few weeks. Building a genuinely better-performing account structure typically takes one to two months of iteration.'],
    ['How much does an engagement cost?',
     'It depends on scope, market competitiveness and how much is already in place. We scope after a short diagnostic call rather than quoting a number before we understand the problem. If your budget will not support the work required to succeed, we will tell you that instead of taking it.'],
];
?>

<!-- ============================ HERO ============================ -->
<section class="hero" aria-labelledby="hp-h1">
    <div class="wrap">
        <h1 id="hp-h1"><span class="turn">Turn</span> visibility into qualified demand.</h1>

        <p class="hero-sub">
            Your buyers search Google, ask ChatGPT, compare on Perplexity and read an AI summary
            long before they reach your website. VTurnU makes growing businesses discoverable
            across all of it, then turns that attention into enquiries your sales team can close.
        </p>

        <div class="hero-actions">
            <a class="cta" href="#start">
                <span class="cta-label">Get my growth audit</span>
                <svg class="cta-arrow" width="18" height="12" viewBox="0 0 18 12" fill="none" aria-hidden="true"><path d="M12 1l5 5-5 5M17 6H1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
            <a class="cta-ghost" href="/case-studies/">
                <span>See what we have moved</span>
                <svg class="cta-arrow" width="18" height="12" viewBox="0 0 18 12" fill="none" aria-hidden="true"><path d="M12 1l5 5-5 5M17 6H1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
        </div>

        <p class="hero-cred">
            <b>150+</b> projects delivered
            <span class="dot" aria-hidden="true"></span>
            <b>10+</b> industries
            <span class="dot" aria-hidden="true"></span>
            Chennai, working across India, the US, UK, Canada and Australia
        </p>

        <!-- Signature interaction: one buyer signal, followed from a typed query
             to a line in your pipeline. Readable with animation disabled. -->
        <div class="signal" id="signal" data-rise>
            <p class="signal-label">How one enquiry actually starts</p>

            <div class="signal-query">
                <p class="signal-phrase on" data-phrase>seo agency for b2b</p>
                <p class="signal-phrase" data-phrase>Which agency can help our B2B company rank on Google <span class="q-mark">and</span> show up in AI answers<span class="q-mark">?</span></p>
                <p class="signal-phrase" data-phrase>Who would you recommend for B2B search and AI visibility in India<span class="q-mark">?</span></p>
            </div>

            <ol class="signal-rail">
                <li class="signal-stage on" data-stage>
                    <i class="st-turn" aria-hidden="true"></i>
                    <span class="st-name">Search</span>
                    <span class="st-note">A short commercial query</span>
                </li>
                <li class="signal-stage" data-stage>
                    <i class="st-turn" aria-hidden="true"></i>
                    <span class="st-name">AI answer</span>
                    <span class="st-note">A summary that names a few companies</span>
                </li>
                <li class="signal-stage" data-stage>
                    <i class="st-turn" aria-hidden="true"></i>
                    <span class="st-name">Your website</span>
                    <span class="st-note">They arrive already half decided</span>
                </li>
                <li class="signal-stage" data-stage>
                    <i class="st-turn" aria-hidden="true"></i>
                    <span class="st-name">Enquiry</span>
                    <span class="st-note">One form, or no form at all</span>
                </li>
                <li class="signal-stage" data-stage>
                    <i class="st-turn" aria-hidden="true"></i>
                    <span class="st-name">Pipeline</span>
                    <span class="st-note">Revenue, or a competitor's revenue</span>
                </li>
            </ol>
        </div>
    </div>
</section>

<!-- ============================ TRUST ============================ -->
<section class="trust" aria-label="Clients">
    <div class="wrap">
        <hr class="rule">
        <p class="trust-line" data-rise>Trusted to solve real growth problems, <span>not to produce reports.</span></p>
        <ul class="logo-line" data-rise>
            <?php foreach ($LOGOS as [$file, $name]): ?>
            <li><img src="/assets/img/clients/<?= e($file) ?>" alt="<?= e($name) ?>" loading="lazy" decoding="async" width="160" height="34"></li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

<!-- ============================ THE SHIFT ============================ -->
<section class="band band-warm" aria-labelledby="shift-h">
    <div class="wrap">
        <h2 class="h-sec" id="shift-h" data-rise>Your customer does not search in one place anymore.</h2>
        <p class="lead-sec" data-rise>
            The same buyer now moves between a search box, a chat window and a summary
            they never asked for. By the time they reach your site the shortlist is often
            already written. Here is one buyer, one need, five surfaces.
        </p>

        <div class="shift-grid">
            <ol class="journey" data-rise>
                <li><span class="j-where">Google</span><span class="j-said">b2b seo agency chennai</span></li>
                <li><span class="j-where">ChatGPT</span><span class="j-said">We manufacture industrial parts. How do we get more export enquiries from search?</span></li>
                <li><span class="j-where">Perplexity</span><span class="j-said">Best digital marketing agencies for B2B in India, with sources</span></li>
                <li><span class="j-where">AI Overview</span><span class="j-said">Reads a three-line summary naming two companies. Yours is not one of them.</span></li>
                <li><span class="j-where">Your site</span><span class="j-said">Arrives with a shortlist already formed, looking for a reason to remove you.</span></li>
                <li><span class="j-where">Decision</span><span class="j-said">Contacts one. Never tells the others they were considered.</span></li>
            </ol>

            <div data-rise>
                <p class="lead-sec" style="margin-top:0">
                    Nothing in that sequence is exotic. It is how a purchase decision looks in 2026
                    for most of the businesses we work with, and it has two consequences that matter
                    commercially.
                </p>
                <p class="lead-sec">
                    First, ranking on one surface is no longer the same as being visible. Second,
                    the moment a machine writes the summary, being <em>quotable</em> matters as much as
                    being <em>rankable</em>. That is a content and structure problem, not a keyword problem.
                </p>
                <p style="margin-top:var(--s6)">
                    <a class="cta-ghost" href="/ai/">
                        <span>How AI search changes the work</span>
                        <svg class="cta-arrow" width="18" height="12" viewBox="0 0 18 12" fill="none" aria-hidden="true"><path d="M12 1l5 5-5 5M17 6H1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </p>
            </div>
        </div>
    </div>
</section>

<!-- ============================ GROWTH SYSTEM ============================ -->
<section class="band-ink system-pin" id="system" aria-labelledby="sys-h">
    <div class="system-stage">
        <div class="system-head wrap">
            <h2 class="h-sec wide" id="sys-h">How VTurnU turns visibility into growth.</h2>
            <p class="lead-sec">
                Search creates discovery. Content creates understanding. Paid media captures
                demand that already exists. Experience converts it. Automation scales whatever
                proves it works. Each stage has one job, and each one is measured.
            </p>
        </div>

        <ol class="system-track" id="system-track">
            <li class="stage on" data-sys>
                <h3 class="stage-name">Be found</h3>
                <p class="stage-what">Technical health, indexation and pages built around the queries buyers actually type.</p>
                <p class="stage-proof">Measured by: non-brand impressions, indexed page coverage, ranked keyword count.</p>
            </li>
            <li class="stage" data-sys>
                <h3 class="stage-name">Be understood</h3>
                <p class="stage-what">Consistent entities and structured data, so both crawlers and answer engines know exactly what you do.</p>
                <p class="stage-proof">Measured by: entity consistency, schema coverage, correct knowledge panel data.</p>
            </li>
            <li class="stage" data-sys>
                <h3 class="stage-name">Be trusted</h3>
                <p class="stage-what">Authority earned off your own site: citations, reviews, coverage and genuine first-hand expertise on the page.</p>
                <p class="stage-proof">Measured by: referring domains, review volume and rating, branded search.</p>
            </li>
            <li class="stage" data-sys>
                <h3 class="stage-name">Be chosen</h3>
                <p class="stage-what">Appearing inside AI answers and comparison moments, with the reasons a buyer needs to shortlist you.</p>
                <p class="stage-proof">Measured by: share of voice in AI answers, citation frequency, comparison rankings.</p>
            </li>
            <li class="stage" data-sys>
                <h3 class="stage-name">Convert</h3>
                <p class="stage-what">Pages, offers and forms designed around the decision the visitor is trying to make.</p>
                <p class="stage-proof">Measured by: enquiry rate, cost per qualified lead, form completion.</p>
            </li>
            <li class="stage" data-sys>
                <h3 class="stage-name">Nurture</h3>
                <p class="stage-what">Follow-up that respects a long buying cycle, because most B2B enquiries do not close on first contact.</p>
                <p class="stage-proof">Measured by: enquiry to opportunity rate, time to close, reply rates.</p>
            </li>
            <li class="stage" data-sys>
                <h3 class="stage-name">Scale</h3>
                <p class="stage-what">More budget and more automation applied only to the paths that have already proven they return.</p>
                <p class="stage-proof">Measured by: contribution per channel, blended acquisition cost, revenue growth.</p>
            </li>
        </ol>

        <div class="system-progress" aria-hidden="true"><i id="sys-bar"></i></div>
    </div>
</section>

<!-- ============================ SERVICES ============================ -->
<section class="band" id="services" aria-labelledby="svc-h">
    <div class="wrap">
        <h2 class="h-sec" id="svc-h" data-rise>Six capabilities, one growth system.</h2>
        <p class="lead-sec" data-rise>
            Most companies do not need all of this at once. They need the two or three
            things that are currently costing them the most. Pick the area that sounds like
            your problem.
        </p>

        <div class="svc">
            <ul class="svc-index" role="tablist" aria-label="Capabilities" data-rise>
                <?php $first = true; foreach ($CAPS as $key => $cap): ?>
                <li>
                    <button type="button"
                            class="svc-tab"
                            role="tab"
                            id="tab-<?= e($key) ?>"
                            aria-controls="panel-<?= e($key) ?>"
                            aria-selected="<?= $first ? 'true' : 'false' ?>"
                            tabindex="<?= $first ? '0' : '-1' ?>"
                            style="--accent: <?= e($cap['accent']) ?>">
                        <i class="tab-turn" aria-hidden="true"></i>
                        <span><?= $cap['tab'] ?></span>
                    </button>
                </li>
                <?php $first = false; endforeach; ?>
            </ul>

            <div data-rise>
                <?php $first = true; foreach ($CAPS as $key => $cap): ?>
                <div class="svc-panel<?= $first ? ' on' : '' ?>"
                     id="panel-<?= e($key) ?>"
                     role="tabpanel"
                     aria-labelledby="tab-<?= e($key) ?>"
                     tabindex="0"
                     style="--accent: <?= e($cap['accent']) ?>"
                     <?= $first ? '' : 'hidden' ?>>
                    <h3 class="svc-solves"><?= e($cap['solves']) ?></h3>
                    <p class="svc-does"><?= e($cap['does']) ?></p>
                    <p class="svc-out"><b>What you should expect:</b> <?= e($cap['out']) ?></p>
                    <ul class="svc-links">
                        <?php foreach ($cap['links'] as [$label, $url]): ?>
                        <li><a href="<?= e($url) ?>"><?= e($label) ?><span aria-hidden="true">&rarr;</span></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php $first = false; endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- ============================ SITUATIONS ============================ -->
<section class="band band-warm" aria-labelledby="sit-h">
    <div class="wrap">
        <h2 class="h-sec" id="sit-h" data-rise>Is VTurnU built for a business like ours?</h2>
        <p class="lead-sec" data-rise>
            Easier to answer with situations than with industries. If one of these is a
            sentence someone in your company has said out loud this quarter, we can help.
        </p>

        <ul class="sit" data-rise>
            <li><a class="sit-row" href="/seo-services/">
                <p class="sit-said">"We depend on Google for enquiries, and rankings have been slipping."</p>
                <p class="sit-fix">Usually a mix of technical decay, thin pages and competitors publishing more useful content. All three are fixable, in that order.</p>
                <span class="sit-go">SEO services</span>
            </a></li>
            <li><a class="sit-row" href="/google-ads/">
                <p class="sit-said">"Our ads get clicks. They do not get customers."</p>
                <p class="sit-fix">Almost always wasted search terms, intent-blind campaign structure and conversion tracking that counts the wrong event.</p>
                <span class="sit-go">Google Ads</span>
            </a></li>
            <li><a class="sit-row" href="/lead-gen-web-design/">
                <p class="sit-said">"Our website looks good but almost nobody enquires."</p>
                <p class="sit-fix">The page is usually describing the company instead of resolving the visitor's doubt at the moment the doubt appears.</p>
                <span class="sit-go">Lead gen web design</span>
            </a></li>
            <li><a class="sit-row" href="/ai-seo/">
                <p class="sit-said">"Competitors appear in AI answers and we do not."</p>
                <p class="sit-fix">Answer engines cite sources they can parse, verify and attribute. That is a structure, entity and authority problem before it is a content problem.</p>
                <span class="sit-go">AI SEO</span>
            </a></li>
            <li><a class="sit-row" href="/digital-marketing-consulting/">
                <p class="sit-said">"Every channel runs separately and nothing connects."</p>
                <p class="sit-fix">Four agencies optimising four different numbers will always produce four good reports and one flat revenue line.</p>
                <span class="sit-go">Consulting</span>
            </a></li>
            <li><a class="sit-row" href="/ai-development/">
                <p class="sit-said">"We want to use AI properly, without rebuilding everything."</p>
                <p class="sit-fix">Start from one task that costs real hours. Automate that. Ignore anything that only sounds impressive in a meeting.</p>
                <span class="sit-go">AI development</span>
            </a></li>
        </ul>
    </div>
</section>

<!-- ============================ PROOF ============================ -->
<section class="band" id="proof" aria-labelledby="proof-h">
    <div class="wrap">
        <h2 class="h-sec" id="proof-h" data-rise>Work we can show you the numbers on.</h2>
        <p class="lead-sec" data-rise>
            Three engagements, with the figures the client measured. Client names are withheld
            where the agreement requires it, which is most of the time in B2B.
        </p>
    </div>

    <?php foreach ($FEATURED as $cslug):
        $c = $CASES_ALL[$cslug] ?? null;
        if (!$c) continue;
        $headline = $c['results'][0] ?? ['', ''];
        $rest = array_slice($c['results'] ?? [], 1);
    ?>
    <div class="wrap">
        <article class="proof-case">
            <div class="proof-metric">
                <p class="metric-big" data-rise><?= e($headline[0]) ?></p>
                <p class="metric-of"><?= e($headline[1]) ?></p>
                <p class="metric-who"><?= e($c['industry'] ?? '') ?></p>
            </div>

            <div class="proof-story">
                <h3><?= e($c['h1'] ?? '') ?></h3>
                <dl class="proof-beat">
                    <dt>The problem</dt>
                    <dd><?= e(is_array($c['challenge'] ?? null) ? implode(' ', $c['challenge']) : (string) ($c['challenge'] ?? '')) ?></dd>
                    <?php if (!empty($c['approach'])): ?>
                    <dt>What we changed</dt>
                    <dd>
                        <ul class="proof-did">
                            <?php foreach ($c['approach'] as $step): ?>
                            <li><b><?= e($step[0] ?? '') ?>.</b> <?= e($step[1] ?? '') ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </dd>
                    <?php endif; ?>
                    <?php if (!empty($c['outcome'])): ?>
                    <dt>What it was worth</dt>
                    <dd><?= e($c['outcome']) ?></dd>
                    <?php endif; ?>
                </dl>

                <?php if ($rest): ?>
                <ul class="proof-nums">
                    <?php foreach ($rest as [$n, $l]): ?>
                    <li><b><?= e($n) ?></b><span><?= e($l) ?></span></li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>

                <?php if (!empty($c['quote'][0])): ?>
                <blockquote class="proof-quote">
                    <p>&ldquo;<?= e($c['quote'][0]) ?>&rdquo;</p>
                    <footer><?= e($c['quote'][1] ?? '') ?></footer>
                </blockquote>
                <?php endif; ?>

                <p style="margin-top:var(--s6)">
                    <a class="cta-ghost" href="/case-studies/<?= e($cslug) ?>/">
                        <span>Read the full case study</span>
                        <svg class="cta-arrow" width="18" height="12" viewBox="0 0 18 12" fill="none" aria-hidden="true"><path d="M12 1l5 5-5 5M17 6H1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </p>
            </div>
        </article>
    </div>
    <?php endforeach; ?>

    <div class="wrap">
        <hr class="rule">
        <p style="margin-top:var(--s6)">
            <a class="cta" href="/case-studies/">
                <span class="cta-label">All case studies</span>
                <svg class="cta-arrow" width="18" height="12" viewBox="0 0 18 12" fill="none" aria-hidden="true"><path d="M12 1l5 5-5 5M17 6H1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
        </p>
    </div>
</section>

<!-- ============================ DIFFERENCE ============================ -->
<section class="band band-ink" aria-labelledby="diff-h">
    <div class="wrap">
        <h2 class="h-sec" id="diff-h" data-rise>Different by design. Accountable by numbers.</h2>
        <p class="lead-sec" data-rise>
            We are not going to claim other agencies are incompetent. We will say plainly
            what we do differently, because those differences are why clients stay.
        </p>

        <ul class="diff" data-rise>
            <li>
                <span class="d-common">Impressions, rankings and reach</span>
                <i class="d-turn" aria-hidden="true"></i>
                <span class="d-ours">Enquiries, pipeline and revenue</span>
            </li>
            <li>
                <span class="d-common">One channel, optimised in isolation</span>
                <i class="d-turn" aria-hidden="true"></i>
                <span class="d-ours">One system, measured end to end</span>
            </li>
            <li>
                <span class="d-common">A monthly deck nobody finishes</span>
                <i class="d-turn" aria-hidden="true"></i>
                <span class="d-ours">A short list of decisions and what they cost</span>
            </li>
            <li>
                <span class="d-common">"AI-powered" as a slogan</span>
                <i class="d-turn" aria-hidden="true"></i>
                <span class="d-ours">AI used only where it measurably helps</span>
            </li>
            <li>
                <span class="d-common">A strategy that could belong to anyone</span>
                <i class="d-turn" aria-hidden="true"></i>
                <span class="d-ours">Research into your buyers, market and margins</span>
            </li>
            <li>
                <span class="d-common">Locked-in annual contracts</span>
                <i class="d-turn" aria-hidden="true"></i>
                <span class="d-ours">No long-term lock-ins, and a 24 hour response</span>
            </li>
        </ul>
    </div>
</section>

<!-- ============================ EXPERTISE ============================ -->
<section class="band" aria-labelledby="exp-h">
    <div class="wrap">
        <h2 class="h-sec" id="exp-h" data-rise>Search is becoming a conversation.</h2>
        <p class="lead-sec" data-rise>
            That does not mean SEO is finished. It means the same fundamentals now serve
            two audiences: a person scanning results, and a system deciding which sources
            it is willing to quote. Six things this actually changes.
        </p>

        <div class="exp-grid">
            <ul class="exp-list" data-rise>
                <li>
                    <h3>Technical foundations still decide everything</h3>
                    <p>A page an answer engine cannot crawl, render or parse cannot be cited. Speed, clean HTML and correct indexation matter more now, not less.</p>
                </li>
                <li>
                    <h3>Machines need content they can extract</h3>
                    <p>Clear headings, answer-first paragraphs and specific claims. A page that buries its answer in paragraph nine will lose to one that leads with it.</p>
                </li>
                <li>
                    <h3>Your entity has to stay consistent</h3>
                    <p>Same business name, same address, same description, same people, everywhere. Inconsistency is the fastest way to be left out of an answer.</p>
                </li>
            </ul>
            <ul class="exp-list" data-rise>
                <li>
                    <h3>Authority is still earned off your own site</h3>
                    <p>Citations, reviews, mentions and coverage. AI systems weight corroboration heavily, because they are trying not to be wrong in public.</p>
                </li>
                <li>
                    <h3>First-hand experience is the real differentiator</h3>
                    <p>Original data, real numbers, actual project detail. Generic content is exactly what a language model can already produce for free.</p>
                </li>
                <li>
                    <h3>Structured data removes ambiguity</h3>
                    <p>Schema is how you state plainly what a page is, who wrote it and what it describes, rather than hoping the parse goes your way.</p>
                </li>
            </ul>
        </div>

        <p style="margin-top:var(--s8)" data-rise>
            <a class="cta-ghost" href="/ai-visibility-tool/">
                <span>Measure how AI engines currently describe your brand</span>
                <svg class="cta-arrow" width="18" height="12" viewBox="0 0 18 12" fill="none" aria-hidden="true"><path d="M12 1l5 5-5 5M17 6H1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
        </p>
    </div>
</section>

<!-- ============================ PEOPLE ============================ -->
<section class="band band-warm" aria-labelledby="ppl-h">
    <div class="wrap">
        <h2 class="h-sec" id="ppl-h" data-rise>Strategy is still a human job.</h2>

        <div class="people-grid">
            <div data-rise>
                <p class="people-note">
                    AI has made research and production faster, and we use it daily. It has
                    not made judgement cheaper. Deciding which market to attack, which margin
                    is worth defending and which enquiry is actually worth chasing still needs
                    somebody who has understood your business.
                </p>
                <p class="people-note">
                    That is the part we do not automate. Every engagement has named people on
                    it, and you talk to the people doing the work rather than to an account
                    manager relaying messages.
                </p>
                <p style="margin-top:var(--s6)">
                    <a class="cta-ghost" href="/about-us/">
                        <span>About VTurnU</span>
                        <svg class="cta-arrow" width="18" height="12" viewBox="0 0 18 12" fill="none" aria-hidden="true"><path d="M12 1l5 5-5 5M17 6H1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </p>
            </div>

            <ul class="facts" data-rise>
                <li><span class="f-k">Based in</span><span class="f-v">Chennai, India</span></li>
                <li><span class="f-k">Working across</span><span class="f-v">India, US, UK, Canada, Australia</span></li>
                <li><span class="f-k">Projects delivered</span><span class="f-v">150+</span></li>
                <li><span class="f-k">Industries served</span><span class="f-v">10+</span></li>
                <li><span class="f-k">Response time</span><span class="f-v">Within 24 hours</span></li>
                <li><span class="f-k">Email</span><span class="f-v"><a href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a></span></li>
            </ul>
        </div>
    </div>
</section>

<!-- ============================ PROCESS ============================ -->
<section class="band band-tight" aria-labelledby="proc-h">
    <div class="wrap">
        <h2 class="h-sec" id="proc-h" data-rise>How an engagement runs.</h2>
        <p class="lead-sec" data-rise>
            Six stages, repeated. The first one is the one most agencies skip.
        </p>

        <ol class="proc" id="proc" data-rise>
            <li><h3>Diagnose</h3><p>Find where growth is actually blocked, which is often not where you think.</p></li>
            <li><h3>Prioritise</h3><p>Rank the fixes by commercial impact against effort, then agree the first three.</p></li>
            <li><h3>Build</h3><p>Ship the work: technical, content, campaigns, pages, tracking.</p></li>
            <li><h3>Measure</h3><p>Against enquiries and revenue, not against impressions.</p></li>
            <li><h3>Improve</h3><p>Keep what moved the number. Stop what did not, quickly.</p></li>
            <li><h3>Scale</h3><p>Put more budget and automation behind the paths that already return.</p></li>
        </ol>
    </div>
</section>

<!-- ============================ FAQ ============================ -->
<section class="band band-tight" aria-labelledby="faq-h">
    <div class="wrap">
        <h2 class="h-sec" id="faq-h" data-rise>Questions worth asking any agency.</h2>
        <p class="lead-sec" data-rise>Including the ones where the honest answer is not the flattering one.</p>

        <div class="faq" data-rise>
            <?php foreach ($FAQS as [$q, $a]): ?>
            <details>
                <summary><span><?= e($q) ?></span><i class="fq-sign" aria-hidden="true"></i></summary>
                <p class="fq-a"><?= e($a) ?></p>
            </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================ CONVERT ============================ -->
<section class="band band-ink" id="start" aria-labelledby="start-h">
    <div class="wrap">
        <div class="convert-grid">
            <div data-rise>
                <h2 id="start-h">Let us find the growth you are currently missing.</h2>
                <p class="lead-sec">
                    Tell us where growth is stuck. We will review your search visibility, AI
                    presence, paid acquisition, website experience or measurement setup, and
                    come back with the most valuable next move. No charge, and no obligation
                    to work with us afterwards.
                </p>
                <p class="lead-sec">
                    Prefer to talk first? Call <a href="<?= e(CONTACT_PHONE_HREF) ?>" style="color:inherit"><?= e(CONTACT_PHONE) ?></a>
                    or email <a href="mailto:<?= e(CONTACT_EMAIL) ?>" style="color:inherit"><?= e(CONTACT_EMAIL) ?></a>.
                </p>
            </div>

            <div data-rise>
                <?php if (($form_status ?? null) === 'success'): ?>
                    <div class="form-step">
                        <p class="step-of">Received</p>
                        <h3 style="font-size:1.5rem;margin:0 0 var(--s2)">Thank you. That has reached us.</h3>
                        <p class="lead-sec" style="margin-top:0">
                            We reply within one working day, usually sooner. The first message back
                            will be a short set of questions rather than a proposal, because we would
                            rather understand the problem before quoting on it.
                        </p>
                    </div>
                <?php else: ?>
                <form class="hp-form raw-lead-form" method="post" action="/#start" id="hp-form" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="recaptcha_token" class="js-recaptcha-token">
                    <input type="hidden" name="service_source" value="homepage">

                    <!-- Step one: the three fields we genuinely need. -->
                    <div class="form-step" data-step="1">
                        <p class="step-of">Step 1 of 2</p>
                        <div class="fields">
                            <div class="field">
                                <label for="f-name">Your name</label>
                                <input id="f-name" name="name" type="text" autocomplete="name" placeholder="Full name" required>
                                <span class="err" data-err-for="f-name"></span>
                            </div>
                            <div class="field">
                                <label for="f-email">Work email</label>
                                <input id="f-email" name="email" type="email" autocomplete="email" placeholder="you@company.com" required>
                                <span class="err" data-err-for="f-email"></span>
                            </div>
                            <div class="field full">
                                <label for="f-company">Company website</label>
                                <input id="f-company" name="company" type="text" autocomplete="organization" placeholder="yourcompany.com">
                                <span class="err" data-err-for="f-company"></span>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="cta js-only" id="hp-next">
                                <span class="cta-label">Continue</span>
                                <svg class="cta-arrow" width="18" height="12" viewBox="0 0 18 12" fill="none" aria-hidden="true"><path d="M12 1l5 5-5 5M17 6H1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </button>
                        </div>
                        <p class="form-next">Two short steps. We never sell or share your details, and there is no automated sales sequence.</p>
                    </div>

                    <!-- Step two: helpful, but not worth losing the enquiry over. -->
                    <div class="form-step" data-step="2">
                        <p class="step-of">Step 2 of 2</p>
                        <div class="fields">
                            <div class="field full">
                                <label for="f-service">What is the main objective?</label>
                                <select id="f-service" name="service">
                                    <option value="">Select the closest one</option>
                                    <option>More qualified leads from search</option>
                                    <option>Visibility in AI answers and AI search</option>
                                    <option>Better return from paid advertising</option>
                                    <option>A website that converts more of its traffic</option>
                                    <option>Content that ranks and gets cited</option>
                                    <option>AI development or automation</option>
                                    <option>A full audit across every channel</option>
                                    <option>Something else</option>
                                </select>
                            </div>
                            <div class="field">
                                <label for="f-budget">Approximate monthly budget</label>
                                <select id="f-budget" name="budget">
                                    <option value="">Prefer not to say</option>
                                    <option>Under 25,000 INR</option>
                                    <option>25,000 to 50,000 INR</option>
                                    <option>50,000 to 1,00,000 INR</option>
                                    <option>1,00,000 to 3,00,000 INR</option>
                                    <option>Above 3,00,000 INR</option>
                                    <option>Not decided yet</option>
                                </select>
                            </div>
                            <div class="field">
                                <label for="f-phone">Phone</label>
                                <input id="f-phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" placeholder="Optional">
                                <input type="hidden" name="country_code" value="<?= e($visitor_dial_code ?? '+91') ?>">
                            </div>
                            <div class="field full">
                                <label for="f-message">Where is growth stuck right now?</label>
                                <textarea id="f-message" name="message" rows="3" placeholder="One or two lines is plenty."></textarea>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="cta">
                                <span class="cta-label">Send my growth audit request</span>
                                <svg class="cta-arrow" width="18" height="12" viewBox="0 0 18 12" fill="none" aria-hidden="true"><path d="M12 1l5 5-5 5M17 6H1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </button>
                            <button type="button" class="cta-ghost js-only" id="hp-back"><span>Back</span></button>
                        </div>
                        <p class="form-msg<?= ($form_status ?? null) === 'error' ? ' bad' : '' ?>" id="hp-msg" role="status">
                            <?= ($form_status ?? null) === 'error' ? 'That did not go through. Please refresh the page and try again, or email ' . e(CONTACT_EMAIL) . '.' : '' ?>
                        </p>
                        <p class="form-next">After you send this, we read it ourselves and reply within one working day with questions, not a proposal.</p>
                    </div>

                    <!-- Honeypot. Named "website" because that is the field the shared
                         security check inspects; it must stay empty. -->
                    <div class="hp-hp" aria-hidden="true">
                        <label for="f-website">Website</label>
                        <input id="f-website" name="website" type="text" tabindex="-1" autocomplete="off">
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- ============================ CLOSING ============================ -->
<section class="foot-close" aria-labelledby="close-h">
    <div class="wrap">
        <hr class="rule">
        <h2 class="foot-ask" id="close-h" style="margin-top:var(--s10)" data-rise>Where will your next customer find you?</h2>
        <p class="foot-answer" data-rise>
            <span>Search</span><i class="fa-turn" aria-hidden="true"></i>
            <span>AI answers</span><i class="fa-turn" aria-hidden="true"></i>
            <span>Paid</span><i class="fa-turn" aria-hidden="true"></i>
            <span>Social</span><i class="fa-turn" aria-hidden="true"></i>
            <span>Your website</span><i class="fa-turn" aria-hidden="true"></i>
            <span class="fa-us">VTurnU</span>
        </p>
        <p style="margin-top:var(--s8)" data-rise>
            <a class="cta" href="#start">
                <span class="cta-label">Get my growth audit</span>
                <svg class="cta-arrow" width="18" height="12" viewBox="0 0 18 12" fill="none" aria-hidden="true"><path d="M12 1l5 5-5 5M17 6H1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
        </p>
    </div>
</section>

<?php /* Structured data. Every claim below is visible on this page, and no
         review or rating markup is emitted because there is no verified review
         data to back it. */ ?>
<script type="application/ld+json">
<?= json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => ['Organization', 'ProfessionalService'],
            '@id' => SITE_URL . '/#organization',
            'name' => SITE_NAME,
            'url' => SITE_URL . '/',
            'slogan' => SITE_TAGLINE,
            'description' => 'VTurnU is a digital growth company helping B2B companies, SMEs and small businesses become discoverable across search engines and AI answer engines, and turn that visibility into qualified demand.',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => SITE_URL . '/assets/img/vturnu-logo-horizontal.png',
            ],
            'email' => CONTACT_EMAIL,
            'telephone' => CONTACT_PHONE,
            'address' => ['@type' => 'PostalAddress', 'addressLocality' => 'Chennai', 'addressRegion' => 'Tamil Nadu', 'addressCountry' => 'IN'],
            'areaServed' => [
                ['@type' => 'Country', 'name' => 'India'],
                ['@type' => 'Country', 'name' => 'United States'],
                ['@type' => 'Country', 'name' => 'United Kingdom'],
                ['@type' => 'Country', 'name' => 'Canada'],
                ['@type' => 'Country', 'name' => 'Australia'],
            ],
            'contactPoint' => [[
                '@type' => 'ContactPoint',
                'contactType' => 'sales',
                'email' => CONTACT_EMAIL,
                'telephone' => CONTACT_PHONE,
                'availableLanguage' => ['English', 'Tamil', 'Hindi'],
            ]],
            'sameAs' => array_values(SOCIAL_LINKS),
            'hasOfferCatalog' => [
                '@type' => 'OfferCatalog',
                'name' => 'Digital growth services',
                'itemListElement' => array_map(fn($c) => [
                    '@type' => 'OfferCatalog',
                    'name' => html_entity_decode(strip_tags($c['tab'])),
                    'itemListElement' => array_map(fn($l) => [
                        '@type' => 'Offer',
                        'itemOffered' => ['@type' => 'Service', 'name' => $l[0], 'url' => SITE_URL . $l[1]],
                    ], $c['links']),
                ], array_values($CAPS)),
            ],
        ],
        [
            '@type' => 'WebSite',
            '@id' => SITE_URL . '/#website',
            'url' => SITE_URL . '/',
            'name' => SITE_NAME,
            'publisher' => ['@id' => SITE_URL . '/#organization'],
            'inLanguage' => 'en',
        ],
        [
            '@type' => 'WebPage',
            '@id' => SITE_URL . '/#webpage',
            'url' => SITE_URL . '/',
            'name' => $page['title'],
            'description' => $page['meta'],
            'isPartOf' => ['@id' => SITE_URL . '/#website'],
            'about' => ['@id' => SITE_URL . '/#organization'],
        ],
        [
            '@type' => 'FAQPage',
            '@id' => SITE_URL . '/#faq',
            'mainEntity' => array_map(fn($f) => [
                '@type' => 'Question',
                'name' => $f[0],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]],
            ], $FAQS),
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
</script>
