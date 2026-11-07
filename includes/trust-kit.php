<?php
/**
 * Trust badges: the independent places a buyer can check us (Google reviews,
 * Clutch, GoodFirms, TechBehemoths), drawn from TRUST_BADGES in config.php.
 *
 * One renderer, four placements: the home hero under the call to action, the
 * footer of every page, the About facts card and the Contact page. Adding an
 * entry to TRUST_BADGES puts it in all four at once.
 *
 * Every badge opens the profile in a new tab with rel="noopener nofollow",
 * the owner's rule for external links. TechBehemoths' verification wants a
 * followed link, so that badge stays unverified while the rule holds.
 */

declare(strict_types=1);

/**
 * @param string $variant hero (over the home hero photo), footer (dark
 *                        ground) or card (white panels on inner pages)
 * @param string $heading short label above the row; '' for none
 */
function render_trust_badges(string $variant = 'card', string $heading = ''): void
{
    if (!TRUST_BADGES) {
        return;
    }
    $id = 'trust-' . $variant;
    $label = $heading !== '' ? ' aria-labelledby="' . e($id) . '"' : ' aria-label="Independent ratings and listings"';
    ?>
<div class="trust trust--<?= e($variant) ?>" role="group"<?= $label ?>>
    <?php if ($heading !== ''): ?><p class="trust-h" id="<?= e($id) ?>"><?= e($heading) ?></p><?php endif; ?>
    <ul class="trust-list">
        <?php foreach (TRUST_BADGES as $b): ?>
        <li><?= trust_badge($b, $variant) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
    <?php
}

/** One badge link. See the entry shapes documented above TRUST_BADGES. */
function trust_badge(array $b, string $variant): string
{
    $name = (string) $b['name'];
    $key = (string) $b['key'];
    $attrs = ' href="' . e((string) $b['url']) . '" target="_blank" rel="noopener nofollow"';
    if (!empty($b['attr'])) {
        $attrs .= ' ' . preg_replace('/[^a-z0-9-]/', '', (string) $b['attr']);
    }
    $eager = $variant === 'hero';

    // Badge art the platform supplies, shown exactly as given.
    if (!empty($b['image'])) {
        return '<a class="tb tb--art tb--' . e($key) . '"' . $attrs . ' title="' . e($name) . '">'
            . '<img src="' . e((string) $b['image']) . '" alt="' . e((string) ($b['alt'] ?? $name)) . '"'
            . ' width="' . (int) $b['image_w'] . '" height="' . (int) $b['image_h'] . '"'
            . ($eager ? '' : ' loading="lazy"') . ' decoding="async"></a>';
    }

    $mark = '<img class="tb-mark" src="' . e((string) $b['mark']) . '" alt="" width="' . (int) $b['mark_w'] . '" height="' . (int) $b['mark_h'] . '"'
        . ($eager ? '' : ' loading="lazy"') . ' decoding="async">';

    if (isset($b['rating'])) {
        $rating = (string) $b['rating'];
        $n = (int) ($b['reviews'] ?? 0);
        $reviews = $n . ' review' . ($n === 1 ? '' : 's');
        $fill = (int) round(((float) $rating) / 5 * 100);
        return '<a class="tb tb--' . e($key) . '"' . $attrs
            . ' aria-label="' . e($rating . ' out of 5 on ' . $name . ', ' . $reviews) . ', opens in a new tab">'
            . $mark
            . '<span class="tb-t" aria-hidden="true"><span class="tb-r"><b>' . e($rating) . '</b><i class="tb-stars" style="--fill:' . $fill . '%"></i></span>'
            . '<small>' . e($n . ' ' . $name . ' review' . ($n === 1 ? '' : 's')) . '</small></span></a>';
    }

    $label = (string) ($b['label'] ?? 'Listed');
    $sub = (string) ($b['sub'] ?? $name . ' profile');
    return '<a class="tb tb--' . e($key) . '"' . $attrs
        . ' aria-label="' . e($label . ' on ' . $name) . ', opens in a new tab">'
        . $mark
        . '<span class="tb-t" aria-hidden="true"><span class="tb-r"><b>' . e($label) . '</b></span><small>' . e($sub) . '</small></span></a>';
}
