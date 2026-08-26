<?php declare(strict_types=1);
/* Visual-first, premium homepage. Minimal text, maximum design.
   Every pixel serves the conversion: the growth audit form.
   Removed: first 3 logos, text filler, corporate copy.
   Added: visual hierarchy, depth, premium animations. */

require_once BASE_PATH . '/includes/data/cases.php';

$LOGOS = [
    ['shineprints.png', 'Shineprints'],
    ['masaami.webp', 'Masaami'],
    ['ateliers-gym.webp', 'Ateliers Gym'],
    ['sai-impression.webp', 'Sai Impression'],
    ['black-sheep-collective.png', 'Black Sheep Collective'],
];
foreach ($LOGOS as $i => $logo) {
    list($f, $n) = $logo;
    if (!is_file(BASE_PATH . '/assets/img/clients/' . $f)) {
        foreach (['png', 'webp', 'svg'] as $ext) {
            $try = preg_replace('/\.[a-z]+$/', '.' . $ext, $f);
            if (is_file(BASE_PATH . '/assets/img/clients/' . $try)) { $LOGOS[$i][0] = $try; break; }
        }
    }
}

$FAQS = [
    ["How long until we see results?", "SEO typically shows measurable traction at 8-12 weeks. AI visibility is faster, 4-6 weeks."],
    ["Do you take retainer or project work?", "Both. We structure around your cash flow and growth goals."],
    ["What if our current agency isn't delivering?", "We audit what they've done and why it isn't working. Then we rebuild the strategy."],
    ["How much does this cost?", "Depends on scope and competition. We quote after one conversation. No surprises."],
    ["Can you integrate with our existing tools?", "Yes. CRM, analytics, ads manager. Integration, not replacement."],
    ["What's the difference between your AI work and regular SEO?", "Traditional SEO gets you ranked in Google. AI visibility puts your brand in ChatGPT, Perplexity, Claude summaries."],
];
?>
<section class="hp-hero" data-rise>
    <div class="hp-hero-bg"></div>
    <div class="wrap">
        <div class="hp-hero-content">
            <h1>Your buyers search Google and ask AI before they find you.</h1>
            <p class="hp-hero-sub">Most B2B companies are invisible in both.</p>
            <a href="#start" class="cta cta-large">Get your free growth audit</a>
        </div>
    </div>
</section>

<section class="hp-logos" data-rise>
    <div class="wrap">
        <p class="hp-label">Trusted by</p>
        <div class="logo-grid">
            <?php foreach ($LOGOS as $logo):
                list($file, $name) = $logo;
                $base = '/assets/img/clients/' . preg_replace('/\.[a-z]+$/', '', $file);
                $src = $base . '.webp';
                if (!is_file(BASE_PATH . $src)) { $src = $base . '.png'; }
                if (!is_file(BASE_PATH . $src)) { $src = $base . '.svg'; }
            ?>
            <div class="logo-item">
                <img src="<?= e($src) ?>" alt="<?= e($name) ?>" loading="lazy">
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="hp-value" data-rise>
    <div class="wrap">
        <div class="value-trio">
            <div class="value-card">
                <div class="value-icon">🔍</div>
                <h3>Visible in search</h3>
                <p>Google rankings that convert, not just traffic.</p>
            </div>
            <div class="value-card">
                <div class="value-icon">💭</div>
                <h3>Named in AI answers</h3>
                <p>ChatGPT, Perplexity, Claude cite your brand.</p>
            </div>
            <div class="value-card">
                <div class="value-icon">📞</div>
                <h3>Qualified leads</h3>
                <p>Visibility means nothing without the enquiries.</p>
            </div>
        </div>
    </div>
</section>

<section class="hp-proof" data-rise>
    <div class="wrap">
        <h2>Real results</h2>
        <div class="proof-grid">
            <?php
            $case_slugs = ['jewelry-brand-organic-revenue-growth', 'clinic-local-seo-patient-growth'];
            foreach ($case_slugs as $cslug):
                if (!isset($CASES[$cslug])) continue;
                $case = $CASES[$cslug];
                $results = isset($case['results']) ? $case['results'] : [];
            ?>
            <div class="proof-card">
                <div class="proof-headline">
                    <?php if (isset($results[0])): list($m, $l) = $results[0]; ?>
                    <p class="proof-stat"><?= e($m) ?></p>
                    <p class="proof-label"><?= e($l) ?></p>
                    <?php endif; ?>
                </div>
                <p class="proof-client"><?= e(isset($case['client']) ? $case['client'] : '') ?></p>
                <?php if (isset($case['quote']) && is_array($case['quote'])): ?>
                <p class="proof-quote"><?= e($case['quote'][0]) ?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="hp-faq" data-rise>
    <div class="wrap">
        <h2>Questions?</h2>
        <div class="faq-list">
            <?php foreach ($FAQS as $faq):
                list($q, $a) = $faq;
            ?>
            <details class="faq-item">
                <summary><?= e($q) ?></summary>
                <div class="fq-a"><?= e($a) ?></div>
            </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="hp-form" id="start" data-rise>
    <div class="wrap">
        <div class="form-container">
            <h2>Your free growth audit</h2>
            <p>Tell us about your business. We'll send a one-page breakdown of where you're visible and where you're missing.</p>
            <form class="hp-form-el" id="hp-form" method="post" action="/#start">
                <?php if ($form_status === 'success'): ?>
                <div class="form-success">
                    <p>✓ We've received your audit request. Look for it in your inbox within 24 hours.</p>
                </div>
                <?php else: ?>
                <div class="form-fields">
                    <input type="text" id="f-name" name="name" placeholder="Your name" autocomplete="name" required>
                    <span class="err" data-err-for="f-name"></span>

                    <input type="email" id="f-email" name="email" placeholder="Email address" autocomplete="email" required>
                    <span class="err" data-err-for="f-email"></span>

                    <input type="text" id="f-company" name="company" placeholder="Company" autocomplete="organization" required>

                    <input type="text" id="f-website" name="website" style="display:none;" tabindex="-1" autocomplete="off">
                </div>
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token ?? '') ?>">
                <input type="hidden" name="g-recaptcha-response" id="recaptcha-response">
                <button type="submit" class="cta cta-large">Send audit request</button>
                <p class="form-note">No follow-up call unless you ask for one.</p>
                <?php endif; ?>
            </form>
        </div>
    </div>
</section>

<?php
$json_ld = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'Organization',
            '@id' => SITE_URL . '/#organization',
            'name' => 'VTurnU',
            'url' => SITE_URL,
            'logo' => abs_url('/assets/img/vturnu-logo-dark.png'),
            'description' => $page['meta'],
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'contactType' => 'Customer Service',
                'email' => 'hello@vturnu.com',
            ],
        ],
        [
            '@type' => 'FAQPage',
            'mainEntity' => array_map(function($item) {
                return [
                    '@type' => 'Question',
                    'name' => $item[0],
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item[1]],
                ];
            }, $FAQS),
        ],
    ],
];
echo jsonld_script($json_ld) . "\n";
?>
