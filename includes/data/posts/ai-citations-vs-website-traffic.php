<?php
/**
 * Explainer: six measurements between "an AI answer mentioned us" and
 * "revenue", what each one proves, and how to report them without overstating.
 * Loaded by templates/blog-post.php through the 'rich' key.
 *
 * "We" is used only for the published FirstChoice figures and for how this
 * site's own enquiry system records sources (includes/crm.php). No AI referral
 * visit or enquiry figures are on file for any client, so none are given.
 * Tool behaviour is reported as each vendor documents it, from the page linked
 * in the sentence, read on 13 November 2026. Study figures carry their sample,
 * period and sponsor. The channel pattern in the code block is our own example,
 * not Google's. The five-line report uses invented numbers and says so.
 * Nothing here promises a citation, a visit or a lead.
 */

$s = [
    'ga4channels' => 'https://support.google.com/analytics/answer/9756891',
    'ga4notes' => 'https://support.google.com/analytics/answer/9164320',
    'ga4custom' => 'https://support.google.com/analytics/answer/13051316',
    'gai' => 'https://developers.google.com/search/docs/appearance/ai-features',
    'gsccount' => 'https://support.google.com/webmasters/answer/7042828',
    'gscgen' => 'https://developers.google.com/search/blog/2026/06/gen-ai-performance-reports',
    'bing' => 'https://blogs.bing.com/webmaster/February-2026/Introducing-AI-Performance-in-Bing-Webmaster-Tools-Public-Preview',
    'openai' => 'https://web.archive.org/web/20261004224023/https://help.openai.com/en/articles/12627856-publishers-and-developers-faq',
    'lapse' => 'https://www.seroundtable.com/utm-parameters-missing-chatgpt-42299.html',
    'ahrefs' => 'https://ahrefs.com/benchmarks/ai-vs-search',
    'kaiser' => 'https://doi.org/10.1287/mksc.2025.0489',
    'clarity' => 'https://clarity.microsoft.com/blog/ai-traffic-converts-at-3x-the-rate-of-other-channels-study/',
    'natzir' => 'https://natzir.com/en/analytics/ai-traffic-measurability/',
];

return [

'body' => static function () use ($s): void { ?>
<p class="bx-open">One website, one quarter, two numbers. The AI visibility report shows thousands of citations in AI answers. The analytics report shows a handful of visits from AI assistants.</p>
<p>Both numbers can be true. They are not two readings of the same thing. A citation counts an answer that pointed to your page. A visit counts a person who clicked. Most readers of an AI answer never click, and some of those who do arrive with no trace of where they came from.</p>
<p>This article sets out six measurements between "an AI answer mentioned us" and "revenue". For each one it says where the number comes from, how far to trust it and the wrong conclusion it invites. Then it covers the tracking that is possible today and a five-line report you can defend in front of a finance lead.</p>

<?php px_answer('ai-citations-vs-website-traffic'); ?>

<?php px_toc([
    'six-measurements' => 'Six measurements, from mention to revenue',
    'why-they-diverge' => 'Why citations and visits diverge',
    'what-each-tool-shows' => 'What each tool can show',
    'ga4-setup' => 'Setting up AI referrals in GA4',
    'visibility-to-enquiries' => 'From visibility to enquiries, honestly',
    'four-conclusions' => 'Four conclusions the data does not support',
    'five-line-report' => 'A report in five lines',
    'faq-title' => 'FAQs',
]); ?>

<h2 id="six-measurements">Six Measurements, From Mention to Revenue</h2>
<p>Think of AI search measurement as a ladder with six rungs. Each rung is a different event, recorded by a different system. The lower rungs are about answers. The upper rungs are about buyers. The mistake is to read a number from a low rung as if it came from a high one.</p>

<?php px_flow('ai-ladder', 'The ladder: six measurements between an AI answer and revenue', [
    ['Mentioned in an answer', 'An assistant names your brand, with or without a link. You find out by running buyer prompts yourself.', 'Sampled', 'b'],
    ['Cited with a link', 'An answer shows one of your pages as a source. Bing Webmaster Tools and Search Console report this for their own products.', 'Counted, in part', 'a'],
    ['Referral visit', 'A person clicks through from an assistant and analytics can tell where they came from.', 'Counted, in part', 'a'],
    ['Enquiry', 'A visitor whose first recorded source was an assistant sends a form or books a call.', 'Counted, in part', 'a'],
    ['Sales opportunity', 'Sales accepts the enquiry and opens an opportunity. AI\'s part in it is one touch among several.', 'Inferred', 'c'],
    ['Revenue', 'The opportunity closes. No report can say how much of the sale an AI answer caused.', 'Inferred', 'c'],
], [
    'sub' => 'The tag shows how the link to AI search is established at each step.',
    'key' => [['a', 'Counted by a system'], ['b', 'Sampled by you'], ['c', 'Inferred']],
    'cap' => 'Each step up is a smaller number and a weaker link to the AI answer. Report each rung by its own name.',
]); ?>

<?php px_table('The six measurements, their sources and the mistake each one invites', ['Measurement', 'Where the number comes from', 'How far to trust it', 'The mistake it invites'], [
    ['1. Mentioned', 'Your own prompt sampling, or a tracking tool that does the same', 'A sample. It moves between runs, so quote it with the number of answers', 'Reading share of answer as market share'],
    ['2. Cited', 'Bing Webmaster Tools AI Performance; Search Console Generative AI report', 'Counted, for one vendor\'s products only', 'Calling citations traffic'],
    ['3. Referral visit', 'Analytics: the AI Assistant channel in Google Analytics', 'Counted, and incomplete. Visits without a referrer show as direct', 'Concluding that few visits means no influence'],
    ['4. Enquiry', 'The first source stored on the enquiry in your CRM', 'Counted, where the source survived', 'Comparing a rate from ten enquiries with a rate from a thousand'],
    ['5. Opportunity', 'CRM stages, joined to the enquiry\'s first source', 'The stage is counted. AI\'s share of the credit is inferred', 'Giving the whole deal to the first touch'],
    ['6. Revenue', 'CRM or finance system', 'The amount is counted. The cause is inferred', 'Claiming that citations produced the revenue'],
]); ?>

<p>If you have not started on the first rung, our guide to <a href="/blog/how-to-measure-ai-search-visibility/">measuring AI search visibility</a> gives the sampling routine. This article is about reading the rungs together.</p>

<h2 id="why-they-diverge">Why Citations and Visits Diverge</h2>
<p>A wide gap between rung two and rung three is normal. There are four reasons, and none of them is a tracking fault you can fully fix.</p>
<ol class="bx-steps">
    <li><strong>The answer often satisfies the reader.</strong> An AI answer is written so that the person does not need to open the sources. Your page helped to produce the answer and nobody visited it.</li>
    <li><strong>Some visits carry no referrer.</strong> No assistant vendor documents this fully. <?= ext_link($s['natzir'], 'One analyst\'s review of his own server logs') ?>, updated during 2026, found that the assistants' websites pass a referrer and that their mobile apps mostly strip it. Analytics files a visit without a referrer under direct traffic. The tags that assistants add to links are not dependable either, as the tracking section below explains.</li>
    <li><strong>Some answers name a brand without a link.</strong> That is a mention with nothing to click.</li>
    <li><strong>Buyers come back later by another route.</strong> A person reads an answer on Monday and searches for the company by name on Thursday. Analytics records a branded organic search visit.</li>
</ol>
<p>The size of the gap is visible in outside data. <?= ext_link($s['ahrefs'], 'Ahrefs\' benchmark') ?> follows 118,671 websites that use its analytics product. For October 2026 it reports AI assistants as 0.46 percent of visits and search as 27.55 percent. This is vendor data from one product's customers, so treat it as a rough scale, not a target.</p>

<h3>From our published work</h3>
<p>FirstChoice Used Auto Parts shows how large the citation number can be next to ordinary search figures. Bing Webmaster Tools AI Performance recorded 10.7K citations in Copilot and Bing AI answers in September 2026. Search Console showed 2.2K Google clicks in the three months from 30 July to 29 October 2026. The <a href="/case-studies/firstchoice-used-auto-parts/">FirstChoice case study</a> has both figures and their sources.</p>
<p class="bx-note"><strong>What that pair does not show.</strong> The two figures come from different engines and different periods, so they cannot be divided into a rate. We have not published AI referral visits for the site, so this article does not give a citations-to-visits figure for it.</p>

<h2 id="what-each-tool-shows">What Each Tool Can Show</h2>
<p>Four sources cover rungs two and three. Each counts something specific, and each has a documented limit.</p>

<h3>Bing Webmaster Tools counts citations in Microsoft's products</h3>
<p>Microsoft <?= ext_link($s['bing'], 'launched the AI Performance report') ?> as a public preview on 10 February 2026. It covers Microsoft Copilot, AI summaries in Bing and some partner integrations. It reports total citations, cited pages and a sample of the grounding queries, which are the phrases the AI used to retrieve the content. Microsoft says the numbers do not indicate placement within an answer, ranking or authority. The announcement does not mention clicks or traffic at all.</p>

<h3>Search Console counts impressions in Google's AI features</h3>
<p>Google says sites that appear in AI Overviews and AI Mode <?= ext_link($s['gai'], 'are included in overall search traffic') ?> in Search Console, under the Web search type. Its <?= ext_link($s['gsccount'], 'counting rules') ?> say a click on an external link in an AI Overview counts as a click, and that every link in an Overview shares the Overview's position.</p>
<p>In June 2026 Google <?= ext_link($s['gscgen'], 'announced a Generative AI performance report') ?>. It shows impressions only, by page, country, device and date, with AI Overviews and AI Mode combined. It has no clicks, no queries and no position. The same impressions are already inside the main Performance report, so the two must not be added together.</p>

<h3>Analytics counts visits that say where they came from</h3>
<p>Google Analytics can count a visit from an assistant when the visit carries a referrer or a tag. The next section covers the setup. What it cannot count is a visit that arrives with neither.</p>

<h3>Server logs count machines</h3>
<p>Logs show requests from AI crawlers and from fetchers that load a page because someone asked about it. They prove that a system read your page. They are not visits by people and should not be added to a traffic figure. Our guide to <a href="/blog/ai-crawler-access-problems/">AI crawler access problems</a> shows how to read them.</p>

<?php px_cta_mid([
    'label' => 'Free AI visibility report',
    'art' => 'chat',
    'h' => 'See What AI Assistants Say About Your Company Today',
    'p' => 'Ask for a free AI visibility report. We run buyer prompts across the main assistants and show where you are named, cited and described wrongly.',
    'href' => '/ai-seo/#enquire',
    'btn' => 'Get My Free AI Visibility Report',
    'cta' => 'blog-citations-report',
    'note' => 'The report covers the first two rungs of the ladder. It does not estimate traffic or leads.',
]); ?>

<h2 id="ga4-setup">Setting Up AI Referrals in GA4</h2>
<p>Until 2026 this needed a custom rule. It now starts with a built-in channel. The steps below follow Google's documentation as read on 13 November 2026.</p>
<ol class="bx-steps">
    <li><strong>Find the AI Assistant channel.</strong> Google Analytics added it to the default channel group on 13 May 2026, according to its <?= ext_link($s['ga4notes'], 'release notes') ?>. The <?= ext_link($s['ga4channels'], 'channel definitions') ?> describe it as users arriving from sources such as ChatGPT, Gemini, Deepseek, Copilot or Grok. The rule is that the medium exactly matches <code>ai-assistant</code>, which Analytics sets when the referrer matches its list of assistants. Open the Traffic acquisition report and look for the channel by name.</li>
    <li><strong>Know what it leaves out.</strong> The same page says the channel excludes Google's AI Overviews and AI Mode. Those clicks are counted in Organic Search, and the definitions give them no channel of their own.</li>
    <li><strong>Use Source Group to see which assistant.</strong> The release notes for 11 June 2026 describe a Source Group field that groups sources such as ChatGPT and Perplexity, and say it is filled in for past data.</li>
    <li><strong>Add a custom channel group only if you need one.</strong> You may want your own list of assistants, or reports for dates before the built-in channel. Google's page on <?= ext_link($s['ga4custom'], 'custom channel groups') ?> says they can be applied to reports retroactively. It also says order matters: traffic goes to the first channel it matches, so an AI channel belongs above Referral. Standard properties allow two custom groups.</li>
</ol>
<p>For the optional custom channel, the condition is "Source matches regex". The pattern below is an example we wrote from the referring sites our own enquiry system recognises. It is not Google's pattern. Test it against the sources in your own property before you rely on it.</p>

<?php lx_code(<<<'TXT'
chatgpt\.com|chat\.openai\.com|perplexity\.ai|gemini\.google\.com|copilot\.microsoft\.com|claude\.ai|deepseek\.com|grok\.com|meta\.ai|you\.com
TXT, 'Source matches regex'); ?>

<p><strong>What will still be missing.</strong> Three gaps remain after any setup.</p>
<ul class="post-list">
    <li><strong>Visits with no referrer.</strong> Clicks from mobile apps often arrive as direct traffic.</li>
    <li><strong>Tags that come and go.</strong> An <?= ext_link($s['openai'], 'archived OpenAI help page') ?> from October 2026 says ChatGPT adds <code>utm_source=chatgpt.com</code> to links from its search results. That page is no longer live. Search Engine Roundtable <?= ext_link($s['lapse'], 'reported that the tag went missing') ?> for about a week in October 2026, and OpenAI made no public statement. Treat the tag as helpful, not dependable.</li>
    <li><strong>Google's own AI features.</strong> They sit inside Organic Search and stay there.</li>
</ul>

<h3>How our own enquiry system handles it</h3>
<p>The enquiry system on this website stores the first and the latest source of every enquiry. It reads campaign tags first and the referring site second. Visits from ChatGPT, Perplexity, Gemini, Copilot, Claude, DeepSeek and a few smaller assistants are grouped as "AI assistants", separate from organic search. An enquiry can be traced to an assistant when the visit carried a referrer or a tag. When it carried neither, the enquiry is recorded as direct, and we do not guess.</p>

<h2 id="visibility-to-enquiries">From Visibility to Enquiries, Honestly</h2>
<p>Nothing joins a citation to a named enquiry. What you can do is collect three signals that would be expected to move if AI visibility were reaching buyers. Together they support a connection. They do not prove one.</p>
<ol class="bx-steps">
    <li><strong>The branded search trend.</strong> If more people meet your name in AI answers, more of them may search for it. Watch branded clicks and impressions in Search Console over several months. Branded search also rises for other reasons, such as advertising, events and press.</li>
    <li><strong>What buyers tell you.</strong> Add "How did you hear about us?" to the enquiry form or the first sales call, with "an AI assistant" as an answer. People forget and simplify, so read the answers as a direction. Our article on <a href="/blog/marketing-attribution-for-long-sales-cycles/">attribution for long sales cycles</a> covers self-reported attribution.</li>
    <li><strong>Enquiries whose first source was an assistant.</strong> This is the only one of the three that is a count. It is a floor, because it misses every buyer who arrived without a referrer.</li>
</ol>
<p>None of these shows that an AI answer caused a sale. A buyer who came from an assistant may already have known the company. A buyer who never clicked may have been persuaded by the answer. Say so in the report.</p>

<h2 id="four-conclusions">Four Conclusions the Data Does Not Support</h2>
<ul class="post-list">
    <li><strong>"Citations are traffic."</strong> A citation is an answer that used your page. Microsoft's report counts citations and does not mention clicks. Google's report counts impressions and has no clicks.</li>
    <li><strong>"No AI visits means no AI influence."</strong> Visits are the part that analytics can see. Answers read without a click, and visits that arrive as direct, are outside it.</li>
    <li><strong>"AI visits convert better."</strong> The studies disagree. A peer-reviewed paper by <?= ext_link($s['kaiser'], 'Kaiser and Schulze in Marketing Science') ?> covers 973 e-commerce sites from August 2024 to July 2025. As its authors summarise it, referrals from AI models were under 0.2 percent of visits and converted below every channel except paid social. We have read the authors' summary, not the full paper. Microsoft's <?= ext_link($s['clarity'], 'Clarity team') ?> published the opposite in November 2025: on more than 1,200 publisher sites, visits from AI signed up at 1.66 percent against 0.15 percent from search. That is vendor research on a different kind of site, with sign-ups as the conversion. Neither study covers B2B enquiries. Measure your own rate, and give its sample size.</li>
    <li><strong>"Our share of answer is our market share."</strong> Share of answer describes your set of prompts. It says how often you were named in the answers you counted, and nothing about who buys.</li>
</ul>

<h2 id="five-line-report">A Report in Five Lines</h2>
<p>Leadership does not need the ladder. It needs one line per rung that has data, each with its source, and one action. Keep the lines in the same order every month.</p>

<?php px_example('Illustration, with invented numbers', static function (): void { ?>
<p>These figures are made up to show the format. They are not from a client and they are not a benchmark.</p>
<?php px_table('A monthly AI search report in five lines', ['Line', 'This month', 'Source and limit'], [
    ['1. Share of answer', '38 percent of 90 answers named us', 'Our own prompt sample on one assistant. A sample, not a market share'],
    ['2. Citations', '1,240 in Copilot and Bing AI answers', 'Bing Webmaster Tools. Microsoft products only'],
    ['3. AI referral visits', '86 sessions', 'Analytics, AI Assistant channel. Excludes visits with no referrer'],
    ['4. Enquiries with an AI first source', '3 of 41 valid enquiries', 'CRM first source. A floor, not a total'],
    ['5. Branded search', 'Clicks up 9 percent on the three-month average', 'Search Console. Other causes are possible'],
    ['Action', 'Correct the two service pages that assistants describe wrongly', ''],
]); ?>
<p>Read the lines together and the picture is modest and usable. The company is being named and cited. A small number of buyers arrive in a way that can be counted. Nothing in the five lines claims a return on investment, because the data cannot support one yet.</p>
<?php }); ?>

<p>Two habits keep the report trusted. Never add the lines together, and never change a line's definition without saying so. When leadership asks how you compare with competitors, that is a separate exercise with its own rules about fairness and sample size. See <a href="/blog/ai-search-competitor-benchmark/">how to benchmark competitors in AI search</a>. For the upper rungs, <a href="/blog/measure-qualified-leads-from-organic-search/">how to measure qualified leads from organic search</a> gives the stages and fields that also apply to enquiries from assistants.</p>
<?php },

'closing' => static function (): void { ?>
<h2 id="final-thoughts">Report Each Number Under Its Own Name</h2>
<p>Citations and visits answer different questions. Citations say that AI systems read your pages and use them. Visits say that some people clicked and could be counted. Enquiries with an AI first source say that a few of those people were buyers.</p>
<p>Report each one under its own name, with its source and its limit. A small number that is described accurately will survive a finance review. A large number that is described as something else will not. If AI answers matter to your buyers, our <a href="/google-ai-overviews-seo/">Google AI Overviews SEO</a> page explains the work that goes into being cited in the first place.</p>

<?php px_cta_main([
    'h' => 'Want AI Search Reported Without the Guesswork?',
    'p' => 'Tell us what you track today. We will show you which rungs of the ladder you can measure now and what each number can support.',
    'btn' => 'Talk to a Strategist',
    'cta' => 'blog-citations-contact',
    'link' => ['/ai-seo/', 'See AI SEO Services', 'blog-citations-service'],
]); ?>

<?php px_author(); ?>
<?php },

];
