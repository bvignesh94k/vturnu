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

    $dsn = getenv('POSTGRES_URL') ?: getenv('DATABASE_URL') ?: '';
    if ($dsn === '') {
        throw new RuntimeException('No database connection string set (POSTGRES_URL or DATABASE_URL).');
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

    $pdo = new PDO($pdoDsn, $parts['user'] ?? '', $parts['pass'] ?? '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    return $pdo;
}
