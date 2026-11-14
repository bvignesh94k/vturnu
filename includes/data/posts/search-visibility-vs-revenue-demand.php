<?php
/**
 * Decision guide: four tests for whether search visibility is new demand,
 * each answered from a report the reader already has, and a decision table.
 * Loaded by templates/blog-post.php through the 'rich' key.
 *
 * "We" is not used for any finding. The two client examples repeat what the
 * Cafy and Sai Impression case studies publish (includes/data/cases-real.php)
 * and add nothing: Cafy's branded share is published as "most", so no split
 * and no chart is given. Search Console and Google Analytics behaviour is
 * reported as Google documents it, from the page linked in the sentence, read
 * on 13 November 2026. The query examples in Test 2 are invented and say so.
 * Nothing here says non-branded traffic is always better, or that passing the
 * tests raises revenue.
 */

$s = [
    'perf' => 'https://support.google.com/webmasters/answer/7576553',
    'branded' => 'https://developers.google.com/search/blog/2025/11/search-console-branded-filter',
    'ga4gsc' => 'https://support.google.com/analytics/answer/10737381',
];

return [

'body' => static function () use ($s): void { ?>
<p class="bx-open">The Search Console chart goes up every month. Impressions are up, clicks are up, and the average position is better than it was in spring. The sales pipeline looks the same as it did a year ago.</p>
<p>Both things can be true, because the chart and the pipeline count different things. Visibility is a count of appearances in search results. Demand is a count of buyers who want what you sell. A site can gain a great deal of the first without adding any of the second.</p>
<p>This article does not argue that leads matter more than traffic. It gives you four tests to run on your own data, in order, each from a report you already have. At the end there is a decision table: which test failed, what that means and what to change.</p>

<?php px_answer('search-visibility-vs-revenue-demand'); ?>

<?php px_toc([
    'appearances-and-buyers' => 'Visibility counts appearances, demand counts buyers',
    'test-1' => 'Test 1: branded vs non-branded search',
    'test-2' => 'Test 2: what the non-branded searches want',
    'test-3' => 'Test 3: whether the searchers fit',
    'test-4' => 'Test 4: whether enquiries become conversations',
    'decision-table' => 'Reading the four results together',
    'report' => 'What a good report shows',
    'limits' => 'What the tests cannot show',
    'faq-title' => 'FAQs',
]); ?>

<h2 id="appearances-and-buyers">Visibility Counts Appearances, Demand Counts Buyers</h2>
<p>The <?= ext_link($s['perf'], 'Performance report in Search Console') ?> has four metrics: clicks, impressions, click-through rate and average position. Its default view covers three months. Every one of those metrics describes a search result. None of them describes a person, a company or an intention to buy.</p>
<p>So a rising chart leaves four questions open. Did these people already know you? What were they looking for? Are they the kind of company that buys from you? Did any of them start a conversation with sales? Each test below answers one of those questions. The same gap between attention and buying appears on social channels, where <a href="/blog/linkedin-engagement-vs-buyer-intent/">LinkedIn engagement is not the same as buyer intent</a>.</p>

<?php px_flow('four-tests', 'Four tests, in order, for a visibility chart that is not matched by revenue', [
    ['Branded vs non-branded search', 'How much of the traffic came from people who searched for you by name.', 'Search Console'],
    ['What the non-branded searches want', 'Whether those searches are to learn, compare, find a supplier or enquire.', 'Search Console queries'],
    ['Whether the searchers fit', 'Whether the countries and landing pages match the buyers you can serve.', 'Search Console and analytics'],
    ['Whether enquiries become conversations', 'The share of enquiries from search that sales accepts, by landing page.', 'CRM or sales inbox'],
], [
    'sub' => 'Each test narrows the one before it. Set your own pass mark for each before you look at the numbers.',
    'cap' => 'The first three tests run on Search Console and analytics, and the third also needs a short list of past enquiries. The fourth needs your sales records.',
]); ?>

<p><strong>Set the pass marks first.</strong> There is no universal threshold for any of these tests. A company with a well-known name will always have a large branded share. A company that sells in one country needs a different country mix from an exporter. Write down what a pass looks like for your business before you open the reports, so the numbers cannot talk you into a comfortable answer.</p>

<h2 id="test-1">Test 1: Branded vs Non-Branded Search</h2>
<p>A branded search is someone typing your company name. That person already knew you. They heard about you from a colleague, an event, an advert or a sales call, and search was the door they used. The click is real. The demand was created somewhere else.</p>
<p>A non-branded search describes a need without naming you. When your page appears for it, search has put you in front of someone who may not have known you existed. That is the part of the chart that can be new demand.</p>
<p><strong>How to run it.</strong> Search Console has a branded queries filter in the Performance report. <?= ext_link($s['branded'], 'Google announced it') ?> on 20 November 2025 and made it available to all eligible sites from 11 March 2026. Apply the filter, note clicks and impressions for branded queries, then do the same for non-branded queries over the same three months.</p>
<p>Google describes the filter's rules and limits in that announcement, and they matter when you read the result:</p>
<ul class="post-list">
    <li><strong>What counts as branded.</strong> Queries that contain the brand name, its variations and misspellings, and brand-related products.</li>
    <li><strong>How queries are sorted.</strong> By an internal system that uses AI, not by a pattern you write. Google says some queries may be put in the wrong group.</li>
    <li><strong>Who gets it.</strong> Top-level properties with enough query volume. A small site, or a property set up for one folder, may not see the filter.</li>
    <li><strong>What it does not do.</strong> Google says the filter has no effect on ranking. It is a way of reading the report.</li>
</ul>
<p>If the filter is missing on your property, filter the query list by your company name and its common misspellings instead, and treat the result as an estimate.</p>
<p><strong>An example from published work.</strong> Cafy is a UK job platform. In the three months to 29 October 2026, Search Console showed 14.8K clicks from 28.6K impressions, a click-through rate of 51.7 percent. A rate that high is itself a clue, because people who search for a brand by name usually click it. Most of that traffic was branded search, which is why the content plan targets non-branded demand next. The <a href="/case-studies/cafy-careers-uk-job-platform/">Cafy case study</a> has the detail. Read as a headline, 14.8K clicks looks like search creating demand. Read through this test, it shows a known brand whose search traffic had not yet reached people who did not know it.</p>
<p class="bx-note"><strong>A large branded share is not a failure.</strong> It means people know you and can find you, which is worth having. It fails this test only if the question was whether search is reaching new buyers. Growth in branded searches can also be a sign that other marketing is working.</p>

<h2 id="test-2">Test 2: What the Non-Branded Searches Want</h2>
<p>Non-branded clicks are not all equal. Someone looking up a definition and someone looking for a supplier both count as one click. Only one of them is near a purchase.</p>
<p><strong>How to run it.</strong> With the non-branded filter still on, export the queries with the most impressions, a few hundred if you have them. Put each one into one of four groups. The table shows the groups. The example wording is invented, for a company that makes industrial labels.</p>

<?php px_table('Four groups of non-branded search, with invented examples', ['Group', 'What the searcher wants', 'Invented example', 'How close to an enquiry'], [
    ['Learn', 'To understand a topic or a term', 'what is a thermal transfer label', 'Far. Many are students, job seekers or people in other industries'],
    ['Compare', 'To weigh options before choosing', 'paper labels vs film labels for cold storage', 'Closer. The need is real, and the supplier is not chosen'],
    ['Find a supplier', 'A list of companies that can do the job', 'barcode label manufacturer in chennai', 'Close. This is a shortlist being built'],
    ['Buy or enquire', 'A price, a quote or a way to order', 'custom label printing quote minimum order', 'Closest. The searcher is ready to contact someone'],
]); ?>

<p>Then read the mix, by impressions and by clicks. A site whose non-branded visibility is almost all "learn" has built an audience, not a market. A site with visibility in "find a supplier" and "buy or enquire" is in front of people who are choosing.</p>
<p>Check position as well as group. A supplier search where your page sits on the third page is counted as an impression and seen by almost nobody.</p>
<p><strong>An example from published work.</strong> Sai Impression manufactures labels in Chennai. In the same three months, its site recorded 92.9K impressions and 1.32K clicks. The <a href="/case-studies/sai-impression-label-manufacturer/">Sai Impression case study</a> notes that commercial searches such as "label manufacturers in india" and "label stock manufacturers in india" now bring buyers who do not yet know the brand. Those are "find a supplier" searches. The case study does not publish the full mix by group, so that is as far as the example goes. For the full set of searches a manufacturer's buyers make, see <a href="/blog/industrial-buyer-search-intent/">how industrial buyers search</a>.</p>
<p><strong>Do not discard the "learn" group.</strong> Articles that answer early questions are how a buyer first meets you, and they give your commercial pages something to be linked from. The test fails only when "learn" is all there is, and nothing on the site catches the reader who is ready to compare or enquire.</p>
<p>Want the four tests run on your Search Console data? <a href="/seo-services/#enquire" data-cta="blog-visibility-audit">Ask for a free SEO audit</a> and say which test you are least sure about.</p>

<h2 id="test-3">Test 3: Whether the Searchers Fit</h2>
<p>A supplier search from a country you do not ship to is not demand you can use. Neither is a visit from a company far too small, or far too large, for what you sell. This test asks whether the people behind the commercial searches could become customers.</p>
<p><strong>How to run it.</strong> Search tools give you two honest signals and no more.</p>
<ul class="post-list">
    <li><strong>Country.</strong> Break the non-branded clicks down by country in Search Console. Compare the list with the markets you serve. A large share from countries outside them is visibility you cannot convert.</li>
    <li><strong>Landing page.</strong> Look at which pages the non-branded clicks land on. Clicks that land on service, product and industry pages came from people looking for what you sell. Clicks that land on one popular article about a general topic tell you little about fit.</li>
</ul>
<p>Analytics adds what visitors did next. The <?= ext_link($s['ga4gsc'], 'Search Console collection in Google Analytics') ?> lists landing pages with their search clicks beside analytics metrics. It holds 16 months of data and runs about 48 hours behind. Use it to see whether the pages that win search clicks are also the pages where people go on to a contact or pricing page.</p>
<p><strong>What neither tool can show.</strong> Search Console does not name the companies that searched, and it does not report job titles or company size. Analytics cannot tell you which query brought a particular visitor. So the last part of this test is done by hand: take the enquiries that came from organic search last quarter and write down the country, the type of company and the page each one first landed on. Twenty rows are enough to see a pattern.</p>

<h2 id="test-4">Test 4: Whether Enquiries Become Conversations</h2>
<p>The first three tests are about who arrives. The fourth is about what sales does with them. For each landing page that brings enquiries from search, count two things: the enquiries received, and the enquiries sales accepted as worth a conversation. The second number divided by the first is the qualified lead rate for that page.</p>
<p>Read it by page, not as one site-wide figure. It is common for a few pages to produce nearly all the accepted enquiries while the pages with the most traffic produce few.</p>
<p>This test needs the source and the first landing page stored on every enquiry, and an agreed rule for "accepted". Our framework for <a href="/blog/measure-qualified-leads-from-organic-search/">measuring qualified leads from organic search</a> gives the stages, the fields to store and the formulas, with a calculator. If you cannot run Test 4 yet, that is a finding in itself: the visibility chart is the only evidence you have, and it cannot answer the question.</p>

<h2 id="decision-table">Reading the Four Results Together</h2>
<p>One failed test is rarely the whole story. The pattern across the four tells you where the gap between visibility and revenue sits, and so what to change. Find the row that matches your results.</p>

<?php px_table('What the four results mean together, and what to do', ['What you find', 'What it means', 'What to change'], [
    ['Mostly branded clicks, and enquiries are good', 'The brand is known and easy to find. Search is not yet reaching new buyers', 'Keep what serves existing demand. Add pages for the non-branded searches a new buyer would make'],
    ['Non-branded is growing, and almost all of it is "learn"', 'Content without a commercial layer. You have readers, not prospects', 'Add comparison, supplier and service pages, and link the articles to them'],
    ['Commercial searches, from the wrong countries or the wrong kind of company', 'The pages attract an audience you cannot serve', 'Say on the page who you serve: markets, order sizes, industries. Target the places you sell in'],
    ['The right searches and the right visitors, and few enquiries', 'The fault is on the page, after the click', 'Review the offer, the proof and the form on the landing pages'],
    ['Enquiries arrive, and sales rejects most of them', 'The page invites people who do not qualify, or junk is getting through', 'Tighten the page and the form, filter junk, and agree the acceptance rule with sales'],
    ['All four tests pass, and revenue is still flat', 'The lag between enquiry and order, or a problem after the handover', 'Report by the quarter leads arrived, and look at what happens between opportunity and order'],
]); ?>

<p>For the fourth row, our article on <a href="/blog/why-your-website-gets-traffic-but-no-leads/">why a website gets traffic but no leads</a> covers the faults that sit on the page. For the first two rows, the work is search strategy: deciding which non-branded searches are worth winning and building the pages for them.</p>
<p><strong>Compare like with like.</strong> Run the tests on the same three months each time, and compare a period with the same period a year earlier where you can, because many B2B searches are seasonal. If Google is rolling out a core update while you measure, wait until it has finished. Our note on the <a href="/updates/november-core-update-2026/">November 2026 core update</a> explains how to compare weeks fairly during one.</p>

<h2 id="report">What a Good Report Shows</h2>
<p>Once the tests are running, the monthly chart of impressions can move to an appendix. Leadership needs three lines.</p>
<ol class="bx-steps">
    <li><strong>New demand reached.</strong> Non-branded clicks on commercial searches, in the markets you serve. Branded clicks are reported beside it, separately.</li>
    <li><strong>Qualified enquiries from it.</strong> Enquiries from organic search that sales accepted, with the landing pages they came through.</li>
    <li><strong>The one thing being changed.</strong> The row of the decision table you are in, and the action it calls for this quarter.</li>
</ol>
<p>A report in that shape can show a month where impressions fell and the business gained, or the reverse. That is the point of it.</p>

<h2 id="limits">What the Tests Cannot Show</h2>
<ul class="post-list">
    <li><strong>They do not prove cause.</strong> A buyer may read an article, leave, and return weeks later by typing your name. That visit is counted as branded, though a non-branded search started it. The split is a guide, not an account of every journey.</li>
    <li><strong>Non-branded is not always better.</strong> A branded visitor is often closer to buying. The tests ask where new demand comes from, not which visitor is worth more.</li>
    <li><strong>The sorting is imperfect.</strong> Google says its branded filter can misclassify queries, and your own four groups are a judgement. Keep the method the same each quarter so the trend means something.</li>
    <li><strong>Passing all four does not promise revenue.</strong> Price, sales follow-up, delivery and timing all sit outside search. The tests show whether search is doing its part.</li>
</ul>
<?php },

'closing' => static function (): void { ?>
<h2 id="final-thoughts">The Chart Answers a Smaller Question Than You Asked</h2>
<p>A visibility chart is not wrong. It answers a smaller question than the one a founder is asking. It says how often you appeared and how often you were clicked. It does not say whether those people were new to you, wanted to buy, could buy from you, or spoke to sales.</p>
<p>Run the four tests in order on one quarter of data and find your row in the table. The result is usually one specific thing to change, which is more useful than a general instruction to do more SEO or to stop doing it.</p>

<?php px_cta_main([
    'h' => 'Not Sure Which Test Your Search Traffic Fails?',
    'p' => 'Tell us what your Search Console chart shows and what your pipeline shows. A strategist will tell you where the gap is likely to be.',
    'btn' => 'Talk to a Strategist',
    'cta' => 'blog-visibility-contact',
    'link' => ['/seo-services/', 'See SEO Services', 'blog-visibility-service'],
]); ?>

<?php px_author(); ?>
<?php },

];
