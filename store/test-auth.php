<?php
/**
 * Test Authentication Setup
 * This script helps diagnose authentication issues
 */

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Authentication Test Results</h2>\n";

// Test 1: Configuration file loading
echo "<h3>1. Configuration Loading</h3>\n";
try {
    require_once __DIR__ . '/config/auth-config.php';
    echo "✅ Configuration loaded successfully<br>\n";
    echo "Google Client ID: " . (defined('GOOGLE_CLIENT_ID') ? GOOGLE_CLIENT_ID : 'NOT DEFINED') . "<br>\n";
    echo "Facebook App ID: " . (defined('FACEBOOK_APP_ID') ? FACEBOOK_APP_ID : 'NOT DEFINED') . "<br>\n";
    echo "Database Name: " . (defined('DB_NAME') ? DB_NAME : 'NOT DEFINED') . "<br>\n";
} catch (Exception $e) {
    echo "❌ Configuration loading failed: " . $e->getMessage() . "<br>\n";
}

// Test 2: Database connection
echo "<h3>2. Database Connection</h3>\n";
try {
    $pdo = getDbConnection();
    echo "✅ Database connection successful<br>\n";
    
    // Test if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Users table exists<br>\n";
        
        // Check table structure
        $stmt = $pdo->query("DESCRIBE users");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "Table columns: " . implode(', ', $columns) . "<br>\n";
    } else {
        echo "❌ Users table does not exist<br>\n";
        echo "Creating users table...<br>\n";
        
        // Create users table
        $sql = "
        CREATE TABLE `users` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `email` varchar(255) NOT NULL,
            `name` varchar(255) NOT NULL,
            `password` varchar(255) DEFAULT NULL,
            `google_id` varchar(255) DEFAULT NULL,
            `facebook_id` varchar(255) DEFAULT NULL,
            `avatar` varchar(500) DEFAULT NULL,
            `remember_token` varchar(255) DEFAULT NULL,
            `login_type` enum('regular','google','facebook','mixed') DEFAULT 'regular',
            `email_verified` tinyint(1) DEFAULT 0,
            `phone` varchar(20) DEFAULT NULL,
            `date_of_birth` date DEFAULT NULL,
            `gender` enum('male','female','other') DEFAULT NULL,
            `is_active` tinyint(1) DEFAULT 1,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `last_login` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `email` (`email`),
            KEY `google_id` (`google_id`),
            KEY `facebook_id` (`facebook_id`),
            KEY `remember_token` (`remember_token`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        
        $pdo->exec($sql);
        echo "✅ Users table created successfully<br>\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>\n";
    echo "Please check:<br>\n";
    echo "- XAMPP is running<br>\n";
    echo "- MySQL service is started<br>\n";
    echo "- Database 'sakha_store' exists (create it in phpMyAdmin if needed)<br>\n";
}

// Test 3: Authentication functions
echo "<h3>3. Authentication Functions</h3>\n";
try {
    if (function_exists('isLoginProviderEnabled')) {
        echo "✅ isLoginProviderEnabled function exists<br>\n";
        echo "Google login enabled: " . (isLoginProviderEnabled('google') ? 'Yes' : 'No') . "<br>\n";
        echo "Facebook login enabled: " . (isLoginProviderEnabled('facebook') ? 'Yes' : 'No') . "<br>\n";
        echo "Regular login enabled: " . (isLoginProviderEnabled('regular') ? 'Yes' : 'No') . "<br>\n";
    } else {
        echo "❌ isLoginProviderEnabled function not found<br>\n";
    }
    
    if (function_exists('verifyGoogleToken')) {
        echo "✅ verifyGoogleToken function exists<br>\n";
    } else {
        echo "❌ verifyGoogleToken function not found<br>\n";
    }
    
} catch (Exception $e) {
    echo "❌ Function test error: " . $e->getMessage() . "<br>\n";
}

echo "<h3>4. Next Steps</h3>\n";
echo "If all tests pass, try logging in again.<br>\n";
echo "If there are errors, fix them and run this test again.<br>\n";
echo "<a href='index.php'>Go back to main page</a><br>\n";
?> 