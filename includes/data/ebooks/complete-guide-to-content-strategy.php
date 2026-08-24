<?php
/** Chapter content for the Complete Guide to Content Strategy. */
return [
'subtitle' => 'From topic clusters to production systems to measurable pipeline',
'description' => 'A content strategy guide built around outcomes: audience and goal foundations, topic cluster architecture, briefs and production, optimisation for search and AI, distribution and measurement.',
'subjects' => ['Content Strategy', 'Content Marketing', 'SEO', 'Editorial Operations'],
'chapters' => [

['Strategy foundations', <<<'MD'
Most content strategies are publishing calendars with a strategy label. The difference is whether the plan starts from a business outcome or from a list of topics.

## The three questions that come first

**1. What business outcome is this for?**

Pick one primary outcome. Content that serves everything serves nothing well.

- Pipeline from search
- Sales enablement, shortening cycles
- Category authority for a new market
- Retention and expansion

Each produces a different plan. Pipeline content is bottom-funnel and commercial. Authority content is broad and slow. Trying to do both with the same eight pieces produces neither.

**2. Who specifically is this for?**

Not "decision makers in mid-market companies." A person with a job, a problem, a vocabulary and a set of alternatives.

The useful test: can you name three real customers who match, and quote something they actually said?

**3. What can you say that others cannot?**

If your content could appear on a competitor's site unchanged, it will not earn attention or citations. Your unfair advantage is usually one of:

- Proprietary data from your client base
- Practitioner experience nobody else has documented
- A method you can name and teach
- Access to experts inside your company

## The honest inventory

Before planning new content, audit what exists. Most companies have more than they think and worse than they hope.

For every existing piece, record: traffic, rankings, conversions, last updated, and a quality judgement.

Then sort into four actions:

| State | Action |
| --- | --- |
| Ranks and converts | Leave alone, update annually |
| Ranks, does not convert | Add conversion path and relevant CTA |
| Does not rank, good content | Optimise and rebuild internal links |
| Does not rank, poor content | Consolidate into a better piece, or remove |

**Removal is a real option.** A large tail of thin content dilutes topical authority. Consolidating twelve weak posts into two strong ones frequently outperforms publishing two new ones.

!STAT Update before publish | In most accounts we manage, updating existing content returns more per hour than new publishing. It is also the least glamorous work and therefore the most neglected.
MD],

['Topic clusters', <<<'MD'
Publishing individual pieces around scattered keywords produces scattered results. Clusters produce compounding ones.

## The model

A cluster has three parts:

**Pillar page.** Comprehensive coverage of a broad topic. Targets the head term. Links to every cluster page.

**Cluster pages.** Specific subtopics, each answering one question thoroughly. Each links back to the pillar.

**Internal links between clusters** where genuinely relevant.

The mechanism: internal linking concentrates relevance and authority around the topic, and the search engine sees a body of connected coverage rather than isolated pages.

## Building one

**1. Choose a topic you can credibly own.** Not "digital marketing." Something narrow enough that you can be the most thorough source: "technical SEO for Shopify stores."

**2. Map the questions.** Every question a buyer asks about this topic. Sources:

- Search suggestions and related searches
- Your sales team's inbox
- Support tickets
- Community forums where your buyers discuss the problem
- Competitor coverage gaps
- Assistant prompts, by asking an AI engine what people ask about the topic

Aim for 20 to 40 questions per cluster.

**3. Group by intent.** Definitional, procedural, comparative, evaluative. Each group becomes a page or a section.

**4. Decide the shape.** One page per question is too granular; one page for everything is too broad. Group questions that share an intent and would be read together.

**5. Sequence by commercial value.** Publish the bottom-funnel pages first. They produce pipeline soonest and fund patience for the rest.

## Cannibalisation

Two pages targeting the same intent compete with each other and split signals.

Detect it: search your site for a query and see whether multiple pages rank and rotate. Or check Search Console for queries where several URLs receive impressions.

Fix it by consolidating into the stronger URL, redirecting the weaker, and merging the useful content.

## Internal linking discipline

The most underused lever in content strategy.

- Every new piece links to at least three existing relevant pieces
- Every new piece receives links from at least two existing pieces
- Anchor text is descriptive, not "click here"
- The pillar links to every cluster page and vice versa
- Commercial pages receive links from the informational content that precedes them

An orphaned piece with no internal links takes far longer to rank and may never do so.
MD],

['Briefs and production', <<<'MD'
Quality is a production problem more often than a talent problem. A good writer with a bad brief produces mediocre work.

## What a usable brief contains

| Element | Why |
| --- | --- |
| Target question | The exact query, not a topic |
| Search intent | What the reader wants to do |
| Angle | What makes this different from what ranks now |
| Must-cover points | Non-negotiable substance |
| Expert source | Who to interview, or which transcript |
| Internal links | Specific URLs to link, both directions |
| Conversion path | What action, and where |
| Format | Structure, approximate length, elements needed |
| What to avoid | Claims we cannot make, competitors not to name |

The angle field matters most. Without it, writers default to summarising what already ranks, which produces a page with no reason to exist.

## The competitive read

Before writing, read the top five results properly. For each note: what it covers, what it misses, how it is structured, and where it is weak.

Then answer one question: **what will this piece do that none of them does?** If there is no answer, do not commission it.

## Getting expertise onto the page

The highest quality-to-effort method is the expert interview.

1. Identify someone who actually does the work
2. Prepare questions only a practitioner can answer: what people get wrong, what you check first, the failure nobody expects
3. Record 30 minutes
4. Transcribe and mine; one interview usually yields two or three pieces
5. Write, then return for factual correction, not rewriting

Keeping "expert corrects facts" separate from "writer writes prose" is what makes this sustainable.

## Generic versus expert content

| Generic | Expert |
| --- | --- |
| Lists the standard steps | Says which step people skip and why it matters |
| "It depends on your needs" | "Under 50 users, do X, because Y" |
| Describes best practice | Describes what happens in practice |
| No numbers | Specific numbers with conditions |
| No recommendation | A clear recommendation with reasoning |

**Generic content describes. Expert content decides.**

## Editorial standards

Write them down once and apply them:

- Specific numbers instead of adjectives wherever a number exists
- Define terms on first use
- Conclusion before reasoning
- One idea per paragraph
- Named author with credentials
- Every factual claim checkable
- No sentence that could appear unchanged on a competitor's site

## Cadence

Two substantial pieces monthly, each better than the current best result, beats eight thin ones. Thin content does not compound; it dilutes.

A workable rhythm for a small team: one interview, two pieces from it, two existing pieces updated, distribution for all four.
MD],

['Optimisation for search and AI', <<<'MD'
The same structural work serves both, which is convenient, because the alternative would be two content programmes.

## The answer-first structure

1. **Direct answer in the first 60 words.** Quotable with no other context.
2. **Supporting context.** Conditions, exceptions, reasoning.
3. **Structured detail.** Tables, steps, comparisons.
4. **Related questions,** each with a subheading and immediate answer.
5. **Provenance.** Author, credentials, last verified.

## On-page essentials

- **Title:** the question or the outcome, front-loaded, under about 60 characters
- **Meta description:** a reason to click, not a summary
- **H1:** one, matching the page intent
- **Subheadings:** phrased as questions where natural
- **URL:** short, readable, stable
- **Images:** descriptive alt text, dimensions declared
- **Schema:** Article with author and dates; FAQPage where questions are visible

## Writing for extraction

Answer engines retrieve passages, not documents. That changes structure priorities:

- Front-load answers within every section, not just the page
- Keep paragraphs to one idea so chunks are coherent
- Use tables for anything comparative, since they extract cleanly
- Define your terms, because a definition is the most quotable unit of content
- Include numbers, because specifics get cited and adjectives do not

## Comparative content

The format that most reliably earns citations, and the one most companies avoid.

An honest comparison names where the alternative is better. That is what makes it credible, and credibility is what makes it quotable. A comparison in which you win every dimension is discounted by every experienced reader and every engine.

## Refresh cycles

Content decays. Rankings slide, data ages, screenshots go stale.

Quarterly:

1. Pull everything ranking positions 4 to 15, the closest to meaningful gains
2. Compare against what outranks it. What do they cover that you do not?
3. Update substantively: new sections, refreshed data, better structure
4. Re-link internally from newer pieces
5. Track for six weeks

A date change with no substantive edit is not a refresh and produces nothing.
MD],

['Distribution and measurement', <<<'MD'
## Distribution is part of production

Plan it before writing, not after publishing.

**Search.** The compounding channel. Requires a real target query, direct answers, and internal links from existing pages. Orphaned content takes far longer to rank.

**Email.** Send the single most useful idea, then link for the rest. Not "new blog post."

**LinkedIn.** For B2B the highest-return social channel. Post substance natively; personal profiles outperform company pages; the first two lines decide whether anyone expands.

**Sales enablement.** The most underused channel. Sales answers the same questions daily; the content answering them should be in their hands and used during deals.

**Communities.** Participate genuinely for months before linking anything.

**Answer engines.** Increasingly the surface that matters, served by the structure work in the previous chapter.

## Repurposing without dilution

One substantial piece supports a LinkedIn series, an email sequence, a sales one-pager, a short video, and a section of a larger guide.

The constraint: each format must stand alone. Reposting identical text everywhere trains the audience to ignore you.

## Measurement

Measure outcomes, not activity.

| Metric | Source | Why |
| --- | --- | --- |
| Influenced pipeline | CRM | Deals with a content touchpoint |
| Self-reported attribution | Form field | Captures what tracking misses |
| Assisted conversions | Analytics | Contribution to conversion |
| Rankings for commercial terms | Rank tracking | Leading indicator |
| Citation share | Prompt set | AI visibility |
| Sales usage | Ask the team | Whether it is actually used |

Deliberately excluded: pageviews, time on page, bounce rate, social followers. They are not decision-relevant and reporting them invites the wrong conversation.

## The attribution problem, stated honestly

Last-click attribution systematically undercredits content. The reader found you through a guide, researched for weeks, then searched your brand and converted. Last-click credits branded search.

Two practical corrections:

1. **Add "how did you hear about us"** to every form. Unscientific and consistently informative.
2. **Report influenced pipeline,** not last-click conversions.

## Realistic timelines

- **Months 1 to 3:** published and indexed. First rankings on low-competition terms.
- **Months 3 to 6:** commercial terms ranking. Attributable pipeline appears.
- **Months 6 to 12:** clusters compound. Organic becomes predictable.
- **Beyond 12:** topical authority lifts new content faster.

Agree these in writing before starting. A programme judged at month two on traffic gets cancelled before the mechanism has had time to work.
MD],

],
'closing' => <<<'MD'
Content strategy is a production system with a measurement loop. The calendar is an output, not the plan.

## The first ninety days

1. **Audit what exists** and decide keep, improve, consolidate or remove.
2. **Pick one topic you can credibly own** and map 20 to 40 real questions.
3. **Publish the bottom-funnel pages first.** They fund the patience the rest requires.
4. **Add "how did you hear about us"** to every form today.

## Where we can help

We build content operations end to end: research, briefs, expert interview production, publishing, distribution and pipeline reporting.

Our free audit tool checks your site's technical and structural readiness in about ten seconds and emails you the report.
MD,
];
