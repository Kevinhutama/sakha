<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/config.php';
requireLogin();

$page_title = "Product Management - MaterialM Admin";

// Load products and categories directly
$database = new Database();
$pdo = $database->getConnection();

// Get products with categories and colors
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

// Get categories for dropdown
$sql = "SELECT id, name FROM categories WHERE status = 'active' ORDER BY name";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>

<style>
.color-preview {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: inline-block;
    border: 2px solid #ddd;
    margin-right: 5px;
}
.product-image {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 4px;
}
.price-original {
    text-decoration: line-through;
    color: #6c757d;
}
.modal-xl {
    max-width: 1200px;
}
</style>

<div class="container-fluid">
    <div id="alertContainer"></div>
    
    <div class="row">
        <div class="col-lg-12">
            <div class="">
                <div class="">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title mb-0">Product Management</h4>
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn btn-primary" onclick="openProductModal()">
                                <i class="ti ti-plus"></i> Add New Product
                            </button>
                        </div>
                    </div>
                </div>
                <div class="">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" id="searchProduct" class="form-control" placeholder="Search products...">
                        </div>
                        <div class="col-md-6 text-end">
                            <span class="text-muted">Total Products: <?php echo count($products); ?></span>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table id="productTable" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Product</th>
                                    <th>Categories</th>
                                    <th>Colors</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo $product['id']; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="../store/<?php echo $product['primary_image'] ?: 'images/placeholder.jpg'; ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                 class="product-image me-2">
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($product['name']); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($product['sku']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo $product['categories'] ?: '-'; ?></td>
                                    <td>
                                        <?php if ($product['colors']): ?>
                                            <?php 
                                            $colors = explode(',', $product['colors']);
                                            foreach ($colors as $color):
                                                $parts = explode('|', $color);
                                                if (count($parts) == 2):
                                            ?>
                                                <span class="color-preview" 
                                                      style="background-color: <?php echo $parts[1]; ?>" 
                                                      title="<?php echo htmlspecialchars($parts[0]); ?>"></span>
                                            <?php 
                                                endif;
                                            endforeach; 
                                            ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($product['discounted_price']): ?>
                                            <span class="price-original">₹<?php echo number_format($product['price']); ?></span><br>
                                            ₹<?php echo number_format($product['discounted_price']); ?>
                                        <?php else: ?>
                                            ₹<?php echo number_format($product['price']); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $product['stock_quantity']; ?></td>
                                    <td>
                                        <?php if ($product['status'] === 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                        
                                        <?php if ($product['featured'] == 1): ?>
                                            <span class="badge bg-primary">Featured</span>
                                        <?php endif; ?>
                                        
                                        <?php if ($product['custom_name_enabled'] == 1): ?>
                                            <span class="badge bg-info">Custom Name</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary me-1" onclick="editProduct(<?php echo $product['id']; ?>)">
                                            <i class="ti ti-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteProduct(<?php echo $product['id']; ?>)">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add/Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="productForm">
                <div class="modal-body">
                    <input type="hidden" id="productId" name="id">
                    <input type="hidden" name="action" value="save">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="productName" class="form-label">Product Name *</label>
                                <input type="text" class="form-control" id="productName" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="productSku" class="form-label">SKU *</label>
                                <input type="text" class="form-control" id="productSku" name="sku" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="productDescription" class="form-label">Description</label>
                                <textarea class="form-control" id="productDescription" name="description" rows="4"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="shortDescription" class="form-label">Short Description</label>
                                <textarea class="form-control" id="shortDescription" name="short_description" rows="4"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="productPrice" class="form-label">Price *</label>
                                <input type="number" class="form-control" id="productPrice" name="price" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="discountedPrice" class="form-label">Discounted Price</label>
                                <input type="number" class="form-control" id="discountedPrice" name="discounted_price" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="stockQuantity" class="form-label">Stock Quantity</label>
                                <input type="number" class="form-control" id="stockQuantity" name="stock_quantity" value="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="categories" class="form-label">Categories</label>
                                <select class="form-select" id="categories" name="categories[]" multiple>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>">
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple categories</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="productStatus" class="form-label">Status</label>
                                <select class="form-select" id="productStatus" name="status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="customNameEnabled" name="custom_name_enabled" value="1">
                                    <label class="form-check-label" for="customNameEnabled">
                                        Custom Name Available
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="productFeatured" name="featured" value="1">
                                    <label class="form-check-label" for="productFeatured">
                                        Featured Product
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="pouchCustomPrice" class="form-label">Pouch Custom Price</label>
                                <input type="number" class="form-control" id="pouchCustomPrice" name="pouch_custom_price" step="0.01" value="5000.00">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sajadahCustomPrice" class="form-label">Sajadah Custom Price</label>
                                <input type="number" class="form-control" id="sajadahCustomPrice" name="sajadah_custom_price" step="0.01" value="5000.00">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Color Management -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6>Product Colors</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addColor">
                                <i class="ti ti-plus"></i> Add Color
                            </button>
                        </div>
                        <div id="colorsContainer">
                            <!-- Color fields will be added here -->
                        </div>
                    </div>
                    
                    <!-- Related Products -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6>Related Products (Optional)</h6>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="addRelatedProduct">
                                <i class="ti ti-plus"></i> Add Related Product
                            </button>
                        </div>
                        <div id="relatedProductsContainer">
                            <!-- Related products will be added here -->
                        </div>
                    </div>
                    
                    <!-- SEO Fields -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="metaTitle" class="form-label">Meta Title</label>
                                <input type="text" class="form-control" id="metaTitle" name="meta_title">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="metaDescription" class="form-label">Meta Description</label>
                                <textarea class="form-control" id="metaDescription" name="meta_description" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $("#addColor").click(function() {
        addColorField();
    });
    
    $("#addRelatedProduct").click(function() {
        addRelatedProduct();
    });
    
    $("#productForm").submit(function(e) {
        e.preventDefault();
        saveProduct();
    });
    
    // Search functionality
    $("#searchProduct").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#productTable tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});

function openProductModal(productId = null) {
    if (productId) {
        $.ajax({
            url: "product-handler.php",
            type: "GET",
            data: { action: "get", id: productId },
            dataType: "json",
            success: function(data) {
                populateForm(data);
                $("#productModal").modal("show");
            },
            error: function(xhr, status, error) {
                console.error("Error getting product:", xhr.responseText);
                showAlert("Error loading product: " + error, "danger");
            }
        });
    } else {
        $("#productForm")[0].reset();
        $("#productId").val("");
        $("#colorsContainer").empty();
        $("#relatedProductsContainer").empty();
        $("#productModal").modal("show");
    }
}

function populateForm(product) {
    $("#productId").val(product.id);
    $("#productName").val(product.name);
    $("#productDescription").val(product.description);
    $("#shortDescription").val(product.short_description);
    $("#productSku").val(product.sku);
    $("#productPrice").val(product.price);
    $("#discountedPrice").val(product.discounted_price);
    $("#stockQuantity").val(product.stock_quantity);
    $("#customNameEnabled").prop("checked", product.custom_name_enabled == 1);
    $("#pouchCustomPrice").val(product.pouch_custom_price);
    $("#sajadahCustomPrice").val(product.sajadah_custom_price);
    $("#productStatus").val(product.status);
    $("#productFeatured").prop("checked", product.featured == 1);
    $("#metaTitle").val(product.meta_title);
    $("#metaDescription").val(product.meta_description);
    
    // Load categories
    if (product.category_ids) {
        var categoryIds = product.category_ids.split(",");
        $("#categories").val(categoryIds);
    }
    
    // Load colors
    $("#colorsContainer").empty();
    if (product.colors_data) {
        product.colors_data.forEach(function(color) {
            addColorField(color);
        });
    }
    
    // Load related products
    $("#relatedProductsContainer").empty();
    if (product.related_products) {
        product.related_products.forEach(function(related) {
            addRelatedProduct(related);
        });
    }
}

function addColorField(colorData = null) {
    var colorId = colorData ? colorData.id : '';
    var colorName = colorData ? colorData.color_name : '';
    var colorCode = colorData ? colorData.color_code : '#000000';
    var colorStatus = colorData ? colorData.status : 'active';
    
    var colorHtml = `
        <div class="color-item border rounded p-3 mb-3">
            <input type="hidden" name="color_ids[]" value="${colorId}">
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">Color Name</label>
                    <input type="text" class="form-control" name="color_names[]" value="${colorName}" placeholder="Enter color name" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Color Code</label>
                    <input type="color" class="form-control form-control-color" name="color_codes[]" value="${colorCode}" title="Choose color">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="color_statuses[]">
                        <option value="active" ${colorStatus === 'active' ? 'selected' : ''}>Active</option>
                        <option value="inactive" ${colorStatus === 'inactive' ? 'selected' : ''}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeColorField(this)">
                        <i class="ti ti-trash"></i> Remove
                    </button>
                </div>
            </div>
        </div>
    `;
    
    $("#colorsContainer").append(colorHtml);
}

function removeColorField(button) {
    $(button).closest('.color-item').remove();
}

function addRelatedProduct(productData = null) {
    var productId = productData ? productData.id : '';
    
    var relatedHtml = `
        <div class="related-item border rounded p-3 mb-3">
            <div class="row">
                <div class="col-md-10">
                    <label class="form-label">Related Product</label>
                    <select class="form-select" name="related_products[]" required>
                        <option value="">Select Related Product</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeRelatedProduct(this)">
                        <i class="ti ti-trash"></i> Remove
                    </button>
                </div>
            </div>
        </div>
    `;
    
    $("#relatedProductsContainer").append(relatedHtml);
    
    // Load products for dropdown
    loadProductsForDropdown($("#relatedProductsContainer .related-item:last select"), productId);
}

function removeRelatedProduct(button) {
    $(button).closest('.related-item').remove();
}

function loadProductsForDropdown(selectElement, selectedId = null) {
    $.ajax({
        url: "product-handler.php",
        type: "GET",
        data: { action: "list_for_dropdown" },
        dataType: "json",
        success: function(data) {
            selectElement.empty();
            selectElement.append('<option value="">Select Related Product</option>');
            data.forEach(function(product) {
                var selected = product.id == selectedId ? 'selected' : '';
                selectElement.append(`<option value="${product.id}" ${selected}>${product.name}</option>`);
            });
        }
    });
}

function editProduct(id) {
    openProductModal(id);
}

function deleteProduct(id) {
    if (confirm("Are you sure you want to delete this product?")) {
        $.ajax({
            url: "product-handler.php",
            type: "POST",
            data: { action: "delete", id: id },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    showAlert("Product deleted successfully", "success");
                    // Reload page to refresh the list
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    showAlert("Error: " + response.message, "danger");
                }
            },
            error: function(xhr, status, error) {
                console.error("Error deleting product:", xhr.responseText);
                showAlert("Error deleting product: " + error, "danger");
            }
        });
    }
}

function saveProduct() {
    var formData = new FormData($("#productForm")[0]);
    
    $.ajax({
        url: "product-handler.php",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        dataType: "json",
        success: function(response) {
            if (response.success) {
                showAlert("Product saved successfully", "success");
                $("#productModal").modal("hide");
                // Reload page to refresh the list
                setTimeout(function() {
                    location.reload();
                }, 1000);
            } else {
                showAlert("Error: " + response.message, "danger");
            }
        },
        error: function(xhr, status, error) {
            console.error("Error saving product:", xhr.responseText);
            showAlert("Error saving product: " + error, "danger");
        }
    });
}

function showAlert(message, type) {
    var alert = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $("#alertContainer").html(alert);
    
    setTimeout(function() {
        $("#alertContainer .alert").alert("close");
    }, 5000);
}
</script>

<?php
$content = ob_get_clean();
include 'includes/layout.php';
?> 