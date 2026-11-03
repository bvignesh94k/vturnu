<?php
/* Test bootstrap: the site's includes with an in-memory stand-in for Postgres
   and captured email, so CRM code runs exactly as written. */
declare(strict_types=1);

error_reporting(E_ALL);
set_error_handler(static function (int $no, string $str, string $file, int $line): bool {
    throw new ErrorException($str, 0, $no, $file, $line);
});
$_SERVER['MAIL_TRANSPORT'] = 'capture';
$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148';
$_SERVER['HTTP_X_VERCEL_IP_COUNTRY'] = 'IN';
$_SERVER['REMOTE_ADDR'] = '203.0.113.9';
$_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ?? '/';
$_SERVER['REQUEST_METHOD'] = $_SERVER['REQUEST_METHOD'] ?? 'GET';

final class FakeStmt
{
    private array $rows = [];
    private int $i = 0;
    public function __construct(private FakeDb $db, private string $sql) {}
    public function execute(array $params = []): bool { $this->rows = $this->db->run($this->sql, $params); $this->i = 0; return true; }
    public function fetchAll(): array { return $this->rows; }
    public function fetch(): mixed { return $this->rows[$this->i++] ?? false; }
    public function fetchColumn(): mixed { $r = $this->rows[$this->i++] ?? null; return $r ? reset($r) : false; }
}

final class FakeDb
{
    public array $log = [];
    public array $leads = [];
    public array $proposals = [];
    public array $settings = [];
    public function query(string $sql): FakeStmt { $s = new FakeStmt($this, $sql); $s->execute(); return $s; }
    public function prepare(string $sql): FakeStmt { return new FakeStmt($this, $sql); }
    public function run(string $sql, array $p): array
    {
        $this->log[] = [$sql, $p];
        $t = trim($sql);
        if (str_contains($sql, 'FROM admin_users')) return [['username' => 'admin', 'password_hash' => '$2y$10$abcdefghijklmnopqrstuuMPx0S6m7iUwBwRYeP/Vnb2ZZqbgFfK6']];
        if (str_contains($sql, 'content_overrides')) return [];
        if (str_contains($sql, 'FROM crm_settings')) return $this->settings ? [['value' => json_encode($this->settings)]] : [];
        if (str_starts_with($t, 'INSERT INTO crm_settings')) { $this->settings = json_decode((string) $p[0], true); return []; }
        if (str_contains($sql, 'to_char(followup') && str_contains($sql, 'FROM enquiries WHERE deleted_at IS NULL AND status')) {
            if (str_contains($sql, "= 'junk'")) return [];
            return array_map(static fn(array $l): array => array_map(static fn($v) => is_array($v) ? json_encode($v) : $v, $l), $this->leads);
        }
        if (str_contains($sql, 'SELECT DISTINCT ON (enquiry_id)')) {
            $out = [];
            foreach ($this->proposals as $pr) {
                if ($pr['kind'] !== 'proposal') continue;
                $id = $pr['enquiry_id'];
                if (!isset($out[$id]) || $pr['sent_at'] > $out[$id]['sent_at']) {
                    $all = array_filter($this->proposals, static fn($x) => $x['enquiry_id'] === $id);
                    $out[$id] = $pr + ['sent_count' => count($all), 'any_viewed' => max(array_column($all, 'viewed_at') ?: [null]), 'any_accepted' => max(array_column($all, 'accepted_at') ?: [null])];
                }
            }
            return array_values($out);
        }
        if (str_contains($sql, 'FROM proposals WHERE enquiry_id = ?')) return array_values(array_filter($this->proposals, static fn($x) => (int) $x['enquiry_id'] === (int) $p[0]));
        if (str_contains($sql, 'FROM proposals WHERE token = ?')) return array_values(array_filter($this->proposals, static fn($x) => $x['token'] === $p[0]));
        if (str_contains($sql, 'SELECT * FROM enquiries WHERE id = ?')) {
            foreach ($this->leads as $l) { if ((int) $l['id'] === (int) $p[0]) return [array_map(static fn($v) => is_array($v) ? json_encode($v) : $v, $l)]; }
            return [];
        }
        if (str_contains($sql, 'SELECT activity FROM enquiries')) return [['activity' => '[]']];
        if (str_contains($sql, 'SELECT notes FROM enquiries')) return [['notes' => '[]']];
        if (str_contains($sql, 'SELECT status FROM enquiries')) return [['status' => 'new']];
        if (str_starts_with($t, 'INSERT INTO enquiries')) return [['id' => 501]];
        if (str_starts_with($t, 'INSERT INTO proposals')) {
            [$eid, $parent, $token, $kind, $subject, $body, $to, $by, $rid] = $p;
            $this->proposals[] = ['id' => count($this->proposals) + 1, 'enquiry_id' => $eid, 'parent_id' => $parent, 'token' => $token, 'kind' => $kind, 'subject' => $subject,
                'body' => $body, 'to_email' => $to, 'sent_by' => $by, 'resend_id' => $rid, 'sent_at' => date('Y-m-d H:i:sP'), 'opened_at' => null, 'open_count' => 0,
                'viewed_at' => null, 'view_count' => 0, 'read_seconds' => 0, 'accepted_at' => null, 'accept_note' => '', 'last_viewed_at' => null];
            return [];
        }
        return [];
    }
}

$GLOBALS['FAKE_DB'] = new FakeDb();
function db(): FakeDb { return $GLOBALS['FAKE_DB']; }

require '/site/includes/config.php';
require '/site/includes/helpers.php';
require '/site/includes/security.php';
require '/site/includes/currency.php';
require '/site/includes/lead-guard.php';
require '/site/includes/crm.php';
require '/site/includes/lead-form.php';

$GLOBALS['T_PASS'] = 0;
$GLOBALS['T_FAIL'] = 0;
function check(string $name, bool $ok, string $detail = ''): void
{
    if ($ok) { $GLOBALS['T_PASS']++; return; }
    $GLOBALS['T_FAIL']++;
    echo "FAIL: $name" . ($detail !== '' ? "\n      $detail" : '') . "\n";
}
function done(): void
{
    echo "\n" . $GLOBALS['T_PASS'] . ' passed, ' . $GLOBALS['T_FAIL'] . " failed\n";
}

/** A lead row as save_enquiry would store it. */
function fixture_lead(array $o = []): array
{
    $message = "Lead grade: A (8/10)\nServices: SEO\nSource page: /seo-services/\nVisitor country: IN\nWebsite: https://acmelabels.in\nHeadquarters: Not given\nCompany size: 51 to 200 people\nBudget: ₹50,000 to ₹1,50,000 / month (about \$550 to \$3,000)\nTimeline: As soon as possible\nEmail: Business domain\n\n"
        . 'Hi team. We manufacture self adhesive labels in Chennai and want 50 qualified leads a month from Google within the next 6 months. Our competitors rank above us for label manufacturers in India.';
    return $o + [
        'id' => '42', 'created_at' => date('Y-m-d H:i:sP', strtotime('-30 hours')), 'source' => 'popup', 'name' => 'priya raman', 'email' => 'priya@acmelabels.in',
        'mobile' => '+91 98765 43210', 'company' => 'Acme Labels (https://acmelabels.in)', 'designation' => '', 'service' => 'SEO',
        'budget' => '₹50,000 to ₹1,50,000 / month (about $550 to $3,000)', 'message' => $message, 'status' => 'new', 'notes' => [], 'value' => '', 'priority' => 'normal',
        'owner' => '', 'followup' => null, 'tags' => '', 'activity' => [], 'channel' => 'Google search', 'channel_group' => 'Organic search',
        'attribution' => ['channel' => 'Google search', 'group' => 'Organic search', 'detail' => '', 'first' => ['at' => gmdate('Y-m-d\TH:i:s\Z', strtotime('-3 days')), 'landing' => '/seo-services/', 'ref' => 'https://www.google.com/', 'q' => []],
            'last' => null, 'first_channel' => 'Google search', 'pages' => 4, 'form_page' => '/pricing/', 'placement' => 'popup', 'device' => 'Mobile', 'country' => 'IN', 'tracked' => true],
        'form_page' => '/pricing/', 'country' => 'IN', 'grade' => 'A', 'score' => 8, 'contacted_at' => null,
    ];
}
