<?php
/**
 * Local-currency pricing.
 *
 * Every price on the site is a FIXED figure per currency, set by hand in
 * PRICE_BOOK below. There is no live conversion: rates move daily, and a B2B
 * buyer should see the same number on Tuesday as on Monday.
 *
 * Which currency a visitor sees:
 *   1. their own choice from the switcher (cookie vt_cur), else
 *   2. their country from the edge geo header, mapped by COUNTRY_CURRENCY,
 *      else USD.
 *
 * To change a price, edit PRICE_BOOK. To add a currency, add it to CURRENCIES,
 * PRICE_BOOK and BUDGET_BANDS, and map its countries in COUNTRY_CURRENCY.
 */

declare(strict_types=1);

/** Display rules per currency: prefix, decimal grouping style, switcher label. */
const CURRENCIES = [
    'INR' => ['₹',    'in', 'INR (₹) India'],
    'USD' => ['US$',  'w',  'USD ($) US dollar'],
    'AED' => ['AED ', 'w',  'AED UAE dirham'],
    'SAR' => ['SAR ', 'w',  'SAR Saudi riyal'],
    'QAR' => ['QAR ', 'w',  'QAR Qatari riyal'],
    'SGD' => ['S$',   'w',  'SGD (S$) Singapore dollar'],
    'MYR' => ['RM',   'w',  'MYR (RM) Malaysian ringgit'],
    'IDR' => ['Rp ',  'w',  'IDR (Rp) Indonesian rupiah'],
    'GBP' => ['£',    'w',  'GBP (£) British pound'],
    'EUR' => ['€',    'w',  'EUR (€) Euro'],
    'CAD' => ['C$',   'w',  'CAD (C$) Canadian dollar'],
    'AUD' => ['A$',   'w',  'AUD (A$) Australian dollar'],
    'ZAR' => ['R ',   'w',  'ZAR (R) South African rand'],
    'KES' => ['KSh ', 'w',  'KES (KSh) Kenyan shilling'],
];

/**
 * Fixed prices. starter / growth = monthly marketing plans; website / store =
 * one-time builds. Rounded to clean local figures at roughly INR parity.
 */
const PRICE_BOOK = [
    'INR' => ['starter' => 35000,   'growth' => 90000,    'website' => 45000,   'store' => 95000],
    'USD' => ['starter' => 449,     'growth' => 1099,     'website' => 549,     'store' => 1149],
    'AED' => ['starter' => 1649,    'growth' => 3999,     'website' => 1999,    'store' => 4199],
    'SAR' => ['starter' => 1699,    'growth' => 4099,     'website' => 2049,    'store' => 4299],
    'QAR' => ['starter' => 1649,    'growth' => 3999,     'website' => 1999,    'store' => 4199],
    'SGD' => ['starter' => 599,     'growth' => 1449,     'website' => 729,     'store' => 1499],
    'MYR' => ['starter' => 1999,    'growth' => 4799,     'website' => 2399,    'store' => 4999],
    'IDR' => ['starter' => 7250000, 'growth' => 17500000, 'website' => 8750000, 'store' => 18500000],
    'GBP' => ['starter' => 349,     'growth' => 849,      'website' => 429,     'store' => 899],
    'EUR' => ['starter' => 419,     'growth' => 999,      'website' => 499,     'store' => 1049],
    'CAD' => ['starter' => 619,     'growth' => 1499,     'website' => 749,     'store' => 1549],
    'AUD' => ['starter' => 679,     'growth' => 1649,     'website' => 829,     'store' => 1749],
    'ZAR' => ['starter' => 7999,    'growth' => 19499,    'website' => 9799,    'store' => 20499],
    'KES' => ['starter' => 57999,   'growth' => 141999,   'website' => 69999,   'store' => 147999],
];

/**
 * Monthly budget bands for the lead forms: four edges make five bands.
 * Band index (0 to 4) is what the lead grade uses, so a band means the same
 * buying power in every currency. India uses its own market-native edges.
 */
const BUDGET_BANDS = [
    'INR' => [50000, 150000, 500000, 1500000],
    'USD' => [1000, 3000, 10000, 25000],
    'AED' => [3500, 11000, 37000, 90000],
    'SAR' => [3500, 11000, 37000, 95000],
    'QAR' => [3500, 11000, 36000, 90000],
    'SGD' => [1300, 4000, 13000, 33000],
    'MYR' => [4500, 13000, 45000, 110000],
    'IDR' => [15000000, 50000000, 160000000, 400000000],
    'GBP' => [800, 2400, 8000, 20000],
    'EUR' => [900, 2800, 9000, 23000],
    'CAD' => [1400, 4000, 14000, 34000],
    'AUD' => [1500, 4500, 15000, 38000],
    'ZAR' => [18000, 55000, 180000, 450000],
    'KES' => [130000, 390000, 1300000, 3200000],
];

/**
 * Countries with their own price list. Everything else sees USD. Nigeria is
 * deliberately USD: the naira moves too fast for a fixed local price.
 */
const COUNTRY_CURRENCY = [
    'IN' => 'INR', 'US' => 'USD', 'AE' => 'AED', 'SA' => 'SAR', 'QA' => 'QAR', 'SG' => 'SGD',
    'MY' => 'MYR', 'ID' => 'IDR', 'GB' => 'GBP', 'CA' => 'CAD', 'AU' => 'AUD', 'ZA' => 'ZAR',
    'KE' => 'KES',
    // Eurozone
    'AT' => 'EUR', 'BE' => 'EUR', 'CY' => 'EUR', 'DE' => 'EUR', 'EE' => 'EUR', 'ES' => 'EUR',
    'FI' => 'EUR', 'FR' => 'EUR', 'GR' => 'EUR', 'HR' => 'EUR', 'IE' => 'EUR', 'IT' => 'EUR',
    'LT' => 'EUR', 'LU' => 'EUR', 'LV' => 'EUR', 'MT' => 'EUR', 'NL' => 'EUR', 'PT' => 'EUR',
    'SI' => 'EUR', 'SK' => 'EUR',
];

const CURRENCY_COOKIE = 'vt_cur';

/** The currency this visitor sees. */
function visitor_currency(): string
{
    static $cur = null;
    if ($cur !== null) {
        return $cur;
    }
    $pick = strtoupper((string) ($_COOKIE[CURRENCY_COOKIE] ?? ''));
    if (isset(CURRENCIES[$pick])) {
        return $cur = $pick;
    }
    return $cur = COUNTRY_CURRENCY[security_visitor_country()] ?? 'USD';
}

/** Group digits: western 1,234,567 or Indian 12,34,567. */
function currency_digits(int $n, string $style): string
{
    if ($style !== 'in' || $n < 100000) {
        return number_format($n);
    }
    $s = (string) $n;
    $last3 = substr($s, -3);
    $rest = substr($s, 0, -3);
    return preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest) . ',' . $last3;
}

/** An amount formatted for display, e.g. ₹35,000, US$449, AED 1,649. */
function money(int $amount, ?string $cur = null): string
{
    $cur = $cur ?? visitor_currency();
    [$prefix, $style] = CURRENCIES[$cur] ?? CURRENCIES['USD'];
    /* Americans read a bare "$", everyone else needs the "US" to tell it
       apart from their own dollar. */
    if ($cur === 'USD' && in_array(security_visitor_country(), ['US', 'PR'], true)) {
        $prefix = '$';
    }
    return $prefix . currency_digits($amount, $style);
}

/** A price from PRICE_BOOK in the visitor's currency. */
function price(string $key, ?string $cur = null): string
{
    $cur = $cur ?? visitor_currency();
    $amount = PRICE_BOOK[$cur][$key] ?? PRICE_BOOK['USD'][$key] ?? 0;
    return money((int) $amount, isset(PRICE_BOOK[$cur][$key]) ? $cur : 'USD');
}

/**
 * The five budget bands as [key, label] in the visitor's currency, plus
 * extra rows passed in (e.g. "Not decided yet"). Keys are b0 to b4.
 */
function budget_bands(?string $cur = null): array
{
    $cur = $cur ?? visitor_currency();
    $e = BUDGET_BANDS[$cur] ?? BUDGET_BANDS['USD'];
    $m = static fn(int $v): string => money($v, $cur);
    return [
        ['b0', 'Under ' . $m($e[0]) . ' / month'],
        ['b1', $m($e[0]) . ' to ' . $m($e[1]) . ' / month'],
        ['b2', $m($e[1]) . ' to ' . $m($e[2]) . ' / month'],
        ['b3', $m($e[2]) . ' to ' . $m($e[3]) . ' / month'],
        ['b4', $m($e[3]) . '+ / month'],
    ];
}

/**
 * Turn a posted budget value into a readable label for the CRM and email.
 * Band keys become the visitor's local band plus the USD band, so sales can
 * compare leads from different countries. Free text passes through.
 */
function budget_label(string $value): string
{
    if (!preg_match('/^b([0-4])$/', $value, $mm)) {
        return $value;
    }
    $i = (int) $mm[1];
    $local = budget_bands()[$i][1];
    $usd = budget_bands('USD')[$i][1];
    return visitor_currency() === 'USD' ? $local : $local . ' (about ' . str_replace(' / month', '', $usd) . ')';
}

/** <option> tags for a budget <select>; $extra rows are appended as-is. */
function budget_options(array $extra = []): string
{
    $out = '';
    foreach (budget_bands() as [$key, $label]) {
        $out .= '<option value="' . e($key) . '">' . e($label) . '</option>';
    }
    foreach ($extra as $label) {
        $out .= '<option>' . e($label) . '</option>';
    }
    return $out;
}

/**
 * A small currency switcher. Works without JavaScript (GET form with a
 * button); main.js submits it on change and hides the button.
 */
function currency_switcher(string $id, string $class = ''): string
{
    $here = (string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
    $cur = visitor_currency();
    $out = '<form class="cur-switch ' . e($class) . '" method="get" action="/currency/">'
         . '<label for="' . e($id) . '">Prices in</label>'
         . '<select id="' . e($id) . '" name="c" class="js-cur-select">';
    foreach (CURRENCIES as $code => [, , $label]) {
        $out .= '<option value="' . e($code) . '"' . ($code === $cur ? ' selected' : '') . '>' . e($label) . '</option>';
    }
    return $out . '</select>'
         . '<input type="hidden" name="r" value="' . e($here) . '">'
         . '<button type="submit" class="js-cur-go">Set</button>'
         . '</form>';
}

/**
 * GET /currency/?c=EUR&r=/pricing/ stores the choice for a year and goes
 * back. Only local paths are accepted for r, so this cannot be used as an
 * open redirect.
 */
function currency_route(): void
{
    $c = strtoupper((string) ($_GET['c'] ?? ''));
    $r = (string) ($_GET['r'] ?? '/');
    if (!preg_match('#^/[A-Za-z0-9/_\-.]*$#', $r) || str_starts_with($r, '//')) {
        $r = '/';
    }
    if (isset(CURRENCIES[$c])) {
        setcookie(CURRENCY_COOKIE, $c, [
            'expires' => time() + 31536000, 'path' => '/', 'secure' => !empty($_SERVER['HTTPS']) || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https',
            'httponly' => false, 'samesite' => 'Lax',
        ]);
    }
    header('Cache-Control: no-store');
    header('Location: ' . $r, true, 303);
    exit;
}
