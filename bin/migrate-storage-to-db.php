<?php
/**
 * One-off migration: reads the old file-based storage/ directory and inserts
 * everything into the new Postgres database (see db/schema.sql).
 *
 * Run this ONCE, before cutting DNS over to Vercel, against a copy of the
 * live VPS's storage/ directory (pull it down first: scp/rsync from
 * 66.29.131.95). It is safe to re-run: enquiries are matched by
 * (created_at, email, message) and skipped if already present; admin_users,
 * content_overrides and nothing else are upserted.
 *
 * Usage (from the project root):
 *   POSTGRES_URL="postgres://user:pass@host/db" php bin/migrate-storage-to-db.php /path/to/storage
 *
 * If no path is given, defaults to ./storage relative to the project root
 * (useful for a local storage/ copy pulled down from the VPS).
 */

declare(strict_types=1);

require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/db.php';

$storageDir = $argv[1] ?? (BASE_PATH . '/storage');
if (!is_dir($storageDir)) {
    fwrite(STDERR, "No such directory: {$storageDir}\n");
    exit(1);
}

function read_json(string $file, $default)
{
    if (!is_file($file)) {
        return $default;
    }
    $d = json_decode((string) file_get_contents($file), true);
    return is_array($d) ? $d : $default;
}

$pdo = db();

/* ---------- admin_users ---------- */
$auth = read_json($storageDir . '/admin.json', []);
if (!empty($auth['user']) && !empty($auth['hash'])) {
    $pdo->prepare('INSERT INTO admin_users (username, password_hash) VALUES (?, ?) ON CONFLICT (username) DO UPDATE SET password_hash = excluded.password_hash')
        ->execute([$auth['user'], $auth['hash']]);
    echo "admin_users: migrated '{$auth['user']}'\n";
} else {
    echo "admin_users: no storage/admin.json found, admin.php will auto-seed the default login on first run\n";
}

/* ---------- content_overrides ---------- */
$typeFiles = ['blog' => 'blog-custom.json', 'case' => 'cases-custom.json', 'resource' => 'resources-custom.json'];
foreach ($typeFiles as $type => $file) {
    $custom = read_json($storageDir . '/' . $file, []);
    $n = 0;
    foreach ($custom as $slug => $data) {
        if (!is_string($slug) || !is_array($data)) {
            continue;
        }
        $pdo->prepare(
            'INSERT INTO content_overrides (content_type, slug, data) VALUES (?, ?, ?::jsonb)
             ON CONFLICT (content_type, slug) DO UPDATE SET data = excluded.data, updated_at = now()'
        )->execute([$type, $slug, json_encode($data, JSON_UNESCAPED_UNICODE)]);
        $n++;
    }
    echo "content_overrides ({$type}): migrated {$n}\n";
}

/* ---------- enquiries (jsonl) + leads-meta.json, merged by id like admin.php used to ---------- */
$leadsFile = $storageDir . '/enquiries.jsonl';
$meta = read_json($storageDir . '/leads-meta.json', []);
$n = 0;
$skipped = 0;
if (is_file($leadsFile)) {
    $exists = $pdo->prepare('SELECT 1 FROM enquiries WHERE created_at = ? AND email = ? AND message = ? LIMIT 1');
    $insert = $pdo->prepare(
        'INSERT INTO enquiries (created_at, source, name, email, mobile, company, designation, service, budget,
                                 message, status, notes, value, priority, owner, followup, tags, activity)
         VALUES (:created_at, :source, :name, :email, :mobile, :company, :designation, :service, :budget,
                 :message, :status, :notes::jsonb, :value, :priority, :owner, :followup, :tags, :activity::jsonb)'
    );
    foreach (file($leadsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $l = json_decode($line, true);
        if (!is_array($l)) {
            continue;
        }
        $createdAt = $l['date'] ?? date('c');
        $email = $l['email'] ?? '';
        $message = $l['message'] ?? '';

        $exists->execute([$createdAt, $email, $message]);
        if ($exists->fetch()) {
            $skipped++;
            continue;
        }

        // Same md5 id the old admin.php computed, to find this row's CRM metadata.
        $id = substr(md5($createdAt . '|' . $email . '|' . $message), 0, 12);
        $m = $meta[$id] ?? [];
        $followup = trim((string) ($m['followup'] ?? ''));

        $insert->execute([
            ':created_at' => $createdAt,
            ':source' => $l['source'] ?? '',
            ':name' => $l['name'] ?? '',
            ':email' => $email,
            ':mobile' => $l['mobile'] ?? '',
            ':company' => $l['company'] ?? '',
            ':designation' => $l['designation'] ?? '',
            ':service' => $l['service'] ?? '',
            ':budget' => $l['budget'] ?? '',
            ':message' => $message,
            ':status' => $m['status'] ?? 'new',
            ':notes' => json_encode($m['notes'] ?? [], JSON_UNESCAPED_UNICODE),
            ':value' => $m['value'] ?? '',
            ':priority' => $m['priority'] ?? 'normal',
            ':owner' => $m['owner'] ?? '',
            ':followup' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $followup) ? $followup : null,
            ':tags' => $m['tags'] ?? '',
            ':activity' => json_encode($m['activity'] ?? [], JSON_UNESCAPED_UNICODE),
        ]);
        $n++;
    }
}
echo "enquiries: migrated {$n}, skipped {$skipped} already-present\n";

echo "\nDone.\n";
