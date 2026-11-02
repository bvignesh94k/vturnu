<?php
/**
 * October 2026 articles: the new Enterprise & IT services and advanced B2B
 * marketing. Same fields as includes/data/blog.php. Merged ahead of the
 * existing posts. Concept and banner per post are set in bin/build-og.mjs.
 */

return [

'ai-first-vs-data-first-marketing' => [
    'rich'  => 'ai-first-vs-data-first-marketing',
    'title' => 'AI-First vs Data-First Marketing: What Drives B2B Growth?',
    'meta'  => 'AI-first or data-first marketing? See how each affects B2B pipeline, what breaks without reliable data, and a 90-day plan to connect both to revenue.',
    'h1'    => 'AI-First vs. Data-First Marketing: What Drives B2B Growth?',
    'lede'  => 'AI-first marketing makes AI the default way work gets done. Data-first marketing makes reliable evidence the default way decisions get made. For B2B growth they are a sequence, not a choice: fix the data behind one revenue decision, apply AI to that decision, keep people accountable for the output, and measure the result in qualified pipeline.',
    'category' => 'Strategy', 'intent' => 'Comparison', 'date' => '2026-11-02', 'read' => '13 min',
    'sections' => [],
    'takeaways' => [],
    'faqs' => [
        ['Is AI-first marketing the same as marketing automation?', 'No. Marketing automation follows rules your team writes, such as sending an email when a form is filled in. AI-first marketing uses models that draft, predict or recommend inside a workflow designed around them. Many teams run both, with automation moving the work and AI improving what moves.'],
        ['How much data do you need before using AI in marketing?', 'It depends on the use case. Research and drafting need very little of your own data. Predictive lead scoring needs a meaningful history of closed deals, including lost ones, because the model learns from outcomes. Check the minimum your vendor requires, then test it on your own records before you trust it.'],
        ['Can a small B2B team do this without a data warehouse?', 'Yes. A well-kept CRM, consistent lifecycle stages and reliable source tracking cover most early needs. A data warehouse becomes useful once several systems need to be combined for reporting.'],
        ['Who should own AI governance in a marketing team?', 'A named marketing leader should be accountable, with input from revenue operations, IT and legal. Keep the rules short: which tools are approved, what data can enter them and who reviews what before it goes live.'],
        ['Will AI-written content hurt our search rankings?', 'Not because AI was used. Google\'s guidance focuses on whether content is helpful, and warns against generating many pages without adding value. Content still needs real expertise, accurate facts and a human editor.'],
        ['How long does it take to see a return from AI in marketing?', 'Time savings can appear within weeks. Pipeline and revenue effects take at least one full sales cycle, so set expectations by sales cycle and not by the date a tool went live.'],
    ],
    'cta' => ['Ready to Build a Smarter B2B Growth Strategy?', 'Tell VTurnU about your goals and challenges.', 'Get My Custom Growth Plan'],
],

'how-to-stop-junk-leads-on-your-website' => [
    'title' => 'How to Stop Junk Leads on Your Website: A B2B Playbook',
    'meta'  => 'Fake enquiries waste sales time. Learn the four types of junk lead, how to spot the brand-impersonation scam, and how to filter leads without losing real ones.',
    'h1'    => 'How to Stop Junk Leads on Your Website',
    'lede'  => 'Junk leads come in four types: gibberish bots, spam links, empty bot submissions and fake brand partnership scams. Stopping them takes layers: hidden traps and a captcha for bots, required business details for quality, and a content screen that quarantines suspect leads instead of deleting them, so a real buyer is never lost.',
    'category' => 'Conversion', 'intent' => 'Problem and Solution', 'date' => '2026-10-30', 'read' => '8 min',
    'sections' => [
        ['The four kinds of junk lead', [
            'Not all junk is the same, and that matters, because each type gets past a different defense. These patterns come from real submissions to B2B websites, including our own.',
        ], [
            'Gibberish bots: random strings in the name and message, a throwaway email domain, and a company field filled with a word like "google".',
            'Link spam: a short message with a tinyurl or similar link, often promoting adult or gambling sites.',
            'Empty bots: a generated name and a message that just says "Hello!", usually with a phone number that cannot exist.',
            'Brand impersonators: fluent English from someone claiming to represent a famous brand, using a lookalike domain. People, not bots, send these.',
        ]],
        ['The scam that looks like your best lead', [
            'The fourth type deserves its own warning. A message arrives from "the marketing lead" at a household brand, offering a long-term agency partnership and a large monthly budget. The email address looks right at a glance, such as brandname-llc.com, brandnamein.com or brandnamework.com. It closes by asking you to reply to the address in the form, and says no proposal is needed yet.',
            'That last line is the tell. Real procurement teams want proposals. These messages want a reply, and what follows is usually a link or a file. Treat them as phishing: check the domain against the brand\'s real website, and never open attachments from them.',
        ], [
            'A domain that contains the brand name but is not the brand\'s website',
            'The same text as the company name repeated in the job title field',
            'Phrases such as "agency partnership" and "reply to the email address provided in this form"',
            'A domain registered only recently, which a public RDAP lookup will show',
        ]],
        ['Why a captcha alone will not fix this', [
            'reCAPTCHA is good at spotting scripts, and it should stay. But a scam written by a person passes it easily, because a person is filling in the form. A captcha also says nothing about whether a real visitor is a real buyer.',
            'Think of lead quality as layers, each stopping what the one before it missed.',
        ], [], [
            'caption' => 'Which layer stops which kind of junk',
            'head' => ['Layer', 'What it does', 'Stops'],
            'rows' => [
                ['Hidden trap field', 'A field people cannot see and bots fill in', 'Simple bots'],
                ['Time and behavior check', 'Flags forms filled in seconds with no typing', 'Scripted bots'],
                ['reCAPTCHA', 'Scores how human the session looks', 'Most automated traffic'],
                ['Required details', 'Asks for company, phone and a real need', 'Low-effort submissions'],
                ['Email checks', 'Rejects throwaway domains and domains with no mail server', 'Fake addresses'],
                ['Content screen', 'Scores names, wording, links and lookalike domains', 'Spam and impersonators'],
            ],
        ]],
        ['Quarantine, never delete', [
            'The most expensive mistake in spam filtering is losing a real lead. A filter will occasionally get it wrong, so nothing should be silently thrown away. Send suspect leads to a separate junk view that someone checks weekly, with one click to restore.',
            'Show the visitor the normal thank-you page either way. If a bot sees an error, its operator learns how to get around your filter. If it sees a success page, it learns nothing.',
        ]],
        ['Ask better questions on the form', [
            'Removing fields raises lead volume, while adding the right ones raises lead quality. If your sales team is short of good conversations rather than short of leads, ask for a work email, company name, phone number and what the person needs. Someone with a real project answers these in seconds. A spammer often gives up or gives themselves away.',
        ]],
        ['Measure the filter itself', [
            'Review the junk view regularly. If you find a real lead there, adjust the rule that caught it. If junk keeps reaching sales, add its pattern. A good filter is maintained, not installed once.',
        ]],
    ],
    'takeaways' => [
        'Junk has four types, and each needs a different defense',
        'Fake brand partnership emails are phishing, so check the domain before replying',
        'A captcha cannot stop a person, so screen the content too',
        'Quarantine suspect leads rather than deleting them',
    ],
    'faqs' => [
        ['Why am I getting so many fake leads from my website?', 'Bots crawl the web looking for forms to submit, and scammers target businesses that advertise services. Any public form will attract them, and the volume usually rises as your site gains visibility.'],
        ['Are agency partnership emails from big brands real?', 'Often not. If the address is not on the brand\'s real domain and the message asks you to reply without a proposal, treat it as phishing.'],
        ['Will more form fields reduce my leads?', 'Slightly, but the leads you keep are better. Choose based on whether your team needs more leads or better ones.'],
        ['Should junk leads be deleted?', 'No. Store them separately, so a wrongly flagged buyer can be recovered.'],
        ['Does reCAPTCHA stop all spam?', 'No. It stops most automated traffic, but not people, and it should be one layer among several.'],
    ],
    'cta' => ['Want better leads, not just more leads?', 'We build lead generation websites with quality filtering built in, so your sales team only speaks to real buyers.', 'Fix My Lead Quality'],
],

'seo-vs-aeo-vs-geo-explained-for-business-leaders' => [
    'title' => 'SEO vs AEO vs GEO: What Business Leaders Need to Know',
    'meta'  => 'SEO, AEO and GEO explained in plain English: how each works, how they differ, how to measure them and where a business should put its budget in 2026.',
    'h1'    => 'SEO vs AEO vs GEO, Explained for Business Leaders',
    'lede'  => 'SEO earns a place in search results, AEO earns a place as the direct answer, and GEO earns a place inside AI-generated responses. They are not rivals. All three rely on the same foundations, so most businesses should strengthen their SEO, then add answer-first content and clear brand signals to win AI visibility.',
    'category' => 'AI Search', 'intent' => 'Comparison', 'date' => '2026-10-28', 'read' => '7 min',
    'sections' => [
        ['Three terms, one buyer journey', [
            'Your buyer may search Google, read an AI Overview, ask ChatGPT for a shortlist and only then visit your site. Each step is a different surface, and these three terms describe how you show up on each one.',
        ], [], [
            'caption' => 'SEO, AEO and GEO at a glance',
            'head' => ['', 'SEO', 'AEO', 'GEO'],
            'rows' => [
                ['Goal', 'Rank in results', 'Be the direct answer', 'Be named in AI responses'],
                ['Where', 'Google and Bing results', 'Snippets, voice, AI Overviews', 'ChatGPT, Gemini, Perplexity, Copilot'],
                ['Win with', 'Relevance and authority', 'Clear, direct answers', 'Entity clarity and corroboration'],
                ['Measure with', 'Rankings, clicks, leads', 'Snippet and Overview presence', 'Share of answer, citations'],
            ],
        ]],
        ['What actually changes', [
            'The fundamentals carry over. Crawlers still need to reach your pages, and quality still matters. What changes is the weight given to three things.',
        ], [
            'Directness: state the answer in the first lines, then explain it. Engines quote what they can lift cleanly.',
            'Entity clarity: make it unmistakable who you are, what you do and where you operate, with consistent facts everywhere.',
            'Corroboration: AI engines trust claims that independent sources repeat. Being mentioned on respected sites matters even without a link.',
        ]],
        ['What to stop worrying about', [
            'New terms bring new myths. You do not need a separate AI website, and no one can buy their way into ChatGPT answers. An llms.txt file is cheap to add, but no major engine has confirmed it as a ranking signal, so treat it as housekeeping rather than strategy.',
        ]],
        ['How to measure AI visibility', [
            'You cannot read rankings from an AI answer, but you can sample it. List the twenty or so questions your buyers really ask, run them monthly across each engine, and record whether you are named and who else is. That gives you a share of answer you can track over time.',
            'Bing Webmaster Tools now reports citations in Copilot and Bing AI answers, which gives you real first-party data. For one of our clients, a US used auto parts seller, it showed 10.7K citations in a single report.',
        ]],
        ['Where should the budget go?', [
            'If your SEO foundations are weak, fix those first, because everything else depends on them. If they are sound, move part of your content budget to answer-first pages and part to earning mentions on trusted sites. Treat AI visibility as an extension of search, not as a separate line of work.',
        ]],
    ],
    'takeaways' => [
        'SEO, AEO and GEO are layers of one discipline',
        'Directness, entity clarity and corroboration matter most for AI',
        'Sample buyer prompts monthly to measure share of answer',
        'Fix SEO foundations before spending on AI visibility',
    ],
    'faqs' => [
        ['Is AEO the same as GEO?', 'They overlap heavily. AEO grew up around featured snippets and voice answers, while GEO refers to generative AI. In practice the same work serves both.'],
        ['Will AI search replace SEO?', 'Not soon. AI engines depend on the indexed web, so SEO remains the foundation.'],
        ['Can I pay to appear in ChatGPT answers?', 'Organic answers cannot be bought. Visibility comes from being a trusted, clear source.'],
        ['How long does GEO take?', 'Technical and entity fixes can show within weeks, while earning independent mentions takes months.'],
    ],
    'cta' => ['Is your brand named when buyers ask AI?', 'We check how the main AI engines present your brand and show you what to fix first.', 'Review My AI Visibility'],
],

'how-to-measure-ai-search-visibility' => [
    'title' => 'How to Measure AI Search Visibility: A Practical Method',
    'meta'  => 'A practical way to measure AI search visibility: build a prompt set, track share of answer, read Bing citation data, and report it to leadership each month.',
    'h1'    => 'How to Measure AI Search Visibility',
    'lede'  => 'Measure AI search visibility by tracking a fixed set of buyer prompts across AI engines each month and recording how often your brand is named. Add first-party data from Bing Webmaster Tools and AI-referred traffic in your analytics, and report share of answer alongside leads, not in place of them.',
    'category' => 'AI Search', 'intent' => 'How-to / Tutorial', 'date' => '2026-10-23', 'read' => '6 min',
    'sections' => [
        ['Step 1: build your prompt set', [
            'Start with twenty to thirty real questions. Get them from sales calls, support tickets and search data, not from a keyword tool alone. Mix them across the journey.',
        ], [
            'Category questions: "best [category] companies in [market]"',
            'Comparison questions: "[you] vs [competitor]"',
            'Problem questions: "how do I fix [problem]"',
            'Brand questions: "what is [your company] known for"',
        ]],
        ['Step 2: run them consistently', [
            'Run the same prompts on the same engines every month: ChatGPT, Gemini, Perplexity, Copilot and Google AI Overviews. AI answers vary, so run each prompt more than once and record a rate rather than a yes or no.',
        ]],
        ['Step 3: record what matters', [
            'For each prompt, capture four things. Were you named? Were you cited with a link? Who else was named? Was what it said about you accurate? Inaccurate statements are a finding in themselves, because they point to weak or conflicting information about your brand.',
        ]],
        ['Step 4: add first-party data', [
            'Prompt sampling is an estimate, so back it with data you own.',
        ], [], [
            'caption' => 'Where to find AI visibility data',
            'head' => ['Source', 'What it tells you'],
            'rows' => [
                ['Bing Webmaster Tools', 'Citations in Copilot and Bing AI answers'],
                ['Google Search Console', 'Clicks and impressions, which include AI features in search'],
                ['GA4 referral traffic', 'Visits from chatgpt.com, perplexity.ai and similar'],
                ['Server logs', 'Which AI crawlers are visiting, and how often'],
                ['Sales conversations', '"How did you hear about us?" now often includes AI tools'],
            ],
        ]],
        ['Step 5: report it properly', [
            'Show leadership share of answer against your top competitors, the trend over time, and a short list of fixes. Keep it next to pipeline, rather than replacing it. AI visibility is a leading signal, and leads are still the outcome.',
        ]],
    ],
    'takeaways' => [
        'Use real buyer questions, not only keywords',
        'Run prompts repeatedly and record a rate',
        'Accuracy of what AI says is a metric too',
        'Report share of answer beside pipeline',
    ],
    'faqs' => [
        ['What is share of answer?', 'The percentage of relevant AI answers that name your brand. It works like share of voice for AI search.'],
        ['How often should I measure?', 'Monthly is enough for most businesses, because engines change gradually.'],
        ['Can Google Search Console show AI Overview traffic separately?', 'Search Console includes AI features within its overall search data, so check its reports for what is currently broken out.'],
        ['Why do results change between runs?', 'AI answers are generated each time, so some variation is normal. That is why you sample and record a rate.'],
    ],
    'cta' => ['Want this measured for your brand?', 'We track buyer prompts across AI engines and turn the results into a monthly plan.', 'Get My AI Visibility Plan'],
],

'what-is-sap-cpq-and-when-do-you-need-it' => [
    'title' => 'What Is SAP CPQ and When Does Your Business Need It?',
    'meta'  => 'SAP CPQ explained: what Configure, Price, Quote software does, the signs you have outgrown spreadsheets, how it connects to CRM and ERP, and how to start.',
    'h1'    => 'What Is SAP CPQ, and When Do You Need It?',
    'lede'  => 'SAP CPQ (Configure, Price, Quote) is cloud software that guides sales teams through valid product options, applies pricing and approval rules, and generates accurate quotes. You need it when your products are configurable, quotes take days, errors reach customers, or discounting has become hard to control.',
    'category' => 'Enterprise IT', 'intent' => 'Informational', 'date' => '2026-10-26', 'read' => '7 min',
    'sections' => [
        ['What does CPQ actually do?', [
            'CPQ software takes the three hardest parts of selling a complex product and turns them into a guided process.',
        ], [
            'Configure: the salesperson chooses options, and rules allow only combinations that can really be built or delivered.',
            'Price: list prices, volume tiers, discounts and margins are applied automatically from a central source.',
            'Quote: an approved, branded quote document is generated, and the deal can pass through to the order system.',
        ]],
        ['Signs you have outgrown spreadsheets', [
            'Most companies do not go looking for CPQ. They arrive at it because something hurts.',
        ], [
            'A non-standard quote takes days and needs an engineer or pricing specialist',
            'Quotes go out with invalid configurations or old prices',
            'Discounts are approved over email, and no one sees the margin impact',
            'Only one or two people really understand the pricing logic',
            'New sales staff take months to quote on their own',
        ]],
        ['How SAP CPQ fits your systems', [
            'CPQ sits between your CRM and your ERP. A deal starts in the CRM, such as SAP Sales Cloud or Salesforce. It is configured and priced in CPQ. Once won, the order passes to an ERP such as SAP S/4HANA. Good integration means nobody re-keys anything, and that is where a lot of the value is.',
        ]],
        ['What makes a project succeed', [
            'CPQ projects rarely fail on technology. They struggle when companies try to model every exception on day one, or when the sales team is not involved.',
        ], [], [
            'caption' => 'CPQ projects: what helps and what hurts',
            'head' => ['Approach', 'Better', 'Riskier'],
            'rows' => [
                ['Scope', 'Start with one product line', 'Model everything at once'],
                ['Pricing', 'Simplify the rules first', 'Automate every historic exception'],
                ['Users', 'Test with real quotes', 'Reveal it at go-live'],
                ['Custom scripts', 'Use where rules run out', 'Script by default'],
            ],
        ]],
        ['How to get started', [
            'Gather your product list, your price list and a few real quotes, including the most painful one. Map who approves what. Then decide what a first phase should prove. A narrow first release that sales actually uses is worth far more than a broad one they avoid.',
        ]],
    ],
    'takeaways' => [
        'CPQ guides configuration, pricing and quoting in one flow',
        'It earns its place when quotes are slow, wrong or hard to control',
        'Integration with CRM and ERP drives much of the value',
        'Start with one product line and real quotes',
    ],
    'faqs' => [
        ['What does CPQ stand for?', 'Configure, Price, Quote.'],
        ['Is SAP CPQ only for SAP customers?', 'No. It can connect to non-SAP CRM and ERP systems, including Salesforce.'],
        ['How long does SAP CPQ take to implement?', 'It depends on product complexity and integrations. A focused first phase typically takes a few months.'],
        ['Do small companies need CPQ?', 'Usually only if their products are genuinely configurable. Simple catalogs rarely need it.'],
    ],
    'cta' => ['Is quoting slowing your sales down?', 'Tell us about your products and systems. A consultant will reply within one working day.', 'Talk to a SAP CPQ Consultant'],
],

'odoo-vs-erpnext-which-erp-fits-your-business' => [
    'title' => 'Odoo vs ERPNext: Which ERP Fits Your Business?',
    'meta'  => 'Odoo vs ERPNext compared: licensing, modules, customization, hosting and total cost, with clear advice on which open-source ERP suits your business.',
    'h1'    => 'Odoo vs ERPNext: Which ERP Fits Your Business?',
    'lede'  => 'Choose Odoo if you want a very large app ecosystem, polished user experience and paid vendor support. Choose ERPNext if you want a fully open-source ERP with no per-user fee and full control of your data. For most growing companies, the deciding factors are your processes, your IT capability and your total cost over five years.',
    'category' => 'Enterprise IT', 'intent' => 'Comparison', 'date' => '2026-10-21', 'read' => '8 min',
    'sections' => [
        ['The short comparison', [
            'Both are modern, modular systems built with Python, and both cover accounting, inventory, sales, purchasing, manufacturing and HR. The differences lie in how they are licensed and supported.',
        ], [], [
            'caption' => 'Odoo and ERPNext compared',
            'head' => ['Factor', 'Odoo', 'ERPNext'],
            'rows' => [
                ['License', 'Community free, Enterprise paid per user', 'Open source, no seat fee'],
                ['Stack', 'Python, PostgreSQL', 'Python on Frappe, MariaDB'],
                ['Ecosystem', 'Very large app store and partner network', 'Smaller, focused community'],
                ['User experience', 'Polished and broad', 'Clean and functional'],
                ['Hosting', 'Odoo Online, Odoo.sh or self-hosted', 'Frappe Cloud or self-hosted'],
                ['Customization', 'Modules', 'Apps on the Frappe Framework'],
            ],
        ]],
        ['When Odoo is the better fit', [
            'Odoo suits companies that want breadth. If you expect to add ecommerce, marketing, documents and many other functions, its app ecosystem saves building them. Enterprise edition also adds features and official support, which matters to teams without in-house developers.',
        ]],
        ['When ERPNext is the better fit', [
            'ERPNext suits companies that value control and predictable costs. With no per-user fee, adding staff does not add license cost, which matters in businesses with many occasional users, such as factory floors or field teams. The whole system is open, so you are not dependent on one vendor\'s roadmap.',
        ]],
        ['Look at five-year cost, not license cost', [
            'Free software is not a free project. Comparing license fees alone is the most common mistake in ERP selection.',
        ], [
            'Implementation and data migration',
            'Customization and its upkeep through upgrades',
            'Hosting, backups and security',
            'Training, and the time your team spends on the project',
            'Ongoing support',
        ]],
        ['Questions to answer before you choose', [
            'Write down your ten most important processes and see how each system handles them out of the box. Ask who will maintain the system in three years. Find out what your accountant needs for tax compliance. And speak to a company of your size in your industry that has lived with the system for a while.',
        ]],
    ],
    'takeaways' => [
        'Odoo offers breadth and vendor support, ERPNext offers openness and control',
        'Seat costs matter most when you have many users',
        'Compare five-year total cost, not license fees',
        'Test your real processes in both before deciding',
    ],
    'faqs' => [
        ['Is ERPNext really free?', 'The software has no license fee, but implementation, hosting and support still cost money.'],
        ['Is Odoo Community enough?', 'For simple needs, sometimes. Check whether the features you rely on are in Community or only in Enterprise.'],
        ['Which is better for manufacturing?', 'Both handle bills of materials and work orders. Compare them against your actual production process.'],
        ['Can both handle Indian GST?', 'Yes, both have India localization. Test it with your accountant before go-live.'],
        ['Can we switch later?', 'Yes, but ERP migrations are costly, so it is worth choosing carefully.'],
    ],
    'cta' => ['Not sure which ERP is right for you?', 'We compare both against your real processes and give you a straight recommendation.', 'Help Me Choose an ERP'],
],

'salesforce-implementation-checklist-for-growing-teams' => [
    'title' => 'Salesforce Implementation Checklist for Growing Teams',
    'meta'  => 'A practical Salesforce implementation checklist: goals, process mapping, data cleanup, automation, training and adoption, so your CRM rollout does not stall.',
    'h1'    => 'Salesforce Implementation Checklist for Growing Teams',
    'lede'  => 'A successful Salesforce implementation starts with clear goals and a mapped sales process, not with configuration. Clean your data before migrating, automate only what is stable, involve real users early, and measure adoption after launch. Most failed rollouts come down to process and people rather than technology.',
    'category' => 'Enterprise IT', 'intent' => 'How-to / Tutorial', 'date' => '2026-10-16', 'read' => '7 min',
    'sections' => [
        ['Before you configure anything', [
            'The decisions made before anyone opens Setup shape the whole project.',
        ], [
            'Write down the three business outcomes the CRM must improve',
            'Name one executive sponsor and one day-to-day owner',
            'Map your sales stages as they really work, not as they appear in a slide',
            'Agree what each stage means, so forecasts mean something',
            'Decide which reports leadership needs, then work backward to the fields',
        ]],
        ['Clean the data first', [
            'Moving bad data into a new CRM just gives you an expensive copy of the problem. Deduplicate accounts and contacts, standardize names and countries, archive what nobody needs, and decide who owns each record. Test the import on a sample before the full migration.',
        ]],
        ['Configure before you customize', [
            'Salesforce can do a great deal with configuration and Flow. Custom code is sometimes right, but every line becomes something to maintain.',
        ], [
            'Use standard objects wherever they fit',
            'Add only the fields people will really fill in',
            'Automate stable processes, not ones still changing',
            'Reach for Apex and Lightning Web Components only when configuration falls short',
        ]],
        ['Plan integrations carefully', [
            'For each connected system, such as ERP, marketing or telephony, decide what data moves, in which direction, how often and which system is the master. Getting these rules right early prevents the duplicate and conflict problems that erode trust in a CRM.',
        ]],
        ['Win adoption', [
            'A CRM nobody uses has no value. Bring real salespeople into testing, so the system reflects how they work. Train with their own accounts, not sample data. In the first month, make sure leadership runs meetings from Salesforce reports, because people quickly stop updating a system that nobody looks at.',
        ]],
        ['After go-live', [
            'Track log-ins, records created and stage updates by team. Hold a review after 30 days and fix what annoys people. Plan a second phase instead of cramming everything into the first.',
        ]],
    ],
    'takeaways' => [
        'Define outcomes and map your process before configuring',
        'Clean data before migrating it',
        'Configure first, and write custom code only when needed',
        'Adoption depends on leaders using the reports',
    ],
    'faqs' => [
        ['How long does a Salesforce implementation take?', 'A focused rollout for a small team can take weeks. Multi-team rollouts with integrations take longer.'],
        ['Why do Salesforce projects fail?', 'Usually because of unclear goals, poor data or low adoption, rather than the software.'],
        ['Should we use Flow or Apex?', 'Start with Flow for maintainability, and use Apex for complex logic Flow would handle poorly.'],
        ['Do we need a Salesforce consultant?', 'Small, simple rollouts can run in-house, while complex data or integrations benefit from experienced help.'],
    ],
    'cta' => ['Planning a Salesforce rollout?', 'We help growing teams get Salesforce right the first time, or rescue a rollout that has stalled.', 'Talk to a Salesforce Consultant'],
],

'it-staffing-models-contract-vs-direct-hire' => [
    'title' => 'IT Staffing Models: Contract vs Contract-to-Hire vs Direct',
    'meta'  => 'Compare IT staffing models: contract, contract-to-hire, direct hire and dedicated teams. See cost, speed and risk, and which one fits your project and budget.',
    'h1'    => 'IT Staffing Models: Which One Fits Your Team?',
    'lede'  => 'Use contract staffing for fixed projects and skills gaps, contract-to-hire when you want to test fit before committing, direct hire for long-term core roles, and a dedicated team when you need capacity without building a department. The right model depends on how long you need the skill and how central it is to your business.',
    'category' => 'Enterprise IT', 'intent' => 'Buying Guide', 'date' => '2026-10-14', 'read' => '6 min',
    'sections' => [
        ['The four models compared', [
            'Each model trades off speed, cost and commitment differently.',
        ], [], [
            'caption' => 'IT staffing models at a glance',
            'head' => ['Model', 'Best for', 'Commitment', 'Main risk'],
            'rows' => [
                ['Contract', 'Projects and skills gaps', 'Low', 'Knowledge leaves when they do'],
                ['Contract-to-hire', 'Testing fit first', 'Medium', 'Candidates may prefer permanent offers'],
                ['Direct hire', 'Core, long-term roles', 'High', 'Longer search, costly if wrong'],
                ['Dedicated team', 'Sustained extra capacity', 'Medium', 'Needs clear management'],
            ],
        ]],
        ['Two questions decide most cases', [
            'First, how long will you need this skill? If the answer is under a year, contract usually wins. Second, how central is it to what makes your company different? Core product and architecture roles deserve permanent people. Specialist or short-term work usually does not.',
        ]],
        ['What a good staffing partner does', [
            'The value of a partner is not forwarding CVs. It is the filtering. A good partner learns the real must-haves, screens technically before you see anyone, checks references, and sends a few strong people rather than many weak ones.',
        ], [
            'Asks hard questions about the role before searching',
            'Verifies skills instead of trusting keywords on a CV',
            'Gives you honest feedback on salary and market availability',
            'Stays involved after the person joins',
        ]],
        ['How to write a brief that gets results', [
            'Separate must-haves from nice-to-haves, and keep the first list short. Explain the project, the team and why someone would want the job. State the budget range. A vague brief with twenty requirements produces either nobody or the wrong people.',
        ]],
    ],
    'takeaways' => [
        'Duration and strategic importance decide the model',
        'Contract-to-hire reduces the risk of a wrong hire',
        'A partner\'s real value is screening',
        'A short, honest brief gets better candidates',
    ],
    'faqs' => [
        ['What is contract-to-hire?', 'A person starts on a contract, and you can offer a permanent role after an agreed period.'],
        ['How are IT staffing fees calculated?', 'Contract roles are usually a rate, while direct hires are often a percentage of annual salary.'],
        ['What is the difference between staff augmentation and a dedicated team?', 'Augmentation adds individuals to your team, while a dedicated team works together on your roadmap.'],
        ['How long does it take to fill an IT role?', 'It depends on how rare the skill is. Common roles move faster than niche ones.'],
    ],
    'cta' => ['Need technology talent?', 'Tell us the role, location and timeline. We reply within one working day.', 'Share My Hiring Need'],
],

'account-based-marketing-for-mid-market-companies' => [
    'title' => 'Account-Based Marketing for Mid-Market B2B Companies',
    'meta'  => 'A practical ABM guide for mid-market B2B: choose target accounts, align sales and marketing, tailor campaigns, and measure pipeline without enterprise budgets.',
    'h1'    => 'Account-Based Marketing for Mid-Market B2B Companies',
    'lede'  => 'Account-based marketing focuses your sales and marketing on a defined list of high-value companies instead of a broad audience. Mid-market firms can run it without enterprise software: agree a target list with sales, map the buying committee, tailor a small set of messages, and measure pipeline from those accounts.',
    'category' => 'Strategy', 'intent' => 'How-to / Tutorial', 'date' => '2026-10-09', 'read' => '7 min',
    'sections' => [
        ['Why ABM suits long B2B sales', [
            'When a handful of deals can make your year, broad campaigns waste most of their reach. ABM flips the funnel. You start with the companies you want, then work out how to reach the people inside them.',
        ]],
        ['Step 1: choose the right accounts', [
            'Sit down with sales and agree a list of perhaps thirty to one hundred companies. Look at your best existing customers and ask what they share.',
        ], [
            'Industry, size and geography that match your best customers',
            'A trigger such as new funding, expansion or a leadership change',
            'A problem you are demonstrably good at solving',
            'A realistic way in, such as an existing contact',
        ]],
        ['Step 2: map the buying committee', [
            'B2B decisions involve several people, and each one cares about something different. Find out who they are for each account, and what each one needs to hear.',
        ], [], [
            'caption' => 'What each buyer cares about',
            'head' => ['Role', 'Cares about', 'Give them'],
            'rows' => [
                ['Economic buyer', 'Return and risk', 'Business case and proof'],
                ['Technical evaluator', 'Fit and security', 'Documentation and detail'],
                ['Daily user', 'Ease of use', 'Demonstrations and examples'],
                ['Finance', 'Cost and terms', 'Clear pricing'],
            ],
        ]],
        ['Step 3: tailor without overdoing it', [
            'You do not need a unique campaign for every account. Most mid-market teams work well with three tiers: deep personalization for the top ten, an industry-specific message for the next group, and a lighter touch for the rest.',
        ]],
        ['Step 4: coordinate channels', [
            'Pair LinkedIn ads targeted at your list with outreach from sales. Point both to landing pages that speak to that industry. Timing matters: the ads warm the account before the rep gets in touch.',
        ]],
        ['Step 5: measure accounts, not clicks', [
            'Track engagement by account, meetings booked, pipeline created and deals won from your list. Expect this to take a full sales cycle, and judge it against that rather than a monthly click report.',
        ]],
    ],
    'takeaways' => [
        'Agree the target list with sales first',
        'Map the buying committee for each account',
        'Tier your personalization to keep it affordable',
        'Measure pipeline from target accounts over a full sales cycle',
    ],
    'faqs' => [
        ['Do I need ABM software?', 'Not to start. A CRM, LinkedIn ads and a spreadsheet are enough to prove the approach.'],
        ['How many accounts should I target?', 'As many as your team can genuinely work, often thirty to one hundred.'],
        ['How long until ABM shows results?', 'Expect at least one full sales cycle, which is often several months in B2B.'],
        ['Is ABM only for large companies?', 'No. It often suits smaller firms better, because they cannot afford to waste reach.'],
    ],
    'cta' => ['Ready to focus on the accounts that matter?', 'We plan and run account-based campaigns with your sales team.', 'Plan My ABM Program'],
],

'marketing-attribution-for-long-sales-cycles' => [
    'title' => 'Marketing Attribution for Long B2B Sales Cycles',
    'meta'  => 'How to attribute marketing in long B2B sales cycles: first-touch, last-touch and multi-touch models, CRM setup, and pipeline reports leadership will trust.',
    'h1'    => 'Marketing Attribution for Long B2B Sales Cycles',
    'lede'  => 'For long B2B sales cycles, connect your website and ad data to your CRM, capture the original source on every lead, and report pipeline by channel. No model is perfect, so compare first-touch and last-touch views, ask buyers how they found you, and use the combined picture to decide where to invest.',
    'category' => 'Strategy', 'intent' => 'Best Practices', 'date' => '2026-10-07', 'read' => '7 min',
    'sections' => [
        ['Why standard attribution breaks down', [
            'If a deal takes nine months and involves five people, the click just before a form fill tells you very little. Platform reports claim credit for the same deal, cookies expire, and a lot of influence happens where you cannot track it, such as conversations, podcasts and AI answers.',
        ]],
        ['The minimum setup', [
            'You do not need an expensive platform to begin. You need clean data flowing into one place.',
        ], [
            'Store the first source and the latest source on every lead',
            'Pass UTM values from forms into your CRM',
            'Connect offline conversions back to Google and Meta',
            'Use one definition of a qualified lead across marketing and sales',
            'Record pipeline and revenue against the original lead',
        ]],
        ['Choosing a model', [
            'Each model answers a different question, so look at more than one.',
        ], [], [
            'caption' => 'What each attribution view tells you',
            'head' => ['View', 'Answers', 'Blind spot'],
            'rows' => [
                ['First-touch', 'What creates new demand?', 'Ignores what closes deals'],
                ['Last-touch', 'What converts demand?', 'Over-credits brand search'],
                ['Multi-touch', 'What contributes along the way?', 'Needs clean data and volume'],
                ['Self-reported', 'What do buyers remember?', 'Memory is imperfect'],
            ],
        ]],
        ['Add the question that software cannot answer', [
            'Add a free-text "How did you hear about us?" field to your forms. It is imperfect, but it surfaces podcasts, referrals, LinkedIn posts and AI tools that no tracking pixel can see. Compare it with your software data. Where they disagree, you usually learn something.',
        ]],
        ['Reporting that leadership trusts', [
            'Report by quarter rather than month, and focus on pipeline created and revenue won by channel. Show the lag: this quarter\'s revenue often comes from activity two or three quarters ago. Be open about what you cannot measure, which earns more trust than false precision.',
        ]],
    ],
    'takeaways' => [
        'Capture first and latest source on every lead',
        'Compare several attribution views rather than choosing one',
        'Ask buyers directly how they found you',
        'Report pipeline by quarter and show the time lag',
    ],
    'faqs' => [
        ['What is the best attribution model for B2B?', 'None works alone. Compare first-touch, last-touch and self-reported data.'],
        ['What are offline conversions?', 'Sales outcomes from your CRM sent back to ad platforms, so they can optimize toward qualified leads.'],
        ['Do I need a multi-touch attribution tool?', 'Only once your data is clean and your volume is high. Start with your CRM.'],
        ['How do I attribute AI search?', 'Track referrals from AI tools, and ask buyers directly, because much of that influence leaves no click.'],
    ],
    'cta' => ['Can you see what really drives pipeline?', 'We set up tracking and CRM reporting that leadership can trust.', 'Fix My Attribution'],
],

'linkedin-ads-vs-google-ads-for-lead-generation' => [
    'title' => 'LinkedIn Ads vs Google Ads for B2B Lead Generation',
    'meta'  => 'LinkedIn Ads vs Google Ads for B2B: how intent, targeting and cost differ, which suits your sales cycle, and how to use both without wasting budget.',
    'h1'    => 'LinkedIn Ads vs Google Ads for B2B Lead Generation',
    'lede'  => 'Google Ads captures people already searching for a solution, so it usually produces leads faster. LinkedIn Ads reaches the right job titles before they are searching, so it builds demand among specific accounts. If people search for what you sell, start with Google. If your category is new or your buyers are very specific, add LinkedIn.',
    'category' => 'Paid Media', 'intent' => 'Comparison', 'date' => '2026-10-02', 'read' => '6 min',
    'sections' => [
        ['The core difference is intent', [
            'On Google, the buyer tells you what they want. On LinkedIn, you choose who sees your message, but they are not looking for it at that moment. Almost everything else follows from this.',
        ], [], [
            'caption' => 'LinkedIn Ads and Google Ads compared',
            'head' => ['Factor', 'Google Ads', 'LinkedIn Ads'],
            'rows' => [
                ['Reaches', 'People searching now', 'People by job title and company'],
                ['Best at', 'Capturing demand', 'Creating demand'],
                ['Cost per click', 'Varies by keyword', 'Usually higher'],
                ['Speed to lead', 'Often quicker', 'Usually slower'],
                ['Best content', 'Service and landing pages', 'Insight, proof, lead forms'],
            ],
        ]],
        ['When to start with Google', [
            'Start here if people already search for what you sell, you can name the keywords, and you have a page ready to convert them. It is usually the shortest route to a lead, and it shows you real buyer language.',
        ]],
        ['When LinkedIn earns its higher cost', [
            'LinkedIn makes sense when your deal value is high, your buyers are defined by title or company, and search volume is low. For a B2B firm selling to a few hundred specific companies, one good deal can pay for months of ads.',
        ]],
        ['Using both together', [
            'The two work well as a pair. LinkedIn introduces you to the right people, and when they later search for a solution, Google captures them. Run retargeting across both, and exclude existing customers.',
        ], [
            'Protect your brand name in Google',
            'Use LinkedIn to reach target accounts with useful proof',
            'Send paid traffic to pages built for each campaign',
            'Pass lead quality from your CRM back to both platforms',
        ]],
        ['What to measure', [
            'Do not compare cost per lead alone. A cheap lead that never closes is the expensive one. Compare cost per qualified lead and pipeline created, and allow for LinkedIn\'s longer path to revenue.',
        ]],
    ],
    'takeaways' => [
        'Google captures demand, LinkedIn creates it',
        'Start with Google if people search for what you sell',
        'LinkedIn suits high-value deals with specific buyers',
        'Compare cost per qualified lead, not cost per lead',
    ],
    'faqs' => [
        ['Is LinkedIn advertising worth it for small businesses?', 'It can be if your deal value is high enough. For low-value sales, the click costs rarely justify it.'],
        ['Which is cheaper, Google Ads or LinkedIn Ads?', 'LinkedIn usually costs more per click, though it depends on your keywords and audience.'],
        ['Should I use LinkedIn lead gen forms?', 'They raise volume by pre-filling details, but quality can fall, so measure qualified leads.'],
        ['How much budget do I need to test LinkedIn?', 'Enough to reach your audience several times and collect meaningful data, usually over a few weeks.'],
    ],
    'cta' => ['Not sure where your ad budget should go?', 'We review your market and show you where each rupee or dollar works hardest.', 'Audit My Ad Account'],
],

];
