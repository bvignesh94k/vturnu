<?php
/** Chapter content for the Ecommerce SEO e-book. */
return [
'subtitle' => 'The architecture, crawl control and content system behind organic store revenue',
'description' => 'A complete ecommerce SEO system: category-first architecture, faceted navigation control, product data, category page strategy and the content layer that feeds checkout.',
'subjects' => ['Ecommerce', 'SEO', 'Digital Marketing', 'Online Retail'],
'chapters' => [

['Store architecture that matches how people buy', <<<'MD'
Most store SEO problems are architecture problems wearing a content costume. Before you write a single product description, get the structure right, because everything downstream inherits it.

## The core principle

Your category tree should mirror how buyers narrow their choice, not how your warehouse organises stock, and not how your ERP exports data.

Buyers narrow by attribute. They start broad, then apply the one or two attributes that matter most to them, then compare. Your job is to have a crawlable, indexable, linkable page waiting at each meaningful stop.

## Getting the hierarchy right

Three levels is usually correct. Four is sometimes justified. Five means you have modelled your internal taxonomy instead of buyer behaviour.

`Category > Subcategory > Attribute page > Product`

For a jewellery store that looks like:

`Rings > Engagement rings > Solitaire engagement rings > [product]`

Each level should earn its existence by having genuine search demand behind it. Run the check honestly: if nobody searches for the level, it should be a filter, not a page.

## URLs

Keep them short, stable and readable. The rules are unglamorous and they matter for years.

- Lowercase, hyphenated, no parameters in the canonical path
- No dates, no IDs where a word will do
- Category path optional in product URLs, but pick one pattern and never change it
- Never encode a filter into a canonical URL

!NOTE Changing URL structure on an established store is one of the most expensive projects in ecommerce SEO. Decide the pattern early and defend it. If you must migrate, map every old URL to a specific new one, never bulk-redirect to the homepage.

## Internal linking

Internal links are how authority and crawl priority flow. Most stores leak both.

Practices that consistently work:

- Link from category pages to their highest-margin subcategories in body copy, not just navigation
- Cross-link related products with genuine relevance, not random "you may also like"
- Link from published guides directly to the category that satisfies the intent
- Keep breadcrumbs on every page, marked up with BreadcrumbList schema

The one to avoid: linking to every category from the footer of every page. It flattens the hierarchy and tells the crawler that nothing is more important than anything else.

## The out-of-stock question

Every store faces this and most get it wrong in one of two directions.

| Situation | Correct handling | Why |
| --- | --- | --- |
| Temporarily out of stock | Keep the page live, show status, offer alerts | The URL has earned equity; deleting it discards that |
| Permanently discontinued, replacement exists | 301 to the replacement | Passes equity, serves the visitor |
| Permanently discontinued, no replacement | 301 to parent category | Better than a 404 for a page with links |
| Never existed, bad URL | 410 | Tells crawlers to stop asking |

Deleting product pages the moment stock hits zero is the most common self-inflicted wound in store SEO. Seasonal products that return every year should keep their URLs permanently.
MD],

['Crawl control: the discipline that protects everything', <<<'MD'
A store with 400 products can generate hundreds of thousands of crawlable URLs through filters alone. Left unmanaged, crawl budget goes to combinations nobody searches for, while your actual category pages get visited rarely.

## Understanding facet explosion

Every filter multiplies. Size, colour, price band, brand, material, and suddenly a single category produces more URL permutations than you have products. Each is technically a unique URL. Almost none deserve indexing.

The maths is worth internalising: five filters with four options each, combinable in any order, produces well over a thousand URLs from one category page.

## The decision framework

For each facet, ask one question: does anybody search for this combination?

| Facet type | Example | Treatment |
| --- | --- | --- |
| High search demand | "black leather sofa" | Indexable landing page with unique copy |
| Occasional demand | "sofa under 50000" | Indexable, thin copy acceptable |
| No demand, useful to users | "sort by newest" | Crawlable, noindex |
| No demand, combinatorial | Three or more filters stacked | Blocked from crawling entirely |

The mistake most stores make is treating this as binary. It is three-way: index, noindex but allow crawl, or block crawl. Confusing the last two is why crawl budget still evaporates on sites that "added noindex".

**Noindex still costs crawl budget.** The crawler must fetch the page to see the tag. If you never want it crawled, handle it in robots.txt or avoid generating the link at all.

## Practical implementation

1. **Decide your indexable facet set.** Usually one filter deep, occasionally two for genuinely searched combinations.
2. **Canonical stacked facets** back to the nearest indexable parent.
3. **Block infinite spaces** in robots.txt: sort orders, session parameters, comparison tools.
4. **Do not link to non-indexable combinations** with crawlable anchors where avoidable.
5. **Verify in Search Console** using the crawl stats report, then again a month later.

## Pagination

Component pagination is no longer consolidated by rel next and prev, which was retired years ago. Current sound practice:

- Each paginated page self-canonicals, it does not canonical to page one
- Every page is crawlable and linked
- Consider a view-all page where product counts allow
- Never noindex page two onward if products only exist there, or those products become undiscoverable

## Duplicate content, the store version

Three sources cause most of it:

- **Manufacturer descriptions** copied verbatim across every retailer. Rewrite the products that matter and accept the tail.
- **Near-identical variants** as separate URLs. Consolidate to one product page with variant selection.
- **Same product in multiple categories** with path-based URLs. Pick one canonical path.

!STAT 60 to 80% | of crawl budget on an unmanaged store typically goes to URLs that will never rank. Fixing this alone often lifts indexation of real pages within weeks.

## Verifying your work

Do not trust the theory. Check the reality.

- Server logs: which URL patterns is Googlebot actually fetching
- Search Console crawl stats: total requests versus useful requests
- A crawl of your own site with a tool set to follow all links, then compare URL count to product count
- Index coverage: are your priority categories all indexed
MD],

['Product and category pages that rank and convert', <<<'MD'
Architecture gets you crawled. These two page types get you revenue.

## Category pages are landing pages

This is the reframe that changes results. Most buying-intent searches land on category pages, not product pages. "Buy running shoes online" is a category query. Yet most stores treat category pages as bare product grids with a title.

A category page that ranks contains:

### 1. A real H1 and a useful opening

Not "Running Shoes" followed immediately by a grid. Two or three sentences that establish what the range covers and help someone choose. Placed above the grid, visible without clicking "read more".

### 2. Merchandised ordering

Default sort should be your best sellers or best margin, not newest or alphabetical. This is a conversion decision that also affects SEO, because engagement signals follow it.

### 3. Buying guidance

The questions a salesperson would answer: how to choose a size, what the price tiers mean, what differentiates the options. This is the content that earns the ranking and it is almost always missing.

### 4. FAQ block

Three to six real questions with direct answers, marked up with FAQPage schema. This is the section most likely to be quoted by an answer engine.

### 5. Internal links

To subcategories, to relevant guides, to the comparison content that supports the decision.

## Product pages

The job is different: convince and convert, while being machine-legible.

**Description.** Write for the buyer's actual uncertainty. What is it, who is it for, what does it not do. Original copy on your top revenue products, at minimum. Manufacturer boilerplate on the tail is acceptable, pretending otherwise wastes budget.

**Specifications.** Structured, complete, consistent across the range. This is what comparison shoppers scan and what structured data consumes.

**Images.** Multiple angles, real scale reference, descriptive alt text, correct dimensions declared to prevent layout shift.

**Reviews.** Genuine reviews, displayed on-page, marked up honestly. Review schema on a page with no reviews is a manual action waiting to happen.

**Availability and price.** In Product schema, accurate and current. Stale price markup produces rich results that misprice you and erode trust when the buyer arrives.

## Schema that earns rich results

| Type | Where | Payoff |
| --- | --- | --- |
| Product | Product pages | Price, availability, ratings in results |
| BreadcrumbList | Everywhere | Path display instead of raw URL |
| FAQPage | Category and product | Expanded results, answer-engine citations |
| Organization | Site-wide | Entity recognition |
| ItemList | Category pages | Better category understanding |

Keep markup synchronised with what the page displays. Divergence between markup and visible content is both a policy violation and a trust problem.

## The conversion layer

SEO that ignores conversion is expensive traffic. The elements that reliably move store conversion rate:

- Total cost visible before checkout, including shipping, as early as possible
- Delivery date, not delivery duration
- Return policy stated plainly near the buy button
- Trust signals near the point of payment, not in the footer
- Guest checkout available without argument
MD],

['The content layer that feeds checkout', <<<'MD'
Product and category pages capture demand that already exists. Content captures demand earlier, and it is the difference between a store that plateaus and one that compounds.

## Why stores need content at all

A buyer researching "how to choose a mattress firmness" is three days from purchase. They are not searching for your product yet. If you are not present at that moment, you meet them later, in a comparison against three competitors, on price.

Content earlier in the journey is how you enter the consideration set before price is the only remaining variable.

## The four formats that produce revenue

Ranked by commercial return, from our client work:

### 1. Buying guides

"How to choose X." Directly precedes purchase, ranks for high-volume terms, and links naturally to categories. This is the highest-return format in ecommerce content and the most commonly skipped.

Structure: the decision criteria, the trade-offs, a recommendation by use case, then links to the relevant category pages.

### 2. Comparison content

"X vs Y." Captures buyers at the shortlist stage. Requires the discipline to be honest about where the alternative wins, which is exactly why it converts: readers can tell the difference between analysis and a sales pitch.

### 3. Problem-led content

"Why does X keep happening." Captures the moment before the buyer knows a product is the answer. Wider top of funnel, longer payback, but it builds topical authority that lifts everything else.

### 4. Use-case content

"Best X for Y." Highly specific, lower volume, very high intent. Often the easiest wins on a new site because competition is thin.

## Connecting content to commerce

Content that does not route to a category page is a blog post, not an asset. Every piece needs a deliberate path to purchase.

- Contextual links to the relevant category, in the body, at the moment the reader would want it
- A product block partway through, not only at the end
- A clear next step at the close, matched to where the reader is in the decision
- Internal links from the category page back to the guide, so the relationship runs both ways

## Measuring content properly

Sessions is the wrong headline metric. Use assisted revenue.

| Metric | Where | What it tells you |
| --- | --- | --- |
| Assisted conversions | Analytics attribution report | Whether content contributes to sales |
| Category page entries from content | Internal link tracking | Whether routing works |
| Ranking for buying-guide terms | Rank tracking | Whether you own the research stage |
| Answer engine citations | Manual prompt checks | Whether you are in AI shortlists |

## A realistic publishing cadence

Two well-researched pieces a month, each genuinely better than what currently ranks, beats eight thin pieces. The tail of thin content does not compound. It dilutes.

Start with the five buying guides that map to your five highest-margin categories. Do those properly before publishing anything else.
MD],

],
'closing' => <<<'MD'
Store SEO rewards sequence. Doing these in the wrong order wastes months, so here is the order that works.

## The first ninety days

1. **Crawl control first.** Audit facet handling and fix the indexation rules. Everything else is undermined until crawl budget reaches your real pages.
2. **Category pages second.** Add opening copy, buying guidance and FAQs to your top ten revenue categories. This is usually the fastest revenue movement available.
3. **Product data third.** Correct schema, accurate availability, original copy on the top sellers.
4. **Content fourth.** Five buying guides mapped to your highest-margin categories.

## Where we can help

We run ecommerce SEO programmes across Shopify, WooCommerce, Magento and custom stacks, starting with a technical audit that identifies exactly where crawl budget and revenue are leaking.

If you want a fast read on your current state, our free audit tool checks indexability, structured data and answer-engine readiness in about ten seconds and emails you the report.
MD,
];
