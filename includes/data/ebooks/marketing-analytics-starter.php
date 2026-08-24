<?php
/** Chapter content for the Marketing Analytics Starter Kit e-book. */
return [
'subtitle' => 'A GA4 setup that answers business questions, not vanity ones',
'description' => 'Analytics built around decisions: the questions worth answering, a GA4 configuration that works, conversion tracking that reconciles with revenue, and reporting people act on.',
'subjects' => ['Analytics', 'GA4', 'Marketing Measurement', 'Conversion Tracking'],
'chapters' => [

['Start from the decision, not the dashboard', <<<'MD'
Most analytics implementations begin with installing a tool and end with dashboards nobody opens. The cause is the starting point. Tools do not produce insight. Questions do.

## The questions worth answering

Before configuring anything, write down the decisions your marketing budget actually turns on. For most businesses the list is short:

1. Which channels produce customers, not just visits?
2. What does a customer cost from each channel?
3. Which pages convert, and which lose people?
4. How long is the path from first visit to enquiry?
5. Which campaigns should stop?
6. What is the leak in the funnel right now?

Six questions. Everything you configure should serve one of them. If a report answers none, it is decoration.

## Why default GA4 disappoints

GA4 out of the box tracks pageviews and some automatic events. That answers almost none of the above, because it does not know:

- What counts as a conversion for your business
- What a conversion is worth
- Which form was submitted, or whether it succeeded
- Which enquiries became customers

All four require deliberate configuration. Without it you get traffic reporting, which is what most teams end up presenting and then quietly stop presenting.

!NOTE The most common analytics failure is not misconfiguration. It is a correctly installed tool measuring things nobody makes decisions with.

## The measurement plan

A simple document, written before touching the tool. Four columns:

| Question | Metric | Event needed | Where reported |
| --- | --- | --- | --- |
| Which channels produce customers | Customers by source | Qualified lead event with source | Monthly review |
| What does a customer cost | CAC by channel | Spend joined to conversions | Monthly review |
| Which pages convert | Conversion rate by landing page | Landing page dimension on conversion | Weekly |
| Where is the funnel leaking | Step completion rates | Funnel step events | Weekly |

Two pages of this saves months of building reports nobody uses.

## Define your conversions precisely

"Conversion" is used too loosely and it corrupts every downstream number.

Distinguish clearly:

- **Micro conversion.** Guide download, newsletter signup. Interest, not intent.
- **Lead.** Enquiry form submitted. Real intent, unqualified.
- **Qualified lead.** Fits your criteria. Sales agrees it is real.
- **Customer.** Paid.

Track all four. Report primarily on the last two. A dashboard that treats a newsletter signup and a sales enquiry as the same event will mislead every budget decision you make from it.

## The single number that survives scrutiny

**Blended customer acquisition cost:** total marketing spend divided by new customers, from your own records.

It ignores attribution arguments, cannot be inflated by platform self-reporting, and reconciles with your bank statement. Every sophisticated model should be sanity-checked against it. When a channel dashboard claims a 6x return and blended CAC has not moved, the dashboard is wrong.
MD],

['A GA4 configuration that works', <<<'MD'
This chapter is the practical setup. It assumes GA4 is installed and nothing else is configured.

## Step 1: Tag through a tag manager

Install GA4 via Google Tag Manager rather than hard-coding it. The reason is operational: marketing can add and change tracking without a developer deployment, and you get a single place to audit what is loading.

Note the performance cost. Every tag competes for the main thread. Audit the container quarterly and remove what nobody uses.

## Step 2: Configure the events that matter

Beyond the automatic ones, most businesses need:

| Event | Fires when | Parameters |
| --- | --- | --- |
| `generate_lead` | Enquiry form submitted successfully | form_name, service, value |
| `file_download` | Resource downloaded | file_name, resource_type |
| `contact_click` | Phone, email or WhatsApp clicked | method |
| `form_start` | First field focused | form_name |
| `scroll_depth` | 25/50/75/100% | percent |
| `view_pricing` | Pricing section viewed | page |

**Fire conversion events on success, not on click.** A submit-button click event counts failed validations as conversions and inflates your numbers by a margin that varies unpredictably.

## Step 3: Mark the right events as key events

Only genuine business outcomes. Marking scroll depth as a conversion makes conversion reporting meaningless.

For most businesses: `generate_lead`, `purchase`, and possibly `contact_click`.

## Step 4: Assign values

Even approximate values transform reporting.

If one in five enquiries becomes a customer at an average first contract of one lakh rupees, an enquiry is worth twenty thousand. Set that as the event value. Now channel reports show value rather than counts, and comparisons become meaningful.

Revisit quarterly as close rates change.

## Step 5: Fix data quality

Three settings that materially affect accuracy:

**Internal traffic.** Filter your own team by IP. On a low-traffic site, staff activity can be a large share of sessions.

**Referral exclusions.** Exclude payment gateways and any domain in your own checkout flow. Otherwise a payment provider appears as a top traffic source and steals credit from the real one.

**Cross-domain measurement.** If your booking or checkout sits on another domain, configure it or every conversion will be attributed to a self-referral.

## Step 6: UTM discipline

Inconsistent tags destroy channel reporting quietly.

Agree a convention and enforce it:

- Lowercase, always. `Facebook` and `facebook` are different sources.
- `utm_source`: the platform. `google`, `facebook`, `linkedin`
- `utm_medium`: the type. `cpc`, `email`, `social`, `referral`
- `utm_campaign`: a readable campaign name with a date
- Never tag internal links. It restarts the session and destroys attribution.

That last one is a frequent and expensive mistake. Tagging your own navigation links attributes conversions to your own site.

## Step 7: Connect the other sources

- **Search Console** for query and ranking data
- **Google Ads** for cost data alongside conversions
- **CRM**, the highest-value connection, for the loop from lead to customer

Without CRM data you can measure leads. With it you can measure customers, which is the only thing that matters for budget decisions.
MD],

['Closing the loop from lead to revenue', <<<'MD'
The gap between marketing reporting and finance reporting is where credibility is lost. Closing it is the highest-value analytics work available.

## The problem

Marketing reports 240 leads. Sales reports 18 opportunities. Finance reports 6 customers. Three teams, three numbers, three different conclusions about whether marketing works.

The cause is that lead source is captured at the start and never travels with the record.

## The fix, in outline

**1. Capture source at form submission.** Store the original UTM parameters, referrer and landing page as hidden fields on every form. First-touch, not last, and persist across the session.

**2. Pass them into the CRM** as fields on the contact record.

**3. Keep them attached** through qualification, opportunity and closed-won.

**4. Report from the CRM,** not the analytics tool, for anything past the enquiry stage.

Once this exists you can answer the question everyone actually wants answered: which channel produces customers, at what cost, at what value.

## What this reveals

The findings are consistently uncomfortable and consistently useful:

- The channel producing the most leads often produces the fewest customers
- A channel dismissed for low volume often has the best close rate
- Some campaigns produce leads that never qualify, and had been judged successful on volume
- Organic and direct usually deserve far more credit than last-click gives them

## Self-reported attribution

Add one optional question to every form: **"How did you hear about us?"**

It is unscientific and it is frequently the most useful data you have. It captures word of mouth, podcasts, forwarded links, a conversation at a conference, and reading a comparison page months ago. Tracking captures none of that.

Use it as a corrective on the model, not a replacement for it.

## Handling long sales cycles

For cycles measured in months, standard attribution windows expire before the deal closes.

Practical adaptations:

- Extend lookback windows to match your actual cycle
- Report on opportunities created, not only closed, as the leading indicator
- Use cohort reporting: leads from January, closed by June
- Feed offline conversions back to ad platforms so bidding optimises for customers rather than form fills

That last one materially improves ad performance and is skipped by most accounts because it takes a day to set up.

## The reconciliation habit

Monthly, compare three numbers:

1. Conversions reported by ad platforms
2. Leads recorded in your CRM
3. Customers recorded in finance

Some variance is normal. Platform numbers run higher because of view-through and modelled conversions. But if platforms report 300 and the CRM has 80, something is broken and every decision made from those platform numbers is wrong.
MD],

['Reporting people actually act on', <<<'MD'
A report that does not change a decision is overhead. Most marketing reporting is overhead.

## The one-page monthly report

Resist the dashboard with forty tiles. One page, five numbers, one recommendation.

| Number | Source | Why it earns a place |
| --- | --- | --- |
| Qualified leads | CRM | The output that matters |
| Blended CAC | Spend divided by customers | Efficiency, unarguable |
| Pipeline value created | CRM | Forward-looking |
| Conversion rate by channel | CRM plus analytics | Where quality differs |
| Leading indicator | Rankings, citation share, impressions | What happens next |

Then a single sentence: **what we are changing next month, and why.**

## What to leave out

Deliberately excluded, and be prepared to defend the exclusion:

- Total sessions. Rewards traffic, not results.
- Bounce rate. Ambiguous and frequently misread.
- Time on page. Long can mean engaged or confused.
- Impressions. Meaningful only as a leading indicator, never as an outcome.
- Social followers. Not correlated with revenue in most businesses.

Removing these is often resisted, because they are the numbers that look good. That is precisely why they should go.

## Weekly versus monthly

**Weekly** is operational. Is anything broken? Did a campaign break? Is spend pacing? Five minutes, exceptions only.

**Monthly** is strategic. What is working, what should change, where does budget move?

Do not do strategy weekly. Channels need time to produce readable signal, and reacting to weekly noise is how accounts get destabilised.

## The diagnostic funnel

When a number drops, work down this sequence rather than guessing:

1. **Did tracking break?** Always check first. Most sudden drops are measurement, not reality.
2. **Did traffic fall?** Which channel, which pages?
3. **Did conversion rate fall?** Same traffic converting worse suggests a site problem.
4. **Did lead quality fall?** Same volume, worse fit, suggests targeting drift.
5. **Did something external change?** Algorithm update, competitor, seasonality.

Working the sequence takes twenty minutes and prevents the most common failure mode: rebuilding a landing page to fix what was actually a broken tag.

## Reviewing the setup itself

Quarterly, verify the measurement rather than reading it:

- Submit a test enquiry and confirm it appears correctly, with source attached
- Check tag manager for tags nobody uses
- Confirm internal traffic filtering still works
- Reconcile platform, CRM and finance numbers
- Re-check event values against current close rates
- Confirm the CRM still receives source fields after any form change

Form changes silently breaking source capture is one of the most common causes of gradual attribution decay, and it is invisible unless somebody tests.
MD],

],
'closing' => <<<'MD'
Analytics earns its keep when it changes decisions. Everything else is reporting theatre.

## The first thirty days

1. **Write the measurement plan.** Six questions, the metrics that answer them. Two pages.
2. **Fix conversion tracking.** Fire on success, assign values, mark only real outcomes as key events.
3. **Capture source into the CRM** so lead-to-customer reporting becomes possible.
4. **Add "how did you hear about us"** to every form.
5. **Reconcile** platform, CRM and finance numbers once, and find out how far apart they are.

## Where we can help

We set up measurement that reconciles: GA4 configuration, CRM integration, offline conversion feedback and reporting built around decisions.

If you want a technical read on your site first, our free audit tool checks the foundations in about ten seconds and emails you the report.
MD,
];
