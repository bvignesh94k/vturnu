<?php
/**
 * PHP side of tests/net/harness.mjs, for tests that fetch through it.
 *
 * test_dns() answers the test host names as gethostbynamel() would, from
 * $_SERVER['TEST_DNS'], and an IPv4 address stands for itself. flip.example
 * answers with a public address the first time and a private one after
 * that, as a DNS rebinding attack would; set $GLOBALS['test_dns_flips'] = 0
 * to start again. test_leaked() is true when a result carries the internal
 * site's marker.
 */
declare(strict_types=1);

function test_dns(string $host): array|false
{
    static $map = null;
    $map ??= json_decode($_SERVER['TEST_DNS'] ?? '{}', true);
    if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        return [$host];
    }
    if ($host === 'flip.example') {
        $n = $GLOBALS['test_dns_flips'] ?? 0;
        $GLOBALS['test_dns_flips'] = $n + 1;
        return $n === 0 ? ['93.184.216.34'] : ['10.0.0.1'];
    }
    return $map[$host] ?? false;
}

function test_leaked(array $result): bool
{
    return str_contains(json_encode($result, JSON_INVALID_UTF8_SUBSTITUTE), 'INTERNAL SECRET');
}
