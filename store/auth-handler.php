<?php
// Initialize session
require_once 'includes/session-config.php';

// Include centralized authentication configuration
require_once __DIR__ . '/config/auth-config.php';

header('Content-Type: application/json');

try {
    $pdo = getDbConnection();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Handle different authentication actions
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'google_login':
        if (!isLoginProviderEnabled('google')) {
            echo json_encode(['success' => false, 'message' => 'Google login is disabled']);
            exit;
        }
        handleGoogleLogin();
        break;
    case 'facebook_login':
        if (!isLoginProviderEnabled('facebook')) {
            echo json_encode(['success' => false, 'message' => 'Facebook login is disabled']);
            exit;
        }
        handleFacebookLogin();
        break;
    case 'regular_login':
        if (!isLoginProviderEnabled('regular')) {
            echo json_encode(['success' => false, 'message' => 'Regular login is disabled']);
            exit;
        }
        handleRegularLogin();
        break;
    case 'logout':
        handleLogout();
        break;
    case 'get_user_status':
        getUserStatus();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function handleGoogleLogin() {
    global $pdo;
    
    $id_token = $_POST['id_token'] ?? '';
    
    if (empty($id_token)) {
        echo json_encode(['success' => false, 'message' => 'ID token is required']);
        return;
    }
    
    // Verify the ID token with Google
    $google_user_info = verifyGoogleToken($id_token, GOOGLE_CLIENT_ID);
    
    if (!$google_user_info) {
        echo json_encode(['success' => false, 'message' => 'Invalid Google token']);
        return;
    }
    
    // Check if user exists in database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR google_id = ?");
    $stmt->execute([$google_user_info['email'], $google_user_info['sub']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Update existing user with Google info
        $login_type = $user['login_type'] === 'regular' ? 'mixed' : 'google';
        $stmt = $pdo->prepare("UPDATE users SET google_id = ?, name = ?, avatar = ?, login_type = ?, email_verified = 1, last_login = NOW(), updated_at = NOW() WHERE id = ?");
        $stmt->execute([$google_user_info['sub'], $google_user_info['name'], $google_user_info['picture'], $login_type, $user['id']]);
        $user_id = $user['id'];
    } else {
        // Create new user
        $stmt = $pdo->prepare("INSERT INTO users (email, name, google_id, avatar, login_type, email_verified, created_at, last_login) VALUES (?, ?, ?, ?, 'google', 1, NOW(), NOW())");
        $stmt->execute([$google_user_info['email'], $google_user_info['name'], $google_user_info['sub'], $google_user_info['picture']]);
        $user_id = $pdo->lastInsertId();
    }
    
    // Set session
    setUserSession($user_id, $google_user_info['email'], $google_user_info['name'], $google_user_info['picture'], 'google');
    
    // Force session write to ensure it's saved
    session_write_close();
    session_start();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Login successful',
        'user' => [
            'id' => $user_id,
            'email' => $google_user_info['email'],
            'name' => $google_user_info['name'],
            'avatar' => $google_user_info['picture'],
            'login_type' => 'google'
        ]
    ]);
}

function handleFacebookLogin() {
    global $pdo;
    
    $access_token = $_POST['access_token'] ?? '';
    
    if (empty($access_token)) {
        echo json_encode(['success' => false, 'message' => 'Access token is required']);
        return;
    }
    
    // Verify the access token with Facebook
    $facebook_user_info = verifyFacebookToken($access_token);
    
    if (!$facebook_user_info) {
        echo json_encode(['success' => false, 'message' => 'Invalid Facebook token']);
        return;
    }
    
    // Check if user exists in database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR facebook_id = ?");
    $stmt->execute([$facebook_user_info['email'], $facebook_user_info['id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Update existing user with Facebook info
        $login_type = $user['login_type'] === 'regular' ? 'mixed' : 'facebook';
        $stmt = $pdo->prepare("UPDATE users SET facebook_id = ?, name = ?, avatar = ?, login_type = ?, email_verified = 1, last_login = NOW(), updated_at = NOW() WHERE id = ?");
        $stmt->execute([$facebook_user_info['id'], $facebook_user_info['name'], $facebook_user_info['picture']['data']['url'], $login_type, $user['id']]);
        $user_id = $user['id'];
    } else {
        // Create new user
        $stmt = $pdo->prepare("INSERT INTO users (email, name, facebook_id, avatar, login_type, email_verified, created_at, last_login) VALUES (?, ?, ?, ?, 'facebook', 1, NOW(), NOW())");
        $stmt->execute([$facebook_user_info['email'], $facebook_user_info['name'], $facebook_user_info['id'], $facebook_user_info['picture']['data']['url']]);
        $user_id = $pdo->lastInsertId();
    }
    
    // Set session
    setUserSession($user_id, $facebook_user_info['email'], $facebook_user_info['name'], $facebook_user_info['picture']['data']['url'], 'facebook');
    
    echo json_encode([
        'success' => true, 
        'message' => 'Login successful',
        'user' => [
            'id' => $user_id,
            'email' => $facebook_user_info['email'],
            'name' => $facebook_user_info['name'],
            'avatar' => $facebook_user_info['picture']['data']['url'],
            'login_type' => 'facebook'
        ]
    ]);
}

function handleRegularLogin() {
    global $pdo;
    
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = $_POST['remember'] ?? false;
    
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email and password are required']);
        return;
    }
    
    // Check user credentials
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        // Update last login and login type
        $login_type = (!empty($user['google_id']) || !empty($user['facebook_id'])) ? 'mixed' : 'regular';
        $stmt = $pdo->prepare("UPDATE users SET login_type = ?, last_login = NOW(), updated_at = NOW() WHERE id = ?");
        $stmt->execute([$login_type, $user['id']]);
        
        // Set session
        setUserSession($user['id'], $user['email'], $user['name'], $user['avatar'], 'regular');
        
        // Set remember me cookie if requested
        if ($remember && ENABLE_REMEMBER_ME) {
            $token = bin2hex(random_bytes(TOKEN_LENGTH));
            $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
            $stmt->execute([$token, $user['id']]);
            setcookie('remember_token', $token, time() + REMEMBER_TOKEN_LIFETIME, '/');
        }
        
        // Force session write to ensure it's saved
        session_write_close();
        session_start();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Login successful',
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['name'],
                'avatar' => $user['avatar'],
                'login_type' => $login_type
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    }
}

function handleLogout() {
    // Clear session
    session_destroy();
    
    // Clear remember me cookie
    setcookie('remember_token', '', time() - 3600, '/');
    
    echo json_encode(['success' => true, 'message' => 'Logout successful']);
}

function getUserStatus() {
    if (isset($_SESSION['user_id'])) {
        echo json_encode([
            'success' => true,
            'logged_in' => true,
            'user' => [
                'id' => $_SESSION['user_id'],
                'email' => $_SESSION['user_email'],
                'name' => $_SESSION['user_name'],
                'avatar' => $_SESSION['user_avatar'],
                'login_type' => $_SESSION['login_type']
            ]
        ]);
    } else {
        echo json_encode(['success' => true, 'logged_in' => false]);
    }
}

function setUserSession($user_id, $email, $name, $avatar, $login_type) {
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_avatar'] = $avatar;
    $_SESSION['login_type'] = $login_type;
    $_SESSION['login_time'] = time();
    
    // Set session lifetime
    if (defined('SESSION_LIFETIME') && SESSION_LIFETIME > 0) {
        ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
        session_set_cookie_params(SESSION_LIFETIME);
    }
    
    // Regenerate session ID for security
    session_regenerate_id(true);
}

function verifyGoogleToken($id_token, $client_id) {
    $url = GOOGLE_TOKEN_VERIFY_URL . "?id_token=" . $id_token;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200 || !$response) {
        return false;
    }
    
    $token_data = json_decode($response, true);
    
    // Verify the audience (client ID)
    if (!isset($token_data['aud']) || $token_data['aud'] !== $client_id) {
        return false;
    }
    
    // Verify the token is not expired
    if (!isset($token_data['exp']) || $token_data['exp'] < time()) {
        return false;
    }
    
    return $token_data;
}

function verifyFacebookToken($access_token) {
    // First, get app access token to verify user token
    $app_token_url = FACEBOOK_GRAPH_URL . "/oauth/access_token?client_id=" . FACEBOOK_APP_ID . "&client_secret=" . FACEBOOK_APP_SECRET . "&grant_type=client_credentials";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $app_token_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $app_response = curl_exec($ch);
    $app_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($app_http_code !== 200 || !$app_response) {
        return false;
    }
    
    $app_token_data = json_decode($app_response, true);
    if (!isset($app_token_data['access_token'])) {
        return false;
    }
    
    // Verify user access token
    $verify_url = FACEBOOK_GRAPH_URL . "/debug_token?input_token=" . $access_token . "&access_token=" . $app_token_data['access_token'];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $verify_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $verify_response = curl_exec($ch);
    $verify_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($verify_http_code !== 200 || !$verify_response) {
        return false;
    }
    
    $verify_data = json_decode($verify_response, true);
    if (!isset($verify_data['data']['is_valid']) || !$verify_data['data']['is_valid']) {
        return false;
    }
    
    // Get user information
    $user_url = FACEBOOK_GRAPH_URL . "/me?fields=id,name,email,picture&access_token=" . $access_token;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $user_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $user_response = curl_exec($ch);
    $user_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($user_http_code !== 200 || !$user_response) {
        return false;
    }
    
    $user_data = json_decode($user_response, true);
    
    // Check required fields
    if (!isset($user_data['id']) || !isset($user_data['email']) || !isset($user_data['name'])) {
        return false;
    }
    
    return $user_data;
}
?> 