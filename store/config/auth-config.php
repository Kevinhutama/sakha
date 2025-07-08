<?php
/**
 * Authentication Configuration
 * Centralized configuration for all authentication providers and settings
 */

// Load sensitive credentials from separate file
if (file_exists(__DIR__ . '/credentials.php')) {
    require_once __DIR__ . '/credentials.php';
} else {
    // Default values if credentials file doesn't exist
    define('GOOGLE_CLIENT_ID', 'your_google_client_id_here');
    define('GOOGLE_CLIENT_SECRET', 'your_google_client_secret_here');
    define('FACEBOOK_APP_ID', 'your_facebook_app_id_here');
    define('FACEBOOK_APP_SECRET', 'your_facebook_app_secret_here');
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'admin_portal');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');

// Application URLs (update these for your domain)
define('APP_URL', 'http://localhost/sakha/store');
define('APP_URL_SECURE', 'https://yourdomain.com/store'); // For production

// Session Configuration
define('SESSION_LIFETIME', 86400 * 30); // 30 days in seconds
define('REMEMBER_TOKEN_LIFETIME', 86400 * 30); // 30 days for remember me

// Security Settings
define('BCRYPT_COST', 12); // Password hashing cost (higher = more secure but slower)
define('TOKEN_LENGTH', 32); // Length for remember tokens

// Login Attempts & Security
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes in seconds

// Email Settings (for future email verification)
define('ENABLE_EMAIL_VERIFICATION', false);
define('FROM_EMAIL', 'noreply@sakhastore.com');
define('FROM_NAME', 'Sakha Store');

// Feature Flags
define('ENABLE_GOOGLE_LOGIN', true);
define('ENABLE_FACEBOOK_LOGIN', true);
define('ENABLE_REGULAR_LOGIN', true);
define('ENABLE_REMEMBER_ME', true);

// API Endpoints
define('GOOGLE_TOKEN_VERIFY_URL', 'https://oauth2.googleapis.com/tokeninfo');
define('FACEBOOK_GRAPH_URL', 'https://graph.facebook.com/v18.0');

// Default User Settings
define('DEFAULT_USER_AVATAR', '/store/images/default-avatar.png');
define('DEFAULT_USER_STATUS', 1); // Active by default

/**
 * Get database connection
 * @return PDO
 */
function getDbConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }
    
    return $pdo;
}

/**
 * Generate JavaScript configuration for frontend
 * @return string JSON configuration
 */
function getJsAuthConfig() {
    return json_encode([
        'googleClientId' => GOOGLE_CLIENT_ID,
        'facebookAppId' => FACEBOOK_APP_ID,
        'appUrl' => APP_URL,
        'enableGoogleLogin' => ENABLE_GOOGLE_LOGIN,
        'enableFacebookLogin' => ENABLE_FACEBOOK_LOGIN,
        'enableRegularLogin' => ENABLE_REGULAR_LOGIN,
        'enableRememberMe' => ENABLE_REMEMBER_ME
    ]);
}

/**
 * Check if a login provider is enabled
 * @param string $provider (google|facebook|regular)
 * @return bool
 */
function isLoginProviderEnabled($provider) {
    switch ($provider) {
        case 'google':
            return ENABLE_GOOGLE_LOGIN;
        case 'facebook':
            return ENABLE_FACEBOOK_LOGIN;
        case 'regular':
            return ENABLE_REGULAR_LOGIN;
        default:
            return false;
    }
}

/**
 * Get redirect URL after login
 * @param string $page Current page
 * @return string Redirect URL
 */
function getRedirectUrl($page = '') {
    $baseUrl = isset($_SERVER['HTTPS']) ? APP_URL_SECURE : APP_URL;
    return $baseUrl . ($page ? '/' . $page : '/index.php');
}
?> 