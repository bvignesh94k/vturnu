<?php
/**
 * Navigation & footer structure.
 * Navbar: Services (marketing mega) | Web Development (mega) | AI Search | Case Studies | Resources | Pricing | Company
 * Each top item may have: label, url, desc (mega tagline), columns [heading, url?, items[[label,url,desc?]]],
 * and promo [eyebrow, title, text, btn, url] rendered as the mega-menu highlight panel.
 */

$NAV = [
    [
        'label' => 'Services',
        'url'   => '/digital-marketing/',
        'desc'  => 'Performance marketing: search, paid, content and social under one roof.',
        'columns' => [
            [
                'heading' => 'SEO & Organic Growth',
                'url'     => '/seo/',
                'items'   => [
                    ['SEO Services', '/seo-services/', 'Rank higher, earn revenue'],
                    ['AI SEO', '/ai-seo/', 'Get cited by AI engines'],
                    ['Ecommerce SEO', '/ecommerce-seo/', 'Grow store revenue organically'],
                    ['Local SEO', '/local-seo/', 'Own the map pack'],
                    ['Lead Gen SEO', '/lead-generation-seo/', 'Pipeline from search'],
                    ['Enterprise SEO', '/enterprise-seo/', 'SEO at scale'],
                ],
            ],
            [
                'heading' => 'Paid Advertising',
                'url'     => '/paid-advertising/',
                'items'   => [
                    ['Google Ads', '/google-ads/', 'Profit-first search campaigns'],
                    ['Facebook Ads', '/facebook-ads/', 'Meta funnels that scale'],
                    ['Instagram Ads', '/instagram-ads/', 'Creative that converts'],
                    ['Enterprise PPC', '/enterprise-ppc/', 'Big budgets, managed tightly'],
                ],
            ],
            [
                'heading' => 'Content & Social',
                'url'     => '/content-marketing/',
                'items'   => [
                    ['Social Media Marketing', '/social-media-marketing/', 'Audience into pipeline'],
                    ['SEO Content Writing', '/seo-content-writing/', 'Content that ranks & converts'],
                    ['Copywriting Services', '/copywriting-services/', 'Words that sell'],
                    ['Email Marketing', '/email-marketing/', 'Owned-audience revenue'],
                    ['Reputation Management', '/reputation-management/', 'Protect what Google says'],
                    ['Outbound Marketing', '/outbound-marketing/', 'Pipeline on demand'],
                ],
            ],
        ],
        'promo' => ['Not sure where to start?', 'Get a free growth audit', 'Tell us your goal: we\'ll recommend the right mix with numbers, in one business day.', 'Run My Free Audit', '/free-seo-audit/'],
    ],

    [
        'label' => 'Web Development',
        'url'   => '/web-services/',
        'desc'  => 'Fast, SEO-ready websites: one-time build cost, yours forever.',
        'columns' => [
            [
                'heading' => 'Websites by Goal',
                'url'     => '/web-services/',
                'items'   => [
                    ['Web Design', '/web-design/', 'Fast, conversion-first sites'],
                    ['Ecommerce Web Design', '/ecommerce-web-design/', 'Stores built to sell'],
                    ['Lead Gen Web Design', '/lead-gen-web-design/', 'Websites that enquire'],
                    ['Headless Web Design', '/headless-web-design/', 'Composable & lightning fast'],
                ],
            ],
            [
                'heading' => 'By Platform',
                'items'   => [
                    ['Shopify', '/shopify-web-design/', 'Shopify done properly'],
                    ['WordPress', '/wordpress-web-design/', 'WordPress without bloat'],
                    ['WooCommerce', '/woocommerce-web-design/', 'WordPress ecommerce, fast'],
                    ['Magento', '/magento-web-design/', 'Adobe Commerce UX'],
                    ['BigCommerce', '/bigcommerce-web-design/', 'Built to scale & sell'],
                ],
            ],
            [
                'heading' => 'Engineering',
                'items'   => [
                    ['Magento Development', '/magento-development/', 'Modules, migrations, speed'],
                    ['Custom Design & Development', '/custom-design-and-development/', 'Bespoke digital builds'],
                    ['AI Development', '/ai-development/', 'Chatbots, automation, MVPs'],
                    ['Web Build Pricing', '/pricing/', 'One-time cost, no surprises'],
                ],
            ],
        ],
        'promo' => ['One-time build cost', 'Pay once, own it forever', 'Design & development billed in month one only, no recurring dev fees. 95+ PageSpeed target on every build.', 'Get a Build Quote', '/contact-us/'],
    ],

    [
        'label' => 'AI Search',
        'url'   => '/ai/',
        'desc'  => 'Win visibility inside ChatGPT, Perplexity and Google AI Overviews.',
        'columns' => [
            [
                'heading' => 'AI Visibility Services',
                'url'     => '/ai/',
                'items'   => [
                    ['AI SEO', '/ai-seo/', 'The full AI-visibility program'],
                    ['Google AI Overviews SEO', '/google-ai-overviews-seo/', 'Be the cited answer'],
                    ['ChatGPT SEO', '/chatgpt-seo/', 'Get recommended in chat'],
                    ['Perplexity SEO', '/perplexity-seo/', 'Win the citation engine'],
                ],
            ],
            [
                'heading' => 'AI Solutions',
                'items'   => [
                    ['AI Development', '/ai-development/', 'Bots, automations, products'],
                    ['Selling on ChatGPT', '/selling-on-chatgpt/', 'Commerce in the chat era'],
                    ['Our AI Policy', '/ai-policy/', 'How we use AI, honestly'],
                ],
            ],
            [
                'heading' => 'Learn AI Search',
                'items'   => [
                    ['What is AEO?', '/blog/what-is-answer-engine-optimization/', 'The plain-English guide'],
                    ['Rank in AI Overviews', '/blog/how-to-rank-in-google-ai-overviews/', 'Step-by-step playbook'],
                    ['The AI Search Playbook', '/ebooks/ai-search-playbook/', 'Free 64-page e-book'],
                ],
            ],
        ],
        'promo' => ['Free report', 'What does AI say about you?', 'We\'ll show you how ChatGPT, Perplexity and AI Overviews present your brand today.', 'Get My AI Report', '/contact-us/'],
    ],

    [
        'label' => 'Case Studies',
        'url'   => '/case-studies/',
        'desc'  => 'Real engagements, real numbers, browse by service or industry.',
        'columns' => [
            [
                'heading' => 'Featured Results',
                'items'   => [
                    ['Jewelry brand: 2.4× organic revenue', '/case-studies/jewelry-brand-organic-revenue-growth/', 'Ecommerce SEO'],
                    ['SaaS: 3× demos, same spend', '/case-studies/saas-ppc-demo-pipeline/', 'Google Ads'],
                    ['Clinic: enquiries doubled', '/case-studies/clinic-local-seo-patient-growth/', 'Local SEO'],
                    ['Law firm: 5× consultations', '/case-studies/law-firm-lead-gen-website/', 'Web design'],
                ],
            ],
            [
                'heading' => 'By Service',
                'items'   => [
                    ['SEO Success Stories', '/case-studies/seo-success-stories/'],
                    ['PPC Growth Campaigns', '/case-studies/ppc-growth-campaigns/'],
                    ['Social Media Wins', '/case-studies/social-media-marketing-wins/'],
                    ['Content Marketing Results', '/case-studies/content-marketing-results/'],
                    ['Branding & Design Impact', '/case-studies/branding-and-design-impact/'],
                ],
            ],
            [
                'heading' => 'By Industry',
                'items'   => [
                    ['SaaS & Tech', '/case-studies/saas-and-tech/'],
                    ['E-commerce', '/case-studies/ecommerce/'],
                    ['Healthcare', '/case-studies/healthcare/'],
                    ['Real Estate', '/case-studies/real-estate/'],
                    ['Manufacturing', '/case-studies/manufacturing/'],
                    ['Education & EduTech', '/case-studies/education/'],
                ],
            ],
        ],
        'promo' => ['Your turn', 'Want numbers like these?', 'Every engagement starts with a free audit and an honest conversation about fit.', 'Start With a Free Audit', '/free-seo-audit/'],
    ],

    [
        'label' => 'Resources',
        'url'   => '/blog/',
        'desc'  => 'Playbooks, guides and e-books from our strategists: free, no fluff.',
        'columns' => [
            [
                'heading' => 'Learn',
                'items'   => [
                    ['Free SEO Audit', '/free-seo-audit/', 'Instant check, results in seconds'],
                    ['Blog', '/blog/', 'Insights & playbooks'],
                    ['Guides', '/guides/', 'Complete evergreen resources'],
                    ['E-books', '/ebooks/', 'Deep dives, free to download'],
                ],
            ],
            [
                'heading' => 'Popular Reads',
                'items'   => [
                    ['How to choose an agency', '/blog/how-to-choose-a-digital-marketing-agency/', 'The buyer\'s checklist'],
                    ['SEO vs PPC: better ROI?', '/blog/seo-vs-ppc-which-drives-better-roi/', 'The honest comparison'],
                    ['Agency pricing, decoded', '/blog/digital-marketing-agency-pricing-guide/', 'What things really cost'],
                    ['Traffic but no leads?', '/blog/why-your-website-gets-traffic-but-no-leads/', 'The 5-cause diagnosis'],
                ],
            ],
            [
                'heading' => 'Free E-books',
                'items'   => [
                    ['The AI Search Playbook', '/ebooks/ai-search-playbook/', '64 pages'],
                    ['Ecommerce SEO: Crawl to Cart', '/ebooks/ecommerce-seo-from-crawl-to-cart/', '48 pages'],
                    ['Lead Gen Website Blueprint', '/ebooks/lead-gen-website-blueprint/', '36 pages'],
                ],
            ],
        ],
        'promo' => ['Stay ahead', 'New playbooks, monthly', 'Get fresh strategies in your inbox, the same thinking our clients pay for.', 'Explore the Blog', '/blog/'],
    ],

    ['label' => 'Pricing', 'url' => '/pricing/'],

    [
        'label' => 'Company',
        'url'   => '/about-us/',
        'columns' => [
            [
                'heading' => 'VTurnU',
                'items'   => [
                    ['About Us', '/about-us/', 'Who we are & how we work'],
                    ['Contact Us', '/contact-us/', 'Reply within 24 hours'],
                    ['Our AI Policy', '/ai-policy/', 'How we use AI, honestly'],
                ],
            ],
        ],
    ],
];

/**
 * Footer, rendered by includes/footer.php.
 * Simple label => [[label, url], …] columns.
 */
$FOOTER = [
    'Digital Marketing' => [
        ['SEO Services', '/seo-services/'],
        ['AI SEO', '/ai-seo/'],
        ['Local SEO', '/local-seo/'],
        ['Ecommerce SEO', '/ecommerce-seo/'],
        ['Google Ads', '/google-ads/'],
        ['Facebook Ads', '/facebook-ads/'],
        ['Social Media Marketing', '/social-media-marketing/'],
        ['Email Marketing', '/email-marketing/'],
    ],
    'Web Development' => [
        ['Web Design', '/web-design/'],
        ['Ecommerce Web Design', '/ecommerce-web-design/'],
        ['Lead Gen Web Design', '/lead-gen-web-design/'],
        ['Shopify Web Design', '/shopify-web-design/'],
        ['WordPress Web Design', '/wordpress-web-design/'],
        ['Magento Development', '/magento-development/'],
        ['Custom Design & Development', '/custom-design-and-development/'],
    ],
    'AI & Resources' => [
        ['Free SEO Audit', '/free-seo-audit/'],
        ['ChatGPT SEO', '/chatgpt-seo/'],
        ['AI Overviews SEO', '/google-ai-overviews-seo/'],
        ['AI Development', '/ai-development/'],
        ['Blog', '/blog/'],
        ['E-books', '/ebooks/'],
        ['Guides', '/guides/'],
        ['Case Studies', '/case-studies/'],
    ],
    'Company' => [
        ['About Us', '/about-us/'],
        ['Pricing', '/pricing/'],
        ['Contact Us', '/contact-us/'],
        ['Privacy Policy', '/privacy-policy/'],
        ['Terms & Conditions', '/terms-and-conditions/'],
    ],
];
