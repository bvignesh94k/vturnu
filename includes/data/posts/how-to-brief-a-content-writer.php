<?php
/**
 * Listicle: ten questions for a content brief, with a brief builder.
 * Loaded by templates/blog-post.php through the 'rich' key. Each question has
 * an answer box (answer mode in includes/listicle-kit.php); assets/js/listicle.js
 * keeps the answers in the reader's browser and turns them into a brief to copy
 * or download. Nothing is sent to the server. The filled-in example at the end
 * describes a made-up company and is labelled as an illustration.
 */

$prompts = static function (array $items): string {
    return '<ul>' . implode('', array_map(static fn ($i) => '<li>' . $i . '</li>', $items)) . '</ul>';
};

$brief_tools = '<div class="lx-field"><label for="lx-project">Working title of the piece</label>'
    . '<input id="lx-project" type="text" data-lx-field="project" autocomplete="off" placeholder="Example: How to size a dosing pump"></div>'
    . '<div class="lx-btns"><button class="lx-btn" type="button" data-lx-brief-copy>Copy my brief</button>'
    . '<button class="lx-btn lx-btn--ghost" type="button" data-lx-brief-download>Download as a text file</button></div>'
    . '<p class="lx-privacy">Your answers stay in this browser, on this device. Nothing is sent to us or to anyone else.</p>';

return [

'body' => static function () use ($prompts, $brief_tools): void { ?>
<p class="bx-open">Most disappointing articles were decided before the writer typed a word. The brief said "1,500 words on warehouse automation, by Friday", and that is exactly what came back.</p>
<p>A writer can only use what you give them. If the brief names a topic and a word count, you get a correct summary of what is already online. If it names a reader, a question, an angle and a person to interview, you get something only your company could have published.</p>
<p>The ten questions below make that difference. Answer each one in the box under it as you read. Your answers are saved in this browser, and at the end you can copy or download them as a finished brief.</p>

<div class="answer-box post-answer">
    <p class="answer-text"><?= e($GLOBALS['BLOG']['how-to-brief-a-content-writer']['lede'] ?? '') ?></p>
</div>

<?php lx_glance([
    ['reader', 'Who is the reader?', 'Audience'],
    ['question', 'What question does the piece answer?', 'Purpose'],
    ['action', 'What should the reader do next?', 'Purpose'],
    ['angle', 'What do you know that others do not?', 'Substance'],
    ['expert', 'Who can the writer interview?', 'Substance'],
    ['proof', 'What proof can be used?', 'Substance'],
    ['must', 'What must be included, and what must be avoided?', 'Rules'],
    ['format', 'What format and length suit the job?', 'Format'],
    ['voice', 'How should it sound?', 'Format'],
    ['approval', 'Who approves it, and by when?', 'Process'],
], 'The 10 questions'); ?>

<?php lx_board('content-brief', [
    'mode' => 'answer',
    'title' => 'Content brief',
    'file' => 'content',
    'label' => 'questions answered',
    'msgs' => [
        [0, 'Answer in your own words. Short notes are fine, and you can come back later.'],
        [4, 'Good start. The next three questions decide whether the piece says anything new.'],
        [9, 'Nearly done. Any question left blank shows as "Not answered yet" in your brief.'],
        [10, 'All ten answered. Copy or download your brief and send it to your writer.'],
    ],
    'tools' => $brief_tools,
]); ?>

<?php lx_item(1, 'reader', 'Who is the reader?', ['tags' => ['Audience', 'Answer this first'], 'answer' => ['label' => 'Your answer', 'placeholder' => 'Example: Plant engineers at water treatment sites. They specify the pump; purchasing places the order.']], static function () use ($prompts): void { ?>
<p>"Decision makers" is not a reader. A writer needs one person to picture: their job, what they already know and what is making them search today. That picture decides the vocabulary, the depth and the examples.</p>
<?php lx_split('Cover', $prompts([
    'Job title, and the kind of company they work for',
    'What they already know about the subject',
    'What just happened that made them look for this',
    'Who else reads it before a decision: a manager, finance, a technical reviewer',
]), 'Avoid', $prompts([
    'Three audiences in one piece. Pick one and write another piece for the next.',
    'Describing the reader by age or location when their job is what matters.',
])); ?>
<?php }); ?>

<?php lx_item(2, 'question', 'What question does the piece answer?', ['tags' => ['Purpose'], 'answer' => ['label' => 'Your answer', 'placeholder' => 'Example: How do I work out what size dosing pump my plant needs?']], static function () use ($prompts): void { ?>
<p>A topic is not a question. "Warehouse automation" could be forty different articles. "What does it cost to automate a 5,000 square metre warehouse?" is one, and the writer knows when it has been answered.</p>
<?php lx_split('Cover', $prompts([
    'The question, in the words a buyer would type or ask an AI assistant',
    'Two or three follow-up questions the same reader asks next',
    'The search phrase you would like the page found for, if you have one',
]), 'Worth knowing', $prompts([
    'Ask sales and support which questions they answered this week. Those are your topics.',
    'If the piece is for search, <a href="/seo-content-writing/">SEO content writing</a> adds the query research to this brief.',
])); ?>
<?php }); ?>

<?php lx_item(3, 'action', 'What should the reader do next?', ['tags' => ['Purpose'], 'answer' => ['label' => 'Your answer', 'placeholder' => 'Example: Send us their flow rate and dose through the sizing form. Engineers reply within one working day.']], static function () use ($prompts): void { ?>
<p>Every piece should lead somewhere that suits a reader at that stage. Someone reading a beginner's guide is not ready for a sales call, and someone reading a comparison of suppliers probably is.</p>
<?php lx_split('Cover', $prompts([
    'The one next step: a related guide, a tool, a sample, an enquiry',
    'The page that step lives on',
    'What happens after they take it, and how fast',
]), 'Avoid', $prompts([
    '"Contact us" at the end of everything, whatever the reader came for.',
    'Three competing buttons. One clear step beats a menu.',
])); ?>
<?php }); ?>

<?php lx_item(4, 'angle', 'What do you know that others do not?', ['tags' => ['Substance', 'Most skipped'], 'answer' => ['label' => 'Your answer', 'placeholder' => 'Example: Most sizing guides ignore back pressure. Half the undersized pumps we replace failed for that reason.']], static function () use ($prompts): void { ?>
<p>This is the question briefs leave out, and it is the one that matters most. If your piece says what the first ten results already say, there is no reason for anyone to read yours, or for a search engine to prefer it.</p>
<?php lx_split('Cover', $prompts([
    'Something you see in your work that the usual advice gets wrong',
    'A mistake customers make before they come to you',
    'A method, a rule of thumb or a number from your own projects',
    'An opinion you would defend in front of a competitor',
]), 'If you are stuck', $prompts([
    'Ask your most experienced person: "What do people always get wrong about this?"',
    'The answer is usually the article.',
])); ?>
<?php }); ?>

<?php lx_item(5, 'expert', 'Who can the writer interview?', ['tags' => ['Substance'], 'answer' => ['label' => 'Your answer', 'placeholder' => 'Example: Our applications engineer, 30 minutes on a call, any afternoon next week.']], static function () use ($prompts): void { ?>
<p>Half an hour with the person who knows the subject is worth more than a week of desk research. The writer gets the detail, the examples and the way your company talks about the problem, all at once.</p>
<?php lx_split('Cover', $prompts([
    'The name and role of the person who knows this subject',
    'How much time they can give: 20 to 30 minutes is usually enough for one piece',
    'Existing material the writer should read first: decks, proposals, call recordings',
]), 'Worth knowing', $prompts([
    'Busy experts say yes more easily to a call than to "can you write something".',
    'This is how our <a href="/blog-writing-services/">blog writing services</a> work: one short interview per article.',
])); ?>
<?php }); ?>

<?php lx_item(6, 'proof', 'What proof can be used?', ['tags' => ['Substance'], 'answer' => ['label' => 'Your answer', 'placeholder' => 'Example: Test bench data for the P-series. The Northfield plant project, with the client\'s approval. No pricing.']], static function () use ($prompts): void { ?>
<p>Readers believe what they can check. List the numbers, projects and sources the writer is allowed to use, and say where each one comes from. A number without a source is a liability, because one wrong figure makes a reader doubt the rest.</p>
<?php lx_split('Cover', $prompts([
    'Figures you can share, with their source and date',
    'Customer projects you may name, and any that must stay anonymous',
    'Outside sources you trust, such as standards bodies or regulators',
    'Anything confidential that must not appear',
]), 'Avoid', $prompts([
    'Round numbers nobody can trace.',
    'Customer names used without written permission. Our guide on <a href="/blog/how-to-write-a-b2b-case-study/">writing a B2B case study</a> covers approvals.',
])); ?>
<?php }); ?>

<?php lx_item(7, 'must', 'What must be included, and what must be avoided?', ['tags' => ['Rules'], 'answer' => ['label' => 'Your answer', 'placeholder' => 'Example: Mention the sizing calculator. Do not name competitors. Do not promise delivery times.']], static function () use ($prompts): void { ?>
<p>Every company has a short list of things that must be said and things that must never be. Writers find out about them in the second round of edits unless you put them in the brief.</p>
<?php lx_split('Include', $prompts([
    'Products, pages or tools the piece should mention and link to',
    'Terms you always use, and how you spell them',
    'Legal or regulatory wording that applies',
]), 'Avoid', $prompts([
    'Claims you cannot support, such as guarantees',
    'Competitor names, if that is your policy',
    'Words your buyers dislike or do not use',
])); ?>
<?php }); ?>

<?php lx_item(8, 'format', 'What format and length suit the job?', ['tags' => ['Format'], 'answer' => ['label' => 'Your answer', 'placeholder' => 'Example: A how-to article with a worked example and one table. As long as the method needs, probably 1,200 to 1,500 words.']], static function () use ($prompts): void { ?>
<p>Choose the format from what the reader has to do with the piece, then let the length follow. A method needs steps. A choice needs a table. A decision that costs a lot needs evidence and room to lay it out.</p>
<?php lx_split('Cover', $prompts([
    'The format: how-to, comparison, checklist, explainer, case study, white paper',
    'Parts you expect: a table, a worked example, a diagram, questions and answers',
    'A length range, as a guide and not a target',
    'Where it will be published and where else it will be used',
]), 'Worth knowing', $prompts([
    'Padding to reach a word count is the fastest way to lose a reader.',
    'Not sure which format fits? See <a href="/blog/content-writing-vs-copywriting-vs-content-marketing/">content writing vs copywriting vs content marketing</a>.',
])); ?>
<?php }); ?>

<?php lx_item(9, 'voice', 'How should it sound?', ['tags' => ['Format'], 'answer' => ['label' => 'Your answer', 'placeholder' => 'Example: Like our senior engineer explaining it to a new colleague. Plain, direct, no sales talk. British spelling.']], static function () use ($prompts): void { ?>
<p>"Professional but friendly" describes every company and helps no writer. Point to something real instead: a page you like, a person whose emails read well, or a list of phrases you never want to see.</p>
<?php lx_split('Cover', $prompts([
    'Two or three pieces whose tone you like, yours or anyone\'s',
    'Who is speaking: the company, a named expert, the founder',
    'Spelling and style: British or American, "we" or the company name',
    'Words and phrases to keep out',
]), 'A quick test', $prompts([
    'Would your most respected engineer say this sentence out loud to a customer?',
    'If not, it does not belong on the page.',
])); ?>
<?php }); ?>

<?php lx_item(10, 'approval', 'Who approves it, and by when?', ['tags' => ['Process'], 'answer' => ['label' => 'Your answer', 'placeholder' => 'Example: The applications engineer checks facts, the marketing lead approves. Draft by the 14th, live by the 21st.']], static function () use ($prompts): void { ?>
<p>Drafts go stale in inboxes. Name one person who checks the facts and one who gives the final yes, and put dates against both. Too many reviewers turn a clear piece into a compromise.</p>
<?php lx_split('Cover', $prompts([
    'Who checks technical accuracy',
    'Who gives final approval: one name',
    'Dates for the interview, the draft, the review and publishing',
    'How many rounds of changes you expect',
]), 'Avoid', $prompts([
    'Review by committee. Collect comments in one document, from named people.',
    'Reviewers rewriting for style when they were asked to check facts.',
])); ?>
<?php }); ?>

<div class="lx-done-panel lx-js">
    <p class="lx-tool-h">Your brief is ready to send</p>
    <p>Copy it into an email or download it as a text file. Questions you skipped show as "Not answered yet", so you can see what is left.</p>
    <div class="lx-btns">
        <button class="lx-btn" type="button" data-lx-brief-copy>Copy my brief</button>
        <button class="lx-btn lx-btn--ghost" type="button" data-lx-brief-download>Download as a text file</button>
    </div>
</div>

<h2 id="example-brief">A Filled-In Brief, on One Page</h2>
<p>This is what a finished brief looks like. The company is made up, to show the level of detail that helps a writer. Notice how short each answer is.</p>

<div class="bx-table-wrap">
    <table class="bx-table" role="table">
        <caption>Example content brief (illustration, not a client)</caption>
        <thead role="rowgroup"><tr role="row"><th scope="col" role="columnheader">Question</th><th scope="col" role="columnheader">Answer in the brief</th></tr></thead>
        <tbody role="rowgroup">
            <?php foreach ([
                ['Reader', 'Plant engineers at municipal water treatment sites. They specify; purchasing orders.'],
                ['Question', 'How do I work out what size dosing pump my plant needs?'],
                ['Next step', 'Send flow rate and dose through the sizing form. Reply within one working day.'],
                ['Angle', 'Most guides ignore back pressure, the most common reason we replace undersized pumps.'],
                ['Expert', 'Applications engineer, 30 minutes by call next week.'],
                ['Proof', 'Test bench data for one pump series. One named project, approved by the client.'],
                ['Include and avoid', 'Mention the sizing calculator. No competitor names. No delivery promises.'],
                ['Format and length', 'How-to with a worked example and one table. About 1,200 to 1,500 words.'],
                ['Voice', 'A senior engineer explaining to a new colleague. Plain, no sales talk.'],
                ['Approval', 'Engineer checks facts, marketing lead approves. Draft by the 14th, live by the 21st.'],
            ] as $row): ?>
            <tr role="row"><th scope="row" role="rowheader"><?= e($row[0]) ?></th><td role="cell" data-label="Answer in the brief"><?= e($row[1]) ?></td></tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<p>A brief like this takes about twenty minutes to write. It usually saves a round of revisions, and it is the difference between an article about pumps and an article your engineers would put their name to.</p>
<?php },

'closing' => static function (): void { ?>
<h2 id="final-thoughts">If You Only Answer Three Questions</h2>
<p>The brief is where your knowledge gets into the piece. Questions four, five and six do most of that work: the angle, the expert and the proof. If you only have time for three answers, give the writer those.</p>
<p>Keep the briefs you write. After a few months they become a record of what your buyers ask and what your company knows about it, which is most of a content strategy.</p>

<aside class="bx-cta bx-cta--main" aria-label="Start a conversation with VTurnU">
    <p class="bx-cta-h">Want Us to Write From Your Brief?</p>
    <p>Send us the brief, or talk it through with us first. We interview your expert, write the piece and edit it until you would sign it.</p>
    <div class="bx-cta-acts">
        <a class="bx-btn bx-btn--light" href="/contact-us/" data-cta="blog-cbrief-send">Send My Brief <span aria-hidden="true">&rarr;</span></a>
        <a class="bx-link bx-link--light" href="/content-writing-services/" data-cta="blog-cbrief-services">See Content Writing Services</a>
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
