# Is your site blocking AI assistants without you knowing? A 10-minute check

Founders keep asking how to get their product mentioned in ChatGPT, Claude or Perplexity answers. Before you write a single new page, check something more basic: whether those assistants can read your site at all.

Plenty of sites block AI search crawlers by accident. Nobody decided to. It came from a template, a default or a security setting. Here is why it happens, which bots matter, and how to check yours in about 10 minutes.

## Why it happens

- **CDN defaults.** On 1 July 2025, Cloudflare started blocking AI crawlers by default for new customers. If you set up your domain on Cloudflare after that, the AI bot block may already be on.
- **Copied robots.txt rules.** A lot of "block AI scrapers" snippets block every AI bot, including the ones that fetch pages for search answers, not only the ones that collect training data.
- **Aggressive bot protection.** Firewall and bot-fight settings can show crawlers a challenge page instead of your content.
- **JavaScript-only pages.** If your main content only appears after scripts run, some crawlers see an almost empty page.

## Training bots and search bots are different

Most AI companies now run separate crawlers for separate jobs, and blocking one does not block the others:

- **OpenAI:** OAI-SearchBot collects pages for ChatGPT search. GPTBot collects training data. ChatGPT-User visits a page when a user asks ChatGPT to.
- **Anthropic:** Claude-SearchBot improves Claude's search results. ClaudeBot collects training data. Claude-User fetches a page when a user asks Claude to.
- **Google:** AI Overviews and AI Mode are part of Google Search and use the normal Googlebot crawl. The separate Google-Extended token controls Gemini training and grounding, and Google says it has no effect on Google Search.

So the real decision is not "AI: yes or no". You can block training bots if you want to, and still let search bots read your pages, so assistants can cite and link to you.

## The 10-minute check

1. **Read your robots.txt.** Open yourdomain.com/robots.txt and search for OAI-SearchBot, GPTBot, Claude-SearchBot, ClaudeBot, PerplexityBot and Google-Extended. Also look for a broad `User-agent: *` with `Disallow: /`.
2. **Check your CDN.** If you use Cloudflare, look at the AI bot settings under the bot and security section of your dashboard, and note what is blocked.
3. **Fetch a page like a bot would.** Run `curl -A "OAI-SearchBot" https://yourdomain.com/pricing` (or any key page) and see whether you get your content or a challenge page.
4. **View the source.** Open view-source on your pricing or product page and search for a sentence from the page. If it is missing, your content depends on JavaScript.
5. **Write your policy down.** Decide which bots you allow, so the next template or plugin update does not quietly change it.

If you would rather not do it by hand, we built a free [AI Crawler Checker](https://vturnu.com/ai-crawler-checker/) that shows which of 23 AI crawlers your robots.txt lets in. No sign-up needed.

## What this does not do

Letting search bots in does not guarantee you get cited. Assistants still pick sources that answer the question clearly and that other sites trust. But if the crawlers cannot read your site, you will not be cited at all, so this is the cheapest fix on the list.

Have you checked yours? I am curious how many of you find a block you did not know about.

*Disclosure: I am B. Vignesh, Founder & CEO of VTurnU, a B2B digital marketing and web development company in Chennai, India. The checker linked above is our free tool.*

===META===
Where to post: indiehackers.com, logged in, "New post" in a relevant group (SEO, Growth or Marketing), or the main feed.
Title (paste as the post title): Is your site blocking AI assistants without you knowing? A 10-minute check
Image (optional, top of post): indiehackers-ai-crawlers-10-minute-check.jpg (1200 x 630)
Image alt text: Dark banner reading "Is your site blocking AI assistants?", listing the five checks.
Link: one vturnu.com link (/ai-crawler-checker/), with a disclosure line, as Indie Hackers readers expect.
Facts and sources: Cloudflare default AI-bot blocking for new customers (announced 1 July 2025); OpenAI and Anthropic crawler documentation (separate bots, separate robots.txt tokens); Google's common crawlers page ("It has no effect on Google Search or other products" for Google-Extended).
