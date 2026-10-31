<?php
/**
 * Enterprise & IT landing page. Expects: $page, $slug, $LANDINGS.
 * One page, one offer: every action leads to the enquiry form at #enquire.
 */
$l = $LANDINGS[$slug];
$siblings = array_diff_key($LANDINGS, [$slug => 1]);
$offers = array_map(static fn($c) => ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => $c[0], 'description' => $c[1]]], $l['capabilities']);
echo jsonld_script([
    '@context' => 'https://schema.org',
    '@type' => 'Service',
    'name' => $page['h1'],
    'serviceType' => $l['service'],
    'description' => $page['meta'],
    'url' => abs_url(page_url($slug)),
    'provider' => ['@id' => SITE_URL . '/#organization'],
    'areaServed' => 'Worldwide',
    'hasOfferCatalog' => ['@type' => 'OfferCatalog', 'name' => $l['service'] . ' services', 'itemListElement' => $offers],
]);
echo jsonld_script([
    '@context' => 'https://schema.org', '@type' => 'WebPage', 'name' => $page['title'], 'url' => abs_url(page_url($slug)),
    'speakable' => ['@type' => 'SpeakableSpecification', 'cssSelector' => ['h1', '.answer-text']],
]);
?>
<section class="page-hero lp-hero">
    <div class="container">
        <p class="eyebrow eyebrow-line"><?= e($l['eyebrow']) ?> · VTurnU Enterprise &amp; IT</p>
        <h1><?= e($page['h1']) ?></h1>
        <p class="lede"><?= e($page['lede']) ?></p>
        <div class="hero-actions">
            <a class="btn btn-grad" href="#enquire">Talk to a Consultant</a>
            <a class="btn btn-outline" href="#how">How We Work</a>
        </div>
        <ul class="hero-chips">
            <?php foreach ($l['chips'] as $c): ?><li><?= e($c) ?></li><?php endforeach; ?>
        </ul>
    </div>
</section>

<section class="section">
    <div class="container split">
        <div class="answer-box" data-reveal="card">
            <h2><?= e($l['answer'][0]) ?></h2>
            <p class="answer-text"><?= e($l['answer'][1]) ?></p>
        </div>
        <div class="panel panel-accent" data-reveal="card">
            <h2 class="panel-h">Who this is for</h2>
            <ul class="check-list">
                <?php foreach ($l['who'] as $w): ?><li><?= e($w) ?></li><?php endforeach; ?>
            </ul>
        </div>
    </div>
</section>

<section class="section section-tint">
    <div class="container">
        <div class="center-text" data-reveal="head">
            <p class="eyebrow">Sound familiar?</p>
            <h2 class="section-title">The problems we are called in to fix</h2>
        </div>
        <div class="lp-pains">
            <?php foreach ($l['pains'] as $i => [$t, $d]): ?>
            <div class="lp-pain tilt" data-reveal="card">
                <span class="lp-pain-n" aria-hidden="true"><?= sprintf('%02d', $i + 1) ?></span>
                <h3><?= e($t) ?></h3>
                <p><?= e($d) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="center-text" data-reveal="head">
            <p class="eyebrow">What we deliver</p>
            <h2 class="section-title"><?= e($l['service']) ?> services, end to end</h2>
        </div>
        <div class="card-grid cols-3 svc-grid">
            <?php $ac = ['ac-yellow', 'ac-terra', 'ac-purple', 'ac-olive', 'ac-cyan', 'ac-pink']; foreach ($l['capabilities'] as $i => [$t, $d]): ?>
            <div class="card svc-card <?= $ac[$i % 6] ?>" data-reveal="card">
                <span class="svc-bar" aria-hidden="true"></span>
                <span class="svc-num"><?= sprintf('%02d', $i + 1) ?></span>
                <h3><?= e($t) ?></h3>
                <p><?= e($d) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="cta-mid">
    <div class="container cta-mid-inner">
        <div>
            <h2>Have a project in mind?</h2>
            <p>Share the scope and timeline. You will hear from a consultant, not a call center, within one working day.</p>
        </div>
        <div class="cta-mid-actions">
            <a class="btn btn-dark" href="#enquire">Send Your Requirement</a>
            <a class="cta-phone" href="<?= e(CONTACT_PHONE_HREF) ?>">or call <?= e(CONTACT_PHONE) ?></a>
        </div>
    </div>
</section>

<section class="section" id="how">
    <div class="container">
        <div class="center-text" data-reveal="head">
            <p class="eyebrow">How we work</p>
            <h2 class="section-title">A clear path from first call to go-live</h2>
        </div>
        <ol class="lp-steps">
            <?php foreach ($l['process'] as $i => [$t, $d]): ?>
            <li class="tilt" data-reveal="card">
                <span class="lp-step-n" aria-hidden="true"><?= $i + 1 ?></span>
                <h3><?= e($t) ?></h3>
                <p><?= e($d) ?></p>
            </li>
            <?php endforeach; ?>
        </ol>
    </div>
</section>

<section class="section section-tint">
    <div class="container">
        <h2 class="section-title center"><?= e($l['table']['caption']) ?></h2>
        <figure class="post-table-wrap">
            <table class="post-table">
                <thead><tr><?php foreach ($l['table']['head'] as $th): ?><th scope="col"><?= e($th) ?></th><?php endforeach; ?></tr></thead>
                <tbody>
                    <?php foreach ($l['table']['rows'] as $row): ?>
                    <tr><?php foreach ($row as $ci => $cell): ?><?php if ($ci === 0): ?><th scope="row"><?= e($cell) ?></th><?php else: ?><td><?= e($cell) ?></td><?php endif; ?><?php endforeach; ?></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </figure>
    </div>
</section>

<?php render_faqs($l['faqs'], $l['service'] . ': questions buyers ask'); ?>

<section class="section lp-form-sec" id="enquire">
    <div class="container lp-form-grid">
        <div class="lp-form-intro" data-reveal="card">
            <p class="eyebrow">Start here</p>
            <h2 class="section-title"><?= e($l['form'][0]) ?></h2>
            <p><?= e($l['form'][1]) ?></p>
            <ul class="check-list">
                <li>A consultant reads every enquiry personally</li>
                <li>Your details stay private</li>
                <li>No obligation and no sales sequence</li>
            </ul>
            <p class="lp-direct">Prefer to talk? <a href="<?= e(CONTACT_PHONE_HREF) ?>"><?= e(CONTACT_PHONE) ?></a> or <a href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a></p>
        </div>
        <div class="contact-form-wrap" data-reveal="card">
            <form class="contact-form lp-form" method="post" action="/contact-us/">
                <?= csrf_field() ?>
                <input type="hidden" name="recaptcha_token" class="js-recaptcha-token">
                <input type="hidden" name="service" value="<?= e($l['service']) ?>">
                <input type="hidden" name="lead_source" value="landing">
                <div class="form-grid">
                    <div class="form-row"><label for="lp-name">Your name *</label><input id="lp-name" name="name" type="text" required autocomplete="name"></div>
                    <div class="form-row"><label for="lp-email">Work email *</label><input id="lp-email" name="email" type="email" required autocomplete="email"></div>
                </div>
                <div class="form-grid">
                    <div class="form-row"><label for="lp-phone">Phone / WhatsApp *</label>
                        <div class="phone-group"><?= country_code_select('country_code', 'lp-cc') ?><input id="lp-phone" name="mobile" type="tel" inputmode="tel" required autocomplete="tel-national"></div>
                    </div>
                    <div class="form-row"><label for="lp-company">Company *</label><input id="lp-company" name="company" type="text" required autocomplete="organization"></div>
                </div>
                <div class="form-row"><label for="lp-message">What do you need? *</label><textarea id="lp-message" name="message" rows="4" required minlength="15" placeholder="Current systems, scope and timeline"></textarea></div>
                <div class="form-row honeypot" aria-hidden="true"><label for="lp-website">Website</label><input id="lp-website" name="website" type="text" tabindex="-1" autocomplete="off"></div>
                <div class="alert alert-error lp-error" role="alert" hidden></div>
                <button class="btn btn-grad btn-block" type="submit">Send My Requirement</button>
                <p class="recaptcha-disclosure">Protected by reCAPTCHA. The Google <a href="https://policies.google.com/privacy" target="_blank" rel="noopener nofollow">Privacy Policy</a> and <a href="https://policies.google.com/terms" target="_blank" rel="noopener nofollow">Terms of Service</a> apply.</p>
            </form>
        </div>
    </div>
</section>

<section class="section section-tint">
    <div class="container">
        <h2 class="section-title center">Explore the Enterprise &amp; IT practice</h2>
        <div class="card-grid cols-4">
            <?php foreach ($siblings as $s => $sib): ?>
            <a class="card card-compact" href="<?= e(page_url($s)) ?>">
                <h3><?= e($sib['service']) ?></h3>
                <span class="card-more">Learn more →</span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
