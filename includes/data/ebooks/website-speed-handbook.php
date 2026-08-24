<?php
/** Chapter content for the Website Speed Handbook e-book. */
return [
'subtitle' => 'Core Web Vitals in plain language, and the fixes that actually move them',
'description' => 'A practical speed handbook: what LCP, INP and CLS really measure, the usual culprits, a prioritised fix list, and how to keep a fast site fast.',
'subjects' => ['Web Performance', 'Core Web Vitals', 'Technical SEO', 'Web Development'],
'chapters' => [

['What the metrics actually measure', <<<'MD'
Speed reporting is full of numbers that sound similar and mean different things. Getting the definitions right is what separates fixing the problem from optimising a score.

## The three Core Web Vitals

### Largest Contentful Paint

**What it measures:** how long until the largest visible element in the viewport finishes rendering. Usually a hero image, a heading, or a banner.

**Target:** under 2.5 seconds for 75% of visits.

**What it really tells you:** how long the visitor stares at a mostly blank screen wondering whether the site is broken.

### Interaction to Next Paint

**What it measures:** how long the page takes to visually respond after a user interaction, across the whole visit. It replaced First Input Delay in March 2024, and the change matters: FID measured only the delay before processing began, which flattered sites badly. INP measures the full round trip, on every interaction, and reports near the worst.

**Target:** under 200 milliseconds.

**What it really tells you:** whether the page feels responsive or laggy when someone taps something.

### Cumulative Layout Shift

**What it measures:** how much visible content moves unexpectedly during loading.

**Target:** under 0.1.

**What it really tells you:** whether people tap the wrong thing because the button moved as their finger landed.

!NOTE INP is where most sites that previously "passed" now fail. If your last audit predates the FID change, your real scores are likely worse than you believe.

## Lab data versus field data

This distinction causes more confusion than any other, and acting on the wrong one wastes real money.

**Lab data** comes from a synthetic test on a simulated device. Lighthouse and PageSpeed Insights produce it. It is repeatable and useful for debugging, and it is not what Google uses for ranking.

**Field data** comes from real visits on real devices, aggregated over 28 days in the Chrome User Experience Report. This is what counts for Core Web Vitals assessment.

The practical consequences:

- A perfect Lighthouse score with failing field data means your real visitors have slower devices and worse networks than the simulation
- Field data lags. A fix today shows up over the following weeks as the 28-day window rolls
- Field data needs traffic. Low-traffic pages may have none, and are assessed at origin level instead

**Where to look:** Search Console's Core Web Vitals report shows field data grouped by page type. Start there, not in Lighthouse.

## The 75th percentile

Assessment uses the 75th percentile, not the average. Three quarters of visits must meet the target.

This matters because averages hide the tail. A site with an average LCP of 2.1 seconds can fail if a quarter of visits take 5 seconds, which is common when a subset of users are on older Android devices or slower connections.

Optimise for the slow quarter, not the mean.

## What speed is worth

Two separate arguments, and it is worth being precise about which you are making.

**Ranking.** Core Web Vitals are a confirmed ranking signal, but a modest one. It rarely outweighs relevance. A slow page with the best answer usually still wins.

**Conversion.** This is the larger effect by some distance. Load time correlates strongly with bounce and conversion rate across every study and every account we have measured.

Argue for speed work on conversion. The SEO benefit is real but secondary.
MD],

['Diagnosing before fixing', <<<'MD'
Most speed work fails because it starts with a generic checklist instead of a diagnosis. The fix for a slow LCP caused by a render-blocking font is entirely different from one caused by a 4MB hero image.

## The diagnostic sequence

Work in this order. Each step narrows the problem.

**1. Get field data first.** Search Console Core Web Vitals report. Which page groups fail, on which metric, on mobile or desktop?

**2. Pick one representative failing URL.** Not the homepage by default. Pick the page type with the most traffic or revenue.

**3. Run a lab test on that URL.** PageSpeed Insights for both field and lab in one place.

**4. Identify the specific element.** The report names the LCP element and lists layout shift sources. This is the actual answer.

**5. Trace the cause,** using the questions below.

## Diagnosing LCP

The LCP element is named in the report. What delays it falls into four buckets.

| Cause | Symptom | Typical fix |
| --- | --- | --- |
| Slow server response | High TTFB, over 600ms | Caching, better hosting, database work |
| Render-blocking resources | Long gap before anything paints | Inline critical CSS, defer the rest |
| Slow resource load | LCP is an image that loads late | Preload, correct format, correct size |
| Client-side rendering | Blank until JavaScript executes | Server render the above-fold content |

**Time to First Byte is the one to check first.** If TTFB is 1.5 seconds, no amount of image optimisation will save you. Everything downstream inherits that delay.

## Diagnosing INP

INP problems are almost always JavaScript occupying the main thread.

Common causes in order of frequency:

1. **Third-party scripts.** Tag managers, chat widgets, analytics, heat maps, ad scripts. Each competes for the main thread.
2. **Large first-party bundles.** Everything shipped on every page rather than split by route.
3. **Expensive event handlers.** Work done synchronously on click or input.
4. **Layout thrashing.** Reading and writing layout properties in a loop.

**The third-party audit is usually the highest-return exercise.** List every external script, then ask for each: what business decision does this inform, and would anyone notice if it were gone? Most sites carry two or three scripts nobody can justify.

## Diagnosing CLS

Layout shift has a small number of causes and all are preventable.

- **Images without dimensions.** The browser cannot reserve space, so content jumps when the image arrives.
- **Ads and embeds** injected into unreserved space.
- **Web fonts** swapping and changing text metrics.
- **Content injected above existing content:** banners, notices, consent bars.
- **Animating layout properties** instead of transform and opacity.

CLS is usually the cheapest of the three to fix and the most visibly annoying to users.

## The mobile reality check

Test on a real mid-range Android device on a real mobile network. Not a flagship phone on office wifi, and not only a desktop simulation.

The gap between what a development team experiences and what a typical visitor experiences is routinely a factor of three or more. Field data reflects the visitor. Your laptop does not.
MD],

['The fixes, in priority order', <<<'MD'
This chapter is the work itself, ordered by return on effort. Doing them in this sequence avoids optimising things that turn out not to matter.

## Tier 1: highest return, lowest risk

### 1. Fix server response time

Nothing else matters if TTFB is slow.

- Enable full-page caching. For a mostly static site this alone can take TTFB from 800ms to under 100ms.
- Use a CDN so assets and cached pages serve from near the visitor.
- Check hosting honestly. Budget shared hosting has a floor you cannot optimise past.
- Profile slow database queries if the site is dynamic.

### 2. Optimise the LCP image

Usually the single largest win on content and commerce sites.

- Serve modern formats. WebP or AVIF, typically 25 to 50% smaller than equivalent JPEG.
- Size correctly. Serving a 2400px image into an 800px slot wastes most of the bytes.
- Preload it: `<link rel="preload" as="image">`.
- Never lazy-load the LCP image. This is a very common and self-defeating mistake.
- Always declare width and height, which also fixes CLS.

### 3. Declare dimensions on every image and embed

One attribute pair, and most CLS disappears.

### 4. Eliminate render-blocking CSS and fonts

- Inline the critical above-fold CSS, load the rest asynchronously
- Use `font-display: swap` so text renders immediately in a fallback
- Preconnect to font origins
- Subset fonts to the characters you actually use
- Cut the number of weights. Six weights of two families is a common and expensive habit

## Tier 2: substantial, more effort

### 5. Audit and defer third-party scripts

For each script: keep, defer, or remove. Defaulting everything to `async` is not the same as deciding.

- Load chat widgets on interaction, not on page load
- Load analytics after first paint
- Remove anything nobody reads the output of

### 6. Reduce JavaScript

- Code split by route rather than shipping one bundle
- Remove unused libraries, particularly date and utility libraries pulled in for one function
- Prefer native APIs over dependencies where reasonable

### 7. Cache aggressively with versioned assets

Long cache lifetimes on static assets, with a version string in the filename or query so updates still reach users immediately.

## Tier 3: architectural

### 8. Server-render above-fold content

If the page is blank until JavaScript runs, no amount of tuning fixes LCP properly. This is a build decision, not an optimisation.

### 9. Reduce total page weight

Every kilobyte costs time on a slow connection. Audit what is actually shipped and question each part.

## The measurement discipline

Change one thing. Measure. Keep or revert.

Batching ten changes and observing a 15% improvement tells you nothing about which change mattered, and one of the ten may have made things worse while being masked by the others.

!STAT 1 change at a time | is slower to execute and far faster to learn from. Batched optimisation produces sites nobody can maintain.
MD],

['Keeping a fast site fast', <<<'MD'
Performance is not a project with an end date. Every site trends slower over time unless something actively prevents it.

## Why sites decay

The pattern is consistent:

- Marketing adds a tracking pixel for a campaign that ended, and it stays
- A new feature ships a library for one component
- Images get uploaded at full camera resolution
- A plugin is installed to solve a small problem and loads on every page
- Nobody owns performance, so nobody notices

Each change is individually reasonable. The accumulation is what kills the site.

## The performance budget

Set explicit limits and treat exceeding them as a bug rather than a discussion.

| Metric | Suggested budget |
| --- | --- |
| Total page weight | Under 1.5MB |
| JavaScript, compressed | Under 300KB |
| Number of third-party origins | Under 8 |
| LCP, field, mobile | Under 2.5s |
| INP, field | Under 200ms |
| CLS, field | Under 0.1 |

Adjust the numbers to your context. The value is in having a threshold that triggers a conversation.

## Continuous monitoring

**Automated lab testing** on every deploy for your key page types. Fail the build if the budget is breached. This catches regressions before they reach visitors.

**Field monitoring** monthly via Search Console, which reflects reality.

**Real user monitoring** if you have the traffic to justify it. It shows the distribution, including the slow tail that averages hide.

## Governance that works

The technical fixes are the easy part. Preventing recurrence is organisational.

1. **Name an owner.** Someone whose responsibility includes performance.
2. **Add it to the definition of done.** New features get a performance check before merge.
3. **Gate third-party scripts.** Adding one requires naming who uses the data and reviewing it quarterly.
4. **Automate image handling** at upload so nobody has to remember.
5. **Report it monthly** alongside traffic and conversion, so it stays visible.

## The pre-launch checklist

Before any significant release:

- Images in modern formats, correctly sized, dimensions declared
- LCP image preloaded and not lazy-loaded
- Critical CSS inlined, rest deferred
- Fonts subset, swapped, preconnected
- Third-party scripts justified and deferred
- Caching headers correct on all static assets
- Tested on a real mid-range phone on mobile data
- Lab test run and compared against the previous release
- Layout shift checked by loading with a throttled connection and watching

## What to tell stakeholders

Speed work competes with feature work for budget, so the argument has to be commercial.

Frame it as conversion, not as a score. "Cutting load time from 4.2 to 1.8 seconds on mobile is worth an estimated X% lift in enquiry rate at current traffic" makes the case in a way that "improving our Lighthouse score to 95" never will.

And be honest about the ranking benefit: real, confirmed, and smaller than the conversion effect. Overstating it costs credibility when rankings do not jump.
MD],

],
'closing' => <<<'MD'
Speed is infrastructure. It quietly multiplies or divides the return on everything else you spend on the site.

## The first two weeks

1. **Open Search Console Core Web Vitals** and identify which page groups fail on which metric. Field data, not Lighthouse.
2. **Check TTFB** on your slowest template. Fix caching first if it exceeds 600ms.
3. **Fix the LCP image** on your highest-traffic page: correct format, correct size, preloaded, not lazy-loaded.
4. **Declare dimensions on every image.** Cheapest CLS fix available.

## Where we can help

We do performance work as an engineering engagement: diagnosis from field data, prioritised fixes, and a budget with monitoring so the gains hold.

If you want a starting read, our free audit tool checks server response, compression and technical health in about ten seconds and emails you the report.
MD,
];
