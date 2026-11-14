<?php
/**
 * Listicle: twelve questions for a website design brief, with a brief builder.
 * Loaded by templates/blog-post.php through the 'rich' key. Each question has
 * an answer box (answer mode in includes/listicle-kit.php); assets/js/listicle.js
 * keeps the answers in the reader's browser and turns them into a brief to copy
 * or download. Nothing is sent to the server. Example answers are labelled.
 */

$prompts = static function (array $items): string {
    return '<ul>' . implode('', array_map(static fn ($i) => '<li>' . $i . '</li>', $items)) . '</ul>';
};

$brief_tools = '<div class="lx-field"><label for="lx-project">Project or company name</label>'
    . '<input id="lx-project" type="text" data-lx-field="project" autocomplete="organization" placeholder="Example: Acme Sensors website"></div>'
    . '<div class="lx-btns"><button class="lx-btn" type="button" data-lx-brief-copy>Copy my brief</button>'
    . '<button class="lx-btn lx-btn--ghost" type="button" data-lx-brief-download>Download as a text file</button></div>'
    . '<p class="lx-privacy">Your answers stay in this browser, on this device. Nothing is sent to us or to anyone else.</p>';

return [

'body' => static function () use ($prompts, $brief_tools): void { ?>
<p class="bx-open">Send five agencies a vague brief and you will get five proposals you cannot compare. One quotes a brochure site, one a lead generation engine, one a full rebrand.</p>
<p>A good brief fixes that. It does not need design language or technical detail. It needs straight answers to twelve questions about your goals, your buyers, your content and your constraints, so that every agency prices the same project.</p>
<p>Answer each question in the box under it as you read. Your answers are saved in this browser, and at the end you can copy or download them as a finished brief, ready to send.</p>

<div class="answer-box post-answer">
    <p class="answer-text"><?= e($GLOBALS['BLOG']['website-design-brief-questions']['lede'] ?? '') ?></p>
</div>

<?php lx_glance([
    ['goal', 'What must the website achieve in the next 12 months?', 'Goals'],
    ['audience', 'Who are your buyers, and what do they need to decide?', 'Audience'],
    ['action', 'What should a visitor do next?', 'Conversion'],
    ['current-site', 'What works on your current site, and what does not?', 'Current site'],
    ['content', 'Which pages do you need, and who will write them?', 'Content'],
    ['proof', 'What proof can you show?', 'Trust'],
    ['brand', 'How should it look and sound?', 'Brand'],
    ['integrations', 'What must the website connect to?', 'Integrations'],
    ['ownership', 'Who will run the site after launch?', 'After launch'],
    ['requirements', 'What technical and legal requirements apply?', 'Requirements'],
    ['budget', 'What budget range and timeline are you working with?', 'Budget'],
    ['decision', 'Who decides, and how will you choose?', 'Decision'],
], 'The 12 questions'); ?>

<?php lx_board('website-brief', [
    'mode' => 'answer',
    'label' => 'questions answered',
    'msgs' => [
        [0, 'Answer in your own words. Short notes are fine, and you can come back later.'],
        [6, 'Keep going. Goals, content and integrations matter most for an accurate quote.'],
        [11, 'Nearly done. Any question left blank shows as "Not answered yet" in your brief.'],
        [12, 'All twelve answered. Copy or download your brief and send it to the agencies you are comparing.'],
    ],
    'tools' => $brief_tools,
]); ?>

<?php lx_item(1, 'goal', 'What must the website achieve in the next 12 months?', ['tags' => ['Goals', 'Answer this first'], 'answer' => ['label' => 'Your answer', 'placeholder' => 'Example: Double qualified quote requests from manufacturers by next September. We get about 10 a month today.']], static function () use ($prompts): void { ?>
<p>Agencies quote very different projects when the goal is unclear. A brochure site, a lead generation site and a sales tool can look alike in a design file and cost very different amounts to build and run.</p>
<?php lx_split('Cover', $prompts([
    'The business goal the site supports: more qualified enquiries, shorter sales cycles, a new market',
    'How you will measure it: enquiries, demo requests, quote requests or applications',
    'Where you are today, if you know it',
    'Any date that drives the project, such as a launch, an event or a funding round',
]), 'Avoid', $prompts([
    'Goals no one can measure, such as "a modern look"',
    'A list of ten goals with no order of importance',
])); ?>
<?php }); ?>

<?php lx_item(2, 'audience', 'Who are your buyers, and what do they need to decide?', ['tags' => ['Audience'], 'answer' => ['label' => 'Your answer', 'placeholder' => 'Example: Plant managers find us; finance directors approve. They ask about lead times, certifications and support.']], static function () use ($prompts): void { ?>
<p>A website is a sales conversation without a salesperson. It can only answer the questions you know buyers ask, so ask the people who talk to buyers every day.</p>
<?php lx_split('Cover', $prompts([
    'Job titles in the buying group, and who signs off',
    'The five questions sales hears most often',
    'What buyers compare you on, and who they compare you with',
    'The languages and regions you sell into',
]), 'Ask sales', $prompts([
    'Why did the last three deals choose you?',
    'Why did the last three lost deals go elsewhere?',
])); ?>
<?php }); ?>

<?php lx_item(3, 'action', 'What should a visitor do next?', ['tags' => ['Conversion'], 'answer' => ['label' => 'Your answer', 'placeholder' => 'Example: Request a quote. Sales replies within four working hours. We need company, product and quantity.']], static function () use ($prompts): void { ?>
<p>Every page needs one clear next step. If you cannot name it, the design team has to guess, and you end up with a site full of buttons that all say "Learn more".</p>
<?php lx_split('Cover', $prompts([
    'The main action: an enquiry form, a demo booking, a quote request or a call',
    'Smaller steps for people not ready yet, such as a guide or a newsletter',
    'What happens after a form is sent: who replies, and how fast',
    'Which form fields sales needs, and which it never uses',
]), 'Worth knowing', $prompts([
    'Every extra field is one more reason to give up, so ask only for what sales uses.',
    'Plan spam protection now. Our guide on <a href="/blog/how-to-stop-junk-leads-on-your-website/">how to stop junk leads on your website</a> explains the options.',
])); ?>
<?php }); ?>

<?php lx_item(4, 'current-site', 'What works on your current site, and what does not?', ['tags' => ['Current site'], 'answer' => ['label' => 'Your answer', 'placeholder' => 'Example: Product pages bring most enquiries. The blog is out of date. Editing anything needs a developer.']], static function () use ($prompts): void { ?>
<p>A redesign that ignores today's site can throw away the pages that bring your enquiries, and the search rankings that bring visitors to them. Start with what to keep.</p>
<?php lx_split('Cover', $prompts([
    'Your top landing pages, and where their visitors come from',
    'Pages that rank in search and must keep their URLs',
    'Known problems: speed, mobile layout, pages that are hard to edit',
    'What customers and sales say about the current site',
]), 'Protect', $prompts([
    'URLs that rank or attract links. Plan a redirect for every one you change, as in our guide to <a href="/blog/website-redesign-without-losing-seo/">redesigning without losing SEO</a>.',
])); ?>
<?php }); ?>

<?php lx_item(5, 'content', 'Which pages do you need, and who will write them?', ['tags' => ['Content', 'Common cause of delay'], 'answer' => ['label' => 'Your answer', 'placeholder' => 'Example: About 25 pages. We will write the service pages; we need help with the home page and case studies.']], static function () use ($prompts): void { ?>
<p>Content is often what holds a launch back. A design can be approved in a few weeks while the copy waits for someone with time to write it. Decide now who writes what, and by when.</p>
<?php lx_split('Cover', $prompts([
    'A first list of pages, grouped into sections',
    'Which existing content can be reused and which needs rewriting',
    'Who writes, reviews and approves each page',
    'Photos, video, diagrams and translations you need',
]), 'If nobody has time', $prompts([
    'Budget for a writer now rather than after the design is done. Our <a href="/website-content-writing-services/">website content writing</a> team works from interviews with your experts, so the copy still sounds like you.',
    'Not sure what each page should say? See <a href="/blog/what-to-write-on-every-page-of-a-b2b-website/">what to write on every page of a B2B website</a>.',
])); ?>
<?php }); ?>

<?php lx_item(6, 'proof', 'What proof can you show?', ['tags' => ['Trust'], 'answer' => ['label' => 'Your answer', 'placeholder' => 'Example: Three case studies approved. Logos for six clients. ISO 9001 certificate.']], static function () use ($prompts): void { ?>
<p>B2B buyers look for evidence that you have solved their problem before. Proof collected at the last minute tends to be thin, so start gathering it while the site is being designed.</p>
<?php lx_split('Cover', $prompts([
    'Case studies you have permission to publish',
    'Client logos you are allowed to show',
    'Certifications, standards, awards and memberships',
    'Reviews on independent sites, and results you can verify',
]), 'Keep it honest', $prompts([
    'Use only real names, real logos and real numbers, with the client\'s permission.',
    'A dated result with its context beats a big claim with none.',
    'Our guide on <a href="/blog/how-to-write-a-b2b-case-study/">how to write a B2B case study</a> covers permissions and how to source a number.',
])); ?>
<?php }); ?>

<?php lx_item(7, 'brand', 'How should it look and sound?', ['tags' => ['Brand'], 'answer' => ['label' => 'Your answer', 'placeholder' => 'Example: Follow our brand guide. We like sites that show the product early and keep text short.']], static function () use ($prompts): void { ?>
<p>Taste is hard to brief. Examples with reasons are easier for a designer to act on than words like "modern" or "clean", which mean something different to everyone.</p>
<?php lx_split('Cover', $prompts([
    'Brand guidelines, logo files, fonts and colours',
    'Three websites you like, and what you like about each',
    'Three you do not like, and why',
    'The tone you want: formal, plain, technical or warm',
]), 'Check early', $prompts([
    'Brand colours against contrast rules. Pale greys and bright yellows often fail; our <a href="/blog/website-accessibility-checklist/">website accessibility checklist</a> has a free contrast checker.',
])); ?>
<?php }); ?>

<?php lx_item(8, 'integrations', 'What must the website connect to?', ['tags' => ['Integrations', 'Often missed'], 'answer' => ['label' => 'Your answer', 'placeholder' => 'Example: Enquiries go to our CRM with source tracking. Product data comes from our ERP once a day.']], static function () use ($prompts): void { ?>
<p>Integrations are where budgets slip, because they are often discovered late. A form that does not pass data properly loses leads without anyone noticing. FlowEazy's old enquiry form, for example, was <a href="/case-studies/floweazy-odoo-partner-website/">silently dropping two key fields</a>, so sales received leads with half the information missing.</p>
<?php lx_split('Cover', $prompts([
    'Your CRM and email platform, and who owns each account',
    'Analytics, tag manager, ad platforms and your consent tool',
    'Booking, chat, payment or product data feeds',
    'Exactly which form fields must reach which system',
]), 'Ask every agency', $prompts([
    'How will you test that every form reaches the CRM with every field?',
    'Who maintains each integration after launch?',
])); ?>
<?php }); ?>

<aside class="bx-cta bx-cta--ai" aria-label="Website design services">
    <div class="bx-cta-copy">
        <p class="bx-cta-h">Halfway Through Your Brief?</p>
        <p>If a question is hard to answer, that is useful to know before you sign anything. We are happy to talk it through, with no obligation to hire us.</p>
        <div class="bx-cta-acts">
            <a class="bx-btn bx-btn--light" href="/web-design/" data-cta="blog-brief-webdesign">See How We Design Websites <span aria-hidden="true">&rarr;</span></a>
            <a class="bx-link bx-link--light" href="/lead-gen-web-design/" data-cta="blog-brief-leadgen">Lead Generation Websites</a>
        </div>
    </div>
    <svg class="bx-cta-art" viewBox="0 0 150 130" width="150" height="130" aria-hidden="true" focusable="false"><rect x="28" y="14" width="94" height="108" rx="12" fill="#fff"/><rect x="52" y="6" width="46" height="18" rx="7" fill="#FF9100"/><path d="M44 50l8 8 14-16M44 78l8 8 14-16" fill="none" stroke="#00B8D9" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/><rect x="74" y="46" width="34" height="8" rx="4" fill="#A9A4EA"/><rect x="74" y="74" width="28" height="8" rx="4" fill="#A9A4EA"/><rect x="44" y="100" width="64" height="8" rx="4" fill="#FF3D8A"/></svg>
</aside>

<?php lx_item(9, 'ownership', 'Who will run the site after launch?', ['tags' => ['After launch'], 'answer' => ['label' => 'Your answer', 'placeholder' => 'Example: Marketing updates news weekly. We want a support plan for updates and backups. We must own the code.']], static function () use ($prompts): void { ?>
<p>The cheapest site to build can be the most expensive to own if every small change needs a developer. Describe the life of the site after launch, not just the launch.</p>
<?php lx_split('Cover', $prompts([
    'Who edits pages, and how often',
    'What your team must be able to change without help',
    'Hosting, backups, security updates and support',
    'Who owns the domain, the hosting account, the code and the design files',
]), 'Get in writing', $prompts([
    'That you own the code and the content.',
    'What the support plan covers, and what it costs per month or per hour of work.',
])); ?>
<?php }); ?>

<?php lx_item(10, 'requirements', 'What technical and legal requirements apply?', ['tags' => ['Requirements'], 'answer' => ['label' => 'Your answer', 'placeholder' => 'Example: WCAG 2.2 AA. Cookie consent for EU visitors. English and German at launch.']], static function () use ($prompts): void { ?>
<p>Requirements raised after a design is approved cost the most to add. List them now, including the ones that feel obvious to you.</p>
<?php lx_split('Cover', $prompts([
    'Speed expectations, and the devices your buyers use',
    'An accessibility standard, usually WCAG 2.2 level AA',
    'Privacy, cookie consent and data storage rules for your markets',
    'Languages, security reviews and any rules specific to your industry',
]), 'More than one language?', $prompts([
    'Settle the URL structure before design starts. Our <a href="/blog/international-seo-checklist/">international SEO checklist</a> explains the choices.',
])); ?>
<?php }); ?>

<?php lx_item(11, 'budget', 'What budget range and timeline are you working with?', ['tags' => ['Budget'], 'answer' => ['label' => 'Your answer', 'placeholder' => 'Example: A build range agreed with finance, plus a monthly support budget. Launch before our trade show in March.']], static function () use ($prompts): void { ?>
<p>Without a range, agencies guess, and their proposals cannot be compared. A range is not a commitment: it lets each agency show what it would do for that money, and say so if the money will not stretch.</p>
<?php lx_split('Cover', $prompts([
    'A range for the build, and a separate one for running costs',
    'Fixed dates, and the reason behind each one',
    'What the budget includes: content, photography, SEO, training and support',
    'What can wait for a second phase',
]), 'Watch for', $prompts([
    'Proposals far below the others. Check what they leave out.',
    'Timelines that skip content, testing or training.',
])); ?>
<?php }); ?>

<?php lx_item(12, 'decision', 'Who decides, and how will you choose?', ['tags' => ['Decision'], 'answer' => ['label' => 'Your answer', 'placeholder' => 'Example: Our CEO and sales director decide. We will score proposals on approach, relevant experience and cost.']], static function () use ($prompts): void { ?>
<p>Projects stall when a senior person sees the work for the first time at the end. Name the people who decide, and agree how you will compare proposals, before the first one arrives.</p>
<?php lx_split('Cover', $prompts([
    'Who approves the brief, the design and the launch',
    'How you will compare proposals, and how much each factor counts',
    'When you want proposals, and when you will decide',
    'Questions you want every agency to answer',
]), 'Comparing agencies?', $prompts([
    'Our <a href="/blog/how-to-choose-a-digital-marketing-agency/">checklist for choosing an agency</a> lists the questions that separate a good partner from a good pitch.',
])); ?>
<?php }); ?>

<div class="lx-done-panel lx-js">
    <p class="lx-tool-h">Your brief is ready to share</p>
    <p>Copy it into an email or download it as a text file. Questions you skipped show as "Not answered yet", so you can see what is left.</p>
    <div class="lx-btns">
        <button class="lx-btn" type="button" data-lx-brief-copy>Copy my brief</button>
        <button class="lx-btn lx-btn--ghost" type="button" data-lx-brief-download>Download as a text file</button>
    </div>
</div>
<?php },

'closing' => static function (): void { ?>
<h2 id="final-thoughts">Final Thoughts</h2>
<p>A brief is not paperwork. It is the cheapest point in a website project to change your mind. Every question answered now is a decision nobody has to make in a rush halfway through the build.</p>
<p>Send the same brief to every agency you are considering, and ask each one to show its working: what it would build, in what order, and what it left out. The differences between the answers will tell you more than the differences between the prices.</p>

<aside class="bx-cta bx-cta--main" aria-label="Start a conversation with VTurnU">
    <p class="bx-cta-h">Ready to Brief Your Website Project?</p>
    <p>Send us your brief, or talk it through with us first. We reply within one working day with questions, not a sales pitch.</p>
    <div class="bx-cta-acts">
        <a class="bx-btn bx-btn--light" href="/contact-us/" data-cta="blog-brief-send">Send My Brief <span aria-hidden="true">&rarr;</span></a>
        <a class="bx-link bx-link--light" href="/web-services/" data-cta="blog-brief-web">See Our Web Services</a>
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
