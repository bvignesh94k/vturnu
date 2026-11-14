<?php
/**
 * Listicle: the international SEO checklist, with an hreflang tag builder.
 * Loaded by templates/blog-post.php through the 'rich' key. The numbered items,
 * progress board and tool come from includes/listicle-kit.php and
 * assets/js/listicle.js. Google's guidance is paraphrased from the pages linked
 * in each sentence (checked 8 Nov 2026). External links are nofollow.
 */

$g = [
    'regions' => 'https://developers.google.com/search/docs/specialty/international/managing-multi-regional-sites',
    'hreflang' => 'https://developers.google.com/search/docs/specialty/international/localized-versions',
    'adaptive' => 'https://developers.google.com/search/docs/specialty/international/locale-adaptive-pages',
    'targeting' => 'https://support.google.com/webmasters/answer/12474899',
    'performance' => 'https://support.google.com/webmasters/answer/17011165',
];

/* A table that turns into stacked cards on a phone, as in the other long posts. */
$table = static function (string $caption, array $head, array $rows): void {
    ?>
<div class="bx-table-wrap">
    <table class="bx-table" role="table">
        <caption><?= e($caption) ?></caption>
        <thead role="rowgroup"><tr role="row"><?php foreach ($head as $th): ?><th scope="col" role="columnheader"><?= e($th) ?></th><?php endforeach; ?></tr></thead>
        <tbody role="rowgroup">
            <?php foreach ($rows as $row): ?>
            <tr role="row">
                <th scope="row" role="rowheader"><?= e($row[0]) ?></th>
                <?php foreach (array_slice($row, 1) as $i => $cell): ?><td role="cell" data-label="<?= e($head[$i + 1]) ?>"><?= e($cell) ?></td><?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
    <?php
};

return [

'body' => static function () use ($g, $table): void { ?>
<p class="bx-open">Launching your website in a second language or country looks like a translation job. For search engines, it is a structure job first.</p>
<p>If Google cannot tell your versions apart, it may show your English page to buyers in France, treat your regional pages as copies of each other, or never find some versions at all. Translation spend is wasted when that happens.</p>
<p>The ten steps below follow the order we work in: choose the market, set the structure, connect the versions, then localize and measure. Tick each step as you finish it. Your progress is saved in this browser, so you can come back to it.</p>

<div class="answer-box post-answer">
    <p class="answer-text"><?= e($GLOBALS['BLOG']['international-seo-checklist']['lede'] ?? '') ?></p>
</div>

<?php lx_glance([
    ['choose-markets', 'Pick markets from evidence, not ambition', 'Before you build'],
    ['url-structure', 'Choose one URL structure and keep it', 'Decide once'],
    ['separate-urls', 'Give every language its own URL', 'Must do'],
    ['hreflang', 'Add hreflang to every version', 'Free builder inside'],
    ['canonicals', 'Point canonical tags at the right page', '15 minutes'],
    ['no-auto-redirects', 'Let visitors choose: no forced redirects', 'Quick win'],
    ['keyword-research', 'Research keywords in each market', 'Per market'],
    ['localize', 'Localize the page, not just the words', 'Per page'],
    ['local-signals', 'Build local proof and links', 'Ongoing'],
    ['measure', 'Measure each market on its own', 'Monthly'],
], 'The 10 steps at a glance'); ?>

<?php lx_board('intl-seo-checklist', [
    'label' => 'steps done',
    'filters' => ['quick' => 'Quick wins', 'project' => 'Bigger jobs'],
    'msgs' => [
        [0, 'Start with step 1. Each step you tick is saved in this browser.'],
        [4, 'Good start. Steps 2 to 5 decide whether Google can tell your versions apart, so finish them before you translate more pages.'],
        [9, 'The structure is in place. The last steps decide whether buyers in each market choose you.'],
        [10, 'All ten done. Recheck your hreflang tags whenever you add or remove a version.'],
    ],
]); ?>

<?php lx_item(1, 'choose-markets', 'Pick markets from evidence, not ambition', ['type' => 'project', 'tags' => ['Bigger job', 'Before you build'], 'mark' => 'Done'], static function () use ($g): void { ?>
<p>Every new market is another website to keep up: pages, links, enquiries in another language and legal pages for another country. Start where people already look for you.</p>
<?php lx_split('How to check',
    '<ul><li>In Search Console, open the Performance report and the Countries tab. Which countries already bring impressions and clicks without any local pages?</li><li>In your analytics and CRM, count enquiries and won deals by country.</li><li>Ask sales which markets keep asking for quotes, proposals or calls in their own language.</li></ul>',
    'How to decide',
    '<ul><li>Pick one or two markets where you can answer an enquiry in the local language and time zone.</li><li>Plan by language as well as by country. One Spanish version can serve several countries when your offer is the same.</li><li>Write down why each market made the list. It keeps the plan honest a year later.</li></ul>'); ?>
<?php }); ?>

<?php lx_item(2, 'url-structure', 'Choose one URL structure and keep it', ['type' => 'project', 'tags' => ['Bigger job', 'Decide once'], 'mark' => 'Done'], static function () use ($g, $table): void { ?>
<p>Google's <?= ext_link($g['regions'] . '#locale-specific-urls', 'guidance on multi-regional sites') ?> compares four ways to give each locale its own address. None of them is a ranking shortcut. Each one trades cost against clarity.</p>
<?php $table('International URL structures compared', ['Option', 'Example', 'Strengths', 'Trade-offs'], [
    ['Country-code domain', 'example.de', 'The clearest country signal; server location matters less', 'Costs more, means separate sites to run, and serves one country each'],
    ['Subdomain', 'de.example.com', 'Easy to set up; each can be hosted in a different place', 'Visitors cannot tell whether "de" means a language or a country'],
    ['Subfolder', 'example.com/de/', 'Easy to set up; one host and one site to maintain', 'The country is less obvious from the URL, and the sites are harder to split later'],
    ['URL parameter', 'example.com/?loc=de', 'None that Google lists', 'Google marks this option as not recommended'],
]); ?>
<p>For a B2B company entering a few markets, subfolders on one domain are usually the simplest to run, because every version shares the same authority and the same codebase. A country-code domain makes sense when you have a local entity, a local team and a budget to run a separate site.</p>
<p>Whatever you choose, treat it as permanent. Moving from one structure to another later is a full migration, with redirects for every URL. If you are ever in that position, follow a plan like our guide on <a href="/blog/website-redesign-without-losing-seo/">how to redesign a website without losing SEO</a>.</p>
<?php }); ?>

<?php lx_item(3, 'separate-urls', 'Give every language its own URL', ['type' => 'project', 'tags' => ['Bigger job', 'Must do'], 'mark' => 'Done'], static function () use ($g): void { ?>
<p>Google asks for <?= ext_link($g['regions'] . '#use-different-urls-for-different-language-versions', 'a different URL for each language version') ?>, not one URL whose content switches with cookies or browser settings. The reason is practical: Googlebot usually crawls without stating a preferred language, and its <?= ext_link($g['adaptive'], 'default crawl addresses appear to be in the US') ?>. A page that changes by visitor may only ever be seen in one language.</p>
<p>Keep one language per page as well. Google works out a page's language from the <?= ext_link($g['regions'] . '#make-sure-the-page-language-is-obvious', 'visible content, not from code or the URL') ?>, so side-by-side translations on one page make the language unclear.</p>
<?php lx_split('How to check',
    '<ul><li>Run each version through URL Inspection in Search Console and view the crawled page. Is it in the right language?</li><li>Check that menus, buttons, forms, error messages and the footer are translated, not only the body text.</li></ul>',
    'How to fix',
    '<ul><li>Move each language to its own URL using the structure from step 2.</li><li>Replace any script that swaps text by browser language with links to each version.</li></ul>'); ?>
<?php }); ?>

<?php lx_item(4, 'hreflang', 'Add hreflang to every version', ['type' => 'quick', 'tags' => ['Quick win', 'Free builder inside'], 'mark' => 'Done'], static function () use ($g): void { ?>
<p>Hreflang tells Google which version of a page to show to which language and region. Google's <?= ext_link($g['hreflang'], 'documentation on localized versions') ?> sets out the rules. In plain terms:</p>
<ul class="post-list">
    <li><strong>Pick one method.</strong> Link tags in the page head, HTTP headers (handy for PDFs) or your XML sitemap all work the same way. Using more than one adds nothing.</li>
    <li><strong>Every version lists every version, itself included.</strong> If your English page points to the French page but the French page does not point back, Google ignores that pair.</li>
    <li><strong>Use full URLs,</strong> including https:// and the domain.</li>
    <li><strong>Use the right codes.</strong> A two-letter language code (ISO 639-1), then optionally a two-letter country code (ISO 3166-1 alpha-2), as in en-GB. A country on its own is not allowed. Script codes work too, as in zh-Hant.</li>
    <li><strong>Avoid codes Google ignores.</strong> Region codes such as 419 (es-419) are not supported, and reserved codes such as UK and EU have no effect. For the United Kingdom, use GB.</li>
    <li><strong>Add x-default</strong> for everyone else: the page to show when no version matches a visitor's language, often a language selector.</li>
</ul>
<p>Here is what a complete set looks like for a page with three versions. The same block goes on all three.</p>
<?php lx_code(<<<'HTML'
<link rel="alternate" hreflang="en" href="https://www.example.com/en/" />
<link rel="alternate" hreflang="en-GB" href="https://www.example.com/en-gb/" />
<link rel="alternate" hreflang="fr" href="https://www.example.com/fr/" />
<link rel="alternate" hreflang="x-default" href="https://www.example.com/" />
HTML, 'HTML example'); ?>

<div class="lx-tool lx-js" data-lx-tool="hreflang" id="hreflang-builder">
    <p class="lx-tool-k">Free tool</p>
    <p class="lx-tool-h">Hreflang tag builder</p>
    <p>Add one row for each language or regional version of a page. The tags update as you type, and the checks flag codes and URLs that Google would ignore.</p>
    <div data-hl-rows></div>
    <button class="lx-hl-add" type="button" data-hl-add>+ Add a version</button>
    <div class="lx-field">
        <label for="hl-xdefault">Fallback page for everyone else (x-default, optional)</label>
        <input id="hl-xdefault" type="url" value="https://www.example.com/" data-hl-xdefault autocomplete="off" spellcheck="false">
    </div>
    <ul class="lx-checks" data-hl-msgs aria-live="polite"></ul>
    <p class="lx-out-h">Add this block to the head of every version listed</p>
    <div class="lx-code"><div class="lx-code-bar"><span>HTML</span><button class="lx-copy" type="button" data-lx-copy>Copy</button></div><pre tabindex="0"><code data-hl-html></code></pre></div>
    <details>
        <summary>Prefer your XML sitemap? Get the sitemap version</summary>
        <div class="lx-code"><div class="lx-code-bar"><span>XML sitemap</span><button class="lx-copy" type="button" data-lx-copy>Copy</button></div><pre tabindex="0"><code data-hl-xml></code></pre></div>
    </details>
    <datalist id="lx-hl-langs">
        <option value="en">English</option><option value="es">Spanish</option><option value="fr">French</option><option value="de">German</option>
        <option value="pt">Portuguese</option><option value="it">Italian</option><option value="nl">Dutch</option><option value="ar">Arabic</option>
        <option value="hi">Hindi</option><option value="ja">Japanese</option><option value="ko">Korean</option><option value="zh-Hans">Chinese, Simplified</option>
        <option value="zh-Hant">Chinese, Traditional</option><option value="tr">Turkish</option><option value="pl">Polish</option><option value="sv">Swedish</option>
    </datalist>
</div>

<p>Once the tags are live, recheck them after every release that adds or removes a page. A missing return link is one of the common mistakes Google lists, and one forgotten page is enough to break a pair.</p>
<?php }); ?>

<?php lx_item(5, 'canonicals', 'Point canonical tags at the right page', ['type' => 'quick', 'tags' => ['Quick win', '15 minutes'], 'mark' => 'Done'], static function () use ($g): void { ?>
<p>A canonical tag names the main URL among duplicates. On a multilingual site, each language version should normally name itself. If your French page's canonical points to the English page, you are telling Google the French page is a copy, and it may never appear in French results.</p>
<p>There is one exception. When two versions in the same language are near copies, such as identical German pages on example.de and example.com/de/, Google suggests you <?= ext_link($g['regions'] . '#dup-content', 'pick a preferred version and use canonical together with hreflang') ?>.</p>
<?php lx_split('How to check',
    '<ul><li>View the source of each version and find the rel="canonical" line. It should be the page\'s own URL.</li><li>In URL Inspection, compare the canonical you declared with the one Google selected.</li></ul>',
    'How to fix',
    '<ul><li>Set each template to output its own URL as the canonical.</li><li>Keep canonical and hreflang URLs identical, including the trailing slash.</li></ul>'); ?>
<?php }); ?>

<?php lx_item(6, 'no-auto-redirects', 'Let visitors choose: no forced redirects', ['type' => 'quick', 'tags' => ['Quick win', 'Under an hour'], 'mark' => 'Done'], static function () use ($g): void { ?>
<p>Sending people to a language version based on a guess about their location or browser feels helpful, but Google advises against <?= ext_link($g['regions'] . '#let-the-user-switch-the-page-language', 'automatic redirects between language versions') ?>. They can stop both visitors and crawlers from reaching every version. A buyer travelling abroad, or using a company VPN, also lands on the wrong page.</p>
<?php lx_split('Do this instead',
    '<ul><li>Show a small banner that suggests the local version and lets the visitor stay.</li><li>Add a language switcher with plain links to each version, so crawlers can follow them too.</li><li>Write each language in its own name: Deutsch, Français, Español.</li></ul>',
    'Avoid',
    '<ul><li>Redirects by IP address or browser language.</li><li>Flags as the only language labels. A flag is a country, not a language.</li><li>Switchers that only work with JavaScript.</li></ul>'); ?>
<?php }); ?>

<aside class="bx-cta bx-cta--audit" aria-label="Free website audit">
    <svg class="bx-cta-art" viewBox="0 0 120 120" width="104" height="104" aria-hidden="true" focusable="false"><circle cx="60" cy="60" r="46" fill="none" stroke="#DAD5F2" stroke-width="12"/><path d="M60 14a46 46 0 1 1-40 69" fill="none" stroke="#FF3D8A" stroke-width="12" stroke-linecap="round"/><path d="M42 61l13 13 25-29" fill="none" stroke="#1D1936" stroke-width="9" stroke-linecap="round" stroke-linejoin="round"/></svg>
    <div class="bx-cta-copy">
        <p class="bx-cta-h">Check Your Site Before You Translate It</p>
        <p>Run the free audit on your main domain. It flags the search basics that every language version will inherit.</p>
        <a class="bx-btn bx-btn--accent" href="/free-seo-audit/" data-cta="blog-intl-audit">Run My Free Website Audit <span aria-hidden="true">&rarr;</span></a>
        <p class="bx-cta-note">The audit checks one URL at a time. It does not test hreflang across versions.</p>
    </div>
</aside>

<?php lx_item(7, 'keyword-research', 'Research keywords in each market', ['type' => 'project', 'tags' => ['Bigger job', 'Per market'], 'mark' => 'Done'], static function (): void { ?>
<p>A translated keyword list is a guess. Buyers in different markets often use different words for the same thing, even in the same language: compare British and American English. Translate your English list word for word and you may target phrases nobody types.</p>
<?php lx_split('How to research',
    '<ul><li>Start from your services, not your English keywords.</li><li>Ask native speakers on your team, local partners or customers which words they use.</li><li>Check search demand per country in your keyword tool, then look at what ranks for each term: the format and the questions it answers.</li></ul>',
    'What to deliver',
    '<ul><li>One keyword map per language, page by page.</li><li>A glossary of approved terms, so every writer and translator uses the same words.</li></ul>'); ?>
<p>This is where a native-speaking specialist earns their fee. Our <a href="/translation-services/">website translation and multilingual SEO</a> work starts with exactly this map before anyone translates a page.</p>
<?php }); ?>

<?php lx_item(8, 'localize', 'Localize the page, not just the words', ['type' => 'project', 'tags' => ['Bigger job', 'Per page'], 'mark' => 'Done'], static function () use ($g): void { ?>
<p>Google lists <?= ext_link($g['regions'] . '#how-does-google-determine-a-target-locale', 'local addresses, phone numbers, language and currency') ?> among the signals it uses to understand who a page is for. Buyers look for the same clues before they trust you with an enquiry.</p>
<?php lx_split('Check each page for',
    '<ul><li>Currency, units, and date and number formats</li><li>A phone number in international format, plus support hours in the local time zone</li><li>Case studies, client logos and certifications that buyers in that market recognise (only real ones)</li><li>Privacy notice, cookie banner and terms for that market</li></ul>',
    'Skip',
    '<ul><li>Geo meta tags such as geo.position: Google ignores them.</li><li>Machine translation published without review by a native speaker.</li><li>Local phone numbers nobody answers.</li></ul>'); ?>
<?php }); ?>

<?php lx_item(9, 'local-signals', 'Build local proof and links', ['type' => 'project', 'tags' => ['Bigger job', 'Ongoing'], 'mark' => 'Done'], static function () use ($g): void { ?>
<p>Links from other sites in the same country are one of the <?= ext_link($g['regions'] . '#how-does-google-determine-a-target-locale', 'signals Google mentions') ?> for working out a page's target market. They also matter to buyers, who look for names they already know.</p>
<?php lx_split('Where to start',
    '<ul><li>Trade associations, chambers of commerce and partner directories in that market</li><li>Local events, webinars or panels where your team can speak</li><li>One genuinely local article each quarter, such as a market report or a regulation explainer</li></ul>',
    'Avoid',
    '<ul><li>Bought links and link exchanges</li><li>Mass directory submissions with no audience</li><li>A Business Profile without a real address in that country</li></ul>'); ?>
<?php }); ?>

<?php lx_item(10, 'measure', 'Measure each market on its own', ['type' => 'quick', 'tags' => ['Quick win', 'Monthly'], 'mark' => 'Done'], static function () use ($g): void { ?>
<p>Totals hide problems. Overall traffic can grow while one market quietly falls, so give every market its own numbers.</p>
<p>In Search Console's Performance report you can <?= ext_link($g['performance'], 'filter and compare by country, page and query') ?>, which is enough to watch each version. Do not look for a country targeting setting: Google <?= ext_link($g['targeting'], 'retired the International Targeting report') ?> and no longer supports country targeting in Search Console. Hreflang is still supported.</p>
<?php lx_split('Your monthly review',
    '<ul><li>Impressions and clicks per country and per language folder</li><li>Top queries in each language: are they the terms from your keyword map?</li><li>Indexed pages per version, and any new hreflang or canonical errors</li></ul>',
    'Tie it to revenue',
    '<ul><li>Record each enquiry\'s market in your CRM.</li><li>Compare qualified enquiries and pipeline per market, not just traffic.</li></ul>'); ?>
<?php }); ?>

<?php },

'closing' => static function (): void { ?>
<h2 id="final-thoughts">Final Thoughts</h2>
<p>International SEO rewards order. Choose markets from evidence, settle the structure once, connect every version with hreflang, and only then pour effort into translation and local proof. Teams that start with translation tend to repeat the work later, when the structure has to change.</p>
<p>Before your next market launch, run the hreflang builder above on one real page and its versions. If the tags come out clean, the rest of the checklist is about earning trust in that market, which is the part that brings enquiries.</p>
<p>Two related guides help with the next steps: <a href="/blog/schema-markup-for-b2b-websites/">schema markup for B2B websites</a>, which should match on every language version, and our <a href="/blog/website-accessibility-checklist/">website accessibility checklist</a>, which covers the page language setting that screen readers depend on.</p>

<aside class="bx-cta bx-cta--main" aria-label="Start a conversation with VTurnU">
    <p class="bx-cta-h">Planning to Launch in a New Market?</p>
    <p>Tell us where you want to grow. We will check your structure, hreflang and keyword plan before you spend on translation.</p>
    <div class="bx-cta-acts">
        <a class="bx-btn bx-btn--light" href="/contact-us/" data-cta="blog-intl-plan">Plan My International SEO <span aria-hidden="true">&rarr;</span></a>
        <a class="bx-link bx-link--light" href="/translation-services/" data-cta="blog-intl-translation">See Translation and Localization</a>
    </div>
    <ul class="bx-cta-trust">
        <li>A senior strategist replies within one working day</li>
        <li>Projects start from <?= e(min_budget()) ?></li>
        <li>Your details stay private</li>
    </ul>
</aside>

<aside class="bx-author" aria-label="About the author">
    <p class="bx-author-h">Written by the VTurnU strategy team</p>
    <p>VTurnU is a B2B digital marketing and branding agency based in Chennai, working across SEO, AI search, paid media, content and websites for clients worldwide. Read more <a href="/about-us/">about the team</a>, <a href="/how-we-work/">how we work</a> and <a href="/ai-policy/">how we use AI</a>.</p>
</aside>
<?php },

];
