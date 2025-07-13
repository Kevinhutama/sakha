<?php
require_once 'includes/config.php';

// Start session and check if user is logged in
startSecureSession();

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$response = ['success' => false, 'message' => 'Invalid request'];

try {
    // Create database connection
    $database = new Database();
    $db = $database->getConnection();
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $status = $_POST['status'] ?? 'active';
            
            // Validate required fields
            if (empty($name)) {
                $response = ['success' => false, 'message' => 'Category name is required'];
                break;
            }
            
            // Auto-generate slug if not provided
            if (empty($slug)) {
                $slug = generateSlug($name);
            } else {
                $slug = generateSlug($slug);
            }
            
            // Check if category name already exists
            $checkQuery = "SELECT id FROM categories WHERE name = :name";
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->bindParam(':name', $name);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                $response = ['success' => false, 'message' => 'Category name already exists'];
                break;
            }
            
            // Check if slug already exists
            $checkSlugQuery = "SELECT id FROM categories WHERE slug = :slug";
            $checkSlugStmt = $db->prepare($checkSlugQuery);
            $checkSlugStmt->bindParam(':slug', $slug);
            $checkSlugStmt->execute();
            
            if ($checkSlugStmt->rowCount() > 0) {
                $slug = $slug . '-' . time(); // Make slug unique by adding timestamp
            }
            
            // Insert new category
            $query = "INSERT INTO categories (name, description, slug, status) VALUES (:name, :description, :slug, :status)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':status', $status);
            
            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Category added successfully'];
            } else {
                $response = ['success' => false, 'message' => 'Failed to add category'];
            }
            break;
            
        case 'edit':
            $id = $_POST['id'] ?? 0;
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $status = $_POST['status'] ?? 'active';
            
            // Validate required fields
            if (empty($id) || empty($name)) {
                $response = ['success' => false, 'message' => 'Category ID and name are required'];
                break;
            }
            
            // Auto-generate slug if not provided
            if (empty($slug)) {
                $slug = generateSlug($name);
            } else {
                $slug = generateSlug($slug);
            }
            
            // Check if category name already exists (excluding current category)
            $checkQuery = "SELECT id FROM categories WHERE name = :name AND id != :id";
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->bindParam(':name', $name);
            $checkStmt->bindParam(':id', $id);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                $response = ['success' => false, 'message' => 'Category name already exists'];
                break;
            }
            
            // Check if slug already exists (excluding current category)
            $checkSlugQuery = "SELECT id FROM categories WHERE slug = :slug AND id != :id";
            $checkSlugStmt = $db->prepare($checkSlugQuery);
            $checkSlugStmt->bindParam(':slug', $slug);
            $checkSlugStmt->bindParam(':id', $id);
            $checkSlugStmt->execute();
            
            if ($checkSlugStmt->rowCount() > 0) {
                $slug = $slug . '-' . time(); // Make slug unique by adding timestamp
            }
            
            // Update category
            $query = "UPDATE categories SET name = :name, description = :description, slug = :slug, status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Category updated successfully'];
            } else {
                $response = ['success' => false, 'message' => 'Failed to update category'];
            }
            break;
            
        case 'delete':
            $id = $_POST['id'] ?? 0;
            
            if (empty($id)) {
                $response = ['success' => false, 'message' => 'Category ID is required'];
                break;
            }
            
            // Check if category is being used by any products
            $checkUsageQuery = "SELECT COUNT(*) as count FROM product_categories WHERE category_id = :id";
            $checkUsageStmt = $db->prepare($checkUsageQuery);
            $checkUsageStmt->bindParam(':id', $id);
            $checkUsageStmt->execute();
            $usage = $checkUsageStmt->fetch();
            
            if ($usage['count'] > 0) {
                $response = ['success' => false, 'message' => 'Cannot delete category. It is being used by ' . $usage['count'] . ' product(s). Please remove the category from all products first.'];
                break;
            }
            
            // Delete category
            $query = "DELETE FROM categories WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                if ($stmt->rowCount() > 0) {
                    $response = ['success' => true, 'message' => 'Category deleted successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Category not found'];
                }
            } else {
                $response = ['success' => false, 'message' => 'Failed to delete category'];
            }
            break;
            
        default:
            $response = ['success' => false, 'message' => 'Invalid action'];
            break;
    }
    
} catch (Exception $e) {
    $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    error_log('Category handler error: ' . $e->getMessage());
}

echo json_encode($response);

/**
 * Generate a URL-friendly slug from a string
 */
function generateSlug($string) {
    // Convert to lowercase
    $slug = strtolower($string);
    
    // Remove special characters except alphanumeric, spaces, and hyphens
    $slug = preg_replace('/[^a-z0-9\s\-]/', '', $slug);
    
    // Replace spaces and multiple hyphens with single hyphen
    $slug = preg_replace('/[\s\-]+/', '-', $slug);
    
    // Trim hyphens from beginning and end
    $slug = trim($slug, '-');
    
    return $slug;
}
?> 