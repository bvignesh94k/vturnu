<?php
/**
 * Resolves EPUB files built from the chapter content in includes/data/ebooks/.
 *
 * On the VPS these were generated on first request and cached to local disk.
 * Vercel's filesystem is read-only outside /tmp and not shared between
 * invocations, so that cache would either fail to write or silently vanish
 * on every cold start. Instead, `bin/build-ebooks.php` pre-builds every
 * ebook once (run locally before a deploy that touches ebook content, see
 * that script's header comment) into assets/downloads/, which ships as an
 * ordinary static file and needs no PHP execution to serve.
 */

declare(strict_types=1);

require_once __DIR__ . '/epub.php';

function ebook_path(string $slug): string
{
    return BASE_PATH . '/assets/downloads/' . preg_replace('/[^a-z0-9-]/', '', $slug) . '.epub';
}

/** True when a resource has authored chapter content available. */
function ebook_has_content(string $slug): bool
{
    return is_file(BASE_PATH . '/includes/data/ebooks/' . preg_replace('/[^a-z0-9-]/', '', $slug) . '.php');
}

/**
 * Build a single EPUB's bytes from its chapter content. Used by
 * bin/build-ebooks.php to pre-render every resource; not called at request
 * time any more.
 */
function ebook_build_bytes(string $slug, array $resource): ?string
{
    if (!ebook_has_content($slug)) {
        return null;
    }
    $contentFile = BASE_PATH . '/includes/data/ebooks/' . preg_replace('/[^a-z0-9-]/', '', $slug) . '.php';
    $content = require $contentFile;
    $book = [
        'uuid'        => epub_uuid($slug),
        'title'       => $resource['h1'] ?? $slug,
        'subtitle'    => $content['subtitle'] ?? '',
        'description' => $content['description'] ?? ($resource['meta'] ?? ''),
        'subjects'    => $content['subjects'] ?? [],
        'author'      => SITE_NAME . ' Strategy Team',
        'tagline'     => SITE_TAGLINE,
        'edition'     => date('F Y') . ' edition',
        'chapters'    => $content['chapters'],
        'closing'     => $content['closing'] ?? '',
    ];
    return epub_build($book);
}

/**
 * Return the path to a pre-built EPUB. Returns null when the resource has no
 * authored content, or the pre-build step has not run yet for this slug.
 */
function ebook_file(string $slug, array $resource): ?string
{
    if (!ebook_has_content($slug)) {
        return null;
    }
    $out = ebook_path($slug);
    return is_file($out) ? $out : null;
}

/**
 * Signed, expiring download token. Stateless: no database row to create or
 * clean up, and the signature makes the link unforgeable.
 */
function ebook_token(string $slug, string $email): string
{
    $exp = time() + (86400 * 30);   // 30 days is long enough to be useful
    $payload = $slug . '|' . $exp . '|' . strtolower(trim($email));
    $sig = hash_hmac('sha256', $payload, SECURITY_SECRET);
    return rtrim(strtr(base64_encode($payload . '|' . $sig), '+/', '-_'), '=');
}

/**
 * Validate a download token.
 * @return array{ok:bool,slug:string,error:string}
 */
function ebook_token_verify(string $token): array
{
    $raw = base64_decode(strtr($token, '-_', '+/'), true);
    if ($raw === false) {
        return ['ok' => false, 'slug' => '', 'error' => 'That download link is not valid.'];
    }
    $parts = explode('|', $raw);
    if (count($parts) !== 4) {
        return ['ok' => false, 'slug' => '', 'error' => 'That download link is not valid.'];
    }
    [$slug, $exp, $email, $sig] = $parts;
    $expected = hash_hmac('sha256', $slug . '|' . $exp . '|' . $email, SECURITY_SECRET);
    if (!hash_equals($expected, $sig)) {
        return ['ok' => false, 'slug' => '', 'error' => 'That download link is not valid.'];
    }
    if ((int) $exp < time()) {
        return ['ok' => false, 'slug' => $slug, 'error' => 'That download link has expired. Request a fresh copy and we will send a new one.'];
    }
    return ['ok' => true, 'slug' => $slug, 'error' => ''];
}

/**
 * Email the requested resource to the visitor, with the EPUB attached and a
 * download link as a fallback.
 *
 * Attachment plus link is deliberate: attachments are the thing the visitor
 * asked for and work offline, but some corporate filters strip unfamiliar
 * attachment types, so the link guarantees the promise is still kept.
 */
function ebook_send(string $slug, array $resource, string $name, string $email): bool
{
    $isEbook = ($resource['type'] ?? 'ebook') === 'ebook';
    $label = $isEbook ? 'e-book' : 'guide';
    $title = $resource['h1'] ?? 'your download';

    $file = ebook_file($slug, $resource);
    $link = rtrim(SITE_URL, '/') . '/download/?t=' . ebook_token($slug, $email);

    $firstName = trim(strtok(trim($name), ' ')) ?: 'there';

    $lines = [];
    $lines[] = 'Hi ' . $firstName . ',';
    $lines[] = '';
    $lines[] = 'Here is your copy of ' . $title . '.';
    $lines[] = '';
    if ($file) {
        $lines[] = 'It is attached to this email as an EPUB file, which opens in Apple Books,';
        $lines[] = 'Google Play Books, Kobo, Calibre and most e-readers. On a phone, tapping';
        $lines[] = 'the attachment is usually enough.';
        $lines[] = '';
        $lines[] = 'If the attachment does not come through, download it here instead:';
    } else {
        $lines[] = 'Download it here:';
    }
    $lines[] = $link;
    $lines[] = '';
    $lines[] = 'This link works for 30 days.';
    $lines[] = '';
    if (!empty($resource['learn'])) {
        $lines[] = 'What is inside:';
        foreach (array_slice($resource['learn'], 0, 4) as $l) {
            $lines[] = '  - ' . $l;
        }
        $lines[] = '';
    }
    $lines[] = 'If you would like this applied to your own site, our free audit tool';
    $lines[] = 'checks 22 points and emails you the report in about ten seconds:';
    $lines[] = rtrim(SITE_URL, '/') . '/free-seo-audit/';
    $lines[] = '';
    $lines[] = 'Reply to this email if you have questions. A person reads it.';
    $lines[] = '';
    $lines[] = SITE_NAME;
    $lines[] = SITE_TAGLINE;
    $lines[] = CONTACT_PHONE . ' | ' . CONTACT_EMAIL;

    $body = implode("\n", $lines);
    $subject = 'Your ' . $label . ': ' . $title;

    if ($file && is_file($file) && filesize($file) < 4194304) {
        return send_email($email, $subject, $body, [
            'reply_to' => CONTACT_EMAIL,
            'attachment' => [
                'filename' => preg_replace('/[^a-z0-9-]/', '', $slug) . '.epub',
                'content' => (string) file_get_contents($file),
            ],
        ]);
    }

    // No attachment available: still deliver the link.
    return send_email($email, $subject, $body, ['reply_to' => CONTACT_EMAIL]);
}
