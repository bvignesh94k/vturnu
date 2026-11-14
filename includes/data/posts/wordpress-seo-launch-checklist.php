<?php
/**
 * Launch checklist: fourteen technical checks on a built WordPress site before
 * it goes live, with a pass or fail board (includes/listicle-kit.php).
 * Loaded by templates/blog-post.php through the 'rich' key.
 *
 * Every statement about what WordPress does by default comes from the
 * WordPress documentation or core notes linked in the sentence, read on
 * 13 November 2026, when the current release was 7.1.4. Statements about
 * Google come from Google Search Central, read on the same day. The commands
 * are generic curl requests. None was run on a WordPress install for this
 * article, and nothing here is described as tested. No plugin is named.
 *
 * "We" is used only for the FirstChoice Used Auto Parts rebuild, as published
 * in its case study. vturnu.com is not built on WordPress and the article does
 * not suggest it is. Nothing here promises a ranking or a lead.
 */

$s = [
    'releases' => 'https://wordpress.org/download/releases/',
    'reading' => 'https://wordpress.org/documentation/article/settings-reading-screen/',
    'noindex53' => 'https://make.wordpress.org/core/2019/09/02/changes-to-prevent-search-engines-indexing-sites/',
    'sitemaps' => 'https://make.wordpress.org/core/2020/07/22/new-xml-sitemaps-functionality-in-wordpress-5-5/',
    'robots' => 'https://developer.wordpress.org/reference/functions/do_robots/',
    'canonical' => 'https://developer.wordpress.org/reference/functions/rel_canonical/',
    'permalinks' => 'https://wordpress.org/documentation/article/customize-permalinks/',
    'images' => 'https://make.wordpress.org/core/2023/07/13/image-performance-enhancements-in-wordpress-6-3/',
    'speculative' => 'https://make.wordpress.org/core/2025/03/06/speculative-loading-in-6-8/',
    'sitehealth' => 'https://wordpress.org/documentation/article/site-health-screen/',
    'gnoindex' => 'https://developers.google.com/search/docs/crawling-indexing/block-indexing',
    'grobots' => 'https://developers.google.com/search/docs/crawling-indexing/robots/intro',
    'gcanonical' => 'https://developers.google.com/search/docs/crawling-indexing/consolidate-duplicate-urls',
    'gsitemap' => 'https://developers.google.com/search/docs/crawling-indexing/sitemaps/build-sitemap',
    'gjs' => 'https://developers.google.com/search/docs/crawling-indexing/javascript/javascript-seo-basics',
    'gsd' => 'https://developers.google.com/search/docs/appearance/structured-data/sd-policies',
    'gmove' => 'https://developers.google.com/search/docs/crawling-indexing/site-move-with-url-changes',
    'gscperf' => 'https://support.google.com/webmasters/answer/7576553',
];

return [

'body' => static function () use ($s): void { ?>
<p class="bx-open">Most WordPress launch problems are settings, not code. One checkbox left on from staging. Two plugins writing the same tag. A page builder that adds the text after the page has loaded.</p>
<p>None of these shows on the screen. The site looks finished, the client signs it off, and three weeks later someone asks why Google has not indexed it, or why the contact form has been silent.</p>
<p>This checklist is a launch gate. It has fourteen checks to run on the built site, not on the plan. Each one says why it matters, how to check it and what to do. Mark each Pass, Fail or Not sure, and the board builds the list of what to fix before the site goes live. It works as a sign-off sheet between a client and a developer.</p>
<p>Where the article says what WordPress does by default, it means the current release, <?= ext_link($s['releases'], 'WordPress 7.1.4') ?>, published on 28 October 2026, and it links to the WordPress page that says so.</p>

<?php px_answer('wordpress-seo-launch-checklist'); ?>

<h2 id="who-checks-what">Eight Checks Need No Developer</h2>
<p>You do not need to read code to run most of this. Eight checks need only a browser and a login to the WordPress dashboard. Six need a developer, or someone with access to the server. The board below lets you filter by either group.</p>
<p>Run the checks on the staging site once the build is complete, and repeat checks 1, 6 and 7 on the live address on launch day. Those three change when the site moves.</p>

<?php px_flow('launch-timeline', 'Where each check sits, from staging to four weeks after launch', [
    ['While the site is on staging', 'Keep staging closed to crawlers and to the public.', 'Check 2'],
    ['When the build is complete', 'Check what every page template outputs: canonical, title, text in the HTML, headings and structured data.', 'Checks 4, 5, 8, 9, 10, 11'],
    ['The week before launch', 'Finish the redirect list, test speed with everything switched on, send test enquiries and set up backups.', 'Checks 3, 12, 13, 14'],
    ['Launch day, on the live address', 'Confirm the site is open to search engines, the sitemap is the right one and robots.txt is the intended one.', 'Checks 1, 6, 7'],
    ['Weeks one to four', 'Watch the sitemap, the Performance report and the enquiry inbox.', 'After launch'],
], [
    'sub' => 'Three checks are repeated on launch day because they change when the site moves.',
    'cap' => 'Most of the gate is passed before launch day. Launch day itself has three checks.',
]); ?>

<?php lx_glance([
    ['search-visibility', 'The search visibility setting', 'You can check'],
    ['staging', 'Staging is closed', 'Developer'],
    ['permalinks-redirects', 'Permalinks and redirects', 'Developer'],
    ['one-canonical', 'One canonical per page', 'You can check'],
    ['titles-descriptions', 'One source for titles and descriptions', 'You can check'],
    ['one-sitemap', 'One XML sitemap', 'You can check'],
    ['robots-txt', 'The robots.txt file', 'You can check'],
    ['thin-pages', 'Pages WordPress creates on its own', 'Developer'],
    ['text-in-html', 'Text and links in the HTML', 'You can check'],
    ['headings-alt', 'Headings and alt text', 'You can check'],
    ['structured-data', 'Structured data from one place', 'Developer'],
    ['speed-real-setup', 'Speed with everything switched on', 'Developer'],
    ['forms-tracking', 'Forms, spam and tracking', 'You can check'],
    ['updates-backups', 'Updates, backups and access', 'Developer'],
], 'The fourteen checks at a glance'); ?>

<?php lx_board('wp-launch-gate', [
    'mode' => 'tri',
    'label' => 'checks run',
    'filters' => ['quick' => 'You can check', 'project' => 'Needs a developer'],
    'msgs' => [
        [0, 'Start with check 1. It takes two minutes and it is the one that stops a launch being seen at all.'],
        [6, 'Keep going. Mark "Not sure" where you could not tell; those are questions for your developer.'],
        [13, 'Nearly there. Anything marked Fail goes on the list to fix before launch.'],
        [14, 'All fourteen checks run. Fix anything marked Fail, in the order shown under "Fix these first", before the site goes live.'],
    ],
]); ?>

<?php lx_item(1, 'search-visibility', 'The search visibility setting is off in production and on in staging', ['type' => 'quick', 'tags' => ['You can check', 'Takes 2 minutes'], 'tri' => true], static function () use ($s): void { ?>
<p>WordPress has one checkbox that asks search engines to stay away from the whole site. It is useful on a staging site and harmful on a live one. It is also the setting most often carried over by accident when a staging site is copied to production.</p>
<p>The option is "Discourage search engines from indexing this site", on the <?= ext_link($s['reading'], 'Reading settings screen') ?>. Since version 5.3 it works by <?= ext_link($s['noindex53'], 'printing a robots meta tag') ?> with <code>noindex, nofollow</code> on every page. It does not add a block to robots.txt.</p>
<?php lx_split('How to check',
    '<p>In the dashboard, open Settings, then Reading, and look at the checkbox. The ' . ext_link($s['sitehealth'], 'Site Health screen') . ', under Tools, also reports whether the site discourages search engines.</p><p>Then check what the page sends. Open any page, press Ctrl and U to view the source, and search for <code>noindex</code>.</p>',
    'What to do',
    '<ul><li><strong>Staging:</strong> the box is ticked.</li><li><strong>Production:</strong> the box is clear, and no page you want found has <code>noindex</code> in its source.</li><li>Repeat this on the live address on launch day. Do not rely on what staging showed.</li></ul>'); ?>
<?php }); ?>

<?php lx_item(2, 'staging', 'Staging is closed to crawlers and people, and nothing links to it', ['type' => 'project', 'tags' => ['Needs a developer', 'Takes 10 minutes'], 'tri' => true], static function () use ($s): void { ?>
<p>A staging site that search engines can reach is a second copy of your website. It can be indexed beside the real one, with test content and half-written pages.</p>
<p>The setting in check 1 is a request, not a lock. Two points from Google's documentation explain why it should not be the only protection. A <code>noindex</code> rule works <?= ext_link($s['gnoindex'], 'only if the page is not blocked in robots.txt') ?>, because Google has to fetch the page to see the rule. And robots.txt <?= ext_link($s['grobots'], 'controls crawling, not indexing') ?>. A password on the staging site avoids both problems, because nothing can be fetched without it.</p>
<?php lx_split('How to check',
    '<p>Open the staging address in a private browser window. You should be asked for a password before any page loads.</p><p>Then search the built site\'s source for the staging address. Menus, images, canonical tags and sitemap entries sometimes keep it after a copy.</p>',
    'What a failure looks like',
    '<ul><li>The staging site opens for anyone who has the address.</li><li>Staging is protected only by a robots.txt block.</li><li>Pages on the production build link to, or load images from, the staging address.</li></ul>'); ?>
<?php }); ?>

<?php lx_item(3, 'permalinks-redirects', 'Permalinks are final, and every old URL has a redirect', ['type' => 'project', 'tags' => ['Needs a developer', 'Takes an hour or more'], 'tri' => true], static function () use ($s): void { ?>
<p>The address of a page is the thing search engines and other websites remember. Change it after launch and you start again for that page, unless a redirect carries visitors and search engines across.</p>
<p>WordPress offers <?= ext_link($s['permalinks'], 'several permalink structures') ?>: Plain, Day and name, Month and name, Numeric, Post name and Custom. Choose one before content is loaded and do not change it afterwards.</p>
<p>If the new site replaces an old one, Google's <?= ext_link($s['gmove'], 'guidance on site moves') ?> is specific. Map each old URL to its new one. Use permanent, server-side redirects. Avoid chains, where one redirect leads to another. Keep the redirects for at least a year.</p>
<?php lx_split('How to check',
    '<p>Open Settings, then Permalinks, and confirm the structure is the one agreed.</p><p>Ask for the redirect list as a spreadsheet: old address, new address. Then request a few old addresses and read the first line of each response:</p><p><code>curl -sI https://www.example.com/old-page/</code></p>',
    'What a pass looks like',
    '<ul><li>Every old address with traffic or links is on the list.</li><li>Each returns a 301 and a <code>Location</code> line with the final address, in one step.</li><li>Nothing redirects to the home page for want of a better match.</li></ul>'); ?>
<p>Our guide to a <a href="/blog/website-redesign-without-losing-seo/">website redesign without losing SEO</a> covers how to build the redirect map.</p>
<?php }); ?>

<?php lx_item(4, 'one-canonical', 'Each page outputs one canonical, pointing to itself', ['type' => 'quick', 'tags' => ['You can check', 'Takes 10 minutes'], 'tri' => true], static function () use ($s): void { ?>
<p>A canonical tag tells search engines which address is the main one for a page. Two tags that disagree, or one that points somewhere else, leave the choice to the search engine.</p>
<p>Google <?= ext_link($s['gcanonical'], 'treats the canonical as a hint') ?>, not an instruction. It recommends a self-referencing canonical on each page, written as an absolute URL. WordPress core <?= ext_link($s['canonical'], 'prints a canonical link') ?> on single posts and pages. A theme or a plugin may print another.</p>
<?php lx_split('How to check',
    '<p>On five different kinds of page (home, a service page, a blog article, a category, a contact page), view the source and search for <code>rel="canonical"</code>.</p><p>Or count the tags from a terminal:</p><p><code>curl -s https://www.example.com/your-page/ | grep -c \'rel="canonical"\'</code></p>',
    'What a failure looks like',
    '<ul><li>The count is 2 or more.</li><li>The address in the tag is the staging address, or uses <code>http</code> where the site uses <code>https</code>.</li><li>A page points to a different page as its canonical without a reason anyone can give.</li></ul>'); ?>
<?php }); ?>

<?php lx_item(5, 'titles-descriptions', 'Titles and descriptions come from one source', ['type' => 'quick', 'tags' => ['You can check', 'Takes 10 minutes'], 'tri' => true], static function () use ($s): void { ?>
<p>On a WordPress site, the theme can write the title tag, and so can a plugin. When both do, a page ends up with two titles or two descriptions, and you no longer control which one a search engine reads.</p>
<p>Decide which part of the site owns these tags, and make sure nothing else writes them.</p>
<?php lx_split('How to check',
    '<p>View the source of the same five pages and search for <code>&lt;title</code> and <code>name="description"</code>. There should be one of each.</p><p>Read them as a searcher would. Does each title say what that page is, in words a buyer uses?</p>',
    'What a failure looks like',
    '<ul><li>Two title tags or two descriptions on one page.</li><li>The same title on many pages, or the site name alone.</li><li>Titles left from the theme\'s demo content, such as "Home" or "Sample Page".</li></ul>'); ?>
<?php }); ?>

<?php lx_item(6, 'one-sitemap', 'There is one XML sitemap, and it lists only pages meant to be indexed', ['type' => 'quick', 'tags' => ['You can check', 'Takes 10 minutes'], 'tri' => true], static function () use ($s): void { ?>
<p>A sitemap is the list of addresses you are asking search engines to consider. It should match the site you mean to publish: no test pages, no thank-you pages, nothing set to <code>noindex</code>.</p>
<p>WordPress has had <?= ext_link($s['sitemaps'], 'XML sitemaps in core since version 5.5') ?>, at <code>/wp-sitemap.xml</code>, with up to 2,000 URLs per file. Core switches the sitemap off when the setting in check 1 is on. A plugin may produce a second sitemap at another address. Either can do the job. Two at once cannot, because nobody knows which one is maintained.</p>
<p>Google's limit is far higher, at <?= ext_link($s['gsitemap'], '50,000 URLs or 50 MB per sitemap file') ?>, so size is rarely the issue on a B2B site. Google also describes sitemap inclusion as a weak signal for choosing a canonical. A sitemap helps pages get found. It does not settle which version is indexed.</p>
<?php lx_split('How to check',
    '<p>Open <code>yoursite.com/wp-sitemap.xml</code> in a browser, and any other sitemap address your developer names. Decide which one is the sitemap and switch the other off.</p><p>Open the lists inside it and scan the addresses.</p>',
    'What to do',
    '<ul><li>Remove anything from the sitemap that should not be indexed.</li><li>On launch day, confirm it lists the live address, not staging.</li><li>Submit that one sitemap in Search Console.</li></ul>'); ?>
<?php }); ?>

<?php lx_item(7, 'robots-txt', 'The robots.txt is the intended one, and blocks nothing needed to render pages', ['type' => 'quick', 'tags' => ['You can check', 'Takes 5 minutes'], 'tri' => true], static function () use ($s): void { ?>
<p>The robots.txt file tells crawlers where they may go. On WordPress it can come from three places: WordPress itself, a plugin, or a real file uploaded to the server. The one that is live is the one that counts.</p>
<p>When there is no file on the server, WordPress <?= ext_link($s['robots'], 'generates a virtual robots.txt') ?>. It has a general rule that disallows <code>/wp-admin/</code>, allows <code>/wp-admin/admin-ajax.php</code>, and on a public site adds a <code>Sitemap:</code> line.</p>
<?php lx_split('How to check',
    '<p>Fetch the live file and read every line:</p><p><code>curl -s https://www.example.com/robots.txt</code></p><p>Compare it with what your developer says it should contain.</p>',
    'What a failure looks like',
    '<ul><li>A <code>Disallow: /</code> under <code>User-agent: *</code>, left from staging.</li><li>Rules that block the folders holding the theme\'s styles and scripts. Search engines need those to see the page as a visitor does.</li><li>A <code>Sitemap:</code> line that names the staging address, or a sitemap you switched off in check 6.</li></ul>'); ?>
<p>If AI search matters to you, the same file decides which AI crawlers may read the site. Our guide to <a href="/blog/ai-crawler-access-problems/">AI crawler access problems</a> covers that, and the layers beyond robots.txt.</p>
<?php }); ?>

<?php px_cta_mid([
    'label' => 'Free SEO audit tool',
    'art' => 'scan',
    'h' => 'Test One Page\'s Basics in Seconds',
    'p' => 'Enter a page address and get twenty checks on it: indexing, title and canonical tags, speed signals, structured data and whether AI crawlers can read it. No sign-up.',
    'href' => '/free-seo-audit/',
    'btn' => 'Run the Free Audit',
    'cta' => 'blog-wp-launch-audit',
    'note' => 'The tool reads public pages, so run it on the live address. It will not get past a password on staging.',
]); ?>

<?php lx_item(8, 'thin-pages', 'Pages WordPress creates on its own are handled on purpose', ['type' => 'project', 'tags' => ['Needs a developer', 'Takes 30 minutes'], 'tri' => true], static function () use ($s): void { ?>
<p>You wrote thirty pages. The site may have several hundred addresses. A WordPress site can produce pages nobody wrote: one for each uploaded image, one for each author, tag and date, and one for every internal search. Most hold a title and a list of links, and little else.</p>
<p>These need a decision each, not a default. Remember from check 4 that core prints its canonical on single posts and pages. As we read that function, archive pages get none from core, so what they output depends on your theme and plugins.</p>
<?php lx_split('How to check',
    '<p>Ask your developer for a list of every kind of page the site can produce. For each kind, visit one example and view its source.</p><p>Look in the sitemap from check 6 for kinds you did not expect.</p>',
    'Decide, for each kind',
    '<ul><li><strong>Keep and index:</strong> it has real content and a reason to be found, such as a category page with an introduction.</li><li><strong>Keep, with <code>noindex</code>:</strong> useful to visitors, not to searchers, such as internal search results.</li><li><strong>Switch off or redirect:</strong> it adds nothing, such as a page for each image.</li></ul>'); ?>
<p>A <code>noindex</code> rule and a robots.txt block do not combine. As in check 2, Google has to be able to fetch a page to see its <code>noindex</code>.</p>
<?php }); ?>

<?php lx_item(9, 'text-in-html', 'The main text and links are in the HTML source', ['type' => 'quick', 'tags' => ['You can check', 'Takes 5 minutes'], 'tri' => true], static function () use ($s): void { ?>
<p>WordPress normally sends a complete page from the server. Some page builders, sliders, tabs and "load more" buttons do not. They send a shell and add the text afterwards with JavaScript. A visitor sees no difference. A crawler that does not run scripts sees an empty space.</p>
<p>Google can run JavaScript, and its <?= ext_link($s['gjs'], 'JavaScript guidance') ?> still carries a warning that matters at launch. If the first HTML a page sends contains <code>noindex</code>, Google may not render the page at all, so a script cannot remove the rule later.</p>
<?php lx_split('How to check',
    '<p>Choose a sentence from the middle of a service page. View the page source and search for it.</p><p>Or from a terminal:</p><p><code>curl -s https://www.example.com/your-page/ | grep -c "a sentence from the page"</code></p><p>Do the same for a link in the main menu and a link in the body.</p>',
    'What a failure looks like',
    '<ul><li>The sentence is on the screen and not in the source.</li><li>Text inside tabs or accordions is missing until you click.</li><li>Menu links are built by a script and are not ordinary links in the source.</li></ul>'); ?>
<p>The same test matters for AI assistants. See <a href="/blog/how-ai-agents-evaluate-b2b-websites/">how AI agents evaluate a B2B website</a> for what they can and cannot read.</p>
<?php }); ?>

<?php lx_item(10, 'headings-alt', 'One H1 per page, headings in order, images with useful alt text', ['type' => 'quick', 'tags' => ['You can check', 'Takes 15 minutes'], 'tri' => true], static function () use ($s): void { ?>
<p>Headings are the outline of a page. People using screen readers move through a page by its headings, and search engines use them to understand what each part is about. Themes often break the outline for visual reasons: a logo set as an H1, or a heading level chosen for its size.</p>
<?php lx_split('How to check',
    '<p>On each page template, search the source for <code>&lt;h1</code>. There should be one, and it should name the page.</p><p>Read the headings in order, top to bottom. They should make sense as a contents list, with no level skipped for styling.</p><p>In the Media Library, open ten images used on key pages and read their alt text.</p>',
    'What a failure looks like',
    '<ul><li>Two H1s, or an H1 that is the site name on every page.</li><li>Headings used for size, such as an H4 above an H2.</li><li>Alt text that is empty on an image carrying information, or that is the file name.</li></ul>'); ?>
<p>The <a href="/blog/website-accessibility-checklist/">website accessibility checklist</a> has eleven further tests, with a contrast checker.</p>
<?php }); ?>

<?php lx_item(11, 'structured-data', 'Structured data comes from one place and matches what the page shows', ['type' => 'project', 'tags' => ['Needs a developer', 'Takes 20 minutes'], 'tri' => true], static function () use ($s): void { ?>
<p>Structured data describes a page to machines: this is an organisation, this is an article, these are its questions and answers. On WordPress, a theme and one or more plugins may each add their own, and the copies can contradict each other.</p>
<p>Google's <?= ext_link($s['gsd'], 'structured data guidelines') ?> recommend the JSON-LD format. They also say that correct markup does not guarantee a rich result. So the aim here is accuracy, not a visual reward in the results.</p>
<?php lx_split('How to check',
    '<p>View the source of a key page and search for <code>application/ld+json</code>. Note how many blocks there are and what each describes.</p><p>Compare each fact in the markup with the visible page: company name, address, phone number, prices, questions and answers.</p>',
    'What a failure looks like',
    '<ul><li>Two blocks describing the organisation, with different details.</li><li>Markup for content the page does not show, such as reviews or questions that appear nowhere on it.</li><li>Demo data from the theme still in the markup.</li></ul>'); ?>
<p>For which types a B2B site needs, with copy-ready code, see <a href="/blog/schema-markup-for-b2b-websites/">schema markup for B2B websites</a>.</p>
<?php }); ?>

<?php lx_item(12, 'speed-real-setup', 'Speed is checked with real plugins, tracking and consent tools switched on', ['type' => 'project', 'tags' => ['Needs a developer', 'Takes 30 minutes'], 'tri' => true], static function () use ($s): void { ?>
<p>A speed test on a bare staging site measures a site nobody will visit. The live site will carry analytics, a consent banner, chat, forms and whatever marketing adds in the first month. Test the page as it will run.</p>
<p>WordPress already does some of this work itself. Core has lazy loaded images since 5.5 and iframes since 5.7. Since 6.3 it <?= ext_link($s['images'], 'adds a high fetch priority') ?>, the attribute <code>fetchpriority="high"</code>, to the image it judges to be the main one. Since 6.8, <?= ext_link($s['speculative'], 'speculative loading') ?> is on by default for logged-out visitors on sites with pretty permalinks. A plugin that repeats one of these adds weight and gains nothing.</p>
<?php lx_split('How to check',
    '<p>Switch on everything that will run on the live site. Run PageSpeed Insights on the home page, a service page and an article, on mobile, several times each, and use the middle result.</p><p>Check whether the main image of each page is being lazy loaded. It should not be.</p>',
    'What to do',
    '<ul><li>Remove scripts that load on every page and are used on one.</li><li>Fix the largest image on each template first.</li><li>After launch, read the field data once it appears. It describes real visits, and the lab test does not.</li></ul>'); ?>
<p>Our <a href="/blog/core-web-vitals-guide-for-business-owners/">Core Web Vitals guide for business owners</a> explains the measures, and <a href="/blog/pagespeed-score-vs-qualified-leads/">what a PageSpeed score misses</a> explains why a good score is not the finish line.</p>
<?php }); ?>

<?php lx_item(13, 'forms-tracking', 'Forms deliver, spam is filtered without losing real leads, and submissions are tracked', ['type' => 'quick', 'tags' => ['You can check', 'Takes 20 minutes'], 'tri' => true], static function () use ($s): void { ?>
<p>For a B2B website, the form is the point of the whole build. It is also the part most likely to work on staging and fail in production, because email delivery depends on the live domain and the live server.</p>
<?php lx_split('How to check',
    '<p>Send a real test enquiry through every form, from a personal email address, on the live site.</p><p>Confirm three things for each: the message reached the right inbox, every field arrived, and the submission was recorded in analytics and in the CRM if you use one.</p>',
    'What a failure looks like',
    '<ul><li>The form shows a thank-you message and nothing arrives.</li><li>The message lands in a spam folder, or goes to the developer\'s address.</li><li>A field the visitor filled in is missing from the email.</li><li>The spam filter deletes suspect messages. It should hold them for review, so a real lead is never lost.</li></ul>'); ?>
<p>See <a href="/blog/how-to-stop-junk-leads-on-your-website/">how to stop junk leads on your website</a> for a filter that quarantines and does not delete, and <a href="/blog/measure-qualified-leads-from-organic-search/">how to measure qualified leads from organic search</a> for what to store on every enquiry.</p>
<?php }); ?>

<?php lx_item(14, 'updates-backups', 'Updates, backups and access are set up, and unused plugins and themes are removed', ['type' => 'project', 'tags' => ['Needs a developer', 'Takes 30 minutes'], 'tri' => true], static function () use ($s): void { ?>
<p>This check is not about search on day one. It is about whether the site is still healthy in month six. A hacked or broken site loses its search visibility quickly, and the cause is usually something left behind at launch.</p>
<?php lx_split('How to check',
    '<p>Open the Plugins and Themes screens and ask what each item is for. Open the Users screen and ask who each account belongs to.</p><p>Ask your developer three questions. Who applies updates, and how often? Where are backups kept, and when was one last restored as a test? Who is told if the site goes down?</p><p>The Site Health screen from check 1 also reports the HTTPS status.</p>',
    'What a pass looks like',
    '<ul><li>No inactive plugins or spare themes left installed.</li><li>No shared logins, and no administrator accounts for people who have left the project.</li><li>Backups stored somewhere other than the web server, with one test restore done.</li><li>A named person responsible for updates.</li></ul>'); ?>
<?php }); ?>

<h2 id="after-launch">The First Four Weeks After Launch</h2>
<p>Passing the gate means the site is ready to be found. It does not mean it has been. The weeks after launch are when a missed redirect or a stray <code>noindex</code> shows itself.</p>

<?php px_table('What to look at after launch, and when', ['When', 'Look at', 'What you are checking'], [
    ['Launch day', 'Checks 1, 6 and 7 on the live address', 'The site is open, the sitemap is the right one and robots.txt is the intended one'],
    ['Launch day', 'A test enquiry through every form', 'Email still delivers from the live domain'],
    ['Week 1', 'The sitemap in Search Console', 'It was read without errors, and it lists the live addresses'],
    ['Weeks 1 and 2', 'A sample of old addresses', 'Each still redirects in one step to the right new page'],
    ['Weeks 2 to 4', 'The Performance report in Search Console', 'Impressions and clicks for the pages that had them before'],
    ['Week 4', 'Field data in PageSpeed Insights, if there is any', 'Speed for real visitors, with all scripts running'],
    ['Every week', 'The enquiry inbox and the spam quarantine', 'Real leads are arriving, and none are being held by mistake'],
]); ?>

<p>The <?= ext_link($s['gscperf'], 'Performance report') ?> shows clicks, impressions, click-through rate and average position, and its default view covers three months. Compare like with like: the same pages, over the same number of days, before and after. A fall in the first weeks after a move with new addresses is not unusual. A page that has dropped to nothing is a reason to recheck its redirect and its <code>noindex</code>.</p>

<h2 id="real-rebuild">One Rebuild, Checked Page by Page</h2>
<p>FirstChoice Used Auto Parts sells used parts in the United States, and it is one of our WordPress projects. We rebuilt 99 of its pages: 31 part pages, a brands hub and 67 make pages.</p>
<p>The gate was applied to every page, not to a sample. Each of the 99 was fetched after publishing and verified for a correct title, a canonical, one H1, a working form and valid FAQ markup. Those are checks 4, 5, 10, 11 and 13 on this list. The site's URLs did not change, so there was no redirect map to get wrong.</p>
<p>The forms had their own delivery test. Every quote form routes to one CRM mailbox, and that was confirmed with a real delivery check. The <a href="/case-studies/firstchoice-used-auto-parts/">FirstChoice case study</a> has the full account and the published figures.</p>
<p>Checking page by page is slow. It is also the only way to know, and it is why this checklist asks you to look at several page types and not only the home page.</p>

<h2 id="what-to-skip">Four Things You Can Skip</h2>
<p>Launch lists grow because nobody removes anything. These four items appear on many of them and are rarely needed on a current WordPress release.</p>
<ul class="post-list">
    <li><strong>A second sitemap for its own sake.</strong> Core has produced one since 5.5. Add another tool only if you need something the core sitemap does not do, and then switch the core one off.</li>
    <li><strong>A separate tool for lazy loading images.</strong> Core has done this since 5.5 for images and 5.7 for iframes.</li>
    <li><strong>A separate tool to preload the next page.</strong> Speculative loading has been in core since 6.8.</li>
    <li><strong>Extra structured data added in the hope of a rich result.</strong> Google says correct markup does not guarantee one. Mark up what the page shows and stop there.</li>
</ul>
<p>The rule behind all four: before adding a plugin, check whether WordPress already does the job. Fewer moving parts means fewer duplicated tags, which is where checks 4, 5, 6 and 11 usually fail.</p>
<p>If the site is still at the planning stage, the <a href="/blog/website-design-brief-questions/">website design brief questions</a> will get these requirements written down before the build starts.</p>
<?php },

'closing' => static function (): void { ?>
<h2 id="final-thoughts">Final Thoughts</h2>
<p>A WordPress site can be well designed, well written and invisible, because of one checkbox. The fourteen checks exist to catch that kind of fault while it is still cheap to fix.</p>
<p>Run them on the built site, repeat three of them on launch day, and keep the marked-up board as the record of what was agreed. Passing the gate does not promise rankings or enquiries. It means that nothing technical is standing between your pages and the people looking for them.</p>

<?php px_cta_main([
    'h' => 'Want a Second Pair of Eyes Before You Launch?',
    'p' => 'Ask for a free website review. We look at the built site against these checks and tell you what to fix first.',
    'href' => '/wordpress-web-design/#enquire',
    'btn' => 'Get My Free Website Review',
    'cta' => 'blog-wp-launch-review',
    'link' => ['/wordpress-web-design/', 'See WordPress Web Design', 'blog-wp-launch-service'],
]); ?>

<?php px_author(); ?>
<?php },

];
