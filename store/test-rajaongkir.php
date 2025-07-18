<?php
// Test script for Rajaongkir API integration
echo "<h2>Rajaongkir API Test</h2>";

// Test 1: Test proxy endpoint
echo "<h3>Test 1: Proxy Connection</h3>";
$testUrl = "rajaongkir-proxy.php?action=test";
$testResponse = file_get_contents($testUrl);
echo "<pre>$testResponse</pre>";

// Test 2: Test provinces endpoint
echo "<h3>Test 2: Provinces</h3>";
$provincesUrl = "rajaongkir-proxy.php?action=provinces";
$provincesResponse = file_get_contents($provincesUrl);
$provincesData = json_decode($provincesResponse, true);
if ($provincesData) {
    echo "<pre>" . json_encode($provincesData, JSON_PRETTY_PRINT) . "</pre>";
} else {
    echo "<p>Failed to get provinces</p>";
}

// Test 3: Test cities endpoint (using Jawa Barat as example)
echo "<h3>Test 3: Cities (Jawa Barat)</h3>";
$citiesUrl = "rajaongkir-proxy.php?action=cities&province_id=9";
$citiesResponse = file_get_contents($citiesUrl);
$citiesData = json_decode($citiesResponse, true);
if ($citiesData) {
    echo "<pre>" . json_encode($citiesData, JSON_PRETTY_PRINT) . "</pre>";
} else {
    echo "<p>Failed to get cities</p>";
}

// Test 4: Test cost calculation
echo "<h3>Test 4: Cost Calculation (Bandung to Jakarta, JNE)</h3>";
$costData = [
    'origin' => 23,      // Bandung
    'destination' => 151, // Jakarta
    'weight' => 1000,    // 1kg
    'courier' => 'jne'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "rajaongkir-proxy.php?action=cost");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($costData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$costResponse = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p>HTTP Code: $httpCode</p>";
echo "<pre>$costResponse</pre>";

// Test 5: Test with JNT
echo "<h3>Test 5: Cost Calculation (Bandung to Jakarta, JNT)</h3>";
$costData['courier'] = 'jnt';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "rajaongkir-proxy.php?action=cost");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($costData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$costResponse = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p>HTTP Code: $httpCode</p>";
echo "<pre>$costResponse</pre>";

// Test 6: Direct API test (for comparison)
echo "<h3>Test 6: Direct API Test</h3>";
$api_key = '2cfb97cdc7344be03623fe445fee4a09';
$base_url = 'https://api.rajaongkir.com/starter';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$base_url/cost");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'origin' => 23,
    'destination' => 151,
    'weight' => 1000,
    'courier' => 'jne'
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'key: ' . $api_key,
    'Content-Type: application/x-www-form-urlencoded'
]);
$directResponse = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p>Direct API HTTP Code: $httpCode</p>";
echo "<pre>$directResponse</pre>";

echo "<h3>Debug Info</h3>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>cURL Version: " . curl_version()['version'] . "</p>";
echo "<p>Server Time: " . date('Y-m-d H:i:s') . "</p>";
?> 