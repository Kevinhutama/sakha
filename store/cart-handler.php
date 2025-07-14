<?php
// Initialize session
require_once 'includes/session-config.php';

// Database configuration
require_once '../admin/includes/config.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: shop.php');
    exit();
}

// Get form data
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$color_id = isset($_POST['color_id']) ? intval($_POST['color_id']) : null;
$size_id = isset($_POST['size_id']) ? intval($_POST['size_id']) : null;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

// Custom name options
$pouch_custom_enabled = isset($_POST['pouch_custom_enabled']) ? true : false;
$pouch_custom_name = isset($_POST['pouch_custom_name']) ? trim($_POST['pouch_custom_name']) : null;
$sajadah_custom_enabled = isset($_POST['sajadah_custom_enabled']) ? true : false;
$sajadah_custom_name = isset($_POST['sajadah_custom_name']) ? trim($_POST['sajadah_custom_name']) : null;
$font_style = isset($_POST['font_style']) ? trim($_POST['font_style']) : null;

// Validation
if ($product_id <= 0) {
    $_SESSION['cart_error'] = 'Invalid product selected.';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}

if ($quantity <= 0) {
    $quantity = 1;
}

// Get user ID and session ID
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$session_id = session_id();

try {
    // Get product details
    $product_query = "SELECT * FROM products WHERE id = ? AND status = 'active'";
    $product_stmt = $db->prepare($product_query);
    $product_stmt->execute([$product_id]);
    $product = $product_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        $_SESSION['cart_error'] = 'Product not found.';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
    
    // Get color details if color_id is provided
    $color_data = null;
    if ($color_id) {
        $color_query = "SELECT * FROM product_colors WHERE id = ? AND product_id = ? AND status = 'active'";
        $color_stmt = $db->prepare($color_query);
        $color_stmt->execute([$color_id, $product_id]);
        $color_data = $color_stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Get size details if size_id is provided
    $size_data = null;
    if ($size_id) {
        $size_query = "SELECT * FROM product_sizes WHERE id = ? AND product_id = ? AND status = 'active'";
        $size_stmt = $db->prepare($size_query);
        $size_stmt->execute([$size_id, $product_id]);
        $size_data = $size_stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Calculate pricing
    $base_price = $product['price'];
    $discounted_price = $product['discounted_price'];
    $effective_price = $discounted_price && $discounted_price < $base_price ? $discounted_price : $base_price;
    
    // Custom name pricing
    $pouch_custom_price = $pouch_custom_enabled && !empty($pouch_custom_name) ? $product['pouch_custom_price'] : 0;
    $sajadah_custom_price = $sajadah_custom_enabled && !empty($sajadah_custom_name) ? $product['sajadah_custom_price'] : 0;
    
    // Calculate totals
    $item_subtotal = $effective_price * $quantity;
    $custom_additions_total = ($pouch_custom_price + $sajadah_custom_price) * $quantity;
    $item_total = $item_subtotal + $custom_additions_total;
    
    // Check if item already exists in cart with same specifications
    $check_query = "SELECT id, quantity FROM cart_items WHERE ";
    $check_params = [];
    
    if ($user_id) {
        $check_query .= "user_id = ? AND ";
        $check_params[] = $user_id;
    } else {
        $check_query .= "user_id IS NULL AND ";
    }
    
    $check_query .= "session_id = ? AND product_id = ? AND ";
    $check_params[] = $session_id;
    $check_params[] = $product_id;
    
    $check_query .= "COALESCE(color_id, 0) = ? AND COALESCE(size_id, 0) = ? AND ";
    $check_params[] = $color_id ?? 0;
    $check_params[] = $size_id ?? 0;
    
    $check_query .= "pouch_custom_enabled = ? AND COALESCE(pouch_custom_name, '') = ? AND ";
    $check_params[] = $pouch_custom_enabled;
    $check_params[] = $pouch_custom_name ?? '';
    
    $check_query .= "sajadah_custom_enabled = ? AND COALESCE(sajadah_custom_name, '') = ? AND ";
    $check_params[] = $sajadah_custom_enabled;
    $check_params[] = $sajadah_custom_name ?? '';
    
    $check_query .= "COALESCE(font_style, '') = ? AND is_active = TRUE";
    $check_params[] = $font_style ?? '';
    
    $check_stmt = $db->prepare($check_query);
    $check_stmt->execute($check_params);
    $existing_item = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing_item) {
        // Update existing item quantity
        $new_quantity = $existing_item['quantity'] + $quantity;
        $new_item_subtotal = $effective_price * $new_quantity;
        $new_custom_additions_total = ($pouch_custom_price + $sajadah_custom_price) * $new_quantity;
        $new_item_total = $new_item_subtotal + $new_custom_additions_total;
        
        $update_query = "UPDATE cart_items SET 
                        quantity = ?, 
                        item_subtotal = ?, 
                        custom_additions_total = ?, 
                        item_total = ?,
                        updated_at = CURRENT_TIMESTAMP
                        WHERE id = ?";
        
        $update_stmt = $db->prepare($update_query);
        $update_stmt->execute([$new_quantity, $new_item_subtotal, $new_custom_additions_total, $new_item_total, $existing_item['id']]);
        
        $_SESSION['cart_success'] = 'Cart updated! Quantity increased to ' . $new_quantity;
    } else {
        // Insert new item
        $insert_query = "INSERT INTO cart_items (
            user_id, session_id, product_id, product_slug, product_name,
            color_id, color_name, color_code, size_id, size_name, size_value,
            quantity, base_price, discounted_price,
            pouch_custom_enabled, pouch_custom_name, pouch_custom_price,
            sajadah_custom_enabled, sajadah_custom_name, sajadah_custom_price,
            font_style, item_subtotal, custom_additions_total, item_total
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $insert_params = [
            $user_id,
            $session_id,
            $product_id,
            $product['slug'],
            $product['name'],
            $color_id,
            $color_data['color_name'] ?? null,
            $color_data['color_code'] ?? null,
            $size_id,
            $size_data['size_name'] ?? null,
            $size_data['size_value'] ?? null,
            $quantity,
            $base_price,
            $discounted_price,
            $pouch_custom_enabled,
            $pouch_custom_name,
            $pouch_custom_price,
            $sajadah_custom_enabled,
            $sajadah_custom_name,
            $sajadah_custom_price,
            $font_style,
            $item_subtotal,
            $custom_additions_total,
            $item_total
        ];
        
        $insert_stmt = $db->prepare($insert_query);
        $insert_stmt->execute($insert_params);
        
        $_SESSION['cart_success'] = 'Product added to cart successfully!';
    }
    
    // Redirect back to the product page
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
    
} catch (PDOException $e) {
    error_log("Cart error: " . $e->getMessage());
    $_SESSION['cart_error'] = 'An error occurred while adding the product to cart. Please try again.';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}

// Function to get cart count for a user/session
function getCartCount($user_id = null, $session_id = null) {
    global $db;
    
    if (!$session_id) {
        $session_id = session_id();
    }
    
    try {
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
        
        return $result['total_items'] ?? 0;
    } catch (PDOException $e) {
        error_log("Cart count error: " . $e->getMessage());
        return 0;
    }
}
?> 