<?php
/**
 * Comparison article: content writing, copywriting and content marketing.
 * Loaded by templates/blog-post.php through the 'rich' key.
 *
 * The pump maker in "One topic, written three ways" is an illustration and the
 * text says so. The one outside definition (Content Marketing Institute) is
 * paraphrased and linked in the sentence that uses it (checked 11 Nov 2026).
 * External links are nofollow.
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
<p class="bx-open">Three job titles, three kinds of invoice, and from the outside they all look like someone who writes. The difference only matters at one moment: when you are about to pay for one of them.</p>
<p>Hire a copywriter to produce a 2,000-word guide and you often get a long advertisement. Hire a content writer to fix a landing page and you get a well-researched page that never asks for the enquiry. Buy either without a plan and you get a folder of good pieces nobody reads.</p>
<p>This article explains what each one is, where they overlap, and how to tell which one your business needs first.</p>

<div class="answer-box post-answer">
    <p class="bx-answer-k">The short answer</p>
    <p class="answer-text"><?= e($GLOBALS['BLOG']['content-writing-vs-copywriting-vs-content-marketing']['lede'] ?? '') ?></p>
</div>

<nav class="bx-toc" aria-label="In this article">
    <p class="bx-toc-h">In this article</p>
    <ol>
        <li><a href="#at-a-glance">The three at a glance</a></li>
        <li><a href="#content-writing">What content writing is</a></li>
        <li><a href="#copywriting">What copywriting is</a></li>
        <li><a href="#content-marketing">What content marketing is</a></li>
        <li><a href="#three-ways">One topic, written three ways</a></li>
        <li><a href="#overlap">Where the lines blur</a></li>
        <li><a href="#which-one">Which one you need, by symptom</a></li>
        <li><a href="#mistakes">Three hiring mistakes</a></li>
        <li><a href="#faq-title">FAQs</a></li>
    </ol>
</nav>

<h2 id="at-a-glance">Content Writing, Copywriting and Content Marketing at a Glance</h2>
<p>Content writing informs, copywriting persuades, and content marketing decides what gets written and where it goes. The table sets them side by side.</p>

<?php $table('Content writing, copywriting and content marketing compared', ['Compared on', 'Content writing', 'Copywriting', 'Content marketing'], [
    ['Its job', 'Answer a question properly', 'Get one action from the reader', 'Decide what to publish, for whom and where'],
    ['The reader is', 'Looking for an answer', 'Deciding whether to act', 'Not reading it: it is a plan'],
    ['Typical formats', 'Articles, guides, white papers, case studies, product descriptions', 'Home and landing pages, ads, sales emails, headlines, buttons', 'Strategy, editorial calendar, distribution and reporting'],
    ['Typical length', 'Long enough to answer fully', 'As short as the decision allows', 'A few pages, reviewed monthly'],
    ['Measured by', 'Search visibility, time on page, enquiries it assists', 'Clicks, form fills, replies, sales', 'Enquiries and pipeline from the whole program'],
    ['Skill underneath', 'Research and clear explanation', 'Knowing the buyer\'s objections', 'Planning, prioritising and measuring'],
]); ?>

<h2 id="content-writing">What Content Writing Is</h2>
<p>Content writing is writing that explains. It produces the articles, guides, white papers, case studies and product descriptions a buyer reads while working out what they need and who can supply it.</p>
<p>The reader arrives with a question. A good piece answers it in the first lines, then earns the rest of the read with detail the reader could not have guessed: a method, a comparison, a number with its source, an example from real work.</p>
<p>In B2B, that detail almost never lives in the writer's head. It lives with your engineers, consultants and sales team. That is why our own <a href="/content-writing-services/">content writing services</a> start every piece with an interview, and why a <a href="/technical-content-writing-services/">technical content</a> draft goes back to a specialist before it is published.</p>
<p>You need content writing when:</p>
<ul class="post-list">
    <li>buyers ask the same questions on every sales call</li>
    <li>competitors appear in search and AI answers for questions you could answer better</li>
    <li>sales has nothing useful to send after a first meeting</li>
    <li>your product needs explaining before anyone can want it</li>
</ul>

<h2 id="copywriting">What Copywriting Is</h2>
<p>Copywriting is writing that asks for something. It produces the headline, the landing page, the advertisement and the sales email, and every line exists to move the reader toward one action.</p>
<p>The reader here is not looking for an education. They are deciding, usually in a few seconds, whether this is for them and whether to click, call or fill in the form. So copy is short, and each sentence either answers an objection or gets cut.</p>
<p>Good copy comes from knowing the buyer better than the product. The useful raw material is the buyer's own words: what they said on sales calls, what they wrote in reviews, what they asked before signing. Our <a href="/copywriting-services/">copywriting services</a> are built on exactly that.</p>
<p>You need copywriting when:</p>
<ul class="post-list">
    <li>pages get visits and few enquiries</li>
    <li>ads get clicks and the landing page loses them</li>
    <li>visitors cannot say what you do after ten seconds on the home page</li>
    <li>sales emails get opened and not answered</li>
</ul>

<h2 id="content-marketing">What Content Marketing Is</h2>
<p>Content marketing is the plan around the writing. It decides which topics are worth covering, who each piece is for, where it will be published and sent, and how you will know whether it worked.</p>
<p>The Content Marketing Institute <?= ext_link('https://contentmarketinginstitute.com/what-is-content-marketing/', 'defines content marketing') ?> as a strategic approach: creating and distributing valuable, relevant and consistent content for a clearly defined audience, with profitable customer action as the goal. Two words in that definition do most of the work. Distributing, because an unread article is a cost. And consistent, because one good piece rarely changes anything.</p>
<p>A content marketing program uses both kinds of writing. Content writing fills the calendar. Copywriting makes sure each piece leads somewhere. The program itself is the part most businesses skip, and it is what our <a href="/content-marketing-services/">content marketing services</a> provide: strategy, calendar, production, distribution and a monthly review of what brought enquiries.</p>
<p>You need content marketing when:</p>
<ul class="post-list">
    <li>you publish regularly and cannot say what it produced</li>
    <li>topics are chosen by whoever has an idea that week</li>
    <li>articles go live and are never sent to anyone</li>
    <li>sales and marketing describe the buyer differently</li>
</ul>

<h2 id="three-ways">One Topic, Written Three Ways</h2>
<p>Take one company and one topic. The company is an illustration, not a client: a manufacturer of dosing pumps for water treatment plants. The topic is how to choose the right pump size.</p>

<?php $table('The same topic handled three ways (illustration)', ['Compared on', 'Content writing', 'Copywriting', 'Content marketing'], [
    ['What gets made', 'A guide: how to size a dosing pump for a treatment plant', 'A landing page for the sizing service', 'A three-month plan around pump selection'],
    ['How it opens', 'Size a dosing pump from three numbers: flow rate, dose and back pressure.', 'Send us your flow rate. Get the right pump size by tomorrow.', 'Goal: 20 sizing requests a quarter from plant engineers.'],
    ['What it contains', 'The method, a worked example, common mistakes, a table of ranges', 'One promise, three proof points, a short form', 'Six topics, who is interviewed, where each piece is sent, what is measured'],
    ['What the reader does', 'Understands the method and trusts the company', 'Fills in the form', 'Nothing: the team follows it'],
    ['It worked if', 'Engineers find it, read it and come back', 'A healthy share of visitors send their numbers', 'Sizing requests rise and sales can name the pieces that helped'],
]); ?>

<p>None of the three replaces the others. The guide brings the engineer in. The landing page turns interest into a request. The plan makes sure the guide exists, gets sent to the right people and is followed by the next useful piece.</p>

<aside class="bx-cta bx-cta--audit" aria-label="Content plan">
    <svg class="bx-cta-art" viewBox="0 0 120 120" width="104" height="104" aria-hidden="true" focusable="false"><rect x="18" y="14" width="84" height="92" rx="12" fill="#fff" stroke="#DAD5F2" stroke-width="6"/><path d="M34 40h52" stroke="#FF3D8A" stroke-width="7" stroke-linecap="round" opacity=".45"/><path d="M30 40h60" stroke="#FF3D8A" stroke-width="2.5" stroke-linecap="round"/><rect x="32" y="54" width="56" height="14" rx="5" fill="#FFC94D"/><path d="M34 82h36" stroke="#1D1936" stroke-width="7" stroke-linecap="round"/></svg>
    <div class="bx-cta-copy">
        <p class="bx-cta-h">Bring One Topic. Leave With a Plan.</p>
        <p>Tell us what you sell and what is not working. We will tell you which kind of writing fixes it, and what we would write first.</p>
        <a class="bx-btn bx-btn--accent" href="/content-writing-services/#enquire" data-cta="blog-cwcm-plan">Get My Content Plan <span aria-hidden="true">&rarr;</span></a>
        <p class="bx-cta-note">A senior strategist replies within one working day.</p>
    </div>
</aside>

<h2 id="overlap">Where the Lines Blur</h2>
<p>Real work rarely stays in one box. These formats sit on a border, and it helps to know which side each leans toward.</p>
<ul class="post-list">
    <li><strong>Website pages.</strong> A service page has to explain like content and ask like copy. This is why <a href="/website-content-writing-services/">website content writing</a> is planned page by page, with one job and one action for each.</li>
    <li><strong>SEO content.</strong> It is content writing with search discipline added: a brief built from real queries, an answer near the top and a plan to keep the page current. See <a href="/seo-content-writing/">SEO content writing</a>.</li>
    <li><strong>Case studies.</strong> They read like content and work like copy, because a buyer uses them to justify a decision. The persuasion comes from checked numbers, not adjectives. More on <a href="/case-study-writing-services/">case study writing</a>.</li>
    <li><strong>Product descriptions.</strong> Short, repeated hundreds of times, and read at the moment of choosing. They need a copywriter's economy and a content writer's accuracy. See <a href="/product-description-writing-services/">product description writing</a>.</li>
    <li><strong>Company blogs.</strong> A <a href="/blog-writing-services/">managed blog</a> is content writing on a schedule. It becomes content marketing only when someone decides the topics from buyer questions and sends each article somewhere.</li>
</ul>

<h2 id="which-one">Which One You Need, by Symptom</h2>
<p>Start from the problem you can see, not from the job title. Most companies recognise themselves in one or two rows.</p>

<?php $table('Which kind of writing fixes which problem', ['What you are seeing', 'What you need', 'Where to start'], [
    ['Buyers ask the same basic questions on every call', 'Content writing', 'One article per question, written from an interview with whoever answers it today'],
    ['Plenty of visitors, few enquiries', 'Copywriting', 'The three pages with the most visits, and the enquiry form'],
    ['You publish often and cannot say what it produced', 'Content marketing', 'A plan built from sales conversations, with reporting on enquiries'],
    ['A new website is designed and the pages are empty', 'Website content writing', 'A page plan: one job and one action per page'],
    ['Sales has nothing to send after a meeting', 'Content writing', 'Two case studies and one in-depth guide'],
    ['Marketing copy gets the product wrong', 'Technical content writing', 'An engineer interview and a technical review for each piece'],
    ['Ads get clicks, and the page loses them', 'Copywriting', 'A landing page that repeats the promise in the ad'],
]); ?>

<p>If the second row is yours, our guide on <a href="/blog/why-your-website-gets-traffic-but-no-leads/">why a website gets traffic but no leads</a> goes through the causes in order.</p>

<h2 id="mistakes">Three Hiring Mistakes That Waste the Budget</h2>
<ol class="bx-steps">
    <li><strong>Buying the wrong skill for the job.</strong> Ask for samples of the exact format you need. A strong landing page writer may never have written a white paper, and the reverse is just as common.</li>
    <li><strong>Buying pieces with no plan.</strong> Ten articles on ten unconnected topics help less than five that answer the questions one type of buyer asks, in the order they ask them.</li>
    <li><strong>Judging each by the other's numbers.</strong> A guide will not produce sales in its first week, and a landing page should not be praised for its word count. Agree what each piece is for before it is written.</li>
</ol>
<p>One practical check before you hire anyone: write a one-page brief. If you cannot say who the reader is and what they should do next, no writer can either. Our <a href="/blog/how-to-brief-a-content-writer/">content brief builder</a> walks through the ten questions.</p>
<?php },

'closing' => static function (): void { ?>
<h2 id="final-thoughts">Final Thoughts</h2>
<p>Content writing earns attention, copywriting turns it into action, and content marketing makes sure both happen on purpose. A business with only the first has readers and no enquiries. With only the second, it has sharp pages nobody finds. With neither plan nor measurement, it has activity.</p>
<p>You do not have to buy all three at once. Find your row in the table above, fix that first, and add the next piece when the first one is working.</p>

<aside class="bx-cta bx-cta--main" aria-label="Start a conversation with VTurnU">
    <p class="bx-cta-h">Not Sure Which One You Need?</p>
    <p>Tell us what you sell and what is not working. We reply with a straight answer, and a plan if you want one.</p>
    <div class="bx-cta-acts">
        <a class="bx-btn bx-btn--light" href="/contact-us/" data-cta="blog-cwcm-contact">Get My Content Plan <span aria-hidden="true">&rarr;</span></a>
        <a class="bx-link bx-link--light" href="/content-writing-services/" data-cta="blog-cwcm-services">See Content Writing Services</a>
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
