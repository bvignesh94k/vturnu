<?php /** Legal / policy pages. Expects: $page, $slug */ ?>
<section class="page-hero">
    <div class="container">
        <h1><?= e($page['h1']) ?></h1>
        <p class="lede"><?= e($page['lede']) ?></p>
    </div>
</section>

<section class="section">
    <div class="container narrow legal-body">
        <?php if ($slug === 'privacy-policy'): ?>
            <p><em>Last updated: <?= date('F Y') ?></em></p>
            <h2>1. Information We Collect</h2>
            <p>When you use vturnu.com or contact us, we may collect: your name, email address, phone number, company details and any information you include in enquiry forms; plus standard technical data such as browser type, pages visited and referring URLs collected through analytics.</p>
            <h2>2. How We Use Your Information</h2>
            <p>We use your information to respond to enquiries, deliver services you've engaged us for, improve our website, and, only where you've agreed, send you relevant updates. We never sell your personal data.</p>
            <h2>3. Cookies &amp; Analytics</h2>
            <p>We use cookies and analytics tools to understand how visitors use our site. You can disable cookies in your browser at any time; the site will continue to work.</p>
            <h2>4. Data Protection</h2>
            <p>Enquiry data is stored securely, access-restricted to team members who need it, and retained only as long as necessary for the purpose it was collected.</p>
            <h2>5. Third Parties</h2>
            <p>We share data with service providers (such as email and analytics platforms) only to the extent needed to operate our business, under appropriate safeguards.</p>
            <h2>6. Your Rights</h2>
            <p>You may request access to, correction of, or deletion of your personal data at any time by emailing <a href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a>.</p>
            <h2>7. Contact</h2>
            <p>Questions about this policy? Contact us at <a href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a> or <?= e(CONTACT_PHONE) ?>.</p>

        <?php elseif ($slug === 'terms-and-conditions'): ?>
            <p><em>Last updated: <?= date('F Y') ?></em></p>
            <h2>1. Acceptance of Terms</h2>
            <p>By accessing vturnu.com or engaging VTurnU's services, you agree to these terms. If you do not agree, please do not use the site or services.</p>
            <h2>2. Services</h2>
            <p>Specific deliverables, timelines and fees for any engagement are defined in the written proposal or agreement for that engagement. In case of conflict, the engagement agreement prevails over these general terms.</p>
            <h2>3. Payments</h2>
            <p>Retainers are invoiced monthly in advance; project work is invoiced per the agreed milestone schedule. Advertising spend is paid by the client directly to the platforms.</p>
            <h2>4. Intellectual Property</h2>
            <p>Upon full payment, deliverables created specifically for you become yours. Our pre-existing tools, frameworks and methodologies remain ours. Website content on vturnu.com may not be reproduced without permission.</p>
            <h2>5. Results Disclaimer</h2>
            <p>Marketing outcomes depend on factors beyond any agency's control. Case-study figures reflect specific past engagements and are not a guarantee of future results.</p>
            <h2>6. Limitation of Liability</h2>
            <p>To the maximum extent permitted by law, VTurnU's aggregate liability under any engagement is limited to the fees paid for that engagement in the preceding three months.</p>
            <h2>7. Termination</h2>
            <p>Either party may end a retainer with 30 days' written notice. Fees for work performed up to the termination date remain payable.</p>
            <h2>8. Contact</h2>
            <p>Questions about these terms? Email <a href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a>.</p>

        <?php else: /* ai-policy */ ?>
            <p><em>Last updated: <?= date('F Y') ?></em></p>
            <h2>Why We Have an AI Policy</h2>
            <p>AI is part of how modern marketing gets done, including at VTurnU. We believe clients deserve to know exactly how we use it. This policy is that answer.</p>
            <h2>1. Human Judgment Leads</h2>
            <p>AI accelerates research, analysis and drafting. Strategy, recommendations and final deliverables are always reviewed, edited and approved by experienced humans. Nothing ships on autopilot.</p>
            <h2>2. Quality Over Volume</h2>
            <p>We do not mass-generate content. Every published piece must meet the same standard regardless of how it was drafted: accurate, original, genuinely useful and aligned with search quality guidelines.</p>
            <h2>3. Your Data Stays Protected</h2>
            <p>We do not feed confidential client data into public AI tools. Where AI tools process client information, we use enterprise offerings with appropriate data-protection controls.</p>
            <h2>4. Transparency</h2>
            <p>Ask us how AI was used in your engagement and we'll tell you, specifically. AI-assisted work is priced fairly: you pay for outcomes and expertise, not inflated hours.</p>
            <h2>5. We Practice What We Optimize</h2>
            <p>As specialists in AI search optimization, we hold ourselves to the standards AI engines reward: expertise, accuracy, originality and trustworthiness.</p>
            <h2>Questions?</h2>
            <p>Talk to us at <a href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a>.</p>
        <?php endif; ?>
    </div>
</section>
