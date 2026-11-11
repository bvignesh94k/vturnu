<?php
/**
 * Listicle: what belongs on each of nine pages of a B2B website.
 * Loaded by templates/blog-post.php through the 'rich' key; the progress board
 * and done toggles come from includes/listicle-kit.php and assets/js/listicle.js.
 *
 * The example openings all describe one made-up company (a dosing pump maker)
 * and each is labelled as an illustration. The one outside figure, Jakob
 * Nielsen's 1997 reading study, is linked in the sentence that uses it
 * (checked 11 Nov 2026). External links are nofollow.
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

/* An example opening line for the made-up company, always labelled. */
$eg = static function (string $line): void {
    ?><p class="bx-note"><strong>Example opening (illustration):</strong> <?= e($line) ?></p><?php
};

return [

'body' => static function () use ($table, $eg): void { ?>
<p class="bx-open">The design is approved, the site map has forty boxes on it, and every box says "copy to come". This is the point where most website projects slow down.</p>
<p>It slows down because nobody decided what each page is for. Without that, every page tries to say everything: who the company is, what it sells, why it is different. Visitors meet the same three paragraphs on every page and learn nothing new by clicking.</p>
<p>Readers will not work to find the difference. In Jakob Nielsen's <?= ext_link('https://www.nngroup.com/articles/how-users-read-on-the-web/', 'research on how people read websites') ?>, 79 percent of test users always scanned any new page they came across, and only 16 percent read word by word. The study dates from 1997, and it still describes how a busy buyer treats a supplier's site.</p>
<p>So give each page one job. The nine pages below cover most B2B websites. For each one you get the job, what to write, what to leave out and an example opening. Tick each page as you finish it.</p>

<div class="answer-box post-answer">
    <p class="bx-answer-k">The short answer</p>
    <p class="answer-text"><?= e($GLOBALS['BLOG']['what-to-write-on-every-page-of-a-b2b-website']['lede'] ?? '') ?></p>
</div>

<?php lx_glance([
    ['home-page', 'Home page', 'Core page'],
    ['service-pages', 'Service or product pages', 'Core page'],
    ['industry-pages', 'Industry and use-case pages', 'Core page'],
    ['pricing-page', 'Pricing, or how pricing works', 'Core page'],
    ['about-page', 'About page', 'Proof'],
    ['case-studies', 'Case studies', 'Proof'],
    ['contact-page', 'Contact and enquiry pages', 'Supporting page'],
    ['blog-pages', 'Blog and resource pages', 'Supporting page'],
    ['thank-you-pages', 'Thank-you and error pages', 'Supporting page'],
], 'The 9 pages at a glance'); ?>

<?php lx_board('b2b-website-pages', [
    'label' => 'pages covered',
    'filters' => ['core' => 'Core pages', 'proof' => 'Proof', 'support' => 'Supporting pages'],
    'msgs' => [
        [0, 'Work through your own site map. Tick a page when it has one job, one action and the content listed.'],
        [3, 'A good start. Service pages and the home page carry most of the load.'],
        [6, 'Most of the site is covered. The proof pages are what turn interest into an enquiry.'],
        [8, 'Nearly there. Check the pages nobody plans: thank-you and error pages.'],
        [9, 'All nine covered. Read each page on a phone before it goes live.'],
    ],
]); ?>

<?php lx_item(1, 'home-page', 'Home page', ['type' => 'core', 'tags' => ['Core page', 'Write it second'], 'mark' => 'This page is covered'], static function () use ($eg): void { ?>
<p>The home page has one job: tell the right visitor they are in the right place, then send them on. It is a signpost. Nobody decides to buy on a home page, but plenty decide to leave.</p>
<?php lx_split('Write',
    '<ul><li>Who you help and with what, in one sentence a stranger would understand.</li><li>One piece of proof: a number, or the kind of customers you work with.</li><li>Clear routes to your main services and industries.</li><li>One main action, repeated.</li></ul>',
    'Leave out',
    '<ul><li>"Welcome to our website."</li><li>Company history. It has its own page.</li><li>A slider with five different messages.</li><li>The word "solutions" with nothing after it.</li></ul>'); ?>
<?php $eg('Dosing pumps for water treatment plants, sized for your plant before you order.'); ?>
<?php }); ?>

<?php lx_item(2, 'service-pages', 'Service or product pages', ['type' => 'core', 'tags' => ['Core page', 'Write these first'], 'mark' => 'This page is covered'], static function () use ($eg): void { ?>
<p>These pages do the selling. A buyer comparing three suppliers reads the service page to decide who makes the shortlist. It has to explain what they get and how it works, clearly enough to repeat to a colleague.</p>
<?php lx_split('Write',
    '<ul><li>What it is and who it is for, in the first lines.</li><li>What is included, as a plain list.</li><li>How it works, step by step, from enquiry to delivery.</li><li>How pricing works.</li><li>Proof that belongs to this service, not a general one.</li><li>The questions buyers ask on sales calls, answered.</li></ul>',
    'Leave out',
    '<ul><li>Features with no result attached.</li><li>Benefits any competitor could claim.</li><li>Your internal names for things.</li><li>Three services squeezed onto one page.</li></ul>'); ?>
<?php $eg('Pump sizing service: send your flow rate, dose and back pressure, and an engineer returns the right model within one working day.'); ?>
<p>A service page has to explain like an article and ask like an advertisement. That mix is the main work in <a href="/website-content-writing-services/">website content writing</a>, and where the page must persuade hard, in <a href="/copywriting-services/">copywriting</a>.</p>
<?php }); ?>

<?php lx_item(3, 'industry-pages', 'Industry and use-case pages', ['type' => 'core', 'tags' => ['Core page'], 'mark' => 'This page is covered'], static function () use ($eg): void { ?>
<p>An industry page shows a buyer in one sector that you know their world. It works only when it says things that are true of that sector and no other.</p>
<?php lx_split('Write',
    '<ul><li>The sector\'s problem, in the words people in it use.</li><li>The standards, regulations or conditions that apply to them.</li><li>A project from that sector, or the closest one you have.</li><li>Which of your products or services fit, and why.</li></ul>',
    'Leave out',
    '<ul><li>The same page with the industry name swapped.</li><li>Sectors you have never worked in.</li><li>General claims the service page already makes.</li></ul>'); ?>
<?php $eg('For municipal water plants: pumps that hold their dose as demand changes through the day, with the documents your regulator asks for.'); ?>
<p>Pages that differ only by the name of the industry help nobody. They are also close to the pattern search engines treat as low value, which we cover in <a href="/blog/does-google-penalise-ai-content/#the-real-risk">what Google's rules say about mass-produced pages</a>. Write five industry pages you can fill properly before you write twenty you cannot.</p>
<?php }); ?>

<?php lx_item(4, 'pricing-page', 'Pricing, or how pricing works', ['type' => 'core', 'tags' => ['Core page', 'Often missing'], 'mark' => 'This page is covered'], static function () use ($eg): void { ?>
<p>Buyers want to know whether you are in their range before they talk to anyone. If your site says nothing, some will assume the worst and leave. The page's job is to set expectations, so the right buyers enquire and the wrong ones do not waste a week of both your time.</p>
<?php lx_split('Write',
    '<ul><li>What drives the price: size, volume, complexity, materials.</li><li>A starting point or a range, if you can give one.</li><li>What is included and what costs extra.</li><li>How a quote is prepared, and how long it takes.</li></ul>',
    'Leave out',
    '<ul><li>"Contact us for pricing", with nothing else.</li><li>Conditions that appear only at contract stage.</li><li>A table of packages if you never sell packages.</li></ul>'); ?>
<?php $eg('Pumps are priced by flow range and materials. Most plants need one of three ranges. Send your numbers for a written quote within two working days.'); ?>
<p>If you cannot publish prices, publish how pricing works. We do this ourselves: our service pages state a minimum project budget and say that every quote is written after a short call.</p>
<?php }); ?>

<aside class="bx-cta bx-cta--audit" aria-label="Website content">
    <svg class="bx-cta-art" viewBox="0 0 120 120" width="104" height="104" aria-hidden="true" focusable="false"><rect x="12" y="20" width="44" height="54" rx="8" fill="#fff" stroke="#DAD5F2" stroke-width="5"/><rect x="64" y="20" width="44" height="54" rx="8" fill="#fff" stroke="#DAD5F2" stroke-width="5"/><path d="M22 36h24M22 48h16M74 36h24M74 48h16" stroke="#C9C5EA" stroke-width="5" stroke-linecap="round"/><rect x="22" y="58" width="20" height="8" rx="4" fill="#FF3D8A"/><rect x="74" y="58" width="20" height="8" rx="4" fill="#FFC94D"/><rect x="30" y="84" width="60" height="18" rx="9" fill="#1D1936"/></svg>
    <div class="bx-cta-copy">
        <p class="bx-cta-h">A Site Map Full of Empty Pages?</p>
        <p>Send us the page list. We plan one job for every page, interview your team, write the content and load it into your CMS.</p>
        <a class="bx-btn bx-btn--accent" href="/website-content-writing-services/#enquire" data-cta="blog-pages-quote">Get My Website Content Quote <span aria-hidden="true">&rarr;</span></a>
        <p class="bx-cta-note">Planning the whole project? Start with our <a href="/blog/website-design-brief-questions/">website design brief builder</a>.</p>
    </div>
</aside>

<?php lx_item(5, 'about-page', 'About page', ['type' => 'proof', 'tags' => ['Proof'], 'mark' => 'This page is covered'], static function () use ($eg): void { ?>
<p>In B2B, the about page is a trust check. A buyer opens it to see whether real people stand behind the claims on the other pages, and whether the company is the size and kind it appears to be.</p>
<?php lx_split('Write',
    '<ul><li>Who runs the company: names, roles and real photographs.</li><li>When it started, and why.</li><li>Where you are, and where you work.</li><li>What you do, and what you turn down.</li><li>Certificates, memberships and registrations a buyer can check.</li></ul>',
    'Leave out',
    '<ul><li>A mission statement nobody could disagree with.</li><li>Stock photographs of strangers in a meeting.</li><li>A year-by-year timeline of everything.</li><li>Values with no example of them in use.</li></ul>'); ?>
<?php $eg('Two process engineers started the company in 2011, after one too many jobs replacing pumps that had been sized from a catalogue.'); ?>
<?php }); ?>

<?php lx_item(6, 'case-studies', 'Case studies', ['type' => 'proof', 'tags' => ['Proof', 'Sales will use these'], 'mark' => 'This page is covered'], static function () use ($eg): void { ?>
<p>A case study gives a buyer something to repeat to their own manager: a company like theirs had the same problem, and here is what happened. It is the page your sales team will send most often.</p>
<?php lx_split('Write',
    '<ul><li>The kind of customer, so the right reader recognises themselves.</li><li>The problem, in the customer\'s words.</li><li>What was done, in order.</li><li>One number, with its source and dates.</li></ul>',
    'Leave out',
    '<ul><li>Adjectives where a number should be.</li><li>Results with no source.</li><li>Three paragraphs about your own company first.</li></ul>'); ?>
<?php $eg('A municipal plant was overdosing chemicals every afternoon. Three correctly sized pumps later, the plant\'s own dosing logs show the difference.'); ?>
<p>The method is in our guide on <a href="/blog/how-to-write-a-b2b-case-study/">how to write a B2B case study</a>. If you have the customers and not the time, see <a href="/case-study-writing-services/">case study writing services</a>.</p>
<?php }); ?>

<?php lx_item(7, 'contact-page', 'Contact and enquiry pages', ['type' => 'support', 'tags' => ['Supporting page'], 'mark' => 'This page is covered'], static function () use ($eg): void { ?>
<p>By the time someone reaches this page they have decided to get in touch. The page's job is to stay out of the way, and to say what happens after they press the button.</p>
<?php lx_split('Write',
    '<ul><li>A short form that asks only for what sales will use.</li><li>What happens next, who replies and how fast.</li><li>Other ways to reach you: phone, email, WhatsApp.</li><li>Address and hours, if people visit.</li></ul>',
    'Leave out',
    '<ul><li>Ten fields, when three would start the conversation.</li><li>"We will get back to you soon."</li><li>Puzzles that block real buyers. Our guide on <a href="/blog/how-to-stop-junk-leads-on-your-website/">stopping junk leads</a> covers gentler filters.</li></ul>'); ?>
<?php $eg('Send your flow rate and dose. An engineer replies within one working day, by email unless you ask for a call.'); ?>
<?php }); ?>

<?php lx_item(8, 'blog-pages', 'Blog and resource pages', ['type' => 'support', 'tags' => ['Supporting page', 'Ongoing'], 'mark' => 'This page is covered'], static function () use ($eg): void { ?>
<p>Articles and guides answer the questions buyers ask before they are ready to talk to you. They bring in people who are still working out what they need, and they give sales something useful to send.</p>
<?php lx_split('Write',
    '<ul><li>One question per article, answered in the first lines.</li><li>Examples and numbers from your own work.</li><li>A link to the service the topic leads to.</li><li>A date, and an update when the facts change.</li></ul>',
    'Leave out',
    '<ul><li>Company news as the only content.</li><li>Articles written for a keyword with nothing to say.</li><li>Posts no one at the company has read.</li></ul>'); ?>
<?php $eg('Size a dosing pump from three numbers: flow rate, dose and back pressure.'); ?>
<p>The quality of an article is mostly decided by its brief. Our <a href="/blog/how-to-brief-a-content-writer/">content brief builder</a> takes about twenty minutes, and <a href="/blog-writing-services/">blog writing services</a> cover the rest if your experts have no time to write.</p>
<?php }); ?>

<?php lx_item(9, 'thank-you-pages', 'Thank-you and error pages', ['type' => 'support', 'tags' => ['Supporting page', 'Usually forgotten'], 'mark' => 'This page is covered'], static function () use ($eg): void { ?>
<p>Nobody plans these pages, and every buyer who enquires sees one of them. A thank-you page is the first thing a new lead reads from you. An error page is what a visitor gets when a link has broken, which is exactly when they need help.</p>
<?php lx_split('Write',
    '<ul><li><strong>Thank-you page:</strong> confirm it arrived, say who will reply and when, and offer something useful to read meanwhile.</li><li><strong>Error page:</strong> say plainly that the page is missing, and offer the main routes and a search box.</li></ul>',
    'Leave out',
    '<ul><li>"Thank you for your submission", and nothing else.</li><li>An error code with no way forward.</li><li>A joke where a link should be.</li></ul>'); ?>
<?php $eg('Thank you. An engineer will reply by tomorrow evening. While you wait, here is the sizing guide most plants ask for.'); ?>
<?php }); ?>

<h2 id="every-page">Every Page on One Screen</h2>
<p>Use this table when you review a site map. If two pages have the same answer in the second column, one of them probably should not exist.</p>

<?php $table('What each page of a B2B website is for', ['Page', 'The visitor is asking', 'It must have', 'Where it leads'], [
    ['Home', 'Am I in the right place?', 'One sentence on who you help, and routes onward', 'A service or industry page'],
    ['Service or product', 'What would I get, and how does it work?', 'Scope, steps, proof and how pricing works', 'An enquiry'],
    ['Industry or use case', 'Do you understand my sector?', 'Their problem in their words, and a relevant project', 'The service that fits'],
    ['Pricing', 'Are you in my range?', 'What drives the price and how a quote works', 'An enquiry'],
    ['About', 'Are these real, capable people?', 'Names, faces and facts a buyer can check', 'A case study or contact'],
    ['Case study', 'Has this worked for someone like me?', 'One number with its source and dates', 'The service it proves'],
    ['Contact', 'How do I start, and what happens next?', 'A short form and a promised reply time', 'The thank-you page'],
    ['Blog or resource', 'How do I solve this problem?', 'The answer first, then the detail', 'A related service or guide'],
    ['Thank-you and error', 'Did that work? Where do I go now?', 'A clear confirmation or a way forward', 'Something useful to read'],
]); ?>

<h2 id="writing-order">The Order to Write Them In</h2>
<p>Do not start with the home page. It summarises the rest of the site, so it is far easier to write once the rest exists.</p>
<ol class="bx-steps">
    <li><strong>Service or product pages.</strong> They force you to define what you sell. Everything else borrows from them.</li>
    <li><strong>Case studies.</strong> You will need the proof for almost every other page.</li>
    <li><strong>Home page.</strong> Now a summary of pages that already exist.</li>
    <li><strong>About, pricing and contact.</strong> Short pages, quick to write once the offer is clear.</li>
    <li><strong>Industry pages.</strong> Only the sectors you can write about truthfully.</li>
    <li><strong>Thank-you and error pages.</strong> Ten minutes each, and they are never forgotten again.</li>
    <li><strong>Articles.</strong> Start with the three questions sales answers most often, and keep going after launch.</li>
</ol>
<p>If the site is a redesign, check one more thing before any page is rewritten: which existing pages already bring visitors and enquiries. Our guide to <a href="/blog/website-redesign-without-losing-seo/">redesigning a website without losing SEO</a> explains how to protect them.</p>
<?php },

'closing' => static function (): void { ?>
<h2 id="final-thoughts">Final Thoughts</h2>
<p>A B2B website is a sales conversation held without a salesperson. Each page takes one turn in it: who you are, what you offer, why to believe you, what it costs and how to start. When every page knows its turn, visitors move forward. When every page tries to say everything, they leave.</p>
<p>Write down the job of each page in one line before anyone writes a paragraph. It is the cheapest hour in the whole project.</p>

<aside class="bx-cta bx-cta--main" aria-label="Start a conversation with VTurnU">
    <p class="bx-cta-h">Want Every Page Written for You?</p>
    <p>We plan the pages, interview your team, write and edit the content, and check each page on a phone before it goes live.</p>
    <div class="bx-cta-acts">
        <a class="bx-btn bx-btn--light" href="/website-content-writing-services/#enquire" data-cta="blog-pages-contact">Get My Website Content Quote <span aria-hidden="true">&rarr;</span></a>
        <a class="bx-link bx-link--light" href="/web-design/" data-cta="blog-pages-webdesign">See Web Design</a>
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
