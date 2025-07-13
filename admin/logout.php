<?php
require_once 'includes/config.php';

// Enhanced logout with better cleanup
startSecureSession();

// Clear all session variables
$_SESSION = array();

// Remove remember me cookie if it exists
if (isset($_COOKIE['remember_admin'])) {
    setcookie('remember_admin', '', time() - 3600, '/', '', true, true);
}

// Destroy the session
session_destroy();

// Redirect to login page with logout message
header('Location: authentication-login.php?logout=success');
exit();
?> 