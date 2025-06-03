<?php

/**
 * This file is an example of what the ip_geo_config.php file should look like.
 * It is not required to use this file, but it is recommended to create a copy of
 * it and rename it to ip_geo_config.php.
 */


/**
 * Define whether a proxied connection is required.
 * If the site is not proxied by Cloudflare, the connection will be rejected.
 * This is a security measure to prevent direct access to the site.
 *
 * @var bool
 */
define('CLOUDFLARE_PROXIED_CONNECTION_REQUIRED', true);


/**
 * Check if an IP address is within a CIDR range.
 *
 * @param string $ip The IP address to check.
 * @param string $cidr The CIDR range (e.g., "173.245.48.0/20" or "2400:cb00::/32").
 * @return bool True if the IP is within the range, false otherwise.
 */
function cloudflare_ip_in_range($ip, $cidr) {
    // Validate IP address
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return false; // Invalid IP
    }

    // Parse CIDR (e.g., "173.245.48.0/20" or "2400:cb00::/32")
    if (!preg_match('#^([0-9a-fA-F:.\\/]+)$#', $cidr, $matches)) {
        return false; // Invalid CIDR format
    }
    $cidr = $matches[1];
    list($subnet, $bits) = array_pad(explode('/', $cidr, 2), 2, null);
    if ($bits === null || !is_numeric($bits)) {
        return false; // Missing or invalid prefix length
    }
    $bits = (int)$bits;

    // Validate subnet and bits based on IP version
    $is_ipv6 = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
    $max_bits = $is_ipv6 ? 128 : 32;
    if (!filter_var($subnet, FILTER_VALIDATE_IP) || $bits < 0 || $bits > $max_bits) {
        return false; // Invalid subnet or prefix length
    }

    // Convert IP and subnet to binary
    $ip_bin = inet_pton($ip);
    $subnet_bin = inet_pton($subnet);
    if ($ip_bin === false || $subnet_bin === false) {
        return false; // Conversion failed
    }

    // Create binary mask
    $mask = str_repeat("\xFF", $bits >> 3); // Full bytes
    if ($bits % 8) {
        $mask .= chr((256 - (1 << (8 - ($bits % 8)))) & 255); // Partial byte
    }
    $mask = str_pad($mask, $is_ipv6 ? 16 : 4, "\x00", STR_PAD_RIGHT); // Pad to IP length

    // Compare using bitwise AND
    return ($ip_bin & $mask) === ($subnet_bin & $mask);
}


/**
 * Get Cloudflare IP ranges.
 * 
 * This list comes from the values found at https://www.cloudflare.com/ips/
 *
 * @return array An array of Cloudflare IP ranges.
 */
function get_cloudflare_ips() {
    return [
        // IPv4 ranges
        '173.245.48.0/20',
        '103.21.244.0/22',
        '103.22.200.0/22',
        '103.31.4.0/22',
        '141.101.64.0/18',
        '108.162.192.0/18',
        '190.93.240.0/20',
        '188.114.96.0/20',
        '197.234.240.0/22',
        '198.41.128.0/17',
        '162.158.0.0/15',
        '104.16.0.0/13',
        '104.24.0.0/14',
        '172.64.0.0/13',
        '131.0.72.0/22',
        // IPv6 ranges
        '2400:cb00::/32',
        '2606:4700::/32',
        '2803:f800::/32',
        '2405:b500::/32',
        '2405:8100::/32',
        '2a06:98c0::/29',
        '2c0f:f248::/32',
    ];
}


/**
 * Check if an IP address is a Cloudflare IP.
 *
 * @param string $ip The IP address to check.
 * @return string|false The Cloudflare IP if the IP is a Cloudflare IP, false otherwise.
 */
function is_cloudflare_ip($ip) {
    $cloudflare_ips = get_cloudflare_ips();
    foreach ($cloudflare_ips as $cidr) {
        if (cloudflare_ip_in_range($ip, $cidr)) {
            return $ip;
        }
    }
    return false;
}


/**
 * Update server variables for Cloudflare proxied connections.
 *
 * @return string|false The Cloudflare IP if the connection is proxied, false otherwise.
 */
function cloudflare_update_server_vars() {
    $is_cloudflare = is_cloudflare_ip($_SERVER['REMOTE_ADDR']);

    if (!$is_cloudflare || empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        return false;
    }

    // Validate HTTP_CF_CONNECTING_IP
    $client_ip = filter_var($_SERVER['HTTP_CF_CONNECTING_IP'], FILTER_VALIDATE_IP);
    if (!$client_ip) {
        return false;
    }

    // Set REMOTE_ADDR
    $_SERVER['REMOTE_ADDR'] = $client_ip;

    return $is_cloudflare;
}



if (defined('CLOUDFLARE_PROXIED_CONNECTION_REQUIRED') && !empty(CLOUDFLARE_PROXIED_CONNECTION_REQUIRED)) {

    // Update server variables and get Cloudflare IP status
	$cloudflare_ip = cloudflare_update_server_vars();

    if (!$cloudflare_ip) {
        http_response_code(404);
        $final = json_encode([
            'error_code' => 4002,
            'error_message' => ERROR_INVALID_IP,
        ]);
        print $final;
        exit();
    }

    define('REMOTE_IP_PREPROCESSED', true);
}







