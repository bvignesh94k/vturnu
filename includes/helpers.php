<?php
/**
 * View helpers, SEO builders and content generators.
 */

declare(strict_types=1);

function e(?string $s): string
{
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

/** Absolute canonical URL for a path. */
function abs_url(string $path): string
{
    return rtrim(SITE_URL, '/') . '/' . ltrim($path, '/');
}

/** URL of a page slug ('' = home). */
function page_url(string $slug): string
{
    return $slug === '' ? '/' : '/' . $slug . '/';
}

/** Breadcrumb trail: [['label','url'],...] ending at current page. */
function breadcrumbs(array $pages, string $slug): array
{
    $trail = [];
    $cur = $slug;
    while ($cur !== null && $cur !== '' && isset($pages[$cur])) {
        $trail[] = [$pages[$cur]['h1'], page_url($cur)];
        $cur = $pages[$cur]['parent'] ?? null;
    }
    $trail[] = ['Home', '/'];
    return array_reverse($trail);
}

/** JSON-LD for the whole site (Organization + WebSite). */
function jsonld_site(): array
{
    return [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Organization',
                '@id' => SITE_URL . '/#organization',
                'name' => SITE_NAME,
                'url' => SITE_URL . '/',
                'slogan' => SITE_TAGLINE,
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => abs_url('/assets/img/vturnu-icon-mark.png'),
                    'width' => 464,
                    'height' => 440,
                ],
                'image' => abs_url('/assets/img/vturnu-logo-dark.png'),
                'email' => CONTACT_EMAIL,
                'telephone' => CONTACT_PHONE,
                'areaServed' => ['India', 'United States', 'United Kingdom', 'Australia', 'Canada'],
                'contactPoint' => [[
                    '@type' => 'ContactPoint',
                    'contactType' => 'sales',
                    'email' => CONTACT_EMAIL,
                    'telephone' => CONTACT_PHONE,
                    'availableLanguage' => ['English', 'Tamil', 'Hindi'],
                ]],
                'sameAs' => array_values(SOCIAL_LINKS),
            ],
            [
                '@type' => 'WebSite',
                '@id' => SITE_URL . '/#website',
                'name' => SITE_NAME,
                'url' => SITE_URL . '/',
                'inLanguage' => 'en',
                'publisher' => ['@id' => SITE_URL . '/#organization'],
            ],
        ],
    ];
}

/** JSON-LD BreadcrumbList. */
function jsonld_breadcrumbs(array $trail): array
{
    $items = [];
    foreach ($trail as $i => [$label, $url]) {
        $items[] = [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'name' => $label,
            'item' => abs_url($url),
        ];
    }
    return ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $items];
}

/** JSON-LD Service for service pages. */
function jsonld_service(array $page, string $slug): array
{
    return [
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        'name' => $page['h1'],
        'description' => $page['meta'],
        'url' => abs_url(page_url($slug)),
        'provider' => ['@id' => SITE_URL . '/#organization'],
        'areaServed' => 'Worldwide',
    ];
}

/** JSON-LD FAQPage. */
function jsonld_faq(array $faqs): array
{
    $items = [];
    foreach ($faqs as [$q, $a]) {
        $items[] = [
            '@type' => 'Question',
            'name' => $q,
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $a],
        ];
    }
    return ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $items];
}

/**
 * Page-level JSON-LD for every template that does not already emit its own.
 *
 * Before this, only service pages, blog posts, the homepage and resource
 * pages carried a page-level node: case studies, About, Contact, pricing,
 * legal and every listing page shipped nothing but Organization, WebSite and
 * BreadcrumbList. That left 40 of 129 URLs with no machine-readable
 * statement of what the page actually is, which is exactly the signal answer
 * engines use to decide whether a page is worth citing.
 *
 * Every value here is read from data already rendered on the page. Nothing is
 * invented: case studies carry no publish date in includes/data/cases.php, so
 * datePublished is omitted rather than guessed.
 *
 * @return array|null null when the caller already emits richer schema.
 */
function jsonld_page(array $page, string $slug, string $canonical, string $template): ?array
{
    $orgId  = SITE_URL . '/#organization';
    $siteId = SITE_URL . '/#website';

    // Templates that already emit their own page-level node elsewhere.
    if (in_array($template, ['service', 'blog-post', 'resource', 'home'], true)) {
        return null;
    }

    $type = match ($template) {
        'contact'    => 'ContactPage',
        'about'      => 'AboutPage',
        'case-detail' => 'Article',
        'blog-list', 'case-list', 'resource-list', 'hub' => 'CollectionPage',
        default      => 'WebPage',
    };

    $node = [
        '@context' => 'https://schema.org',
        '@type' => $type,
        '@id' => $canonical . '#webpage',
        'url' => $canonical,
        'name' => $page['title'] ?? ($page['h1'] ?? ''),
        'description' => $page['meta'] ?? '',
        'inLanguage' => 'en',
        'isPartOf' => ['@id' => $siteId],
        'publisher' => ['@id' => $orgId],
    ];

    /* A case study is an article about a real engagement. Fields come straight
       from the case record so the markup can never drift from the page. */
    if ($template === 'case-detail') {
        /* Resolve through $page['case'], the same key templates/case-detail.php
           uses, rather than parsing the slug: the page and the case record are
           deliberately allowed to have different names. */
        $case = $GLOBALS['CASES'][$page['case'] ?? ''] ?? null;

        $node['headline'] = $page['h1'] ?? ($page['title'] ?? '');
        $node['mainEntityOfPage'] = ['@type' => 'WebPage', '@id' => $canonical];
        $node['author'] = ['@id' => $orgId];
        $node['image'] = abs_url('/assets/img/vturnu-logo-dark.png');

        if ($case) {
            if (!empty($case['industry'])) {
                $node['about'] = ['@type' => 'Thing', 'name' => $case['industry']];
            }
            if (!empty($case['services']) && is_array($case['services'])) {
                $node['mentions'] = array_values(array_map(
                    fn($s) => ['@type' => 'Service', 'name' => $s[0], 'url' => abs_url($s[1])],
                    array_filter($case['services'], fn($s) => is_array($s) && count($s) >= 2)
                ));
            }
        }
    }

    /* The About and Contact pages are the two strongest entity-trust signals
       on the site, so both point explicitly back at the Organization. */
    if ($template === 'about' || $template === 'contact') {
        $node['about'] = ['@id' => $orgId];
        $node['mainEntity'] = ['@id' => $orgId];
    }

    /* Listing pages: name the collection so an answer engine can tell an index
       from a leaf page rather than treating all 129 URLs as equivalent. */
    if ($type === 'CollectionPage') {
        $node['isPartOf'] = ['@id' => $siteId];
        if (!empty($page['h1'])) {
            $node['name'] = $page['h1'];
        }
    }

    // Give voice assistants the same two elements every template renders first.
    $node['speakable'] = [
        '@type' => 'SpeakableSpecification',
        'cssSelector' => ['h1', '.lede', '.answer-text'],
    ];

    return $node;
}

function jsonld_script(array $data): string
{
    return '<script type="application/ld+json">'
        . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        . '</script>';
}

/* ------------------------------------------------------------------ */
/* Content generators, vary sections by page category                */
/* ------------------------------------------------------------------ */

/** Six "what's included" cards for a service page. */
function service_features(array $page): array
{
    if (!empty($page['features'])) {
        return $page['features'];
    }
    $name = $page['h1'];
    $cat = $page['cat'] ?? 'seo';

    $sets = [
        'seo' => [
            ['Technical SEO Audit', 'Crawl health, Core Web Vitals, indexation and site architecture, we fix the foundation before building on it.'],
            ['Keyword & Intent Strategy', 'Keyword research mapped to buyer intent and your funnel, so every page has a clear job to do.'],
            ['On-Page Optimization', 'Titles, structure, internal links and schema markup tuned page by page for maximum relevance.'],
            ['Content That Ranks', 'Briefs and content built from SERP analysis, written to answer better than whatever ranks today.'],
            ['Authority Building', 'Clean, editorial link acquisition and digital PR that strengthens your whole domain.'],
            ['Transparent Reporting', 'Monthly reporting on rankings, traffic and, most importantly, leads and revenue.'],
        ],
        'ai' => [
            ['AI Visibility Audit', 'Where does your brand appear across ChatGPT, Perplexity, Gemini and AI Overviews today? We map your baseline.'],
            ['Entity & Brand Optimization', 'Knowledge-graph and entity work so AI systems understand exactly who you are and what you offer.'],
            ['Answer-Ready Content', 'Content restructured into the clear, citable format generative engines prefer to quote.'],
            ['Structured Data at Scale', 'Schema and machine-readable signals that make your pages easy for AI crawlers to parse and trust.'],
            ['Citation & Mention Building', 'Placements on the sources AI engines cite most, so you enter the answer pool.'],
            ['AI Visibility Tracking', 'Ongoing monitoring of your share of voice inside AI answers, with monthly insight reports.'],
        ],
        'ppc' => [
            ['Account & Tracking Setup', 'Conversion tracking, audiences and account structure built correctly before a rupee is spent.'],
            ['Campaign Strategy', 'Channel and budget mix designed around your margins, targets and sales cycle.'],
            ['Creative & Copy', 'Ads that earn the click: tested hooks, angles and formats refreshed before fatigue sets in.'],
            ['Bid & Budget Optimization', 'Continuous optimization against CPA and ROAS targets with automation guardrails.'],
            ['Landing Page CRO', 'Paid traffic deserves better than your homepage, pages built to convert each campaign.'],
            ['Live Reporting', 'A dashboard you can open anytime, plus monthly deep-dives on what moved and why.'],
        ],
        'social' => [
            ['Channel Strategy', 'The right platforms for your audience, with a clear role for each in your funnel.'],
            ['Content Calendar & Creation', 'Thumb-stopping posts, reels and stories produced on a consistent, planned cadence.'],
            ['Community Management', 'Comments, DMs and mentions handled fast, in your brand voice.'],
            ['Paid Social Amplification', 'Organic tells us what works; paid puts it in front of thousands more of the right people.'],
            ['Influencer & UGC', 'Creator partnerships and user-generated content that borrow trust your ads can\'t buy.'],
            ['Analytics & Iteration', 'Monthly performance reviews that feed straight back into next month\'s content.'],
        ],
        'content' => [
            ['Content Strategy', 'Audience research, topic clusters and an editorial roadmap tied to business goals.'],
            ['Expert Writing', 'Writers matched to your industry, with editorial review on every piece.'],
            ['SEO Optimization', 'Every asset structured and optimized to earn search visibility from day one.'],
            ['Design & Formatting', 'Content packaged to be read: visuals, formatting and UX that keep people on the page.'],
            ['Distribution', 'Email, social and outreach plans so great content actually gets seen.'],
            ['Performance Measurement', 'Content tracked to leads and revenue, with refresh cycles for what\'s working.'],
        ],
        'web' => [
            ['Discovery & UX Strategy', 'We start with your users and goals: sitemaps, wireframes and flows before any pixels.'],
            ['Custom Design', 'A design system built for your brand, not a template with your logo dropped in.'],
            ['Performance-First Build', 'Fast by default: optimized assets, clean code and Core Web Vitals in the green.'],
            ['SEO-Ready Architecture', 'Semantic markup, schema, clean URLs and migration-safe redirects baked into every build.'],
            ['Responsive & Accessible', 'Flawless on every device and usable by everyone, accessibility treated as a requirement.'],
            ['Launch & Support', 'Structured QA, smooth launch and a support plan so your site keeps improving after go-live.'],
        ],
        'consulting' => [
            ['Marketing Audit', 'A frank, evidence-based review of your channels, funnel and analytics.'],
            ['Growth Strategy', 'A prioritized roadmap: where to invest, what to stop, and what to test next.'],
            ['Analytics & Measurement', 'Tracking and dashboards that give you one version of the truth.'],
            ['Channel Playbooks', 'Documented, repeatable playbooks your in-house team can run.'],
            ['Team Coaching', 'Hands-on upskilling for your marketers through weekly working sessions.'],
            ['Quarterly Reviews', 'Regular strategy checkpoints to keep the roadmap honest and current.'],
        ],
        'seo-industry' => [], // filled below
    ];

    if ($cat === 'seo-industry') {
        $ind = $page['industry'] ?? 'your industry';
        return [
            ['Industry Keyword Research', "Keyword and intent mapping specific to how buyers in $ind actually search."],
            ['Competitor Gap Analysis', "We reverse-engineer the sites winning in $ind and find the gaps you can own."],
            ['Technical SEO', 'Crawlability, speed and structured data, the foundation tuned for your platform.'],
            ['Specialist Content', "Content written with genuine $ind expertise, reviewed for accuracy and compliance."],
            ['Authority & Trust Signals', 'Links, mentions and reviews from sources that matter in your space.'],
            ['Lead & Revenue Reporting', 'Reporting focused on enquiries and revenue, not just positions.'],
        ];
    }

    return $sets[$cat] ?? $sets['seo'];
}

/** Four process steps varied by category. */
function service_process(array $page): array
{
    $cat = $page['cat'] ?? 'seo';
    $map = [
        'web' => [
            ['Discover', 'Goals, users, content and technical requirements, captured in a clear brief.'],
            ['Design', 'Wireframes to polished UI, reviewed with you at every stage.'],
            ['Build', 'Clean, fast, SEO-ready development with structured QA.'],
            ['Launch & Grow', 'Smooth go-live, then measurement and iteration to keep improving.'],
        ],
        'ppc' => [
            ['Audit & Setup', 'Tracking verified, account restructured, waste eliminated.'],
            ['Launch', 'Campaigns live with clear hypotheses and success metrics.'],
            ['Optimize', 'Weekly testing of bids, audiences, creative and landing pages.'],
            ['Scale', 'Double down on winners and expand into new channels profitably.'],
        ],
    ];
    return $map[$cat] ?? [
        ['Audit & Research', 'We analyze your current position, competitors and opportunity.'],
        ['Strategy', 'A prioritized roadmap with clear targets and timelines.'],
        ['Execute', 'Our specialists implement, create and optimize, fast.'],
        ['Measure & Improve', 'Transparent reporting and continuous iteration on results.'],
    ];
}

/** FAQs varied by category & service name. */
function service_faqs(array $page): array
{
    if (!empty($page['faqs'])) {
        return $page['faqs'];
    }
    $name = strtolower($page['h1']);
    $cat = $page['cat'] ?? 'seo';

    $timing = match ($cat) {
        'ppc' => 'Paid campaigns start producing data immediately; expect meaningful optimization gains within the first 4–6 weeks.',
        'web' => 'A typical project runs 4–10 weeks depending on scope, we\'ll give you a fixed timeline after discovery.',
        default => 'You\'ll usually see early movement in 6–8 weeks, with compounding results from month three onward.',
    };

    return [
        ["How much do {$name} cost?", 'Pricing depends on scope and competitiveness. After a free consultation we\'ll send a fixed, transparent proposal: no hidden fees, no long lock-ins. See our pricing page for typical ranges.'],
        ['How soon will I see results?', $timing],
        ['How do you report progress?', 'You get a live dashboard plus a monthly report that ties activity to outcomes: leads, sales and revenue, not just vanity metrics.'],
        ['Do you work with businesses like mine?', 'We work with startups, SMEs and enterprises across SaaS, ecommerce, healthcare, real estate, education and manufacturing. Ask us for a relevant case study on our first call.'],
        ['How do we get started?', 'Book a free consultation via our contact page. We\'ll review your goals, audit your current position and come back with a clear plan within a few days.'],
    ];
}

/**
 * Validate + persist an enquiry from any form (contact page, home quote, pop-up).
 * Returns true when stored, false when validation fails (or honeypot tripped).
 *
 * The DB insert is wrapped so a persistence hiccup never blocks the visitor's
 * "thank you" page or the follow-up email, exactly like the old file write
 * (which silenced its own errors with @). The difference is this failure is
 * no longer silent: it goes to error_log(), visible in Vercel's function logs,
 * so a real outage is not indistinguishable from a page that just worked.
 */
function save_enquiry(array $post, string $source): bool
{
    $honeypot = trim($post['website'] ?? '');
    $name  = trim($post['name'] ?? '');
    $email = trim($post['email'] ?? '');
    if ($honeypot !== '' || $name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    /* The country code arrives from its own <select>, so store the full
       E.164-style number rather than a bare local one. Skip the prefix if
       the visitor already typed one, to avoid "+91 +91 …". */
    $local = trim($post['mobile'] ?? ($post['phone'] ?? ''));
    $cc = trim($post['country_code'] ?? '');
    if ($local !== '' && $cc !== '' && !str_starts_with($local, '+')) {
        $local = $cc . ' ' . $local;
    }

    $entry = [
        'date' => date('c'),
        'source' => $source,
        'name' => $name,
        'email' => $email,
        'mobile' => $local,
        'company' => trim($post['company'] ?? ''),
        'designation' => trim($post['designation'] ?? ''),
        'service' => trim($post['service'] ?? ''),
        'budget' => trim($post['budget'] ?? ''),
        'message' => trim($post['message'] ?? ''),
    ];

    try {
        db()->prepare(
            'INSERT INTO enquiries (source, name, email, mobile, company, designation, service, budget, message)
             VALUES (:source, :name, :email, :mobile, :company, :designation, :service, :budget, :message)'
        )->execute([
            ':source' => $entry['source'], ':name' => $entry['name'], ':email' => $entry['email'],
            ':mobile' => $entry['mobile'], ':company' => $entry['company'], ':designation' => $entry['designation'],
            ':service' => $entry['service'], ':budget' => $entry['budget'], ':message' => $entry['message'],
        ]);
    } catch (Throwable $e) {
        error_log('save_enquiry: DB insert failed: ' . $e->getMessage());
    }

    mail_enquiry($entry);

    return true;
}

/**
 * Forward an enquiry to the sales inbox via send_email().
 *
 * From: is a domain address, never the visitor's, or SPF/DMARC on the receiving
 * side treats the message as spoofed and drops it. The visitor's address goes in
 * Reply-To so hitting reply still reaches them.
 */
function mail_enquiry(array $entry): bool
{
    $labels = [
        'name' => 'Name', 'email' => 'Email', 'mobile' => 'Phone / WhatsApp',
        'company' => 'Company', 'designation' => 'Designation', 'service' => 'Service needed',
        'budget' => 'Budget', 'message' => 'Message',
    ];

    $lines = ['New enquiry from the ' . SITE_NAME . ' website', str_repeat('=', 46), ''];
    foreach ($labels as $key => $label) {
        $val = trim((string) ($entry[$key] ?? ''));
        if ($val !== '') {
            $lines[] = str_pad($label . ':', 18) . $val;
        }
    }
    $lines[] = '';
    $lines[] = str_pad('Source:', 18) . ($entry['source'] ?? 'website');
    $lines[] = str_pad('Received:', 18) . date('d M Y, g:i a T', strtotime($entry['date'] ?? 'now'));
    $lines[] = '';
    $lines[] = 'Reply directly to this email to respond to the enquirer.';

    $subject = sprintf('New enquiry: %s%s', $entry['name'], $entry['service'] !== '' ? ' (' . $entry['service'] . ')' : '');

    return send_email(ENQUIRY_TO, $subject, implode("\n", $lines), [
        'reply_to' => $entry['name'] . ' <' . $entry['email'] . '>',
    ]);
}

/**
 * Single choke point for all outbound mail, via the Resend API. Every mail()
 * call in the codebase (enquiry notifications, audit reports, ebook delivery)
 * goes through this instead, because Vercel's runtime has no mail server for
 * PHP's mail() to hand off to.
 *
 * @param array{html?:string,reply_to?:string,attachment?:array{filename:string,content:string}} $opts
 *   attachment 'content' is raw bytes; this function base64-encodes it.
 */
function send_email(string $to, string $subject, string $body, array $opts = []): bool
{
    $apiKey = getenv('RESEND_API_KEY') ?: '';
    if ($apiKey === '') {
        error_log('send_email: RESEND_API_KEY is not set, dropping email to ' . $to);
        return false;
    }

    $host = parse_url(SITE_URL, PHP_URL_HOST) ?: 'vturnu.com';
    $payload = [
        'from' => SITE_NAME . ' <noreply@' . $host . '>',
        'to' => [$to],
        'subject' => $subject,
        'text' => $body,
    ];
    if (!empty($opts['html'])) {
        $payload['html'] = $opts['html'];
    }
    if (!empty($opts['reply_to'])) {
        $payload['reply_to'] = $opts['reply_to'];
    }
    if (!empty($opts['attachment'])) {
        $payload['attachments'] = [[
            'filename' => $opts['attachment']['filename'],
            'content' => base64_encode($opts['attachment']['content']),
        ]];
    }

    $ch = curl_init('https://api.resend.com/emails');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $apiKey, 'Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $res = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($res === false || $curlErr !== '') {
        error_log('send_email: curl error sending to ' . $to . ': ' . $curlErr);
        return false;
    }
    if ($httpCode < 200 || $httpCode >= 300) {
        error_log('send_email: Resend HTTP ' . $httpCode . ' for ' . $to . ': ' . substr((string) $res, 0, 300));
        return false;
    }
    return true;
}

/**
 * AEO answer box: a question-styled H2 + a concise, quotable direct answer
 * (the format answer engines and featured snippets prefer to cite).
 * Returns [question, answer].
 */
function answer_box(array $page): array
{
    if (!empty($page['answer'])) {
        return $page['answer'];
    }
    // "Search Engine Optimization Services" -> "Search Engine Optimization" for natural questions
    $name = preg_replace('/\s+Services$/i', '', $page['h1']);
    $lname = strtolower($name);
    $cat = $page['cat'] ?? 'seo';

    return match ($cat) {
        'ai' => [
            "What is $name?",
            "$name is the practice of making your brand visible and recommendable inside AI-powered search: ChatGPT, Perplexity, Gemini and Google AI Overviews. VTurnU combines entity optimization, structured data and citation-worthy content so AI engines understand, trust and recommend your business when buyers ask.",
        ],
        'ppc' => [
            "What does $name include?",
            "VTurnU's $lname covers strategy, campaign build, creative, conversion tracking and continuous optimization: managed by certified specialists and reported against CPA and ROAS, not vanity metrics. Most accounts see measurable efficiency gains within the first 4–6 weeks.",
        ],
        'web' => [
            "What makes VTurnU's $lname different?",
            "Every website VTurnU builds is engineered to score 90+ on Google PageSpeed, rank in search and convert visitors into enquiries. You get custom design (no bloated templates), SEO-ready architecture, mobile-first responsiveness and a fixed timeline agreed before we start.",
        ],
        'social' => [
            "How does $lname grow a business?",
            "$name works when it's built as a funnel, not a posting schedule. VTurnU pairs platform strategy and consistent creative with paid amplification and monthly measurement, so followers become leads, and leads become revenue you can attribute.",
        ],
        'content' => [
            "Why invest in $lname?",
            "$name compounds: every asset keeps attracting, educating and converting buyers long after it's published. VTurnU ties each piece to search demand and a funnel stage, so content produces qualified leads, not just traffic.",
        ],
        'consulting' => [
            "When do you need $lname?",
            "If you're spending on marketing but can't say which channel is profitable, you need $lname. VTurnU audits your funnel, fixes measurement and gives you a prioritized roadmap, typically finding 20–40% of budget that can work harder within the first month.",
        ],
        'seo-industry' => [
            "Why does " . ($page['industry'] ?? 'your industry') . " need specialist SEO?",
            "Generic SEO misses how buyers in " . ($page['industry'] ?? 'your industry') . " actually search. VTurnU builds industry-specific keyword strategy, compliant expert content and the trust signals your niche demands, so you outrank competitors who treat every industry the same.",
        ],
        default => [
            "What is $name?",
            "$name is the process of earning higher rankings and qualified organic traffic through technical excellence, intent-matched content and authority building. VTurnU delivers it as a measurable growth program: audited, prioritized and reported against leads and revenue, with early movement typically visible in 6–8 weeks.",
        ],
    };
}

/** Buying-intent bullets shown beside the answer box. */
function why_choose(array $page): array
{
    return $page['why'] ?? [
        'Free audit and fixed-price proposal before you commit',
        'Senior specialists on your account: no junior handoffs',
        'Reporting tied to leads and revenue, updated live',
        'Month-to-month engagement: we earn renewal with results',
    ];
}

/**
 * Category-specific lead magnet for CTAs.
 * Returns [headline, supporting line, button label].
 */
function cta_offer(array $page): array
{
    if (!empty($page['offer'])) {
        return $page['offer'];
    }
    $cat = $page['cat'] ?? ($page['template'] ?? '') ;

    return match ($cat) {
        'ai' => ['Get a Free AI Visibility Report', 'See exactly how ChatGPT, Perplexity and Google AI Overviews present your brand today, and what to fix first.', 'Get My Free AI Report'],
        'ppc' => ['Get a Free Ad Account Audit', 'We\'ll find the wasted spend in your account and show you the fix: in one 30-minute call, free.', 'Audit My Ad Account'],
        'web' => ['Get a Free Website Review', 'Speed, SEO and conversion, we\'ll review your current site and send a prioritized fix list within 48 hours.', 'Review My Website'],
        'social' => ['Get a Free Social Media Audit', 'A frank review of your profiles, content and funnel, with three moves that would grow you fastest.', 'Audit My Social Media'],
        'content' => ['Get a Free Content Strategy Session', 'Bring your goals; leave with a topic roadmap you can execute, with or without us.', 'Book My Free Session'],
        'consulting' => ['Book a Free Strategy Call', '30 minutes with a senior strategist. Real recommendations, zero sales script.', 'Book My Free Call'],
        'seo-industry' => ['Get a Free Industry SEO Audit', 'See exactly where you rank against your top three competitors, and the gaps you can win.', 'Get My Free Audit'],
        default => ['Get a Free SEO Audit', 'A 47-point audit of your site with a prioritized action plan: yours free, whether we work together or not.', 'Get My Free SEO Audit'],
    };
}

/** Trust badge row, guarantees that remove purchase anxiety. */
function render_trust_badges(): void
{
    $badges = [
        ['M12 2 4 5v6c0 5 3.4 9.4 8 11 4.6-1.6 8-6 8-11V5l-8-3Zm-1 13-3-3 1.4-1.4L11 12.2l4.6-4.6L17 9l-6 6Z', 'No lock-in contracts'],
        ['M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-3.3 0-8 1.7-8 5v3h16v-3c0-3.3-4.7-5-8-5Z', 'Senior specialists only'],
        ['M4 20V10h3v10H4Zm6.5 0V4h3v16h-3ZM17 20v-7h3v7h-3Z', 'Revenue-first reporting'],
        ['M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2Zm4.2 14.2L11 13V7h1.5v5.3l4.5 2.7-.8 1.2Z', '24-hour reply guarantee'],
    ];
    echo '<ul class="badge-row" aria-label="Our guarantees">';
    foreach ($badges as $i => [$path, $label]) {
        echo '<li class="badge badge-' . ($i + 1) . '"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="' . $path . '"/></svg>' . e($label) . '</li>';
    }
    echo '</ul>';
}

/** "Typical agency vs VTurnU" comparison, the strongest differentiator block. */
function render_compare(): void
{
    ?>
<section class="section compare-band" aria-label="How VTurnU compares to a typical agency">
    <div class="container">
        <div class="center-text" data-reveal="head">
            <p class="eyebrow">The difference</p>
            <h2 class="section-title">Any agency can promise. <span class="grad-text">Here's what actually changes.</span></h2>
        </div>
        <div class="compare-grid">
            <div class="compare-col compare-them" data-reveal="left">
                <h3>A typical agency</h3>
                <ul>
                    <li>Junior executives run your account after the sales call</li>
                    <li>Reports full of impressions, reach and vanity metrics</li>
                    <li>The same recycled strategy for every client, every industry</li>
                    <li>12-month lock-ins signed before any proof of results</li>
                    <li>Days of silence when you ask a hard question</li>
                </ul>
            </div>
            <div class="compare-col compare-us" data-reveal="right">
                <span class="compare-tag">The VTurnU way</span>
                <h3>Working with VTurnU</h3>
                <ul>
                    <li>Senior specialists on your account: nothing outsourced</li>
                    <li>Reporting that talks revenue, leads and cost per acquisition</li>
                    <li>Strategy built from your market, competitors and margins</li>
                    <li>Month-to-month engagements: we re-earn your business</li>
                    <li>A guaranteed reply within one business day, every time</li>
                </ul>
                <a class="btn btn-grad" href="/contact-us/">Work the VTurnU way →</a>
            </div>
        </div>
    </div>
</section>
    <?php
}

/**
 * VTurnAI upsell block.
 *
 * The free audit answers "do I have a problem" once. This is the offer to
 * answer "is it fixed yet" every month, and it is placed where that question
 * is most alive: directly under a finished report. $campaign only tags the
 * outbound link so product analytics can tell the placements apart.
 */
function render_product_upsell(string $campaign, ?string $heading = null, ?string $intro = null): void
{
    $heading ??= 'This was one page, once. What about every engine, every month?';
    $intro   ??= 'The check above reads the HTML your homepage serves today. ' . PRODUCT_NAME
        . ', our own platform, keeps watching: it crawls your whole site and tracks what ChatGPT, Gemini, '
        . 'Claude, Perplexity, Grok and Copilot actually say about you when a customer asks.';
    ?>
<div class="product-upsell" data-reveal="card">
    <p class="eyebrow">Go further with <?= e(PRODUCT_NAME) ?></p>
    <h3><?= e($heading) ?></h3>
    <p><?= e($intro) ?></p>
    <ul class="check-list">
        <li>Brand mentions and citations tracked across 8 engines</li>
        <li>25 buying-intent prompts watched, with competitor share of voice</li>
        <li>One V Score across SEO, AEO, GEO and HEO, trended month over month</li>
        <li>A fix list ordered by visibility impact, not by what is easy to detect</li>
    </ul>
    <div class="product-upsell-actions">
        <a class="btn btn-grad" href="<?= e(product_url($campaign)) ?>" target="_blank" rel="noopener">Start <?= e(PRODUCT_TRIAL_DAYS) ?>-Day Free Trial</a>
        <a class="cta-phone" href="<?= e(PRODUCT_PAGE) ?>">or see what it tracks →</a>
    </div>
</div>
    <?php
}

/** "What happens when you enquire", removes the fear of hitting send. */
function render_next_steps(): void
{
    ?>
<section class="section next-steps" aria-label="What happens after you enquire">
    <div class="container">
        <div class="center-text" data-reveal="head">
            <p class="eyebrow">Zero-risk start</p>
            <h2 class="section-title">What happens when you hit <span class="grad-text">"send"</span></h2>
        </div>
        <div class="steps-line">
            <?php
            $steps = [
                ['Within 24 hours', 'A senior strategist replies', 'No bots, no sales reps, a specialist personally reviews your website and goals before writing back.'],
                ['Within 72 hours', 'You get a free mini-audit', 'Concrete gaps and quick wins you can act on immediately, whether or not you hire us.'],
                ['Only if it fits', 'A roadmap and honest quote', "If we can't move the needle for you, we'll say so, and point you toward what will."],
            ];
            foreach ($steps as [$time, $title, $desc]): ?>
            <div class="step-card" data-reveal="card">
                <span class="step-time"><?= e($time) ?></span>
                <h3><?= e($title) ?></h3>
                <p><?= e($desc) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        <?php render_trust_badges(); ?>
        <p class="center next-steps-cta" data-reveal="cta">
            <a class="btn btn-grad" href="/free-seo-audit/">Start With a Free Audit</a><br>
            <small>No pressure. No obligation. You keep the audit either way.</small>
        </p>
    </div>
</section>
    <?php
}

/**
 * Pick up to $count entries from $pool, walking forward from $current and
 * wrapping around the end.
 *
 * Taking the first N matches instead (the obvious approach) makes every page
 * link to the same handful of early entries, so everything further down the
 * array ends up with no inbound internal links at all. Starting from the
 * current item's own position spreads the links evenly: over the whole set,
 * each entry is linked from roughly $count others.
 *
 * $filter narrows the preferred pool (same category, same resource type).
 * When it cannot fill $count, a second pass ignores it rather than render a
 * half-empty module.
 *
 * @param array<string,mixed> $pool  ordered slug => item
 * @param callable|null       $filter fn(mixed $item): bool
 * @return array<string,mixed>
 */
function related_ring(array $pool, string $current, int $count = 3, ?callable $filter = null): array
{
    $keys = array_keys($pool);
    $n = count($keys);
    if ($n <= 1) {
        return [];
    }
    $pos = array_search($current, $keys, true);
    $start = $pos === false ? -1 : $pos;

    $out = [];
    foreach ([$filter, null] as $pass) {
        for ($i = 1; $i < $n && count($out) < $count; $i++) {
            $k = $keys[($start + $i) % $n];
            if ($k === $current || isset($out[$k])) {
                continue;
            }
            if ($pass !== null && !$pass($pool[$k])) {
                continue;
            }
            $out[$k] = $pool[$k];
        }
        if (count($out) >= $count) {
            break;
        }
    }
    return $out;
}

/**
 * Up to 4 related service links, ringed over true siblings (same parent).
 *
 * Ringing over the parent's curated `children` list instead makes the relation
 * asymmetric: a hub lists children whose own `parent` points somewhere deeper,
 * those pages never ring over the hub's list, and entries at the head of it
 * (seo-services under /seo/) end up with no inbound sibling links. Sibling sets
 * are closed circles, so every page both gives and receives.
 */
function related_pages(array $pages, string $slug): array
{
    $parent = $pages[$slug]['parent'] ?? null;

    $siblings = [];
    foreach ($pages as $s => $p) {
        if (($p['parent'] ?? null) === $parent && ($p['template'] ?? 'service') === 'service') {
            $siblings[$s] = $p;
        }
    }
    return related_ring($siblings, $slug, 4);
}

/** Demo case-study cards per list type. */
function case_cards(string $type): array
{
    $sets = [
        'seo' => [
            ['SaaS platform', '+312% organic traffic in 9 months', 'A technical SEO overhaul and topic-cluster content engine took a B2B SaaS from page 3 to owning its category terms.', ['312%', 'Organic traffic growth'], ['48', 'Keywords in top 3']],
            ['Fashion retailer', '2.4× organic revenue year over year', 'Category-page strategy and faceted-navigation fixes unlocked rankings the store had been sitting on for years.', ['2.4×', 'Organic revenue'], ['-38%', 'Reliance on paid traffic']],
            ['Dental group', '#1 in the map pack across 12 locations', 'Location page system, review velocity program and local links put every clinic at the top of its neighborhood.', ['12/12', 'Locations ranking top 3'], ['+167%', 'Appointment requests']],
        ],
        'ppc' => [
            ['D2C brand', 'ROAS from 1.8 to 4.6 in one quarter', 'Feed restructuring, creative testing and budget reallocation turned a break-even account into a growth engine.', ['4.6×', 'ROAS'], ['-52%', 'Cost per acquisition']],
            ['B2B services firm', '3× demo bookings at the same spend', 'Intent-tiered search campaigns and rebuilt landing pages tripled conversions without a budget increase.', ['3×', 'Demo bookings'], ['+89%', 'Conversion rate']],
            ['EdTech platform', '41% cheaper enrollments at 2× scale', 'Full-funnel Meta + Google structure let the brand double spend while CPAs fell.', ['-41%', 'Cost per enrollment'], ['2×', 'Monthly ad spend scaled']],
        ],
        'social' => [
            ['Wellness brand', '0 to 120K engaged followers in a year', 'A reels-first content system plus creator collabs built an audience that now drives 30% of revenue.', ['120K', 'Followers gained'], ['30%', 'Revenue from social']],
            ['Real estate developer', '4.2M reach for a single launch', 'A coordinated launch campaign across Instagram and Facebook sold out phase one in six weeks.', ['4.2M', 'Campaign reach'], ['6 wks', 'Phase one sold out']],
            ['B2B manufacturer', 'LinkedIn pipeline worth ₹3.8Cr', 'Executive thought-leadership and targeted campaigns turned a dormant page into a lead source.', ['₹3.8Cr', 'Pipeline generated'], ['+540%', 'Engagement rate']],
        ],
        'content' => [
            ['Fintech startup', '210 keywords ranked with 24 articles', 'A tightly clustered content strategy earned rankings, links and a steady flow of qualified signups.', ['210', 'Ranking keywords'], ['+178%', 'Organic signups']],
            ['Healthcare provider', 'Authority content that doubled bookings', 'Medically reviewed articles and treatment guides built trust, and doubled appointment requests.', ['2×', 'Appointment requests'], ['96', 'Featured snippets won']],
            ['Logistics company', 'From zero blog to 60K monthly readers', 'An editorial engine focused on operator questions made the brand the industry\'s reference point.', ['60K', 'Monthly readers'], ['+130%', 'Inbound enquiries']],
        ],
        'design' => [
            ['SaaS rebrand', 'Conversion rate up 74% post-redesign', 'A repositioned brand and rebuilt marketing site turned the same traffic into far more demos.', ['+74%', 'Conversion rate'], ['-1.9s', 'Load time improvement']],
            ['Retail chain', 'A brand system that unified 40 stores', 'New identity, guidelines and web presence brought consistency, and measurable recall, across regions.', ['40', 'Stores unified'], ['+22%', 'Brand recall']],
            ['Manufacturer', 'RFQs doubled after UX overhaul', 'Restructured product architecture and spec-first pages made it easy for engineers to buy.', ['2×', 'RFQ submissions'], ['-35%', 'Bounce rate']],
        ],
        'saas' => [
            ['DevTools startup', 'From 800 to 14K organic visits/month', 'Product-led SEO and comparison content captured buyers at the moment of evaluation.', ['17×', 'Organic traffic'], ['+320%', 'Free trial starts']],
            ['HR platform', 'CAC down 44% by rebalancing channels', 'Consulting engagement that shifted budget from saturated paid channels into content and lifecycle email.', ['-44%', 'Customer acquisition cost'], ['+61%', 'MQL-to-SQL rate']],
        ],
        'ecom' => [
            ['Jewelry store', 'Organic revenue up 190% in 8 months', 'Buying-guide content and product schema turned informational traffic into transactions.', ['+190%', 'Organic revenue'], ['+85%', 'Assisted conversions']],
            ['Beauty brand', 'Marketplace-beating rankings', 'Ingredient-led SEO outranked Amazon and Nykaa for the brand\'s hero categories.', ['Top 3', 'For 60+ category terms'], ['3.1×', 'ROAS on paid retargeting']],
        ],
        'health' => [
            ['Multi-specialty clinic', '+167% appointment requests', 'Local SEO, treatment pages and a review program made the clinic the obvious local choice.', ['+167%', 'Appointments'], ['4.8★', 'Average rating maintained']],
            ['Wellness D2C', 'Compliant content that ranks', 'E-E-A-T-first editorial and expert review workflows won YMYL rankings competitors couldn\'t hold.', ['+240%', 'Organic traffic'], ['0', 'Compliance issues']],
        ],
        'realestate' => [
            ['Property developer', '2,300 qualified leads in 6 months', 'Hyperlocal SEO and paid campaigns for three launches filled the CRM with verified buyers.', ['2,300', 'Qualified leads'], ['-33%', 'Cost per lead']],
            ['Brokerage', 'Map-pack dominance in 8 suburbs', 'Location landing pages and review velocity put the brand first for every neighborhood search.', ['8/8', 'Suburbs ranked #1'], ['+92%', 'Call volume']],
        ],
        'education' => [
            ['University', 'Enrollment enquiries up 145%', 'Program-page SEO and scholarship content captured students at every research stage.', ['+145%', 'Enquiries'], ['52', 'Programs ranking top 5']],
            ['Training institute', 'Paid CPL cut by half', 'Restructured Google Ads and localized landing pages halved cost per lead in one term.', ['-50%', 'Cost per lead'], ['+2.1×', 'Enrollment rate']],
        ],
        'manufacturing' => [
            ['Industrial equipment maker', 'RFQs from organic up 210%', 'Spec-first product pages and technical content reached engineers mid-research.', ['+210%', 'RFQ volume'], ['1,400', 'Product pages optimized']],
            ['Components exporter', 'Global visibility in 6 markets', 'Multilingual SEO and localized content opened inbound pipelines across three continents.', ['6', 'Markets ranking'], ['+75%', 'Export enquiries']],
        ],
        'edutech' => [
            ['Learning app', 'Organic installs up 4× in a year', 'App-store-aligned SEO content and tutorial hubs became the platform\'s cheapest acquisition channel.', ['4×', 'Organic installs'], ['-58%', 'Blended CAC']],
            ['Upskilling platform', 'Course pages that convert', 'Comparison and syllabus content plus CRO lifted paid-course conversions dramatically.', ['+96%', 'Course enrollments'], ['38%', 'Traffic from AI answers']],
        ],
    ];
    return $sets[$type] ?? $sets['seo'];
}
