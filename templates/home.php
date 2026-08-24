<?php /** Home page, from "VTurnU Website Revamp" design, light gradient theme.
 * Expects: $page, $slug, $form_status */

$home_faqs = [
    ['What makes VTurnU the best digital marketing agency in Chennai?', 'We keep a selective client roster and tie every engagement to measurable outcomes, 80%+ average organic traffic growth and 50%+ ROAS growth on paid campaigns. Senior specialists work on your account directly; nothing is outsourced or templated.'],
    ['Do you only work with businesses in Chennai?', 'No. Chennai is home, but we serve startups, SMBs and enterprises across India, the US, UK and Australia. Strategy, reporting and communication are built for remote collaboration across time zones.'],
    ['What is AEO and why does it matter now?', 'Answer Engine Optimization makes your brand the answer AI assistants give. As search shifts to ChatGPT, Gemini and Google AI Overviews, we structure your content, schema and authority signals so AI engines cite you, not your competitors.'],
    ['How soon will we see results?', 'Paid campaigns typically show traction within 4–6 weeks. SEO compounds over 6–9 months, that is where our 80%+ average organic growth figure comes from. We set realistic milestones in your roadmap before you commit.'],
    ['How does pricing work?', 'Every engagement is custom-quoted based on your goals, market and channel mix, no one-size-fits-all packages. Request a quote and we will respond within one business day with honest recommendations and a mini-audit.'],
];

/* Organization (not LocalBusiness) so the entity is defined by what it does and
   who it serves, with no postal address published. */
echo jsonld_script([
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    '@id' => SITE_URL . '/#organization',
    'name' => SITE_NAME,
    'slogan' => SITE_TAGLINE,
    'description' => 'Digital marketing agency offering SEO, AEO, PPC, social media marketing, web development and AI development for businesses in India, the US, UK and Australia.',
    'url' => SITE_URL . '/',
    'email' => CONTACT_EMAIL,
    'telephone' => CONTACT_PHONE,
    'areaServed' => ['India', 'United States', 'United Kingdom', 'Australia', 'Canada'],
    'knowsAbout' => [
        'Search Engine Optimization', 'Answer Engine Optimization', 'Generative Engine Optimization',
        'Google Ads', 'Meta Ads', 'Social Media Marketing', 'Content Marketing',
        'Web Development', 'Ecommerce Development', 'AI Development',
    ],
    'contactPoint' => [[
        '@type' => 'ContactPoint',
        'contactType' => 'sales',
        'email' => CONTACT_EMAIL,
        'telephone' => CONTACT_PHONE,
        'availableLanguage' => ['English', 'Tamil', 'Hindi'],
        'areaServed' => ['IN', 'US', 'GB', 'AU', 'CA'],
    ]],
]);
echo jsonld_script(jsonld_faq($home_faqs));
?>

<!-- Announcement bar -->
<div class="announce">
    <span class="announce-dot" aria-hidden="true"></span>
    <span>Now onboarding a limited number of growth-serious clients for Q3 2026</span>
    <a href="#quote">Request a quote →</a>
</div>

<!-- Hero, exact replica of vturnu-hero.html -->
<section class="vturnu-hero" id="top">
    <div class="hero-main">
        <div class="hero-left">
            <div class="hero-badge">
                <span></span> AI-first Digital &amp; IT Consulting · Chennai, IN
            </div>

            <h1 class="hero-h1">
                <span class="hh-v">V</span> turn <span class="hh-u">Ur</span> <br>ideas <em>into revenue</em>
            </h1>

            <p class="hero-desc">
                SEO, performance ads, content and AI automation, engineered for the one metric that matters: your ROI.
            </p>

            <div class="hero-ctas">
                <a class="btn btn-primary" href="/contact-us/">Book a strategy call</a>
                <?php /* Was "Free Website Audit Tool". Points at our own product
                         page rather than straight at vturnai.com, so homepage
                         traffic is warmed up on our domain first and only
                         converted intent leaves the site. The free audit tool
                         is still reachable from the nav, footer and the
                         product page's comparison table. */ ?>
                <a class="btn btn-secondary" href="/ai-visibility-tool/">Check My AI Visibility</a>
                <a class="btn-whatsapp" href="https://wa.me/919363731498?text=Hi%20VTurnU%2C%20I%27d%20like%20to%20grow%20my%20business.%20Can%20we%20talk%3F" target="_blank" rel="noopener"><span>✆</span> WhatsApp</a>
            </div>

            <div class="hero-stats">
                <div class="stat">
                    <div class="stat-num" data-count="50" data-prefix="+" data-suffix="%">+50%</div>
                    <div class="stat-label">leads via AI automation</div>
                </div>
                <div class="stat">
                    <div class="stat-num" data-count="20" data-prefix="−" data-suffix="%">−20%</div>
                    <div class="stat-label">wasted ad spend</div>
                </div>
                <div class="stat">
                    <div class="stat-num">5.0★</div>
                    <div class="stat-label">Google Business rating</div>
                </div>
                <div class="stat">
                    <div class="stat-num" data-count="240" data-suffix="%">240%</div>
                    <div class="stat-label">social engagement growth</div>
                </div>
            </div>
        </div>

        <div class="hero-right">
            <div class="viz-dash" aria-hidden="true">
                <div class="dash-head">
                    <span class="dash-brand"><img src="/assets/img/vturnu-icon-mark.svg" alt="" width="22" height="21"> Growth Dashboard</span>
                    <span class="dash-live"><i></i>LIVE</span>
                </div>
                <div class="dash-kpis">
                    <div class="dash-kpi"><small>Organic sessions</small><strong>48.2K</strong><em>▲ 27%</em></div>
                    <div class="dash-kpi"><small>Leads</small><strong>1,847</strong><em>▲ 35%</em></div>
                    <div class="dash-kpi"><small>ROAS</small><strong>4.6×</strong><em>▲ 18%</em></div>
                </div>
                <div class="dash-chart">
                    <svg viewBox="0 0 320 120" preserveAspectRatio="none" focusable="false">
                        <path class="dash-area" d="M0,100 C40,92 60,78 90,72 C120,66 150,58 180,48 C210,38 240,34 270,22 C290,14 305,10 320,6 L320,120 L0,120 Z"/>
                        <path class="dash-line" d="M0,100 C40,92 60,78 90,72 C120,66 150,58 180,48 C210,38 240,34 270,22 C290,14 305,10 320,6"/>
                    </svg>
                </div>
                <div class="dash-bars">
                    <i style="--h:.35"></i><i style="--h:.5"></i><i style="--h:.42"></i><i style="--h:.62"></i><i style="--h:.55"></i><i style="--h:.78"></i><i style="--h:.92"></i>
                </div>
            </div>

            <div class="card-float card-float-1">
                <div class="card-label">Organic traffic</div>
                <div class="card-value">+312%</div>
                <div class="card-change">↗ trending</div>
            </div>

            <div class="card-float card-float-2">
                <div class="card-label">Leads (Q2 2026)</div>
                <div class="card-value">1,847</div>
                <div class="card-change">▲ 35% growth</div>
            </div>
        </div>
    </div>

    <div class="ticker">
        <div class="ticker-content">
            <?php $ticker_items = ['SEO', 'ANSWER ENGINE OPTIMIZATION', 'PPC / SEM', 'SOCIAL MEDIA', 'AI AUTOMATION', 'WEB DEVELOPMENT', 'UI/UX DESIGN', 'CONTENT', 'LOCAL SEO']; ?>
            <?php for ($t = 0; $t < 2; $t++): foreach ($ticker_items as $tk): ?><span><?= e($tk) ?></span><span class="ticker-dot">✦</span><?php endforeach; endfor; ?>
        </div>
    </div>
</section>

<!-- Trust bar -->
<section class="trustbar" aria-label="Ratings and clients">
    <div class="container trustbar-inner">
        <div class="ratings">
            <div><span class="stars">★★★★★</span><small>5.0 on Google</small></div>
            <div><span class="stars">★★★★★</span><small>Listed on Clutch</small></div>
            <div><span class="stars">★★★★★</span><small>Listed on GoodFirms</small></div>
        </div>
        <div class="trust-divider" aria-hidden="true"></div>
        <div class="marquee-mask" aria-hidden="true">
            <div class="marquee-track">
                <?php $clients = [
                    ['SAI Impression', '/assets/img/clients/sai-impression.webp'],
                    ['Ateliers Gym', '/assets/img/clients/ateliers-gym.png'],
                    ['Masaami', '/assets/img/clients/masaami.webp'],
                    ['The Black Sheep Collective', '/assets/img/clients/black-sheep-collective.png'],
                    ['Shineprints', '/assets/img/clients/shineprints.png'],
                    ['UdayPedia', '/assets/img/clients/udaypedia.svg'],
                    ['Yodgy', '/assets/img/clients/yodgy.png'],
                    ['Atomic SEO', '/assets/img/clients/atomic-seo.png'],
                    ['Boosterio', '/assets/img/clients/boosterio.png'],
                ]; ?>
                <?php foreach (array_merge($clients, $clients) as [$name, $src]): ?>
                <span class="client-logo"><img src="<?= e($src) ?>" alt="<?= e($name) ?>" width="120" height="40" loading="lazy" decoding="async"></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- Services -->
<section class="section" id="services">
    <div class="container">
        <div class="sec-head" data-reveal="head">
            <div>
                <p class="eyebrow">What we do</p>
                <h2 class="section-title">Every service, one obsession: <span class="grad-text">ROI.</span></h2>
            </div>
            <p class="sec-head-note">Full-stack digital growth, from being found, to being chosen, to being remembered.</p>
        </div>
        <div class="card-grid cols-3 svc-grid">
            <?php
            $services = [
                ['01', 'SEO & AEO', 'Rank on Google, and get cited by ChatGPT, Gemini & AI Overviews. Technical SEO, content strategy and answer-engine optimization that compounds.', '80%+ avg. organic growth', '/seo/', 'ac-yellow', 'See SEO plans'],
                ['02', 'Paid Advertising (PPC)', 'Google Ads and Meta campaigns run by performance specialists: structured for profit, not vanity clicks.', '50%+ ROAS growth', '/paid-advertising/', 'ac-terra', 'Scale my ads'],
                ['03', 'Social Media Marketing', 'Content systems and community strategy that turn followers into pipeline: Instagram, LinkedIn, YouTube.', 'Content → conversations → clients', '/social-media-marketing/', 'ac-purple', 'Grow my audience'],
                ['04', 'Web Development', 'Fast, conversion-engineered websites, built to score green on Core Web Vitals and turn visits into enquiries.', 'Speed + design + conversion', '/web-services/', 'ac-olive', 'Build my website'],
                ['05', 'AI Development', 'Chatbots, automations and AI integrations with clean UX, from MVP to scale, designed around your business model.', 'MVP → market → scale', '/ai-development/', 'ac-cyan', 'Ship my AI product'],
                ['06', 'Design & Branding', 'Identity systems and creative that make your brand look like the category leader it\'s becoming.', 'Look premium. Charge premium.', '/content-marketing/', 'ac-pink', 'Upgrade my brand'],
            ];
            foreach ($services as [$num, $title, $desc, $metric, $url, $accent, $go]): ?>
            <a class="card svc-card <?= $accent ?>" href="<?= e($url) ?>" data-reveal="card">
                <span class="svc-bar" aria-hidden="true"></span>
                <span class="svc-num"><?= e($num) ?></span>
                <h3><?= e($title) ?></h3>
                <p><?= e($desc) ?></p>
                <span class="svc-metric"><?= e($metric) ?></span>
                <span class="svc-go"><?= e($go) ?> <em aria-hidden="true">→</em></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Why VTurnU -->
<section class="section why-band" id="results">
    <div class="container split">
        <div data-reveal>
            <p class="eyebrow">Why VTurnU</p>
            <h2 class="section-title">We don't take every client. <span class="grad-text">On purpose.</span></h2>
            <p>We partner only with businesses that are serious about their digital presence and ROI. Fewer clients means senior attention on every account, strategies built for your market, not recycled templates, and reporting you'll actually read.</p>
            <ul class="check-list">
                <li><strong>Selective roster</strong>, we onboard a limited number of clients per quarter</li>
                <li><strong>ROI-first contracts</strong>, every deliverable maps to a revenue metric</li>
                <li><strong>Full-stack under one roof</strong>: marketing, web, app and brand teams working as one</li>
            </ul>
            <a class="btn btn-grad" href="#quote">See if we're a fit</a>
        </div>
        <div class="why-stats">
            <div class="why-stat ac-yellow" data-reveal="stat"><span class="counter grad-text" data-count="80" data-suffix="%+">80%+</span><small>average organic traffic growth within 6–9 months</small></div>
            <div class="why-stat ac-pink" data-reveal="stat"><span class="counter grad-text" data-count="50" data-suffix="%+">50%+</span><small>ROAS growth on Google &amp; Meta ad accounts we manage</small></div>
            <div class="why-stat ac-cyan" data-reveal="stat"><span class="counter grad-text" data-count="5" data-suffix="+">5+</span><small>years growing brands across India, US, UK &amp; Australia</small></div>
            <div class="why-stat ac-purple" data-reveal="stat"><span class="counter grad-text" data-count="10" data-suffix="+">10+</span><small>end-to-end projects delivered: web, app &amp; brand</small></div>
        </div>
    </div>
</section>

<!-- Process -->
<section class="section" id="process">
    <div class="container">
        <div data-reveal>
            <p class="eyebrow">How we work</p>
            <h2 class="section-title">From first call to first result</h2>
        </div>
        <div class="proc-grid">
            <?php
            $steps = [
                ['STEP 1', 'Discovery & audit', 'We study your market, competitors and current digital footprint, and tell you honestly if we can move the needle.', 'accent-yellow'],
                ['STEP 2', 'Strategy & roadmap', 'A quarter-by-quarter growth plan with clear KPIs, channel mix and investment, signed off before work begins.', 'accent-cyan'],
                ['STEP 3', 'Build & launch', 'Campaigns, content, websites or apps: shipped in sprints, with you in the loop at every milestone.', 'accent-pink'],
                ['STEP 4', 'Measure & scale', 'Monthly reporting on revenue metrics: double down on what works, cut what doesn\'t, compound the wins.', 'accent-purple'],
            ];
            foreach ($steps as [$label, $title, $desc, $accent]): ?>
            <div class="proc-card <?= $accent ?>" data-reveal="card">
                <span class="proc-step"><?= e($label) ?></span>
                <h3><?= e($title) ?></h3>
                <p><?= e($desc) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php render_compare(); ?>

<!-- Testimonials -->
<section class="section section-tint" aria-label="Client testimonials">
    <div class="container">
        <div data-reveal>
            <p class="eyebrow">Client words</p>
            <h2 class="section-title">Trusted by owners who track every rupee</h2>
        </div>
        <div class="card-grid cols-3">
            <?php
            $testimonials = [
                ['Our organic enquiries nearly doubled in eight months. VTurnU\'s reporting is the first I\'ve seen that talks revenue, not just rankings.', 'R', 'Founder · E-commerce, Chennai'],
                ['They rebuilt our website and ad funnels together, cost per lead dropped by a third while lead quality went up.', 'D', 'Director · Healthcare, UK'],
                ['A small, senior team that actually answers the phone. Our app shipped on time and the launch campaign paid for itself in week one.', 'C', 'CEO · SaaS startup, Australia'],
            ];
            foreach ($testimonials as [$quote, $initial, $role]): ?>
            <figure class="card tsm-card" data-reveal="card">
                <span class="stars">★★★★★</span>
                <blockquote>"<?= e($quote) ?>"</blockquote>
                <figcaption>
                    <span class="tsm-avatar" aria-hidden="true"><?= e($initial) ?></span>
                    <span><strong>Verified client</strong><br><small><?= e($role) ?></small></span>
                </figcaption>
            </figure>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="section" id="faq">
    <div class="container narrow">
        <div class="center-text" data-reveal="head">
            <p class="eyebrow">FAQ</p>
            <h2 class="section-title">Questions clients ask us</h2>
        </div>
        <div class="faq-list">
            <?php foreach ($home_faqs as [$q, $a]): ?>
            <details class="faq">
                <summary><?= e($q) ?></summary>
                <p><?= e($a) ?></p>
            </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Quote form -->
<section class="quote-band" id="quote">
    <div class="container quote-inner">
        <div class="quote-copy" data-reveal="left">
            <p class="eyebrow">Get a quote</p>
            <h2 class="section-title">Ready to grow? Tell us where it hurts.</h2>
            <p>Share a few details and we'll come back within one business day with honest next steps, and a custom quote if we're the right fit.</p>
            <ul class="arrow-list">
                <li>No pushy sales calls, ever</li>
                <li>Free mini-audit with every quote request</li>
                <li>Chennai HQ · serving clients worldwide</li>
            </ul>
        </div>
        <div class="quote-form-wrap" data-reveal="right">
            <?php if ($form_status === 'success'): ?>
                <div class="quote-done">
                    <span class="quote-done-icon" aria-hidden="true">✓</span>
                    <h3>Request received</h3>
                    <p>We'll review your details and reply within one business day with your mini-audit and quote.</p>
                </div>
            <?php else: ?>
                <?php if ($form_status === 'error'): ?>
                <div class="alert alert-error" role="alert">Please fill in your name and a valid email address, then try again.</div>
                <?php endif; ?>
                <form class="contact-form raw-lead-form" method="post" action="/#quote">
                    <?= csrf_field() ?>
                    <input type="hidden" name="recaptcha_token" class="js-recaptcha-token">
                    <div class="form-grid">
                        <div class="form-row"><label for="q-name">Your name *</label><input id="q-name" name="name" type="text" placeholder="Full name" required autocomplete="name"></div>
                        <div class="form-row"><label for="q-company">Company</label><input id="q-company" name="company" type="text" placeholder="Business name" autocomplete="organization"></div>
                    </div>
                    <div class="form-grid">
                        <div class="form-row"><label for="q-email">Email *</label><input id="q-email" name="email" type="email" placeholder="you@company.com" required autocomplete="email"></div>
                        <div class="form-row"><label for="q-phone">Phone / WhatsApp</label>
                            <div class="phone-group"><?= country_code_select('country_code', 'q-cc') ?><input id="q-phone" name="phone" type="tel" inputmode="tel" placeholder="98765 43210" autocomplete="tel-national"></div>
                        </div>
                    </div>
                    <div class="form-row">
                        <label for="q-service">What do you need?</label>
                        <select id="q-service" name="service">
                            <option>SEO / AEO</option>
                            <option>Paid advertising (Google / Meta)</option>
                            <option>Social media marketing</option>
                            <option>Web development</option>
                            <option>AI development</option>
                            <option>Design &amp; branding</option>
                            <option>Full-stack growth (multiple)</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label for="q-budget">Monthly budget range</label>
                        <select id="q-budget" name="budget">
                            <option>Under ₹50k / $600</option>
                            <option>₹50k–₹1.5L / $600–$1,800</option>
                            <option>₹1.5L–₹5L / $1,800–$6,000</option>
                            <option>₹5L+ / $6,000+</option>
                            <option>One-time project, need a quote</option>
                        </select>
                    </div>
                    <div class="form-row"><label for="q-message">Tell us about your goals</label><textarea id="q-message" name="message" rows="3" placeholder="e.g. We want more qualified leads from Google in the next 6 months…"></textarea></div>
                    <div class="form-row honeypot" aria-hidden="true"><label for="q-website">Website</label><input id="q-website" name="website" type="text" tabindex="-1" autocomplete="off"></div>
                    <button class="btn btn-grad btn-block" type="submit">Get My Custom Quote</button>
                    <p class="form-privacy">We reply within 1 business day. Your details stay private.</p>
                    <p class="recaptcha-disclosure">This site is protected by reCAPTCHA and the Google <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Privacy Policy</a> and <a href="https://policies.google.com/terms" target="_blank" rel="noopener">Terms of Service</a> apply.</p>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>
