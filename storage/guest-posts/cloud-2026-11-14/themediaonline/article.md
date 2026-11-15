# Block, allow or charge? How publishers can decide what AI crawlers may read

**By B. Vignesh, Founder & CEO, VTurnU**

For twenty years, publishers had one question to answer about robots: do we let Google in? The answer was almost always yes, because search sent readers back.

AI assistants have made the question harder. They read your journalism too, but they do not all do the same thing with it, and not all of them send anyone back. A single decision to "block AI" or "allow AI" hides at least three different choices. I think publishers will get better results by making each one on purpose.

## First, know that there are three kinds of visitor

The large AI companies now run separate crawlers for separate jobs, and their own documentation says so.

- **Training crawlers** collect pages to improve AI models. OpenAI's is called GPTBot.
- **Search crawlers** build the index an assistant uses to find and link to sources. OpenAI's is OAI-SearchBot and Perplexity's is PerplexityBot. [OpenAI states](https://developers.openai.com/api/docs/bots) that sites which opt out of OAI-SearchBot will not be shown in ChatGPT search answers, though they can still appear as navigational links.
- **User-triggered fetchers** visit a page because a person asked the assistant about it. OpenAI's is ChatGPT-User and Perplexity's is Perplexity-User. Both companies say that, because a user started the request, robots.txt rules may not apply.

Google works differently. AI Overviews and AI Mode are part of Google Search. [Google's guidance](https://developers.google.com/search/docs/appearance/ai-features) is that a page must be indexed and eligible for a snippet to appear as a supporting link, with no extra requirements, and that publishers limit what is shown with the same snippet controls that apply to ordinary results. A separate setting, Google-Extended, covers training and grounding for Gemini. Google says it does not affect a site's inclusion or ranking in Search.

So one line that "blocks AI" in robots.txt can remove you from ChatGPT's search answers while doing nothing about user-triggered fetches. It pays to be specific.

## Second, decide what you want from each

There are three reasonable goals, and they pull in different directions.

**Payment.** If your reporting is original and hard to replace, training access is something to license, not to give away. This is a live issue in South Africa: the Competition Commission's Media and Digital Platforms Market Inquiry, whose final report came out in November 2025, looked at how AI companies use news content.

**Visibility.** If your business depends on being found, search crawlers are closer to Googlebot than to a scraper. Blocking them trades away citations and links.

**Protection.** Subscriber-only work should not be readable by any bot that has not paid, whatever it calls itself.

One sensible starting point is to treat the three groups separately: charge or block training crawlers, allow search crawlers on public pages and keep everything out of paywalled sections. Your own answer may differ. What matters is that it is a decision, written down and owned by someone senior.

## Third, remember that robots.txt is a request, not a lock

A robots.txt file tells well-behaved crawlers what you prefer. It does not stop anyone. Enforcement happens at the network level.

On 1 July 2025, [Cloudflare said](https://blog.cloudflare.com/content-independence-day-no-ai-crawl-without-compensation/) it was changing its default to block AI crawlers unless they pay creators for their content. It also opened a private beta called pay per crawl, which lets a site owner allow, charge or block each crawler. Whether or not you use that service, the principle holds: decide in policy, enforce at the firewall, and check the server logs to see who actually came. OpenAI and Perplexity both publish the IP addresses their crawlers use, so genuine visits can be told apart from impostors.

## Fourth, measure what you get back

Add AI assistants as a referral source in your analytics and review it monthly, next to search and social. If an assistant reads thousands of pages and sends a handful of readers, that is evidence for your next licensing conversation. If referrals are growing, it is a reason to keep the door open.

## A five-minute start

Open yoursite.co.za/robots.txt and read it. You may find rules nobody remembers writing, added by a template, a plugin or a security setting. If you prefer a quick view, my company built a free [AI Crawler Checker](https://vturnu.com/ai-crawler-checker/) that shows which of 23 AI crawlers your robots.txt currently lets in.

I do not think the publishers who come through this period well will be the ones who blocked everything, or the ones who allowed everything. They will be the ones who knew, crawler by crawler, what they were trading and what they were getting for it.

---

*B. Vignesh is the Founder & CEO of VTurnU, a B2B digital marketing and web development company based in Chennai, India. He has worked in digital marketing since 2017 and leads the company's work on search and AI search visibility.*

Email: connect@vturnu.com · Website: [vturnu.com](https://vturnu.com) · LinkedIn: [linkedin.com/in/vigneshuday](https://www.linkedin.com/in/vigneshuday/)

===META===
Title: Block, allow or charge? How publishers can decide what AI crawlers may read
Meta title: Block, Allow or Charge? A Publisher's Guide to AI Crawlers
Meta description: Training crawlers, search crawlers and user-triggered fetchers are different. How publishers can set a policy for each, enforce it and measure the return.
Slug: block-allow-or-charge-publishers-ai-crawlers
Keywords: AI crawlers, publishers, robots.txt, GPTBot, OAI-SearchBot, pay per crawl, content licensing
Image: themediaonline-block-allow-or-charge.jpg (1200 x 675)
Image alt text: Banner reading "Block, allow or charge?", listing training crawlers, search crawlers, user-triggered fetchers and Google's AI features, each with the control that applies.
Sources named in the text: OpenAI crawler documentation; Perplexity crawler documentation; Google Search Central (AI features and your website; Google-Extended); Cloudflare blog, 1 July 2025; Competition Commission of South Africa, Media and Digital Platforms Market Inquiry final report, November 2025.
Exclusive: written for The Media Online and not published anywhere else.
