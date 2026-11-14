<?php
/**
 * The one lead form. Every place a visitor can ask for help (home, contact, the
 * quote pop-up, service and landing pages, the audit page) renders this same
 * form, so a lead always arrives with the same fields, validation and checks.
 *
 * Flow: render_lead_form_card() draws it, assets/js/lead-form.js validates and
 * submits it to /enquiry/, and lead_submit() below re-checks everything on the
 * server, because nothing the browser says can be trusted.
 */

declare(strict_types=1);

/** The choices in the "I need help with" drop-down, grouped. */
const LEAD_SERVICES = [
    'Digital marketing' => ['SEO', 'AI search visibility', 'Google Ads and paid media', 'Social media marketing', 'Content marketing', 'Email and outbound', 'Branding'],
    'Websites and AI' => ['Website design and development', 'Ecommerce store', 'Landing pages', 'AI development'],
    'Enterprise and IT' => ['SAP CPQ', 'Salesforce', 'Odoo ERP', 'ERPNext', 'IT staffing'],
    'Not sure yet' => ['Full growth program', 'I need advice on where to start'],
];
const LEAD_SIZES = ['1 to 10 people', '11 to 50 people', '51 to 200 people', '201 to 1,000 people', '1,000+ people'];
const LEAD_TIMELINES = ['As soon as possible', 'Within 1 to 3 months', 'In 3 to 6 months', 'Just researching'];
const LEAD_SOURCES = ['home-quote', 'contact-page', 'popup', 'service-page', 'landing-page', 'audit-consultation', 'blog-post'];

/** Example numbers, so the placeholder shows what a correct entry looks like. */
const LEAD_PHONE_EXAMPLES = [
    'IN' => '98765 43210', 'US' => '415 555 0132', 'CA' => '416 555 0132', 'GB' => '7400 123456', 'AE' => '50 123 4567',
    'SA' => '50 123 4567', 'QA' => '3312 3456', 'SG' => '8123 4567', 'MY' => '12 345 6789', 'ID' => '812 3456 7890',
    'AU' => '412 345 678', 'ZA' => '71 234 5678', 'NG' => '802 123 4567', 'KE' => '712 345 678',
];

/** Every value the service drop-down may legitimately send. */
function lead_service_values(): array
{
    return array_merge(...array_values(LEAD_SERVICES));
}

/** Map a page's own name or topic to the closest drop-down choice, or ''. */
function lead_service_option(string $hint): string
{
    $h = strtolower($hint);
    $rules = [
        '/sap\s*cpq/' => 'SAP CPQ', '/salesforce/' => 'Salesforce', '/odoo/' => 'Odoo ERP', '/erpnext/' => 'ERPNext',
        '/staffing/' => 'IT staffing', '/ai development/' => 'AI development',
        '/\bai\b|chatgpt|perplexity|overviews/' => 'AI search visibility',
        '/\bseo\b|search engine/' => 'SEO',
        '/ecommerce|shopify|woocommerce|bigcommerce|magento/' => 'Ecommerce store',
        '/landing/' => 'Landing pages',
        '/web|wordpress|headless|custom design/' => 'Website design and development',
        '/google ads|facebook|instagram|\bppc\b|paid|advertising/' => 'Google Ads and paid media',
        '/social|reputation/' => 'Social media marketing', '/email|outbound/' => 'Email and outbound',
        '/brand/' => 'Branding', '/content|copywrit|translation/' => 'Content marketing',
        '/consult|digital marketing|online marketing/' => 'I need advice on where to start',
    ];
    foreach ($rules as $re => $label) {
        if (preg_match($re, $h)) {
            return $label;
        }
    }
    return '';
}

/** The service choice to preselect on a given page. */
function lead_page_service(array $page, string $slug): string
{
    return lead_service_option($slug . ' ' . ($page['h1'] ?? ''));
}

/* ------------------------------------------------------------------ */
/* Validation (server side; lead-form.js applies the same rules)       */
/* ------------------------------------------------------------------ */

/** Tidy what the visitor typed before it is checked or stored. */
function lead_normalise(array $post): array
{
    $clean = static fn($v): string => trim(preg_replace('/[ \t]+/', ' ', str_replace("\0", '', (string) $v)));
    foreach (['name', 'email', 'company', 'service', 'budget', 'company_website', 'company_size', 'timeline', 'country_code', 'country_iso', 'from_page'] as $k) {
        $post[$k] = $clean($post[$k] ?? '');
    }
    $post['email'] = strtolower($post['email']);
    $post['message'] = trim(str_replace("\0", '', (string) ($post['message'] ?? '')));

    /* Keep just the national digits: drop a pasted "+91", the trunk "0" and the spacing. */
    $dial = $post['country_code'];
    $digits = preg_replace('/\D/', '', (string) ($post['mobile'] ?? ($post['phone'] ?? '')));
    $dialDigits = preg_replace('/\D/', '', $dial);
    if ($dialDigits !== '' && str_starts_with(trim((string) ($post['mobile'] ?? '')), '+') && str_starts_with($digits, $dialDigits)) {
        $digits = substr($digits, strlen($dialDigits));
    }
    $post['mobile'] = ltrim($digits, '0');

    $site = $post['company_website'];
    if ($site !== '' && !preg_match('#^https?://#i', $site)) {
        $site = 'https://' . $site;
    }
    $post['company_website'] = $site;
    return $post;
}

/** National-number rule for a dial code: [minDigits, maxDigits, optional first-digit pattern]. */
function lead_phone_rule(string $dial): array
{
    $code = ltrim($dial, '+');
    if ($code === '91') {
        return [10, 10, '/^[6-9]/'];
    }
    if ($code === '1') {
        return [10, 10, '/^[2-9]/'];
    }
    if (isset(LEAD_PHONE_LENGTHS[$code])) {
        $l = LEAD_PHONE_LENGTHS[$code];
        return [min($l), max($l), null];
    }
    return [6, 13, null];
}

/**
 * Check a normalised lead. Returns field => message for every problem, so the
 * browser can mark each field, or an empty array when the lead is complete.
 *
 * @return array<string,string>
 */
function lead_validate(array $p): array
{
    $err = [];

    $name = $p['name'];
    if (mb_strlen($name) < 2 || !preg_match('/\p{L}/u', $name)) {
        $err['name'] = 'Please enter your full name.';
    } elseif (preg_match('/\d|@|https?:|www\./i', $name)) {
        $err['name'] = 'Please enter your name without numbers or links.';
    } elseif (mb_strlen($name) > 80) {
        $err['name'] = 'That name is too long.';
    }

    $email = $p['email'];
    $domain = substr((string) strrchr($email, '@'), 1);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !str_contains($domain, '.') || strlen($email) > 120) {
        $err['email'] = 'Please enter a valid email address, for example you@company.com.';
    } else {
        $typos = ['gmial.com' => 'gmail.com', 'gmai.com' => 'gmail.com', 'gnail.com' => 'gmail.com', 'gmail.co' => 'gmail.com',
                  'yaho.com' => 'yahoo.com', 'yahooo.com' => 'yahoo.com', 'hotmial.com' => 'hotmail.com', 'outlok.com' => 'outlook.com'];
        if (isset($typos[$domain])) {
            $err['email'] = 'Did you mean ' . substr($email, 0, -strlen($domain)) . $typos[$domain] . '?';
        }
    }

    $company = $p['company'];
    if (mb_strlen($company) < 2 || !preg_match('/\p{L}/u', $company)) {
        $err['company'] = 'Please enter your company name, so we can prepare for the conversation.';
    } elseif (mb_strlen($company) > 120) {
        $err['company'] = 'That company name is too long.';
    }

    $dial = $p['country_code'] !== '' ? $p['country_code'] : '+91';
    $digits = (string) $p['mobile'];
    [$min, $max, $first] = lead_phone_rule($dial);
    if ($digits === '') {
        $err['mobile'] = 'Please enter a phone or WhatsApp number we can reach you on.';
    } elseif (strlen($digits) < $min || strlen($digits) > $max || ($first && !preg_match($first, $digits)) || preg_match('/^(\d)\1{5,}$/', $digits)) {
        $err['mobile'] = $dial === '+91'
            ? 'Please enter a 10 digit mobile number starting with 6, 7, 8 or 9.'
            : 'That phone number does not look right for ' . $dial . '. Please check it.';
    }

    if (!in_array($p['service'], lead_service_values(), true)) {
        $err['service'] = 'Please choose what you need help with.';
    }
    if (!preg_match('/^b[0-4]$/', $p['budget']) && $p['budget'] !== 'Not decided yet') {
        $err['budget'] = 'Please choose a budget range.';
    }

    $msg = $p['message'];
    $words = str_word_count(preg_replace('/[^\p{L}\s]/u', ' ', $msg));
    if (mb_strlen($msg) < 15 || $words < 3) {
        $err['message'] = 'Please tell us a little about your goal. One or two sentences is enough.';
    } elseif (mb_strlen($msg) > 2000) {
        $err['message'] = 'That message is a little long. Please keep it under 2,000 characters.';
    }

    if ($p['company_website'] !== '' && !preg_match('#^https?://([a-z0-9-]+\.)+[a-z]{2,}(/\S*)?$#i', $p['company_website'])) {
        $err['company_website'] = 'Please enter a website address like company.com.';
    }
    if ($p['company_size'] !== '' && !in_array($p['company_size'], LEAD_SIZES, true)) {
        $err['company_size'] = 'Please choose from the list.';
    }
    if ($p['timeline'] !== '' && !in_array($p['timeline'], LEAD_TIMELINES, true)) {
        $err['timeline'] = 'Please choose from the list.';
    }
    return $err;
}

/**
 * Everything that happens to a submitted lead, in one place: security gate,
 * normalising, validation, grading, storing and emailing.
 *
 * @return array{ok:bool,error:string,errors:array<string,string>,source:string}
 */
function lead_submit(array $post): array
{
    $source = (string) ($post['lead_source'] ?? '');
    $source = in_array($source, LEAD_SOURCES, true) ? $source : 'popup';
    $fail = static fn(string $msg, array $errors = []): array => ['ok' => false, 'error' => $msg, 'errors' => $errors, 'source' => $source];

    $check = security_check_submission($post);
    if (!$check['ok']) {
        return $fail($check['error']);
    }
    $token = (string) ($post['csrf_token'] ?? '');
    if (security_token_used($token)) {
        return $fail('This form was already sent. Please refresh the page if you want to send another.');
    }

    $lead = lead_normalise($post);
    $errors = lead_validate($lead);
    if ($errors) {
        return $fail(reset($errors), $errors);
    }
    if (!security_rate_ok('email', 3, 86400, $lead['email'])) {
        return $fail('We already have your enquiry. A strategist will reply within one working day, or email ' . CONTACT_EMAIL . '.');
    }

    $lead = enquiry_qualify($lead);
    if (!save_enquiry($lead, $source)) {
        return $fail('We could not save your enquiry just now. Please call ' . CONTACT_PHONE . ' or email ' . CONTACT_EMAIL . ' and we will help right away.');
    }
    security_token_burn($token);
    return ['ok' => true, 'error' => '', 'errors' => [], 'source' => $source];
}

/* ------------------------------------------------------------------ */
/* The form                                                            */
/* ------------------------------------------------------------------ */

/** A value the visitor already typed, so a failed non-JavaScript submit does not wipe the form. */
function lf_old(string $name, string $default = ''): string
{
    if (($GLOBALS['form_status'] ?? null) === 'error' && isset($_POST[$name]) && is_string($_POST[$name])) {
        return $_POST[$name];
    }
    return $default;
}

/** One labelled field with room for its error message. */
function lf_field(string $uid, string $name, string $label, string $control, bool $required = true, string $hint = '', string $class = ''): string
{
    $msg = (($GLOBALS['form_status'] ?? null) === 'error') ? (string) (($GLOBALS['form_errors'] ?? [])[$name] ?? '') : '';
    return '<div class="lf-f ' . e($class) . ($msg !== '' ? ' has-error' : '') . '" data-field="' . e($name) . '">'
        . '<label for="' . e($uid) . '-' . e($name) . '">' . e($label)
        . ($required ? ' <span class="lf-req" aria-hidden="true">*</span>' : ' <span class="lf-opt">optional</span>') . '</label>'
        . $control
        . ($hint !== '' ? '<p class="lf-hint">' . e($hint) . '</p>' : '')
        . '<p class="lf-err" id="' . e($uid) . '-' . e($name) . '-err" role="alert">' . e($msg) . '</p></div>';
}

/** <option> tags for a plain list, marking one as selected. */
function lf_options(array $items, string $selected = ''): string
{
    $out = '';
    foreach ($items as $v) {
        $out .= '<option' . ($v === $selected ? ' selected' : '') . '>' . e($v) . '</option>';
    }
    return $out;
}

/**
 * The phone field: a country picker showing flag and dial code, chosen from the
 * visitor's IP, beside the number. A real <select> stays underneath, so the
 * form still submits without JavaScript.
 */
function lf_phone(string $uid, bool $lazy): string
{
    $iso = security_visitor_country();
    if (!isset(COUNTRY_DIAL_CODES[$iso])) {
        $iso = 'IN';
    }
    $names = country_names();
    $rows = [];
    foreach (COUNTRY_DIAL_CODES as $code => $dial) {
        $rows[] = [$names[$code] ?? $code, $code, $dial];
    }
    usort($rows, static fn($a, $b) => strcmp($a[0], $b[0]));
    $iso = strtoupper(lf_old('country_iso', $iso));
    $dial = COUNTRY_DIAL_CODES[$iso] ?? '+91';

    $opts = '';
    foreach ($rows as [$label, $code, $d]) {
        $opts .= '<option value="' . e($d) . '" data-iso="' . e($code) . '"' . ($code === $iso ? ' selected' : '') . '>' . e($d . ' ' . $label) . '</option>';
    }
    $ph = LEAD_PHONE_EXAMPLES[$iso] ?? 'Phone number';
    $current = ($names[$iso] ?? $iso) . ' ' . $dial;

    $control = '<div class="lf-phone" data-phone>'
        . '<div class="lf-cc" data-cc>'
        . '<button type="button" class="lf-cc-btn" aria-haspopup="listbox" aria-expanded="false" aria-label="Country calling code, now ' . e($current) . '" data-cc-btn>'
        . '<img class="lf-flag" src="/assets/img/flags/' . e(strtolower($iso)) . '.svg" width="22" height="15" alt=""' . ($lazy ? ' loading="lazy"' : '') . ' decoding="async">'
        . '<span class="lf-dial">' . e($dial) . '</span><i class="lf-caret" aria-hidden="true"></i></button>'
        . '<select class="lf-cc-select" name="country_code" aria-label="Country calling code" data-cc-select>' . $opts . '</select>'
        . '<input type="hidden" name="country_iso" value="' . e($iso) . '">'
        . '</div>'
        . '<input id="' . e($uid) . '-mobile" name="mobile" type="tel" inputmode="tel" autocomplete="tel-national" required maxlength="22"'
        . ' placeholder="' . e($ph) . '" value="' . e(lf_old('mobile')) . '" aria-describedby="' . e($uid) . '-mobile-err">'
        . '</div>';
    return lf_field($uid, 'mobile', 'Phone / WhatsApp', $control, true, '', 'lf-f--phone');
}

/**
 * Draw the lead form.
 *
 * @param array{uid?:string,source?:string,service?:string,button?:string,tone?:string,lazy?:bool} $o
 *   uid: unique id prefix (several forms can share a page), source: where it sits,
 *   service: drop-down choice to preselect, tone: '' or 'dark', lazy: defer the flag image.
 */
function render_lead_form_card(array $o = []): void
{
    $uid = $o['uid'] ?? 'lf';
    $source = $o['source'] ?? 'contact-page';
    $service = lf_old('service', $o['service'] ?? '');
    $button = $o['button'] ?? 'Send my enquiry';
    $path = (string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
    /* After a failed submission the contact page keeps where the enquiry began. */
    if ($source === 'contact-page') {
        $ps = lf_old('lead_source');
        if (in_array($ps, LEAD_SOURCES, true)) { $source = $ps; }
        $pf = lf_old('from_page');
        if (preg_match('#^/[A-Za-z0-9/_-]{0,200}$#', $pf)) { $path = $pf; }
    }
    $v = static fn(string $n): string => e(lf_old($n));
    $in = static fn(string $uid, string $name, string $type, string $attrs): string
        => '<input id="' . e($uid) . '-' . e($name) . '" name="' . e($name) . '" type="' . $type . '" value="' . e(lf_old($name)) . '" aria-describedby="' . e($uid) . '-' . e($name) . '-err" ' . $attrs . '>';

    $serviceSel = '<select id="' . e($uid) . '-service" name="service" required aria-describedby="' . e($uid) . '-service-err"><option value="">Select a service</option>';
    foreach (LEAD_SERVICES as $group => $items) {
        $serviceSel .= '<optgroup label="' . e($group) . '">' . lf_options($items, $service) . '</optgroup>';
    }
    $serviceSel .= '</select>';

    $budgetSel = '<select id="' . e($uid) . '-budget" name="budget" required aria-describedby="' . e($uid) . '-budget-err"><option value="">Select a range</option>'
        . str_replace('<option value="' . lf_old('budget') . '">', '<option value="' . lf_old('budget') . '" selected>', budget_options(['Not decided yet']))
        . '</select>';
    ?>
<form class="lf<?= ($o['tone'] ?? '') === 'dark' ? ' lf--dark' : '' ?>" id="<?= e($uid) ?>" method="post" action="/contact-us/" data-lf>
    <?= csrf_field() ?>
    <input type="hidden" name="recaptcha_token" class="js-recaptcha-token">
    <input type="hidden" name="vt_form" value="lead-v3">
    <input type="hidden" name="lead_source" value="<?= e($source) ?>">
    <input type="hidden" name="from_page" value="<?= e($path) ?>">
    <div class="lf-hp" aria-hidden="true"><label for="<?= e($uid) ?>-website">Leave this empty</label><input id="<?= e($uid) ?>-website" name="website" type="text" tabindex="-1" autocomplete="off"></div>

    <div class="lf-grid">
        <?= lf_field($uid, 'name', 'Full name', $in($uid, 'name', 'text', 'required autocomplete="name" maxlength="80" placeholder="Your name"')) ?>
        <?= lf_field($uid, 'email', 'Work email', $in($uid, 'email', 'email', 'required autocomplete="email" maxlength="120" inputmode="email" placeholder="you@company.com"')) ?>
        <?= lf_phone($uid, !empty($o['lazy'])) ?>
        <?= lf_field($uid, 'company', 'Company', $in($uid, 'company', 'text', 'required autocomplete="organization" maxlength="120" placeholder="Company name"')) ?>
        <?= lf_field($uid, 'service', 'I need help with', $serviceSel) ?>
        <?= lf_field($uid, 'budget', 'Monthly budget', $budgetSel) ?>
    </div>
    <?= lf_field($uid, 'message', 'What would you like to achieve?', '<textarea id="' . e($uid) . '-message" name="message" rows="4" required minlength="15" maxlength="2000" aria-describedby="' . e($uid) . '-message-err" placeholder="For example: we want more qualified enquiries from Google and AI search in the next six months.">' . $v('message') . '</textarea>', true, '', 'lf-f--wide') ?>

    <details class="lf-more"<?= (lf_old('company_website') . lf_old('company_size') . lf_old('timeline')) !== '' ? ' open' : '' ?>>
        <summary>Add detail for a sharper reply <span class="lf-opt">optional</span></summary>
        <div class="lf-grid">
            <?= lf_field($uid, 'company_website', 'Company website', $in($uid, 'company_website', 'text', 'autocomplete="url" inputmode="url" maxlength="160" placeholder="company.com"'), false) ?>
            <?= lf_field($uid, 'timeline', 'When do you want to start?', '<select id="' . e($uid) . '-timeline" name="timeline"><option value="">Select</option>' . lf_options(LEAD_TIMELINES, lf_old('timeline')) . '</select>', false) ?>
            <?= lf_field($uid, 'company_size', 'Company size', '<select id="' . e($uid) . '-company_size" name="company_size"><option value="">Select</option>' . lf_options(LEAD_SIZES, lf_old('company_size')) . '</select>', false) ?>
        </div>
    </details>

    <div class="lf-alert" role="alert" hidden></div>
    <button class="lf-submit" type="submit"><span class="lf-submit-t"><?= e($button) ?></span><svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path d="M5 12h13m-5-6 6 6-6 6" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
    <ul class="lf-assure">
        <li>A senior strategist replies within one working day</li>
        <li>Your details stay private</li>
        <li>No obligation</li>
    </ul>
    <p class="lf-legal">Protected by reCAPTCHA. The Google <a href="https://policies.google.com/privacy" target="_blank" rel="noopener nofollow">Privacy Policy</a> and <a href="https://policies.google.com/terms" target="_blank" rel="noopener nofollow">Terms of Service</a> apply.</p>
</form>
    <?php
}
