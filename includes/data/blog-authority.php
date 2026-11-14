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
