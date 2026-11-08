<?php
/**
 * Trust badges: the independent places a buyer can check us (Google reviews,
 * Clutch, GoodFirms, TechBehemoths, Pick an Agency). The list lives in
 * TRUST_BADGES in includes/config.php; this file only draws it.
 *
 * One renderer, four placements: the home hero under the call to action, the
 * footer strip on every page, the About facts card and the Contact page.
 * Adding an entry to TRUST_BADGES puts it in all four at once, unless the
 * entry limits itself with 'only' (one placement) or 'home' => false (never
 * on the home page). Pick an Agency uses both: inner-page footers only.
 *
 * Every badge is the same tile: equal width and height, the platform's logo
 * centred at its own optical size, and the average rating in a gold pill where
 * the platform rates us. Nothing else: no review counts, no captions.
 *
 * Links open in a new tab. They are nofollow, except where an entry sets
 * 'follow' (TechBehemoths, whose verification only accepts a followed link).
 */

declare(strict_types=1);

/**
 * @param string $variant hero (home hero), footer (strip on the dark footer)
 *                        or card (white panels on inner pages)
 * @param string $heading short label above the tiles; '' for none
 * @param bool   $on_home true when drawn on the home page, which leaves out
 *                        every badge marked 'home' => false
 */
function render_trust_badges(string $variant = 'card', string $heading = '', bool $on_home = false): void
{
    $badges = trust_badges_for($variant, $on_home);
    if (!$badges) {
        return;
    }
    $id = 'trust-' . $variant;
    $label = $heading !== '' ? ' aria-labelledby="' . e($id) . '"' : ' aria-label="Independent ratings and listings"';
    /* trust--n5 lets the CSS lay five tiles out evenly (see TRUST BADGES). */
    ?>
<div class="trust trust--<?= e($variant) ?> trust--n<?= count($badges) ?>" role="group"<?= $label ?>>
    <?php if ($heading !== ''): ?><p class="trust-h" id="<?= e($id) ?>"><?= e($heading) ?></p><?php endif; ?>
    <div class="trust-body">
        <ul class="trust-list">
            <?php foreach ($badges as $b): ?>
            <li><?= trust_badge($b, $variant === 'hero') ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
    <?php
}

/** The badges one placement shows: TRUST_BADGES in list order, less any entry
    limited to another placement ('only') or kept off the home page ('home'). */
function trust_badges_for(string $variant, bool $on_home = false): array
{
    return array_values(array_filter(TRUST_BADGES, static fn(array $b): bool =>
        (!isset($b['only']) || $b['only'] === $variant)
        && !($on_home && ($b['home'] ?? true) === false)));
}

/** One badge tile. The fields are documented above TRUST_BADGES. */
function trust_badge(array $b, bool $eager = false): string
{
    $name = (string) $b['name'];
    $rating = (string) ($b['rating'] ?? '');
    $rel = !empty($b['follow']) ? 'noopener' : 'noopener nofollow';
    $extra = !empty($b['attr']) ? ' ' . preg_replace('/[^a-z0-9-]/', '', (string) $b['attr']) : '';
    $color = (string) ($b['color'] ?? '');
    $style = preg_match('/^#[0-9a-f]{3,8}$/i', $color) ? ' style="--c:' . $color . '"' : '';

    /* A platform's own badge art names itself through its alt text, as its
       snippet does. Our logos are decorative and the link says what it is. */
    $alt = (string) ($b['alt'] ?? '');
    $aria = $alt !== '' ? '' : ' aria-label="' . e(
        ($rating !== '' ? $name . ', rated ' . $rating . ' out of 5' : 'VTurnU on ' . $name) . ' (opens in a new tab)'
    ) . '"';

    $logo = '<img class="tb-logo" src="' . e((string) $b['logo']) . '" alt="' . e($alt) . '"'
        . ' width="' . (int) $b['logo_w'] . '" height="' . (int) $b['logo_h'] . '"'
        . ' style="--h:' . (int) ($b['size'] ?? 20) . 'px"'
        . ($eager ? '' : ' loading="lazy"') . ' decoding="async">';

    $rate = $rating === '' ? '' : '<span class="tb-rate" aria-hidden="true">' . e($rating)
        . '<svg viewBox="0 0 24 24" focusable="false"><path fill="currentColor" d="M12 2.6l2.9 5.9 6.5.95-4.7 4.58 1.11 6.47L12 17.4l-5.81 3.06 1.11-6.47-4.7-4.58 6.5-.95z"/></svg></span>';

    return '<a class="tb tb--' . e((string) $b['key']) . '" href="' . e((string) $b['url']) . '" target="_blank" rel="' . $rel . '"'
        . $extra . $aria . $style . '>' . $logo . $rate . '</a>';
}
