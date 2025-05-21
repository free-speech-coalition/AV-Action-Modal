<?php
# Start off with an hour since this is not easily undone once sent to a browser.
define('HSTS_PRELOAD_MAX_AGE', 3600);

define('ERROR_INVALID_IP', '`ip_address` is invalid');
define('ERROR_MISSING_API_KEY', '`API_KEY` is required');
define('ERROR_MISSING_IP','`ip_address` is required');
define('API_KEY_NAME', 'IP_GEO_KEY');
define('API_KEY', get_api_key());

header('Content-Type: application/json; charset=utf-8');

// CORS
# OPTIONS necessary for CORS preflight.
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
# Also add Authorization if ever needed (if FSC ever requires API tokens, etc).
header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
# Already set in server conf?
header('Access-Control-Allow-Origin: *');

// CSP
# upgrade-insecure-requests is irrelevant here since there's nothing but json.
header("Content-Security-Policy: default-src 'none'");

// HSTS
# Reduce time in dev environments.
header('Strict-Transport-Security: max-age=' . HSTS_PRELOAD_MAX_AGE . '; includeSubDomains');

// Other security headers
header('Referrer-Policy: no-referrer-when-downgrade');
# Don't sniff stuff.
header('X-Content-Type-Options: nosniff');
# Prevents framing of API data, however unlikely this may be. Could also be SAMEORIGIN.
header('X-Frame-Options: DENY');




function get_api_key() {
    // Can this be set in .htaccess instead? And then used with apache_getenv()? Example:
    // RewriteRule ^ - [E=IP_GEO_KEY:abcdefghijklmlmlmp]
    $lines = file(__DIR__."/.env", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        if ($name === API_KEY_NAME) {
            return $value;
        }
    }
    return null;
}

function get_geolocation($api_key, $ip, $lang = "en", $fields = "state_prov") {
	$ret = [];
    if (!$api_key) {
        http_response_code(500);
        return json_encode([
            'error_code' => 5002,
            'error_message' => ERROR_MISSING_API_KEY,
        ]);
    }
    $url = "https://api.ipgeolocation.io/ipgeo?apiKey=".$api_key."&ip=".$ip."&lang=".$lang."&fields=".$fields;
    $cURL = curl_init();
    curl_setopt($cURL, CURLOPT_URL, $url);
    curl_setopt($cURL, CURLOPT_HTTPGET, true);
    curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($cURL, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Accept: application/json',
        'User-Agent: '.$_SERVER['HTTP_USER_AGENT'],
    ));

    try {
        return curl_exec($cURL);
    } catch (Exception $e) {
        http_response_code(500);
        return json_encode([
            'error_code' => 5001,
            'error_message' => $e->getMessage(),
        ]);
    }
    return json_encode($ret);
}


function show_json_response() {
    $remote_ip = false;

	// @todo Update this for security reasons. Never trust HTTP_* headers. 
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $remote_ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $remote_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $remote_ip = $_SERVER['REMOTE_ADDR'];
    }
    
    if (!$remote_ip) {
        http_response_code(400);
        return json_encode([
            'error_code' => 4001,
            'error_message' => ERROR_MISSING_IP,
        ]);
    }
    
    if (filter_var($remote_ip, FILTER_VALIDATE_IP) == false) {
        http_response_code(400);
        return json_encode([
            'error_code' => 4002,
            'error_message' => ERROR_INVALID_IP,
        ]);
    }
    
    $json_response = get_geolocation(API_KEY, $remote_ip);
    return $json_response;
}

print show_json_response();





