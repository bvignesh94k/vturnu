<?php
/**
 * How-to: benchmarking competitors in AI search with a four-part scorecard,
 * a sample-size table and a sort of the sources behind each name, with a
 * calculator (data-lx-tool="calc", data-calc="sample" in
 * assets/js/listicle.js). Loaded by templates/blog-post.php through the
 * 'rich' key.
 *
 * No sample was run for this article, so nothing is written in the first
 * person and no competitor or real brand is named. Brand A to Brand D and
 * every count beside them are invented and labelled as such where they
 * appear. The variability study (SparkToro with Gumshoe, January 2026) was
 * read through an archive copy on 13 November 2026; it is not peer reviewed
 * and one co-author works for an AI tracking company, and the article says
 * both. The Bing Webmaster Tools post was read on 13 November 2026. The
 * margins are worked out from the standard formula for the difference
 * between two proportions and agree with what the calculator prints.
 * Nothing here says the method predicts a recommendation.
 */

$s = [
    'sparktoro' => 'https://sparktoro.com/blog/new-research-ais-are-highly-inconsistent-when-recommending-brands-or-products-marketers-should-take-care-when-tracking-ai-visibility/',
    'bing' => 'https://blogs.bing.com/webmaster/February-2026/Introducing-AI-Performance-in-Bing-Webmaster-Tools-Public-Preview',
    'bingshare' => 'https://blogs.bing.com/search/June-2026/New-AI-Visibility-Insights-in-Bing-Webmaster-Tools-Intents-Topics-Citation-Share-Compare',
];

$in = static fn (string $name, string $label, string $value): string =>
    '<label><span>' . e($label) . '</span><input type="number" inputmode="numeric" min="0" step="1" data-calc-in="' . e($name) . '" value="' . e($value) . '"></label>';

return [

'body' => static function () use ($s, $in): void { ?>
<p class="bx-open">"We appear in 46 percent of AI answers and our main competitor appears in 56." It sounds like a finding. It means very little until you know how many answers were counted, who wrote the questions and what "appear" meant.</p>
<p>Comparing brands in AI search is harder than measuring your own, for three reasons. The answers change from one run to the next. The questions are easy to tilt towards yourself without noticing. And a gap that looks large on a slide is often smaller than the noise in the sample.</p>
<p>This guide is a manual method you can run in a spreadsheet. It covers which competitors to include, how to write prompts that favour nobody, four things to score for each brand, how large a gap must be before it is real, and how to find the outside pages that get competitors named. It builds on the monthly routine in our guide to <a href="/blog/how-to-measure-ai-search-visibility/">measuring AI search visibility</a>, so set that up first if you have not.</p>

<?php px_answer('ai-search-competitor-benchmark'); ?>

<?php px_toc([
    'fair-set' => 'Choose a fair set of competitors',
    'neutral-prompts' => 'Write prompts that favour nobody',
    'four-scores' => 'Score four things for each brand',
    'real-gap' => 'How large a gap has to be',
    'sources' => 'Find the sources behind the names',
    'engines' => 'Report each engine separately',
    'one-page' => 'A report that fits on one page',
    'limits' => 'What a benchmark cannot tell you',
    'faq-title' => 'FAQs',
]); ?>

<h2 id="fair-set">Choose a Fair Set of Competitors</h2>
<p>The comparison is only as fair as the list of brands in it. A list drawn up from memory in a meeting tends to hold the rivals sales loses to most often and to miss the ones buyers hear about from an assistant.</p>
<p>Three rules keep the set honest.</p>
<ol class="bx-steps">
    <li><strong>Three to five direct competitors.</strong> They sell to the same kind of buyer, in the same market, at a similar size of deal. A company that wins a different customer is not a competitor for this purpose, even if it sells a similar product.</li>
    <li><strong>One larger name for reference.</strong> Include one well-known brand in the category and label it as the reference. It shows what a high rate looks like on your prompts. It is not a target for next quarter.</li>
    <li><strong>A written rule for brands you did not list.</strong> Assistants will name companies that are not on your list. Decide in advance when one joins the scorecard. A workable rule: any brand named in at least a tenth of the answers in two months running is added from the following month.</li>
</ol>
<p>Apart from brands added under that rule, fix the set for at least a quarter. If the list changes every month, no trend can be read from it.</p>

<h2 id="neutral-prompts">Write Prompts That Favour Nobody</h2>
<p>The prompt set decides the result more than anything else. Questions written by your own marketing team carry your own vocabulary: your product names, your category label and the features you lead with. An assistant answers the question it was asked, so a tilted question produces a tilted answer.</p>
<p>Use the buyer's wording, and keep brand names out of every category prompt. Sales call notes, enquiry forms and the queries in Search Console are better sources of wording than a brainstorm. Then sort the prompts into three groups, so the set covers different moments in a purchase.</p>
<ul class="post-list">
    <li><strong>Shortlist prompts.</strong> The buyer asks who to consider. No brand is named.</li>
    <li><strong>Problem prompts.</strong> The buyer describes a difficulty and asks how to solve it. No brand is named.</li>
    <li><strong>Comparison prompts.</strong> The buyer names two or more brands and asks how they differ. Score these as a separate group, because every brand named in the question will be named in the answer.</li>
</ul>

<?php px_table('Prompts that tilt the result, and neutral versions of the same question', ['Group', 'Tilted prompt', 'Why it is tilted', 'Neutral version'], [
    ['Shortlist', 'Which suppliers offer [a feature only you have]?', 'It asks for your differentiator, so it finds you', 'Which suppliers should a mid-sized company in [country] consider for [category]?'],
    ['Shortlist', 'Is [your brand] a good choice for [category]?', 'It names you and invites agreement', 'What should a buyer look for when choosing a [category] supplier, and which companies meet that?'],
    ['Problem', 'How does [your product name] solve [problem]?', 'It uses your product name as the subject', 'Our team has [problem]. What are the usual ways to fix it, and who provides them?'],
    ['Comparison', 'Why is [your brand] better than [competitor]?', 'It states the conclusion in the question', 'How do [brand] and [brand] differ for a company that needs [requirement]?'],
]); ?>

<p>In comparison prompts, rotate the order in which the brands are named, so that no brand is always first in the question. Before the first run, give the full set to someone outside marketing and ask one thing: could a competitor have written these? If the answer is no, rewrite.</p>

<h2 id="four-scores">Score Four Things for Each Brand</h2>
<p>For every answer, record four things about every brand in the set. Each one is a yes or a no, which keeps two people scoring the same answer in agreement.</p>

<?php px_table('The four scores, and the rule for each', ['Score', 'Count a yes when', 'What it shows'], [
    ['Named', 'The brand appears anywhere in the answer', 'Whether the assistant connects the brand with the question at all'],
    ['Cited with a link', 'The answer links to a page on the brand\'s own website', 'Whether the brand\'s own pages are being used as a source'],
    ['Described accurately', 'What the answer says about the brand is correct today. Score this only where the brand is named', 'Whether being named is helping or misleading the buyer'],
    ['Recommended', 'The answer puts the brand forward as a choice for the buyer\'s need, and does not only list it or mention it in passing', 'The difference between being suggested and being present'],
]); ?>

<p>"Recommended" needs a written rule, because it is a judgement. One that works: the answer says the brand suits the buyer's stated need, or includes it in a list the answer itself presents as its suggestions. A brand mentioned as an alternative, as background or with a warning is named and not recommended.</p>
<p>You can only check accuracy for competitors from what they publish, so keep that score to plain facts: what they sell, where they operate and what they say they charge.</p>

<p class="bx-note"><strong>Do not score "named first".</strong> It is tempting to give extra credit to the brand at the top of a list. A <?= ext_link($s['sparktoro'], 'study published by SparkToro in January 2026') ?>, carried out with a co-author from Gumshoe, an AI tracking company, had 600 volunteers run 12 prompts through ChatGPT, Claude and Google's AI features in November and December 2025, for 2,961 runs in total. The chance of getting the same list of brands twice was under 1 in 100, and about 1 in 1,000 for the same list in the same order. How often a brand appeared across many runs was much steadier. The study is not peer reviewed, and its authors disclose the link to a company that sells tracking. Its conclusion still matches the arithmetic: count how often, not where.</p>

<?php px_example('Example scorecard, with invented brands and numbers', static function (): void { ?>
<p>Four made-up brands in an unnamed category. Thirty neutral prompts were run three times each on one assistant, which gives 90 answers. Brand A is the company doing the benchmark. None of these figures comes from a real test.</p>
<?php px_table('One engine, 90 answers, four brands', ['Brand', 'Named', 'Cited with a link', 'Described accurately', 'Recommended'], [
    ['Brand A (you)', '41 of 90 (46 percent)', '12 of 90 (13 percent)', '33 of the 41 answers that named it', '19 of 90 (21 percent)'],
    ['Brand B', '50 of 90 (56 percent)', '21 of 90 (23 percent)', '46 of 50', '28 of 90 (31 percent)'],
    ['Brand C', '29 of 90 (32 percent)', '6 of 90 (7 percent)', '25 of 29', '9 of 90 (10 percent)'],
    ['Brand D', '18 of 90 (20 percent)', '9 of 90 (10 percent)', '17 of 18', '5 of 90 (6 percent)'],
]); ?>
<p>The tempting headline is "Brand B is 10 points ahead of us". The next section shows why that line should not be written yet, and which of these gaps can be reported.</p>
<?php }); ?>

<h2 id="real-gap">How Large a Gap Has to Be Before It Is Real</h2>
<p>Every rate on the scorecard is an estimate from a sample, so it carries a margin of error. When you compare two brands, both rates carry one, and the margin on the difference is wider than the margin on either rate alone.</p>
<p>The sum is the standard one for the difference between two proportions. Take each brand's rate as a fraction, <em>p</em>, and the number of answers, <em>n</em>:</p>
<?php lx_code(<<<'TXT'
margin on the gap = 1.96 x square root of ( pA x (1 - pA) / n  +  pB x (1 - pB) / n )

Example: both brands near 50 percent, 90 answers
  0.5 x 0.5 / 90  +  0.5 x 0.5 / 90  =  0.005556
  square root of 0.005556            =  0.0745
  1.96 x 0.0745                      =  0.146, or 14.6 points
TXT, 'Arithmetic'); ?>
<p>The margin is widest when both rates are near 50 percent, so that case gives a safe figure to plan with.</p>

<?php px_chart('gap-by-sample', 'The gap two brands need before they can be told apart', 'Approximate 95 percent margin on the difference between two rates, in points, when both are near 50 percent', [
    ['label' => '30 answers each', 'value' => 25.3, 'text' => 'About 25 points'],
    ['label' => '60 answers each', 'value' => 17.9, 'text' => 'About 18 points'],
    ['label' => '90 answers each', 'value' => 14.6, 'text' => 'About 15 points'],
    ['label' => '180 answers each', 'value' => 10.3, 'text' => 'About 10 points'],
    ['label' => '360 answers each', 'value' => 7.3, 'text' => 'About 7 points'],
    ['label' => '720 answers each', 'value' => 5.2, 'text' => 'About 5 points'],
], [
    'max' => 36, 'ticks' => [0, 5, 10, 15, 20, 25], 'wide' => true,
    'head' => ['Answers counted for each brand', 'Smallest gap worth reporting', 'What that sample is'],
    'cells' => [
        ['30 answers', 'About 25 points', '10 prompts, three runs each'],
        ['60 answers', 'About 18 points', '20 prompts, three runs each'],
        ['90 answers', 'About 15 points', '30 prompts, three runs each'],
        ['180 answers', 'About 10 points', '60 prompts, three runs each'],
        ['360 answers', 'About 7 points', '60 prompts, three runs, two months combined'],
        ['720 answers', 'About 5 points', '60 prompts, three runs, four months combined'],
    ],
    'source' => 'Worked out for this article: 1.96 times the square root of 2 times 0.25 divided by n. Three runs of one prompt are related to each other, so they tell you less than three separate prompts would. Treat every figure here as a minimum.',
]); ?>

<p>With 90 answers, a gap under roughly 15 points is inside the noise. Halving the margin takes four times as many answers, which is why a small prompt set cannot settle a close race however carefully it is run.</p>
<p>These margins are wider than the ones in our measurement guide, and the two agree. That guide gives the margin on one rate: about 10 points at 90 answers. A comparison involves two rates, each with its own error, so the margin on the gap is about 1.4 times larger.</p>
<p>Now read the invented scorecard again, at 90 answers:</p>
<ul class="post-list">
    <li><strong>Brand A and Brand B, named:</strong> 46 against 56 percent. The gap is 10 points and the margin is about 14.5. Treat them as level.</li>
    <li><strong>Brand A and Brand C, named:</strong> 46 against 32 percent. The gap is about 13 points and the margin is about 14. Still inside the noise, though close to the edge. Watch it next month.</li>
    <li><strong>Brand A and Brand D, named:</strong> 46 against 20 percent. The gap is about 26 points and the margin is about 13. This one can be reported.</li>
</ul>

<div class="lx-tool lx-js" data-lx-tool="calc" data-calc="sample" id="gap-calculator">
    <p class="lx-tool-k">Calculator</p>
    <p class="lx-tool-h">Check Whether a Gap Is Larger Than the Noise</p>
    <p>The numbers for Brand A and Brand B from the invented example are filled in. Replace them with your own counts for two brands on the same answers. Nothing you type is stored or sent.</p>
    <div class="lx-calc">
        <?= $in('n', 'Answers counted (the same for both brands)', '90') ?>
        <?= $in('a', 'Answers that named the first brand', '41') ?>
        <?= $in('b', 'Answers that named the second brand', '50') ?>
    </div>
    <ul class="lx-calc-out" aria-live="polite">
        <li><b data-calc-out="ra"></b>First brand's rate</li>
        <li><b data-calc-out="rb"></b>Second brand's rate</li>
        <li><b data-calc-out="gap"></b>Gap between them</li>
        <li><b data-calc-out="moe"></b>Margin of error on the gap</li>
    </ul>
    <p class="lx-calc-verdict" data-calc-verdict aria-live="polite"></p>
    <div class="lx-btns"><button class="lx-btn lx-btn--ghost" type="button" data-calc-reset>Clear the numbers</button></div>
</div>

<p>The calculator works for any of the four scores, as long as both counts come from the same set of answers. It uses the sum shown above, so it shares the same caution: repeated runs are not fully independent, and the true margin is somewhat wider than the one printed.</p>

<h2 id="sources">Find the Sources Behind the Names</h2>
<p>A scorecard says who is named. It does not say why. The closest thing to a why that you can observe is the set of pages an assistant links to when it names a competitor.</p>
<p>Each time an answer names a competitor and shows sources, copy the links into a second sheet, with the brand and the prompt beside each one. After a month, sort the links into five groups.</p>

<?php px_table('Five groups of cited source, and what each suggests', ['Group', 'What it is', 'What to do with it'], [
    ['The competitor\'s own site', 'Service pages, comparison pages, pricing pages, articles', 'Note the page type. If their comparison page is cited and you have none, that is a gap in your own site'],
    ['Directories and lists', 'Supplier directories and "top suppliers" articles', 'Check whether you are listed, and whether the listing is complete and current'],
    ['Review sites', 'Platforms where customers rate suppliers', 'Compare the number and age of reviews, not only the score'],
    ['Forums and communities', 'Threads where practitioners answer each other', 'Read what is said. It shows the wording buyers use and the objections they raise'],
    ['News and trade press', 'Coverage, interviews and contributed articles', 'A list of publications that cover your category and may cover you'],
]); ?>

<?php px_example('Illustration, with invented numbers', static function (): void { ?>
<p>In the made-up benchmark above, the answers that named Brand B showed 60 source links over the month. Sorted into the five groups, they might fall like this.</p>
<?php px_table('60 links cited beside Brand B, sorted by group', ['Group', 'Links', 'Share of the 60'], [
    ['Directories and lists', '22', '37 percent'],
    ['Brand B\'s own site', '14', '23 percent'],
    ['Review sites', '11', '18 percent'],
    ['Forums and communities', '8', '13 percent'],
    ['News and trade press', '5', '8 percent'],
]); ?>
<p>The useful output is not the percentages. It is the list underneath them: the eight or ten specific pages that appear again and again. Those are the places where being present is worth the effort.</p>
<?php }); ?>

<p>Read the result with care. A cited link shows what the assistant displayed beside its answer. It does not prove that the page caused the brand to be named. It is a list of places worth being present, and it is a better starting point than a guess. Our article on <a href="/blog/chatgpt-seo-how-brands-get-recommended/">how brands get recommended in ChatGPT</a> explains why mentions on other sites matter.</p>
<p>One first-party report helps here. Bing Webmaster Tools has an <?= ext_link($s['bing'], 'AI Performance report') ?>, in public preview since February 2026, which counts citations of your own pages in Copilot and Bing's AI answers. In June 2026 Microsoft <?= ext_link($s['bingshare'], 'added Citation Share and a Compare view') ?>. Citation Share is your share of all the citations given for a query the AI used to find sources. Microsoft says it does not reveal competitor domains and does not represent a share of traffic. So it tells you how much of the citing you receive on a topic, and not who receives the rest. The manual sort above fills that gap.</p>

<h2 id="engines">Report Each Engine Separately Before Any Total</h2>
<p>Assistants draw on different indexes and different sources, so they disagree about the same category. A brand can be named often on one and rarely on another. An average across engines hides exactly that.</p>
<p>Keep one scorecard for each engine. Give a combined figure only after the separate ones, and only if every engine was run with the same prompts and the same number of answers.</p>
<p>The disagreement is information. If a competitor leads on one engine alone, look at the sources that engine cites for them. It may be one directory, one review site or one set of pages that the other engines use less. If you trail on every engine, the cause is more likely to be on your own site, starting with whether the crawlers can read it. Our guide to <a href="/blog/ai-crawler-access-problems/">AI crawler access problems</a> covers that check.</p>

<h2 id="one-page">A Report That Fits on One Page</h2>
<p>Leadership asked one question, so the report should answer it on one page. Collect the answers every month, alongside your own tracking, and report the comparison once a quarter on the combined answers. One month on a small sample mostly reports noise.</p>
<ol class="bx-steps">
    <li><strong>The scorecard,</strong> for each engine, with the number of answers printed beside every rate.</li>
    <li><strong>The gaps that are larger than the margin,</strong> and a plain statement that the others are level.</li>
    <li><strong>What changed since last quarter,</strong> including any brand added under the written rule and any change to the prompts.</li>
    <li><strong>What the assistants got wrong about you,</strong> since an inaccurate description is the most direct thing you can fix.</li>
    <li><strong>Three actions,</strong> each tied to a line in the source sort: a listing to complete, a page to write, a publication to approach.</li>
</ol>
<p>Leave out the single combined score, the rank order of brands and any chart without a sample size. Each of them invites a conclusion the data cannot carry. The next question from leadership is usually whether any of this brings visitors, and our article on <a href="/blog/ai-citations-vs-website-traffic/">AI citations vs website traffic</a> covers what can and cannot be shown.</p>

<h2 id="limits">What a Benchmark Cannot Tell You</h2>
<p>State these limits in the report itself. They are easier to accept there than when someone else raises them.</p>
<ul class="post-list">
    <li><strong>It is not market share.</strong> Being named in 46 percent of 90 answers to your prompts says nothing about what share of buyers choose you.</li>
    <li><strong>It is not a ranking.</strong> Assistants do not hold a fixed order of brands, and the order inside one answer changes between runs.</li>
    <li><strong>It does not show why a brand was named.</strong> The source sort gives leads to follow, not causes.</li>
    <li><strong>It describes your prompts.</strong> A different, equally fair set of questions would give different rates. The comparison holds only within the set.</li>
    <li><strong>It does not predict next month.</strong> Assistants change their models and sources without notice. A lead today is an observation, and acting on the source list does not guarantee that an assistant will name you.</li>
</ul>
<p>Within those limits, the benchmark does one useful thing. It replaces "they seem to be everywhere in ChatGPT" with a count, a margin and a short list of places to work on. The wider method sits in our <a href="/guides/complete-guide-to-ai-seo/">complete guide to AI SEO</a>.</p>
<?php },

'closing' => static function (): void { ?>
<h2 id="final-thoughts">Read It Like the Small Survey It Is</h2>
<p>A competitor benchmark in AI search is a small survey, and it should be read like one. The sample size comes first, the gap second and the conclusion last.</p>
<p>Most of the value is in the second sheet. Knowing that a rival is named more often changes nothing by itself. Knowing which directories, reviews and articles sit beside their name gives you a list of work to do.</p>

<?php px_cta_main([
    'h' => 'See Who AI Assistants Name Instead of You',
    'p' => 'Ask for a free AI visibility report. We run buyer prompts across the main assistants and show where you are named, who else is, and what is wrong.',
    'href' => '/ai-seo/#enquire',
    'btn' => 'Get My Free AI Visibility Report',
    'cta' => 'blog-benchmark-report',
    'link' => ['/ai-seo/', 'See AI SEO Services', 'blog-benchmark-service'],
]); ?>

<?php px_author(); ?>
<?php },

];
