<?php
/**
 * Check if an IP address is in a given range.
 *
 * This function checks if an IP address is within a specified range.
 * 
 * @param string $ip The IP address to check.
 * @param string $range The IP range to check against.
 * @return bool True if the IP address is in the range, false otherwise.
 */

function ipInRange($ip, $range) {
    if (strpos($range, '/') === false) {
        return $ip === $range;
    }

    list($range, $netmask) = explode('/', $range, 2);
    $rangeDecimal = ip2long($range);
    $ipDecimal = ip2long($ip);
    $wildcardDecimal = pow(2, (32 - $netmask)) - 1;
    $netmaskDecimal = ~$wildcardDecimal;

    return ($ipDecimal & $netmaskDecimal) === ($rangeDecimal & $netmaskDecimal);
}