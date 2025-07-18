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



// Function to generate slug from text
function generateSlug($text) {
    // Convert to lowercase and remove special characters
    $slug = strtolower(trim($text));
    
    // Replace spaces, underscores, and multiple hyphens with single hyphen
    $slug = preg_replace('/[^\w\s-]/', '', $slug);
    $slug = preg_replace('/[\s_-]+/', '-', $slug);
    
    // Remove leading and trailing hyphens
    $slug = trim($slug, '-');
    
    return $slug;
}

// Function to upload image with color prefix and product-specific folder
function uploadImage($file, $targetDir, $colorName = '', $productId = null) {
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
    
    // Create product-specific directory if productId is provided
    if ($productId) {
        $targetDir = $targetDir . '/' . $productId;
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
    
    // Generate filename with color prefix
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $colorPrefix = $colorName ? sanitizeFilename($colorName) . '_' : '';
    $uniqueFilename = $colorPrefix . uniqid() . '_' . time() . '.' . $extension;
    $targetPath = $targetDir . '/' . $uniqueFilename;
    
    // Additional check for file existence (shouldn't happen with unique filename, but just in case)
    if (file_exists($targetPath)) {
        return ['success' => false, 'message' => 'Target file already exists: ' . $targetPath];
    }
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        // Return path relative to store directory
        $relativePath = $productId ? 'images/products/' . $productId . '/' . $uniqueFilename : 'images/products/' . $uniqueFilename;
        return ['success' => true, 'filename' => $uniqueFilename, 'path' => $relativePath];
    } else {
        // Get more specific error information
        $lastError = error_get_last();
        $errorDetails = $lastError ? $lastError['message'] : 'Unknown system error';
        
        return ['success' => false, 'message' => 'Failed to move uploaded file from ' . $file['tmp_name'] . ' to ' . $targetPath . '. Error: ' . $errorDetails];
    }
}

// Function to sanitize filename
function sanitizeFilename($filename) {
    // Remove special characters and spaces, replace with underscores
    $filename = preg_replace('/[^a-zA-Z0-9]/', '_', $filename);
    // Remove multiple underscores
    $filename = preg_replace('/_+/', '_', $filename);
    // Remove leading/trailing underscores
    $filename = trim($filename, '_');
    // Convert to lowercase
    return strtolower($filename);
}

// Function to upload thumbnail image
function uploadThumbnail($file, $productId, $type) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxFileSize = 5 * 1024 * 1024; // 5MB
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'File size exceeds server limit',
            UPLOAD_ERR_FORM_SIZE => 'File size exceeds form limit',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];
        
        $errorMessage = $errorMessages[$file['error']] ?? 'Unknown upload error';
        return ['success' => false, 'message' => $errorMessage];
    }
    
    // Validate file type
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.'];
    }
    
    // Validate file size
    if ($file['size'] > $maxFileSize) {
        return ['success' => false, 'message' => 'File size too large. Maximum 5MB allowed.'];
    }
    
    // Create thumbnails directory structure
    $thumbnailDir = '../store/images/thumbnails/' . $productId;
    if (!is_dir($thumbnailDir)) {
        if (!mkdir($thumbnailDir, 0755, true)) {
            return ['success' => false, 'message' => 'Failed to create thumbnail directory'];
        }
    }
    
    // Generate filename with type prefix
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $type . '_' . time() . '.' . $extension;
    $targetPath = $thumbnailDir . '/' . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        $relativePath = 'images/thumbnails/' . $productId . '/' . $filename;
        return ['success' => true, 'filename' => $filename, 'path' => $relativePath];
    } else {
        return ['success' => false, 'message' => 'Failed to move uploaded file'];
    }
}

try {
    // Check if we're in edit mode
    $edit_mode = isset($_POST['edit_mode']) && $_POST['edit_mode'] == '1';
    $product_id = null;
    
    if ($edit_mode) {
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        if ($product_id <= 0) {
            throw new Exception('Invalid product ID for edit operation.');
        }
        
        // Verify product exists
        $checkQuery = "SELECT id FROM products WHERE id = ? AND status != 'deleted'";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->execute([$product_id]);
        if (!$checkStmt->fetch()) {
            throw new Exception('Product not found or has been deleted.');
        }
    }
    
    // Start transaction
    $db->beginTransaction();
    
    // Validate required fields
    if (empty($_POST['name']) || empty($_POST['price'])) {
        throw new Exception('Product name and price are required.');
    }
    
    if (empty($_POST['slug'])) {
        throw new Exception('Product slug is required.');
    }
    
    if (!isset($_POST['categories']) || empty($_POST['categories'])) {
        throw new Exception('Please select at least one category.');
    }
    
    // Validate primary thumbnail (mandatory for new products)
    if (!$edit_mode && (!isset($_FILES['primary_thumbnail']) || $_FILES['primary_thumbnail']['error'] === UPLOAD_ERR_NO_FILE)) {
        throw new Exception('Primary thumbnail is required.');
    }
    
    // Sanitize input data
    $name = trim($_POST['name']);
    $slug = generateSlug(trim($_POST['slug']));
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
    
    // Validate slug uniqueness (exclude current product when editing)
    if ($edit_mode) {
        $slugCheckQuery = "SELECT id FROM products WHERE slug = ? AND id != ?";
        $slugCheckStmt = $db->prepare($slugCheckQuery);
        $slugCheckStmt->execute([$slug, $product_id]);
    } else {
        $slugCheckQuery = "SELECT id FROM products WHERE slug = ?";
        $slugCheckStmt = $db->prepare($slugCheckQuery);
        $slugCheckStmt->execute([$slug]);
    }
    
    if ($slugCheckStmt->fetch()) {
        throw new Exception('A product with this slug already exists. Please choose a different slug.');
    }
    
    // Validate price
    if ($price <= 0) {
        throw new Exception('Price must be greater than 0.');
    }
    
    if ($discounted_price !== null && $discounted_price >= $price) {
        throw new Exception('Discounted price must be less than regular price.');
    }
    
    // Insert or update product
    if ($edit_mode) {
        // Update existing product
        $query = "UPDATE products SET name = ?, slug = ?, description = ?, short_description = ?, 
                  price = ?, discounted_price = ?, custom_name_enabled = ?, pouch_custom_price = ?, 
                  sajadah_custom_price = ?, status = ?, featured = ?, updated_at = CURRENT_TIMESTAMP 
                  WHERE id = ?";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            $name,
            $slug,
            $description,
            $short_description,
            $price,
            $discounted_price,
            $custom_name_enabled,
            $pouch_custom_price,
            $sajadah_custom_price,
            $status,
            $featured,
            $product_id
        ]);
    } else {
        // Insert new product
        $query = "INSERT INTO products (name, slug, description, short_description, price, discounted_price, 
                  custom_name_enabled, pouch_custom_price, sajadah_custom_price, status, featured) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            $name,
            $slug,
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
    }
    
    // Handle product categories
    if ($edit_mode) {
        // Clear existing categories
        $deleteCategoriesQuery = "DELETE FROM product_categories WHERE product_id = ?";
        $deleteCategoriesStmt = $db->prepare($deleteCategoriesQuery);
        $deleteCategoriesStmt->execute([$product_id]);
    }
    
    // Insert product categories
    $categoryQuery = "INSERT INTO product_categories (product_id, category_id) VALUES (?, ?)";
    $categoryStmt = $db->prepare($categoryQuery);
    
    foreach ($categories as $category_id) {
        $categoryStmt->execute([$product_id, $category_id]);
    }
    
    // Handle product colors
    $colorNames = $_POST['color_names'] ?? [];
    $colorCodes = $_POST['color_codes'] ?? [];
    
    if ($edit_mode) {
        // In edit mode, get existing color IDs and preserve images
        $existingColorsQuery = "SELECT id, color_name, color_code, sort_order FROM product_colors WHERE product_id = ? ORDER BY sort_order";
        $existingColorsStmt = $db->prepare($existingColorsQuery);
        $existingColorsStmt->execute([$product_id]);
        $existingColors = $existingColorsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // BACKUP APPROACH: Store existing images temporarily
        $existingImagesQuery = "SELECT pi.*, pc.sort_order as color_sort_order FROM product_images pi 
                               LEFT JOIN product_colors pc ON pi.color_id = pc.id 
                               WHERE pi.product_id = ? AND pi.status = 'active' 
                               ORDER BY pc.sort_order, pi.sort_order";
        $existingImagesStmt = $db->prepare($existingImagesQuery);
        $existingImagesStmt->execute([$product_id]);
        $existingImages = $existingImagesStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Group existing images by color sort order
        $imagesByColorOrder = [];
        foreach ($existingImages as $image) {
            $colorOrder = $image['color_sort_order'] ?? 0;
            if (!isset($imagesByColorOrder[$colorOrder])) {
                $imagesByColorOrder[$colorOrder] = [];
            }
            $imagesByColorOrder[$colorOrder][] = $image;
        }
        
        // Clear existing colors AND images (we'll restore them after)
        $deleteColorsQuery = "DELETE FROM product_colors WHERE product_id = ?";
        $deleteColorsStmt = $db->prepare($deleteColorsQuery);
        $deleteColorsStmt->execute([$product_id]);
        
        // Insert updated colors and get mapping of old IDs to new IDs
        $colorIdMapping = [];
        $colorQuery = "INSERT INTO product_colors (product_id, color_name, color_code, sort_order) VALUES (?, ?, ?, ?)";
        $colorStmt = $db->prepare($colorQuery);
        
        for ($i = 0; $i < count($colorNames); $i++) {
            if (!empty($colorNames[$i]) && !empty($colorCodes[$i])) {
                $colorStmt->execute([
                    $product_id,
                    trim($colorNames[$i]),
                    trim($colorCodes[$i]),
                    $i
                ]);
                
                $newColorId = $db->lastInsertId();
                
                // Find the corresponding old color ID if it exists
                $oldColorId = null;
                foreach ($existingColors as $existingColor) {
                    if ($existingColor['sort_order'] == $i) {
                        $oldColorId = $existingColor['id'];
                        break;
                    }
                }
                
                if ($oldColorId) {
                    $colorIdMapping[$oldColorId] = $newColorId;
                }
                
                // BACKUP APPROACH: Restore existing images for this color order if no new images uploaded
                if (isset($imagesByColorOrder[$i])) {
                    $colorImageFieldName = "color_images_" . $i;
                    $hasNewImages = isset($_FILES[$colorImageFieldName]) && !empty($_FILES[$colorImageFieldName]['name'][0]);
                    
                    if (!$hasNewImages) {
                        // Restore existing images for this color
                        $restoreImageQuery = "INSERT INTO product_images (product_id, color_id, image_path, alt_text, is_primary, sort_order, status) VALUES (?, ?, ?, ?, ?, ?, 'active')";
                        $restoreImageStmt = $db->prepare($restoreImageQuery);
                        
                        foreach ($imagesByColorOrder[$i] as $image) {
                            $restoreImageStmt->execute([
                                $product_id,
                                $newColorId,
                                $image['image_path'],
                                $image['alt_text'],
                                $image['is_primary'],
                                $image['sort_order']
                            ]);
                        }
                        
                    }
                }
            }
        }
    } else {
        // Insert product colors for new products
        if (!empty($colorNames) && !empty($colorCodes)) {
            $colorQuery = "INSERT INTO product_colors (product_id, color_name, color_code, sort_order) VALUES (?, ?, ?, ?)";
            $colorStmt = $db->prepare($colorQuery);
            
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
    }
    
    // Handle product sizes
    if ($edit_mode) {
        // Clear existing sizes
        $deleteSizesQuery = "DELETE FROM product_sizes WHERE product_id = ?";
        $deleteSizesStmt = $db->prepare($deleteSizesQuery);
        $deleteSizesStmt->execute([$product_id]);
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
    
    // Handle product images
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
    
    // Get color IDs that were just inserted
    $colorQuery = "SELECT id FROM product_colors WHERE product_id = ? ORDER BY sort_order";
    $colorStmt = $db->prepare($colorQuery);
    $colorStmt->execute([$product_id]);
    $colorIds = $colorStmt->fetchAll(PDO::FETCH_COLUMN);
    
    if ($edit_mode) {
        // In edit mode, only clear images for colors that have new uploads
        // This preserves existing images for colors that don't have new uploads
        $deleteColorImagesQuery = "DELETE FROM product_images WHERE product_id = ? AND color_id = ?";
        $deleteColorImagesStmt = $db->prepare($deleteColorImagesQuery);
        
        $imageQuery = "INSERT INTO product_images (product_id, color_id, image_path, alt_text, is_primary, sort_order) VALUES (?, ?, ?, ?, ?, ?)";
        $imageStmt = $db->prepare($imageQuery);
        

        
        // Process images for each color
        for ($colorIndex = 0; $colorIndex < count($colorIds); $colorIndex++) {
            $colorId = $colorIds[$colorIndex];
            $colorImageFieldName = "color_images_" . $colorIndex;
            
            // Check if new images are uploaded for this color
            if (isset($_FILES[$colorImageFieldName]) && !empty($_FILES[$colorImageFieldName]['name'][0])) {
                // Clear existing images for this color only
                $deleteColorImagesStmt->execute([$product_id, $colorId]);
                
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
                        
                        $uploadResult = uploadImage($file, $uploadDir, $colorNames[$colorIndex], $product_id);
                        
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
            // If no new images for this color, existing images are preserved
        }
        
    } else {
        // In create mode, insert all new images
        $imageQuery = "INSERT INTO product_images (product_id, color_id, image_path, alt_text, is_primary, sort_order) VALUES (?, ?, ?, ?, ?, ?)";
        $imageStmt = $db->prepare($imageQuery);
        
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
                        
                        $uploadResult = uploadImage($file, $uploadDir, $colorNames[$colorIndex], $product_id);
                        
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
    }
    
    // Handle product thumbnails
    $primaryThumbnailPath = null;
    $secondaryThumbnailPath = null;
    
    // Process primary thumbnail
    if (isset($_FILES['primary_thumbnail']) && $_FILES['primary_thumbnail']['error'] !== UPLOAD_ERR_NO_FILE) {
        $primaryResult = uploadThumbnail($_FILES['primary_thumbnail'], $product_id, 'primary');
        if ($primaryResult['success']) {
            $primaryThumbnailPath = $primaryResult['path'];
        } else {
            throw new Exception('Primary thumbnail upload failed: ' . $primaryResult['message']);
        }
    }
    
    // Process secondary thumbnail (optional)
    if (isset($_FILES['secondary_thumbnail']) && $_FILES['secondary_thumbnail']['error'] !== UPLOAD_ERR_NO_FILE) {
        $secondaryResult = uploadThumbnail($_FILES['secondary_thumbnail'], $product_id, 'secondary');
        if ($secondaryResult['success']) {
            $secondaryThumbnailPath = $secondaryResult['path'];
        } else {
            throw new Exception('Secondary thumbnail upload failed: ' . $secondaryResult['message']);
        }
    }
    
    // Handle thumbnail removal flags
    $removePrimaryThumbnail = isset($_POST['remove_primary_thumbnail']) && $_POST['remove_primary_thumbnail'] == '1';
    $removeSecondaryThumbnail = isset($_POST['remove_secondary_thumbnail']) && $_POST['remove_secondary_thumbnail'] == '1';
    
    // Save thumbnail information to database
    if ($primaryThumbnailPath || $secondaryThumbnailPath || $removePrimaryThumbnail || $removeSecondaryThumbnail) {
        if ($edit_mode) {
            // Check if thumbnail record exists
            $checkThumbnailQuery = "SELECT id, primary_image, secondary_image FROM product_thumbnails WHERE product_id = ?";
            $checkThumbnailStmt = $db->prepare($checkThumbnailQuery);
            $checkThumbnailStmt->execute([$product_id]);
            $existingThumbnail = $checkThumbnailStmt->fetch();
            
            if ($existingThumbnail) {
                // Update existing thumbnail record
                $updateFields = [];
                $updateValues = [];
                
                // Handle primary thumbnail
                if ($primaryThumbnailPath) {
                    // New primary thumbnail uploaded
                    $updateFields[] = "primary_image = ?";
                    $updateValues[] = $primaryThumbnailPath;
                } elseif ($removePrimaryThumbnail) {
                    // Primary thumbnail should be removed
                    $updateFields[] = "primary_image = ?";
                    $updateValues[] = null;
                }
                
                // Handle secondary thumbnail  
                if ($secondaryThumbnailPath) {
                    // New secondary thumbnail uploaded
                    $updateFields[] = "secondary_image = ?";
                    $updateValues[] = $secondaryThumbnailPath;
                } elseif ($removeSecondaryThumbnail) {
                    // Secondary thumbnail should be removed
                    $updateFields[] = "secondary_image = ?";
                    $updateValues[] = null;
                }
                
                if (!empty($updateFields)) {
                    $updateValues[] = $product_id;
                    $updateQuery = "UPDATE product_thumbnails SET " . implode(', ', $updateFields) . " WHERE product_id = ?";
                    $updateStmt = $db->prepare($updateQuery);
                    $updateStmt->execute($updateValues);
                }
            } else {
                // Insert new thumbnail record (only if we have new uploads, not removals)
                if ($primaryThumbnailPath || $secondaryThumbnailPath) {
                    $insertThumbnailQuery = "INSERT INTO product_thumbnails (product_id, primary_image, secondary_image) VALUES (?, ?, ?)";
                    $insertThumbnailStmt = $db->prepare($insertThumbnailQuery);
                    $insertThumbnailStmt->execute([$product_id, $primaryThumbnailPath, $secondaryThumbnailPath]);
                }
            }
        } else {
            // Insert new thumbnail record for new product (only if we have uploads)
            if ($primaryThumbnailPath || $secondaryThumbnailPath) {
                $insertThumbnailQuery = "INSERT INTO product_thumbnails (product_id, primary_image, secondary_image) VALUES (?, ?, ?)";
                $insertThumbnailStmt = $db->prepare($insertThumbnailQuery);
                $insertThumbnailStmt->execute([$product_id, $primaryThumbnailPath, $secondaryThumbnailPath]);
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
    
    // Success message and redirect back to the same page
    if ($edit_mode) {
        $_SESSION['success_message'] = 'Product "' . $name . '" has been updated successfully!';
        header('Location: add-product.php?id=' . $product_id);
    } else {
        $_SESSION['success_message'] = 'Product "' . $name . '" has been created successfully!';
        header('Location: add-product.php?id=' . $product_id);
    }
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
        'slug' => $_POST['slug'] ?? '',
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
                        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                        $uniqueFilename = 'temp_' . uniqid() . '_' . time() . '.' . $extension;
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