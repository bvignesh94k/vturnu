<?php
/**
 * Page registry. Every routable page: slug => definition.
 * Fields: title (SEO <title>), meta (description), h1, lede (hero copy),
 *         template (default 'service'), cat (content generator category),
 *         parent (breadcrumb slug), children (for hub pages).
 *
 * SEO rules: titles ~55-63 chars, brand only on home page,
 * descriptions ~150-160 chars, intent-first and conversion-oriented.
 */

$PAGES = [

    /* ---------- Core ---------- */

    '' => [
        'template' => 'home',
        /* Global B2B intent leads the title; Chennai stays in the description,
           the on-page facts and the Organization address. */
        'title'    => 'B2B Digital Marketing Agency: SEO, AI Search & Web | VTurnU',
        'meta'     => 'Chennai-based B2B digital marketing agency for India, the UAE, Singapore, the US and Canada. SEO, AI search, paid media and websites that win qualified leads.',
        'og_title' => 'Get found. Get chosen. Get pipeline. | VTurnU',
        'og_meta'  => 'Your buyers search Google, ask ChatGPT and compare vendors before they ever talk to sales. VTurnU makes B2B companies the name they keep meeting, and turns it into qualified pipeline.',
        'h1'       => 'B2B digital marketing agency: get found, get chosen, get pipeline.',
        'lede'     => 'VTurnU is a B2B digital marketing and web development agency headquartered in Chennai, India, helping B2B companies win qualified leads through SEO, AI search optimization, paid media and conversion-focused websites.',
    ],

    'about-us' => [
        'template' => 'about',
        'title' => 'About VTurnU | Digital Marketing & Branding Team in Chennai',
        'meta'  => 'VTurnU is a Chennai digital marketing and branding team helping businesses in India and worldwide grow with SEO, AI search, paid media and fast websites.',
        'h1'    => 'About VTurnU',
        'lede'  => 'We help you grow your brand, your website and your ideas, with strategy, creativity and technology working together.',
        'parent' => null,
        'faqs' => [
            ['What does VTurnU do?', 'VTurnU is a digital marketing and branding company based in Chennai, India. We run SEO, AI search optimization, paid advertising, content and social media, and we also build websites and deliver enterprise IT projects.'],
            ['Where is VTurnU based?', 'We are based in Chennai, Tamil Nadu, and work with clients around the world.'],
            ['What kind of companies do you work with?', 'Growing businesses with a clear offer, from SMEs and founders to established B2B firms, including manufacturers, ecommerce brands, software companies and professional services.'],
            ['Who will work on my account?', 'Experienced specialists. The people you meet in the pitch are the people who do the work.'],
            ['How do I start?', 'Send an enquiry through the contact page. We reply within one working day and begin with an honest audit.'],
        ],
    ],

    'how-we-work' => [
        'template' => 'how-we-work',
        'title' => 'How We Work | From First Call to Measured Growth',
        'meta'  => 'See how VTurnU works: a senior reply within a day, an honest audit, a prioritized plan, tracked execution and plain reporting, with no lock-in after 90 days.',
        'h1'    => 'How We Work With You',
        'lede'  => 'No black box and no long handover to juniors. Here is exactly what happens from your first message to the monthly report.',
        'parent' => 'about-us',
        'faqs' => [
            ['What happens after I enquire?', 'A senior strategist reviews your website and replies within one working day. If it looks like a fit, we arrange a short call and prepare a free audit.'],
            ['How long is the minimum commitment?', 'Ninety days, because that is the minimum honest window to judge a search program. After that we work month to month.'],
            ['Who owns the accounts and content?', 'You do. Ad accounts, analytics, content and code stay in your name.'],
            ['How will you report progress?', 'With a plain-language report on leads, cost per lead and revenue, plus regular review calls.'],
            ['Do you use AI in client work?', 'Yes, as a tool under human review. Our AI policy explains how, and your data stays protected.'],
            ['Can you work with our in-house team?', 'Yes. Many clients keep an in-house marketer or developer, and we plug into the way they already work.'],
        ],
    ],

    'our-clients' => [
        'template' => 'our-clients',
        'title' => 'Our Clients | Brands Growing With VTurnU',
        'meta'  => 'Meet the brands that work with VTurnU, from B2B manufacturers to ecommerce and SaaS companies, with live websites and measured results you can check.',
        'h1'    => 'Our Clients',
        'lede'  => 'Manufacturers, ecommerce brands, software companies and service businesses that turned to us for growth.',
        'parent' => 'about-us',
        'faqs' => [
            ['Which industries does VTurnU work with?', 'Manufacturing, ecommerce, software and SaaS, professional services, healthcare, real estate and education, among others.'],
            ['Can I speak to a client?', 'Yes, where a relevant client is happy to talk. Ask us on your first call.'],
            ['Do you work with competitors in the same market?', 'We tell you up front if we work with a direct competitor, so you can decide.'],
            ['Do you take on small businesses?', 'Yes, as long as the budget fits. Projects start from ' . min_budget() . ', and we will tell you honestly if a smaller first step makes more sense.'],
        ],
    ],

    /* Every lead form lands here after a successful submission. A real page,
       not an inline panel: it gives the visitor a clear finish, gives analytics
       and ads a single conversion URL to fire on, and lets the confirmation
       carry proper next steps instead of one line squeezed under the form.
       noindex, because it should never appear in search results. */
    'thank-you' => [
        'template' => 'thank-you',
        'noindex'  => true,
        'title' => 'Thank You | VTurnU',
        'meta'  => 'Your enquiry has reached the VTurnU team. We reply within one working day.',
        'h1'    => 'Thank you. Your enquiry is in.',
        'lede'  => 'A senior strategist reads every enquiry personally. You will hear back within one working day, usually sooner.',
    ],

    /* Human-readable index of the site. The XML sitemap stays where crawlers
       expect it, at /sitemap.xml, and is no longer linked in the footer: it is
       a machine file and visitors clicking it got a wall of raw XML. */
    'sitemap' => [
        'template' => 'sitemap',
        'title' => 'Sitemap | Every Page on VTurnU',
        'meta'  => 'Browse every page on vturnu.com: services, AI search, web development, enterprise IT, case studies, guides, e-books and articles, grouped by section.',
        'h1'    => 'Sitemap',
        'lede'  => 'Everything on this site in one place, organized the way our services are.',
    ],

    'contact-us' => [
        'template' => 'contact',
        'title' => 'Contact VTurnU | Get a Growth Plan Within One Working Day',
        'meta'  => 'Talk to a VTurnU specialist about digital marketing, websites or enterprise IT. Honest advice and a clear next step within one working day. Based in Chennai.',
        'h1'    => 'Let\'s Talk Possibilities.',
        'lede'  => 'Whether you are scaling your brand, launching a product or rethinking your digital presence, we are here to listen, think and build with you.',
        'faqs' => [
            ['How quickly will you reply?', 'Within one working day, and usually sooner. A real person reads every enquiry.'],
            ['Is the first conversation free?', 'Yes. The first call and the initial audit carry no cost and no obligation.'],
            ['What should I include in my message?', 'Your goal, your website, your market and any deadline. The more specific you are, the more useful our reply will be.'],
            ['Do you work with clients outside India?', 'Yes. We work with clients worldwide and schedule calls in your time zone.'],
        ],
    ],

    'free-seo-audit' => [
        'template' => 'audit',
        'title' => 'Free SEO Audit Tool | Instant Website & AI Search Check',
        'meta'  => 'Run a free instant SEO audit of your website. Check indexing, speed, on-page tags and whether ChatGPT, Perplexity and Google AI can even read your site.',
        'faqs' => [
            ['Is the audit really free?', 'Yes. It runs instantly and there is no charge and no sales call required.'],
            ['What does the audit check?', 'Twenty checks across indexing, on-page tags, speed signals, structured data and whether AI crawlers can read your site. It also notes whether you have an llms.txt file, which does not change the score.'],
            ['How accurate is it?', 'It reads what your homepage serves right now, so it is a reliable snapshot of that page. A full audit covers the whole site.'],
            ['What should I do with the results?', 'Fix the critical items first. If you would like help, send us the report and we will suggest the next steps.'],
        ],
        'h1'    => 'Free SEO & AI Search Audit',
        'lede'  => 'Enter your website and get an instant report on what is holding your rankings back, including whether AI search engines can read your site at all. No signup, results in seconds.',
    ],

    /* Free linkable tool: crawler data and the checker live in includes/ai-crawlers.php. */
    'ai-crawler-checker' => [
        'template' => 'ai-crawler-checker',
        'title' => 'AI Crawler Checker: Can ChatGPT, Claude & Perplexity See You?',
        'meta'  => 'Free AI crawler checker. See which of 23 AI crawlers your robots.txt allows, from GPTBot to PerplexityBot, and what they can read without JavaScript.',
        'h1'    => 'AI Crawler Checker',
        'lede'  => 'See which AI crawlers your robots.txt lets in, from ChatGPT, Claude and Perplexity to Google, Apple and Meta. Then see how much of your page they can read without running JavaScript.',
        'cat' => 'ai', 'parent' => 'ai',
        'faqs' => [
            ['Does blocking GPTBot remove my site from ChatGPT search?', 'OpenAI documents them separately. GPTBot collects content that may be used to train its models, while OAI-SearchBot finds pages for ChatGPT search. Each has its own robots.txt token, so you can block one and allow the other.'],
            ['Does Google-Extended affect Google Search or AI Overviews?', 'Google says Google-Extended does not affect inclusion in Google Search and is not a ranking signal. AI Overviews and AI Mode link to pages that are indexed in Google Search with a snippet, so they depend on Googlebot and on snippet controls such as nosnippet.'],
            ['Do AI crawlers obey robots.txt?', 'The search and training crawlers listed here are documented with robots.txt controls. Several user fetchers are not: OpenAI says robots.txt rules may not apply to ChatGPT-User, Perplexity says Perplexity-User generally ignores them, and Meta and Amazon say their user fetchers may bypass them.'],
            ['My robots.txt allows everything. Why can AI tools still not read my site?', 'Common causes are a firewall or CDN rule that blocks AI crawlers, main content that only appears after JavaScript runs, and noindex or nosnippet tags. This checker shows the last two. Your firewall settings and server logs show the first.'],
            ['Do I need an llms.txt file?', 'No. llms.txt is a proposed convention, not a standard, and robots.txt is the control these companies document. The checker notes whether you have one.'],
            ['How often should I check?', 'After any change to robots.txt, your CMS or plugins, your CDN or security settings, or a redesign. Companies also add and rename crawlers, and the reference list on this page shows the date it was last checked.'],
        ],
    ],

    'privacy-policy' => [
        'template' => 'legal',
        'title' => 'Privacy Policy | How We Collect, Use & Protect Your Data',
        'meta'  => 'Read our privacy policy: what data we collect, how we use it, how we protect it, and the choices and rights available to you as a visitor or client.',
        'h1'    => 'Privacy Policy',
        'lede'  => 'Your privacy matters to us. This policy explains what we collect, why we collect it, and how we keep it safe.',
    ],

    'terms-and-conditions' => [
        'template' => 'legal',
        'title' => 'Terms & Conditions | Engagement, IP & Service Terms',
        'meta'  => 'The terms and conditions governing use of vturnu.com and our services, including engagement terms, intellectual property and limitation of liability.',
        'h1'    => 'Terms and Conditions',
        'lede'  => 'These terms govern your use of vturnu.com and our services. Please read them carefully.',
    ],

    /* ---------- Hubs ---------- */

    'digital-marketing' => [
        'template' => 'hub',
        'title' => 'Digital Marketing Services in Chennai | SEO, Ads & Branding',
        'meta'  => 'Full-funnel digital marketing and branding from Chennai for businesses worldwide: SEO, paid ads, social, content and consulting. Get a free growth plan.',
        'h1'    => 'Digital Marketing Services',
        'lede'  => 'One partner for search, paid media, content and brand: strategy, execution and reporting under one roof.',
        'children' => ['seo', 'paid-advertising', 'content-marketing', 'content-writing-services', 'branding-services', 'online-marketing', 'digital-marketing-consulting'],
        'faqs' => [
            ['What is included in digital marketing services?', 'Typically SEO, paid advertising, content, social media, email and conversion work. We plan them together, so each channel supports the others.'],
            ['Which channel should I start with?', 'If people already search for what you sell, start with search. If they do not know it exists yet, paid social creates the demand. We recommend a mix after an audit.'],
            ['How much does digital marketing cost?', 'Engagements start from ' . min_budget() . '. Ad spend is separate and paid directly to the platform. We confirm the exact scope after a free audit.'],
            ['How soon will I see results?', 'Paid campaigns can produce enquiries in the first month. SEO usually shows movement by week eight and compounds from there.'],
            ['How do you measure success?', 'In qualified leads, cost per lead and revenue, not in impressions or follower counts.'],
            ['Is branding part of digital marketing?', 'Yes. A clear brand lowers the cost of every other channel, because people recognize and trust you sooner.'],
        ],
    ],

    'seo' => [
        'template' => 'hub',
        'title' => 'SEO Services by Type & Industry | Rank Higher, Sell More',
        'meta'  => 'Explore SEO services by type or industry: classic SEO, AI SEO, ecommerce, lead gen, local, enterprise and franchise programs that rank and convert. Free audit.',
        'h1'    => 'SEO That Ranks, and Converts',
        'lede'  => 'Pick your SEO by type or by industry. Every program is built on technical excellence, quality content and clean link acquisition.',
        'parent' => 'digital-marketing',
        'faqs' => [
            ['What is SEO?', 'SEO is the practice of earning visibility in search engines through a sound website, useful content and trusted references, so people find you without paying per click.'],
            ['How long does SEO take?', 'Early movement usually shows by week eight. Competitive terms take months, and results compound over time.'],
            ['Which type of SEO do I need?', 'Local SEO for customers nearby, ecommerce SEO for stores, lead generation SEO for B2B and service firms, and enterprise SEO for large sites.'],
            ['Is SEO still worth it with AI search?', 'Yes. AI engines read the same web, and they favor the clear, well-structured sites that good SEO produces.'],
            ['Do you guarantee rankings?', 'No, and you should be wary of anyone who does. We commit to the work, the reporting and honest results.'],
        ],
        'children' => ['seo-services', 'ai-seo', 'ecommerce-seo', 'lead-generation-seo', 'local-seo', 'enterprise-seo', 'franchise-seo', 'ecommerce-seo-for-fashion', 'ecommerce-seo-for-beauty', 'ecommerce-seo-for-jewelry', 'ecommerce-seo-for-health-and-wellness', 'ecommerce-seo-for-cannabis', 'ecommerce-seo-for-hemp-and-cbd', 'lead-gen-seo-for-business-industrial', 'lead-gen-seo-for-artists', 'lead-gen-seo-for-lawyers', 'lead-gen-seo-for-dentists', 'lead-gen-seo-for-finance'],
    ],

    'ai' => [
        'template' => 'hub',
        'title' => 'AI Search Optimization Services | AEO, GEO & AI SEO Agency',
        'meta'  => 'Get your brand named in ChatGPT, Gemini, Perplexity and Google AI Overviews. AEO and GEO services from VTurnU, with real AI citation results to show.',
        'h1'    => 'AI Search: Be the Brand AI Recommends',
        'lede'  => 'Search is changing. We help your brand get found, and chosen, inside ChatGPT, Gemini, Perplexity, Copilot and Google AI Overviews.',
        'children' => ['ai-seo', 'google-ai-overviews-seo', 'chatgpt-seo', 'perplexity-seo', 'selling-on-chatgpt', 'ai-blog-posts', 'ai-crawler-checker'],
        'faqs' => [
            ['What is AEO?', 'Answer engine optimization, or AEO, is the practice of making your content the source AI assistants and search features quote when they answer a question.'],
            ['What is GEO?', 'Generative engine optimization, or GEO, means earning visibility inside AI-generated answers. In practice it overlaps almost entirely with AEO.'],
            ['Can I really influence what ChatGPT says about my brand?', 'You cannot control it, but you can influence it, by making your site readable, your facts consistent and your brand mentioned on sources AI engines trust.'],
            ['How do you measure AI visibility?', 'We track a set of buyer prompts across AI engines, then add the first-party data that now exists: the AI Assistant channel in Google Analytics for visits, the generative AI report in Search Console for impressions in AI Overviews and AI Mode, and Bing Webmaster Tools for citations in Copilot.'],
            ['How is this different from SEO?', 'It builds on SEO, then puts more weight on direct answers, entity clarity and independent corroboration.'],
            ['How long does it take?', 'Technical and entity fixes can show within weeks. Building trusted third-party mentions takes months.'],
        ],
    ],

    'content-marketing' => [
        'template' => 'hub',
        'title' => 'Content Marketing Services | Strategy, Writing & Results',
        'meta'  => 'Partner with a trusted content marketing team: SEO content writing, copywriting, translation, email and outbound, ROI-driven content that attracts and converts.',
        'h1'    => 'Content Marketing That Earns Attention',
        'lede'  => 'Strategy, writing, distribution and measurement: content programs built to attract, nurture and convert your audience.',
        /* Content first, in the order a buyer narrows it down: the program, the
           writing, then each format. The landing pages are registered in
           content-landings.php, after this array, and exist by the time a hub renders. */
        'children' => [
            'content-marketing-services', 'content-writing-services', 'blog-writing-services', 'website-content-writing-services',
            'seo-content-writing', 'copywriting-services', 'white-paper-writing-services', 'case-study-writing-services',
            'product-description-writing-services', 'technical-content-writing-services', 'translation-services',
            'branding-services', 'email-marketing', 'outbound-marketing',
        ],
    ],

    'web-services' => [
        'template' => 'hub',
        'title' => 'Web Design & Development Services | Fast, SEO-Ready Sites',
        'meta'  => 'Fast, SEO-ready websites built to convert: ecommerce, lead gen and custom builds on Shopify, WordPress and more. See live client sites and get a quote.',
        'h1'    => 'Websites Built to Grow Your Business',
        'lede'  => 'Design and development by goal or by platform: fast, secure, SEO-ready websites that turn visitors into customers.',
        'faqs' => [
            ['How much does a website cost?', 'Website projects start from ' . min_budget() . '. Custom builds are quoted after a scoping call, because the work depends on your pages, features and integrations.'],
            ['How long does a website take?', 'A business website usually takes three to four weeks, and an online store five to eight, once content is ready.'],
            ['Will I own my website?', 'Yes. Design and development are billed once, and the site, code and content are yours.'],
            ['Which platform should I choose?', 'Shopify suits most online stores, WordPress suits content-led sites, and a custom build suits unusual requirements. We recommend based on your needs, not our preference.'],
            ['Is SEO included?', 'Yes. Clean structure, schema, speed and mobile layout are built into every project.'],
            ['Can you rebuild my site without losing rankings?', 'Yes. We map every URL and set redirects, so ranking signals carry over.'],
        ],
        'children' => ['web-design', 'ecommerce-web-design', 'lead-gen-web-design', 'headless-web-design', 'ai-development', 'bigcommerce-web-design', 'shopify-web-design', 'woocommerce-web-design', 'magento-web-design', 'wordpress-web-design', 'magento-development', 'custom-design-and-development', 'website-content-writing-services'],
    ],

    'case-studies' => [
        'template' => 'case-hub',
        'title' => 'Case Studies | Real Clients, Measured SEO & AI Search Results',
        'meta'  => 'Named VTurnU case studies with measured results: 10.7K AI citations, 178K Google impressions and mobile speed from 26 to the 90s, with sources shown.',
        'h1'    => 'Results We Can Show You',
        'lede'  => 'Named clients, live websites and measured numbers, with the source of each one.',
        'faqs' => [
            ['Are these real clients?', 'Yes. Each named case study links to the live website, and states where every number comes from.'],
            ['Where do the numbers come from?', 'From each client\'s Google Search Console, Bing Webmaster Tools and PageSpeed data, with dates on each case study.'],
            ['Do you publish lead numbers?', 'Lead and revenue figures are the client\'s private business data, so we do not publish them without permission.'],
            ['Can you get the same results for me?', 'Results depend on your market and starting point. We share a realistic forecast after an audit, not a promise.'],
        ],
    ],

    'enterprise-it-services' => [
        'template' => 'hub',
        'title' => 'Enterprise IT Services | SAP CPQ, Salesforce, ERP & Staffing',
        'meta'  => 'VTurnU Enterprise & IT: SAP CPQ, Salesforce, Odoo ERP, ERPNext and IT staffing, delivered by specialists as a separate practice. Talk to a consultant.',
        'h1'    => 'Enterprise & IT Services',
        'lede'  => 'A separate practice from our marketing work: enterprise applications and technology talent, delivered by specialists who speak the language of your systems.',
        'cat' => 'web',
        'scene' => 'orbit',
        'answer' => ['What does the VTurnU Enterprise & IT practice do?', 'It implements and improves business systems, namely SAP CPQ, Salesforce, Odoo ERP and ERPNext, and places technology professionals through IT staffing. It runs as its own practice, with consultants focused on systems, processes and delivery rather than marketing.'],
        'offer' => ['Tell us about your project', 'Share your systems, scope and timeline. A consultant replies within one working day.', 'Talk to a Consultant'],
        'why' => ['Consultants who have done this before', 'Discovery first, then a fixed plan', 'Documented work your team can maintain', 'Clear weekly progress reporting'],
        'children' => ['sap-cpq-services', 'salesforce-services', 'odoo-erp-services', 'erpnext-services', 'it-staffing-services'],
        'faqs' => [
            ['Is this separate from your digital marketing work?', 'Yes. Enterprise & IT is its own practice, with its own consultants. You can use it alone, or alongside our marketing services.'],
            ['Which ERP should we choose?', 'It depends on your processes, team and budget. Odoo has a wide app ecosystem, ERPNext is fully open source, and SAP suits larger, complex organizations.'],
            ['How are enterprise projects priced?', 'As a fixed fee for an agreed phase, or on time and materials for evolving work. Staffing is an agreed rate or a percentage of salary.'],
            ['Do you support systems after go-live?', 'Yes. Training, documentation and ongoing support are part of how we plan every project.'],
        ],
    ],

    'branding-services' => [
        'title' => 'Branding Services | Positioning, Identity & Brand Strategy',
        'meta'  => 'Branding services from VTurnU: positioning, naming, logo and visual identity, messaging and brand guidelines that make every marketing rupee work harder.',
        'h1'    => 'Branding Services',
        'lede'  => 'A brand people recognize and trust lowers the cost of every other channel. We define who you are, then make it unmistakable everywhere.',
        'cat' => 'content', 'parent' => 'digital-marketing',
        'answer' => ['What do branding services include?', 'Branding services define how a business is seen. They cover positioning, messaging, naming, logo and visual identity, tone of voice and brand guidelines, then apply them across the website, sales material and marketing, so every touchpoint feels like the same company.'],
        'features' => [
            ['Brand Strategy', 'Research into your market, buyers and competitors, leading to a clear position only you can own.'],
            ['Messaging', 'What you say and how you say it: value proposition, proof points and tone of voice.'],
            ['Visual Identity', 'Logo, color, typography and imagery built as a system, not a one-off mark.'],
            ['Brand Guidelines', 'A practical guide your team and vendors can follow, with real examples.'],
            ['Website and Collateral', 'Your new brand applied to the website, presentations and sales material.'],
            ['Launch', 'A rollout plan for customers, staff and search engines, including entity updates.'],
        ],
        'faqs' => [
            ['What is the difference between branding and marketing?', 'Branding defines who you are and why you are different. Marketing gets that message to the right people. Strong branding makes marketing cheaper.'],
            ['How long does a branding project take?', 'A focused identity project usually takes four to eight weeks. A full strategy and rollout takes longer.'],
            ['Do we need a new logo?', 'Not always. Often the position and messaging need the work, and the existing logo can stay or be refined.'],
            ['Will a rebrand hurt our SEO?', 'It does not have to. If your name or domain changes, we plan redirects and update your entity data across the web.'],
            ['What do we receive at the end?', 'Logo files in every format, a brand guideline document, and templates your team can use straight away.'],
        ],
    ],

    /* ---------- Resources ---------- */

    'blog' => [
        'template' => 'blog-list',
        'title' => 'Digital Marketing Blog | SEO, AI Search & Growth Playbooks',
        'meta'  => 'Actionable articles on SEO, AI search, paid media, content and conversion: playbooks, comparisons and pricing guides written by working strategists.',
        'h1'    => 'Growth Playbooks & Insights',
        'lede'  => 'The same thinking our clients pay for, published free. SEO, AI search, paid media, content and conversion, without the fluff.',
    ],
    /* Google search updates, explained. The list fills from $UPDATES below and
       from the admin panel (Search Updates). */
    'updates' => [
        'template' => 'update-list',
        'title' => 'Google Search Updates Explained: Core, Spam & AI Changes',
        'meta'  => 'Google search updates explained in plain words: what Google announced, when each update started and finished, and what to check on your site next.',
        'h1'    => 'Google search updates, explained in plain words',
        'crumb' => 'Search Updates',
        'lede'  => 'When Google changes how search works, we explain it here: what was announced, when it started and finished, what it can mean for your traffic and what to check next. Every fact comes with its source.',
        'date'  => '2026-11-12',
        'faqs'  => [
            ['What is a Google core update?', 'A core update is a broad change to the systems Google uses to rank search results. Google releases several a year. Core updates do not target specific sites, and they are not penalties.'],
            ['How often does Google release core updates?', 'Google\'s Search Status Dashboard lists three core updates in 2025 (March, June and December) and three in 2026 so far (March, May and November). Google also makes smaller core updates that it does not announce.'],
            ['Where does the information on these pages come from?', 'Dates, durations and statements come from Google\'s Search Status Dashboard and Google Search Central documentation, linked in each update. Where we cite news coverage, we name the publication.'],
            ['Will you cover future updates?', 'Yes. We add each confirmed Google update that can affect traffic, and we update a page when Google confirms its rollout is complete.'],
        ],
    ],
    'ebooks' => [
        'template' => 'resource-list',
        'title' => 'Free Digital Marketing E-books | Read Online, No Sign-Up',
        'meta'  => 'Read free e-books on AI search, ecommerce SEO, lead gen websites, paid media budgeting and more, right here with no form. Written by working practitioners.',
        'h1'    => 'Free E-books, Written by Practitioners',
        'lede'  => 'Deep-dive playbooks you can read right here, with no form and no email. Share any chapter with your team.',
        'rtype' => 'ebook',
        'parent' => null,
        'faqs' => [
            ['Are these e-books really free?', 'Yes. Every e-book is free to read in full on this site, with no sign-up and no email.'],
            ['Can I share a chapter with my team?', 'Yes. Every chapter has its own link, so you can send people straight to it.'],
            ['Who writes them?', 'The VTurnU strategy team, drawing on the work we do for clients.'],
            ['Can you apply this to my business?', 'Yes. If you would rather have it done for you, send us an enquiry and we will reply within one working day.'],
        ],
    ],
    'guides' => [
        'template' => 'resource-list',
        'title' => 'Complete Digital Marketing Guides | Free Evergreen Resources',
        'meta'  => 'Complete guides to local SEO, AI SEO, content strategy, ecommerce platforms, B2B lead gen and Google Ads, free to read in full with no form to fill.',
        'h1'    => 'The Complete Guides',
        'lede'  => 'Evergreen, in-depth references maintained by our strategists. Bookmark them and come back when you need them.',
        'rtype' => 'guide',
        'faqs' => [
            ['What is the difference between a guide and an e-book?', 'Guides are broad references on a whole subject. E-books go deep on one playbook or problem.'],
            ['Are the guides kept up to date?', 'Yes. We revisit them as search, platforms and AI engines change.'],
            ['Do I need to sign up to read them?', 'No. Every guide is free and fully open.'],
            ['Which guide should I start with?', 'Start with the channel closest to your goal: local SEO for nearby customers, B2B lead gen for sales teams, or AI SEO for visibility in AI answers.'],
        ],
    ],
    'ai-blog-posts' => [
        'template' => 'blog-list',
        'title' => 'AI Search & Marketing Articles | Stay Ahead of AI Discovery',
        'meta'  => 'The latest thinking on AI search, generative engines and AI-assisted marketing, how AI is reshaping discovery and exactly what to do about it.',
        'h1'    => 'AI Search & Marketing Insights',
        'lede'  => 'Our latest thinking on AI search, generative engines and AI-assisted marketing.',
        'parent' => 'ai',
        'blog_cat' => 'AI Search',
    ],

    /* ---------- Digital Marketing: SEO by type ---------- */

    'seo-services' => [
        'title' => 'SEO Services in Chennai, India | Rank Higher, Grow Revenue',
        'meta'  => 'Partner with an expert SEO team in Chennai serving India, the US & Canada. Technical, content and authority SEO that grows revenue. Get a free SEO audit today.',
        'h1'    => 'Search Engine Optimization Services',
        'lede'  => 'Rank higher, earn qualified traffic and grow revenue with full-stack SEO: technical foundations, content that answers, and authority that lasts.',
        'cat' => 'seo', 'parent' => 'seo',
    ],
    'ai-seo' => [
        'title' => 'AI SEO Services | Get Cited by ChatGPT, Gemini & AI Search',
        'meta'  => 'AI SEO that wins the new search: optimize for ChatGPT, Perplexity, Gemini and Google AI Overviews with structured data, entity SEO and answer-ready content.',
        'h1'    => 'AI SEO Services',
        'lede'  => 'Traditional rankings aren\'t enough anymore. We optimize your brand to be cited, recommended and chosen by AI search engines.',
        'cat' => 'ai', 'parent' => 'seo',
    ],
    'ecommerce-seo' => [
        'title' => 'Ecommerce SEO Services | Turn Search Traffic Into Sales',
        'meta'  => 'Ecommerce SEO that grows store revenue: category strategy, product schema, crawl health and content that captures buying intent. Get a free store audit.',
        'h1'    => 'Ecommerce SEO Services',
        'lede'  => 'Turn your store into your best-performing sales channel with SEO built for category, product and buying-intent queries.',
        'cat' => 'seo', 'parent' => 'seo',
    ],
    'lead-generation-seo' => [
        'title' => 'Lead Generation SEO Services | Fill Your Sales Pipeline',
        'meta'  => 'Lead generation SEO that fills your pipeline: bottom-funnel keyword strategy, conversion-optimized pages and content that turns searches into enquiries.',
        'h1'    => 'Lead Gen SEO Services',
        'lede'  => 'We build SEO programs measured in qualified leads, not just rankings and traffic.',
        'cat' => 'seo', 'parent' => 'seo',
    ],
    'local-seo' => [
        'title' => 'Local SEO Services | Dominate the Map Pack & Near-Me Search',
        'meta'  => 'Boost local visibility with expert local SEO: Google Business Profile optimization, local citations and review systems that attract nearby customers.',
        'h1'    => 'Local SEO Services',
        'lede'  => 'Dominate the map pack and near-me searches with local SEO that brings customers through your door.',
        'cat' => 'seo', 'parent' => 'seo',
    ],
    'enterprise-seo' => [
        'title' => 'Enterprise SEO Services | Scalable SEO for Large Websites',
        'meta'  => 'Enterprise SEO for large sites and teams: scalable technical SEO, internal linking architecture, governance and reporting that executives trust.',
        'h1'    => 'Enterprise SEO Services',
        'lede'  => 'SEO for sites with thousands of pages and stakeholders to match: scalable processes, measurable outcomes.',
        'cat' => 'seo', 'parent' => 'seo',
    ],
    'franchise-seo' => [
        'title' => 'Franchise SEO Services | Grow Every Location Together',
        'meta'  => 'Franchise SEO that scales across locations: location page systems, consistent NAP data, local content and brand-wide reporting for every franchisee.',
        'h1'    => 'Franchise SEO Services',
        'lede'  => 'One brand, many locations, zero cannibalization, franchise SEO that lifts every location together.',
        'cat' => 'seo', 'parent' => 'seo',
    ],

    /* ---------- Digital Marketing: Paid Advertising ---------- */

    'paid-advertising' => [
        'template' => 'hub',
        'title' => 'Paid Advertising Services | ROI-Focused PPC Management',
        'meta'  => 'ROI-focused paid advertising: Google Ads, Facebook Ads, Instagram Ads and enterprise PPC. Creative, targeting and optimization that lower CPA and lift ROAS.',
        'h1'    => 'Paid Advertising Services',
        'lede'  => 'Every rupee accountable. Paid media programs engineered around ROAS, not vanity metrics.',
        'parent' => 'digital-marketing',
        'children' => ['google-ads', 'facebook-ads', 'instagram-ads', 'enterprise-ppc'],
    ],
    'google-ads' => [
        'title' => 'Google Ads Management Services | Lower CPA, Higher ROAS',
        'meta'  => 'Google Ads management that maximizes ROI: search, shopping, display and Performance Max campaigns with rigorous testing and transparent reporting. Free audit.',
        'h1'    => 'Google Ads Management',
        'lede'  => 'Capture demand the moment it happens: search, shopping and PMax campaigns tuned for profitable growth.',
        'cat' => 'ppc', 'parent' => 'paid-advertising',
    ],
    'facebook-ads' => [
        'title' => 'Facebook Ads Management | Meta Campaigns That Convert',
        'meta'  => 'Facebook advertising that converts: full-funnel Meta campaigns, scroll-stopping creative, precise audiences and continuous creative testing. Get a free plan.',
        'h1'    => 'Facebook Ads Management',
        'lede'  => 'Full-funnel Meta campaigns with creative that stops the scroll and targeting that finds your buyers.',
        'cat' => 'ppc', 'parent' => 'paid-advertising',
    ],
    'instagram-ads' => [
        'title' => 'Instagram Ads Management | Reels & Stories Ads That Sell',
        'meta'  => 'Instagram advertising built for attention: Reels, Stories and feed campaigns with native-feeling creative that drives engagement, traffic and sales.',
        'h1'    => 'Instagram Ads Management',
        'lede'  => 'Native-feeling Reels and Stories ads that build brand and drive measurable sales.',
        'cat' => 'ppc', 'parent' => 'paid-advertising',
    ],
    'enterprise-ppc' => [
        'title' => 'Enterprise PPC Management Services | Paid Media at Scale',
        'meta'  => 'Enterprise PPC for large budgets and complex accounts: cross-channel governance, feed management, automation guardrails and executive-grade reporting.',
        'h1'    => 'Enterprise PPC Management',
        'lede'  => 'Big budgets deserve better systems: enterprise paid media with governance, automation and accountability built in.',
        'cat' => 'ppc', 'parent' => 'paid-advertising',
    ],

    /* ---------- Digital Marketing: Online Marketing ---------- */

    'online-marketing' => [
        'template' => 'hub',
        'title' => 'Online Marketing Services | Social, Email & Reputation',
        'meta'  => 'Grow everywhere your audience lives: social media marketing, email marketing, reputation management and digital marketing consulting under one roof.',
        'h1'    => 'Online Marketing Services',
        'lede'  => 'Social, email, reputation and strategy, the channels that keep your brand present between searches.',
        'parent' => 'digital-marketing',
        'children' => ['social-media-marketing', 'email-marketing', 'reputation-management', 'digital-marketing-consulting'],
    ],
    'social-media-marketing' => [
        'title' => 'Social Media Marketing Services | Followers Into Customers',
        'meta'  => 'Drive leads and brand growth with proven social media marketing: strategy, content, community and paid social from a team that reports on revenue, not likes.',
        'h1'    => 'Social Media Marketing Services',
        'lede'  => 'Build a brand people follow, and a funnel that turns followers into customers.',
        'cat' => 'social', 'parent' => 'online-marketing',
    ],
    'email-marketing' => [
        'title' => 'Email Marketing Services | Lifecycle Campaigns That Convert',
        'meta'  => 'Lifecycle email marketing that compounds: welcome flows, nurture sequences, promotions and retention campaigns with deliverability best practice baked in.',
        'h1'    => 'Email Marketing',
        'lede'  => 'The highest-ROI channel in marketing, done properly: flows, campaigns and lists that grow revenue on autopilot.',
        'cat' => 'content', 'parent' => 'online-marketing',
    ],
    'reputation-management' => [
        'title' => 'Reputation Management Services | Protect & Grow Your Brand',
        'meta'  => 'Online reputation management: review generation, monitoring, response strategy and search suppression to protect and strengthen your brand image.',
        'h1'    => 'Reputation Management Services',
        'lede'  => 'Your reputation is your best salesperson. We help you build it, monitor it and defend it.',
        'cat' => 'social', 'parent' => 'online-marketing',
    ],
    'digital-marketing-consulting' => [
        'title' => 'Digital Marketing Consulting | Audits, Strategy & Roadmaps',
        'meta'  => 'Digital marketing consulting for teams that want clarity: audits, channel strategy, analytics setup and quarterly roadmaps from senior strategists.',
        'h1'    => 'Digital Marketing Consulting',
        'lede'  => 'Senior strategists on your side: audits, roadmaps and coaching that make your whole marketing engine smarter.',
        'cat' => 'consulting', 'parent' => 'online-marketing',
    ],

    /* ---------- AI Services ---------- */

    'ai-development' => [
        'title' => 'AI Development Services | Custom Chatbots & Automations',
        'meta'  => 'Custom AI development: chatbots, recommendation systems, content automation and AI integrations built around your business workflows and data. Get a quote.',
        'h1'    => 'AI Development Services',
        'lede'  => 'From chatbots to custom automations: practical AI built around your workflows, not hype.',
        'cat' => 'ai', 'parent' => 'ai',
    ],
    'google-ai-overviews-seo' => [
        'title' => 'Google AI Overviews SEO | Get Featured in the AI Answer',
        'meta'  => 'Optimize for Google AI Overviews: answer-ready content structure, entity optimization and schema strategies that earn citations in AI-generated results.',
        'h1'    => 'Google AI Overviews SEO',
        'lede'  => 'AI Overviews now sit above position one. We make sure your brand is in the answer, not below it.',
        'cat' => 'ai', 'parent' => 'ai',
    ],
    'chatgpt-seo' => [
        'title' => 'ChatGPT SEO Services | Make Your Brand the AI\'s Answer',
        'meta'  => 'ChatGPT SEO: make your brand visible in ChatGPT answers and shopping results with entity building, authoritative citations and machine-readable content.',
        'h1'    => 'ChatGPT SEO',
        'lede'  => 'Millions ask ChatGPT what to buy and who to hire. We work to make your brand the answer.',
        'cat' => 'ai', 'parent' => 'ai',
    ],
    'perplexity-seo' => [
        'title' => 'Perplexity SEO Services | Earn Citations in AI Answers',
        'meta'  => 'Perplexity SEO: earn citations in Perplexity answers through source-worthy content, freshness signals, structured data and digital PR that AI engines trust.',
        'h1'    => 'Perplexity SEO',
        'lede'  => 'Perplexity cites its sources, we make your site one of them.',
        'cat' => 'ai', 'parent' => 'ai',
    ],
    'selling-on-chatgpt' => [
        'title' => 'Selling on ChatGPT | Get Your Products Into AI Shopping',
        'meta'  => 'Get your products discoverable and buyable inside ChatGPT: product feed optimization, structured data and AI commerce readiness for the conversational era.',
        'h1'    => 'Selling on ChatGPT',
        'lede'  => 'AI assistants are becoming shopping assistants. Get your catalog ready for conversational commerce.',
        'cat' => 'ai', 'parent' => 'ai',
    ],
    'ai-policy' => [
        'template' => 'legal',
        'title' => 'Our AI Policy | Responsible, Human-Led AI in Client Work',
        'meta'  => 'How we use AI: our principles for responsible AI use in client work: human oversight, transparency, data protection and quality control on every deliverable.',
        'h1'    => 'Our AI Policy',
        'lede'  => 'We use AI to work smarter for our clients: always with human judgment, transparency and your data protected.',
        'parent' => 'ai',
    ],

    /* ---------- SEO: Ecommerce SEO by industry ---------- */

    'ecommerce-seo-for-fashion' => [
        'title' => 'Fashion Ecommerce SEO | Rank Through Every Season & Drop',
        'meta'  => 'Ecommerce SEO for fashion brands: seasonal category strategy, faceted navigation done right, and trend-driven content that captures style searches.',
        'h1'    => 'Ecommerce SEO for Fashion',
        'lede'  => 'Fashion search is seasonal, visual and fast-moving. Our SEO keeps your collections ranking through every drop and season.',
        'cat' => 'seo-industry', 'industry' => 'fashion ecommerce', 'parent' => 'ecommerce-seo',
    ],
    'ecommerce-seo-for-beauty' => [
        'title' => 'Beauty Ecommerce SEO | Outrank Marketplaces, Win Baskets',
        'meta'  => 'SEO for beauty and cosmetics stores: ingredient-led content, product schema, review signals and category strategy that outranks the big marketplaces.',
        'h1'    => 'Ecommerce SEO for Beauty',
        'lede'  => 'From ingredient searches to routine guides, beauty SEO that wins trust and baskets.',
        'cat' => 'seo-industry', 'industry' => 'beauty ecommerce', 'parent' => 'ecommerce-seo',
    ],
    'ecommerce-seo-for-jewelry' => [
        'title' => 'Jewelry Ecommerce SEO | High-Trust Rankings That Convert',
        'meta'  => 'Jewelry SEO for high-consideration purchases: buying-guide content, product schema with pricing, local + ecommerce hybrid strategy and trust building.',
        'h1'    => 'Ecommerce SEO for Jewelry',
        'lede'  => 'High-value purchases need high-trust rankings. We build jewelry SEO around consideration, credibility and conversion.',
        'cat' => 'seo-industry', 'industry' => 'jewelry ecommerce', 'parent' => 'ecommerce-seo',
    ],
    'ecommerce-seo-for-health-and-wellness' => [
        'title' => 'Health & Wellness Ecommerce SEO | E-E-A-T-First Growth',
        'meta'  => 'SEO for health and wellness stores: E-E-A-T-first content, compliant claims, expert review workflows and category strategy for supplement and wellness brands.',
        'h1'    => 'Ecommerce SEO for Health and Wellness',
        'lede'  => 'Wellness is a YMYL category, rankings require expertise and trust. We build both into every page.',
        'cat' => 'seo-industry', 'industry' => 'health and wellness ecommerce', 'parent' => 'ecommerce-seo',
    ],
    'ecommerce-seo-for-cannabis' => [
        'title' => 'Cannabis Ecommerce SEO | Compliant Growth Where Ads Can\'t Go',
        'meta'  => 'Cannabis SEO where paid ads can\'t go: compliant content strategy, local + ecommerce visibility and authority building for cannabis retailers and brands.',
        'h1'    => 'Ecommerce SEO for Cannabis',
        'lede'  => 'With paid channels restricted, organic is your growth engine. We build compliant, durable cannabis SEO.',
        'cat' => 'seo-industry', 'industry' => 'cannabis', 'parent' => 'ecommerce-seo',
    ],
    'ecommerce-seo-for-hemp-and-cbd' => [
        'title' => 'Hemp & CBD SEO | Win Organic Where Ad Platforms Say No',
        'meta'  => 'SEO for hemp and CBD brands: compliant product content, education-led keyword strategy and authority building in a heavily restricted ad landscape.',
        'h1'    => 'Ecommerce SEO for Hemp and CBD',
        'lede'  => 'Restricted from most ad platforms, hemp and CBD brands win or lose on organic. We make sure you win.',
        'cat' => 'seo-industry', 'industry' => 'Hemp and CBD', 'parent' => 'ecommerce-seo',
    ],

    /* ---------- SEO: Lead Gen SEO by industry ---------- */

    'lead-gen-seo-for-business-industrial' => [
        'title' => 'Industrial & B2B Lead Gen SEO | Turn Searches Into RFQs',
        'meta'  => 'Lead gen SEO for business and industrial companies: technical-buyer keyword strategy, spec-rich content and RFQ-optimized pages that fill your pipeline.',
        'h1'    => 'Lead Gen SEO for Business & Industrial',
        'lede'  => 'Engineers and procurement teams search differently. We build SEO for spec sheets, RFQs and long B2B sales cycles.',
        'cat' => 'seo-industry', 'industry' => 'business and industrial', 'parent' => 'lead-generation-seo',
    ],
    'lead-gen-seo-for-artists' => [
        'title' => 'SEO for Artists | Turn Searches Into Commissions & Sales',
        'meta'  => 'Lead gen SEO for artists and creative studios: portfolio SEO, commission-intent keywords and visibility that turns searches into commissions and sales.',
        'h1'    => 'Lead Gen SEO for Artists',
        'lede'  => 'Your art deserves to be found. We optimize portfolios and commission pages for the searches collectors actually make.',
        'cat' => 'seo-industry', 'industry' => 'artists and creative studios', 'parent' => 'lead-generation-seo',
    ],
    'lead-gen-seo-for-lawyers' => [
        'title' => 'Law Firm SEO Services | Turn Searches Into Consultations',
        'meta'  => 'SEO for law firms: practice-area pages that rank, local SEO for legal searches, and E-E-A-T content that turns high-intent searches into consultations.',
        'h1'    => 'Lead Gen SEO for Lawyers',
        'lede'  => 'Legal SEO is the most competitive on earth. We win it with practice-area depth, local dominance and authority.',
        'cat' => 'seo-industry', 'industry' => 'law firms', 'parent' => 'lead-generation-seo',
    ],
    'lead-gen-seo-for-dentists' => [
        'title' => 'Dental SEO Services | Fill Your Chairs From the Map Pack',
        'meta'  => 'Dental SEO that fills chairs: local map-pack dominance, treatment-page strategy and review systems that make your practice the obvious choice nearby.',
        'h1'    => 'Lead Gen SEO for Dentists',
        'lede'  => 'Patients pick from the map pack. We put your practice there, and make your treatment pages convert.',
        'cat' => 'seo-industry', 'industry' => 'dental practices', 'parent' => 'lead-generation-seo',
    ],
    'lead-gen-seo-for-finance' => [
        'title' => 'Finance SEO Services | YMYL-Compliant Rankings & Leads',
        'meta'  => 'SEO for finance companies: YMYL-compliant content, expert authorship, calculator and comparison assets that earn rankings and qualified financial leads.',
        'h1'    => 'Lead Gen SEO for Finance',
        'lede'  => 'Finance is YMYL territory, trust is the ranking factor. We build content and authority that satisfies both Google and regulators.',
        'cat' => 'seo-industry', 'industry' => 'financial services', 'parent' => 'lead-generation-seo',
    ],

    /* ---------- Content Marketing ---------- */

    'seo-content-writing' => [
        'title' => 'SEO Content Writing Services | Content That Ranks & Sells',
        'meta'  => 'SEO content writing that ranks and converts: keyword-mapped briefs, expert writers, on-page optimization and content refreshes that compound over time.',
        'h1'    => 'SEO Content Writing',
        'lede'  => 'Content built from search data and written for humans: the kind that ranks, earns links and converts.',
        'cat' => 'content', 'parent' => 'content-marketing',
    ],
    'copywriting-services' => [
        'title' => 'Professional Copywriting Services | Copy That Converts',
        'meta'  => 'Professional copywriting services: landing pages, ads, product copy and brand messaging written to persuade, backed by research and conversion testing.',
        'h1'    => 'Copywriting Services',
        'lede'  => 'Words that sell: landing pages, ads and product copy sharpened by research and conversion testing.',
        'cat' => 'content', 'parent' => 'content-marketing',
    ],
    'translation-services' => [
        'title' => 'Translation & Localization Services | Multilingual SEO',
        'meta'  => 'Marketing translation and localization: multilingual SEO content, hreflang implementation and culturally adapted copy that performs in every market.',
        'h1'    => 'Translation Services',
        'lede'  => 'Go global without losing your voice, localized content and multilingual SEO done right.',
        'cat' => 'content', 'parent' => 'content-marketing',
    ],
    'outbound-marketing' => [
        'title' => 'Outbound Marketing Services | Outreach That Books Meetings',
        'meta'  => 'Outbound marketing that books meetings: targeted lists, personalized cold email and LinkedIn sequences, and follow-up systems that respect the inbox.',
        'h1'    => 'Outbound Marketing',
        'lede'  => 'Don\'t wait to be found. Precision outbound, cold email and LinkedIn, that starts conversations with your ideal buyers.',
        'cat' => 'content', 'parent' => 'content-marketing',
    ],

    /* ---------- Web Services ---------- */

    'web-design' => [
        'title' => 'Web Design Services in Chennai, India | Fast, SEO-Ready',
        'meta'  => 'SEO-optimized web design from Chennai for businesses across India, the US & Canada: fast, scalable, user-friendly websites built to convert. Get a free quote.',
        'h1'    => 'Web Design Services',
        'lede'  => 'Beautiful is table stakes. We design websites that load fast, rank well and convert visitors into customers.',
        'cat' => 'web', 'parent' => 'web-services',
    ],
    'ecommerce-web-design' => [
        'title' => 'Ecommerce Web Design | Online Stores Built to Sell More',
        'meta'  => 'Ecommerce web design that sells: conversion-first product pages, frictionless checkout flows and fast, SEO-ready storefronts on any platform. Get a quote.',
        'h1'    => 'Ecommerce Web Design',
        'lede'  => 'Every template decision measured against one metric: does it sell more? Stores designed for conversion from day one.',
        'cat' => 'web', 'parent' => 'web-services',
    ],
    'lead-gen-web-design' => [
        'title' => 'Lead Generation Web Design | Websites That Win Enquiries',
        'meta'  => 'Lead generation web design: persuasive landing pages, trust-building layouts and forms engineered to turn visitors into enquiries. Get a free design audit.',
        'h1'    => 'Lead Gen Web Design',
        'lede'  => 'Your website should be your hardest-working salesperson. We design sites around one job: generating qualified enquiries.',
        'cat' => 'web', 'parent' => 'web-services',
    ],
    'headless-web-design' => [
        'title' => 'Headless Web Design & Development | Fast Composable Sites',
        'meta'  => 'Headless web design and development: blazing-fast composable frontends with the CMS your team loves: future-proof, secure and SEO-strong.',
        'h1'    => 'Headless Web Design',
        'lede'  => 'Decouple your frontend for speed, security and flexibility, headless architecture without the complexity tax.',
        'cat' => 'web', 'parent' => 'web-services',
    ],
    'bigcommerce-web-design' => [
        'title' => 'BigCommerce Web Design | Custom Stores That Scale & Sell',
        'meta'  => 'BigCommerce web design: custom Stencil themes, conversion-focused UX and SEO-ready builds that scale with your catalog. Talk to a BigCommerce specialist.',
        'h1'    => 'BigCommerce Web Design',
        'lede'  => 'Custom BigCommerce storefronts that look premium, load fast and scale with your catalog.',
        'cat' => 'web', 'parent' => 'web-services',
    ],
    'shopify-web-design' => [
        'title' => 'Shopify Web Design Services | Custom Stores Built to Sell',
        'meta'  => 'Shopify web design: custom themes, high-converting product pages and app integrations, beautiful stores that are built to sell. Get a free consultation.',
        'h1'    => 'Shopify Web Design',
        'lede'  => 'Custom Shopify stores that stand out from the template crowd, designed for brand and built for conversion.',
        'cat' => 'web', 'parent' => 'web-services',
    ],
    'woocommerce-web-design' => [
        'title' => 'WooCommerce Design Services | Fast WordPress Ecommerce',
        'meta'  => 'WooCommerce design and development: fast, secure WordPress ecommerce with custom design, optimized checkout and easy content management. Get a quote.',
        'h1'    => 'WooCommerce Design',
        'lede'  => 'The flexibility of WordPress, the power of a real store, WooCommerce builds that stay fast and secure.',
        'cat' => 'web', 'parent' => 'web-services',
    ],
    'magento-web-design' => [
        'title' => 'Magento Web Design Services | Adobe Commerce UX Experts',
        'meta'  => 'Magento web design for serious catalogs: custom Adobe Commerce themes, B2B and B2C UX and performance tuning that keeps big stores fast.',
        'h1'    => 'Magento Design',
        'lede'  => 'Enterprise-grade Magento storefronts, custom design and UX for complex catalogs and demanding buyers.',
        'cat' => 'web', 'parent' => 'web-services',
    ],
    'wordpress-web-design' => [
        'title' => 'WordPress Web Design | Custom, Fast & Easy-to-Edit Sites',
        'meta'  => 'WordPress web design without the bloat: custom lightweight themes, editor-friendly builds, hardened security and SEO-ready structure. Get a free quote.',
        'h1'    => 'WordPress Web Design',
        'lede'  => 'Custom WordPress that\'s fast, secure and easy for your team to edit, no page-builder bloat.',
        'cat' => 'web', 'parent' => 'web-services',
    ],
    'magento-development' => [
        'title' => 'Magento Development Services | Adobe Commerce Engineering',
        'meta'  => 'Magento development by certified engineers: custom modules, ERP and payment integrations, migrations and performance optimization for Adobe Commerce.',
        'h1'    => 'Magento Development',
        'lede'  => 'Custom modules, integrations, migrations and speed, deep Magento engineering for stores that can\'t afford downtime.',
        'cat' => 'web', 'parent' => 'web-services',
    ],
    'custom-design-and-development' => [
        'title' => 'Custom Web Design & Development | Bespoke Digital Builds',
        'meta'  => 'Custom design and development for products that don\'t fit templates: bespoke UX, modern stacks and scalable architecture built around your business.',
        'h1'    => 'Custom Design and Development',
        'lede'  => 'When templates can\'t express your idea, we design and engineer from scratch: bespoke, scalable, yours.',
        'cat' => 'web', 'parent' => 'web-services',
    ],

    /* ---------- Case Studies: By Service ---------- */

    'case-studies/seo-success-stories' => [
        'template' => 'case-list',
        'title' => 'SEO Case Studies | Organic Growth With Real Revenue Numbers',
        'meta'  => 'SEO case studies with real numbers: traffic growth, ranking wins and revenue impact across industries. See how strategic SEO delivers measurable results.',
        'h1'    => 'SEO Success Stories',
        'lede'  => 'Rankings are nice. Revenue is better. Here\'s the organic growth we\'ve delivered.',
        'parent' => 'case-studies', 'cs_type' => 'seo',
    ],
    'case-studies/ppc-growth-campaigns' => [
        'template' => 'case-list',
        'title' => 'PPC Case Studies | Paid Campaigns That Cut CPA & Scaled',
        'meta'  => 'PPC case studies: how we scaled paid campaigns while cutting CPA: Google Ads, Meta and enterprise PPC results with real numbers behind them.',
        'h1'    => 'PPC Growth Campaigns',
        'lede'  => 'Lower CPAs, higher ROAS, scaled budgets, paid media stories with the numbers to prove it.',
        'parent' => 'case-studies', 'cs_type' => 'ppc',
    ],
    'case-studies/social-media-marketing-wins' => [
        'template' => 'case-list',
        'title' => 'Social Media Case Studies | Audience Growth That Paid Off',
        'meta'  => 'Social media case studies: audience growth, engagement lifts and social-driven revenue across B2B and B2C brands: real programs, real results.',
        'h1'    => 'Social Media Marketing Wins',
        'lede'  => 'From invisible to unmissable, social programs that built audiences and moved revenue.',
        'parent' => 'case-studies', 'cs_type' => 'social',
    ],
    'case-studies/content-marketing-results' => [
        'template' => 'case-list',
        'title' => 'Content Marketing Case Studies | Publishing Into Pipeline',
        'meta'  => 'Content marketing case studies: organic growth, lead generation and authority building through strategic content programs that compound over time.',
        'h1'    => 'Content Marketing Results',
        'lede'  => 'Content that compounds, programs that turned publishing into pipeline.',
        'parent' => 'case-studies', 'cs_type' => 'content',
    ],
    'case-studies/branding-and-design-impact' => [
        'template' => 'case-list',
        'title' => 'Branding & Design Case Studies | Redesigns That Converted',
        'meta'  => 'Branding and web design case studies: rebrands, redesigns and conversion lifts that show what great design does for the bottom line.',
        'h1'    => 'Branding & Design Impact',
        'lede'  => 'Design isn\'t decoration, it\'s conversion. Rebrands and redesigns that changed the numbers.',
        'parent' => 'case-studies', 'cs_type' => 'design',
    ],

    /* ---------- Case Studies: By Industry ---------- */

    'case-studies/saas-and-tech' => [
        'template' => 'case-list',
        'title' => 'SaaS & Tech Case Studies | Signups, Demos & Pipeline Wins',
        'meta'  => 'How SaaS and tech companies grow with us: product-led SEO, demand gen and content engines that drive signups, demos and qualified pipeline.',
        'h1'    => 'SaaS & Tech Case Studies',
        'lede'  => 'Signups, demos and pipeline, growth stories from software and technology clients.',
        'parent' => 'case-studies', 'cs_type' => 'saas',
    ],
    'case-studies/ecommerce' => [
        'template' => 'case-list',
        'title' => 'Ecommerce Case Studies | Revenue, ROAS & Conversion Wins',
        'meta'  => 'Ecommerce growth case studies: organic revenue lifts, ROAS improvements and store redesigns that increased conversion for online retailers.',
        'h1'    => 'E-commerce Case Studies',
        'lede'  => 'More traffic, higher conversion, bigger baskets, ecommerce growth stories with real revenue numbers.',
        'parent' => 'case-studies', 'cs_type' => 'ecom',
    ],
    'case-studies/healthcare' => [
        'template' => 'case-list',
        'title' => 'Healthcare Case Studies | Patient Growth Done Compliantly',
        'meta'  => 'Healthcare marketing case studies: patient acquisition, local SEO for practices and compliant content programs that built trust and bookings.',
        'h1'    => 'Healthcare Case Studies',
        'lede'  => 'Compliant, trustworthy and effective, how healthcare brands grew patient volume with us.',
        'parent' => 'case-studies', 'cs_type' => 'health',
    ],
    'case-studies/real-estate' => [
        'template' => 'case-list',
        'title' => 'Real Estate Case Studies | Property Lead Generation Wins',
        'meta'  => 'Real estate marketing case studies: lead generation for developers and agencies through local SEO, PPC and high-converting property sites.',
        'h1'    => 'Real Estate Case Studies',
        'lede'  => 'Site visits that start as searches, how property brands filled their pipelines.',
        'parent' => 'case-studies', 'cs_type' => 'realestate',
    ],
    'case-studies/education' => [
        'template' => 'case-list',
        'title' => 'Education Marketing Case Studies | Enrollment Growth Wins',
        'meta'  => 'Education marketing case studies: enrollment growth for institutes and universities through SEO, paid media and content marketing programs.',
        'h1'    => 'Education Case Studies',
        'lede'  => 'From first enquiry to enrollment, growth stories from education brands.',
        'parent' => 'case-studies', 'cs_type' => 'education',
    ],
    'case-studies/manufacturing' => [
        'template' => 'case-list',
        'title' => 'Manufacturing Case Studies | RFQ & Industrial SEO Growth',
        'meta'  => 'Manufacturing marketing case studies: RFQ growth, technical SEO for industrial catalogs and B2B demand generation that reached real buyers.',
        'h1'    => 'Manufacturing Case Studies',
        'lede'  => 'Industrial buyers do their research online, here\'s how manufacturers became the answer.',
        'parent' => 'case-studies', 'cs_type' => 'manufacturing',
    ],
    'case-studies/edutech' => [
        'template' => 'case-list',
        'title' => 'EduTech Case Studies | Lower CAC, Faster Platform Growth',
        'meta'  => 'EduTech marketing case studies: user acquisition and organic growth for learning platforms through SEO, content and performance marketing.',
        'h1'    => 'EduTech Case Studies',
        'lede'  => 'Learning platforms live and die on acquisition cost, here\'s how we brought it down.',
        'parent' => 'case-studies', 'cs_type' => 'edutech',
    ],
];

/* ---------- Register content pages: blog posts, case studies, resources ---------- */

require __DIR__ . '/blog.php';
require __DIR__ . '/cases.php';
require __DIR__ . '/resources.php';
require __DIR__ . '/landings.php';
require __DIR__ . '/content-landings.php';
/* The update helpers are needed here (menu labels) and in the admin panel,
   which do not all load the page kits first. */
require_once dirname(__DIR__) . '/update-kit.php';
$UPDATES = require __DIR__ . '/updates.php';

/* Slugs that ship with the site. Admin uses these to tell an edited built-in
   (which can be reverted) apart from a post created from scratch. */
$BLOG_BUILTIN      = array_keys($BLOG);
$CASES_BUILTIN     = array_keys($CASES);
$RESOURCES_BUILTIN = array_keys($RESOURCES);
$UPDATES_BUILTIN   = array_keys($UPDATES);

/**
 * Every admin edit, without touching the database on a public page view.
 *
 * Reading content_overrides here used to run three queries on every request,
 * crawlers included. The database never got to sleep, used up its monthly
 * compute allowance, and then refused all connections: the admin panel stopped
 * loading and new leads stopped being stored.
 *
 * Instead the overrides are read once an hour through the CDN-cached
 * /content-overrides.json (see api/index.php) and kept for ten minutes in the
 * function's /tmp. The admin panel still reads the database directly, so it
 * always shows the true state. An admin edit reaches the public site within
 * about an hour.
 */
function content_overrides_all(): array
{
    static $all = null;
    if ($all !== null) {
        return $all;
    }
    if (str_starts_with((string) ($GLOBALS['slug'] ?? ''), 'admin')) {
        try {
            $all = [];
            foreach (db()->query('SELECT content_type, slug, data FROM content_overrides')->fetchAll() as $r) {
                $all[$r['content_type']][$r['slug']] = json_decode($r['data'], true);
            }
            return $all;
        } catch (Throwable $e) {
            error_log('content_overrides_all: ' . $e->getMessage());
            return $all = [];
        }
    }
    if (env_var('VERCEL') === null) {
        return $all = [];   // Local development has no database.
    }

    $cache = sys_get_temp_dir() . '/vt-content-overrides.json';
    $age = is_file($cache) ? time() - (int) filemtime($cache) : PHP_INT_MAX;
    if ($age < 600) {
        return $all = (json_decode((string) @file_get_contents($cache), true) ?: []);
    }

    $ch = curl_init(SITE_URL . '/content-overrides.json');
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 2, CURLOPT_CONNECTTIMEOUT => 1]);
    $data = json_decode((string) curl_exec($ch), true);
    if (is_array($data) && !empty($data['ok'])) {
        $all = $data['rows'] ?? [];
        @file_put_contents($cache, json_encode($all, JSON_UNESCAPED_UNICODE));
        return $all;
    }
    // Keep serving the last good copy rather than dropping every admin edit.
    @touch($cache);
    return $all = (json_decode((string) @file_get_contents($cache), true) ?: []);
}

/**
 * Layer admin edits over the shipped content.
 *
 * Saved fields win; anything the admin form does not cover (article sections,
 * takeaways, FAQs) falls back to the original post, so editing only the SEO
 * fields of a built-in article never blanks its body.
 */
function apply_content_overrides(array $base, string $contentType, array $defaults): array
{
    foreach ((content_overrides_all()[$contentType] ?? []) as $slug => $item) {
        $slug = (string) $slug;
        if (!is_string($slug) || !preg_match('/^[a-z][a-z0-9-]*$/', $slug) || !is_array($item)) {
            continue;
        }
        // Drop empty values so a blank optional field cannot erase original content.
        $item = array_filter($item, fn($v) => $v !== '' && $v !== null && $v !== []);
        $base[$slug] = $item + ($base[$slug] ?? $defaults);
    }
    return $base;
}

/* Search titles and descriptions. Laid over the shipped content before admin
   edits, so anything saved in the admin panel still wins. */
$SEO_META = require __DIR__ . '/seo-meta.php';
foreach ($BLOG as $k => $v) {
    if (isset($SEO_META['blog/' . $k])) {
        [$BLOG[$k]['title'], $BLOG[$k]['meta']] = $SEO_META['blog/' . $k];
    }
}
foreach ($CASES as $k => $v) {
    if (isset($SEO_META['case-studies/' . $k])) {
        [$CASES[$k]['title'], $CASES[$k]['meta']] = $SEO_META['case-studies/' . $k];
    }
}
foreach ($RESOURCES as $k => $v) {
    $key = ($v['type'] === 'guide' ? 'guides/' : 'ebooks/') . $k;
    if (isset($SEO_META[$key])) {
        [$RESOURCES[$k]['title'], $RESOURCES[$k]['meta']] = $SEO_META[$key];
    }
}
foreach ($UPDATES as $k => $v) {
    if (isset($SEO_META['updates/' . $k])) {
        [$UPDATES[$k]['title'], $UPDATES[$k]['meta']] = $SEO_META['updates/' . $k];
    }
}
foreach ($PAGES as $k => $p) {
    if (isset($SEO_META[$k])) {
        [$PAGES[$k]['title'], $PAGES[$k]['meta']] = $SEO_META[$k];
    }
}

$BLOG = apply_content_overrides($BLOG, 'blog', [
    'category' => 'Strategy', 'intent' => 'Informational',
    'sections' => [], 'takeaways' => [], 'faqs' => [],
]);

/* Drafts: a post marked 'draft' => true is held back everywhere, so its URL
   is a 404 and it is left out of the blog lists, related posts, the sitemap,
   the feed and llms.txt. Delete the flag to publish it again. */
$BLOG = array_filter($BLOG, static fn(array $b): bool => empty($b['draft']));

$CASES = apply_content_overrides($CASES, 'case', [
    'industry' => '', 'service' => 'multi', 'date' => '', 'challenge' => '',
    'solution' => '', 'results' => [], 'quote' => null, 'cta' => ['', '', ''],
]);

$RESOURCES = apply_content_overrides($RESOURCES, 'resource', [
    'type' => 'ebook', 'category' => '', 'size' => '', 'description' => '',
    'topics' => [], 'url' => '', 'image' => '', 'cta' => ['', '', ''],
]);

/* Search updates: admin edits laid over the shipped ones, drafts held back
   (the admin list reads $UPDATES_ALL so a draft stays editable), newest first. */
$UPDATES = apply_content_overrides($UPDATES, 'update', [
    'type' => 'Other', 'status' => 'info', 'started' => '', 'finished' => '', 'rollout' => '', 'scope' => '',
    'source' => '', 'read' => '', 'sections' => [], 'takeaways' => [], 'faqs' => [], 'cta' => ['', '', ''],
]);
$UPDATES = array_filter($UPDATES, static fn ($u): bool => is_array($u) && !empty($u['h1']) && !empty($u['date']));
uasort($UPDATES, static fn (array $a, array $b): int => strcmp((string) (($b['started'] ?? '') ?: $b['date']), (string) (($a['started'] ?? '') ?: $a['date'])));
$UPDATES_ALL = $UPDATES;
$UPDATES = array_filter($UPDATES, static fn (array $u): bool => empty($u['draft']));

foreach ($BLOG as $bslug => $b) {
    $PAGES['blog/' . $bslug] = [
        'template' => 'blog-post',
        'title' => $b['title'], 'meta' => $b['meta'], 'h1' => $b['h1'], 'lede' => $b['lede'],
        'parent' => 'blog', 'post' => $bslug, 'date' => $b['modified'] ?? $b['date'],
        // Read only by the XML sitemap, for an accurate lastmod.
        'date' => $b['modified'] ?? $b['date'] ?? '',
    ];
}

foreach ($CASES as $cslug => $c) {
    $PAGES['case-studies/' . $cslug] = [
        'template' => 'case-detail',
        'title' => $c['title'], 'meta' => $c['meta'], 'h1' => $c['h1'], 'lede' => $c['lede'],
        'parent' => 'case-studies', 'case' => $cslug,
    ];
}

foreach ($RESOURCES as $rslug => $r) {
    $hub = $r['type'] === 'guide' ? 'guides' : 'ebooks';
    $PAGES[$hub . '/' . $rslug] = [
        'template' => 'resource',
        'title' => $r['title'], 'meta' => $r['meta'], 'h1' => $r['h1'], 'lede' => $r['lede'],
        'parent' => $hub, 'resource' => $rslug,
    ];
}

foreach ($UPDATES as $uslug => $u) {
    $PAGES['updates/' . $uslug] = [
        'template' => 'update-post',
        'title' => $u['title'] ?? $u['h1'], 'meta' => $u['meta'] ?? '', 'h1' => $u['h1'], 'lede' => $u['lede'] ?? '',
        'crumb' => update_short($u),
        'parent' => 'updates', 'update' => $uslug,
        /* header.php emits these as FAQPage; the template renders the same list. */
        'faqs' => $u['faqs'] ?? [],
        // Read only by the XML sitemap, for an accurate lastmod.
        'date' => $u['modified'] ?? $u['date'],
    ];
}
if ($UPDATES) {
    $PAGES['updates']['date'] = max((string) ($PAGES['updates']['date'] ?? ''), (string) max(array_map(static fn ($u) => (string) ($u['modified'] ?? $u['date']), $UPDATES)));
}

/* The Updates menu lists the newest updates, so a new one added in the admin
   panel appears in the header without a code change. */
if (isset($NAV) && is_array($NAV)) {
    foreach ($NAV as &$nav_item) {
        if (($nav_item['url'] ?? '') === '/updates/' && !empty($nav_item['columns'])) {
            /* The column heading already links to /updates/, so the list holds updates only. */
            $nav_item['columns'][0]['items'] = array_map(
                static fn (string $s, array $u): array => [update_short($u), '/updates/' . $s . '/', update_line($u)],
                array_keys(array_slice($UPDATES, 0, 4, true)),
                array_values(array_slice($UPDATES, 0, 4, true))
            );
        }
    }
    unset($nav_item);
}
