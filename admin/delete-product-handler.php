<?php
require_once 'includes/config.php';
requireLogin();

// Set content type to JSON
header('Content-Type: application/json');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Check if product_id is provided
if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Product ID is required']);
    exit;
}

$product_id = intval($_POST['product_id']);

// Get database connection
$database = new Database();
$db = $database->getConnection();

try {
    // Start transaction
    $db->beginTransaction();
    
    // First, check if product exists and is not already deleted
    $checkQuery = "SELECT id, name, status FROM products WHERE id = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$product_id]);
    $product = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        throw new Exception('Product not found');
    }
    
    if ($product['status'] === 'deleted') {
        throw new Exception('Product is already deleted');
    }
    
    // Perform soft delete by updating status to 'deleted'
    $deleteQuery = "UPDATE products SET status = 'deleted', updated_at = NOW() WHERE id = ?";
    $deleteStmt = $db->prepare($deleteQuery);
    $deleteResult = $deleteStmt->execute([$product_id]);
    
    if (!$deleteResult) {
        throw new Exception('Failed to delete product');
    }
    
    // Check if any rows were affected
    if ($deleteStmt->rowCount() === 0) {
        throw new Exception('Product not found or already deleted');
    }
    
    // Optional: Update related records status if needed
    // For example, you might want to mark product colors as inactive
    $updateColorsQuery = "UPDATE product_colors SET status = 'inactive' WHERE product_id = ?";
    $updateColorsStmt = $db->prepare($updateColorsQuery);
    $updateColorsStmt->execute([$product_id]);
    
    // Commit transaction
    $db->commit();
    
    // Log the deletion for audit purposes
    error_log("Product deleted (soft): ID={$product_id}, Name='{$product['name']}', Admin=" . $_SESSION['user_id']);
    
    // Return success response
    echo json_encode([
        'success' => true, 
        'message' => 'Product "' . htmlspecialchars($product['name']) . '" has been deleted successfully'
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $db->rollback();
    
    // Log error for debugging
    error_log("Product deletion error: " . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false, 
        'message' => 'Error deleting product: ' . $e->getMessage()
    ]);
}
?> 