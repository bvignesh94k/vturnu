<?php
/**
 * Explainer: what LinkedIn counts as engagement, why most of it is not buying
 * interest, and how to sort signals into three bands and record them by
 * account. Loaded by templates/blog-post.php through the 'rich' key.
 *
 * Research-based, with no first-hand data, so the article makes no "we" claim
 * and cites no case study. LinkedIn's definitions are paraphrased from the
 * help page linked in each sentence, read on 13 and 14 November 2026. The
 * Buyer Intent pages carry LinkedIn's own stamp of about three years old, and
 * the article says so. The one quotation is from Dawes (2021). Vendor
 * research is called vendor research. Every account name and every case in
 * the two example boxes is invented and labelled as invented. No engagement
 * benchmark is given, and nothing here promises a lead.
 */

$s = [
    'page' => 'https://www.linkedin.com/help/linkedin/answer/a564051',
    'ads' => 'https://www.linkedin.com/help/lms/answer/a422348',
    'member' => 'https://www.linkedin.com/help/linkedin/answer/a701208',
    'distribution' => 'https://www.linkedin.com/help/linkedin/answer/a516930',
    'postanalytics' => 'https://www.linkedin.com/help/linkedin/answer/a516971',
    'dawes' => 'https://business.linkedin.com/content/dam/me/business/en-us/marketing-solutions/resources/pdfs/advertising-effectiveness-and-the-95-5-rule.pdf',
    'edelman' => 'https://www.edelman.com/insights/hidden-buyer-b2b',
    'intent' => 'https://www.linkedin.com/help/sales-navigator/answer/a798004',
    'intentfaq' => 'https://www.linkedin.com/help/sales-navigator/answer/a507435',
    'intentacts' => 'https://www.linkedin.com/help/sales-navigator/answer/a1325209',
    'plans' => 'https://business.linkedin.com/sell/sales-navigator/compare-plans',
    'hub' => 'https://www.linkedin.com/help/lms/answer/a6884751',
    'hubmetrics' => 'https://www.linkedin.com/help/lms/answer/a7109308',
    'hubemployer' => 'https://www.linkedin.com/help/lms/answer/a10166020',
    'capi' => 'https://www.linkedin.com/help/lms/answer/a1655394',
    'capidev' => 'https://learn.microsoft.com/en-us/linkedin/marketing/integrations/ads-reporting/conversions-api',
    'revenue' => 'https://www.linkedin.com/help/lms/answer/a715191',
    'influenced' => 'https://www.linkedin.com/help/lms/answer/a1367472',
];

return [

'body' => static function () use ($s): void { ?>
<p class="bx-open">Picture two posts on a company's LinkedIn Page. One collects 300 reactions and no enquiries. The other collects 12 reactions, and a month later one of those 12 people asks for a proposal.</p>
<p>The example is made up, but the pattern is familiar to anyone who reports LinkedIn to a sales team. Marketing shows impressions, reactions and followers. Sales asks which of those people want to buy, and the report has no answer.</p>
<p>Engagement is not worthless. It is a count of interest in a post, and it is being asked a question about purchases. This article sets out what LinkedIn counts, who is likely to be behind the count, and which signals do sit closer to a buying decision. It ends with a sheet that records those signals by account, which is the form sales can use.</p>

<?php px_answer('linkedin-engagement-vs-buyer-intent'); ?>

<?php px_toc([
    'what-linkedin-counts' => 'What LinkedIn counts as engagement',
    'who-engages' => 'A reaction does not say who reacted, or why',
    'not-in-market' => 'Most buyers are not in the market today',
    'three-bands' => 'Signals in three bands, from weak to strong',
    'six-cases' => 'Six cases, sorted',
    'score-accounts' => 'Score accounts, not posts',
    'sales-navigator' => 'What Sales Navigator\'s Buyer Intent tells you',
    'pipeline' => 'Connecting LinkedIn to pipeline',
    'faq-title' => 'FAQs',
]); ?>

<h2 id="what-linkedin-counts">What LinkedIn Counts as Engagement</h2>
<p>LinkedIn uses the word in three places, and it means something different in each. The definitions below are from LinkedIn's help pages as they read on 13 and 14 November 2026.</p>

<?php px_table('How LinkedIn defines engagement in three places', ['Where', 'What is counted', 'How the rate is worked out'], [
    ['Company Page posts', 'Clicks, reactions, comments and shares. A click can be on the content, the company name or the logo', 'Interactions divided by impressions'],
    ['Ads in Campaign Manager', 'Social actions, plus clicks to the landing page and to the LinkedIn Page, whether charged or not', 'Paid and free clicks divided by total impressions'],
    ['Posts by a member', 'Reactions, comments, saves, sends and reposts. Clicks are not included', 'LinkedIn shows no engagement rate here'],
]); ?>

<p>For a Page, <?= ext_link($s['page'], 'LinkedIn\'s content analytics page') ?> gives the engagement rate as interactions divided by impressions. Follows are listed separately and are not part of the rate, and LinkedIn describes impressions as an estimate. For ads, <?= ext_link($s['ads'], 'the Campaign Manager definitions') ?> are different again, and the average click-through rate there counts only chargeable clicks. For a person's own posts, <?= ext_link($s['member'], 'the member analytics page') ?> lists engagements and gives no rate at all.</p>
<p>Two things follow. First, an engagement rate from a Page report and one from an ad report are not the same sum, so they should not share a chart. Second, every one of these definitions counts actions on a post. None of them contains a company name, a job role or a purchase.</p>

<h2 id="who-engages">A Reaction Does Not Say Who Reacted, or Why</h2>
<p>A post does not go to the market. According to <?= ext_link($s['distribution'], 'LinkedIn\'s page on content distribution') ?>, it goes to a subset of your connections and followers, and their activity can affect how far it travels. The audience starts with the network you already have, which is not the same as the buyers you want.</p>
<p>LinkedIn's <?= ext_link($s['postanalytics'], 'post analytics') ?> show the job titles, companies, industries and seniority of the people who viewed a post, once there are enough of them. The page describes those breakdowns for viewers. It describes nothing equivalent for the people who reacted or commented. To learn who engaged, someone has to open the list and read the names.</p>
<p>When they do, the list on a typical B2B post can include:</p>
<ul class="post-list">
    <li><strong>Colleagues and former colleagues,</strong> who react because they know the author.</li>
    <li><strong>Peers in the same job,</strong> who found the idea useful for their own work.</li>
    <li><strong>Competitors and agencies,</strong> who are watching the category.</li>
    <li><strong>Suppliers and job seekers,</strong> who want to be noticed by the company.</li>
    <li><strong>Some people at companies that could buy.</strong></li>
</ul>
<p>No published study with a disclosed method was found that measures these shares, so this article does not give any. The point is narrower. A reaction says "this was interesting to me". It does not say "my company has a budget for this". Only the last group on the list can become revenue, and the total hides how large that group is.</p>

<h2 id="not-in-market">Most Buyers Are Not in the Market Today</h2>
<p>There is a second reason engagement and intent drift apart, and it applies even when the right people are reacting. In a 2021 paper for the B2B Institute, which is part of LinkedIn, Professor John Dawes of the Ehrenberg-Bass Institute argued that <?= ext_link($s['dawes'], '"up to 95% of business clients are not in the market"') ?> for many goods and services at a given time (Dawes, 2021).</p>
<p>The reasoning is simple arithmetic. If companies change a supplier such as their main bank or law firm about every five years, about 20 percent are buying in a given year and about 5 percent in a given quarter. The paper calls the figure a rule of thumb, not a precise number, and says the share depends on how often the category is bought. It is an argument, not a survey.</p>
<p>Read with that in mind, engagement has a different job. A buyer at a target account who reads and reacts today is probably not buying this quarter. The post may still help that person remember the company when a need does arise. The intent, when it comes, tends to show up later and somewhere else: in a search, a visit to a pricing page or a message.</p>
<p>The person who matters may also never react. Vendor research by <?= ext_link($s['edelman'], 'Edelman and LinkedIn') ?>, based on nearly 2,000 professionals, describes "hidden buyers": people in finance, legal, procurement and operations who shape a deal without meeting the sales team. They can read everything and click nothing.</p>

<h2 id="three-bands">Signals in Three Bands, From Weak to Strong</h2>
<p>If engagement is not intent, something else has to stand in for it. The useful step is to stop treating LinkedIn activity as one number and sort it by how close each signal sits to a buying decision.</p>

<?php px_flow('signal-bands', 'Three bands of LinkedIn signal', [
    ['Weak: someone noticed', 'Impressions, reactions, follower growth. Counted per post, with no account attached.', 'Interest in a post', 'c'],
    ['Moderate: an account keeps coming back', 'Several people from one target account, over several weeks, or clicks through to a service page.', 'Interest in the company', 'b'],
    ['Strong: someone asked for something', 'A reply, a visit to pricing or contact pages, a demo or quote request.', 'Interest in buying', 'a'],
], [
    'sub' => 'The bands describe how close a signal is to a purchase. They are a way of sorting, not a measurement.',
    'key' => [['c', 'Weak'], ['b', 'Moderate'], ['a', 'Strong']],
    'cap' => 'A signal moves up a band when it gains a company name, repetition or a request.',
]); ?>

<?php px_table('LinkedIn signals by band, with what each one shows and does not show', ['Signal', 'Band', 'What it shows', 'What it does not show'], [
    ['Impressions', 'Weak', 'The post was displayed. LinkedIn calls the figure an estimate', 'That anyone read it, or who they were'],
    ['Reactions and comments', 'Weak', 'Someone found the post worth a response', 'Their company, their role in a purchase or any need'],
    ['Follower growth', 'Weak', 'More people chose to see future posts', 'Whether the new followers are buyers, peers or suppliers'],
    ['Several people from one target account engaging over weeks', 'Moderate', 'The company name is known inside that account, across more than one person', 'That a project or a budget exists'],
    ['Company Page visits from target accounts', 'Moderate', 'Someone there looked the company up', 'Why they looked'],
    ['Clicks from a post to a service page', 'Moderate', 'Interest moved from the topic to what is sold', 'That the visitor has any say in a purchase'],
    ['A reply to a direct message', 'Strong', 'A named person is willing to talk', 'That the timing is right'],
    ['Visits to pricing or contact pages from the account', 'Strong', 'Someone is checking how to buy', 'Who it was, unless they fill in a form'],
    ['A demo or quote request', 'Strong', 'A stated need, from a named person', 'That the request fits the criteria sales uses to qualify'],
    ['A rise in the account\'s Buyer Intent level in Sales Navigator', 'Strong', 'LinkedIn\'s own score for the account has gone up', 'Which activity moved it, or that the interest is in what is being sold'],
]); ?>

<p>Three things move a signal up the table: a company name, repetition and a request. A single reaction has none of them. Five people from one account over six weeks have the first two. A quote request has all three.</p>
<p>Even the strong band is not proof. A quote request can come from a student or a competitor, which is why the last column matters. The bands tell sales where to look first.</p>

<h2 id="six-cases">Six Cases, Sorted</h2>
<p>The bands are easier to use after a few examples. Every case below is invented for illustration, and none describes a real company.</p>

<?php px_example('Illustration: six invented cases', static function (): void { ?>
<?php px_table('Six made-up LinkedIn events and how to read each one', ['What happened', 'Band', 'How to read it'], [
    ['A marketing manager at a competitor comments on a post about pricing models', 'Weak', 'It adds to the engagement rate. It is a peer watching the category, not a buyer'],
    ['A post about a team away day gets 300 reactions, mostly from staff, friends and former colleagues', 'Weak', 'It may help hiring. It says nothing about demand'],
    ['A procurement lead at a target account follows the Page and does nothing else', 'Weak', 'Worth noting on the account sheet. One action by one person is not a pattern'],
    ['Three engineers from one target account open the same technical guide within a week', 'Moderate', 'Something is being discussed inside that account. A reason for a relevant, low-pressure message'],
    ['An operations director at a target account replies to a message and asks about lead times', 'Strong', 'A named person with a specific question. Sales should answer the same day'],
    ['A target account\'s intent level rises in Sales Navigator, and a quote request arrives through the website a week later', 'Strong', 'Two separate signals agree. The request is the evidence, and the score is the supporting detail'],
]); ?>
<p>Notice that the post with the most reactions sits in the weak band, and the events in the strong band may have no public engagement at all.</p>
<?php }); ?>

<h2 id="score-accounts">Score Accounts, Not Posts</h2>
<p>A post report answers "which post did well?". Sales needs the answer to "which companies are warming up?". That calls for a different sheet, with one row for each target account and the signals collected against it.</p>
<p>Five columns are enough to start. Keep the sheet in the CRM if it has account records, or in a spreadsheet if it does not.</p>
<ol class="bx-steps">
    <li><strong>Target account.</strong> Taken from an agreed list, not from whoever happened to engage. Our guide to <a href="/blog/account-based-marketing-for-mid-market-companies/">account-based marketing for mid-market companies</a> covers how to choose the list.</li>
    <li><strong>People engaged.</strong> How many different people from the account, and their roles.</li>
    <li><strong>Signals seen.</strong> What they did, with dates.</li>
    <li><strong>Strongest signal.</strong> The highest band reached in the period.</li>
    <li><strong>Next step.</strong> One action, with an owner.</li>
</ol>

<?php px_example('Example account sheet. The account names are invented', static function (): void { ?>
<?php px_table('One month of LinkedIn signals, recorded by account', ['Target account', 'People engaged', 'Signals seen', 'Strongest signal', 'Next step'], [
    ['Northgate Packaging (invented)', '4: two engineers, a plant manager, a buyer', 'Reactions on three posts over five weeks, two clicks to the service page', 'Moderate', 'Marketing sends the plant manager the related guide'],
    ['Halden Freight (invented)', '1: operations director', 'Replied to a message and asked about onboarding time', 'Strong', 'Sales books a call this week'],
    ['Brightwell Foods (invented)', '1: marketing executive', 'One comment on one post', 'Weak', 'None. Review next month'],
    ['Corvane Systems (invented)', '2: finance lead, IT manager', 'Pricing page visit from the account, then a quote request', 'Strong', 'Sales qualifies the request against the agreed criteria'],
]); ?>
<p>The sheet has four rows and no engagement rate. It still tells sales more than a chart of impressions, because every row ends in a decision.</p>
<?php }); ?>

<p>Review the sheet once a month with sales. Accounts that stay in the weak band for a quarter are a prompt to change what is posted, and our article on <a href="/blog/social-media-content-strategy-for-business/">social media content strategy for business</a> covers that side. Accounts in the moderate band are candidates for a direct approach, which is where <a href="/outbound-marketing/">outbound marketing</a> takes over from content.</p>

<h2 id="sales-navigator">What Sales Navigator's Buyer Intent Tells You</h2>
<p>LinkedIn sells its own answer to this problem. <?= ext_link($s['intent'], 'Buyer Intent in Sales Navigator') ?> gives a view of the accounts LinkedIn judges to be showing interest. It appears on account pages and in alerts, and it can be used as a search filter.</p>
<p>A caution about the documentation comes first. LinkedIn stamps its <?= ext_link($s['intentfaq'], 'Buyer Intent FAQ') ?> as about three years old, so the details below may have changed since. As that page reads today:</p>
<ul class="post-list">
    <li><strong>What feeds it.</strong> The score combines more than 180 signals in four groups: activity on LinkedIn, interactions with ads, accepted or declined InMail messages, and visits to your website where LinkedIn's Insight Tag is installed.</li>
    <li><strong>The levels.</strong> High, Moderate, Neutral and Negative.</li>
    <li><strong>The time window.</strong> The filter for high or moderate intent covers the past 30 days.</li>
    <li><strong>The plans.</strong> LinkedIn's <?= ext_link($s['plans'], 'plan comparison') ?>, updated in September 2026, lists Buyer Intent for Advanced and Advanced Plus, and not for Core.</li>
</ul>
<p>LinkedIn's list of <?= ext_link($s['intentacts'], 'activities that indicate buyer intent') ?> includes company follows, Page and profile visits, new connections, lead form completions, ad views and clicks, accepted InMails and tagged website visits. Each activity is marked as showing either the person's identity or only the type of profile.</p>
<p>Comments and reactions on posts are among the signals. So engagement does feed the score, as one input among many. LinkedIn does not publish how the signals are weighted, and this article does not guess.</p>
<p>That is why the intent level belongs in the strong band with a condition attached. It is a prompt to look at an account and start a conversation. It is not proof that the account is buying, or that the interest is in your product. Check it against a signal you can see yourself, such as a reply or a visit to a pricing page.</p>

<h2 id="pipeline">Connecting LinkedIn to Pipeline</h2>
<p>The last step is to see whether accounts that showed signals went on to become opportunities. There are three ways to do it, and they work best together.</p>

<h3>Ask the buyer</h3>
<p>Add a "How did you hear about us?" field to the enquiry form and let people answer in their own words. A buyer who writes "I have been following your posts for a year" has given evidence that no tracking tool records. Our guide to <a href="/blog/marketing-attribution-for-long-sales-cycles/">marketing attribution for long sales cycles</a> explains how to use self-reported answers beside tracked ones.</p>

<h3>Match accounts in the CRM</h3>
<p>Once a quarter, compare the account sheet with the opportunities opened in the CRM. Count how many new opportunities came from accounts that had reached the moderate or strong band beforehand. This shows that the two moved together. It does not show that LinkedIn caused the opportunity. For the stages and fields to store on each enquiry, see <a href="/blog/measure-qualified-leads-from-organic-search/">how to measure qualified leads from organic search</a>. The same ledger works for any channel.</p>

<h3>Use LinkedIn's own reports, knowing what they need</h3>
<p>LinkedIn offers three tools for advertisers. Each has requirements, and each has a limit worth stating when the numbers are presented.</p>

<?php px_table('LinkedIn\'s reports that connect activity to companies and revenue', ['Report', 'What it shows', 'What it needs', 'The limit'], [
    ['Companies Hub', 'For each company: an engagement level, organic engagements, paid clicks, leads and revenue won', 'A LinkedIn Page tied to the ad account. Revenue figures need CRM sync through Business Manager', 'Members are counted under the current employer on their profile, so totals differ from campaign reports. Small values show as a dash'],
    ['Conversions API', 'Online and offline conversions, such as a qualified lead or a sale, sent from your server to LinkedIn', 'A conversion rule and a connection, direct or through a partner. Each event needs an identifier such as a hashed email', 'Only matched events count, and an event must be no more than 90 days old when it is sent'],
    ['Revenue Attribution Report', 'Revenue won, return on ad spend and pipeline, for deals that LinkedIn marketing influenced', 'Business Manager admin access and a CRM connection: Salesforce, Dynamics 365 or HubSpot', '"Influenced" means a matching CRM lead engaged inside the lookback window. It is a link in time, not proof of cause'],
]); ?>

<p>The details are in LinkedIn's pages on <?= ext_link($s['hub'], 'Companies Hub') ?>, its <?= ext_link($s['hubmetrics'], 'metrics') ?> and <?= ext_link($s['hubemployer'], 'how members are matched to companies') ?>, the <?= ext_link($s['capi'], 'Conversions API') ?> and its <?= ext_link($s['capidev'], 'developer reference') ?>, and the <?= ext_link($s['revenue'], 'Revenue Attribution Report') ?> with its <?= ext_link($s['influenced'], 'definition of an influenced deal') ?>. The engagement level in Companies Hub combines the paid engagement rate with the number of organic engagements and ranks the company against others advertising on LinkedIn. It is a relative position, so it should not be read as a count.</p>
<p>These reports are built around advertising. If you are deciding whether to pay for reach on LinkedIn at all, our comparison of <a href="/blog/linkedin-ads-vs-google-ads-for-lead-generation/">LinkedIn Ads and Google Ads for lead generation</a> covers that choice.</p>

<h2 id="what-to-report">A LinkedIn Report That Sales Will Accept</h2>
<p>Keep the engagement numbers. They show whether posts are reaching people and which topics hold attention, and that is worth knowing. Move them to the second page, and lead with accounts.</p>
<ul class="post-list">
    <li><strong>Target accounts with any signal this month,</strong> and how many reached the moderate or strong band.</li>
    <li><strong>Accounts that moved up a band,</strong> with the signal that moved them.</li>
    <li><strong>Strong signals passed to sales,</strong> and what happened to each.</li>
    <li><strong>Opportunities opened</strong> at accounts that had shown signals beforehand, stated as a match and not as a cause.</li>
    <li><strong>Reach and engagement,</strong> as supporting detail, with Page and ad figures kept apart.</li>
</ul>
<?php },

'closing' => static function (): void { ?>
<h2 id="final-thoughts">Change the Unit From Posts to Accounts</h2>
<p>Engagement and intent answer different questions. Engagement says a post found an audience. Intent says a company is moving toward a purchase. The first can exist for years without the second, and the second can appear with no public engagement at all.</p>
<p>The fix is not a better engagement rate. It is a change of unit, from posts to accounts, and a habit of sorting signals by how close they sit to a request. None of this guarantees that an account will buy. It gives sales a short, honest list of where to look first.</p>

<?php px_cta_main([
    'h' => 'See Which of Your LinkedIn Signals Point to Real Accounts',
    'p' => 'Ask for a free social media audit. We review your profiles and tell you three priority moves.',
    'href' => '/social-media-marketing/#enquire',
    'btn' => 'Get My Free Social Media Audit',
    'cta' => 'blog-linkedin-audit',
    'link' => ['/social-media-marketing/', 'See Social Media Marketing Services', 'blog-linkedin-service'],
]); ?>

<?php px_author(); ?>
<?php },

];
