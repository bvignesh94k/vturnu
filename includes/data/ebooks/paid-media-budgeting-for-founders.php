<?php
/** Chapter content for the Paid Media Budgeting e-book. */
return [
'subtitle' => 'A unit-economics-first approach to spending on ads without burning cash',
'description' => 'How much to spend on paid media, where to spend it, and when to scale: contribution margin, payback periods, channel sequencing and the diagnostics that stop waste.',
'subjects' => ['Paid Advertising', 'PPC', 'Marketing Budget', 'Unit Economics'],
'chapters' => [

['Start with the maths, not the channel', <<<'MD'
The most expensive mistake in paid media is choosing a channel before knowing what a customer is worth. Everything that follows, budget, bidding, creative, scaling, depends on numbers most founders have never calculated precisely.

## The four numbers you need

Before spending anything, know these. If you do not have them, getting them is the first project.

### 1. Average order value or first contract value

What a new customer pays you initially. For subscription or retainer businesses use first-month value, not annual, because cash timing matters.

### 2. Contribution margin

Revenue minus the variable costs of delivering it. Not gross margin from your accounts. The actual marginal cost of serving one more customer.

For a service business that includes delivery labour. For ecommerce it includes cost of goods, payment fees, shipping and expected returns. A store with a 60% gross margin often has a 35% contribution margin once those land.

**This is the number that funds advertising.** Not revenue.

### 3. Customer lifetime value

Contribution margin multiplied by expected repeat purchases or retained months. Be conservative. Optimistic lifetime value has funded more failed ad accounts than any other single assumption.

For a business under two years old you do not have reliable retention data. Use first-purchase contribution margin and treat repeat as upside.

### 4. Maximum allowable cost per acquisition

The number that governs everything:

`Max CPA = Contribution margin × Target payback ratio`

If contribution margin on a first order is 4,000 rupees and you want to recover acquisition cost on the first purchase, your maximum CPA is 4,000 rupees. Spend more and you are buying revenue at a loss.

!STAT Contribution margin | not revenue, is what funds advertising. Most overspending traces back to budgeting against the wrong number.

## Payback period, the constraint nobody plans for

Two businesses with identical lifetime value can have completely different capacity to spend, because of when the money arrives.

| Model | Cash in | Sustainable payback |
| --- | --- | --- |
| Ecommerce, single purchase | Immediate | Must profit on first order |
| Ecommerce, repeat category | Immediate, then recurring | 1 to 3 months acceptable |
| Service retainer | Monthly | 3 to 6 months if churn is low |
| High-ticket B2B | On close, long cycle | 6 to 12 months with funding |

A business without external funding cannot run a twelve-month payback regardless of what the lifetime value model says. You will run out of cash while being technically profitable on paper. This is the most common way a growing company dies.

## Setting the first budget

Once you have max CPA, budget becomes arithmetic instead of guesswork.

1. Decide how many customers you want this month
2. Multiply by max CPA to get maximum spend
3. Reduce by 30% for the learning period, when costs run above target
4. That is your test budget

**A floor to respect:** below roughly 30 conversions per month, a channel cannot optimise and you cannot read the data. If your max CPA times 30 exceeds what you can afford, paid media is not your channel yet. That is a real answer, not a failure.

## The question to answer before spending

Can you afford to lose the first 60 days of spend entirely?

The learning period is genuinely lossy. Algorithms need conversion volume, creative needs testing, audiences need discovery. Budget that cannot survive being spent inefficiently should not enter a paid channel at all.
MD],

['Choosing and sequencing channels', <<<'MD'
Channel choice is a function of intent, not popularity. The right first channel is usually obvious once you ask the right question.

## The intent question

Does demand for what you sell already exist as a search?

**Yes:** Someone is typing "commercial water purifier supplier Chennai." Start with search. You are harvesting existing intent, which is the cheapest form of demand.

**No:** Nobody searches for your category because they do not know it exists. Start with paid social. You are creating demand, which is more expensive and slower, but it is the only option.

Getting this wrong is costly in both directions. Running search ads for a category nobody searches produces a handful of impressions. Running discovery social for a high-intent category means paying to interrupt people who would have found you anyway.

## Channel characteristics

| Channel | Intent | Typical use | Watch for |
| --- | --- | --- | --- |
| Google Search | High, existing | Harvest demand | Competitor bidding inflates cost |
| Google Shopping | High, ecommerce | Product discovery | Feed quality decides everything |
| Performance Max | Mixed, automated | Scale after data exists | Poor visibility, needs strong signal |
| Meta (FB/Instagram) | Low, created | Demand creation, retargeting | Creative fatigue is rapid |
| LinkedIn | Low, B2B targeted | Considered B2B | Highest cost per click by far |
| YouTube | Low, attention | Awareness, remarketing | Needs real production quality |

## The sequence that works

Resist running everything at once. Run this order.

**Phase 1: Brand defence and existing demand.**
Bid on your own brand terms and your highest-intent category terms. Cheap, converts immediately, establishes a conversion baseline. Some founders object to paying for brand terms they rank for organically. If competitors bid on your name, the alternative is letting them intercept buyers who were specifically looking for you.

**Phase 2: Retargeting.**
People who visited but did not convert. Highest return in the account, almost always. Cheap because the audience is small and warm.

**Phase 3: Non-brand search.**
The real test. Higher cost, genuinely incremental. This is where you discover whether your unit economics work at scale.

**Phase 4: Demand creation.**
Paid social prospecting. Start only once you know your conversion rate and CPA from the phases above, because you need a benchmark to judge it against.

**Phase 5: Automated and broad.**
Performance Max, broad match with smart bidding. These need conversion data to work and will waste money without it.

Most accounts we audit started at phase 5 because it was recommended as easy, with no data to feed it.

## Budget allocation

A workable starting split for a business past the test stage:

- 15 to 20% brand defence and retargeting
- 50 to 60% proven, profitable campaigns
- 20 to 30% testing new angles, audiences, channels

The testing allocation is not optional. Accounts without one decay as creative fatigues and auction dynamics shift.

## When to add a channel

Add only when the current channel is genuinely capped, meaning increasing budget no longer increases conversions at acceptable CPA. Adding a second channel while the first is still scaling profitably splits attention and budget for no reason.
MD],

['Reading the account: diagnostics that prevent waste', <<<'MD'
Most wasted ad spend is visible in the account for weeks before anyone notices. These are the checks that surface it.

## The weekly review, in order

Do these in sequence. Each answers a different question and the order prevents misdiagnosis.

### 1. Is conversion tracking correct?

Before analysing anything, verify the data. Broken tracking makes good campaigns look bad and produces confident wrong decisions.

- Do platform conversions roughly match your CRM or backend? Some variance is normal. A 3x gap is a tracking fault.
- Are you counting the right event? Counting form views instead of submissions is common and flattering.
- Is one conversion being counted by multiple tags?
- Are offline conversions fed back for long sales cycles?

### 2. Where is spend actually going?

Sort by spend, descending. Look at the top 20% of line items, which usually carry 80% of budget. For each, ask whether it is producing at target CPA.

The most common finding: a single broad-match keyword or a single audience consuming 40% of budget with no conversions.

### 3. Search terms, not keywords

The keyword is what you bid on. The search term is what people actually typed. The gap between them is where money disappears.

Review search terms weekly. Add negatives aggressively. In a neglected account, irrelevant terms routinely account for a quarter of spend.

### 4. Is creative fatiguing?

On paid social, frequency above roughly 3 to 4 with declining click-through means the audience has seen it enough. Refresh before performance collapses, not after.

### 5. Landing page match

Does the page match the ad promise exactly? An ad for "engagement rings under one lakh" landing on a general jewellery homepage wastes the click that was already paid for.

## The metrics that mislead

| Metric | Why it misleads | Use instead |
| --- | --- | --- |
| Click-through rate | High CTR from unqualified clicks costs money | Conversion rate from click |
| Impressions | Rewards reach, not results | Conversions |
| Cost per click | Cheap clicks that never convert are expensive | Cost per acquisition |
| ROAS alone | Ignores margin entirely | Contribution margin per rupee spent |
| Platform-reported conversions | Attribution is self-serving | Blended CAC against actual revenue |

**Blended CAC** deserves emphasis: total marketing spend divided by total new customers, from your own records. It ignores attribution arguments entirely and is the only number that reconciles with your bank account.

## Diagnosing a campaign that stopped working

Work through in this order, because fixing later items first wastes effort:

1. Did tracking break? Check first, always.
2. Did a competitor enter the auction? Check impression share and CPC trend.
3. Did creative fatigue? Check frequency and CTR over time.
4. Did the landing page change or slow down?
5. Did seasonality shift?
6. Did the algorithm lose its signal after a structure change?

Structural changes reset learning. Every significant edit costs a learning period, which is why constant tinkering underperforms patience.

!NOTE A practical rule: change one variable at a time and wait for at least 30 conversions before judging the result. Faster than that, you are reading noise.
MD],

['Scaling without breaking the economics', <<<'MD'
Scaling is where most accounts lose money, because the assumption is that doubling budget doubles results. It does not.

## Why costs rise as you scale

Three forces work against you simultaneously.

**Audience quality declines.** The first thousand people reached are the most likely to buy. The ten-thousandth is less interested by definition.

**Auction pressure increases.** Bidding for more volume means bidding for more competitive inventory.

**Frequency rises.** The same people see your ads more often, which lifts cost per incremental conversion.

The practical consequence: CPA rises as spend rises. Your plan must account for it rather than being surprised by it.

## The correct scaling model

Do not plan around a fixed CPA. Plan around a CPA curve.

| Monthly spend | Expected CPA | Still profitable? |
| --- | --- | --- |
| 1x baseline | 100% of target | Yes |
| 2x | 110 to 125% | Usually |
| 4x | 130 to 160% | Depends on margin |
| 8x | 170 to 220% | Often not |

Your scaling ceiling is the spend level where CPA meets max CPA. Beyond it you are buying unprofitable customers. Knowing that number in advance prevents a great deal of pain.

## How to scale mechanically

**Increase budgets gradually.** 20 to 30% every three or four days. Larger jumps reset the learning phase and performance drops before it recovers.

**Expand before you bid harder.** In order:

1. New geographies with the same offer
2. New audiences with the same creative
3. New creative for the same audiences
4. New offers or landing pages
5. Only then, higher bids

Raising bids is the least efficient lever and the one most people reach for first.

**Keep testing funded.** A scaling account still needs 20% in testing, otherwise you scale into fatigue with nothing ready to replace what stops working.

## When not to scale

Do not increase spend if any of these is true:

- Your delivery capacity cannot absorb more customers
- Lead quality is already declining at current volume
- Conversion tracking is unverified
- You have fewer than 30 conversions a month in the campaign
- Cash cannot survive a longer payback at higher CPA

Scaling a business that cannot deliver produces refunds, bad reviews and churn. The advertising works and the company gets worse.

## The efficiency work that beats scaling

Before spending more, check whether you can get more from current spend. These usually return faster than a budget increase:

- Landing page speed. Every second of load time costs conversions.
- Form friction. Reducing fields often lifts conversion measurably.
- Offer clarity. What the visitor gets, stated plainly.
- Follow-up speed. Response time within an hour versus a day changes close rates materially.
- Negative keyword hygiene.
- Retargeting coverage for people who did not convert first time.

A 20% lift in conversion rate is equivalent to a 20% cut in CPA, and it costs media budget nothing.

## A simple monthly scorecard

| Number | Source | Target |
| --- | --- | --- |
| Blended CAC | Spend divided by new customers | Below max CPA |
| Contribution margin per customer | Finance | Above CAC |
| Payback period | Finance | Within cash tolerance |
| Conversions | Platform, verified against CRM | Growing |
| Testing share of spend | Platform | 20% |

If the first three hold, you can spend more. If they do not, more spend makes the problem larger.
MD],

],
'closing' => <<<'MD'
Paid media rewards discipline more than cleverness. The accounts that compound are the ones where somebody knows the unit economics and refuses to spend past them.

## Before your next rupee of spend

1. **Calculate contribution margin.** Actual variable cost, not accounting gross margin.
2. **Set max CPA** from it, and write it somewhere your team sees.
3. **Verify conversion tracking** against your CRM before trusting any report.
4. **Confirm you can absorb** a 60-day learning period.

## Where we can help

We audit and manage paid accounts with unit economics as the starting point, not an afterthought. The audit is free and typically identifies the wasted spend in the first session.

If you want a technical read on your landing pages first, our free audit tool checks speed and conversion foundations in about ten seconds and emails you the report.
MD,
];
