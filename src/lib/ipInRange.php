<?php

declare(strict_types=1);

/**
 * Check if an IP is in a given range
 * @param string $ip IP address to check
 * @param string $range IP range to check against
 * @return bool true if IP is in range, false otherwise
 */
function ipInRange(string $ip, string $range): bool
{
    // Exact IP match check before other checks
    if (trim($range) === trim($ip)) {
        return true;
    }

    // $range is in CIDR format
    if (strpos($range, '/') !== false) {
        list($range, $netmask) = explode('/', $range, 2);
        $range_decimal = ip2long($range);
        $ip_decimal = ip2long($ip);
        $wildcard_decimal = pow(2, (32 - (int)$netmask)) - 1;
        $netmask_decimal = ~$wildcard_decimal;

        return ($ip_decimal & $netmask_decimal) == ($range_decimal & $netmask_decimal);
    }

    // Range might be 127.0.0.*
    if (strpos($range, '*') !== false) {
        $lower = str_replace('*', '0', $range);
        $upper = str_replace('*', '255', $range);
        $range = "$lower-$upper";
    }

    if (strpos($range, '-') !== false) {
        list($lower, $upper) = explode('-', $range, 2);
        $lower_decimal = (int)ip2long($lower);
        $upper_decimal = (int)ip2long($upper);
        $ip_decimal = (int)ip2long($ip);
        return ($ip_decimal >= $lower_decimal && $ip_decimal <= $upper_decimal);
    }

    // If no special format matches, try exact match again
    return trim($range) === trim($ip);
}