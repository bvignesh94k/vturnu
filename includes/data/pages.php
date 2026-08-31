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
        'title'    => 'Digital Marketing Agency in Chennai for Search & AI | VTurnU',
        'meta'     => 'Chennai digital growth company for B2B firms and SMEs. We make you visible in Google and in AI answers, then turn that into qualified enquiries.',
        /* Social cards get their own pair: the title tag carries the local
           commercial intent, while a shared link should lead with the idea. */
        'og_title' => 'Turn visibility into qualified demand | VTurnU',
        'og_meta'  => 'Your buyers search Google, ask ChatGPT and read AI summaries before they ever reach your site. VTurnU makes growing businesses discoverable across all of it.',
        'h1'       => 'Turn visibility into qualified demand.',
        'lede'     => 'VTurnU makes growing businesses discoverable across search engines and AI answer engines, then turns that attention into enquiries your sales team can close.',
    ],

    'about-us' => [
        'template' => 'about',
        'title' => 'About Us | The Team Behind Chennai\'s Results-First Agency',
        'meta'  => 'We\'re a Chennai-born digital marketing team helping brands across India, the US & Canada grow with SEO, AI search, paid media and web. See our story and values.',
        'h1'    => 'About VTurnU',
        'lede'  => 'We help you grow your brand, your website, and your ideas, with strategy, creativity and technology working together.',
        'parent' => null,
    ],

    'contact-us' => [
        'template' => 'contact',
        'title' => 'Contact Us | Get a Free Digital Marketing Quote in 24 Hours',
        'meta'  => 'Talk to a digital marketing specialist today. Free audit, honest advice and a custom quote within 24 hours: for businesses in Chennai, India, the US & Canada.',
        'h1'    => 'Let\'s Talk Possibilities.',
        'lede'  => 'Whether you\'re scaling your brand, launching a new product, or reimagining your digital presence: we\'re here to listen, think, and build with you.',
    ],

    'free-seo-audit' => [
        'template' => 'audit',
        'title' => 'Free SEO Audit Tool | Instant Website & AI Search Check',
        'meta'  => 'Run a free instant SEO audit of your website. Check indexing, speed, on-page tags and whether ChatGPT, Perplexity and Google AI can even read your site.',
        'h1'    => 'Free SEO & AI Search Audit',
        'lede'  => 'Enter your website and get an instant report on what is holding your rankings back, including whether AI search engines can read your site at all. No signup, results in seconds.',
    ],

    /* VTurnU's own SaaS product. This page sells VTurnAI on our domain rather
       than bouncing visitors straight off-site, so the search intent
       ("AI visibility tool", "track brand in ChatGPT") lands on a page we
       rank, and only converted intent leaves for vturnai.com. */
    'ai-visibility-tool' => [
        'template' => 'product',
        'title' => 'AI Visibility Tool | Track Your Brand in ChatGPT & Gemini',
        // Trimmed to 159 chars: at 166 it was the one description on the site
        // at risk of truncation in the SERP.
        'meta'  => 'See how often ChatGPT, Gemini, Perplexity and AI Overviews mention your brand. VTurnAI scores your SEO, AEO, GEO and HEO visibility. 7-day free trial.',
        'h1'    => 'Track Your Brand Across AI Search',
        'lede'  => 'VTurnAI is our own monitoring platform. It watches ChatGPT, Gemini, Claude, Perplexity, Grok and Copilot, tells you how often each one mentions or recommends you, and scores exactly what to fix first.',
    ],

    'pricing' => [
        'template' => 'pricing',
        'title' => 'Digital Marketing Pricing & Packages | Clear, Flexible Plans',
        'meta'  => 'Transparent pricing: web design & development billed once in month one; SEO, AEO, SMO and Google & Meta ads run monthly with budgets built around your goals.',
        'h1'    => 'Transparent Pricing, Measurable Outcomes',
        'lede'  => 'No hidden fees, no lock-ins. Web development is a one-time build cost: marketing runs monthly, sized to your goals and budget.',
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
        'title' => 'Digital Marketing Services in Chennai | SEO, Ads & Growth',
        'meta'  => 'Full-funnel digital marketing from Chennai for brands across India, the US & Canada: SEO, paid ads, social, email and consulting. Book a free strategy call.',
        'h1'    => 'Digital Marketing Services',
        'lede'  => 'One partner for search, paid media and online marketing: strategy, execution and reporting under one roof.',
        'children' => ['seo', 'seo-services', 'ai-seo', 'ecommerce-seo', 'lead-generation-seo', 'local-seo', 'enterprise-seo', 'franchise-seo', 'paid-advertising', 'google-ads', 'facebook-ads', 'instagram-ads', 'enterprise-ppc', 'online-marketing', 'social-media-marketing', 'email-marketing', 'reputation-management', 'digital-marketing-consulting'],
    ],

    'seo' => [
        'template' => 'hub',
        'title' => 'SEO Services by Type & Industry | Rank Higher, Sell More',
        'meta'  => 'Explore SEO services by type or industry: classic SEO, AI SEO, ecommerce, lead gen, local, enterprise and franchise programs that rank and convert. Free audit.',
        'h1'    => 'SEO That Ranks, and Converts',
        'lede'  => 'Pick your SEO by type or by industry. Every program is built on technical excellence, quality content and clean link acquisition.',
        'parent' => 'digital-marketing',
        'children' => ['seo-services', 'ai-seo', 'ecommerce-seo', 'lead-generation-seo', 'local-seo', 'enterprise-seo', 'franchise-seo', 'ecommerce-seo-for-fashion', 'ecommerce-seo-for-beauty', 'ecommerce-seo-for-jewelry', 'ecommerce-seo-for-health-and-wellness', 'ecommerce-seo-for-cannabis', 'ecommerce-seo-for-hemp-and-cbd', 'lead-gen-seo-for-business-industrial', 'lead-gen-seo-for-artists', 'lead-gen-seo-for-lawyers', 'lead-gen-seo-for-dentists', 'lead-gen-seo-for-finance'],
    ],

    'ai' => [
        'template' => 'hub',
        'title' => 'AI Marketing Services | AI SEO, ChatGPT & AI Development',
        'meta'  => 'Win visibility in AI search: AI SEO, ChatGPT SEO, Perplexity SEO, Google AI Overviews optimization and custom AI development. Get found where buyers now search.',
        'h1'    => 'AI Services for the New Search Era',
        'lede'  => 'Search is changing. We help your brand get found, and chosen: inside ChatGPT, Perplexity, Google AI Overviews and beyond.',
        'children' => ['ai-seo', 'ai-development', 'google-ai-overviews-seo', 'chatgpt-seo', 'perplexity-seo', 'selling-on-chatgpt', 'ai-policy', 'ai-blog-posts'],
    ],

    'content-marketing' => [
        'template' => 'hub',
        'title' => 'Content Marketing Services | Strategy, Writing & Results',
        'meta'  => 'Partner with a trusted content marketing team: SEO content writing, copywriting, translation, email and outbound, ROI-driven content that attracts and converts.',
        'h1'    => 'Content Marketing That Earns Attention',
        'lede'  => 'Strategy, writing, distribution and measurement: content programs built to attract, nurture and convert your audience.',
        'children' => ['seo-content-writing', 'copywriting-services', 'translation-services', 'email-marketing-services', 'outbound-marketing'],
    ],

    'web-services' => [
        'template' => 'hub',
        'title' => 'Web Design & Development Services | Fast, SEO-Ready Sites',
        'meta'  => 'Fast, SEO-ready websites built to convert: ecommerce, lead gen and headless builds on Shopify, WordPress, Magento and more. Get a free build quote.',
        'h1'    => 'Websites Built to Grow Your Business',
        'lede'  => 'Design and development by goal or by platform: fast, secure, SEO-ready websites that turn visitors into customers.',
        'children' => ['web-design', 'ecommerce-web-design', 'lead-gen-web-design', 'headless-web-design', 'ai-development', 'bigcommerce-web-design', 'shopify-web-design', 'woocommerce-web-design', 'magento-web-design', 'wordpress-web-design', 'magento-development', 'custom-design-and-development'],
    ],

    'case-studies' => [
        'template' => 'hub',
        'title' => 'Case Studies | Real Results From SEO, PPC & Web Projects',
        'meta'  => 'Real results from real engagements: SEO success stories, PPC growth campaigns, social wins and content results across SaaS, ecommerce, healthcare and more.',
        'h1'    => 'Results We\'re Proud Of',
        'lede'  => 'Browse our work by service or by industry, the numbers speak for themselves.',
        'children' => ['case-studies/jewelry-brand-organic-revenue-growth', 'case-studies/saas-ppc-demo-pipeline', 'case-studies/clinic-local-seo-patient-growth', 'case-studies/fashion-brand-roas-turnaround', 'case-studies/real-estate-lead-generation-engine', 'case-studies/law-firm-lead-gen-website', 'case-studies/edutech-content-engine-signups', 'case-studies/manufacturer-rfq-seo-program', 'case-studies/restaurant-chain-local-domination', 'case-studies/fitness-brand-social-community', 'case-studies/seo-success-stories', 'case-studies/ppc-growth-campaigns', 'case-studies/social-media-marketing-wins', 'case-studies/content-marketing-results', 'case-studies/branding-and-design-impact', 'case-studies/saas-and-tech', 'case-studies/ecommerce', 'case-studies/healthcare', 'case-studies/real-estate', 'case-studies/education', 'case-studies/manufacturing', 'case-studies/edutech'],
    ],

    /* ---------- Resources ---------- */

    'blog' => [
        'template' => 'blog-list',
        'title' => 'Digital Marketing Blog | SEO, AI Search & Growth Playbooks',
        'meta'  => 'Actionable articles on SEO, AI search, paid media, content and conversion: playbooks, comparisons and pricing guides written by working strategists.',
        'h1'    => 'Growth Playbooks & Insights',
        'lede'  => 'The same thinking our clients pay for, published free. SEO, AI search, paid media, content and conversion, without the fluff.',
    ],
    'ebooks' => [
        'template' => 'resource-list',
        'title' => 'Free Digital Marketing E-books | Practical Growth Playbooks',
        'meta'  => 'Download free e-books on AI search, ecommerce SEO, lead gen websites, paid media budgeting and more. Practical, in-depth and written by practitioners.',
        'h1'    => 'Free E-books, Written by Practitioners',
        'lede'  => 'Deep-dive playbooks you can download and share with your team, each one distilled from real client engagements.',
        'rtype' => 'ebook',
    ],
    'guides' => [
        'template' => 'resource-list',
        'title' => 'Complete Digital Marketing Guides | Free Evergreen Resources',
        'meta'  => 'Complete guides to local SEO, AI SEO, content strategy, ecommerce platforms, B2B lead gen and Google Ads: maintained, evergreen and free to read.',
        'h1'    => 'The Complete Guides',
        'lede'  => 'Evergreen, comprehensive resources maintained by our strategists: bookmark them, they keep getting better.',
        'rtype' => 'guide',
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
    'email-marketing-services' => [
        'title' => 'Email Marketing Services | Nurture, Convert & Retain',
        'meta'  => 'Boost ROI with expert email marketing services: strategy, automation flows, campaign copy and deliverability. Get a free email marketing plan today.',
        'h1'    => 'Email Marketing Services',
        'lede'  => 'Nurture leads, increase sales and keep customers coming back, full-service email from strategy to send.',
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
        'lede'  => 'From prospectus downloads to enrollments, growth stories from education brands.',
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

/* Slugs that ship with the site. Admin uses these to tell an edited built-in
   (which can be reverted) apart from a post created from scratch. */
$BLOG_BUILTIN      = array_keys($BLOG);
$CASES_BUILTIN     = array_keys($CASES);
$RESOURCES_BUILTIN = array_keys($RESOURCES);

/**
 * Layer admin edits over the shipped content.
 *
 * Saved fields win; anything the admin form does not cover (article sections,
 * takeaways, FAQs) falls back to the original post, so editing only the SEO
 * fields of a built-in article never blanks its body.
 *
 * Reads the content_overrides table (see db/schema.sql) instead of a local
 * *-custom.json file, since Vercel's filesystem cannot hold admin edits
 * between requests. Fails soft to $base on any database error: a DB hiccup
 * should degrade to "no overrides applied" rather than a broken page.
 */
function apply_content_overrides(array $base, string $contentType, array $defaults): array
{
    try {
        $stmt = db()->prepare('SELECT slug, data FROM content_overrides WHERE content_type = ?');
        $stmt->execute([$contentType]);
        $rows = $stmt->fetchAll();
    } catch (Throwable $e) {
        error_log('apply_content_overrides(' . $contentType . '): ' . $e->getMessage());
        return $base;
    }
    foreach ($rows as $row) {
        $slug = $row['slug'];
        $item = json_decode($row['data'], true);
        if (!is_string($slug) || !preg_match('/^[a-z][a-z0-9-]*$/', $slug) || !is_array($item)) {
            continue;
        }
        // Drop empty values so a blank optional field cannot erase original content.
        $item = array_filter($item, fn($v) => $v !== '' && $v !== null && $v !== []);
        $base[$slug] = $item + ($base[$slug] ?? $defaults);
    }
    return $base;
}

$BLOG = apply_content_overrides($BLOG, 'blog', [
    'category' => 'Strategy', 'intent' => 'Informational',
    'sections' => [], 'takeaways' => [], 'faqs' => [],
]);

$CASES = apply_content_overrides($CASES, 'case', [
    'industry' => '', 'service' => 'multi', 'date' => '', 'challenge' => '',
    'solution' => '', 'results' => [], 'quote' => null, 'cta' => ['', '', ''],
]);

$RESOURCES = apply_content_overrides($RESOURCES, 'resource', [
    'type' => 'ebook', 'category' => '', 'size' => '', 'description' => '',
    'topics' => [], 'url' => '', 'image' => '', 'cta' => ['', '', ''],
]);

foreach ($BLOG as $bslug => $b) {
    $PAGES['blog/' . $bslug] = [
        'template' => 'blog-post',
        'title' => $b['title'], 'meta' => $b['meta'], 'h1' => $b['h1'], 'lede' => $b['lede'],
        'parent' => 'blog', 'post' => $bslug,
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
