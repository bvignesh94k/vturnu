<?php
/**
 * /technical-content-writing-services/ : content for buyers of technical
 * products. The page is clear that this is not user documentation.
 */

return [
    'name' => 'Technical Content Writing Services',
    'card' => 'Accurate content for software, IT and industrial buyers.',
    'title' => 'Technical Content Writing Services for Software & Industry',
    'meta'  => 'Technical content writing for software, IT and industrial companies: engineer interviews, accurate drafts and a technical review before publishing. Talk to us.',
    'updated' => '2026-11-11',

    'eyebrow' => 'Technical content writing services',
    'hero' => 'Technical content writing for products that are hard to explain',
    'lede' => 'Software, IT and industrial buyers can tell when a writer does not understand the product. We interview your engineers, learn the subject properly and write content that is accurate enough for a specialist and clear enough for the person who signs the order.',
    'layout' => 'flip',
    'chips' => ['An engineer interview for every piece', 'A technical review before publishing', 'For software, IT and industrial companies'],
    'visual' => ['pair', 'Engineer\'s note', 'Rule: block discount > 15% without approval. Sync to ERP.', 'Buyer\'s page', 'Sales can quote in minutes. Large discounts wait for a manager.', 'Checked by your engineer'],
    'cta' => 'Discuss my technical content',
    'alt' => ['See what we write', '#scope'],

    'proof' => [
        ['101', 'pages built for FlowEazy, an Odoo ERP partner'],
        ['15', 'long-form articles for Sai Impression, a label manufacturer'],
        [(string) $cl_it, 'enterprise software and IT guides on our own blog'],
    ],

    'answer' => ['What is technical content writing?', 'Technical content writing explains complex products, software and processes to the people who evaluate and buy them. It covers articles, solution pages, white papers, comparison guides and product explainers. It differs from technical documentation, which instructs existing users: technical content helps a buyer understand a product well enough to choose it.'],
    'facts' => [
        ['Who it is for', 'Software, IT services and industrial companies with a technical sale.'],
        ['What you can order', 'Articles, solution pages, comparison guides, white papers and explainers.'],
        ['How accuracy is kept', 'An engineer interview before writing, and a technical review before publishing.'],
        ['How it is priced', 'Per project or as a monthly plan, quoted in writing. Projects start from ' . $cl_min . '.'],
    ],

    'pain' => ['Technical buyers stop reading at the first mistake',
        ['The writer guessed', 'One wrong term in the second paragraph, and an engineer closes the tab.'],
        ['Your engineers write it themselves', 'Accurate, dense, and unreadable for the finance director who approves the budget.'],
        ['Marketing simplifies it into nothing', 'Every product becomes a robust, scalable platform.'],
        ['Reviews take months', 'Drafts sit with busy specialists because they need rewriting, not checking.'],
    ],

    'edit' => ['The same feature, before and after an editor', 'Technical readers trust what they can picture and check. The examples are illustrations, not client work.', [
        ['Our robust, scalable platform leverages cutting-edge technology to streamline your operations.', 'The system checks every quote against your pricing rules before it reaches the customer, so a wrong discount cannot be sent.', 'Describe the mechanism. A reader can test a mechanism against what they know.'],
        ['Supports seamless integration with all major ERPs.', 'Connects to two named ERP systems through their standard APIs. Anything else needs a custom connector, which takes longer.', 'State the limits. It is often the most persuasive line on the page.'],
        ['Utilises advanced algorithms for optimal performance.', 'Recalculates the price in under a second when an option changes.', 'Replace the adjective with what the user will notice.'],
    ]],

    'scope' => ['What we write for technical companies', [
        ['Explainer articles', 'How something works, when to use it and what it costs, for readers who are evaluating.'],
        ['Solution and product pages', 'What the product does, how it fits existing systems and what it will not do.'],
        ['Comparison guides', 'Honest side-by-side comparisons, including where the alternative is the better choice.'],
        ['White papers', 'Deeper arguments with data, for buying committees and technical evaluators.', '/white-paper-writing-services/'],
        ['Implementation guides', 'What a project involves, step by step, written for the buyer rather than the user.'],
        ['Case studies', 'Technical projects told with the constraints, the approach and the measured result.', '/case-study-writing-services/'],
    ]],

    'cta1' => ['Hard product to explain?', 'Tell us what it does in your own words. A senior strategist replies within one working day with how we would approach it.', 'Discuss my technical content'],

    'steps' => ['How we keep it accurate', [
        ['Learn', 'We read your documentation and study the product before the first call.'],
        ['Interview', 'An engineer explains the subject. We ask until we could explain it ourselves.'],
        ['Draft', 'Written for the buyer, with each term defined once and used consistently.'],
        ['Technical review', 'Your specialist checks the facts only, which is far quicker than rewriting.'],
        ['Edit and publish', 'A final edit for clarity, then publishing with diagrams or tables where they help.'],
    ]],

    'table' => ['Technical content and technical documentation compared', ['Type', 'Reader', 'Purpose', 'Examples'], [
        ['Technical content', 'Buyers and evaluators', 'Help them choose', 'Articles, comparison guides, white papers'],
        ['Technical documentation', 'Existing users and developers', 'Help them use the product', 'Manuals, API references, release notes'],
        ['Sales engineering content', 'Buying committees', 'Answer detailed objections', 'Architecture notes, security answers, tender responses'],
    ], 'This page is about content for buyers. If you need user manuals or API references, tell us, and we will say honestly whether we are the right team.'],

    'case' => [
        'slug' => 'floweazy-odoo-partner-website',
        'n' => '101',
        'l' => 'pages for an Odoo ERP partner',
        'story' => 'FlowEazy implements Odoo ERP for companies in the US, Canada and beyond. We built 101 pages for it, including nine industry pages, each with its own title, description and answer block, and produced thirty-nine blog posts with a direct answer at the top of each.',
    ],

    'cta2' => ['Selling enterprise software or IT services?', 'Our own team implements SAP CPQ, Salesforce, Odoo and ERPNext, so the vocabulary is familiar.', 'See Enterprise & IT', '/enterprise-it-services/', ['or discuss my technical content', '#enquire']],

    'fit' => ['Your product needs explaining before it can be sold', 'An engineer or product specialist can give us short interviews', 'You would rather be precise than impressive'],

    'faqs' => [
        ['How much does technical content writing cost?', 'It depends on the depth of the subject and the format. We quote per project or as a monthly plan, in writing, after a short call. Projects start from ' . $cl_min . '.'],
        ['Do your writers have technical backgrounds?', 'Our writers are experienced at explaining technical subjects, and our own team delivers enterprise software projects. The depth in each piece still comes from your engineers, through an interview and a technical review.'],
        ['How much of our engineers\' time will you need?', 'A short interview for each piece, and a few minutes to check the draft for accuracy. We prepare the questions in advance so the call is efficient.'],
        ['What is the difference between technical content and technical documentation?', 'Technical content helps a buyer understand and choose a product. Technical documentation helps an existing user operate it. We focus on content for buyers.'],
        ['Can you write about our software if it is complex or new?', 'Yes. We study your documentation and demos first, then interview the people who built it, and your specialist reviews every draft for accuracy.'],
        ['Do you use AI for technical content?', 'AI tools may help with research and structure. They also make confident technical mistakes, so a person writes every piece and your specialist checks it before it is published.'],
        ['Which technical fields have you written about?', 'ERP, CRM and quoting software, IT services, labels and packaging, and auto parts. For a new field, the interviews and your technical review keep the content accurate.'],
    ],

    'form' => ['Tell us about your product', 'Share what it does and who buys it. A senior strategist replies within one working day.'],

    'reading' => [
        ['What is SAP CPQ and when do you need it?', '/blog/what-is-sap-cpq-and-when-do-you-need-it/'],
        ['Odoo vs ERPNext: which ERP fits your business?', '/blog/odoo-vs-erpnext-which-erp-fits-your-business/'],
        ['Does Google penalise AI content?', '/blog/does-google-penalise-ai-content/'],
    ],
];
