<?php /** Our Clients. Expects: $page, $slug, $CASES */
$logos = array_values(array_filter([
    ['sai-impression.webp', 'Sai Impression'],
    ['firstchoice-autoparts.webp', 'FirstChoice Used Auto Parts'],
    ['shineprints.png', 'Shineprints'],
    ['masaami.webp', 'Masaami'],
    ['ateliers-gym.png', 'Ateliers Gym'],
    ['black-sheep-collective.png', 'The Black Sheep Collective'],
    ['pemotech.png', 'Pemotech'],
    ['boosterio.png', 'Boosterio'],
    ['yodgy.png', 'Yodgy'],
], static fn($l) => is_file(BASE_PATH . '/assets/img/clients/' . $l[0])));
?>
<?php render_hero([
    'k' => 'Our clients', 'h1' => $page['h1'], 'lede' => $page['lede'], 'tone' => 'peach', 'layout' => 'flip',
    'cta' => ['Become a client', '/contact-us/'], 'alt' => ['Read the case studies', '/case-studies/'],
    'visual' => hero_visual(['logos', array_map(static fn($l) => $l[0], $logos)]),
]); ?>

<section class="section">
    <div class="container">
        <h2 class="section-title center">Brands that turned with us</h2>
        <ul class="logo-wall">
            <?php foreach ($logos as [$file, $name]): ?>
            <li class="tilt"><img src="/assets/img/clients/<?= e($file) ?>" alt="<?= e($name) ?>" width="160" height="56" loading="lazy" decoding="async"></li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

<?php render_work_showcase('Work you can visit today', 'Live websites and measured results, with the source of every number.'); ?>

<?php render_faqs(page_faqs($page, $slug), 'Questions about our client work'); ?>
