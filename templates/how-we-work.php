<?php /** How We Work. Expects: $page, $slug */
$stages = [
    ['Listen', 'Day 1', 'A senior strategist reads your enquiry and your website before replying. On the first call we talk about your goals, margins and sales process, not our services.'],
    ['Audit', 'Week 1', 'An honest review of your site, tracking, competitors and AI visibility. You keep it, whether or not we work together.'],
    ['Plan', 'Week 2', 'A prioritized roadmap with targets, owners and costs. If we cannot help, we say so here.'],
    ['Build', 'Weeks 3 to 6', 'Tracking first, then technical fixes, pages and campaigns. Nothing launches before it can be measured.'],
    ['Grow', 'Month 2 onward', 'Fortnightly or monthly reviews move effort to what produces qualified leads and revenue.'],
    ['Report', 'Every month', 'A plain-language report: what changed, what it produced, and what happens next.'],
];
?>
<?php render_hero([
    'k' => 'How we work', 'h1' => $page['h1'], 'lede' => $page['lede'], 'tone' => 'lilac', 'layout' => 'flip',
    'cta' => ['Get my growth plan', '/contact-us/'], 'alt' => ['See client results', '/case-studies/'],
    'visual' => hero_visual(['steps', ['A senior reply within a day', 'An honest audit', 'A prioritised plan', 'Tracked execution', 'Plain reporting']]),
]); ?>

<section class="section">
    <div class="container">
        <div class="center-text" data-reveal="head">
            <p class="eyebrow">The path</p>
            <h2 class="section-title">From first message to measured growth</h2>
        </div>
        <ol class="lp-steps lp-steps-6">
            <?php foreach ($stages as $i => [$t, $w, $d]): ?>
            <li class="tilt" data-reveal="card">
                <span class="lp-step-n" aria-hidden="true"><?= $i + 1 ?></span>
                <span class="step-time"><?= e($w) ?></span>
                <h3><?= e($t) ?></h3>
                <p><?= e($d) ?></p>
            </li>
            <?php endforeach; ?>
        </ol>
    </div>
</section>

<section class="section section-tint">
    <div class="container">
        <h2 class="section-title center">What you can hold us to</h2>
        <div class="card-grid cols-3">
            <div class="card" data-reveal="card"><h3>You own everything</h3><p>Ad accounts, analytics, content and code stay in your name. If we part ways, you keep it all.</p></div>
            <div class="card" data-reveal="card"><h3>Reports you can read</h3><p>We report in leads, cost per lead and revenue, not impressions alone.</p></div>
            <div class="card" data-reveal="card"><h3>A reply within a day</h3><p>You get a response within one working day, every time.</p></div>
            <div class="card" data-reveal="card"><h3>Real people on your account</h3><p>The specialists you meet do the work, and they use AI tools under human review. See our <a href="/ai-policy/">AI policy</a>.</p></div>
            <div class="card" data-reveal="card"><h3>No lock-in after 90 days</h3><p>Ninety days is the minimum honest window to judge the work. After that we earn renewal monthly.</p></div>
            <div class="card" data-reveal="card"><h3>Straight talk</h3><p>If a channel is not working, we tell you and move the budget.</p></div>
        </div>
    </div>
</section>


<?php render_faqs(page_faqs($page, $slug), 'Working with us: common questions'); ?>
