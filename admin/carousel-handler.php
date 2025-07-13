<?php
require_once 'includes/config.php';

// Require authentication
requireLogin();

// Set content type to JSON
header('Content-Type: application/json');

// Function to handle file uploads
function handleFileUpload($file, $uploadDir, $prefix = '') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Create upload directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.');
    }
    
    // Validate file size (max 5MB)
    $maxSize = 5 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        throw new Exception('File size too large. Maximum size is 5MB.');
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $prefix . '_' . uniqid() . '.' . $extension;
    $filepath = $uploadDir . '/' . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return 'images/carousel/' . $filename;
    } else {
        // Enhanced error reporting
        $error_msg = 'Failed to move uploaded file.';
        $error_msg .= ' Upload dir: ' . $uploadDir;
        $error_msg .= ' Target path: ' . $filepath;
        $error_msg .= ' Temp file: ' . $file['tmp_name'];
        $error_msg .= ' Dir exists: ' . (is_dir($uploadDir) ? 'Yes' : 'No');
        $error_msg .= ' Dir writable: ' . (is_writable($uploadDir) ? 'Yes' : 'No');
        throw new Exception($error_msg);
    }
}

// Function to delete file
function deleteFile($filePath) {
    $fullPath = '../store/' . $filePath;
    if (file_exists($fullPath)) {
        unlink($fullPath);
    }
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $action = $_POST['action'] ?? '';
    $uploadDir = '../store/images/carousel';
    
    switch ($action) {
        case 'add':
            // Validate required fields
            if (empty($_POST['title']) || empty($_POST['display_order'])) {
                throw new Exception('Title and display order are required.');
            }
            
            // Handle file uploads
            if (!isset($_FILES['web_image']) || $_FILES['web_image']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Web image is required.');
            }
            
            $webImagePath = handleFileUpload($_FILES['web_image'], $uploadDir, 'web');
            $mobileImagePath = null;
            
            if (isset($_FILES['mobile_image']) && $_FILES['mobile_image']['error'] === UPLOAD_ERR_OK) {
                $mobileImagePath = handleFileUpload($_FILES['mobile_image'], $uploadDir, 'mobile');
            }
            
            // Insert into database
            $query = "INSERT INTO carousel_images (title, description, web_image_path, mobile_image_path, button_text, button_url, display_order, is_active) 
                      VALUES (:title, :description, :web_image_path, :mobile_image_path, :button_text, :button_url, :display_order, :is_active)";
            
            $stmt = $db->prepare($query);
            $stmt->execute([
                ':title' => $_POST['title'],
                ':description' => $_POST['description'] ?? '',
                ':web_image_path' => $webImagePath,
                ':mobile_image_path' => $mobileImagePath,
                ':button_text' => $_POST['button_text'] ?? 'Shop Now',
                ':button_url' => $_POST['button_url'] ?? '#',
                ':display_order' => (int)$_POST['display_order'],
                ':is_active' => isset($_POST['is_active']) ? 1 : 0
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Carousel image added successfully.']);
            break;
            
        case 'edit':
            // Validate required fields
            if (empty($_POST['id']) || empty($_POST['title']) || empty($_POST['display_order'])) {
                throw new Exception('ID, title, and display order are required.');
            }
            
            $id = (int)$_POST['id'];
            
            // Get current image data
            $query = "SELECT web_image_path, mobile_image_path FROM carousel_images WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->execute([':id' => $id]);
            $currentData = $stmt->fetch();
            
            if (!$currentData) {
                throw new Exception('Carousel image not found.');
            }
            
            $webImagePath = $currentData['web_image_path'];
            $mobileImagePath = $currentData['mobile_image_path'];
            
            // Handle new file uploads
            if (isset($_FILES['web_image']) && $_FILES['web_image']['error'] === UPLOAD_ERR_OK) {
                $newWebImagePath = handleFileUpload($_FILES['web_image'], $uploadDir, 'web');
                deleteFile($webImagePath); // Delete old file
                $webImagePath = $newWebImagePath;
            }
            
            if (isset($_FILES['mobile_image']) && $_FILES['mobile_image']['error'] === UPLOAD_ERR_OK) {
                $newMobileImagePath = handleFileUpload($_FILES['mobile_image'], $uploadDir, 'mobile');
                if ($mobileImagePath) {
                    deleteFile($mobileImagePath); // Delete old file
                }
                $mobileImagePath = $newMobileImagePath;
            }
            
            // Update database
            $query = "UPDATE carousel_images SET 
                      title = :title, 
                      description = :description, 
                      web_image_path = :web_image_path, 
                      mobile_image_path = :mobile_image_path, 
                      button_text = :button_text, 
                      button_url = :button_url, 
                      display_order = :display_order, 
                      is_active = :is_active 
                      WHERE id = :id";
            
            $stmt = $db->prepare($query);
            $stmt->execute([
                ':title' => $_POST['title'],
                ':description' => $_POST['description'] ?? '',
                ':web_image_path' => $webImagePath,
                ':mobile_image_path' => $mobileImagePath,
                ':button_text' => $_POST['button_text'] ?? 'Shop Now',
                ':button_url' => $_POST['button_url'] ?? '#',
                ':display_order' => (int)$_POST['display_order'],
                ':is_active' => isset($_POST['is_active']) ? 1 : 0,
                ':id' => $id
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Carousel image updated successfully.']);
            break;
            
        case 'delete':
            if (empty($_POST['id'])) {
                throw new Exception('ID is required.');
            }
            
            $id = (int)$_POST['id'];
            
            // Get image paths before deletion
            $query = "SELECT web_image_path, mobile_image_path FROM carousel_images WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->execute([':id' => $id]);
            $imageData = $stmt->fetch();
            
            if (!$imageData) {
                throw new Exception('Carousel image not found.');
            }
            
            // Delete from database
            $query = "DELETE FROM carousel_images WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->execute([':id' => $id]);
            
            // Delete image files
            deleteFile($imageData['web_image_path']);
            if ($imageData['mobile_image_path']) {
                deleteFile($imageData['mobile_image_path']);
            }
            
            echo json_encode(['success' => true, 'message' => 'Carousel image deleted successfully.']);
            break;
            
        case 'toggle_status':
            if (empty($_POST['id']) || !isset($_POST['is_active'])) {
                throw new Exception('ID and status are required.');
            }
            
            $id = (int)$_POST['id'];
            $isActive = (int)$_POST['is_active'];
            
            $query = "UPDATE carousel_images SET is_active = :is_active WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->execute([
                ':is_active' => $isActive,
                ':id' => $id
            ]);
            
            $status = $isActive ? 'activated' : 'deactivated';
            echo json_encode(['success' => true, 'message' => "Carousel image $status successfully."]);
            break;
            
        default:
            throw new Exception('Invalid action.');
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 