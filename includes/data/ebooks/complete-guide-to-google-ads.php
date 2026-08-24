<?php
/** Chapter content for the Complete Guide to Google Ads. */
return [
'subtitle' => 'Account structure, bidding, targeting and the optimisation rhythm that compounds',
'description' => 'A complete working guide to Google Ads: conversion foundations, account structure, targeting, bidding strategy, ad and landing page craft, and a weekly optimisation cadence.',
'subjects' => ['Google Ads', 'PPC', 'Paid Search', 'Digital Marketing'],
'chapters' => [

['Foundations: get these wrong and nothing else matters', <<<'MD'
Every underperforming Google Ads account we audit has a problem in this chapter, and the account owner is usually working on chapter five.

## Conversion tracking comes first

An account without reliable conversion tracking is not an advertising account. It is a spending account.

Smart bidding, which is now the default and the only viable approach at scale, optimises toward conversions. If conversions are miscounted, the algorithm optimises toward the wrong thing with complete confidence.

**The verification checklist:**

- Submit a real test enquiry and confirm exactly one conversion is recorded
- Compare a month of platform conversions against your CRM. A 3x gap means something is broken
- Confirm conversions fire on success, not on button click
- Check for duplicate tags counting the same action twice
- Verify the conversion window matches your actual sales cycle

**Primary versus secondary conversions.** Only genuine business outcomes should be primary. Newsletter signups and PDF downloads as primary conversions teach the algorithm to find people who download things, which is not the same as people who buy.

## Choose the right conversion action

For businesses with a sales process, form fills are a proxy. The real outcome is a qualified lead or a customer.

If your CRM can identify qualified leads, feed them back as offline conversions. The bidding then optimises for lead quality rather than lead volume, which typically changes campaign performance more than any other single intervention.

!STAT 30 conversions | per campaign per month is roughly the minimum for smart bidding to work. Below that, the algorithm cannot learn and results are noise.

## Account hygiene before spend

Six settings that quietly waste budget on new accounts:

1. **Search Partners.** Off by default for testing. Performance differs substantially from Search and it is not separately controllable.
2. **Display Network expansion on Search campaigns.** Off. Always. This single setting is responsible for a remarkable share of wasted spend.
3. **Location targeting: presence, not interest.** The default includes people merely interested in your location, which for a local business means paying for the wrong country.
4. **Auto-applied recommendations.** Off. Google will apply changes including broadening match types without asking.
5. **Ad rotation.** Optimise, unless you are running a controlled test.
6. **Campaign-level negative lists** applied from day one.

## Budget floors

An honest constraint: below roughly 30 conversions a month, you cannot run this account well. If your target cost per acquisition times 30 exceeds your monthly budget, either narrow the targeting until the maths works, or accept that paid search is not yet the right channel.

Spreading a small budget across six campaigns guarantees that none of them ever learns.
MD],

['Structure: fewer campaigns than you think', <<<'MD'
Account structure has changed. Advice written before smart bidding recommends granular structures that now actively harm performance by starving each unit of data.

## The principle

**Consolidate for data, separate for control.**

Split campaigns only when you need a genuinely different budget, a different geography, or a different bidding target. Not because the keywords feel different.

## A structure that works

For most businesses:

```
Campaign: Brand
  Ad group: Brand terms
  Ad group: Brand plus competitor comparisons

Campaign: Core services, high intent
  Ad group: Service A
  Ad group: Service B
  Ad group: Service C

Campaign: Broader research terms
  Ad group: Problem-aware terms

Campaign: Remarketing (Display or PMax)
```

Four campaigns. Most accounts we inherit have eighteen, each with three conversions a month, none of them learning.

## Brand campaigns

The perennial argument: why pay for traffic you would get free?

The case for running them:

- Competitors bid on your brand name; without a brand campaign they intercept buyers looking for you
- Brand terms are inexpensive and convert at high rates
- You control the message and the landing page
- It provides the algorithm with cheap conversion data

The case against is real too: if nobody bids on your brand and your organic result dominates, brand spend may be largely incremental cost. Test it. Pause for two weeks and measure total conversions, not just paid ones.

## Single keyword ad groups

Once standard practice, now mostly counterproductive. They fragment data across dozens of units that never accumulate enough conversions to optimise.

Group by theme and intent instead: five to twenty closely related keywords per ad group, all served well by the same ad and landing page.

## Match types in practice

| Match type | Behaviour | Use for |
| --- | --- | --- |
| Exact | Close variants of the term | Proven converters, tight control |
| Phrase | Contains the meaning | Reliable middle ground |
| Broad | Related by intent, uses account signals | Only with smart bidding and good conversion data |

Broad match has genuinely improved, but it amplifies whatever signal you give it. With clean conversion data it discovers valuable terms. With broken tracking it discovers expensive irrelevance efficiently.

**Start with phrase and exact.** Introduce broad only once conversion data is verified and negatives are established.

## Performance Max

Powerful and opaque. Two rules make it survivable:

1. **Do not run it as your first campaign.** It needs conversion data to work and will spend the learning period expensively.
2. **Use brand exclusions** so it does not simply harvest brand traffic and claim credit for conversions you would have had anyway.

That second point matters commercially. A PMax campaign reporting excellent return is frequently absorbing brand searches. Check the search terms and asset group reporting before believing the headline number.
MD],

['Targeting: reaching the right person', <<<'MD'
Keywords are only one targeting dimension, and treating them as the only one leaves most of the lever unused.

## Keyword research that reflects intent

Volume is the least important attribute of a keyword. Intent is the most important.

Sort every candidate term into four buckets:

| Intent | Example | Priority |
| --- | --- | --- |
| Transactional | "buy commercial water purifier" | Highest |
| Commercial research | "best water purifier for offices" | High |
| Comparison | "aquaguard vs kent" | High |
| Informational | "how does RO purification work" | Low for paid |

Informational terms are excellent for content and usually poor for ads. Paying to educate someone who is months from buying is a strategy that requires patience most budgets do not have.

## Negative keywords

The most neglected and highest-return routine work in any account.

**Build a base negative list before launching:** free, jobs, careers, salary, DIY, cheap, second hand, repair, complaint, and any near-miss term that is not your business.

**Then review search terms weekly.** The report shows what people actually typed. In a neglected account, a quarter of spend routinely goes to terms nobody would have bid on deliberately.

Apply negatives at the right level: shared lists for account-wide exclusions, campaign level for cross-campaign separation, ad group level for fine control.

## Audience layering

Add audiences in observation mode first. This gathers performance data by segment without restricting reach, and then you bid up what works.

Worth layering on most accounts:

- Website visitors, segmented by page depth
- Converters, usually to exclude, sometimes to upsell
- Customer match lists from your CRM
- In-market segments relevant to your category
- Similar audiences derived from converters

## Geography

More decisive than most accounts treat it.

- Use presence targeting, not interest, for local businesses
- Set bid adjustments by location once you have data; performance varies substantially by city and often by area
- Exclude locations you cannot serve, explicitly
- For India, remember that language and buying behaviour vary by region far more than a national campaign assumes

## Schedule

If your business converts by phone and nobody answers on Sunday, do not pay for Sunday clicks that ring an empty office.

Review the hour-of-day and day-of-week reports after a month of data. Most accounts show clear patterns worth acting on.

## Device

Mobile and desktop frequently differ by a factor of two in conversion rate, in either direction depending on the business. Check before assuming. A B2B service selling to people at desks behaves very differently from a local service people search for while out.
MD],

['Bidding, budgets and the ads themselves', <<<'MD'
## Choosing a bidding strategy

The right choice depends almost entirely on how much conversion data you have.

| Strategy | Requires | Use when |
| --- | --- | --- |
| Manual CPC | Nothing | Very new account, no conversion data yet |
| Maximise clicks | Nothing | Gathering initial traffic data only |
| Maximise conversions | ~15 to 30 per month | Building volume, CPA flexible |
| Target CPA | ~30 per month | You know your acceptable CPA |
| Target ROAS | ~50 per month, values assigned | Ecommerce with varying order values |

**Do not set an aggressive target immediately.** Setting a target CPA 40% below your current actual CPA causes the system to restrict delivery severely, and the campaign effectively stops.

Start at or slightly above your current achieved CPA, then tighten by 10 to 15% at a time, allowing two weeks between changes.

## The learning period

Every significant change resets learning. During it, performance is unreliable and should not be judged.

Triggers: bidding strategy change, large budget change, conversion action change, major structural edits.

**The discipline this demands:** change one thing, wait for the learning period plus a meaningful sample, then judge. Accounts edited daily never leave learning and never perform.

## Budget mechanics

- Daily budget times 30.4 is the monthly cap; daily spend can exceed the daily figure but the monthly total holds
- Campaigns limited by budget will tell you so; if the campaign is profitable, raise it
- Do not spread budget so thin that no campaign reaches its conversion threshold

## Responsive search ads

The current format, and the constraints are worth understanding.

- Provide the full complement of headlines and descriptions
- Make them genuinely different, not fifteen paraphrases
- Pin sparingly. Pinning everything defeats the format; pin only what must always appear, such as a regulatory disclaimer or a price
- Include the keyword theme in some headlines, not all
- Write at least one headline that names the specific outcome
- Write one that addresses the main objection

**Ad strength is a guideline, not a ranking factor.** An "average" ad that converts well is better than an "excellent" ad that does not. Do not degrade a working ad to satisfy the meter.

## Assets, formerly extensions

Free additional real estate, and they measurably improve click-through. Add all that apply:

- Sitelinks, four or more, to genuinely different pages
- Callouts for differentiators
- Structured snippets
- Call assets, with call tracking
- Location assets for local businesses
- Price and promotion assets where relevant
- Lead form assets, used cautiously, since lead quality is often lower

## Landing pages

The most commonly ignored half of a paid search account.

- **Message match.** The page must continue the ad's promise, in the same words. A generic homepage wastes the click you already paid for.
- **Speed.** Every second of load time costs conversions. Paid traffic makes this cost immediate and measurable.
- **One clear action.** Remove navigation that leads away.
- **Proof near the action.** The trust signal belongs beside the button, not in the footer.
- **Form length matched to intent.** Fewer fields for cold traffic, more where qualification matters.

A 20% improvement in landing page conversion rate is equivalent to a 20% reduction in cost per acquisition, and it costs no media budget at all.
MD],

['The optimisation rhythm', <<<'MD'
Accounts do not fail suddenly. They decay, and a routine is what prevents it.

## Weekly, about thirty minutes

1. **Check spend pacing** against budget
2. **Review search terms,** add negatives
3. **Check for disapprovals** and policy issues
4. **Scan for anomalies:** a line item consuming budget without converting
5. **Verify conversions are still recording**

That last check catches the most damaging failure mode. A tracking break discovered five weeks later has already misdirected five weeks of bidding.

## Monthly, about two hours

1. **Performance by campaign, ad group and keyword,** against target CPA
2. **Search term mining** for new keyword opportunities
3. **Ad testing:** pause the clear losers, add new variants
4. **Audience and demographic performance,** apply bid adjustments
5. **Geographic and schedule adjustments**
6. **Landing page conversion rates** by campaign
7. **Reconcile platform conversions against CRM**

## Quarterly

1. **Structural review.** Is consolidation or separation now warranted?
2. **Competitive analysis.** Auction insights, impression share trend
3. **Bidding strategy review** as data volume changes
4. **Full landing page review**
5. **Budget reallocation** across channels based on blended CAC

## Diagnosing a drop

In this order, because fixing later items first wastes time:

1. **Tracking.** Did conversions stop recording?
2. **Budget.** Did it change, or is the campaign now budget-limited?
3. **Competition.** Check impression share lost to rank and CPC trend.
4. **Quality.** Did landing page speed or availability change?
5. **Learning.** Was there a recent edit that reset it?
6. **Seasonality.** Compare year on year, not month on month.
7. **Policy.** Any disapprovals limiting delivery?

## What to stop doing

Practices that were once standard and now harm accounts:

- Single keyword ad groups, which fragment data
- Daily bid adjustments, which prevent learning
- Optimising to ad strength rather than performance
- Judging performance during a learning period
- Using last-click platform attribution as the sole truth
- Enabling every auto-applied recommendation

## The number that keeps you honest

Whatever the platform reports, check blended customer acquisition cost monthly: total marketing spend divided by new customers, from your own records.

Platform-reported return is calculated by the party selling the advertising. Blended CAC reconciles with your bank account. When the two disagree substantially, trust the second one.
MD],

],
'closing' => <<<'MD'
Google Ads rewards discipline over cleverness. The accounts that compound are the ones where someone verifies the data, respects the learning period, and refuses to spend past the unit economics.

## The first two weeks

1. **Verify conversion tracking** against your CRM before changing anything else.
2. **Turn off Display expansion** on Search campaigns and switch location targeting to presence.
3. **Build the base negative list** and review search terms.
4. **Consolidate** if you have more campaigns than conversions can support.

## Where we can help

We audit and manage Google Ads accounts starting from unit economics and conversion integrity. The audit is free and usually identifies the wasted spend in the first session.

If you want a read on your landing pages first, our free audit tool checks speed and technical foundations in about ten seconds and emails you the report.
MD,
];
