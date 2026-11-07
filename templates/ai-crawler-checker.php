<?php /** AI Crawler Checker, a free tool. Expects: $page, $slug, $aic_result, $aic_error, $aic_input */
require_once BASE_PATH . '/includes/ai-crawlers.php';

$crawlers = aic_crawlers();
$checked = date('j F Y', strtotime(AIC_VERIFIED));

echo jsonld_script([
    '@context' => 'https://schema.org',
    '@type' => 'WebApplication',
    '@id' => abs_url('/ai-crawler-checker/') . '#app',
    'name' => 'AI Crawler Checker',
    'url' => abs_url('/ai-crawler-checker/'),
    'description' => 'Checks a page against the robots.txt rules for ' . count($crawlers) . ' AI crawlers and reports how much of the page can be read without JavaScript.',
    'applicationCategory' => 'DeveloperApplication',
    'operatingSystem' => 'Any',
    'browserRequirements' => 'Any modern web browser',
    'isAccessibleForFree' => true,
    'publisher' => ['@id' => SITE_URL . '/#organization'],
]);

render_hero([
    'k' => 'Free tool, no signup',
    'h1' => $page['h1'],
    'lede' => $page['lede'],
    'tone' => 'lilac',
    'layout' => 'tight',
    'visual' => hero_visual(['code', 'robots.txt', ['User-agent: GPTBot', 'Disallow: /', '', 'User-agent: OAI-SearchBot', 'Allow: /'], 'Read the way crawlers read it']),
]);
?>

<section class="section aic-tool" id="check">
    <div class="container narrow">
        <?php if ($aic_error !== ''): ?>
        <div class="alert alert-error" role="alert"><?= e($aic_error) ?></div>
        <?php endif; ?>

        <form method="post" action="/ai-crawler-checker/#results" class="panel aic-form">
            <?= csrf_field() ?>
            <label for="aic-url">Page to check</label>
            <div class="aic-row">
                <input id="aic-url" name="site_url" type="text" inputmode="url" placeholder="yourcompany.com" required value="<?= e($aic_input) ?>" autocomplete="url" spellcheck="false">
                <button class="btn btn-grad" type="submit">Check crawlers</button>
            </div>
            <div class="honeypot" aria-hidden="true"><input name="website" type="text" tabindex="-1" autocomplete="off"></div>
            <p class="aic-hint">Checks <?= count($crawlers) ?> AI crawlers against your robots.txt and reads the page the way a crawler without JavaScript does. Free and no signup. A check can take up to 20 seconds.</p>
        </form>

        <?php if ($aic_result) {
            aic_render_results($aic_result);
        } ?>
    </div>
</section>

<section class="section section-tint" id="crawlers">
    <div class="container">
        <p class="eyebrow">Reference</p>
        <h2 class="aic-ref-h">The AI crawlers this tool checks</h2>
        <p class="aic-lead">Each line restates the company's own documentation, checked on <?= e($checked) ?>. The crawler names are the tokens you write in robots.txt.</p>
        <?php foreach (aic_groups() as $g => [$heading, $intro]): ?>
        <h3 class="aic-ref-group"><?= e($heading) ?></h3>
        <p class="aic-group-intro"><?= e($intro) ?></p>
        <div class="aic-table-wrap">
            <table class="aic-table aic-ref">
                <thead><tr><th scope="col">Crawler</th><th scope="col">What it does</th><th scope="col">Source</th></tr></thead>
                <tbody>
                    <?php foreach ($crawlers as [$token, $vendor, $group, $does, $note, $docs]): if ($group !== $g) { continue; } ?>
                    <tr>
                        <th scope="row"><code><?= e($token) ?></code><small><?= e($vendor) ?></small></th>
                        <td><?= e($does) ?><?php if ($note !== ''): ?> <em><?= e($note) ?></em><?php endif; ?></td>
                        <td><?= ext_link($docs, $vendor . ' docs') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="section">
    <div class="container narrow aic-prose">
        <h2>Search, user fetchers and training are three separate decisions</h2>
        <p>Most AI companies now run more than one crawler, and robots.txt treats each one on its own. Blocking one does not block the others.</p>
        <ul class="check-list">
            <li><strong>Search and AI answer crawlers</strong>, such as OAI-SearchBot, Claude-SearchBot and PerplexityBot, decide whether an assistant can find your page and cite it. If you want to be recommended, keep these open.</li>
            <li><strong>User fetchers</strong>, such as ChatGPT-User and Perplexity-User, load a page when someone asks an assistant about it. Several companies say these may not follow robots.txt, because a person asked for the page.</li>
            <li><strong>Training crawlers and controls</strong>, such as GPTBot, ClaudeBot and Google-Extended, decide whether your content can be used to build models. OpenAI, Anthropic, Google and Apple document these separately from their search crawlers, so you can opt out of training and stay in AI search.</li>
        </ul>

        <h2>Example robots.txt rules</h2>
        <h3>Stay open to AI search, opt out of model training</h3>
        <pre class="aic-code"><code># Training crawlers and controls: opt out
User-agent: GPTBot
Disallow: /

User-agent: ClaudeBot
Disallow: /

User-agent: Google-Extended
Disallow: /

User-agent: Applebot-Extended
Disallow: /

User-agent: meta-externalagent
Disallow: /

User-agent: CCBot
Disallow: /

User-agent: MistralAI-Training
Disallow: /

# Everyone else, including search and AI answer crawlers
User-agent: *
Allow: /</code></pre>
        <p>Amazonbot is left out on purpose: Amazon uses it to improve its products as well as for training, so decide on it separately. Run this checker again after you publish the change.</p>

        <h3>Welcome every crawler</h3>
        <p>If you want every AI crawler, make sure no rule blocks them. A robots.txt with only <code>User-agent: *</code> and <code>Allow: /</code>, or no robots.txt at all, allows everything.</p>

        <h3>Avoid these mistakes</h3>
        <ul class="check-list">
            <li>Do not disallow Googlebot or Bingbot to keep content out of AI. That removes the pages from Google Search or Bing too.</li>
            <li>Search tags such as noindex and nosnippet change what appears in search, including Google's AI features. They are not training controls. For Google, the training control is Google-Extended.</li>
            <li>Check robots.txt after redesigns, plugin updates and CDN changes, which often rewrite it.</li>
            <li>Allow time for changes to apply. DuckDuckGo, for example, says a DuckAssistBot opt-out takes effect after 72 hours.</li>
        </ul>

        <h2>Why JavaScript matters</h2>
        <p>Many sites build their pages in the browser with JavaScript. A crawler that does not run JavaScript gets only the HTML the server sends, which can be an empty shell. In December 2024, <?= ext_link('https://vercel.com/blog/the-rise-of-the-ai-crawler', 'Vercel and MERJ reported') ?> that none of the major AI crawlers they studied rendered JavaScript, including OpenAI's crawlers, ClaudeBot, Meta-ExternalAgent and PerplexityBot. Google's Gemini uses Googlebot's infrastructure, and <?= ext_link('https://support.apple.com/en-us/119829', 'Apple says') ?> Applebot may render pages in a browser.</p>
        <p>This checker counts the words your server sends before any JavaScript runs. If the number is low, render the main content on the server.</p>

        <h2>What this checker cannot see</h2>
        <ul class="check-list">
            <li><strong>Firewalls and CDNs.</strong> A security rule can block AI crawlers that robots.txt allows. <?= ext_link('https://developers.google.com/search/docs/appearance/ai-features', 'Google\'s guidance') ?> asks site owners to check that crawling is allowed in robots.txt and by any CDN or hosting setup.</li>
            <li><strong>Whether a crawler visited.</strong> Your server logs show which crawlers came and what they received.</li>
            <li><strong>Pages after JavaScript runs.</strong> Crawlers that do run JavaScript, such as Googlebot, may see more than the word count here.</li>
            <li><strong>Different answers for different visitors.</strong> We fetch the page as "VTurnU-AI-Crawler-Checker". If your site treats crawlers differently by name or address, they may get another answer.</li>
        </ul>
    </div>
</section>

<?php render_faqs(page_faqs($page, $slug), 'AI crawler questions'); ?>

<section class="cta-mid">
    <div class="container cta-mid-inner">
        <div>
            <h2>Want to know what AI says about your company?</h2>
            <p>Crawler access is step one. We check how ChatGPT, Gemini, Perplexity and AI Overviews describe your brand, and what to fix first.</p>
        </div>
        <div class="cta-mid-actions">
            <a class="btn btn-dark" href="/ai/">See AI search services</a>
            <a class="cta-phone" href="/contact-us/">or ask for a review</a>
        </div>
    </div>
</section>
