<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - Simple Version</title>
    <link rel="stylesheet" href="assets/css/styles.min.css">
    <style>
        .color-preview {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-block;
            border: 2px solid #ddd;
            margin-right: 5px;
        }
        .color-management {
            max-height: 300px;
            overflow-y: auto;
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
</head>
<body>
    <?php
    require_once 'includes/config.php';
    requireLogin();
    ?>
    
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="body-wrapper">
            <?php include 'includes/header.php'; ?>
            
            <div class="body-wrapper-inner">
                <div class="container-fluid">
                    <div id="alertContainer"></div>
                    
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h4 class="card-title mb-0">Product Management (Simple Version)</h4>
                                        </div>
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-primary" onclick="openProductModal()">
                                                <i class="ti ti-plus"></i> Add New Product
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <input type="text" id="searchProduct" class="form-control" placeholder="Search products...">
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
                                                <!-- Products will be loaded here -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- All the modal HTML would go here directly -->
    <div class="modal fade" id="productModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add/Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="productForm">
                    <div class="modal-body">
                        <!-- All form fields would go here directly -->
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
                        
                        <!-- More form fields... -->
                        
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/sidebarmenu.js"></script>
    <script src="assets/js/app.min.js"></script>
    <script>
        // All JavaScript would go here directly
        $(document).ready(function() {
            loadProducts();
            loadCategories();
            
            $("#addColor").click(function() {
                addColorField();
            });
            
            $("#productForm").submit(function(e) {
                e.preventDefault();
                saveProduct();
            });
            
            $("#searchProduct").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#productTable tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
        
        function loadProducts() {
            $.ajax({
                url: "product-handler.php",
                type: "GET",
                data: { action: "list" },
                dataType: "json",
                success: function(data) {
                    displayProducts(data);
                },
                error: function(xhr, status, error) {
                    showAlert("Error loading products: " + error, "danger");
                }
            });
        }
        
        // More JavaScript functions...
        
    </script>
</body>
</html> 