<?php
// Script to fetch all cities from Rajaongkir API and store them locally
// This reduces API calls and improves performance

set_time_limit(300); // 5 minutes timeout
echo "<h2>Fetching Cities from Rajaongkir API</h2>";

// Debug information
echo "<h3>System Information</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Current working directory: " . getcwd() . "<br>";
echo "Script user: " . get_current_user() . "<br>";
echo "Available memory: " . ini_get('memory_limit') . "<br>";
echo "Available disk space: " . formatBytes(disk_free_space('.')) . "<br>";
echo "<br>";

// Load credentials
require_once 'config/credentials.php';

// API configuration
$api_key = RAJAONGKIR_API_KEY;
$base_url = RAJAONGKIR_BASE_URL;

// Step 1: Get all provinces
echo "<h3>Step 1: Fetching Provinces...</h3>";
$provinces = fetchProvinces($api_key, $base_url);
if (!$provinces) {
    die("Failed to fetch provinces");
}
echo "Found " . count($provinces) . " provinces<br>";

// Step 2: Get all cities for each province
echo "<h3>Step 2: Fetching Cities for Each Province...</h3>";
$allCities = [];
$totalCities = 0;

foreach ($provinces as $province) {
    echo "Fetching cities for {$province['province']}... ";
    $cities = fetchCities($api_key, $base_url, $province['province_id']);
    
    if ($cities) {
        $provinceData = [
            'province_id' => $province['province_id'],
            'province_name' => $province['province'],
            'cities' => $cities
        ];
        
        $allCities[] = $provinceData;
        $totalCities += count($cities);
        echo "Found " . count($cities) . " cities<br>";
    } else {
        echo "Failed to fetch cities<br>";
    }
    
    // Small delay to avoid hitting rate limits
    usleep(100000); // 0.1 second delay
}

echo "<h3>Step 3: Saving Data...</h3>";
echo "Total cities found: $totalCities<br>";

// Create data structure
$cityData = [
    'updated_at' => date('Y-m-d H:i:s'),
    'total_provinces' => count($provinces),
    'total_cities' => $totalCities,
    'provinces' => $allCities
];

// Save to JSON file
$filename = 'data/rajaongkir-cities.json';
$dataDir = dirname($filename);

// Check and create directory
if (!is_dir($dataDir)) {
    if (!mkdir($dataDir, 0777, true)) {
        echo "❌ Failed to create directory: $dataDir<br>";
        echo "Directory permissions: " . substr(sprintf('%o', fileperms(dirname($dataDir))), -4) . "<br>";
        die();
    }
    echo "✅ Created directory: $dataDir<br>";
}

// Check directory permissions
if (!is_writable($dataDir)) {
    echo "❌ Directory is not writable: $dataDir<br>";
    echo "Directory permissions: " . substr(sprintf('%o', fileperms($dataDir)), -4) . "<br>";
    echo "Current user: " . get_current_user() . "<br>";
    die();
}

echo "✅ Directory is writable: $dataDir<br>";

// Prepare JSON data
$jsonData = json_encode($cityData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "❌ JSON encoding error: " . json_last_error_msg() . "<br>";
    die();
}

echo "✅ JSON data prepared (" . formatBytes(strlen($jsonData)) . ")<br>";

// Write to file
$result = file_put_contents($filename, $jsonData);
if ($result !== false) {
    echo "✅ Data saved to $filename<br>";
    echo "File size: " . formatBytes(filesize($filename)) . "<br>";
    echo "Bytes written: " . formatBytes($result) . "<br>";
} else {
    echo "❌ Failed to save data to $filename<br>";
    echo "Error: " . error_get_last()['message'] . "<br>";
    echo "Available disk space: " . formatBytes(disk_free_space($dataDir)) . "<br>";
}

// Create a simple city lookup file (flat structure for quick access)
echo "<h3>Step 4: Creating City Lookup File...</h3>";
$cityLookup = [];
foreach ($allCities as $provinceData) {
    foreach ($provinceData['cities'] as $city) {
        $cityLookup[$city['city_id']] = [
            'city_id' => $city['city_id'],
            'city_name' => $city['city_name'],
            'city_type' => $city['type'],
            'province_id' => $provinceData['province_id'],
            'province_name' => $provinceData['province_name'],
            'display_name' => $city['type'] . ' ' . $city['city_name']
        ];
    }
}

$lookupFilename = 'data/city-lookup.json';
$lookupData = [
    'updated_at' => date('Y-m-d H:i:s'),
    'total_cities' => count($cityLookup),
    'cities' => $cityLookup
];

$lookupJson = json_encode($lookupData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "❌ JSON encoding error for lookup: " . json_last_error_msg() . "<br>";
} else {
    $result = file_put_contents($lookupFilename, $lookupJson);
    if ($result !== false) {
        echo "✅ City lookup saved to $lookupFilename<br>";
        echo "File size: " . formatBytes(filesize($lookupFilename)) . "<br>";
    } else {
        echo "❌ Failed to save city lookup to $lookupFilename<br>";
        echo "Error: " . error_get_last()['message'] . "<br>";
    }
}

// Create province-cities mapping for frontend
echo "<h3>Step 5: Creating Province-Cities Map...</h3>";
$provinceCitiesMap = [];
foreach ($allCities as $provinceData) {
    $provinceCitiesMap[$provinceData['province_name']] = $provinceData['cities'];
}

$mapFilename = 'data/province-cities-map.json';
$mapData = [
    'updated_at' => date('Y-m-d H:i:s'),
    'provinces' => $provinceCitiesMap
];

$mapJson = json_encode($mapData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "❌ JSON encoding error for map: " . json_last_error_msg() . "<br>";
} else {
    $result = file_put_contents($mapFilename, $mapJson);
    if ($result !== false) {
        echo "✅ Province-cities map saved to $mapFilename<br>";
        echo "File size: " . formatBytes(filesize($mapFilename)) . "<br>";
    } else {
        echo "❌ Failed to save province-cities map to $mapFilename<br>";
        echo "Error: " . error_get_last()['message'] . "<br>";
    }
}

echo "<h3>✅ All Done!</h3>";
echo "You can now use the local city data instead of making API calls.<br>";
echo "Update your checkout.php to use these local files.<br>";

// Display sample data
echo "<h3>Sample Data Preview:</h3>";
echo "<h4>First 3 Cities:</h4>";
$sampleCities = array_slice($cityLookup, 0, 3, true);
foreach ($sampleCities as $cityId => $cityData) {
    echo "ID: $cityId - {$cityData['display_name']} ({$cityData['province_name']})<br>";
}

// Functions
function fetchProvinces($api_key, $base_url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$base_url/province");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['key: ' . $api_key]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if ($data && isset($data['rajaongkir']['results'])) {
            return $data['rajaongkir']['results'];
        }
    }
    
    return false;
}

function fetchCities($api_key, $base_url, $province_id) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$base_url/city?province=$province_id");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['key: ' . $api_key]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if ($data && isset($data['rajaongkir']['results'])) {
            return $data['rajaongkir']['results'];
        }
    }
    
    return false;
}

function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}
?> 