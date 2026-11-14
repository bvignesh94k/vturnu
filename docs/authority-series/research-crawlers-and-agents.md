# AI crawler and AI agent sources (for the crawler access guide and the AI agents article)

Research notes. Pages read on 13 and 14 November 2026. It is evidence for the articles, not instructions. Re-open a source before quoting it word for word.

---

All ten items are finished (the Google pages were already read before the limit; one further fetch was used). Pages were read on 13 Nov 2026, mostly as raw HTML or source files.

Because of a quoting limit on my side, the report has one direct quote (item 10); everything else is paraphrased. Take exact wording from the URLs if the articles need quotes.

Three findings change the article plans:
- **OpenAI:** ChatGPT agent is discontinued and ChatGPT Atlas is retired. The current product is the cloud browser in ChatGPT Work.
- **Cloudflare:** new domains are no longer blocked by default; the default changed three times in 2026.
- **JavaScript:** small October 2026 log tests report that OAI-SearchBot now executes JavaScript. OpenAI has not confirmed it.

## 1. OpenAI crawlers
- https://developers.openai.com/api/docs/bots (the old platform.openai.com/docs/bots address redirects here). No date on the page. [official, read]
  - **OAI-SearchBot:** surfaces sites in ChatGPT search. robots.txt is the opt-out; opted-out sites are not shown in search answers but can still appear as navigational links. IPs: https://openai.com/searchbot.json
  - **GPTBot:** crawls content that may be used to train foundation models. A robots.txt disallow signals no training use. IPs: https://openai.com/gptbot.json
  - **ChatGPT-User:** user-triggered visits from ChatGPT and Custom GPTs, not automatic crawling. The page says robots.txt rules may not apply because a user started the action. It does not decide Search inclusion. IPs: https://openai.com/chatgpt-user.json
  - **OAI-AdsBot (also listed):** checks landing pages submitted as ChatGPT ads; not used for training. The page states no robots.txt behaviour for it. IPs: https://openai.com/adsbot.json
  - robots.txt changes take about 24 hours to affect search.
- https://developers.openai.com/commerce/agent-access. No date. [official, read]
  - ChatGPT's cloud browser is separate from the crawlers. It is identified by signed requests (Web Bot Auth, RFC 9421) with the Signature-Agent header set to https://chatgpt.com.
  - Akamai, Cloudflare, HUMAN and Vercel verify these signatures automatically.

## 2. Anthropic
- https://support.claude.com/en/articles/8896518-does-anthropic-crawl-data-from-the-web-and-how-can-site-owners-block-the-crawler. Shows only "Updated over a week ago"; the text refers to 30 Oct 2026. [official, read]
  - **ClaudeBot:** collects content that may be used for training. Blocking it signals that future material should be excluded.
  - **Claude-User:** fetches pages when a user asks. Blocking may reduce visibility in user-directed search.
  - **Claude-SearchBot:** crawls to improve search results. Blocking stops indexing for search.
  - All three honour robots.txt and the non-standard Crawl-delay, and will not bypass CAPTCHAs. Opt-out is per subdomain.
  - IP blocking is described as unreliable because it stops the bots reading robots.txt. The IP list is at https://claude.com/crawling/bots.json.
  - From 30 Oct 2026 Anthropic says it recognises a machine-readable "no AI training" preference signal based on an IETF draft. Rules naming ClaudeBot take priority.
- https://support.claude.com/en/articles/18234133-how-do-anthropic-hosted-browsers-identify-themselves-when-they-browse-the-web-on-a-user-s-behalf. "Updated over a week ago". [official, read]
  - Anthropic-hosted browsers driven by Claude (for example Claude Duet) carry the token Claude-Agent. IPs are at https://claude.com/.well-known/sail. This is controlled separately from the crawlers.
- https://platform.claude.com/docs/en/agents-and-tools/tool-use/web-fetch-tool. No date. [official, read]
  - Anthropic's API web fetch tool does not support pages rendered dynamically with JavaScript. This is the developer tool, not a statement about Claude-User.

## 3. Perplexity
- https://docs.perplexity.ai/docs/resources/perplexity-crawlers. No date. [official, read]
  - **PerplexityBot:** surfaces and links sites in Perplexity search; not used for foundation-model training. Perplexity recommends allowing it in robots.txt and allowing its IPs: https://www.perplexity.ai/perplexitybot.json
  - **Perplexity-User:** fetches a page when a user asks a question; not for crawling or training. The page says it generally ignores robots.txt because a user requested the fetch. IPs: https://www.perplexity.ai/perplexity-user.json
  - robots.txt changes can take up to 24 hours.
  - Cloudflare Bot Fight Mode cannot be skipped by custom WAF rules and may still challenge these bots.

## 4. Google
- https://developers.google.com/crawling/docs/crawlers-fetchers/google-common-crawlers (the old Search Central URL redirects here). Last updated 2026-10-30 UTC. [official, read]
  - Google-Extended is a robots.txt token with no user agent string of its own.
  - It controls use of crawled content for training future Gemini models and for grounding in Gemini Apps and Vertex AI.
  - It does not affect inclusion in Google Search and is not a ranking signal.
  - Google intends to support the AIPREF training-preference signal in early 2027.
- https://developers.google.com/search/docs/appearance/ai-features. Last updated 2025-12-10 UTC. [official, read]
  - AI Overviews and AI Mode are controlled through Googlebot and snippet controls, not Google-Extended.
- https://developers.google.com/crawling/docs/crawlers-fetchers/google-user-triggered-fetchers. Last updated 2026-08-19 UTC. [official, read]
  - User-triggered fetchers generally ignore robots.txt.
  - Listed: Chrome Web Store, Feedfetcher, Gemini Notebook (formerly Google-NotebookLM), Google-Agent, Google Messages, Pinpoint, Publisher Center, Read Aloud, Site Verifier.
  - Google-Agent is used by agents hosted on Google infrastructure that browse and act on a user's request. Its IPs are in user-triggered-agents.json. Google is testing Web Bot Auth for it.
- https://developers.google.com/search/docs/crawling-indexing/javascript/javascript-seo-basics. Last updated 2026-03-04 UTC. [official, read]
  - Google processes JavaScript in three phases: crawl, render, index.
  - Pages returning 200 are queued for rendering; a headless, evergreen Chromium runs the JavaScript when resources allow. Rendering may be skipped for non-200 pages.
  - Server-side or pre-rendering is still recommended because not all bots run JavaScript.

## 5. Cloudflare
- https://www.cloudflare.com/press/press-releases/2025/cloudflare-just-changed-how-ai-crawlers-scrape-the-internet-at-large/. 1 Jul 2025. [official, read]
  - Cloudflare claimed to be the first infrastructure provider to block AI crawlers by default without permission or payment. Every new domain was asked at sign-up whether to allow them.
- https://blog.cloudflare.com/introducing-pay-per-crawl/. 1 Jul 2025. [official, read]
  - Pay per crawl launched in private beta.
- https://blog.cloudflare.com/introducing-ai-crawl-control/. 28 Aug 2025. [official, read]
  - AI Audit was renamed AI Crawl Control, with custom HTTP 402 responses.
- https://blog.cloudflare.com/content-independence-day-ai-options. 1 Jul 2026. [official, read]
  - All plans can now manage three behaviours: Search, Agent and Training.
  - It announced that from 15 Sep 2026 new domains would block Training and Agent on pages with ads.
- https://blog.cloudflare.com/bot-preference-sync/. 21 Aug 2026. [official, read]
  - Bot Preference Sync writes the chosen policy into robots.txt.
  - The default was revised: new domains get no blocks unless the owner says at onboarding that the site earns from ads, in which case Training is set to Disallow.
- https://blog.cloudflare.com/accountable-mixed-use-ai-crawlers/. 15 Sep 2026. [official, read]
  - New Disallow AI Training setting; Block AI Bots and Managed robots.txt are deprecated.
  - Two onboarding presets: without ads, all three are allowed; with ads, Search is allowed, Training is disallowed and Agent is blocked on pages with ads.
- https://developers.cloudflare.com/bots/additional-configurations/block-ai-bots/. Last updated 2 Nov 2026. [official, read]
  - The setting is now called AI Crawler and Agent access policies.
  - New domains default to Allow for Search, Agents and Training.
- https://blog.cloudflare.com/personal-agents-visibility-control. 4 Nov 2026. [official, read]
  - Adds per-agent controls for Personal Agents.
- https://developers.cloudflare.com/ai-crawl-control/ (updated 14 Aug 2026) and https://developers.cloudflare.com/ai-crawl-control/features/pay-per-crawl/what-is-pay-per-crawl/ (updated 28 Jul 2026). [official, read]
  - AI Crawl Control is on all plans. Pay Per Crawl is still in closed beta.

## 6. Vercel and MERJ
- https://vercel.com/blog/the-rise-of-the-ai-crawler. 17 Dec 2024. [official, read]
  - **Scale (one month on Vercel's network):** Googlebot 4.5 billion fetches, GPTBot 569 million, Claude 370 million, AppleBot 314 million, PerplexityBot 24.4 million. Rendering was tested on nextjs.org and two job boards.
  - **Rendering:** none of the major AI crawlers rendered JavaScript: OAI-SearchBot, ChatGPT-User, GPTBot, ClaudeBot, Meta-ExternalAgent, Bytespider, PerplexityBot. Gemini (through Googlebot) and AppleBot do render.
  - ChatGPT and Claude fetched JavaScript files (11.50% and 23.84% of requests) without executing them.
  - 404 rates were 34.82% for ChatGPT and 34.16% for Claude.
- No newer study of comparable scale was found. Smaller tests, all [secondary]:
  - https://www.searchengineworld.com/do-ai-assistants-actually-render-your-javascript-when-grounding-we-put-it-to-the-test. 9 Jun 2026. Twelve assistants on user-triggered fetches: ChatGPT, Claude, Gemini, Perplexity, Meta AI, Copilot and Grok answered from raw HTML; DeepSeek, ERNIE, Qwen, Kimi and Mistral ran the script.
  - https://www.searchviu.com/en/guides/schema-markup-ai/. Test run 30 Oct 2025, updated 15 Oct 2026. One page, five systems: none read a price that existed only in JSON-LD; ChatGPT and Claude missed JavaScript-inserted content.
  - https://genezio.com/research/openai-crawler-runs-javascript/. 22 Oct 2026. A beacon test showed a request from OpenAI's published IPs that only executed JavaScript could make.
  - https://bogdanripa.substack.com/p/an-analysis-of-openais-bots. 22 Oct 2026. One small site, 8 to 22 Oct 2026, 560 requests: OAI-SearchBot rendered pages about 32 minutes after fetching the HTML; ChatGPT-User read HTML only; GPTBot was inconclusive. It links to the Genezio post, so treat the two as one cluster.

## 7. Lighthouse Agentic Browsing
- https://github.com/GoogleChrome/lighthouse/releases and https://github.com/GoogleChrome/lighthouse/blob/main/changelog.md. [official, read]
  - **Introduced:** the category was created in v13.2.0 (30 Apr 2026 in the changelog; release published 1 May 2026) in a separate config. It joined the default config in v13.3.0 on 7 May 2026 (PR #17002).
  - v13.5.0 (17 Sep 2026 in the changelog; release published 18 Sep) added the seventh audit. v13.6.0 (27 Oct 2026) moved it to ARD v0.91 and ard.json.
- https://github.com/GoogleChrome/lighthouse/blob/v13.6.0/core/config/default-config.js and the audit files. [official, read]

| Audit ID | Title | Note |
|---|---|---|
| `agent-accessibility-tree` | Accessibility tree is well-formed | |
| `webmcp-form-coverage` | WebMCP form coverage | informative |
| `webmcp-registered-tools` | WebMCP tools registered | informative |
| `webmcp-schema-validity` | WebMCP schemas are valid | |
| `cumulative-layout-shift` | Cumulative Layout Shift | |
| `llms-txt` | llms.txt follows recommendations | |
| `ard-schema` | ARD manifest is valid | added in v13.5.0 |

  - **Scoring:** the display mode is a fraction, passed over passable. Not-applicable, manual and informative audits are left out of the count.
  - **llms.txt logic:** a 4xx is not applicable; a 5xx or 429 fails; a pass needs an H1, at least one Markdown link and at least 50 characters.
- https://developer.chrome.com/docs/lighthouse/agentic-browsing/scoring. Last updated 2026-05-05 UTC. [official, read]
  - No 0 to 100 score; a fractional score instead.
  - The category and WebMCP support are labelled experimental and rest on proposed standards.
  - It needs Chrome 150 or later; the WebMCP audits need the origin trial.
  - Nothing about Search ranking.
- https://developer.chrome.com/blog/agent-ready-toolkit. 22 Jun 2026. [official, read]
  - Available from Chrome 150; described as informational and not benchmarked.
  - It says ordinary SEO principles still apply when agents are only searching.
- **PageSpeed Insights:**
  - https://developers.google.com/speed/docs/insights/release_notes. Last updated 2025-10-27 UTC. [official, read] No mention of the category; the last entry is Lighthouse 13.0.
  - Lighthouse PR #17041 (merged 1 Jun 2026) says the category was not yet enabled in PSI. PR #17090 (merged 23 Jun 2026) says it could be turned on. [official, read]
  - My own run on pagespeed.web.dev on 13 Nov 2026 for example.com showed Agentic Browsing as 2/2 on Lighthouse 13.6.0: two passed, five not applicable. [direct observation]
  - A 3/3 would follow for a site that also has a valid llms.txt. That is derived from the source code, not documented.
- **Ranking:** I found no official statement that the category affects Google Search ranking.

## 8. WebMCP
- https://webmachinelearning.github.io/webmcp/. Draft Community Group Report, 12 Nov 2026. [official, read]
  - Published by the W3C Web Machine Learning Community Group; it is not a W3C Standard and not on the standards track.
  - Editors are from Microsoft and Google.
  - It lets a web page offer JavaScript-based tools to AI agents.
- https://developer.chrome.com/blog/webmcp-epp. 10 Feb 2026. [official, read] Early preview announced.
- https://developer.chrome.com/docs/ai/webmcp. Published 18 May 2026, updated 7 Oct 2026. [official, read]
  - Chrome calls it a proposed web standard, with a JavaScript API and annotated HTML forms.
- https://developer.chrome.com/blog/ai-webmcp-origin-trial. 9 Jun 2026. [official, read] Origin trial opens in Chrome 149.
- https://chromestatus.com/feature/5117755740913664. Updated 11 Nov 2026. [official, read]
  - Developer trial from Chrome 146; origin trial Chrome 149 to 156; not shipped. No signal from Firefox or Safari.

## 9. Consumer browsing agents
**OpenAI**
- https://help.openai.com/en/articles/11752874-chatgpt-agent. Updated about 27 Oct 2026. [official, read] ChatGPT agent is no longer available; it points to ChatGPT Work and the cloud browser.
- https://help.openai.com/en/articles/20001371-chatgpt-atlas-retirement. Updated about 22 Oct 2026. [official, read] ChatGPT Atlas is retired.
- https://help.openai.com/en/articles/20001280-using-cloud-browser-in-chatgpt. Updated about 28 Oct 2026. [official, read]
  - A browser on a remote computer that reads pages, clicks buttons and fills forms on public and signed-in sites. Paid plans only.
  - It is designed to ask in chat before hard-to-reverse actions or financial, legal or account commitments, such as a booking or payment.
  - It pauses for the user to sign in through a secure form the model cannot see, and asks before opening a new site by default.
  - Sites may block it.
- https://developers.openai.com/commerce/agent-access. No date. [official, read] OpenAI requires human review before any consequential action; site owners cannot yet set their own confirmation rules.

**Google**
- https://blog.google/products-and-platforms/products/chrome/gemini-3-auto-browse/. 28 Jan 2026. [official, read] Gemini in Chrome auto browse handles multi-step tasks and is designed to pause for confirmation on purchases or social posts.
- https://support.google.com/chrome/answer/16821166?hl=en. No date. [official, read]
  - US, adults, paid Google AI plans, English; described as experimental.
  - The user approves a plan first.
  - Gemini hands over for finalising payments, accepting terms and creating accounts.
  - It aims to ask before sending messages, changing data, submitting forms or scheduling.
  - It warns that it can still complete a purchase by mistake.
- https://support.google.com/gemini/answer/17094507?hl=en. No date. [official, read] Gemini app tasks use local Chrome or a remote browser, and are designed to always ask before purchases, financial transactions, form submission and sending communications.

**Anthropic**
- https://claude.com/claude-in-chrome (no date) and https://claude.com/resources/articles/claude-in-chrome-generally-available (26 Aug 2026). [official, read]
  - Claude in Chrome reads the signed-in page, clicks, types, fills forms and navigates. It is generally available on paid plans.
- https://support.claude.com/en/articles/12902446-claude-in-chrome-permissions-guide. "Updated this week". [official, read]
  - Three modes: manual approval, automatic approval with a safety check on each action, or skip all approvals.
  - Explicit permission is always needed for permission changes, authorisations, entering sensitive information and permanent deletions.
  - Prohibited in any mode: purchases or financial transactions, creating accounts, handling card or ID data, trades, and acting on instructions found in emails or web content.
  - The product page words this more softly, saying purchases wait for the user.

**Perplexity**
- https://www.perplexity.ai/hub/blog/mitigating-prompt-injection-in-comet. 22 Oct 2025. [official, read] Comet pauses for confirmation before sending emails or messages, changing the calendar, placing final shopping orders, or entering user details it does not already know.
- https://www.perplexity.ai/hub/blog/comet-assistant-puts-you-in-control. 14 Nov 2025. [official, read through the fetch tool's summary; direct download was blocked] It asks before logging in to a site or completing a cart purchase.
- https://www.perplexity.ai/help-center/comet/en/articles/12658082-control-what-comet-assistant-can-use. Last modified 14 Sep 2026. [official, read] Users can block the assistant on specific sites; it says Comet never has access to passwords or payment data.

## 10. llms.txt
- https://llmstxt.org/. Published 3 Sep 2024, modified 10 Aug 2026. [official, read]
  - Now version 2 of a proposal by Jeremy Howard. It remains a proposal, not a W3C or IETF standard.
- https://developers.google.com/search/docs/fundamentals/ai-optimization-guide. Last updated 2026-07-10 UTC. [official, read]
  - Google Search does not use llms.txt.
  - Quote, Google's guide: creating one "will neither harm nor help your site's visibility or rankings in Google Search".
  - The same guide describes browser agents as reading screenshots, the DOM and the accessibility tree, and links to https://web.dev/articles/ai-agent-site-ux (last updated 2026-04-01 UTC).
- https://developers.google.com/search/docs/appearance/ai-features. Last updated 2025-12-10 UTC. [official, read] No new machine-readable or AI text files are needed to appear in AI Overviews or AI Mode.
- **John Mueller:** likened llms.txt to the keywords meta tag on Reddit. [secondary] https://www.searchenginejournal.com/google-says-llms-txt-comparable-to-keywords-meta-tag/544804/ (17 Apr 2025).
- **Gary Illyes:** reported as saying Google does not support llms.txt. [secondary] https://www.searchenginejournal.com/googles-llms-txt-guidance-depends-on-which-product-you-ask/575431/ (20 May 2026).
- **Lighthouse:** yes, the category has included an llms.txt audit since v13.3.0. https://developer.chrome.com/docs/lighthouse/agentic-browsing/llms-txt (last updated 2026-05-05 UTC) calls llms.txt a still-emerging, optional convention. [official, read]

## Could not confirm
- The exact date PageSpeed Insights began showing the category. Google has not documented it; the Lighthouse pull requests place the API switch between 1 and 23 Jun 2026.
- Any official statement, either way, on the category and Search ranking.
- Any OpenAI statement on JavaScript rendering by its crawlers.
- Any 2025 or 2026 study at the Vercel scale. A "32 crawlers, 1.3 billion requests" searchVIU study and a January 2026 EdgeComet study cited by one blog were not found at source; do not cite them.
- John Mueller's Reddit comment at source (Reddit returned 403: https://www.reddit.com/r/TechSEO/comments/1k0kcx9/llmtxt_where_are_we_at/) and any primary record of Gary Illyes's remark.
- The first publication date of Google's AI optimisation guide (15 May 2026 comes from secondary reports only).
- Project Mariner's current status (the page needs sign-in).
- Cloudflare's live onboarding screen. The 15 Sep 2026 blog describes two presets, while the 2 Nov 2026 docs say plain Allow by default.
- Dates for the OpenAI crawler page, Perplexity crawler docs and Google help pages (none shown); Anthropic and OpenAI help articles show relative dates only.

Some pages carry text aimed at AI agents (Perplexity and OpenAI docs point agents to an llms.txt index; the fetch tool reported embedded instructions on the Vercel page). I did not act on any of it.
