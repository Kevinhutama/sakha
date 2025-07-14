<?php
/**
 * Centralized Session Configuration
 * Include this file at the beginning of any PHP file that needs session management
 */

// Configure session settings
ini_set('session.use_cookies', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Set to 1 for HTTPS in production
ini_set('session.gc_maxlifetime', 86400); // 24 hours

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?> 