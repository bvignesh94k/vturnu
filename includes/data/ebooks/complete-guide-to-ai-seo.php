<?php
/** Chapter content for the Complete Guide to AI SEO. */
return [
'subtitle' => 'Getting retrieved, chosen and cited by generative search engines',
'description' => 'A working guide to AI search optimisation: the retrieval landscape, how sources get chosen, content built for answers, entity foundations, corroboration and measurement.',
'subjects' => ['AI SEO', 'Answer Engine Optimization', 'Generative Engine Optimization', 'SEO'],
'chapters' => [

['The new retrieval landscape', <<<'MD'
Search did not disappear. It gained a layer. Understanding that layer is the difference between adapting and panicking.

## The surfaces that now matter

**AI Overviews** sit above organic results on a growing share of queries. When present, the traditional first result drops below the fold on most phones.

**Assistants** are used for vendor research, shortlisting and comparison. These are commercial queries that previously produced the most valuable clicks.

**In-product search** inside the tools your buyers already use, increasingly answering rather than listing.

## How each surface gets its information

Not all of them work the same way, and the differences change what you do.

| Surface | Source | Implication |
| --- | --- | --- |
| AI Overviews | Live index, grounded | Standard SEO gets you into the candidate pool |
| ChatGPT with search | Live retrieval plus training corpus | Crawler access is decisive |
| Perplexity | Live retrieval, always cited | Structure and clarity dominate |
| Gemini | Google index plus training | Overlaps heavily with organic |
| Assistants without search | Training data only | Slow-moving; brand mentions matter |

The practical takeaway: **most of these read the live web through a crawler.** Access, speed and structure decide whether you are in the running.

## What actually changed for practitioners

Three shifts:

1. **The unit of competition moved from page to passage.** Engines retrieve and evaluate chunks, not documents.
2. **Citation replaced position.** Being named in the answer is the visibility.
3. **Corroboration became a ranking input.** A claim repeated across independent sources becomes a fact worth quoting.

## What did not change

Almost everything else. Crawlability, relevance, clarity, authority and originality all still matter, and matter more.

The teams who do well here are usually the ones who were already doing search properly. The tactics that stop working are the ones that were always gaming: keyword density, thin location pages, content padded to a word count.

!STAT Access first | The most common blocker we find is not content quality. It is a robots.txt or firewall rule blocking AI crawlers, often added years ago and never revisited.
MD],

['Being chosen: the source model', <<<'MD'
Three filters decide whether you appear. A page must pass all three, and most effort goes to the last while failing the first.

## Filter one: retrieval

Mechanical and binary.

- **Crawler access.** Check robots.txt for GPTBot, OAI-SearchBot, ChatGPT-User, ClaudeBot, PerplexityBot, Google-Extended. Also check your CDN or WAF, which can block independently.
- **Rendering.** If content requires JavaScript, expect partial or empty extraction.
- **Response speed.** Live retrieval systems time out.
- **Index presence.** Not indexed means not a candidate.

Test this before anything else. It takes an hour and it is where most of the loss occurs.

## Filter two: passage relevance

Engines chunk documents and score chunks.

The consequence is counterintuitive: a 700 word page answering directly can beat a 3,000 word guide that buries the answer in paragraph fourteen. The long guide may be the better document. The short one is the better chunk.

This is why answer-first structure is mechanical rather than stylistic.

## Filter three: corroboration and trust

Among relevant passages, engines prefer verifiable claims from publishers with reason to be trusted.

Signals that demonstrably matter:

- Consistent business identity across the web
- Named authors with real credentials
- Specific, checkable claims
- Agreement with independent sources
- Original data that others reference

## The diagnostic

Run this before changing anything.

| Check | Method | Common failure |
| --- | --- | --- |
| Crawler access | Read robots.txt and firewall rules | Blocked by a security template |
| Render dependency | View source with JS off | Content only in the bundle |
| Answer position | Read your first 60 words | Opens with company history |
| Specificity | Count numbers on the page | Adjectives instead of figures |
| Entity clarity | Search brand name plus "company" | No consistent record |

## The strategic implication

Once filter three is understood, a durable approach emerges: publish something specific and true that nobody else has, make it easy to quote, and get it referenced.

At that point you stop competing for a citation slot and become the source of the fact.
MD],

['Content for answers', <<<'MD'
## The answer-first structure

Every page intended to earn citations follows the same skeleton.

**1. Direct answer, first 60 words.** Answer the title question plainly. Test: could this paragraph be quoted with no other context?

**2. Supporting context, 150 to 300 words.** Conditions, exceptions, reasoning. This is what stops the direct answer from being glib.

**3. Structured detail.** Tables, numbered processes, comparisons. Extracted with high fidelity and attributed accurately.

**4. Related questions, each with its own subheading and immediate answer.** The highest-leverage section, because it multiplies the queries one page can serve.

**5. Provenance.** Author, credentials, last verified date.

## Rewriting an opening

**Before:** "In today's rapidly evolving digital landscape, businesses face unprecedented challenges when selecting a marketing partner."

**After:** "A competent SEO retainer for a mid-market Indian business runs fifty thousand to two lakh rupees monthly, scaling with market competitiveness rather than site size. Below thirty thousand you are buying part-time junior time, not strategy."

The second can be quoted. The first cannot be quoted by anyone, for any purpose.

## Question architecture

Build around question clusters rather than keywords.

| Intent | Question shape | Requirement |
| --- | --- | --- |
| Definitional | What is X | A clean, quotable definition |
| Procedural | How do I X | Numbered steps with specifics |
| Comparative | X vs Y, best X | An honest table including where you lose |
| Evaluative | Is X worth it, cost of X | Real numbers and conditions |

**Comparative content is where most brands lose nerve.** A comparison concluding you win everything is discounted immediately. One that names where a competitor is the better choice gets cited, because it reads as evaluation rather than marketing.

## Formatting that measurably helps

- One idea per paragraph
- Subheadings phrased as questions where natural
- Numbers over adjectives
- Define terms on first use
- Conclusion before reasoning
- One clear topic per URL

## What to stop

- Keyword density work, which is noise to semantic retrieval
- Padding to word counts, which dilutes chunk relevance
- Thin location pages that corroborate nothing
- Anonymous publishing
- Hedged answers containing no specifics
MD],

['Entity foundations and corroboration', <<<'MD'
Content decides whether you are quotable. Entity work decides whether the engine knows who is being quoted.

## Consistent identity

The same name, contact details and description everywhere you appear. Inconsistency prevents an entity from consolidating, because the system cannot tell whether it is looking at one company or three.

Audit by searching your brand name and listing every property describing you. Fix the disagreements.

## Organization schema, properly complete

Most sites ship a stub. A useful block declares:

- Legal name and trading names
- Logo as a proper ImageObject with dimensions
- Contact points with type and language
- Areas served
- Fields of expertise
- Links to every profile you control

That last field is a direct statement that these scattered profiles are one entity.

## Page-level structured data

| Page type | Schema | Common error |
| --- | --- | --- |
| Article | Article with author and dates | No author, no dateModified |
| Question content | FAQPage | Marking up invisible content |
| Service | Service linked to Organization | Orphaned from the entity |
| Product | Product with real price | Fabricated reviews |

Fabricated markup is worth calling out. Review schema without reviews is a manual action risk and, more immediately, it teaches the engine your structured data is unreliable.

## Author identity

Named authors with a real page: credentials, history, external work. The weighting gap between authored and anonymous content is substantial, and largest for anything touching money, health or law.

## Building corroboration

Schema is your claim about yourself. Corroboration is others making the same claim.

In order of effort:

1. Complete, consistent directory profiles
2. Association and partner listings
3. Review platforms relevant to your category
4. Guest bylines under a named person
5. Original research others cite by name

The fifth compounds and the others do not. A published benchmark that becomes the number people quote makes you the source rather than a candidate.

## llms.txt

A plain-text summary at your root, written for machine consumption. Support is not universal and it is not a ranking factor.

It is also nearly free, harmless if ignored, and a clean statement of structure for systems that read it. Publish one, generate it from live content so it cannot drift, and do not expect it to substitute for content and entity work.
MD],

['Measurement and iteration', <<<'MD'
## What you cannot measure

State the limits before promising anything internally.

- No impression counts for answer engines
- Answers vary by user, session and phrasing
- No citation API for arbitrary brands
- Attribution from answer to conversion is indirect

Anyone promising exact AI impression share is selling a model, not a measurement.

## What you can measure

### Citation share by prompt set

1. Build 40 to 60 prompts a real buyer would ask, across definitional, comparative and evaluative intents
2. Run monthly across relevant engines, clean session, no personalisation
3. Record: cited or not, which URL, which competitors appeared
4. Track the percentage where you appear

**Freeze the prompt set.** Changing it monthly destroys comparability, which is the whole point.

### Assistant referral traffic

Identifiable referrers in analytics. Segment them. Volume looks small, quality is usually high, because the visitor arrived after reading an evaluation.

Judge on conversion rate, not volume. Comparing to organic session counts is the wrong comparison and will always look bad.

### Crawler access logs

The most underused data in the discipline. Server logs show which agents fetch which pages and what they receive.

- Are crawlers reaching priority pages
- Are they getting 200s or errors
- Which content attracts repeat retrieval
- Did access change after a config edit

### Branded demand

The lagging commercial indicator. Working AI visibility raises branded search and direct traffic.

## Monthly report

| Metric | Source |
| --- | --- |
| Citation share | Prompt set |
| Prompts won or lost | Prompt diff |
| Assistant referrals | Analytics segment |
| AI crawler hits | Server logs |
| Branded search | Search Console |

Then one sentence: what changes next month, and why.

## Realistic timelines

- **Weeks 1 to 4:** access and technical fixes. Citation share barely moves.
- **Weeks 4 to 12:** restructured content indexed. Definitional prompts move first.
- **Months 3 to 6:** comparative and evaluative prompts shift. This is the commercially valuable set and it moves last.
- **Months 6 plus:** entity and corroboration work compounds.

Do not read early definitional wins as proof that commercial prompts will follow automatically. They require the corroboration work.
MD],

],
'closing' => <<<'MD'
AI search rewards the site that is easiest to quote accurately. Every technique in this guide is a way of being easy to quote.

## The first thirty days

1. **Check crawler access** in robots.txt and at your CDN. Highest-return hour available.
2. **Rewrite the opening 60 words** of your five most important commercial pages.
3. **Complete Organization schema** and link every profile you control.
4. **Run your prompt set once** to establish a baseline before changing anything else.

## Where we can help

We run AI search programmes end to end: access remediation, content restructuring, entity work and monthly citation-share reporting.

Our free audit tool checks crawler access, answer readiness and schema in about ten seconds and emails you the report.
MD,
];
