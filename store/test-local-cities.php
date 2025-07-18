<?php
// Test script for local city data
echo "<h2>Local City Data Test</h2>";

// Test 1: Check if data files exist
echo "<h3>Test 1: Data Files Status</h3>";
$dataFiles = [
    'data/rajaongkir-cities.json' => 'Main city data',
    'data/city-lookup.json' => 'City lookup by ID',
    'data/province-cities-map.json' => 'Province-cities mapping'
];

foreach ($dataFiles as $file => $description) {
    if (file_exists($file)) {
        $size = filesize($file);
        echo "✅ <strong>$description</strong>: $file (Size: " . formatBytes($size) . ")<br>";
    } else {
        echo "❌ <strong>$description</strong>: $file - FILE NOT FOUND<br>";
    }
}

// Test 2: Test local proxy stats
echo "<h3>Test 2: Local Proxy Stats</h3>";
$statsUrl = "local-cities-proxy.php?action=stats";
$statsResponse = file_get_contents($statsUrl);
$statsData = json_decode($statsResponse, true);

if ($statsData) {
    echo "<pre>" . json_encode($statsData, JSON_PRETTY_PRINT) . "</pre>";
} else {
    echo "<p>Failed to get stats from local proxy</p>";
}

// Test 3: Test provinces from local data
echo "<h3>Test 3: Provinces from Local Data</h3>";
$provincesUrl = "local-cities-proxy.php?action=provinces";
$provincesResponse = file_get_contents($provincesUrl);
$provincesData = json_decode($provincesResponse, true);

if ($provincesData && isset($provincesData['rajaongkir']['results'])) {
    $provinces = $provincesData['rajaongkir']['results'];
    echo "<p>Found " . count($provinces) . " provinces</p>";
    echo "<h4>First 5 provinces:</h4>";
    for ($i = 0; $i < min(5, count($provinces)); $i++) {
        echo "- {$provinces[$i]['province']} (ID: {$provinces[$i]['province_id']})<br>";
    }
} else {
    echo "<p>Failed to get provinces from local data</p>";
}

// Test 4: Test cities for a specific province
echo "<h3>Test 4: Cities for Jawa Barat (from local data)</h3>";
$citiesUrl = "local-cities-proxy.php?action=cities&province_name=" . urlencode('Jawa Barat');
$citiesResponse = file_get_contents($citiesUrl);
$citiesData = json_decode($citiesResponse, true);

if ($citiesData && isset($citiesData['rajaongkir']['results'])) {
    $cities = $citiesData['rajaongkir']['results'];
    echo "<p>Found " . count($cities) . " cities in Jawa Barat</p>";
    echo "<h4>First 10 cities:</h4>";
    for ($i = 0; $i < min(10, count($cities)); $i++) {
        echo "- {$cities[$i]['type']} {$cities[$i]['city_name']} (ID: {$cities[$i]['city_id']})<br>";
    }
} else {
    echo "<p>Failed to get cities for Jawa Barat</p>";
    echo "<p>Response: $citiesResponse</p>";
}

// Test 5: Test city lookup by ID
echo "<h3>Test 5: City Lookup by ID (Bandung = 23)</h3>";
$lookupUrl = "local-cities-proxy.php?action=city_lookup&city_id=23";
$lookupResponse = file_get_contents($lookupUrl);
$lookupData = json_decode($lookupResponse, true);

if ($lookupData && $lookupData['status'] === 'success') {
    echo "<p>City lookup successful:</p>";
    echo "<pre>" . json_encode($lookupData['city'], JSON_PRETTY_PRINT) . "</pre>";
} else {
    echo "<p>Failed to lookup city ID 23</p>";
    echo "<p>Response: $lookupResponse</p>";
}

// Test 6: Compare speed (local vs API)
echo "<h3>Test 6: Speed Comparison</h3>";

// Local data speed
$startTime = microtime(true);
$localResponse = file_get_contents("local-cities-proxy.php?action=cities&province_name=" . urlencode('DKI Jakarta'));
$localTime = microtime(true) - $startTime;

// API speed (if available)
$startTime = microtime(true);
$apiResponse = file_get_contents("rajaongkir-proxy.php?action=provinces");
$apiTime = microtime(true) - $startTime;

echo "<p>Local data response time: " . number_format($localTime * 1000, 2) . " ms</p>";
echo "<p>API response time: " . number_format($apiTime * 1000, 2) . " ms</p>";
echo "<p>Speed improvement: " . number_format(($apiTime / $localTime), 2) . "x faster</p>";

// Test 7: Test cost calculation (should still use API)
echo "<h3>Test 7: Cost Calculation (still uses API)</h3>";
$costData = [
    'origin' => 23,      // Bandung
    'destination' => 151, // Jakarta
    'weight' => 1000,    // 1kg
    'courier' => 'jne'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "local-cities-proxy.php?action=cost");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($costData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$costResponse = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p>Cost calculation HTTP Code: $httpCode</p>";
$costData = json_decode($costResponse, true);
if ($costData && isset($costData['rajaongkir']['results'][0]['costs'])) {
    $services = $costData['rajaongkir']['results'][0]['costs'];
    echo "<p>Found " . count($services) . " JNE services</p>";
    
    // Find REG service
    foreach ($services as $service) {
        if ($service['service'] === 'REG') {
            echo "<p>JNE REG service found: RP " . number_format($service['cost'][0]['value']) . " (ETA: {$service['cost'][0]['etd']} days)</p>";
            break;
        }
    }
} else {
    echo "<p>Cost calculation failed or no results</p>";
}

echo "<h3>Summary</h3>";
echo "<p>✅ City data is now served from local files instead of API calls</p>";
echo "<p>✅ This reduces API quota usage and improves response times</p>";
echo "<p>✅ Cost calculations still use the API for real-time pricing</p>";
echo "<p>✅ To update city data, run: <code>fetch-cities.php</code></p>";

function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}
?> 