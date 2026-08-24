<?php /** E-book / guide landing page with lead-capture. Expects: $page, $slug, $RESOURCES */
$res = $RESOURCES[$page['resource']] ?? null;
if (!$res) { http_response_code(404); echo '<section class="section"><div class="container"><h1>Resource not found</h1></div></section>'; return; }
$is_ebook = $res['type'] === 'ebook';
$hub = $is_ebook ? 'ebooks' : 'guides';
$type_label = $is_ebook ? 'Free E-book' : 'Complete Guide';

$related = related_ring($RESOURCES, $page['resource'], 3, fn($r) => $r['type'] === $res['type']);

if (!empty($res['faqs'])) { echo jsonld_script(jsonld_faq($res['faqs'])); }
echo jsonld_script([
    '@context' => 'https://schema.org',
    '@type' => $is_ebook ? 'Book' : 'Article',
    'name' => $res['h1'],
    'headline' => $res['h1'],
    'description' => $res['meta'],
    'author' => ['@type' => 'Organization', 'name' => SITE_NAME],
    'publisher' => ['@type' => 'Organization', 'name' => SITE_NAME],
]);
?>
<section class="page-hero resource-hero">
    <div class="container resource-hero-inner">
        <div>
            <p class="eyebrow"><?= e($type_label) ?> · <?= e($res['size']) ?></p>
            <h1><?= e($page['h1']) ?></h1>
            <p class="lede"><?= e($page['lede']) ?></p>
            <ul class="check-list resource-learn">
                <?php foreach ($res['learn'] as $l): ?><li><?= e($l) ?></li><?php endforeach; ?>
            </ul>
            <p class="trust-microcopy">✔ Instant access &nbsp;·&nbsp; ✔ Written by practitioners &nbsp;·&nbsp; ✔ No spam, ever</p>
        </div>
        <div class="resource-form-card" id="get">
            <h2 class="resource-form-title"><?= $is_ebook ? 'Get your free copy' : 'Get the full guide + updates' ?></h2>
            <p class="qm-sub">Tell us where to send it: it arrives in minutes, along with future updates to this <?= $is_ebook ? 'e-book' : 'guide' ?>.</p>
            <form class="contact-form resource-form" method="post" action="/contact-us/" data-resource="<?= e($page['resource']) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="recaptcha_token" class="js-recaptcha-token">
                <div class="form-row"><label for="r-name">Your name *</label><input id="r-name" name="name" type="text" placeholder="Full name" required autocomplete="name"></div>
                <div class="form-row"><label for="r-email">Work email *</label><input id="r-email" name="email" type="email" placeholder="you@company.com" required autocomplete="email"></div>
                <div class="form-row"><label for="r-company">Company</label><input id="r-company" name="company" type="text" placeholder="Business name" autocomplete="organization"></div>
                <div class="form-row honeypot" aria-hidden="true"><label for="r-website">Website</label><input id="r-website" name="website" type="text" tabindex="-1" autocomplete="off"></div>
                <input type="hidden" name="service" value="Resource: <?= e($res['h1']) ?>">
                <?php /* The slug is what tells the server which file to send. It was
                         previously only a data attribute, which never reaches PHP. */ ?>
                <input type="hidden" name="resource" value="<?= e($page['resource']) ?>">
                <div class="alert alert-error r-error" role="alert" hidden>Please fill in your name and a valid email, then try again.</div>
                <button class="btn btn-grad btn-block" type="submit"><?= $is_ebook ? 'Send Me the E-book' : 'Send Me the Guide' ?></button>
                <p class="form-privacy">We'll also share occasional playbooks you can unsubscribe from anytime.</p>
                <p class="recaptcha-disclosure">This site is protected by reCAPTCHA and the Google <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Privacy Policy</a> and <a href="https://policies.google.com/terms" target="_blank" rel="noopener">Terms of Service</a> apply.</p>
            </form>
            <div class="quote-done r-success" hidden>
                <span class="quote-done-icon" aria-hidden="true">✓</span>
                <h3>On its way!</h3>
                <p>Check your inbox in the next few minutes. While you wait, want this applied to your business?</p>
                <a class="btn btn-dark" href="/free-seo-audit/">Check My Site Free</a>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title center">What's inside</h2>
        <div class="card-grid cols-3">
            <?php $i = 0; $accents = ['ac-yellow', 'ac-pink', 'ac-cyan', 'ac-purple']; foreach ($res['chapters'] as [$t, $d]): ?>
            <div class="card svc-card <?= $accents[$i % 4] ?>" data-reveal="card">
                <span class="svc-bar" aria-hidden="true"></span>
                <span class="svc-num"><?= sprintf('%02d', ++$i) ?></span>
                <h3><?= e($t) ?></h3>
                <p><?= e($d) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if (!empty($res['table'])): ?>
<?php /* Side-by-side comparison for resources that exist to help a reader
         choose between options. Answer engines lift tabular comparisons
         directly, so a selection guide without one gives them nothing
         structured to quote. */ ?>
<section class="section section-tint">
    <div class="container">
        <h2 class="section-title center"><?= e($res['table']['caption'] ?? 'At a glance') ?></h2>
        <figure class="post-table-wrap">
            <table class="post-table">
                <thead>
                    <tr><?php foreach ($res['table']['head'] as $th): ?><th scope="col"><?= e($th) ?></th><?php endforeach; ?></tr>
                </thead>
                <tbody>
                    <?php foreach ($res['table']['rows'] as $row): ?>
                    <tr>
                        <?php foreach ($row as $ci => $cell): ?>
                        <?php if ($ci === 0): ?><th scope="row"><?= e($cell) ?></th>
                        <?php else: ?><td><?= e($cell) ?></td><?php endif; ?>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </figure>
    </div>
</section>
<?php endif; ?>

<section class="section section-tint">
    <div class="container narrow center-text">
        <h2 class="section-title">Who this is for</h2>
        <ul class="badge-row resource-who">
            <?php foreach ($res['who'] as $w): ?><li class="badge"><?= e($w) ?></li><?php endforeach; ?>
        </ul>
        <a class="btn btn-grad" href="#get">Get It Free</a>
    </div>
</section>

<?php if (!empty($res['faqs'])): ?>
<section class="section">
    <div class="container narrow">
        <h2 class="section-title center">Questions, answered</h2>
        <div class="faq-list">
            <?php foreach ($res['faqs'] as [$q, $a]): ?>
            <details class="faq"><summary><?= e($q) ?></summary><p><?= e($a) ?></p></details>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section">
    <div class="container">
        <h2 class="section-title center">More <?= $is_ebook ? 'e-books' : 'guides' ?> you'll like</h2>
        <div class="card-grid cols-3">
            <?php foreach ($related as $rslug => $r): ?>
            <a class="card post-card" href="/<?= e($hub) ?>/<?= e($rslug) ?>/">
                <span class="card-tag"><?= e($is_ebook ? 'E-book · ' . $r['size'] : 'Guide · ' . $r['size']) ?></span>
                <h3><?= e($r['h1']) ?></h3>
                <p><?= e(mb_substr($r['lede'], 0, 120)) ?>…</p>
                <span class="post-card-foot"><span>Free</span><em aria-hidden="true">Get it →</em></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
