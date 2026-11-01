<?php /** About page. Expects: $page, $slug */ ?>
<?php render_hero([
    'k' => 'About VTurnU', 'h1' => $page['h1'], 'lede' => $page['lede'], 'layout' => 'split',
    'cta' => ['Start a conversation', '/contact-us/'], 'alt' => ['How we work', '/how-we-work/'],
    'visual' => hero_visual(['routes', ['An honest audit', 'A plan tied to a number', 'Senior hands on the work', 'Reports in plain language']]),
]); ?>

<section class="section">
    <div class="container split">
        <div>
            <p class="eyebrow">Our story</p>
            <h2 class="section-title">Together we turn heads</h2>
            <p>VTurnU began with a simple observation: most businesses do not need more marketing, they need marketing that works. Too many brands were paying for activity instead of outcomes, and for reports instead of revenue.</p>
            <p>So we built a team around the opposite idea. Every engagement starts with an honest audit. Every strategy is tied to a business number. Every report answers one question: is this making you grow?</p>
            <p>Digital marketing and branding are our core. We also build websites and run a separate Enterprise &amp; IT practice, so a growing business can work with one accountable partner from Chennai, wherever its customers are.</p>
        </div>
        <div>
            <div class="stats-col">
                <div class="stat tilt"><strong>150+</strong><span>Projects delivered</span></div>
                <div class="stat tilt"><strong>10+</strong><span>Industries served</span></div>
                <div class="stat tilt"><strong>25+</strong><span>Specialists on the team</span></div>
                <div class="stat tilt"><strong>24h</strong><span>Reply time</span></div>
            </div>
        </div>
    </div>
</section>

<section class="section section-tint">
    <div class="container">
        <p class="eyebrow center">What we do</p>
        <h2 class="section-title center">Four practices, one team</h2>
        <div class="card-grid cols-4">
            <a class="card" href="/digital-marketing/" data-reveal="card"><h3>Digital Marketing</h3><p>SEO, paid media, content, social and branding. Our core.</p></a>
            <a class="card" href="/ai/" data-reveal="card"><h3>AI Search</h3><p>Visibility inside ChatGPT, Gemini, Perplexity and AI Overviews.</p></a>
            <a class="card" href="/web-services/" data-reveal="card"><h3>Web Development</h3><p>Fast, search-ready websites and custom builds.</p></a>
            <a class="card" href="/enterprise-it-services/" data-reveal="card"><h3>Enterprise &amp; IT</h3><p>SAP CPQ, Salesforce, Odoo, ERPNext and IT staffing.</p></a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <p class="eyebrow center">Values</p>
        <h2 class="section-title center">What we believe</h2>
        <div class="card-grid cols-3">
            <div class="card" data-reveal="card"><h3>Outcomes over activity</h3><p>Deliverables are a means, not an end. We measure ourselves on your leads, sales and revenue.</p></div>
            <div class="card" data-reveal="card"><h3>Honesty first</h3><p>If a channel will not work for you, we will say so, even when it costs us the engagement.</p></div>
            <div class="card" data-reveal="card"><h3>Stay ahead</h3><p>From AI Overviews to conversational commerce, we adopt what is next before it is obvious.</p></div>
            <div class="card" data-reveal="card"><h3>Senior hands</h3><p>Your account is run by experienced specialists, not handed down to juniors after the pitch.</p></div>
            <div class="card" data-reveal="card"><h3>Transparent always</h3><p>Clear pricing, live reporting and no lock-in. You always know what is happening and why.</p></div>
            <div class="card" data-reveal="card"><h3>Grow together</h3><p>Our best clients stay for years, because we grow when they grow.</p></div>
        </div>
    </div>
</section>

<section class="section section-tint">
    <div class="container narrow">
        <aside class="facts-card" aria-labelledby="facts-h">
            <h2 id="facts-h">VTurnU at a glance</h2>
            <dl>
                <div><dt>Company</dt><dd>VTurnU</dd></div>
                <div><dt>Headquarters</dt><dd>Chennai, Tamil Nadu, India</dd></div>
                <div><dt>Founded</dt><dd><?= e(FOUNDING_YEAR) ?></dd></div>
                <div><dt>Email</dt><dd><a href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a></dd></div>
                <div><dt>Phone</dt><dd><a href="<?= e(CONTACT_PHONE_HREF) ?>"><?= e(CONTACT_PHONE) ?></a></dd></div>
                <?php if (gst_visible()): ?>
                <div><dt>GSTIN</dt><dd><?= e(GSTIN) ?></dd></div>
                <?php endif; ?>
            </dl>
        </aside>
    </div>
</section>

<?php render_faqs(page_faqs($page, $slug), 'About VTurnU: common questions'); ?>
