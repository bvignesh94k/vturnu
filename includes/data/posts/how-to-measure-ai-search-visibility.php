<?php
/**
 * Method: measuring AI search visibility in five steps. Rebuilt on
 * 14 November 2026 at the same address as the October 2026 article, with a
 * run protocol, a recording sheet to download and sources for every claim.
 * Loaded by templates/blog-post.php through the 'rich' key.
 *
 * The variability study (SparkToro with Gumshoe, January 2026) was read
 * through an archive copy because the live page refuses automated readers.
 * It is not peer reviewed, and one co-author works for an AI tracking
 * company; the article says both. Google and Microsoft pages were read on
 * 13 November 2026. Margins of error are worked out from the standard formula
 * for a proportion and described as approximate.
 */

$s = [
    'sparktoro' => 'https://sparktoro.com/blog/new-research-ais-are-highly-inconsistent-when-recommending-brands-or-products-marketers-should-take-care-when-tracking-ai-visibility/',
    'bing' => 'https://blogs.bing.com/webmaster/February-2026/Introducing-AI-Performance-in-Bing-Webmaster-Tools-Public-Preview',
    'gscgen' => 'https://developers.google.com/search/blog/2026/06/gen-ai-performance-reports',
    'ga4' => 'https://support.google.com/analytics/answer/9756891',
];

return [

'body' => static function () use ($s): void { ?>
<p class="bx-open">You cannot look up a ranking in ChatGPT. There is no position one, no results page and no report that says where you stand. The same question can produce a different answer an hour later.</p>
<p>That does not make AI search unmeasurable. It makes it a sampling problem. You ask a fixed set of buyer questions, in the same way, often enough to see a pattern, and you count how often your company is named.</p>
<p>This guide gives the method in five steps, with a recording sheet you can download and the sample sizes you need before a number means anything. It takes about half a day to set up and a few hours a month to run.</p>

<?php px_answer('how-to-measure-ai-search-visibility'); ?>

<?php px_toc([
    'step-1' => 'Step 1: Build a fixed prompt set',
    'step-2' => 'Step 2: Run it the same way every month',
    'step-3' => 'Step 3: Record the same six things for every answer',
    'step-4' => 'Step 4: Add the data you own',
    'step-5' => 'Step 5: Report a rate, with its sample size',
    'limits' => 'What this method cannot tell you',
    'faq-title' => 'FAQs',
]); ?>

<h2 id="step-1">Step 1: Build a Fixed Prompt Set</h2>
<p>A prompt set is a list of questions a real buyer would ask an AI assistant. Take them from sales calls, enquiry forms and support tickets, not only from a keyword tool. Write them the way a buyer talks, in full sentences.</p>
<p>Sort them into four groups, because each group tells you something different.</p>

<?php px_table('Four groups of buyer prompts', ['Group', 'Example wording', 'What it shows'], [
    ['Shortlist', '"Which companies supply [product] for [use] in [country]?"', 'Whether you are considered at all'],
    ['Comparison', '"How do [supplier A] and [supplier B] compare for [need]?"', 'How you are described next to a rival'],
    ['Problem', '"How do I fix [problem your product solves]?"', 'Whether your content is used as a source'],
    ['Brand', '"What does [your company] do, and is it reliable?"', 'Whether what AI says about you is accurate'],
]); ?>

<p><strong>How many prompts?</strong> Start with 20 to 30, spread across the four groups. That is enough to see large differences and small enough to run every month without giving up. Once the routine is settled, grow the set to 40 to 60, the range we use in our <a href="/guides/complete-guide-to-ai-seo/">guide to AI SEO</a>. Beyond that you add work faster than you add accuracy.</p>
<p>Two rules keep the set useful. Leave your own brand name out of the shortlist and problem prompts, so the question does not lead the answer. And freeze the set. If you change the questions every month, you can no longer compare one month with the next, which is the whole purpose.</p>

<h2 id="step-2">Step 2: Run It the Same Way Every Month</h2>
<p>AI answers vary far more than search results do. In January 2026, <?= ext_link($s['sparktoro'], 'SparkToro published a study') ?>, run with the AI tracking company Gumshoe, in which 600 volunteers ran 12 prompts nearly 3,000 times across ChatGPT, Claude and Google's AI features. The chance of getting the same list of brands twice was less than 1 in 100. The chance of the same list in the same order was about 1 in 1,000.</p>
<p>The same study found something steadier underneath. How often a brand appeared across many runs was fairly consistent. Its authors concluded that the share of answers naming a brand is a reasonable thing to track, and that position within an answer is not. The study is not peer reviewed, and one of its authors sells tracking software, but the finding matches what anyone sees who runs the same prompt twice.</p>
<p>So the method has to control what it can and repeat what it cannot.</p>
<ol class="bx-steps">
    <li><strong>Use the same engines each month.</strong> For most B2B companies that means ChatGPT, Gemini, Perplexity, Copilot and Google's AI Overviews or AI Mode. Drop any your buyers do not use.</li>
    <li><strong>Start clean.</strong> Use a signed-out window or a temporary chat where the product has one, so earlier conversations do not shape the answer. Start a new chat for every prompt.</li>
    <li><strong>Paste the prompt exactly.</strong> Copy it from your sheet. Small changes in wording change the answer.</li>
    <li><strong>Run every prompt three times on each engine.</strong> One run is an anecdote. Three give you a rate.</li>
    <li><strong>Note the date and the country you ran from.</strong> Answers differ by location, and you will want to know later which run was which.</li>
</ol>
<p>Thirty prompts, three runs and four engines come to 360 answers, at about a minute each to run and record. That is roughly six hours a month, and it can be split between two people. If it is too much, keep the two engines your buyers use most.</p>

<h2 id="step-3">Step 3: Record the Same Six Things for Every Answer</h2>
<p>Decide what to record before you start, and record the same things every time. Six columns are enough.</p>

<?php px_table('What to record for each answer', ['Column', 'What to write', 'Why it matters'], [
    ['Named', 'Yes or no: does the answer mention your company?', 'This is the count behind share of answer'],
    ['Cited', 'Yes or no: does it link to one of your pages?', 'A citation can send a visit. A mention cannot'],
    ['Page cited', 'The address it linked to', 'Shows which pages AI systems treat as sources'],
    ['Other brands named', 'Every other company in the answer', 'Shows who you are being compared with'],
    ['Accurate', 'Yes, partly or no', 'Wrong facts point to weak or conflicting information about you'],
    ['What was wrong', 'The incorrect statement, copied out', 'This becomes your list of things to fix'],
]); ?>

<p>Do not record your position in the answer as a score. The study above found it close to random from one run to the next.</p>
<p>The accuracy columns are the ones people skip, and they are often the most useful. If three assistants say you are based in the wrong city, or list a service you stopped offering, that is not an AI problem. It is a sign that your own pages, your listings and the sites that mention you disagree with each other.</p>

<div class="lx-tool" id="recording-sheet">
    <p class="lx-tool-k">Free download</p>
    <p class="lx-tool-h">The Recording Sheet</p>
    <p>A spreadsheet with the columns above, plus the date, engine, prompt group and run number, and one example row. It opens in Excel, Google Sheets or Numbers.</p>
    <div class="lx-btns"><a class="lx-btn lx-dl" href="/assets/downloads/ai-visibility-recording-sheet.csv" download data-cta="blog-aivis-sheet">Download the sheet (CSV)</a></div>
    <p class="lx-privacy">No sign-up. The file is a plain CSV with no tracking in it.</p>
</div>

<h2 id="step-4">Step 4: Add the Data You Own</h2>
<p>Prompt sampling is an estimate. Four first-party sources sit beside it, and each one counts something different.</p>

<?php px_table('First-party sources for AI visibility, and what each one counts', ['Source', 'What it counts', 'What it leaves out'], [
    ['Bing Webmaster Tools, AI Performance', 'Citations of your pages in Copilot and Bing\'s AI answers, with a sample of the queries behind them', 'Clicks, and anything outside Microsoft\'s products'],
    ['Search Console, Generative AI report', 'Impressions of your pages in Google\'s AI Overviews and AI Mode', 'Clicks, queries and position'],
    ['Analytics, AI Assistant channel', 'Visits that arrive from assistants such as ChatGPT', 'Visits that arrive without a referrer, which show as direct'],
    ['Server logs', 'Requests from AI crawlers and fetchers', 'People. A crawler request is not a visit'],
]); ?>

<p>All three reports are recent. Microsoft <?= ext_link($s['bing'], 'launched AI Performance') ?> as a public preview in February 2026. Google <?= ext_link($s['gscgen'], 'announced the Generative AI performance report') ?> in June 2026, and it shows impressions only. Google Analytics added an <?= ext_link($s['ga4'], 'AI Assistant channel') ?> to its default channel group in May 2026.</p>
<p>These numbers do not add up to one total, and they should not be forced to. A citation is not a visit, and a visit is not an enquiry. Our article on <a href="/blog/ai-citations-vs-website-traffic/">AI citations vs website traffic</a> explains what each one proves and how to set up the tracking. If the crawlers are not reaching you at all, start with the <a href="/blog/ai-crawler-access-problems/">six access checks</a> instead.</p>

<h2 id="step-5">Step 5: Report a Rate, With Its Sample Size</h2>
<p>The headline number is share of answer: the answers that named you, divided by the answers you counted. Report it for each engine separately before you give a total, because engines disagree and the differences are informative.</p>
<p>Always put the sample size next to the rate. A percentage from a small sample moves a long way by chance.</p>

<?php px_chart('margin-by-sample', 'How far a share of answer can move by chance', 'Approximate 95 percent margin of error, in points either way, for a rate near 50 percent', [
    ['label' => '30 answers', 'value' => 17.9, 'text' => 'About 18 points'],
    ['label' => '60 answers', 'value' => 12.7, 'text' => 'About 13 points'],
    ['label' => '90 answers', 'value' => 10.3, 'text' => 'About 10 points'],
    ['label' => '180 answers', 'value' => 7.3, 'text' => 'About 7 points'],
    ['label' => '360 answers', 'value' => 5.2, 'text' => 'About 5 points'],
], [
    'max' => 26, 'ticks' => [0, 5, 10, 15, 20],
    'head' => ['Answers counted', 'Margin either way'],
    'source' => 'Worked out from the standard formula for a proportion: 1.96 times the square root of p times (1 minus p), divided by n, with p at 0.5. Repeated runs of one prompt are related to each other, so treat these as minimums.',
]); ?>

<p>Ninety answers is 30 prompts run three times on one engine. At that size, a share of answer of 42 percent one month and 47 percent the next is not a change. It is the same number measured twice. A move from 42 to 60 is worth acting on.</p>
<p>A monthly report needs four lines:</p>
<ul class="post-list">
    <li><strong>Share of answer</strong> for each engine, with the number of answers beside it.</li>
    <li><strong>The trend</strong> over at least three months, not month on month.</li>
    <li><strong>What AI got wrong</strong> about you, and whether last month's errors have gone.</li>
    <li><strong>Three fixes</strong> for the coming month.</li>
</ul>
<p>Leadership will ask how you compare with competitors. The same sheet answers that, with some extra care about fairness and sample size. See <a href="/blog/ai-search-competitor-benchmark/">how to benchmark competitors in AI search</a>.</p>

<h2 id="limits">What This Method Cannot Tell You</h2>
<p>It is better to state the limits yourself than to have a finance lead find them.</p>
<ul class="post-list">
    <li><strong>It is a sample.</strong> Your prompts are a few dozen of the thousands of ways buyers ask. Share of answer describes your prompt set, not the market.</li>
    <li><strong>It is not a market share.</strong> Being named in 40 percent of answers does not mean 40 percent of buyers choose you.</li>
    <li><strong>It does not explain why.</strong> You can see that a rival is named more often. The sheet will not tell you the reason, only which pages were cited.</li>
    <li><strong>It does not measure revenue.</strong> Keep it beside your pipeline figures, never in place of them.</li>
</ul>
<?php },

'closing' => static function (): void { ?>
<h2 id="final-thoughts">Treat It as a Sample, and Say How Big</h2>
<p>AI visibility can be measured well enough to manage, as long as you treat it as a sample and say how big the sample is. Fixed prompts, repeated runs, six columns and a rate with its margin are the whole method.</p>
<p>The first month gives you a baseline and a list of things AI gets wrong about you. Fix those first. They are usually the cheapest improvements you will find, and they are the ones that cost you buyers who never visit your site.</p>

<?php px_cta_main([
    'h' => 'Want This Measured for Your Brand?',
    'p' => 'Ask for a free AI visibility report. We run buyer prompts across the main assistants and show you where you are named, where you are missing and what is wrong.',
    'href' => '/ai-seo/#enquire',
    'btn' => 'Get My Free AI Visibility Report',
    'cta' => 'blog-aivis-report',
    'link' => ['/ai-seo/', 'See AI SEO Services', 'blog-aivis-service'],
]); ?>

<?php px_author(); ?>
<?php },

];
