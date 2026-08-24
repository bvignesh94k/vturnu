<?php
/**
 * Downloadable resources: 10 e-books + 6 guides, each with a dedicated landing page.
 * Keyed by slug (lowercase + hyphens). Rendered by templates/resource.php
 * at /ebooks/<slug>/ or /guides/<slug>/ based on type.
 * Fields: type (ebook|guide), title, meta, h1, lede, size (pages/read),
 *         learn [], chapters [[title, desc]], who [], faqs [[q,a]]
 */

$RESOURCES = [

/* ---------------- E-BOOKS ---------------- */

'ai-search-playbook' => [
    'type' => 'ebook', 'size' => '64 pages',
    'title' => 'The AI Search Playbook: Win ChatGPT & AI Overviews (Free)',
    'meta'  => 'Free 64-page e-book: how AI engines pick sources, the AEO content system, entity building and measurement. The complete playbook for AI-era visibility.',
    'h1'    => 'The AI Search Playbook',
    'lede'  => 'Everything a marketing leader needs to win visibility inside ChatGPT, Perplexity, Gemini and Google AI Overviews: how engines choose sources, the content system that earns citations, and how to measure it monthly.',
    'learn' => [
        'How generative engines retrieve, judge and cite sources: in plain language',
        'The answer-first page structure that wins citations (with templates)',
        'Entity and schema foundations most brands are missing',
        'A monthly measurement system for AI citation share',
    ],
    'chapters' => [
        ['The shift from links to answers', 'What actually changed in search behavior, with the data that matters for budget decisions.'],
        ['How engines choose sources', 'Retrieval, authority and corroboration, the three-layer model behind every citation.'],
        ['The AEO content system', 'Answer boxes, question architecture and formatting patterns, with before/after examples.'],
        ['Entities, schema and trust', 'Making your brand machine-legible: markup, consistency and the corroboration layer.'],
        ['Measurement and reporting', 'Prompt-sampling methodology, citation-share dashboards and realistic timelines.'],
    ],
    'who' => ['Marketing leaders planning next year\'s search budget', 'SEO teams extending into AI visibility', 'Founders whose buyers research in ChatGPT'],
    'faqs' => [
        ['Is this beginner-friendly?', 'Yes: concepts are explained from zero, but the systems and templates are the same ones we run for clients.'],
        ['How current is it?', 'Updated quarterly as engine behavior evolves; your download includes future revisions.'],
    ],
],

'ecommerce-seo-from-crawl-to-cart' => [
    'type' => 'ebook', 'size' => '48 pages',
    'title' => 'Ecommerce SEO: From Crawl to Cart (Free E-book)',
    'meta'  => 'Free 48-page e-book covering the complete ecommerce SEO system: architecture, faceted navigation, product schema, category content and content-to-cart funnels.',
    'h1'    => 'Ecommerce SEO: From Crawl to Cart',
    'lede'  => 'The complete technical and content system for growing store revenue organically: architecture, crawl control, product data, category strategy and the content layer that feeds commerce.',
    'learn' => [
        'Category-first architecture that matches how buyers search',
        'Faceted navigation control: what to index, what to kill',
        'Product schema and review systems that win rich results',
        'The buying-guide content layer that compounds store revenue',
    ],
    'chapters' => [
        ['Store architecture', 'URL strategy, category trees and internal linking for stores of every size.'],
        ['Crawl control', 'Facets, filters, pagination and duplicates, the discipline that protects everything else.'],
        ['Product pages that rank and convert', 'Descriptions, schema, reviews and out-of-stock handling done right.'],
        ['Category pages as landing pages', 'Copy, FAQs and merchandising for the pages where buying searches land.'],
        ['The content-commerce funnel', 'Guides and comparisons that capture research demand and route it to checkout.'],
    ],
    'who' => ['Store owners on Shopify, Woo, Magento or custom stacks', 'Ecommerce marketers who own the organic number', 'Developers who want SEO-safe store decisions'],
    'faqs' => [
        ['Does it apply to my platform?', 'Yes, principles are platform-agnostic with platform-specific notes where behavior differs.'],
        ['Is it useful for small catalogs?', 'Absolutely; small stores skip the scale problems and get to revenue moves faster.'],
    ],
],

'lead-gen-website-blueprint' => [
    'type' => 'ebook', 'size' => '36 pages',
    'title' => 'The Lead Gen Website Blueprint (Free E-book)',
    'meta'  => 'Free 36-page e-book: how to structure, write and design a service-business website that turns visitors into enquiries: offers, trust, forms and speed.',
    'h1'    => 'The Lead Gen Website Blueprint',
    'lede'  => 'How to structure, write and design a website that turns visitors into enquiries, the page-by-page blueprint we use when building lead-generation sites for clients.',
    'learn' => [
        'The page architecture every service business needs (and what to skip)',
        'Offer design: why "contact us" fails and what converts instead',
        'Trust stacking: the evidence sequence that turns skeptics into enquiries',
        'Form, speed and mobile standards with checklists',
    ],
    'chapters' => [
        ['Lead-gen site anatomy', 'Home, service, proof and conversion pages, the jobs of each and how they interlink.'],
        ['Offers and CTAs', 'Lead magnets, audits and consult offers, with placement patterns that convert.'],
        ['Writing for conversion', 'Headline formulas, objection-driven copy and message match with traffic.'],
        ['Trust architecture', 'Reviews, cases, credentials and guarantees, sequenced where doubt actually occurs.'],
        ['Technical standards', 'Speed, forms, tracking and the launch checklist.'],
    ],
    'who' => ['Service business owners planning a build or rebuild', 'Marketers whose sites get traffic but not enquiries', 'Designers who want conversion logic behind decisions'],
    'faqs' => [
        ['Is this only for new websites?', 'No, most readers apply it to existing sites; the blueprint doubles as an audit checklist.'],
    ],
],

'paid-media-budgeting-for-founders' => [
    'type' => 'ebook', 'size' => '28 pages',
    'title' => 'Paid Media Budgeting for Founders (Free E-book)',
    'meta'  => 'Free e-book for founders: how much to spend on ads, how to split channels, when to scale and the unit-economics math that keeps growth from burning runway.',
    'h1'    => 'Paid Media Budgeting for Founders',
    'lede'  => 'How much to spend, where to spend it, and when to scale, the unit-economics-first approach to paid media that grows revenue without burning runway.',
    'learn' => [
        'Working backward from CAC targets to honest budgets',
        'Minimum viable budgets per channel, and when small budgets just buy noise',
        'The scale decision: signals that spend increases will hold efficiency',
        'Reading agency and platform reporting without being misled',
    ],
    'chapters' => [
        ['Unit economics first', 'LTV, margin and payback windows, the three numbers that set every budget.'],
        ['Channel minimums and fit', 'What meaningful tests cost on search, social and marketplaces.'],
        ['Structuring early spend', 'Validation campaigns, measurement setup and the first ninety days.'],
        ['Scaling without decay', 'Audience saturation, creative velocity and diminishing-return signals.'],
        ['Reporting that tells the truth', 'Brand-vs-nonbrand, incrementality basics and the vanity-metric traps.'],
    ],
    'who' => ['Founders funding their first serious ad budgets', 'Operators sanity-checking agency proposals', 'Finance-minded leaders who want marketing math'],
    'faqs' => [
        ['Is this India-specific?', 'Numbers are given in ₹ and $ with market notes for both; the math is universal.'],
    ],
],

'local-seo-for-multi-location' => [
    'type' => 'ebook', 'size' => '40 pages',
    'title' => 'Local SEO for Multi-Location Businesses (Free E-book)',
    'meta'  => 'Free 40-page e-book: profile systems, location pages, review engines and reporting for clinics, chains and franchises operating across multiple locations.',
    'h1'    => 'Local SEO for Multi-Location Businesses',
    'lede'  => 'Clinics, chains, franchises: multi-location local SEO is a systems problem. This e-book covers the profile, page, review and reporting systems that win every neighborhood you operate in.',
    'learn' => [
        'Profile management at scale without inconsistency creep',
        'Location pages that rank: templates without template penalties',
        'Review velocity engines your front-line staff will actually run',
        'Per-location reporting that finds underperformers fast',
    ],
    'chapters' => [
        ['The multi-location model', 'How local ranking really works across service areas and pins.'],
        ['Profile systems', 'Standardization, categories, photos and posting calendars across locations.'],
        ['Location page architecture', 'Unique-where-it-matters templates, schema and internal linking.'],
        ['The review engine', 'Operational ask-flows, response systems and rating recovery.'],
        ['Measurement', 'Map-rank tracking by neighborhood and profile-action reporting.'],
    ],
    'who' => ['Healthcare groups and clinic chains', 'Retail, F&B and service franchises', 'Agencies managing multi-location clients'],
    'faqs' => [
        ['Does it work for two locations?', 'Yes, systems shrink gracefully; even single locations benefit from the review and page chapters.'],
    ],
],

'business-content-engine' => [
    'type' => 'ebook', 'size' => '44 pages',
    'title' => 'The B2B Content Engine (Free E-book) | Pipeline From Publishing',
    'meta'  => 'Free 44-page e-book: the B2B content system that creates pipeline: bottom-funnel first strategy, expert-interview production, distribution and attribution.',
    'h1'    => 'The B2B Content Engine',
    'lede'  => 'How B2B companies turn publishing into pipeline: the bottom-funnel-first strategy, an expert-interview production system, distribution that compounds and attribution you can defend.',
    'learn' => [
        'Why bottom-funnel content comes first, and the pages that print pipeline',
        'The interview-driven production system that scales expertise, not filler',
        'Distribution loops: social, email and sales enablement from one asset',
        'Attribution models that survive CFO scrutiny',
    ],
    'chapters' => [
        ['Strategy: revenue-mapped topics', 'Comparison, pricing, alternatives and use-case content before thought leadership.'],
        ['Production: the expert engine', 'Interview workflows that turn practitioner knowledge into ranked assets.'],
        ['Distribution: one asset, many surfaces', 'Atomization, founder channels and sales-motion integration.'],
        ['Measurement', 'Content-touch analysis, self-reported attribution and pipeline influence.'],
    ],
    'who' => ['B2B marketing leaders building content programs', 'Founders doing founder-led content', 'Content teams tired of traffic-without-pipeline'],
    'faqs' => [
        ['We have a technical product, will this work?', 'Especially then: the expert-interview system exists precisely to convert deep knowledge into content buyers find.'],
    ],
],

'website-speed-handbook' => [
    'type' => 'ebook', 'size' => '32 pages',
    'title' => 'The Website Speed Handbook (Free E-book) | Core Web Vitals',
    'meta'  => 'Free e-book: the business owner\'s handbook to website speed: Core Web Vitals explained, the usual culprits, fix priorities and how to hold vendors accountable.',
    'h1'    => 'The Website Speed Handbook',
    'lede'  => 'Speed is revenue infrastructure. This handbook explains Core Web Vitals in plain language, identifies the usual culprits, and gives you the fix priorities, plus the standards to hold any vendor to.',
    'learn' => [
        'The three vitals, translated into business impact',
        'The culprit checklist: images, scripts, hosting, themes',
        'Fix priorities by effort-to-impact ratio',
        'Speed clauses to put in every design contract',
    ],
    'chapters' => [
        ['Why speed is a money metric', 'Conversion and ranking effects, with the numbers.'],
        ['Reading your real scores', 'Field vs lab data and what to test.'],
        ['The culprits', 'Diagnosis patterns from a hundred audits.'],
        ['The fix sequence', 'Ordered priorities with expected gains.'],
        ['Keeping it fast', 'Budgets, monitoring and vendor accountability.'],
    ],
    'who' => ['Business owners with slow sites and finger-pointing vendors', 'Marketers whose campaigns land on heavy pages', 'Anyone commissioning a new build'],
    'faqs' => [
        ['Do I need to be technical?', 'No, it is written for decision-makers; the technical appendix is there when your developer needs specifics.'],
    ],
],

'whatsapp-marketing-playbook' => [
    'type' => 'ebook', 'size' => '30 pages',
    'title' => 'The WhatsApp Marketing Playbook (Free E-book)',
    'meta'  => 'Free e-book: WhatsApp marketing for Indian and global businesses: lifecycle flows, catalog selling, compliance and the metrics that matter.',
    'h1'    => 'The WhatsApp Marketing Playbook',
    'lede'  => 'The channel your customers actually open. Lifecycle flows, lead qualification, catalog selling and compliance, the complete WhatsApp playbook for businesses in India and beyond.',
    'learn' => [
        'Lifecycle flows: welcome, nurture, recovery, reactivation, with templates',
        'Lead qualification bots that sales teams thank you for',
        'Catalog and payment journeys inside the chat',
        'Compliance, consent and staying off the block list',
    ],
    'chapters' => [
        ['Why WhatsApp wins in engagement-first markets', 'Open-rate reality versus email and SMS.'],
        ['Setup done right', 'Business platform options, numbers and consent architecture.'],
        ['Lifecycle flows', 'Flow-by-flow builds with message templates.'],
        ['Selling in the chat', 'Catalogs, carts and follow-ups that convert.'],
        ['Measurement and hygiene', 'Metrics, list health and compliance guardrails.'],
    ],
    'who' => ['D2C and retail brands in WhatsApp-first markets', 'Service businesses qualifying leads by chat', 'Lifecycle marketers extending beyond email'],
    'faqs' => [
        ['Is this only for India?', 'India is the deepest case study, but flows and compliance guidance apply to every market WhatsApp serves.'],
    ],
],

'marketing-analytics-starter' => [
    'type' => 'ebook', 'size' => '38 pages',
    'title' => 'Marketing Analytics Starter Kit: GA4 & Attribution (Free)',
    'meta'  => 'Free e-book: the analytics foundation every business needs: GA4 setup that answers real questions, conversion tracking, UTM discipline and honest attribution.',
    'h1'    => 'The Marketing Analytics Starter Kit',
    'lede'  => 'Decisions are only as good as the data underneath. This kit covers the GA4 setup that answers real business questions, conversion tracking that tells the truth, and attribution honesty for multi-channel reality.',
    'learn' => [
        'GA4 configured around business questions, not default reports',
        'Conversion and event tracking that survives audits',
        'UTM discipline the whole team will actually follow',
        'Attribution: what is knowable, what isn\'t, and how to decide anyway',
    ],
    'chapters' => [
        ['Questions before dashboards', 'Defining the five questions your analytics must answer.'],
        ['GA4 foundations', 'Property setup, events, key events and audiences.'],
        ['Tracking integrity', 'Forms, calls, chats and ecommerce, capturing every conversion type.'],
        ['Campaign hygiene', 'UTM standards and channel groupings that keep reports readable.'],
        ['Attribution honesty', 'Models, their lies, and triangulation that works.'],
    ],
    'who' => ['Marketers inheriting messy analytics', 'Founders who don\'t trust their dashboards', 'Teams preparing for serious ad spend'],
    'faqs' => [
        ['We have GA4 installed, is this still useful?', 'Installed and configured are different worlds; most value here is in the configuration and integrity chapters.'],
    ],
],

'brand-identity-for-growth' => [
    'type' => 'ebook', 'size' => '34 pages',
    'title' => 'Brand Identity for Growth Companies (Free E-book)',
    'meta'  => 'Free e-book: brand identity as a growth asset: positioning, visual systems, voice and the consistency mechanics that compound trust and pricing power.',
    'h1'    => 'Brand Identity for Growth Companies',
    'lede'  => 'Brand is pricing power wearing design clothes. How growth-stage companies build positioning, visual systems and voice that compound trust, without big-agency budgets.',
    'learn' => [
        'Positioning: the decision that makes every other decision easier',
        'Visual identity systems that scale from card to campaign',
        'Voice and message architecture teams can actually follow',
        'Consistency mechanics: the unglamorous engine of brand equity',
    ],
    'chapters' => [
        ['Brand as business asset', 'CAC, price tolerance and trust, the measurable returns.'],
        ['Positioning first', 'Category, difference and proof, the one-page foundation.'],
        ['The visual system', 'Logo, color, type and usage rules built for real-world abuse.'],
        ['Voice and message', 'Message hierarchy and tone guides that survive delegation.'],
        ['Rollout and governance', 'Asset systems, templates and keeping it consistent at speed.'],
    ],
    'who' => ['Founders professionalizing a scrappy brand', 'Marketing leads preparing a rebrand', 'Companies whose look undersells their quality'],
    'faqs' => [
        ['Do we need a full rebrand to use this?', 'No, many readers tighten what exists; the governance chapters alone often fix the visible problems.'],
    ],
],

/* ---------------- GUIDES ---------------- */

'complete-guide-to-local-seo' => [
    'type' => 'guide', 'size' => '25 min read',
    'title' => 'The Complete Guide to Local SEO, Rank in the Map Pack',
    'meta'  => 'The complete local SEO guide: Google Business Profile, reviews, citations, location pages and tracking: everything, in the order that moves rankings.',
    'h1'    => 'The Complete Guide to Local SEO',
    'lede'  => 'Google Business Profile, reviews, citations, location pages and measurement: everything a local business needs to win the map pack, in the order that actually moves rankings.',
    'learn' => [
        'The local ranking model: relevance, distance, prominence: in practice',
        'GBP optimization that goes beyond claiming and filling',
        'Review systems, citation hygiene and local content',
        'Rank tracking by neighborhood, and the actions that matter',
    ],
    'chapters' => [
        ['How local rankings work', 'The three factors and what you can influence.'],
        ['Google Business Profile mastery', 'Categories, content, photos, posts and Q&A.'],
        ['Reviews and reputation', 'Velocity systems and response strategy.'],
        ['Citations and consistency', 'The listings that matter and NAP discipline.'],
        ['Location pages and content', 'Multi-area architecture and LocalBusiness schema.'],
        ['Measurement', 'Geo-grid tracking and GBP action reporting.'],
    ],
    'who' => ['Local service businesses and practices', 'Multi-location operators', 'Marketers who own a local number'],
    'faqs' => [
        ['How is this different from the e-book?', 'This guide is the open, always-current web version; the multi-location e-book goes deeper on chain-scale systems.'],
    ],
],

'complete-guide-to-ai-seo' => [
    'type' => 'guide', 'size' => '30 min read',
    'title' => 'The Complete Guide to AI SEO, Win AI Search Visibility',
    'meta'  => 'The complete guide to AI SEO: how generative engines choose sources, answer-first content, entity building, measurement and the ninety-day starting plan.',
    'h1'    => 'The Complete Guide to AI SEO',
    'lede'  => 'How generative engines choose their sources, and how to become one of them. Answer-first content, entity foundations, corroboration and measurement, with a ninety-day starting plan.',
    'learn' => [
        'The retrieval-and-citation model behind ChatGPT, Perplexity and AI Overviews',
        'Answer-first content architecture with real page patterns',
        'Entity SEO: schema, consistency and the corroboration layer',
        'Citation-share measurement and a ninety-day rollout plan',
    ],
    'chapters' => [
        ['The new retrieval landscape', 'What each engine does differently, and what they share.'],
        ['Being chosen: the source model', 'Authority, clarity, freshness, the citation triathlon.'],
        ['Content for answers', 'Question architecture, answer boxes and formatting patterns.'],
        ['Entity foundations', 'Organization schema, knowledge-graph presence and consistency.'],
        ['Corroboration building', 'Reviews, mentions and the third-party evidence layer.'],
        ['Measurement and iteration', 'Prompt sampling, dashboards and monthly cadence.'],
    ],
    'who' => ['SEO leads extending into AI visibility', 'Brands whose buyers research in assistants', 'Agencies building AEO service lines'],
    'faqs' => [
        ['Will this be outdated in six months?', 'The guide is maintained continuously: engine specifics evolve, the source-selection model has stayed stable.'],
    ],
],

'complete-guide-to-content-strategy' => [
    'type' => 'guide', 'size' => '28 min read',
    'title' => 'The Complete Guide to Content Strategy, Plan to Publish',
    'meta'  => 'The complete content strategy guide: topic clusters, briefs, production systems, distribution and measurement: a content engine from zero, step by step.',
    'h1'    => 'The Complete Guide to Content Strategy',
    'lede'  => 'Topic clusters, briefs, production and measurement: how to build a content engine from zero that earns rankings, citations and pipeline, not just publish counts.',
    'learn' => [
        'Revenue-mapped topic selection before keyword lists',
        'Cluster architecture that builds topical authority',
        'Brief and production systems that keep quality at cadence',
        'Distribution loops and measurement beyond pageviews',
    ],
    'chapters' => [
        ['Strategy foundations', 'Audience, intent tiers and the revenue map.'],
        ['Topic clusters', 'Pillars, supports and internal-linking architecture.'],
        ['Briefs and production', 'Templates, expert input and editorial standards.'],
        ['Optimization for search and AI', 'On-page patterns that win both surfaces.'],
        ['Distribution', 'Social, email and sales-enablement loops.'],
        ['Measurement', 'Cluster-level reporting and pipeline attribution.'],
    ],
    'who' => ['Marketers building a program from scratch', 'Teams stuck publishing without results', 'Founders doing content-led growth'],
    'faqs' => [
        ['How much content does this require?', 'Cadence scales to your resources, the guide shows minimum-viable and aggressive variants of the same system.'],
    ],
],

'complete-guide-to-ecommerce-platforms' => [
    'type' => 'guide', 'size' => '22 min read',
    'title' => 'The Complete Guide to Ecommerce Platform Selection',
    'meta'  => 'Shopify vs WooCommerce vs Magento vs BigCommerce vs headless, a requirements-first guide to choosing your ecommerce platform without regret.',
    'h1'    => 'The Complete Guide to Ecommerce Platform Selection',
    'lede'  => 'Shopify, WooCommerce, Magento, BigCommerce or headless? A requirements-first framework, catalog complexity, ownership, budget, SEO control, that chooses for you.',
    'learn' => [
        'The requirements matrix that makes the decision obvious',
        'Honest platform profiles: strengths, taxes and lock-ins',
        'Total cost of ownership beyond the subscription price',
        'Migration realities and SEO-safe switching',
    ],
    'chapters' => [
        ['Requirements before brands', 'Catalog, integrations, team and growth plans, the scoring matrix.'],
        ['Platform profiles', 'Shopify, Woo, Magento, BigCommerce and headless, without vendor gloss.'],
        ['Cost of ownership', 'Apps, themes, developers and payment margins, the real math.'],
        ['SEO implications', 'Control differences that matter and workarounds.'],
        ['Migration playbook', 'Sequencing, redirects and the equity-protection checklist.'],
    ],
    'who' => ['Founders launching or replatforming a store', 'Retailers outgrowing their current stack', 'Agencies advising platform decisions'],
    'faqs' => [
        ['Do you favor a platform?', 'We build on all of them, which is exactly why the guide can afford to be honest.'],
    ],
],

'complete-guide-to-business-lead-gen-seo' => [
    'type' => 'guide', 'size' => '26 min read',
    'title' => 'The Complete Guide to B2B Lead Gen SEO, Search to Sale',
    'meta'  => 'The complete guide to B2B lead generation SEO: bottom-funnel keywords, comparison pages, intent capture and content that fills pipelines, not just dashboards.',
    'h1'    => 'The Complete Guide to B2B Lead Gen SEO',
    'lede'  => 'Bottom-funnel keywords, comparison pages and decision content: the SEO system that fills B2B pipelines with buyers, not readers.',
    'learn' => [
        'The B2B intent hierarchy and why most content targets the wrong tier',
        'Money pages: comparisons, alternatives, pricing and use cases',
        'Capturing demand your champions can forward internally',
        'Reporting SEO in pipeline language executives fund',
    ],
    'chapters' => [
        ['B2B search behavior', 'Committees, long cycles and the queries that signal budgets.'],
        ['The money-page layer', 'Comparison, alternative and pricing pages that convert shortlists.'],
        ['Solution and use-case content', 'Speaking to problems before product names exist.'],
        ['Authority that closes', 'Cases, data and expert content sales can weaponize.'],
        ['Pipeline measurement', 'Attribution triangulation and executive reporting.'],
    ],
    'who' => ['B2B SaaS and services marketers', 'Founders selling considered purchases', 'SEO teams judged on pipeline'],
    'faqs' => [
        ['Our niche has tiny search volume, worth it?', 'B2B volume is low and worth thousands per click; the guide includes low-volume/high-value tactics specifically.'],
    ],
],

'complete-guide-to-google-ads' => [
    'type' => 'guide', 'size' => '32 min read',
    'title' => 'The Complete Guide to Google Ads for SMBs',
    'meta'  => 'The complete Google Ads guide for small and mid-size businesses: account structure, match types, bidding, landing pages and scaling without waste.',
    'h1'    => 'The Complete Guide to Google Ads',
    'lede'  => 'Structure, match types, negatives, bidding and landing pages: the complete Google Ads system for SMBs, from first campaign to confident scaling.',
    'learn' => [
        'Account structure that keeps intent and budgets honest',
        'Match types and negatives: the targeting half nobody maintains',
        'Bidding strategy by data maturity, not platform nudges',
        'The landing-page and measurement standards that make ads pay',
    ],
    'chapters' => [
        ['Foundations', 'Goals, conversion tracking and budget math before spend.'],
        ['Structure', 'Campaigns, ad groups and intent tiers that scale.'],
        ['Targeting', 'Keywords, match types and the living negative library.'],
        ['Bidding and budgets', 'Manual to smart bidding, the graduation path.'],
        ['Ads and landing pages', 'Message match, extensions and page standards.'],
        ['Optimization cadence', 'The weekly and monthly rituals that compound.'],
    ],
    'who' => ['SMB owners running or buying ads', 'In-house marketers inheriting accounts', 'Anyone auditing an agency\'s work'],
    'faqs' => [
        ['Does this cover Performance Max?', 'Yes: including when it helps, when it hides waste, and how to feed it honestly.'],
    ],
],
];
