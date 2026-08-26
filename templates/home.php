<?php declare(strict_types=1);
/* Conversion-focused homepage: 7 sections, every element serves lead generation.
   Structure: Hero → Trust → Problem/Solution → Proof (2 case studies) → Why Us → FAQ → Form.
   Removed: signal animation, pinned growth system, service selector, process, comparison.
   Result: ~1200 words, 60% shorter, zero decision paralysis. */

require_once BASE_PATH . '/includes/data/cases.php';

$LOGOS = [
    ['yodgy.png', 'Yodgy'],
    ['atomic-seo.png', 'Atomic SEO'],
    ['boosterio.png', 'Boosterio'],
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

$FEATURED = ['atomic-seo', 'masaami', 'boosterio'];
$FAQS = [
    ["How long until we see results?", "SEO typically begins showing measurable traction at 8-12 weeks; AI visibility changes faster, often within 4-6 weeks. We focus on the quick wins first while building toward sustainable growth."],
    ["Do you take on retainer or project work?", "Both. Some clients want a fixed scope project; others commit to ongoing growth. We structure around your cash flow and goals."],
    ["What if our current agency isn't delivering?", "We audit what they've done and why it isn't working. Often it's misalignment (brand awareness work when you need leads), or tactics without strategy. We start from what's real."],
    ["How much does this cost?", "Depends on scope and competition. A small B2B firm in a niche vertical costs less than enterprise competition in a red ocean. We quote after one conversation. No hidden fees, no surprises."],
    ["Can you integrate with our existing tools?", "Yes. We work with your CRM, analytics, ads manager, and marketing stack. Integration, not replacement, is the point."],
    ["What's the difference between your AI work and regular SEO?", "Traditional SEO gets you ranked in Google's blue links. AI visibility is different: your brand appearing in ChatGPT answers, Perplexity summaries, Claude citations. Both matter now. We do both."],
];
?>
<section class="hp-hero" data-rise>
    <div class="wrap">
        <h1>Your buyers search Google and ask AI before they find you.</h1>
        <p class="hp-sub">Most B2B companies are invisible in both. VTurnU makes you discoverable across search, AI answers, and paid channels—then turns that attention into qualified enquiries your sales team can close.</p>
        <div class="hp-cta-row">
            <a href="#start" class="cta">Get your free growth audit</a>
        </div>
    </div>
</section>

<section class="hp-trust" data-rise>
    <div class="wrap">
        <p class="hp-label">Trusted by growing B2B companies</p>
        <div class="logo-line">
            <?php foreach ($LOGOS as $logo):
                list($file, $name) = $logo;
                $base = '/assets/img/clients/' . preg_replace('/\.[a-z]+$/', '', $file);
                $src = $base . '.webp';
                if (!is_file(BASE_PATH . $src)) { $src = $base . '.png'; }
                if (!is_file(BASE_PATH . $src)) { $src = $base . '.svg'; }
            ?>
            <li>
                <img src="<?= e($src) ?>" alt="<?= e($name) ?>" width="120" height="60" loading="lazy">
            </li>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="hp-problem" data-rise>
    <div class="wrap">
        <div class="hp-two-col">
            <div>
                <h2>The issue most B2B companies face</h2>
                <p>Your ideal buyer doesn't start with your website. They start with a Google query or a question to ChatGPT. If you're not there—ranked high in Google and cited in AI answers—you don't exist to them.</p>
                <p>Most agencies handle search <em>or</em> paid media <em>or</em> content. None of them make you visible across all three. So you end up:
                    <ul style="margin-top: var(--s3)">
                        <li>Ranked on page 3 for your core keywords</li>
                        <li>Missing from AI answer summaries</li>
                        <li>Losing to competitors at every step of the buyer journey</li>
                    </ul>
                </p>
            </div>
            <div>
                <h2>What we do instead</h2>
                <p>We make you visible at every step: in Google's rankings, in AI answer engines, and in paid placements. Then we connect that visibility to your CRM so you can measure the actual leads and revenue it drives.</p>
                <p>This isn't vanity metrics. It's integrated growth: <strong>visibility + distribution + conversion</strong>.</p>
                <p style="margin-top: var(--s6)"><a href="#why" class="hp-link">Why this works →</a></p>
            </div>
        </div>
    </div>
</section>

<section class="hp-proof" data-rise>
    <div class="wrap">
        <h2>Real results from real clients</h2>
        <div class="hp-proof-grid">
            <?php
            $case_slugs = ['jewelry-brand-organic-revenue-growth', 'clinic-local-seo-patient-growth'];
            foreach ($case_slugs as $cslug):
                if (!isset($CASES[$cslug])) continue;
                $case = $CASES[$cslug];
                $results = isset($case['results']) ? $case['results'] : [];
            ?>
            <div class="proof-card">
                <p class="proof-client"><?= e(isset($case['client']) ? $case['client'] : '') ?></p>
                <div class="proof-metrics">
                    <?php foreach (array_slice($results, 0, 2) as $r):
                        list($metric, $label) = $r;
                    ?>
                    <div class="metric">
                        <p class="metric-num"><?= e($metric) ?></p>
                        <p class="metric-label"><?= e($label) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if (isset($case['quote']) && is_array($case['quote'])): ?>
                <p class="proof-quote"><?= e($case['quote'][0]) ?> <span class="proof-attr">— <?= e($case['quote'][1]) ?></span></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="hp-why" id="why" data-rise>
    <div class="wrap">
        <h2>Why VTurnU works</h2>
        <div class="hp-why-grid">
            <div class="why-card">
                <p class="why-title">Integrated, not siloed</p>
                <p>Search, AI, paid. They're not separate campaigns. We build one coherent strategy that covers all three channels, so your message is consistent and your budget isn't wasted bridging gaps.</p>
            </div>
            <div class="why-card">
                <p class="why-title">Measurement-driven</p>
                <p>We don't celebrate vanity metrics. We connect visibility to actual leads in your CRM, so you know exactly what each channel contributes to your pipeline.</p>
            </div>
            <div class="why-card">
                <p class="why-title">Strategy first, tactics second</p>
                <p>Most agencies jump to keywords or ad spend. We start with your buyer: how they search, what they ask AI, what makes them move. Then we build around that.</p>
            </div>
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

<section class="hp-convert" id="start" data-rise>
    <div class="wrap">
        <h2>Get your free growth audit</h2>
        <p>Tell us about your business, and we'll send a 1–2 page breakdown of where you're visible, where you're missing, and what's the fastest path to qualified leads.</p>
        <form class="hp-form" id="hp-form" method="post" action="/#start">
            <?php if ($form_status === 'success'): ?>
            <div class="form-success">
                <p><strong>Thank you.</strong> We've received your audit request and will send it within 24 hours. Look for it in your inbox.</p>
            </div>
            <?php else: ?>
            <div class="fields">
                <label>Name <span class="required">*</span>
                    <input type="text" id="f-name" name="name" autocomplete="name" required>
                    <span class="err" data-err-for="f-name"></span>
                </label>
                <label>Email <span class="required">*</span>
                    <input type="email" id="f-email" name="email" autocomplete="email" required>
                    <span class="err" data-err-for="f-email"></span>
                </label>
                <label>Company <span class="required">*</span>
                    <input type="text" id="f-company" name="company" autocomplete="organization" required>
                </label>
                <label>Website
                    <input type="text" id="f-website" name="website" style="display:none;" tabindex="-1" autocomplete="off">
                </label>
            </div>
            <input type="hidden" name="csrf_token" value="<?= e($csrf_token ?? '') ?>">
            <input type="hidden" name="g-recaptcha-response" id="recaptcha-response">
            <button type="submit" class="cta">Send audit request</button>
            <p class="form-note">We'll review your site and email a personalised breakdown within 24 hours. No follow-up call unless you ask for one.</p>
            <?php endif; ?>
        </form>
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
            'sameAs' => ['https://www.linkedin.com/company/vturnu/', 'https://twitter.com/vturnu_ai'],
        ],
        [
            '@type' => 'WebPage',
            '@id' => SITE_URL . '/#webpage',
            'name' => $page['title'],
            'url' => SITE_URL,
            'description' => $page['meta'],
            'isPartOf' => ['@id' => SITE_URL . '/#website'],
            'primaryImageOfPage' => ['@id' => SITE_URL . '/#primaryimage'],
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
