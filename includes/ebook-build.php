<?php
/**
 * Builds EPUB files from the chapter content in includes/data/ebooks/.
 *
 * Files are generated once and cached on disk, then served by the tokenised
 * download route. Generation is cheap (well under a second) but doing it per
 * request would be wasteful, and a cached file also means the download link
 * in a delivery email keeps working even if content files change later.
 */

declare(strict_types=1);

require_once __DIR__ . '/epub.php';

/** Where generated EPUB files live. Outside the web root by .htaccess rule. */
function ebook_dir(): string
{
    $dir = BASE_PATH . '/storage/ebooks';
    if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
    return $dir;
}

function ebook_path(string $slug): string
{
    return ebook_dir() . '/' . preg_replace('/[^a-z0-9-]/', '', $slug) . '.epub';
}

/** True when a resource has authored chapter content available. */
function ebook_has_content(string $slug): bool
{
    return is_file(BASE_PATH . '/includes/data/ebooks/' . preg_replace('/[^a-z0-9-]/', '', $slug) . '.php');
}

/**
 * Return the path to a built EPUB, generating it if missing or stale.
 * Returns null when the resource has no authored content.
 */
function ebook_file(string $slug, array $resource): ?string
{
    if (!ebook_has_content($slug)) {
        return null;
    }
    $contentFile = BASE_PATH . '/includes/data/ebooks/' . preg_replace('/[^a-z0-9-]/', '', $slug) . '.php';
    $out = ebook_path($slug);

    // Rebuild when the source content or the generator itself is newer.
    $stale = !is_file($out)
        || filemtime($out) < filemtime($contentFile)
        || filemtime($out) < filemtime(__DIR__ . '/epub.php');

    if ($stale) {
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
        $bytes = epub_build($book);
        file_put_contents($out, $bytes, LOCK_EX);
    }
    return $out;
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
    if (!function_exists('mail')) {
        return false;
    }
    $host = parse_url(SITE_URL, PHP_URL_HOST) ?: 'vturnu.com';
    $from = 'website@' . $host;
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
        $boundary = 'vt' . bin2hex(random_bytes(12));
        $headers = implode("\r\n", [
            'From: ' . SITE_NAME . ' <' . $from . '>',
            'Reply-To: ' . CONTACT_EMAIL,
            'MIME-Version: 1.0',
            'Content-Type: multipart/mixed; boundary="' . $boundary . '"',
        ]);
        $attachment = chunk_split(base64_encode((string) file_get_contents($file)));
        $filename = preg_replace('/[^a-z0-9-]/', '', $slug) . '.epub';

        $msg  = '--' . $boundary . "\r\n";
        $msg .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $msg .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $msg .= $body . "\r\n\r\n";
        $msg .= '--' . $boundary . "\r\n";
        $msg .= 'Content-Type: application/epub+zip; name="' . $filename . '"' . "\r\n";
        $msg .= "Content-Transfer-Encoding: base64\r\n";
        $msg .= 'Content-Disposition: attachment; filename="' . $filename . '"' . "\r\n\r\n";
        $msg .= $attachment . "\r\n";
        $msg .= '--' . $boundary . "--";

        return @mail($email, $subject, $msg, $headers, '-f' . $from);
    }

    // No attachment available: still deliver the link.
    $headers = implode("\r\n", [
        'From: ' . SITE_NAME . ' <' . $from . '>',
        'Reply-To: ' . CONTACT_EMAIL,
        'Content-Type: text/plain; charset=UTF-8',
    ]);
    return @mail($email, $subject, $body, $headers, '-f' . $from);
}
