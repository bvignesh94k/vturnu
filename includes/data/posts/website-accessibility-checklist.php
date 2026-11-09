<?php
/**
 * Listicle: eleven accessibility tests anyone can run, scored pass, fail or not
 * sure, with a colour contrast checker. Loaded by templates/blog-post.php through
 * the 'rich' key; the interactive parts come from includes/listicle-kit.php and
 * assets/js/listicle.js. Success criteria are from WCAG 2.2 (W3C Recommendation,
 * updated 12 December 2024) and are paraphrased, not quoted. Checked 8 Nov 2026.
 * External links are nofollow.
 */

$sc = static fn (string $id, string $label): string => ext_link('https://www.w3.org/TR/WCAG22/#' . $id, $label);
$w3 = [
    'large' => 'https://www.w3.org/WAI/WCAG22/Understanding/contrast-minimum.html',
    'easy' => 'https://www.w3.org/WAI/test-evaluate/preliminary/',
    'tools' => 'https://www.w3.org/WAI/test-evaluate/tools/selecting/',
    'eaa' => 'https://eur-lex.europa.eu/eli/dir/2019/882/oj/eng',
];

return [

'body' => static function () use ($sc, $w3): void { ?>
<p class="bx-open">You do not need a developer or a special tool to find many accessibility problems on your website. You need a keyboard, a phone, about an hour and a list of what to look for.</p>
<p>The eleven tests below come from the Web Content Accessibility Guidelines, WCAG 2.2, the W3C standard most organisations aim for at level AA. Each test says what to check, how to check it and how to fix what you find. Mark each one Pass, Fail or Not sure as you go, and the board builds your list of fixes.</p>
<p>Accessible sites work better for everyone. Clear labels, readable contrast and pages that work without a mouse help a tired buyer on a phone as much as someone using a screen reader.</p>
<p class="bx-note"><strong>A note on the law.</strong> Rules differ by country and by what you sell. In the EU, for example, the European Accessibility Act has <?= ext_link($w3['eaa'], 'applied since 28 June 2025') ?> to certain products and services, including e-commerce services for consumers, with an exemption for microenterprises that provide services. Check what applies to you with a specialist; this checklist is not legal advice.</p>

<div class="answer-box post-answer">
    <p class="bx-answer-k">The short answer</p>
    <p class="answer-text"><?= e($GLOBALS['BLOG']['website-accessibility-checklist']['lede'] ?? '') ?></p>
</div>

<?php lx_glance([
    ['keyboard', 'Use the whole page with a keyboard', 'WCAG 2.1.1'],
    ['focus', 'Always see where you are', 'WCAG 2.4.7, 2.4.11'],
    ['contrast', 'Check text and icon contrast', 'Free checker inside'],
    ['zoom', 'Zoom to 200 percent, then narrow the window', 'WCAG 1.4.4, 1.4.10'],
    ['alt-text', 'Give images useful alt text', 'WCAG 1.1.1'],
    ['headings', 'Use headings in a logical order', 'WCAG 1.3.1, 2.4.6'],
    ['links', 'Make every link make sense', 'WCAG 2.4.4'],
    ['forms', 'Label every field and explain every error', 'WCAG 3.3.1, 3.3.2'],
    ['media', 'Caption videos and let people stop motion', 'WCAG 1.2.2, 1.4.2, 2.2.2'],
    ['language', 'Set the page language', 'WCAG 3.1.1'],
    ['targets', 'Make tap targets big enough', 'WCAG 2.5.8'],
], 'The 11 tests at a glance'); ?>

<?php lx_board('a11y-tests', [
    'mode' => 'tri',
    'label' => 'tests run',
    'filters' => ['quick' => 'Quick tests', 'deeper' => 'Take longer'],
    'msgs' => [
        [0, 'Pick one important page, such as your home page or contact page, and run each test on it.'],
        [5, 'Keep going. Mark "Not sure" when you cannot tell; those are worth a closer look by a specialist.'],
        [10, 'One test to go.'],
        [11, 'All eleven tests run. Start with anything marked Fail, in the order shown under "Fix these first".'],
    ],
]); ?>

<?php lx_item(1, 'keyboard', 'Use the whole page with a keyboard', ['type' => 'deeper', 'tags' => ['Takes 10 minutes', 'WCAG 2.1.1, level A'], 'tri' => true], static function () use ($sc): void { ?>
<p>Some people cannot use a mouse, and many screen reader users move around a page with the keyboard. If a menu, a slider or a form only works with a mouse, it does not work for them. WCAG covers this under <?= $sc('keyboard', 'Keyboard') ?>.</p>
<?php lx_split('How to test',
    '<p>Put the mouse aside. Press Tab to move forward and Shift and Tab to go back. Use Enter to follow links and press buttons, Space to tick boxes, and the arrow keys inside menus and radio groups.</p><p>Can you reach and use every link, menu, field and button? Can you close pop-ups and the chat window with Esc?</p>',
    'How to fix',
    '<ul><li>Use real links and buttons instead of clickable boxes.</li><li>Make menus open with Enter, not only on hover.</li><li>Make sure focus never gets stuck inside a pop-up.</li></ul>'); ?>
<?php }); ?>

<?php lx_item(2, 'focus', 'Always see where you are', ['type' => 'quick', 'tags' => ['Takes 5 minutes', 'WCAG 2.4.7 and 2.4.11, level AA'], 'tri' => true], static function () use ($sc): void { ?>
<p>Keyboard users need to see which element they are on. WCAG asks for a <?= $sc('focus-visible', 'visible focus indicator') ?>, and version 2.2 added that the focused element must not be <?= $sc('focus-not-obscured-minimum', 'completely hidden by other content') ?>, such as a sticky header or a cookie banner.</p>
<?php lx_split('How to test',
    '<p>During the keyboard test, watch the outline or highlight on each element. Does it ever disappear? Does a sticky header, a cookie banner or a chat button cover the item you are on?</p>',
    'How to fix',
    '<ul><li>Never remove the browser\'s focus outline without a clear replacement.</li><li>Add scroll padding so sticky headers do not cover focused items.</li></ul>'); ?>
<?php }); ?>

<?php lx_item(3, 'contrast', 'Check text and icon contrast', ['type' => 'quick', 'tags' => ['Free checker inside', 'WCAG 1.4.3 and 1.4.11, level AA'], 'tri' => true], static function () use ($sc, $w3): void { ?>
<p>Low contrast is easy to miss on a good monitor indoors and hard to read on a phone in daylight. At level AA, WCAG asks for a <?= $sc('contrast-minimum', 'contrast ratio of at least 4.5 to 1') ?> for normal text and 3 to 1 for large text, and <?= $sc('non-text-contrast', '3 to 1 for icons, input borders') ?> and other parts of the interface people need to see.</p>
<p>Large text means at least 18 point, or 14 point bold. W3C's <?= ext_link($w3['large'], 'explanation of the contrast rule') ?> puts that at roughly 24 pixels, or about 18.5 pixels in bold, on screen.</p>

<div class="lx-tool lx-js" data-lx-tool="contrast" id="contrast-checker">
    <p class="lx-tool-k">Free tool</p>
    <p class="lx-tool-h">Colour contrast checker</p>
    <p>Enter your text and background colours as hex codes, or pick them. The ratio is never rounded up, so a result just under a threshold fails, as it should.</p>
    <div class="lx-cc-grid">
        <div class="lx-field"><label for="cc-fg">Text colour</label><div class="lx-cc-pair"><input type="color" value="#767676" data-cc-pick="fg" aria-label="Pick the text colour"><input id="cc-fg" type="text" value="#767676" data-cc="fg" autocomplete="off" spellcheck="false" maxlength="7"></div></div>
        <div class="lx-field"><label for="cc-bg">Background colour</label><div class="lx-cc-pair"><input type="color" value="#ffffff" data-cc-pick="bg" aria-label="Pick the background colour"><input id="cc-bg" type="text" value="#FFFFFF" data-cc="bg" autocomplete="off" spellcheck="false" maxlength="7"></div></div>
        <button class="lx-btn lx-btn--ghost" type="button" data-cc-swap>Swap colours</button>
    </div>
    <p class="lx-cc-err" data-cc-err hidden>Enter each colour as a hex code, such as #1D1936.</p>
    <div class="lx-cc-preview" data-cc-preview><p class="lx-cc-big">Large text sample</p><p>Normal text sample: request a quote for your next project.</p></div>
    <p class="lx-cc-ratio">Contrast ratio <strong data-cc-ratio>4.54</strong> to 1</p>
    <ul class="lx-cc-res">
        <li data-cc-min="4.5"><span>Normal text, level AA (4.5 to 1)</span><b data-cc-verdict>Pass</b></li>
        <li data-cc-min="3"><span>Large text, level AA (3 to 1)</span><b data-cc-verdict>Pass</b></li>
        <li data-cc-min="3"><span>Icons, borders and form fields (3 to 1)</span><b data-cc-verdict>Pass</b></li>
        <li data-cc-min="7"><span>Normal text, level AAA (7 to 1)</span><b data-cc-verdict>Fail</b></li>
        <li data-cc-min="4.5"><span>Large text, level AAA (4.5 to 1)</span><b data-cc-verdict>Pass</b></li>
    </ul>
</div>

<?php lx_split('How to test',
    '<p>Check body text, pale grey captions, placeholder text, text over images, button labels and links. Test your brand colours too, especially light ones.</p>',
    'How to fix',
    '<ul><li>Darken the text or lighten the background.</li><li>For text over photos, add a solid or semi-transparent panel behind it.</li></ul>'); ?>
<?php }); ?>

<?php lx_item(4, 'zoom', 'Zoom to 200 percent, then narrow the window', ['type' => 'quick', 'tags' => ['Takes 5 minutes', 'WCAG 1.4.4 and 1.4.10, level AA'], 'tri' => true], static function () use ($sc): void { ?>
<p>Many people enlarge text to read comfortably. WCAG asks that text can be <?= $sc('resize-text', 'resized to 200 percent') ?> without losing content or function, and that content <?= $sc('reflow', 'reflows at a width of 320 CSS pixels') ?> without scrolling in two directions.</p>
<?php lx_split('How to test',
    '<p>Press Ctrl and + (Cmd and + on a Mac) until the browser shows 200 percent. Then go to 400 percent on a desktop browser, which is close to a 320 pixel wide screen. Can you read everything without scrolling sideways? Does anything overlap or vanish?</p>',
    'How to fix',
    '<ul><li>Use responsive layouts.</li><li>Avoid fixed heights on boxes that hold text.</li><li>Never put important text inside images.</li></ul>'); ?>
<?php }); ?>

<?php lx_item(5, 'alt-text', 'Give images useful alt text', ['type' => 'quick', 'tags' => ['Takes 10 minutes', 'WCAG 1.1.1, level A'], 'tri' => true], static function () use ($sc): void { ?>
<p>Screen readers read out an image's alt text. Without it, a chart, a product photo or a linked logo becomes silence, or worse, a file name. WCAG covers this under <?= $sc('non-text-content', 'Non-text Content') ?>.</p>
<?php lx_split('How to test',
    '<p>Right-click an image and choose Inspect, or use an accessibility browser extension, to read each alt attribute. Informative images should describe what matters. Decorative images should have empty alt text, written alt="". A linked logo should say where it goes.</p>',
    'How to fix',
    '<ul><li>Write alt text for meaning, not keywords: "Calibration lab with three test benches" beats "image1" or "lab".</li><li>Mark purely decorative images with empty alt text so screen readers skip them.</li></ul>'); ?>
<?php }); ?>

<?php lx_item(6, 'headings', 'Use headings in a logical order', ['type' => 'deeper', 'tags' => ['Takes 10 minutes', 'WCAG 1.3.1 and 2.4.6'], 'tri' => true], static function () use ($sc): void { ?>
<p>Screen reader users jump from heading to heading to scan a page, the way sighted readers skim. That only works when headings are real heading tags, in a sensible order, that <?= $sc('headings-and-labels', 'describe the section that follows') ?>. Structure shown visually must also be <?= $sc('info-and-relationships', 'available in the code') ?>.</p>
<?php lx_split('How to test',
    '<p>List the headings with a headings extension or your browser\'s accessibility panel. Is there one H1 that names the page? Do the levels follow in order? Is any large bold text pretending to be a heading?</p>',
    'How to fix',
    '<ul><li>Choose heading levels for structure, then style them however you like.</li><li>Turn bold paragraphs that act as headings into real headings.</li></ul>'); ?>
<?php }); ?>

<aside class="bx-cta bx-cta--audit" aria-label="Free website audit">
    <svg class="bx-cta-art" viewBox="0 0 120 120" width="104" height="104" aria-hidden="true" focusable="false"><circle cx="60" cy="60" r="46" fill="none" stroke="#DAD5F2" stroke-width="12"/><path d="M60 14a46 46 0 1 1-40 69" fill="none" stroke="#FF3D8A" stroke-width="12" stroke-linecap="round"/><path d="M42 61l13 13 25-29" fill="none" stroke="#1D1936" stroke-width="9" stroke-linecap="round" stroke-linejoin="round"/></svg>
    <div class="bx-cta-copy">
        <p class="bx-cta-h">Halfway There? Check the Basics Too</p>
        <p>The free audit flags missing image alt text, heading structure and other page basics in seconds.</p>
        <a class="bx-btn bx-btn--accent" href="/free-seo-audit/" data-cta="blog-a11y-audit">Run My Free Website Audit <span aria-hidden="true">&rarr;</span></a>
        <p class="bx-cta-note">It is a search audit, not a full accessibility test. Use it alongside the tests on this page.</p>
    </div>
</aside>

<?php lx_item(7, 'links', 'Make every link make sense', ['type' => 'quick', 'tags' => ['Takes 5 minutes', 'WCAG 2.4.4, level A'], 'tri' => true], static function () use ($sc): void { ?>
<p>Screen reader users can bring up a list of every link on a page. Ten links that all say "Read more" tell them nothing. WCAG asks that the <?= $sc('link-purpose-in-context', 'purpose of each link is clear') ?> from its text, or from its text together with the surrounding context.</p>
<?php lx_split('How to test',
    '<p>Search the page for "click here", "read more" and "learn more". Would someone hearing only the link text know where each one goes?</p>',
    'How to fix',
    '<ul><li>Put the destination in the link: "Read the calibration case study" instead of "Read more".</li><li>Make links look like links, not only by colour.</li></ul>'); ?>
<?php }); ?>

<?php lx_item(8, 'forms', 'Label every field and explain every error', ['type' => 'deeper', 'tags' => ['Takes 10 minutes', 'WCAG 3.3.1 and 3.3.2, level A'], 'tri' => true], static function () use ($sc): void { ?>
<p>Forms are where accessibility problems cost enquiries directly. Every field needs <?= $sc('labels-or-instructions', 'a label or instructions') ?>, and when something goes wrong, the error must be <?= $sc('error-identification', 'identified and described in text') ?>.</p>
<?php lx_split('How to test',
    '<p>Click each label: does it put the cursor in its field? Submit the form empty. Are the errors described in words next to the right fields, or only shown as a red border? Does the hint inside a field vanish as soon as you start typing, taking the instructions with it?</p>',
    'How to fix',
    '<ul><li>Use visible labels; placeholder text is not a label.</li><li>Write errors that say what to do: "Enter a work email, such as name@company.com".</li><li>Keep what people typed when an error appears.</li></ul>'); ?>
<?php }); ?>

<?php lx_item(9, 'media', 'Caption videos and let people stop motion', ['type' => 'deeper', 'tags' => ['Takes 10 minutes', 'WCAG 1.2.2, 1.4.2 and 2.2.2, level A'], 'tri' => true], static function () use ($sc): void { ?>
<p>Videos with speech need <?= $sc('captions-prerecorded', 'captions') ?>. Audio that plays on its own for more than three seconds needs a way to <?= $sc('audio-control', 'pause it or turn it down') ?>. And carousels or animations that move for more than five seconds need a way to <?= $sc('pause-stop-hide', 'pause, stop or hide them') ?>.</p>
<?php lx_split('How to test',
    '<p>Play each video with the sound off. Are there accurate captions, not just unedited automatic ones? Does anything move or play without you asking? Can you stop it?</p>',
    'How to fix',
    '<ul><li>Correct automatic captions before you publish.</li><li>Add pause controls to carousels and background video, or remove the motion.</li></ul>'); ?>
<?php }); ?>

<?php lx_item(10, 'language', 'Set the page language', ['type' => 'quick', 'tags' => ['Takes 2 minutes', 'WCAG 3.1.1, level A'], 'tri' => true], static function () use ($sc): void { ?>
<p>Screen readers use the page's <?= $sc('language-of-page', 'language setting') ?> to choose how words are pronounced. Without it, a French page may be read aloud with English rules. It is a one-line fix in your template.</p>
<?php lx_split('How to test',
    '<p>View the page source and look at the html tag on the first lines. It should carry a lang attribute, such as lang="en", with the right code on each language version.</p>',
    'How to fix',
    '<ul><li>Set lang in the site template, per language version.</li><li>Google does not use this attribute to detect language, but screen readers do. For the search side, see our <a href="/blog/international-seo-checklist/">international SEO checklist</a>.</li></ul>'); ?>
<?php }); ?>

<?php lx_item(11, 'targets', 'Make tap targets big enough', ['type' => 'quick', 'tags' => ['Takes 5 minutes', 'WCAG 2.5.8, level AA'], 'tri' => true], static function () use ($sc): void { ?>
<p>Small buttons are hard to hit with a finger, and harder still for people with a tremor. WCAG 2.2 added a <?= $sc('target-size-minimum', 'minimum target size of 24 by 24 CSS pixels') ?>, with exceptions such as links inside a sentence and targets with enough space around them.</p>
<?php lx_split('How to test',
    '<p>On a phone, try the small things: close buttons, social icons, pagination, checkboxes and footer links. Do you ever hit the wrong one?</p>',
    'How to fix',
    '<ul><li>Add padding around small icons and links.</li><li>For main buttons, go well beyond the minimum.</li></ul>'); ?>
<?php }); ?>

<h2 id="automated-checks">Then Run an Automated Check</h2>
<p>Automated checkers are fast and catch problems you might miss, such as missing labels in code. They cannot do the job alone: W3C is clear that evaluation tools <?= ext_link($w3['tools'], 'can only assist') ?> a person in judging accessibility. Use one alongside the tests above, not instead of them.</p>
<ul class="post-list">
    <li><strong>Lighthouse in Chrome:</strong> open DevTools, choose Lighthouse and run the accessibility category on each page template.</li>
    <li><strong>W3C's Easy Checks:</strong> the <?= ext_link($w3['easy'], 'first review guide') ?> from W3C covers several of these tests in more depth.</li>
    <li><strong>A specialist audit:</strong> worth it for web applications, complex forms, or when you need a formal conformance report.</li>
</ul>
<?php },

'closing' => static function (): void { ?>
<h2 id="final-thoughts">Final Thoughts</h2>
<p>None of these tests needs special skill, and together they cover problems that stop real people from using a website. Run them on your home page, your main service page and your contact form first: those are the pages where a barrier costs you an enquiry.</p>
<p>Fix what fails, retest, and build the checks into your release routine, so new pages do not bring the same problems back. If you are planning a new site, put WCAG 2.2 level AA in the brief from day one; our <a href="/blog/website-design-brief-questions/">website design brief builder</a> has a place for it. And if forms are where your site struggles, our guide to <a href="/blog/how-to-stop-junk-leads-on-your-website/">stopping junk leads</a> shows how to filter spam without locking real buyers out.</p>

<aside class="bx-cta bx-cta--main" aria-label="Start a conversation with VTurnU">
    <p class="bx-cta-h">Building or Redesigning Your Website?</p>
    <p>We design and build websites with accessibility checked from the first template, not patched in at the end.</p>
    <div class="bx-cta-acts">
        <a class="bx-btn bx-btn--light" href="/contact-us/" data-cta="blog-a11y-talk">Talk to Our Web Team <span aria-hidden="true">&rarr;</span></a>
        <a class="bx-link bx-link--light" href="/web-design/" data-cta="blog-a11y-webdesign">See Our Web Design Work</a>
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
