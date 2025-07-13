<?php
require_once 'includes/config.php';
requireLogin();

$page_title = "Category Management - MaterialM Admin";

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Get all categories
$query = "SELECT * FROM categories ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Start output buffering for content
ob_start();
?>

<style>
/* Override container-fluid max-width */
.container-fluid {
    max-width: none !important;
    width: 100% !important;
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

.category-info h6 {
    margin: 0 0 5px 0;
    font-size: 14px;
}

.category-info small {
    color: #6c757d;
    display: block;
}

.description-text {
    max-width: 300px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-semibold mb-1">
            <iconify-icon icon="solar:folder-bold" class="me-2"></iconify-icon>
            Category Management
        </h4>
        <p class="text-muted mb-0">Manage your product categories</p>
    </div>
    <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
        <iconify-icon icon="solar:add-circle-bold" class="me-2"></iconify-icon>
        Add New Category
    </button>
</div>

<!-- Search Bar -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="search-container">
            <iconify-icon icon="solar:magnifer-linear" class="search-icon"></iconify-icon>
            <input type="text" class="form-control" id="searchInput" placeholder="Search categories...">
        </div>
    </div>
    <div class="col-md-8">
        <div class="d-flex justify-content-end">
            <small class="text-muted d-flex align-items-center">
                <span id="categoryCount"><?php echo count($categories); ?></span> categories found
            </small>
        </div>
    </div>
</div>

<!-- Categories Table -->
<div class="table-responsive bg-white rounded-3 border">
    <table class="table table-striped table-hover mb-0">
        <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Slug</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="categoryTableBody">
            <?php foreach ($categories as $category): ?>
            <tr class="category-row">
                <td>
                    <div class="category-info">
                        <h6><?php echo htmlspecialchars($category['name']); ?></h6>
                        <small>ID: <?php echo $category['id']; ?></small>
                    </div>
                </td>
                <td>
                    <div class="description-text" title="<?php echo htmlspecialchars($category['description'] ?? ''); ?>">
                        <?php echo htmlspecialchars($category['description'] ?? 'No description'); ?>
                    </div>
                </td>
                <td>
                    <code><?php echo htmlspecialchars($category['slug']); ?></code>
                </td>
                <td>
                    <?php if ($category['status'] == 'active'): ?>
                        <span class="badge bg-success">Active</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Inactive</span>
                    <?php endif; ?>
                </td>
                <td>
                    <small class="text-muted">
                        <?php echo date('M j, Y', strtotime($category['created_at'])); ?>
                    </small>
                </td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-sm btn-outline-primary edit-category-btn" 
                                type="button" 
                                title="Edit Category"
                                data-category-id="<?php echo $category['id']; ?>"
                                data-category-name="<?php echo htmlspecialchars($category['name']); ?>"
                                data-category-description="<?php echo htmlspecialchars($category['description'] ?? ''); ?>"
                                data-category-slug="<?php echo htmlspecialchars($category['slug']); ?>"
                                data-category-status="<?php echo $category['status']; ?>">
                            <iconify-icon icon="solar:pen-bold"></iconify-icon>
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-category-btn" 
                                type="button" 
                                title="Delete Category"
                                data-category-id="<?php echo $category['id']; ?>"
                                data-category-name="<?php echo htmlspecialchars($category['name']); ?>">
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
        <h5 class="mt-3 text-muted">No categories found</h5>
        <p class="text-muted">Try adjusting your search terms</p>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCategoryModalLabel">
                    <iconify-icon icon="solar:add-circle-bold" class="me-2"></iconify-icon>
                    Add New Category
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addCategoryForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="addCategoryName" class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="addCategoryName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="addCategoryDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="addCategoryDescription" name="description" rows="3" placeholder="Optional category description"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="addCategorySlug" class="form-label">Slug</label>
                        <input type="text" class="form-control" id="addCategorySlug" name="slug" placeholder="Auto-generated from name">
                        <small class="form-text text-muted">Leave empty to auto-generate from category name</small>
                    </div>
                    <div class="mb-3">
                        <label for="addCategoryStatus" class="form-label">Status</label>
                        <select class="form-select" id="addCategoryStatus" name="status">
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <iconify-icon icon="solar:check-circle-bold" class="me-2"></iconify-icon>
                        Add Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCategoryModalLabel">
                    <iconify-icon icon="solar:pen-bold" class="me-2"></iconify-icon>
                    Edit Category
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editCategoryForm">
                <input type="hidden" id="editCategoryId" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editCategoryName" class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editCategoryName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editCategoryDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editCategoryDescription" name="description" rows="3" placeholder="Optional category description"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editCategorySlug" class="form-label">Slug</label>
                        <input type="text" class="form-control" id="editCategorySlug" name="slug">
                        <small class="form-text text-muted">URL-friendly version of the category name</small>
                    </div>
                    <div class="mb-3">
                        <label for="editCategoryStatus" class="form-label">Status</label>
                        <select class="form-select" id="editCategoryStatus" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <iconify-icon icon="solar:check-circle-bold" class="me-2"></iconify-icon>
                        Update Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Real-time search functionality
document.getElementById('searchInput').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const categoryRows = document.querySelectorAll('.category-row');
    const noResults = document.getElementById('noResults');
    const categoryCount = document.getElementById('categoryCount');
    let visibleCount = 0;
    
    categoryRows.forEach(row => {
        const categoryInfo = row.querySelector('.category-info');
        const description = row.querySelector('.description-text');
        const slug = row.querySelector('code');
        
        // Get text content for search
        const categoryName = categoryInfo.querySelector('h6').textContent.toLowerCase();
        const categoryId = categoryInfo.querySelector('small').textContent.toLowerCase();
        const descriptionText = description.textContent.toLowerCase();
        const slugText = slug.textContent.toLowerCase();
        
        // Check if search term matches any of the content
        if (categoryName.includes(searchTerm) || 
            categoryId.includes(searchTerm) || 
            descriptionText.includes(searchTerm) || 
            slugText.includes(searchTerm)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update category count
    categoryCount.textContent = visibleCount;
    
    // Show/hide no results message
    if (visibleCount === 0) {
        noResults.style.display = 'block';
    } else {
        noResults.style.display = 'none';
    }
});

// Auto-generate slug from name
document.getElementById('addCategoryName').addEventListener('input', function() {
    const name = this.value;
    const slug = name.toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .trim();
    document.getElementById('addCategorySlug').value = slug;
});

// Edit category functionality
document.querySelectorAll('.edit-category-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.categoryId;
        const name = this.dataset.categoryName;
        const description = this.dataset.categoryDescription;
        const slug = this.dataset.categorySlug;
        const status = this.dataset.categoryStatus;
        
        document.getElementById('editCategoryId').value = id;
        document.getElementById('editCategoryName').value = name;
        document.getElementById('editCategoryDescription').value = description;
        document.getElementById('editCategorySlug').value = slug;
        document.getElementById('editCategoryStatus').value = status;
        
        const modal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
        modal.show();
    });
});

// Delete category functionality
document.querySelectorAll('.delete-category-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.categoryId;
        const name = this.dataset.categoryName;
        
        if (confirm(`Are you sure you want to delete the category "${name}"? This action cannot be undone.`)) {
            deleteCategory(id);
        }
    });
});

// Add category form submission
document.getElementById('addCategoryForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'add');
    
    fetch('category-handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Category added successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding the category.');
    });
});

// Edit category form submission
document.getElementById('editCategoryForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'edit');
    
    fetch('category-handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Category updated successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the category.');
    });
});

// Delete category function
function deleteCategory(id) {
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);
    
    fetch('category-handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Category deleted successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the category.');
    });
}
</script>

<?php
$content = ob_get_clean();
include 'includes/layout.php';
?> 