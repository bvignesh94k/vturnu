<?php
/**
 * VTurnU Admin: leads CRM + content manager with full editing.
 * Routed from index.php for any /admin/ URL. Stateless-cookie auth,
 * CSRF-protected POSTs. Storage: Postgres (admin_users, enquiries,
 * content_overrides, see db/schema.sql), not local files.
 *
 * Vercel's filesystem is not writable between requests, so there is no PHP
 * session here: auth is a signed, expiring cookie instead (same HMAC pattern
 * as csrf_token()), verified on every request rather than
 * looked up from server-side state.
 *
 */

declare(strict_types=1);

const ADMIN_COOKIE = 'vt_admin';
// Idle timeout: 2 hours. Every authenticated request reissues the cookie
// with a fresh expiry, so this behaves like the old session idle-timeout
// (logged out after 2 hours of inactivity) rather than a hard 2-hour cap.
const ADMIN_SESSION_TTL = 7200;

/** Issue (or renew) the signed admin session cookie for $user. */
function admin_issue_session(string $user): void
{
    $exp = time() + ADMIN_SESSION_TTL;
    $payload = $user . '|' . $exp;
    $sig = hash_hmac('sha256', $payload, SECURITY_SECRET);
    $token = rtrim(strtr(base64_encode($payload . '|' . $sig), '+/', '-_'), '=');
    setcookie(ADMIN_COOKIE, $token, [
        'expires' => 0, // a browser-session cookie; ADMIN_SESSION_TTL is what actually governs validity
        'path' => '/admin/',
        'secure' => !empty($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
}

/** The logged-in username, or null if there is no valid session cookie. */
function admin_verify_session(): ?string
{
    $token = $_COOKIE[ADMIN_COOKIE] ?? '';
    if ($token === '') {
        return null;
    }
    $raw = base64_decode(strtr($token, '-_', '+/'), true);
    if ($raw === false) {
        return null;
    }
    $parts = explode('|', $raw);
    if (count($parts) !== 3) {
        return null;
    }
    [$user, $exp, $sig] = $parts;
    $expected = hash_hmac('sha256', $user . '|' . $exp, SECURITY_SECRET);
    if (!hash_equals($expected, $sig) || (int) $exp < time()) {
        return null;
    }
    return $user;
}

function admin_clear_session(): void
{
    setcookie(ADMIN_COOKIE, '', [
        'expires' => time() - 3600,
        'path' => '/admin/',
        'secure' => !empty($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
}

const ADMIN_STATUSES = ['new', 'contacted', 'qualified', 'proposal', 'won', 'lost'];
const ADMIN_PRIORITIES = ['low', 'normal', 'high'];

/* Contacts arrive from three distinct funnels and are worked differently, so
   the admin separates them rather than pooling every submission into one list.
   Anything not matched here falls into sales leads, which is the safe default:
   a new form should surface as a lead rather than disappear into a sub-list. */
const ADMIN_SEG_AUDIT  = ['seo-audit', 'audit-consultation'];
const ADMIN_SEG_EBOOK  = ['resource'];
const ADMIN_SEG_SIGNUP = ['newsletter'];

/** Which admin section a lead belongs to: audit, ebook, newsletter or lead. */
function lead_segment(array $l): string {
    $s = $l['source'] ?? '';
    if (in_array($s, ADMIN_SEG_AUDIT, true))  return 'audit';
    if (in_array($s, ADMIN_SEG_EBOOK, true))  return 'ebook';
    if (in_array($s, ADMIN_SEG_SIGNUP, true)) return 'newsletter';
    return 'lead';
}

/** Filter a lead list down to one segment. */
function leads_in(array $leads, string $segment): array {
    return array_values(array_filter($leads, fn($l) => lead_segment($l) === $segment));
}
/* Statuses that take a deal out of the open pipeline. */
const ADMIN_CLOSED = ['won', 'lost'];
const BLOG_INTENTS = ['Informational', 'Commercial Investigation', 'How-to / Tutorial', 'Buying Guide', 'Comparison', 'Problem and Solution', 'Industry Trends', 'Use Cases', 'Benefits', 'FAQs', 'Best Practices', 'Case Study', 'Cost & Pricing', 'Trends & Innovations', 'Mistakes to Avoid'];
const BLOG_CATS = ['Strategy', 'SEO', 'AI Search', 'Paid Media', 'Content', 'Social', 'Web', 'Conversion', 'Innovation', 'Case Study'];
const INDUSTRIES = ['Jewelry', 'SaaS & Tech', 'Healthcare', 'Real Estate', 'Ecommerce', 'Food & Hospitality', 'Legal Services', 'Fashion', 'Manufacturing', 'Education'];

/* ---------- Content override storage (Postgres content_overrides table) ---------- */

function admin_get_overrides(string $type): array {
    try {
        $stmt = db()->prepare('SELECT slug, data FROM content_overrides WHERE content_type = ?');
        $stmt->execute([$type]);
        $out = [];
        foreach ($stmt->fetchAll() as $row) { $out[$row['slug']] = json_decode($row['data'], true); }
        return $out;
    } catch (Throwable $e) {
        error_log('admin_get_overrides(' . $type . '): ' . $e->getMessage());
        return [];
    }
}
function admin_save_override(string $type, string $slug, array $data): void {
    db()->prepare(
        'INSERT INTO content_overrides (content_type, slug, data, updated_at) VALUES (?, ?, ?::jsonb, now())
         ON CONFLICT (content_type, slug) DO UPDATE SET data = excluded.data, updated_at = now()'
    )->execute([$type, $slug, json_encode($data, JSON_UNESCAPED_UNICODE)]);
}
function admin_delete_override(string $type, string $slug): void {
    db()->prepare('DELETE FROM content_overrides WHERE content_type = ? AND slug = ?')->execute([$type, $slug]);
}

/* Auth: admin_users table, auto-seeded with the default login on first run,
   exactly like the old admin.json did. */
function admin_load_auth(): array {
    try {
        $row = db()->query('SELECT username, password_hash FROM admin_users ORDER BY id ASC LIMIT 1')->fetch();
        if ($row) {
            return ['user' => $row['username'], 'hash' => $row['password_hash']];
        }
    } catch (Throwable $e) {
        /* Database unreachable: refuse every login. Falling through to the seed
           below would accept the default password during an outage. */
        error_log('admin_load_auth: ' . $e->getMessage());
        return ['user' => '', 'hash' => '!'];
    }
    $seed = ['user' => 'admin', 'hash' => password_hash('vturnu@admin', PASSWORD_DEFAULT)];
    try {
        db()->prepare('INSERT INTO admin_users (username, password_hash) VALUES (?, ?) ON CONFLICT (username) DO NOTHING')
            ->execute([$seed['user'], $seed['hash']]);
    } catch (Throwable $e) {
        error_log('admin_load_auth seed: ' . $e->getMessage());
    }
    return $seed;
}
$auth = admin_load_auth();

/* ---------- Leads (Postgres enquiries table) ---------- */

/** Real leads by default. Pass true for the leads the quality screen held back. */
function admin_load_leads(bool $junk = false): array {
    try {
        $rows = db()->query(
            "SELECT id, created_at, source, name, email, mobile, company, designation, service, budget,
                    message, status, notes, value, priority, owner,
                    to_char(followup, 'YYYY-MM-DD') AS followup, tags, activity,
                    channel, channel_group, attribution, form_page, country, grade, score, contacted_at
             FROM enquiries WHERE deleted_at IS NULL AND status " . ($junk ? "= 'junk'" : "<> 'junk'") . "
             ORDER BY created_at DESC"
        )->fetchAll();
    } catch (Throwable $e) {
        error_log('admin_load_leads: ' . $e->getMessage());
        return [];
    }
    foreach ($rows as &$l) {
        $l['id'] = (string) $l['id'];
        $l['date'] = date('c', strtotime($l['created_at']));
        $l['notes'] = json_decode($l['notes'] ?? '[]', true) ?: [];
        $l['activity'] = json_decode($l['activity'] ?? '[]', true) ?: [];
        $l['followup'] = $l['followup'] ?? '';
        $l['attribution'] = json_decode($l['attribution'] ?? '{}', true) ?: [];
        $l['channel'] = (string) ($l['channel'] ?? '') ?: 'Not tracked';
        $l['channel_group'] = (string) ($l['channel_group'] ?? '') ?: 'Not tracked';
        $l['grade'] = (string) ($l['grade'] ?? '');
    }
    return $rows;
}

/** Append one entry to a lead's activity timeline. */
function admin_log_activity(string $id, string $text): void {
    if (!ctype_digit($id)) { return; }
    $stmt = db()->prepare('SELECT activity FROM enquiries WHERE id = ?');
    $stmt->execute([(int) $id]);
    $row = $stmt->fetch();
    $log = $row ? (json_decode($row['activity'] ?? '[]', true) ?: []) : [];
    $log[] = ['date' => date('Y-m-d H:i'), 'text' => $text];
    // Keep the timeline bounded so the column cannot grow without limit.
    if (count($log) > 200) { $log = array_slice($log, -200); }
    db()->prepare('UPDATE enquiries SET activity = ?::jsonb WHERE id = ?')
        ->execute([json_encode($log, JSON_UNESCAPED_UNICODE), (int) $id]);
}

function admin_set_lead_status(string $id, string $status): void {
    if (!ctype_digit($id)) { return; }
    db()->prepare('UPDATE enquiries SET status = ? WHERE id = ? AND deleted_at IS NULL')
        ->execute([$status, (int) $id]);
}

function admin_get_lead_status(string $id): ?string {
    if (!ctype_digit($id)) { return null; }
    $stmt = db()->prepare('SELECT status FROM enquiries WHERE id = ?');
    $stmt->execute([(int) $id]);
    $row = $stmt->fetch();
    return $row ? $row['status'] : null;
}

function admin_update_lead_deal(string $id, array $fields): void {
    if (!ctype_digit($id)) { return; }
    db()->prepare('UPDATE enquiries SET value = ?, priority = ?, owner = ?, followup = ?, tags = ? WHERE id = ? AND deleted_at IS NULL')
        ->execute([$fields['value'], $fields['priority'], $fields['owner'], $fields['followup'] !== '' ? $fields['followup'] : null, $fields['tags'], (int) $id]);
}

function admin_append_lead_note(string $id, string $text): void {
    if (!ctype_digit($id)) { return; }
    $stmt = db()->prepare('SELECT notes FROM enquiries WHERE id = ?');
    $stmt->execute([(int) $id]);
    $row = $stmt->fetch();
    $notes = $row ? (json_decode($row['notes'] ?? '[]', true) ?: []) : [];
    $notes[] = ['date' => date('Y-m-d H:i'), 'text' => $text];
    db()->prepare('UPDATE enquiries SET notes = ?::jsonb WHERE id = ?')
        ->execute([json_encode($notes, JSON_UNESCAPED_UNICODE), (int) $id]);
}

/** Soft delete: Postgres already has its own durability, so unlike the old
 *  file-based version there is no need for a backup-before-delete dance. */
function admin_delete_lead(string $id): void {
    if (!ctype_digit($id)) { return; }
    db()->prepare('UPDATE enquiries SET deleted_at = now() WHERE id = ?')->execute([(int) $id]);
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

$logged_in = admin_verify_session() !== null;
$csrf = csrf_token();
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
            admin_issue_session($auth['user']);
            header('Location: /admin/'); exit;
        } else {
            security_login_fail();
            $flash = 'Wrong username or password.';
        }
    } elseif (!$logged_in) {
        http_response_code(403); $flash = 'Session expired. Please log in again.';
    } elseif (!csrf_verify($_POST['csrf'] ?? '')) {
        http_response_code(403); $flash = 'Invalid request token. Try again.';
    } else {
        // Any authenticated action extends the idle-timeout window, matching
        // the old session's "log out after 2 hours of inactivity" behaviour.
        admin_issue_session($auth['user']);
        switch ($action) {
            case 'logout':
                admin_clear_session();
                header('Location: /admin/'); exit;

            /* A false positive: back into the pipeline, and on to the sales inbox
               it never reached. */
            case 'lead_restore':
                $lid = $_POST['id'] ?? '';
                foreach (admin_load_leads(true) as $l) {
                    if ($l['id'] === $lid) {
                        db()->prepare("UPDATE enquiries SET status = 'new', tags = '' WHERE id = ?")->execute([(int) $lid]);
                        admin_log_activity($lid, 'Restored from Junk by ' . $auth['user']);
                        mail_enquiry(['date' => $l['date']] + $l);
                        $flash = 'Lead restored and sent to ' . ENQUIRY_TO . '.';
                        break;
                    }
                }
                break;

            /* One pass over untouched older leads, for the junk that arrived before
               the filter existed. Leads someone has already worked are left alone. */
            case 'junk_recheck':
                $moved = 0;
                foreach (admin_load_leads() as $l) {
                    if ($l['status'] !== 'new') {
                        continue;
                    }
                    $check = lead_screen($l, (string) $l['source'], false);
                    if ($check['verdict'] === 'junk') {
                        db()->prepare("UPDATE enquiries SET status = 'junk', tags = 'auto-junk' WHERE id = ?")->execute([(int) $l['id']]);
                        admin_log_activity($l['id'], 'Moved to Junk by re-check: ' . implode('; ', $check['reasons']));
                        $moved++;
                    }
                }
                $flash = $moved . ' older ' . ($moved === 1 ? 'lead' : 'leads') . ' moved to Junk.';
                break;

            case 'lead_status':
                $st = $_POST['status'] ?? 'new';
                $lid = $_POST['id'] ?? '';
                if (in_array($st, ADMIN_STATUSES, true) && $lid !== '') {
                    $prev = admin_get_lead_status($lid) ?? 'new';
                    admin_set_lead_status($lid, $st);
                    if ($prev !== $st) {
                        admin_log_activity($lid, 'Status: ' . ucfirst($prev) . ' to ' . ucfirst($st));
                    }
                    /* Leaving "new" is the first reply, which the response-time figures count from. */
                    if ($prev === 'new' && $st !== 'new' && ctype_digit($lid)) {
                        db()->prepare('UPDATE enquiries SET contacted_at = coalesce(contacted_at, now()) WHERE id = ?')->execute([(int) $lid]);
                    }
                    $flash = 'Status updated.';
                }
                break;

            /* A proposal or follow-up written in the composer, sent from connect@vturnu.com. */
            case 'proposal_send':
                $lid = (string) ($_POST['id'] ?? '');
                $target = ctype_digit($lid) ? crm_lead((int) $lid) : null;
                $kind = ($_POST['kind'] ?? '') === 'followup' ? 'followup' : 'proposal';
                if (!$target) {
                    $flash = 'Lead not found.';
                    break;
                }
                $parent = null;
                foreach (crm_proposals((int) $lid) as $pp) {
                    if ($pp['kind'] === 'proposal') { $parent = $pp; break; }
                }
                $sent = crm_send($target, $kind, (string) ($_POST['subject'] ?? ''), (string) ($_POST['body'] ?? ''), [
                    'stage' => $kind === 'proposal' && !empty($_POST['stage']),
                    'remind_days' => !empty($_POST['remind']) ? max(1, min(30, (int) ($_POST['remind_days'] ?? 3))) : 0,
                    'copy' => !empty($_POST['copy']),
                    'by' => $auth['user'],
                    'link_token' => $kind === 'followup' ? (string) ($parent['token'] ?? '') : '',
                    'parent_id' => $kind === 'followup' && $parent ? (int) $parent['id'] : 0,
                ]);
                if ($sent['ok'] && $sent['error'] === '') {
                    header('Location: /admin/?view=lead&id=' . $lid . '&sent=' . $kind); exit;
                }
                $flash = $sent['ok'] ? $sent['error'] : 'Not sent: ' . $sent['error'];
                if (!$sent['ok']) { $composer_post = $_POST; }
                break;

            case 'crm_settings':
                crm_save_settings($_POST);
                $flash = 'CRM settings saved.';
                break;

            case 'digest_now':
                $d = crm_daily_digest(true);
                $flash = $d['sent'] ? 'Digest sent to ' . ENQUIRY_TO . '.' : 'The digest could not be sent. Check that email is set up.';
                break;

            case 'lead_update':
                $lid = $_POST['id'] ?? '';
                if ($lid !== '') {
                    $prio = $_POST['priority'] ?? 'normal';
                    // Strip everything but digits so "₹50,000" and "50000" both store cleanly.
                    $val = preg_replace('/[^0-9]/', '', (string) ($_POST['value'] ?? ''));
                    $fup = trim($_POST['followup'] ?? '');
                    if ($fup !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fup)) { $fup = ''; }
                    admin_update_lead_deal($lid, [
                        'value'    => $val,
                        'priority' => in_array($prio, ADMIN_PRIORITIES, true) ? $prio : 'normal',
                        'owner'    => trim($_POST['owner'] ?? ''),
                        'followup' => $fup,
                        'tags'     => trim($_POST['tags'] ?? ''),
                    ]);
                    admin_log_activity($lid, 'Deal details updated');
                    $flash = 'Lead details saved.';
                }
                break;

            case 'lead_note':
                $note = trim($_POST['note'] ?? '');
                if ($note !== '') {
                    $id = $_POST['id'] ?? '';
                    admin_append_lead_note($id, $note);
                    admin_log_activity($id, 'Note: ' . mb_substr($note, 0, 60));
                    $flash = 'Note added.';
                }
                break;

            case 'lead_delete':
                admin_delete_lead($_POST['id'] ?? '');
                header('Location: /admin/?view=leads'); exit;

            case 'blog_save':
                $custom = admin_get_overrides('blog');
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
                admin_save_override('blog', $bslug, $custom[$bslug]);
                header('Location: /admin/?view=blogs&saved=' . urlencode($bslug)); exit;

            case 'blog_delete':
                admin_delete_override('blog', $_POST['slug'] ?? '');
                header('Location: /admin/?view=blogs'); exit;

            case 'case_save':
                $custom = admin_get_overrides('case');
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
                admin_save_override('case', $cslug, $custom[$cslug]);
                header('Location: /admin/?view=cases&saved=' . urlencode($cslug)); exit;

            case 'case_delete':
                admin_delete_override('case', $_POST['slug'] ?? '');
                header('Location: /admin/?view=cases'); exit;

            case 'resource_save':
                $custom = admin_get_overrides('resource');
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
                admin_save_override('resource', $rslug, $custom[$rslug]);
                header('Location: /admin/?view=resources&saved=' . urlencode($rslug)); exit;

            case 'resource_delete':
                admin_delete_override('resource', $_POST['slug'] ?? '');
                header('Location: /admin/?view=resources'); exit;

            case 'password':
                $cur = (string) ($_POST['current'] ?? '');
                $new = (string) ($_POST['new'] ?? '');
                if (!password_verify($cur, $auth['hash'])) { $flash = 'Current password is wrong.'; }
                elseif (strlen($new) < 8) { $flash = 'New password must be at least 8 characters.'; }
                else {
                    $newHash = password_hash($new, PASSWORD_DEFAULT);
                    $newUser = trim($_POST['user'] ?? '') !== '' ? trim($_POST['user']) : $auth['user'];
                    db()->prepare('UPDATE admin_users SET username = ?, password_hash = ?, updated_at = now() WHERE username = ?')
                        ->execute([$newUser, $newHash, $auth['user']]);
                    $auth['user'] = $newUser;
                    $auth['hash'] = $newHash;
                    // The session cookie is bound to the username, so a
                    // rename needs a fresh cookie or the old one stops verifying.
                    admin_issue_session($newUser);
                    $flash = 'Credentials updated.';
                }
                break;
        }
    }
    $logged_in = admin_verify_session() !== null;
}

/* CSV export */
if ($logged_in && $view === 'export') {
    // Export the section you were looking at, not always everything.
    $seg = $_GET['seg'] ?? '';
    $segName = in_array($seg, ['lead', 'audit', 'ebook', 'newsletter'], true) ? $seg : 'all';
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="vturnu-' . $segName . '-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Date', 'Status', 'Priority', 'Owner', 'Deal value', 'Next follow-up', 'Tags',
                   'Form', 'Channel', 'Channel group', 'Campaign or referrer', 'First landing page', 'Form page', 'Country', 'Grade', 'Proposal',
                   'Name', 'Email', 'Phone', 'Company', 'Service', 'Budget', 'Message', 'Notes']);
    $rows = admin_load_leads();
    $pidx = crm_proposal_index();
    if ($segName !== 'all') { $rows = leads_in($rows, $segName); }
    foreach ($rows as $l) {
        $notes = implode(' | ', array_map(fn($n) => ($n['date'] ?? '') . ' ' . ($n['text'] ?? ''), $l['notes'] ?? []));
        $a = $l['attribution'];
        fputcsv($out, [
            $l['date'] ?? '', $l['status'], $l['priority'], $l['owner'], $l['value'], $l['followup'], $l['tags'],
            $l['source'] ?? '', $l['channel'], $l['channel_group'], (string) ($a['detail'] ?? ''), (string) ($a['first']['landing'] ?? ''),
            $l['form_page'] ?? '', $l['country'] ?? '', $l['grade'], crm_proposal_state($pidx[$l['id']] ?? null),
            $l['name'] ?? '', $l['email'] ?? '', $l['mobile'] ?? '', $l['company'] ?? '',
            $l['service'] ?? '', $l['budget'] ?? '', $l['message'] ?? '', $notes,
        ]);
    }
    exit;
}

/* Data for views */
$leads = $logged_in ? admin_load_leads() : [];
$junk_leads = $logged_in ? admin_load_leads(true) : [];
/* The latest proposal for each lead, so lists and the dashboard can show where it stands. */
$proposal_index = $logged_in ? crm_proposal_index() : [];
$custom_posts = admin_get_overrides('blog');
$custom_cases = admin_get_overrides('case');
$custom_resources = admin_get_overrides('resource');

function e_a($v): string { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }

/* Pipeline colours walk the logo gradient from cyan to orange as a deal
   progresses, so the board reads left to right as heat. */
function status_color(string $s): string {
    return [
        'new' => '#00B8D9', 'contacted' => '#4338CA', 'qualified' => '#5B56C9',
        'proposal' => '#FF3D8A', 'won' => '#12A150', 'lost' => '#9A93BC',
    ][$s] ?? '#9A93BC';
}
function status_badge(string $s): string {
    return '<span class="badge-st" style="--st:' . status_color($s) . '">' . e_a(ucfirst($s)) . '</span>';
}
function priority_badge(string $p): string {
    $c = ['high' => '#FF3D8A', 'normal' => '#5B56C9', 'low' => '#9A93BC'][$p] ?? '#9A93BC';
    return '<span class="badge-st" style="--st:' . $c . '">' . e_a(ucfirst($p)) . '</span>';
}
/* Channel groups in the order the dashboard lists them, each with its colour. */
const CRM_GROUP_COLORS = [
    'Paid ads' => '#FF3D8A', 'Organic search' => '#12A150', 'AI assistants' => '#4338CA', 'Social' => '#00B8D9', 'Referral' => '#FF9100',
    'Email' => '#5B56C9', 'Campaigns' => '#B0388A', 'Direct' => '#6F6992', 'Unknown' => '#9A93BC', 'Not tracked' => '#B9B4D0',
];
/** Where a lead came from, as a coloured chip; the campaign or referrer goes in the tooltip. */
function channel_badge(array $l): string {
    $c = CRM_GROUP_COLORS[$l['channel_group'] ?? ''] ?? '#9A93BC';
    $tip = trim((string) ($l['attribution']['detail'] ?? ''));
    return '<span class="badge-st src" style="--st:' . $c . '"' . ($tip !== '' ? ' title="' . e_a($tip) . '"' : '') . '>' . e_a($l['channel'] ?? 'Not tracked') . '</span>';
}
function grade_badge(string $g): string {
    if ($g === '') return '<span style="color:var(--muted)">-</span>';
    $c = ['A' => '#12A150', 'B' => '#FF9100', 'C' => '#9A93BC'][$g] ?? '#9A93BC';
    return '<span class="badge-st grade" style="--st:' . $c . '" title="Lead grade from budget, size, timeline, website and email">' . e_a($g) . '</span>';
}
/** Where the latest proposal stands: Sent, Opened, Read or Said yes. */
function proposal_badge(?array $p): string {
    $state = crm_proposal_state($p);
    if ($state === 'none') return '<span style="color:var(--muted)">Not sent</span>';
    [$label, $c] = ['sent' => ['Sent', '#5B56C9'], 'opened' => ['Opened', '#00B8D9'], 'viewed' => ['Read', '#FF9100'], 'accepted' => ['Said yes', '#12A150']][$state];
    $tip = 'Sent ' . date('j M', strtotime((string) $p['sent_at'])) . ((int) ($p['sent_count'] ?? 1) > 1 ? ', ' . (int) $p['sent_count'] . ' emails in all' : '');
    return '<span class="badge-st" style="--st:' . $c . '" title="' . e_a($tip) . '">' . $label . '</span>';
}
/** How long a new lead has waited for a first reply. */
function waiting_for(array $l): string {
    $h = (int) floor((time() - strtotime((string) ($l['date'] ?? 'now'))) / 3600);
    return $h < 1 ? 'under an hour' : ($h < 48 ? $h . 'h' : (int) floor($h / 24) . ' days');
}
/** The visitor's phone as digits for wa.me and tel: links. */
function phone_digits(string $mobile): string {
    return (string) preg_replace('/\D/', '', $mobile);
}

/** Indian-format money, e.g. 250000 -> 2,50,000. */
function money_fmt($v): string {
    $n = (int) preg_replace('/[^0-9]/', '', (string) $v);
    if ($n <= 0) return '';
    $s = (string) $n;
    if (strlen($s) <= 3) return $s;
    $last3 = substr($s, -3);
    $rest = substr($s, 0, -3);
    return preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest) . ',' . $last3;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>VTurnU Admin<?= $logged_in ? ' | ' . e_a(ucfirst($view)) : '' ?></title>
<link rel="icon" type="image/svg+xml" href="/assets/img/vturnu-icon-mark.svg">
<style>
:root { --ink:#1D1936; --line:#E5E3F5; --soft:#4A4470; --muted:#6F6992; --bg:#F7F6FD;
        --y:#FF9100; --p:#FF3D8A; --c:#00B8D9; --v:#4338CA;
        --grad:linear-gradient(120deg,#00B8D9,#4338CA 40%,#FF3D8A 72%,#FF9100 100%);
        --grad-v:linear-gradient(160deg,#00B8D9,#4338CA); --grad-u:linear-gradient(160deg,#FF3D8A,#FF9100); }
* { margin:0; padding:0; box-sizing:border-box; }
body { font:15px/1.6 'Poppins','Segoe UI',system-ui,sans-serif; background:var(--bg); color:var(--ink); }
a { color:inherit; text-decoration:none; }
.layout { display:grid; grid-template-columns:220px 1fr; min-height:100vh; }
.side { background:var(--ink); color:#C9CBD1; padding:20px 14px; display:flex; flex-direction:column; gap:3px; }
.side-brand { display:flex; align-items:center; gap:8px; font-weight:800; color:#fff; font-size:1.05rem; margin-bottom:3px; padding:0 10px; }
.side-brand img { height:26px; width:auto; flex:none; }
.side-brand .bw-v { background:var(--grad-v); -webkit-background-clip:text; background-clip:text; color:transparent; }
.side-brand .bw-u { background:var(--grad-u); -webkit-background-clip:text; background-clip:text; color:transparent; }
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
.kpi-link { text-decoration:none; transition:transform .12s ease, box-shadow .12s ease; }
.kpi-link:hover { transform:translateY(-2px); box-shadow:0 8px 20px rgba(29,25,54,.1); }
.kpi b { display:block; font-size:1.6rem; letter-spacing:-.02em; }
.kpi span { font-size:.76rem; color:var(--muted); }
.kpi-link { display:block; text-decoration:none; transition:transform .12s ease, box-shadow .12s ease; }
.kpi-link:hover { transform:translateY(-2px); box-shadow:0 8px 20px rgba(29,25,54,.1); border-color:var(--v); }
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
/* ---------- CRM: pipeline board, badges, timeline ---------- */
.barline { display:block; height:8px; border-radius:999px; background:var(--line); overflow:hidden; }
.barline i { display:block; height:100%; border-radius:999px; }
.chk { display:inline-flex; align-items:center; gap:6px; font-size:.82rem; font-weight:600; color:var(--soft); white-space:nowrap; }
.chk input { width:auto; }
.tag-hi { font-size:.6rem; font-weight:800; text-transform:uppercase; letter-spacing:.05em; color:#fff; background:#FF3D8A; border-radius:999px; padding:2px 7px; vertical-align:middle; }
.tag-list { color:var(--muted); font-size:.68rem; }
.due-flag { color:#D62C74; font-weight:800; }
.timeline { list-style:none; display:grid; gap:0; }
.timeline li { position:relative; padding:0 0 14px 18px; border-left:2px solid var(--line); }
.timeline li:last-child { border-left-color:transparent; padding-bottom:0; }
.timeline li::before { content:""; position:absolute; left:-6px; top:4px; width:10px; height:10px; border-radius:50%; background:var(--grad-v); }
.timeline span { display:block; font-size:.84rem; }
.timeline small { color:var(--muted); font-size:.68rem; }

.board { display:grid; grid-template-columns:repeat(6,minmax(180px,1fr)); gap:12px; align-items:start; overflow-x:auto; padding-bottom:8px; }
.board-col { background:#fff; border:1px solid var(--line); border-radius:12px; padding:10px; min-height:90px; }
.board-head { display:flex; flex-direction:column; gap:2px; padding:4px 6px 10px; border-bottom:2px solid var(--st); margin-bottom:10px; }
.board-head strong { font-size:.84rem; }
.board-head span { font-size:.68rem; color:var(--muted); font-weight:700; }
.board-card { display:block; background:var(--bg); border:1px solid var(--line); border-left:3px solid var(--st,var(--line)); border-radius:9px; padding:9px 10px; margin-bottom:8px; transition:transform .12s ease, box-shadow .12s ease; }
.board-card:hover { transform:translateY(-2px); box-shadow:0 6px 16px rgba(29,25,54,.1); }
.board-card strong { display:block; font-size:.82rem; }
.board-card em { display:block; font-style:normal; font-size:.7rem; color:var(--soft); }
.board-card > span { display:block; font-size:.68rem; color:var(--muted); margin-top:2px; }
.board-meta { display:flex; flex-wrap:wrap; gap:6px; margin-top:6px; align-items:center; }
.board-meta b { font-size:.72rem; }
.board-meta i { font-style:normal; font-size:.62rem; font-weight:800; color:var(--muted); border:1px solid var(--line); border-radius:999px; padding:1px 7px; }
.board-meta i.pri-high { color:#fff; background:#FF3D8A; border-color:#FF3D8A; }
.board-meta i.due { color:#fff; background:#D62C74; border-color:#D62C74; }
.board-empty { color:var(--muted); font-size:.74rem; text-align:center; padding:10px 0; }

/* ---------- Leads dashboard, sources and proposals ---------- */
.badge-st.src { font-weight:700; }
.badge-st.grade { min-width:24px; text-align:center; }
.kpi small { display:block; font-size:.72rem; color:var(--muted); margin-top:2px; }
.kpi.hot { border-color:rgba(255,61,138,.45); background:linear-gradient(180deg,#FFF5F9,#fff); }
.kpi.hot b { color:#D62C74; }
.attn td { vertical-align:middle; }
.attn .why { font-size:.72rem; font-weight:800; white-space:nowrap; }
.split { display:grid; grid-template-columns:1.25fr 1fr; gap:16px; align-items:start; }
.split > .panel { margin:0; }
.group-row td { padding:7px 10px; }
.group-row .chips { display:flex; flex-wrap:wrap; gap:5px; margin-top:4px; }
.group-row .chips span { font-size:.68rem; color:var(--soft); background:var(--bg); border:1px solid var(--line); border-radius:999px; padding:1px 8px; }
.days { display:grid; grid-template-columns:repeat(14,1fr); gap:6px; align-items:end; height:120px; padding-top:8px; }
.days div { display:flex; flex-direction:column; align-items:center; justify-content:flex-end; height:100%; gap:4px; }
.days i { display:block; width:100%; min-height:3px; border-radius:6px 6px 2px 2px; background:var(--grad-v); }
.days small { font-size:.6rem; color:var(--muted); }
.days b { font-size:.66rem; }
.kv td:first-child { width:150px; color:var(--muted); }
.lead-actions { display:flex; flex-wrap:wrap; gap:8px; margin:6px 0 18px; }
.lead-actions .btn { text-decoration:none; }
.btn.wa { background:#E7F8EE; color:#11733E; }
.quote { border-left:3px solid var(--p); background:#FFF7FA; padding:9px 12px; border-radius:0 8px 8px 0; font-size:.88rem; margin:6px 0 10px; }
.prop-list { list-style:none; display:grid; gap:9px; }
.prop-list li { border:1px solid var(--line); border-radius:10px; padding:10px 12px; background:#FBFAF6; }
.prop-list strong { display:block; font-size:.86rem; }
.prop-list .meta { display:flex; flex-wrap:wrap; gap:6px; align-items:center; margin-top:6px; font-size:.72rem; color:var(--muted); }
.composer { display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1fr); gap:18px; align-items:start; }
.composer textarea.body { min-height:560px; font-family:Consolas,'Cascadia Mono',monospace; font-size:.82rem; line-height:1.55; }
.composer .opts { display:grid; gap:8px; margin:12px 0; }
.composer .opts input[type=number] { width:70px; padding:4px 8px; }
.mail-preview { background:#fff; border:1px solid var(--line); border-radius:12px; padding:22px 24px; max-height:820px; overflow:auto; }
.mail-preview .mp-head { font-size:.76rem; color:var(--muted); border-bottom:1px solid var(--line); padding-bottom:10px; margin-bottom:14px; }
.mail-preview .mp-head b { color:var(--ink); }
.detected li { list-style:none; font-size:.82rem; padding:4px 0; border-bottom:1px dashed var(--line); }
.detected li b { color:var(--soft); font-weight:700; margin-right:6px; }
.dash-warn { display:none; color:#C22349; font-size:.76rem; font-weight:700; margin-top:6px; }

@media (max-width:1100px){ .board{grid-template-columns:repeat(3,minmax(180px,1fr))} }
@media (max-width:1100px){ .composer,.split{grid-template-columns:1fr} }
@media (max-width:860px){ .layout{grid-template-columns:1fr} .side{flex-direction:row;flex-wrap:wrap;align-items:center} .side .spacer{display:none} .cards{grid-template-columns:1fr 1fr} .grid2,.grid3{grid-template-columns:1fr} .board{grid-template-columns:1fr} }
</style>
</head>
<body>

<?php if (!$logged_in): ?>
<div class="login-wrap">
    <div class="login">
        <img src="/assets/img/vturnu-icon-mark.svg" alt="" style="height:48px;margin-bottom:14px">
        <h1><span style="background:var(--grad-v);-webkit-background-clip:text;background-clip:text;color:transparent">V</span>Turn<span style="background:var(--grad-u);-webkit-background-clip:text;background-clip:text;color:transparent">U</span> Admin</h1>
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
    </div>
</div>

<?php else: ?>
<div class="layout">
    <aside class="side">
        <div class="side-brand"><img src="/assets/img/vturnu-icon-mark-on-dark.svg" alt=""><span><span class="bw-v">V</span>Turn<span class="bw-u">U</span> Admin</span></div>
        <div class="side-sub">CRM & Content</div>
        <a class="nav<?= $view === 'dashboard' ? ' on' : '' ?>" href="/admin/?view=dashboard">Dashboard</a>
        <a class="nav<?= in_array($view, ['leads', 'lead'], true) ? ' on' : '' ?>" href="/admin/?view=leads">Sales leads</a>
        <a class="nav<?= $view === 'junk' ? ' on' : '' ?>" href="/admin/?view=junk">Junk filter<?= $junk_leads ? ' (' . count($junk_leads) . ')' : '' ?></a>
        <a class="nav<?= $view === 'audits' ? ' on' : '' ?>" href="/admin/?view=audits">Audit requests</a>
        <a class="nav<?= $view === 'downloads' ? ' on' : '' ?>" href="/admin/?view=downloads">E-book downloads</a>
        <?php /* Newsletter signups were being captured and segmented, and even
                 included in the CSV export, but had no screen of their own, so
                 the list was invisible unless you exported it. */ ?>
        <a class="nav<?= $view === 'subscribers' ? ' on' : '' ?>" href="/admin/?view=subscribers">Subscribers</a>
        <a class="nav<?= $view === 'pipeline' ? ' on' : '' ?>" href="/admin/?view=pipeline">Pipeline</a>
        <a class="nav<?= $view === 'reports' ? ' on' : '' ?>" href="/admin/?view=reports">Reports</a>
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
    <?php if (!empty($_GET['saved'])): ?><div class="flash">✓ Saved. The public website picks up edits within about an hour. <a href="/blog/<?= e_a($_GET['saved']) ?>/" target="_blank" style="font-weight:800">view page ↗</a></div><?php endif; ?>

    <?php if ($view === 'dashboard'):
        $week_ago = date('c', strtotime('-7 days'));
        $month_ago = date('c', strtotime('-30 days'));
        $day_ago = date('c', strtotime('-24 hours'));
        $today_start = date('c', strtotime('today'));
        $sales = leads_in($leads, 'lead');
        $this_week = count(array_filter($sales, fn($l) => ($l['date'] ?? '') >= $week_ago));
        $today_n = count(array_filter($sales, fn($l) => ($l['date'] ?? '') >= $today_start));
        $this_month = count(array_filter($leads, fn($l) => ($l['date'] ?? '') >= $month_ago));
        $by_status = array_fill_keys(ADMIN_STATUSES, 0);
        $open_value = 0; $won_value = 0;
        foreach ($leads as $l) {
            $by_status[$l['status']] = ($by_status[$l['status']] ?? 0) + 1;
            $v = (int) $l['value'];
            if ($l['status'] === 'won') { $won_value += $v; }
            elseif (!in_array($l['status'], ADMIN_CLOSED, true)) { $open_value += $v; }
        }
        $closed = $by_status['won'] + $by_status['lost'];
        $win_rate = $closed > 0 ? round($by_status['won'] / $closed * 100) : 0;
        $today = date('Y-m-d');
        $due = array_filter($leads, fn($l) => $l['followup'] !== '' && $l['followup'] <= $today && !in_array($l['status'], ADMIN_CLOSED, true));
        /* Sales leads still waiting for a first reply, oldest first: speed to lead wins deals. */
        $unworked = array_values(array_filter($sales, fn($l) => $l['status'] === 'new'));
        usort($unworked, fn($a, $b) => strcmp($a['date'], $b['date']));
        $late = array_filter($unworked, fn($l) => ($l['date'] ?? '') < $day_ago);
        /* Proposals in the last 30 days, and how far each got. */
        $recent_p = array_filter($proposal_index, fn($p) => strtotime((string) $p['sent_at']) >= strtotime('-30 days'));
        $p_opened = count(array_filter($recent_p, fn($p) => crm_proposal_state($p) !== 'sent'));
        $p_read = count(array_filter($recent_p, fn($p) => in_array(crm_proposal_state($p), ['viewed', 'accepted'], true)));
        $p_yes = count(array_filter($recent_p, fn($p) => crm_proposal_state($p) === 'accepted'));
        /* One list of what needs a person, most urgent first: a yes, a proposal being read,
           a lead waiting too long, a lead just in, then follow-ups that are due. */
        $byId = array_column($leads, null, 'id');
        $attn = [];
        foreach ($proposal_index as $plid => $p) {
            $l = $byId[(string) $plid] ?? null;
            if (!$l || in_array($l['status'], ADMIN_CLOSED, true)) continue;
            $st = crm_proposal_state($p);
            if ($st === 'accepted') { $attn[] = [0, $l, 'Said yes to the proposal', '#12A150', 'call']; }
            elseif ($st === 'viewed' && strtotime((string) ($p['any_viewed'] ?? '')) >= strtotime('-7 days')) { $attn[] = [1, $l, 'Read the proposal', '#FF9100', 'followup']; }
        }
        foreach ($unworked as $l) {
            $old = ($l['date'] ?? '') < $day_ago;
            $attn[] = [$old ? 2 : 3, $l, 'Waiting ' . waiting_for($l), $old ? '#D62C74' : '#5B56C9', 'proposal'];
        }
        foreach ($due as $l) { $attn[] = [4, $l, 'Follow-up due ' . date('j M', strtotime($l['followup'])), '#4338CA', isset($proposal_index[$l['id']]) ? 'followup' : 'proposal']; }
        usort($attn, fn($a, $b) => $a[0] <=> $b[0]);
        $listed = [];
        $attn = array_values(array_filter($attn, function ($a) use (&$listed) {
            if (isset($listed[$a[1]['id']])) return false;
            return $listed[$a[1]['id']] = true;
        }));
        /* Where sales leads come from: channel groups, with the channels inside each. */
        $groups = [];
        foreach ($sales as $l) {
            $g = $l['channel_group'];
            $groups[$g]['n'] = ($groups[$g]['n'] ?? 0) + 1;
            $groups[$g]['a'] = ($groups[$g]['a'] ?? 0) + ($l['grade'] === 'A' ? 1 : 0);
            $groups[$g]['ch'][$l['channel']] = ($groups[$g]['ch'][$l['channel']] ?? 0) + 1;
        }
        uasort($groups, fn($a, $b) => $b['n'] <=> $a['n']);
        $max_g = max(1, ...array_values(array_map(fn($d) => $d['n'], $groups ?: [['n' => 1]])));
        $by_service = [];
        foreach ($sales as $l) {
            $s = trim((string) preg_replace('/,.*$/', '', (string) $l['service'])) ?: 'Not chosen';
            $by_service[$s] = ($by_service[$s] ?? 0) + 1;
        }
        arsort($by_service);
        $days = [];
        for ($i = 13; $i >= 0; $i--) { $days[date('Y-m-d', strtotime("-$i days"))] = 0; }
        foreach ($sales as $l) { $d = date('Y-m-d', strtotime($l['date'])); if (isset($days[$d])) { $days[$d]++; } }
        $max_d = max(1, ...array_values($days));
        /* Typical time to a first reply over the last 90 days. */
        $resp = [];
        foreach ($sales as $l) {
            if (!empty($l['contacted_at']) && ($l['date'] ?? '') >= date('c', strtotime('-90 days'))) {
                $resp[] = max(0, strtotime((string) $l['contacted_at']) - strtotime($l['date'])) / 3600;
            }
        }
        sort($resp);
        $median_resp = $resp ? $resp[intdiv(count($resp), 2)] : null;
    ?>
        <h1>Dashboard</h1>
        <p class="sub">Leads, proposals and pipeline at a glance.</p>
        <div class="cards">
            <a class="kpi kpi-link grad" href="/admin/?view=leads"><b><?= $this_week ?></b><span>New leads this week</span><small><?= $today_n ?> today · <?= count($sales) ?> in all</small></a>
            <a class="kpi kpi-link<?= $late ? ' hot' : '' ?>" href="/admin/?view=leads&status=new"><b><?= count($unworked) ?></b><span>Waiting for a first reply</span><small><?= $unworked ? 'Oldest waiting ' . e_a(waiting_for($unworked[0])) : 'Everyone has had a reply' ?></small></a>
            <a class="kpi kpi-link" href="/admin/?view=leads&proposal=sent"><b><?= count($recent_p) ?></b><span>Proposals sent, last 30 days</span><small><?= $p_opened ?> opened · <?= $p_read ?> read · <?= $p_yes ?> said yes</small></a>
            <div class="kpi"><b>₹<?= money_fmt($open_value) ?: '0' ?></b><span>Open pipeline value</span><small>Win rate <?= $win_rate ?>% (<?= $by_status['won'] ?>/<?= $closed ?> closed)<?= $median_resp !== null ? ' · first reply in ' . ($median_resp < 1 ? 'under an hour' : round($median_resp) . 'h') . ', typically' : '' ?></small></div>
        </div>

        <div class="panel"<?= $attn ? ' style="border-color:rgba(255,61,138,.35)"' : '' ?>>
            <h2>Needs attention<?= $attn ? ' (' . count($attn) . ')' : '' ?></h2>
            <?php if ($attn): ?>
            <table class="attn"><tbody>
            <?php foreach (array_slice($attn, 0, 10) as [$rank, $l, $why, $color, $next]): ?>
                <tr>
                    <td style="width:170px"><span class="why" style="color:<?= $color ?>"><?= e_a($why) ?></span></td>
                    <td><strong><?= e_a($l['name']) ?></strong><?= $l['company'] !== '' ? '<br><small style="color:var(--muted)">' . e_a(mb_substr((string) $l['company'], 0, 44)) . '</small>' : '' ?></td>
                    <td><?= e_a(mb_substr((string) $l['service'], 0, 30)) ?></td>
                    <td><?= channel_badge($l) ?></td>
                    <td><?= grade_badge($l['grade']) ?></td>
                    <td style="text-align:right;white-space:nowrap">
                        <?php if ($next === 'proposal'): ?><a class="btn sm grad" href="/admin/?view=proposal&id=<?= e_a($l['id']) ?>">Send proposal</a>
                        <?php elseif ($next === 'followup'): ?><a class="btn sm" href="/admin/?view=proposal&kind=followup&id=<?= e_a($l['id']) ?>">Follow up</a>
                        <?php elseif (phone_digits((string) $l['mobile']) !== ''): ?><a class="btn sm" href="tel:+<?= e_a(phone_digits((string) $l['mobile'])) ?>">Call now</a><?php endif; ?>
                        <a class="btn ghost sm" href="/admin/?view=lead&id=<?= e_a($l['id']) ?>">Open</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody></table>
            <?php else: ?><p class="note">Nothing is waiting. New leads, proposals being read and follow-ups that fall due appear here.</p><?php endif; ?>
        </div>

        <div class="split" style="margin-bottom:18px">
            <div class="panel">
                <h2>Where leads come from</h2>
                <table><tbody>
                <?php foreach ($groups as $g => $d): arsort($d['ch']); ?>
                    <tr class="group-row">
                        <td style="width:46%"><strong><?= e_a((string) $g) ?></strong><div class="chips"><?php foreach (array_slice($d['ch'], 0, 4, true) as $ch => $n): ?><span><?= e_a((string) $ch) ?> · <?= $n ?></span><?php endforeach; ?></div></td>
                        <td><span class="barline"><i style="width:<?= (int) round($d['n'] / $max_g * 100) ?>%;background:<?= CRM_GROUP_COLORS[$g] ?? '#9A93BC' ?>"></i></span></td>
                        <td style="text-align:right;width:80px"><strong><?= $d['n'] ?></strong><br><small style="color:var(--muted)"><?= $d['a'] ?> grade A</small></td>
                    </tr>
                <?php endforeach; if (!$groups): ?><tr><td>No leads yet.</td></tr><?php endif; ?>
                </tbody></table>
                <p class="note">Every new enquiry records the search engine, ad, AI assistant, social network or site that brought the visitor. Leads from before tracking started show as Not tracked.</p>
            </div>
            <div class="panel">
                <h2>Last 14 days</h2>
                <div class="days" role="img" aria-label="Sales leads per day, last 14 days">
                <?php foreach ($days as $d => $n): ?>
                    <div title="<?= e_a(date('j M', strtotime($d))) ?>: <?= $n ?> <?= $n === 1 ? 'lead' : 'leads' ?>"><b><?= $n ?: '' ?></b><i style="height:<?= max(3, (int) round($n / $max_d * 86)) ?>px"></i><small><?= e_a(date('j', strtotime($d))) ?></small></div>
                <?php endforeach; ?>
                </div>
                <h2 style="margin-top:18px">What people ask for</h2>
                <table><tbody>
                <?php foreach (array_slice($by_service, 0, 6, true) as $s => $n): ?>
                    <tr><td><?= e_a((string) $s) ?></td><td style="text-align:right;font-weight:800"><?= $n ?></td></tr>
                <?php endforeach; if (!$by_service): ?><tr><td>No leads yet.</td></tr><?php endif; ?>
                </tbody></table>
            </div>
        </div>

        <?php /* Contacts split by funnel, because each is worked differently. */ ?>
        <div class="cards">
            <a class="kpi kpi-link" href="/admin/?view=leads"><b><?= count(leads_in($leads, 'lead')) ?></b><span>Sales leads</span></a>
            <a class="kpi kpi-link" href="/admin/?view=audits"><b><?= count(leads_in($leads, 'audit')) ?></b><span>Audit requests</span></a>
            <a class="kpi kpi-link" href="/admin/?view=downloads"><b><?= count(leads_in($leads, 'ebook')) ?></b><span>E-book downloads</span></a>
            <a class="kpi kpi-link" href="/admin/?view=subscribers"><b><?= count(leads_in($leads, 'newsletter')) ?></b><span>Subscribers</span></a>
        </div>

        <div class="panel">
            <h2>Quick actions</h2>
            <div class="actions-row">
                <a class="btn grad" href="/admin/?view=pipeline">Open pipeline board</a>
                <a class="btn" href="/admin/?view=reports">Reports</a>
                <a class="btn ghost" href="/admin/?view=leads">All leads</a>
                <a class="btn ghost" href="/admin/?view=blog-edit">+ New blog post</a>
                <a class="btn ghost" href="/admin/?view=export">Export CSV</a>
            </div>
        </div>

        <div class="cards" style="grid-template-columns:1fr 1fr">
            <div class="panel" style="margin:0">
                <h2>Pipeline by stage</h2>
                <table><tbody>
                <?php foreach (ADMIN_STATUSES as $st): $pct = count($leads) ? round($by_status[$st] / count($leads) * 100) : 0; ?>
                    <tr>
                        <td style="width:110px"><?= status_badge($st) ?></td>
                        <td><span class="barline"><i style="width:<?= $pct ?>%;background:<?= status_color($st) ?>"></i></span></td>
                        <td style="text-align:right;font-weight:800;width:44px"><?= $by_status[$st] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody></table>
                <p class="note">Won revenue booked: <strong>₹<?= money_fmt($won_value) ?: '0' ?></strong> · <?= $this_month ?> leads in last 30 days</p>
            </div>
            <div class="panel" style="margin:0">
                <h2>Which form they used</h2>
                <?php $by_form = [];
                foreach ($leads as $l) { $s = CRM_PLACEMENTS[$l['source']] ?? ($l['source'] ?: 'unknown'); $by_form[$s] = ($by_form[$s] ?? 0) + 1; }
                arsort($by_form); ?>
                <table><tbody>
                <?php foreach (array_slice($by_form, 0, 7, true) as $src => $n): ?>
                    <tr><td><?= e_a(ucfirst((string) $src)) ?></td><td style="text-align:right;font-weight:800"><?= $n ?></td></tr>
                <?php endforeach; if (!$by_form): ?><tr><td>No leads yet.</td></tr><?php endif; ?>
                </tbody></table>
            </div>
        </div>

        <div class="panel">
            <h2>Recent leads</h2>
            <table>
                <thead><tr><th>Lead</th><th>Source</th><th>Service</th><th>Grade</th><th>Proposal</th><th>Status</th><th></th></tr></thead>
                <tbody>
                <?php foreach (array_slice($leads, 0, 8) as $l): ?>
                    <tr>
                        <td><strong><?= e_a($l['name'] ?? '') ?></strong><br><small style="color:var(--muted)"><?= e_a($l['email'] ?? '') ?></small></td>
                        <td><?= channel_badge($l) ?></td>
                        <td><?= e_a(mb_substr($l['service'] ?? '', 0, 32)) ?></td>
                        <td><?= grade_badge($l['grade']) ?></td>
                        <td><?= lead_segment($l) === 'lead' ? proposal_badge($proposal_index[$l['id']] ?? null) : '<span style="color:var(--muted)">-</span>' ?></td>
                        <td><?= status_badge($l['status']) ?></td>
                        <td><a class="btn ghost sm" href="/admin/?view=lead&id=<?= e_a($l['id']) ?>">Open</a></td>
                    </tr>
                <?php endforeach; if (!$leads): ?><tr><td colspan="7">No leads yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>

    <?php elseif ($view === 'pipeline'):
        $cols = array_fill_keys(ADMIN_STATUSES, []);
        foreach ($leads as $l) { $cols[$l['status']][] = $l; }
    ?>
        <h1>Pipeline</h1>
        <p class="sub">Every open deal by stage. Click a card to open the full record.</p>
        <div class="board">
            <?php foreach (ADMIN_STATUSES as $st):
                $colVal = array_sum(array_map(fn($l) => (int) $l['value'], $cols[$st])); ?>
            <div class="board-col">
                <div class="board-head" style="--st:<?= status_color($st) ?>">
                    <strong><?= ucfirst($st) ?></strong>
                    <span><?= count($cols[$st]) ?><?= $colVal ? ' · ₹' . money_fmt($colVal) : '' ?></span>
                </div>
                <?php foreach ($cols[$st] as $l): ?>
                <a class="board-card" href="/admin/?view=lead&id=<?= e_a($l['id']) ?>">
                    <strong><?= e_a($l['name'] ?? '') ?></strong>
                    <?php if ($l['company']): ?><em><?= e_a($l['company']) ?></em><?php endif; ?>
                    <span><?= e_a(mb_substr($l['service'] ?? '', 0, 30)) ?></span>
                    <div class="board-meta">
                        <?php if ($l['value'] !== ''): ?><b>₹<?= money_fmt($l['value']) ?></b><?php endif; ?>
                        <?php if ($l['priority'] === 'high'): ?><i class="pri-high">High</i><?php endif; ?>
                        <?php if ($l['followup'] !== ''): ?><i class="<?= $l['followup'] <= date('Y-m-d') ? 'due' : '' ?>"><?= e_a(date('j M', strtotime($l['followup']))) ?></i><?php endif; ?>
                    </div>
                </a>
                <?php endforeach; if (!$cols[$st]): ?><p class="board-empty">Empty</p><?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

    <?php elseif ($view === 'leads'):
        $f_status = $_GET['status'] ?? '';
        $f_prio   = $_GET['priority'] ?? '';
        $f_owner  = trim($_GET['owner'] ?? '');
        $f_due    = !empty($_GET['due']);
        $f_q      = trim($_GET['q'] ?? '');
        $f_chan   = trim($_GET['channel'] ?? '');
        $f_grade  = $_GET['grade'] ?? '';
        $f_prop   = $_GET['proposal'] ?? '';
        $today    = date('Y-m-d');
        $scope    = leads_in($leads, 'lead');
        $filtered = array_filter($scope, function ($l) use ($f_status, $f_prio, $f_owner, $f_due, $f_q, $f_chan, $f_grade, $f_prop, $today, $proposal_index) {
            if ($f_status !== '' && $l['status'] !== $f_status) return false;
            if ($f_prio !== '' && $l['priority'] !== $f_prio) return false;
            if ($f_owner !== '' && strcasecmp($l['owner'], $f_owner) !== 0) return false;
            if ($f_due && !($l['followup'] !== '' && $l['followup'] <= $today && !in_array($l['status'], ADMIN_CLOSED, true))) return false;
            if ($f_chan !== '' && $l['channel'] !== $f_chan && $l['channel_group'] !== $f_chan) return false;
            if ($f_grade !== '' && $l['grade'] !== $f_grade) return false;
            if ($f_prop !== '') {
                $st = crm_proposal_state($proposal_index[$l['id']] ?? null);
                if ($f_prop === 'sent' ? $st === 'none' : $st !== $f_prop) return false;
            }
            if ($f_q !== '') {
                $hay = strtolower(($l['name'] ?? '') . ' ' . ($l['email'] ?? '') . ' ' . ($l['company'] ?? '')
                     . ' ' . ($l['service'] ?? '') . ' ' . $l['tags'] . ' ' . ($l['mobile'] ?? '') . ' ' . $l['channel']);
                if (!str_contains($hay, strtolower($f_q))) return false;
            }
            return true;
        });
        $owners = array_values(array_unique(array_filter(array_map(fn($l) => $l['owner'], $leads))));
        sort($owners);
        $channels = array_values(array_unique(array_map(fn($l) => $l['channel'], $scope)));
        sort($channels);
        $qs = array_filter(['status' => $f_status, 'priority' => $f_prio, 'owner' => $f_owner, 'due' => $f_due ? '1' : '', 'q' => $f_q,
                            'channel' => $f_chan, 'grade' => $f_grade, 'proposal' => $f_prop]);
    ?>
        <h1>Sales leads</h1>
        <p class="sub">Enquiries from the quote, contact and consultation forms.
            <?= count($filtered) ?> of <?= count($scope) ?> shown ·
            <a href="/admin/?view=export&seg=lead" style="font-weight:800;color:var(--p)">Export CSV ↓</a> ·
            <a href="/admin/?view=pipeline" style="font-weight:800">Board view</a></p>
        <form class="filters" method="get" action="/admin/">
            <input type="hidden" name="view" value="leads">
            <input type="search" name="q" placeholder="Search name, email, company, phone, tag…" value="<?= e_a($f_q) ?>" style="width:270px">
            <select name="status" onchange="this.form.submit()">
                <option value="">All statuses</option>
                <?php foreach (ADMIN_STATUSES as $st): ?>
                <option value="<?= $st ?>"<?= $f_status === $st ? ' selected' : '' ?>><?= ucfirst($st) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="priority" onchange="this.form.submit()">
                <option value="">Any priority</option>
                <?php foreach (ADMIN_PRIORITIES as $p): ?>
                <option value="<?= $p ?>"<?= $f_prio === $p ? ' selected' : '' ?>><?= ucfirst($p) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if ($owners): ?>
            <select name="owner" onchange="this.form.submit()">
                <option value="">Any owner</option>
                <?php foreach ($owners as $o): ?>
                <option value="<?= e_a($o) ?>"<?= strcasecmp($f_owner, $o) === 0 ? ' selected' : '' ?>><?= e_a($o) ?></option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>
            <?php if ($channels): ?>
            <select name="channel" onchange="this.form.submit()">
                <option value="">Any source</option>
                <?php foreach (array_keys(CRM_GROUP_COLORS) as $g): if (!in_array($g, array_map(fn($l) => $l['channel_group'], $scope), true)) continue; ?>
                <option value="<?= e_a($g) ?>"<?= $f_chan === $g ? ' selected' : '' ?>>All <?= e_a(strtolower($g)) ?></option>
                <?php endforeach; ?>
                <?php foreach ($channels as $c): ?>
                <option value="<?= e_a($c) ?>"<?= $f_chan === $c ? ' selected' : '' ?>><?= e_a($c) ?></option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>
            <select name="grade" onchange="this.form.submit()">
                <option value="">Any grade</option>
                <?php foreach (['A', 'B', 'C'] as $g): ?><option value="<?= $g ?>"<?= $f_grade === $g ? ' selected' : '' ?>>Grade <?= $g ?></option><?php endforeach; ?>
            </select>
            <select name="proposal" onchange="this.form.submit()">
                <?php foreach (['' => 'Any proposal state', 'none' => 'No proposal yet', 'sent' => 'Proposal sent', 'opened' => 'Opened', 'viewed' => 'Read', 'accepted' => 'Said yes'] as $k => $label): ?>
                <option value="<?= $k ?>"<?= $f_prop === $k ? ' selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
            <label class="chk"><input type="checkbox" name="due" value="1"<?= $f_due ? ' checked' : '' ?> onchange="this.form.submit()"> Follow-up due</label>
            <button class="btn ghost" type="submit">Filter</button>
            <?php if ($qs): ?><a class="btn ghost" href="/admin/?view=leads">Clear</a><?php endif; ?>
        </form>
        <div class="panel">
            <table>
                <thead><tr><th>When</th><th>Lead</th><th>Source</th><th>Service</th><th>Grade</th><th>Proposal</th><th>Follow-up</th><th>Status</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($filtered as $l):
                    $overdue = $l['followup'] !== '' && $l['followup'] <= $today && !in_array($l['status'], ADMIN_CLOSED, true);
                    $pstate = crm_proposal_state($proposal_index[$l['id']] ?? null); ?>
                    <tr>
                        <td style="white-space:nowrap"><?= e_a(date('j M', strtotime($l['date'] ?? 'now'))) ?><br><small style="color:var(--muted)"><?= e_a(date('H:i', strtotime($l['date'] ?? 'now'))) ?></small></td>
                        <td>
                            <strong><?= e_a($l['name'] ?? '') ?></strong>
                            <?php if ($l['priority'] === 'high'): ?> <span class="tag-hi">High</span><?php endif; ?>
                            <br><small style="color:var(--muted)"><?= e_a($l['email'] ?? '') ?></small>
                            <?php if ($l['company'] !== ''): ?><br><small><?= e_a(mb_substr((string) $l['company'], 0, 48)) ?></small><?php endif; ?>
                            <?php if ($l['tags'] !== ''): ?><br><small class="tag-list"><?= e_a($l['tags']) ?></small><?php endif; ?>
                        </td>
                        <td><?= channel_badge($l) ?><?php if (($l['attribution']['detail'] ?? '') !== ''): ?><br><small style="color:var(--muted)"><?= e_a(mb_substr((string) $l['attribution']['detail'], 0, 36)) ?></small><?php endif; ?></td>
                        <td><?= e_a(mb_substr($l['service'] ?? '', 0, 34)) ?><?= $l['value'] !== '' ? '<br><small style="color:var(--muted)">₹' . money_fmt($l['value']) . ($l['owner'] !== '' ? ' · ' . e_a($l['owner']) : '') . '</small>' : '' ?></td>
                        <td><?= grade_badge($l['grade']) ?></td>
                        <td><?= proposal_badge($proposal_index[$l['id']] ?? null) ?></td>
                        <td style="white-space:nowrap"><?= $l['followup'] !== ''
                            ? '<span class="' . ($overdue ? 'due-flag' : '') . '">' . e_a(date('j M', strtotime($l['followup']))) . '</span>'
                            : '<span style="color:var(--muted)">-</span>' ?></td>
                        <td><?= status_badge($l['status']) ?><?= $l['status'] === 'new' ? '<br><small class="' . (($l['date'] ?? '') < date('c', strtotime('-24 hours')) ? 'due-flag' : '') . '" style="font-size:.68rem">' . e_a(waiting_for($l)) . '</small>' : '' ?></td>
                        <td style="white-space:nowrap"><a class="btn ghost sm" href="/admin/?view=lead&id=<?= e_a($l['id']) ?>">Open</a><?php if ($pstate === 'none' && !in_array($l['status'], ADMIN_CLOSED, true)): ?> <a class="btn sm grad" href="/admin/?view=proposal&id=<?= e_a($l['id']) ?>">Proposal</a><?php endif; ?></td>
                    </tr>
                <?php endforeach; if (!$filtered): ?><tr><td colspan="9">No leads match.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>

    <?php elseif ($view === 'junk'): ?>
        <h1>Junk filter</h1>
        <p class="sub">Submissions the quality check held back. They never reached <?= e_a(ENQUIRY_TO) ?>. If a real lead lands here, restore it and it goes straight into your pipeline and inbox.</p>
        <form method="post" style="margin-bottom:18px">
            <input type="hidden" name="action" value="junk_recheck">
            <input type="hidden" name="csrf" value="<?= e_a($csrf) ?>">
            <button class="btn ghost" type="submit">Re-check older new leads</button>
        </form>
        <div class="card">
            <table>
                <thead><tr><th>When</th><th>Contact</th><th>Message</th><th>Why it was held</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($junk_leads as $l): $why = end($l['activity']); ?>
                    <tr>
                        <td><?= e_a(date('j M, g:ia', strtotime($l['date']))) ?></td>
                        <td><strong><?= e_a($l['name']) ?></strong><br><?= e_a($l['email']) ?><?= $l['mobile'] !== '' ? '<br>' . e_a($l['mobile']) : '' ?><?= $l['company'] !== '' ? '<br>' . e_a($l['company']) : '' ?></td>
                        <td style="max-width:320px"><?= e_a(mb_substr((string) $l['message'], 0, 220)) ?></td>
                        <td style="max-width:260px"><?= e_a(preg_replace('/^Quality check: junk \(\d+\): /', '', (string) ($why['text'] ?? ''))) ?></td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="action" value="lead_restore">
                                <input type="hidden" name="id" value="<?= e_a($l['id']) ?>">
                                <input type="hidden" name="csrf" value="<?= e_a($csrf) ?>">
                                <button class="btn ghost" type="submit">Restore</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; if (!$junk_leads): ?><tr><td colspan="5">Nothing held back. Junk will appear here when the filter catches it.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>

    <?php elseif ($view === 'audits'):
        $f_q = trim($_GET['q'] ?? '');
        $scope = leads_in($leads, 'audit');
        $filtered = $f_q === '' ? $scope : array_values(array_filter($scope, function ($l) use ($f_q) {
            $hay = strtolower(($l['name'] ?? '') . ' ' . ($l['email'] ?? '') . ' ' . ($l['company'] ?? '') . ' ' . ($l['message'] ?? ''));
            return str_contains($hay, strtolower($f_q));
        }));
        // The audit tool records the score in the message field, so pull it out.
        $scoreOf = function (array $l): ?int {
            return preg_match('/score\s+(\d+)/i', (string) ($l['message'] ?? ''), $m) ? (int) $m[1] : null;
        };
        $withScore = array_filter(array_map($scoreOf, $scope), fn($v) => $v !== null);
        $avgScore = $withScore ? (int) round(array_sum($withScore) / count($withScore)) : 0;
        $weakest = $withScore ? min($withScore) : 0;
    ?>
        <h1>Audit requests</h1>
        <p class="sub">People who ran the free site audit or asked for a strategist review.
            <?= count($filtered) ?> of <?= count($scope) ?> shown ·
            <a href="/admin/?view=export&seg=audit" style="font-weight:800;color:var(--p)">Export CSV ↓</a></p>

        <div class="cards">
            <div class="kpi grad"><b><?= count($scope) ?></b><span>Total audit contacts</span></div>
            <div class="kpi"><b><?= $avgScore ?: '-' ?></b><span>Average site score</span></div>
            <div class="kpi"><b><?= $weakest ?: '-' ?></b><span>Lowest score seen</span></div>
            <div class="kpi"><b><?= count(array_filter($scope, fn($l) => $l['status'] === 'new')) ?></b><span>Not yet contacted</span></div>
        </div>
        <p class="note">A low score is a sales signal: that visitor has a problem you can name specifically on the first call.</p>

        <form class="filters" method="get" action="/admin/">
            <input type="hidden" name="view" value="audits">
            <input type="search" name="q" placeholder="Search name, email, site…" value="<?= e_a($f_q) ?>" style="width:280px">
            <button class="btn ghost" type="submit">Search</button>
            <?php if ($f_q !== ''): ?><a class="btn ghost" href="/admin/?view=audits">Clear</a><?php endif; ?>
        </form>

        <div class="panel">
            <table>
                <thead><tr><th>When</th><th>Contact</th><th>Site audited</th><th>Score</th><th>Status</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($filtered as $l): $sc = $scoreOf($l); ?>
                    <tr>
                        <td style="white-space:nowrap"><?= e_a(date('j M', strtotime($l['date'] ?? 'now'))) ?><br><small style="color:var(--muted)"><?= e_a(date('H:i', strtotime($l['date'] ?? 'now'))) ?></small></td>
                        <td><strong><?= e_a($l['name'] ?? '') ?></strong><br><small style="color:var(--muted)"><?= e_a($l['email'] ?? '') ?></small></td>
                        <td><?= e_a($l['company'] ?: '-') ?></td>
                        <td><?= $sc !== null
                            ? '<span class="badge-st" style="--st:' . ($sc >= 85 ? '#12A150' : ($sc >= 65 ? '#5B56C9' : ($sc >= 45 ? '#FF9100' : '#FF3D8A'))) . '">' . $sc . '/100</span>'
                            : '<span style="color:var(--muted)">consultation</span>' ?></td>
                        <td><?= status_badge($l['status']) ?></td>
                        <td><a class="btn ghost sm" href="/admin/?view=lead&id=<?= e_a($l['id']) ?>">Open</a></td>
                    </tr>
                <?php endforeach; if (!$filtered): ?><tr><td colspan="6">No audit requests yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>

    <?php elseif ($view === 'downloads'):
        $f_q = trim($_GET['q'] ?? '');
        $f_res = trim($_GET['res'] ?? '');
        $scope = leads_in($leads, 'ebook');
        // "Resource: The AI Search Playbook" is stored in service; strip the prefix.
        $titleOf = fn(array $l) => trim(preg_replace('/^Resource:\s*/i', '', (string) ($l['service'] ?? ''))) ?: 'Unknown';
        $filtered = array_values(array_filter($scope, function ($l) use ($f_q, $f_res, $titleOf) {
            if ($f_res !== '' && $titleOf($l) !== $f_res) return false;
            if ($f_q !== '') {
                $hay = strtolower(($l['name'] ?? '') . ' ' . ($l['email'] ?? '') . ' ' . ($l['company'] ?? '') . ' ' . $titleOf($l));
                if (!str_contains($hay, strtolower($f_q))) return false;
            }
            return true;
        }));
        $byTitle = [];
        foreach ($scope as $l) { $t = $titleOf($l); $byTitle[$t] = ($byTitle[$t] ?? 0) + 1; }
        arsort($byTitle);
        $week = date('c', strtotime('-7 days'));
    ?>
        <h1>E-book downloads</h1>
        <p class="sub">Everyone who requested a downloadable resource.
            <?= count($filtered) ?> of <?= count($scope) ?> shown ·
            <a href="/admin/?view=export&seg=ebook" style="font-weight:800;color:var(--p)">Export CSV ↓</a></p>

        <div class="cards">
            <div class="kpi grad"><b><?= count($scope) ?></b><span>Total downloads</span></div>
            <div class="kpi"><b><?= count(array_filter($scope, fn($l) => ($l['date'] ?? '') >= $week)) ?></b><span>This week</span></div>
            <div class="kpi"><b><?= count($byTitle) ?></b><span>Titles downloaded</span></div>
            <div class="kpi"><b><?= count(array_unique(array_map(fn($l) => strtolower($l['email'] ?? ''), $scope))) ?></b><span>Unique people</span></div>
        </div>

        <div class="cards" style="grid-template-columns:1fr 1fr">
            <div class="panel" style="margin:0">
                <h2>Most downloaded</h2>
                <table><tbody>
                <?php foreach (array_slice($byTitle, 0, 8, true) as $t => $n): ?>
                    <tr>
                        <td><a href="/admin/?view=downloads&res=<?= urlencode($t) ?>"><?= e_a($t) ?></a></td>
                        <td style="text-align:right;font-weight:800"><?= $n ?></td>
                    </tr>
                <?php endforeach; if (!$byTitle): ?><tr><td>No downloads yet.</td></tr><?php endif; ?>
                </tbody></table>
            </div>
            <div class="panel" style="margin:0">
                <h2>Turning downloads into deals</h2>
                <p class="note">A download tells you the topic someone is working on right now. The title they chose is the opening line of the follow-up call.</p>
                <p class="note">Repeat downloaders are the warmest contacts in this list. Two or more titles from one address usually means an active project.</p>
            </div>
        </div>

        <form class="filters" method="get" action="/admin/">
            <input type="hidden" name="view" value="downloads">
            <input type="search" name="q" placeholder="Search name, email, title…" value="<?= e_a($f_q) ?>" style="width:280px">
            <?php if ($byTitle): ?>
            <select name="res" onchange="this.form.submit()">
                <option value="">All titles</option>
                <?php foreach ($byTitle as $t => $n): ?>
                <option value="<?= e_a($t) ?>"<?= $f_res === $t ? ' selected' : '' ?>><?= e_a(mb_substr($t, 0, 40)) ?></option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>
            <button class="btn ghost" type="submit">Search</button>
            <?php if ($f_q !== '' || $f_res !== ''): ?><a class="btn ghost" href="/admin/?view=downloads">Clear</a><?php endif; ?>
        </form>

        <div class="panel">
            <table>
                <thead><tr><th>When</th><th>Contact</th><th>Title downloaded</th><th>Status</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($filtered as $l): ?>
                    <tr>
                        <td style="white-space:nowrap"><?= e_a(date('j M', strtotime($l['date'] ?? 'now'))) ?><br><small style="color:var(--muted)"><?= e_a(date('H:i', strtotime($l['date'] ?? 'now'))) ?></small></td>
                        <td><strong><?= e_a($l['name'] ?? '') ?></strong><br><small style="color:var(--muted)"><?= e_a($l['email'] ?? '') ?></small></td>
                        <td><?= e_a($titleOf($l)) ?></td>
                        <td><?= status_badge($l['status']) ?></td>
                        <td><a class="btn ghost sm" href="/admin/?view=lead&id=<?= e_a($l['id']) ?>">Open</a></td>
                    </tr>
                <?php endforeach; if (!$filtered): ?><tr><td colspan="5">No downloads yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>

    <?php elseif ($view === 'lead'):
        $lead = null;
        foreach ($leads as $l) { if ($l['id'] === ($_GET['id'] ?? '')) { $lead = $l; break; } }
        if (!$lead): ?>
        <h1>Lead not found</h1>
        <?php else:
            $facts = crm_lead_facts($lead);
            $attr = $lead['attribution'];
            $props = crm_proposals((int) $lead['id']);
            $hasProposal = (bool) array_filter($props, fn($p) => $p['kind'] === 'proposal');
            $digits = phone_digits((string) $lead['mobile']);
            $crm_cfg = crm_settings();
            $wa_text = 'Hi ' . $facts['first'] . ', this is ' . crm_first_name((string) $crm_cfg['sender_name']) . ' from VTurnU, about your enquiry'
                . ($facts['service'] !== '' ? ' on ' . crm_service_phrase($facts, crm_intent($facts)) : '') . '.';
            $others = array_values(array_filter($leads, fn($o) => $o['id'] !== $lead['id']
                && (strcasecmp((string) $o['email'], (string) $lead['email']) === 0 || (strlen($digits) >= 8 && phone_digits((string) $o['mobile']) === $digits))));
            $touch_line = function (?array $t): string {
                if (!$t) return '';
                [$ch, , $detail] = crm_classify_touch($t);
                return e_a($t['at'] !== '' ? date('j M Y, H:i', strtotime($t['at'])) : 'Date not recorded') . ' · ' . e_a($ch) . ($detail !== '' ? ' (' . e_a($detail) . ')' : '')
                    . ($t['landing'] !== '' ? '<br><small class="mono">' . e_a(mb_substr($t['landing'], 0, 90)) . '</small>' : '')
                    . ($t['ref'] !== '' ? '<br><small style="color:var(--muted)">from ' . e_a(mb_substr($t['ref'], 0, 90)) . '</small>' : '');
            };
            $first_t = $attr['first'] ?? null;
            $last_t = $attr['last'] ?? null;
        ?>
        <h1><?= e_a($lead['name'] ?? 'Lead') ?> <?= grade_badge($lead['grade']) ?> <?= channel_badge($lead) ?></h1>
        <p class="sub"><a href="/admin/?view=leads">← Back</a> · captured <?= e_a(date('j M Y, H:i', strtotime($lead['date'] ?? 'now'))) ?> from the <strong><?= e_a(CRM_PLACEMENTS[$lead['source']] ?? $lead['source']) ?></strong><?= ($lead['form_page'] ?? '') !== '' ? ' on <span class="mono">' . e_a(strtok((string) $lead['form_page'], '?')) . '</span>' : '' ?><?= $lead['status'] === 'new' ? ' · waiting ' . e_a(waiting_for($lead)) . ' for a first reply' : '' ?></p>
        <?php if (!empty($_GET['sent'])): ?><div class="flash">✓ <?= ($_GET['sent'] === 'followup' ? 'Follow-up' : 'Proposal') ?> sent to <?= e_a($lead['email']) ?> from <?= e_a(CONTACT_EMAIL) ?>. When they open it, read it online or say yes, it shows up here and in the morning digest.</div><?php endif; ?>
        <div class="lead-actions">
            <a class="btn grad" href="/admin/?view=proposal&id=<?= e_a($lead['id']) ?>"><?= $hasProposal ? 'Send a new proposal' : 'Send proposal' ?></a>
            <?php if ($hasProposal): ?><a class="btn" href="/admin/?view=proposal&kind=followup&id=<?= e_a($lead['id']) ?>">Send follow-up</a><?php endif; ?>
            <?php if ($digits !== ''): ?>
            <a class="btn wa" href="https://wa.me/<?= e_a($digits) ?>?text=<?= rawurlencode($wa_text) ?>" target="_blank" rel="noopener">WhatsApp</a>
            <a class="btn ghost" href="tel:+<?= e_a($digits) ?>">Call</a>
            <?php endif; ?>
            <a class="btn ghost" href="mailto:<?= e_a($lead['email']) ?>">Email</a>
        </div>
        <div class="cards" style="grid-template-columns:1.4fr 1fr">
            <div class="panel" style="margin:0">
                <h2>Enquiry</h2>
                <table class="kv"><tbody>
                    <tr><td>Email</td><td><a href="mailto:<?= e_a($lead['email'] ?? '') ?>" style="font-weight:700;color:var(--p)"><?= e_a($lead['email'] ?? '') ?></a></td></tr>
                    <tr><td>Phone</td><td><?= $lead['mobile'] ? '<a href="tel:+' . e_a($digits) . '" style="font-weight:700">' . e_a($lead['mobile']) . '</a>' : '-' ?></td></tr>
                    <tr><td>Company</td><td><?= e_a($facts['company'] ?: '-') ?><?= $facts['website'] !== '' ? ' · <a href="' . e_a($facts['website']) . '" target="_blank" rel="noopener nofollow" style="color:var(--v);font-weight:700">' . e_a($facts['domain']) . ' ↗</a>' : '' ?></td></tr>
                    <tr><td>Service</td><td><?= e_a($lead['service'] ?: '-') ?></td></tr>
                    <tr><td>Budget</td><td><?= e_a($lead['budget'] ?: '-') ?></td></tr>
                    <?php if ($facts['timeline'] !== ''): ?><tr><td>Timeline</td><td><?= e_a($facts['timeline']) ?></td></tr><?php endif; ?>
                    <?php if ($facts['size'] !== ''): ?><tr><td>Company size</td><td><?= e_a($facts['size']) ?></td></tr><?php endif; ?>
                </tbody></table>
                <?php if ($facts['note'] !== ''): ?>
                <label style="margin-top:14px">In their words</label>
                <div class="quote"><?= nl2br(e_a($facts['note'])) ?></div>
                <?php endif; ?>
                <details style="margin-top:8px"><summary style="cursor:pointer;font-size:.78rem;color:var(--muted)">Full message as received</summary>
                    <p style="font-size:.82rem;margin-top:8px"><?= nl2br(e_a($lead['message'] ?: '-')) ?></p>
                </details>
            </div>
            <div>
                <div class="panel">
                    <h2>Stage</h2>
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
                    <h2>Deal</h2>
                    <form method="post">
                        <input type="hidden" name="action" value="lead_update">
                        <input type="hidden" name="csrf" value="<?= e_a($csrf) ?>">
                        <input type="hidden" name="id" value="<?= e_a($lead['id']) ?>">
                        <label for="d-value">Deal value (₹)</label>
                        <input id="d-value" name="value" type="text" inputmode="numeric" placeholder="e.g. 150000" value="<?= e_a($lead['value']) ?>">
                        <label for="d-prio">Priority</label>
                        <select id="d-prio" name="priority">
                            <?php foreach (ADMIN_PRIORITIES as $p): ?>
                            <option value="<?= $p ?>"<?= $lead['priority'] === $p ? ' selected' : '' ?>><?= ucfirst($p) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label for="d-owner">Owner</label>
                        <input id="d-owner" name="owner" type="text" placeholder="Who is handling this" value="<?= e_a($lead['owner']) ?>">
                        <label for="d-fup">Next follow-up</label>
                        <input id="d-fup" name="followup" type="date" value="<?= e_a($lead['followup']) ?>">
                        <label for="d-tags">Tags</label>
                        <input id="d-tags" name="tags" type="text" placeholder="comma, separated" value="<?= e_a($lead['tags']) ?>">
                        <br><button class="btn grad" type="submit" style="width:100%">Save deal details</button>
                    </form>
                </div>
                <div class="panel">
                    <form method="post" onsubmit="return confirm('Delete this lead? This cannot be undone.')">
                        <input type="hidden" name="action" value="lead_delete">
                        <input type="hidden" name="csrf" value="<?= e_a($csrf) ?>">
                        <input type="hidden" name="id" value="<?= e_a($lead['id']) ?>">
                        <button class="btn danger" type="submit">Delete lead</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="cards" style="grid-template-columns:1fr 1.4fr">
            <div class="panel" style="margin:0">
                <h2>How they found you</h2>
                <?php if ($attr): ?>
                <table class="kv"><tbody>
                    <tr><td>Source</td><td><?= channel_badge($lead) ?><?= ($attr['detail'] ?? '') !== '' ? ' <small>' . e_a((string) $attr['detail']) . '</small>' : '' ?></td></tr>
                    <?php if ($first_t): ?><tr><td>First visit</td><td><?= $touch_line($first_t) ?></td></tr><?php endif; ?>
                    <?php if ($last_t && $last_t !== $first_t): ?><tr><td>Latest visit</td><td><?= $touch_line($last_t) ?></td></tr><?php endif; ?>
                    <?php if (($attr['pages'] ?? 0) > 0):
                        $span = $first_t && ($first_t['at'] ?? '') !== '' ? (int) floor((strtotime($lead['date']) - strtotime($first_t['at'])) / 86400) : null; ?>
                    <tr><td>Pages viewed</td><td><?= (int) $attr['pages'] ?> before enquiring<?= $span === null ? '' : ($span < 1 ? ', on their first day' : ', over ' . $span . ($span === 1 ? ' day' : ' days')) ?></td></tr><?php endif; ?>
                    <tr><td>Form</td><td><?= e_a(ucfirst(CRM_PLACEMENTS[$attr['placement'] ?? ''] ?? (CRM_PLACEMENTS[$lead['source']] ?? $lead['source']))) ?><?= ($attr['form_page'] ?? '') !== '' ? ' on <span class="mono">' . e_a(strtok((string) $attr['form_page'], '?')) . '</span>' : '' ?></td></tr>
                    <?php if (($attr['device'] ?? '') !== '' || ($attr['country'] ?? '') !== ''): ?><tr><td>Device</td><td><?= e_a(trim(($attr['device'] ?? '') . (($attr['country'] ?? '') !== '' ? ', ' . (country_names()[$attr['country']] ?? $attr['country']) : ''), ', ')) ?></td></tr><?php endif; ?>
                    <?php $tags = array_merge($first_t['q'] ?? [], $last_t['q'] ?? []); if ($tags): ?><tr><td>Campaign tags</td><td class="mono" style="font-size:.74rem"><?php foreach ($tags as $k => $v): ?><?= e_a($k) ?>=<?= e_a(mb_substr((string) $v, 0, 40)) ?><br><?php endforeach; ?></td></tr><?php endif; ?>
                </tbody></table>
                <?php else: ?>
                <p class="note">This lead arrived before source tracking started (or from a browser that blocks it), so where they came from was not recorded.<?= ($lead['form_page'] ?? '') !== '' ? ' They sent the form from <span class="mono">' . e_a((string) $lead['form_page']) . '</span>.' : '' ?></p>
                <?php endif; ?>
            </div>
            <div class="panel" style="margin:0">
                <h2>Proposals and follow-ups</h2>
                <?php if ($props): ?>
                <ul class="prop-list">
                <?php foreach ($props as $p): ?>
                    <li>
                        <strong><?= e_a($p['subject']) ?></strong>
                        <div class="meta">
                            <span><?= $p['kind'] === 'followup' ? 'Follow-up' : 'Proposal' ?> sent <?= e_a(date('j M, H:i', strtotime((string) $p['sent_at']))) ?><?= $p['sent_by'] !== '' ? ' by ' . e_a($p['sent_by']) : '' ?></span>
                            <?= !empty($p['opened_at']) ? '<span class="badge-st" style="--st:#00B8D9">Opened' . ((int) $p['open_count'] > 1 ? ' ×' . (int) $p['open_count'] : '') . '</span>' : '<span>not opened yet</span>' ?>
                            <?= !empty($p['viewed_at']) ? '<span class="badge-st" style="--st:#FF9100">Read online' . ((int) $p['view_count'] > 1 ? ' ×' . (int) $p['view_count'] : '') . ((int) $p['read_seconds'] >= 60 ? ', ' . (int) round($p['read_seconds'] / 60) . ' min' : '') . '</span>' : '' ?>
                            <?= !empty($p['accepted_at']) ? '<span class="badge-st" style="--st:#12A150">Said yes ' . e_a(date('j M', strtotime((string) $p['accepted_at']))) . '</span>' : '' ?>
                            <?php if ($p['kind'] === 'proposal'): ?><a href="<?= e_a(crm_preview_url((string) $p['token'])) ?>" target="_blank" rel="noopener" style="color:var(--v);font-weight:700">View as the client sees it ↗</a><?php endif; ?>
                        </div>
                        <?php if (($p['accept_note'] ?? '') !== ''): ?><div class="quote" style="margin-top:8px"><?= nl2br(e_a((string) $p['accept_note'])) ?></div><?php endif; ?>
                    </li>
                <?php endforeach; ?>
                </ul>
                <p class="note">"Opened" comes from the email's image loading, which some mail apps do on their own. "Read online" and "Said yes" are certain.</p>
                <?php else: ?>
                <p class="note">No proposal yet. <a href="/admin/?view=proposal&id=<?= e_a($lead['id']) ?>" style="color:var(--p);font-weight:800">Draft one now</a>: it is written from this enquiry, and you can edit every word before it goes.</p>
                <?php endif; ?>
                <?php if ($others): ?>
                <h2 style="margin-top:16px">Other enquiries from this person</h2>
                <table><tbody>
                <?php foreach ($others as $o): ?>
                    <tr><td><?= e_a(date('j M Y', strtotime($o['date']))) ?></td><td><?= e_a(mb_substr((string) $o['service'], 0, 40)) ?></td><td><?= status_badge($o['status']) ?></td><td><a class="btn ghost sm" href="/admin/?view=lead&id=<?= e_a($o['id']) ?>">Open</a></td></tr>
                <?php endforeach; ?>
                </tbody></table>
                <?php endif; ?>
            </div>
        </div>
        <div class="cards" style="grid-template-columns:1.4fr 1fr">
            <div class="panel" style="margin:0">
                <h2>Notes</h2>
                <form method="post" class="actions-row">
                    <input type="hidden" name="action" value="lead_note">
                    <input type="hidden" name="csrf" value="<?= e_a($csrf) ?>">
                    <input type="hidden" name="id" value="<?= e_a($lead['id']) ?>">
                    <input type="text" name="note" placeholder="Call summary, next step…" style="flex:1" required>
                    <button class="btn grad" type="submit">Add</button>
                </form>
                <ul class="notes-list">
                    <?php foreach (array_reverse($lead['notes']) as $n): ?>
                    <li><?= e_a($n['text']) ?><small><?= e_a($n['date']) ?></small></li>
                    <?php endforeach; if (!$lead['notes']): ?><li style="color:var(--muted)">No notes yet.</li><?php endif; ?>
                </ul>
            </div>
            <div class="panel" style="margin:0">
                <h2>Activity</h2>
                <ul class="timeline">
                    <?php foreach (array_reverse($lead['activity']) as $a): ?>
                    <li><span><?= e_a($a['text']) ?></span><small><?= e_a($a['date']) ?></small></li>
                    <?php endforeach; ?>
                    <li><span>Lead captured from the <?= e_a(CRM_PLACEMENTS[$lead['source'] ?? ''] ?? ($lead['source'] ?? 'website')) ?><?= $lead['channel'] !== 'Not tracked' ? ' (came from ' . e_a($lead['channel']) . ')' : '' ?></span><small><?= e_a(date('Y-m-d H:i', strtotime($lead['date'] ?? 'now'))) ?></small></li>
                </ul>
            </div>
        </div>
        <?php endif; ?>

    <?php elseif ($view === 'proposal'):
        /* The composer: a draft written from the enquiry, edited by a person, sent from connect@vturnu.com. */
        $lead = null;
        foreach ($leads as $l) { if ($l['id'] === ($_GET['id'] ?? '')) { $lead = $l; break; } }
        if (!$lead): ?>
        <h1>Lead not found</h1>
        <p class="sub"><a href="/admin/?view=leads">← Sales leads</a></p>
        <?php else:
            $props = crm_proposals((int) $lead['id']);
            $parent = null;
            foreach ($props as $pp) { if ($pp['kind'] === 'proposal') { $parent = $pp; break; } }
            $kind = ($_GET['kind'] ?? '') === 'followup' && $parent ? 'followup' : 'proposal';
            $crm_cfg = crm_settings();
            $draft = $kind === 'followup' ? crm_followup_draft($lead, $parent, $crm_cfg) : crm_proposal_draft($lead, $crm_cfg);
            $facts = crm_lead_facts($lead);
            $intent = $draft['intent'] ?? crm_intent($facts);
            $c_subject = (string) ($composer_post['subject'] ?? $draft['subject']);
            $c_body = (string) ($composer_post['body'] ?? $draft['body']);
        ?>
        <h1><?= $kind === 'followup' ? 'Follow-up to ' : 'Proposal for ' ?><?= e_a($lead['name']) ?><?= $facts['company'] !== '' ? ', ' . e_a($facts['company']) : '' ?></h1>
        <p class="sub"><a href="/admin/?view=lead&id=<?= e_a($lead['id']) ?>">← Back to the lead</a> · drafted from their enquiry. Read it, change anything you like, then send.</p>
        <div class="composer">
            <form method="post" action="/admin/?view=proposal&id=<?= e_a($lead['id']) ?><?= $kind === 'followup' ? '&kind=followup' : '' ?>" class="panel" style="margin:0" data-confirm="<?= e_a('Send this ' . ($kind === 'followup' ? 'follow-up' : 'proposal') . ' to ' . $lead['email'] . ' now?') ?>" onsubmit="return confirm(this.dataset.confirm)">
                <input type="hidden" name="action" value="proposal_send">
                <input type="hidden" name="csrf" value="<?= e_a($csrf) ?>">
                <input type="hidden" name="id" value="<?= e_a($lead['id']) ?>">
                <input type="hidden" name="kind" value="<?= $kind ?>">
                <table class="kv"><tbody>
                    <tr><td>From</td><td><?= e_a(crm_from($crm_cfg)) ?></td></tr>
                    <tr><td>To</td><td><?= e_a($lead['name']) ?> &lt;<?= e_a($lead['email']) ?>&gt;</td></tr>
                    <tr><td>Replies go to</td><td><?= e_a(CONTACT_EMAIL) ?></td></tr>
                </tbody></table>
                <label for="pc-subject">Subject</label>
                <input id="pc-subject" name="subject" maxlength="200" required value="<?= e_a($c_subject) ?>">
                <label for="pc-body">Message <span style="text-transform:none;font-weight:400;letter-spacing:0">("## " starts a heading, "- " a bullet, **bold** for emphasis)</span></label>
                <textarea id="pc-body" name="body" class="body" required><?= e_a($c_body) ?></textarea>
                <p class="dash-warn" id="pc-dash">This text contains a long dash. It is replaced with a comma (or "to" between numbers) when sent.</p>
                <div class="opts">
                    <?php if ($kind === 'proposal'): ?><label class="chk"><input type="checkbox" name="stage" value="1" checked> Move the lead to the Proposal stage</label><?php endif; ?>
                    <label class="chk"><input type="checkbox" name="remind" value="1" checked> Remind me to follow up in <input type="number" name="remind_days" min="1" max="30" value="<?= (int) $crm_cfg['followup_days'] ?: 3 ?>"> days</label>
                    <label class="chk"><input type="checkbox" name="copy" value="1" checked> Send a copy to <?= e_a(ENQUIRY_TO) ?></label>
                </div>
                <div class="actions-row">
                    <button class="btn grad" type="submit">Send to <?= e_a($lead['email']) ?></button>
                    <a class="btn ghost" href="/admin/?view=proposal&id=<?= e_a($lead['id']) ?><?= $kind === 'followup' ? '&kind=followup' : '' ?>">Start again from a fresh draft</a>
                    <?php if ($kind === 'proposal' && $parent): ?><a class="btn ghost" href="/admin/?view=proposal&kind=followup&id=<?= e_a($lead['id']) ?>">Write a follow-up instead</a><?php endif; ?>
                </div>
                <?php if ($kind === 'proposal'): ?><p class="note">The email carries a button to a private web copy of the proposal. You will see when it is opened and read, and they can say yes there in one click.</p><?php endif; ?>
            </form>
            <div>
                <?php if ($kind === 'proposal'): ?>
                <div class="panel">
                    <h2>What the draft is based on</h2>
                    <ul class="detected">
                        <li><b>Service</b><?= e_a($lead['service'] ?: 'Not chosen') ?></li>
                        <li><b>Their goals</b><?= e_a(crm_list(array_map(fn($g) => CRM_GOALS[$g][1], array_slice($intent['goals'], 0, 3)))) ?></li>
                        <?php if ($intent['quote'] !== ''): ?><li><b>Quoted back</b>"<?= e_a($intent['quote']) ?>"</li><?php endif; ?>
                        <?php if ($intent['industry'] !== ''): ?><li><b>Industry</b><?= e_a(ucfirst($intent['industry'])) ?> (from their words and company)</li><?php endif; ?>
                        <?php if ($intent['horizon'] !== '' || $intent['target'] !== ''): ?><li><b>Target</b><?= e_a(trim($intent['target'] . ($intent['horizon'] !== '' ? ' within ' . $intent['horizon'] : ''))) ?></li><?php endif; ?>
                        <?php if ($intent['markets']): ?><li><b>Markets</b><?= e_a(crm_list($intent['markets'])) ?></li><?php endif; ?>
                        <li><b>Budget</b><?= e_a($lead['budget'] ?: 'Not given') ?></li>
                        <li><b>Timeline</b><?= e_a($facts['timeline'] ?: 'Not given') ?></li>
                        <li><b>Proof cited</b><?= ($draft['proof'] ?? []) ? e_a(crm_list(array_map(fn($p) => $p['client'], $draft['proof']))) : 'None that fits this service, so none is claimed' ?></li>
                        <li><b>Source</b><?= channel_badge($lead) ?></li>
                    </ul>
                    <p class="note">Case study figures are quoted exactly as published on the site. Change anything that does not fit this client.</p>
                </div>
                <?php endif; ?>
                <div class="mail-preview">
                    <div class="mp-head"><b id="pc-psubject"><?= e_a($c_subject) ?></b><br>From <?= e_a(crm_from($crm_cfg)) ?> to <?= e_a($lead['email']) ?></div>
                    <div id="pc-preview"><?= crm_markup_html(crm_no_dashes($c_body)) ?></div>
                    <?php if ($kind === 'proposal' || $parent): ?><p style="margin:6px 0 14px"><span style="display:inline-block;padding:10px 20px;border-radius:999px;background:#4338CA;color:#fff;font:bold 13px Arial">Open your proposal</span></p><?php endif; ?>
                    <p style="font:13px/1.55 Arial;color:#4A4470"><strong style="color:#1D1936"><?= e_a((string) $crm_cfg['sender_name']) ?></strong><br><?= e_a((string) $crm_cfg['sender_title']) ?>, VTurnU<br><?= e_a((string) $crm_cfg['sender_phone']) ?> · vturnu.com</p>
                </div>
            </div>
        </div>
        <script>
        (function () {
            var body = document.getElementById('pc-body'), subject = document.getElementById('pc-subject');
            var out = document.getElementById('pc-preview'), head = document.getElementById('pc-psubject'), warn = document.getElementById('pc-dash');
            function esc(s) { return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); }
            function inline(s) {
                return esc(s).replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                    .replace(/https?:\/\/[^\s<]+[^\s<.,;:!?)]/g, function (u) { return '<a href="' + u + '" style="color:#4338CA">' + u + '</a>'; });
            }
            var P = 'margin:0 0 14px;font:15px/1.65 Arial,Helvetica,sans-serif;color:#1D1936', H = 'margin:22px 0 8px;font:bold 16px/1.4 Arial,Helvetica,sans-serif;color:#4338CA';
            function render() {
                var text = body.value.replace(/(\d)\s*[–—]\s*(\d)/g, '$1 to $2').replace(/\s*[—―]\s*/g, ', ').replace(/\s+–\s+/g, ', ');
                var html = [], list = [], para = [];
                function flush() {
                    if (para.length) { html.push('<p style="' + P + '">' + para.join('<br>') + '</p>'); para = []; }
                    if (list.length) { html.push('<ul style="margin:0 0 14px;padding-left:20px">' + list.join('') + '</ul>'); list = []; }
                }
                text.split(/\r?\n/).forEach(function (line) {
                    var t = line.replace(/\s+$/, '');
                    var m = t.match(/^\s*[-*] (.+)$/);
                    if (t.indexOf('## ') === 0) { flush(); html.push('<h2 style="' + H + '">' + inline(t.slice(3)) + '</h2>'); }
                    else if (m) { if (para.length) { html.push('<p style="' + P + '">' + para.join('<br>') + '</p>'); para = []; } list.push('<li style="margin:0 0 7px;font:15px/1.6 Arial">' + inline(m[1]) + '</li>'); }
                    else if (!t.trim()) { flush(); }
                    else { if (list.length) { html.push('<ul style="margin:0 0 14px;padding-left:20px">' + list.join('') + '</ul>'); list = []; } para.push(inline(t)); }
                });
                flush();
                out.innerHTML = html.join('');
                head.textContent = subject.value;
                warn.style.display = /[–—]/.test(body.value + subject.value) ? 'block' : 'none';
            }
            body.addEventListener('input', render);
            subject.addEventListener('input', render);
            render();
        })();
        </script>
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

    <?php elseif ($view === 'subscribers'):
        $f_q = trim($_GET['q'] ?? '');
        $scope = leads_in($leads, 'newsletter');
        $filtered = $f_q === '' ? $scope : array_values(array_filter($scope, function ($l) use ($f_q) {
            $hay = strtolower(($l['name'] ?? '') . ' ' . ($l['email'] ?? '') . ' ' . ($l['company'] ?? ''));
            return str_contains($hay, strtolower($f_q));
        }));
        /* One person can subscribe twice from two devices. Counting unique
           addresses rather than rows keeps the list honest. */
        $unique = array_unique(array_map(fn($l) => strtolower(trim($l['email'] ?? '')), $scope));
        $unique = array_filter($unique);
        $week  = date('c', strtotime('-7 days'));
        $month = date('c', strtotime('-30 days'));
        // Signups per month, most recent first, for the mini trend table.
        $by_month = [];
        foreach ($scope as $l) {
            $m = substr((string) ($l['date'] ?? ''), 0, 7);
            if ($m !== '') { $by_month[$m] = ($by_month[$m] ?? 0) + 1; }
        }
        krsort($by_month);
        $peak = $by_month ? max($by_month) : 1;
    ?>
        <h1>Newsletter subscribers</h1>
        <p class="sub">Everyone who opted in from the newsletter form.
            <?= count($filtered) ?> of <?= count($scope) ?> shown ·
            <a href="/admin/?view=export&seg=newsletter" style="font-weight:800;color:var(--p)">Export CSV ↓</a></p>

        <div class="cards">
            <div class="kpi grad"><b><?= count($unique) ?></b><span>Unique subscribers</span></div>
            <div class="kpi"><b><?= count(array_filter($scope, fn($l) => ($l['date'] ?? '') >= $week)) ?></b><span>New this week</span></div>
            <div class="kpi"><b><?= count(array_filter($scope, fn($l) => ($l['date'] ?? '') >= $month)) ?></b><span>New in 30 days</span></div>
            <div class="kpi"><b><?= count($scope) - count($unique) ?></b><span>Duplicate signups</span></div>
        </div>

        <div class="cards" style="grid-template-columns:1fr 1fr">
            <div class="panel" style="margin:0">
                <h2>Signups by month</h2>
                <table><tbody>
                <?php foreach (array_slice($by_month, 0, 8, true) as $m => $n): ?>
                    <tr>
                        <td style="width:92px"><?= e_a(date('M Y', strtotime($m . '-01'))) ?></td>
                        <td><span class="barline"><i style="width:<?= (int) round($n / $peak * 100) ?>%;background:var(--grad-v)"></i></span></td>
                        <td style="text-align:right;font-weight:800;width:40px"><?= $n ?></td>
                    </tr>
                <?php endforeach; if (!$by_month): ?><tr><td>No signups yet.</td></tr><?php endif; ?>
                </tbody></table>
            </div>
            <div class="panel" style="margin:0">
                <h2>Working this list</h2>
                <p class="note">A subscriber is not a lead. They asked to hear from you, which is permission to stay useful, not permission to pitch.</p>
                <p class="note">Someone who subscribes <em>and</em> appears in audit requests or downloads is a different story. Cross-check the email before you write it off as a cold contact.</p>
            </div>
        </div>

        <form class="filters" method="get" action="/admin/">
            <input type="hidden" name="view" value="subscribers">
            <input type="search" name="q" placeholder="Search name, email, company…" value="<?= e_a($f_q) ?>" style="width:280px">
            <button class="btn ghost" type="submit">Search</button>
            <?php if ($f_q !== ''): ?><a class="btn ghost" href="/admin/?view=subscribers">Clear</a><?php endif; ?>
        </form>

        <div class="panel">
            <table>
                <thead><tr><th>When</th><th>Name</th><th>Email</th><th>Also seen in</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($filtered as $l):
                    /* Cross-reference: the same address turning up in another
                       funnel is the single most useful thing on this screen. */
                    $mail = strtolower(trim($l['email'] ?? ''));
                    $also = [];
                    foreach ($leads as $other) {
                        if (strtolower(trim($other['email'] ?? '')) !== $mail || $mail === '') continue;
                        $seg = lead_segment($other);
                        if ($seg !== 'newsletter') { $also[$seg] = true; }
                    }
                    $labels_seg = ['lead' => 'Sales lead', 'audit' => 'Audit', 'ebook' => 'Download'];
                ?>
                    <tr>
                        <td style="white-space:nowrap"><?= e_a(date('j M Y', strtotime($l['date'] ?? 'now'))) ?></td>
                        <td><strong><?= e_a($l['name'] ?? '') ?></strong></td>
                        <td><a href="mailto:<?= e_a($l['email'] ?? '') ?>"><?= e_a($l['email'] ?? '') ?></a></td>
                        <td><?php if ($also): foreach (array_keys($also) as $s): ?><span class="tag-ro"><?= e_a($labels_seg[$s] ?? $s) ?></span> <?php endforeach; else: ?><span style="color:var(--muted)">-</span><?php endif; ?></td>
                        <td><a class="btn ghost sm" href="/admin/?view=lead&id=<?= e_a($l['id']) ?>">Open</a></td>
                    </tr>
                <?php endforeach; if (!$filtered): ?><tr><td colspan="5">No subscribers yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>

    <?php elseif ($view === 'reports'):
        /* Everything here is derived from the same lead store the other screens
           read. No separate analytics file to fall out of sync. */
        $months = [];
        for ($i = 11; $i >= 0; $i--) { $months[date('Y-m', strtotime("-$i months"))] = ['n' => 0, 'won' => 0, 'value' => 0]; }
        foreach ($leads as $l) {
            $m = substr((string) ($l['date'] ?? ''), 0, 7);
            if (!isset($months[$m])) continue;
            $months[$m]['n']++;
            if ($l['status'] === 'won') { $months[$m]['won']++; $months[$m]['value'] += (int) $l['value']; }
        }
        $peak_m = max(1, max(array_column($months, 'n')));

        /* Source performance: volume alone is misleading, because the form that
           produces the most contacts is rarely the one that produces the most
           revenue. Win rate and booked value are what decide where to invest. */
        $src_stats = [];
        foreach ($leads as $l) {
            $s = $l['source'] ?: 'unknown';
            $src_stats[$s] ??= ['n' => 0, 'won' => 0, 'lost' => 0, 'value' => 0];
            $src_stats[$s]['n']++;
            if ($l['status'] === 'won')  { $src_stats[$s]['won']++;  $src_stats[$s]['value'] += (int) $l['value']; }
            if ($l['status'] === 'lost') { $src_stats[$s]['lost']++; }
        }
        uasort($src_stats, fn($a, $b) => $b['value'] <=> $a['value'] ?: $b['n'] <=> $a['n']);

        /* The same question by channel: which searches, ads, AI assistants and
           sites bring leads that turn into proposals, yeses and revenue. */
        $chan_stats = [];
        foreach (leads_in($leads, 'lead') as $l) {
            $c = $l['channel'];
            $chan_stats[$c] ??= ['group' => $l['channel_group'], 'n' => 0, 'a' => 0, 'prop' => 0, 'yes' => 0, 'won' => 0, 'value' => 0];
            $chan_stats[$c]['n']++;
            $chan_stats[$c]['a'] += $l['grade'] === 'A' ? 1 : 0;
            $ps = crm_proposal_state($proposal_index[$l['id']] ?? null);
            $chan_stats[$c]['prop'] += $ps !== 'none' ? 1 : 0;
            $chan_stats[$c]['yes'] += $ps === 'accepted' ? 1 : 0;
            if ($l['status'] === 'won') { $chan_stats[$c]['won']++; $chan_stats[$c]['value'] += (int) $l['value']; }
        }
        uasort($chan_stats, fn($a, $b) => $b['value'] <=> $a['value'] ?: $b['n'] <=> $a['n']);

        $owner_stats = [];
        foreach ($leads as $l) {
            $o = $l['owner'] !== '' ? $l['owner'] : 'Unassigned';
            $owner_stats[$o] ??= ['n' => 0, 'won' => 0, 'value' => 0, 'open' => 0];
            $owner_stats[$o]['n']++;
            if ($l['status'] === 'won') { $owner_stats[$o]['won']++; $owner_stats[$o]['value'] += (int) $l['value']; }
            if (!in_array($l['status'], ADMIN_CLOSED, true)) { $owner_stats[$o]['open']++; }
        }
        uasort($owner_stats, fn($a, $b) => $b['value'] <=> $a['value']);

        $seg_stats = [];
        foreach (['lead' => 'Sales leads', 'audit' => 'Audit requests', 'ebook' => 'E-book downloads', 'newsletter' => 'Subscribers'] as $k => $label) {
            $rows = leads_in($leads, $k);
            $won = count(array_filter($rows, fn($l) => $l['status'] === 'won'));
            $seg_stats[$label] = ['n' => count($rows), 'won' => $won,
                'value' => array_sum(array_map(fn($l) => $l['status'] === 'won' ? (int) $l['value'] : 0, $rows))];
        }

        $total_won = array_sum(array_map(fn($l) => $l['status'] === 'won' ? (int) $l['value'] : 0, $leads));
        $won_count = count(array_filter($leads, fn($l) => $l['status'] === 'won'));
        $avg_deal  = $won_count ? (int) round($total_won / $won_count) : 0;
        $no_owner  = count(array_filter($leads, fn($l) => $l['owner'] === '' && !in_array($l['status'], ADMIN_CLOSED, true)));
        $no_value  = count(array_filter($leads, fn($l) => $l['value'] === '' && !in_array($l['status'], ADMIN_CLOSED, true)));
        $stale     = count(array_filter($leads, fn($l) => $l['status'] === 'new' && ($l['date'] ?? '') < date('c', strtotime('-14 days'))));
    ?>
        <h1>Reports</h1>
        <p class="sub">Where pipeline actually comes from, and what it is worth.</p>

        <div class="cards">
            <div class="kpi grad"><b>₹<?= money_fmt($total_won) ?: '0' ?></b><span>Revenue booked</span></div>
            <div class="kpi"><b>₹<?= money_fmt($avg_deal) ?: '0' ?></b><span>Average won deal</span></div>
            <div class="kpi"><b><?= count($leads) ?></b><span>Contacts, all funnels</span></div>
            <div class="kpi"><b><?= $won_count ?></b><span>Deals won</span></div>
        </div>

        <?php if ($no_owner || $no_value || $stale): ?>
        <div class="panel" style="border-color:rgba(255,61,138,.35)">
            <h2>Data gaps worth closing</h2>
            <p class="note">Reports are only as good as what gets filled in. These open deals are missing the fields the numbers above depend on.</p>
            <div class="actions-row" style="margin-top:10px">
                <?php if ($no_owner): ?><a class="btn ghost" href="/admin/?view=leads"><?= $no_owner ?> open with no owner</a><?php endif; ?>
                <?php if ($no_value): ?><a class="btn ghost" href="/admin/?view=leads"><?= $no_value ?> open with no deal value</a><?php endif; ?>
                <?php if ($stale): ?><a class="btn ghost" href="/admin/?view=leads&status=new"><?= $stale ?> untouched for 14+ days</a><?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="panel">
            <h2>Last 12 months</h2>
            <table>
                <thead><tr><th>Month</th><th>Contacts</th><th style="width:44%">Volume</th><th>Won</th><th style="text-align:right">Value</th></tr></thead>
                <tbody>
                <?php foreach (array_reverse($months, true) as $m => $d): ?>
                    <tr>
                        <td style="white-space:nowrap"><?= e_a(date('M Y', strtotime($m . '-01'))) ?></td>
                        <td style="font-weight:800"><?= $d['n'] ?></td>
                        <td><span class="barline"><i style="width:<?= (int) round($d['n'] / $peak_m * 100) ?>%;background:var(--grad-v)"></i></span></td>
                        <td><?= $d['won'] ?: '<span style="color:var(--muted)">-</span>' ?></td>
                        <td style="text-align:right;font-weight:800"><?= $d['value'] ? '₹' . money_fmt($d['value']) : '<span style="color:var(--muted);font-weight:400">-</span>' ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="panel">
            <h2>Which channel earns its keep</h2>
            <table>
                <thead><tr><th>Channel</th><th>Sales leads</th><th>Grade A</th><th>Proposals</th><th>Said yes</th><th>Won</th><th style="text-align:right">Revenue</th></tr></thead>
                <tbody>
                <?php foreach ($chan_stats as $c => $d): ?>
                    <tr>
                        <td><?= channel_badge(['channel' => $c, 'channel_group' => $d['group'], 'attribution' => []]) ?> <small style="color:var(--muted)"><?= e_a((string) $d['group']) ?></small></td>
                        <td style="font-weight:800"><?= $d['n'] ?></td>
                        <td><?= $d['a'] ?: '<span style="color:var(--muted)">-</span>' ?></td>
                        <td><?= $d['prop'] ?: '<span style="color:var(--muted)">-</span>' ?></td>
                        <td><?= $d['yes'] ?: '<span style="color:var(--muted)">-</span>' ?></td>
                        <td><?= $d['won'] ?: '<span style="color:var(--muted)">-</span>' ?></td>
                        <td style="text-align:right;font-weight:800"><?= $d['value'] ? '₹' . money_fmt($d['value']) : '<span style="color:var(--muted);font-weight:400">-</span>' ?></td>
                    </tr>
                <?php endforeach; if (!$chan_stats): ?><tr><td colspan="7">No sales leads yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="panel">
            <h2>Which form earns its keep</h2>
            <table>
                <thead><tr><th>Form</th><th>Contacts</th><th>Won</th><th>Lost</th><th>Win rate</th><th style="text-align:right">Revenue</th></tr></thead>
                <tbody>
                <?php foreach ($src_stats as $s => $d):
                    $closed = $d['won'] + $d['lost'];
                    $wr = $closed ? round($d['won'] / $closed * 100) : null; ?>
                    <tr>
                        <td><strong><?= e_a($s) ?></strong></td>
                        <td><?= $d['n'] ?></td>
                        <td><?= $d['won'] ?></td>
                        <td><?= $d['lost'] ?></td>
                        <td><?= $wr === null ? '<span style="color:var(--muted)">no closes yet</span>' : $wr . '%' ?></td>
                        <td style="text-align:right;font-weight:800"><?= $d['value'] ? '₹' . money_fmt($d['value']) : '<span style="color:var(--muted);font-weight:400">-</span>' ?></td>
                    </tr>
                <?php endforeach; if (!$src_stats): ?><tr><td colspan="6">No leads yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="cards" style="grid-template-columns:1fr 1fr">
            <div class="panel" style="margin:0">
                <h2>By funnel</h2>
                <table>
                    <thead><tr><th>Funnel</th><th>Contacts</th><th>Won</th><th style="text-align:right">Revenue</th></tr></thead>
                    <tbody>
                    <?php foreach ($seg_stats as $label => $d): ?>
                        <tr>
                            <td><?= e_a($label) ?></td>
                            <td style="font-weight:800"><?= $d['n'] ?></td>
                            <td><?= $d['won'] ?: '-' ?></td>
                            <td style="text-align:right"><?= $d['value'] ? '₹' . money_fmt($d['value']) : '-' ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="panel" style="margin:0">
                <h2>By owner</h2>
                <table>
                    <thead><tr><th>Owner</th><th>Open</th><th>Won</th><th style="text-align:right">Revenue</th></tr></thead>
                    <tbody>
                    <?php foreach ($owner_stats as $o => $d): ?>
                        <tr>
                            <td><?= e_a($o) ?></td>
                            <td><?= $d['open'] ?></td>
                            <td><?= $d['won'] ?></td>
                            <td style="text-align:right;font-weight:800"><?= $d['value'] ? '₹' . money_fmt($d['value']) : '-' ?></td>
                        </tr>
                    <?php endforeach; if (!$owner_stats): ?><tr><td colspan="4">No leads yet.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

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
        <?php $crm_cfg = crm_settings(); ?>
        <div class="panel" style="max-width:640px">
            <h2>Proposals and automation</h2>
            <form method="post">
                <input type="hidden" name="action" value="crm_settings">
                <input type="hidden" name="csrf" value="<?= e_a($csrf) ?>">
                <p class="note" style="margin:0 0 4px">Proposals, follow-ups and acknowledgements are sent from <strong><?= e_a(CONTACT_EMAIL) ?></strong> under this name, and replies come back to it.</p>
                <div class="grid3">
                    <div><label for="s-name">Sender name</label><input id="s-name" name="sender_name" maxlength="60" value="<?= e_a((string) $crm_cfg['sender_name']) ?>"></div>
                    <div><label for="s-title">Title</label><input id="s-title" name="sender_title" maxlength="60" value="<?= e_a((string) $crm_cfg['sender_title']) ?>"></div>
                    <div><label for="s-phone">Phone / WhatsApp</label><input id="s-phone" name="sender_phone" maxlength="30" value="<?= e_a((string) $crm_cfg['sender_phone']) ?>"></div>
                </div>
                <div class="opts" style="display:grid;gap:8px;margin-top:14px">
                    <label class="chk"><input type="checkbox" name="auto_ack" value="1"<?= $crm_cfg['auto_ack'] ? ' checked' : '' ?>> Send every new sales enquiry an instant, personal acknowledgement</label>
                    <label class="chk"><input type="checkbox" name="digest" value="1"<?= $crm_cfg['digest'] ? ' checked' : '' ?>> Email a morning digest to <?= e_a(ENQUIRY_TO) ?> at 9:00 am IST (new leads, waiting leads, follow-ups due, proposals read or accepted)</label>
                    <label class="chk">Default follow-up reminder after a proposal: <input type="number" name="followup_days" min="0" max="30" value="<?= (int) $crm_cfg['followup_days'] ?>" style="width:70px;padding:4px 8px"> days</label>
                </div>
                <br><button class="btn grad" type="submit">Save</button>
            </form>
            <form method="post" style="margin-top:12px">
                <input type="hidden" name="action" value="digest_now">
                <input type="hidden" name="csrf" value="<?= e_a($csrf) ?>">
                <button class="btn ghost" type="submit">Send the digest now</button>
            </form>
        </div>
        <div class="panel" style="max-width:500px">
            <h2>Data & Backups</h2>
            <p style="font-size:.84rem;color:var(--muted);margin-bottom:12px">Leads, CRM state and custom content are all stored in Postgres (the <span class="mono">enquiries</span>, <span class="mono">admin_users</span> and <span class="mono">content_overrides</span> tables). Export leads to CSV below for an offline copy.</p>
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
