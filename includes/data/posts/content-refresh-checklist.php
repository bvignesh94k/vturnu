<?php
/**
 * Listicle: nine signs a page needs updating, with a refresh, rewrite, merge or
 * retire decision helper. Loaded by templates/blog-post.php through the 'rich'
 * key; the interactive parts come from includes/listicle-kit.php and
 * assets/js/listicle.js. Google's guidance is paraphrased from the pages linked
 * in each sentence (checked 8 Nov 2026). External links are nofollow.
 */

$g = [
    'compare' => 'https://support.google.com/webmasters/answer/17011165',
    'helpful' => 'https://developers.google.com/search/docs/fundamentals/creating-helpful-content',
    'dates' => 'https://developers.google.com/search/docs/appearance/publication-dates',
    'redirects' => 'https://developers.google.com/search/docs/crawling-indexing/301-redirects',
];

$table = static function (string $caption, array $head, array $rows): void {
    ?>
<div class="bx-table-wrap">
    <table class="bx-table" role="table">
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

'body' => static function () use ($g, $table): void { ?>
<p class="bx-open">Old content is not a problem in itself. A three-year-old page that still answers the question well can stay exactly as it is.</p>
<p>The trouble starts when a page that used to bring visitors and enquiries starts to slip, and nobody notices until the numbers are well down. The nine signs below help you spot that early, on one page at a time, and decide what kind of work it needs.</p>
<p>Open the page you are reviewing, work down the list and tick each sign you see. The count tells you how serious it is, and the decision helper after the list tells you what to do.</p>

<div class="answer-box post-answer">
    <p class="answer-text"><?= e($GLOBALS['BLOG']['content-refresh-checklist']['lede'] ?? '') ?></p>
</div>

<?php lx_glance([
    ['clicks-falling', 'Clicks are falling while impressions hold', 'Search signal'],
    ['rankings-slipping', 'Rankings have slipped for the main query', 'Search signal'],
    ['facts-dated', 'Facts, prices or screenshots are out of date', 'On the page'],
    ['intent-shifted', 'The search now wants a different kind of page', 'Search signal'],
    ['cannibalization', 'Two of your pages compete for the same search', 'Search signal'],
    ['no-enquiries', 'The page gets visits but no enquiries', 'On the page'],
    ['ai-answers', 'AI answers and snippets quote others, not you', 'Search signal'],
    ['sales-questions', 'Buyers ask questions the page does not answer', 'On the page'],
    ['technical-decay', 'Broken links, slow media or missing internal links', 'On the page'],
], 'The 9 signs at a glance'); ?>

<?php lx_board('content-refresh', [
    'label' => 'signs found',
    'filters' => ['search' => 'Search signals', 'page' => 'On the page'],
    'msgs' => [
        [0, 'Open the page you are reviewing and work down the list. Tick each sign you find.'],
        [1, 'One sign: a light refresh is usually enough. Fix that issue and move on.'],
        [3, 'Two or three signs: plan a proper refresh of this page.'],
        [9, 'Four or more signs: this page probably needs a rewrite or a merge. Use the decision helper below.'],
    ],
]); ?>

<?php lx_item(1, 'clicks-falling', 'Clicks are falling while impressions hold', ['type' => 'search', 'tags' => ['Search signal', 'Fix the snippet'], 'mark' => 'I see this'], static function () use ($g): void { ?>
<p>If the page still appears in results as often as before but fewer people click, the page is not the problem. The listing is. Something above it, or beside it, is now more tempting.</p>
<?php lx_split('How to check',
    '<p>In Search Console\'s Performance report, filter to this page and ' . ext_link($g['compare'], 'compare the last three months with the three before') . '. Steady impressions with falling clicks point to the snippet.</p>',
    'What to do',
    '<ul><li>Rewrite the title and description around the query people use.</li><li>Answer the question in the first paragraph.</li><li>Search the query yourself and look at what now sits above you.</li></ul>'); ?>
<?php }); ?>

<?php lx_item(2, 'rankings-slipping', 'Rankings have slipped for the main query', ['type' => 'search', 'tags' => ['Search signal'], 'mark' => 'I see this'], static function (): void { ?>
<p>A page that drifts from the first page of results to the second loses much of its traffic, because far fewer people look beyond the first page. A slow slide usually means newer or more complete pages have overtaken yours.</p>
<?php lx_split('How to check',
    '<ul><li>In Search Console, filter to the page and its main query, and watch the average position over six to twelve months.</li><li>Open the pages that now rank above you. What do they cover that you do not?</li><li>If the slide began during a Google core update, wait until the rollout ends before you judge it. Our notes on the <a href="/updates/november-core-update-2026/">November 2026 core update</a> show which weeks to compare.</li></ul>',
    'What to do',
    '<ul><li>Add what is missing, in your own words and from your own experience.</li><li>Update examples and figures.</li><li>Link to the page from newer, related posts.</li></ul>'); ?>
<?php }); ?>

<?php lx_item(3, 'facts-dated', 'Facts, prices or screenshots are out of date', ['type' => 'page', 'tags' => ['On the page', 'Quick to fix'], 'mark' => 'I see this'], static function () use ($g): void { ?>
<p>Readers notice an old screenshot or a discontinued product faster than any algorithm does, and they stop trusting everything else on the page. For B2B buyers checking a supplier, stale facts are a reason to look elsewhere.</p>
<?php lx_split('How to check',
    '<ul><li>Read the page as a buyer would. Look for old years, old prices, retired products and outdated interface screenshots.</li><li>Click every link and check that sources still say what you quote.</li></ul>',
    'What to do',
    '<ul><li>Update the facts and replace the screenshots.</li><li>Cite current sources inline.</li><li>Change the visible date only when you have changed the content properly, and keep it consistent with the date in your markup, as Google\'s ' . ext_link($g['dates'], 'guidance on byline dates') . ' asks.</li></ul>'); ?>
<?php }); ?>

<?php lx_item(4, 'intent-shifted', 'The search now wants a different kind of page', ['type' => 'search', 'tags' => ['Search signal', 'Usually a rewrite'], 'mark' => 'I see this'], static function (): void { ?>
<p>Sometimes the words people type stay the same while what they want changes. A query that once returned long guides may now return comparison tables, calculators, templates or videos. Adding paragraphs to an essay will not win that search back.</p>
<?php lx_split('How to check',
    '<ul><li>Search your main query in a private window.</li><li>Note the format of the top five results: guide, list, comparison, tool, template or video.</li><li>Compare it with your page honestly.</li></ul>',
    'What to do',
    '<ul><li>Rebuild the page in the format the results reward, keeping your expertise and your examples.</li><li>If a tool or template is what people want, consider building one.</li></ul>'); ?>
<?php }); ?>

<?php lx_item(5, 'cannibalization', 'Two of your pages compete for the same search', ['type' => 'search', 'tags' => ['Search signal', 'Usually a merge'], 'mark' => 'I see this'], static function () use ($g): void { ?>
<p>When two of your pages target the same search, they can take turns ranking and neither settles near the top. This is common on blogs that have grown for years without a content plan.</p>
<?php lx_split('How to check',
    '<p>In Search Console, ' . ext_link($g['compare'], 'filter by the query') . ' and open the Pages tab. If two or more of your URLs share the impressions for one query, they are competing.</p>',
    'What to do',
    '<ul><li>Merge the useful parts into the stronger page.</li><li>Send the weaker URL to it with a permanent redirect, which Google ' . ext_link($g['redirects'], 'recommends when a page has moved for good') . '.</li><li>Update your internal links to point at the page that remains.</li></ul>'); ?>
<?php }); ?>

<?php lx_item(6, 'no-enquiries', 'The page gets visits but no enquiries', ['type' => 'page', 'tags' => ['On the page', 'Revenue signal'], 'mark' => 'I see this'], static function (): void { ?>
<p>Traffic that never turns into a conversation is a cost, not an asset. Usually the page answers the question well and then leaves the reader with nowhere useful to go.</p>
<?php lx_split('How to check',
    '<ul><li>In your analytics, list landing pages with conversions next to sessions.</li><li>Read the page end to end. Is there one clear next step that suits someone at this stage?</li></ul>',
    'What to do',
    '<ul><li>Add an offer that fits the reader: a checklist, a tool, an example or a short consultation.</li><li>Link to the service page the topic leads to.</li><li>See our guide on <a href="/blog/why-your-website-gets-traffic-but-no-leads/">why a website gets traffic but no leads</a>.</li></ul>'); ?>
<?php }); ?>

<aside class="bx-cta bx-cta--audit" aria-label="Free website audit">
    <svg class="bx-cta-art" viewBox="0 0 120 120" width="104" height="104" aria-hidden="true" focusable="false"><circle cx="60" cy="60" r="46" fill="none" stroke="#DAD5F2" stroke-width="12"/><path d="M60 14a46 46 0 1 1-40 69" fill="none" stroke="#FF3D8A" stroke-width="12" stroke-linecap="round"/><path d="M42 61l13 13 25-29" fill="none" stroke="#1D1936" stroke-width="9" stroke-linecap="round" stroke-linejoin="round"/></svg>
    <div class="bx-cta-copy">
        <p class="bx-cta-h">Check a Page in Seconds</p>
        <p>Run the free audit on the page you are reviewing. It flags the technical basics, such as titles, headings, image alt text and structured data.</p>
        <a class="bx-btn bx-btn--accent" href="/free-seo-audit/" data-cta="blog-refresh-audit">Run My Free Website Audit <span aria-hidden="true">&rarr;</span></a>
        <p class="bx-cta-note">The audit checks one URL at a time. It does not read your Search Console data.</p>
    </div>
</aside>

<?php lx_item(7, 'ai-answers', 'AI answers and snippets quote others, not you', ['type' => 'search', 'tags' => ['Search signal', 'AI search'], 'mark' => 'I see this'], static function (): void { ?>
<p>More buyers now read an AI summary before they click anything. If Google's AI Overviews or AI assistants answer the questions your page covers and cite other sites, your page is being passed over, even if it still ranks.</p>
<?php lx_split('How to check',
    '<ul><li>Ask the questions your page answers in Google and in the AI assistants your buyers use.</li><li>Note who is cited, and how their pages are structured.</li><li>Our method for <a href="/blog/how-to-measure-ai-search-visibility/">measuring AI search visibility</a> makes this repeatable each month.</li></ul>',
    'What to do',
    '<ul><li>Put a direct answer in the first lines under each heading.</li><li>Phrase headings the way people ask.</li><li>Add specific, current facts that are yours to state. More in our guide on <a href="/blog/how-to-rank-in-google-ai-overviews/">ranking in Google AI Overviews</a>.</li></ul>'); ?>
<?php }); ?>

<?php lx_item(8, 'sales-questions', 'Buyers ask questions the page does not answer', ['type' => 'page', 'tags' => ['On the page', 'Ask sales'], 'mark' => 'I see this'], static function (): void { ?>
<p>Your sales and support teams hear the real questions every week. If the page on that topic does not answer them, buyers find the answers somewhere else, often on a competitor's site.</p>
<?php lx_split('How to check',
    '<ul><li>Ask sales and support for the last ten questions they heard on this topic.</li><li>Search the page for each one.</li></ul>',
    'What to do',
    '<ul><li>Add a short section, or a question and answer, for each one that comes up again and again.</li><li>Use the buyer\'s own words in the heading.</li><li>If the answers need a page of their own, start with a <a href="/blog/how-to-brief-a-content-writer/">one-page content brief</a>.</li></ul>'); ?>
<?php }); ?>

<?php lx_item(9, 'technical-decay', 'Broken links, slow media or missing internal links', ['type' => 'page', 'tags' => ['On the page', 'Quick to fix'], 'mark' => 'I see this'], static function (): void { ?>
<p>Pages decay quietly. Links break when other sites move, images grow heavier as people replace them, and new posts go live without linking back to the older page that covers the topic in depth.</p>
<?php lx_split('How to check',
    '<ul><li>Check every link on the page, internal and external.</li><li>Look at image file sizes and loading time on a phone.</li><li>Count how many of your other pages link to this one.</li></ul>',
    'What to do',
    '<ul><li>Fix or remove broken links.</li><li>Compress or resize heavy images.</li><li>Add two or three links to the page from related, newer posts.</li></ul>'); ?>
<?php }); ?>

<h2 id="what-to-do">Refresh, Rewrite, Merge or Retire?</h2>
<p>The signs tell you a page needs work. They do not tell you which kind. Use the helper below on one page at a time, or read the table after it.</p>

<div class="lx-tool lx-js" data-lx-tool="refresh" id="decision-helper">
    <p class="lx-tool-k">Decision helper</p>
    <p class="lx-tool-h">What should you do with this page?</p>
    <p>Answer five questions about one page. The recommendation updates as you go.</p>
    <fieldset class="lx-q"><legend>1. Does another page on your site target the same search?</legend>
        <label><input type="radio" name="rf-merge" value="y"> Yes</label><label><input type="radio" name="rf-merge" value="n"> No</label></fieldset>
    <fieldset class="lx-q"><legend>2. Does the topic still matter to the buyers you want?</legend>
        <label><input type="radio" name="rf-topic" value="y"> Yes</label><label><input type="radio" name="rf-topic" value="n"> No</label></fieldset>
    <fieldset class="lx-q"><legend>3. In the last 12 months, has the page earned search impressions, links or enquiries?</legend>
        <label><input type="radio" name="rf-value" value="y"> Yes</label><label><input type="radio" name="rf-value" value="n"> No</label></fieldset>
    <fieldset class="lx-q"><legend>4. Is your page the same kind of page that ranks for this search today?</legend>
        <label><input type="radio" name="rf-intent" value="y"> Yes</label><label><input type="radio" name="rf-intent" value="n"> No</label></fieldset>
    <fieldset class="lx-q"><legend>5. Are the facts, examples and screenshots still accurate?</legend>
        <label><input type="radio" name="rf-facts" value="y"> Yes</label><label><input type="radio" name="rf-facts" value="n"> No</label></fieldset>
    <div class="lx-result" aria-live="polite">
        <div data-rf="ask"><p>Answer question <span data-rf-next>1</span> to continue.</p></div>
        <div data-rf="merge" hidden><p class="lx-result-h">Merge it</p><p>Move the useful parts into the stronger of the two pages, redirect this URL there with a permanent redirect, and update your internal links.</p></div>
        <div data-rf="retire" hidden><p class="lx-result-h">Retire it</p><p>Nothing depends on this page. Redirect it to the closest relevant page if there is one. If there is none, let it return a 404 or 410.</p></div>
        <div data-rf="park" hidden><p class="lx-result-h">Leave it as it is</p><p>It still earns visits or links, but the topic is not worth more of your time. Add a link to a relevant service page and move on.</p></div>
        <div data-rf="rewrite" hidden><p class="lx-result-h">Rewrite it</p><p>The topic matters, but the page is not working. Rebuild it around what today's results offer: their format, their depth and the questions they answer.</p></div>
        <div data-rf="refresh" hidden><p class="lx-result-h">Refresh it</p><p>The page works but has gone stale. Update the facts, examples and screenshots, check the links, then update the date.</p></div>
        <div data-rf="keep" hidden><p class="lx-result-h">Keep it, and polish</p><p>Tighten the title and first paragraph, add links from newer posts and check that the call to action still fits.</p></div>
    </div>
    <button class="lx-reset" type="button" data-rf-reset>Start again</button>
</div>

<?php $table('What to do with a page that needs work', ['Situation', 'What to do', 'Watch out for'], [
    ['Another page targets the same search', 'Merge into the stronger page and redirect', 'Keep the URL that has more links and visits'],
    ['The topic no longer matters and the page earns nothing', 'Retire it, with a redirect if a close match exists', 'Do not redirect everything to the home page'],
    ['The topic no longer matters but the page still earns links or visits', 'Leave it, and add a relevant internal link', 'Spending hours on it'],
    ['The topic matters but the page earns nothing, or the format is wrong', 'Rewrite it', 'Keeping the old structure out of habit'],
    ['The page works but facts or examples are stale', 'Refresh it', 'Changing the date without changing the content'],
    ['Everything still holds', 'Keep it and polish', 'Fixing what is not broken'],
]); ?>
<p>One rule applies to all of them: Google's guidance on <?= ext_link($g['helpful'] . '#changing-dates', 'helpful content') ?> warns against changing a page's date to make it look fresh when the content has not substantially changed. Update the date when you have done real work, not instead of it.</p>
<?php },

'closing' => static function (): void { ?>
<h2 id="final-thoughts">Final Thoughts</h2>
<p>In many content libraries, a few pages do the heavy lifting and the rest do little. A short review each quarter, one page at a time, keeps the pages that matter strong and stops the rest from getting in their way.</p>
<p>Start with the pages that bring you enquiries today, then the ones that used to. Those are where an hour of work pays back fastest.</p>

<aside class="bx-cta bx-cta--main" aria-label="Start a conversation with VTurnU">
    <p class="bx-cta-h">Want Your Content Library Reviewed?</p>
    <p>We find the pages worth refreshing, merging or retiring, then update them for search and for AI answers.</p>
    <div class="bx-cta-acts">
        <a class="bx-btn bx-btn--light" href="/contact-us/" data-cta="blog-refresh-review">Review My Content <span aria-hidden="true">&rarr;</span></a>
        <a class="bx-link bx-link--light" href="/seo-content-writing/" data-cta="blog-refresh-writing">See SEO Content Writing</a>
    </div>
    <ul class="bx-cta-trust">
        <li>A senior strategist replies within one working day</li>
        <li>Projects start from <?= e(min_budget()) ?></li>
        <li>Your details stay private</li>
    </ul>
</aside>

<aside class="bx-author" aria-label="About the author">
    <p class="bx-author-h">Written by the VTurnU strategy team</p>
    <p>VTurnU is a B2B digital marketing and branding agency based in Chennai, working across SEO, AI search, paid media, content and websites for clients worldwide. Read more <a href="/about-us/">about the team</a>, <a href="/how-we-work/">how we work</a> and <a href="/ai-policy/">how we use AI</a>.</p>
</aside>
<?php },

];
