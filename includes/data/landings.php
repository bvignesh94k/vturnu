<?php
/**
 * Enterprise & IT landing pages, rendered by templates/landing.php.
 *
 * This practice is a separate line of business from digital marketing, so these
 * pages speak to a different buyer: an IT head, a sales operations lead, a CFO.
 * No partner status or certification is claimed anywhere, because none has been
 * supplied. Add it here once it can be evidenced.
 */

$LANDINGS = [

'sap-cpq-services' => [
    'eyebrow' => 'SAP CPQ services',
    'title' => 'SAP CPQ Implementation & Consulting Services | VTurnU',
    'meta'  => 'SAP CPQ implementation, configuration and integration with S/4HANA and Salesforce. Faster, accurate quotes for complex products. Talk to a consultant.',
    'h1'    => 'SAP CPQ Services That Turn Complex Quoting Into Revenue',
    'lede'  => 'When a quote takes days and depends on the one person who knows the price list, deals slow down and margins leak. We implement and improve SAP CPQ so sales can quote complex products correctly, the first time.',
    'chips' => ['Discovery to go-live', 'S/4HANA and CRM integration', 'Rule and pricing migration'],
    'answer' => ['What is SAP CPQ?', 'SAP CPQ (Configure, Price, Quote) is SAP\'s cloud application for selling configurable products and services. It guides a salesperson through valid product options, applies pricing and discount rules, routes approvals, and produces a branded quote document, usually connected to a CRM such as SAP Sales Cloud or Salesforce and an ERP such as SAP S/4HANA.'],
    'pains' => [
        ['Quotes take days', 'Reps wait on engineering or pricing teams for every non-standard deal, and the buyer moves on.'],
        ['Errors reach the customer', 'Invalid configurations and wrong prices create rework, disputes and awkward re-quotes.'],
        ['Discounts nobody controls', 'Approvals happen over email, so margin erodes without anyone seeing it.'],
        ['Knowledge in one head', 'Pricing logic lives in spreadsheets and with a few senior people, which is a real risk to the business.'],
    ],
    'capabilities' => [
        ['Implementation', 'Full SAP CPQ rollout: product catalog, configuration rules, pricing, approvals and quote templates.'],
        ['Product modeling', 'Attributes, rules and constraints that reflect how your products are really sold.'],
        ['Pricing and approvals', 'Price books, margin floors and tiered approval workflows with a clear audit trail.'],
        ['Integration', 'Connections to SAP S/4HANA, SAP Sales Cloud, Salesforce or Microsoft Dynamics, so orders flow without re-keying.'],
        ['Scripting and customization', 'Custom logic where standard rules stop, built to survive upgrades and documented for your team.'],
        ['Support and optimization', 'Fixing a struggling CPQ, cleaning up rules, improving performance and training users.'],
    ],
    'process' => [
        ['Discover', 'We map your products, pricing, approvals and systems, and agree what success looks like.'],
        ['Design', 'Solution design, data model and integration plan, signed off before build.'],
        ['Build', 'Configuration in short sprints, with your sales team reviewing real quotes.'],
        ['Test', 'Quote scenarios tested end to end, including the awkward edge cases.'],
        ['Launch', 'Phased go-live, user training and hypercare.'],
    ],
    'table' => [
        'caption' => 'Quoting before and after a well-run CPQ',
        'head' => ['Area', 'Spreadsheets and email', 'SAP CPQ'],
        'rows' => [
            ['Configuration', 'Depends on rep knowledge', 'Guided, only valid options'],
            ['Pricing', 'Manual, version risk', 'Central rules and price books'],
            ['Approvals', 'Email threads', 'Automatic routing with audit trail'],
            ['Quote document', 'Copied and edited', 'Generated, branded, consistent'],
            ['ERP order', 'Re-keyed', 'Passed through integration'],
        ],
    ],
    'who' => ['Manufacturers selling configurable products', 'Technology and telecom companies with complex bundles', 'Sales operations teams drowning in non-standard deals', 'Companies already on SAP or moving to S/4HANA'],
    'faqs' => [
        ['How long does a SAP CPQ implementation take?', 'It depends on product complexity and integrations. A focused first phase for one product line typically takes a few months, and we fix the timeline after discovery rather than quoting one blind.'],
        ['Does SAP CPQ work with Salesforce?', 'Yes. SAP CPQ can sit alongside Salesforce as well as SAP Sales Cloud, with opportunities and quotes passing between the systems through integration.'],
        ['Can you fix an existing SAP CPQ setup?', 'Yes. Many projects start with an underperforming implementation: slow configuration, tangled rules or low adoption. We audit it and fix the causes.'],
        ['Do we need S/4HANA to use SAP CPQ?', 'No. SAP CPQ is a cloud product that can connect to S/4HANA, older SAP systems or a non-SAP ERP. The integration design changes, not the core value.'],
        ['Will our team be able to maintain it?', 'That is the goal. We document rules, train administrators and hand over, with support available if you want it.'],
        ['What do you need from us to start?', 'A price list, a product list, a few real quotes including a painful one, and an hour with the people who currently do the quoting.'],
    ],
    'form' => ['Discuss your SAP CPQ project', 'Tell us about your products and systems. A consultant replies within one working day.'],
    'service' => 'SAP CPQ',
],

'salesforce-services' => [
    'eyebrow' => 'Salesforce services',
    'title' => 'Salesforce Consulting & Implementation Services | VTurnU',
    'meta'  => 'Salesforce implementation, customization, integration and support for growing businesses: Sales Cloud, Service Cloud, automation and clean reporting.',
    'h1'    => 'Salesforce Consulting That Your Sales Team Will Actually Use',
    'lede'  => 'A CRM only pays back when people use it and leaders trust the numbers. We design Salesforce around how you really sell and serve, then connect it to the rest of your business.',
    'chips' => ['Sales and Service Cloud', 'Automation with Flow', 'Integrations and data migration'],
    'answer' => ['What do Salesforce consulting services include?', 'Salesforce consulting covers planning, configuring and improving Salesforce for your business: setting up objects, sales processes and service workflows, automating work with Flow, building custom components with Apex and Lightning Web Components where needed, migrating data, integrating other systems, and training users so adoption sticks.'],
    'pains' => [
        ['Low adoption', 'Reps keep their own spreadsheets because the CRM slows them down.'],
        ['Numbers nobody trusts', 'Duplicate records and inconsistent stages make every forecast an argument.'],
        ['Disconnected systems', 'Marketing, ERP and support sit in separate tools, so nobody sees the whole customer.'],
        ['Over-customization', 'Years of custom code make every change slow and risky.'],
    ],
    'capabilities' => [
        ['New implementations', 'Sales Cloud and Service Cloud set up around your actual process, not a generic template.'],
        ['Automation', 'Flow-based automation for routing, approvals, reminders and handoffs.'],
        ['Custom development', 'Apex and Lightning Web Components for the cases configuration cannot reach.'],
        ['Integration', 'Connections to ERP, marketing, telephony and finance systems.'],
        ['Data migration and cleanup', 'Deduplication, mapping and validated migration from spreadsheets or another CRM.'],
        ['Reporting', 'Dashboards that give leadership one version of pipeline, forecast and service performance.'],
    ],
    'process' => [
        ['Assess', 'We review your process, data and current org, and identify what is really holding you back.'],
        ['Design', 'Roles, stages, automation and reports agreed with the people who will use them.'],
        ['Build', 'Configuration first, custom code only where it earns its place.'],
        ['Migrate and test', 'Data moved and verified, with real users testing real scenarios.'],
        ['Adopt', 'Training, launch support and a review of what people actually use.'],
    ],
    'table' => [
        'caption' => 'Configure or customize? How we decide',
        'head' => ['Need', 'Our default', 'Why'],
        'rows' => [
            ['Routing and approvals', 'Flow', 'Maintainable by admins'],
            ['Layouts and fields', 'Configuration', 'Survives upgrades'],
            ['Complex calculations', 'Apex', 'When Flow would be fragile'],
            ['Tailored screens', 'Lightning Web Components', 'When standard pages fall short'],
            ['System connections', 'API or middleware', 'Matched to volume and risk'],
        ],
    ],
    'who' => ['Growing sales teams outgrowing spreadsheets', 'Companies rescuing a stalled Salesforce rollout', 'Service teams that need case management', 'Businesses connecting Salesforce to an ERP'],
    'faqs' => [
        ['How long does a Salesforce implementation take?', 'A focused Sales Cloud rollout for a small team can take weeks. Multi-department rollouts with integrations take longer. We give a fixed plan after the assessment.'],
        ['Can you take over an existing Salesforce org?', 'Yes. We start with a health check of your data, automation and customizations, then prioritize what to fix.'],
        ['Do you handle data migration?', 'Yes, including deduplication and validation, because moving bad data into a new CRM simply moves the problem.'],
        ['Is Salesforce right for a small business?', 'Sometimes. If your process is simple, a lighter CRM may serve you better, and we will say so before you commit to licenses.'],
        ['Can Salesforce connect to our ERP?', 'Usually yes, through APIs or middleware. We scope what data should flow, in which direction and how often.'],
        ['Do you train our team?', 'Yes. Adoption is the point of the project, so training and post-launch support are part of the plan.'],
    ],
    'form' => ['Talk to a Salesforce consultant', 'Tell us where you are today. We reply within one working day with a clear next step.'],
    'service' => 'Salesforce',
],

'odoo-erp-services' => [
    'eyebrow' => 'Odoo ERP services',
    'title' => 'Odoo ERP Implementation & Customization Services | VTurnU',
    'meta'  => 'Odoo ERP implementation, customization, migration and support for manufacturers, traders and service firms. One system for sales, stock, accounts and more.',
    'h1'    => 'Odoo ERP Implementation That Runs Your Whole Business in One Place',
    'lede'  => 'When sales, stock, purchasing and accounts live in separate tools, every month-end is a reconciliation project. We implement Odoo so one system runs the business, starting with what hurts most.',
    'chips' => ['Phased rollout', 'Community or Enterprise', 'GST-ready for India'],
    'answer' => ['What is Odoo ERP?', 'Odoo is a modular business software suite covering CRM, sales, inventory, manufacturing, purchasing, accounting, HR, projects and websites in one system. It is offered as an open-source Community edition and a paid Enterprise edition, so a company can start with the modules it needs and add more as it grows.'],
    'pains' => [
        ['Stock you cannot trust', 'The system says one thing and the warehouse says another.'],
        ['Data typed twice', 'Orders are re-entered between sales, stores and accounts, and errors creep in.'],
        ['Slow month-end', 'Closing the books depends on chasing spreadsheets.'],
        ['No single view', 'Owners wait for reports instead of seeing the business today.'],
    ],
    'capabilities' => [
        ['Implementation', 'Module selection, setup, chart of accounts, workflows and go-live.'],
        ['Customization', 'Python modules for the processes that make your business different, kept upgrade-safe.'],
        ['Migration', 'Data moved from Tally, spreadsheets or an older ERP, then reconciled.'],
        ['Integration', 'Connections to ecommerce stores, payment gateways, shipping and marketplaces.'],
        ['Version upgrades', 'Moves to newer Odoo versions with custom modules tested and ported.'],
        ['Support and training', 'User training and ongoing help so the system keeps improving after launch.'],
    ],
    'process' => [
        ['Map', 'We walk through how orders, stock and money really move today.'],
        ['Prioritize', 'We agree the modules that deliver value first, and what waits.'],
        ['Configure', 'Setup and customization, reviewed with your team in short cycles.'],
        ['Migrate', 'Masters and opening balances loaded and reconciled.'],
        ['Go live', 'Parallel run where needed, training and close support in the first weeks.'],
    ],
    'table' => [
        'caption' => 'Odoo Community and Enterprise at a glance',
        'head' => ['Consideration', 'Community', 'Enterprise'],
        'rows' => [
            ['License', 'Open source, no user fee', 'Paid per user'],
            ['Scope', 'Core modules', 'Full suite, including more accounting and mobile features'],
            ['Hosting', 'Self-hosted', 'Odoo Online, Odoo.sh or self-hosted'],
            ['Best for', 'Simple needs and in-house IT', 'Growing firms wanting the full suite and official support'],
        ],
    ],
    'who' => ['Manufacturers and assemblers', 'Traders and distributors', 'Multi-branch retailers', 'Service companies outgrowing Tally and spreadsheets'],
    'faqs' => [
        ['How long does an Odoo implementation take?', 'A focused rollout for a small company often takes two to three months. Larger, multi-company projects run in phases over longer. We fix the plan after mapping your processes.'],
        ['Community or Enterprise: which should we choose?', 'It depends on the modules you need and who will maintain the system. We compare both against your actual requirements before you pay for licenses.'],
        ['Can Odoo handle Indian GST?', 'Yes. Odoo has localization for India covering GST, and we configure and test it against your invoicing and filing needs, with your accountant involved.'],
        ['Can you migrate from Tally?', 'Yes. Masters and opening balances are moved and reconciled. Many teams keep historical records in Tally for reference.'],
        ['Is Odoo better than ERPNext?', 'Neither is better for everyone. Odoo has a wider app ecosystem and paid support, while ERPNext is fully open source. See our ERPNext page, or ask us to compare them for your business.'],
        ['Will upgrades break our customizations?', 'They can, if customizations are careless. We build them as separate modules and test them against each upgrade.'],
    ],
    'form' => ['Plan your Odoo rollout', 'Tell us what you run today and what hurts most. We reply within one working day.'],
    'service' => 'Odoo ERP',
],

'erpnext-services' => [
    'eyebrow' => 'ERPNext services',
    'title' => 'ERPNext Implementation & Customization Services | VTurnU',
    'meta'  => 'ERPNext implementation, Frappe customization, hosting and support. Open-source ERP for accounting, stock, manufacturing and HR without per-user license fees.',
    'h1'    => 'ERPNext Implementation: Full ERP Without the License Bill',
    'lede'  => 'ERPNext gives growing companies accounting, inventory, manufacturing and HR in one open-source system. We set it up properly, so free software does not turn into an expensive mess.',
    'chips' => ['No per-user license fee', 'Frappe customization', 'Hosting and support'],
    'answer' => ['What is ERPNext?', 'ERPNext is an open-source ERP system built on the Frappe Framework. It covers accounting, inventory, manufacturing, buying, selling, projects and more, and it has no per-user license fee. Businesses can self-host it or use managed hosting, and they pay for implementation and support rather than for seats.'],
    'pains' => [
        ['Per-seat costs that grow', 'License bills rise every time you hire.'],
        ['Locked-in data', 'Your records sit in a system you cannot truly control.'],
        ['DIY setups that stall', 'A free download is not an implementation, and half-configured systems get abandoned.'],
        ['Rigid workflows', 'Off-the-shelf tools make the business change to fit the software.'],
    ],
    'capabilities' => [
        ['Implementation', 'Accounting, stock, buying, selling and manufacturing configured to your process.'],
        ['Frappe development', 'Custom apps, doctypes, scripts and reports on the Frappe Framework.'],
        ['Migration', 'Items, customers, suppliers and opening balances moved in and verified.'],
        ['Hosting', 'Self-hosted or managed cloud, with backups and security handled.'],
        ['Integration', 'Connections to ecommerce, payment, shipping and other business systems.'],
        ['Support and upgrades', 'Help desk, training and version upgrades with custom apps tested.'],
    ],
    'process' => [
        ['Understand', 'We document your workflows, reports and pain points.'],
        ['Fit', 'We separate what ERPNext does out of the box from what truly needs building.'],
        ['Configure', 'Setup and custom apps in short, reviewed cycles.'],
        ['Train', 'Hands-on sessions with real data for each team.'],
        ['Go live', 'Cutover with close support, then steady improvement.'],
    ],
    'table' => [
        'caption' => 'ERPNext and Odoo compared',
        'head' => ['Consideration', 'ERPNext', 'Odoo'],
        'rows' => [
            ['License', 'Open source, all modules, no seat fee', 'Community free, Enterprise paid per user'],
            ['Framework', 'Frappe, Python, MariaDB', 'Odoo, Python, PostgreSQL'],
            ['Ecosystem', 'Smaller, focused community', 'Very large app ecosystem'],
            ['Best for', 'Control and low running costs', 'Breadth of apps and paid support'],
        ],
    ],
    'who' => ['SMEs that want control of their data', 'Manufacturers with bill of materials and work orders', 'Companies with growing teams and rising seat costs', 'Businesses with in-house IT'],
    'faqs' => [
        ['Is ERPNext really free?', 'The software has no license fee. You still pay for hosting, implementation, customization and support, and a good implementation is where the value is.'],
        ['Is ERPNext suitable for manufacturing?', 'Yes. It includes bills of materials, work orders, job cards and production planning, which suit many small and mid-sized manufacturers.'],
        ['Where is ERPNext hosted?', 'On your own server, a cloud provider or managed hosting. We recommend based on your IT capability and data requirements.'],
        ['Can ERPNext handle Indian GST?', 'Yes, through its India compliance tooling, which we configure and test with your accountant.'],
        ['ERPNext or Odoo: which is better?', 'ERPNext suits companies that value openness and low running costs. Odoo suits those wanting a larger app ecosystem. We compare both against your requirements.'],
        ['Can you customize ERPNext?', 'Yes. The Frappe Framework makes custom apps practical, and we keep them separate from the core so upgrades stay manageable.'],
    ],
    'form' => ['Discuss your ERPNext project', 'Tell us about your business and current systems. We reply within one working day.'],
    'service' => 'ERPNext',
],

'it-staffing-services' => [
    'eyebrow' => 'IT staffing services',
    'title' => 'IT Staffing Services | Contract, Contract-to-Hire & Direct',
    'meta'  => 'IT staffing for developers, cloud, data, SAP, Salesforce and QA roles: contract, contract-to-hire, direct hire and dedicated teams. Share your role today.',
    'h1'    => 'IT Staffing Services: The Right Engineers, Without the Long Search',
    'lede'  => 'A vacant role delays your roadmap every week it stays open. We find, screen and place technology professionals across every common stack, on the engagement model that suits the work.',
    'chips' => ['Contract and contract-to-hire', 'Direct hire', 'Dedicated teams'],
    'answer' => ['What are IT staffing services?', 'IT staffing services help companies find and hire technology professionals, either temporarily or permanently. A staffing partner sources candidates, screens their skills and handles the process, so hiring managers review a short list of qualified people. Common models are contract, contract-to-hire, direct hire and dedicated teams.'],
    'pains' => [
        ['Roles stay open for months', 'Projects slip while your team covers the gap.'],
        ['Too many weak CVs', 'Hiring managers spend their week screening people who cannot do the job.'],
        ['Sudden project demand', 'A new project needs skills you do not want on the payroll permanently.'],
        ['Costly mis-hires', 'A wrong hire costs far more than a slower search.'],
    ],
    'capabilities' => [
        ['Software engineering', 'Frontend, backend, full-stack and mobile developers across common stacks.'],
        ['Cloud and DevOps', 'AWS, Azure and Google Cloud engineers, DevOps and site reliability roles.'],
        ['Data and AI', 'Data engineers, analysts, data scientists and machine learning engineers.'],
        ['Enterprise applications', 'SAP, Salesforce, Odoo, ERPNext and Oracle consultants and developers.'],
        ['Quality and security', 'QA and test automation engineers and cybersecurity specialists.'],
        ['Delivery leadership', 'Project managers, business analysts, scrum masters and technical leads.'],
    ],
    'process' => [
        ['Brief', 'We learn the role, the team, the real must-haves and the budget.'],
        ['Source', 'We search our network and the market for people who fit.'],
        ['Screen', 'Technical screening and reference checks before you see a CV.'],
        ['Shortlist', 'A small list of strong candidates, with notes on each.'],
        ['Onboard', 'Interview support, offer, joining and follow-up after they start.'],
    ],
    'table' => [
        'caption' => 'Which staffing model fits your need',
        'head' => ['Model', 'How it works', 'Best when'],
        'rows' => [
            ['Contract', 'Professional works for a fixed term', 'Project work or a temporary skills gap'],
            ['Contract-to-hire', 'Contract first, with an option to hire', 'You want to assess fit before committing'],
            ['Direct hire', 'We recruit, you employ', 'A long-term role on your own team'],
            ['Dedicated team', 'A team working on your roadmap', 'You need capacity without building a department'],
        ],
    ],
    'who' => ['Startups scaling engineering fast', 'Enterprises covering project demand', 'IT services firms needing bench strength', 'Companies hiring for SAP, Salesforce or ERP skills'],
    'faqs' => [
        ['How quickly can you share candidates?', 'It depends on how specialized the role is. We confirm a realistic timeline once we understand the brief, and we would rather send three strong profiles than thirty weak ones.'],
        ['Which roles do you cover?', 'Software engineering, cloud and DevOps, data and AI, enterprise applications such as SAP and Salesforce, QA, cybersecurity and project delivery.'],
        ['What is contract-to-hire?', 'The professional starts on a contract and you can make them a permanent offer after an agreed period, so both sides can check the fit first.'],
        ['How do you screen candidates?', 'We verify experience, assess technical skills against your brief and check references before a profile reaches you.'],
        ['Do you place people outside India?', 'Yes, subject to the role and work authorization. Tell us the location and we will confirm what is practical.'],
        ['What does it cost?', 'Direct hires are usually a percentage of annual salary, and contract roles are an agreed rate. We confirm the commercials after understanding the role.'],
    ],
    'form' => ['Share your hiring need', 'Tell us the role, location and timeline. We reply within one working day.'],
    'service' => 'IT staffing',
],

];

foreach ($LANDINGS as $lslug => $l) {
    $PAGES[$lslug] = [
        'template' => 'landing',
        'title' => $l['title'], 'meta' => $l['meta'], 'h1' => $l['h1'], 'lede' => $l['lede'],
        'parent' => 'enterprise-it-services',
    ];
}
