<?php
/**
 * Post-submission confirmation. Every lead form redirects here.
 *
 * A dedicated URL rather than an inline panel, for three reasons: the visitor
 * gets an unambiguous finish, analytics and ad platforms get one conversion
 * URL to fire on, and there is room to say what actually happens next instead
 * of a single line under a form that is still on screen.
 *
 * Expects: $page. Optional ?s= carries the form source so the copy can match
 * what the visitor actually asked for.
 */

$source = preg_replace('/[^a-z0-9-]/', '', (string) ($_GET['s'] ?? ''));

/* What we promised, per form. Anything unrecognised falls back to the generic
   enquiry wording rather than claiming something that was never offered. */
$promise = match ($source) {
    'seo-audit', 'audit-consultation' => 'Your audit report is on its way to your inbox. If it has not arrived in a few minutes, check your spam folder.',
    'resource'   => 'Your download is on its way to your inbox, with a link that stays valid for 30 days.',
    'newsletter' => 'You are on the list. One useful email a month, and you can leave any time.',
    default      => 'We will review your details and reply with honest next steps, plus a free mini-audit of where you stand today.',
};
?>
<section class="page-hero ty-hero">
    <div class="container ty-wrap">
        <span class="ty-mark" aria-hidden="true">
            <svg viewBox="0 0 32 32" width="34" height="34" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 16.5 13 23l13-14"/>
            </svg>
        </span>
        <h1><?= e($page['h1']) ?></h1>
        <p class="lede"><?= e($page['lede']) ?></p>
        <p class="ty-promise"><?= e($promise) ?></p>

        <ol class="ty-steps">
            <li>
                <strong>Within one working day</strong>
                <span>A senior strategist replies personally. No bots, no junior handoff.</span>
            </li>
            <li>
                <strong>Within 72 hours</strong>
                <span>You get a free mini-audit: concrete gaps and quick wins you can act on, whether or not you hire us.</span>
            </li>
            <li>
                <strong>Only if it fits</strong>
                <span>A roadmap and an honest quote. If we cannot move the needle for you, we will say so.</span>
            </li>
        </ol>

        <div class="ty-urgent">
            <p>Need to talk sooner?</p>
            <div class="ty-actions">
                <a class="btn btn-grad" href="<?= e(CONTACT_PHONE_HREF) ?>">Call <?= e(CONTACT_PHONE) ?></a>
                <a class="btn btn-ghost" href="https://wa.me/<?= e(CONTACT_WHATSAPP) ?>?text=Hi%20VTurnU%2C%20I%20just%20submitted%20an%20enquiry." target="_blank" rel="noopener nofollow">WhatsApp us</a>
            </div>
        </div>
    </div>
</section>

<section class="section ty-next">
    <div class="container">
        <h2 class="section-title">While you wait</h2>
        <div class="ty-grid">
            <?php
            /* Only link pages that genuinely exist, so this block cannot rot
               if the content architecture changes later. */
            $suggestions = array_values(array_filter([
                ['case-studies',      'Read the case studies',   'What the work produced for businesses like yours, including the constraints.'],
                ['blog',              'Latest insight',          'How search and AI discovery are actually changing, written plainly.'],
                ['ai-visibility-tool','Check your AI visibility','See which answer engines name your brand today, and which name competitors.'],
                ['pricing',           'How pricing works',       'What engagements typically cost and what sits inside each one.'],
            ], fn($s) => isset($PAGES[$s[0]])));
            foreach ($suggestions as [$slug, $title, $blurb]): ?>
            <a class="ty-card" href="<?= e(page_url($slug)) ?>">
                <strong><?= e($title) ?></strong>
                <span><?= e($blurb) ?></span>
                <em aria-hidden="true">&rarr;</em>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
