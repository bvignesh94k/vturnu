<?php
/**
 * Search update: Google's November 2026 core update. Loaded by
 * templates/update-post.php through the 'rich' key on its record in
 * includes/data/updates.php.
 *
 * Every date, duration and statement comes from Google's Search Status
 * Dashboard or Google Search Central (checked 12 November 2026), linked in the
 * sentence that uses it. News coverage is named where it is the source. The
 * pump distributor in the example is made up and labelled so. Status-dependent
 * lines read the record, so marking the update complete in the admin panel
 * updates the facts, the chart and the calendar together. External links are
 * nofollow and open in a new tab (ext_link).
 */

$g = [
    'incident' => 'https://status.search.google.com/incidents/WLxcJyC6jMWMfryxsKMc',
    'history' => 'https://status.search.google.com/products/rGHU1u87FJnkP6W2GwMi/history',
    'core' => 'https://developers.google.com/search/docs/appearance/core-updates',
    'drops' => 'https://developers.google.com/search/docs/monitor-debug/debugging-search-traffic-drops',
    'helpful' => 'https://developers.google.com/search/docs/fundamentals/creating-helpful-content',
    'ai' => 'https://developers.google.com/search/docs/appearance/ai-features',
    'sej' => 'https://www.searchenginejournal.com/google-november-2026-core-update/596614/',
    'ser' => 'https://www.seroundtable.com/google-november-2026-core-update-42504.html',
    'ppc' => 'https://ppc.land/googles-core-update-shifts-rankings-for-up-to-2-weeks-before-black-friday/',
];

$table = static function (string $caption, array $head, array $rows, string $class = ''): void {
    ?>
<div class="bx-table-wrap">
    <table class="bx-table<?= $class !== '' ? ' ' . e($class) : '' ?>" role="table">
        <caption><?= e($caption) ?></caption>
        <thead role="rowgroup"><tr role="row"><?php foreach ($head as $th): ?><th scope="col" role="columnheader"><?= e($th) ?></th><?php endforeach; ?></tr></thead>
        <tbody role="rowgroup">
            <?php foreach ($rows as $row): ?>
            <tr role="row">
                <th scope="row" role="rowheader"><?= e($row[0]) ?></th>
                <?php foreach (array_slice($row, 1) as $i => $cell): ?><td role="cell" data-label="<?= e($head[$i + 1]) ?>"><?= e($cell) ?></td><?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
    <?php
};

return [

'body' => static function () use ($g, $table): void {
    $u = $GLOBALS['UPDATES']['november-core-update-2026'] ?? [];
    $done = ($u['status'] ?? '') === 'complete' && !empty($u['finished']);
    $end = update_end($u);
    $took = $done ? (int) round((strtotime($u['finished']) - strtotime($u['started'])) / 86400) : 0;
    ?>
<p class="bx-open">On 10 November 2026, Google started rolling out its third core update of the year. If your search traffic moves up or down over the next few weeks, this is the most likely reason.</p>
<p>Core updates make people nervous because Google says very little about them. This one is no different: one line on Google's status dashboard, one short social post and no new guidance. So this page sticks to what is confirmed, explains it in plain words and gives you a simple plan for checking your own site without guessing.</p>

<div class="answer-box post-answer">
    <p class="answer-text"><?= e($u['lede'] ?? '') ?></p>
</div>

<?php ux_facts($u, [
    ['Google\'s description', 'A regular update designed to better surface relevant, satisfying content'],
    ['New guidance from Google', 'None published with this update'],
    ['Official source', ext_link($g['incident'], 'Google Search Status Dashboard')],
]); ?>

<nav class="bx-toc" aria-label="In this article">
    <p class="bx-toc-h">In this article</p>
    <ol>
        <li><a href="#what-is-a-core-update">What a core update is</a></li>
        <li><a href="#what-google-said">What Google has said</a></li>
        <li><a href="#updates-in-2026">How it fits into 2026</a></li>
        <li><a href="#timing">Why the timing matters</a></li>
        <li><a href="#check-your-site">How to check your site</a></li>
        <li><a href="#example">A worked example</a></li>
        <li><a href="#if-you-dropped">If your rankings dropped</a></li>
        <li><a href="#mistakes">Five mistakes to avoid</a></li>
        <li><a href="#ai-overviews">AI Overviews and AI Mode</a></li>
        <li><a href="#faq-title">FAQs</a></li>
    </ol>
</nav>

<h2 id="what-is-a-core-update">What Is a Google Core Update?</h2>
<p>A core update is a broad change to the systems Google uses to rank search results. Google releases several a year. In its <?= ext_link($g['core'], 'guide to core updates') ?>, Google says these changes are broad in nature and don't target specific sites or individual pages. It is not a penalty, and there is nothing to "switch off".</p>
<p>A simple way to picture it: imagine a food critic who ranks the same 100 restaurants every year. This year the critic pays closer attention to how fresh the ingredients are. Nothing about your restaurant changed, but your place on the list can still move, because the way restaurants are compared changed. A core update works like that. Your page can be exactly as good as last month while other pages are now judged more helpful for the same search.</p>
<p>That is why there is no single thing to "fix" after a core update. The useful question is not "what did we do wrong?" but "is our page still the most useful answer for the people searching?"</p>
<p>Google also makes smaller core updates that it does not announce, according to the same guide. So if your traffic shifts at a time when no update has been announced, a quiet ranking change is still one possible reason.</p>

<h2 id="what-google-said">What Google Has Said About the November 2026 Core Update</h2>
<p>Google announced the update on its <?= ext_link($g['incident'], 'Search Status Dashboard') ?>. The entry says the update began at 10:06 a.m. Pacific time on 10 November 2026, applies globally and to all languages, and may take up to two weeks to roll out.</p>
<p>Google Search Central also called it "a regular update designed to better surface relevant, satisfying content" in a LinkedIn post, as reported by <?= ext_link($g['sej'], 'Search Engine Journal') ?> and <?= ext_link($g['ser'], 'Search Engine Roundtable') ?>.</p>
<p>That is the whole announcement. Google did not publish a blog post, did not say which kinds of sites it expects to move and did not release new advice. If an article claims to know exactly what this update targets, treat that as a guess, not a fact.</p>

<h2 id="updates-in-2026">How This Update Fits Into 2026</h2>
<p>This is the third core update of 2026, after the March and May core updates, and the eighth ranking update Google has listed on its dashboard this year. It began 173 days after the May core update, the longest gap between core updates in 2026.</p>
<?php $table('Every ranking update on Google\'s Search Status Dashboard in 2026', ['Update', 'Started', 'How long it took'], [
    ['February 2026 Discover update', '5 Feb 2026', '21 days, 17 hours'],
    ['March 2026 spam update', '24 Mar 2026', '19 hours, 30 minutes'],
    ['March 2026 core update', '27 Mar 2026', '12 days, 4 hours'],
    ['May 2026 core update', '21 May 2026', '11 days, 21 hours'],
    ['June 2026 spam update', '24 Jun 2026', '2 days, 1 hour'],
    ['August 2026 spam update', '18 Aug 2026', '2 days, 16 hours'],
    ['September 2026 spam update', '24 Sep 2026', '13 days, 16 hours'],
    ['November 2026 core update', '10 Nov 2026', $done ? 'Finished ' . update_day($u['finished']) . ', about ' . $took . ' days' : 'Rolling out (Google\'s estimate: up to 2 weeks)'],
], 'ux-y-table'); ?>
<p class="ux-note-s">Source: Google's <?= ext_link($g['history'], 'ranking update history') ?> on the Search Status Dashboard, checked 12 November 2026.</p>

<?php ux_duration_chart('core-update-durations', 'How long recent core updates took to roll out', 'Every core update in 2025 and 2026, from start to finish, as recorded by Google.', [
    ['label' => 'March 2025', 'hours' => 333, 'text' => '13 days, 21 hours'],
    ['label' => 'June 2025', 'hours' => 402, 'text' => '16 days, 18 hours'],
    ['label' => 'December 2025', 'hours' => 434, 'text' => '18 days, 2 hours', 'label_on' => true],
    ['label' => 'March 2026', 'hours' => 292, 'text' => '12 days, 4 hours'],
    ['label' => 'May 2026', 'hours' => 285, 'text' => '11 days, 21 hours', 'label_on' => true],
    $done
        ? ['label' => 'November 2026', 'hours' => $took * 24, 'text' => 'About ' . $took . ' days', 'label_on' => true]
        : ['label' => 'November 2026', 'hours' => 336, 'text' => 'Up to 14 days (estimate)', 'estimate' => true, 'label_on' => true],
], 21, 'Source: ' . ext_link($g['history'], 'Google Search Status Dashboard, ranking history') . '. Durations as Google lists them; the November 2026 bar shows Google\'s own estimate until it finishes.'); ?>

<p>So "up to two weeks" is a guide, not a promise. The last five core updates took anywhere from just under 12 days to just over 18, and two of 2025's three ran past two weeks. Both earlier core updates in 2026 finished in about 12 days.</p>

<h2 id="timing">Why the Timing Matters This Year</h2>
<?php if ($done): ?>
<p>Google confirmed the rollout finished on <?= e(update_day($u['finished'], 'l j F Y')) ?>. The first full week after it ends runs into the US holiday season: Thanksgiving falls on 26 November, Black Friday on 27 November and Cyber Monday on 30 November.</p>
<?php else: ?>
<p>If the rollout runs the full two weeks, it ends around <?= e(update_day($end, 'l j F')) ?>. That is two days before Thanksgiving in the US (26 November), three days before Black Friday (27 November) and six days before Cyber Monday (30 November).</p>
<?php endif; ?>
<p>Google's <?= ext_link($g['core'], 'advice') ?> is to wait at least a full week after a core update completes, then compare that week with a week before the update started. This time, the "after" week includes some of the busiest shopping days of the year. A raw comparison could make a store look like it gained from the update when it only gained from Black Friday, or make a B2B site look hit when its buyers were simply on holiday. <?= ext_link($g['ppc'], 'PPC Land') ?> and Search Engine Journal both flagged this timing.</p>

<?php ux_window('measure-window', $u, [
    ['2026-11-26', 'Thanksgiving (US)'],
    ['2026-11-27', 'Black Friday'],
    ['2026-11-30', 'Cyber Monday'],
], 'Dates from Google\'s Search Status Dashboard and its core update advice: wait at least a full week after the rollout completes, then compare that week with a week before it started.'); ?>

<p>Three ways to keep the comparison fair:</p>
<ul class="post-list">
    <li><strong>Use 3 to 9 November as your "before" week.</strong> It is the last full week before the update began.</li>
    <li><strong>Add a year-over-year view.</strong> Search Console's Performance report can <?= ext_link($g['drops'], 'compare periods year over year') ?>, which shows how much of the change is the season rather than the update.</li>
    <li><strong>Compare pages, not just the whole site.</strong> A holiday tends to lift or lower most pages together. A ranking change usually hits some pages and not others.</li>
</ul>
<p>If you run an online store, start with the product and category pages that carry your holiday sales. That is where <a href="/ecommerce-seo/">ecommerce SEO</a> work tends to pay back fastest after an update.</p>

<aside class="bx-cta bx-cta--audit" aria-label="Free SEO audit">
    <svg class="bx-cta-art" viewBox="0 0 120 120" width="104" height="104" aria-hidden="true" focusable="false"><circle cx="60" cy="60" r="46" fill="none" stroke="#DAD5F2" stroke-width="12"/><path d="M60 14a46 46 0 1 1-40 69" fill="none" stroke="#FF3D8A" stroke-width="12" stroke-linecap="round"/><path d="M42 61l13 13 25-29" fill="none" stroke="#1D1936" stroke-width="9" stroke-linecap="round" stroke-linejoin="round"/></svg>
    <div class="bx-cta-copy">
        <p class="bx-cta-h">Rule Out the Simple Problems First</p>
        <p>A ranking drop is not always the update. Run the free audit on the pages that lost traffic to check titles, headings, structured data and crawl basics.</p>
        <a class="bx-btn bx-btn--accent" href="/free-seo-audit/" data-cta="update-nov26-audit">Run My Free SEO Audit <span aria-hidden="true">&rarr;</span></a>
        <p class="bx-cta-note">The audit checks one URL at a time. It does not read your Search Console data.</p>
    </div>
</aside>

<h2 id="check-your-site">How to Check Whether the Update Affected Your Site</h2>
<p>Search Console is the record that counts, because it shows your own pages and your own searches. Follow these steps in order.</p>
<ol class="bx-steps">
    <li><strong>Wait for Google to confirm the rollout is complete.</strong> The <?= ext_link($g['incident'], 'dashboard entry') ?> shows the finish date. Rankings can move up and down while an update rolls out, so early numbers can mislead.</li>
    <li><strong>Wait one more full week.</strong> This is Google's own recommendation, and it gives you a clean week of data after the update.</li>
    <li><strong>Compare the right weeks.</strong> In Search Console, open Performance, then Search results, choose Date and use Compare: the first full week after the rollout against 3 to 9 November.</li>
    <li><strong>Check each search type separately.</strong> Web, Image, Video and News results can move differently, and Google's guide suggests looking at them one at a time.</li>
    <li><strong>Read clicks and impressions together.</strong> If impressions held steady but clicks fell, Google's <?= ext_link($g['drops'], 'traffic drop guide') ?> points to your title and snippet, or something new appearing above you, rather than your ranking.</li>
    <li><strong>Judge the size of the drop.</strong> Google's guide gives two examples: a slip from position 2 to 4 is small and needs no drastic action, while a fall from position 4 to 29 calls for a closer look.</li>
    <li><strong>Look for patterns.</strong> Which pages moved, which queries and which types of page? A drop on one template or one topic tells you far more than a site-wide total.</li>
    <li><strong>Rule out other causes.</strong> Technical problems, security issues, manual actions, site moves and seasonality can all cut traffic too. Check the Manual actions and Security issues reports in Search Console before blaming the update.</li>
</ol>

<h2 id="example">Example: Reading the Numbers After a Core Update</h2>
<p>Here is the method on one page. The business and the numbers are made up to show how the steps work.</p>
<p>An industrial pump distributor gets most of its enquiries from one guide: "How to size a dosing pump". In the week of 3 to 9 November, the guide earned 1,200 clicks from Google at an average position of 3.1.</p>
<p>During the rollout, clicks swing from day to day. On 18 November they are 35 percent down, and the owner wants to rewrite the guide that afternoon. The right move is to wait: the update is still rolling out, and the page could recover or fall further before it settles.</p>
<p>The rollout ends on 24 November in this example. On 2 December, the team compares 25 November to 1 December with 3 to 9 November, and also with the same week last year, because of Thanksgiving. One of two things will usually show up:</p>
<?php $table('Two possible results for the same page (illustration with made-up numbers)', ['Result', 'What the numbers show', 'What to do'], [
    ['A small slip', 'Average position moves from 3.1 to 4.0. Clicks are down 9 percent, about the same as the same week last year.', 'Nothing drastic. The season explains most of it. Keep improving the page as normal and check again in a month.'],
    ['A large drop', 'Average position falls from 3.1 to 24. Clicks are down 70 percent, while the site\'s other pages held steady.', 'Compare the guide with what now ranks. Say the top results now offer a sizing calculator and real test data, and the guide has neither: add a worked example, your own test results and a simple calculator, then give it time.'],
]); ?>
<p>In the second case the fix is not a trick. It is making the page more useful than what replaced it. Google's guide says some improvements can show within a few days, others can take several months, and some may only show after the next core update.</p>

<h2 id="if-you-dropped">What to Do If Your Rankings Dropped</h2>
<p>Google's advice after a core update comes down to one idea: make the content more helpful, and be patient. In practice:</p>
<ul class="post-list">
    <li><strong>Assess the whole site, then the hardest-hit pages.</strong> Google's <?= ext_link($g['helpful'], 'helpful content guidance') ?> lists questions to ask, such as whether a page offers original information, reporting, research or analysis, and whether it is the kind of page you would bookmark or share with a friend.</li>
    <li><strong>Improve pages for readers, not for the update.</strong> Add first-hand experience, current facts, clearer answers and better examples. Our <a href="/blog/content-refresh-checklist/">content refresh checklist</a> helps you decide whether a page needs a refresh, a rewrite, a merge or nothing at all.</li>
    <li><strong>Start with the pages that bring enquiries.</strong> A page that lost visits but never brought a lead is a lower priority than one that fills your sales pipeline. If traffic and leads have drifted apart, see <a href="/blog/why-your-website-gets-traffic-but-no-leads/">why a website gets traffic but no leads</a>.</li>
    <li><strong>Keep a change log.</strong> Note what you changed and when, so you can tell later what made the difference.</li>
    <li><strong>Get expert input where your team lacks time.</strong> Strong pages usually need the knowledge of the people who do the work. A clear <a href="/blog/how-to-brief-a-content-writer/">content brief</a> gets that knowledge onto the page, and <a href="/seo-content-writing/">SEO content writing</a> or a full <a href="/seo-services/">SEO program</a> can carry the rest.</li>
</ul>
<p>If you gained rankings, enjoy it, but keep it in perspective. The next core update looks at everything again. Note which pages gained and what they have in common, then do more of that.</p>

<h2 id="mistakes">Five Mistakes to Avoid After a Core Update</h2>
<ol class="bx-steps">
    <li><strong>Rewriting pages in the middle of the rollout.</strong> Daily swings are not a verdict. Wait for the finish and the extra week.</li>
    <li><strong>Deleting pages in bulk.</strong> Google's guide treats removing content as a last resort, not a first step.</li>
    <li><strong>Changing dates without changing content.</strong> Google's guidance warns against <?= ext_link($g['helpful'] . '#changing-dates', 'updating a page\'s date to look fresh') ?> when nothing substantial has changed.</li>
    <li><strong>Treating a daily "volatility" score as your result.</strong> Rank trackers sample their own keyword sets. They can show that search results are moving, not whether your pages moved.</li>
    <li><strong>Assuming you were penalised.</strong> A core update is not a manual action. If Google had taken manual action against your site, it would appear in the Manual actions report in Search Console. If you publish AI-assisted pages, here is <a href="/blog/does-google-penalise-ai-content/">what Google's rules on AI content actually say</a>.</li>
</ol>

<h2 id="ai-overviews">Does the Update Affect AI Overviews and AI Mode?</h2>
<p>Google has not said. What its <?= ext_link($g['ai'], 'documentation on AI features') ?> does say is that there are no additional requirements to appear in AI Overviews or AI Mode, and no special optimizations needed. A page has to be indexed and eligible to show in Google Search with a snippet.</p>
<p>So the work that helps a page in regular results is the same work that helps it get linked in AI answers: a clear answer near the top, facts you can stand behind, and pages Google can crawl and index. If you want to track how often AI answers mention your brand, here is <a href="/blog/how-to-measure-ai-search-visibility/">how to measure AI search visibility</a>, and our <a href="/google-ai-overviews-seo/">AI Overviews optimization</a> service covers the rest.</p>
<?php },

'closing' => static function (): void { ?>
<h2 id="final-thoughts">Final Thoughts</h2>
<p>A core update is Google checking again which pages answer each search best. You cannot opt out of it, and you do not need to fear it. Wait for the rollout to finish, measure the right weeks, and spend your effort on the pages that matter most to your business.</p>
<p>We will update this page when Google confirms the rollout is complete. New updates appear on our <a href="/updates/">search updates page</a> as soon as Google announces them.</p>

<aside class="bx-cta bx-cta--main" aria-label="Start a conversation with VTurnU">
    <p class="bx-cta-h">Not Sure What the Update Did to Your Site?</p>
    <p>Send us your site and the dates your traffic changed. A senior strategist will look at your numbers with you and tell you plainly what we see.</p>
    <div class="bx-cta-acts">
        <a class="bx-btn bx-btn--light" href="/contact-us/" data-cta="update-nov26-contact">Ask a Strategist <span aria-hidden="true">&rarr;</span></a>
        <a class="bx-link bx-link--light" href="/seo-services/" data-cta="update-nov26-seo">See Our SEO Services</a>
    </div>
    <ul class="bx-cta-trust">
        <li>A senior strategist replies within one working day</li>
        <li>Projects start from <?= e(min_budget()) ?></li>
        <li>Your details stay private</li>
    </ul>
</aside>

<aside class="bx-author" aria-label="About the author">
    <p class="bx-author-h">Written by the VTurnU strategy team</p>
    <p>VTurnU is a B2B digital marketing and branding agency based in Chennai, working across SEO, AI search, paid media, content and websites for clients worldwide. Sources for this update were checked on 12 November 2026. Read more <a href="/about-us/">about the team</a>, <a href="/how-we-work/">how we work</a> and <a href="/ai-policy/">how we use AI</a>.</p>
</aside>
<?php },

];
