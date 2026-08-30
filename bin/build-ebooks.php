<?php
/**
 * Pre-builds every EPUB into assets/downloads/, as a plain static file.
 *
 * On the VPS these were built on first request and cached to local disk.
 * Vercel's filesystem is read-only outside /tmp and not shared between
 * invocations, so that on-demand cache cannot work there. Instead, run this
 * once locally (or in CI) before any deploy that touches ebook content —
 * new resource added, or an existing includes/data/ebooks/*.php file edited
 * — and commit the output. assets/downloads/ then ships as an ordinary
 * static file and needs no PHP execution to serve.
 *
 * Usage (from the project root):
 *   php bin/build-ebooks.php
 */

declare(strict_types=1);

require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/ebook-build.php';
require __DIR__ . '/../includes/data/resources.php';

$outDir = BASE_PATH . '/assets/downloads';
if (!is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

$built = 0;
$skipped = 0;

foreach ($RESOURCES as $slug => $resource) {
    if (!ebook_has_content($slug)) {
        $skipped++;
        continue;
    }
    $bytes = ebook_build_bytes($slug, $resource);
    if ($bytes === null) {
        fwrite(STDERR, "  SKIP  {$slug} (no content)\n");
        $skipped++;
        continue;
    }
    $out = ebook_path($slug);
    file_put_contents($out, $bytes, LOCK_EX);
    printf("  BUILT %-48s %6d bytes\n", $slug, strlen($bytes));
    $built++;
}

printf("\n%d built, %d skipped. Output: %s\n", $built, $skipped, $outDir);
