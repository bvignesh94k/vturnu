<?php
/** Chapter content for the Complete Guide to Ecommerce Platforms. */
return [
'subtitle' => 'Choosing between Shopify, WooCommerce, Magento and headless without regret',
'description' => 'A decision guide for ecommerce platforms: defining requirements before shortlisting, honest platform profiles, total cost of ownership, SEO implications and a migration playbook.',
'subjects' => ['Ecommerce', 'Shopify', 'WooCommerce', 'Magento', 'Platform Selection'],
'chapters' => [

['Requirements before brands', <<<'MD'
Platform debates usually start with brand names and end in a decision nobody can justify. Reverse the order: define what you actually need, then see which platforms satisfy it.

## The questions that determine the answer

**1. Catalogue size and complexity.** Fifty simple products is a different problem from twelve thousand SKUs with configurable options and per-customer pricing.

**2. Order volume and peaks.** Steady 200 orders a month, or 200 in an hour during a sale?

**3. Selling model.** Simple retail, subscriptions, B2B with negotiated pricing, marketplace, or a mix?

**4. Markets.** Single currency and language, or multiple with different tax, payment and compliance requirements?

**5. Integrations.** Which systems must connect: ERP, accounting, warehouse, CRM, tax, shipping? List them specifically, with names.

**6. Team capability.** Do you have developers? Will you? A platform requiring engineering that you do not have is the wrong platform regardless of its capabilities.

**7. Customisation depth.** Do you need the checkout itself to behave unusually, or is a standard flow acceptable?

That last question is often decisive and is rarely asked early enough.

## Weighting the requirements

Not all requirements are equal. Sort into three:

| Class | Meaning | Effect on decision |
| --- | --- | --- |
| Must have | The business cannot operate without it | Eliminates platforms |
| Should have | Significant value, workaround exists | Scores platforms |
| Nice to have | Convenience | Tie-break only |

Most failed selections happen because a "nice to have" was treated as a "must have," or because a genuine "must have" was discovered after launch.

!NOTE Write the requirements document before speaking to any vendor. Vendor conversations reshape requirements toward what that vendor does well, and it happens without anyone intending it.

## The questions that do not matter as much as people think

- **Theme availability.** You will customise or replace it anyway.
- **App or plugin count.** Quality and maintenance matter, not quantity.
- **Which platform a competitor uses.** Their constraints are not yours.
- **Headline pricing.** Chapter three covers why this misleads.

## When to stay where you are

Migration is expensive, risky and disruptive. Before shortlisting alternatives, ask whether the current platform is genuinely the constraint.

Frequently the real problem is a badly built theme, an unoptimised database, or a missing integration, all of which are cheaper to fix than to migrate away from.

Migrate when the platform imposes a ceiling you have actually hit, not when the current implementation is poorly executed.
MD],

['Platform profiles', <<<'MD'
Honest assessments, including where each is a poor choice.

## Shopify

**Strengths.** Fastest to launch and operate. Hosting, security, PCI compliance and updates handled. Reliable checkout with strong conversion. Large app ecosystem. Scales through high-traffic events without engineering work.

**Weaknesses.** Checkout customisation is limited unless you are on the enterprise tier. Transaction fees apply unless using their payment product. App costs accumulate substantially. Complex B2B pricing rules need workarounds. You operate inside their constraints, permanently.

**Choose it when:** you want to sell rather than run infrastructure, your model is reasonably standard, and you value time to market and operational simplicity.

**Avoid it when:** you need deep checkout customisation, unusual pricing logic, or full data ownership.

## WooCommerce

**Strengths.** Complete control over code and data. No platform transaction fees. Enormous plugin ecosystem. Natural fit if content and commerce are equally important. Inexpensive to start.

**Weaknesses.** You own the hosting, security, updates and performance. Plugin conflicts are a genuine and recurring operational cost. Performance at scale requires real engineering. Quality varies wildly across plugins.

**Choose it when:** content marketing is central, you need customisation, and you have or will have technical capability.

**Avoid it when:** nobody will own maintenance. An unmaintained WooCommerce store becomes a security liability.

## Magento (Adobe Commerce)

**Strengths.** Built for complexity: large catalogues, multiple stores, sophisticated B2B pricing, granular permissions. Powerful natively without extensions.

**Weaknesses.** Expensive to build and maintain. Requires specialist developers who are scarce and costly. Heavy, and performance work is mandatory rather than optional. Long implementations.

**Choose it when:** genuine complexity justifies it and you have the budget for specialist engineering.

**Avoid it when:** your requirements would fit a simpler platform. Magento for a 200-product store is a common and expensive mistake.

## Headless and composable

**Strengths.** Complete front-end freedom. Best achievable performance. Multiple channels from one backend. Independent scaling of parts.

**Weaknesses.** Substantially more engineering, ongoing. Many things that are configuration elsewhere become development. Higher total cost. Requires a permanent team.

**Choose it when:** you have a real engineering team, front-end experience is a competitive differentiator, and you sell across several channels.

**Avoid it when:** you are choosing it for the architecture rather than a business requirement. This is the most common failure mode in the category.

## Others worth considering

**BigCommerce.** Similar positioning to Shopify with fewer transaction fee constraints and stronger native B2B features. Smaller ecosystem.

**Regional platforms.** In some markets, local platforms handle payment methods, tax and logistics integrations better than global ones. Worth evaluating if you sell primarily in one country.
MD],

['Cost of ownership', <<<'MD'
Headline pricing is the least useful number in platform selection. Model three years of total cost instead.

## What to include

| Cost | Frequently forgotten |
| --- | --- |
| Platform licence or subscription | Tier increases as you grow |
| Hosting and CDN | Self-hosted only, but substantial |
| Apps, plugins, extensions | The largest hidden cost on Shopify |
| Payment processing | Rates differ; transaction fees add up |
| Initial build | Design, development, data migration |
| Ongoing development | Every platform needs continuous work |
| Maintenance and security | Self-hosted only, non-optional |
| Support | Included, or a paid tier |
| Training | Real cost, usually ignored |
| Migration out | The exit cost of this decision |

## The app cost trap

A common pattern: a store launches on a modest monthly plan, then adds apps. Reviews, subscriptions, upsells, loyalty, advanced search, page builder, back-in-stock alerts.

Each is a monthly fee, individually reasonable. Collectively they frequently exceed the platform subscription several times over.

Before committing, list the apps you will need for your requirements and price them. It changes comparisons materially.

## The maintenance reality

Self-hosted platforms shift cost from subscription to labour. That trade can be worthwhile, but only if someone actually does the work.

Non-optional ongoing work on a self-hosted store:

- Security patches, promptly
- Plugin and core updates, tested before deploying
- Backups, verified by restoring occasionally
- Performance monitoring
- Uptime monitoring

Budget for it explicitly, or the "cheaper" platform becomes a breach.

## Total cost comparison, illustrative shape

For a mid-sized store over three years, the pattern typically looks like:

- **Shopify:** lower build, higher recurring, predictable, minimal engineering
- **WooCommerce:** lower recurring, higher labour, variable, needs capability
- **Magento:** high build, high maintenance, justified only by complexity
- **Headless:** highest build and ongoing, justified only by strategic need

The pattern matters more than any specific figure, and the ranking often surprises teams who compared subscription prices alone.

## Switching cost

The cost of a wrong decision is not just the new platform. It includes:

- Rebuilding the front end
- Migrating products, customers and orders
- Rebuilding integrations
- Retraining staff
- URL migration and the SEO risk in the next chapter
- Business disruption during transition

Factor this in when weighing a platform that "might" work against one that clearly does.
MD],

['SEO implications', <<<'MD'
Every platform can rank. They differ in how much work it takes and which problems you inherit.

## What to check on any platform

| Capability | Why it matters |
| --- | --- |
| Full control of title and meta description | Basic, but some platforms restrict templates |
| Editable URL structure | Especially product and category paths |
| Canonical tag control | Essential for faceted navigation |
| Redirect management | Bulk import matters during migration |
| robots.txt editing | Some platforms restrict this |
| Structured data | Native quality varies enormously |
| Faceted navigation control | The largest technical SEO issue in ecommerce |
| Pagination handling | Frequently poor by default |
| Server response time | Sets the ceiling for everything |
| Image optimisation | Modern formats, correct sizing |

## Platform-specific issues worth knowing

**Shopify.** Forces `/products/` and `/collections/` in URLs. Product pages accessible via multiple collection paths, requiring canonical discipline. robots.txt editable now but with constraints. Structured data depends on theme quality and is frequently wrong in purchased themes.

**WooCommerce.** Full control, which means full responsibility. Performance is the usual issue, driven by plugin load and hosting. Default faceted navigation via plugins often generates enormous crawlable spaces.

**Magento.** Powerful SEO controls natively. Layered navigation generates many URLs and needs deliberate configuration. Performance requires ongoing work.

**Headless.** Complete control and complete responsibility. Server-side rendering of above-fold content is mandatory rather than optional; client-only rendering causes real indexation and AI retrieval problems.

## Faceted navigation, the recurring problem

Every platform has this issue and every implementation must decide:

- Which filter combinations deserve indexing, usually one filter deep
- Which are crawlable but not indexed
- Which are blocked from crawling entirely

Get this wrong and crawl budget goes to combinations nobody searches while your actual category pages get visited rarely.

## Speed by platform

Managed platforms give you a reasonable floor and a ceiling you cannot exceed. Self-hosted gives no floor and no ceiling.

Practically: a well-built Shopify store is fast without effort. A well-built WooCommerce store can be faster, with effort. A badly built one on either is slow.

For most teams the managed floor is worth more than the theoretical ceiling.
MD],

['Migration playbook', <<<'MD'
Migrations lose traffic when they are executed carelessly. They do not have to.

## Before you start

1. **Full crawl of the current site.** Every URL, title, meta, status code. This is your reference and you cannot recreate it afterwards.
2. **Export current performance.** Rankings, top landing pages, Search Console data. You need a baseline to detect problems.
3. **Inventory integrations** and confirm equivalents exist on the new platform.
4. **Agree a rollback plan.** What triggers reverting, and how.

## URL mapping

The single most important task. Do it manually or semi-manually. Do not automate it and hope.

- Every existing URL maps to one specific new URL
- Never bulk-redirect to the homepage. This destroys the value of every link you have earned
- Products that no longer exist map to their closest equivalent or their category
- Keep the mapping document permanently

## Content and data

- Migrate product descriptions, including hand-written ones. Do not accept regenerated boilerplate
- Preserve reviews. They are hard to rebuild and affect conversion
- Migrate customer accounts, with a password reset flow
- Preserve order history for support and returns
- Move images at full quality; do not re-compress compressed images

## Pre-launch checklist

- All redirects tested, including a sample by hand
- Staging site blocked from indexing, and verified as blocked
- Analytics and tag manager configured and tested with a real transaction
- Search Console prepared for the new property
- Structured data validated on each template
- Speed tested on a real mid-range phone
- Checkout tested end to end with real payment
- 404 handling in place with a useful page

## Launch

Prefer a low-traffic window. Then, in order:

1. Deploy
2. Verify redirects immediately, with a crawl
3. Remove the indexing block from the new site
4. Submit the new sitemap
5. Verify analytics is recording
6. Watch error logs for the first hours

## After launch

**First 48 hours:** monitor 404s and fix mappings you missed. Watch conversion rate for checkout problems, which cost money immediately.

**First two weeks:** crawl errors in Search Console, indexation of key pages, ranking movement.

**First three months:** expect a temporary dip. A well-executed migration usually recovers within four to eight weeks. Communicate this expectation in advance so a normal dip does not trigger a panic.

## Realistic expectations

Even a perfect migration usually shows a temporary decline while the index updates. What separates a good migration from a bad one is not whether there is a dip, but whether it recovers and how quickly.

Signs of a bad migration: traffic does not recover after eight weeks, large numbers of 404s from previously ranking URLs, or key pages absent from the index.
MD],

],
'closing' => <<<'MD'
Platform choice is a requirements exercise, not a preference. The right answer is the platform that satisfies your must-haves at a total cost you can sustain, with capability you actually have.

## The decision process

1. **Write requirements before talking to vendors,** classified as must, should and nice.
2. **Model three years of total cost,** including apps, maintenance and labour.
3. **Check the SEO capabilities** in chapter four against your needs.
4. **Ask honestly whether migration is necessary,** or whether the current implementation is the real problem.

## Where we can help

We advise on platform selection and run migrations with URL mapping, redirect strategy and performance monitoring, so search visibility survives the move.

Our free audit tool gives you a technical baseline of your current store in about ten seconds and emails you the report.
MD,
];
