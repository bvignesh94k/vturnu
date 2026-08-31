<?php
/**
 * Postgres connection. Everything that used to be a file under storage/ now
 * lives in this database: leads, admin auth, CMS content overrides and rate
 * limiting. See db/schema.sql for the tables.
 */

declare(strict_types=1);

/** Lazily-connected PDO handle, cached for the lifetime of one invocation. */
function db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    /* Neon (via the Vercel integration) creates several of these. Try the
       pooled URL first, then the common alternatives, so the app does not
       depend on one exact variable name surviving a dashboard change.
       env_var() rather than getenv(): see the note in includes/config.php. */
    $dsn = env_var('POSTGRES_URL')
        ?? env_var('DATABASE_URL')
        ?? env_var('POSTGRES_DATABASE_URL')
        ?? env_var('POSTGRES_URL_NON_POOLING')
        ?? env_var('POSTGRES_PRISMA_URL')
        ?? '';
    if ($dsn === '') {
        throw new RuntimeException('No database connection string set (tried POSTGRES_URL, DATABASE_URL, POSTGRES_DATABASE_URL, POSTGRES_URL_NON_POOLING, POSTGRES_PRISMA_URL).');
    }

    // Vercel/Neon inject a postgres:// URL; PDO's pgsql driver wants a DSN.
    $parts = parse_url($dsn);
    if ($parts === false || empty($parts['host'])) {
        throw new RuntimeException('POSTGRES_URL / DATABASE_URL is not a valid connection string.');
    }
    $pdoDsn = sprintf(
        'pgsql:host=%s;port=%s;dbname=%s;sslmode=require',
        $parts['host'],
        (string) ($parts['port'] ?? 5432),
        ltrim($parts['path'] ?? '', '/')
    );

    /* Credentials arrive percent-encoded inside the URL, so a generated
       password containing @ / : or + would otherwise be passed through
       verbatim and rejected by the server. */
    $user = isset($parts['user']) ? rawurldecode($parts['user']) : '';
    $pass = isset($parts['pass']) ? rawurldecode($parts['pass']) : '';

    $pdo = new PDO($pdoDsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 8,
    ]);
    return $pdo;
}
