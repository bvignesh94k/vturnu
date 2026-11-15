<?php
/**
 * Diagnostic guide: six layers between an AI crawler and a page's content,
 * tested in order, with a pass or fail board (includes/listicle-kit.php).
 * Loaded by templates/blog-post.php through the 'rich' key.
 *
 * "We" is used only for the two diagnoses published in our case studies
 * (FlowEazy and Cafy) and for the checks we ran ourselves on 14 November 2026.
 * Vendor behaviour is reported as each vendor documents it, from the page
 * linked in the sentence, read on 13 November 2026. The October 2026 tests of
 * JavaScript rendering are small and unconfirmed, and are labelled that way.
 * The log lines and counts in layer 6 are examples, not data from a site.
 * Nothing here promises a citation.
 */

$s = [
    'openai' => 'https://developers.openai.com/api/docs/bots',
    'anthropic' => 'https://support.claude.com/en/articles/8896518-does-anthropic-crawl-data-from-the-web-and-how-can-site-owners-block-the-crawler',
    'perplexity' => 'https://docs.perplexity.ai/docs/resources/perplexity-crawlers',
    'gcommon' => 'https://developers.google.com/crawling/docs/crawlers-fetchers/google-common-crawlers',
    'gfetchers' => 'https://developers.google.com/crawling/docs/crawlers-fetchers/google-user-triggered-fetchers',
    'gai' => 'https://developers.google.com/search/docs/appearance/ai-features',
    'gjs' => 'https://developers.google.com/search/docs/crawling-indexing/javascript/javascript-seo-basics',
    'cfdocs' => 'https://developers.cloudflare.com/bots/additional-configurations/block-ai-bots/',
    'cf2025' => 'https://www.cloudflare.com/press/press-releases/2025/cloudflare-just-changed-how-ai-crawlers-scrape-the-internet-at-large/',
    'cfsync' => 'https://blog.cloudflare.com/bot-preference-sync/',
    'vercel' => 'https://vercel.com/blog/the-rise-of-the-ai-crawler',
    'genezio' => 'https://genezio.com/research/openai-crawler-runs-javascript/',
];

return [

'body' => static function () use ($s): void { ?>
<p class="bx-open">Your robots.txt allows every AI crawler. Yet when you ask ChatGPT or Perplexity about your company, the answer is thin, out of date, or about someone else.</p>
<p>"Allowed" is not the same as "readable". Between a crawler and your content there are six layers, and robots.txt is only the first. A firewall can refuse the request. A CDN setting can block it. The page can arrive with no text in it, because the text is added later by JavaScript the crawler never runs.</p>
<p>This guide is a fault-finding sequence. Test the six layers in order, mark each one Pass, Fail or Not sure, and the board builds your list of fixes. Each test takes a few minutes and most need nothing more than a terminal or your browser.</p>

<?php px_answer('ai-crawler-access-problems'); ?>

<h2 id="three-kinds">Three Kinds of AI Crawler, Three Different Consequences</h2>
<p>Before testing anything, be clear about which crawlers you care about. AI companies run three kinds, and blocking one does not have the effect people expect on the others.</p>

<?php px_table('The three kinds of AI crawler and what blocking each one does', ['Kind', 'What it does', 'Examples', 'If it cannot read you'], [
    ['Search and answer crawlers', 'Find and index pages so an assistant can show and link them', 'OAI-SearchBot, Claude-SearchBot, PerplexityBot, Googlebot', 'You are not offered as a source in that assistant\'s search answers'],
    ['Fetchers that act for a person', 'Load one page because someone asked about it', 'ChatGPT-User, Claude-User, Perplexity-User', 'The assistant tells the person it could not open your page'],
    ['Training crawlers and controls', 'Collect content that may be used to train models', 'GPTBot, ClaudeBot, Google-Extended', 'Your content is left out of future training. Search answers are not affected'],
]); ?>

<p>The split matters because each vendor documents these separately. <?= ext_link($s['openai'], 'OpenAI') ?> lists OAI-SearchBot for ChatGPT search and GPTBot for training, each with its own robots.txt token. <?= ext_link($s['anthropic'], 'Anthropic') ?> does the same with Claude-SearchBot, Claude-User and ClaudeBot. Google's AI Overviews and AI Mode <?= ext_link($s['gai'], 'rely on Googlebot') ?>, while <?= ext_link($s['gcommon'], 'Google-Extended') ?> is a robots.txt token with no crawler of its own. Google says it controls the use of your content for Gemini training and grounding, and does not affect Google Search.</p>
<p>The fetchers follow different rules again. OpenAI says robots.txt rules may not apply to ChatGPT-User, because a person started the request. <?= ext_link($s['perplexity'], 'Perplexity') ?> says Perplexity-User generally ignores robots.txt for the same reason. Google's <?= ext_link($s['gfetchers'], 'user-triggered fetchers') ?> generally ignore it too. So robots.txt is a reliable control for the first and third rows of the table, and a weak one for the second.</p>

<?php px_cta_mid([
    'label' => 'Free AI crawler checker',
    'art' => 'scan',
    'h' => 'Check Layer One in a Minute',
    'p' => 'Our free AI Crawler Checker reads your robots.txt against 23 AI crawlers and controls, then shows how much of a page can be read without JavaScript.',
    'href' => '/ai-crawler-checker/',
    'btn' => 'Open the AI Crawler Checker',
    'cta' => 'blog-crawler-checker',
    'note' => 'The checker covers robots.txt and the page\'s HTML. The other layers below need a terminal and your server logs.',
]); ?>

<?php lx_glance([
    ['robots-rules', 'The robots.txt rules', 'Marketing'],
    ['firewall', 'The firewall and bot rules', 'Host or security'],
    ['cdn-settings', 'The CDN\'s AI settings', 'CDN account owner'],
    ['raw-html', 'The page without JavaScript', 'Developer'],
    ['walls', 'Walls in front of the content', 'Developer and marketing'],
    ['server-log', 'The server log', 'Host or developer'],
], 'The six layers, in the order to test them'); ?>

<?php lx_board('ai-crawler-layers', [
    'mode' => 'tri',
    'label' => 'layers tested',
    'msgs' => [
        [0, 'Pick one page that matters, such as a service page or an article, and run every test on that page.'],
        [3, 'Halfway. Mark "Not sure" where you could not run the test; those are questions for your developer or host.'],
        [5, 'One layer to go. The server log is the one that settles it.'],
        [6, 'All six layers tested. Start with anything marked Fail, in the order shown under "Fix these first".'],
    ],
]); ?>

<?php lx_item(1, 'robots-rules', 'The robots.txt rules', ['tags' => ['Owner: marketing', 'Takes 5 minutes'], 'tri' => true], static function () use ($s): void { ?>
<p>Start here, because it is the cheapest test and the one every vendor documents. A rule can block a crawler by name, or block everything under a general rule that was left behind from a staging site.</p>
<?php lx_split('How to test',
    '<p>Open <code>yoursite.com/robots.txt</code> in a browser, or run the <a href="/ai-crawler-checker/">AI Crawler Checker</a>. Look for a <code>Disallow: /</code> under <code>User-agent: *</code>, and for rules that name the search crawlers in the table above.</p><p>Check every subdomain you publish on, such as a blog or a help centre. Anthropic\'s documentation says its opt-out applies per subdomain, and each subdomain has its own file.</p>',
    'What a failure looks like',
    '<ul><li>The search crawlers are disallowed, often by a rule meant only for training crawlers.</li><li>The main site allows crawlers and the blog subdomain does not.</li><li>The file was changed today. OpenAI and Perplexity both say a robots.txt change can take up to about 24 hours to take effect.</li></ul>'); ?>
<?php }); ?>

<?php lx_item(2, 'firewall', 'The firewall and bot rules', ['tags' => ['Owner: host or security team', 'Takes 10 minutes'], 'tri' => true], static function () use ($s): void { ?>
<p>A web application firewall or a security plugin decides whether a request gets an answer at all. Many are set to challenge anything that looks automated, and an AI crawler looks automated because it is.</p>
<p>The test is to request the same page twice, once as a browser and once with a crawler's name in the user agent, and compare what comes back. Each command prints the status code and the size of the response.</p>
<?php lx_code(<<<'SH'
# 1. As a normal browser
curl -s -o /dev/null -w "%{http_code} %{size_download}\n" -A "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0 Safari/537.36" https://www.example.com/your-page/

# 2. With a search crawler's name in the user agent
curl -s -o /dev/null -w "%{http_code} %{size_download}\n" -A "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36; compatible; OAI-SearchBot/1.4; +https://openai.com/searchbot" https://www.example.com/your-page/
SH, 'Terminal'); ?>
<p>On Windows, use <code>curl.exe</code> and replace <code>/dev/null</code> with <code>NUL</code>. Repeat the second command with <code>ClaudeBot</code> and <code>PerplexityBot</code> in place of <code>OAI-SearchBot</code>.</p>
<?php lx_split('How to read it',
    '<ul><li><strong>200 and a similar size for both:</strong> no blanket rule against that name.</li><li><strong>403:</strong> refused by a rule.</li><li><strong>429:</strong> rate limited.</li><li><strong>503, or 200 with a much smaller size:</strong> probably a challenge page in place of your content.</li></ul>',
    'Why this is only a first hint',
    '<p>Some firewalls check the address a request comes from, not only its name. Your test comes from your own address, so a firewall may refuse you as an impostor while letting the real crawler through, or the reverse.</p><p>A pass here rules out the crudest block. Layer 6 is the proof.</p>'); ?>
<?php }); ?>

<?php lx_item(3, 'cdn-settings', 'The CDN\'s AI settings', ['tags' => ['Owner: whoever holds the CDN account', 'Takes 10 minutes'], 'tri' => true], static function () use ($s): void { ?>
<p>If your site sits behind a content delivery network, the network may have its own switch for AI crawlers, separate from your firewall rules and from the robots.txt file in your CMS. People are often surprised to find it, because nobody on the team chose the setting.</p>
<p>Cloudflare is the clearest example, and its defaults have moved. In July 2025 it <?= ext_link($s['cf2025'], 'announced that new domains would block AI crawlers by default') ?>. During 2026 it replaced that with separate policies for search, agents and training. Its <?= ext_link($s['cfdocs'], 'documentation, updated in November 2026') ?>, says new domains now default to allowing all three. A domain added in between may have whatever was chosen, or defaulted, on the day it was set up.</p>
<?php lx_split('How to test',
    '<p>Log in to the CDN and find the AI crawler or bot settings. Note what is set for search crawlers, agents and training.</p><p>Then fetch the live robots.txt and compare it with the file in your CMS:</p><p><code>curl -s https://www.example.com/robots.txt</code></p>',
    'What a failure looks like',
    '<ul><li>Search crawlers are set to Block, or a general "block AI bots" option is on.</li><li>The live robots.txt has lines your CMS file does not. Cloudflare can ' . ext_link($s['cfsync'], 'write its policy into robots.txt') . ' for you.</li><li>A bot protection mode is challenging every automated visitor. Perplexity\'s documentation notes that Cloudflare\'s Bot Fight Mode can still challenge its crawlers.</li></ul>'); ?>
<?php }); ?>

<?php lx_item(4, 'raw-html', 'The page without JavaScript', ['tags' => ['Owner: developer', 'Takes 5 minutes'], 'tri' => true], static function () use ($s): void { ?>
<p>This is the layer that catches modern websites. A page built with a JavaScript framework can send the browser an almost empty document and fill in the text afterwards. A person never notices. A crawler that does not run JavaScript receives the empty document and leaves.</p>
<p>The largest published study of this is by <?= ext_link($s['vercel'], 'Vercel and MERJ') ?>, from December 2024. Across their network, none of the main AI crawlers they tracked ran JavaScript, including OpenAI's, Anthropic's and Perplexity's. Googlebot did. Google's own <?= ext_link($s['gjs'], 'JavaScript guidance') ?> still recommends rendering on the server, because not every bot runs scripts.</p>
<p class="bx-note"><strong>A newer and smaller finding.</strong> In October 2026, a <?= ext_link($s['genezio'], 'small log test by Genezio') ?> reported that OpenAI's search crawler ran JavaScript on its own website. OpenAI has not confirmed it, and the test covers very few sites. It does not change the advice: text that is in the HTML works for every crawler, today.</p>
<?php lx_split('How to test',
    '<p>Fetch the page without a browser and count the lines that contain a sentence from the middle of your article:</p><p><code>curl -s https://www.example.com/your-article/ | grep -c "a sentence from the article"</code></p><p>No terminal? Open the page, press Ctrl and U to view the source, and search it for the sentence.</p>',
    'What a failure looks like',
    '<ul><li>The command prints 0, or the sentence is not in the source.</li><li>The source is short and mostly script tags.</li><li>Headings and menus are present, and the article body is not.</li></ul>'); ?>
<?php }); ?>

<?php lx_item(5, 'walls', 'Walls in front of the content', ['tags' => ['Owner: developer and marketing', 'Takes 10 minutes'], 'tri' => true], static function () use ($s): void { ?>
<p>A page can return a normal response with real HTML and still hide what matters. Anything a visitor has to click through, log in to or wait for is a wall to a crawler, which does none of those things.</p>
<?php lx_split('What to look for',
    '<ul><li><strong>Logins and gated pages.</strong> Specifications, pricing guidance or case studies behind a form.</li><li><strong>Consent screens</strong> that replace the page until someone accepts.</li><li><strong>Country blocks.</strong> Crawlers visit from data centres, often in another country.</li><li><strong>Content loaded on a click:</strong> tabs, accordions and "read more" links that fetch text only when pressed.</li><li><strong>Facts locked in PDFs or images.</strong></li></ul>',
    'How to test',
    '<p>Open the page in a private window with JavaScript switched off in the browser\'s settings. What you can read without clicking is roughly what a crawler gets.</p><p>Check the page for <code>noindex</code> and <code>nosnippet</code> too. Google says its AI features follow the same snippet controls as Search.</p>'); ?>
<?php }); ?>

<?php lx_item(6, 'server-log', 'The server log', ['tags' => ['Owner: host or developer', 'Takes 20 minutes'], 'tri' => true], static function () use ($s): void { ?>
<p>Every test so far is you, pretending to be a crawler. The log is the record of what happened when the real one came. It answers three questions: did it visit, which pages did it ask for, and what did it receive?</p>
<p>Ask your host for the access logs for the last 30 days, or use the crawler report in your CDN. Then count responses by status code for the AI crawlers:</p>
<?php lx_code(<<<'SH'
grep -iE "OAI-SearchBot|ChatGPT-User|GPTBot|ClaudeBot|Claude-SearchBot|Claude-User|PerplexityBot|Perplexity-User" access.log | awk '{print $9}' | sort | uniq -c
SH, 'Terminal'); ?>
<p>The output is a count beside each status code. This is an example of the shape, not data from a real site:</p>
<?php lx_code(<<<'TXT'
    212 200
     31 301
     57 404
     18 403
TXT, 'Example output'); ?>
<?php lx_split('How to read it',
    '<ul><li><strong>No lines at all:</strong> the crawlers are not reaching the server. Go back to layers 1 to 3.</li><li><strong>403 or 503:</strong> they arrive and are refused.</li><li><strong>Many 404s:</strong> they are asking for addresses that no longer exist. The Vercel study found that about a third of the requests from ChatGPT\'s and Claude\'s crawlers ended in a 404. Redirect old addresses.</li><li><strong>200s on the pages that matter:</strong> access is working.</li></ul>',
    'Check that the visitor is genuine',
    '<p>Anyone can send a request with a crawler\'s name. OpenAI, Anthropic and Perplexity each publish the address ranges their crawlers use, linked from the documentation pages above. Compare the addresses in your log with those lists before you trust a line.</p>'); ?>
<?php }); ?>

<h2 id="two-diagnoses">Two Diagnoses From Our Own Work</h2>
<p>Both of these were layer 4 failures. Both sites have a robots.txt that allows AI crawlers, which we checked on 14 November 2026. On both, the articles were unreadable to a crawler that does not run JavaScript.</p>
<ul class="post-list">
    <li><strong>FlowEazy.</strong> The blog loaded its articles through JavaScript, so AI crawlers received a page without the article. The fix is to render article pages on the server. It is built and, at the time of writing, waiting for the client's final server upload. The <a href="/case-studies/floweazy-odoo-partner-website/">FlowEazy case study</a> has the background.</li>
    <li><strong>Cafy.</strong> Blog articles stayed invisible until JavaScript ran. We documented the fix for the client's developer, who shipped it. When we repeated the layer 4 test on 14 November 2026, an article returned its full text, about 2,000 words, in the raw HTML. See the <a href="/case-studies/cafy-careers-uk-job-platform/">Cafy case study</a>.</li>
</ul>
<p>Neither problem showed up in a robots.txt check, a rankings report or a speed score. Our article on <a href="/blog/pagespeed-score-vs-qualified-leads/">what a PageSpeed score cannot see</a> looks at the same blind spot from the other side.</p>

<h2 id="symptom-to-layer">From Symptom to Layer</h2>
<p>If you cannot test everything today, start from what you have noticed. Each symptom points to the layers most likely to be at fault.</p>

<?php px_table('What you see, and where to look first', ['What you notice', 'Look first at', 'Why'], [
    ['You rank in Google, and ChatGPT or Perplexity never cite you', 'Layers 1, 2 and 3', 'Googlebot is allowed through, and the other crawlers are refused by a bot rule'],
    ['Assistants cite your home page and never your articles', 'Layer 4', 'The home page is plain HTML, and the articles are filled in by JavaScript'],
    ['Assistants describe you with old information', 'Layers 1 and 6', 'The search crawler is blocked or has not visited, so the answer leans on older data'],
    ['Citations stopped suddenly', 'Layers 2 and 3', 'A firewall rule, a security plugin or a CDN default changed'],
    ['An assistant says it could not open your page', 'Layers 2, 3 and 5', 'The fetcher that acts for a person was challenged or met a wall'],
    ['Specifications or certificates are missing from answers', 'Layers 4 and 5', 'The facts are in a PDF, an image or a tab that loads on a click'],
]); ?>

<h2 id="who-fixes-what">Who Fixes What</h2>
<p>Access problems last because each layer belongs to someone different, and none of them is asked the whole question. This table is the message to send.</p>

<?php px_table('The owner of each layer, and what to ask them for', ['Layer', 'Who can change it', 'What to ask for'], [
    ['1. robots.txt', 'Marketing, with the developer', 'A written policy: which crawlers are allowed, which are not, and why'],
    ['2. Firewall', 'Host or security team', 'Verified AI search crawlers allowed through, matched by the operators\' published address ranges'],
    ['3. CDN settings', 'Whoever holds the CDN account', 'A review of the AI crawler policies and the bot protection mode'],
    ['4. Rendering', 'Developer', 'Main text, headings and links present in the HTML the server sends'],
    ['5. Walls', 'Developer and marketing', 'Public pages readable without a login, a consent click or a second request'],
    ['6. Logs', 'Host or developer', 'Thirty days of access logs, or a crawler report from the CDN, every month'],
]); ?>

<p>Whether to allow training crawlers is a separate decision from all of this, and it belongs to the business, not to the developer. You can allow search and refuse training. The vendors document the two separately so that you can.</p>

<h2 id="what-not-to-do">Three Things Not to Do</h2>
<ul class="post-list">
    <li><strong>Do not serve crawlers a different page from the one visitors see.</strong> Search engines treat that as cloaking. Fix the page for everyone.</li>
    <li><strong>Do not block every AI crawler to "protect content" without knowing the cost.</strong> Blocking the search crawlers removes you from the answers your buyers are reading. Decide per kind of crawler.</li>
    <li><strong>Do not treat one passed test as permanent.</strong> A plugin update, a new firewall rule or a changed CDN default can close a layer overnight. Repeat layers 2, 3 and 6 every quarter.</li>
</ul>
<p>Access is also only the first condition. A crawler that can read your page still has to find something worth quoting. Our guide to <a href="/blog/how-to-measure-ai-search-visibility/">measuring AI search visibility</a> covers how to track what happens next, and <a href="/blog/how-ai-agents-evaluate-b2b-websites/">how AI agents evaluate a B2B website</a> covers the agents that click and type, which face a different set of obstacles.</p>
<?php },

'closing' => static function (): void { ?>
<h2 id="final-thoughts">Access Comes First, and It Is Usually a Dull Fix</h2>
<p>When an AI assistant gets your company wrong, the cause is often dull: a setting, a status code, a page that arrives empty. Those are good problems to have, because they can be found in an afternoon and fixed without rewriting anything.</p>
<p>Work down the six layers in order and stop at the first failure. Fixing access does not guarantee that anyone will cite you. It only makes it possible, and nothing else you do for AI search works until it is.</p>

<?php px_cta_main([
    'h' => 'Find Out Where AI Crawlers Stop on Your Site',
    'p' => 'Ask for a free AI visibility report. We check what the main assistants say about you and where their crawlers are being turned away.',
    'href' => '/ai-seo/#enquire',
    'btn' => 'Get My Free AI Visibility Report',
    'cta' => 'blog-crawler-report',
    'link' => ['/ai-seo/', 'See AI SEO Services', 'blog-crawler-service'],
]); ?>

<?php px_author(); ?>
<?php },

];
