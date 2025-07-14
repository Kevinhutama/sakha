<?php
// Cart helper functions
// This file contains reusable cart-related functions

// Function to get database connection
function getCartDbConnection() {
    static $db = null;
    static $tablesChecked = false;
    
    if ($db === null) {
        try {
            require_once __DIR__ . '/../../admin/includes/config.php';
            $database = new Database();
            $db = $database->getConnection();
        } catch (Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            return null;
        }
    }
    
    // Check if cart tables exist (only once)
    if ($db && !$tablesChecked) {
        try {
            $stmt = $db->query("SHOW TABLES LIKE 'cart_items'");
            if ($stmt->rowCount() == 0) {
                error_log("Cart table 'cart_items' does not exist");
                return null;
            }
            $tablesChecked = true;
        } catch (Exception $e) {
            error_log("Error checking cart tables: " . $e->getMessage());
            return null;
        }
    }
    
    return $db;
}

// Function to get cart count for a user/session
function getCartCount($user_id = null, $session_id = null) {
    $db = getCartDbConnection();
    if (!$db) return 0;
    
    try {
        if (!$session_id) {
            $session_id = session_id();
        }
        
        $query = "SELECT SUM(quantity) as total_items FROM cart_items WHERE ";
        $params = [];
        
        if ($user_id) {
            $query .= "user_id = ? AND ";
            $params[] = $user_id;
        } else {
            $query .= "user_id IS NULL AND ";
        }
        
        $query .= "session_id = ? AND is_active = TRUE AND expires_at > NOW()";
        $params[] = $session_id;
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return intval($result['total_items'] ?? 0);
    } catch (Exception $e) {
        error_log("Cart count error: " . $e->getMessage());
        return 0;
    }
}

// Function to get cart items for a user/session
function getCartItems($user_id = null, $session_id = null) {
    $db = getCartDbConnection();
    if (!$db) return [];
    
    try {
        if (!$session_id) {
            $session_id = session_id();
        }
        
        $query = "SELECT * FROM cart_details WHERE ";
        $params = [];
        
        if ($user_id) {
            $query .= "user_id = ? AND ";
            $params[] = $user_id;
        } else {
            $query .= "user_id IS NULL AND ";
        }
        
        $query .= "session_id = ? ORDER BY created_at DESC";
        $params[] = $session_id;
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $items;
    } catch (Exception $e) {
        error_log("Cart items error: " . $e->getMessage());
        return [];
    }
}

// Function to get cart summary for a user/session
function getCartSummary($user_id = null, $session_id = null) {
    $db = getCartDbConnection();
    if (!$db) {
        return [
            'total_items' => 0,
            'total_quantity' => 0,
            'subtotal' => 0,
            'custom_additions' => 0,
            'total_amount' => 0
        ];
    }
    
    try {
        if (!$session_id) {
            $session_id = session_id();
        }
        
        $query = "SELECT * FROM cart_summary WHERE ";
        $params = [];
        
        if ($user_id) {
            $query .= "user_id = ? AND ";
            $params[] = $user_id;
        } else {
            $query .= "user_id IS NULL AND ";
        }
        
        $query .= "session_id = ?";
        $params[] = $session_id;
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $summary = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $summary ?: [
            'total_items' => 0,
            'total_quantity' => 0,
            'subtotal' => 0,
            'custom_additions' => 0,
            'total_amount' => 0
        ];
    } catch (Exception $e) {
        error_log("Cart summary error: " . $e->getMessage());
        return [
            'total_items' => 0,
            'total_quantity' => 0,
            'subtotal' => 0,
            'custom_additions' => 0,
            'total_amount' => 0
        ];
    }
}

// Function to format price
function formatPrice($price) {
    return 'RP ' . number_format($price, 0, ',', '.');
}

?> 