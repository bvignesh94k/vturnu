<?php
/**
 * Reader-page extras for every e-book and guide: the direct answer shown first
 * (what search and AI engines quote), key takeaways, and up to eight FAQs.
 * Merged over $RESOURCES in resources.php.
 */

return [

'ai-search-playbook' => [
    'answer' => ['How do you get your brand cited by AI search engines?', 'AI engines cite sources they can reach, understand and corroborate. Give crawlers access, answer questions directly near the top of each page, state clearly who you are with structured data, and earn mentions on independent sites. Then sample the prompts your buyers use each month and track how often you are named.'],
    'takeaways' => ['Share of answer is the new measure, not sessions alone', 'A page must pass retrieval, authority and corroboration to be cited', 'Answer-first structure beats length', 'Entity clarity decides whether you are a known business or an anonymous URL'],
    'faqs' => [
        ['What is the difference between SEO and AEO?', 'SEO earns rankings in a results page. AEO, answer engine optimization, earns a place inside the answer itself. They share foundations such as crawlability and quality content, but AEO puts more weight on direct answers, clear entities and independent corroboration.'],
        ['Which AI engines does this cover?', 'ChatGPT, Perplexity, Gemini, Copilot and Google AI Overviews. The retrieval logic is similar across them, so the same core work carries over.'],
        ['Can AI citations be measured?', 'Yes. Build a list of the prompts your buyers use, run them on a schedule across each engine, and record whether you are named. Bing Webmaster Tools also reports citations in Copilot.'],
        ['How long until AI visibility improves?', 'Technical and entity fixes can show within weeks, as engines refresh sources. Earning independent corroboration takes longer, usually months.'],
        ['Is this beginner-friendly?', 'Yes. Concepts are explained from zero, and the systems are the same ones we run for clients.'],
    ],
],
'ecommerce-seo-from-crawl-to-cart' => [
    'answer' => ['What is the most important part of ecommerce SEO?', 'Category pages. Most buying searches land on categories, so a clear architecture, controlled faceted navigation and useful category copy usually move revenue faster than anything else. Product schema and reviews then win rich results, and buying guides feed research traffic into the store.'],
    'takeaways' => ['Build the category tree around how buyers search', 'Control facets before they waste crawl budget', 'Treat category pages as landing pages', 'Use guides to route research demand toward products'],
    'faqs' => [
        ['Should filter pages be indexed?', 'Only the combinations with real search demand. The rest should be kept out of the index, or thin duplicates will compete with the pages that matter.'],
        ['What should happen to out-of-stock products?', 'Keep the page live if the product returns, and show alternatives. Redirect to the closest category only when it is gone for good.'],
        ['Does this apply to my platform?', 'Yes. The principles work on Shopify, WooCommerce, Magento and custom stacks, and the e-book notes where behavior differs.'],
        ['Which schema matters for a store?', 'Product with price and availability, plus reviews where they are genuine, and BreadcrumbList for category paths.'],
        ['Is it useful for small catalogs?', 'Yes. Small stores skip the scale problems and get to revenue moves faster.'],
    ],
],
'lead-gen-website-blueprint' => [
    'answer' => ['What makes a website generate leads?', 'A lead generation website gives one clear offer per page, proves it with evidence, and makes the next step easy. Replace "contact us" with a specific offer such as an audit, show proof before you ask, keep forms short but qualifying, and load fast on a phone.'],
    'takeaways' => ['Every page needs one job and one main action', 'A specific offer beats a generic contact button', 'Put proof before the ask', 'Speed and mobile layout are conversion issues'],
    'faqs' => [
        ['How many form fields should a lead form have?', 'As few as you need to qualify. Fewer fields bring more leads, while a few well-chosen ones bring better leads. Choose based on whether your sales team is short of leads or short of good ones.'],
        ['Which pages does a service business need?', 'A home page, a page per service, proof such as case studies, an about page and a contact page with a clear offer.'],
        ['Should pricing be on the website?', 'Showing a starting price usually filters out poor-fit enquiries and builds trust, even for custom work.'],
        ['How do I stop junk leads?', 'Combine a hidden trap field, a captcha, a time check and required business details, then screen the content. Store flagged leads rather than deleting them.'],
        ['How fast should the site be?', 'Aim to pass Core Web Vitals, including a Largest Contentful Paint under 2.5 seconds on a phone.'],
    ],
],
'paid-media-budgeting-for-founders' => [
    'answer' => ['How much should a founder spend on paid ads?', 'Work backward from unit economics. Estimate what a customer is worth, set the most you can pay to win one, then divide by your conversion rates to find an affordable cost per click. Fund a test large enough to collect meaningful data, and scale only what stays inside that limit.'],
    'takeaways' => ['Start from customer value, not a round number', 'Budget a test by the conversions it needs', 'Fix tracking before spending', 'Scale in steps and watch marginal cost'],
    'faqs' => [
        ['What is a good ROAS?', 'It depends on your margin. A brand with thin margins may need a much higher return than a software company, so calculate break-even ROAS first.'],
        ['How long should I test before judging a campaign?', 'Long enough to collect meaningful conversions, often a few weeks. Small budgets take longer to give a reliable answer.'],
        ['Google Ads or Meta first?', 'Google captures existing demand, Meta creates it. If people already search for what you sell, start with Google.'],
        ['What share of budget goes to testing?', 'Many teams reserve a tenth to a fifth for new audiences and creative, and keep the rest on what is proven.'],
        ['When should I stop a campaign?', 'When it stays above your target cost after you have fixed tracking, landing page and targeting. Stop on evidence, not one bad week.'],
    ],
],
'local-seo-for-multi-location' => [
    'answer' => ['How do multi-location businesses rank locally?', 'Each location needs its own verified Google Business Profile, a genuine location page with unique content, consistent name, address and phone data, and a steady flow of reviews. Central teams set the standards, while each location supplies the local detail that generic templates cannot.'],
    'takeaways' => ['One verified profile per real location', 'Location pages must be genuinely unique', 'Consistency needs an owner', 'Reviews are a process, not a campaign'],
    'faqs' => [
        ['Do I need a page for every location?', 'Yes, if it is a real location that serves customers. Each page should have local detail, not a swapped city name.'],
        ['Can one Google Business Profile cover several branches?', 'No. Each eligible location should have its own profile.'],
        ['Who should manage reviews?', 'Set the process centrally and let each location ask for and respond to reviews, using shared guidance.'],
        ['How do I track rankings by location?', 'Use geo-grid tracking, because a business ranks differently street by street.'],
        ['What about service areas with no office?', 'Profiles need to follow Google\'s service-area rules. Do not create addresses you do not operate from.'],
    ],
],
'marketing-analytics-starter' => [
    'answer' => ['What should a small business track in marketing analytics?', 'Start with what connects to revenue: leads by source, qualified leads, cost per qualified lead and closed revenue by channel. Set up conversion tracking and a simple CRM link first, then build one dashboard that answers which channel is worth more money.'],
    'takeaways' => ['Track conversions before traffic', 'Connect marketing data to sales outcomes', 'One dashboard, one source of truth', 'Decide what each number will change'],
    'faqs' => [
        ['Is GA4 enough?', 'For site behavior, usually yes. For revenue, connect it to your CRM, because GA4 alone cannot see which lead became a customer.'],
        ['What is a good attribution model?', 'Data-driven where you have the volume. For small accounts, compare first-touch and last-touch and treat both as directional.'],
        ['How often should I review the numbers?', 'Weekly for paid campaigns and monthly for strategy.'],
        ['Why do GA4 and ad platforms disagree?', 'They use different attribution rules and time windows. Pick one source for each decision rather than trying to make them match.'],
        ['What is the first thing to fix?', 'Accurate conversion tracking on your main lead form or checkout. Everything else depends on it.'],
    ],
],
'website-speed-handbook' => [
    'answer' => ['How do you make a website faster?', 'Reduce what the browser must do before the main content appears. Delay third-party tags, compress and size images, trim render-blocking CSS, serve fonts efficiently and keep layout stable. Then measure with PageSpeed Insights and real-user data rather than a single lab score.'],
    'takeaways' => ['Third-party tags are usually the biggest cost', 'Reserve space to prevent layout shift', 'Lab scores vary, field data decides', 'Speed work must be measured page type by page type'],
    'faqs' => [
        ['What are Core Web Vitals?', 'Three measures of real experience: Largest Contentful Paint for loading, Interaction to Next Paint for responsiveness and Cumulative Layout Shift for visual stability.'],
        ['What scores do I need to pass?', 'LCP under 2.5 seconds, INP under 200 milliseconds and CLS under 0.1, measured at the 75th percentile of real visits.'],
        ['Why is my lab score high but Core Web Vitals fail?', 'Lab tests simulate one device. Core Web Vitals use 28 days of real visitors, so results also move slowly after a fix.'],
        ['Does speed affect rankings?', 'It is a small ranking factor, but it has a real effect on conversions, especially on mobile.'],
        ['Is a page builder too slow?', 'It can be. Heavy builders add scripts and markup, and a well-tuned build is often faster than a theme with many plugins.'],
    ],
],
'whatsapp-marketing-playbook' => [
    'answer' => ['How do businesses use WhatsApp for marketing?', 'Businesses use the WhatsApp Business Platform to send opted-in customers useful messages such as order updates, reminders and offers, and to answer enquiries quickly. It works when customers have explicitly agreed to hear from you, and messages are timely and relevant rather than broadcast at everyone.'],
    'takeaways' => ['Opt-in is the foundation', 'Utility messages earn permission for offers', 'Speed of reply is the sales advantage', 'Segment instead of broadcasting'],
    'faqs' => [
        ['Do I need the WhatsApp Business API?', 'For automation, templates and scale, yes, usually through an official provider. A small business can start with the WhatsApp Business app.'],
        ['Can I message people who have not opted in?', 'You should not. Messaging without consent risks blocks and account limits, and it damages trust.'],
        ['How often should we message customers?', 'Only as often as you have something useful. Watch block rates and ease off when they rise.'],
        ['What does WhatsApp marketing cost?', 'Meta charges by message type and country, and providers add a fee. Check current pricing for your country.'],
        ['Is it suitable for B2B?', 'Yes, particularly in India, the Gulf and Southeast Asia, where buyers often prefer it to email.'],
    ],
],
'brand-identity-for-growth' => [
    'answer' => ['What makes a strong brand identity?', 'A strong brand identity is a clear position expressed consistently. It starts with who you serve and why you are different, and then carries that through your name, logo, color, type and tone of voice, with guidelines that keep every touchpoint recognizable as you grow.'],
    'takeaways' => ['Position first, design second', 'Consistency builds recognition', 'A brand system beats a one-off logo', 'Guidelines only work when someone owns them'],
    'faqs' => [
        ['What is the difference between a brand and a logo?', 'A logo is one mark. A brand is what people believe about you, built through every experience they have with your business.'],
        ['Do we need a full rebrand?', 'Often not. Many businesses get most of the benefit from tightening their positioning and applying what exists consistently.'],
        ['What should brand guidelines include?', 'Logo use, color, typography, imagery, tone of voice and real examples.'],
        ['When is rebranding the right move?', 'When the business has changed, you are entering new markets or the brand no longer matches what you sell.'],
        ['How does branding affect marketing results?', 'A recognized brand tends to win more clicks, convert better and need less persuasion, which lowers the cost of every other channel.'],
    ],
],
'business-content-engine' => [
    'answer' => ['How do you build a content engine for a business?', 'Choose a few topics tied to what you sell, map them to buyer questions, and set a publishing rhythm your team can sustain. Use a repeatable brief, an editor and a distribution plan, then measure which content influences leads and update what is working.'],
    'takeaways' => ['Fewer topics, deeper coverage', 'A repeatable brief improves every piece', 'Distribution is half the work', 'Refreshing old content often beats publishing new'],
    'faqs' => [
        ['How often should a business publish?', 'As often as you can sustain with quality. One excellent article a fortnight usually beats daily thin posts.'],
        ['Who should write the content?', 'Subject experts supply the insight and writers shape it. Interviewing your own experts is often the fastest route.'],
        ['Can AI write our content?', 'It can speed up research and drafting, but people must add experience, check facts and edit. Unreviewed AI text rarely earns trust.'],
        ['How do I measure content ROI?', 'Track assisted conversions and pipeline influenced, not just pageviews.'],
        ['When should old content be updated?', 'When traffic drops, facts change or a page ranks just off page one.'],
    ],
],
'complete-guide-to-local-seo' => [
    'answer' => ['What is local SEO?', 'Local SEO helps a business appear when nearby people search for what it offers, especially in the Google map pack. Rankings depend on relevance, distance and prominence, so the work centers on your Google Business Profile, reviews, consistent listings and strong location pages.'],
    'takeaways' => ['You influence relevance and prominence, not distance', 'Your Google Business Profile is your most important local asset', 'Review velocity matters as well as rating', 'Measure by neighborhood with geo-grid tracking'],
    'faqs' => [
        ['How long does local SEO take?', 'Profile fixes can move rankings within weeks. Competitive map-pack positions usually take a few months of steady work.'],
        ['Do reviews affect local rankings?', 'Yes. Quantity, quality and recency all contribute, and they strongly influence who gets the click.'],
        ['What is NAP consistency?', 'Keeping your name, address and phone number identical across your site and major listings, so search engines trust the data.'],
        ['Should I add keywords to my business name?', 'No. Stuffing your profile name breaks Google guidelines and can get a profile suspended.'],
        ['Do service-area businesses need location pages?', 'Pages for areas you truly serve can help when they contain real local information. Near-identical pages do not.'],
    ],
],
'complete-guide-to-ai-seo' => [
    'answer' => ['What is AI SEO?', 'AI SEO is the practice of making a brand visible inside AI-generated answers from ChatGPT, Perplexity, Gemini, Copilot and Google AI Overviews. It builds on classic SEO, then adds answer-first content, clear entity signals and independent corroboration, so engines can find, trust and quote you.'],
    'takeaways' => ['AI SEO extends SEO rather than replacing it', 'Engines retrieve, assess, then synthesize', 'Corroboration across independent sites builds trust', 'Start with a 90-day plan and measure share of answer'],
    'faqs' => [
        ['Is AI SEO different from GEO?', 'They are close to the same thing. GEO, generative engine optimization, is another name for earning visibility in AI-generated answers.'],
        ['Do I need llms.txt?', 'It is a low-cost addition, but no major engine has confirmed it as a ranking signal. Crawl access and clear content matter far more.'],
        ['Does schema help with AI search?', 'It helps machines understand your entity and content. It supports visibility, and it will not win citations by itself.'],
        ['Should I block AI crawlers?', 'If you want to be cited, allow the crawlers behind the engines your buyers use. Blocking them removes you from the answers.'],
        ['How do I start?', 'Check crawler access, fix entity consistency, rewrite your top pages to answer first, and begin monthly prompt sampling.'],
    ],
],
'complete-guide-to-content-strategy' => [
    'answer' => ['What is a content strategy?', 'A content strategy is a plan for what you publish, who it is for and how it supports business goals. It defines your audience, the topics you will own, the formats and rhythm, how content gets distributed, and how success is measured against leads and revenue.'],
    'takeaways' => ['Begin with business goals and buyer questions', 'Own topic clusters instead of isolated keywords', 'Plan distribution before writing', 'Audit and update on a schedule'],
    'faqs' => [
        ['What is a topic cluster?', 'A main pillar page linked with related articles that cover a subject in depth, which signals expertise and helps people navigate.'],
        ['What is the difference between content strategy and content marketing?', 'Strategy decides what to create and why. Content marketing is the execution and distribution of it.'],
        ['How do I find content ideas?', 'Ask sales and support what buyers really ask, then validate with search data.'],
        ['How long is the right length for an article?', 'As long as it takes to answer the question well. Length alone does not rank.'],
        ['How often should strategy be reviewed?', 'Quarterly, using what the data shows about what drove leads.'],
    ],
],
'complete-guide-to-ecommerce-platforms' => [
    'answer' => ['Which ecommerce platform is best?', 'There is no single best platform. Shopify suits most brands that want speed and low maintenance, WooCommerce suits WordPress users who want control, Magento suits large or complex catalogs, BigCommerce suits growing multichannel sellers, and headless suits teams with engineering resources and demanding front ends.'],
    'takeaways' => ['Choose by catalog, team and growth plan', 'Count total cost of ownership, not the subscription', 'Check SEO and speed limits before committing', 'Plan the migration before you need it'],
    'faqs' => [
        ['Is Shopify or WooCommerce better for SEO?', 'Both can rank well. WooCommerce gives more control, while Shopify has fewer moving parts and URL conventions you must accept.'],
        ['When is Magento worth it?', 'When you have a large catalog, complex B2B rules and the budget for specialist development.'],
        ['What is headless commerce?', 'A separate front end connected to a commerce back end. It offers speed and flexibility, at a higher build and maintenance cost.'],
        ['How do I migrate without losing rankings?', 'Map every old URL to its new one, keep metadata, apply 301 redirects and monitor Search Console after launch.'],
        ['What hidden costs should I check?', 'Apps, transaction fees, themes, developer time and hosting at your real traffic level.'],
    ],
],
'complete-guide-to-business-lead-gen-seo' => [
    'answer' => ['How does SEO generate B2B leads?', 'B2B lead generation SEO targets the searches that signal buying intent, such as service, comparison and pricing queries, and sends them to pages built to convert. It then measures success in qualified leads and pipeline by connecting search data to your CRM, rather than in rankings alone.'],
    'takeaways' => ['Prioritize bottom-of-funnel keywords first', 'Service pages carry most of the revenue', 'Comparison and pricing content attracts real buyers', 'Report in pipeline, not positions'],
    'faqs' => [
        ['What is a good first keyword to target?', 'Your core service plus a commercial modifier, even at low volume. In B2B, a few high-intent searches can be worth more than thousands of visits.'],
        ['How long does B2B SEO take to produce leads?', 'Early leads can come within months on lower-competition terms. Competitive categories usually take longer.'],
        ['Should I publish pricing pages?', 'Yes, where possible. Buyers search for pricing, and a helpful page filters out poor fits.'],
        ['How do I measure SEO leads?', 'Track form and call conversions by landing page, and pass the source into your CRM so you can see what closed.'],
        ['Does AI search hurt B2B SEO?', 'It changes it. Informational traffic falls first, while being cited in AI answers makes the case for clear, expert content.'],
    ],
],
'complete-guide-to-google-ads' => [
    'answer' => ['How do you run profitable Google Ads?', 'Start with accurate conversion tracking and a break-even target. Structure campaigns by intent, write ads that match each search, send clicks to a page built for that offer, and optimize on qualified conversions. Automated bidding works best once it has reliable conversion data.'],
    'takeaways' => ['Tracking comes before budget', 'Structure by intent, not by product list', 'Landing pages decide much of your cost', 'Feed Smart Bidding qualified conversions'],
    'faqs' => [
        ['How much should I spend on Google Ads?', 'Enough to collect meaningful conversion data at your market\'s cost per click. Work out your target cost per lead first.'],
        ['Is Performance Max good for lead generation?', 'It can work, but it needs strong conversion signals and exclusions, or it may chase low-quality leads. Test it beside search campaigns.'],
        ['What is Quality Score?', 'Google\'s rating of how relevant your ad, keyword and landing page are. A higher score tends to lower cost per click.'],
        ['Should I bid on my own brand?', 'Usually yes, particularly if competitors bid on it. It is cheap and protects your traffic.'],
        ['How soon can I expect results?', 'Data arrives immediately. Expect a few weeks of learning before performance settles.'],
    ],
],

];
