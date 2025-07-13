<?php
require_once 'includes/config.php';

// Require authentication
requireLogin();

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            listProducts();
            break;
        case 'get':
            getProduct();
            break;
        case 'save':
            saveProduct();
            break;
        case 'delete':
            deleteProduct();
            break;
        case 'categories':
            getCategories();
            break;
        case 'list_for_dropdown':
            listProductsForDropdown();
            break;
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function listProducts() {
    $database = new Database();
    $pdo = $database->getConnection();
    
    $sql = "SELECT p.*, 
                   GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ') as categories,
                   GROUP_CONCAT(DISTINCT CONCAT(pc.color_name, '|', pc.color_code) ORDER BY pc.sort_order SEPARATOR ', ') as colors,
                   pi.image_path as primary_image
            FROM products p
            LEFT JOIN product_categories pc_rel ON p.id = pc_rel.product_id
            LEFT JOIN categories c ON pc_rel.category_id = c.id
            LEFT JOIN product_colors pc ON p.id = pc.product_id AND pc.status = 'active'
            LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
            GROUP BY p.id
            ORDER BY p.id DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($products);
}

function getProduct() {
    $database = new Database();
    $pdo = $database->getConnection();
    
    $id = $_GET['id'] ?? 0;
    
    // Get product details
    $sql = "SELECT * FROM products WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        throw new Exception('Product not found');
    }
    
    // Get categories
    $sql = "SELECT category_id FROM product_categories WHERE product_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $product['category_ids'] = implode(',', $categories);
    
    // Get colors
    $sql = "SELECT * FROM product_colors WHERE product_id = ? ORDER BY sort_order";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $product['colors_data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get related products
    $sql = "SELECT rp.related_product_id as id, p.name 
            FROM related_products rp 
            JOIN products p ON rp.related_product_id = p.id 
            WHERE rp.product_id = ? 
            ORDER BY rp.sort_order";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $product['related_products'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($product);
}

function saveProduct() {
    $database = new Database();
    $pdo = $database->getConnection();
    
    $pdo->beginTransaction();
    
    try {
        $id = $_POST['id'] ?? null;
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $short_description = $_POST['short_description'] ?? '';
        $sku = $_POST['sku'] ?? '';
        $price = $_POST['price'] ?? 0;
        $discounted_price = $_POST['discounted_price'] ?? null;
        $stock_quantity = $_POST['stock_quantity'] ?? 0;
        $custom_name_enabled = isset($_POST['custom_name_enabled']) ? 1 : 0;
        $pouch_custom_price = $_POST['pouch_custom_price'] ?? 5000.00;
        $sajadah_custom_price = $_POST['sajadah_custom_price'] ?? 5000.00;
        $status = $_POST['status'] ?? 'active';
        $featured = isset($_POST['featured']) ? 1 : 0;
        $meta_title = $_POST['meta_title'] ?? '';
        $meta_description = $_POST['meta_description'] ?? '';
        
        // Generate slug from name
        $slug = generateSlug($name);
        
        if ($id) {
            // Update existing product
            $sql = "UPDATE products SET 
                        name = ?, slug = ?, description = ?, short_description = ?, 
                        sku = ?, price = ?, discounted_price = ?, stock_quantity = ?, 
                        custom_name_enabled = ?, pouch_custom_price = ?, sajadah_custom_price = ?, 
                        status = ?, featured = ?, meta_title = ?, meta_description = ?
                    WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $name, $slug, $description, $short_description, $sku, $price, 
                $discounted_price, $stock_quantity, $custom_name_enabled, 
                $pouch_custom_price, $sajadah_custom_price, $status, $featured, 
                $meta_title, $meta_description, $id
            ]);
        } else {
            // Insert new product
            $sql = "INSERT INTO products (name, slug, description, short_description, sku, price, 
                        discounted_price, stock_quantity, custom_name_enabled, pouch_custom_price, 
                        sajadah_custom_price, status, featured, meta_title, meta_description) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $name, $slug, $description, $short_description, $sku, $price, 
                $discounted_price, $stock_quantity, $custom_name_enabled, 
                $pouch_custom_price, $sajadah_custom_price, $status, $featured, 
                $meta_title, $meta_description
            ]);
            $id = $pdo->lastInsertId();
        }
        
        // Handle categories
        if (isset($_POST['categories'])) {
            // Delete existing categories
            $sql = "DELETE FROM product_categories WHERE product_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            
            // Insert new categories
            foreach ($_POST['categories'] as $category_id) {
                $sql = "INSERT INTO product_categories (product_id, category_id) VALUES (?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$id, $category_id]);
            }
        }
        
        // Handle colors
        if (isset($_POST['color_names'])) {
            $color_ids = $_POST['color_ids'] ?? [];
            $color_names = $_POST['color_names'] ?? [];
            $color_codes = $_POST['color_codes'] ?? [];
            $color_statuses = $_POST['color_statuses'] ?? [];
            
            // Get existing color IDs to determine which to delete
            $sql = "SELECT id FROM product_colors WHERE product_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            $existing_color_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $processed_color_ids = [];
            
            // Process each color
            for ($i = 0; $i < count($color_names); $i++) {
                $color_id = $color_ids[$i] ?? null;
                $color_name = $color_names[$i] ?? '';
                $color_code = $color_codes[$i] ?? '#000000';
                $color_status = $color_statuses[$i] ?? 'active';
                
                if ($color_id && $color_id != '') {
                    // Update existing color
                    $sql = "UPDATE product_colors SET color_name = ?, color_code = ?, status = ?, sort_order = ? WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$color_name, $color_code, $color_status, $i, $color_id]);
                    $processed_color_ids[] = $color_id;
                } else {
                    // Insert new color
                    $sql = "INSERT INTO product_colors (product_id, color_name, color_code, status, sort_order) VALUES (?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$id, $color_name, $color_code, $color_status, $i]);
                    $processed_color_ids[] = $pdo->lastInsertId();
                }
            }
            
            // Delete colors that are no longer present
            $colors_to_delete = array_diff($existing_color_ids, $processed_color_ids);
            if (!empty($colors_to_delete)) {
                $placeholders = implode(',', array_fill(0, count($colors_to_delete), '?'));
                $sql = "DELETE FROM product_colors WHERE id IN ($placeholders)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($colors_to_delete);
            }
        }
        
        // Handle related products
        // Delete existing related products
        $sql = "DELETE FROM related_products WHERE product_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        
        if (isset($_POST['related_products'])) {
            $sort_order = 0;
            foreach ($_POST['related_products'] as $related_id) {
                if ($related_id && $related_id != $id) { // Don't allow self-reference
                    $sql = "INSERT INTO related_products (product_id, related_product_id, sort_order) VALUES (?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$id, $related_id, $sort_order]);
                    $sort_order++;
                }
            }
        }
        
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Product saved successfully']);
        
    } catch (Exception $e) {
        $pdo->rollback();
        throw $e;
    }
}

function deleteProduct() {
    $database = new Database();
    $pdo = $database->getConnection();
    
    $id = $_POST['id'] ?? 0;
    
    if (!$id) {
        throw new Exception('Product ID is required');
    }
    
    $pdo->beginTransaction();
    
    try {
        // Check if product exists
        $sql = "SELECT id FROM products WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            throw new Exception('Product not found');
        }
        
        // Delete product (cascading deletes will handle related tables)
        $sql = "DELETE FROM products WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
        
    } catch (Exception $e) {
        $pdo->rollback();
        throw $e;
    }
}

function getCategories() {
    $database = new Database();
    $pdo = $database->getConnection();
    
    $sql = "SELECT id, name FROM categories WHERE status = 'active' ORDER BY name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($categories);
}

function listProductsForDropdown() {
    $database = new Database();
    $pdo = $database->getConnection();
    
    $sql = "SELECT id, name FROM products WHERE status = 'active' ORDER BY name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($products);
}

function generateSlug($string) {
    // Convert to lowercase
    $string = strtolower($string);
    
    // Replace spaces and special characters with hyphens
    $string = preg_replace('/[^a-z0-9\-]/', '-', $string);
    
    // Remove multiple consecutive hyphens
    $string = preg_replace('/-+/', '-', $string);
    
    // Remove leading and trailing hyphens
    $string = trim($string, '-');
    
    return $string;
}
?> 