<?php
/**
 * Explainer: what AI browser agents can do on a website today, what stops
 * them, and two tests a reader can run (Agentic Browsing in Lighthouse, and a
 * six-task check with an AI browser).
 * Loaded by templates/blog-post.php through the 'rich' key.
 *
 * Every capability is taken from the vendor's own page linked in the sentence,
 * read on 13 November 2026. Products in this area change quickly: OpenAI's
 * help pages say ChatGPT agent and the Atlas browser are no longer available,
 * so the article names what each vendor documents on that date and says so.
 * Lighthouse facts come from its releases, source and documentation. Client
 * results are as published in includes/data/cases-real.php. Nothing here says
 * the Lighthouse category affects rankings, because Google has not said so.
 */

$s = [
    'oai_browser' => 'https://help.openai.com/en/articles/20001280-using-cloud-browser-in-chatgpt',
    'oai_access' => 'https://developers.openai.com/commerce/agent-access',
    'g_auto' => 'https://support.google.com/chrome/answer/16821166?hl=en',
    'claude_perm' => 'https://support.claude.com/en/articles/12902446-claude-in-chrome-permissions-guide',
    'comet' => 'https://www.perplexity.ai/hub/blog/mitigating-prompt-injection-in-comet',
    'lh_scoring' => 'https://developer.chrome.com/docs/lighthouse/agentic-browsing/scoring',
    'lh_releases' => 'https://github.com/GoogleChrome/lighthouse/releases',
    'lh_llms' => 'https://developer.chrome.com/docs/lighthouse/agentic-browsing/llms-txt',
    'toolkit' => 'https://developer.chrome.com/blog/agent-ready-toolkit',
    'g_guide' => 'https://developers.google.com/search/docs/fundamentals/ai-optimization-guide',
    'agent_ux' => 'https://web.dev/articles/ai-agent-site-ux',
    'webmcp' => 'https://webmachinelearning.github.io/webmcp/',
    'webmcp_status' => 'https://chromestatus.com/feature/5117755740913664',
    'llmstxt' => 'https://llmstxt.org/',
];

return [

'body' => static function () use ($s): void { ?>
<p class="bx-open">An AI agent does not look at your website the way a visitor does. It reads the structure underneath the design, tries to act on it, and stops wherever the page is ambiguous.</p>
<p>That matters because buyers have started handing over parts of the job. "Find three suppliers of food-grade conveyor belts in Germany and tell me their minimum order" is now a task someone can give to a browser. The agent opens your site, looks for the answer, and reports back. If it cannot find the minimum order, you are not in the summary.</p>
<p>There is also a great deal of forecast being sold as fact. This article keeps the two apart. It covers what agents can do today according to the companies that make them, what stops them on a typical B2B site, two tests you can run this week, and what is not real yet.</p>

<?php px_answer('how-ai-agents-evaluate-b2b-websites'); ?>

<?php px_toc([
    'three-kinds' => 'Three things people call an agent',
    'what-agents-can-do' => 'What browser agents can do today',
    'agentic-browsing' => 'The test Google ships: Agentic Browsing',
    'where-agents-stop' => 'Where B2B websites stop an agent',
    'six-tasks' => 'Six tasks to try with an AI browser',
    'not-real-yet' => 'What is not real yet',
    'llms-txt' => 'Where llms.txt fits',
    'this-quarter' => 'What to do this quarter',
    'faq-title' => 'FAQs',
]); ?>

<h2 id="three-kinds">Three Things People Call an Agent</h2>
<p>The word covers three different pieces of technology. They read your site in different ways, and you control each of them in a different place.</p>

<?php px_flow('agent-kinds', 'Three kinds of "AI agent", from reading to acting', [
    ['Crawlers and fetchers', 'They request a page and read its HTML. They do not click or type. Search crawlers build an index; fetchers load one page when a person asks about it.', 'Controlled by robots.txt and your firewall', 'a'],
    ['Browser agents', 'They drive a real browser for a person: read the page, click, type, fill in forms and move between pages, pausing for approval at sensitive steps.', 'Controlled by the user, and by your bot rules', 'b'],
    ['Tools a page offers to agents', 'A page can describe actions, such as "request a quote", in a form an agent can call directly. This is a proposal in an early trial.', 'Controlled by your developers. Not a standard yet', 'c'],
], [
    'sub' => 'Most of what is written about "AI agents" describes the third kind. Most of what happens on your site today is the first two.',
    'key' => [['a', 'Established'], ['b', 'Available now'], ['c', 'Proposed']],
]); ?>

<p>The first kind is covered in our guide to <a href="/blog/ai-crawler-access-problems/">AI crawler access problems</a>. This article is about the second, with a note on the third.</p>

<h2 id="what-agents-can-do">What Browser Agents Can Do Today</h2>
<p>These descriptions come from each company's own documentation, read on 13 November 2026. The products change often. OpenAI's help pages now say that two of its earlier products, ChatGPT agent and the Atlas browser, are no longer available. Treat any list like this as a snapshot with a date on it. Most of these agents are limited to paid plans, and Google's is limited to the United States for now.</p>

<?php px_table('What four browser agents are documented to do, and where they stop', ['Product', 'What it can do', 'Where it pauses or refuses'], [
    ['ChatGPT cloud browser (OpenAI)', 'Reads pages, clicks buttons and fills in forms in a browser on a remote computer, on public and signed-in sites', 'Designed to ask before actions that are hard to reverse and before financial, legal or account commitments. The user signs in through a form the model cannot see'],
    ['Gemini in Chrome, auto browse (Google)', 'Carries out multi-step tasks in Chrome after the user approves a plan', 'Hands back to the user to finish payments, accept terms and create accounts. Google describes it as experimental and warns it can make mistakes'],
    ['Claude in Chrome (Anthropic)', 'Reads the page, clicks, types, fills in forms and navigates', 'Its permissions guide lists purchases, financial transactions and creating accounts as things it will not do in any mode'],
    ['Comet assistant (Perplexity)', 'Browses and acts on pages for the user', 'Pauses for confirmation before sending messages, placing a final order or entering details it does not already have'],
]); ?>

<p>Sources: OpenAI's <?= ext_link($s['oai_browser'], 'cloud browser help page') ?>, Google's <?= ext_link($s['g_auto'], 'help page for auto browse') ?>, Anthropic's <?= ext_link($s['claude_perm'], 'permissions guide for Claude in Chrome') ?> and Perplexity's <?= ext_link($s['comet'], 'post on how Comet handles risky actions') ?>.</p>
<p>Three things stand out for a B2B website.</p>
<ul class="post-list">
    <li><strong>They read structure.</strong> Google's <?= ext_link($s['g_guide'], 'AI optimisation guide for site owners') ?> describes browser agents as working from screenshots, the page's code and its accessibility tree, the same structure a screen reader uses. A button with no name in that tree is a button the agent has to guess at.</li>
    <li><strong>They are built to stop before commitments.</strong> Every one of the four pauses or refuses at payment. An agent can research you, compare you and prepare an enquiry. A person still presses the final button.</li>
    <li><strong>You can recognise some of them.</strong> OpenAI says its cloud browser <?= ext_link($s['oai_access'], 'signs its requests') ?> so that a site can verify them, and that large security providers check those signatures automatically. That makes it possible to let a verified agent through without opening the door to every bot.</li>
</ul>

<h2 id="agentic-browsing">The Test Google Ships: Agentic Browsing</h2>
<p>Since May 2026, Lighthouse, the testing tool behind PageSpeed Insights, has had a category called Agentic Browsing. According to the <?= ext_link($s['lh_releases'], 'Lighthouse release notes') ?>, it arrived in version 13.2 and joined the default set of tests in version 13.3. It checks whether a page is built in a way a browser agent can work with.</p>
<p>The result is a fraction, not a score out of 100. In version 13.6 the category has seven checks:</p>

<?php px_table('The seven checks in Lighthouse\'s Agentic Browsing category (version 13.6)', ['Check', 'What it looks at', 'Applies to most sites today?'], [
    ['Accessibility tree is well-formed', 'Whether the structure an agent reads is complete and consistent', 'Yes'],
    ['Cumulative Layout Shift', 'Whether elements move while the page loads, which makes clicks miss', 'Yes'],
    ['llms.txt follows recommendations', 'Whether an llms.txt file, if the site has one, is well-formed', 'Only if the file exists'],
    ['WebMCP tools registered', 'Whether the page offers tools to agents', 'Information only'],
    ['WebMCP form coverage', 'Whether forms are described for agents', 'Information only'],
    ['WebMCP schemas are valid', 'Whether those tool descriptions are valid', 'Only if the page uses WebMCP'],
    ['ARD manifest is valid', 'Whether an ard.json manifest for agents, if the site has one, is valid', 'Only if the file exists'],
]); ?>

<p>Checks that do not apply are left out of the fraction. That is why a simple site shows 2 of 2, and a site with a valid llms.txt file shows 3 of 3. Google's <?= ext_link($s['lh_scoring'], 'documentation for the category') ?> calls it experimental and says it rests on proposed standards. The Chrome team's <?= ext_link($s['toolkit'], 'announcement') ?> describes the results as informational.</p>
<h3>What our published results mean</h3>
<p>The rebuilt pages we tested for <a href="/case-studies/sai-impression-label-manufacturer/">Sai Impression</a> and <a href="/case-studies/firstchoice-used-auto-parts/">FirstChoice Used Auto Parts</a> scored 3 of 3. Read against the table, that means three things: the accessibility tree is well-formed, the layout is stable, and the llms.txt file is valid.</p>
<p>It does not mean an agent can complete an enquiry on those sites, and it says nothing about ranking. Google has not said the category affects Search. It is a floor: a page that fails it will be hard for an agent to use, and a page that passes still has to be tested with a real task.</p>

<?php px_cta_mid([
    'label' => 'Free SEO audit tool',
    'art' => 'scan',
    'h' => 'Start With What Crawlers Can Read',
    'p' => 'Before an agent can act on a page, it has to be able to read it. Our free audit runs twenty checks on your homepage, including whether AI crawlers are allowed in. No sign-up.',
    'href' => '/free-seo-audit/',
    'btn' => 'Run the Free Audit',
    'cta' => 'blog-agents-audit',
]); ?>

<h2 id="where-agents-stop">Where B2B Websites Stop an Agent</h2>
<p>The same faults come up again and again, and most of them also trouble human visitors who use a keyboard or a screen reader.</p>
<ol class="bx-steps">
    <li><strong>Buttons and fields with no name.</strong> An icon with no label, or a field identified only by grey placeholder text, has nothing in the accessibility tree for an agent to match to its task.</li>
    <li><strong>Content that appears late.</strong> Text added by JavaScript after a delay, or only when a tab is clicked, may not be there when the agent reads the page.</li>
    <li><strong>Layouts that move.</strong> If a banner pushes the page down as it loads, the agent's click lands on the wrong element.</li>
    <li><strong>Facts locked in PDFs and images.</strong> Minimum orders, tolerances and certificates inside a brochure are invisible to an agent reading the page.</li>
    <li><strong>Forms that fail silently.</strong> A form that rejects an entry without saying why leaves an agent, like a person, with nothing to correct.</li>
    <li><strong>A bot challenge on every page.</strong> A challenge is built to stop automation, and an agent is automation, even when a real buyer sent it.</li>
</ol>
<p>The first four are covered, with tests anyone can run, in our <a href="/blog/website-accessibility-checklist/">website accessibility checklist</a>. Google's own advice on <?= ext_link($s['agent_ux'], 'site experience for AI agents') ?> makes the same link between accessible pages and pages agents can use.</p>

<h2 id="six-tasks">Six Tasks to Try With an AI Browser</h2>
<p>A score tells you whether the page is well built. A task tells you whether an agent can do what a buyer would ask. If you have access to one of the browser agents above, give it these six jobs on your own website and record where it stops.</p>

<?php px_table('Six buyer tasks, and what a pass looks like', ['Task', 'A pass looks like', 'Where it usually stops'], [
    ['1. Say what the company sells, and to whom', 'A correct summary in two sentences', 'The home page talks about values and never names the product'],
    ['2. Find the minimum order or starting budget', 'A figure, with the page it came from', 'The figure is in a PDF, or nowhere'],
    ['3. List certifications and standards', 'A list that matches your real certificates', 'Certificates are shown as logo images with no text'],
    ['4. Compare two products or services', 'A table of real differences', 'Each page uses different terms for the same property'],
    ['5. Explain how to request a quote', 'The steps, and the information you will ask for', 'The form sits behind a button with no name'],
    ['6. Fill in the enquiry form and stop before sending', 'Every field filled in correctly, nothing sent', 'A field rejects the entry and does not say why'],
]); ?>

<p>You can paste this instruction into the agent. Replace the address with your own.</p>
<?php lx_code(<<<'TXT'
I am testing how well my own company's website works for AI agents.
Using only this website: https://www.example.com

1. Tell me what the company sells and who it sells to.
2. Find the minimum order quantity or the starting budget.
3. List its certifications and the standards it works to.
4. Compare two of its products or services in a table.
5. Explain how to request a quote and what information is needed.
6. Open the enquiry form and fill it in with the test details below.
   Do not submit the form. Stop and tell me when it is ready to send.

For each task, say whether you completed it, which page you used,
and exactly where you got stuck if you did not.
TXT, 'Instruction to paste'); ?>
<p class="bx-note"><strong>Use your own site, and do not let it send.</strong> The sixth task ends before the form is submitted. Run the test more than once: agents do not take the same path every time, and one run is an anecdote.</p>

<h2 id="not-real-yet">What Is Not Real Yet</h2>
<p>Three ideas are common in presentations and absent from the vendors' documentation.</p>
<ul class="post-list">
    <li><strong>Agents that buy for a business without a person.</strong> All four products above stop or hand back at payment. Nothing in their documentation describes an agent placing a B2B order on its own authority.</li>
    <li><strong>Agents negotiating with your website.</strong> The proposal that would let a page offer tools to an agent, <?= ext_link($s['webmcp'], 'WebMCP') ?>, is a draft from a W3C community group, not a standard. Chrome lists it as an <?= ext_link($s['webmcp_status'], 'origin trial') ?> that has not shipped, with no sign of support from other browsers.</li>
    <li><strong>A way to be "chosen by agents".</strong> Nobody can promise that an agent will recommend you. What you can do is make sure it can read you, and that what it reads is accurate.</li>
</ul>

<h2 id="llms-txt">Where llms.txt Fits</h2>
<p>The <?= ext_link($s['llmstxt'], 'llms.txt proposal') ?> is a plain text file that lists a site's most useful pages for AI tools. Two facts about it are both true, and they are often quoted against each other.</p>
<p>Lighthouse checks it. If your site has the file, the Agentic Browsing category tests whether it is well-formed, and Google's <?= ext_link($s['lh_llms'], 'page about that check') ?> calls llms.txt an optional convention that is still emerging. Google Search does not use it. Google's guide for site owners says creating one "will neither harm nor help your site's visibility or rankings in Google Search".</p>
<p>So add one if it takes an hour, keep it accurate, and do not expect it to change your traffic. It is housekeeping.</p>

<h2 id="this-quarter">What to Do This Quarter</h2>
<p>None of this needs a new platform. The useful work is the same work that helps people.</p>
<ol class="bx-steps">
    <li><strong>Give every button, link and form field a real name.</strong> This is the single change that helps agents and screen reader users most.</li>
    <li><strong>Put the facts buyers ask for in text.</strong> Minimum order, lead time, service area, certificates and what a quote needs, on the page and not only in a download.</li>
    <li><strong>Keep the layout still while it loads.</strong> Reserve space for images, banners and embeds.</li>
    <li><strong>Make forms say what went wrong.</strong> Label each field, explain each error next to it and confirm clearly when the enquiry is sent.</li>
    <li><strong>Review your bot rules.</strong> Challenge suspicious traffic on forms and logins, not every visitor on every public page.</li>
    <li><strong>Run both tests every quarter.</strong> The Lighthouse category takes a minute. The six tasks take about an hour.</li>
</ol>
<p>If you are about to rebuild the site, add these to the brief. Our <a href="/blog/wordpress-seo-launch-checklist/">WordPress launch checklist</a> shows where they belong in the sign-off.</p>
<?php },

'closing' => static function (): void { ?>
<h2 id="final-thoughts">Final Thoughts</h2>
<p>An agent is a visitor with no patience and no intuition. It cannot guess that the grey icon is the menu, or that the minimum order is on page four of the brochure. Everything it needs has to be stated, named and reachable.</p>
<p>That is a description of a well-built website. The companies that do the plain work now, accessible names, facts in text, forms that explain themselves, will be easy for agents to use whatever the products are called next year.</p>

<?php px_cta_main([
    'h' => 'Find Out Where an AI Agent Gets Stuck on Your Site',
    'p' => 'Ask for a free AI visibility report. We check what AI assistants can read on your site and what they say about you, and list what to fix first.',
    'href' => '/ai-seo/#enquire',
    'btn' => 'Get My Free AI Visibility Report',
    'cta' => 'blog-agents-report',
    'link' => ['/ai-seo/', 'See AI SEO Services', 'blog-agents-service'],
]); ?>

<?php px_author(); ?>
<?php },

];
