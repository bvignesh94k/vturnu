<?php
/**
 * Local currency.
 *
 * The site publishes one number: the minimum budget, ₹50,000. A visitor sees it
 * in their own currency, chosen automatically from their country by the edge geo
 * header. There is nothing to select. Anyone outside the listed countries sees USD.
 *
 * Figures are FIXED by hand in MIN_BUDGET, never live-converted, so a buyer sees
 * the same number on Tuesday as on Monday. They are clean local amounts rounded
 * up, so none falls below ₹50,000 at the rate used (about ₹96 to US$1, July 2026).
 * Review them when exchange rates move.
 */

declare(strict_types=1);

/** Display rules per currency: prefix, digit grouping. */
const CURRENCIES = [
    'INR' => ['₹',    'in'],
    'USD' => ['US$',  'w'],
    'AED' => ['AED ', 'w'],
    'SAR' => ['SAR ', 'w'],
    'QAR' => ['QAR ', 'w'],
    'SGD' => ['S$',   'w'],
    'MYR' => ['RM',   'w'],
    'IDR' => ['Rp ',  'w'],
    'GBP' => ['£',    'w'],
    'EUR' => ['€',    'w'],
    'CAD' => ['C$',   'w'],
    'AUD' => ['A$',   'w'],
    'ZAR' => ['R ',   'w'],
    'KES' => ['KSh ', 'w'],
];

/** The minimum budget in each currency. */
const MIN_BUDGET = [
    'INR' => 50000,
    'USD' => 550,
    'AED' => 2000,
    'SAR' => 2100,
    'QAR' => 2000,
    'SGD' => 750,
    'MYR' => 2400,
    'IDR' => 9500000,
    'GBP' => 425,
    'EUR' => 500,
    'CAD' => 775,
    'AUD' => 850,
    'ZAR' => 10000,
    'KES' => 72000,
];

/**
 * Monthly budget bands for the lead forms: four edges make five bands, and the
 * first edge is the minimum budget. Band index (0 to 4) is what the lead grade
 * uses, so a band means the same buying power in every currency.
 */
const BUDGET_BANDS = [
    'INR' => [50000, 150000, 500000, 1500000],
    'USD' => [550, 3000, 10000, 25000],
    'AED' => [2000, 11000, 37000, 90000],
    'SAR' => [2100, 11000, 37000, 95000],
    'QAR' => [2000, 11000, 36000, 90000],
    'SGD' => [750, 4000, 13000, 33000],
    'MYR' => [2400, 13000, 45000, 110000],
    'IDR' => [9500000, 50000000, 160000000, 400000000],
    'GBP' => [425, 2400, 8000, 20000],
    'EUR' => [500, 2800, 9000, 23000],
    'CAD' => [775, 4000, 14000, 34000],
    'AUD' => [850, 4500, 15000, 38000],
    'ZAR' => [10000, 55000, 180000, 450000],
    'KES' => [72000, 390000, 1300000, 3200000],
];

/**
 * Countries with their own currency. Everything else sees USD. Nigeria is
 * deliberately USD: the naira moves too fast for a fixed local figure.
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

/** The currency for this visitor's country. */
function visitor_currency(): string
{
    static $cur = null;
    return $cur ??= COUNTRY_CURRENCY[security_visitor_country()] ?? 'USD';
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

/** An amount formatted for display, e.g. ₹50,000, US$550, AED 2,000. */
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

/** The minimum budget in the visitor's currency. */
function min_budget(): string
{
    return money(MIN_BUDGET[visitor_currency()] ?? MIN_BUDGET['USD']);
}

/** The line that explains the minimum budget. GST applies to Indian clients only. */
function min_budget_note(): string
{
    return 'The minimum budget for any project or monthly plan. Ad spend is separate and paid directly to the platform.'
        . (gst_visible() ? ' GST is extra.' : '');
}

/**
 * The five budget bands as [key, label] in the visitor's currency.
 * Keys are b0 to b4.
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
