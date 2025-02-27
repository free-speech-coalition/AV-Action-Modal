<?php
header('Content-Type: application/json; charset=utf-8');
$API_KEY = "efaebf5eeae541019722aba565851296";

function get_geolocation($apiKey, $ip, $lang = "en", $fields = "state_prov") {
    $url = "https://api.ipgeolocation.io/ipgeo?apiKey=".$apiKey."&ip=".$ip."&lang=".$lang."&fields=".$fields;
    $cURL = curl_init();
    curl_setopt($cURL, CURLOPT_URL, $url);
    curl_setopt($cURL, CURLOPT_HTTPGET, true);
    curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($cURL, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Accept: application/json',
        'User-Agent: '.$_SERVER['HTTP_USER_AGENT']
    ));
    $response = [];
    try {
        return curl_exec($cURL);
    } catch (Exception $e) {
        var_dump($e);
        $response = $e;
    }
    return json_decode($response);
}

$remote_ip = $_GET['ip_address'] ?? false;

if (!$remote_ip) {
    http_response_code(400);
    echo json_encode([
        'error_code' => 4001,
        'error' => '`ip_address` is required'
    ]);
    return;
}

if (filter_var($remote_ip, FILTER_VALIDATE_IP) == false) {
    http_response_code(400);
    echo json_encode([
        'error_code' => 4002,
        'error' => '`ip_address` is invalid'
    ]);
    return;
}

$location = get_geolocation($API_KEY, $remote_ip);
echo $location;
?>
