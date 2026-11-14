<?php
/**
 * Framework: measuring qualified leads from organic search. Six stages, nine
 * fields, two valid joins between four systems and six formulas, with a
 * calculator (data-lx-tool="calc" in assets/js/listicle.js).
 * Loaded by templates/blog-post.php through the 'rich' key.
 *
 * The description of our own enquiry system matches the code: the first and
 * latest touch are kept in the visitor's browser by assets/js/main.js, and
 * includes/crm.php sorts each touch into a channel and a channel group.
 * Every number in the worked example and the cohort table is invented and
 * labelled as such. Vendor pages were read on 13 November 2026. No benchmark
 * figures are quoted, because none was found with a published method.
 */

$s = [
    'keyevents' => 'https://support.google.com/analytics/answer/9267568',
    'leadevents' => 'https://support.google.com/analytics/answer/9267735',
    'channels' => 'https://support.google.com/analytics/answer/9756891',
    'gsc' => 'https://support.google.com/analytics/answer/10737381',
    'attribution' => 'https://support.google.com/analytics/answer/10597962',
    'hubspot' => 'https://knowledge.hubspot.com/records/use-lifecycle-stages',
    'sal' => 'https://web.archive.org/web/20240305100219/https://www.forrester.com/blogs/sales-accepted-leads-the-most-important-and-most-overlooked-step-in-the-demand-creation-process/',
];

$in = static fn (string $name, string $label, string $value): string =>
    '<label><span>' . e($label) . '</span><input type="number" inputmode="decimal" min="0" step="any" data-calc-in="' . e($name) . '" value="' . e($value) . '"></label>';

return [

'body' => static function () use ($s, $in): void { ?>
<p class="bx-open">Ask three people how many leads organic search produced last quarter and you will get three numbers. Marketing counts form fills. Sales counts the conversations worth having. Finance counts customers.</p>
<p>All three are right about what they counted. The trouble is that nothing connects them, and organic search suffers most from the gap. A buyer reads an article in March, comes back by typing your address in April and signs in September. In most reports, search gets no credit for that customer, and the article looks like a cost.</p>
<p>This article gives you a working ledger for organic leads: six stages with one owner each, nine fields to store on every enquiry, the two joins between your systems that are valid, and six formulas. By the end you can report qualified leads, cost per qualified lead and pipeline from organic search, and say where each number came from.</p>

<?php px_answer('measure-qualified-leads-from-organic-search'); ?>

<?php px_toc([
    'six-stages' => 'Six stages, one owner each',
    'where-organic-gets-lost' => 'Where organic search gets lost',
    'nine-fields' => 'Nine fields to store on every enquiry',
    'joining-four-systems' => 'Joining four systems that share no ID',
    'six-formulas' => 'Six formulas, with a worked example',
    'report-the-lag' => 'Reporting the lag honestly',
    'leave-out' => 'What to leave out of the report',
    'faq-title' => 'FAQs',
]); ?>

<h2 id="six-stages">Six Stages, One Owner Each</h2>
<p>A lead is not one thing. It is a record that moves through stages, and each stage needs a rule for getting in and one person who decides. Most disputes about lead quality are really disputes about a stage nobody defined.</p>

<?php px_flow('lead-stages', 'The six stages of an organic lead, and who decides each one', [
    ['Form submission', 'Anything that arrives through a form, a call or a chat.', 'The website', 'a'],
    ['Valid enquiry', 'A real person at a real organisation, asking about something you sell.', 'Marketing', 'a'],
    ['Marketing qualified lead', 'Fits the kind of customer you want: size, sector, country, need.', 'Marketing', 'a'],
    ['Sales accepted lead', 'Sales agrees it meets the criteria and commits to follow up.', 'Sales', 'b'],
    ['Opportunity', 'A confirmed need, a budget holder and a next step with a date.', 'Sales', 'b'],
    ['Closed won', 'A signed order or contract.', 'Sales and finance', 'c'],
], [
    'layout' => 'row',
    'sub' => 'Each stage has an entry rule and one owner. Count leads at every stage, not only the first.',
    'key' => [['a', 'Decided by marketing or the site'], ['b', 'Decided by sales'], ['c', 'Confirmed by finance']],
    'cap' => 'Names vary between companies. What matters is that each stage has a written rule and a named owner.',
]); ?>

<p>These names are conventions, not standards. <?= ext_link($s['hubspot'], 'HubSpot\'s default lifecycle stages') ?>, for example, run from lead to marketing qualified lead, sales qualified lead, opportunity and customer, with no separate stage for sales acceptance. The sales accepted lead comes from the demand waterfall that SiriusDecisions, now part of Forrester, <?= ext_link($s['sal'], 'described in 2012') ?>: a formal step where sales confirms that a lead meets the criteria both teams agreed, before anyone contacts the buyer.</p>
<p>Use whichever names your CRM uses. Keep the acceptance step, though, even if it is only a tick box. It is the point where marketing's definition of a good lead meets sales's, and it is where you find out that they differ.</p>
<p>In analytics, the same idea exists as events. Google Analytics calls the actions that matter to a business <?= ext_link($s['keyevents'], 'key events') ?>, and its <?= ext_link($s['leadevents'], 'recommended events for lead generation') ?> include one for a new lead, one for a qualified lead, one for a disqualified lead and one for a lead that became a customer. If your CRM can send those back, analytics stops ending at the form.</p>

<h2 id="where-organic-gets-lost">Where Organic Search Gets Lost</h2>
<p>Organic search loses credit in four places. Each is a small technical gap, and together they explain why the channel looks weaker in a revenue report than in a traffic report.</p>
<ol class="bx-steps">
    <li><strong>Only the last visit is recorded.</strong> Many forms store the source of the visit in which the form was sent. In a long B2B purchase, the visit that mattered was weeks earlier. Analytics has a similar limit: Google Analytics documents a default <?= ext_link($s['attribution'], 'lookback window of 90 days') ?> for most key events, so a first visit older than that is not credited.</li>
    <li><strong>The source never reaches the CRM.</strong> Analytics knows the channel. The CRM knows who became a customer. If no field carries the source from one to the other, nobody can say which channel the customer came from.</li>
    <li><strong>Junk is counted as leads.</strong> Spam, students, job seekers and suppliers all fill in forms. Counted together with buyers, they make every channel look productive and every lead look poor.</li>
    <li><strong>Branded searches hide the real work.</strong> Someone who searches for your company by name arrived because of something earlier: a referral, an article, an AI answer. Our guide to <a href="/blog/search-visibility-vs-revenue-demand/">search visibility and revenue</a> shows how to separate the two.</li>
</ol>
<p>There is a newer wrinkle. Google's <?= ext_link($s['channels'], 'definition of the Organic Search channel') ?> now includes clicks from AI Overviews and AI Mode, while visits from assistants such as ChatGPT sit in a separate AI Assistant channel that Google added in May 2026. Organic search, in your reports, already contains some AI. See <a href="/blog/ai-citations-vs-website-traffic/">AI citations vs website traffic</a> for what each of those numbers proves.</p>

<h2 id="nine-fields">Nine Fields to Store on Every Enquiry</h2>
<p>The fix for all four leaks is the same: store the evidence on the enquiry itself, at the moment it is sent, so it travels with the lead into the CRM. Nine fields are enough.</p>

<?php px_table('The nine fields, where each one comes from and what it answers', ['Field', 'Where it comes from', 'What it answers'], [
    ['1. First source', 'Recorded in the visitor\'s browser on their first visit', 'What first brought this buyer to us?'],
    ['2. Latest source', 'The most recent visit that came from outside the site', 'What brought them back to enquire?'],
    ['3. First landing page', 'The first page they saw', 'Which page started the relationship?'],
    ['4. Form page', 'The page the enquiry was sent from', 'Which page asked for the enquiry and got it?'],
    ['5. Channel group', 'Worked out from the source by fixed rules', 'Organic search, paid, AI assistant, referral, direct?'],
    ['6. Country', 'The form, or the visitor\'s location', 'Is this a market we serve?'],
    ['7. How they heard of you', 'A question on the form, or on the first call', 'What does the buyer remember, in their own words?'],
    ['8. Lead grade', 'A simple score from what the buyer told you', 'Who should sales call first?'],
    ['9. Junk verdict', 'The spam filter, with its reason', 'Should this count as a lead at all?'],
]); ?>

<p>Fields one and two are stored separately on purpose. A buyer whose first source is organic search and whose latest source is a branded paid ad is an organic lead that paid search closed. Store only one of the two and you will give all the credit to the wrong channel.</p>
<p>Field seven matters because tracking misses things. Recommendations, podcasts and AI assistants often leave no trace in analytics. Asking is the only way to hear about them, and our guide to <a href="/blog/marketing-attribution-for-long-sales-cycles/">attribution for long sales cycles</a> explains how to use the answers beside the tracked data.</p>

<h3>How our own enquiry form does it</h3>
<p>We built this into our own website, so here is a working version and its limits.</p>
<ul class="post-list">
    <li><strong>First and latest visit.</strong> A small script records the first visit: the time, the landing page, the referring site, any campaign tags and any ad click identifiers. It keeps that record for 90 days. It records a latest visit only when someone arrives from another site or with a tag, so moving between pages, or coming back directly, never overwrites a real source.</li>
    <li><strong>Sent with the form.</strong> Both records are posted with the enquiry, together with the page the form was on and the visitor's country.</li>
    <li><strong>Sorted by rules.</strong> The admin panel sorts each visit into a channel, such as Google search or ChatGPT, and a channel group, such as organic search or AI assistants.</li>
    <li><strong>Graded and filtered.</strong> Each enquiry gets a grade of A, B or C from the budget, company size and timeline the buyer gave, and from whether they used a company website and a business email address. Suspect enquiries are moved to a junk view, not deleted, so a real buyer can always be restored.</li>
</ul>
<p class="bx-note"><strong>The limits.</strong> The record lives in the visitor's browser. A buyer who researches on a phone and enquires on a laptop arrives with only the laptop's history, and a browser in private mode sends no source at all. That is why the question in field seven stays on the list.</p>

<?php px_cta_mid([
    'label' => 'Free SEO audit',
    'art' => 'doc',
    'h' => 'Which of the Nine Fields Do Your Forms Lose?',
    'p' => 'Request a free SEO audit and mention lead tracking in your message. We will check what your enquiry forms record, and what they drop, along with the rest.',
    'href' => '/lead-generation-seo/#enquire',
    'btn' => 'Request My Free SEO Audit',
    'cta' => 'blog-qleads-audit',
    'note' => 'Fighting fake enquiries first? Read <a href="/blog/how-to-stop-junk-leads-on-your-website/">how to stop junk leads</a>.',
]); ?>

<h2 id="joining-four-systems">Joining Four Systems That Share No ID</h2>
<p>The data you need sits in four places, and no single identifier runs through all of them.</p>

<?php px_table('What each system knows, and what it does not', ['System', 'What it knows', 'What it does not know'], [
    ['Search Console', 'The searches a page appeared for, with clicks, impressions and position', 'Who searched, or what they did next'],
    ['Analytics', 'Visits, landing pages, channels and key events', 'The search query, or what happened after the form'],
    ['The enquiry form', 'The person, and the nine fields above', 'Whether they became a customer'],
    ['The CRM', 'The stage each lead reached, and the value', 'How the lead found you, unless the form told it'],
]); ?>

<p>Two joins between these systems are sound. One is not.</p>
<ul class="post-list">
    <li><strong>Sound: Search Console to analytics, by landing page and date.</strong> Google's <?= ext_link($s['gsc'], 'Search Console integration for Google Analytics') ?> adds a report that lists landing pages with their search clicks and impressions beside analytics metrics, key events included. It tells you which pages turn search visits into enquiries.</li>
    <li><strong>Sound: the form to the CRM, by the enquiry record.</strong> If the nine fields are saved on the lead, every later stage can be counted by first source and by first landing page.</li>
    <li><strong>Not sound: a search query to one named lead.</strong> Google does not pass the query to your site, and the same documentation says the queries report cannot be broken down by analytics dimensions. Nobody can truthfully write "this customer searched for industrial label printing". What you can say is that the customer's first landing page was the label printing page, and that the page appears for these searches.</li>
</ul>
<p>The landing page is the hinge. It is the one fact that Search Console, analytics, the form and the CRM can all hold. Report by landing page and the four systems line up.</p>

<h2 id="six-formulas">Six Formulas, With a Worked Example</h2>
<p>With stages and fields in place, six sums turn the counts into something you can manage. Calculate each one for organic search as a whole, then for each landing page that brings enquiries.</p>

<?php px_table('Six formulas for organic search leads', ['Measure', 'How to calculate it', 'What it tells you'], [
    ['Valid enquiry rate', 'Valid enquiries divided by form submissions', 'How much of what arrives is real'],
    ['Qualified lead rate', 'Qualified leads divided by valid enquiries', 'Whether the page attracts the right buyers'],
    ['Organic share of qualified leads', 'Organic qualified leads divided by all qualified leads', 'How much sales depends on search'],
    ['Cost per organic qualified lead', 'Organic search cost for the period divided by organic qualified leads', 'What a real lead from search costs'],
    ['Lead to opportunity lag', 'The median number of days from enquiry to opportunity', 'How long to wait before judging a month'],
    ['Pipeline per 1,000 organic visits', 'Pipeline value from organic leads, divided by organic visits, times 1,000', 'What search traffic is worth, in money'],
]); ?>

<p>"Qualified" here means sales accepted, stage four in the diagram. "Organic search cost" should include everything spent to earn the traffic: agency fees, writers, tools and the time of your own staff.</p>

<?php px_example('Worked example, with invented numbers', static function (): void { ?>
<p>A company has these figures for one quarter. They are made up to show the arithmetic, and they are not a benchmark.</p>
<?php px_table('One quarter of organic search, as an example', ['Count', 'Figure', 'Result'], [
    ['Organic search visits', '12,000', ''],
    ['Form submissions from those visits', '100', ''],
    ['Valid enquiries', '64', 'Valid enquiry rate: 64 percent'],
    ['Qualified leads from organic search', '24', 'Qualified lead rate: 37.5 percent'],
    ['Qualified leads from all channels', '60', 'Organic share: 40 percent'],
    ['Organic search cost for the quarter', '360,000', 'Cost per organic qualified lead: 15,000'],
    ['Opportunities from those 24 leads', '9', 'Lead to opportunity rate: 37.5 percent'],
    ['Pipeline value of those opportunities', '3,600,000', 'Pipeline per 1,000 visits: 300,000'],
]); ?>
<p>Read as a headline, this quarter produced 100 leads from search. Read through the ledger, it produced 24 that sales accepted, at 15,000 each, and those became 3.6 million of pipeline. The second version is the one a finance lead can use.</p>
<?php }); ?>

<div class="lx-tool lx-js" data-lx-tool="calc" data-calc="leads" id="lead-calculator">
    <p class="lx-tool-k">Calculator</p>
    <p class="lx-tool-h">Work Out Your Own Six Numbers</p>
    <p>The example figures are filled in. Replace them with one quarter of your own data, in any currency. Nothing you type is stored or sent.</p>
    <div class="lx-calc">
        <?= $in('visits', 'Organic search visits', '12000') ?>
        <?= $in('forms', 'Form submissions from organic search', '100') ?>
        <?= $in('valid', 'Valid enquiries', '64') ?>
        <?= $in('qual', 'Qualified leads from organic search', '24') ?>
        <?= $in('all', 'Qualified leads from all channels', '60') ?>
        <?= $in('opps', 'Opportunities from organic leads', '9') ?>
        <?= $in('pipe', 'Pipeline value of those opportunities', '3600000') ?>
        <?= $in('cost', 'Organic search cost for the period', '360000') ?>
    </div>
    <ul class="lx-calc-out" aria-live="polite">
        <li><b data-calc-out="valid"></b>Valid enquiry rate</li>
        <li><b data-calc-out="qualrate"></b>Qualified lead rate</li>
        <li><b data-calc-out="share"></b>Organic share of qualified leads</li>
        <li><b data-calc-out="cpl"></b>Cost per organic qualified lead</li>
        <li><b data-calc-out="opprate"></b>Lead to opportunity rate</li>
        <li><b data-calc-out="pipe"></b>Pipeline per 1,000 organic visits</li>
    </ul>
    <div class="lx-btns"><button class="lx-btn lx-btn--ghost" type="button" data-calc-reset>Clear the numbers</button></div>
</div>

<h2 id="report-the-lag">Reporting the Lag Honestly</h2>
<p>B2B leads take time to become opportunities and longer to become revenue. If you compare the leads that arrived in March with the deals that closed in March, you are comparing two unrelated groups of buyers, and the newest months will always look weak.</p>
<p>The answer is to report by cohort: group leads by the month or quarter they arrived, and follow each group forward.</p>

<?php px_example('Illustration, with invented numbers', static function (): void { ?>
<?php px_table('Qualified leads from organic search, followed by the quarter they arrived', ['Arrived in', 'Qualified leads', 'Opportunities after 1 quarter', 'After 2 quarters', 'After 3 quarters'], [
    ['Quarter 1', '24', '4', '8', '9'],
    ['Quarter 2', '27', '5', '9', 'Not yet known'],
    ['Quarter 3', '31', '5', 'Not yet known', 'Not yet known'],
]); ?>
<p>Quarter 3 looks worse than quarter 1 if you only read the totals. Read across the rows and it is level with it at the same age. The empty cells are the honest part of the table.</p>
<?php }); ?>

<p>The fifth formula tells you how wide to make the table. If your median lag from enquiry to opportunity is 70 days, a month is too short a window to judge anything, and a quarter is the smallest unit worth reporting.</p>

<h2 id="leave-out">What to Leave Out of the Report</h2>
<p>A report gets shorter and more useful when you remove the numbers nobody acts on. These four are the usual candidates.</p>
<ul class="post-list">
    <li><strong>Rankings as a headline.</strong> A position is an input. Keep it in the working file for the people who need it.</li>
    <li><strong>Total form fills.</strong> Without the valid enquiry rate beside it, the total rewards spam.</li>
    <li><strong>Last-click revenue on its own.</strong> It under-counts every channel that starts journeys and over-counts every channel that ends them.</li>
    <li><strong>Any number with no owner.</strong> If nobody would do anything differently when it moves, it does not belong on the page.</li>
</ul>
<p>What is left fits on one screen: qualified leads from organic search, their cost, the pipeline they produced, the lag, and the three landing pages doing most of the work. Our <a href="/guides/complete-guide-to-business-lead-gen-seo/">guide to lead generation SEO</a> covers how to grow those pages, and the <a href="/ebooks/marketing-analytics-starter/">marketing analytics starter</a> covers the analytics setup behind them.</p>
<?php },

'closing' => static function (): void { ?>
<h2 id="final-thoughts">Start With the Fields</h2>
<p>Organic search rarely fails on the evidence. It fails on the record keeping. The visits are counted, the customers are counted, and the line between them was never written down.</p>
<p>Six stages, nine fields and six formulas are enough to write it down. Start with the fields, because they cannot be added to leads you have already received. Every enquiry that arrives without its first source is one you will never be able to trace.</p>

<?php px_cta_main([
    'h' => 'Want Search Reported in Qualified Leads?',
    'p' => 'Tell us what you sell and how leads reach your sales team today. We will show you where the source is being lost and what to fix first.',
    'btn' => 'Talk to a Strategist',
    'cta' => 'blog-qleads-contact',
    'link' => ['/lead-generation-seo/', 'See Lead Generation SEO', 'blog-qleads-service'],
]); ?>

<?php px_author(); ?>
<?php },

];
