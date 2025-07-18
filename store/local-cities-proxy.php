<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Get the action from the request
$action = $_GET['action'] ?? '';

if ($action === 'provinces') {
    // Get all provinces from local data
    $provincesData = getProvincesFromLocal();
    if ($provincesData) {
        echo json_encode([
            'rajaongkir' => [
                'status' => ['code' => 200, 'description' => 'OK'],
                'results' => $provincesData
            ]
        ]);
    } else {
        echo json_encode(['error' => 'Failed to load provinces from local data']);
    }
    
} elseif ($action === 'cities') {
    // Get cities by province from local data
    $province_name = $_GET['province_name'] ?? '';
    if (empty($province_name)) {
        echo json_encode(['error' => 'Province name is required']);
        exit;
    }
    
    $citiesData = getCitiesFromLocal($province_name);
    if ($citiesData) {
        echo json_encode([
            'rajaongkir' => [
                'status' => ['code' => 200, 'description' => 'OK'],
                'results' => $citiesData
            ]
        ]);
    } else {
        echo json_encode(['error' => 'Failed to load cities from local data or province not found']);
    }
    
} elseif ($action === 'cost') {
    // Still use Rajaongkir API for cost calculation (this needs to be dynamic)
    $origin = $_POST['origin'] ?? '';
    $destination = $_POST['destination'] ?? '';
    $weight = $_POST['weight'] ?? '';
    $courier = $_POST['courier'] ?? '';
    
    if (empty($origin) || empty($destination) || empty($weight) || empty($courier)) {
        echo json_encode(['error' => 'All parameters are required']);
        exit;
    }
    
    // Convert to proper data types as required by Rajaongkir
    $origin = intval($origin);
    $destination = intval($destination);
    $weight = intval($weight);
    $courier = strtolower(trim($courier));
    
    // Validate parameters
    if ($origin <= 0 || $destination <= 0) {
        echo json_encode(['error' => 'Invalid city ID - must be positive integer']);
        exit;
    }
    
    if ($weight <= 0) {
        echo json_encode(['error' => 'Invalid weight - must be positive integer (grams)']);
        exit;
    }
    
    if (!in_array($courier, ['jne', 'jnt', 'pos', 'tiki', 'rpx', 'esl', 'pcp', 'pandu', 'wahana', 'sicepat', 'jet', 'dse', 'first', 'ncs', 'star'])) {
        echo json_encode(['error' => 'Invalid courier code - supported: jne, jnt, pos, tiki, etc.']);
        exit;
    }
    
    // Use Rajaongkir API for cost calculation
    $api_key = '2cfb97cdc7344be03623fe445fee4a09';
    $base_url = 'https://api.rajaongkir.com/starter';
    
    $url = "$base_url/cost";
    $data = [
        'origin' => $origin,
        'destination' => $destination,
        'weight' => $weight,
        'courier' => $courier
    ];
    
    // Debug logging
    error_log("Rajaongkir cost request: " . json_encode($data));
    
    $response = makeApiRequest($url, 'POST', $api_key, $data);
    echo $response;
    
} elseif ($action === 'city_lookup') {
    // Get city info by ID
    $city_id = $_GET['city_id'] ?? '';
    if (empty($city_id)) {
        echo json_encode(['error' => 'City ID is required']);
        exit;
    }
    
    $cityInfo = getCityById($city_id);
    if ($cityInfo) {
        echo json_encode([
            'status' => 'success',
            'city' => $cityInfo
        ]);
    } else {
        echo json_encode(['error' => 'City not found']);
    }
    
} elseif ($action === 'stats') {
    // Get statistics about local data
    $stats = getLocalDataStats();
    echo json_encode($stats);
    
} else {
    echo json_encode(['error' => 'Invalid action']);
}

// Helper functions
function getProvincesFromLocal() {
    $dataFile = 'data/rajaongkir-cities.json';
    if (!file_exists($dataFile)) {
        return false;
    }
    
    $data = json_decode(file_get_contents($dataFile), true);
    if (!$data || !isset($data['provinces'])) {
        return false;
    }
    
    // Convert to provinces format
    $provinces = [];
    foreach ($data['provinces'] as $province) {
        $provinces[] = [
            'province_id' => $province['province_id'],
            'province' => $province['province_name']
        ];
    }
    
    return $provinces;
}

function getCitiesFromLocal($province_name) {
    $dataFile = 'data/province-cities-map.json';
    if (!file_exists($dataFile)) {
        return false;
    }
    
    $data = json_decode(file_get_contents($dataFile), true);
    if (!$data || !isset($data['provinces'][$province_name])) {
        return false;
    }
    
    return $data['provinces'][$province_name];
}

function getCityById($city_id) {
    $dataFile = 'data/city-lookup.json';
    if (!file_exists($dataFile)) {
        return false;
    }
    
    $data = json_decode(file_get_contents($dataFile), true);
    if (!$data || !isset($data['cities'][$city_id])) {
        return false;
    }
    
    return $data['cities'][$city_id];
}

function getLocalDataStats() {
    $stats = [
        'status' => 'success',
        'files' => []
    ];
    
    $files = [
        'rajaongkir-cities.json' => 'Main city data',
        'city-lookup.json' => 'City lookup by ID',
        'province-cities-map.json' => 'Province-cities mapping'
    ];
    
    foreach ($files as $filename => $description) {
        $filepath = 'data/' . $filename;
        if (file_exists($filepath)) {
            $fileData = json_decode(file_get_contents($filepath), true);
            $stats['files'][$filename] = [
                'description' => $description,
                'exists' => true,
                'size' => filesize($filepath),
                'updated_at' => $fileData['updated_at'] ?? 'Unknown',
                'total_cities' => $fileData['total_cities'] ?? 0
            ];
        } else {
            $stats['files'][$filename] = [
                'description' => $description,
                'exists' => false,
                'error' => 'File not found'
            ];
        }
    }
    
    return $stats;
}

// Reuse the makeApiRequest function from rajaongkir-proxy.php
function makeApiRequest($url, $method, $api_key, $data = null) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // Set headers based on method
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'key: ' . $api_key,
            'Content-Type: application/x-www-form-urlencoded'
        ]);
    } else {
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'key: ' . $api_key
        ]);
    }
    
    if ($method === 'POST' && $data) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        
        // Debug logging for POST requests
        error_log("Rajaongkir POST URL: " . $url);
        error_log("Rajaongkir POST data: " . http_build_query($data));
    }
    
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    
    if (curl_errno($ch)) {
        curl_close($ch);
        $errorMsg = 'Connection failed: ' . $curlError;
        error_log("Rajaongkir API Error: " . $errorMsg);
        return json_encode(['error' => $errorMsg]);
    }
    
    curl_close($ch);
    
    if ($httpCode !== 200) {
        $errorMsg = 'API request failed with HTTP ' . $httpCode;
        
        // Log the response body for debugging
        error_log("Rajaongkir API Response Body: " . $response);
        
        // Add more context based on HTTP codes
        switch ($httpCode) {
            case 400:
                $errorMsg .= ' (Bad Request - Invalid parameters)';
                // Try to parse response for more details
                $responseData = json_decode($response, true);
                if ($responseData && isset($responseData['rajaongkir']['status']['description'])) {
                    $errorMsg .= ' - ' . $responseData['rajaongkir']['status']['description'];
                }
                break;
            case 401:
                $errorMsg .= ' (Unauthorized - Invalid API key)';
                break;
            case 403:
                $errorMsg .= ' (Forbidden - API key quota exceeded)';
                break;
            case 404:
                $errorMsg .= ' (Not Found - Invalid endpoint)';
                break;
            case 500:
                $errorMsg .= ' (Server Error - Rajaongkir service unavailable)';
                break;
            case 503:
                $errorMsg .= ' (Service Unavailable - Rajaongkir maintenance)';
                break;
            default:
                $errorMsg .= ' (Unknown error)';
        }
        
        error_log("Rajaongkir API Error: " . $errorMsg);
        return json_encode(['error' => $errorMsg]);
    }
    
    // Validate JSON response
    $jsonResponse = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $errorMsg = 'Invalid JSON response from Rajaongkir API';
        error_log("Rajaongkir API Error: " . $errorMsg);
        return json_encode(['error' => $errorMsg]);
    }
    
    return $response;
}
?> 