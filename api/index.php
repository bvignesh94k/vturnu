<?php
/**
 * VTurnU, front controller. Runs here (under api/) because Vercel only
 * treats files inside api/ as executable functions — a file with this exact
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
require dirname(__DIR__) . '/includes/data/menu.php';
require dirname(__DIR__) . '/includes/data/pages.php';

// Detected once per request, used to prefill phone fields across every form.
$visitor_dial_code = security_visitor_dial_code();

/* parse_url() returns false, not null, on a malformed URI, so ?? never caught
   it and trim(false) was a fatal under strict_types: a request to "//" or any
   junk path returned a 500 to the visitor and to crawlers instead of a 404. */
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$slug = trim(is_string($uri) ? $uri : '/', '/');

/* XML sitemap. lastmod comes from the post date where we have one, so
   crawlers can tell what actually changed instead of refetching everything. */
if ($slug === 'sitemap.xml') {
    header('Content-Type: application/xml; charset=UTF-8');
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($PAGES as $s => $p) {
        $loc = e(abs_url(page_url($s)));
        $tpl = $p['template'] ?? 'service';
        $priority = $s === '' ? '1.0' : ($tpl === 'hub' ? '0.8' : ($tpl === 'blog-post' ? '0.7' : '0.6'));
        $freq = $s === '' ? 'daily' : ($tpl === 'blog-post' ? 'monthly' : 'weekly');
        $lastmod = !empty($p['date']) ? date('Y-m-d', strtotime((string) $p['date'])) : date('Y-m-d');
        echo "  <url><loc>{$loc}</loc><lastmod>{$lastmod}</lastmod><changefreq>{$freq}</changefreq><priority>{$priority}</priority></url>\n";
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
    $out[] = '> Performance-driven digital marketing, web and AI development. We help businesses';
    $out[] = '> in India, the US, UK, Canada and Australia grow measurable revenue through SEO,';
    $out[] = '> answer engine optimization, paid media, content, web development and AI automation.';
    $out[] = '';
    $out[] = 'Contact: ' . CONTACT_EMAIL . ' | ' . CONTACT_PHONE;
    $out[] = 'Sitemap: ' . abs_url('/sitemap.xml');
    $out[] = '';

    $groups = [
        'Services' => [], 'Guides and Articles' => [], 'Case Studies' => [], 'Company' => [],
    ];
    foreach ($PAGES as $s => $p) {
        $tpl = $p['template'] ?? 'service';
        $line = '- [' . ($p['h1'] ?? $s) . '](' . abs_url(page_url($s)) . '): ' . ($p['lede'] ?? '');
        if (str_starts_with($s, 'blog/') || str_starts_with($s, 'ebooks/') || str_starts_with($s, 'guides/')) {
            $groups['Guides and Articles'][] = $line;
        } elseif (str_starts_with($s, 'case-studies/')) {
            $groups['Case Studies'][] = $line;
        } elseif (in_array($s, ['about-us', 'contact-us', 'pricing', 'ai-policy'], true)) {
            $groups['Company'][] = $line;
        } elseif (in_array($tpl, ['service', 'hub'], true)) {
            $groups['Services'][] = $line;
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

/* AJAX enquiry endpoint (quote pop-up, resource downloads, newsletter, audit-page consultation) */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $slug === 'enquiry') {
    header('Content-Type: application/json; charset=UTF-8');
    $check = security_check_submission($_POST);
    if (!$check['ok']) {
        echo json_encode(['ok' => false, 'error' => $check['error']]);
        exit;
    }
    $svc = trim($_POST['service'] ?? '');
    $src = $svc === 'Newsletter signup' ? 'newsletter'
        : (str_starts_with($svc, 'Resource: ') ? 'resource'
        : (str_starts_with($svc, 'Free SEO Audit Page') ? 'audit-consultation'
        : 'popup'));

    $saved = save_enquiry($_POST, $src);

    /* Resource downloads: actually deliver the thing that was promised.
       Sending happens after the response so the visitor is not left waiting
       on a mail call, which is the slowest part of the request on most hosts. */
    if ($saved && $src === 'resource') {
        $rslug = preg_replace('/[^a-z0-9-]/', '', (string) ($_POST['resource'] ?? ''));
        if ($rslug !== '' && isset($RESOURCES[$rslug])) {
            $rname  = trim((string) ($_POST['name'] ?? ''));
            $remail = trim((string) ($_POST['email'] ?? ''));
            $rres   = $RESOURCES[$rslug];
            register_shutdown_function(function () use ($rslug, $rres, $rname, $remail) {
                if (function_exists('fastcgi_finish_request')) { fastcgi_finish_request(); }
                require_once BASE_PATH . '/includes/ebook-build.php';
                ebook_send($rslug, $rres, $rname, $remail);
            });
        }
    }

    echo json_encode(['ok' => $saved]);
    exit;
}

/* Tokenised resource download. The link in the delivery email lands here. */
if ($slug === 'download') {
    require_once dirname(__DIR__) . '/includes/ebook-build.php';
    $check = ebook_token_verify((string) ($_GET['t'] ?? ''));
    if (!$check['ok']) {
        http_response_code(410);
        $download_error = $check['error'];
    } else {
        $dslug = $check['slug'];
        $res = $RESOURCES[$dslug] ?? null;
        $file = $res ? ebook_file($dslug, $res) : null;
        if ($file && is_file($file)) {
            $name = ($res['h1'] ?? $dslug);
            $safe = preg_replace('/[^A-Za-z0-9 ]/', '', $name);
            header('Content-Type: application/epub+zip');
            header('Content-Disposition: attachment; filename="' . $safe . '.epub"');
            header('Content-Length: ' . filesize($file));
            header('X-Content-Type-Options: nosniff');
            header('Cache-Control: private, max-age=0, must-revalidate');
            readfile($file);
            exit;
        }
        http_response_code(404);
        $download_error = 'That resource could not be found.';
    }
}

/* Enquiry form submission (contact page + home quote form) */
$form_status = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($slug, ['contact-us', ''], true)) {
    $check = security_check_submission($_POST);
    $source = $slug === '' ? 'home-quote' : 'contact-page';
    $form_status = ($check['ok'] && save_enquiry($_POST, $source)) ? 'success' : 'error';
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

/* Resolve page */
if (!empty($download_error)) {
    // An expired or invalid download link. Explain it and offer a way forward
    // rather than dropping the visitor on a generic 404.
    $page = [
        'template' => '404',
        'title' => 'Download link problem',
        'meta' => 'This download link is no longer valid.',
        'h1' => 'That link is no longer valid',
        'lede' => $download_error . ' You can request a fresh copy from the resource page and we will send a new link straight away.',
    ];
    $slug = '__404__';
} elseif (!isset($PAGES[$slug])) {
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
