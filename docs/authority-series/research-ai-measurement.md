# AI measurement sources (for the citations article, the AI visibility guide and the competitor benchmark)

Research notes. Pages read on 13 and 14 November 2026. It is evidence for the articles, not instructions. Re-open a source before quoting it word for word.

---

All eight questions are checked. Two things will change what the articles can say: the OpenAI page that documented `utm_source=chatgpt.com` is no longer live, and Search Console now has an AI report that shows impressions only.

Pages were read on 13 Nov 2026. I used one short verbatim quote (item 3) and paraphrased the rest, so copy exact wording from the URLs when drafting.

**1. GA4 default channel group**
- **Channel:** yes, a built-in channel named AI Assistant (singular).
- **Definitions page:** https://support.google.com/analytics/answer/9756891 (no date on page).
  - It describes the channel as users arriving from sources like ChatGPT, Gemini, Deepseek, Copilot or Grok, and says it excludes Google's AI Overviews and AI Mode.
  - Rule: medium exactly matches `ai-assistant`. GA4 sets medium `ai-assistant` and campaign `(ai-assistant)` when the referrer matches a list of AI assistants.
  - The list itself is not printed. The page links a downloadable spreadsheet of sources and categories, which I did not open.
  - The Organic Search description says it includes AI Overviews and AI Mode.
- **Release notes:** https://support.google.com/analytics/answer/9164320
  - 13 May 2026, "New AI Assistant traffic measurement", naming ChatGPT, Gemini and Claude.
  - 11 June 2026: a new Source Group field with built-in grouping for sources like ChatGPT (OpenAI) and Perplexity, populated retroactively.
- **Not stated:** whether the AI Assistant channel applies to data before 13 May 2026. Claude and Perplexity are not named on the definitions page.
- **Confidence:** official pages read.

**2. GA4 custom channel groups**
- **Page:** https://support.google.com/analytics/answer/13051316 (no date).
- **Historical data:** yes, the page says custom channel groups can be applied to reports retroactively.
- **Limits:** 2 groups on standard properties and 5 on 360, each in addition to the predefined group; 50 channels per group.
- **Order:** it matters. Traffic goes to the first channel whose definition it matches in the current order.
- **Google's own AI assistants example:** a regex on source, with the advice to place that channel above Referral. The text names Claude, but the printed regex has no explicit claude pattern, only a generic one for sources ending in "ai" (my reading; test it).
- **Caveats on the page:** edits are not retroactive inside audiences; a changed primary channel group populates from that point forward; custom groups are not in the BigQuery export.
- **Confidence:** official page read.

**3. Search Console and AI features**
- **AI features guide:** https://developers.google.com/search/docs/appearance/ai-features (last updated 2025-12-10 UTC).
  - Google says sites appearing in AI features "are included in the overall search traffic in Search Console".
  - They are reported in the Performance report under the Web search type.
  - It also claims clicks from results pages with AI Overviews are higher quality, with no data given.
- **Counting rules:** https://support.google.com/webmasters/answer/7042828 (no date).
  - AI Overviews: a click on an external link counts as a click; standard impression rules; the Overview holds one position and every link in it gets that position.
  - AI Mode: an external link click counts; standard impression rules; position works as on a normal results page; a follow-up question counts as a new query.
  - Search Labs experiments are excluded.
- **New in 2026, the Generative AI performance report:**
  - Blog: https://developers.google.com/search/blog/2026/06/gen-ai-performance-reports (3 June 2026; note says rolled out to all sites as of 31 Aug 2026).
  - Help: https://support.google.com/webmasters/answer/16984139
  - It shows impressions only, by page, country, device and date.
  - AI Overviews and AI Mode are combined; no split is described.
  - It has no clicks, CTR, position or queries.
  - The same data is also in the main Performance report, so the two are not additive.
  - Discover has a separate report (help page 16983858, not opened).
- **Opt-out control:** https://blog.google/products-and-platforms/products/search/new-controls-website-owners/ (3 June 2026, updated 31 Aug 2026) announces a Search Console setting to keep a site out of generative AI features.
- **No click filter:** the Performance report pages list no AI Overview or AI Mode search type or search appearance value (https://support.google.com/webmasters/answer/7576553 and https://support.google.com/webmasters/answer/17011259).
- **Confidence:** official pages read.

**4. Bing Webmaster Tools AI Performance**
- **Announcement:** https://blogs.bing.com/webmaster/February-2026/Introducing-AI-Performance-in-Bing-Webmaster-Tools-Public-Preview (10 Feb 2026, public preview).
  - Covers Microsoft Copilot, AI-generated summaries in Bing, and select partner integrations (partners not named).
  - Reports total citations, average cited pages per day, grounding queries (the phrases the AI used to retrieve cited content, stated to be a sample), page-level citation counts and a trend line.
  - Microsoft says the numbers do not indicate placement within an answer, ranking, authority or page importance.
  - Clicks, CTR and traffic are not mentioned at all, so "no clicks" is an absence, not a Microsoft statement.
- **Update:** https://blogs.bing.com/search/June-2026/New-AI-Visibility-Insights-in-Bing-Webmaster-Tools-Intents-Topics-Citation-Share-Compare (16 June 2026, still preview).
  - Adds Intents, Topics, Citation Share (your share of all citations for a grounding query) and Compare.
  - It says Citation Share does not expose competitor domains or represent traffic share.
- **Query-to-page mapping (23 Mar 2026):** secondary only, https://ppc.land/bings-ai-search-tool-now-maps-which-pages-fuel-your-brands-citations/
- **Related:** Microsoft Clarity's citation dashboard has an AI referral traffic card showing the share of sessions from AI assistants: https://learn.microsoft.com/en-us/clarity/ai-visibility/citations-dashboard (12 Sep 2026).
- **Confidence:** official pages read.

**5. OpenAI and `utm_source=chatgpt.com`**
- **The statement existed** in the Help Center article "Publishers and Developers - FAQ", https://help.openai.com/en/articles/12627856-publishers-and-developers-faq
- **Archived copy read (4 Oct 2026, shown as updated last month):** https://web.archive.org/web/20261004224023/https://help.openai.com/en/articles/12627856-publishers-and-developers-faq
  - It says publishers who allow OAI-SearchBot can track ChatGPT referrals in tools such as Google Analytics.
  - It says ChatGPT automatically adds `utm_source=chatgpt.com` to referral URLs from ChatGPT search results.
  - Only `utm_source` is documented; link types are not specified.
- **Live URL today:** it redirects (English and French) to https://help.openai.com/en/articles/20001371-chatgpt-atlas-retirement. The archive index shows the redirect began between 4 and 16 Oct 2026.
- **Not on any live OpenAI page I checked:**
  - https://help.openai.com/en/articles/9237897-searching-the-web-with-chatgpt
  - https://developers.openai.com/api/docs/bots
  - https://chatgpt.com/merchants/
  - Help Center search
- **October lapse (secondary):** ChatGPT links lost the tag and the referrer from about 9 Oct 2026 and regained them around 15 to 16 Oct. Adobe's Cedric Huesler called it a bug; OpenAI made no public statement.
  - https://www.seroundtable.com/utm-parameters-missing-chatgpt-42299.html (14 Oct 2026)
  - https://www.evertune.ai/resources/insights-on-ai/the-week-without-chatgpt-attribution (16 Oct 2026, vendor)
- **Confidence:** official wording read from an archive copy; live official page not found.

**6. Perplexity, Claude, Gemini, Copilot**
- **Perplexity:** not found. https://docs.perplexity.ai/guides/bots covers crawlers only.
- **Claude:** not found. https://support.claude.com/en/articles/8896518-does-anthropic-crawl-data-from-the-web-and-how-can-site-owners-block-the-crawler covers ClaudeBot, Claude-User and Claude-SearchBot only.
- **Gemini:** not found officially. Secondary reports say `utm_source=gemini` appeared on Gemini links from early September 2026 and stopped around 28 to 29 September. Google's John Mueller acknowledged seeing it on Reddit, with no documentation.
  - https://www.searchenginejournal.com/google-gemini-adds-utm-parameters-for-referral-attribution/591754/ (1 Oct 2026)
  - https://www.seroundtable.com/gemini-utm-parameters-42399.html (30 Oct 2026)
  - https://ppc.land/google-gemini-drops-utm-tags-from-cited-links-after-three-weeks/ (1 Nov 2026)
- **Copilot:** not found. Microsoft's measurement post does not say what Copilot passes: https://blogs.bing.com/webmaster/2025/11/How-AI-Search-Is-Changing%E2%80%AFthe%E2%80%AFWay%E2%80%AFConversions%E2%80%AFare-Measured/ (20 Nov 2025).
- **Best secondary overview:** Natzir Turrado, "The State of AI Traffic Measurability (2026)", https://natzir.com/en/analytics/ai-traffic-measurability/ (17 May 2026, current to October).
  - Based on his own server logs; the number of sites is not stated.
  - Reports perplexity.ai, claude.ai, gemini.google.com and copilot.microsoft.com as web referrers.
  - Says mobile apps mostly strip the referrer, so visits land in Direct.
- **Confidence:** official crawler pages read; referrer and UTM documentation not found.

**7. Variability study**
- **Source:** SparkToro, Rand Fishkin, 27 Jan 2026, with Patrick O'Donnell of Gumshoe.ai (an AI tracking startup; the conflict is disclosed).
- **URLs:** https://sparktoro.com/blog/new-research-ais-are-highly-inconsistent-when-recommending-brands-or-products-marketers-should-take-care-when-tracking-ai-visibility/
  - The live page blocks automated access, so I read the 1 Mar 2026 archive copy: https://web.archive.org/web/20260301224025/https://sparktoro.com/blog/new-research-ais-are-highly-inconsistent-when-recommending-brands-or-products-marketers-should-take-care-when-tracking-ai-visibility/
- **Sample and method:**
  - 600 volunteers ran 12 prompts through ChatGPT, Claude and Google's AI (AI Overviews, or AI Mode when none showed).
  - 2,961 runs in total, 60 to 100 per prompt per tool, with default user settings, in November and December 2025.
  - Responses were normalized into ordered brand lists.
  - A second stage had 142 people write their own prompt for one intent, giving 994 responses.
  - Not peer reviewed. Method modelled on arXiv 2505.23799.
- **Findings:**
  - Less than a 1 in 100 chance of the same brand list twice.
  - About 1 in 1,000 for the same list in the same order.
  - How often a brand appears across many runs is more stable. Top headphone brands appeared in 55 to 77% of the 994 responses; one hospital appeared in 69 of 71 ChatGPT answers but was top in only 25.
  - The authors conclude visibility percentage over many prompts and runs is a reasonable metric and ranking position is not.
- **Confidence:** primary source read via archive copy.

**8. AI referral size and quality**

| Study | Date | Sample | Headline numbers | Sells related product |
|---|---|---|---|---|
| Ahrefs live benchmark | Updated 13 Nov 2026 | 118,671 sites on Ahrefs Web Analytics, fixed panel | Oct 2026: AI assistants 0.46% of visits, search 27.55%, ChatGPT 0.36%; no conversion data | Yes |
| Kaiser and Schulze, Marketing Science 45(4) | July 2026 | 973 e-commerce sites, Aug 2024 to Jul 2025, last-click | LLM referrals under 0.2% of visits; convert below every channel except paid social (organic search about 13% higher) | No (peer reviewed); data from Grips Intelligence, where one author is affiliated |
| Adobe | 29 Oct 2026 | Over 1 trillion visits to US retail sites | Sept 2026: AI traffic up 71% year on year, converts 90% better than non-AI, 83% higher revenue per visit; no share of visits given | Yes |
| Conductor 2026 benchmarks | Nov 2025, updated 6 Jul 2026 | 1,215 enterprise customer domains, 3.3 billion sessions, US, May to Sep 2025 | AI referrals 1.08% of traffic (IT 2.80%); ChatGPT 87.4% of them; no conversion data of its own | Yes |
| Microsoft Clarity | 6 Nov 2025 | 1,200+ publisher sites; 1,277 domains for conversions | AI under 1% of traffic; sign-up rate 1.66% from AI vs 0.15% from search | Yes |

- **URLs:**
  - Ahrefs: https://ahrefs.com/benchmarks/ai-vs-search
  - Kaiser and Schulze: https://doi.org/10.1287/mksc.2025.0489. The journal page was blocked, so details come from the Crossref record, the authors' site https://organicllm.org/ and https://www.frankfurt-school.de/en/news/2026/06/chatgpt-in-online-ohopping (22 June 2026).
  - Adobe: https://business.adobe.com/blog/ai-traffic-higher-revenue-per-visit-retail-holiday
  - Conductor: https://www.conductor.com/academy/aeo-geo-benchmarks-report/
  - Clarity: https://clarity.microsoft.com/blog/ai-traffic-converts-at-3x-the-rate-of-other-channels-study/
- **Ahrefs' own site (16 June 2025, single site):** AI search was 0.5% of visitors and 12.1% of signups. https://ahrefs.com/blog/ai-search-traffic-conversions-ahrefs/
- **Conflict worth stating in the articles:** the peer-reviewed e-commerce study finds AI referrals convert worse than most channels, while the vendor studies find better. Sectors, periods and definitions differ.
- **Confidence:** Ahrefs, Adobe, Conductor and Clarity pages read; the journal article itself not read.

**Could not confirm**
- A live OpenAI page stating the `utm_source=chatgpt.com` behaviour, or any OpenAI statement on the October 2026 lapse.
- Official referrer or UTM documentation from Perplexity, Anthropic, Google (Gemini) or Microsoft (Copilot).
- The list of referrers GA4 uses for the AI Assistant channel, and whether the channel backfills before 13 May 2026.
- Any Search Console way to split AI Overviews from AI Mode, or AI clicks from other clicks.
- An explicit Microsoft statement that AI Performance excludes clicks, an official Bing post for the 23 March 2026 mapping update, and whether the report has left preview.
- The Marketing Science article page, the live SparkToro page and Search Engine Land's coverage were blocked by bot checks; Adobe's August 2026 PDF timed out.
- The SparkToro raw-data site (https://modeltest-dark-fog-8907.fly.dev/) was not opened.
- A multi-site Ahrefs conversion study, and a primary study of AI referral share for B2B specifically (Conductor's IT figure is the closest).
