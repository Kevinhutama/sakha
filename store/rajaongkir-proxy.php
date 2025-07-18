<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Rajaongkir API configuration
$api_key = '2cfb97cdc7344be03623fe445fee4a09';
$base_url = 'https://api.rajaongkir.com/starter';

// Get the action from the request
$action = $_GET['action'] ?? '';

if ($action === 'provinces') {
    // Get all provinces
    $url = "$base_url/province";
    $response = makeApiRequest($url, 'GET', $api_key);
    echo $response;
    
} elseif ($action === 'cities') {
    // Get cities by province
    $province_id = $_GET['province_id'] ?? '';
    if (empty($province_id)) {
        echo json_encode(['error' => 'Province ID is required']);
        exit;
    }
    
    $url = "$base_url/city?province=$province_id";
    $response = makeApiRequest($url, 'GET', $api_key);
    echo $response;
    
} elseif ($action === 'cost') {
    // Calculate shipping cost
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
    
} elseif ($action === 'test') {
    // Test endpoint for debugging
    echo json_encode([
        'status' => 'success',
        'message' => 'Rajaongkir proxy is working',
        'api_key' => substr($api_key, 0, 8) . '...',
        'base_url' => $base_url,
        'post_data' => $_POST
    ]);
    
} else {
    echo json_encode(['error' => 'Invalid action']);
}

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