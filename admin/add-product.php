<?php
require_once 'includes/config.php';
requireLogin();

// Check if we're in edit mode
$edit_mode = false;
$product_id = null;
$existing_product = null;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $edit_mode = true;
    $product_id = intval($_GET['id']);
}

$page_title = $edit_mode ? "Edit Product - MaterialM Admin" : "Add New Product - MaterialM Admin";

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Get all active categories for dropdown
$query = "SELECT id, name FROM categories WHERE status = 'active' ORDER BY name";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get form data from session if available (for error repopulation) or load existing product data
$form_data = $_SESSION['form_data'] ?? [];

// If in edit mode, load existing product data
if ($edit_mode && $product_id) {
    try {
        // Get product details
        $query = "SELECT * FROM products WHERE id = ? AND status != 'deleted'";
        $stmt = $db->prepare($query);
        $stmt->execute([$product_id]);
        $existing_product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$existing_product) {
            $_SESSION['error_message'] = "Product not found or has been deleted.";
            header('Location: product-management.php');
            exit();
        }
        
        // Only use existing product data if no form data from session (no errors)
        if (empty($form_data)) {
            $form_data = $existing_product;
            
            // Get product categories
            $query = "SELECT category_id FROM product_categories WHERE product_id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$product_id]);
            $product_categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $form_data['categories'] = $product_categories;
            
            // Get product colors
            $query = "SELECT color_name, color_code FROM product_colors WHERE product_id = ? AND status = 'active' ORDER BY id";
            $stmt = $db->prepare($query);
            $stmt->execute([$product_id]);
            $colors = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($colors) {
                $form_data['color_names'] = array_column($colors, 'color_name');
                $form_data['color_codes'] = array_column($colors, 'color_code');
            }
            
            // Get product sizes
            $query = "SELECT size_name, size_value FROM product_sizes WHERE product_id = ? AND status = 'active' ORDER BY id";
            $stmt = $db->prepare($query);
            $stmt->execute([$product_id]);
            $sizes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($sizes) {
                $form_data['size_names'] = array_column($sizes, 'size_name');
                $form_data['size_values'] = array_column($sizes, 'size_value');
            }
            
            // Get product images grouped by color in the same order as colors
            $query = "SELECT pi.*, pc.color_name, pc.sort_order as color_sort_order FROM product_images pi 
                      LEFT JOIN product_colors pc ON pi.color_id = pc.id 
                      WHERE pi.product_id = ? AND pi.status = 'active' 
                      ORDER BY pc.sort_order, pi.is_primary DESC, pi.id";
            $stmt = $db->prepare($query);
            $stmt->execute([$product_id]);
            $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Group images by color index (matching the form structure)
            $form_data['color_images'] = [];
            if ($images) {
                $color_images_by_order = [];
                foreach ($images as $image) {
                    $color_order = $image['color_sort_order'] ?? 0;
                    if (!isset($color_images_by_order[$color_order])) {
                        $color_images_by_order[$color_order] = [];
                    }
                    $color_images_by_order[$color_order][] = [
                        'path' => $image['image_path'],
                        'is_primary' => $image['is_primary']
                    ];
                }
                
                // Convert to indexed array matching color order
                ksort($color_images_by_order);
                $form_data['color_images'] = array_values($color_images_by_order);
            }
        }
        
    } catch (PDOException $e) {
        error_log("Error loading product for edit: " . $e->getMessage());
        $_SESSION['error_message'] = "Error loading product data.";
        header('Location: product-management.php');
        exit();
    }
}

// Clean up temporary images from previous session (older than 1 hour)
if (is_dir('../store/temp_images')) {
    $tempFiles = glob('../store/temp_images/temp_*');
    foreach ($tempFiles as $file) {
        if (file_exists($file) && (time() - filemtime($file)) > 3600) {
            unlink($file);
        }
    }
}

// Start output buffering for content
ob_start();

// Clear form data from session after displaying (to prevent it from persisting)
if (isset($_SESSION['form_data'])) {
    // We'll clear it at the end of the file after the form is rendered
    $clear_form_data = true;
} else {
    $clear_form_data = false;
}
?>

<style>
/* Override container-fluid max-width */
.container-fluid {
    max-width: none !important;
    width: 100% !important;
}

.form-section {
    background: white;
    border-radius: 8px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-section-title {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 20px;
    color: #333;
    border-bottom: 2px solid #f0f0f0;
    padding-bottom: 10px;
}

.form-section-title.mb-0 {
    margin-bottom: 0;
    border-bottom: none;
    padding-bottom: 0;
}

.color-input-group {
    display: flex;
    flex-direction: column;
    gap: 15px;
    margin-bottom: 20px;
    padding: 15px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    background-color: #f9f9f9;
}

.color-header {
    display: flex;
    align-items: center;
    gap: 10px;
}

.color-images-section {
    border: 2px dashed #ddd;
    border-radius: 6px;
    padding: 20px;
    text-align: center;
    position: relative;
    cursor: pointer;
    transition: all 0.3s ease;
    min-height: 120px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.color-images-section:hover {
    border-color: #007bff;
    background-color: #f8f9ff;
}

.color-images-section.dragover {
    border-color: #007bff;
    background-color: #e7f3ff;
    transform: scale(1.02);
}

.color-images-section.dragover::before {
    content: "Drop images here";
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #007bff;
    color: white;
    padding: 8px 16px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    z-index: 10;
}

.color-images-section.dragover > * {
    opacity: 0.3;
}

.color-image-preview {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 4px;
    margin: 3px;
    position: relative;
}

.color-image-preview-container {
    position: relative;
    display: inline-block;
}

.color-remove-image {
    position: absolute;
    top: 2px;
    right: 2px;
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 12px;
}

.color-preview {
    width: 60px;
    height: 40px;
    border-radius: 4px;
    border: 2px solid #ddd;
    cursor: pointer;
    transition: border-color 0.3s;
}

.color-preview:hover {
    border-color: #007bff;
}

.size-input-group {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}

.image-upload-area {
    border: 2px dashed #ddd;
    border-radius: 8px;
    padding: 40px;
    text-align: center;
    margin-bottom: 20px;
}

.image-upload-area.dragover {
    border-color: #007bff;
    background-color: #f8f9ff;
}

.image-preview {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 4px;
    margin: 5px;
}

.image-preview-container {
    position: relative;
    display: inline-block;
}

.remove-image {
    position: absolute;
    top: 5px;
    right: 5px;
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.btn-add-item {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    color: #495057;
}

.btn-add-item:hover {
    background: #e9ecef;
}

.btn-remove-item {
    background: #dc3545;
    border: 1px solid #dc3545;
    color: white;
}

.btn-remove-item:hover {
    background: #c82333;
}

.custom-pricing-section {
    background: #f8f9fa;
    border-radius: 6px;
    padding: 16px;
    margin-top: 16px;
}

.required {
    color: #dc3545;
}

/* Quill Editor Styling */
.ql-editor {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
    font-size: 14px !important;
    line-height: 1.5 !important;
}

.ql-container {
    border: 1px solid #dee2e6 !important;
    border-top: none !important;
    border-radius: 0 0 0.375rem 0.375rem !important;
}

.ql-toolbar.ql-snow {
    border: 1px solid #dee2e6 !important;
    border-bottom: none !important;
    border-radius: 0.375rem 0.375rem 0 0 !important;
    background: #f8f9fa !important;
}

.ql-toolbar.ql-snow .ql-picker-label {
    border-color: #dee2e6 !important;
}

.ql-toolbar.ql-snow .ql-picker-label:hover {
    background-color: #e9ecef !important;
}

.ql-toolbar.ql-snow button {
    color: #495057 !important;
}

.ql-toolbar.ql-snow button:hover {
    background-color: #e9ecef !important;
    color: #212529 !important;
}

.ql-toolbar.ql-snow button.ql-active {
    background-color: #007bff !important;
    color: white !important;
}

.ql-snow .ql-tooltip {
    background-color: #fff !important;
    border: 1px solid #dee2e6 !important;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
}

#quill-editor {
    background: white;
    border-radius: 0.375rem;
}
</style>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-semibold mb-1"><?php echo $edit_mode ? 'Edit Product' : 'Add New Product'; ?></h4>
            <p class="text-muted mb-0"><?php echo $edit_mode ? 'Update product information' : 'Add a new product to your catalog'; ?></p>
        </div>
        <div>
            <?php if (!$edit_mode): ?>
                <button type="button" class="btn btn-info me-2" onclick="generateDefaultValues()">
                    <iconify-icon icon="solar:magic-stick-3-linear" class="me-2"></iconify-icon>
                    Generate
                </button>
            <?php endif; ?>
            <a href="product-management.php" class="btn btn-outline-secondary me-2">
                <iconify-icon icon="solar:arrow-left-linear" class="me-2"></iconify-icon>
                Back to Products
            </a>
            <?php if ($edit_mode && isset($_SESSION['success_message'])): ?>
                <a href="../store/product-detail.php?slug=<?php echo htmlspecialchars($existing_product['slug']); ?>" target="_blank" class="btn btn-outline-info me-2">
                    <iconify-icon icon="solar:eye-linear" class="me-2"></iconify-icon>
                    Preview
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <iconify-icon icon="solar:check-circle-bold" class="me-2"></iconify-icon>
            <?php echo htmlspecialchars($_SESSION['success_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <iconify-icon icon="solar:danger-circle-bold" class="me-2"></iconify-icon>
            <?php echo htmlspecialchars($_SESSION['error_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <form id="addProductForm" action="add-product-handler.php" method="POST" enctype="multipart/form-data">
        <?php if ($edit_mode): ?>
            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
            <input type="hidden" name="edit_mode" value="1">
        <?php endif; ?>
        
        <!-- Basic Information Section -->
        <div class="form-section">
            <h5 class="form-section-title">Basic Information</h5>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name <span class="required">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($form_data['name'] ?? ''); ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="slug" class="form-label">Slug <span class="required">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="slug" name="slug" value="<?php echo htmlspecialchars($form_data['slug'] ?? ''); ?>" required readonly>
                            <button type="button" class="btn btn-outline-secondary" id="editSlugBtn" onclick="toggleSlugEdit()">
                                <iconify-icon icon="solar:pen-linear"></iconify-icon>
                            </button>
                        </div>
                        <small class="text-muted">Auto-generated from product name. Click edit to customize.</small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="price" class="form-label">Price <span class="required">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" value="<?php echo htmlspecialchars($form_data['price'] ?? ''); ?>" required>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="discounted_price" class="form-label">Discounted Price</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" id="discounted_price" name="discounted_price" step="0.01" value="<?php echo htmlspecialchars($form_data['discounted_price'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="active" <?php echo ($form_data['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($form_data['status'] ?? 'active') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="featured" class="form-label">Featured Product</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="featured" name="featured" value="1" <?php echo ($form_data['featured'] ?? 0) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="featured">
                                Mark as featured product
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="short_description" class="form-label">Short Description</label>
                        <textarea class="form-control" id="short_description" name="short_description" rows="3" placeholder="Brief description for product listing"><?php echo htmlspecialchars($form_data['short_description'] ?? ''); ?></textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="description" class="form-label">Full Description</label>
                        <textarea class="form-control" id="description" name="description" rows="5" placeholder="Detailed product description"><?php echo htmlspecialchars($form_data['description'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Categories Section -->
        <div class="form-section">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="form-section-title mb-0">Categories</h5>
                <a href="category-management.php" target="_blank" class="btn btn-outline-primary btn-sm">
                    <iconify-icon icon="solar:add-circle-linear" class="me-2"></iconify-icon>
                    Add Categories
                </a>
            </div>
            <div class="row">
                <?php foreach ($categories as $category): ?>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="category_<?php echo $category['id']; ?>" name="categories[]" value="<?php echo $category['id']; ?>" <?php echo in_array($category['id'], $form_data['categories'] ?? []) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="category_<?php echo $category['id']; ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </label>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Custom Pricing Section -->
        <div class="form-section">
            <h5 class="form-section-title">Custom Options</h5>
            
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="custom_name_enabled" name="custom_name_enabled" value="1" <?php echo ($form_data['custom_name_enabled'] ?? 0) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="custom_name_enabled">
                    Enable custom name option
                </label>
            </div>

            <div class="custom-pricing-section" id="customPricingSection" style="display: none;">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="pouch_custom_price" class="form-label">Pouch Custom Price</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="pouch_custom_price" name="pouch_custom_price" step="0.01" value="<?php echo htmlspecialchars($form_data['pouch_custom_price'] ?? '5000.00'); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="sajadah_custom_price" class="form-label">Sajadah Custom Price</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="sajadah_custom_price" name="sajadah_custom_price" step="0.01" value="<?php echo htmlspecialchars($form_data['sajadah_custom_price'] ?? '5000.00'); ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Colors Section -->
        <div class="form-section">
            <h5 class="form-section-title">Colors & Images</h5>
            <p class="text-muted mb-3">Add colors and their corresponding images. Each color can have multiple images.</p>
            
            <div id="colorsContainer">
                <?php 
                $colorNames = $form_data['color_names'] ?? [''];
                $colorCodes = $form_data['color_codes'] ?? ['#ff0000'];
                $colorImages = $form_data['color_images'] ?? [[]];
                $colorCount = max(count($colorNames), count($colorCodes), 1);
                
                for ($i = 0; $i < $colorCount; $i++):
                    $colorName = $colorNames[$i] ?? '';
                    $colorCode = $colorCodes[$i] ?? '#ff0000';
                    $images = $colorImages[$i] ?? [];
                ?>
                <div class="color-input-group" data-color-index="<?php echo $i; ?>">
                    <div class="color-header">
                        <input type="text" class="form-control" name="color_names[]" placeholder="Color name (e.g., Red, Blue)" value="<?php echo htmlspecialchars($colorName); ?>" style="flex: 1;">
                        <input type="color" class="form-control color-preview" name="color_codes[]" value="<?php echo htmlspecialchars($colorCode); ?>">
                        <button type="button" class="btn btn-remove-item btn-sm" onclick="removeColorInput(this)">
                            <iconify-icon icon="solar:trash-bin-minimalistic-linear"></iconify-icon>
                        </button>
                    </div>
                    
                    <div class="color-images-section" onclick="triggerColorImageUpload(<?php echo $i; ?>)">
                        <iconify-icon icon="solar:camera-add-linear" style="font-size: 32px; color: #6c757d;"></iconify-icon>
                        <p class="mb-0 mt-2">Click to upload or drag & drop images for this color</p>
                        <small class="text-muted">Support for multiple images. First image will be primary for this color.</small>
                        <input type="file" class="color-image-input" name="color_images_<?php echo $i; ?>[]" data-color-index="<?php echo $i; ?>" multiple accept="image/*" style="display: none;">
                    </div>
                    
                    <div class="color-image-previews" data-color-index="<?php echo $i; ?>">
                        <?php if (!empty($images)): ?>
                            <?php foreach ($images as $imageIndex => $image): ?>
                                <div class="color-image-preview-container">
                                    <img src="../store/<?php echo htmlspecialchars($image['path']); ?>" class="color-image-preview" alt="Color image">
                                    <button type="button" class="color-remove-image" onclick="removeColorImage(this)">×</button>
                                    <?php if ($imageIndex === 0): ?>
                                        <span class="badge bg-primary position-absolute" style="top: 2px; left: 2px; font-size: 10px;">Primary</span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
            
            <button type="button" class="btn btn-add-item btn-sm" onclick="addColorInput()">
                <iconify-icon icon="solar:add-circle-linear" class="me-2"></iconify-icon>
                Add Color
            </button>
        </div>

        <!-- Sizes Section -->
        <div class="form-section">
            <h5 class="form-section-title">Sizes</h5>
            <div id="sizesContainer">
                <?php 
                $sizeNames = $form_data['size_names'] ?? [''];
                $sizeValues = $form_data['size_values'] ?? [''];
                $sizeCount = max(count($sizeNames), count($sizeValues), 1);
                
                for ($i = 0; $i < $sizeCount; $i++):
                    $sizeName = $sizeNames[$i] ?? '';
                    $sizeValue = $sizeValues[$i] ?? '';
                ?>
                <div class="size-input-group">
                    <input type="text" class="form-control" name="size_names[]" placeholder="Size name (e.g., Small, Medium)" value="<?php echo htmlspecialchars($sizeName); ?>" style="flex: 1;">
                    <input type="text" class="form-control" name="size_values[]" placeholder="Size value (e.g., S, M)" value="<?php echo htmlspecialchars($sizeValue); ?>" style="flex: 1;">
                    <button type="button" class="btn btn-remove-item btn-sm" onclick="removeSizeInput(this)">
                        <iconify-icon icon="solar:trash-bin-minimalistic-linear"></iconify-icon>
                    </button>
                </div>
                <?php endfor; ?>
            </div>
            <button type="button" class="btn btn-add-item btn-sm" onclick="addSizeInput()">
                <iconify-icon icon="solar:add-circle-linear" class="me-2"></iconify-icon>
                Add Size
            </button>
        </div>



        <!-- Submit Buttons -->
        <div class="d-flex justify-content-end gap-2 mb-4">
            <a href="product-management.php" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <iconify-icon icon="solar:diskette-linear" class="me-2"></iconify-icon>
                <?php echo $edit_mode ? 'Update Product' : 'Save Product'; ?>
            </button>
        </div>
    </form>
</div>

<!-- Quill.js CDN -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

<script>
// Toggle custom pricing section
document.getElementById('custom_name_enabled').addEventListener('change', function() {
    const customPricingSection = document.getElementById('customPricingSection');
    if (this.checked) {
        customPricingSection.style.display = 'block';
    } else {
        customPricingSection.style.display = 'none';
    }
});

// Initialize custom pricing section visibility and color image uploads
document.addEventListener('DOMContentLoaded', function() {
    const customNameEnabled = document.getElementById('custom_name_enabled');
    const customPricingSection = document.getElementById('customPricingSection');
    
    if (customNameEnabled.checked) {
        customPricingSection.style.display = 'block';
    } else {
        customPricingSection.style.display = 'none';
    }
    
    // Add event listeners for existing color image inputs
    const colorImageInputs = document.querySelectorAll('.color-image-input');
    colorImageInputs.forEach(input => {
        const colorIndex = parseInt(input.getAttribute('data-color-index'));
        input.addEventListener('change', function(e) {
            handleColorImageUpload(e, colorIndex);
        });
    });
    
    // Add drag and drop event listeners to existing image sections
    const existingImageSections = document.querySelectorAll('.color-images-section');
    existingImageSections.forEach(section => {
        addDragAndDropListeners(section);
    });
    
    // Initialize slug generation
    initSlugGeneration();
    
    // Initialize Quill for Full Description
    initQuill();
});

// Slug generation functionality
function initSlugGeneration() {
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    
    // Auto-generate slug when typing product name
    nameInput.addEventListener('input', function() {
        if (slugInput.readOnly) {
            const generatedSlug = generateSlug(this.value);
            slugInput.value = generatedSlug;
        }
    });
    
    // Generate initial slug if name field has value
    if (nameInput.value && slugInput.readOnly) {
        slugInput.value = generateSlug(nameInput.value);
    }
}

// Generate slug from text
function generateSlug(text) {
    return text
        .toLowerCase()
        .trim()
        .replace(/[^\w\s-]/g, '') // Remove special characters except spaces and hyphens
        .replace(/[\s_-]+/g, '-') // Replace spaces and underscores with hyphens
        .replace(/^-+|-+$/g, ''); // Remove leading/trailing hyphens
}

// Toggle slug editing
function toggleSlugEdit() {
    const slugInput = document.getElementById('slug');
    const editBtn = document.getElementById('editSlugBtn');
    
    if (slugInput.readOnly) {
        // Enable editing
        slugInput.readOnly = false;
        slugInput.focus();
        editBtn.innerHTML = '<iconify-icon icon="solar:check-circle-linear"></iconify-icon>';
        editBtn.className = 'btn btn-outline-success';
        editBtn.title = 'Save slug';
    } else {
        // Disable editing and validate
        const slugValue = slugInput.value.trim();
        if (!slugValue) {
            alert('Slug cannot be empty');
            return;
        }
        
        // Clean the slug
        slugInput.value = generateSlug(slugValue);
        slugInput.readOnly = true;
        editBtn.innerHTML = '<iconify-icon icon="solar:pen-linear"></iconify-icon>';
        editBtn.className = 'btn btn-outline-secondary';
        editBtn.title = 'Edit slug';
    }
}

// Initialize Quill for Full Description
let quillEditor;

function initQuill() {
    if (typeof Quill !== 'undefined') {
        // Hide the original textarea
        document.getElementById('description').style.display = 'none';
        
        // Create a div for Quill editor
        const quillContainer = document.createElement('div');
        quillContainer.id = 'quill-editor';
        quillContainer.style.height = '300px';
        document.getElementById('description').parentNode.appendChild(quillContainer);
        
        // Initialize Quill
        quillEditor = new Quill('#quill-editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline'],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'align': [] }],
                    ['link'],
                    ['clean']
                ]
            },
            placeholder: 'Detailed product description...'
        });
        
        // Get initial content from textarea
        const initialContent = document.getElementById('description').value;
        if (initialContent) {
            quillEditor.root.innerHTML = initialContent;
        }
        
        // Update hidden textarea on content change
        quillEditor.on('text-change', function() {
            document.getElementById('description').value = quillEditor.root.innerHTML;
        });
    }
}

// Add drag and drop event listeners to an image section
function addDragAndDropListeners(imageSection) {
    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        imageSection.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });
    
    // Highlight drop area when item is dragged over it
    ['dragenter', 'dragover'].forEach(eventName => {
        imageSection.addEventListener(eventName, highlight, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        imageSection.addEventListener(eventName, unhighlight, false);
    });
    
    // Handle dropped files
    imageSection.addEventListener('drop', handleDrop, false);
}

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

function highlight(e) {
    e.currentTarget.classList.add('dragover');
}

function unhighlight(e) {
    e.currentTarget.classList.remove('dragover');
}

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    
    // Get the color index from the closest color input group
    const colorGroup = e.currentTarget.closest('.color-input-group');
    const colorIndex = parseInt(colorGroup.getAttribute('data-color-index'));
    
    // Get the file input for this color
    const fileInput = colorGroup.querySelector('.color-image-input');
    
    if (files.length > 0 && fileInput) {
        // Filter only image files
        const imageFiles = Array.from(files).filter(file => file.type.startsWith('image/'));
        
        if (imageFiles.length > 0) {
            // Create a new FileList-like object
            const dataTransfer = new DataTransfer();
            imageFiles.forEach(file => dataTransfer.items.add(file));
            
            // Update the file input
            fileInput.files = dataTransfer.files;
            
            // Trigger the change event to handle the upload
            const changeEvent = new Event('change', { bubbles: true });
            fileInput.dispatchEvent(changeEvent);
        }
    }
}

// Add color input
function addColorInput() {
    const container = document.getElementById('colorsContainer');
    const colorIndex = container.children.length;
    const newColor = document.createElement('div');
    newColor.className = 'color-input-group';
    newColor.setAttribute('data-color-index', colorIndex);
    newColor.innerHTML = `
        <div class="color-header">
            <input type="text" class="form-control" name="color_names[]" placeholder="Color name (e.g., Red, Blue)" style="flex: 1;">
            <input type="color" class="form-control color-preview" name="color_codes[]" value="#ff0000">
            <button type="button" class="btn btn-remove-item btn-sm" onclick="removeColorInput(this)">
                <iconify-icon icon="solar:trash-bin-minimalistic-linear"></iconify-icon>
            </button>
        </div>
        
        <div class="color-images-section" onclick="triggerColorImageUpload(${colorIndex})">
            <iconify-icon icon="solar:camera-add-linear" style="font-size: 32px; color: #6c757d;"></iconify-icon>
            <p class="mb-0 mt-2">Click to upload or drag & drop images for this color</p>
            <small class="text-muted">Support for multiple images. First image will be primary for this color.</small>
            <input type="file" class="color-image-input" name="color_images_${colorIndex}[]" data-color-index="${colorIndex}" multiple accept="image/*" style="display: none;">
        </div>
        
        <div class="color-image-previews" data-color-index="${colorIndex}"></div>
    `;
    container.appendChild(newColor);
    
    // Add event listener for the new image input
    const imageInput = newColor.querySelector('.color-image-input');
    imageInput.addEventListener('change', function(e) {
        handleColorImageUpload(e, colorIndex);
    });
    
    // Add drag and drop listeners to the new image section
    const imageSection = newColor.querySelector('.color-images-section');
    addDragAndDropListeners(imageSection);
}

// Remove color input
function removeColorInput(button) {
    const colorGroup = button.closest('.color-input-group');
    colorGroup.remove();
    
    // Update color indices after removal
    const container = document.getElementById('colorsContainer');
    const colorGroups = container.querySelectorAll('.color-input-group');
    colorGroups.forEach((group, index) => {
        group.setAttribute('data-color-index', index);
        const imageInput = group.querySelector('.color-image-input');
        const imageSection = group.querySelector('.color-images-section');
        const previews = group.querySelector('.color-image-previews');
        
        if (imageInput) {
            imageInput.setAttribute('data-color-index', index);
            imageInput.name = `color_images_${index}[]`;
        }
        if (imageSection) imageSection.setAttribute('onclick', `triggerColorImageUpload(${index})`);
        if (previews) previews.setAttribute('data-color-index', index);
    });
}

// Add size input
function addSizeInput() {
    const container = document.getElementById('sizesContainer');
    const newSize = document.createElement('div');
    newSize.className = 'size-input-group';
    newSize.innerHTML = `
        <input type="text" class="form-control" name="size_names[]" placeholder="Size name (e.g., Small, Medium)" style="flex: 1;">
        <input type="text" class="form-control" name="size_values[]" placeholder="Size value (e.g., S, M)" style="flex: 1;">
        <button type="button" class="btn btn-remove-item btn-sm" onclick="removeSizeInput(this)">
            <iconify-icon icon="solar:trash-bin-minimalistic-linear"></iconify-icon>
        </button>
    `;
    container.appendChild(newSize);
}

// Remove size input
function removeSizeInput(button) {
    const sizeGroup = button.closest('.size-input-group');
    sizeGroup.remove();
}

// Color-specific image upload handling
function triggerColorImageUpload(colorIndex) {
    const imageInput = document.querySelector(`[data-color-index="${colorIndex}"].color-image-input`);
    if (imageInput) {
        imageInput.click();
    }
}

function handleColorImageUpload(event, colorIndex) {
    const files = event.target.files;
    const previewContainer = document.querySelector(`[data-color-index="${colorIndex}"].color-image-previews`);
    
    if (!files.length || !previewContainer) return;
    
    // Clear existing previews for this color
    previewContainer.innerHTML = '';
    
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const imageContainer = document.createElement('div');
            imageContainer.className = 'color-image-preview-container';
            
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'color-image-preview';
            
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'color-remove-image';
            removeBtn.innerHTML = '×';
            removeBtn.onclick = function() {
                removeColorImage(removeBtn);
            };
            
            imageContainer.appendChild(img);
            imageContainer.appendChild(removeBtn);
            
            if (i === 0) {
                const primaryBadge = document.createElement('span');
                primaryBadge.className = 'badge bg-primary position-absolute';
                primaryBadge.style.top = '2px';
                primaryBadge.style.left = '2px';
                primaryBadge.style.fontSize = '10px';
                primaryBadge.textContent = 'Primary';
                imageContainer.appendChild(primaryBadge);
            }
            
            previewContainer.appendChild(imageContainer);
        };
        
        reader.readAsDataURL(file);
    }
}

function removeColorImage(button) {
    const imageContainer = button.closest('.color-image-preview-container');
    const previewContainer = imageContainer.closest('.color-image-previews');
    const colorGroup = previewContainer.closest('.color-input-group');
    const colorIndex = colorGroup.getAttribute('data-color-index');
    const wasPrimary = imageContainer.querySelector('.badge.bg-primary');
    
    imageContainer.remove();
    
    // If we removed the primary image, make the first remaining image primary
    if (wasPrimary) {
        const remainingImages = previewContainer.querySelectorAll('.color-image-preview-container');
        if (remainingImages.length > 0) {
            const firstImage = remainingImages[0];
            const primaryBadge = document.createElement('span');
            primaryBadge.className = 'badge bg-primary position-absolute';
            primaryBadge.style.top = '2px';
            primaryBadge.style.left = '2px';
            primaryBadge.style.fontSize = '10px';
            primaryBadge.textContent = 'Primary';
            firstImage.appendChild(primaryBadge);
        }
    }
    
    // If no images left, clear the file input
    const remainingImages = previewContainer.querySelectorAll('.color-image-preview-container');
    if (remainingImages.length === 0) {
        const fileInput = colorGroup.querySelector('.color-image-input');
        if (fileInput) {
            fileInput.value = '';
        }
    }
}

// Form validation
document.getElementById('addProductForm').addEventListener('submit', function(e) {
    // Save Quill content to textarea before validation
    if (typeof quillEditor !== 'undefined' && quillEditor) {
        document.getElementById('description').value = quillEditor.root.innerHTML;
    }
    
    const name = document.getElementById('name').value.trim();
    const slug = document.getElementById('slug').value.trim();
    const price = document.getElementById('price').value;
    
    if (!name) {
        e.preventDefault();
        alert('Please enter a product name');
        return;
    }
    
    if (!slug) {
        e.preventDefault();
        alert('Please enter a product slug');
        return;
    }
    
    // Validate slug format
    const slugPattern = /^[a-z0-9-]+$/;
    if (!slugPattern.test(slug)) {
        e.preventDefault();
        alert('Slug can only contain lowercase letters, numbers, and hyphens');
        return;
    }
    
    if (!price || price <= 0) {
        e.preventDefault();
        alert('Please enter a valid price');
        return;
    }
    
    // Check if at least one category is selected
    const categories = document.querySelectorAll('input[name="categories[]"]:checked');
    if (categories.length === 0) {
        e.preventDefault();
        alert('Please select at least one category');
        return;
    }
});

// Generate default values for all form fields
function generateDefaultValues() {
    if (!confirm('This will fill all form fields with default values. Are you sure?')) {
        return;
    }
    
    // Basic Information
    const productName = 'Sample Product ' + Math.floor(Math.random() * 1000);
    document.getElementById('name').value = productName;
    document.getElementById('slug').value = generateSlug(productName);
    document.getElementById('price').value = '299000';
    document.getElementById('discounted_price').value = '249000';
    document.getElementById('short_description').value = 'High-quality product with excellent features and modern design.';
    
    // Set Quill content
    const defaultDescription = `
        <h3>Product Overview</h3>
        <p>This is a comprehensive product description that highlights all the amazing features, benefits, and specifications of our premium product.</p>
        
        <h4>Key Features:</h4>
        <ul>
            <li><strong>Premium Quality:</strong> Made with the finest materials and cutting-edge technology</li>
            <li><strong>Exceptional Performance:</strong> Delivers outstanding results and value for money</li>
            <li><strong>Versatile Use:</strong> Perfect for everyday use and special occasions</li>
            <li><strong>Modern Design:</strong> Contemporary styling that fits any setting</li>
        </ul>
        
        <h4>Specifications:</h4>
        <ul>
            <li>High-quality construction</li>
            <li>Durable and long-lasting</li>
            <li>Easy to use and maintain</li>
            <li>Excellent customer support</li>
        </ul>
        
        <p><strong>Why Choose This Product?</strong><br>
        Our product stands out from the competition with its superior quality, innovative features, and exceptional value. Trusted by thousands of satisfied customers worldwide.</p>
    `;
    
    if (typeof quillEditor !== 'undefined' && quillEditor) {
        quillEditor.root.innerHTML = defaultDescription;
        document.getElementById('description').value = defaultDescription;
    } else {
        document.getElementById('description').value = defaultDescription;
    }
    document.getElementById('status').value = 'active';
    document.getElementById('featured').checked = true;
    
    // Categories - select first 2 categories if available
    const categoryCheckboxes = document.querySelectorAll('input[name="categories[]"]');
    categoryCheckboxes.forEach((checkbox, index) => {
        checkbox.checked = index < 2;
    });
    
    // Custom Name Options
    document.getElementById('custom_name_enabled').checked = true;
    document.getElementById('custom_name_enabled').dispatchEvent(new Event('change')); // Show section
    document.getElementById('pouch_custom_price').value = '50000';
    document.getElementById('sajadah_custom_price').value = '50000';
    
    // Clear existing colors and add default ones
    const colorsContainer = document.getElementById('colorsContainer');
    colorsContainer.innerHTML = '';
    
    // Add default colors
    const defaultColors = [
        { name: 'Red', code: '#dc3545' },
        { name: 'Blue', code: '#0d6efd' },
        { name: 'Green', code: '#198754' },
        { name: 'Black', code: '#000000' }
    ];
    
    defaultColors.forEach((color, index) => {
        addColorInput();
        const colorGroups = document.querySelectorAll('.color-input-group');
        const currentGroup = colorGroups[colorGroups.length - 1];
        
        const nameInput = currentGroup.querySelector('input[name="color_names[]"]');
        const codeInput = currentGroup.querySelector('input[name="color_codes[]"]');
        
        if (nameInput && codeInput) {
            nameInput.value = color.name;
            codeInput.value = color.code;
        }
    });
    
    // Clear existing sizes and add default ones
    const sizesContainer = document.getElementById('sizesContainer');
    sizesContainer.innerHTML = '';
    
    // Add default sizes
    const defaultSizes = [
        { name: 'Small', value: 'S' },
        { name: 'Medium', value: 'M' },
        { name: 'Large', value: 'L' },
        { name: 'Extra Large', value: 'XL' }
    ];
    
    defaultSizes.forEach((size, index) => {
        addSizeInput();
        const sizeGroups = document.querySelectorAll('.size-input-group');
        const currentGroup = sizeGroups[sizeGroups.length - 1];
        
        const nameInput = currentGroup.querySelector('input[name="size_names[]"]');
        const valueInput = currentGroup.querySelector('input[name="size_values[]"]');
        
        if (nameInput && valueInput) {
            nameInput.value = size.name;
            valueInput.value = size.value;
        }
    });
    
    // Show success message
    const alertHtml = `
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <iconify-icon icon="solar:check-circle-bold" class="me-2"></iconify-icon>
            Default values have been generated successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    const container = document.querySelector('.container-fluid');
    const pageHeader = container.querySelector('.d-flex.justify-content-between.align-items-center.mb-4');
    pageHeader.insertAdjacentHTML('afterend', alertHtml);
    
    // Scroll to top to show the success message
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>

<?php
$content = ob_get_clean();
include 'includes/auth_layout.php';

// Clear form data from session after form is rendered
if ($clear_form_data) {
    unset($_SESSION['form_data']);
}
?> 