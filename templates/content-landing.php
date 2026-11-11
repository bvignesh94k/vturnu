<?php
/**
 * Content writing and content marketing landing page.
 * Expects: $page, $slug, $CONTENT_LANDINGS. Blocks: includes/content-kit.php.
 *
 * One page, one offer. The order follows the buyer: what this is, whether it is
 * their problem, what good looks like, what they get, how it runs, proof, fit
 * and the form. Every button leads to the enquiry form at #enquire unless a
 * call to action names another page on purpose.
 */
$l = $CONTENT_LANDINGS[$slug];

echo cl_jsonld($page, $slug, $l);

render_hero([
    'k' => $l['eyebrow'],
    'h1' => $l['hero'],
    'lede' => $page['lede'],
    'cta' => [$l['cta'], '#enquire'],
    'alt' => $l['alt'] ?? ['See what is included', '#scope'],
    'visual' => cx_visual($l['visual']),
    'tone' => 'paper',
    'layout' => $l['layout'] ?? hero_layout($slug),
    'extra' => cl_chips($l['chips']),
    'class' => 'hx--content',
]);

cl_proofbar($l['proof']);
cl_answer($l['answer'], $l['facts']);
render_money_pain($l['pain']);
cl_edit($l['edit'], $l['cta']);
cl_scope($l['scope']);
cl_cta('warm', $l['cta1']);
cl_steps($l['steps']);
cl_table($l['table']);
cl_case($l['case']);
/* The fit block sits flush under the proof (it has no top padding of its own),
   so the second band comes after the pair, not between them. */
render_money_fit($l['fit']);
cl_cta('cool', $l['cta2']);
render_faqs(page_faqs($page, $slug), $l['name'] . ': questions buyers ask');
render_lead_form('Content writing', $l['form'][0], $l['form'][1], 'enquire', $l['cta'], 'landing-page');
cl_related($slug, $l['reading'] ?? []);
