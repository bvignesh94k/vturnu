<?php
/**
 * EPUB 3 generator with a dependency-free ZIP writer.
 *
 * Why hand-rolled ZIP instead of ZipArchive: the ext/zip extension is not
 * guaranteed on shared cPanel hosting, and EPUB has one hard structural
 * rule that ZipArchive makes awkward anyway (the "mimetype" entry must be
 * the first entry and must be STORED, not deflated). Writing the archive
 * directly is about eighty lines, works on any PHP build with zlib, and
 * removes an install-time failure mode entirely.
 *
 * Output validates as EPUB 3.0 and opens in Apple Books, Google Play Books,
 * Kobo, Calibre, Thorium and the Kindle app (via Send to Kindle).
 */

declare(strict_types=1);

/* ------------------------------------------------------------------ */
/* Minimal ZIP writer                                                  */
/* ------------------------------------------------------------------ */

final class SimpleZip
{
    /** @var array<int,array{name:string,data:string,store:bool}> */
    private array $files = [];

    public function add(string $name, string $data, bool $store = false): void
    {
        $this->files[] = ['name' => $name, 'data' => $data, 'store' => $store];
    }

    public function build(): string
    {
        $out = '';
        $central = '';
        $offset = 0;

        foreach ($this->files as $f) {
            $name = $f['name'];
            $data = $f['data'];
            $crc = crc32($data);
            $uncompressed = strlen($data);

            if ($f['store']) {
                $method = 0;              // STORED
                $payload = $data;
            } else {
                $method = 8;              // DEFLATE
                // Raw deflate stream, which is what ZIP method 8 expects.
                $payload = gzdeflate($data, 9);
                if ($payload === false) { $method = 0; $payload = $data; }
            }
            $compressed = strlen($payload);

            // Local file header
            $lfh = pack('V', 0x04034b50)   // signature
                 . pack('v', 20)           // version needed
                 . pack('v', 0)            // flags
                 . pack('v', $method)
                 . pack('v', 0)            // mod time
                 . pack('v', 0)            // mod date
                 . pack('V', $crc)
                 . pack('V', $compressed)
                 . pack('V', $uncompressed)
                 . pack('v', strlen($name))
                 . pack('v', 0)            // extra len
                 . $name;

            $out .= $lfh . $payload;

            // Central directory record
            $central .= pack('V', 0x02014b50)
                      . pack('v', 20)      // version made by
                      . pack('v', 20)      // version needed
                      . pack('v', 0)
                      . pack('v', $method)
                      . pack('v', 0)
                      . pack('v', 0)
                      . pack('V', $crc)
                      . pack('V', $compressed)
                      . pack('V', $uncompressed)
                      . pack('v', strlen($name))
                      . pack('v', 0)       // extra
                      . pack('v', 0)       // comment
                      . pack('v', 0)       // disk
                      . pack('v', 0)       // internal attrs
                      . pack('V', 0)       // external attrs
                      . pack('V', $offset)
                      . $name;

            // Next entry starts where the archive currently ends. Read after
            // appending, so the value recorded above is this entry's own start.
            $offset = strlen($out);
        }

        $eocd = pack('V', 0x06054b50)
              . pack('v', 0) . pack('v', 0)
              . pack('v', count($this->files))
              . pack('v', count($this->files))
              . pack('V', strlen($central))
              . pack('V', strlen($out))
              . pack('v', 0);

        return $out . $central . $eocd;
    }
}

/* ------------------------------------------------------------------ */
/* EPUB assembly                                                       */
/* ------------------------------------------------------------------ */

/** Escape for XML/XHTML bodies and attributes. */
function epub_x(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_XML1, 'UTF-8');
}

/**
 * Convert a light markup subset into XHTML.
 *
 * Supported per line:
 *   ## Heading           -> <h2>
 *   ### Sub-heading      -> <h3>
 *   - item               -> <ul><li>
 *   1. item              -> <ol><li>
 *   > quote              -> <blockquote>
 *   | a | b |            -> table row (first row becomes the header)
 *   !NOTE text           -> callout box
 *   !STAT value | label  -> statistic block
 *   blank line           -> paragraph break
 * Inline: **bold**, *italic*, `code`.
 */
function epub_markup(string $raw): string
{
    $lines = preg_split('/\r\n|\r|\n/', $raw);
    $out = [];
    $listType = null;   // 'ul' | 'ol' | null
    $inTable = false;
    $tableHeaderDone = false;
    $para = [];

    $inline = function (string $t): string {
        $t = epub_x($t);
        $t = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $t);
        $t = preg_replace('/(?<!\*)\*(?!\s)(.+?)(?<!\s)\*(?!\*)/s', '<em>$1</em>', $t);
        $t = preg_replace('/`(.+?)`/s', '<code>$1</code>', $t);
        return $t;
    };

    $flushPara = function () use (&$para, &$out, $inline) {
        if ($para) {
            $out[] = '<p>' . $inline(implode(' ', $para)) . '</p>';
            $para = [];
        }
    };
    $closeList = function () use (&$listType, &$out) {
        if ($listType) { $out[] = '</' . $listType . '>'; $listType = null; }
    };
    $closeTable = function () use (&$inTable, &$tableHeaderDone, &$out) {
        if ($inTable) { $out[] = '</tbody></table>'; $inTable = false; $tableHeaderDone = false; }
    };

    foreach ($lines as $line) {
        $t = rtrim($line);
        $trim = trim($t);

        if ($trim === '') { $flushPara(); $closeList(); $closeTable(); continue; }

        if (str_starts_with($trim, '### ')) {
            $flushPara(); $closeList(); $closeTable();
            $out[] = '<h3>' . $inline(substr($trim, 4)) . '</h3>';
            continue;
        }
        if (str_starts_with($trim, '## ')) {
            $flushPara(); $closeList(); $closeTable();
            $out[] = '<h2>' . $inline(substr($trim, 3)) . '</h2>';
            continue;
        }
        if (str_starts_with($trim, '!STAT ')) {
            $flushPara(); $closeList(); $closeTable();
            $parts = array_map('trim', explode('|', substr($trim, 6), 2));
            $out[] = '<div class="stat"><span class="stat-v">' . $inline($parts[0]) . '</span>'
                   . '<span class="stat-l">' . $inline($parts[1] ?? '') . '</span></div>';
            continue;
        }
        if (str_starts_with($trim, '!NOTE ')) {
            $flushPara(); $closeList(); $closeTable();
            $out[] = '<div class="note">' . $inline(substr($trim, 6)) . '</div>';
            continue;
        }
        if (str_starts_with($trim, '> ')) {
            $flushPara(); $closeList(); $closeTable();
            $out[] = '<blockquote><p>' . $inline(substr($trim, 2)) . '</p></blockquote>';
            continue;
        }
        if (str_starts_with($trim, '|') && substr_count($trim, '|') >= 2) {
            $flushPara(); $closeList();
            $cells = array_map('trim', array_filter(explode('|', trim($trim, '|')), fn($c) => $c !== null));
            // A separator row of dashes only marks the header boundary.
            if (preg_match('/^[\s\-|:]+$/', $trim)) { continue; }
            if (!$inTable) {
                $out[] = '<table><thead><tr>';
                foreach ($cells as $c) { $out[] = '<th>' . $inline($c) . '</th>'; }
                $out[] = '</tr></thead><tbody>';
                $inTable = true; $tableHeaderDone = true;
                continue;
            }
            $out[] = '<tr>';
            foreach ($cells as $c) { $out[] = '<td>' . $inline($c) . '</td>'; }
            $out[] = '</tr>';
            continue;
        }
        if (preg_match('/^-\s+(.*)$/', $trim, $m)) {
            $flushPara(); $closeTable();
            if ($listType !== 'ul') { $closeList(); $out[] = '<ul>'; $listType = 'ul'; }
            $out[] = '<li>' . $inline($m[1]) . '</li>';
            continue;
        }
        if (preg_match('/^\d+\.\s+(.*)$/', $trim, $m)) {
            $flushPara(); $closeTable();
            if ($listType !== 'ol') { $closeList(); $out[] = '<ol>'; $listType = 'ol'; }
            $out[] = '<li>' . $inline($m[1]) . '</li>';
            continue;
        }

        $closeList(); $closeTable();
        $para[] = $trim;
    }
    $flushPara(); $closeList(); $closeTable();

    return implode("\n", $out);
}

/** Brand stylesheet embedded in every EPUB. */
function epub_stylesheet(): string
{
    return <<<'CSS'
@namespace epub "http://www.idpf.org/2007/ops";
html, body { margin: 0; padding: 0; }
body {
  font-family: "Iowan Old Style", "Palatino Linotype", Palatino, Georgia, serif;
  line-height: 1.62; color: #1D1936; padding: 1.1em 1.2em; font-size: 1em;
  -webkit-hyphens: auto; hyphens: auto;
}
h1, h2, h3, .kicker, .stat-v, .stat-l, .note strong, table, .cover-tag, .cover-brand {
  font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
}
h1 { font-size: 1.85em; line-height: 1.18; margin: 0 0 .5em; color: #1D1936; font-weight: 800; letter-spacing: -.01em; }
h2 { font-size: 1.28em; line-height: 1.3; margin: 1.9em 0 .55em; color: #4338CA; font-weight: 700; }
h3 { font-size: 1.06em; margin: 1.4em 0 .4em; color: #1D1936; font-weight: 700; }
p { margin: 0 0 .95em; text-align: justify; }
ul, ol { margin: 0 0 1.1em 1.3em; padding: 0; }
li { margin-bottom: .42em; }
strong { color: #1D1936; }
code { font-family: "SF Mono", Consolas, monospace; font-size: .9em; background: #F2F1FC; padding: .1em .35em; border-radius: 3px; }
blockquote {
  margin: 1.3em 0; padding: .7em 1.1em; border-left: 3px solid #FF3D8A;
  background: #FBFAFF; font-style: italic; color: #3B3560;
}
blockquote p { margin: 0; text-align: left; }
.kicker {
  font-size: .72em; letter-spacing: .14em; text-transform: uppercase;
  color: #5B56C9; font-weight: 700; margin: 0 0 .5em;
}
.note {
  margin: 1.3em 0; padding: .85em 1.05em; background: #F5F4FE;
  border: 1px solid #E5E3F5; border-radius: 6px; font-size: .94em; color: #3B3560;
}
.stat {
  margin: 1.3em 0; padding: .9em 1.05em; background: #FBFAFF;
  border-left: 4px solid #00B8D9; border-radius: 4px;
}
.stat-v { display: block; font-size: 1.7em; font-weight: 800; color: #4338CA; line-height: 1.1; }
.stat-l { display: block; font-size: .84em; color: #3B3560; margin-top: .2em; }
table { width: 100%; border-collapse: collapse; margin: 1.3em 0; font-size: .88em; }
th, td { text-align: left; padding: .5em .6em; border-bottom: 1px solid #E5E3F5; vertical-align: top; }
th { background: #F5F4FE; color: #1D1936; font-weight: 700; }
hr { border: 0; border-top: 1px solid #E5E3F5; margin: 2em 0; }

/* Title page */
.cover { text-align: center; padding-top: 12%; }
.cover-mark { font-size: 3.4em; font-weight: 800; letter-spacing: -.03em; line-height: 1; margin-bottom: .35em; }
.cover-mark .v { color: #00B8D9; }
.cover-mark .u { color: #FF3D8A; }
.cover-brand { font-size: .8em; letter-spacing: .2em; text-transform: uppercase; color: #5B56C9; font-weight: 700; }
.cover h1 { font-size: 2.2em; margin: 1.1em 0 .4em; }
.cover-tag { font-size: .95em; color: #3B3560; font-style: italic; }
.cover-meta { margin-top: 2.4em; font-size: .8em; color: #6F6992; line-height: 1.8; }
.toc-list { list-style: none; margin-left: 0; }
.toc-list li { margin-bottom: .7em; }
.toc-list a { text-decoration: none; color: #1D1936; }
.toc-num { color: #5B56C9; font-weight: 700; margin-right: .5em; }
CSS;
}

/** One XHTML document. */
function epub_page(string $title, string $bodyHtml, string $lang = 'en'): string
{
    $t = epub_x($title);
    return <<<XHTML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:epub="http://www.idpf.org/2007/ops" lang="{$lang}" xml:lang="{$lang}">
<head>
<meta charset="UTF-8"/>
<title>{$t}</title>
<link rel="stylesheet" type="text/css" href="style.css"/>
</head>
<body>
{$bodyHtml}
</body>
</html>
XHTML;
}

/**
 * Build a complete EPUB 3 file.
 *
 * @param array $book  title, subtitle, tagline, chapters[[heading, markup]], meta[]
 * @return string      raw .epub bytes
 */
function epub_build(array $book): string
{
    $uid = 'urn:uuid:' . $book['uuid'];
    $title = $book['title'];
    $author = $book['author'] ?? SITE_NAME;
    $lang = 'en';
    $modified = gmdate('Y-m-d\TH:i:s\Z');

    $zip = new SimpleZip();

    // 1. mimetype: must be first and stored uncompressed.
    $zip->add('mimetype', 'application/epub+zip', true);

    // 2. Container
    $zip->add('META-INF/container.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<container version="1.0" xmlns="urn:oasis:names:tc:opendocument:xmlns:container">
  <rootfiles>
    <rootfile full-path="OEBPS/content.opf" media-type="application/oebps-package+xml"/>
  </rootfiles>
</container>
XML);

    $zip->add('OEBPS/style.css', epub_stylesheet());

    // 3. Title page
    $coverBody = '<div class="cover">'
        . '<p class="cover-mark"><span class="v">V</span>Turn<span class="u">U</span></p>'
        . '<p class="cover-brand">' . epub_x($book['tagline'] ?? SITE_TAGLINE) . '</p>'
        . '<h1>' . epub_x($title) . '</h1>'
        . (!empty($book['subtitle']) ? '<p class="cover-tag">' . epub_x($book['subtitle']) . '</p>' : '')
        . '<p class="cover-meta">' . epub_x($author) . '<br/>'
        . epub_x($book['edition'] ?? ('Edition ' . date('Y'))) . '<br/>'
        . epub_x(SITE_URL) . '</p>'
        . '</div>';
    $zip->add('OEBPS/title.xhtml', epub_page($title, $coverBody, $lang));

    // 4. Chapters
    $manifest = [];
    $spine = [];
    $navItems = [];
    $n = 0;
    foreach ($book['chapters'] as $ch) {
        $n++;
        $file = sprintf('ch%02d.xhtml', $n);
        $heading = $ch[0];
        $body = '<p class="kicker">Chapter ' . $n . '</p>'
              . '<h1>' . epub_x($heading) . '</h1>'
              . epub_markup($ch[1]);
        $zip->add('OEBPS/' . $file, epub_page($heading, $body, $lang));
        $manifest[] = '<item id="ch' . $n . '" href="' . $file . '" media-type="application/xhtml+xml"/>';
        $spine[] = '<itemref idref="ch' . $n . '"/>';
        $navItems[] = '<li><a href="' . $file . '"><span class="toc-num">' . sprintf('%02d', $n) . '</span>'
                    . epub_x($heading) . '</a></li>';
    }

    // 5. Closing page: the commercial ask, kept honest and specific.
    $endBody = '<p class="kicker">Next step</p><h1>Put this to work</h1>'
        . epub_markup($book['closing'] ?? '')
        . '<div class="note"><strong>' . epub_x(SITE_NAME) . '</strong><br/>'
        . 'Email: ' . epub_x(CONTACT_EMAIL) . '<br/>'
        . 'Phone: ' . epub_x(CONTACT_PHONE) . '<br/>'
        . 'Web: ' . epub_x(SITE_URL) . '</div>';
    $zip->add('OEBPS/end.xhtml', epub_page('Put this to work', $endBody, $lang));

    // 6. Navigation document (EPUB 3 requirement)
    $navBody = '<nav epub:type="toc" id="toc"><h1>Contents</h1><ol class="toc-list">'
             . '<li><a href="title.xhtml"><span class="toc-num">00</span>Title</a></li>'
             . implode('', $navItems)
             . '<li><a href="end.xhtml"><span class="toc-num">' . sprintf('%02d', $n + 1) . '</span>Put this to work</a></li>'
             . '</ol></nav>';
    $zip->add('OEBPS/nav.xhtml', epub_page('Contents', $navBody, $lang));

    // 7. Package document
    $subjects = '';
    foreach (($book['subjects'] ?? []) as $s) {
        $subjects .= '<dc:subject>' . epub_x($s) . '</dc:subject>';
    }
    $opf = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
        . '<package xmlns="http://www.idpf.org/2007/opf" version="3.0" unique-identifier="bookid" xml:lang="' . $lang . '">'
        . '<metadata xmlns:dc="http://purl.org/dc/elements/1.1/">'
        . '<dc:identifier id="bookid">' . epub_x($uid) . '</dc:identifier>'
        . '<dc:title>' . epub_x($title) . '</dc:title>'
        . '<dc:creator>' . epub_x($author) . '</dc:creator>'
        . '<dc:publisher>' . epub_x(SITE_NAME) . '</dc:publisher>'
        . '<dc:language>' . $lang . '</dc:language>'
        . '<dc:description>' . epub_x($book['description'] ?? '') . '</dc:description>'
        . '<dc:date>' . date('Y-m-d') . '</dc:date>'
        . '<dc:rights>' . epub_x('Copyright ' . date('Y') . ' ' . SITE_NAME . '. All rights reserved.') . '</dc:rights>'
        . $subjects
        . '<meta property="dcterms:modified">' . $modified . '</meta>'
        . '</metadata>'
        . '<manifest>'
        . '<item id="nav" href="nav.xhtml" media-type="application/xhtml+xml" properties="nav"/>'
        . '<item id="css" href="style.css" media-type="text/css"/>'
        . '<item id="title" href="title.xhtml" media-type="application/xhtml+xml"/>'
        . implode('', $manifest)
        . '<item id="end" href="end.xhtml" media-type="application/xhtml+xml"/>'
        . '</manifest>'
        . '<spine>'
        . '<itemref idref="title"/>'
        . '<itemref idref="nav"/>'
        . implode('', $spine)
        . '<itemref idref="end"/>'
        . '</spine>'
        . '</package>';
    $zip->add('OEBPS/content.opf', $opf);

    return $zip->build();
}

/**
 * Deterministic UUID v5-style id from a slug, so rebuilding a book keeps
 * the same identifier and readers treat it as the same title rather than
 * a duplicate on every regeneration.
 */
function epub_uuid(string $slug): string
{
    $h = sha1('vturnu-ebook-' . $slug);
    return sprintf('%08s-%04s-5%03s-%04x-%12s',
        substr($h, 0, 8), substr($h, 8, 4), substr($h, 13, 3),
        (hexdec(substr($h, 16, 4)) & 0x3fff) | 0x8000, substr($h, 20, 12));
}
