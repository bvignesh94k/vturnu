<?php
/**
 * The sections every money page is built from. The structure follows how a B2B
 * buyer decides: is this my problem, what do I get, has it worked before, what
 * happens first, is it right for me, how do I start.
 *
 * All wording comes from includes/data/money-*.php, written per page.
 */

declare(strict_types=1);

/** Money page content for a slug, or an empty array. */
function money_data(string $slug): array
{
    static $all = null;
    if ($all === null) {
        $all = array_merge(
            (array) require BASE_PATH . '/includes/data/money-marketing.php',
            (array) require BASE_PATH . '/includes/data/money-digital.php'
        );
    }
    return $all[$slug] ?? [];
}

/** First-90-days plan by service line: [window, title, what happens]. */
function money_plan(string $cat): array
{
    $plans = [
        'ppc' => [
            ['Days 1 to 14', 'Find the leak', 'We audit the account, verify every conversion and cut the search terms and audiences that never buy.'],
            ['Days 15 to 45', 'Rebuild and launch', 'New structure, new ads and a landing page for each campaign, live with a target cost per lead.'],
            ['Days 46 to 90', 'Scale what sells', 'Budget moves to the campaigns producing qualified leads, checked against what sales accepted.'],
        ],
        'ai' => [
            ['Days 1 to 14', 'Measure the gap', 'We run your buyer questions through the AI engines and record who gets named today.'],
            ['Days 15 to 45', 'Fix what AI reads', 'Entity facts, structured data and your key pages are rewritten so machines can quote them.'],
            ['Days 46 to 90', 'Earn the mentions', 'Coverage on sources AI engines trust, then a fresh prompt test against your baseline.'],
        ],
        'web' => [
            ['Days 1 to 14', 'Plan the pages that sell', 'We map buyers, pages and enquiry paths, and agree the scope and date in writing.'],
            ['Days 15 to 45', 'Design and build', 'You review real pages, not mockups, on a staging link as they come together.'],
            ['Days 46 to 90', 'Launch and improve', 'Redirects, tracking and speed are verified, then we tune the pages against real enquiries.'],
        ],
        'content' => [
            ['Days 1 to 14', 'Learn your voice', 'We interview your experts, study your buyers and agree the tone and topics.'],
            ['Days 15 to 45', 'Publish the first set', 'The pieces closest to a buying decision go first, edited and approved by you.'],
            ['Days 46 to 90', 'Double down', 'We read what brought enquiries, refresh it and plan the next set around it.'],
        ],
        'social' => [
            ['Days 1 to 14', 'Pick the right room', 'We audit your profiles and competitors, and settle where your buyers actually pay attention.'],
            ['Days 15 to 45', 'Start the rhythm', 'A planned calendar goes live, built around your people, work and customers.'],
            ['Days 46 to 90', 'Turn attention into leads', 'What earns response gets paid support, and every profile gets a clear path to enquire.'],
        ],
        'consulting' => [
            ['Days 1 to 14', 'See the real numbers', 'We review your channels, funnel and tracking, and speak to whoever owns sales.'],
            ['Days 15 to 45', 'Agree the roadmap', 'You get a ranked plan: what to stop, what to fix and where the next rupee goes.'],
            ['Days 46 to 90', 'Prove it works', 'Your team or ours runs the first moves, reviewed against the numbers we agreed.'],
        ],
        'enterprise' => [
            ['Days 1 to 14', 'Scope it properly', 'We review your processes, systems and goals, then fix the scope and team.'],
            ['Days 15 to 45', 'Configure and test', 'Work runs in short, reviewed cycles, with your people testing real scenarios.'],
            ['Days 46 to 90', 'Go live and support', 'Cutover, training and early-life support, with a clear plan for what comes next.'],
        ],
        'seo' => [
            ['Days 1 to 14', 'Find what is holding you back', 'We audit the site, fix tracking and map the searches your buyers really use.'],
            ['Days 15 to 45', 'Fix the foundation', 'Technical issues go first, then the pages closest to a buying decision.'],
            ['Days 46 to 90', 'Build what ranks', 'New pages and trusted mentions go live, measured in enquiries rather than positions.'],
        ],
    ];
    $key = match ($cat) { 'seo-industry' => 'seo', default => $cat };
    return $plans[$key] ?? $plans['seo'];
}

/**
 * Which real client result belongs beside each service line:
 * [case slug, result index, what it shows]. Only named, measured work.
 */
function money_proof(string $cat): ?array
{
    $map = [
        'seo' => ['firstchoice-used-auto-parts', 1, 'We rebuilt 99 part and vehicle pages for FirstChoice Used Auto Parts, a US quote-led business. Every page was checked after publishing, and buyers now find the site on the exact parts they search for.'],
        'seo-industry' => ['sai-impression-label-manufacturer', 0, 'Sai Impression makes labels in Chennai. Buyers who had never heard of the brand now find it on commercial searches such as label manufacturers in India.'],
        'ai' => ['firstchoice-used-auto-parts', 0, 'Bing Webmaster Tools recorded these citations for FirstChoice Used Auto Parts in September 2026, on questions such as how much a used transmission costs.'],
        'web' => ['floweazy-odoo-partner-website', 0, 'FlowEazy scored 26 on mobile before we rebuilt its speed and search foundations. The same team now runs a 101-page site, with the same technology it already knew.'],
        'content' => ['sai-impression-label-manufacturer', 0, 'For Sai Impression we wrote fifteen long-form articles and designed 51 branded banners, so every post reads and looks like the company rather than a stock library.'],
        'ppc' => ['firstchoice-used-auto-parts', 0, 'Paid clicks only pay off if the page and the form work. For FirstChoice Used Auto Parts we fixed 34 quote forms and routed every lead to one CRM mailbox, tested with real deliveries.', ['34', 'Quote forms fixed and routed to one CRM']],
        'social' => ['sai-impression-label-manufacturer', 2, 'Attention is wasted on a slow site. Sai Impression moved from 55 to the 90s on mobile, so the people who click through from social actually see the page.'],
        'consulting' => ['cafy-careers-uk-job-platform', 0, 'Cafy looked healthy, yet most of its clicks were people already searching for the brand. Our audit showed that, saved it from chasing the wrong score, and set the plan for non-branded demand.'],
    ];
    return $map[$cat] ?? null;
}

/** The dark strip under a hero: one real result and two working promises. */
function render_money_bar(string $cat): void
{
    $p = money_proof($cat) ?? money_proof('seo');
    $c = $GLOBALS['CASES'][$p[0]] ?? null;
    if (!$c) {
        return;
    }
    [$n, $l] = $p[3] ?? $c['results'][$p[1]];
    ?>
<section class="mp-proofbar" aria-label="Why buyers trust VTurnU">
    <div class="container">
        <ul>
            <li><b><?= e($n) ?></b><?= e($l) ?>, <?= e($c['client']) ?></li>
            <li><b>One working day</b>A senior strategist replies to every enquiry</li>
            <li><b>Yours to keep</b>You own the accounts, content, code and data</li>
        </ul>
    </div>
</section>
    <?php
}

function render_money_pain(array $pain): void
{
    ?>
<section class="mp-sec">
    <div class="container mp-pain">
        <div class="mp-pain-h">
            <p class="mp-k">Sound familiar?</p>
            <h2><?= e($pain[0]) ?></h2>
        </div>
        <ul class="mp-painlist">
            <?php foreach (array_slice($pain, 1) as [$t, $d]): ?>
            <li data-reveal="card"><h3><?= e($t) ?></h3><p><?= e($d) ?></p></li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
    <?php
}

function render_money_gets(array $gets): void
{
    ?>
<section class="mp-sec mp-gets">
    <div class="container">
        <div class="mp-gets-h">
            <p class="mp-k">What changes when we start</p>
            <h2><?= e($gets[0]) ?></h2>
        </div>
        <ul class="mp-getlist">
            <?php foreach (array_slice($gets, 1) as [$t, $d]): ?>
            <li data-reveal="card"><h3><?= e($t) ?></h3><p><?= e($d) ?></p></li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
    <?php
}

function render_money_proof(string $cat): void
{
    $p = money_proof($cat);
    $c = $p ? ($GLOBALS['CASES'][$p[0]] ?? null) : null;
    if (!$c) {
        return;
    }
    [$n, $l] = $p[3] ?? $c['results'][$p[1]];
    ?>
<section class="mp-sec">
    <div class="container">
        <div class="mp-proof" data-reveal="card">
            <div>
                <p class="mp-k">Real client, real number</p>
                <div class="mp-proof-n"><?= e($n) ?></div>
                <p class="mp-proof-l"><?= e($l) ?></p>
            </div>
            <div>
                <p class="mp-story"><?= e($p[2]) ?></p>
                <a href="/case-studies/<?= e($p[0]) ?>/">Read the <?= e($c['client']) ?> case study &rarr;</a>
                <small>Measured data, with the source and dates on the case study.</small>
            </div>
        </div>
    </div>
</section>
    <?php
}

function render_money_plan(string $cat): void
{
    ?>
<section class="mp-sec">
    <div class="container">
        <p class="mp-k">Your first 90 days</p>
        <h2>What happens after you say yes</h2>
        <ol class="mp-plan">
            <?php foreach (money_plan($cat) as [$w, $t, $d]): ?>
            <li data-reveal="card"><b><?= e($w) ?></b><h3><?= e($t) ?></h3><p><?= e($d) ?></p></li>
            <?php endforeach; ?>
        </ol>
    </div>
</section>
    <?php
}

/** Who this suits, beside the minimum budget in the visitor's own currency. */
function render_money_fit(array $fit): void
{
    ?>
<section class="mp-sec" style="padding-top:0">
    <div class="container mp-fit">
        <div class="mp-fit-a">
            <h3>This works best if</h3>
            <ul>
                <?php foreach ($fit as $f): ?><li><?= e($f) ?></li><?php endforeach; ?>
            </ul>
        </div>
        <div class="mp-fit-b">
            <p class="mp-k">Minimum budget</p>
            <b class="mp-budget"><?= e(min_budget()) ?></b>
            <p><?= e(min_budget_note()) ?></p>
        </div>
    </div>
</section>
    <?php
}

/** A call to action between sections, for the moment a buyer has just seen the proof. */
function render_cta_band(string $title, string $sub, string $btn, string $href = '#enquire'): void
{
    ?>
<section class="mp-cta" aria-label="Get started">
    <div class="container mp-cta-in">
        <div>
            <h2><?= e($title) ?></h2>
            <p><?= e($sub) ?></p>
        </div>
        <div class="mp-cta-act">
            <a class="hx-cta" href="<?= e($href) ?>"><?= e($btn) ?> <span aria-hidden="true">&rarr;</span></a>
            <a class="mp-cta-call" href="<?= e(CONTACT_PHONE_HREF) ?>">or call <?= e(CONTACT_PHONE) ?></a>
        </div>
    </div>
</section>
    <?php
}

/**
 * The enquiry section: what happens next on the left, the one lead form on the
 * right. Used by every service, hub and enterprise page.
 */
function render_lead_form(string $service, string $title, string $sub, string $id = 'enquire', string $btn = 'Send my enquiry', string $source = 'service-page'): void
{
    ?>
<section class="section mp-form lf-sec" id="<?= e($id) ?>">
    <div class="container lf-wrap">
        <div class="lf-intro" data-reveal="card">
            <p class="eyebrow">Start here</p>
            <h2 class="section-title"><?= e($title) ?></h2>
            <p class="lf-sub"><?= e($sub) ?></p>
            <ol class="lf-next">
                <li><b>Within one working day</b><span>A senior strategist reads your enquiry and replies personally.</span></li>
                <li><b>A short call</b><span>Fifteen to thirty minutes to understand your goal, with no sales script.</span></li>
                <li><b>A clear proposal</b><span>Scope, timeline and price in writing. Projects start from <?= e(min_budget()) ?>.</span></li>
            </ol>
            <p class="lf-direct">Prefer to talk? <a href="<?= e(CONTACT_PHONE_HREF) ?>"><?= e(CONTACT_PHONE) ?></a>, <a href="https://wa.me/<?= e(CONTACT_WHATSAPP) ?>" target="_blank" rel="noopener nofollow">WhatsApp</a> or <a href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a></p>
        </div>
        <div class="lf-card" data-reveal="card">
            <h3 class="lf-card-h">Tell us about your project</h3>
            <?php render_lead_form_card(['uid' => 'lf-' . $id, 'source' => $source, 'service' => lead_service_option($service), 'button' => $btn]); ?>
        </div>
    </div>
</section>
    <?php
}
