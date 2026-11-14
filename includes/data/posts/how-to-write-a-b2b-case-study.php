<?php
/**
 * Listicle: eight steps to a B2B case study, with interview questions to copy.
 * Loaded by templates/blog-post.php through the 'rich' key; the progress board
 * and done toggles come from includes/listicle-kit.php and assets/js/listicle.js.
 *
 * Every figure and quoted line is taken as published from VTurnU's own case
 * studies (includes/data/cases-real.php) and links to the case it comes from.
 * FirstChoice sells through quote requests: it is never described as a store.
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

$questions = <<<'TXT'
Before
1. What was happening in the business when you started looking for help?
2. What had you already tried, and why did it not work?
3. What would have happened if you had done nothing?

Choosing
4. Who else did you consider, and what made you choose us?
5. What nearly stopped you from going ahead?

The work
6. What did we do that you did not expect?
7. What was harder than you thought it would be?

After
8. What is different now? What can you do that you could not do before?
9. Which number would you show your own manager to prove it worked?
10. What would you tell a company like yours that is considering the same thing?
TXT;

return [

'body' => static function () use ($table, $questions): void { ?>
<p class="bx-open">Most B2B case studies are written for the company that published them. Three paragraphs about the vendor, a logo, and a line about delight.</p>
<p>A buyer reads a case study for a different reason. They are about to recommend you to their own manager, and they need evidence that someone like them took the same risk and it paid off. If the page does not give them a number they can repeat and a situation they recognise, it has not done its job.</p>
<p>The eight steps below are the ones we follow for our own case studies, with examples taken from them. Tick each step as you finish it.</p>

<div class="answer-box post-answer">
    <p class="answer-text"><?= e($GLOBALS['BLOG']['how-to-write-a-b2b-case-study']['lede'] ?? '') ?></p>
</div>

<?php lx_glance([
    ['choose', 'Choose the customer your next buyer will recognise', 'Before writing'],
    ['permission', 'Get permission before you write a word', 'Before writing'],
    ['interview', 'Interview the customer, not only your own team', 'Before writing'],
    ['number', 'Find one number, and say where it came from', 'The evidence'],
    ['limits', 'Say what you could not measure', 'The evidence'],
    ['structure', 'Tell it in four parts', 'Writing'],
    ['headline', 'Write the headline and first paragraph last', 'Writing'],
    ['versions', 'Make three versions and put them to work', 'After writing'],
], 'The 8 steps at a glance'); ?>

<?php lx_board('case-study-steps', [
    'label' => 'steps done',
    'filters' => ['before' => 'Before writing', 'evidence' => 'The evidence', 'writing' => 'Writing', 'after' => 'After writing'],
    'msgs' => [
        [0, 'Work down the list for one customer story. Tick each step as you finish it.'],
        [3, 'The groundwork is under way. Do not start writing until the interview is done.'],
        [5, 'You have the evidence. The writing is the short part.'],
        [7, 'Nearly there. One story should now become three versions.'],
        [8, 'All eight done. Send the short version to sales today.'],
    ],
]); ?>

<?php lx_item(1, 'choose', 'Choose the customer your next buyer will recognise', ['type' => 'before', 'tags' => ['Before writing'], 'mark' => 'Done'], static function (): void { ?>
<p>The instinct is to write about your largest or most famous customer. The more useful choice is the customer who looks like the companies you want next: the same kind of business, the same size, the same problem.</p>
<?php lx_split('Good signs',
    '<ul><li>The result can be measured, or at least clearly described.</li><li>The customer is happy and willing to be interviewed.</li><li>Sales keeps meeting prospects with the same problem.</li></ul>',
    'Think again if',
    '<ul><li>The project was unusual and you would not want five more like it.</li><li>The only result is that the customer was pleased.</li><li>The story depends on details the customer will never approve.</li></ul>'); ?>
<?php }); ?>

<?php lx_item(2, 'permission', 'Get permission before you write a word', ['type' => 'before', 'tags' => ['Before writing', 'Do this first'], 'mark' => 'Done'], static function (): void { ?>
<p>More case studies die in approval than in writing. Ask at the start, in writing, and be specific about what you want to publish. A customer who has agreed to the outline rarely blocks the final draft.</p>
<?php lx_split('Agree in writing',
    '<ul><li>Whether the company can be named, and the logo used.</li><li>Which numbers can be published.</li><li>Who can be quoted, by name and job title.</li><li>Who gives the final approval, and how long they need.</li></ul>',
    'If they cannot be named',
    '<ul><li>Offer an anonymous version: the industry, the size and the result, with no name.</li><li>It is weaker than a named story and still far better than no proof.</li></ul>'); ?>
<?php }); ?>

<?php lx_item(3, 'interview', 'Interview the customer, not only your own team', ['type' => 'before', 'tags' => ['Before writing', '30 minutes'], 'mark' => 'Done'], static function () use ($questions): void { ?>
<p>Your own team remembers the project. The customer remembers the problem, the doubts and what changed afterwards, which are the parts a buyer wants to read. Thirty minutes on a recorded call is enough.</p>
<p>These ten questions cover the story from start to finish. Ask them in order, and let the customer talk.</p>
<div class="lx-code lx-code--text">
    <div class="lx-code-bar"><span>Interview questions</span><button class="lx-copy lx-js" type="button" data-lx-copy>Copy</button></div>
    <pre tabindex="0"><code><?= e($questions) ?></code></pre>
</div>
<p>Question nine matters most. The number the customer would show their own manager is the number your next buyer needs.</p>
<?php }); ?>

<?php lx_item(4, 'number', 'Find one number, and say where it came from', ['type' => 'evidence', 'tags' => ['The evidence'], 'mark' => 'Done'], static function (): void { ?>
<p>One number a reader can check is worth more than five they cannot. A checkable number has three parts: the figure, the source and the dates.</p>
<p>This is the line we publish for a label manufacturer we work with:</p>
<blockquote class="bx-quote">
    <p>In the three months to 29 October 2026, Google Search Console shows 92.9K impressions and 1.32K clicks for saiimpression.com.</p>
    <cite>From the <a href="/case-studies/sai-impression-label-manufacturer/">Sai Impression case study</a></cite>
</blockquote>
<p>A reader knows what was measured, by which tool and over which period. Compare that with "massively increased visibility", which could mean anything.</p>
<?php lx_split('A good number has',
    '<ul><li>A source the customer can open: their analytics, CRM or accounts.</li><li>A period with a start and an end.</li><li>The unit stated plainly: clicks, enquiries, hours, orders.</li></ul>',
    'Leave out',
    '<ul><li>Percentages with no starting figure.</li><li>Numbers from memory.</li><li>Results from a different period than the work.</li></ul>'); ?>
<?php }); ?>

<?php lx_item(5, 'limits', 'Say what you could not measure', ['type' => 'evidence', 'tags' => ['The evidence', 'Builds trust'], 'mark' => 'Done'], static function (): void { ?>
<p>This is the step almost everyone skips, and it is the one that makes the rest believable. Buyers know that no project is measured perfectly. When a case study admits its limits, they trust the numbers it does give.</p>
<p>Three examples from our own pages:</p>
<ul class="post-list">
    <li><strong>No data, so no number.</strong> The <a href="/case-studies/floweazy-odoo-partner-website/">FlowEazy case study</a> says: "Search Console was not connected when this work was done, so no traffic figure is quoted." It reports the pages built and the speed scores instead.</li>
    <li><strong>A comparison with a flaw.</strong> For <a href="/case-studies/firstchoice-used-auto-parts/">FirstChoice Used Auto Parts</a>, two three-month reports overlap. The page says so, and adds that the comparison "shows direction rather than a clean before and after".</li>
    <li><strong>A number that flatters.</strong> <a href="/case-studies/cafy-careers-uk-job-platform/">Cafy</a> had a 51.7% click-through rate. The page explains that most of those clicks were people already searching for the brand, which is why the next stage of work targets everyone else.</li>
</ul>
<p>None of these lines weakened the story. Each one tells the reader that the other figures were checked by someone who was prepared to leave a number out.</p>
<?php }); ?>

<aside class="bx-cta bx-cta--audit" aria-label="Case study writing">
    <svg class="bx-cta-art" viewBox="0 0 120 120" width="104" height="104" aria-hidden="true" focusable="false"><rect x="14" y="20" width="70" height="26" rx="9" fill="#fff" stroke="#DAD5F2" stroke-width="5"/><rect x="26" y="50" width="70" height="26" rx="9" fill="#fff" stroke="#DAD5F2" stroke-width="5"/><rect x="38" y="80" width="70" height="26" rx="9" fill="#1D1936"/><path d="M50 93l7 7 13-14" fill="none" stroke="#FFC94D" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/></svg>
    <div class="bx-cta-copy">
        <p class="bx-cta-h">Have a Happy Customer and No Time?</p>
        <p>We interview your customer, check every number with them and deliver a web page, a PDF and a short version for sales.</p>
        <a class="bx-btn bx-btn--accent" href="/case-study-writing-services/" data-cta="blog-casestudy-service">See Case Study Writing <span aria-hidden="true">&rarr;</span></a>
        <p class="bx-cta-note">You approve the outline and the final draft. So does your customer.</p>
    </div>
</aside>

<?php lx_item(6, 'structure', 'Tell it in four parts', ['type' => 'writing', 'tags' => ['Writing'], 'mark' => 'Done'], static function () use ($table): void { ?>
<p>A case study is a short story with a fixed shape. Each part answers one question the buyer is asking, in the order they ask it.</p>
<?php $table('The four parts of a B2B case study', ['Part', 'The buyer is asking', 'What to write', 'Share of the page'], [
    ['Situation', 'Is this company like mine?', 'Who the customer is, what they sell and to whom', 'About a tenth'],
    ['Problem', 'Did they have my problem?', 'What was going wrong, what it cost and what they had tried', 'About a quarter'],
    ['What was done', 'What would I be buying?', 'The work, in order, in plain words, with the decisions explained', 'About a third'],
    ['Result', 'Did it work, and how do they know?', 'The number, its source and dates, what could not be measured, and the customer\'s own words', 'About a third'],
]); ?>
<p>Write the problem in the customer's words, not yours. "Sales staff were calling numbers that did not exist" is a problem a reader can feel. "Suboptimal lead quality" is not.</p>
<?php }); ?>

<?php lx_item(7, 'headline', 'Write the headline and first paragraph last', ['type' => 'writing', 'tags' => ['Writing'], 'mark' => 'Done'], static function (): void { ?>
<p>Many readers see only the headline and the first lines, on a results page, in an email or in an AI summary. Those lines have to carry the whole story: who the customer is, what was done and the result.</p>
<p>A pattern that works is the customer, then the work, then the number. Two of our own headlines follow it:</p>
<ul class="post-list">
    <li>FirstChoice Used Auto Parts: 99 Pages Rebuilt, 10.7K AI Citations</li>
    <li>FlowEazy: A 101-Page Global Site That Went From 26 to the 90s on Mobile</li>
</ul>
<?php lx_split('The first paragraph should',
    '<ul><li>Name the kind of company, so the right reader stays.</li><li>State the result with its number.</li><li>Make sense with nothing else around it.</li></ul>',
    'Avoid',
    '<ul><li>Opening with your own company.</li><li>Headlines such as "A successful partnership".</li><li>Saving the result for the last paragraph.</li></ul>'); ?>
<?php }); ?>

<?php lx_item(8, 'versions', 'Make three versions and put them to work', ['type' => 'after', 'tags' => ['After writing'], 'mark' => 'Done'], static function (): void { ?>
<p>A case study that lives only on a web page is used by a fraction of the people who need it. Sales works in email, proposals and slides, so give them the story in the forms they use.</p>
<?php lx_split('The three versions',
    '<ul><li><strong>Web page:</strong> the full story, for search, AI answers and visitors.</li><li><strong>PDF:</strong> one or two designed pages to attach to a proposal.</li><li><strong>Short version:</strong> four or five lines for an email or a slide.</li></ul>',
    'Then use it',
    '<ul><li>Link it from the service page it proves.</li><li>Send the short version to sales with a note on when to use it.</li><li>Check the numbers again in a year, and update the date only when you update the content.</li></ul>'); ?>
<?php }); ?>

<h2 id="no-numbers">When You Cannot Share Numbers or Name the Customer</h2>
<p>It happens often in B2B. Some customers cannot be named, and some projects have no outcome data yet. You still have options, and it helps to know how strong each kind of evidence is.</p>

<?php $table('Kinds of evidence in a case study, strongest first', ['Evidence', 'Example', 'How to present it'], [
    ['Business results from the customer\'s systems', 'Enquiries, orders or revenue from their CRM or accounts', 'Figure, source and dates'],
    ['Channel data with a source', 'Search Console clicks, AI citations reported by Bing Webmaster Tools', 'Figure, source and dates, with any caveat'],
    ['Operational change', 'A form that stopped losing fields, a page that loads faster', 'Before and after, measured the same way'],
    ['Work delivered', 'Pages built, articles written, banners designed', 'As a count, and never dressed up as an outcome'],
    ['The customer\'s own words', 'A quote about what changed for their team', 'Named and approved, or anonymous with the job title'],
]); ?>

<p>Be clear about which kind you are showing. "101 pages built" is work delivered, and "92.9K impressions in three months" is channel data. Both are true and both are useful. They are not the same claim, and a careful buyer can tell when one is presented as the other.</p>
<?php },

'closing' => static function (): void { ?>
<h2 id="final-thoughts">The One Piece of Marketing a Buyer Can Check</h2>
<p>A case study is the one piece of marketing a buyer can check. That is its whole value, and it is why the method matters more than the prose. Choose the right customer, ask before you write, find one number with a source and admit what you could not measure.</p>
<p>Do that and the writing takes an afternoon. Skip it and no amount of polish will make the page believable.</p>

<aside class="bx-cta bx-cta--main" aria-label="Start a conversation with VTurnU">
    <p class="bx-cta-h">Want Case Studies Your Sales Team Will Send?</p>
    <p>Tell us which customers have a story. We handle the interview, the numbers, the approvals and all three versions.</p>
    <div class="bx-cta-acts">
        <a class="bx-btn bx-btn--light" href="/case-study-writing-services/#enquire" data-cta="blog-casestudy-plan">Plan My Case Studies <span aria-hidden="true">&rarr;</span></a>
        <a class="bx-link bx-link--light" href="/case-studies/" data-cta="blog-casestudy-read">Read Our Case Studies</a>
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
