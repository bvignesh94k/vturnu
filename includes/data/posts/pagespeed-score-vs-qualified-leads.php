<?php
/**
 * Explainer: what a PageSpeed score measures, what it cannot see, and the
 * order to check speed, conversion and lead quality in.
 * Loaded by templates/blog-post.php through the 'rich' key.
 *
 * Client figures are exactly as published in includes/data/cases-real.php.
 * Google's pages are paraphrased from the page linked in each sentence, with
 * one short quotation. The Deloitte report was read in full, including its
 * lead generation chapter and its limitations page. Sources checked on
 * 13 and 14 November 2026. Nothing here promises a ranking or a lead result.
 */

$s = [
    'psi' => 'https://developers.google.com/speed/docs/insights/v5/about',
    'labfield' => 'https://web.dev/articles/lab-and-field-data-differences',
    'crux' => 'https://developer.chrome.com/docs/crux/methodology',
    'vitals' => 'https://web.dev/articles/vitals',
    'scoring' => 'https://developer.chrome.com/docs/lighthouse/performance/performance-scoring',
    'variability' => 'https://github.com/GoogleChrome/lighthouse/blob/main/docs/variability.md',
    'pageexp' => 'https://developers.google.com/search/docs/appearance/page-experience',
    'deloitte' => 'https://www.deloitte.com/ie/en/services/consulting/research/milliseconds-make-millions.html',
    'vodafone' => 'https://web.dev/case-studies/vodafone',
];

return [

'body' => static function () use ($s): void { ?>
<p class="bx-open">A page can load in under a second and still lose every good lead that reaches it.</p>
<p>That sounds like a contradiction, because speed scores are usually presented as a stand-in for results. A 95 means the website is healthy. A 40 means it is costing you business. The score is real and worth improving. It is also a test of one thing, run in a lab, on one page.</p>
<p>On three of our published projects, the speed score and the enquiry problem turned out to be unrelated. One site had a form that silently dropped two fields. One was sending fake leads to its sales team. One had articles that crawlers could not see. No speed test showed any of it.</p>
<p>This article explains what the PageSpeed score measures, what it cannot see, and the order in which to check speed, conversion and lead quality.</p>

<?php px_answer('pagespeed-score-vs-qualified-leads'); ?>

<?php px_toc([
    'four-numbers' => 'Four numbers that get mistaken for one',
    'what-the-score-tests' => 'What the PageSpeed score actually tests',
    'what-the-score-missed' => 'Three things the score could not see',
    'what-speed-changes' => 'What speed changes, and what it does not',
    'order-of-checks' => 'The order to check things in',
    'when-speed-pays' => 'When speed work is worth paying for',
    'faq-title' => 'FAQs',
]); ?>

<h2 id="four-numbers">Four Numbers That Get Mistaken for One</h2>
<p>When someone says a website "performs well", they could mean any of four measurements. Each comes from a different place and answers a different question.</p>

<?php px_table('Four measurements of a web page, from furthest to closest to revenue', ['Number', 'What it measures', 'Where it comes from', 'What it cannot tell you'], [
    ['Lab score', 'How fast one page loads in a simulated test, as a number from 0 to 100', 'A Lighthouse run inside PageSpeed Insights', 'What real visitors experienced, or whether anyone enquired'],
    ['Field data', 'Loading, responsiveness and visual stability for real Chrome users', 'The Chrome UX Report, over the previous 28 days', 'Anything about pages with too few visits to be included'],
    ['Conversion rate', 'The share of visits that end in a form, a call or a sign-up', 'Your analytics', 'Whether those enquiries came from real buyers'],
    ['Qualified lead rate', 'The share of enquiries that sales accepts as worth a conversation', 'Your CRM or sales inbox', 'Why the rest were turned away'],
]); ?>

<p>The lab score is the furthest from revenue and the easiest to get, which is why it is quoted most. The qualified lead rate is the closest and the hardest to get, which is why it is often missing from the report.</p>

<h2 id="what-the-score-tests">What the PageSpeed Score Actually Tests</h2>
<p>PageSpeed Insights shows two kinds of data on one screen, and they are easy to confuse. Google's <?= ext_link($s['psi'], 'description of the tool') ?> explains that the performance score comes from Lighthouse, which loads the page once in a simulated environment. For the mobile test, that means an emulated mid-range phone on a mobile network. A score of 90 or more is rated good, 50 to 89 needs improvement, and below 50 is poor.</p>
<p>Three details about that score matter to anyone using it to judge a website.</p>
<ol class="bx-steps">
    <li><strong>It changes from one run to the next.</strong> Lighthouse's own <?= ext_link($s['variability'], 'notes on score variability') ?> list seven sources of variation and recommend running several tests and taking the median. They say the median of five runs is twice as stable as a single run. One test, on one afternoon, is a weak basis for a decision.</li>
    <li><strong>It depends on which test you read.</strong> On 12 October 2026, the FlowEazy homepage scored 99 on mobile and 77 on desktop in PageSpeed Insights. Same page, same day, two scores. "The score" is never one number.</li>
    <li><strong>It weights what a lab can measure.</strong> In the <?= ext_link($s['scoring'], 'scoring Google documents for Lighthouse 10') ?>, Total Blocking Time is 30 percent of the score, Largest Contentful Paint 25 percent and Cumulative Layout Shift 25 percent, with First Contentful Paint and Speed Index making up the rest. Interaction to Next Paint, the Core Web Vital for responsiveness, is not in the lab score at all, because a lab test has no real person clicking.</li>
</ol>
<p>The second kind of data describes people. Field data comes from the <?= ext_link($s['crux'], 'Chrome UX Report') ?>, which collects real visits by Chrome users. <?= ext_link($s['psi'], 'PageSpeed Insights') ?> shows the previous 28 days of that data, at the 75th percentile. A page passes the Core Web Vitals assessment when all three metrics are good for at least 75 percent of visits. The <?= ext_link($s['vitals'], 'thresholds Google publishes') ?> are 2.5 seconds or less for Largest Contentful Paint, 200 milliseconds or less for Interaction to Next Paint, and 0.1 or less for Cumulative Layout Shift. Our <a href="/blog/core-web-vitals-guide-for-business-owners/">Core Web Vitals guide for business owners</a> explains each one in plain words.</p>
<p>Google's explanation of <?= ext_link($s['labfield'], 'why lab and field data differ') ?> says that where both exist, field data should come first, because it reflects what real users experienced. The PageSpeed Insights documentation makes the same point from the other side: a good lab score does not necessarily mean real users have a good experience.</p>
<p><strong>There is a catch for B2B websites.</strong> A page appears in the Chrome UX Report only if it is public and popular enough, and Google does not publish the minimum. Many B2B pages, with a few hundred visits a month, show no field data. PageSpeed Insights then falls back to data for the whole site. If the site is also below the threshold, the lab score is the only number on the screen. That is how a simulated test of one page ends up being read as a verdict on a business.</p>

<h2 id="what-the-score-missed">Three Things the Score Could Not See</h2>
<p>These examples come from our published case studies. In each one the speed work was real and worth doing. In each one, a separate fault was costing enquiries, and a perfect score would not have shown it.</p>

<?php px_chart('lab-scores', 'Mobile lab scores before and after, on three pages we worked on', 'Lighthouse performance score, 0 to 100. Lab scores vary between runs.', [
    ['label' => 'FlowEazy homepage', 'from' => 26, 'to' => 96, 'text' => '26 to 96 or more'],
    ['label' => 'FlowEazy service page', 'from' => 62, 'to' => 92, 'text' => '62 to 92 or more'],
    ['label' => 'Sai Impression', 'from' => 55, 'to' => 90, 'text' => '55 to the 90s'],
], [
    'mode' => 'range', 'wide' => true, 'max' => 140, 'ticks' => [0, 25, 50, 75, 100],
    'legend' => [['from', 'Before our work'], ['to', 'After, at the lowest published figure']],
    'head' => ['Page', 'Before', 'After', 'What the score did not show'],
    'cells' => [
        ['FlowEazy homepage', '26', '96 or more', 'The enquiry form was dropping two fields'],
        ['FlowEazy service page', '62', '92 to 98 across ten page types', 'The same form fault'],
        ['Sai Impression', '55', 'The 90s', 'Bot leads with fake numbers were reaching sales'],
    ],
    'source' => 'Source: VTurnU case studies for <a href="/case-studies/floweazy-odoo-partner-website/">FlowEazy</a> and <a href="/case-studies/sai-impression-label-manufacturer/">Sai Impression</a>. Lighthouse lab runs on the final builds, October 2026.',
]); ?>

<h3>A form that dropped half of every enquiry</h3>
<p>FlowEazy sells Odoo ERP services. Its homepage scored 26 for mobile performance in the lab before our work and 96 or more after it. During the same project we found that the enquiry form was silently dropping the Service and Requirements fields. Sales was receiving leads with half the information missing. The page could have scored 100 and those leads would still have arrived unusable.</p>

<h3>Leads that were not people</h3>
<p>Sai Impression manufactures labels. Its mobile lab score moved from 55 to the 90s. Separately, bot leads with fake phone numbers were reaching the sales team, and staff were calling people who did not exist. The fix was a filter that holds suspect leads in quarantine, so a real enquiry is never deleted by mistake. Our guide on <a href="/blog/how-to-stop-junk-leads-on-your-website/">how to stop junk leads</a> describes the method. No speed tool counts fake enquiries.</p>

<h3>Content that was responsive and invisible</h3>
<p>Cafy is a UK job platform built with a JavaScript framework. In real-user data from 16 October 2026, its desktop Interaction to Next Paint was 106 milliseconds, comfortably inside Google's threshold. At the same time, its blog articles stayed invisible until JavaScript ran. The lab did flag a symptom, an 8.3 second Largest Contentful Paint. It could not flag the consequence: a crawler that does not run JavaScript received a page with no article in it. The <a href="/case-studies/cafy-careers-uk-job-platform/">Cafy case study</a> covers the fix, and our guide to <a href="/blog/ai-crawler-access-problems/">AI crawler access problems</a> shows how to test your own pages for the same fault.</p>

<p class="bx-note"><strong>What these examples do not show.</strong> We do not have before and after lead quality figures for these projects, so we are not claiming that speed made no difference to them. The claim is narrower. In each case, the fault that was costing enquiries sat outside anything a speed test measures.</p>

<?php px_cta_mid([
    'label' => 'Free SEO audit tool',
    'art' => 'scan',
    'h' => 'Check More Than Speed on Your Homepage',
    'p' => 'Enter your website and get twenty checks in seconds: indexing, page tags, speed signals, structured data and whether AI crawlers can read the page. No sign-up.',
    'href' => '/free-seo-audit/',
    'btn' => 'Run the Free Audit',
    'cta' => 'blog-pagespeed-audit',
    'note' => 'Want the fixes too? The <a href="/ebooks/website-speed-handbook/">Website Speed Handbook</a> is free to read online.',
]); ?>

<h2 id="what-speed-changes">What Speed Changes, and What It Does Not</h2>
<p>Speed matters. The evidence that faster pages keep more visitors is reasonable. It is also narrower than the way it is usually quoted.</p>
<p>The study cited most often is <?= ext_link($s['deloitte'], 'Milliseconds Make Millions') ?>, prepared by Deloitte for Google in 2020. It followed 37 brand websites for four weeks in late 2019 and compared natural changes in mobile load time with what visitors did. For retail sites, a 0.1 second improvement went with 8.4 percent more conversions. That is the figure that gets repeated.</p>
<p>The lead generation chapter of the same report is less tidy, and more useful to a B2B reader. With a 0.1 second improvement, progression from the first step of a form to submitting it rose by 21.6 percent. The same chapter reports that conversion rates on lead generation sites went down as speed improved, by almost 2 percent on mobile. The report's own notes add that the results are correlations from 37 sites, that speed was measured with lab data, and that Deloitte did not audit the figures. A conversion in that study is a completed form. Nobody checked whether the people who completed it were buyers.</p>
<p>A cleaner test is the <?= ext_link($s['vodafone'], 'Vodafone case study') ?> that Google published in 2021. Vodafone split paid traffic evenly between the original version of one landing page and a faster one. The version with a 31 percent better Largest Contentful Paint produced 8 percent more sales and a 15 percent better lead to visit rate. A controlled test is stronger evidence than a correlation. It is still one consumer page, and "lead" there carries no measure of quality.</p>
<p>Put together, a fair reading is this:</p>
<ul class="post-list">
    <li><strong>Speed removes a reason to leave.</strong> Visitors on a phone and a mobile connection feel it most.</li>
    <li><strong>Speed does not add a reason to enquire.</strong> The offer, the proof and the form do that.</li>
    <li><strong>Speed says nothing about who enquires.</strong> That depends on which searches and ads brought people to the page.</li>
    <li><strong>For Google, it is one input among many.</strong> Google's <?= ext_link($s['pageexp'], 'guidance on page experience') ?> says Core Web Vitals are used by its ranking systems. It also says good results in those reports "doesn't guarantee that your pages will rank at the top of Google Search results", and that it tries to show the most relevant content even where page experience is weaker.</li>
</ul>

<h2 id="order-of-checks">The Order to Check Things In</h2>
<p>When enquiries are low or poor, speed is one of five things to check, and it is second on the list. Each check is quicker than the one after it, and a failure early on makes the later numbers meaningless. There is no point measuring conversion on a page that search engines cannot read.</p>

<?php px_flow('check-order', 'Five checks, in order, for a page that is not producing qualified leads', [
    ['Can people and crawlers reach the page?', 'It returns a normal response, it is indexed, and its main text is in the HTML without waiting for scripts.', 'Search Console and a raw HTML check'],
    ['Is it fast for real visitors?', 'Read the field data first. If there is none, run the lab test five times and use the middle score.', 'PageSpeed Insights'],
    ['Does the page say the right thing to the right visitor?', 'The first screen names who it is for, what they get and what to do next.', 'Read it as a buyer would'],
    ['Does the form work, and does the enquiry arrive whole?', 'Send a test enquiry and confirm that every field reaches the inbox and the CRM.', 'A test submission'],
    ['Are the leads real and qualified?', 'Count valid enquiries, and the share sales accepts, for each landing page.', 'CRM or sales inbox'],
], [
    'sub' => 'Stop at the first check that fails and fix it before moving on.',
    'cap' => 'Speed is the second check. Three of the five have nothing to do with load time.',
]); ?>

<p>The third check is where most of the gain usually sits, and our <a href="/blog/landing-page-optimization-guide/">landing page optimization guide</a> covers it. For the fifth, see <a href="/blog/measure-qualified-leads-from-organic-search/">how to measure qualified leads from organic search</a>, which gives the stages, the fields and the formulas.</p>

<h2 id="when-speed-pays">When Speed Work Is Worth Paying For</h2>
<p>Speed work is engineering time, and it competes with everything else on the list. These are the cases where it earns its place first, and the cases where it should wait.</p>

<?php lx_split('Do it first when', '<ul>'
    . '<li>Field data shows the pages that bring enquiries failing Core Web Vitals.</li>'
    . '<li>You pay for the clicks. A slow landing page wastes money on every paid visit.</li>'
    . '<li>Tags and widgets have piled up. On FlowEazy, loading Tag Manager after the first interaction did most of the work.</li>'
    . '</ul>', 'Let it wait when', '<ul>'
    . '<li>Field data already passes and the lab score is in the 80s or 90s. The last ten points rarely change what a visitor feels.</li>'
    . '<li>The form loses fields, the message is unclear or the leads are junk. Those are losing enquiries today.</li>'
    . '</ul>'); ?>

<p>Google says much the same in its page experience guidance: there is more to a good page than Core Web Vitals, and chasing a perfect score only for search may not be the best use of your time.</p>
<?php },

'closing' => static function (): void { ?>
<h2 id="final-thoughts">A High Score Is the Start of the Review</h2>
<p>A speed score answers one question well: did this page load quickly in a test? It is worth getting right. It is also the easiest of the five checks to put on a slide, which is why it gets more attention than it has earned.</p>
<p>The enquiry is decided later, by what the page says, whether the form delivers and who was sent to the page in the first place. Check them in order, and treat a high score as the start of the review.</p>

<?php px_cta_main([
    'h' => 'Fast Website, Weak Enquiries?',
    'p' => 'We review the page, the form and the leads it sends you, and tell you which of the five checks is failing.',
    'href' => '/lead-gen-web-design/#enquire',
    'btn' => 'Get My Free Website Review',
    'cta' => 'blog-pagespeed-review',
    'link' => ['/lead-gen-web-design/', 'See Lead Generation Web Design', 'blog-pagespeed-service'],
]); ?>

<?php px_author(); ?>
<?php },

];
