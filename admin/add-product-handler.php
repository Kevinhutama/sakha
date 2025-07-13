<?php
require_once 'includes/config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: product-management.php');
    exit;
}

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Function to generate unique filename
function generateUniqueFilename($originalName) {
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    return uniqid() . '_' . time() . '.' . $extension;
}

// Function to upload image
function uploadImage($file, $targetDir) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxFileSize = 5 * 1024 * 1024; // 5MB
    
    // Check for upload errors first
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'File size exceeds server limit (php.ini upload_max_filesize)',
            UPLOAD_ERR_FORM_SIZE => 'File size exceeds form limit (MAX_FILE_SIZE)',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder on server',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by PHP extension'
        ];
        
        $errorMessage = $errorMessages[$file['error']] ?? 'Unknown upload error (code: ' . $file['error'] . ')';
        return ['success' => false, 'message' => $errorMessage];
    }
    
    // Check if file was actually uploaded
    if (!is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'message' => 'File was not uploaded via HTTP POST'];
    }
    
    // Check file type
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed. Received: ' . $file['type']];
    }
    
    // Check file size
    if ($file['size'] > $maxFileSize) {
        return ['success' => false, 'message' => 'File size too large. Maximum 5MB allowed. File size: ' . round($file['size'] / 1024 / 1024, 2) . 'MB'];
    }
    
    // Check if target directory exists and is writable
    if (!is_dir($targetDir)) {
        if (!mkdir($targetDir, 0755, true)) {
            return ['success' => false, 'message' => 'Failed to create target directory: ' . $targetDir];
        }
    }
    
    if (!is_writable($targetDir)) {
        return ['success' => false, 'message' => 'Target directory is not writable: ' . $targetDir . ' (Check permissions)'];
    }
    
    $uniqueFilename = generateUniqueFilename($file['name']);
    $targetPath = $targetDir . '/' . $uniqueFilename;
    
    // Additional check for file existence (shouldn't happen with unique filename, but just in case)
    if (file_exists($targetPath)) {
        return ['success' => false, 'message' => 'Target file already exists: ' . $targetPath];
    }
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'filename' => $uniqueFilename, 'path' => 'images/products/' . $uniqueFilename];
    } else {
        // Get more specific error information
        $lastError = error_get_last();
        $errorDetails = $lastError ? $lastError['message'] : 'Unknown system error';
        
        return ['success' => false, 'message' => 'Failed to move uploaded file from ' . $file['tmp_name'] . ' to ' . $targetPath . '. Error: ' . $errorDetails];
    }
}

try {
    // Start transaction
    $db->beginTransaction();
    
    // Validate required fields
    if (empty($_POST['name']) || empty($_POST['price'])) {
        throw new Exception('Product name and price are required.');
    }
    
    if (!isset($_POST['categories']) || empty($_POST['categories'])) {
        throw new Exception('Please select at least one category.');
    }
    
    // Sanitize input data
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $short_description = trim($_POST['short_description']);
    $price = floatval($_POST['price']);
    $discounted_price = !empty($_POST['discounted_price']) ? floatval($_POST['discounted_price']) : null;
    $custom_name_enabled = isset($_POST['custom_name_enabled']) ? 1 : 0;
    $pouch_custom_price = !empty($_POST['pouch_custom_price']) ? floatval($_POST['pouch_custom_price']) : 5000.00;
    $sajadah_custom_price = !empty($_POST['sajadah_custom_price']) ? floatval($_POST['sajadah_custom_price']) : 5000.00;
    $status = $_POST['status'] ?? 'active';
    $featured = isset($_POST['featured']) ? 1 : 0;
    $categories = $_POST['categories'];
    
    // Validate price
    if ($price <= 0) {
        throw new Exception('Price must be greater than 0.');
    }
    
    if ($discounted_price !== null && $discounted_price >= $price) {
        throw new Exception('Discounted price must be less than regular price.');
    }
    
    // Insert product
    $query = "INSERT INTO products (name, description, short_description, price, discounted_price, 
              custom_name_enabled, pouch_custom_price, sajadah_custom_price, status, featured) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $db->prepare($query);
    $stmt->execute([
        $name,
        $description,
        $short_description,
        $price,
        $discounted_price,
        $custom_name_enabled,
        $pouch_custom_price,
        $sajadah_custom_price,
        $status,
        $featured
    ]);
    
    $product_id = $db->lastInsertId();
    
    // Insert product categories
    $categoryQuery = "INSERT INTO product_categories (product_id, category_id) VALUES (?, ?)";
    $categoryStmt = $db->prepare($categoryQuery);
    
    foreach ($categories as $category_id) {
        $categoryStmt->execute([$product_id, $category_id]);
    }
    
    // Insert product colors
    if (!empty($_POST['color_names']) && !empty($_POST['color_codes'])) {
        $colorQuery = "INSERT INTO product_colors (product_id, color_name, color_code, sort_order) VALUES (?, ?, ?, ?)";
        $colorStmt = $db->prepare($colorQuery);
        
        $colorNames = $_POST['color_names'];
        $colorCodes = $_POST['color_codes'];
        
        for ($i = 0; $i < count($colorNames); $i++) {
            if (!empty($colorNames[$i]) && !empty($colorCodes[$i])) {
                $colorStmt->execute([
                    $product_id,
                    trim($colorNames[$i]),
                    trim($colorCodes[$i]),
                    $i
                ]);
            }
        }
    }
    
    // Insert product sizes
    if (!empty($_POST['size_names']) && !empty($_POST['size_values'])) {
        $sizeQuery = "INSERT INTO product_sizes (product_id, size_name, size_value, sort_order) VALUES (?, ?, ?, ?)";
        $sizeStmt = $db->prepare($sizeQuery);
        
        $sizeNames = $_POST['size_names'];
        $sizeValues = $_POST['size_values'];
        
        for ($i = 0; $i < count($sizeNames); $i++) {
            if (!empty($sizeNames[$i]) && !empty($sizeValues[$i])) {
                $sizeStmt->execute([
                    $product_id,
                    trim($sizeNames[$i]),
                    trim($sizeValues[$i]),
                    $i
                ]);
            }
        }
    }
    
    // Handle color-specific image uploads
    $uploadDir = '../store/images/products';
    
    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            throw new Exception('Failed to create upload directory: ' . $uploadDir);
        }
    }
    
    // Test if directory is writable by creating a temporary file
    $testFile = $uploadDir . '/test_write_' . time() . '.tmp';
    if (!file_put_contents($testFile, 'test')) {
        throw new Exception('Upload directory is not writable: ' . $uploadDir . ' (Check permissions)');
    }
    unlink($testFile); // Clean up test file
    
    $imageQuery = "INSERT INTO product_images (product_id, color_id, image_path, alt_text, is_primary, sort_order) VALUES (?, ?, ?, ?, ?, ?)";
    $imageStmt = $db->prepare($imageQuery);
    
    // Get color IDs that were just inserted
    $colorQuery = "SELECT id FROM product_colors WHERE product_id = ? ORDER BY sort_order";
    $colorStmt = $db->prepare($colorQuery);
    $colorStmt->execute([$product_id]);
    $colorIds = $colorStmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Process images for each color
    for ($colorIndex = 0; $colorIndex < count($colorIds); $colorIndex++) {
        $colorId = $colorIds[$colorIndex];
        $colorImageFieldName = "color_images_" . $colorIndex;
        
        if (isset($_FILES[$colorImageFieldName]) && !empty($_FILES[$colorImageFieldName]['name'][0])) {
            $imageCount = count($_FILES[$colorImageFieldName]['name']);
            
            for ($i = 0; $i < $imageCount; $i++) {
                if (!empty($_FILES[$colorImageFieldName]['name'][$i])) {
                    $file = [
                        'name' => $_FILES[$colorImageFieldName]['name'][$i],
                        'type' => $_FILES[$colorImageFieldName]['type'][$i],
                        'tmp_name' => $_FILES[$colorImageFieldName]['tmp_name'][$i],
                        'error' => $_FILES[$colorImageFieldName]['error'][$i],
                        'size' => $_FILES[$colorImageFieldName]['size'][$i]
                    ];
                    
                    $uploadResult = uploadImage($file, $uploadDir);
                    
                    if ($uploadResult['success']) {
                        $is_primary = ($i === 0) ? 1 : 0; // First image is primary for this color
                        $imageStmt->execute([
                            $product_id,
                            $colorId,
                            $uploadResult['path'],
                            $name . ' - ' . $colorNames[$colorIndex], // Use product name + color as alt text
                            $is_primary,
                            $i
                        ]);
                    } else {
                        throw new Exception('Image upload failed for color ' . $colorNames[$colorIndex] . ': ' . $uploadResult['message']);
                    }
                }
            }
        }
    }
    
    // Commit transaction
    $db->commit();
    
    // Clean up temporary images on successful submission
    if (isset($_SESSION['form_data']['color_images'])) {
        foreach ($_SESSION['form_data']['color_images'] as $colorImages) {
            foreach ($colorImages as $tempImage) {
                $tempFilePath = str_replace('../store/', '../store/', $tempImage['path']);
                if (file_exists($tempFilePath)) {
                    unlink($tempFilePath);
                }
            }
        }
    }
    
    // Clear form data from session
    unset($_SESSION['form_data']);
    
    // Success message
    $_SESSION['success_message'] = 'Product "' . $name . '" has been created successfully!';
    header('Location: product-management.php');
    exit;
    
} catch (Exception $e) {
    // Rollback transaction on error
    $db->rollback();
    
    // Enhanced error logging for debugging
    error_log("Product creation error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Store form data in session to repopulate form
    $_SESSION['form_data'] = [
        'name' => $_POST['name'] ?? '',
        'description' => $_POST['description'] ?? '',
        'short_description' => $_POST['short_description'] ?? '',
        'price' => $_POST['price'] ?? '',
        'discounted_price' => $_POST['discounted_price'] ?? '',
        'custom_name_enabled' => isset($_POST['custom_name_enabled']) ? 1 : 0,
        'pouch_custom_price' => $_POST['pouch_custom_price'] ?? '5000.00',
        'sajadah_custom_price' => $_POST['sajadah_custom_price'] ?? '5000.00',
        'status' => $_POST['status'] ?? 'active',
        'featured' => isset($_POST['featured']) ? 1 : 0,
        'categories' => $_POST['categories'] ?? [],
        'color_names' => $_POST['color_names'] ?? [],
        'color_codes' => $_POST['color_codes'] ?? [],
        'color_images' => [], // Will be populated below
        'size_names' => $_POST['size_names'] ?? [],
        'size_values' => $_POST['size_values'] ?? []
    ];
    
    // Handle color-specific image uploads for temporary storage
    $colorImages = [];
    $colorNames = $_POST['color_names'] ?? [];
    
    for ($colorIndex = 0; $colorIndex < count($colorNames); $colorIndex++) {
        $colorImages[$colorIndex] = [];
        
        // Check if this color has uploaded images
        $colorImageFieldName = "color_images_" . $colorIndex;
        if (isset($_FILES[$colorImageFieldName]) && !empty($_FILES[$colorImageFieldName]['name'][0])) {
            $tempImageDir = '../store/temp_images';
            
            // Create temporary directory if it doesn't exist
            if (!is_dir($tempImageDir)) {
                mkdir($tempImageDir, 0755, true);
            }
            
            $imageCount = count($_FILES[$colorImageFieldName]['name']);
            
            for ($i = 0; $i < $imageCount; $i++) {
                if (!empty($_FILES[$colorImageFieldName]['name'][$i])) {
                    $file = [
                        'name' => $_FILES[$colorImageFieldName]['name'][$i],
                        'type' => $_FILES[$colorImageFieldName]['type'][$i],
                        'tmp_name' => $_FILES[$colorImageFieldName]['tmp_name'][$i],
                        'error' => $_FILES[$colorImageFieldName]['error'][$i],
                        'size' => $_FILES[$colorImageFieldName]['size'][$i]
                    ];
                    
                    // Only store valid image files temporarily
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (in_array($file['type'], $allowedTypes) && $file['size'] <= 5 * 1024 * 1024) {
                        $uniqueFilename = 'temp_' . generateUniqueFilename($file['name']);
                        $tempPath = $tempImageDir . '/' . $uniqueFilename;
                        
                        if (move_uploaded_file($file['tmp_name'], $tempPath)) {
                            $colorImages[$colorIndex][] = [
                                'name' => $file['name'],
                                'path' => '../store/temp_images/' . $uniqueFilename,
                                'is_primary' => ($i === 0)
                            ];
                        }
                    }
                }
            }
        }
    }
    
    $_SESSION['form_data']['color_images'] = $colorImages;
    
    // Set error message
    $_SESSION['error_message'] = 'Error creating product: ' . $e->getMessage();
    header('Location: add-product.php');
    exit;
}
?> 