<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$ERROR_INVALID_IP = '`ip_address` is invalid';
$ERROR_MISSING_API_KEY = '`API_KEY` is required';
$ERROR_MISSING_IP = '`ip_address` is required';
$API_KEY_NAME = "IP_GEO_KEY";
$API_KEY = get_api_key();

function get_api_key() {
    global $API_KEY_NAME;

    $lines = file(__DIR__."/.env", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        if ($name === $API_KEY_NAME) {
            return $value;
        }
    }
}

function get_geolocation($api_key, $ip, $lang = "en", $fields = "state_prov") {
    global $ERROR_MISSING_API_KEY;

    if (!$api_key) {
        http_response_code(500);
        return json_encode([
            'error_code' => 5002,
            'error_message' => $ERROR_MISSING_API_KEY,
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
        'User-Agent: '.$_SERVER['HTTP_USER_AGENT']
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
}

$remote_ip = false;

if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $remote_ip = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $remote_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $remote_ip = $_SERVER['REMOTE_ADDR'];
}

if (!$remote_ip) {
    http_response_code(400);
    echo json_encode([
        'error_code' => 4001,
        'error_message' => $ERROR_MISSING_IP
    ]);
    return;
}

if (filter_var($remote_ip, FILTER_VALIDATE_IP) == false) {
    http_response_code(400);
    echo json_encode([
        'error_code' => 4002,
        'error_message' => $ERROR_INVALID_IP
    ]);
    return;
}

$json_response = get_geolocation($API_KEY, $remote_ip);
echo $json_response;
?>
