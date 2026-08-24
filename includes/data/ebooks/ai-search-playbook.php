<?php
/** Chapter content for the AI Search Playbook e-book. */
return [
'subtitle' => 'How to earn citations inside ChatGPT, Perplexity, Gemini and AI Overviews',
'description' => 'A practical system for winning visibility in AI answer engines: how retrieval works, the page structure that earns citations, entity foundations, and a monthly measurement method.',
'subjects' => ['Answer Engine Optimization', 'SEO', 'Digital Marketing', 'AI Search'],
'chapters' => [

['The shift from links to answers', <<<'MD'
For twenty years the job was straightforward. Rank a page, earn a click, convert the visitor. The ranking was the product.

That contract has broken. A large share of commercial research now happens inside an answer, not a results page. The buyer asks a question, reads a synthesised response, and forms a shortlist before visiting a single website. Your brand either appears inside that synthesis or it does not exist for that buyer.

## What actually changed

Three things happened at once, and it is the combination that matters.

**Answers moved above results.** Google AI Overviews now appear on a large and growing share of informational and commercial-research queries. When an Overview appears, the traditional first organic result drops below the fold on most phone screens. The position you fought for is still there. Fewer people scroll to it.

**Assistants became a research surface.** ChatGPT, Perplexity, Gemini and Copilot are used for vendor comparison, pricing research, shortlisting and due diligence. These are commercial queries. They were previously the most valuable clicks in search.

**Citations became the new ranking.** Answer engines name their sources. Being named is the visibility. A citation carries implied endorsement that a blue link never had, because the engine chose you as the source worth quoting.

!STAT 1 in 3 | of the queries we track for clients now return an AI answer before any organic result

## The uncomfortable maths

Most marketing teams are measuring a number that is quietly becoming less representative. Organic sessions can fall while brand demand rises, because the research happened inside an answer and the visit only occurs later, often as a direct or branded search.

If you judge search purely on session counts, you will conclude that search is declining and cut the budget that feeds the answers. That is the trap. The correct measure is share of answer: how often your brand is named when a buyer asks a question you should own.

## What does not change

This is where most coverage of AI search gets it wrong. Answer engines are not a separate channel with separate rules. They are readers with specific preferences, and they read the same web everyone else reads.

- They need to reach your page. Crawl access and rendering still decide everything.
- They prefer content that answers directly. Structure beats length.
- They corroborate. A claim repeated across independent sources becomes a fact worth citing.
- They favour clarity about who you are. Entity signals decide whether you are a known company or an anonymous URL.

Every one of those is a discipline that good SEO already contains. The work is not to replace your search programme. It is to sharpen the parts that answer engines weight most heavily, and to stop doing the parts that only ever existed to game a rankings algorithm.

## How to read the rest of this book

Chapter two explains how engines actually select sources, because the tactics only make sense once the retrieval model is clear. Chapter three is the content system, with page structures you can copy. Chapter four covers the entity and schema layer that decides whether you are legible as a business. Chapter five is measurement, which is where most teams give up, because they assume it cannot be measured. It can.

> If you take one idea from this book, take this one: answer engines reward the site that is easiest to quote accurately. Everything that follows is a way of being easy to quote.
MD],

['How engines choose sources', <<<'MD'
You cannot optimise for a black box. Fortunately this one is not black. The retrieval pipeline behind most answer engines is well documented in principle, and the practical implications are consistent across ChatGPT, Perplexity, Gemini and AI Overviews.

## The three-layer model

Think of source selection as three filters. A page must survive all three. Most pages fail at the first and their owners spend their budget on the third.

### Layer one: retrieval

The engine needs candidate documents. It gets them from a search index, a live crawl, or its own stored corpus. This layer is mechanical and unforgiving.

- If your robots.txt blocks the crawler, you are excluded. No further consideration.
- If your content requires JavaScript to render, expect partial or empty extraction.
- If your page is slow to respond, live-retrieval systems time out and move on.
- If your page is not in the index for the underlying search engine, it is not a candidate.

**This layer is binary and it is where most of the loss happens.** Check it first, always.

### Layer two: passage relevance

The engine does not evaluate your page. It evaluates passages within your page. A retrieval system chunks documents and scores chunks against the query.

The practical consequence is significant. A 3,000 word guide that buries the direct answer in paragraph fourteen will lose to a 700 word page that answers in the opening lines, even if the long guide is more thorough overall. The long guide may be a better document. The short one is a better chunk.

This is why answer-first structure is not a stylistic preference. It is how the retrieval unit works.

### Layer three: corroboration and trust

Among relevant passages, the engine prefers claims it can verify against other sources, from publishers it has reason to trust.

Trust signals that demonstrably matter:

- Consistent, verifiable business identity across the web
- Named authors with real credentials and history
- Specific, checkable claims rather than vague superlatives
- Agreement with other independent sources on factual points
- Original data, which cannot be corroborated but can become the thing others corroborate against

## Why your competitor gets cited and you do not

Run this diagnostic before changing anything.

| Check | How to test | Common failure |
| --- | --- | --- |
| Crawl access | Fetch robots.txt, look for GPTBot, ClaudeBot, PerplexityBot, Google-Extended | Blocked by default in a security template |
| Render dependency | View source with JavaScript disabled | Content only in the JS bundle |
| Answer position | Read your first 60 words | Opens with brand history, not the answer |
| Claim specificity | Count numbers in the page | Adjectives instead of figures |
| Entity clarity | Search your brand name plus "company" | No knowledge panel, inconsistent details |

In our audits the single most common blocker is not content quality. It is that a security plugin or a well-meaning developer disallowed the AI crawlers, often years ago, and nobody revisited it.

!NOTE Blocking AI crawlers does not protect your content from being trained on. Most training corpora predate the block and use different agents. It only removes you from the answers where you could have been cited.

## The corroboration flywheel

Once you understand layer three, a strategy emerges that is more durable than any formatting trick.

1. Publish something specific and true that nobody else has published: your own data, your own benchmark, your own tested result.
2. Make it trivially quotable: a clear number, a named method, a defined term.
3. Get it referenced. Not link building for authority. Reference building for corroboration.
4. The engine now sees a claim that multiple sources attribute to you.

At that point you are not competing for a citation slot. You are the source of the fact.
MD],

['The AEO content system', <<<'MD'
This chapter is the operational core. It gives you a page structure, a question architecture, and a set of formatting rules you can apply this week.

## The answer-first page structure

Every page that should earn citations follows the same skeleton. The order is deliberate and it is not the order most brands write in.

### 1. The direct answer, in the first 60 words

Open by answering the question in the title. Plainly, completely, with no wind-up. If someone read only this paragraph, they should have the answer.

A useful test: could this paragraph be lifted out and quoted with no other context? If it starts with "In today's competitive landscape" then no, and you have wasted the most valuable position on the page.

**Before:** "Choosing an SEO agency is one of the most important decisions a growing business can make. With so many options available, it can be difficult to know where to start."

**After:** "A competent SEO retainer for a mid-market Indian business runs between fifty thousand and two lakh rupees a month, scaling with market competitiveness rather than site size. Below thirty thousand you are usually buying a junior executive part time, not a strategy."

The second version can be quoted. The first cannot.

### 2. The supporting context, 150 to 300 words

Now the nuance. Conditions, exceptions, the reasoning behind the answer. This is what stops the direct answer from being glib, and it is where you demonstrate that you actually know the subject.

### 3. Structured detail

Tables, numbered processes, comparison rows. Answer engines extract structured content with high fidelity, and structured content is easier to attribute accurately.

### 4. Related questions, answered individually

Each with its own subheading phrased as the question a real person types. Each answered immediately underneath. This is the highest-leverage section on most pages, because it multiplies the number of queries a single page can serve.

### 5. Provenance

Who wrote this, what they know, when it was last verified. Date and author are trust signals that cost nothing to add and are missing on most commercial pages.

## Question architecture

Stop building pages around keywords. Build them around question clusters.

A keyword approach produces one page for "ecommerce SEO" that tries to serve every intent at once. A question approach produces a hub that owns the topic and satisfies distinct queries precisely.

For any topic, map questions across four intents:

| Intent | Question shape | What it needs |
| --- | --- | --- |
| Definitional | What is X | A clean, quotable definition |
| Procedural | How do I X | Numbered steps with specifics |
| Comparative | X vs Y, best X | An honest table, including where you lose |
| Evaluative | Is X worth it, X cost | Real numbers and conditions |

Comparative content is where most brands lose their nerve. A comparison that concludes you are best at everything is worthless to an engine and transparent to a buyer. A comparison that names the cases where a competitor is the better choice is the one that gets cited, because it reads as evaluation rather than marketing.

## Formatting rules that measurably help

- One idea per paragraph. Long paragraphs chunk badly.
- Subheadings phrased as questions where natural.
- Numbers over adjectives. "Improved performance" is unquotable. "Cut LCP from 4.1s to 1.6s" is a citation.
- Define your terms on first use, even the obvious ones.
- Put the conclusion before the reasoning, not after.
- Keep a single clear topic per URL.

!NOTE A quick audit: open your five most important commercial pages and read only the first sentence of each. If none of them answers a question, you have found your highest-return fix.

## What to stop doing

- Keyword density work. It is noise to a semantic retrieval system.
- Padding to hit a word count. Padding dilutes chunk relevance.
- Thin location pages that differ only by city name. They corroborate nothing.
- Publishing without an author. Anonymous content carries a trust penalty.
- Answers that hedge everything. Engines cite specifics, and hedged content contains none.
MD],

['Entities, schema and trust', <<<'MD'
Content decides whether you are quotable. Entity work decides whether the engine knows who is being quoted. Most brands invest heavily in the first and ignore the second, then wonder why a smaller competitor gets named instead.

## What an entity actually is

An entity is a thing the machine has a record for: a company, a person, a product, a concept. It has attributes and relationships. When an answer engine says "according to VTurnU, a digital marketing agency in Chennai," it is drawing on an entity record, not on one page.

If no such record exists, you are a URL. URLs get read. Entities get cited.

## The four foundations

### 1. Consistent identity across the web

The same business name, the same contact details, the same description, everywhere you appear. Directories, social profiles, review sites, your own footer. Inconsistency is the fastest way to prevent an entity from consolidating, because the system cannot tell whether it is looking at one company or three.

Audit this by searching your brand name and listing every property that describes you. Fix the ones that disagree.

### 2. Organization schema that is actually complete

Most sites ship a stub. A useful Organization block declares identity, contact, reach and expertise:

- Legal name and any trading names
- Logo as a proper ImageObject with dimensions
- Contact points with type and language
- Areas served
- Fields of expertise
- Links to every profile you control

That last one matters more than its effort suggests. It is the explicit statement that these scattered profiles are all the same entity.

### 3. Page-level structured data

Match the type to the content and keep it truthful.

| Page type | Schema | Common mistake |
| --- | --- | --- |
| Article or guide | Article, with author and dates | No author, no dateModified |
| Question content | FAQPage | Marking up questions not visible on the page |
| Service page | Service, with provider linked to the Organization | Orphaned, not linked to the entity |
| Case study | Article plus verifiable result claims | Results with no method or timeframe |
| Product | Product with real price and availability | Fabricated review markup |

Fabricated markup is worth naming explicitly. Review schema on a page with no reviews is a manual-action risk and, more immediately, it teaches the engine that your structured data cannot be trusted.

### 4. Author identity

Named authors with a real page: credentials, history, links to their work elsewhere. Answer engines weight authored content over anonymous content, and for anything touching money, health or law the gap is substantial.

## The corroboration layer

Schema is a claim you make about yourself. Corroboration is other people making the same claim. Both are needed.

Practical sources of corroboration, roughly in order of effort:

1. Complete, consistent profiles on the major business directories
2. Industry association and partner listings
3. Review platforms where your category is actually reviewed
4. Speaker, contributor and guest bylines under a named person
5. Original research that others cite by name

## A note on llms.txt

An emerging convention: a plain-text file at your root that summarises what your site is and which pages matter, written for machine consumption. Support is not universal and it is not a ranking factor.

It is also close to free to publish, it costs nothing if ignored, and it is a clean statement of your structure for the systems that do read it. Publish one. Keep it generated from live content so it cannot drift out of date. Do not expect it to do the work that content and entity signals do.
MD],

['Measurement and reporting', <<<'MD'
Most teams do not measure AI visibility because they believe it is unmeasurable. It is not precisely measurable the way rank tracking is. It is measurable well enough to steer a budget, which is the actual requirement.

## What you cannot measure

Be honest about the limits before promising anything internally.

- There is no impression count for answer engines
- Answers vary by user, session, phrasing and time
- No engine publishes a citation API for arbitrary brands
- Attribution from an answer to a later conversion is usually indirect

Any vendor promising exact AI impression share is selling a model, not a measurement. Say so before someone else does.

## What you can measure

### 1. Citation share by prompt set

The core method, and it works.

1. Build a fixed set of 40 to 60 prompts a real buyer would ask. Mix definitional, comparative and evaluative intents.
2. Run them monthly across the engines that matter to you, in a clean session with no personalisation.
3. Record for each: were you cited, which URL, in what position, and which competitors appeared.
4. Track the percentage of prompts where you appear. That is your citation share.

Keep the prompt set frozen. Changing it every month destroys comparability, which is the entire point.

!STAT 40 to 60 | prompts is enough for a stable monthly signal. Beyond that you are adding cost, not accuracy.

### 2. Referral traffic from assistants

Assistant referrals appear in analytics with identifiable referrers. Segment them. The volume will look small and the quality will usually be high, because the visitor arrived after reading an evaluation rather than clicking a link speculatively.

Judge this traffic on conversion rate and lead quality, not volume. Comparing it to organic session counts will always look bad and will always be the wrong comparison.

### 3. Crawler access logs

Your server logs show which AI agents fetch which pages, how often, and what they receive. This is the most underused data in the entire discipline.

Answers this data gives you directly:

- Are the crawlers reaching your priority pages at all
- Are they receiving 200 responses or errors
- Which content attracts repeat retrieval
- Did access change after a robots.txt or firewall edit

### 4. Branded demand

The lagging indicator that matters commercially. If AI visibility is working, branded search volume and direct traffic rise, because buyers encountered you inside answers and later looked you up.

## A monthly reporting format

Keep it to one page. Five numbers, one action.

| Metric | Source | Reading |
| --- | --- | --- |
| Citation share | Prompt set run | The headline number |
| Prompts won or lost | Prompt set diff | Where movement happened |
| Assistant referrals | Analytics segment | Volume and conversion rate |
| AI crawler hits | Server logs | Access health |
| Branded search | Search Console | Demand trend |

Then one sentence: what we are changing next month, and why.

## Realistic timelines

Set these expectations before you start, not after the first disappointing month.

- Weeks 1 to 4: access and technical fixes. Citation share barely moves.
- Weeks 4 to 12: restructured content indexed. First movement on definitional prompts.
- Months 3 to 6: comparative and evaluative prompts begin to shift. This is the commercially valuable set and it moves last.
- Months 6 and beyond: entity and corroboration work compounds.

The order matters. Definitional prompts move first because they are the easiest to answer well. Do not read early wins there as proof the commercial prompts will follow automatically. They require the corroboration work in chapter four.
MD],

],
'closing' => <<<'MD'
You now have the model, the content system, the entity foundations and a measurement method. The gap between reading this and seeing results is execution order, and that is where most programmes stall.

## The first thirty days

1. **Check crawler access.** Read your robots.txt for GPTBot, OAI-SearchBot, ClaudeBot, PerplexityBot and Google-Extended. Also check your CDN or firewall, which may block them independently of robots.txt. This is the single highest-return hour in the whole programme.
2. **Fix your top five pages.** Rewrite the opening 60 words of each to answer directly.
3. **Complete your Organization schema** and link every profile you control.
4. **Build and run your prompt set once** to establish a baseline before you change anything else.

## Where we can help

We run this system for clients as a managed programme: baseline audit, access remediation, content restructuring, entity work and monthly citation-share reporting.

If you would like to see where you stand before deciding anything, our free audit tool checks crawler access, answer-readiness and schema foundations on your homepage in about ten seconds, and emails you the full report.
MD,
];
