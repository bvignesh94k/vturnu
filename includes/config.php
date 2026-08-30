<?php
/**
 * VTurnU, global configuration.
 */

declare(strict_types=1);

date_default_timezone_set('Asia/Kolkata');

define('SITE_NAME', 'VTurnU');
define('SITE_TAGLINE', 'Together we turn heads');
define('SITE_URL', 'https://vturnu.com'); // canonical production URL
define('CONTACT_PHONE', '+91 93637 31498');
define('CONTACT_PHONE_HREF', 'tel:+919363731498');
define('CONTACT_WHATSAPP', '919363731498');
define('CONTACT_EMAIL', 'sales@vturnu.com');
// Single source of truth: everything that shows a number uses CONTACT_PHONE.
define('FORM_PHONE', CONTACT_PHONE);
// Where every form submission is delivered.
define('ENQUIRY_TO', 'sales@vturnu.com');
define('COPYRIGHT', 'Copyright © 2025 VTurnU. All rights reserved.');

/* VTurnAI: our own SaaS product, sold from this site but hosted on its own
   domain. Every outbound link carries UTM tags so the product analytics can
   separate traffic this site sends from traffic vturnai.com earns directly.
   Defined once here because the links appear on ~10 pages. */
define('PRODUCT_NAME', 'VTurnAI');
define('PRODUCT_URL', 'https://vturnai.com/');
define('PRODUCT_PAGE', '/ai-visibility-tool/');   // the on-site landing page
define('PRODUCT_PRICE', '₹499');
/* String, not int: these are display values passed straight to e(), which is
   typed ?string and lives in a strict_types file, so an int here is a fatal
   rather than a coercion. Nothing does arithmetic on it. */
define('PRODUCT_TRIAL_DAYS', '7');

/** Outbound link to vturnai.com, tagged so we can attribute the signup. */
function product_url(string $campaign = 'site', string $path = ''): string {
    $base = rtrim(PRODUCT_URL, '/') . '/' . ltrim($path, '/');
    return rtrim($base, '/') . '/?utm_source=vturnu.com&utm_medium=referral&utm_campaign=' . rawurlencode($campaign);
}

define('BASE_PATH', dirname(__DIR__));

// Social profiles
define('SOCIAL_LINKS', [
    'LinkedIn'  => 'https://www.linkedin.com/company/vturnu',
    'Facebook'  => 'https://www.facebook.com/vturnu',
    'Twitter'   => 'https://twitter.com/vturnu',
    'Instagram' => 'https://www.instagram.com/vturnu',
]);

/* ------------------------------------------------------------------ */
/* Security: CSRF signing key + reCAPTCHA v3 credentials              */
/* ------------------------------------------------------------------ */

// Signs the stateless CSRF token embedded in every form (no PHP session
// needed for anonymous visitors), and the admin session cookie and ebook
// download tokens. Generated once with random_bytes(32); changing it
// invalidates every token currently sitting in an open tab.
//
// On Vercel this comes from the SECURITY_SECRET environment variable. The
// hardcoded value stays as a local-dev fallback (XAMPP has no env var to
// read), so nothing here is a real secret leak: whichever value is live in
// production is the one set in the Vercel dashboard, never this file.
define('SECURITY_SECRET', getenv('SECURITY_SECRET') ?: '8207431fffc74e211ad0a0102b7862fc33d383260ddcd835590833521f488afe');

// From https://www.google.com/recaptcha/admin (reCAPTCHA v3). Until real
// keys are set, security_recaptcha_ok() passes every submission through
// rather than blocking real customers on a misconfigured site key.
define('RECAPTCHA_SITE_KEY', '6LfgqIMtAAAAAOM2_Z4QgkIqg6JPWG3sJ9QpWhhg');
define('RECAPTCHA_SECRET_KEY', getenv('RECAPTCHA_SECRET_KEY') ?: '6LfgqIMtAAAAAKk3SHntnhRQaVcBdagHuaVNajkH');

/**
 * ISO 3166-1 alpha-2 country code -> E.164 calling code, for prefilling
 * the phone field based on the visitor's detected country. Not exhaustive
 * for every shared code (e.g. NANP members beyond +1 aren't all listed
 * individually), but covers the countries VTurnU actually serves and
 * common visitor origins.
 */
define('COUNTRY_DIAL_CODES', [
    'US'=>'+1','CA'=>'+1','IN'=>'+91','GB'=>'+44','AU'=>'+61','AE'=>'+971','SG'=>'+65',
    'AF'=>'+93','AL'=>'+355','DZ'=>'+213','AR'=>'+54','AM'=>'+374','AT'=>'+43','AZ'=>'+994',
    'BH'=>'+973','BD'=>'+880','BY'=>'+375','BE'=>'+32','BZ'=>'+501','BJ'=>'+229','BT'=>'+975',
    'BO'=>'+591','BA'=>'+387','BW'=>'+267','BR'=>'+55','BN'=>'+673','BG'=>'+359','BF'=>'+226',
    'KH'=>'+855','CM'=>'+237','CL'=>'+56','CN'=>'+86','CO'=>'+57','CR'=>'+506','HR'=>'+385',
    'CU'=>'+53','CY'=>'+357','CZ'=>'+420','DK'=>'+45','DO'=>'+1','EC'=>'+593','EG'=>'+20',
    'SV'=>'+503','EE'=>'+372','ET'=>'+251','FJ'=>'+679','FI'=>'+358','FR'=>'+33','GE'=>'+995',
    'DE'=>'+49','GH'=>'+233','GR'=>'+30','GT'=>'+502','HK'=>'+852','HU'=>'+36','IS'=>'+354',
    'ID'=>'+62','IR'=>'+98','IQ'=>'+964','IE'=>'+353','IL'=>'+972','IT'=>'+39','JM'=>'+1',
    'JP'=>'+81','JO'=>'+962','KZ'=>'+7','KE'=>'+254','KW'=>'+965','KG'=>'+996','LA'=>'+856',
    'LV'=>'+371','LB'=>'+961','LY'=>'+218','LI'=>'+423','LT'=>'+370','LU'=>'+352','MO'=>'+853',
    'MK'=>'+389','MG'=>'+261','MY'=>'+60','MV'=>'+960','ML'=>'+223','MT'=>'+356','MU'=>'+230',
    'MX'=>'+52','MD'=>'+373','MC'=>'+377','MN'=>'+976','ME'=>'+382','MA'=>'+212','MM'=>'+95',
    'NA'=>'+264','NP'=>'+977','NL'=>'+31','NZ'=>'+64','NI'=>'+505','NE'=>'+227','NG'=>'+234',
    'NO'=>'+47','OM'=>'+968','PK'=>'+92','PA'=>'+507','PG'=>'+675','PY'=>'+595','PE'=>'+51',
    'PH'=>'+63','PL'=>'+48','PT'=>'+351','QA'=>'+974','RO'=>'+40','RU'=>'+7','RW'=>'+250',
    'SA'=>'+966','RS'=>'+381','SC'=>'+248','SL'=>'+232','SK'=>'+421','SI'=>'+386','ZA'=>'+27',
    'KR'=>'+82','ES'=>'+34','LK'=>'+94','SD'=>'+249','SE'=>'+46','CH'=>'+41','SY'=>'+963',
    'TW'=>'+886','TJ'=>'+992','TZ'=>'+255','TH'=>'+66','TT'=>'+1','TN'=>'+216','TR'=>'+90',
    'TM'=>'+993','UG'=>'+256','UA'=>'+380','UY'=>'+598','UZ'=>'+998','VE'=>'+58','VN'=>'+84',
    'YE'=>'+967','ZM'=>'+260','ZW'=>'+263',
]);
