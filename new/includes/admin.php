<?php
/**
 * VTurnU Admin: leads CRM + content manager with full editing.
 * Routed from index.php for any /admin/ URL. Session auth, CSRF-protected POSTs.
 * Storage: storage/admin.json (auth), storage/leads-meta.json (CRM state),
 *          storage/blog-custom.json, storage/cases-custom.json, storage/resources-custom.json
 *
 * Default login: admin / vturnu@admin. Change it in Settings after first login.
 */

declare(strict_types=1);

session_name('vturnu_admin');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => !empty($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();

// Idle timeout: 2 hours since the last request logs the session out, so a
// laptop left open at a shared desk doesn't stay authenticated indefinitely.
const ADMIN_IDLE_TIMEOUT = 7200;
if (!empty($_SESSION['admin_ok']) && !empty($_SESSION['last_seen']) && (time() - $_SESSION['last_seen']) > ADMIN_IDLE_TIMEOUT) {
    $_SESSION = [];
    session_regenerate_id(true);
}
$_SESSION['last_seen'] = time();

const ADMIN_STATUSES = ['new', 'contacted', 'qualified', 'proposal', 'won', 'lost'];
const BLOG_INTENTS = ['Informational', 'Commercial Investigation', 'How-to / Tutorial', 'Buying Guide', 'Comparison', 'Problem–Solution', 'Industry Trends', 'Use Cases', 'Benefits', 'FAQs', 'Best Practices', 'Case Study', 'Cost & Pricing', 'Trends & Innovations', 'Mistakes to Avoid'];
const BLOG_CATS = ['Strategy', 'SEO', 'AI Search', 'Paid Media', 'Content', 'Social', 'Web', 'Conversion', 'Innovation', 'Case Study'];
const INDUSTRIES = ['Jewelry', 'SaaS & Tech', 'Healthcare', 'Real Estate', 'Ecommerce', 'Food & Hospitality', 'Legal Services', 'Fashion', 'Manufacturing', 'Education'];

$storage = BASE_PATH . '/storage';
if (!is_dir($storage)) { mkdir($storage, 0755, true); }

/* ---------- Storage helpers ---------- */

function admin_read_json(string $file, $default) {
    if (!is_file($file)) return $default;
    $d = json_decode((string) file_get_contents($file), true);
    return is_array($d) ? $d : $default;
}
function admin_write_json(string $file, $data): void {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

/* Auth */
$auth_file = $storage . '/admin.json';
$auth = admin_read_json($auth_file, []);
if (empty($auth['hash'])) {
    $auth = ['user' => 'admin', 'hash' => password_hash('vturnu@admin', PASSWORD_DEFAULT)];
    admin_write_json($auth_file, $auth);
}

/* ---------- Leads ---------- */

function admin_load_leads(string $storage): array {
    $file = $storage . '/enquiries.jsonl';
    $leads = [];
    if (is_file($file)) {
        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $l = json_decode($line, true);
            if (!is_array($l)) continue;
            $l['id'] = substr(md5(($l['date'] ?? '') . '|' . ($l['email'] ?? '') . '|' . ($l['message'] ?? '')), 0, 12);
            $leads[] = $l;
        }
    }
    $meta = admin_read_json($storage . '/leads-meta.json', []);
    foreach ($leads as &$l) {
        $m = $meta[$l['id']] ?? [];
        $l['status'] = $m['status'] ?? 'new';
        $l['notes'] = $m['notes'] ?? [];
    }
    return array_reverse($leads);
}

function admin_save_lead_meta(string $storage, string $id, array $patch): void {
    $file = $storage . '/leads-meta.json';
    $meta = admin_read_json($file, []);
    $meta[$id] = array_merge($meta[$id] ?? [], $patch);
    admin_write_json($file, $meta);
}

function admin_delete_lead(string $storage, string $id): void {
    $file = $storage . '/enquiries.jsonl';
    if (!is_file($file)) return;
    $keep = [];
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $l = json_decode($line, true);
        $lid = is_array($l) ? substr(md5(($l['date'] ?? '') . '|' . ($l['email'] ?? '') . '|' . ($l['message'] ?? '')), 0, 12) : '';
        if ($lid !== $id) $keep[] = $line;
    }
    file_put_contents($file, $keep ? implode("\n", $keep) . "\n" : '', LOCK_EX);
    $meta = admin_read_json($storage . '/leads-meta.json', []);
    unset($meta[$id]);
    admin_write_json($file, $meta);
}

/* ---------- Content helpers ---------- */

function admin_slugify(string $title): string {
    $s = strtolower($title);
    $s = preg_replace('/[^a-z]+/', '-', $s);
    return trim(preg_replace('/-+/', '-', $s), '-') ?: 'post';
}

function admin_parse_sections(string $raw): array {
    $sections = []; $current = null;
    foreach (preg_split('/\r\n|\r|\n/', $raw) as $line) {
        $line = rtrim($line);
        if (str_starts_with($line, '## ')) {
            if ($current) $sections[] = $current;
            $current = [substr($line, 3), [], []];
        } elseif ($current === null) {
            if (trim($line) !== '') { $current = ['Overview', [trim($line)], []]; }
        } elseif (str_starts_with($line, '- ')) {
            $current[2][] = substr($line, 2);
        } elseif (trim($line) !== '') {
            $current[1][] = trim($line);
        }
    }
    if ($current) $sections[] = $current;
    return $sections;
}

/* ---------- Auth gate + actions ---------- */

$logged_in = !empty($_SESSION['admin_ok']);
if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(16)); }
$csrf = $_SESSION['csrf'];
$view = $_GET['view'] ?? 'dashboard';
$flash = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        sleep(1);
        if (!security_login_ok()) {
            $mins = (int) ceil(security_login_lock_remaining() / 60);
            $flash = "Too many failed attempts. Try again in {$mins} minute" . ($mins === 1 ? '' : 's') . '.';
        } elseif (hash_equals($auth['user'], trim($_POST['user'] ?? '')) && password_verify((string) ($_POST['pass'] ?? ''), $auth['hash'])) {
            security_login_clear();
            session_regenerate_id(true);
            $_SESSION['admin_ok'] = true;
            $_SESSION['csrf'] = bin2hex(random_bytes(16));
            header('Location: /admin/'); exit;
        } else {
            security_login_fail();
            $flash = 'Wrong username or password.';
        }
    } elseif (!$logged_in) {
        http_response_code(403); $flash = 'Session expired. Please log in again.';
    } elseif (!hash_equals($csrf, $_POST['csrf'] ?? '')) {
        http_response_code(403); $flash = 'Invalid request token. Try again.';
    } else {
        switch ($action) {
            case 'logout':
                session_destroy();
                header('Location: /admin/'); exit;

            case 'lead_status':
                $st = $_POST['status'] ?? 'new';
                if (in_array($st, ADMIN_STATUSES, true)) {
                    admin_save_lead_meta($storage, $_POST['id'] ?? '', ['status' => $st]);
                    $flash = 'Status updated.';
                }
                break;

            case 'lead_note':
                $note = trim($_POST['note'] ?? '');
                if ($note !== '') {
                    $meta = admin_read_json($storage . '/leads-meta.json', []);
                    $id = $_POST['id'] ?? '';
                    $notes = $meta[$id]['notes'] ?? [];
                    $notes[] = ['date' => date('Y-m-d H:i'), 'text' => $note];
                    admin_save_lead_meta($storage, $id, ['notes' => $notes]);
                    $flash = 'Note added.';
                }
                break;

            case 'lead_delete':
                admin_delete_lead($storage, $_POST['id'] ?? '');
                header('Location: /admin/?view=leads'); exit;

            case 'blog_save':
                $custom = admin_read_json($storage . '/blog-custom.json', []);
                $orig = $_POST['orig_slug'] ?? '';
                // Editing anything that already exists (built-in or custom) saves under
                // its own slug, so a built-in post becomes an editable override.
                $known = $orig !== '' && (isset($custom[$orig]) || in_array($orig, $GLOBALS['BLOG_BUILTIN'] ?? [], true));
                $bslug = $known ? $orig : admin_slugify($_POST['title_h1'] ?? '');
                if (!$known && isset($GLOBALS['BLOG'][$bslug])) { $bslug .= '-custom'; }
                $custom[$bslug] = [
                    // No brand suffix: only the homepage carries the brand in its title.
                    'title' => trim($_POST['seo_title'] ?? '') ?: trim($_POST['title_h1'] ?? ''),
                    'meta' => trim($_POST['meta_desc'] ?? ''),
                    'h1' => trim($_POST['title_h1'] ?? ''),
                    'lede' => trim($_POST['lede'] ?? ''),
                    'category' => trim($_POST['category'] ?? 'Strategy'),
                    'intent' => trim($_POST['intent'] ?? 'Informational'),
                    'date' => $_POST['pub_date'] ?: date('Y-m-d'),
                    'read' => trim($_POST['read_time'] ?? '6 min'),
                    'sections' => admin_parse_sections($_POST['body'] ?? ''),
                    'takeaways' => array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $_POST['takeaways'] ?? ''))),
                    'faqs' => array_values(array_filter(array_map(
                        fn($l) => count($p = array_map('trim', explode('|', $l, 2))) === 2 ? $p : null,
                        preg_split('/\r\n|\r|\n/', $_POST['faqs'] ?? '')
                    ))),
                    'cta' => [trim($_POST['cta_head'] ?? ''), trim($_POST['cta_sub'] ?? ''), trim($_POST['cta_btn'] ?? '')]
                ];
                admin_write_json($storage . '/blog-custom.json', $custom);
                header('Location: /admin/?view=blogs&saved=' . urlencode($bslug)); exit;

            case 'blog_delete':
                $custom = admin_read_json($storage . '/blog-custom.json', []);
                unset($custom[$_POST['slug'] ?? '']);
                admin_write_json($storage . '/blog-custom.json', $custom);
                header('Location: /admin/?view=blogs'); exit;

            case 'case_save':
                $custom = admin_read_json($storage . '/cases-custom.json', []);
                $orig = $_POST['orig_slug'] ?? '';
                $known = $orig !== '' && (isset($custom[$orig]) || in_array($orig, $GLOBALS['CASES_BUILTIN'] ?? [], true));
                $cslug = $known ? $orig : admin_slugify($_POST['title_h1'] ?? '');
                if (!$known && isset($GLOBALS['CASES'][$cslug])) { $cslug .= '-custom'; }
                $results = array_values(array_filter(array_map(
                    fn($l) => count($p = array_map('trim', explode('|', $l, 2))) === 2 ? $p : null,
                    preg_split('/\r\n|\r|\n/', $_POST['results'] ?? '')
                )));
                $quote_parts = array_map('trim', explode('|', $_POST['quote'] ?? '', 3));
                $custom[$cslug] = [
                    'title' => trim($_POST['seo_title'] ?? '') ?: (trim($_POST['title_h1'] ?? '') . ' Case Study'),
                    'meta' => trim($_POST['meta_desc'] ?? ''),
                    'h1' => trim($_POST['title_h1'] ?? ''),
                    'lede' => trim($_POST['lede'] ?? ''),
                    'industry' => trim($_POST['industry'] ?? ''),
                    'service' => trim($_POST['service'] ?? 'multi'),
                    'date' => $_POST['date'] ?: date('Y-m-d'),
                    'challenge' => trim($_POST['challenge'] ?? ''),
                    'solution' => trim($_POST['solution'] ?? ''),
                    'results' => $results,
                    'quote' => count($quote_parts) === 3 ? array_slice($quote_parts, 0, 3) : null,
                    'cta' => [trim($_POST['cta_head'] ?? ''), trim($_POST['cta_sub'] ?? ''), trim($_POST['cta_btn'] ?? '')]
                ];
                admin_write_json($storage . '/cases-custom.json', $custom);
                header('Location: /admin/?view=cases&saved=' . urlencode($cslug)); exit;

            case 'case_delete':
                $custom = admin_read_json($storage . '/cases-custom.json', []);
                unset($custom[$_POST['slug'] ?? '']);
                admin_write_json($storage . '/cases-custom.json', $custom);
                header('Location: /admin/?view=cases'); exit;

            case 'resource_save':
                $custom = admin_read_json($storage . '/resources-custom.json', []);
                $orig = $_POST['orig_slug'] ?? '';
                $known = $orig !== '' && (isset($custom[$orig]) || in_array($orig, $GLOBALS['RESOURCES_BUILTIN'] ?? [], true));
                $rslug = $known ? $orig : admin_slugify($_POST['title_h1'] ?? '');
                if (!$known && isset($GLOBALS['RESOURCES'][$rslug])) { $rslug .= '-custom'; }
                $topics = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $_POST['topics'] ?? '')));
                $custom[$rslug] = [
                    'title' => trim($_POST['seo_title'] ?? '') ?: (trim($_POST['title_h1'] ?? '') . ' | Free Guide'),
                    'meta' => trim($_POST['meta_desc'] ?? ''),
                    'h1' => trim($_POST['title_h1'] ?? ''),
                    'lede' => trim($_POST['lede'] ?? ''),
                    'type' => trim($_POST['type'] ?? 'ebook'),
                    'category' => trim($_POST['category'] ?? ''),
                    'size' => trim($_POST['size'] ?? ''),
                    'description' => trim($_POST['description'] ?? ''),
                    'topics' => array_values($topics),
                    'url' => trim($_POST['url'] ?? ''),
                    'image' => trim($_POST['image'] ?? ''),
                    'cta' => [trim($_POST['cta_head'] ?? ''), trim($_POST['cta_sub'] ?? ''), trim($_POST['cta_btn'] ?? '')]
                ];
                admin_write_json($storage . '/resources-custom.json', $custom);
                header('Location: /admin/?view=resources&saved=' . urlencode($rslug)); exit;

            case 'resource_delete':
                $custom = admin_read_json($storage . '/resources-custom.json', []);
                unset($custom[$_POST['slug'] ?? '']);
                admin_write_json($storage . '/resources-custom.json', $custom);
                header('Location: /admin/?view=resources'); exit;

            case 'password':
                $cur = (string) ($_POST['current'] ?? '');
                $new = (string) ($_POST['new'] ?? '');
                if (!password_verify($cur, $auth['hash'])) { $flash = 'Current password is wrong.'; }
                elseif (strlen($new) < 8) { $flash = 'New password must be at least 8 characters.'; }
                else {
                    $auth['hash'] = password_hash($new, PASSWORD_DEFAULT);
                    if (trim($_POST['user'] ?? '') !== '') { $auth['user'] = trim($_POST['user']); }
                    admin_write_json($auth_file, $auth);
                    $flash = 'Credentials updated.';
                }
                break;
        }
    }
    $logged_in = !empty($_SESSION['admin_ok']);
}

/* CSV export */
if ($logged_in && $view === 'export') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="vturnu-leads-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Date', 'Status', 'Source', 'Name', 'Email', 'Phone', 'Company', 'Service', 'Budget', 'Message']);
    foreach (admin_load_leads($storage) as $l) {
        fputcsv($out, [$l['date'] ?? '', $l['status'], $l['source'] ?? '', $l['name'] ?? '', $l['email'] ?? '', $l['mobile'] ?? '', $l['company'] ?? '', $l['service'] ?? '', $l['budget'] ?? '', $l['message'] ?? '']);
    }
    exit;
}

/* Data for views */
$leads = $logged_in ? admin_load_leads($storage) : [];
$custom_posts = admin_read_json($storage . '/blog-custom.json', []);
$custom_cases = admin_read_json($storage . '/cases-custom.json', []);
$custom_resources = admin_read_json($storage . '/resources-custom.json', []);

function e_a($v): string { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }
function status_badge(string $s): string {
    $colors = ['new' => '#29B8DB', 'contacted' => '#F7B500', 'qualified' => '#9B59B6', 'proposal' => '#E8365D', 'won' => '#2ECC71', 'lost' => '#8B8E96'];
    return '<span class="badge-st" style="--st:' . ($colors[$s] ?? '#888') . '">' . e_a(ucfirst($s)) . '</span>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>VTurnU Admin<?= $logged_in ? ' | ' . e_a(ucfirst($view)) : '' ?></title>
<link rel="icon" type="image/png" href="/assets/img/favicon.png">
<style>
:root { --ink:#17181C; --line:#E7E3D8; --soft:#5A5D66; --muted:#8B8E96; --bg:#F7F5EF;
        --y:#F7B500; --p:#E8365D; --c:#29B8DB; --v:#9B59B6;
        --grad:linear-gradient(120deg,#F7B500,#E8365D 45%,#9B59B6 75%,#29B8DB 100%); }
* { margin:0; padding:0; box-sizing:border-box; }
body { font:15px/1.6 'Segoe UI',system-ui,sans-serif; background:var(--bg); color:var(--ink); }
a { color:inherit; text-decoration:none; }
.layout { display:grid; grid-template-columns:220px 1fr; min-height:100vh; }
.side { background:var(--ink); color:#C9CBD1; padding:20px 14px; display:flex; flex-direction:column; gap:3px; }
.side-brand { font-weight:800; color:#fff; font-size:1rem; margin-bottom:3px; padding:0 10px; }
.side-brand em { font-style:normal; background:var(--grad); -webkit-background-clip:text; background-clip:text; color:transparent; }
.side-sub { font-size:.68rem; color:#8B8E96; padding:0 10px; margin-bottom:14px; text-transform:uppercase; letter-spacing:.08em; }
.side a.nav { display:block; padding:9px 11px; border-radius:8px; font-weight:600; font-size:.88rem; }
.side a.nav:hover { background:rgba(255,255,255,.08); color:#fff; }
.side a.nav.on { background:var(--grad); color:var(--ink); font-weight:800; }
.side .spacer { flex:1; }
.side form button { display:block; width:100%; text-align:left; padding:9px 11px; border-radius:8px; background:none; border:0; color:#C9CBD1; font:inherit; font-weight:600; font-size:.88rem; cursor:pointer; }
.side form button:hover { background:rgba(255,255,255,.08); color:#fff; }
.main { padding:26px 30px; max-width:1400px; }
h1 { font-size:1.4rem; letter-spacing:-.01em; margin-bottom:3px; }
.sub { color:var(--muted); font-size:.86rem; margin-bottom:20px; }
.flash { background:#FFF6DC; border:1px solid var(--y); border-radius:10px; padding:10px 14px; margin-bottom:16px; font-size:.9rem; }
.cards { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:22px; }
.kpi { background:#fff; border:1px solid var(--line); border-radius:12px; padding:16px 18px; }
.kpi b { display:block; font-size:1.6rem; letter-spacing:-.02em; }
.kpi span { font-size:.76rem; color:var(--muted); }
.kpi.grad b { background:var(--grad); -webkit-background-clip:text; background-clip:text; color:transparent; }
.panel { background:#fff; border:1px solid var(--line); border-radius:12px; padding:18px 20px; margin-bottom:18px; }
.panel h2 { font-size:1.02rem; margin-bottom:12px; }
table { width:100%; border-collapse:collapse; font-size:.84rem; }
th { text-align:left; font-size:.68rem; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); padding:8px 10px; border-bottom:2px solid var(--line); }
td { padding:9px 10px; border-bottom:1px solid var(--line); vertical-align:top; }
tr:hover td { background:#FBFAF6; }
.badge-st { display:inline-block; padding:3px 10px; border-radius:999px; font-size:.7rem; font-weight:800; background:color-mix(in srgb, var(--st) 15%, white); color:var(--st); border:1px solid color-mix(in srgb, var(--st) 40%, white); }
.btn { display:inline-block; padding:8px 16px; border-radius:999px; border:0; cursor:pointer; font:inherit; font-size:.84rem; font-weight:800; background:var(--ink); color:#fff; }
.btn.grad { background:linear-gradient(135deg,#FFC20E,#F7B500 60%,#F98F1F); color:var(--ink); }
.btn.ghost { background:none; border:1px solid var(--line); color:var(--soft); }
.btn.danger { background:#FDE3EA; color:#C22349; }
.btn.sm { padding:5px 12px; font-size:.76rem; }
input, select, textarea { font:inherit; font-size:.88rem; padding:9px 11px; border:1px solid var(--line); border-radius:8px; background:#fff; width:100%; }
textarea { min-height:100px; resize:vertical; }
label { display:block; font-size:.74rem; font-weight:800; text-transform:uppercase; letter-spacing:.05em; color:var(--soft); margin:12px 0 5px; }
.grid2 { display:grid; grid-template-columns:1fr 1fr; gap:0 16px; }
.grid3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:0 16px; }
.filters { display:flex; gap:10px; margin-bottom:14px; flex-wrap:wrap; }
.filters input, .filters select { width:auto; }
.login-wrap { min-height:100vh; display:grid; place-items:center; background:radial-gradient(ellipse 700px 400px at 80% 10%, rgba(247,181,0,.15), transparent 60%), radial-gradient(ellipse 600px 400px at 10% 90%, rgba(41,184,219,.12), transparent 60%), var(--bg); }
.login { background:#fff; border:1px solid var(--line); border-radius:16px; padding:32px 30px; width:min(380px, calc(100vw - 40px)); box-shadow:0 30px 70px rgba(23,24,28,.12); }
.login h1 { margin-bottom:2px; }
.note { font-size:.76rem; color:var(--muted); margin-top:12px; }
.hint { background:#F0F7FA; border:1px solid #BFE4EF; color:#15768F; border-radius:8px; padding:8px 12px; font-size:.76rem; margin-top:12px; }
.mono { font-family:Consolas,monospace; font-size:.8rem; }
.notes-list { display:grid; gap:7px; margin-top:7px; }
.notes-list li { list-style:none; background:#FBFAF6; border:1px solid var(--line); border-radius:8px; padding:7px 10px; font-size:.82rem; }
.notes-list small { color:var(--muted); display:block; font-size:.68rem; }
.actions-row { display:flex; gap:7px; align-items:center; flex-wrap:wrap; }
.tag-ro { font-size:.66rem; font-weight:800; color:var(--muted); border:1px solid var(--line); border-radius:999px; padding:2px 9px; }
.tag-ed { font-size:.62rem; font-weight:800; color:#8A6400; background:#FFF3CC; border-radius:999px; padding:2px 8px; margin-left:6px; vertical-align:middle; letter-spacing:.04em; text-transform:uppercase; }
.seo-group { background:#F9F7F2; border:1px solid rgba(247,181,0,.15); border-radius:10px; padding:14px 16px; margin:14px 0; }
.seo-group h3 { font-size:.88rem; font-weight:700; color:var(--ink); margin-bottom:10px; }
.seo-note { font-size:.74rem; color:var(--muted); margin-top:4px; font-style:italic; }
@media (max-width:860px){ .layout{grid-template-columns:1fr} .side{flex-direction:row;flex-wrap:wrap;align-items:center} .side .spacer{display:none} .cards{grid-template-columns:1fr 1fr} .grid2,.grid3{grid-template-columns:1fr} }
</style>
</head>
<body>

<?php if (!$logged_in): ?>
<div class="login-wrap">
    <div class="login">
        <h1>VTurn<span style="background:var(--grad);-webkit-background-clip:text;background-clip:text;color:transparent">U</span> Admin</h1>
        <p class="sub">CRM, Content & SEO Manager</p>
        <?php if ($flash): ?><div class="flash"><?= e_a($flash) ?></div><?php endif; ?>
        <form method="post">
            <input type="hidden" name="action" value="login">
            <label for="a-user">Username</label>
            <input id="a-user" name="user" type="text" required autocomplete="username">
            <label for="a-pass">Password</label>
            <input id="a-pass" name="pass" type="password" required autocomplete="current-password">
            <br><br><button class="btn grad" type="submit" style="width:100%">Sign In</button>
        </form>
        <p class="hint">First login: <span class="mono">admin / vturnu@admin</span>. Change credentials in Settings.</p>
    </div>
</div>

<?php else: ?>
<div class="layout">
    <aside class="side">
        <div class="side-brand">VTurn<em>U</em> Admin</div>
        <div class="side-sub">CRM & Content</div>
        <a class="nav<?= $view === 'dashboard' ? ' on' : '' ?>" href="/admin/?view=dashboard">Dashboard</a>
        <a class="nav<?= in_array($view, ['leads', 'lead'], true) ? ' on' : '' ?>" href="/admin/?view=leads">Leads (CRM)</a>
        <a class="nav<?= in_array($view, ['blogs', 'blog-edit'], true) ? ' on' : '' ?>" href="/admin/?view=blogs">Blog Posts</a>
        <a class="nav<?= in_array($view, ['cases', 'case-edit'], true) ? ' on' : '' ?>" href="/admin/?view=cases">Case Studies</a>
        <a class="nav<?= in_array($view, ['resources', 'resource-edit'], true) ? ' on' : '' ?>" href="/admin/?view=resources">E-books & Guides</a>
        <a class="nav<?= $view === 'settings' ? ' on' : '' ?>" href="/admin/?view=settings">Settings</a>
        <div class="spacer"></div>
        <a style="display:block;padding:9px 11px;border-radius:8px;font-weight:600;font-size:.88rem;color:#C9CBD1" href="/" target="_blank">↗ View website</a>
        <form method="post"><input type="hidden" name="action" value="logout"><input type="hidden" name="csrf" value="<?= e_a($csrf) ?>"><button type="submit">Log out</button></form>
    </aside>

    <main class="main">
    <?php if ($flash): ?><div class="flash"><?= e_a($flash) ?></div><?php endif; ?>
    <?php if (!empty($_GET['saved'])): ?><div class="flash">✓ Saved. <a href="/blog/<?= e_a($_GET['saved']) ?>/" target="_blank" style="font-weight:800">view live ↗</a></div><?php endif; ?>

    <?php if ($view === 'dashboard'):
        $week_ago = date('c', strtotime('-7 days'));
        $this_week = count(array_filter($leads, fn($l) => ($l['date'] ?? '') >= $week_ago));
        $by_status = array_fill_keys(ADMIN_STATUSES, 0);
        foreach ($leads as $l) { $by_status[$l['status']] = ($by_status[$l['status']] ?? 0) + 1; }
    ?>
        <h1>Dashboard</h1>
        <p class="sub">Overview of your funnel and content library.</p>
        <div class="cards">
            <div class="kpi grad"><b><?= count($leads) ?></b><span>Total leads captured</span></div>
            <div class="kpi"><b><?= $this_week ?></b><span>New in last 7 days</span></div>
            <div class="kpi"><b><?= count($custom_posts) ?></b><span>Published blog posts</span></div>
            <div class="kpi"><b><?= $by_status['won'] ?></b><span>Won deals</span></div>
        </div>
        <div class="panel">
            <h2>Quick actions</h2>
            <p style="margin-bottom:10px;color:var(--muted)">Jump to content management or review leads.</p>
            <div class="actions-row">
                <a class="btn grad" href="/admin/?view=blogs&view=blog-edit">+ New blog post</a>
                <a class="btn" href="/admin/?view=leads">Review leads</a>
                <a class="btn ghost" href="/blog/">View blog</a>
            </div>
        </div>
        <div class="cards" style="grid-template-columns:1fr 1fr">
            <div class="panel" style="margin:0">
                <h2>Pipeline status</h2>
                <table><tbody>
                <?php foreach (ADMIN_STATUSES as $st): ?>
                    <tr><td><?= status_badge($st) ?></td><td style="text-align:right;font-weight:800"><?= $by_status[$st] ?></td></tr>
                <?php endforeach; ?>
                </tbody></table>
            </div>
            <div class="panel" style="margin:0">
                <h2>Recent leads</h2>
                <table><tbody>
                <?php foreach (array_slice($leads, 0, 5) as $l): ?>
                    <tr><td><strong><?= e_a($l['name'] ?? '') ?></strong><br><small style="color:var(--muted)"><?= e_a($l['email'] ?? '') ?></small></td><td><?= status_badge($l['status']) ?></td></tr>
                <?php endforeach; if (!$leads): ?><tr><td colspan="2">No leads yet.</td></tr><?php endif; ?>
                </tbody></table>
            </div>
        </div>

    <?php elseif ($view === 'leads'):
        $f_status = $_GET['status'] ?? '';
        $f_q = trim($_GET['q'] ?? '');
        $filtered = array_filter($leads, function ($l) use ($f_status, $f_q) {
            if ($f_status !== '' && $l['status'] !== $f_status) return false;
            if ($f_q !== '') {
                $hay = strtolower(($l['name'] ?? '') . ' ' . ($l['email'] ?? '') . ' ' . ($l['company'] ?? '') . ' ' . ($l['service'] ?? ''));
                if (!str_contains($hay, strtolower($f_q))) return false;
            }
            return true;
        });
    ?>
        <h1>Leads (CRM)</h1>
        <p class="sub"><?= count($filtered) ?> of <?= count($leads) ?> leads · <a href="/admin/?view=export" style="font-weight:800;color:var(--p)">Export CSV ↓</a></p>
        <form class="filters" method="get" action="/admin/">
            <input type="hidden" name="view" value="leads">
            <input type="search" name="q" placeholder="Search name, email, company…" value="<?= e_a($f_q) ?>" style="width:280px">
            <select name="status" onchange="this.form.submit()">
                <option value="">All statuses</option>
                <?php foreach (ADMIN_STATUSES as $st): ?>
                <option value="<?= $st ?>"<?= $f_status === $st ? ' selected' : '' ?>><?= ucfirst($st) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn ghost" type="submit">Filter</button>
        </form>
        <div class="panel">
            <table>
                <thead><tr><th>When</th><th>Lead</th><th>Service</th><th>Status</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($filtered as $l): ?>
                    <tr>
                        <td style="white-space:nowrap"><?= e_a(date('j M', strtotime($l['date'] ?? 'now'))) ?><br><small style="color:var(--muted)"><?= e_a(date('H:i', strtotime($l['date'] ?? 'now'))) ?></small></td>
                        <td><strong><?= e_a($l['name'] ?? '') ?></strong><br><small style="color:var(--muted)"><?= e_a($l['email'] ?? '') ?></small></td>
                        <td><?= e_a(mb_substr($l['service'] ?? '', 0, 40)) ?></td>
                        <td><?= status_badge($l['status']) ?></td>
                        <td><a class="btn ghost sm" href="/admin/?view=lead&id=<?= e_a($l['id']) ?>">Open</a></td>
                    </tr>
                <?php endforeach; if (!$filtered): ?><tr><td colspan="5">No leads match.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>

    <?php elseif ($view === 'lead'):
        $lead = null;
        foreach ($leads as $l) { if ($l['id'] === ($_GET['id'] ?? '')) { $lead = $l; break; } }
        if (!$lead): ?>
        <h1>Lead not found</h1>
        <?php else: ?>
        <h1><?= e_a($lead['name'] ?? 'Lead') ?></h1>
        <p class="sub"><a href="/admin/?view=leads">← Back</a> · captured <?= e_a(date('j M Y, H:i', strtotime($lead['date'] ?? 'now'))) ?> via <strong><?= e_a($lead['source'] ?? '') ?></strong></p>
        <div class="cards" style="grid-template-columns:1.4fr 1fr">
            <div class="panel" style="margin:0">
                <h2>Enquiry</h2>
                <table><tbody>
                    <tr><td style="width:100px;color:var(--muted)">Email</td><td><a href="mailto:<?= e_a($lead['email'] ?? '') ?>" style="font-weight:700;color:var(--p)"><?= e_a($lead['email'] ?? '') ?></a></td></tr>
                    <tr><td style="color:var(--muted)">Phone</td><td><?= $lead['mobile'] ? '<a href="tel:' . e_a($lead['mobile']) . '" style="font-weight:700">' . e_a($lead['mobile']) . '</a>' : '–' ?></td></tr>
                    <tr><td style="color:var(--muted)">Company</td><td><?= e_a($lead['company'] ?: '–') ?></td></tr>
                    <tr><td style="color:var(--muted)">Service</td><td><?= e_a($lead['service'] ?: '–') ?></td></tr>
                    <tr><td style="color:var(--muted)">Message</td><td><?= nl2br(e_a($lead['message'] ?: '–')) ?></td></tr>
                </tbody></table>
            </div>
            <div>
                <div class="panel">
                    <h2>Status</h2>
                    <form method="post" class="actions-row">
                        <input type="hidden" name="action" value="lead_status">
                        <input type="hidden" name="csrf" value="<?= e_a($csrf) ?>">
                        <input type="hidden" name="id" value="<?= e_a($lead['id']) ?>">
                        <select name="status" style="flex:1">
                            <?php foreach (ADMIN_STATUSES as $st): ?>
                            <option value="<?= $st ?>"<?= $lead['status'] === $st ? ' selected' : '' ?>><?= ucfirst($st) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn" type="submit">Update</button>
                    </form>
                </div>
                <div class="panel">
                    <form method="post" onsubmit="return confirm('Delete this lead?')">
                        <input type="hidden" name="action" value="lead_delete">
                        <input type="hidden" name="csrf" value="<?= e_a($csrf) ?>">
                        <input type="hidden" name="id" value="<?= e_a($lead['id']) ?>">
                        <button class="btn danger" type="submit">Delete</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="panel">
            <h2>Notes</h2>
            <form method="post" class="actions-row">
                <input type="hidden" name="action" value="lead_note">
                <input type="hidden" name="csrf" value="<?= e_a($csrf) ?>">
                <input type="hidden" name="id" value="<?= e_a($lead['id']) ?>">
                <input type="text" name="note" placeholder="Follow-up note…" style="flex:1" required>
                <button class="btn grad" type="submit">Add</button>
            </form>
            <ul class="notes-list">
                <?php foreach (array_reverse($lead['notes']) as $n): ?>
                <li><?= e_a($n['text']) ?><small><?= e_a($n['date']) ?></small></li>
                <?php endforeach; if (!$lead['notes']): ?><li style="color:var(--muted)">No notes yet.</li><?php endif; ?>
            </ul>
        </div>
        <?php endif; ?>

    <?php elseif ($view === 'blogs'): ?>
        <h1>Blog Posts</h1>
        <p class="sub"><?= count($GLOBALS['BLOG']) ?> posts, all editable · <a class="btn grad sm" href="/admin/?view=blog-edit">+ New post</a></p>
        <div class="panel">
            <table>
                <thead><tr><th>Title</th><th>Category</th><th>Intent</th><th>Date</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($GLOBALS['BLOG'] as $bslug => $b):
                    $is_builtin = in_array($bslug, $GLOBALS['BLOG_BUILTIN'] ?? [], true);
                    $is_edited  = isset($custom_posts[$bslug]);
                ?>
                    <tr>
                        <td>
                            <strong><?= e_a($b['h1']) ?></strong>
                            <?php if ($is_builtin && $is_edited): ?><span class="tag-ed">edited</span><?php endif; ?>
                            <br><small class="mono" style="color:var(--muted)"><?= e_a($bslug) ?></small>
                        </td>
                        <td><?= e_a($b['category']) ?></td>
                        <td><?= e_a($b['intent']) ?></td>
                        <td style="white-space:nowrap"><?= e_a($b['date'] ?? '') ?></td>
                        <td class="actions-row">
                            <a class="btn ghost sm" href="/blog/<?= e_a($bslug) ?>/" target="_blank">View</a>
                            <a class="btn sm" href="/admin/?view=blog-edit&slug=<?= e_a($bslug) ?>">Edit</a>
                            <?php if ($is_builtin && $is_edited): ?>
                            <form method="post" onsubmit="return confirm('Revert this post to its original shipped content?')" style="display:inline">
                                <input type="hidden" name="action" value="blog_delete">
                                <input type="hidden" name="csrf" value="<?= e_a($csrf) ?>">
                                <input type="hidden" name="slug" value="<?= e_a($bslug) ?>">
                                <button class="btn danger sm" type="submit">Revert</button>
                            </form>
                            <?php elseif (!$is_builtin): ?>
                            <form method="post" onsubmit="return confirm('Delete this post permanently?')" style="display:inline">
                                <input type="hidden" name="action" value="blog_delete">
                                <input type="hidden" name="csrf" value="<?= e_a($csrf) ?>">
                                <input type="hidden" name="slug" value="<?= e_a($bslug) ?>">
                                <button class="btn danger sm" type="submit">Delete</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php elseif ($view === 'blog-edit'):
        $edit_slug = $_GET['slug'] ?? '';
        // Built-ins load their shipped content, edited posts load their override.
        $editing = $GLOBALS['BLOG'][$edit_slug] ?? null;
    ?>
        <h1><?= $editing ? 'Edit blog post' : 'New blog post' ?></h1>
        <p class="sub"><a href="/admin/?view=blogs">← Back to posts</a></p>
        <form method="post" class="panel">
            <input type="hidden" name="action" value="blog_save">
            <input type="hidden" name="csrf" value="<?= e_a($csrf) ?>">
            <input type="hidden" name="orig_slug" value="<?= e_a($edit_slug) ?>">

            <div class="seo-group">
                <h3>SEO Settings</h3>
                <label>Page Title (SEO)</label>
                <input name="seo_title" value="<?= e_a($editing['title'] ?? '') ?>" placeholder="e.g. How to Rank in Google AI Overviews | VTurnU">
                <p class="seo-note">Recommended: 50-60 characters, include main keyword and brand</p>

                <label>Meta Description</label>
                <input name="meta_desc" value="<?= e_a($editing['meta'] ?? '') ?>" placeholder="150-160 characters">
                <p class="seo-note">Write for humans, include CTA or value proposition</p>
            </div>

            <label>H1 Heading *</label>
            <input name="title_h1" required value="<?= e_a($editing['h1'] ?? '') ?>" placeholder="Main headline visible on page">

            <label>Lede (AEO Answer Box) *</label>
            <textarea name="lede" required rows="3" placeholder="Direct 2-3 sentence answer to the H1 question, used for AI engines"><?= e_a($editing['lede'] ?? '') ?></textarea>

            <div class="grid3">
                <div>
                    <label>Category</label>
                    <select name="category"><?php foreach (BLOG_CATS as $c): ?><option<?= ($editing['category'] ?? '') === $c ? ' selected' : '' ?>><?= $c ?></option><?php endforeach; ?></select>
                </div>
                <div>
                    <label>Intent Type</label>
                    <select name="intent"><?php foreach (BLOG_INTENTS as $i): ?><option<?= ($editing['intent'] ?? '') === $i ? ' selected' : '' ?>><?= $i ?></option><?php endforeach; ?></select>
                </div>
                <div>
                    <label>Publication Date</label>
                    <input type="date" name="pub_date" value="<?= e_a($editing['date'] ?? date('Y-m-d')) ?>">
                </div>
            </div>

            <label>Read Time (e.g., "8 min")</label>
            <input name="read_time" value="<?= e_a($editing['read'] ?? '6 min') ?>">

            <label>Body Content *</label>
            <textarea name="body" rows="14" required placeholder="## Section Title&#10;First paragraph…&#10;&#10;Second paragraph…&#10;&#10;## Another Section&#10;- Bullet point&#10;- Another point"><?php if ($editing && !empty($editing['sections'])): foreach ($editing['sections'] as $s) { echo "## " . e_a($s[0]) . "\n"; foreach ($s[1] as $p) { echo e_a($p) . "\n"; } foreach ($s[2] as $l) { echo "- " . e_a($l) . "\n"; } echo "\n"; } else: echo ''; endif; ?></textarea>

            <div class="grid2">
                <div>
                    <label>Key Takeaways (one per line)</label>
                    <textarea name="takeaways" rows="4"><?= e_a(implode("\n", $editing['takeaways'] ?? [])) ?></textarea>
                </div>
                <div>
                    <label>FAQs (format: Question | Answer)</label>
                    <textarea name="faqs" rows="4" placeholder="How does AEO work? | Answer here&#10;Is SEO still important? | Yes, because…"><?= e_a(implode("\n", array_map(fn($f) => "{$f[0]} | {$f[1]}", $editing['faqs'] ?? []))) ?></textarea>
                </div>
            </div>

            <div class="seo-group">
                <h3>CTA Settings</h3>
                <label>CTA Heading</label>
                <input name="cta_head" value="<?= e_a($editing['cta'][0] ?? 'Ready to grow?') ?>">
                <label>CTA Supporting Text</label>
                <input name="cta_sub" value="<?= e_a($editing['cta'][1] ?? 'Get a free audit and honest next steps.') ?>">
                <label>CTA Button Label</label>
                <input name="cta_btn" value="<?= e_a($editing['cta'][2] ?? 'Get My Free Audit') ?>">
            </div>

            <br><button class="btn grad" type="submit"><?= $editing ? 'Save Changes' : 'Publish Post' ?></button>
        </form>

    <?php elseif ($view === 'cases'): ?>
        <h1>Case Studies</h1>
        <p class="sub"><?= count($GLOBALS['CASES']) ?> case studies, all editable · <a class="btn grad sm" href="/admin/?view=case-edit">+ New case</a></p>
        <div class="panel">
            <table>
                <thead><tr><th>Case</th><th>Industry</th><th>Result</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($GLOBALS['CASES'] as $cslug => $c):
                    $is_builtin = in_array($cslug, $GLOBALS['CASES_BUILTIN'] ?? [], true);
                    $is_edited  = isset($custom_cases[$cslug]);
                ?>
                    <tr>
                        <td>
                            <strong><?= e_a($c['h1']) ?></strong>
                            <?php if ($is_builtin && $is_edited): ?><span class="tag-ed">edited</span><?php endif; ?>
                            <br><small class="mono" style="color:var(--muted)"><?= e_a($cslug) ?></small>
                        </td>
                        <td><?= e_a($c['industry'] ?? '') ?></td>
                        <td><strong><?= e_a($c['results'][0][0] ?? '') ?></strong> <?= e_a($c['results'][0][1] ?? '') ?></td>
                        <td class="actions-row">
                            <a class="btn ghost sm" href="/case-studies/<?= e_a($cslug) ?>/" target="_blank">View</a>
                            <a class="btn sm" href="/admin/?view=case-edit&slug=<?= e_a($cslug) ?>">Edit</a>
                            <?php if ($is_builtin && $is_edited): ?>
                            <form method="post" onsubmit="return confirm('Revert this case study to its original content?')" style="display:inline">
                                <input type="hidden" name="action" value="case_delete">
                                <input type="hidden" name="csrf" value="<?= e_a($csrf) ?>">
                                <input type="hidden" name="slug" value="<?= e_a($cslug) ?>">
                                <button class="btn danger sm" type="submit">Revert</button>
                            </form>
                            <?php elseif (!$is_builtin): ?>
                            <form method="post" onsubmit="return confirm('Delete this case permanently?')" style="display:inline">
                                <input type="hidden" name="action" value="case_delete">
                                <input type="hidden" name="csrf" value="<?= e_a($csrf) ?>">
                                <input type="hidden" name="slug" value="<?= e_a($cslug) ?>">
                                <button class="btn danger sm" type="submit">Delete</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php elseif ($view === 'case-edit'):
        $edit_slug = $_GET['slug'] ?? '';
        $editing = $GLOBALS['CASES'][$edit_slug] ?? null;
    ?>
        <h1><?= $editing ? 'Edit case study' : 'New case study' ?></h1>
        <p class="sub"><a href="/admin/?view=cases">← Back to cases</a></p>
        <form method="post" class="panel">
            <input type="hidden" name="action" value="case_save">
            <input type="hidden" name="csrf" value="<?= e_a($csrf) ?>">
            <input type="hidden" name="orig_slug" value="<?= e_a($edit_slug) ?>">

            <div class="seo-group">
                <h3>SEO Settings</h3>
                <label>Page Title (SEO)</label>
                <input name="seo_title" value="<?= e_a($editing['title'] ?? '') ?>" placeholder="e.g. Case Study: Doubling Organic Leads for a Clinic">
                <p class="seo-note">Recommended: 55-63 characters, include key metric</p>

                <label>Meta Description</label>
                <input name="meta_desc" value="<?= e_a($editing['meta'] ?? '') ?>" placeholder="150-160 characters">
                <p class="seo-note">Write for humans, include the result or outcome</p>
            </div>

            <label>H1 Heading *</label>
            <input name="title_h1" required value="<?= e_a($editing['h1'] ?? '') ?>" placeholder="Case title: what did we achieve?">

            <label>Lede (Summary) *</label>
            <textarea name="lede" required rows="3" placeholder="2-3 sentence summary of the challenge, solution and result"><?= e_a($editing['lede'] ?? '') ?></textarea>

            <div class="grid3">
                <div>
                    <label>Client Industry *</label>
                    <input name="industry" required value="<?= e_a($editing['industry'] ?? '') ?>" placeholder="e.g. Healthcare, Ecommerce, SaaS">
                </div>
                <div>
                    <label>Service Category *</label>
                    <select name="service" required>
                        <option value="">Select service</option>
                        <option<?= ($editing['service'] ?? '') === 'seo' ? ' selected' : '' ?>>SEO & Organic</option>
                        <option<?= ($editing['service'] ?? '') === 'ppc' ? ' selected' : '' ?>>PPC & Paid Media</option>
                        <option<?= ($editing['service'] ?? '') === 'social' ? ' selected' : '' ?>>Social Media</option>
                        <option<?= ($editing['service'] ?? '') === 'web' ? ' selected' : '' ?>>Web Design</option>
                        <option<?= ($editing['service'] ?? '') === 'multi' ? ' selected' : '' ?>>Multi-channel</option>
                    </select>
                </div>
                <div>
                    <label>Project Date</label>
                    <input type="date" name="date" value="<?= e_a($editing['date'] ?? date('Y-m-d')) ?>">
                </div>
            </div>

            <label>Challenge (Problem) *</label>
            <textarea name="challenge" required rows="4" placeholder="What was the business challenge? What traffic/lead/revenue problem were they facing?"><?= e_a($editing['challenge'] ?? '') ?></textarea>

            <label>Solution (What We Did) *</label>
            <textarea name="solution" required rows="4" placeholder="How did we solve it? What strategy and tactics did we implement?"><?= e_a($editing['solution'] ?? '') ?></textarea>

            <label>Results & Metrics *</label>
            <p class="seo-note">Format: Metric | Value (one per line, e.g. "Organic Revenue | 2.4×")</p>
            <textarea name="results" required rows="4" placeholder="Organic Traffic | +156%&#10;Revenue | 2.4×&#10;Average Order Value | +34%"><?= e_a(implode("\n", array_map(fn($r) => "{$r[0]} | {$r[1]}", $editing['results'] ?? []))) ?></textarea>

            <label>Client Quote (Optional)</label>
            <p class="seo-note">Format: Quote text | Client name, title, company</p>
            <textarea name="quote" rows="2" placeholder=""The improvement was immediate and measurable." | John Doe, Marketing Director, Example Inc."><?= $editing && isset($editing['quote']) ? e_a($editing['quote'][0]) . ' | ' . e_a($editing['quote'][1] . ', ' . $editing['quote'][2]) : '' ?></textarea>

            <div class="seo-group">
                <h3>CTA Settings</h3>
                <label>CTA Heading</label>
                <input name="cta_head" value="<?= e_a($editing['cta'][0] ?? 'Ready for similar results?') ?>">
                <label>CTA Supporting Text</label>
                <input name="cta_sub" value="<?= e_a($editing['cta'][1] ?? 'Let\'s discuss your goals and build a plan.') ?>">
                <label>CTA Button Label</label>
                <input name="cta_btn" value="<?= e_a($editing['cta'][2] ?? 'Book a Discovery Call') ?>">
            </div>

            <br><button class="btn grad" type="submit"><?= $editing ? 'Save Changes' : 'Publish Case' ?></button>
        </form>

    <?php elseif ($view === 'resources'): ?>
        <h1>E-books & Guides</h1>
        <p class="sub"><?= count($GLOBALS['RESOURCES']) ?> resources, all editable · <a class="btn grad sm" href="/admin/?view=resource-edit">+ New resource</a></p>
        <div class="panel">
            <table>
                <thead><tr><th>Resource</th><th>Type</th><th>Category</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($GLOBALS['RESOURCES'] as $rslug => $r):
                    $hub = $r['type'] === 'guide' ? 'guides' : 'ebooks';
                    $is_builtin = in_array($rslug, $GLOBALS['RESOURCES_BUILTIN'] ?? [], true);
                    $is_edited  = isset($custom_resources[$rslug]);
                ?>
                    <tr>
                        <td>
                            <strong><?= e_a($r['h1']) ?></strong>
                            <?php if ($is_builtin && $is_edited): ?><span class="tag-ed">edited</span><?php endif; ?>
                            <br><small class="mono" style="color:var(--muted)"><?= e_a($rslug) ?></small>
                        </td>
                        <td><?= ucfirst($r['type']) ?></td>
                        <td><?= e_a($r['category'] ?? 'General') ?></td>
                        <td class="actions-row">
                            <a class="btn ghost sm" href="/<?= $hub ?>/<?= e_a($rslug) ?>/" target="_blank">View</a>
                            <a class="btn sm" href="/admin/?view=resource-edit&slug=<?= e_a($rslug) ?>">Edit</a>
                            <?php if ($is_builtin && $is_edited): ?>
                            <form method="post" onsubmit="return confirm('Revert this resource to its original content?')" style="display:inline">
                                <input type="hidden" name="action" value="resource_delete">
                                <input type="hidden" name="csrf" value="<?= e_a($csrf) ?>">
                                <input type="hidden" name="slug" value="<?= e_a($rslug) ?>">
                                <button class="btn danger sm" type="submit">Revert</button>
                            </form>
                            <?php elseif (!$is_builtin): ?>
                            <form method="post" onsubmit="return confirm('Delete this resource permanently?')" style="display:inline">
                                <input type="hidden" name="action" value="resource_delete">
                                <input type="hidden" name="csrf" value="<?= e_a($csrf) ?>">
                                <input type="hidden" name="slug" value="<?= e_a($rslug) ?>">
                                <button class="btn danger sm" type="submit">Delete</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php elseif ($view === 'resource-edit'):
        $edit_slug = $_GET['slug'] ?? '';
        $editing = $GLOBALS['RESOURCES'][$edit_slug] ?? null;
    ?>
        <h1><?= $editing ? 'Edit resource' : 'New resource' ?></h1>
        <p class="sub"><a href="/admin/?view=resources">← Back to resources</a></p>
        <form method="post" class="panel">
            <input type="hidden" name="action" value="resource_save">
            <input type="hidden" name="csrf" value="<?= e_a($csrf) ?>">
            <input type="hidden" name="orig_slug" value="<?= e_a($edit_slug) ?>">

            <div class="seo-group">
                <h3>SEO Settings</h3>
                <label>Page Title (SEO)</label>
                <input name="seo_title" value="<?= e_a($editing['title'] ?? '') ?>" placeholder="e.g. The Complete Guide to Local SEO | Rank in the Map Pack">
                <p class="seo-note">Recommended: 55-63 characters, include main topic</p>

                <label>Meta Description</label>
                <input name="meta_desc" value="<?= e_a($editing['meta'] ?? '') ?>" placeholder="150-160 characters">
                <p class="seo-note">Write for humans, include the value or outcome</p>
            </div>

            <label>H1 Heading *</label>
            <input name="title_h1" required value="<?= e_a($editing['h1'] ?? '') ?>" placeholder="Main title of the resource">

            <label>Lede (Summary) *</label>
            <textarea name="lede" required rows="3" placeholder="2-3 sentence summary of what readers will learn"><?= e_a($editing['lede'] ?? '') ?></textarea>

            <div class="grid3">
                <div>
                    <label>Resource Type *</label>
                    <select name="type" required>
                        <option value="">Select type</option>
                        <option<?= ($editing['type'] ?? '') === 'ebook' ? ' selected' : '' ?>>E-book</option>
                        <option<?= ($editing['type'] ?? '') === 'guide' ? ' selected' : '' ?>>Guide</option>
                    </select>
                </div>
                <div>
                    <label>Category *</label>
                    <input name="category" required value="<?= e_a($editing['category'] ?? '') ?>" placeholder="e.g. SEO, Paid Media, Web Design">
                </div>
                <div>
                    <label>Size (e.g. "48 pages") *</label>
                    <input name="size" required value="<?= e_a($editing['size'] ?? '') ?>" placeholder="e.g. 48 pages, 15 min read">
                </div>
            </div>

            <label>Description *</label>
            <textarea name="description" required rows="5" placeholder="Full description of the resource. What will readers learn? Who is it for? What problems does it solve?"><?= e_a($editing['description'] ?? '') ?></textarea>

            <label>Key Topics (one per line)</label>
            <textarea name="topics" rows="4" placeholder="Topic 1&#10;Topic 2&#10;Topic 3&#10;Topic 4"><?= e_a(implode("\n", $editing['topics'] ?? [])) ?></textarea>

            <label>Download URL / Link *</label>
            <input name="url" required value="<?= e_a($editing['url'] ?? '') ?>" placeholder="https://example.com/download or /assets/pdfs/guide.pdf">

            <label>Featured Image / Cover *</label>
            <input name="image" required value="<?= e_a($editing['image'] ?? '') ?>" placeholder="e.g. /assets/covers/guide-name.jpg or external URL">
            <p class="seo-note">Should be 1200×630 or square for best display</p>

            <div class="seo-group">
                <h3>CTA Settings</h3>
                <label>CTA Heading</label>
                <input name="cta_head" value="<?= e_a($editing['cta'][0] ?? 'Download Now') ?>">
                <label>CTA Supporting Text</label>
                <input name="cta_sub" value="<?= e_a($editing['cta'][1] ?? 'Get instant access to practical insights.') ?>">
                <label>CTA Button Label</label>
                <input name="cta_btn" value="<?= e_a($editing['cta'][2] ?? 'Get the Guide') ?>">
            </div>

            <br><button class="btn grad" type="submit"><?= $editing ? 'Save Changes' : 'Publish Resource' ?></button>
        </form>

    <?php elseif ($view === 'settings'): ?>
        <h1>Settings</h1>
        <p class="sub">Admin credentials and configuration.</p>
        <div class="panel" style="max-width:500px">
            <h2>Credentials</h2>
            <form method="post">
                <input type="hidden" name="action" value="password">
                <input type="hidden" name="csrf" value="<?= e_a($csrf) ?>">
                <label>Username</label>
                <input name="user" value="<?= e_a($auth['user']) ?>">
                <label>Current Password *</label>
                <input name="current" type="password" required autocomplete="current-password">
                <label>New Password * (min 8 chars)</label>
                <input name="new" type="password" required minlength="8" autocomplete="new-password">
                <br><br><button class="btn grad" type="submit">Update Credentials</button>
            </form>
        </div>
        <div class="panel" style="max-width:500px">
            <h2>Data & Backups</h2>
            <p style="font-size:.84rem;color:var(--muted);margin-bottom:12px">Leads stored in <span class="mono">storage/enquiries.jsonl</span> | CRM state in <span class="mono">leads-meta.json</span> | Custom content in <span class="mono">blog-custom.json</span>, <span class="mono">cases-custom.json</span>, <span class="mono">resources-custom.json</span>. Back these up regularly.</p>
            <a class="btn ghost" href="/admin/?view=export">Download Leads (CSV)</a>
        </div>

    <?php else: ?>
        <h1>Not found</h1>
        <p class="sub"><a href="/admin/">← Dashboard</a></p>
    <?php endif; ?>
    </main>
</div>
<?php endif; ?>
</body>
</html>
