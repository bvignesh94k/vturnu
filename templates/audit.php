<?php /** Free SEO audit tool. Expects: $page, $audit_result, $audit_error, $audit_input */

$labels = [
    'pass' => ['Passed', 'ok'],
    'warn' => ['Needs attention', 'warn'],
    'fail' => ['Critical', 'bad'],
];
?>
<?php render_hero([
    'k' => 'Free tool, no signup', 'h1' => $page['h1'], 'lede' => $page['lede'], 'tone' => 'mint', 'layout' => 'tight',
    'visual' => hero_visual(['gauge', ['Speed', 'Indexing', 'Titles and meta', 'Structure']]),
]); ?>

<section class="section">
    <div class="container narrow">

        <?php if ($audit_error !== ''): ?>
            <div class="alert alert-error" role="alert"><?= e($audit_error) ?></div>
        <?php endif; ?>

        <?php if (!$audit_result): ?>
        <div class="audit-two-col">
            <div class="panel audit-form-panel">
                <h2>Check your website</h2>
                <p>Two fields, no signup. Enter your site and where to send the report.</p>
                <form method="post" action="/free-seo-audit/" class="contact-form audit-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="recaptcha_token" class="js-recaptcha-token">
                    <div class="form-row">
                        <label for="a-url">Your website *</label>
                        <input id="a-url" name="site_url" type="text" inputmode="url" placeholder="yourcompany.com" required value="<?= e($audit_input['url']) ?>">
                    </div>
                    <div class="form-row">
                        <label for="a-email">Email, to receive your report *</label>
                        <input id="a-email" name="email" type="email" placeholder="you@company.com" required autocomplete="email" value="<?= e($audit_input['email']) ?>">
                    </div>
                    <div class="form-row honeypot" aria-hidden="true">
                        <label for="a-website">Website</label>
                        <input id="a-website" name="website" type="text" tabindex="-1" autocomplete="off">
                    </div>
                    <button class="btn btn-grad btn-block" type="submit">Run My Free Audit</button>
                    <p class="form-privacy">Results appear here instantly and a copy goes to your inbox. We never share your details.</p>
                    <p class="recaptcha-disclosure">This site is protected by reCAPTCHA and the Google <a href="https://policies.google.com/privacy" target="_blank" rel="noopener nofollow">Privacy Policy</a> and <a href="https://policies.google.com/terms" target="_blank" rel="noopener nofollow">Terms of Service</a> apply.</p>
                </form>
            </div>

            <div class="panel panel-accent audit-lead-panel">
                <h2>Want a strategist to review it with you?</h2>
                <p>Skip the wait. Tell us about your goals and a senior strategist replies within one business day with a growth plan, not just a report.</p>
                <?php render_lead_form_card(['uid' => 'au', 'source' => 'audit-consultation', 'service' => 'SEO', 'button' => 'Get my free consultation']); ?>
            </div>
        </div>

        <div class="audit-teaser">
            <h3>What the audit checks</h3>
            <div class="audit-teaser-grid">
                <div><strong>Indexability &amp; speed</strong><span>HTTPS, noindex traps, canonical tags, robots.txt, sitemap, server response, compression</span></div>
                <div><strong>On-page SEO</strong><span>Title and description length, H1 structure, content depth, image alt text, mobile viewport</span></div>
                <div><strong>AI search readiness</strong><span>Schema markup, FAQ data, and whether ChatGPT, Claude, Perplexity or Google AI are blocked</span></div>
            </div>
            <p class="audit-also">Need a crawler-by-crawler answer for one page? Try the free <a href="/ai-crawler-checker/">AI Crawler Checker</a>.</p>
        </div>

        <?php /* Shown before the audit runs too, because a share of visitors
                 arrive already knowing they need monitoring rather than a
                 one-off score, and should not have to run a check to find the
                 product. Softer framing than the post-report block. */ ?>
        

        <?php else: /* ---------- Report ---------- */
            $s = $audit_result;
            $band = $s['score'] >= 85 ? 'good' : ($s['score'] >= 65 ? 'mid' : 'low');
        ?>
        <div class="audit-report" data-reveal="card">
            <div class="audit-score-head audit-band-<?= $band ?>">
                <div class="audit-dial" style="--pct: <?= (int) $s['score'] ?>">
                    <span class="audit-dial-num"><?= (int) $s['score'] ?></span>
                    <span class="audit-dial-out">/100</span>
                </div>
                <div class="audit-score-copy">
                    <p class="eyebrow">Audit complete</p>
                    <h2><?= e($s['grade']) ?></h2>
                    <p class="audit-target"><?= e($s['url']) ?></p>
                    <ul class="audit-tally">
                        <li class="ok"><strong><?= (int) $s['summary']['pass'] ?></strong> passed</li>
                        <li class="warn"><strong><?= (int) $s['summary']['warn'] ?></strong> need attention</li>
                        <li class="bad"><strong><?= (int) $s['summary']['fail'] ?></strong> critical</li>
                    </ul>
                </div>
            </div>

            <p class="audit-sent">A full copy has been emailed to <strong><?= e($audit_input['email']) ?></strong>.</p>

            <?php foreach ($s['groups'] as $heading => $checks): ?>
            <div class="audit-group">
                <h3><?= e($heading) ?></h3>
                <ul class="audit-list">
                    <?php foreach ($checks as $c): [$word, $cls] = $labels[$c['status']]; ?>
                    <li class="audit-item audit-<?= $cls ?>">
                        <span class="audit-mark" aria-hidden="true"><?= $c['status'] === 'pass' ? '✓' : ($c['status'] === 'warn' ? '!' : '✕') ?></span>
                        <div>
                            <p class="audit-label"><?= e($c['label']) ?> <span class="sr-only">(<?= e($word) ?>)</span></p>
                            <p class="audit-detail"><?= e($c['detail']) ?></p>
                            <?php if ($c['fix'] !== '' && $c['status'] !== 'pass'): ?>
                            <p class="audit-fix"><?= e($c['fix']) ?></p>
                            <?php endif; ?>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endforeach; ?>

            <div class="panel panel-accent audit-limits">
                <h3>What this automated check does not cover</h3>
                <p>This reads the HTML your homepage serves. Backlink profile, keyword rankings, competitor gaps, real-world Core Web Vitals and what AI engines actually say about you need continuous tracking or a human eye. Two ways to go further, below.</p>
            </div>

            <?php /* Placed here, not higher up: the visitor has just read their
                     own findings, so "what about every engine, every month" is
                     a question they are already asking. The managed-service CTA
                     follows it, giving a self-serve and a done-for-you door. */ ?>
            
        </div>

        <section class="cta-mid audit-cta">
            <div class="container cta-mid-inner">
                <div>
                    <h2>Want these fixed, in priority order?</h2>
                    <p>We will walk you through the findings and tell you honestly which ones actually move revenue for a business like yours.</p>
                </div>
                <div class="cta-mid-actions">
                    <a class="btn btn-dark" href="/contact-us/">Book a Free Consultation</a>
                    <a class="cta-phone" href="<?= e(CONTACT_PHONE_HREF) ?>">or call <?= e(CONTACT_PHONE) ?></a>
                </div>
            </div>
        </section>

        <p class="center audit-again"><a href="/free-seo-audit/">Audit another website</a></p>
        <?php endif; ?>
    </div>
</section>

<?php render_faqs(page_faqs($page, $slug), 'About the free audit'); ?>
