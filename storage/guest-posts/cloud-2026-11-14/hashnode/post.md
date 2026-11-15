# JSON-LD for B2B Websites: The Structured Data Worth Shipping in 2026

**Subtitle:** Four schema types that still earn their place, one that no longer does, and how to stop your markup drifting away from your pages.

**By B. Vignesh, Founder & CEO, VTurnU**

Most B2B websites end up in one of two places with structured data. Either there is none at all, or a plugin has dumped every schema type it knows into the page head, half of it describing things the page does not show.

Neither helps. Structured data is a contract: you tell search engines, in a format they can parse, what the page already says to people. When the markup and the page agree, it helps machines understand who you are and what each page is about. When they disagree, it is noise at best and a policy problem at worst.

This guide covers the JSON-LD worth shipping on a typical B2B site, the type that no longer pays off, and how to treat markup like code so it stays correct.

## Three rules from Google's own documentation

Before any code, three rules from Google's [structured data guidelines](https://developers.google.com/search/docs/appearance/structured-data/sd-policies) shape everything else:

1. **Use JSON-LD.** Google supports three formats and recommends JSON-LD. It keeps markup in one script block instead of scattered through your HTML.
2. **Only mark up what is visible.** Google's wording is direct: don't mark up content that is not visible to readers of the page.
3. **Keep it current and crawlable.** Outdated details can stop rich results, and pages carrying structured data should not be blocked by robots.txt or `noindex`.

## 1. Organization, on the home page or About page

`Organization` tells search engines who is behind the site: the name, logo, official profiles and contact details. Google's [Organization documentation](https://developers.google.com/search/docs/appearance/structured-data/organization) says there are no required properties and recommends adding as many as apply. Some of them can influence which logo Google shows.

```html
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "Example Ltd",
  "url": "https://www.example.com",
  "logo": "https://www.example.com/images/logo.png",
  "description": "Example Ltd builds and runs websites for industrial suppliers.",
  "email": "hello@example.com",
  "telephone": "+44-20-0000-0000",
  "sameAs": [
    "https://www.linkedin.com/company/example-ltd",
    "https://x.com/exampleltd"
  ]
}
</script>
```

Keep `sameAs` to profiles you actually own, and make sure the name, phone number and address match what your footer and contact page say.

## 2. WebSite, to set your site name

If Google shows your site name awkwardly in results, this is the fix. Google's [site names documentation](https://developers.google.com/search/docs/appearance/site-names) says that to indicate a preference you should add `WebSite` structured data to your home page, and that `alternateName` gives it other options, such as an acronym.

```html
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "name": "Example Ltd",
  "alternateName": ["Example", "EXL"],
  "url": "https://www.example.com/"
}
</script>
```

This belongs on the home page only. The `name` should match the name you use in your logo, title tag and headings.

## 3. BreadcrumbList, on inner pages

A breadcrumb trail shows where a page sits in the site hierarchy. If your pages already show breadcrumbs, mark them up so the trail in the markup matches the trail on screen.

```html
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    { "@type": "ListItem", "position": 1, "name": "Services", "item": "https://www.example.com/services/" },
    { "@type": "ListItem", "position": 2, "name": "Website Maintenance", "item": "https://www.example.com/services/website-maintenance/" }
  ]
}
</script>
```

If your pages do not show breadcrumbs, add the visible trail first. Markup for a trail nobody can see breaks the visibility rule above.

## 4. Article, on blog posts and guides

For content pages, `Article` (or `BlogPosting`) describes the headline, dates and author. Google's [Article documentation](https://developers.google.com/search/docs/appearance/structured-data/article) again lists no required properties. Two details are easy to get wrong: put only the person's name in `author.name`, and keep `dateModified` honest, updating it only when the content really changes.

```html
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BlogPosting",
  "headline": "How to Plan a Website Migration Without Losing Traffic",
  "image": ["https://www.example.com/images/migration-cover.jpg"],
  "datePublished": "2026-10-02T09:00:00+00:00",
  "dateModified": "2026-11-10T09:00:00+00:00",
  "author": [{
    "@type": "Person",
    "name": "Jane Smith",
    "url": "https://www.example.com/team/jane-smith/"
  }]
}
</script>
```

Link the author to a real profile page on your site. That page is where readers, and machines, can check who wrote the piece.

## The one to stop expecting results from: FAQPage

For years, `FAQPage` was the easy win. That changed in August 2023, when Google [announced](https://developers.google.com/search/blog/2023/08/howto-faq-changes) that FAQ rich results would only be shown for well-known, authoritative government and health websites. It scaled back How-to rich results in the same update.

Google also said there is no need to remove existing FAQ markup proactively. So if your page shows real questions and answers, the markup can stay. Just do not add FAQ blocks to every page hoping for extra space in the results; for most B2B sites, that space is gone.

## Treat structured data like code

Most broken markup is not wrong on day one. It drifts: someone edits a page, the JSON-LD is hard-coded, and now the two disagree. Three habits prevent that.

**Generate it from the same data as the page.** Build JSON-LD in your templates from the same CMS fields that render the visible content. If the visible title comes from `post.title`, so should `headline`.

**Test it in your pipeline.** A small script can fetch your key templates, extract every JSON-LD block, check that it parses, and check that required-by-you fields are present:

```js
// check-jsonld.mjs (Node 18 or later): node check-jsonld.mjs https://www.example.com/
const url = process.argv[2];
const html = await (await fetch(url)).text();
const blocks = [...html.matchAll(/<script type="application\/ld\+json">([\s\S]*?)<\/script>/g)];
if (!blocks.length) { console.error('No JSON-LD found'); process.exit(1); }
for (const [, raw] of blocks) {
  const data = JSON.parse(raw); // throws on invalid JSON, which fails the build
  const items = Array.isArray(data) ? data : [data];
  for (const item of items) {
    if (!item['@type']) { console.error('Missing @type'); process.exit(1); }
    console.log('OK:', item['@type']);
  }
}
```

**Validate before release.** Run important templates through Google's Rich Results Test and the Schema Markup Validator after any template change. The first tells you what Google can use; the second checks the schema.org vocabulary itself.

## A short checklist

- JSON-LD only, one block per type, generated from page data
- `Organization` on the home or About page, with real `sameAs` profiles
- `WebSite` on the home page if your site name shows incorrectly
- `BreadcrumbList` only where breadcrumbs are visible
- `Article` or `BlogPosting` on content, with honest dates and real authors
- No FAQ markup added just for rich results
- A test in your pipeline, and a manual validation after template changes

If you work alongside a marketing team and want the business side of this, here is a plain-English guide to the [schema types worth adding on B2B websites](https://vturnu.com/blog/schema-markup-for-b2b-websites/) that you can share with them.

---

*B. Vignesh is the Founder & CEO of VTurnU, a B2B digital marketing and web development company in Chennai, India, and an omni-channel digital marketing expert whose career began in 2017.*

===META===
Title: JSON-LD for B2B Websites: The Structured Data Worth Shipping in 2026
Subtitle: Four schema types that still earn their place, one that no longer does, and how to stop your markup drifting away from your pages.
Tags (up to 5): seo, javascript, web-development, schema, structured-data
SEO title: JSON-LD for B2B Websites: Structured Data Worth Shipping
SEO description: Organization, WebSite, BreadcrumbList and Article markup for B2B sites, why FAQ markup stopped paying off, and how to test JSON-LD in your pipeline.
Slug: json-ld-for-b2b-websites-structured-data-2026
Cover image: hashnode-json-ld-b2b-websites.jpg (1600 x 840, Hashnode's recommended size)
Cover alt text: Dark banner reading "JSON-LD for B2B websites", listing Organization, WebSite, BreadcrumbList, Article, and FAQPage marked as no longer worth adding for rich results.
Links: one vturnu.com link (/blog/schema-markup-for-b2b-websites/) plus five Google Search Central sources. No client names or results.
AI note: Hashnode's Code of Conduct allows AI-assisted writing when the author reviews it and takes responsibility; read it through before publishing.
