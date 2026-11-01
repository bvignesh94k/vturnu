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

/**
 * Entity facts shared by both Organization nodes (this one and the homepage's
 * richer graph in templates/home.php), so the two can never drift apart.
 * sameAs joins the social accounts and the directory profiles: that list is
 * what lets search and AI engines treat every listing as the same business.
 */
function org_entity_facts(): array
{
    return [
        'legalName' => LEGAL_NAME,
        'alternateName' => 'VTurnU Digital Solutions',
        'foundingDate' => FOUNDING_YEAR,
        'founder' => ['@type' => 'Person', 'name' => FOUNDER_NAME, 'jobTitle' => FOUNDER_TITLE],
        'numberOfEmployees' => ['@type' => 'QuantitativeValue', 'minValue' => 10, 'maxValue' => 49],
        'knowsAbout' => ['Search Engine Optimization', 'Answer Engine Optimization', 'Generative Engine Optimization', 'Google Ads', 'Meta Ads', 'Content Marketing', 'Social Media Marketing', 'Web Development'],
        'sameAs' => array_values(array_unique(array_merge(array_values(SOCIAL_LINKS), ENTITY_PROFILES))),
    ];
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
                'areaServed' => array_map(static fn($c) => ['@type' => 'Country', 'name' => $c], TARGET_MARKETS),
                'contactPoint' => [[
                    '@type' => 'ContactPoint',
                    'contactType' => 'sales',
                    'email' => CONTACT_EMAIL,
                    'telephone' => CONTACT_PHONE,
                    'availableLanguage' => ['English', 'Tamil', 'Hindi'],
                ]],
            ] + org_entity_facts(),
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
        'areaServed' => array_map(static fn($c) => ['@type' => 'Country', 'name' => $c], TARGET_MARKETS),
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

/**
 * FAQs for a service page. Each category answers the questions buyers of that
 * service really ask, so sixty pages do not repeat one generic list. Industry
 * pages add the industry to the question, which is how people search for it.
 */
function service_faqs(array $page): array
{
    if (!empty($page['faqs'])) {
        return $page['faqs'];
    }
    $h1 = preg_replace('/\s+Services$/i', '', $page['h1']);
    /* Lower case reads naturally in a question, except for names that are always capitalised. */
    $name = strtr(strtolower($h1), [
        'seo' => 'SEO', 'ai ' => 'AI ', 'ppc' => 'PPC', 'chatgpt' => 'ChatGPT', 'google' => 'Google', 'perplexity' => 'Perplexity',
        'facebook' => 'Facebook', 'instagram' => 'Instagram', 'wordpress' => 'WordPress', 'shopify' => 'Shopify',
        'magento' => 'Magento', 'woocommerce' => 'WooCommerce', 'bigcommerce' => 'BigCommerce',
    ]);
    $cat = $page['cat'] ?? 'seo';
    $ind = $page['industry'] ?? '';
    $min = min_budget();

    $sets = [
        'seo' => [
            ["How much does {$name} cost?", "Engagements start from {$min}. Larger or more competitive programs are quoted after a free audit, because the work depends on your market and starting point."],
            ["How long does {$name} take to work?", 'You will usually see early movement within six to eight weeks, and results compound from month three onward. Very competitive terms can take longer.'],
            ['What do you do in the first month?', 'We audit the site, fix tracking, map keywords to buying intent, and start on the technical issues that hold everything else back.'],
            ['Do you guarantee a number one ranking?', 'No, and nobody honest can, because no one controls Google. We commit to the work, clear reporting and a plan based on evidence.'],
            ['Does this also help with AI search?', 'Yes. AI engines read the same web, so clear structure, schema and trusted content help you in both places.'],
            ['How do you report progress?', 'A monthly report ties rankings and traffic to enquiries and revenue, followed by a review call.'],
        ],
        'seo-industry' => [
            ["Why does {$ind} need specialist SEO?", "Buyers in {$ind} search in their own way and need specific reassurance. Generic SEO misses that language and the proof they look for."],
            ["How long does SEO take for {$ind}?", 'Most businesses see movement within six to eight weeks. Competitive markets take longer, and we forecast this after an audit.'],
            ['What makes content rank in this field?', 'Real expertise. Named experts, accurate claims, specific examples and pages that answer buyers\' real questions.'],
            ['Do you work with businesses of my size?', "Yes, from single-location businesses to larger brands. Engagements start from {$min}."],
            ['How do you measure success?', 'In enquiries and revenue, with rankings and traffic as supporting signals.'],
        ],
        'ai' => [
            ["What is {$name}?", 'It is the work of making your brand visible and recommended inside AI-generated answers, through clear content, structured data, consistent entity facts and mentions on trusted sources.'],
            ['Can you guarantee AI engines will mention us?', 'No one can, because AI answers vary. We improve the signals engines rely on, and we track how often you are named across a set of buyer prompts.'],
            ['How do you measure results?', 'We sample buyer prompts across engines each month and record whether you are named, along with citation data from Bing Webmaster Tools where available.'],
            ['Do we still need regular SEO?', 'Yes. AI engines draw on the same web, and sites that are crawlable and well structured have a head start.'],
            ['How long does it take?', 'Technical and entity fixes can show within weeks. Earning corroboration on independent sites usually takes a few months.'],
            ['Is my industry a good fit?', 'If your buyers research before they buy, yes. B2B services, software and considered purchases benefit the most.'],
        ],
        'ppc' => [
            ["How much does {$name} cost?", "Management starts from {$min}, and ad spend is paid directly to the platform. We recommend a budget after looking at your market and target cost per lead."],
            ['How soon will campaigns produce results?', 'Campaigns collect data immediately. Expect meaningful optimization in the first four to six weeks.'],
            ['Who owns the ad account?', 'You do. Accounts stay in your name, so you keep all data and history if we ever part ways.'],
            ['What do you track?', 'Real conversions such as qualified leads, calls and sales, set up before we spend your budget.'],
            ['How do you stop wasted spend?', 'Tight targeting, negative keywords, regular search term reviews and landing pages built for each campaign.'],
            ['What is a good cost per lead?', 'It depends on what a customer is worth to you. We calculate an affordable target from your margins first.'],
        ],
        'social' => [
            ["How much does {$name} cost?", "Engagements start from {$min}. We confirm the scope after a free review of your profiles."],
            ['Which platforms should we use?', 'The ones your buyers use. LinkedIn usually leads for B2B, and Instagram and Facebook for consumer brands.'],
            ['Do you create the content?', 'Yes. We plan, design and write posts, and we use your real people and work wherever we can.'],
            ['How do you measure social media?', 'Against leads, sales and reach among your target audience, not likes alone.'],
            ['Should we run paid social too?', 'Usually yes. Organic content shows what works, and paid puts it in front of more of the right people.'],
        ],
        'content' => [
            ["How much does {$name} cost?", "Engagements start from {$min}. Larger programs are quoted after a free strategy session."],
            ['Who writes the content?', 'Specialist writers who interview your experts, with an editor reviewing every piece. AI may help with research, but people write, check and approve.'],
            ['How long until content brings results?', 'Usually three to six months, and well-chosen content keeps working for years.'],
            ['Will you match our brand voice?', 'Yes. We document your tone first, and you approve early pieces before we scale.'],
            ['How do you measure content performance?', 'By the rankings, leads and pipeline it influences, not pageviews alone.'],
        ],
        'web' => [
            ["How much does {$name} cost?", "Projects start from {$min}. Custom builds are quoted after a scoping call, and design and development are billed once."],
            ['How long does a website project take?', 'A business website takes around three to four weeks and an online store five to eight, once content is ready.'],
            ['Will the site be fast?', 'We target 90+ on PageSpeed and test Core Web Vitals on real pages before launch.'],
            ['Will I be able to edit it myself?', 'Yes. We build on a platform that suits your team and show you how to manage it.'],
            ['Can you migrate my existing site without losing rankings?', 'Yes. We map old URLs, set redirects and watch Search Console after launch.'],
            ['Who owns the website?', 'You do, including the code, content and design.'],
        ],
        'consulting' => [
            ['What does a consulting engagement include?', 'An evidence-based audit, a prioritized roadmap, measurement setup and regular reviews with your team.'],
            ['Do you do the work, or only advise?', 'Either. Many clients want a roadmap for their own team, while others ask us to deliver it too.'],
            ['How long does an audit take?', 'Usually two to three weeks, depending on how many channels and accounts are involved.'],
            ['What access do you need?', 'Read access to analytics, ad accounts and Search Console, plus a conversation with whoever owns sales.'],
        ],
    ];

    return $sets[$cat] ?? $sets['seo'];
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
/**
 * Cache-busting version for a static asset: a short hash of its contents.
 *
 * filemtime() cannot be used: on Vercel every deployed file carries the same
 * fixed timestamp, so ?v= never changed between deploys, and browsers holding
 * the year-long immutable copy of the CSS and JS kept serving stale files.
 */
function asset_v(string $path): string
{
    static $memo = [];
    if (!isset($memo[$path])) {
        $hash = @md5_file(BASE_PATH . $path);
        $memo[$path] = $hash ? substr($hash, 0, 10) : (string) time();
    }
    return $memo[$path];
}

/** Free webmail domains. A B2B enquiry from one of these is flagged, not refused. */
const FREE_EMAIL_DOMAINS = [
    'gmail.com', 'googlemail.com', 'yahoo.com', 'yahoo.co.in', 'yahoo.co.uk', 'ymail.com',
    'hotmail.com', 'outlook.com', 'live.com', 'msn.com', 'icloud.com', 'me.com', 'mac.com',
    'aol.com', 'proton.me', 'protonmail.com', 'zoho.com', 'zohomail.in', 'gmx.com', 'mail.com',
    'yandex.com', 'rediffmail.com', 'qq.com', '163.com',
];

/**
 * Fold the homepage B2B qualifier into the fields the enquiries table already
 * has, and grade the lead so sales can see at a glance who to call first.
 *
 * The table has no columns for markets, size, timeline or website, and a
 * schema change is not worth it for a summary a person reads, so those go to
 * the top of the message. The grade weighs budget, company size, timeline,
 * a company website and a business (non-webmail) email.
 */
function enquiry_qualify(array $post): array
{
    $list = static function ($v): string {
        $items = array_filter(array_map('trim', is_array($v) ? $v : [(string) $v]), 'strlen');
        return implode(', ', array_slice($items, 0, 12));
    };
    $services = $list($post['services'] ?? ($post['service'] ?? ''));
    $markets  = $list($post['markets'] ?? '');
    $site     = trim((string) ($post['company_website'] ?? ''));
    $hq       = trim((string) ($post['hq_country'] ?? ''));
    $size     = trim((string) ($post['company_size'] ?? ''));
    $budget   = trim((string) ($post['budget'] ?? ''));
    $timeline = trim((string) ($post['timeline'] ?? ''));
    $email    = strtolower(trim((string) ($post['email'] ?? '')));
    $domain   = substr((string) strrchr($email, '@'), 1);
    $business = $domain !== '' && !in_array($domain, FREE_EMAIL_DOMAINS, true);

    $points = 0;
    /* Budget arrives as a band key (b0 to b4) so the grade means the same in
       every currency; see BUDGET_BANDS in includes/currency.php. */
    $points += ['b0' => 0, 'b1' => 2, 'b2' => 3, 'b3' => 4, 'b4' => 4, 'Not decided yet' => 1][$budget] ?? 0;
    $budget = budget_label($budget);
    $points += ['1 to 10 people' => 0, '11 to 50 people' => 1, '51 to 200 people' => 2,
                '201 to 1,000 people' => 2, '1,000+ people' => 2][$size] ?? 0;
    $points += ['As soon as possible' => 2, 'Within 1 to 3 months' => 2, 'In 3 to 6 months' => 1, 'Just researching' => 0][$timeline] ?? 0;
    $points += $business ? 1 : 0;
    $points += preg_match('/^(https?:\/\/)?[a-z0-9-]+(\.[a-z0-9-]+)+/i', $site) ? 1 : 0;
    $grade = $points >= 7 ? 'A' : ($points >= 4 ? 'B' : 'C');

    $summary = [
        'Lead grade: ' . $grade . ' (' . $points . '/10)',
        'Services: ' . ($services !== '' ? $services : 'Not specified'),
        'Source page: ' . (trim((string) ($post['from_page'] ?? '')) ?: 'Not recorded'),
        'Visitor country: ' . (security_visitor_country() ?: 'Unknown')
            . (($pi = strtoupper(trim((string) ($post['country_iso'] ?? '')))) !== '' && $pi !== security_visitor_country() ? ' (phone country: ' . $pi . ')' : ''),
        ...($markets !== '' ? ['Target markets: ' . $markets] : []),
        'Website: ' . ($site !== '' ? $site : 'Not given'),
        'Headquarters: ' . ($hq !== '' ? $hq : 'Not given'),
        'Company size: ' . ($size !== '' ? $size : 'Not given'),
        'Budget: ' . ($budget !== '' ? $budget : 'Not given'),
        'Timeline: ' . ($timeline !== '' ? $timeline : 'Not given'),
        'Email: ' . ($business ? 'Business domain' : 'Free webmail, verify fit'),
    ];
    $note = trim((string) ($post['message'] ?? ''));
    $post['raw_message'] = $note;

    $post['service'] = $services !== '' ? $services : trim((string) ($post['service'] ?? ''));
    $post['company'] = trim((string) ($post['company'] ?? '')) . ($site !== '' ? ' (' . $site . ')' : '');
    $post['message'] = implode("\n", $summary) . ($note !== '' ? "\n\n" . $note : '');
    $post['budget'] = $budget;
    return $post;
}

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
        'service' => html_entity_decode(trim($post['service'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
        'budget' => budget_label(trim((string) ($post['budget'] ?? ''))),
        'message' => trim($post['message'] ?? ''),
    ];

    /* Quality screen. Everything is stored; only what passes reaches the sales
       inbox. Junk keeps status 'junk' so the admin Junk view can restore it. */
    $screen = lead_screen($entry + ['raw_message' => $post['raw_message'] ?? null], $source, true, [
        'hx' => (string) ($post['_hx'] ?? ''),
        'captcha' => $GLOBALS['recaptcha_signal'] ?? null,
    ]);
    $status = $screen['verdict'] === 'junk' ? 'junk' : 'new';
    $tags = $screen['verdict'] === 'ok' ? '' : ($screen['verdict'] === 'junk' ? 'auto-junk' : 'check-first');
    $activity = json_encode([[
        'date' => date('Y-m-d H:i'),
        'text' => 'Quality check: ' . $screen['verdict'] . ' (' . $screen['score'] . ')'
            . ($screen['reasons'] ? ': ' . implode('; ', $screen['reasons']) : ''),
    ]], JSON_UNESCAPED_UNICODE);

    $stored = false;
    try {
        db()->prepare(
            'INSERT INTO enquiries (source, name, email, mobile, company, designation, service, budget, message, status, tags, activity)
             VALUES (:source, :name, :email, :mobile, :company, :designation, :service, :budget, :message, :status, :tags, CAST(:activity AS jsonb))'
        )->execute([
            ':source' => $entry['source'], ':name' => $entry['name'], ':email' => $entry['email'],
            ':mobile' => $entry['mobile'], ':company' => $entry['company'], ':designation' => $entry['designation'],
            ':service' => $entry['service'], ':budget' => $entry['budget'], ':message' => $entry['message'],
            ':status' => $status, ':tags' => $tags, ':activity' => $activity,
        ]);
        $stored = true;
    } catch (Throwable $e) {
        error_log('save_enquiry: DB insert failed: ' . $e->getMessage());
    }

    /* A junk verdict skips the inbox, unless the database failed too: then the
       email is the only copy, and a false positive must not vanish. */
    if ($screen['verdict'] === 'junk' && $stored) {
        return true;
    }
    $entry['quality'] = $screen;
    $entry['not_stored'] = !$stored;
    $emailed = mail_enquiry($entry);

    /* A lead survives if it reached the database or the sales inbox. If
       neither worked, say so, because the alternative is what happened
       before: the visitor is thanked, the enquiry is gone, and nobody finds
       out until someone wonders why the forms stopped producing business.
       Returning false surfaces the error and invites them to call instead. */
    if (!$stored && !$emailed) {
        error_log('save_enquiry: LEAD LOST, neither database nor email accepted it: ' . $entry['email']);
        return false;
    }
    if (!$stored) {
        error_log('save_enquiry: DB unavailable, lead delivered by email only: ' . $entry['email']);
    }

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

    $quality = $entry['quality'] ?? ['verdict' => 'ok', 'reasons' => []];
    $lines = ['New enquiry from the ' . SITE_NAME . ' website', str_repeat('=', 46), ''];
    if (!empty($entry['not_stored'])) {
        $lines[] = 'NOT SAVED TO THE ADMIN PANEL: the database was unavailable. This email is the only copy.';
        $lines[] = '';
    }
    if ($quality['verdict'] !== 'ok') {
        $lines[] = ($quality['verdict'] === 'junk' ? 'LIKELY JUNK' : 'CHECK BEFORE REPLYING') . ': ' . implode('; ', $quality['reasons']);
        $lines[] = '';
    }
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

    $prefix = ['ok' => '', 'review' => '[Check first] ', 'junk' => '[Likely junk] '][$quality['verdict']] ?? '';
    $grade = preg_match('/Lead grade: ([ABC])/', (string) ($entry['message'] ?? ''), $gm) ? ' [Grade ' . $gm[1] . ']' : '';
    $subject = $prefix . sprintf('New enquiry: %s%s', $entry['name'], $entry['service'] !== '' ? ' (' . $entry['service'] . ')' : '');
    $subject .= $grade;

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
    // env_var() rather than getenv(): see the note in includes/config.php.
    $apiKey = env_var('RESEND_API_KEY', '');
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
    // No curl_close(): it has done nothing since PHP 8.0 and is deprecated in
    // 8.5, which is the version Vercel runs. The handle frees itself.

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
            "VTurnU's $lname covers strategy, campaign build, creative, conversion tracking and continuous optimization, reported against cost per qualified lead rather than clicks.",
        ],
        'web' => [
            "What makes VTurnU's $lname different?",
            "Every website VTurnU builds is engineered for speed, search and enquiries. You get custom design (no bloated templates), SEO-ready architecture, mobile-first responsiveness and a fixed timeline agreed before we start.",
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
            "If you're spending on marketing but can't say which channel is profitable, you need $lname. VTurnU audits your funnel, fixes measurement and gives you a prioritized roadmap.",
        ],
        'seo-industry' => [
            "Why does " . ($page['industry'] ?? 'your industry') . " need specialist SEO?",
            "Generic SEO misses how buyers in " . ($page['industry'] ?? 'your industry') . " actually search. VTurnU builds industry-specific keyword strategy, compliant expert content and the trust signals your niche demands, so you outrank competitors who treat every industry the same.",
        ],
        default => [
            "What is $name?",
            "$name is the process of earning higher rankings and qualified organic traffic through technical excellence, intent-matched content and authority building. VTurnU delivers it as a measurable growth program: audited, prioritized and reported against leads and revenue, with early movement typically visible in 6 to 8 weeks.",
        ],
    };
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
