# Authority series: working notes

Working notes for the November 2026 article series. This folder is not part of the website (it is listed in `.vercelignore`) and can be deleted once the series is live.

## 1. Where the work stands (14 November 2026)

**Built and working locally**

- `includes/post-kit.php`: shared helpers for these articles. `px_answer`, `px_toc`, `px_table`, `px_flow` (numbered diagram), `px_chart` (bar or range chart with a table twin), `px_example` (box for invented numbers), `px_cta_mid`, `px_cta_main`, `px_author`. Loaded from `api/index.php`.
- `assets/css/style.css`: the block headed POST KIT.
- `assets/js/listicle.js`: `TOOLS.calc`, with two sets of sums: `data-calc="leads"` (used) and `data-calc="sample"` (written for the competitor benchmark article, not used yet).
- `includes/data/blog-authority.php`: the records, merged ahead of every other post at the end of `includes/data/blog.php`.
- `includes/data/seo-meta.php`: search titles and descriptions, added just above the `/* AUTHORITY:END */` marker.
- `templates/blog-post.php`: the byline shows "Updated" when a record has `modified`.
- `assets/downloads/ai-visibility-recording-sheet.csv`.

**Articles written** (`includes/data/posts/<slug>.php`)

| Slug | Notes |
|---|---|
| `pagespeed-score-vs-qualified-leads` | Checked on desktop and phone |
| `measure-qualified-leads-from-organic-search` | Calculator checked |
| `ai-crawler-access-problems` | Pass or fail board checked |
| `how-ai-agents-evaluate-b2b-websites` | Parse-checked only. Not yet viewed in a browser |
| `how-to-measure-ai-search-visibility` | Rebuilt at its old address. Parse-checked only. Not yet viewed |

**Still to write.** The slugs are fixed, because the written articles already link to them.

| Slug | Working title | Category, intent |
|---|---|---|
| `ai-citations-vs-website-traffic` | AI Citations vs Website Traffic: What B2B Marketers Should Measure | AI Search, Explainer |
| `search-visibility-vs-revenue-demand` | Search Visibility vs Revenue: Four Tests for Whether SEO Is Creating Demand | SEO, Decision Guide |
| `wordpress-seo-launch-checklist` | Search-Ready WordPress for B2B: A Technical Checklist Before You Launch | Web, Checklist |
| `ai-search-competitor-benchmark` | How to Benchmark Competitors in AI Search: A Repeatable Scorecard | AI Search, How-to |
| `linkedin-engagement-vs-buyer-intent` | Why LinkedIn Engagement Is Not the Same as B2B Buyer Intent | Social, Explainer |
| `industrial-buyer-search-intent` | How Industrial Buyers Search: Mapping Their Queries to the Pages a Manufacturer Needs | SEO, Guide |

**After the writing**

1. Banners. `php bin/og-data.php > og-data.json`, then `node bin/build-og.mjs og-data.json <filter>`. It needs Chrome: set the `CHROME` environment variable to the binary. Add a concept for each new article to `ART` in `bin/build-og.mjs` and map the slug in `BLOG_ART`. Suggested concepts: layers (crawler access), gauge (PageSpeed), ledger (qualified leads), ladder (citations), split (visibility vs revenue), agent (AI agents), launch (WordPress), scorecard (benchmark), signal (LinkedIn), matrix (industrial). The rebuilt AI visibility guide keeps its banner. If Chrome is not available, leave the banners and say so.
2. Links from existing pages to the new articles, where the page format allows a link in a sentence: `/ai-crawler-checker/`, `/ai-seo/`, `/lead-generation-seo/`, `/seo-services/`, `/lead-gen-web-design/`, `/wordpress-web-design/`, `/social-media-marketing/`, `/lead-gen-seo-for-business-industrial/`, and the rich posts in `includes/data/posts/`. Older posts stored as plain `sections` arrays cannot hold links; leave them.
3. Checks, all of them: parse every changed PHP file; `PHP_WASM_DIR=<dir> node tests/crm/run.mjs`; `node bin/audit-site.mjs http://127.0.0.1:8100` against `node bin/dev-server.mjs 8100`; search titles at most 63 characters (count `&amp;` as one), descriptions 140 to 160; no em dash or en dash in any site file; every outside link answers 200 to a browser user agent; every internal link resolves; each article viewed at 1280 and 390 pixels wide.
4. Release. The site is deployed with the Vercel CLI from the owner's computer. Do not push to `main` from anywhere else. Finish on this branch and report; the release and the live check happen on the owner's computer.

## 2. Rules for every article

- Plain, short sentences. No em dashes, no en dashes, no superlatives, no promise of rankings, citations or leads.
- Headings state something. Real questions are for the FAQ only.
- The first 100 to 150 words carry the main point, then `px_answer()` prints the record's `lede`.
- Client figures appear exactly as published in `includes/data/cases-real.php`. Those four case studies are the only ones to cite or link.
- Every invented example is labelled as invented, in words, where it appears.
- Outside claims link to their source inside the sentence, with `ext_link()` (opens in a new tab, nofollow). No list of sources at the end.
- Vendor behaviour is reported as the vendor documents it, with the date checked. Vendor research is called vendor research.
- "We" is used only for work that is documented.
- Byline: the VTurnU strategy team (`px_author()`).
- The competitor benchmark article names no competitor. Its example uses invented labels and says so.
- At most three calls to action per article. Destinations and the offer on each:

| Article | Middle | End |
|---|---|---|
| Citations vs traffic | `/ai-seo/#enquire` (free AI visibility report) | `/contact-us/`, with a link to `/ai-seo/` |
| Visibility vs revenue | a text link to `/seo-services/#enquire` (free SEO audit) | `/contact-us/`, with a link to `/seo-services/` |
| WordPress checklist | `/free-seo-audit/` (free tool, twenty checks on the homepage, no sign-up) | `/wordpress-web-design/#enquire` (free website review) |
| Competitor benchmark | none | `/ai-seo/#enquire` |
| LinkedIn | none | `/social-media-marketing/#enquire` (free social media audit) |
| Industrial buyers | `/lead-gen-seo-for-business-industrial/#enquire` (free industry SEO audit) | `/contact-us/` |

## 3. What the source check changed

- Google Analytics has a built-in **AI Assistant** channel since 13 May 2026 (medium `ai-assistant`). Organic Search now includes clicks from AI Overviews and AI Mode.
- Search Console has a **Generative AI performance report** (announced 3 June 2026): impressions only, AI Overviews and AI Mode combined, no clicks, queries or position.
- OpenAI's page documenting `utm_source=chatgpt.com` is no longer live (an archive copy exists), and the tag went missing for about a week in October 2026. Treat it as helpful, not dependable.
- Bing's AI Performance report is a public preview (10 February 2026). In June 2026 it gained Intents, Topics, Citation Share and Compare. Microsoft says the numbers do not show placement, ranking or authority.
- The SparkToro and Gumshoe study (January 2026) found position inside an AI answer close to random between runs, and the share of answers naming a brand fairly steady. So the benchmark scorecard should count named, cited and accurate, and should not score "named first".
- No fresh Lighthouse run of the Agentic Browsing category was obtained for vturnu.com on 14 November 2026, so the AI agents article quotes only the two published client results. Do not add a claim of a fresh test.
- A raw HTML test on 14 November 2026: a Cafy blog article returned about 2,000 words of text in the HTML. FlowEazy article pages still load their text with JavaScript, and its fix is waiting for the client's server upload. The crawler article says exactly this and no more.

## 4. Verified facts for the WordPress checklist and the visibility article

Pages read on 13 November 2026.

**WordPress**

- Current release: 7.1.4, 28 October 2026 (https://wordpress.org/download/releases/). 7.1 shipped 19 August 2026 and 7.0 on 20 May 2026.
- "Discourage search engines from indexing this site" (Settings, Reading): since 5.3 it prints a robots meta tag with `noindex, nofollow`, not a robots.txt block (https://wordpress.org/documentation/article/settings-reading-screen/, https://make.wordpress.org/core/2019/09/02/changes-to-prevent-search-engines-indexing-sites/).
- XML sitemaps are in core since 5.5 at `/wp-sitemap.xml`, 2,000 URLs per file, referenced from robots.txt, and switched off when the discourage setting is on (https://make.wordpress.org/core/2020/07/22/new-xml-sitemaps-functionality-in-wordpress-5-5/).
- The virtual robots.txt: `User-agent: *`, `Disallow: /wp-admin/`, `Allow: /wp-admin/admin-ajax.php`, and a `Sitemap:` line on public sites (https://developer.wordpress.org/reference/functions/do_robots/).
- Core prints a canonical link on single posts and pages only (https://developer.wordpress.org/reference/functions/rel_canonical/). Archives get none from core; that is a reading of the source, so word it carefully.
- Permalink options: Plain, Day and name, Month and name, Numeric, Post name, Custom (https://wordpress.org/documentation/article/customize-permalinks/). The documentation pages disagree about the default, so do not state one.
- Lazy loading for images since 5.5 and iframes since 5.7; `fetchpriority="high"` on the likely main image since 6.3 (https://make.wordpress.org/core/2023/07/13/image-performance-enhancements-in-wordpress-6-3/).
- Speculative loading since 6.8, on by default for logged-out visitors on sites with pretty permalinks (https://make.wordpress.org/core/2025/03/06/speculative-loading-in-6-8/).
- Site Health (Tools, Site Health) shows whether the site discourages search engines, the permalink structure and HTTPS status (https://wordpress.org/documentation/article/site-health-screen/).
- The 7.0 and 7.1 field guides show no change to sitemaps, robots, canonicals, speculative loading or lazy loading.

**Google Search Central**

- `noindex` works only if the page is not blocked in robots.txt (https://developers.google.com/search/docs/crawling-indexing/block-indexing).
- robots.txt controls crawling, not indexing (https://developers.google.com/search/docs/crawling-indexing/robots/intro).
- A canonical is a hint. Redirects and `rel=canonical` are strong signals, sitemap inclusion a weak one. Google recommends a self-referencing canonical with absolute URLs (https://developers.google.com/search/docs/crawling-indexing/consolidate-duplicate-urls).
- A sitemap file holds up to 50,000 URLs or 50 MB (https://developers.google.com/search/docs/crawling-indexing/sitemaps/build-sitemap).
- A `noindex` in the first HTML can stop Google rendering the page, so a script cannot remove it later (https://developers.google.com/search/docs/crawling-indexing/javascript/javascript-seo-basics).
- JSON-LD is recommended; correct markup does not guarantee a rich result (https://developers.google.com/search/docs/appearance/structured-data/sd-policies).
- Site moves: map old to new URLs, use permanent server-side redirects, avoid chains, keep redirects for at least a year, and remove development `noindex` rules and robots.txt blocks at launch (https://developers.google.com/search/docs/crawling-indexing/site-move-with-url-changes).

**Search Console**

- Performance report: clicks, impressions, click-through rate and average position; the default view is three months (https://support.google.com/webmasters/answer/7576553).
- Branded queries filter: announced 20 November 2025; available to all eligible sites from 11 March 2026 (https://developers.google.com/search/blog/2025/11/search-console-branded-filter). A branded query contains the brand name, variations or misspellings, or brand-related products. Classification is by an internal AI-assisted system, not a pattern. It is offered on top-level properties with enough volume. Google says some queries may be misclassified and that the filter has no effect on ranking. Cite the blog post for dates; the help pages disagree with it.
- The Search Console collection in Google Analytics lists landing pages with search clicks beside analytics metrics, holds 16 months and lags 48 hours (https://support.google.com/analytics/answer/10737381).

The other research notes in this folder cover AI measurement, crawlers and agents, industrial buyers and LinkedIn.

## 5. Specifications for the six remaining articles

## A1. AI Citations vs Website Traffic: What B2B Marketers Should Measure

**Type:** new article. **Proposed slug:** /blog/ai-citations-vs-website-traffic/ (not live). **Length:** 2,000 to 2,600 words.

**Brief.** The reader has an AI visibility number (citations, mentions or "share of answer") and a much smaller number of visits from AI assistants, and has to explain the difference to a founder or a finance lead. After reading, they can say what each number proves, set up the tracking that is possible, and report AI search without overstating it.

**Unique angle.** Setup guides for tracking AI traffic are everywhere. What is missing is interpretation: a ladder of six measurements from "mentioned" to "revenue", each with its data source, how far it can be trusted, and the wrong conclusion people draw from it.

**Search intent.** Informational with a how-to section. Phrases seen: "ai referral traffic ga4", "how to see ai referral traffic in ga4", "ai citations vs mentions", "ai search referral traffic". Primary phrase: AI citations vs website traffic; secondary: AI referral traffic in GA4. It must not target "measure AI search visibility" (R1).

**Questions the article answers**
1. What is the difference between a mention, a citation and a visit?
2. Why are citations high and AI referral visits low on the same site?
3. Which AI visits can analytics see, and which arrive looking like direct traffic?
4. How do you set up GA4 to separate AI assistants from other referrals?
5. What can Search Console and Bing Webmaster Tools show about AI answers, and what can they not?
6. How do you connect AI visibility to enquiries without claiming more than the data supports?
7. What belongs in a report to leadership?

**Outline**
- Opening: one site, two numbers. Thousands of citations in AI answers, and a handful of visits from AI assistants. Both are true, and they measure different things.
- **The short answer** (answer box).
- **Six measurements, from mention to revenue.** The ladder as a table: mentioned in an answer, cited with a link, referral visit, enquiry or assisted conversion, sales opportunity, revenue. Columns: where the number comes from, whether it is counted, sampled or inferred, and the mistake it invites.
- **Why citations and visits diverge.** The answer often satisfies the reader without a click; some assistants and apps pass no referrer, so visits appear as direct; some answers name a brand without a link; buyers come back later through a branded search.
  - *From published work:* FirstChoice Used Auto Parts recorded 10.7K citations in Copilot and Bing AI answers in September 2026 (Bing Webmaster Tools), beside 2.2K Google clicks in three months. *Evidence slot:* its AI referral visits for the same period, if supplied.
- **What each tool can show.** Bing Webmaster Tools: citations, as Microsoft defines them. Search Console: how Google counts AI features in the Performance report. GA4: referral visits from assistants that pass a referrer or a tag. Server logs: crawler and fetcher requests, which are not visits by people.
- **Setting up AI referrals in GA4.** The steps, the sources to include, the order of channel rules, and what will still be missing. (Written against Google's current documentation, including any built-in channel.)
  - *How our own enquiry system handles it:* the site records the first and latest source of every enquiry and groups ChatGPT, Perplexity, Gemini, Copilot, Claude and others as "AI assistants", so an enquiry can be traced to an assistant when the visit carried a referrer or a tag.
- **From visibility to enquiries, honestly.** Three checks that support a connection without proving it: branded search trend, self-reported "how did you hear about us", and enquiries whose first source was an assistant. What none of them proves.
- **Four conclusions the data does not support.** "Citations are traffic." "No AI visits means no AI influence." "AI visits convert better" (stated only if a primary study supports it, with its limits). "Our share of answer is our market share."
- **A report in five lines.** Citations or share of answer with sample size; AI referral visits; enquiries with an AI first source; branded search trend; one action.
- FAQs (5), closing, CTA, author box.

**Original method.** The six-step ladder with a trust grade for each measurement, and the five-line report.

**Evidence.** On file: the FirstChoice citation and click figures (published); the enquiry system's AI assistant grouping (in the site's code). Missing: AI referral visits and enquiries for FirstChoice or vturnu.com. Without them the divergence is explained but not shown with a real pair of numbers.

**Sources to verify and cite:** 
- GA4 default channel group, including the AI Assistant channel. https://support.google.com/analytics/answer/9756891 (official page read).
- GA4 release notes (AI Assistant channel 13 May 2026, Source Group 11 June 2026). https://support.google.com/analytics/answer/9164320 (official page read).
- GA4 custom channel groups (order, limits, retroactive use). https://support.google.com/analytics/answer/13051316 (official page read).
- Google: AI features and your website. https://developers.google.com/search/docs/appearance/ai-features (official page read).
- Search Console: how impressions, clicks and position are counted, AI features included. https://support.google.com/webmasters/answer/7042828 (official page read).
- Search Console: Generative AI performance report. https://developers.google.com/search/blog/2026/06/gen-ai-performance-reports (official page read).
- Bing Webmaster Tools: AI Performance (public preview). https://blogs.bing.com/webmaster/February-2026/Introducing-AI-Performance-in-Bing-Webmaster-Tools-Public-Preview (official page read).
- OpenAI publisher FAQ on utm_source=chatgpt.com. https://web.archive.org/web/20261004224023/https://help.openai.com/en/articles/12627856-publishers-and-developers-faq (archived copy read; the live page now redirects).
- Ahrefs: AI assistants vs search, share of visits across 118,671 sites. https://ahrefs.com/benchmarks/ai-vs-search (vendor data, page read).
- Kaiser and Schulze, Marketing Science (2026): LLM referrals on 973 e-commerce sites. https://doi.org/10.1287/mksc.2025.0489 (peer reviewed; details from the record and the authors, article page not read).
- Microsoft Clarity: AI traffic and sign-up rates on publisher sites. https://clarity.microsoft.com/blog/ai-traffic-converts-at-3x-the-rate-of-other-channels-study/ (vendor data, page read).

**Internal links out (verified):** /blog/how-to-measure-ai-search-visibility/ (how to sample), /case-studies/firstchoice-used-auto-parts/, /blog/marketing-attribution-for-long-sales-cycles/ (self-reported attribution), /ai-seo/, /google-ai-overviews-seo/. **Planned:** A10, A5, A2 (logs).
**Reverse links after publication:** from R1 (step 4), /blog/seo-vs-aeo-vs-geo-explained-for-business-leaders/ ("How to measure AI visibility"), /blog/how-to-rank-in-google-ai-overviews/ (step four), the attribution article (the FAQ on attributing AI search), /ai-seo/ (the FAQ "How do you measure results?").

**CTAs**
- Middle, after "what each tool can show": "See what AI assistants say about your company today", destination /ai-seo/#enquire (its live offer is a free AI visibility report).
- End: main CTA to /contact-us/, secondary to /ai-seo/.

**Visuals**
- Featured image: new concept "ladder" (six rungs, the lower ones solid and the upper ones dotted).
- Figure 1: the ladder as a designed diagram, with "counted, sampled, inferred" marked.
- Figure 2: citations beside visits for one site. Real data only; omitted if the visits figure is not supplied.
- A code-style block for the GA4 channel rule, with a copy button.


**Guardrails.** No traffic or conversion statistics without a primary study, its sample and its sponsor. No claim that citations lead to revenue. Tool behaviour is described as documented on the date checked.

---

## A8. Search Visibility vs Revenue: Four Tests for Whether SEO Is Creating Demand

**Type:** new article. **Proposed slug:** /blog/search-visibility-vs-revenue-demand/ (not live). **Length:** 1,800 to 2,300 words.

**Brief.** The reader is a founder, CMO or head of growth looking at a Search Console chart that goes up while revenue does not. After reading, they can run four tests on their own data and decide whether to invest more in SEO, change what it targets, or fix what happens after the click.

**Unique angle.** The site already argues that leads matter more than traffic in two places. This article does not repeat the argument. It is a test procedure with a decision at the end: four questions, each answered from a named report, each with a pass mark the reader sets for their own business.

**Search intent.** Informational, decision support. Phrases seen: "branded vs non branded search", "organic search branded vs non branded", "demand capture vs demand generation". Primary phrase: branded vs non-branded search (as the first and most concrete test). It must not target "traffic but no leads" (existing post) or "measure qualified leads" (A5).

**Questions the article answers**
1. Is this visibility new demand, or people who already knew the company?
2. What do the non-branded searches actually ask for?
3. Are the searchers the kind of company that buys?
4. Do the enquiries turn into sales conversations?
5. What should change, depending on which test fails?

**Outline**
- Opening: a chart that rises and a pipeline that does not. Visibility is a count of appearances; demand is a count of buyers. The article gives four tests to tell them apart.
- **The short answer** (answer box).
- **Test 1: branded or non-branded.** How to split queries with the branded queries filter in Search Console, what the split means, and its limits. *Example from published work:* Cafy had 14.8K clicks and a 51.7 percent click-through rate in three months, and most of it was people searching for the brand by name, which is why its content plan targets non-branded demand next.
- **Test 2: what the non-branded searches want.** Sorting queries into four groups (learn, compare, find a supplier, buy or enquire) and reading the mix. *Example:* Sai Impression's commercial queries such as "label manufacturers in india".
- **Test 3: whether the searchers fit.** Country, company type and the pages they land on. What Search Console and GA4 can and cannot show about who is searching.
- **Test 4: whether enquiries become conversations.** Qualified lead rate by landing page (one paragraph, then a link to A5 for the method).
- **Reading the four results together.** A decision table: which tests failed, what it means, what to do. For example: branded only and enquiries fine means the brand is known and search is not yet reaching new buyers; non-branded but only "learn" queries means content without a commercial layer.
- **What a good report shows.** Three lines for leadership: new demand reached, qualified enquiries from it, the one thing being changed.
- FAQs (4), closing, CTA, author box.

**Original method.** The four tests and the decision table. The tests use only reports the reader already has.

**Evidence.** On file: the published Cafy and Sai Impression lines. Missing: Cafy's actual branded and non-branded click split (the published text says "most"). If it arrives with approval, Test 1 gets a real chart; if not, the published sentence is used as is.

**Sources to verify and cite:** 
- Search Console: Performance report metrics. https://support.google.com/webmasters/answer/7576553 (official page read).
- Google: branded queries filter in Search Console. https://developers.google.com/search/blog/2025/11/search-console-branded-filter (official page read).
- GA4: Search Console integration. https://support.google.com/analytics/answer/10737381 (official page read).

**Internal links out (verified):** /case-studies/cafy-careers-uk-job-platform/, /case-studies/sai-impression-label-manufacturer/, /blog/why-your-website-gets-traffic-but-no-leads/ (when the fault is on the page), /updates/november-core-update-2026/ (comparing weeks fairly), /seo-services/. **Planned:** A5 (the method behind Test 4).
**Reverse links after publication:** from /blog/seo-vs-ppc-which-drives-better-roi/, /blog/benefits-of-hiring-an-seo-agency/, the SelectedFirms recap, /seo-services/.

**CTAs**
- Middle, after Test 2: the free audit tool is not a fit here. Use a short text prompt instead: "Want the four tests run on your Search Console data?" linking to /seo-services/#enquire.
- End: main CTA to /contact-us/, secondary to /seo-services/.

**Visuals**
- Featured image: new concept "split" (one bar in two colours, the larger part labelled "already knew you").
- Figure 1: branded vs non-branded clicks as a two-part bar. Real data only if supplied; otherwise the figure is omitted, not invented.
- Figure 2: the decision table as a designed grid (four tests across, outcomes down).


**Guardrails.** No claim that non-branded traffic is always better. No promise that passing the tests raises revenue. The filter's limits are stated as Google states them.

---

## A6. Search-Ready WordPress for B2B: A Technical Checklist Before You Launch

**Type:** new article, interactive checklist. **Proposed slug:** /blog/wordpress-seo-launch-checklist/ (not live). **Length:** 2,200 to 2,800 words.

**Brief.** The reader is about to launch or relaunch a B2B website on WordPress: a marketing manager signing off the build, or the developer who built it. After reading, they have a pass or fail result for each technical check, and a short list of what must be fixed before the site goes live.

**Unique angle.** WordPress SEO articles tend to be plugin lists. This one is a launch gate: fourteen checks on the built site, each with how to test it, what WordPress does by default, and what breaks it. It never recommends a plugin where core already does the job, and it says which checks need a developer.

**Search intent.** Technical checklist. Phrases seen: "wordpress on page seo checklist", "core web vitals assessment failed wordpress", "is wordpress good for seo". Primary phrase: WordPress SEO checklist before launch. It must not target "WordPress web design company" (the service page) or "redesign without losing SEO" (existing post).

**Questions the article answers**
1. Is the site actually open to search engines on launch day?
2. Does each page have one address, one title and one canonical?
3. Is the important text in the HTML, or added later by scripts?
4. Which pages does WordPress create on its own that should not be indexed?
5. Are the sitemap and robots.txt coming from one place?
6. Will the site stay fast once real plugins and tracking are on it?
7. Do the forms deliver, and are enquiries tracked?

**Outline**
- Opening: most WordPress launch problems are settings, not code: one checkbox left on from staging, two plugins writing the same tag, a page builder that hides the text.
- **The short answer** (answer box).
- The checklist at a glance, then the progress board (pass, fail, not sure, with a "fix these first" list).
- **Fourteen checks.** Each has: why it matters, how to check, what to do.
  1. The "discourage search engines" setting is off in production and on in staging.
  2. Staging is closed to crawlers and people, and nothing links to it.
  3. Permalinks are final, and every old URL has a redirect.
  4. Each page outputs one canonical, pointing to itself.
  5. One source for titles and descriptions; no duplicates from theme and plugin.
  6. One XML sitemap, listing only pages meant to be indexed, submitted in Search Console.
  7. robots.txt is the intended one, and blocks nothing needed to render pages.
  8. Thin pages WordPress creates (attachment, author, tag and date archives, internal search) are handled on purpose.
  9. The main text and links are in the HTML source, not added only by scripts.
  10. One H1 per page, headings in order, images with useful alt text.
  11. Structured data comes from one place and matches what the page shows.
  12. Speed is checked with real plugins, tracking and consent tools switched on; field data is reviewed after launch.
  13. Forms deliver to the right inbox, spam is filtered without losing real leads, and submissions are tracked.
  14. Updates, backups and access are set up, and unused plugins and themes are removed.
- **After launch: the first four weeks.** What to watch in Search Console and analytics, and when.
- **From a real rebuild.** FirstChoice Used Auto Parts runs on WordPress. Its 99 rebuilt pages were checked one by one for title, canonical, a single H1, a working form and valid FAQ markup, and its URLs did not change.
- **What you can skip.** Settings and plugins that are often recommended and rarely needed.
- FAQs (5), closing, CTA, author box.

**Original method.** The fourteen-point launch gate with a pass or fail record, usable as a sign-off sheet between a client and a developer.

**Evidence.** On file: the FirstChoice rebuild facts (published). vturnu.com is not built on WordPress, and the article does not suggest it is.

**Sources to verify and cite:** 
- WordPress releases (7.1.4, 28 October 2026). https://wordpress.org/download/releases/ (official page read).
- WordPress: Reading settings and the search engine visibility option. https://wordpress.org/documentation/article/settings-reading-screen/ (official page read).
- WordPress core: XML sitemaps since 5.5. https://make.wordpress.org/core/2020/07/22/new-xml-sitemaps-functionality-in-wordpress-5-5/ (official page read).
- WordPress core: virtual robots.txt. https://developer.wordpress.org/reference/functions/do_robots/ (official source read).
- WordPress core: canonical tag on single views. https://developer.wordpress.org/reference/functions/rel_canonical/ (official source read).
- WordPress core: speculative loading since 6.8. https://make.wordpress.org/core/2025/03/06/speculative-loading-in-6-8/ (official page read).
- WordPress: Site Health screen. https://wordpress.org/documentation/article/site-health-screen/ (official page read).
- Google: block indexing with noindex. https://developers.google.com/search/docs/crawling-indexing/block-indexing (official page read).
- Google: canonical URLs. https://developers.google.com/search/docs/crawling-indexing/consolidate-duplicate-urls (official page read).
- Google: build and submit a sitemap. https://developers.google.com/search/docs/crawling-indexing/sitemaps/build-sitemap (official page read).
- Google: site moves with URL changes. https://developers.google.com/search/docs/crawling-indexing/site-move-with-url-changes (official page read).
- Google: structured data guidelines. https://developers.google.com/search/docs/appearance/structured-data/sd-policies (official page read).

**Internal links out (verified):** /wordpress-web-design/, /blog/website-redesign-without-losing-seo/ (redirect maps), /blog/schema-markup-for-b2b-websites/ (check 11), /blog/website-accessibility-checklist/ (check 10), /blog/core-web-vitals-guide-for-business-owners/ (check 12), /blog/how-to-stop-junk-leads-on-your-website/ (check 13), /blog/website-design-brief-questions/, /case-studies/firstchoice-used-auto-parts/, /free-seo-audit/. **Planned:** A2 (check 9), A4 (check 12), A7.
**Reverse links after publication:** from /wordpress-web-design/ (the FAQ on migrating without losing rankings), the redesign article ("launch week"), the design brief article (the requirements question).

**CTAs**
- Middle, after check 7: the free audit tool, /free-seo-audit/, to test one page's basics.
- End: "Want a second pair of eyes before you launch?", destination /wordpress-web-design/#enquire (its live offer is a free website review).

**Visuals**
- Featured image: the existing "brief" concept is close; a new "launch" concept (a checklist with a switch moving from staging to live) would be clearer.
- Figure 1: where each check sits on the timeline from staging to four weeks after launch.
- Commands and code samples with copy buttons, each tested on a current WordPress install before publication.

**Guardrails.** Every statement about what WordPress does by default is checked against WordPress documentation for the current release, and the release is named. No plugin is called essential. Nothing is described as tested unless it was run.

---

## A10. How to Benchmark Competitors in AI Search: A Repeatable Scorecard

**Type:** new article. **Proposed slug:** /blog/ai-search-competitor-benchmark/ (not live). **Length:** 1,800 to 2,400 words.

**Brief.** The reader already samples their own visibility (R1) and has been asked "how do we compare with our competitors in ChatGPT?". After reading, they can run a fair comparison, read it without over-claiming, and find the outside pages that get competitors recommended.

**Unique angle.** Most guides on this are written by companies selling tracking tools. This one is a manual method anyone can run in a spreadsheet, and it is honest about sample size: it shows how large a gap has to be before it means anything.

**Search intent.** How-to. Search evidence is the weakest of the ten: no autocomplete phrases for competitor wording, and results dominated by tool vendors. Primary phrase: AI search competitor analysis. It must not target "measure AI search visibility" (R1) or "AI visibility tools".

**Questions the article answers**
1. Which competitors belong in the comparison?
2. How do you write prompts that do not favour your own brand?
3. What do you score for each brand?
4. How big does a difference have to be before it is real?
5. Which outside pages are getting competitors named?
6. What do you report, and how often?

**Outline**
- Opening: "we appear in 40 percent of answers and they appear in 55" means little until you know how many answers were counted.
- **The short answer** (answer box).
- **Choose a fair set.** Three to five direct competitors for the same buyer and market, one larger name for reference, and a rule for adding brands the assistants name that you did not list.
- **Write neutral prompts.** No brand names in category prompts; buyer wording; three groups (shortlist, comparison, problem). A before-and-after table of biased and neutral prompts.
- **Score four things per brand.** Named or not, cited with a link, described accurately, and recommended or only mentioned. One scorecard table. Do not score "named first": the January 2026 variability study found position close to random between runs.
- **How much difference is real.** A table of sample sizes and the gap needed before two brands can be told apart, with the arithmetic shown. For example, with 90 answers a gap under roughly 15 points is within noise. The caveat that repeated runs are not fully independent is stated.
- **Find the sources behind the names.** Collect the links cited when a competitor is named, sort them (the competitor's own site, directories and lists, review sites, forums, news), and turn the result into a short list of places to earn a mention.
- **Engines disagree, and that is information.** Report each engine separately before any total.
- **A one-page report.** The scorecard, the sample size, what changed, three actions.
- **What this cannot tell you.** No market share, no ranking, no proof of why a brand was named.
- FAQs (4), closing, CTA, author box.

**Original method.** The four-part scorecard, the sample-size table (computed for the article) and the source sort.

**Evidence.** None on file. Option: run a small, dated sample for one category and publish it with the full method. By default the example uses an unnamed category and invented brand labels, marked as an illustration, until you say whether competitors may be named.

**Sources to verify and cite:** 
- SparkToro and Gumshoe variability study (January 2026). https://sparktoro.com/blog/new-research-ais-are-highly-inconsistent-when-recommending-brands-or-products-marketers-should-take-care-when-tracking-ai-visibility/ (primary study, read through an archive copy).
- Bing Webmaster Tools: Citation Share and Compare (June 2026). https://blogs.bing.com/search/June-2026/New-AI-Visibility-Insights-in-Bing-Webmaster-Tools-Intents-Topics-Citation-Share-Compare (official page read).
- The margin of error arithmetic. computed for the article from the standard formula for a proportion (own calculation, shown in full).

**Internal links out (verified):** /blog/how-to-measure-ai-search-visibility/ (the sampling routine), /ai-seo/, /guides/complete-guide-to-ai-seo/, /blog/chatgpt-seo-how-brands-get-recommended/ (corroboration). **Planned:** A1.
**Reverse links after publication:** from R1 (step 5), the AI SEO guide's measurement chapter, /ai-seo/.

**CTAs**
- Middle: none.
- End: "See who AI assistants name instead of you", destination /ai-seo/#enquire (free AI visibility report).

**Visuals**
- Featured image: new concept "scorecard" (four brand columns with ticks, one column highlighted).
- Figure 1: the scorecard, as an example with invented brands, labelled.
- Figure 2: the sample-size table as a chart: gap needed against number of answers.

**Guardrails.** No named competitor without your approval. No claim that the method predicts recommendations. Every percentage in an example carries its sample size.

---

## A9. Why LinkedIn Engagement Is Not the Same as B2B Buyer Intent

**Type:** new article, research-based. **Proposed slug:** /blog/linkedin-engagement-vs-buyer-intent/ (not live). **Length:** 1,700 to 2,200 words.

**Brief.** The reader runs B2B marketing and reports LinkedIn in impressions, reactions and followers. Sales asks which of those people want to buy. After reading, they can sort LinkedIn signals into weak, moderate and strong, score them by account, and report LinkedIn in a way sales accepts.

**Unique angle.** The article does not say engagement is worthless. It shows who engages with B2B posts, why most of them are not buyers today, and which observable signals do move with buying interest, using LinkedIn's own definitions. It is written without first-person claims, because there is no first-hand data on file.

**Search intent.** Informational. Phrases seen: "linkedin engagement rate formula", "linkedin engagement rate benchmark", "linkedin sales navigator buyer intent", "how does sales navigator determine buyer intent", "linkedin engagement vs impression". Primary phrase: LinkedIn engagement vs buyer intent.

**Questions the article answers**
1. How does LinkedIn define engagement and engagement rate?
2. Who actually reacts to and comments on B2B posts?
3. Why is a buyer who is not in the market today still worth reaching?
4. Which LinkedIn signals suggest buying interest, and which do not?
5. What does Sales Navigator's Buyer Intent measure, and what are its limits?
6. How can engagement be tied to accounts and to pipeline?

**Outline**
- Opening: a post with 300 reactions and no enquiries, and a post with 12 reactions that started a deal. The difference is who reacted and what they did next.
- **The short answer** (answer box).
- **What LinkedIn counts as engagement.** The definitions and formulas as LinkedIn publishes them, for organic posts and for ads.
- **Who engages.** Peers, competitors, job seekers, suppliers, your own team, and some buyers. Why a reaction says "this was interesting" and not "we are buying".
- **Most buyers are not in the market today.** The 95-5 idea from the LinkedIn B2B Institute, stated as its authors state it, and what it means for reading engagement: engagement builds memory, intent shows up later and elsewhere.
- **Signals, from weak to strong.** A table with three bands. Weak: impressions, reactions, follower growth. Moderate: several people from one target account engaging over weeks, company page visits from target accounts, clicks to a service page. Strong: a reply to a message, a visit to pricing or contact pages from the account, a demo or quote request, a rise in Sales Navigator's intent level. Each row says what it shows and what it does not.
- **Examples.** Six short cases of engagement that is and is not intent (for example, a comment from a competitor's marketer; three engineers from one target account opening the same guide in a week). Labelled as illustrations.
- **Score accounts, not posts.** A simple account sheet: target account, people engaged, signals seen, strongest signal, next step. How to keep it in a CRM or a spreadsheet.
- **What Sales Navigator's Buyer Intent tells you.** What feeds it, the levels, the time window and the plans that include it, as LinkedIn documents them, and why it is a prompt for a conversation, not proof.
- **Connecting LinkedIn to pipeline.** Self-reported attribution, account matching in the CRM, and LinkedIn's own conversion and revenue reports, with their requirements and limits.
- FAQs (4), closing, CTA, author box.

**Original method.** The three-band signal table and the account sheet.

**Evidence.** None first-hand. All claims rest on LinkedIn's documentation and primary research read in full.

**Sources to verify and cite:** 
- LinkedIn Help: content analytics for a Page (engagement rate). https://www.linkedin.com/help/linkedin/answer/a564051 (official page read).
- LinkedIn Help: engagement metrics in Campaign Manager. https://www.linkedin.com/help/lms/answer/a422348 (official page read).
- LinkedIn Help: Buyer Intent FAQ in Sales Navigator. https://www.linkedin.com/help/sales-navigator/answer/a507435 (official page read; LinkedIn stamps it about three years old).
- LinkedIn Help: Companies Hub metrics. https://www.linkedin.com/help/lms/answer/a7109308 (official page read).
- LinkedIn Help: Conversions API. https://www.linkedin.com/help/lms/answer/a1655394 (official page read).
- LinkedIn Help: Revenue Attribution Report. https://www.linkedin.com/help/lms/answer/a715191 (official page read).
- John Dawes, B2B Institute and Ehrenberg-Bass: the 95-5 rule (2021). https://business.linkedin.com/content/dam/me/business/en-us/marketing-solutions/resources/pdfs/advertising-effectiveness-and-the-95-5-rule.pdf (primary paper read in full).
- LinkedIn Help: how content is distributed in the feed. https://www.linkedin.com/help/linkedin/answer/a516930 (official page read).
- Dreamdata: LinkedIn Ads Benchmarks Report 2026. https://dreamdata.io/blog/announcing-linkedin-ads-benchmarks-report-2026 (vendor research, summary page read, full report gated).

**Internal links out (verified):** /blog/account-based-marketing-for-mid-market-companies/ (choosing and measuring accounts), /blog/linkedin-ads-vs-google-ads-for-lead-generation/ (paid choice), /blog/marketing-attribution-for-long-sales-cycles/ (self-reported attribution), /blog/social-media-content-strategy-for-business/ (what to post), /social-media-marketing/, /outbound-marketing/. **Planned:** A5.
**Reverse links after publication:** from the social content strategy post ("converting attention"), the ABM article (step 5), /social-media-marketing/ (the FAQ on measuring social media).

**CTAs**
- Middle: none. The article is short and the sections are tightly linked.
- End: "See which of your LinkedIn signals point to real accounts", destination /social-media-marketing/#enquire (its live offer is a free social media audit).

**Visuals**
- Featured image: new concept "signal" (a row of reaction icons fading into one highlighted account card).
- Figure 1: the three-band signal table as a designed graphic.
- Figure 2: the account sheet, shown as an example with invented account names and labelled as such.

**Guardrails.** No engagement benchmarks unless a primary source with a method is found. No claim about how LinkedIn's feed or intent score works beyond what LinkedIn documents. Vendor research is labelled as vendor research.

---

## A3. How Industrial Buyers Search: Mapping Their Queries to the Pages a Manufacturer Needs

**Type:** new article. **Proposed slug:** /blog/industrial-buyer-search-intent/ (not live). **Length:** 2,000 to 2,600 words.

**Write the research-based version.** No client query data is on file. Background on the two versions: If the client data arrives, the article leads with what a 105-page label manufacturer site shows, and the title becomes "How Industrial Buyers Search: Lessons From a 105-Page Label Manufacturer Website". If it does not, the article is research-based, with the published Sai Impression facts as one example and no "lessons from" claim. The outline below works for both; the evidence slots are marked.

**Brief.** The reader markets a manufacturer, an industrial distributor or an engineering services firm. Their site has a home page, an "our products" page and a brochure PDF. After reading, they know the kinds of search an engineer or a buyer makes at each stage, and which page has to exist to answer each one.

**Unique angle.** Industrial SEO advice is usually a list of tactics. This article is a map: six families of search, the buyer behind each, and the page type that answers it, with what that page must contain. It treats specification data as the content, not as an attachment.

**Search intent.** Informational. "Industrial SEO" is a buying phrase that belongs to the service page /lead-gen-seo-for-business-industrial/, so this article targets how industrial buyers search. The phrasing is unvalidated: my autocomplete seeds returned almost nothing, and more seeds will be tested before drafting.

**Questions the article answers**
1. How much research does an industrial buyer do before contacting a supplier?
2. What do engineers type, as opposed to purchasing staff?
3. Which searches signal a buyer close to a request for quotation?
4. Which page should answer each kind of search?
5. What has to be on a product or capability page for it to be found and trusted?
6. How should enquiries from these pages be measured?

**Outline**
- Opening: the engineer who needs a part rarely searches for a company. They search for a specification, an application or a problem. The article maps those searches to pages.
- **The short answer** (answer box).
- **Buyers research before they call.** What current surveys of engineers and industrial buyers report, with sample sizes and dates, and one line on how AI tools now sit beside search. (Figures only from the reports read in full.)
- **Six families of industrial search.** Table: specification ("material, size, tolerance, standard"), application ("part for a use"), supplier ("manufacturer or supplier plus place"), compliance ("standard or certificate"), problem ("why something fails"), commercial ("minimum order, lead time, price basis"). Columns: who searches, what they need to see, how close to an enquiry.
  - *Evidence slot:* which families the Sai Impression site earns impressions for, by page type. Today only two supplier-type queries are published ("label manufacturers in india", "label stock manufacturers in india").
- **The page that answers each search.** Product family page, application page, capability and machinery page, certification page, technical article, RFQ page. For each: what it must contain and one common mistake (specifications locked in a PDF, one page for forty products, a contact form in place of an RFQ form).
- **What a product page needs to be read by a person and a machine.** Specifications as text and tables, units, standards, variants, documents, a clear enquiry route. Link to the product description service only where it fits.
- **Country and language searches.** When "in India" or "in Germany" changes the page that should rank, and when it does not.
- **Measuring enquiries from these pages.** RFQs by landing page and by search family, not total traffic. Link to A5.
- FAQs (4), closing, CTA, author box.

**Original method.** The search-to-page map, usable as a gap check: list your pages against the six families and see which searches have nowhere to land.

**Evidence.** On file: the Sai Impression case study (105 pages in the sitemap, 92.9K impressions and 1.32K clicks in the three months to 29 October 2026, two commercial queries, 15 articles). Missing: Search Console data by query and page, enquiries by landing page, and the client's approval.

**Sources to verify and cite:** 
- TREW Marketing and GlobalSpec: 2026 State of Marketing to Engineers. https://advertising.globalspec.com/wp-content/uploads/2026/03/SMTE-Report_GS2602_FINAL.pdf (full report read; vendor research, field dates not stated).
- TREW Marketing and GlobalSpec: 2025 edition (most valuable content). https://www.trewmarketing.com/hubfs/Technical%20Resources/Research/2025%20SMTE%20Research/PDF/2025%20State%20of%20Marketing%20to%20Engineers%20Research.pdf (full report read).
- RH Blake with ISA and Automation.com: 2026 B2B manufacturing buying journey (121 respondents). https://rhblake.com/manufacturing-buying-journey/ (official summary read; report gated, small sample).
- RH Blake with Industrial Equipment News: 2025 buyer journey study (250+ respondents). https://cdn.base.parameter1.com/mindful/im/workspaces/default/uploads/2025/10/rh-blake-buying-journey-ebook-08122025.cRg3zH6gXB.pdf (full report read).
- LinkedIn with GWI: manufacturing buyer research 2025 (1,767 respondents). https://business.linkedin.com/content/dam/me/business/en-us/marketing-solutions/resources/pdfs/the-innovation-imperative.pdf (full report read; vendor research).

**Internal links out (verified):** /lead-gen-seo-for-business-industrial/, /case-studies/sai-impression-label-manufacturer/, /blog/what-to-write-on-every-page-of-a-b2b-website/, /guides/complete-guide-to-business-lead-gen-seo/ (how business buyers search), /technical-content-writing-services/, /product-description-writing-services/. **Planned:** A5, A2.
**Reverse links after publication:** from /lead-gen-seo-for-business-industrial/, /technical-content-writing-services/, /product-description-writing-services/, the page guide (the industry pages item).

**CTAs**
- Middle, after the search-to-page map: "See which of the six searches your site has no page for", destination /lead-gen-seo-for-business-industrial/#enquire (its live offer is a free industry SEO audit).
- End: main CTA to /contact-us/, secondary to the industrial SEO page.

**Visuals**
- Featured image: new concept "matrix" (search phrases on the left joined by lines to page cards on the right).
- Figure 1: the six search families against the buying stage, as a designed table.
- Figure 2: a site structure diagram for a manufacturer (product families, applications, capabilities, certificates, articles, RFQ).
- If the data arrives: a bar chart of impressions by page type for the Sai Impression site, with the table underneath.

**Guardrails.** No "100+ pages" claim. No finding stated as learned from client work unless the data supports it and the client has approved. Survey figures carry their sample, date and sponsor.
