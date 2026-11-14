<?php
/**
 * November 2026 authority series: ten articles and one rebuilt guide on AI
 * search, organic leads, websites and buyer intent. Same fields as
 * includes/data/blog.php, merged ahead of every other post (see the end of
 * blog.php). Each article's markup is in includes/data/posts/<slug>.php and
 * uses the helpers in includes/post-kit.php.
 *
 * Rules these articles follow: client figures appear exactly as published in
 * includes/data/cases-real.php; every invented example is labelled in the
 * article; outside claims link to the page they come from, checked on 13 and
 * 14 November 2026; nothing promises a ranking, a citation or a lead.
 * 'service' names the page shown as the related service under the article.
 */

return [

'pagespeed-score-vs-qualified-leads' => [
    'rich'  => 'pagespeed-score-vs-qualified-leads',
    'title' => 'PageSpeed Score vs Qualified Leads: What the Score Misses',
    'meta'  => 'A high PageSpeed score shows a page loads fast in a lab test. It cannot show if forms work or leads are real. See what to check, and in what order.',
    'h1'    => 'Why a High PageSpeed Score Does Not Guarantee More Qualified Leads',
    'lede'  => 'A PageSpeed score shows how fast one page loaded in a simulated test. It cannot show what real visitors experienced, whether the page spoke to the right buyer, whether the form delivered the enquiry or whether the enquiry was real. Speed removes a reason to leave. Qualified leads also depend on access, message, form and lead quality, so check all five in order.',
    'kicker' => 'Speed and leads',
    'category' => 'Conversion', 'intent' => 'Explainer', 'date' => '2026-11-14', 'read' => '9 min',
    'service' => ['/lead-gen-web-design/', 'lead generation web design'],
    'sections' => [],
    'takeaways' => [],
    'faqs' => [
        ['What is a good PageSpeed score?', 'PageSpeed Insights rates 90 or more as good, 50 to 89 as needing improvement and below 50 as poor. That score is a lab test of one page load. Passing the Core Web Vitals assessment in the field data is the more meaningful target, because it describes real visits.'],
        ['Does a higher PageSpeed score improve Google rankings?', 'Not by itself. Google says Core Web Vitals are used by its ranking systems, and also that good results do not guarantee a top position. It aims to show the most relevant content even where page experience is weaker, so speed helps most where many similar pages compete.'],
        ['Why does my PageSpeed score change every time I test?', 'The lab test is affected by network conditions, the test machine, third-party scripts and anything on the page that changes between loads. Lighthouse recommends running several tests and using the median. Five runs give a much steadier figure than one.'],
        ['My pages show no field data in PageSpeed Insights. What should I use?', 'Field data needs enough visits from Chrome users, and many B2B pages do not reach that level. Check whether the tool shows data for the whole site instead. If it does not, use the median of several lab runs and compare it with your own analytics for the same pages.'],
        ['Does site speed affect conversion rate?', 'Studies have found that faster pages keep more visitors, especially on phones. The same studies count a completed form as a conversion and do not measure lead quality. Speed removes a reason to leave. It does not make an unqualified visitor qualified.'],
    ],
    'cta' => ['Fast Website, Weak Enquiries?', 'We review the page, the form and the leads it sends you, and tell you which check is failing.', 'Get My Free Website Review'],
],

'measure-qualified-leads-from-organic-search' => [
    'rich'  => 'measure-qualified-leads-from-organic-search',
    'listicle' => true,
    'title' => 'How to Measure Qualified Leads From Organic Search (B2B)',
    'meta'  => 'Measure qualified leads from organic search with six lead stages, nine fields to store on every enquiry and six formulas. Includes a free calculator.',
    'h1'    => 'How to Measure Qualified Leads From Organic Search: A B2B Framework',
    'lede'  => 'To measure qualified leads from organic search, give each lead stage one owner and a written entry rule, store nine fields on every enquiry (starting with the first source and the first landing page), join your systems by landing page, and report six numbers: valid enquiry rate, qualified lead rate, organic share, cost per qualified lead, lag and pipeline per 1,000 visits.',
    'kicker' => 'Organic leads',
    'category' => 'SEO', 'intent' => 'Framework', 'date' => '2026-11-14', 'read' => '12 min',
    'service' => ['/lead-generation-seo/', 'lead generation SEO'],
    'sections' => [],
    'takeaways' => [],
    'faqs' => [
        ['What counts as a qualified lead in B2B?', 'A qualified lead is an enquiry that sales has accepted as worth a conversation, against criteria agreed with marketing in advance: usually the type and size of company, the country, the need and the timing. A form submission is not a qualified lead until someone has checked it against those criteria.'],
        ['What is the difference between an MQL, an SAL and an SQL?', 'A marketing qualified lead fits the profile marketing was asked to find. A sales accepted lead is one that sales has agreed to follow up. A sales qualified lead, or opportunity, has a confirmed need and a next step. The names vary between CRMs, so write down your own entry rule for each.'],
        ['Can Google Analytics show which search query produced a lead?', 'No. Google does not pass the search query to your website. Search Console shows the queries for each page, and analytics shows which landing pages led to enquiries. You can join the two by landing page, but you cannot tie one query to one named lead.'],
        ['How long does it take for organic search leads to turn into revenue?', 'It depends on your sales cycle, so measure it instead of borrowing a benchmark. Take the median number of days from enquiry to opportunity, and from opportunity to signed order, for leads whose first source was organic search. Then report results by the quarter the lead arrived.'],
        ['Should branded searches count as organic search leads?', 'Count them, and report them separately. A buyer who searches for your company by name already knew you, so that lead says more about your reputation than about your content. Non-branded searches show whether search is reaching new buyers.'],
    ],
    'cta' => ['Want Search Reported in Qualified Leads?', 'Tell us how leads reach your sales team today. We will show you where the source is being lost.', 'Talk to a Strategist'],
],

'ai-crawler-access-problems' => [
    'rich'  => 'ai-crawler-access-problems',
    'listicle' => true,
    'title' => 'AI Crawler Access Problems: 6 Checks Beyond robots.txt',
    'meta'  => 'Robots.txt allows AI crawlers, yet ChatGPT and Perplexity still cannot read your site? Test six layers in order, from firewall rules to server logs.',
    'h1'    => 'AI Crawler Access Problems on B2B Websites: A Diagnostic Guide Beyond robots.txt',
    'lede'  => 'If robots.txt allows AI crawlers and assistants still cannot read your website, the block is in another layer. Test six, in order: the robots.txt rules, the firewall, the CDN\'s AI settings, the page\'s HTML without JavaScript, walls such as logins and consent screens, and the server log. The log is the only proof that a crawler arrived and what it received.',
    'kicker' => 'AI crawlers',
    'category' => 'AI Search', 'intent' => 'Diagnostic Guide', 'date' => '2026-11-14', 'read' => '12 min',
    'service' => ['/ai-seo/', 'AI SEO services'],
    'sections' => [],
    'takeaways' => [],
    'faqs' => [
        ['Do AI crawlers run JavaScript?', 'In the largest published study, by Vercel and MERJ in December 2024, none of the main AI crawlers ran JavaScript, while Googlebot did. Small tests in October 2026 reported one OpenAI crawler running scripts, which OpenAI has not confirmed. The safe course is to have your main text in the HTML the server sends.'],
        ['Which AI crawlers should a B2B website allow?', 'If you want to be cited, allow the search crawlers, such as OAI-SearchBot, Claude-SearchBot and PerplexityBot, and keep Googlebot open, because Google\'s AI features depend on it. Training crawlers such as GPTBot and ClaudeBot are a separate business decision. Blocking them does not remove you from search answers.'],
        ['How do I know if my firewall is blocking AI crawlers?', 'Request a page with a normal browser user agent, then again with a crawler\'s name in the user agent, and compare the status codes and response sizes. A 403, a 503 or a much smaller response suggests a block. Confirm it in your server logs, because some firewalls decide by address, not by name.'],
        ['Does Cloudflare block AI crawlers by default?', 'It depends on when your domain was added and what was chosen at setup. Cloudflare announced blocking by default for new domains in July 2025 and changed its approach during 2026. Its documentation in November 2026 says new domains default to allowing search, agents and training. Check your own dashboard.'],
        ['How long after a fix will AI assistants notice?', 'OpenAI and Perplexity say a robots.txt change can take up to about 24 hours to take effect. After that, it depends on when each crawler next visits the page. Nobody can promise a citation, but you can confirm the visit in your server log.'],
    ],
    'cta' => ['Find Out Where AI Crawlers Stop on Your Site', 'Ask for a free AI visibility report. We check what assistants say about you and where crawlers are turned away.', 'Get My Free AI Visibility Report'],
],

'how-ai-agents-evaluate-b2b-websites' => [
    'rich'  => 'how-ai-agents-evaluate-b2b-websites',
    'listicle' => true,
    'title' => 'How AI Agents Evaluate a B2B Website: What They Can Do Now',
    'meta'  => 'What AI agents can read, click and complete on a B2B website today, what stops them, and two tests to run: Agentic Browsing in Lighthouse and six tasks.',
    'h1'    => 'How AI Agents Evaluate a B2B Website: What They Can Read, Click and Complete Today',
    'lede'  => 'AI agents evaluate a website by reading its structure, not its design. Browser agents from OpenAI, Google, Anthropic and Perplexity can read pages, click, type and fill in forms, and they are designed to pause before payments and other commitments. They stop where a page is ambiguous: unnamed buttons, text that loads late, facts locked in PDFs and forms that fail silently. Two tests show where your site stands.',
    'kicker' => 'AI agents',
    'category' => 'AI Search', 'intent' => 'Explainer', 'date' => '2026-11-14', 'read' => '11 min',
    'service' => ['/ai-seo/', 'AI SEO services'],
    'sections' => [],
    'takeaways' => [],
    'faqs' => [
        ['What is an AI agent, compared with a chatbot or a crawler?', 'A crawler requests pages and reads them. A chatbot answers questions from what it has read. A browser agent goes further: it drives a browser for a person, reading pages, clicking, typing and filling in forms to finish a task, and pausing for approval at sensitive steps.'],
        ['Can AI agents fill in forms and buy things on a website today?', 'They can click, type and fill in forms. The vendors\' documentation says they pause or hand back to the user before payments, account creation and other commitments, and Anthropic\'s guide for Claude in Chrome lists purchases as something it will not do. A person still approves the final step.'],
        ['What is the Agentic Browsing score in Lighthouse?', 'It is a fraction, such as 2 of 2 or 3 of 3, showing how many applicable checks a page passed. Version 13.6 has seven checks, covering the accessibility tree, layout stability, llms.txt and two proposed standards. Google describes the category as experimental.'],
        ['Does the Agentic Browsing result affect Google rankings?', 'Google has not said that it does. Its documentation describes the category as experimental and its results as informational. Treat it as a test of how usable a page is for agents, not as a ranking factor.'],
        ['Do I need an llms.txt file for AI agents?', 'It is optional. Lighthouse checks the file if you have one. Google says Google Search does not use it, and that it will neither harm nor help your rankings. Add one if it is quick, keep it accurate, and put your effort into the page itself.'],
    ],
    'cta' => ['Find Out Where an AI Agent Gets Stuck on Your Site', 'Ask for a free AI visibility report. We check what AI assistants can read on your site and what to fix first.', 'Get My Free AI Visibility Report'],
],

'ai-citations-vs-website-traffic' => [
    'rich'  => 'ai-citations-vs-website-traffic',
    'listicle' => true,
    'title' => 'AI Citations vs Website Traffic: What Each Number Proves',
    'meta'  => 'AI citations count answers that used your page. AI referral traffic in GA4 counts people who clicked. See six measurements and what each one proves.',
    'h1'    => 'AI Citations vs Website Traffic: What B2B Marketers Should Measure',
    'lede'  => 'A citation counts an AI answer that used your page as a source. A visit counts a person who clicked through and whose source analytics could read. Citations are usually far higher, because most readers never click and some clicks arrive looking like direct traffic. Report six measurements separately: mentions, citations, referral visits, enquiries, opportunities and revenue. Never present one as another.',
    'kicker' => 'AI measurement',
    'category' => 'AI Search', 'intent' => 'Explainer', 'date' => '2026-11-14', 'read' => '11 min',
    'service' => ['/ai-seo/', 'AI SEO services'],
    'sections' => [],
    'takeaways' => [],
    'faqs' => [
        ['What is the difference between an AI mention, a citation and a visit?', 'A mention is an AI answer that names your brand. A citation is an answer that shows one of your pages as a source, usually with a link. A visit is a person clicking through to your website. Each is counted by a different system, and each is smaller than the one before.'],
        ['Why are my AI citations high and my AI referral traffic low?', 'Most people read the answer and do not open the sources. Some who do click arrive without a referrer, so analytics records them as direct traffic. Others return days later through a search for your brand name. A wide gap is normal and does not mean the tracking is broken.'],
        ['How do I see AI referral traffic in GA4?', 'Open the Traffic acquisition report and look for the AI Assistant channel, which Google added to the default channel group in May 2026. The Source Group field shows which assistant sent the visit. The channel excludes Google\'s AI Overviews and AI Mode, which are counted in Organic Search.'],
        ['Does Search Console show clicks from AI Overviews?', 'Not separately. Google counts clicks from AI Overviews and AI Mode inside the main Performance report, mixed with other search clicks. The Generative AI performance report, announced in June 2026, shows impressions only, with no clicks, queries or position.'],
        ['Do visits from AI assistants convert better than search visits?', 'The published studies disagree. A peer-reviewed study of e-commerce sites found AI referrals converting below most channels, while vendor studies of other kinds of site report the opposite. None covers B2B enquiries. Measure your own rate and quote the number of visits behind it.'],
    ],
    'cta' => ['Want AI Search Reported Without the Guesswork?', 'Tell us what you track today. We will show you which rungs you can measure now and what each number can support.', 'Talk to a Strategist'],
],

'search-visibility-vs-revenue-demand' => [
    'rich'  => 'search-visibility-vs-revenue-demand',
    'title' => 'Search Visibility vs Revenue: 4 Tests for Real SEO Demand',
    'meta'  => 'Search visibility rising while revenue stays flat? Run four tests on your own data, starting with branded vs non-branded search, and see what to change.',
    'h1'    => 'Search Visibility vs Revenue: Four Tests for Whether SEO Is Creating Demand',
    'lede'  => 'Search visibility counts appearances in results. It does not count buyers. To see whether SEO is creating demand, run four tests in order: how much of the traffic is branded, what the non-branded searches want, whether the searchers fit the companies you can serve, and whether their enquiries become sales conversations. The pattern of passes and fails shows what to change.',
    'kicker' => 'Visibility and revenue',
    'category' => 'SEO', 'intent' => 'Decision Guide', 'date' => '2026-11-14', 'read' => '10 min',
    'service' => ['/seo-services/', 'SEO services'],
    'sections' => [],
    'takeaways' => [],
    'faqs' => [
        ['What is the difference between branded and non-branded search?', 'A branded search contains your company or product name, so the searcher already knew you. A non-branded search describes a need without naming you. Branded traffic shows that people can find you. Non-branded traffic shows whether search is putting you in front of people who did not know you.'],
        ['How do I see branded and non-branded queries in Search Console?', 'Use the branded queries filter in the Performance report. Google made it available to all eligible sites from 11 March 2026. It is offered on top-level properties with enough query volume, and Google says some queries may be misclassified. If you do not have it, filter the query list by your brand name.'],
        ['Is a high share of branded traffic a bad sign?', 'No. It means people know your name and find you easily. It becomes a concern only when you expected search to reach new buyers and nearly all the traffic comes from people who already knew you. Report branded and non-branded search separately so each can be judged for what it is.'],
        ['Why is my organic traffic growing while leads stay flat?', 'Usually for one of four reasons: the growth is branded, the new searches are informational, the visitors are not the companies you can serve, or the enquiries do not pass sales. Test them in that order. Each one points to a different fix, from new pages to changes on the form.'],
    ],
    'cta' => ['Is Your Search Traffic New Demand?', 'Tell us what your Search Console chart and your pipeline show. We will tell you where the gap is likely to be.', 'Talk to a Strategist'],
],

'wordpress-seo-launch-checklist' => [
    'rich'  => 'wordpress-seo-launch-checklist',
    'listicle' => true,
    'title' => 'WordPress SEO Checklist Before Launch: 14 Technical Checks',
    'meta'  => 'Launching a B2B website on WordPress? Run 14 technical checks first: indexing, canonicals, sitemap, robots.txt, speed and forms. Free pass or fail board.',
    'h1'    => 'Search-Ready WordPress for B2B: A Technical Checklist Before You Launch',
    'lede'  => 'Before a WordPress site goes live, run fourteen checks on the built site. Confirm the search visibility setting is off in production, each page has one title and one canonical, there is one sitemap and the intended robots.txt, the main text is in the HTML, speed holds with every script switched on, and forms deliver. Repeat three of the checks on the live address on launch day.',
    'kicker' => 'WordPress launch',
    'category' => 'Web', 'intent' => 'Checklist', 'date' => '2026-11-14', 'read' => '14 min',
    'service' => ['/wordpress-web-design/', 'WordPress web design'],
    'sections' => [],
    'takeaways' => [],
    'faqs' => [
        ['Is WordPress good for SEO?', 'WordPress handles several basics in core: an XML sitemap since version 5.5, a canonical link on single posts and pages, and lazy loading for images. How a site performs in search still depends on how it is built, what it says and whether settings such as search visibility are right at launch.'],
        ['Does the "Discourage search engines" setting block Google?', 'It asks search engines not to index the site. Since WordPress 5.3 it prints a robots meta tag with noindex and nofollow on every page, and it does not add a block to robots.txt. It is a request, so protect a staging site with a password as well.'],
        ['Do I need a plugin to create an XML sitemap in WordPress?', 'Not for a basic one. WordPress has produced a sitemap in core since version 5.5, at /wp-sitemap.xml, with up to 2,000 URLs per file. A plugin can replace it if you need more control. Use one sitemap or the other, not both.'],
        ['How long should redirects stay in place after a relaunch?', 'Google\'s guidance on site moves says to keep redirects for at least a year. It also says to map each old URL to its new one, use permanent server-side redirects and avoid chains, where one redirect leads to another.'],
        ['Why is my new WordPress site not showing in Google?', 'Check three things first. Look for the search visibility setting under Settings, Reading. Search a page\'s source for noindex. Read the live robots.txt for a rule that disallows everything. Then confirm your sitemap has been submitted in Search Console. New pages can still take time to appear.'],
    ],
    'cta' => ['Want a Second Pair of Eyes Before You Launch?', 'Ask for a free website review. We look at the built site against these checks and tell you what to fix first.', 'Get My Free Website Review'],
],

'ai-search-competitor-benchmark' => [
    'rich'  => 'ai-search-competitor-benchmark',
    'listicle' => true,
    'title' => 'How to Benchmark Competitors in AI Search: A Scorecard',
    'meta'  => 'Benchmark competitors in AI search with a fair brand set, neutral prompts, a four-part scorecard and a calculator that shows if a gap is real or noise.',
    'h1'    => 'How to Benchmark Competitors in AI Search: A Repeatable Scorecard',
    'lede'  => 'To benchmark competitors in AI search, fix a set of three to five direct competitors, run the same neutral buyer prompts on each engine, and score every brand on four things: named, cited with a link, described accurately and recommended. Print the number of answers beside every rate. With 90 answers per engine, a gap under about 15 points is noise. Then sort the links cited beside each competitor to find where to earn a mention.',
    'kicker' => 'AI benchmarking',
    'category' => 'AI Search', 'intent' => 'How-to', 'date' => '2026-11-14', 'read' => '11 min',
    'service' => ['/ai-seo/', 'AI SEO services'],
    'sections' => [],
    'takeaways' => [],
    'faqs' => [
        ['How many competitors should an AI search benchmark include?', 'Three to five direct competitors that sell to the same buyer in the same market, plus one larger name as a reference. Add a brand you did not list only under a written rule, such as being named in at least a tenth of the answers for two months running.'],
        ['How many answers do I need before comparing two brands?', 'It depends on the gap you want to detect. With 90 answers for each brand, a gap under about 15 points is inside the margin of error. With 360 answers it is about 7 points. Repeated runs of one prompt are related, so treat these figures as minimums.'],
        ['Should I track which brand an AI assistant names first?', 'No. A study published by SparkToro in January 2026 found that the same prompt almost never returned the same list of brands in the same order. How often a brand is named across many runs is far steadier, so count that.'],
        ['Can Bing Webmaster Tools show how competitors perform in AI answers?', 'Not directly. Its AI Performance report counts citations of your own pages. Citation Share, added in June 2026, shows your share of the citations for a query. Microsoft says it does not reveal competitor domains and does not represent a share of traffic.'],
    ],
    'cta' => ['See Who AI Assistants Name Instead of You', 'Ask for a free AI visibility report. We run buyer prompts across the main assistants and show where you are named and who else is.', 'Get My Free AI Visibility Report'],
],

'linkedin-engagement-vs-buyer-intent' => [
    'rich'  => 'linkedin-engagement-vs-buyer-intent',
    'title' => 'LinkedIn Engagement vs Buyer Intent: Which Signals Matter',
    'meta'  => 'LinkedIn engagement shows interest in a post, not a plan to buy. Sort signals into weak, moderate and strong, record them by account and report to sales.',
    'h1'    => 'Why LinkedIn Engagement Is Not the Same as B2B Buyer Intent',
    'lede'  => 'LinkedIn engagement counts clicks, reactions, comments and shares on a post. It shows that someone found the post interesting. It does not show that their company is buying. Sort LinkedIn signals into weak, moderate and strong, record them by account instead of by post, and treat a strong signal as a reason to start a conversation, not as proof of a deal.',
    'kicker' => 'LinkedIn signals',
    'category' => 'Social', 'intent' => 'Explainer', 'date' => '2026-11-14', 'read' => '10 min',
    'service' => ['/social-media-marketing/', 'social media marketing'],
    'sections' => [],
    'takeaways' => [],
    'faqs' => [
        ['How does LinkedIn calculate engagement rate?', 'For a company Page, LinkedIn divides interactions by impressions. Interactions are clicks, reactions, comments and shares. Follows are counted separately. Ads use a different sum: paid and free clicks divided by total impressions. Posts by a member show engagements and no rate.'],
        ['What is a good LinkedIn engagement rate?', 'LinkedIn publishes the formula and no target, and no independent benchmark with a disclosed method was found for this article. Compare each post with your own past posts of the same kind. Then check who engaged, because a high rate from peers and staff says little about buyers.'],
        ['What does Buyer Intent in Sales Navigator measure?', 'LinkedIn\'s documentation says it combines more than 180 signals: activity on LinkedIn, ad interactions, InMail responses and visits to your website where the Insight Tag is installed. Accounts are graded High, Moderate, Neutral or Negative. LinkedIn stamps that page as about three years old, so check the current product.'],
        ['Can LinkedIn engagement be tied to revenue?', 'Partly. Record signals by account and compare them with opportunities in your CRM, ask buyers how they heard about you, and use LinkedIn\'s Revenue Attribution Report if you advertise and have a supported CRM. Each method shows that engagement came before a deal. None proves it caused the deal.'],
    ],
    'cta' => ['Lots of Reactions, Few Conversations?', 'Ask for a free social media audit. We review your profiles and give you three priority moves.', 'Get My Free Social Media Audit'],
],

'industrial-buyer-search-intent' => [
    'rich'  => 'industrial-buyer-search-intent',
    'title' => 'How Industrial Buyers Search: Six Search Types and Pages',
    'meta'  => 'Industrial buyers search by specification, application, supplier, standard, problem and terms. See the page a manufacturer needs for each search.',
    'h1'    => 'How Industrial Buyers Search: Mapping Their Queries to the Pages a Manufacturer Needs',
    'lede'  => 'Industrial buyers rarely search for a company. They search in six ways: by specification, by application, by supplier and place, by standard or certificate, by problem, and by commercial terms. Each needs its own page: a product family page, an application page, a capability page, a certification page, a technical article and a request for quotation page. List your pages against the six to find the gaps.',
    'kicker' => 'Industrial search',
    'category' => 'SEO', 'intent' => 'Guide', 'date' => '2026-11-14', 'read' => '10 min',
    'service' => ['/lead-gen-seo-for-business-industrial/', 'industrial SEO'],
    'sections' => [],
    'takeaways' => [],
    'faqs' => [
        ['How much research do industrial buyers do before contacting a supplier?', 'In the 2026 State of Marketing to Engineers survey by TREW Marketing and GlobalSpec, engineers said that on average 62 percent of the buying process happens online before they speak to a supplier. That is vendor research with 709 answers to the question, so treat it as an indication, not a measurement.'],
        ['Do engineers use AI tools in place of search engines?', 'Mostly not yet. In the same 2026 survey, 77 percent said they use a search engine more often than a generative AI tool, 8 percent said the reverse and 16 percent use both equally. Respondents rated their trust in AI answers at 4.7 out of 10 on average.'],
        ['Should a manufacturer put datasheets on the page or in a PDF?', 'Both. Keep the PDF for people who want to download it, and put the same specifications on the product page as text and tables. Values on the page are easier for search engines and AI crawlers to read, and for a buyer to compare without opening a file.'],
        ['Does every product need its own page?', 'Usually not. Group products into families a buyer would recognise and give each family a page, with sizes, grades and finishes in a table. A separate page for one product makes sense when buyers search for it by name or when its specification differs enough to need its own explanation.'],
    ],
    'cta' => ['Want Your Pages Mapped to How Your Buyers Search?', 'Tell us what you make and who buys it. We will show you which searches your site answers today and which it does not.', 'Talk to a Strategist'],
],

/* Rebuilt at its October 2026 address. 'date' is the first publication date
   and 'modified' the rebuild, shown in the byline and in the article markup. */
'how-to-measure-ai-search-visibility' => [
    'rich'  => 'how-to-measure-ai-search-visibility',
    'title' => 'How to Measure AI Search Visibility: A Practical Method',
    'meta'  => 'Learn how to measure brand visibility in ChatGPT, Gemini and AI Overviews, using prompt sampling, citation data and first-party reports you can repeat monthly.',
    'h1'    => 'How to Measure AI Search Visibility',
    'lede'  => 'Measure AI search visibility by running a fixed set of 20 to 30 buyer prompts on the same AI engines every month, three times each, and recording how often your brand is named, cited and described correctly. Report the result as a share of answer with its sample size beside it. Then add first-party data from Bing Webmaster Tools, Search Console and analytics, and keep all of it next to your pipeline figures.',
    'kicker' => 'AI visibility',
    'category' => 'AI Search', 'intent' => 'How-to / Tutorial', 'date' => '2026-10-23', 'modified' => '2026-11-14', 'read' => '9 min',
    'service' => ['/ai-seo/', 'AI SEO services'],
    'sections' => [],
    'takeaways' => [],
    'faqs' => [
        ['What is share of answer?', 'The percentage of AI answers, from a fixed set of buyer questions, that name your brand. It is an estimate from a sample, so always quote it with the number of answers it is based on. It is not a market share.'],
        ['How many prompts do I need to track?', 'Start with 20 to 30 buyer questions and run each one three times on every engine you track. That gives 60 to 90 answers per engine, which is enough to see large differences. Grow the set to 40 to 60 prompts once the routine is running.'],
        ['How often should I measure AI search visibility?', 'Monthly is enough for most businesses. Run the same prompts, on the same engines, in the same way each time, so that a change in the number reflects a change in the answers and not in your method.'],
        ['Why do AI answers change between runs?', 'Each answer is generated afresh, so the brands named and their order vary. A study published by SparkToro in January 2026 found that the same prompt almost never returned the same list of brands twice. How often a brand appears across many runs is far steadier, which is why you record a rate.'],
        ['Can Google Search Console show AI Overview traffic separately?', 'Partly. Search Console now has a Generative AI performance report that shows impressions in AI Overviews and AI Mode by page, country and device. It does not show clicks, queries or position, and the same impressions are already counted in the main Performance report.'],
    ],
    'cta' => ['Want This Measured for Your Brand?', 'Ask for a free AI visibility report. We run buyer prompts across the main assistants and show where you are named and what is wrong.', 'Get My Free AI Visibility Report'],
],

];
