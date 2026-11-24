<?php
/**
 * Introducing Vee. Loaded by templates/blog-post.php through the 'rich' key.
 *
 * Every link in here goes to a real, live page on this site, with anchor text
 * that names what the target page is actually about (the owner's standing
 * rule, 24 Nov 2026, see [[feedback-blog-internal-linking]] in memory). The
 * one external link (the EU AI Act) goes to the European Commission's own
 * policy page and is nofollow, the site's convention for citations.
 */

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

'body' => static function () use ($table): void { ?>
<p class="bx-open">Click the chat bubble on this page. You are talking to Vee, the AI assistant we shipped on vturnu.com, and this is the exact account of how we built it, what it cost, and the bug it shipped with on day one.</p>

<div class="answer-box post-answer">
    <p class="answer-text"><?= e($GLOBALS['BLOG']['introducing-vee-ai-chat-assistant']['lede'] ?? '') ?></p>
</div>

<h2 id="what-vee-does">What Vee Actually Does</h2>
<p>It is built to handle everything a visitor might open live chat for, not just sales:</p>
<ul class="post-list">
    <li>Sales questions across every service VTurnU offers, from <a href="/web-design/">website design and development</a> to <a href="/ai-seo/">AI search visibility</a>, answered from the site's own service pages so Vee never improvises a scope or a figure it is not supposed to.</li>
    <li>Prices: the site publishes one number, a minimum budget by country, and Vee holds that line too. No invented package, no hourly rate, ever.</li>
    <li>Guest posts, link requests, sponsored posts and partnership pitches: Vee collects the details and hands them to a person. It never accepts, declines or prices one of these in the chat.</li>
    <li>Existing clients who need help, and anyone who simply asks to talk to a person, which gets a direct answer and an immediate alert to the team, not a queue.</li>
</ul>

<h2 id="why-not-paid">Why a Paid Platform Was Not the Starting Point</h2>
<p>A chat widget like this is usually sold as a monthly subscription, often for a feature set most small sites use a fraction of. We wanted to know what a genuinely useful version would cost on infrastructure that is free at the scale a normal business website actually sees, and whether "free" meant a toy or something that could carry a real sales conversation.</p>
<p>It carries one. Vee answers through a chain of free-tier AI models: Groq's free plan first, Cloudflare Workers AI's free daily allocation as a backup, and a plain scripted reply as the last resort if both are unavailable for that minute. A visitor is never told "something went wrong"; worst case, Vee still takes their name and contact details and a person follows up, which is the actual job a chat widget has to do.</p>
<p>The honest limit is volume: free plans cap how many AI replies a day are possible, somewhere in the low hundreds for the stack we used. Past that cap, Vee drops to the scripted reply until the next day's quota resets. For the traffic a small or mid-size business website gets, that ceiling is rarely the bottleneck; for a high-traffic site, the same chain works with a paid key slotted in behind the free one, which is a one-line change, not a rebuild.</p>

<?php $table('A typical paid chat widget vs. how Vee is built', ['Compared on', 'Typical paid widget', 'Vee'], [
    ['Monthly cost', 'A subscription, usually priced per seat or per conversation', 'Free AI plans, up to their own daily volume'],
    ['What it knows', 'Whatever you type into a script builder', "The site's own service pages and FAQs, kept in sync automatically"],
    ['When it does not know', 'A generic "I did not understand that"', 'Says so, and alerts a person, with the conversation attached'],
    ['Visitor contact details', 'Usually passed to the AI model along with everything else', 'Found and replaced with a placeholder before the AI model ever sees them'],
    ['AI disclosure', 'Varies by vendor', 'Always, in the header and the first message'],
]); ?>

<aside class="post-cta-inline" aria-label="Check your own site">
    <div>
        <strong>Curious what AI search engines already see on your site?</strong>
        <span>Run the <a href="/ai-crawler-checker/">AI Crawler Checker</a> or the <a href="/free-seo-audit/">free SEO audit</a>. Results in seconds, no call required.</span>
    </div>
    <a class="btn btn-grad" href="/free-seo-audit/">Check My Site</a>
</aside>

<h2 id="honesty-rule">The Honesty Rule We Built in Before Anyone Asked for It</h2>
<p>Since 2 August 2026, the <?= ext_link('https://digital-strategy.ec.europa.eu/en/policies/regulatory-framework-ai', "EU's AI Act") ?> has required an AI system to disclose that it is an AI when a person is interacting with it, under Article 50. Vee carries an "AI assistant" label in its own header, says so in its first message, and answers truthfully if a visitor asks whether they are talking to a person.</p>
<p>The other decision was not legally required, just obvious once you think about what a chat widget actually sees: a visitor's email address and phone number never reach the AI model at all. They are found in the visitor's message by the server, replaced with a plain placeholder before the model reads anything, and handled separately from that point on. The model that answers a sales question has no reason to see a stranger's contact details, so it never does.</p>

<h2 id="first-day">What Happened the First Day It Went Live</h2>
<p>Vee shipped, answered correctly, and saved every lead to the CRM exactly as designed. The one thing that did not happen was the email telling the team about it. Every single notification was silently rejected by the email provider, for one specific reason: a visitor who gave an email address but had not yet given their name got a malformed "reply to" field, which the provider's API refuses outright.</p>
<p>Nothing about that showed up in the chat itself; the visitor got a normal, helpful reply. It only surfaced because we went looking for the actual email in the inbox it was supposed to reach, found nothing, and pulled the live server logs instead of assuming the silence meant success. That is the part worth naming plainly: a chat widget can look completely fine to the person using it while quietly failing the one job it exists to do for the business running it. We fixed the formatting, confirmed a real email landed, and wrote an automated test for exactly that condition so it cannot happen again unnoticed.</p>

<h2 id="the-clock">A Small Detail Most Chat Widgets Get Wrong: the Clock</h2>
<p>Tell a visitor in New York that your team works "9:30 AM to 6:30 PM India time" and you have told them nothing they can use. Vee instead works out what that actually means for the person reading it: an Indian visitor gets the plain hours, and everyone else gets the time difference stated directly, plus what time it genuinely is for the team right now, using the timezone their own browser already reports. It is a small thing to get right, and almost every chat widget we have used does not bother.</p>

<h2 id="use-one">If Your Own Website Could Use One of These</h2>
<p>None of this needed a large budget or a platform subscription: a free AI plan, a database the site already had, and the same email service most small business sites already use for order and lead notifications. That is the version of "AI chat assistant" most businesses actually need: one that answers the real questions their visitors ask, from their own real content, and gets a human involved the moment it should.</p>
<p>We build this for client sites through our <a href="/ai-development/">AI development</a> service, the same way we built it for our own: scoped to what the business actually sells, trained on its own service pages and FAQs, and kept on free infrastructure for as long as that infrastructure can carry the traffic. There is no price list for it, the same as everything else we build; the site publishes a minimum budget and the rest is quoted after a short call once we know what the chat needs to cover.</p>
<p>If your own site already gets found by AI engines, a chat assistant like this is the next logical piece: our guide on <a href="/blog/what-is-answer-engine-optimization/">what Answer Engine Optimization (AEO) actually is</a> covers how a site earns that visibility in the first place, and our piece on <a href="/blog/how-ai-agents-evaluate-b2b-websites/">how AI agents evaluate B2B websites</a> covers what happens once something, human or AI, actually lands on the page.</p>

<h2 id="key-points">What to Remember</h2>
<ul class="check-list">
    <li>An AI chat assistant can run on free infrastructure (a free-tier model chain plus a fallback) for the traffic a normal business website sees.</li>
    <li>Disclosing that a visitor is talking to an AI is not optional in the EU since 2 August 2026, and it costs nothing to build in from day one.</li>
    <li>A visitor's contact details can be kept out of the AI model entirely, by redacting them before the model ever sees the message.</li>
    <li>Hours and reply times should account for a visitor's own timezone, not state a head office's clock as if it means something universally.</li>
    <li>A chat widget can fail silently at the one job that matters to the business (telling someone a lead arrived) while still looking correct to the visitor; check the actual inbox, not just the chat window.</li>
</ul>
<?php },

'closing' => static function (): void { ?>
<h2 id="final-thoughts">An AI Assistant Is a Build, Not a Purchase</h2>
<p>Vee is not a plugin we installed. It is a small, specific piece of software built around what VTurnU actually sells, running on infrastructure that costs nothing until the traffic genuinely needs more. That is the same approach we would bring to a client's site: start from what the business sells and what its visitors actually ask, not from a generic script.</p>

<aside class="bx-cta bx-cta--main" aria-label="Talk to VTurnU about an AI assistant">
    <p class="bx-cta-h">Want an AI Assistant Like Vee on Your Site?</p>
    <p>We build it from your own content and keep it on free infrastructure for as long as that can carry your traffic. Tell us about your site and we will tell you what is possible.</p>
    <div class="bx-cta-acts">
        <a class="bx-btn bx-btn--light" href="/contact-us/" data-cta="blog-vee-contact">Talk to Us About an AI Assistant <span aria-hidden="true">&rarr;</span></a>
        <a class="bx-link bx-link--light" href="/ai-development/" data-cta="blog-vee-ai-dev">See AI Development Services</a>
    </div>
    <ul class="bx-cta-trust">
        <li>Built from your own service pages and FAQs, not a generic script</li>
        <li>Projects start from <?= e(min_budget()) ?></li>
        <li>No price list and no hourly rate, the same rule Vee itself follows</li>
    </ul>
</aside>

<aside class="bx-author" aria-label="About the author">
    <p class="bx-author-h">Written by the VTurnU strategy team</p>
    <p>VTurnU is a B2B digital marketing and branding agency based in Chennai, working across SEO, AI search, paid media, content, websites and AI development for clients worldwide. Read more <a href="/about-us/">about the team</a>, <a href="/how-we-work/">how we work</a> and <a href="/ai-policy/">how we use AI</a>.</p>
</aside>
<?php },

];
