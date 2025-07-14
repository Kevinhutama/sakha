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
    header('Location: cart.php');
    exit();
}

// Get form data
$action = isset($_POST['action']) ? $_POST['action'] : '';
$cart_item_id = isset($_POST['cart_item_id']) ? intval($_POST['cart_item_id']) : 0;

// Get user ID and session ID
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$session_id = session_id();

try {
    
    switch ($action) {
        case 'update_quantity':
            updateCartQuantity($db, $cart_item_id, $user_id, $session_id);
            break;
            
        case 'remove_item':
            removeCartItem($db, $cart_item_id, $user_id, $session_id);
            break;
            
        case 'apply_coupon':
            applyCoupon($db, $user_id, $session_id);
            break;
            
        default:
            $_SESSION['cart_error'] = 'Invalid action.';
            break;
    }
    
} catch (Exception $e) {
    error_log("Cart update error: " . $e->getMessage());
    $_SESSION['cart_error'] = 'An error occurred while updating the cart. Please try again.';
}

// Redirect back to cart
header('Location: cart.php');
exit();

// Function to update cart item quantity
function updateCartQuantity($db, $cart_item_id, $user_id, $session_id) {
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    if ($quantity < 1) {
        $_SESSION['cart_error'] = 'Quantity must be at least 1.';
        return;
    }
    
    // Verify the cart item belongs to the current user/session
    $verify_query = "SELECT * FROM cart_items WHERE id = ? AND ";
    $verify_params = [$cart_item_id];
    
    if ($user_id) {
        $verify_query .= "user_id = ? AND ";
        $verify_params[] = $user_id;
    } else {
        $verify_query .= "user_id IS NULL AND ";
    }
    
    $verify_query .= "session_id = ? AND is_active = TRUE";
    $verify_params[] = $session_id;
    
    $verify_stmt = $db->prepare($verify_query);
    $verify_stmt->execute($verify_params);
    $cart_item = $verify_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cart_item) {
        $_SESSION['cart_error'] = 'Cart item not found.';
        return;
    }
    
    // Calculate new totals
    $base_price = $cart_item['discounted_price'] && $cart_item['discounted_price'] < $cart_item['base_price'] 
                  ? $cart_item['discounted_price'] 
                  : $cart_item['base_price'];
    
    $custom_additions_per_item = $cart_item['pouch_custom_price'] + $cart_item['sajadah_custom_price'];
    
    $new_item_subtotal = $base_price * $quantity;
    $new_custom_additions_total = $custom_additions_per_item * $quantity;
    $new_item_total = $new_item_subtotal + $new_custom_additions_total;
    
    // Update the cart item
    $update_query = "UPDATE cart_items SET 
                    quantity = ?, 
                    item_subtotal = ?, 
                    custom_additions_total = ?, 
                    item_total = ?,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?";
    
    $update_stmt = $db->prepare($update_query);
    $update_stmt->execute([$quantity, $new_item_subtotal, $new_custom_additions_total, $new_item_total, $cart_item_id]);
    
    $_SESSION['cart_success'] = 'Cart updated successfully!';
}

// Function to remove cart item
function removeCartItem($db, $cart_item_id, $user_id, $session_id) {
    // Verify the cart item belongs to the current user/session
    $verify_query = "SELECT product_name FROM cart_items WHERE id = ? AND ";
    $verify_params = [$cart_item_id];
    
    if ($user_id) {
        $verify_query .= "user_id = ? AND ";
        $verify_params[] = $user_id;
    } else {
        $verify_query .= "user_id IS NULL AND ";
    }
    
    $verify_query .= "session_id = ? AND is_active = TRUE";
    $verify_params[] = $session_id;
    
    $verify_stmt = $db->prepare($verify_query);
    $verify_stmt->execute($verify_params);
    $cart_item = $verify_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cart_item) {
        $_SESSION['cart_error'] = 'Cart item not found.';
        return;
    }
    
    // Soft delete the cart item
    $delete_query = "UPDATE cart_items SET is_active = FALSE, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
    $delete_stmt = $db->prepare($delete_query);
    $delete_stmt->execute([$cart_item_id]);
    
    $_SESSION['cart_success'] = 'Item "' . $cart_item['product_name'] . '" removed from cart.';
}

// Function to apply coupon (placeholder)
function applyCoupon($db, $user_id, $session_id) {
    $coupon_code = isset($_POST['coupon_code']) ? trim($_POST['coupon_code']) : '';
    
    if (empty($coupon_code)) {
        $_SESSION['cart_error'] = 'Please enter a coupon code.';
        return;
    }
    
    // For now, this is a placeholder. You can implement actual coupon logic here
    // Check if coupon exists and is valid
    
    // Example of how you might structure a coupons table query:
    /*
    $coupon_query = "SELECT * FROM coupons WHERE code = ? AND status = 'active' AND expires_at > NOW()";
    $coupon_stmt = $db->prepare($coupon_query);
    $coupon_stmt->execute([$coupon_code]);
    $coupon = $coupon_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($coupon) {
        // Apply coupon discount
        // Store coupon info in session or database
        $_SESSION['cart_success'] = 'Coupon applied successfully!';
    } else {
        $_SESSION['cart_error'] = 'Invalid or expired coupon code.';
    }
    */
    
    // For demonstration, we'll just show a message
    $_SESSION['cart_error'] = 'Coupon functionality will be implemented soon. Code entered: ' . htmlspecialchars($coupon_code);
}
?> 