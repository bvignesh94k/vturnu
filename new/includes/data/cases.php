<?php
/**
 * Case studies, 10 detailed engagements. Keyed by slug (lowercase + hyphens).
 * Rendered by templates/case-detail.php at /case-studies/<slug>/.
 * Fields: title, meta, h1, lede, client, industry, services[], cs_type, location, duration,
 *         challenge [paras], approach [[title, desc]], results [[metric, label]], quote [text, role], outcome
 */

$CASES = [

'jewelry-brand-organic-revenue-growth' => [
    'title' => 'Jewelry Ecommerce SEO Case Study: 2.4× Organic Revenue',
    'meta'  => 'How a jewelry ecommerce brand grew organic revenue 2.4× in ten months: category architecture, product schema and buying-guide content. Full case study.',
    'h1'    => 'Jewelry Brand: 2.4× Organic Revenue in Ten Months',
    'lede'  => 'A premium jewelry retailer was buying every sale through ads while its category pages sat invisible. Ten months of architectural SEO turned organic into its largest revenue channel.',
    'client' => 'Premium jewelry ecommerce brand', 'industry' => 'Ecommerce · Jewelry', 'cs_type' => 'seo',
    'services' => [['Ecommerce SEO', '/ecommerce-seo/'], ['SEO Content Writing', '/seo-content-writing/']],
    'location' => 'Chennai, India', 'duration' => '10 months',
    'challenge' => [
        'The brand had beautiful products, healthy paid ROAS, and near-zero organic presence. Category pages were auto-generated lists with no copy, product pages carried manufacturer descriptions duplicated across the industry, and faceted navigation had spawned thousands of thin URLs competing with the pages that mattered.',
        'Every rupee of growth was rented from ad platforms, and rising CPCs were squeezing margin quarter over quarter.',
    ],
    'approach' => [
        ['Architecture triage', 'Crawled and mapped the store, canonicalized facet chaos, and rebuilt the category tree around how buyers actually search: by occasion, metal, and price band.'],
        ['Category pages as landing pages', 'Wrote unique, intent-matched copy for forty priority categories with FAQ blocks and internal links to best-sellers.'],
        ['Product data done right', 'Unique descriptions for hero SKUs, complete Product schema with price, availability and ratings, and a post-purchase review capture flow.'],
        ['Buying-guide content layer', 'Gifting guides, metal comparisons and budget guides, each funneling readers into the matching category.'],
    ],
    'results' => [
        ['2.4×', 'Organic revenue vs prior period'],
        ['+186%', 'Organic sessions to category pages'],
        ['48', 'Keywords in top three positions'],
        ['-38%', 'Paid share of total revenue'],
    ],
    'quote' => ['Organic used to be a rounding error in our revenue mix. It is now our largest channel, and the one that grows while we sleep.', 'Founder, jewelry brand'],
    'outcome' => 'Organic became the store\'s largest revenue channel, paid dependence dropped by more than a third, and the category architecture continues to compound with every new collection launch.',
],

'clinic-local-seo-patient-growth' => [
    'title' => 'Healthcare Local SEO Case Study: 2× Patient Enquiries',
    'meta'  => 'How a multi-location clinic doubled organic patient enquiries in eight months with Google Business Profile rebuilds, location pages and a review engine.',
    'h1'    => 'Multi-Location Clinic: Patient Enquiries Doubled',
    'lede'  => 'Four locations, eleven reviews, zero map-pack presence. Eight months later: top-three map positions everywhere it operates and organic enquiries up 104%.',
    'client' => 'Multi-location specialty clinic group', 'industry' => 'Healthcare', 'cs_type' => 'health',
    'services' => [['Local SEO', '/local-seo/'], ['SEO Content Writing', '/seo-content-writing/']],
    'location' => 'Chennai, India', 'duration' => '8 months',
    'challenge' => [
        'A respected medical team was digitally invisible: one thin page for all four locations, unclaimed and inconsistent business profiles, and paid ads carrying the entire acquisition load at climbing cost per patient.',
    ],
    'approach' => [
        ['Profile foundations', 'Claimed, verified and completely rebuilt every Google Business Profile; fixed name-address-phone consistency across forty directories; shipped LocalBusiness schema.'],
        ['Real location pages', 'One genuine page per clinic, unique copy, doctor bios, treatment FAQs, embedded maps, with site architecture rebuilt around them.'],
        ['Review engine', 'A post-visit review ask embedded in front-desk workflow, with human response templates. Reviews grew from eleven to two hundred plus.'],
        ['Condition content', 'Treatment and condition articles answering real patient searches, each internally linked to its location page.'],
    ],
    'results' => [
        ['+104%', 'Organic patient enquiries'],
        ['4/4', 'Locations in map-pack top three'],
        ['200+', 'Google reviews (from 11)'],
        ['-34%', 'Blended cost per acquisition'],
    ],
    'quote' => ['Patients now tell us "you were everywhere when I searched". That was never true before this engagement.', 'Operations head, clinic group'],
    'outcome' => 'Organic share of new patients rose from eighteen to forty-one percent, easing paid dependence and paying for the program by month five.',
],

'saas-ppc-demo-pipeline' => [
    'title' => 'SaaS PPC Case Study: 3× Demo Bookings at the Same Spend',
    'meta'  => 'How a B2B SaaS tripled demo bookings without raising budget: intent-tiered search campaigns, rebuilt landing pages and conversion tracking done right.',
    'h1'    => 'B2B SaaS: 3× Demo Bookings at the Same Spend',
    'lede'  => 'The account had budget; it lacked structure. Rebuilding campaigns around intent tiers and fixing the click-to-demo journey tripled qualified demos without a rupee of extra spend.',
    'client' => 'B2B SaaS platform (workflow automation)', 'industry' => 'SaaS & Tech', 'cs_type' => 'ppc',
    'services' => [['Google Ads', '/google-ads/'], ['Lead Gen Web Design', '/lead-gen-web-design/']],
    'location' => 'Bengaluru, India · serving US market', 'duration' => '6 months',
    'challenge' => [
        'Broad-match keywords, one campaign for every intent level, conversions firing on newsletter signups and demo requests alike, the algorithm was optimizing toward noise. Demos cost too much, and sales complained about lead quality weekly.',
    ],
    'approach' => [
        ['Tracking before targeting', 'Rebuilt conversion measurement so demos, trials and signups were valued differently; gave smart bidding a truthful target.'],
        ['Intent-tiered structure', 'Separated competitor, category and problem-aware queries into campaigns with budgets and bids matching their economics.'],
        ['Negative discipline', 'A living negative library killed job-seeker, free-tool and student traffic that had consumed a fifth of spend.'],
        ['Message-matched landing pages', 'Purpose-built pages per intent tier replaced the generic homepage: same promise as the ad, one action, proof stacked.'],
    ],
    'results' => [
        ['3×', 'Demo bookings, same budget'],
        ['-58%', 'Cost per qualified demo'],
        ['+89%', 'Landing page conversion rate'],
        ['21%', 'Spend reclaimed from waste queries'],
    ],
    'quote' => ['Same budget, three times the demos, and sales finally stopped complaining about lead quality. The structure was the strategy.', 'Growth lead, SaaS client'],
    'outcome' => 'The account became the company\'s most predictable pipeline source, and the intent-tier structure scaled cleanly when budgets doubled the following quarter.',
],

'fashion-brand-roas-turnaround' => [
    'title' => 'D2C Fashion Meta Ads Case Study: ROAS 1.8 to 4.6',
    'meta'  => 'How a D2C fashion brand went from break-even to 4.6 ROAS in one quarter: feed rebuilds, creative testing systems and full-funnel Meta structure.',
    'h1'    => 'D2C Fashion: ROAS From 1.8 to 4.6 in One Quarter',
    'lede'  => 'A break-even ad account is a business on a treadmill. Feed discipline, a creative testing system and honest measurement turned this one into a growth engine in ninety days.',
    'client' => 'D2C fashion brand', 'industry' => 'Ecommerce · Fashion', 'cs_type' => 'ppc',
    'services' => [['Facebook Ads', '/facebook-ads/'], ['Instagram Ads', '/instagram-ads/']],
    'location' => 'Mumbai, India', 'duration' => '3 months (ongoing)',
    'challenge' => [
        'Blended ROAS of 1.8, effectively break-even after margins, with fatigue-prone creative, a product feed full of errors, and no structural separation between prospecting and returning customers, so reported numbers flattered while the business treaded water.',
    ],
    'approach' => [
        ['Feed first', 'Fixed the catalog: titles rewritten for queries, images to spec, availability synced, the quiet engine of every dynamic format.'],
        ['Honest account structure', 'Split prospecting from retargeting and brand from non-brand, so each layer earned its own budget on true incremental performance.'],
        ['Creative testing system', 'Weekly creative sprints, hooks, formats, angles, with kill/scale rules, ending the fatigue cycle that had capped every previous scale attempt.'],
        ['Recovery flows', 'Cart and browse abandonment sequences on WhatsApp and email reclaimed intent the ads had already paid for.'],
    ],
    'results' => [
        ['4.6', 'Blended ROAS (from 1.8)'],
        ['-52%', 'Cost per acquisition'],
        ['2×', 'Monthly ad spend scaled profitably'],
        ['18%', 'Revenue reclaimed by recovery flows'],
    ],
    'quote' => ['We had almost concluded ads "don\'t work for us". They work. What we had didn\'t.', 'Co-founder, fashion brand'],
    'outcome' => 'The brand scaled monthly spend two-fold while ROAS held above four, and the creative system continues to produce winners without founder involvement.',
],

'real-estate-lead-generation-engine' => [
    'title' => 'Real Estate Lead Gen Case Study: 3.1× Qualified Site Visits',
    'meta'  => 'How a property developer 3.1×\'d qualified site-visit bookings: local SEO, high-intent PPC and a conversion-first project microsite working as one funnel.',
    'h1'    => 'Property Developer: 3.1× Qualified Site Visits',
    'lede'  => 'Property buying starts as a search. One integrated funnel, local SEO, surgical PPC and a microsite built to book visits, tripled qualified site visits for a mid-size developer.',
    'client' => 'Residential property developer', 'industry' => 'Real Estate', 'cs_type' => 'realestate',
    'services' => [['Local SEO', '/local-seo/'], ['Google Ads', '/google-ads/'], ['Lead Gen Web Design', '/lead-gen-web-design/']],
    'location' => 'Chennai, India', 'duration' => '7 months',
    'challenge' => [
        'Portals owned the demand and charged for every lead, many of them recycled. The developer\'s own site ranked for nothing, converted at under half a percent, and paid campaigns pointed at a brochure homepage that answered no buyer question.',
    ],
    'approach' => [
        ['Project microsites that sell', 'Purpose-built pages per project: pricing transparency, floor plans, locality guides, EMI calculator, and one job, book a site visit.'],
        ['Locality search ownership', 'Location pages and content for "flats in <area>" queries, with LocalBusiness schema and map presence for the sales office.'],
        ['Surgical paid capture', 'Exact and phrase campaigns on high-intent queries only, with negatives excluding renters, job-seekers and budget mismatches.'],
        ['Lead qualification flow', 'WhatsApp-first follow-up with budget and timeline qualifiers, so sales called visits, not tyre-kickers.'],
    ],
    'results' => [
        ['3.1×', 'Qualified site-visit bookings'],
        ['-61%', 'Cost per qualified lead vs portals'],
        ['#1-3', 'Map positions for priority localities'],
        ['4.2%', 'Microsite conversion rate (from 0.4%)'],
    ],
    'quote' => ['For the first time, our own funnel out-delivers the portals, at a third of their cost per genuine buyer.', 'Sales director, developer'],
    'outcome' => 'The developer now launches every new project with this funnel as standard, and portal spend has been cut to a supplementary channel.',
],

'edutech-content-engine-signups' => [
    'title' => 'EduTech Content SEO Case Study: +212% Organic Signups',
    'meta'  => 'How a learning platform grew organic signups 212% with a topic-cluster content engine, programmatic course pages and AI-search-ready formatting.',
    'h1'    => 'EduTech Platform: +212% Organic Signups',
    'lede'  => 'Acquisition cost decides which learning platforms survive. A topic-cluster content engine plus programmatic SEO took this one from paid-dependent to organic-first in nine months.',
    'client' => 'Online learning platform', 'industry' => 'EduTech', 'cs_type' => 'edutech',
    'services' => [['SEO Content Writing', '/seo-content-writing/'], ['SEO Services', '/seo-services/'], ['AI SEO', '/ai-seo/']],
    'location' => 'Hyderabad, India', 'duration' => '9 months',
    'challenge' => [
        'Course pages ranked for nothing but the brand name; every signup was bought. Content existed, a neglected blog of announcements, but nothing mapped to what learners actually search at decision time: comparisons, career outcomes, "is X worth it" questions.',
    ],
    'approach' => [
        ['Demand mapping', 'Built the keyword universe around learner decisions, career paths, tool comparisons, salary questions, and clustered it into teachable topics.'],
        ['Cluster engine', 'Pillar guides with supporting articles, produced on a weekly cadence with expert review, all internally linked to course pages.'],
        ['Programmatic course SEO', 'Templated-but-unique course and category pages with Course schema, FAQs and outcome data.'],
        ['Answer-ready formatting', 'Question headings and concise answers throughout, the platform now gets cited in AI Overviews for its core topics.'],
    ],
    'results' => [
        ['+212%', 'Organic signups'],
        ['+340%', 'Non-brand organic traffic'],
        ['120+', 'Keywords in top three'],
        ['-47%', 'Blended acquisition cost'],
    ],
    'quote' => ['Content went from a checkbox to our primary acquisition channel. The cluster model made it systematic instead of hopeful.', 'Head of growth, EduTech client'],
    'outcome' => 'Organic overtook paid as the largest signup source in month eight, and AI-engine citations now bring measurable referral signups the platform never planned for.',
],

'manufacturer-rfq-seo-program' => [
    'title' => 'Manufacturing SEO Case Study: 4× RFQs From Organic',
    'meta'  => 'How an industrial manufacturer quadrupled RFQs: technical catalog SEO, spec-sheet content and buyer-intent pages that reach engineers mid-research.',
    'h1'    => 'Industrial Manufacturer: 4× RFQs From Organic Search',
    'lede'  => 'Industrial buyers research quietly and shortlist before ever emailing. Making this manufacturer\'s expertise findable, catalog, specs, applications, quadrupled inbound RFQs.',
    'client' => 'Precision components manufacturer', 'industry' => 'Manufacturing', 'cs_type' => 'manufacturing',
    'services' => [['SEO Services', '/seo-services/'], ['Web Design', '/web-design/']],
    'location' => 'Coimbatore, India · exporting globally', 'duration' => '11 months',
    'challenge' => [
        'Deep engineering capability, invisible online: a brochure site with no product detail, PDFs holding all technical content where crawlers and buyers both struggled, and zero presence on the application and specification queries engineers actually type.',
    ],
    'approach' => [
        ['Catalog liberation', 'Rebuilt the product catalog as crawlable HTML: one page per product family with specs, tolerances, materials and applications out of the PDFs.'],
        ['Application content', 'Pages targeting "component for <application>" queries, how buyers search when they don\'t know the product name yet.'],
        ['Trust for procurement', 'Certifications, capability pages, machinery lists and export credentials, the evidence procurement teams shortlist on.'],
        ['RFQ-first conversion', 'Spec-upload RFQ forms and response-time promises replaced the generic contact page.'],
    ],
    'results' => [
        ['4×', 'Inbound RFQs from organic'],
        ['+267%', 'Non-brand organic traffic'],
        ['32', 'Countries generating enquiries'],
        ['6', 'New export accounts attributed'],
    ],
    'quote' => ['Enquiries now arrive already knowing our tolerances and certifications. The website finally sells the way our engineers do.', 'Managing director, manufacturer'],
    'outcome' => 'Export enquiries diversified across thirty-plus countries, and two of the six new accounts became the company\'s largest customers within the year.',
],

'restaurant-chain-local-domination' => [
    'title' => 'Restaurant Chain Local SEO Case Study: +156% Direction Requests',
    'meta'  => 'How a regional restaurant chain lifted direction requests 156% and online orders 89%: profile systems, review velocity and local landing pages at scale.',
    'h1'    => 'Restaurant Chain: +156% Direction Requests Across Outlets',
    'lede'  => '"Best biryani near me" is won or lost in the map pack. Systematic local SEO across twelve outlets turned search presence into footfall and direct online orders.',
    'client' => 'Regional restaurant chain (12 outlets)', 'industry' => 'Food & Hospitality', 'cs_type' => 'seo',
    'services' => [['Local SEO', '/local-seo/'], ['Reputation Management', '/reputation-management/']],
    'location' => 'Tamil Nadu, India', 'duration' => '6 months',
    'challenge' => [
        'Twelve outlets, twelve inconsistent profiles: some unclaimed, menus outdated, photos years old, reviews unanswered for months. Aggregators owned the customer relationship and charged commission for demand the brand\'s own name generated.',
    ],
    'approach' => [
        ['Profile system, not one-off fixes', 'Standardized and rebuilt all twelve profiles, categories, menus, attributes, geo-tagged photography, with a monthly refresh calendar.'],
        ['Review velocity engine', 'QR-based table asks and post-order prompts, plus same-day human responses in brand voice, including honest handling of complaints.'],
        ['Outlet landing pages', 'A page per outlet with local copy, menu highlights, parking and delivery info, and Restaurant schema.'],
        ['Direct-order push', 'Profiles and pages pointed to first-party ordering, reclaiming margin from aggregator commissions.'],
    ],
    'results' => [
        ['+156%', 'Direction requests (GBP)'],
        ['+89%', 'Direct online orders'],
        ['12/12', 'Outlets in local top three'],
        ['4.5★', 'Average rating (from 3.8)'],
    ],
    'quote' => ['Every outlet manager can now see search turning into tables. And direct orders finally outgrew the commission apps.', 'Marketing head, restaurant chain'],
    'outcome' => 'Direct ordering overtook the largest aggregator in month five, permanently improving unit economics across the chain.',
],

'law-firm-lead-gen-website' => [
    'title' => 'Law Firm Web Design Case Study: 5× Consultation Requests',
    'meta'  => 'How a law firm\'s conversion-first website rebuild, practice-area pages, trust architecture, local SEO, multiplied consultation requests five-fold.',
    'h1'    => 'Law Firm: 5× Consultation Requests After Rebuild',
    'lede'  => 'Legal clients hire trust. A rebuilt site that answered real questions, proved credentials and made contacting effortless multiplied consultations five-fold on similar traffic.',
    'client' => 'Mid-size law firm', 'industry' => 'Legal Services', 'cs_type' => 'design',
    'services' => [['Lead Gen Web Design', '/lead-gen-web-design/'], ['Local SEO', '/local-seo/'], ['SEO Content Writing', '/seo-content-writing/']],
    'location' => 'Chennai, India', 'duration' => '5 months',
    'challenge' => [
        'The old site was a digital business card: one paragraph per practice area, no lawyer profiles beyond names, a contact form asking eight questions, and page speeds that lost half of mobile visitors before content painted. Traffic existed; consultations did not.',
    ],
    'approach' => [
        ['Practice-area architecture', 'A true page per practice area answering the questions clients search, process, timelines, costs, outcomes, with FAQ schema throughout.'],
        ['Trust as design', 'Lawyer profiles with credentials and matters handled, results (within advertising norms), client words, and bar memberships surfaced site-wide.'],
        ['Frictionless contact', 'Three-field form, click-to-call everywhere, WhatsApp intake, with response-time promises stated and kept.'],
        ['Local legal SEO', 'Profile rebuild, citations and locality pages for the practice areas people search geographically.'],
    ],
    'results' => [
        ['5×', 'Consultation requests'],
        ['3.4%', 'Site conversion rate (from 0.6%)'],
        ['-71%', 'Mobile bounce on key pages'],
        ['top 3', 'Map pack, priority practice areas'],
    ],
    'quote' => ['The site used to be something we apologized for. Now it does the first consultation\'s work before we ever speak.', 'Managing partner, law firm'],
    'outcome' => 'Consultation volume let the firm add two associates, and practice-area pages now rank ahead of national directories for its core local queries.',
],

'fitness-brand-social-community' => [
    'title' => 'Fitness Brand Social Media Case Study: 0 to 120K Community',
    'meta'  => 'How a fitness brand built a 120K engaged community in a year, reels-first content system, creator collabs, and turned it into 30% of revenue.',
    'h1'    => 'Fitness Brand: Zero to 120K Community Driving 30% of Revenue',
    'lede'  => 'Followers are vanity until they buy. A reels-first content system and creator partnerships built this fitness brand a real audience, and a channel now producing nearly a third of revenue.',
    'client' => 'D2C fitness & nutrition brand', 'industry' => 'Health & Fitness', 'cs_type' => 'social',
    'services' => [['Social Media Marketing', '/social-media-marketing/'], ['Email Marketing', '/email-marketing/']],
    'location' => 'Pune, India', 'duration' => '12 months',
    'challenge' => [
        'A genuinely good product line launching into a feed already full of fitness noise: no audience, no content engine, and founder time too scarce for the daily-grind approach most advice assumes.',
    ],
    'approach' => [
        ['Content system over inspiration', 'Three weekly pillars, transformation proof, myth-busting education, product-in-use, batch-produced monthly so consistency survived busy weeks.'],
        ['Reels-first distribution', 'Short-form native edits with strong hooks; every piece designed for saves and shares, the currencies the algorithm actually pays.'],
        ['Creator collabs, not celebrity ads', 'Mid-tier fitness creators with engaged audiences, briefed for authenticity, seeding both content and credibility.'],
        ['Audience to asset', 'Lead magnets (meal plans, programs) bridged followers to an email and WhatsApp list the brand owns, where launches convert.'],
    ],
    'results' => [
        ['120K', 'Engaged followers in 12 months'],
        ['30%', 'Revenue attributed to social'],
        ['38K', 'Email/WhatsApp list from social'],
        ['6.8%', 'Average engagement rate'],
    ],
    'quote' => ['Launch days now sell out from our own audience before ads even start. That community is the moat we didn\'t know we could afford.', 'Founder, fitness brand'],
    'outcome' => 'Owned-audience launches now de-risk every new product, and the content system runs on six hours of founder time per month.',
],
];
