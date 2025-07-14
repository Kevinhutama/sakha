<?php
require_once 'includes/config.php';
requireLogin();

$page_title = "Product Management - MaterialM Admin";

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Get all products with their categories and colors (all products for now)
$query = "SELECT p.*, 
                 GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ') as categories,
                 GROUP_CONCAT(DISTINCT CONCAT(pc.color_name, ':', pc.color_code) ORDER BY pc.sort_order SEPARATOR ', ') as colors,
                 pi.image_path as primary_image
          FROM products p
          LEFT JOIN product_categories pc_rel ON p.id = pc_rel.product_id
          LEFT JOIN categories c ON pc_rel.category_id = c.id
          LEFT JOIN product_colors pc ON p.id = pc.product_id AND pc.status = 'active'
          LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
          GROUP BY p.id
          ORDER BY p.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Start output buffering for content
ob_start();
?>

<style>
/* Override container-fluid max-width */
.container-fluid {
    max-width: none !important;
    width: 100% !important;
}

.product-image {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 4px;
}

.color-preview {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    display: inline-block;
    border: 1px solid #ddd;
    margin-right: 3px;
}

.price-original {
    text-decoration: line-through;
    color: #6c757d;
    font-size: 0.9em;
}

.price-discounted {
    color: #dc3545;
    font-weight: bold;
}

.search-container {
    position: relative;
}

.search-container .form-control {
    padding-left: 40px;
}

.search-container .search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
}

.action-buttons .btn {
    padding: 4px 8px;
    margin: 0 2px;
}

.table-responsive {
    height: 100vh;
    overflow-y: auto;
}

.table th {
    background-color: #f8f9fa;
    position: sticky;
    top: 0;
    z-index: 10;
}

.no-results {
    text-align: center;
    padding: 40px;
    color: #6c757d;
}

.product-info {
    max-width: 200px;
}

.product-info h6 {
    margin: 0 0 5px 0;
    font-size: 14px;
}

.product-info small {
    color: #6c757d;
    display: block;
    margin-bottom: 3px;
}

.category-badges .badge {
    margin: 1px;
    font-size: 0.7em;
}

.action-buttons {
    display: flex;
    gap: 5px;
}

.action-buttons .btn {
    padding: 4px 8px;
    font-size: 14px;
}

.action-buttons .btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}


</style>

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

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-semibold mb-1">Product Management</h4>
        <p class="text-muted mb-0">Manage your products catalog</p>
    </div>
    <a href="add-product.php" class="btn btn-primary">
        <iconify-icon icon="solar:add-circle-bold" class="me-2"></iconify-icon>
        Add New Product
    </a>
</div>

<!-- Search Bar -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="search-container">
            <iconify-icon icon="solar:magnifer-linear" class="search-icon"></iconify-icon>
            <input type="text" class="form-control" id="searchInput" placeholder="Search products...">
        </div>
    </div>
    <div class="col-md-8">
        <div class="d-flex justify-content-end">
            <small class="text-muted d-flex align-items-center">
                <span id="productCount"><?php echo count($products);?></span> products found
            </small>
        </div>
    </div>
</div>

<!-- Products Table -->
<div class="table-responsive bg-white rounded-3 border">
    <table class="table table-striped table-hover mb-0">
        <thead>
            <tr>
                <th>Image</th>
                <th>Product</th>
                <th>Categories</th>
                <th>Colors</th>
                <th>Price</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="productTableBody">
            <?php foreach ($products as $product): ?>
            <tr class="product-row">
                <td>
                    <?php if ($product['primary_image']): ?>
                        <img src="../store/<?php echo htmlspecialchars($product['primary_image']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             class="product-image">
                    <?php else: ?>
                        <div class="product-image bg-light d-flex align-items-center justify-content-center">
                            <iconify-icon icon="solar:camera-linear" class="text-muted"></iconify-icon>
                        </div>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="product-info">
                        <h6><a href="../store/product-detail.php?slug=<?php echo htmlspecialchars($product['slug']); ?>" target="_blank" class="text-decoration-none"><?php echo htmlspecialchars($product['name']); ?></a></h6>
                        <small><?php echo htmlspecialchars(substr($product['short_description'], 0, 60)) . '...'; ?></small>
                    </div>
                </td>
                <td>
                    <div class="category-badges">
                        <?php 
                        if ($product['categories']) {
                            $categories = explode(', ', $product['categories']);
                            foreach ($categories as $category) {
                                echo '<span class="badge bg-secondary">' . htmlspecialchars($category) . '</span> ';
                            }
                        } else {
                            echo '<span class="text-muted">No categories</span>';
                        }
                        ?>
                    </div>
                </td>
                <td>
                    <div class="colors-container">
                        <?php 
                        if ($product['colors']) {
                            $colors = explode(', ', $product['colors']);
                            foreach ($colors as $color) {
                                $colorParts = explode(':', $color);
                                if (count($colorParts) == 2) {
                                    echo '<span class="color-preview" style="background-color: ' . htmlspecialchars($colorParts[1]) . '" title="' . htmlspecialchars($colorParts[0]) . '"></span>';
                                }
                            }
                        } else {
                            echo '<span class="text-muted">No colors</span>';
                        }
                        ?>
                    </div>
                </td>
                <td>
                    <div class="price-container">
                        <?php if ($product['discounted_price'] && $product['discounted_price'] < $product['price']): ?>
                            <div class="price-original">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></div>
                            <div class="price-discounted">Rp <?php echo number_format($product['discounted_price'], 0, ',', '.'); ?></div>
                        <?php else: ?>
                            <div>Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></div>
                        <?php endif; ?>
                    </div>
                </td>
                <td>
                    <?php if ($product['status'] == 'active'): ?>
                        <span class="badge bg-success">Active</span>
                    <?php elseif ($product['status'] == 'deleted'): ?>
                        <span class="badge bg-dark">Deleted</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Inactive</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="action-buttons">
                        <a href="add-product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit Product">
                            <iconify-icon icon="solar:pen-bold"></iconify-icon>
                        </a>
                        <button class="btn btn-sm btn-outline-danger" type="button" title="Delete Product" onclick="deleteProduct(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name'], ENT_QUOTES); ?>')">
                            <iconify-icon icon="solar:trash-bin-trash-bold"></iconify-icon>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="no-results" id="noResults" style="display: none;">
        <iconify-icon icon="solar:magnifer-linear" class="fs-1 text-muted"></iconify-icon>
        <h5 class="mt-3 text-muted">No products found</h5>
        <p class="text-muted">Try adjusting your search terms</p>
    </div>
</div>

<script>
// Real-time search functionality
document.getElementById('searchInput').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const productRows = document.querySelectorAll('.product-row');
    const noResults = document.getElementById('noResults');
    const productCount = document.getElementById('productCount');
    let visibleCount = 0;
    
    productRows.forEach(row => {
        const productInfo = row.querySelector('.product-info');
        const categoryBadges = row.querySelector('.category-badges');
        
        // Get text content for search
        const productName = productInfo.querySelector('h6').textContent.toLowerCase();
        const productDesc = productInfo.querySelector('small') ? productInfo.querySelector('small').textContent.toLowerCase() : '';
        const categories = categoryBadges.textContent.toLowerCase();
        
        // Check if search term matches any of the content
        if (productName.includes(searchTerm) || 
            productDesc.includes(searchTerm) || 
            categories.includes(searchTerm)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update product count
    productCount.textContent = visibleCount;
    
    // Show/hide no results message
    if (visibleCount === 0) {
        noResults.style.display = 'block';
    } else {
        noResults.style.display = 'none';
    }
});

// Delete product function
function deleteProduct(productId, productName) {
    if (confirm('Are you sure you want to delete the product "' + productName + '"?\n\nThis action cannot be undone.')) {
        // Show loading state
        const deleteButton = event.target.closest('button');
        const originalContent = deleteButton.innerHTML;
        deleteButton.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"></div>';
        deleteButton.disabled = true;
        
        // Send AJAX request to delete handler
        fetch('delete-product-handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'product_id=' + productId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the row from table with animation
                const row = deleteButton.closest('tr');
                row.style.transition = 'opacity 0.3s ease';
                row.style.opacity = '0';
                
                setTimeout(() => {
                    row.remove();
                    
                    // Update product count
                    const productCount = document.getElementById('productCount');
                    const currentCount = parseInt(productCount.textContent);
                    productCount.textContent = currentCount - 1;
                    
                    // Show success message
                    showMessage('success', data.message);
                    
                    // Check if no products left
                    const remainingRows = document.querySelectorAll('.product-row');
                    if (remainingRows.length === 0) {
                        document.getElementById('noResults').style.display = 'block';
                    }
                }, 300);
            } else {
                // Show error message
                showMessage('error', data.message);
                
                // Restore button state
                deleteButton.innerHTML = originalContent;
                deleteButton.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('error', 'An error occurred while deleting the product');
            
            // Restore button state
            deleteButton.innerHTML = originalContent;
            deleteButton.disabled = false;
        });
    }
}

// Function to show success/error messages
function showMessage(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const iconClass = type === 'success' ? 'solar:check-circle-bold' : 'solar:danger-circle-bold';
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <iconify-icon icon="${iconClass}" class="me-2"></iconify-icon>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    // Insert at the top of the page
    const container = document.querySelector('.container-fluid');
    const pageHeader = container.querySelector('.d-flex.justify-content-between.align-items-center.mb-4');
    pageHeader.insertAdjacentHTML('afterend', alertHtml);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alert = container.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}

// Add button click handlers (without implementation)
document.addEventListener('DOMContentLoaded', function() {
    // Edit buttons are now links, no need for click handlers
});
</script>

<?php
$content = ob_get_clean();
include 'includes/layout.php';
?>
