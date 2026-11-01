<?php
/**
 * Navigation and footer structure.
 *
 * Order: Digital Marketing | AI Search | Web Development | Enterprise & IT |
 * Case Studies | Resources | Pricing | Company.
 *
 * Digital marketing and branding are the core business, so they lead. Enterprise
 * & IT is a separate practice for a different buyer, and stays in its own menu.
 *
 * One rule keeps the menu honest: a page appears in one menu only. A link listed
 * twice makes a visitor wonder whether the two are different things.
 *
 * Each item may have: label, url, desc (tagline), columns [heading, url?,
 * items[[label, url, desc?]]], promo [eyebrow, title, text, button, url].
 */

$NAV = [
    [
        'label' => 'Digital Marketing',
        'url'   => '/digital-marketing/',
        'desc'  => 'Search, paid media, content and brand, run by one team against one number: qualified pipeline.',
        'columns' => [
            [
                'heading' => 'SEO',
                'url'     => '/seo/',
                'items'   => [
                    ['SEO Services', '/seo-services/', 'Rank higher, earn revenue'],
                    ['Local SEO', '/local-seo/', 'Own the map pack'],
                    ['Ecommerce SEO', '/ecommerce-seo/', 'Grow store revenue organically'],
                    ['Lead Generation SEO', '/lead-generation-seo/', 'Pipeline from search'],
                    ['Enterprise SEO', '/enterprise-seo/', 'SEO at scale'],
                    ['Franchise SEO', '/franchise-seo/', 'Every location, one brand'],
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
                'heading' => 'Brand & Content',
                'url'     => '/content-marketing/',
                'items'   => [
                    ['Branding', '/branding-services/', 'Positioning and identity'],
                    ['Social Media Marketing', '/social-media-marketing/', 'Audience into pipeline'],
                    ['SEO Content Writing', '/seo-content-writing/', 'Content that ranks and sells'],
                    ['Copywriting', '/copywriting-services/', 'Words that sell'],
                    ['Email Marketing', '/email-marketing/', 'Owned-audience revenue'],
                    ['Reputation Management', '/reputation-management/', 'Protect what Google says'],
                    ['Outbound Marketing', '/outbound-marketing/', 'Pipeline on demand'],
                ],
            ],
        ],
        'promo' => ['Not sure where to start?', 'Tell us the goal, we bring the plan', 'Share what you want to grow. We reply with the right channel mix and honest numbers in one working day.', 'Get My Growth Plan', '/contact-us/'],
    ],

    [
        'label' => 'AI Search',
        'url'   => '/ai/',
        'desc'  => 'Be the brand ChatGPT, Gemini, Perplexity and Google AI Overviews name when buyers ask.',
        'columns' => [
            [
                'heading' => 'AI Visibility',
                'url'     => '/ai/',
                'items'   => [
                    ['AI SEO', '/ai-seo/', 'The full AI visibility program'],
                    ['Google AI Overviews SEO', '/google-ai-overviews-seo/', 'Be the cited answer'],
                    ['ChatGPT SEO', '/chatgpt-seo/', 'Get recommended in chat'],
                    ['Perplexity SEO', '/perplexity-seo/', 'Win the citation engine'],
                    ['Selling on ChatGPT', '/selling-on-chatgpt/', 'Commerce in the chat era'],
                ],
            ],
            [
                'heading' => 'Learn AI Search',
                'url'     => '/ai-blog-posts/',
                'items'   => [
                    ['What is AEO?', '/blog/what-is-answer-engine-optimization/', 'The plain-English guide'],
                    ['Rank in AI Overviews', '/blog/how-to-rank-in-google-ai-overviews/', 'Step-by-step playbook'],
                    ['The AI Search Playbook', '/ebooks/ai-search-playbook/', 'Read the full e-book'],
                ],
            ],
        ],
        'promo' => ['AI visibility review', 'What does AI say about you?', 'We check how ChatGPT, Gemini, Perplexity and AI Overviews present your brand, and what to fix first.', 'Request a Review', '/contact-us/'],
    ],

    [
        'label' => 'Web Development',
        'url'   => '/web-services/',
        'desc'  => 'Fast, search-ready websites built to convert, billed once and yours to keep.',
        'columns' => [
            [
                'heading' => 'By Goal',
                'url'     => '/web-services/',
                'items'   => [
                    ['Web Design', '/web-design/', 'Fast, conversion-first sites'],
                    ['Ecommerce Web Design', '/ecommerce-web-design/', 'Stores built to sell'],
                    ['Lead Gen Web Design', '/lead-gen-web-design/', 'Websites that enquire'],
                    ['Headless Web Design', '/headless-web-design/', 'Composable and very fast'],
                ],
            ],
            [
                'heading' => 'By Platform',
                'items'   => [
                    ['Shopify', '/shopify-web-design/', 'Shopify done properly'],
                    ['WordPress', '/wordpress-web-design/', 'WordPress without bloat'],
                    ['WooCommerce', '/woocommerce-web-design/', 'WordPress ecommerce, fast'],
                    ['Magento', '/magento-web-design/', 'Adobe Commerce UX'],
                    ['BigCommerce', '/bigcommerce-web-design/', 'Built to scale and sell'],
                ],
            ],
            [
                'heading' => 'Engineering',
                'items'   => [
                    ['Custom Development', '/custom-design-and-development/', 'Bespoke digital builds'],
                    ['Magento Development', '/magento-development/', 'Modules, migrations, speed'],
                    ['AI Development', '/ai-development/', 'Chatbots, automation, MVPs'],
                    ['Websites We Built', '/web-services/#work', 'Live client sites'],
                ],
            ],
        ],
        'promo' => ['One-time build cost', 'Pay once, own it forever', 'Design and development are billed once. No recurring development fees, and a 90+ PageSpeed target on every build.', 'Get a Build Quote', '/contact-us/'],
    ],

    [
        'label' => 'Enterprise & IT',
        'url'   => '/enterprise-it-services/',
        'desc'  => 'A separate practice: enterprise applications and technology talent, delivered by specialists.',
        'columns' => [
            [
                'heading' => 'Enterprise Applications',
                'url'     => '/enterprise-it-services/',
                'items'   => [
                    ['SAP CPQ', '/sap-cpq-services/', 'Configure, price, quote'],
                    ['Salesforce', '/salesforce-services/', 'Sales, service, revenue cloud'],
                    ['Odoo ERP', '/odoo-erp-services/', 'Implementation and rollout'],
                    ['ERPNext', '/erpnext-services/', 'Open-source ERP, done right'],
                ],
            ],
            [
                'heading' => 'Technology Talent',
                'items'   => [
                    ['IT Staffing', '/it-staffing-services/', 'Contract, contract-to-hire, direct'],
                ],
            ],
        ],
        'promo' => ['Enterprise enquiry', 'Tell us about the project', 'Share the scope and timeline. A consultant replies within one working day with a clear next step.', 'Talk to a Consultant', '/contact-us/'],
    ],

    [
        'label' => 'Case Studies',
        'url'   => '/case-studies/',
        'desc'  => 'Named clients, live websites and measured numbers you can check yourself.',
        'columns' => [
            [
                'heading' => 'Client Stories',
                'url'     => '/case-studies/',
                'items'   => [
                    ['Sai Impression', '/case-studies/sai-impression-label-manufacturer/', 'B2B manufacturing, Chennai'],
                    ['FirstChoice Auto Parts', '/case-studies/firstchoice-used-auto-parts/', 'Used auto parts, AI visibility, US'],
                    ['FlowEazy', '/case-studies/floweazy-odoo-partner-website/', 'B2B software, global'],
                    ['Cafy', '/case-studies/cafy-careers-uk-job-platform/', 'SaaS job platform, UK'],
                ],
            ],
        ],
        'promo' => ['Your turn', 'Want your name on this list?', 'Every engagement starts with an honest audit and a conversation about fit.', 'Start the Conversation', '/contact-us/'],
    ],

    [
        'label' => 'Resources',
        'url'   => '/blog/',
        'desc'  => 'Free playbooks and guides from our strategists, readable right here with no form to fill.',
        'columns' => [
            [
                'heading' => 'Learn',
                'items'   => [
                    ['Blog', '/blog/', 'New insight every month'],
                    ['Guides', '/guides/', 'Complete, evergreen references'],
                    ['E-books', '/ebooks/', 'Deep dives, free to read'],
                ],
            ],
            [
                'heading' => 'Popular Reads',
                'items'   => [
                    ['How to choose an agency', '/blog/how-to-choose-a-digital-marketing-agency/', 'The buyer\'s checklist'],
                    ['SEO vs PPC', '/blog/seo-vs-ppc-which-drives-better-roi/', 'The honest comparison'],
                    ['Traffic but no leads?', '/blog/why-your-website-gets-traffic-but-no-leads/', 'The five-cause diagnosis'],
                ],
            ],
            [
                'heading' => 'Free Tool',
                'items'   => [
                    ['Free SEO Audit', '/free-seo-audit/', 'Results in seconds'],
                ],
            ],
        ],
    ],

    [
        'label' => 'Company',
        'url'   => '/about-us/',
        'desc'  => 'A Chennai team working with growing businesses in India and around the world.',
        'columns' => [
            [
                'heading' => 'Who We Are',
                'items'   => [
                    ['About VTurnU', '/about-us/', 'Our story and what we believe'],
                    ['How We Work', '/how-we-work/', 'From first call to reporting'],
                    ['Our Clients', '/our-clients/', 'Brands that turned with us'],
                ],
            ],
            [
                'heading' => 'Trust & Policies',
                'items'   => [
                    ['Our AI Policy', '/ai-policy/', 'How we use AI, honestly'],
                    ['Privacy Policy', '/privacy-policy/', 'What we collect and why'],
                    ['Terms & Conditions', '/terms-and-conditions/', 'Engagement terms'],
                ],
            ],
        ],
        'promo' => ['Talk to us', 'We reply within one working day', 'Tell us where you want to be in twelve months and we will bring the map.', 'Contact Us', '/contact-us/'],
    ],
];

/**
 * Footer, rendered by includes/footer.php.
 * Simple label => [[label, url], ...] columns.
 */
$FOOTER = [
    'Digital Marketing' => [
        ['SEO Services', '/seo-services/'],
        ['Local SEO', '/local-seo/'],
        ['Google Ads', '/google-ads/'],
        ['Facebook Ads', '/facebook-ads/'],
        ['Branding', '/branding-services/'],
        ['Social Media Marketing', '/social-media-marketing/'],
        ['Email Marketing', '/email-marketing/'],
    ],
    'AI Search' => [
        ['AI SEO', '/ai-seo/'],
        ['AI Overviews SEO', '/google-ai-overviews-seo/'],
        ['ChatGPT SEO', '/chatgpt-seo/'],
        ['Perplexity SEO', '/perplexity-seo/'],
        ['Free SEO Audit', '/free-seo-audit/'],
    ],
    'Web & Engineering' => [
        ['Web Design', '/web-design/'],
        ['Ecommerce Web Design', '/ecommerce-web-design/'],
        ['Shopify', '/shopify-web-design/'],
        ['WordPress', '/wordpress-web-design/'],
        ['Custom Development', '/custom-design-and-development/'],
        ['AI Development', '/ai-development/'],
    ],
    'Enterprise & IT' => [
        ['SAP CPQ', '/sap-cpq-services/'],
        ['Salesforce', '/salesforce-services/'],
        ['Odoo ERP', '/odoo-erp-services/'],
        ['ERPNext', '/erpnext-services/'],
        ['IT Staffing', '/it-staffing-services/'],
    ],
    'Company' => [
        ['About Us', '/about-us/'],
        ['How We Work', '/how-we-work/'],
        ['Our Clients', '/our-clients/'],
        ['Case Studies', '/case-studies/'],
        ['Blog', '/blog/'],
        ['Guides', '/guides/'],
        ['E-books', '/ebooks/'],
    ],
];
