<?php
/**
 * VTurnU, front controller. Runs here (under api/) because Vercel only
 * treats files inside api/ as executable functions, a file with this exact
 * name at the project root gets served as a plain static file instead
 * (that was the bug: vturnu.com downloading raw PHP source). The project
 * root's index.php is now just a one-line proxy to this file, kept only for
 * local XAMPP dev and the VPS deploy, and is excluded from the Vercel
 * deployment entirely (see .vercelignore) so there's nothing at the root
 * for Vercel to serve statically.
 *
 * Clean URLs: /seo-services/, /case-studies/healthcare/, /sitemap.xml …
 */

declare(strict_types=1);

if (!ob_start('ob_gzhandler')) {
    ob_start();
}

require dirname(__DIR__) . '/includes/config.php';
require dirname(__DIR__) . '/includes/db.php';
require dirname(__DIR__) . '/includes/helpers.php';
require dirname(__DIR__) . '/includes/security.php';
require dirname(__DIR__) . '/includes/currency.php';
require dirname(__DIR__) . '/includes/lead-guard.php';
require dirname(__DIR__) . '/includes/crm.php';
require dirname(__DIR__) . '/includes/page-kit.php';
require dirname(__DIR__) . '/includes/lead-form.php';
require dirname(__DIR__) . '/includes/hero-kit.php';
require dirname(__DIR__) . '/includes/money-kit.php';
require dirname(__DIR__) . '/includes/trust-kit.php';
require dirname(__DIR__) . '/includes/listicle-kit.php';

/* parse_url() returns false, not null, on a malformed URI, so ?? never caught
   it and trim(false) was a fatal under strict_types: a request to "//" or any
   junk path returned a 500 to the visitor and to crawlers instead of a 404. */
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$slug = trim(is_string($uri) ? $uri : '/', '/');

/* Admin edits, served as one CDN-cached JSON document so public pages never
   open a database connection themselves. See content_overrides_all() in
   includes/data/pages.php for why. Handled before pages.php loads, because
   pages.php is what reads it. */
if ($slug === 'content-overrides.json') {
    header('Content-Type: application/json; charset=UTF-8');
    header('X-Robots-Tag: noindex');
    try {
        $rows = db()->query('SELECT content_type, slug, data FROM content_overrides')->fetchAll();
        $out = ['ok' => true, 'rows' => []];
        foreach ($rows as $r) {
            $out['rows'][$r['content_type']][$r['slug']] = json_decode($r['data'], true);
        }
        header('Cache-Control: public, s-maxage=3600, stale-while-revalidate=86400');
        echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } catch (Throwable $e) {
        error_log('content-overrides.json: ' . $e->getMessage());
        header('Cache-Control: public, s-maxage=300');
        echo '{"ok":false}';
    }
    exit;
}

require dirname(__DIR__) . '/includes/data/menu.php';
require dirname(__DIR__) . '/includes/data/pages.php';

// Detected once per request, used to prefill phone fields across every form.
$visitor_dial_code = security_visitor_dial_code();

/* URLs that moved or were retired. Permanent, so ranking signals follow. */
$REDIRECTS = [
    'pricing' => '/contact-us/',
    'currency' => '/',
    'ai-visibility-tool' => '/ai-seo/',
    'email-marketing-services' => '/email-marketing/',
];
if (isset($REDIRECTS[$slug])) {
    header('Location: ' . $REDIRECTS[$slug], true, 301);
    exit;
}
/* Old e-book delivery emails still link here. The content is now on the site. */
if ($slug === 'download') {
    header('Location: /ebooks/', true, 301);
    exit;
}

/* XML sitemap. lastmod comes from the post date where we have one, so
   crawlers can tell what actually changed instead of refetching everything;
   SITE_UPDATED covers site-wide changes such as new titles. */
if ($slug === 'sitemap.xml') {
    header('Content-Type: application/xml; charset=UTF-8');
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($PAGES as $s => $p) {
        // A sitemap must only list indexable URLs; noindex pages (thank-you)
        // would otherwise be advertised to crawlers and then refused.
        if (!empty($p['noindex'])) {
            continue;
        }
        $loc = e(abs_url(page_url($s)));
        $tpl = $p['template'] ?? 'service';
        $priority = $s === '' ? '1.0' : ($tpl === 'hub' ? '0.8' : ($tpl === 'blog-post' ? '0.7' : '0.6'));
        $freq = $s === '' ? 'daily' : ($tpl === 'blog-post' ? 'monthly' : 'weekly');
        // Only a real date goes out. Stamping undated pages with today's date
        // made every URL look changed on every crawl, and Google stops
        // trusting a sitemap's lastmod once it proves inaccurate.
        $changed = max(SITE_UPDATED, !empty($p['date']) ? date('Y-m-d', strtotime((string) $p['date'])) : '');
        $lastmod = '<lastmod>' . $changed . '</lastmod>';
        echo "  <url><loc>{$loc}</loc>{$lastmod}<changefreq>{$freq}</changefreq><priority>{$priority}</priority></url>\n";
    }
    echo '</urlset>';
    exit;
}

/* RSS feed: gives Bing/Yandex and AI crawlers a dated, machine-readable
   stream of new articles rather than making them diff the blog index. */
if ($slug === 'feed.xml') {
    header('Content-Type: application/rss+xml; charset=UTF-8');
    $posts = array_slice($GLOBALS['BLOG'] ?? [], 0, 30, true);
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom"><channel>' . "\n";
    echo '  <title>' . e(SITE_NAME) . ' Blog</title>' . "\n";
    echo '  <link>' . e(abs_url('/blog/')) . '</link>' . "\n";
    echo '  <description>' . e(SITE_TAGLINE) . '. Search, AI search, paid media and web growth insight.</description>' . "\n";
    echo '  <language>en</language>' . "\n";
    echo '  <atom:link href="' . e(abs_url('/feed.xml')) . '" rel="self" type="application/rss+xml"/>' . "\n";
    foreach ($posts as $ps => $post) {
        $url = e(abs_url('/blog/' . $ps . '/'));
        $pub = !empty($post['date']) ? date(DATE_RSS, strtotime((string) $post['date'])) : date(DATE_RSS);
        echo "  <item>\n";
        echo '    <title>' . e($post['h1'] ?? $ps) . "</title>\n";
        echo "    <link>{$url}</link>\n    <guid isPermaLink=\"true\">{$url}</guid>\n";
        echo '    <pubDate>' . $pub . "</pubDate>\n";
        echo '    <description>' . e($post['lede'] ?? '') . "</description>\n";
        echo "  </item>\n";
    }
    echo '</channel></rss>';
    exit;
}

/* llms.txt: a plain-text map of the site for AI answer engines.
   Generated from live data so it can never drift out of date. */
if ($slug === 'llms.txt') {
    header('Content-Type: text/plain; charset=UTF-8');
    $out = [];
    $out[] = '# ' . SITE_NAME;
    $out[] = '';
    $out[] = '> VTurnU is a digital marketing and branding company based in Chennai, India. It helps';
    $out[] = '> businesses in ' . implode(', ', TARGET_MARKETS) . ' grow through SEO, AI search optimization (AEO and GEO), paid media, content and branding.';
    $out[] = '> It also builds websites, and runs a separate Enterprise & IT practice covering SAP CPQ,';
    $out[] = '> Salesforce, Odoo ERP, ERPNext and IT staffing.';
    $out[] = '';
    $out[] = 'Contact: ' . CONTACT_EMAIL . ' | ' . CONTACT_PHONE;
    $out[] = 'Enquire: ' . abs_url('/contact-us/');
    $out[] = 'Sitemap: ' . abs_url('/sitemap.xml');
    $out[] = '';
    $out[] = '## Key facts';
    $out[] = '';
    $out[] = '- Best for: B2B companies with a clear offer that want qualified leads, including manufacturers, software companies, ERP partners and professional services.';
    $out[] = '- Core services: B2B SEO, AI search optimization (GEO and AEO), Google Ads and paid media, content marketing, branding and lead generation websites.';
    $out[] = '- Separate practice: SAP CPQ, Salesforce, Odoo ERP and ERPNext implementation, and IT staffing.';
    $out[] = '- Minimum budget: INR 50,000 for any project or monthly plan, shown in local currency by country. Ad spend is separate.';
    $out[] = '- Commitment: a 90 day minimum for search programs, then month to month.';
    $out[] = '- Response: a senior strategist replies within one working day.';
    $out[] = '- Ownership: clients own their accounts, content, code and data.';
    $out[] = '';
    $out[] = '## Measured client results';
    $out[] = '';
    foreach ($CASES as $cs => $c) {
        if (!empty($c['real'])) {
            $facts = array_map(static fn($r) => $r[0] . ' ' . $r[1], array_slice($c['results'], 0, 3));
            $out[] = '- [' . $c['client'] . '](' . abs_url('/case-studies/' . $cs . '/') . '): ' . implode('; ', $facts) . '. ' . ($c['site_label'] ?? '');
        }
    }
    $out[] = '';

    $groups = [
        'Digital Marketing and AI Search' => [], 'Free tools' => [], 'Enterprise & IT' => [], 'Guides and E-books' => [],
        'Articles' => [], 'Case Studies' => [], 'Company' => [],
    ];
    foreach ($PAGES as $s => $p) {
        $tpl = $p['template'] ?? 'service';
        $line = '- [' . ($p['h1'] ?? $s) . '](' . abs_url(page_url($s)) . '): ' . ($p['meta'] ?? ($p['lede'] ?? ''));
        if (!empty($p['noindex'])) {
            continue;
        }
        if (in_array($tpl, ['audit', 'ai-crawler-checker'], true)) {
            $groups['Free tools'][] = $line;
        } elseif ($tpl === 'landing' || $s === 'enterprise-it-services') {
            $groups['Enterprise & IT'][] = $line;
        } elseif (str_starts_with($s, 'blog/')) {
            $groups['Articles'][] = $line;
        } elseif (str_starts_with($s, 'ebooks/') || str_starts_with($s, 'guides/')) {
            $groups['Guides and E-books'][] = $line;
        } elseif ($tpl === 'case-detail') {
            $groups['Case Studies'][] = $line;
        } elseif (in_array($s, ['about-us', 'how-we-work', 'our-clients', 'contact-us', 'ai-policy'], true)) {
            $groups['Company'][] = $line;
        } elseif (in_array($tpl, ['service', 'hub'], true)) {
            $groups['Digital Marketing and AI Search'][] = $line;
        }
    }
    foreach ($groups as $heading => $lines) {
        if (!$lines) continue;
        $out[] = '## ' . $heading;
        $out[] = '';
        foreach ($lines as $l) {
            // Keep each entry to one readable line.
            $out[] = preg_replace('/\s+/', ' ', $l);
        }
        $out[] = '';
    }
    echo implode("\n", $out);
    exit;
}

/* Admin panel + CRM */
if ($slug === 'admin' || str_starts_with($slug, 'admin/')) {
    require dirname(__DIR__) . '/includes/admin.php';
    exit;
}

/* CRM: private web proposals, email open pixels and the morning digest. */
if (crm_route($slug)) {
    exit;
}

/* AJAX enquiry endpoint (quote pop-up, resource downloads, newsletter, audit-page consultation) */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $slug === 'enquiry') {
    header('Content-Type: application/json; charset=UTF-8');

    /* The shared lead form: validated, screened, stored and emailed by lead_submit(). */
    if (($_POST['vt_form'] ?? '') === 'lead-v3') {
        $r = lead_submit($_POST);
        echo json_encode([
            'ok' => $r['ok'], 'error' => $r['error'], 'errors' => (object) $r['errors'],
            'redirect' => $r['ok'] ? '/thank-you/?s=' . rawurlencode($r['source']) : null,
        ]);
        exit;
    }

    /* Only the newsletter still posts here outside the shared form. Anything else
       is a script aiming at the endpoint to skip validation. */
    if (trim((string) ($_POST['service'] ?? '')) !== 'Newsletter signup') {
        echo json_encode(['ok' => false, 'error' => 'Please use the form on the page.']);
        exit;
    }
    $check = security_check_submission($_POST);
    if (!$check['ok']) {
        echo json_encode(['ok' => false, 'error' => $check['error']]);
        exit;
    }
    $svc = trim($_POST['service'] ?? '');
    $src = $svc === 'Newsletter signup' ? 'newsletter'
        : (str_starts_with($svc, 'Free SEO Audit Page') ? 'audit-consultation'
        : (trim((string) ($_POST['lead_source'] ?? '')) === 'landing' ? 'landing-page' : 'popup'));

    $missing = lead_missing($_POST, $src);
    if ($missing !== '') {
        echo json_encode(['ok' => false, 'error' => $missing]);
        exit;
    }
    $saved = save_enquiry($_POST, $src);

    /* Hand back where to go next so the pop-up forms can send the visitor to
       the same thank-you page the non-JS flow uses, instead of swapping in a
       small panel underneath a form that is still on screen. */
    echo json_encode([
        'ok' => $saved,
        'redirect' => $saved ? '/thank-you/?s=' . rawurlencode($src) : null,
    ]);
    exit;
}

/* Enquiry form submission (contact page + home quote form).
   On success, redirect to the thank-you page rather than re-rendering the form
   page with a flag: it gives the visitor a clean finish, gives analytics one
   conversion URL to fire on, and means a refresh cannot resubmit the form. */
$form_status = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($slug, ['contact-us', ''], true) && ($_POST['vt_form'] ?? '') === 'lead-v3') {
    /* No JavaScript: the shared form posts here and gets a redirect or its own errors back. */
    $r = lead_submit($_POST);
    if ($r['ok']) {
        header('Location: /thank-you/?s=' . rawurlencode($r['source']), true, 303);
        exit;
    }
    $form_status = 'error';
    $form_error = $r['error'];
    $form_errors = $r['errors'];
    $slug = 'contact-us';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($slug, ['contact-us', ''], true)) {
    /* A post without the shared form's marker came from a script, not this site. */
    $form_status = 'error';
    $form_error = 'Please use the form below.';
    $form_errors = [];
}

/* Free SEO audit: fetch the visitor's site, score it, show it and email a copy. */
$audit_result = null;
$audit_error  = '';
$audit_input  = ['url' => '', 'name' => '', 'email' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $slug === 'free-seo-audit') {
    require dirname(__DIR__) . '/includes/audit.php';

    $audit_input = [
        'url'   => trim($_POST['site_url'] ?? ''),
        'name'  => trim($_POST['name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
    ];
    $honeypot = trim($_POST['website'] ?? '');
    $ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    [$safe_url, $url_err] = audit_safe_url($audit_input['url']);
    $csrf_ok = csrf_verify((string) ($_POST['csrf_token'] ?? ''));

    if ($honeypot !== '') {
        $audit_error = 'Something went wrong. Please try again.';
    } elseif (!$csrf_ok) {
        $audit_error = 'Your session expired. Please refresh the page and try again.';
    } elseif (csrf_age((string) ($_POST['csrf_token'] ?? '')) < 2) {
        $audit_error = 'Please try again.';
    } elseif (security_ua_is_bot()) {
        $audit_error = 'Something went wrong. Please try again.';
    } elseif (!filter_var($audit_input['email'], FILTER_VALIDATE_EMAIL)) {
        $audit_error = 'Please enter a valid email address so we can send your report.';
    } elseif ($url_err !== '') {
        $audit_error = $url_err;
    } elseif (!audit_rate_ok($ip)) {
        $audit_error = 'You have run several audits recently. Please try again in an hour, or email ' . CONTACT_EMAIL . ' and we will run it for you.';
    } elseif (!security_recaptcha_ok((string) ($_POST['recaptcha_token'] ?? ''))) {
        $audit_error = 'We could not verify you are human. Please refresh the page and try again.';
    } else {
        $audit_result = run_site_audit($safe_url);
        if (!$audit_result['ok']) {
            $audit_error = $audit_result['error'];
            $audit_result = null;
        } else {
            /* Deliver the report to the browser first, then do the mailing.
               Sending is the slowest part on some hosts, and the visitor should
               never wait on it to see their own results. */
            $audit_done = $audit_result;
            $audit_who  = $audit_input;
            register_shutdown_function(function () use ($audit_done, $audit_who) {
                if (function_exists('fastcgi_finish_request')) {
                    fastcgi_finish_request();
                }
                $fallback_name = ucfirst(strtok($audit_who['email'], '@')) ?: 'Website Audit Lead';
                save_enquiry([
                    'name'    => $audit_who['name'] !== '' ? $audit_who['name'] : $fallback_name,
                    'email'   => $audit_who['email'],
                    'company' => parse_url($audit_done['url'], PHP_URL_HOST),
                    'service' => 'Free SEO audit',
                    'message' => 'Audit score ' . $audit_done['score'] . '/100 for ' . $audit_done['url'],
                ], 'seo-audit');
                audit_send_mail($audit_done, $audit_who['name'], $audit_who['email']);
            });
        }
    }
}

/* AI Crawler Checker: a plain form post, answered by the page itself, so the
   tool works without JavaScript and keeps no state. */
$aic_result = null;
$aic_error = '';
$aic_input = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $slug === 'ai-crawler-checker') {
    require_once dirname(__DIR__) . '/includes/ai-crawlers.php';
    [$aic_result, $aic_error, $aic_input] = aic_handle_post();
}

/* One URL per page. Without this, /seo-services and /seo-services/ both
   answered 200 (only the canonical tag named the slash form), which splits
   links and crawling between two addresses. GET and HEAD only, so a form
   posted to a bare path still reaches its handler above. */
if ($slug !== '' && isset($PAGES[$slug]) && is_string($uri) && !str_ends_with($uri, '/')
    && in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)) {
    $qs = (string) ($_SERVER['QUERY_STRING'] ?? '');
    header('Location: ' . page_url($slug) . ($qs !== '' ? '?' . $qs : ''), true, 308);
    exit;
}

/* Resolve page */
if (!isset($PAGES[$slug])) {
    http_response_code(404);
    $page = [
        'template' => '404',
        'title' => 'Page Not Found | ' . SITE_NAME,
        'meta' => 'The page you are looking for could not be found.',
        'h1' => 'Page Not Found',
        'lede' => 'The page you\'re looking for doesn\'t exist or has moved.',
    ];
    $slug = '__404__';
} else {
    $page = $PAGES[$slug];
}

$template = $page['template'] ?? 'service';
$trail = $slug === '__404__' ? [['Home', '/']] : breadcrumbs($PAGES, $slug);
$canonical = abs_url(page_url($slug === '__404__' ? '' : $slug));

require BASE_PATH . '/includes/header.php';
$template_file = BASE_PATH . '/templates/' . $template . '.php';
require is_file($template_file) ? $template_file : BASE_PATH . '/templates/service.php';
require BASE_PATH . '/includes/footer.php';
