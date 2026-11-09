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

/** A Postgres error with its SQLSTATE, as PDO raises them (23P01: the meetings no-overlap rule). */
final class FakePdoException extends PDOException
{
    public function __construct(string $message, string $state)
    {
        parent::__construct($message);
        $this->code = $state;
    }
}

final class FakeDb
{
    public array $log = [];
    public array $leads = [];
    public array $proposals = [];
    public array $settings = [];
    public array $bookingSettings = [];
    /** The meetings table: rows as Postgres returns them (timestamps as text, JSON as text). */
    public array $meetings = [];
    /** Pretend the busy-times query saw nothing, to stage two bookings racing for one time. */
    public bool $hideBusy = false;
    private array $snapshot = [];
    private bool $tx = false;
    public function query(string $sql): FakeStmt { $s = new FakeStmt($this, $sql); $s->execute(); return $s; }
    public function prepare(string $sql): FakeStmt { return new FakeStmt($this, $sql); }
    public function exec(string $sql): int { $this->log[] = [$sql, []]; return 0; }
    public function beginTransaction(): bool { $this->tx = true; $this->snapshot = [$this->meetings, $this->leads]; return true; }
    public function commit(): bool { $this->tx = false; return true; }
    public function rollBack(): bool { $this->tx = false; [$this->meetings, $this->leads] = $this->snapshot; return true; }
    public function inTransaction(): bool { return $this->tx; }

    /** Split "a = ?, b = to_timestamp(?), c = 'x, y'" at the commas that are not inside brackets or quotes. */
    private static function split(string $s): array
    {
        $out = []; $buf = ''; $depth = 0; $q = false;
        for ($i = 0, $n = strlen($s); $i < $n; $i++) {
            $c = $s[$i];
            if ($c === "'") { $q = !$q; }
            if (!$q && $c === '(') { $depth++; }
            if (!$q && $c === ')') { $depth--; }
            if (!$q && $depth === 0 && $c === ',') { $out[] = trim($buf); $buf = ''; continue; }
            $buf .= $c;
        }
        if (trim($buf) !== '') { $out[] = trim($buf); }
        return $out;
    }

    /** The value one SQL expression gives, taking ? from $p in order. */
    private static function value(string $expr, array &$p, ?array $row, string $col): mixed
    {
        $e = trim($expr);
        $ts = static fn($v): string => date('Y-m-d H:i:sP', (int) $v);
        return match (true) {
            $e === '?' => array_shift($p),
            $e === 'to_timestamp(?)' => $ts(array_shift($p)),
            (bool) preg_match('/^CAST\(\? AS jsonb\)$/i', $e) => array_shift($p),
            $e === 'now()' => date('Y-m-d H:i:sP'),
            strtoupper($e) === 'NULL' => null,
            (bool) preg_match("/^'(.*)'$/s", $e, $m) => $m[1],
            (bool) preg_match('/^(\w+) \+ 1$/', $e, $m) => (int) ($row[$m[1]] ?? 0) + 1,
            (bool) preg_match('/^coalesce\((\w+), now\(\)\)$/i', $e, $m) => $row[$m[1]] ?? date('Y-m-d H:i:sP'),
            (bool) preg_match('/^-?\d+$/', $e) => (int) $e,
            default => throw new RuntimeException("FakeDb cannot evaluate $col = $e"),
        };
    }

    private function meetingDefaults(): array
    {
        $now = date('Y-m-d H:i:sP');
        return ['id' => count($this->meetings) + 1, 'enquiry_id' => null, 'token' => '', 'status' => 'requested', 'name' => '', 'email' => '', 'phone' => '', 'company' => '', 'notes' => '',
            'time_zone' => '', 'start_at' => null, 'end_at' => null, 'event_id' => '', 'ical_uid' => '', 'event_link' => '', 'meet_url' => '', 'sequence' => 0, 'rsvp' => '',
            'reschedules' => 0, 'reminder_ids' => '[]', 'request_subject' => '', 'request_body' => '', 'requested_by' => '', 'requested_at' => null, 'request_mail_id' => '',
            'opened_at' => null, 'viewed_at' => null, 'booked_at' => null, 'cancelled_at' => null, 'cancelled_by' => '', 'cancel_reason' => '', 'created_at' => $now, 'updated_at' => $now];
    }

    /** The exclusion constraint: two booked calls may not overlap. */
    private function checkOverlap(array $row): void
    {
        if ($row['status'] !== 'booked') { return; }
        $s = strtotime((string) $row['start_at']); $e = strtotime((string) $row['end_at']);
        foreach ($this->meetings as $o) {
            if ($o['id'] !== $row['id'] && $o['status'] === 'booked' && strtotime((string) $o['start_at']) < $e && strtotime((string) $o['end_at']) > $s) {
                throw new FakePdoException('conflicting key value violates exclusion constraint "meetings_no_overlap"', '23P01');
            }
        }
    }

    /** UPDATE enquiries ... WHERE id = ?: applied to the lead where the expression is modelled, skipped where not. */
    private function updateLead(string $sets, array $p): void
    {
        $id = (string) array_pop($p);
        foreach ($this->leads as $k => $lead) {
            if ((string) $lead['id'] !== $id) { continue; }
            foreach (self::split($sets) as $set) {
                [$col, $expr] = array_map('trim', explode('=', $set, 2));
                if ($expr === "CASE WHEN status = 'new' THEN 'contacted' ELSE status END") {
                    $lead['status'] = $lead['status'] === 'new' ? 'contacted' : $lead['status'];
                    continue;
                }
                if (preg_match('/^(CAST\(\? AS jsonb\)|\?::jsonb)$/i', $expr)) {
                    $lead[$col] = json_decode((string) array_shift($p), true);
                    continue;
                }
                try {
                    $lead[$col] = self::value($expr, $p, $lead, $col);
                } catch (Throwable $e) {
                    array_splice($p, 0, substr_count($expr, '?'));
                }
            }
            $this->leads[$k] = $lead;
        }
    }

    private function meetingsRun(string $t, array $p): array
    {
        $rows = $this->meetings;
        if (preg_match('/^INSERT INTO meetings \(([^)]+)\)\s*VALUES \((.+)\)$/s', $t, $m)) {
            $row = $this->meetingDefaults();
            $cols = array_map('trim', explode(',', $m[1]));
            foreach (self::split($m[2]) as $i => $expr) { $row[$cols[$i]] = self::value($expr, $p, $row, $cols[$i]); }
            $row['enquiry_id'] = $row['enquiry_id'] === null ? null : (int) $row['enquiry_id'];
            $this->checkOverlap($row);
            $this->meetings[] = $row;
            return [];
        }
        if (preg_match('/^UPDATE meetings SET (.+) WHERE id = \?$/s', $t, $m)) {
            $id = (int) array_pop($p);
            foreach ($this->meetings as $k => $row) {
                if ((int) $row['id'] !== $id) { continue; }
                foreach (self::split($m[1]) as $set) {
                    [$col, $expr] = array_map('trim', explode('=', $set, 2));
                    $row[$col] = self::value($expr, $p, $row, $col);
                }
                $row['enquiry_id'] = $row['enquiry_id'] === null ? null : (int) $row['enquiry_id'];
                $this->checkOverlap($row);
                $this->meetings[$k] = $row;
            }
            return [];
        }
        if (str_contains($t, 'WHERE token = ?')) { return array_values(array_filter($rows, static fn($r) => $r['token'] === $p[0])); }
        if (str_contains($t, 'WHERE enquiry_id = ?')) {
            $out = array_values(array_filter($rows, static fn($r) => (int) $r['enquiry_id'] === (int) $p[0]));
            usort($out, static fn($a, $b) => strcmp($b['created_at'], $a['created_at']) ?: $b['id'] <=> $a['id']);
            return $out;
        }
        if (str_contains($t, 'extract(epoch from start_at)')) {
            if ($this->hideBusy) { return []; }
            $out = [];
            foreach ($rows as $r) {
                $s = strtotime((string) $r['start_at']); $e = strtotime((string) $r['end_at']);
                if ($r['status'] === 'booked' && $e > (int) $p[0] && $s < (int) $p[1] && (int) $r['id'] !== (int) $p[2]) { $out[] = ['id' => $r['id'], 's' => $s, 'e' => $e]; }
            }
            return $out;
        }
        if (str_contains($t, "WHERE status = 'booked' AND end_at > now()")) {
            $out = array_values(array_filter($rows, static fn($r) => $r['status'] === 'booked' && strtotime((string) $r['end_at']) > time() && strtotime((string) $r['start_at']) < time() + (int) $p[0] * 86400));
            usort($out, static fn($a, $b) => strtotime((string) $a['start_at']) <=> strtotime((string) $b['start_at']));
            return $out;
        }
        if (str_contains($t, 'ORDER BY coalesce(start_at, created_at) DESC')) {
            usort($rows, static fn($a, $b) => strtotime((string) ($b['start_at'] ?? $b['created_at'])) <=> strtotime((string) ($a['start_at'] ?? $a['created_at'])));
            return $rows;
        }
        throw new RuntimeException('FakeDb does not know this meetings query: ' . $t);
    }

    public function run(string $sql, array $p): array
    {
        $this->log[] = [$sql, $p];
        $t = trim((string) preg_replace('/\s+/', ' ', $sql));
        if (str_contains($sql, "crm_settings WHERE key = 'booking'")) return $this->bookingSettings ? [['value' => json_encode($this->bookingSettings)]] : [];
        if (str_starts_with($t, 'INSERT INTO crm_settings') && str_contains($sql, "'booking'")) { $this->bookingSettings = json_decode((string) $p[0], true); return []; }
        if (str_contains($sql, "to_regclass('public.meetings')")) return [['t' => 'meetings']];
        if (str_contains($sql, 'pg_advisory_xact_lock')) return [['pg_advisory_xact_lock' => '']];
        if (preg_match('/\bmeetings\b/', $t) && !str_contains($t, 'FROM enquiries') && !str_starts_with($t, 'UPDATE enquiries')) return $this->meetingsRun($t, $p);
        if (str_contains($sql, 'FROM enquiries WHERE lower(email) = ?')) {
            foreach (array_reverse($this->leads) as $l) { if (strtolower((string) $l['email']) === $p[0] && $l['status'] !== 'junk') return [['id' => $l['id']]]; }
            return [];
        }
        if (str_starts_with($t, "INSERT INTO enquiries") && str_contains($t, "VALUES ('booking'")) {
            [$name, $email, $phone, $company, $service, $message, $tags, $activity, $channel, $group, $attr, $page, $country] = $p;
            $id = (string) (600 + count($this->leads));
            $this->leads[] = fixture_lead(['id' => $id, 'source' => 'booking', 'name' => $name, 'email' => $email, 'mobile' => $phone, 'company' => $company, 'service' => $service,
                'message' => $message, 'tags' => $tags, 'channel' => $channel, 'channel_group' => $group, 'attribution' => json_decode($attr, true), 'form_page' => $page, 'country' => $country,
                'grade' => '', 'score' => null, 'status' => 'new', 'created_at' => date('Y-m-d H:i:sP')]);
            return [['id' => $id]];
        }
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
        if (str_contains($sql, 'SELECT activity FROM enquiries')) {
            foreach ($this->leads as $l) { if ((string) $l['id'] === (string) $p[0]) return [['activity' => json_encode($l['activity'] ?? [])]]; }
            return [['activity' => '[]']];
        }
        if (preg_match('/^UPDATE enquiries SET (.+) WHERE id = \?( AND deleted_at IS NULL)?$/s', $t, $um)) { $this->updateLead($um[1], $p); return []; }
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
require '/site/includes/booking.php';
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
