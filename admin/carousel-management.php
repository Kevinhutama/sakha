<?php
require_once 'includes/config.php';

// Require authentication
requireLogin();

$page_title = "Carousel Management - Admin Portal";
$additional_js = '
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Handle form submission
function handleCarouselForm(formId) {
    const form = document.getElementById(formId);
    const formData = new FormData(form);
    
    fetch("carousel-handler.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: "Success!",
                text: data.message,
                icon: "success",
                confirmButtonText: "OK"
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                title: "Error!",
                text: data.message,
                icon: "error",
                confirmButtonText: "OK"
            });
        }
    })
    .catch(error => {
        console.error("Error:", error);
        Swal.fire({
            title: "Error!",
            text: "An error occurred while processing the request.",
            icon: "error",
            confirmButtonText: "OK"
        });
    });
}

// Delete carousel item
function deleteCarouselItem(id) {
    Swal.fire({
        title: "Are you sure?",
        text: "This action cannot be undone!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, delete it!"
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append("action", "delete");
            formData.append("id", id);
            
            fetch("carousel-handler.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: "Deleted!",
                        text: data.message,
                        icon: "success",
                        confirmButtonText: "OK"
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: "Error!",
                        text: data.message,
                        icon: "error",
                        confirmButtonText: "OK"
                    });
                }
            });
        }
    });
}

// Toggle active status
function toggleActiveStatus(id, currentStatus) {
    const newStatus = currentStatus === "1" ? "0" : "1";
    const formData = new FormData();
    formData.append("action", "toggle_status");
    formData.append("id", id);
    formData.append("is_active", newStatus);
    
    fetch("carousel-handler.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            Swal.fire({
                title: "Error!",
                text: data.message,
                icon: "error",
                confirmButtonText: "OK"
            });
        }
    });
}

// Preview image before upload
function previewImage(input, previewId) {
    const file = input.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(previewId).src = e.target.result;
            document.getElementById(previewId).style.display = "block";
        };
        reader.readAsDataURL(file);
    }
}
</script>
';

// Get carousel images from database
try {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT * FROM carousel_images ORDER BY display_order ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $carousel_images = $stmt->fetchAll();
} catch (Exception $e) {
    $carousel_images = [];
    $error_message = "Error fetching carousel images: " . $e->getMessage();
}

ob_start();
?>

<style>
/* Override container-fluid max-width */
.container-fluid {
    max-width: none !important;
    width: 100% !important;
}

.carousel-image-preview {
    width: 60px;
    height: 40px;
    object-fit: cover;
    border-radius: 4px;
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

.action-buttons .btn {
    padding: 4px 8px;
    margin: 0 2px;
}
</style>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-semibold mb-1">
            <iconify-icon icon="solar:widget-5-bold" class="me-2"></iconify-icon>
            Carousel Images Management
        </h4>
        <p class="text-muted mb-0">Manage carousel images for your website</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCarouselModal">
        <iconify-icon icon="solar:add-circle-bold" class="me-2"></iconify-icon>
        Add New Carousel Image
    </button>
</div>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger mb-4" role="alert">
        <iconify-icon icon="solar:danger-circle-bold" class="me-2"></iconify-icon>
        <?php echo htmlspecialchars($error_message); ?>
    </div>
<?php endif; ?>

<!-- Carousel Images Table -->
<div class="table-responsive bg-white rounded-3 border">
                        <table class="table table-striped table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Preview</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($carousel_images)): ?>
                                    <?php foreach ($carousel_images as $image): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-secondary"><?php echo $image['display_order']; ?></span>
                                            </td>
                                            <td>
                                                                                <img src="../store/<?php echo htmlspecialchars($image['web_image_path']); ?>" 
                                     alt="<?php echo htmlspecialchars($image['title']); ?>" 
                                     class="carousel-image-preview">
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($image['title']); ?></strong>
                                            </td>
                                            <td>
                                                <p class="mb-0 text-truncate" style="max-width: 200px;">
                                                    <?php echo htmlspecialchars($image['description']); ?>
                                                </p>
                                            </td>
                                            <td>
                                                <?php if ($image['is_active']): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editCarouselModal<?php echo $image['id']; ?>"
                                                            title="Edit">
                                                        <iconify-icon icon="solar:pen-bold"></iconify-icon>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-warning" 
                                                            onclick="toggleActiveStatus(<?php echo $image['id']; ?>, '<?php echo $image['is_active']; ?>')"
                                                            title="Toggle Status">
                                                        <iconify-icon icon="solar:power-bold"></iconify-icon>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteCarouselItem(<?php echo $image['id']; ?>)"
                                                            title="Delete">
                                                        <iconify-icon icon="solar:trash-bin-trash-bold"></iconify-icon>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            <p class="mb-0">No carousel images found.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
</div>

<!-- Add Carousel Modal -->
<div class="modal fade" id="addCarouselModal" tabindex="-1" aria-labelledby="addCarouselModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCarouselModalLabel">Add New Carousel Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addCarouselForm" onsubmit="event.preventDefault(); handleCarouselForm('addCarouselForm');">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="display_order" class="form-label">Display Order <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="display_order" name="display_order" min="1" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="web_image" class="form-label">Web Image <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="web_image" name="web_image" 
                                       accept="image/*" required onchange="previewImage(this, 'webPreview')">
                                <img id="webPreview" class="mt-2 img-thumbnail" style="display: none; max-width: 200px;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="mobile_image" class="form-label">Mobile Image (Optional)</label>
                                <input type="file" class="form-control" id="mobile_image" name="mobile_image" 
                                       accept="image/*" onchange="previewImage(this, 'mobilePreview')">
                                <img id="mobilePreview" class="mt-2 img-thumbnail" style="display: none; max-width: 200px;">
                                <small class="form-text text-muted">If not provided, web image will be used for mobile</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="button_text" class="form-label">Button Text</label>
                                <input type="text" class="form-control" id="button_text" name="button_text" value="Shop Now">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="button_url" class="form-label">Button URL</label>
                                <input type="text" class="form-control" id="button_url" name="button_url" value="#">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                            <label class="form-check-label" for="is_active">
                                Active
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Carousel Image</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Carousel Modals -->
<?php if (!empty($carousel_images)): ?>
    <?php foreach ($carousel_images as $image): ?>
        <div class="modal fade" id="editCarouselModal<?php echo $image['id']; ?>" tabindex="-1" aria-labelledby="editCarouselModalLabel<?php echo $image['id']; ?>" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editCarouselModalLabel<?php echo $image['id']; ?>">Edit Carousel Image</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="editCarouselForm<?php echo $image['id']; ?>" onsubmit="event.preventDefault(); handleCarouselForm('editCarouselForm<?php echo $image['id']; ?>');">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id" value="<?php echo $image['id']; ?>">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_title_<?php echo $image['id']; ?>" class="form-label">Title <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="edit_title_<?php echo $image['id']; ?>" name="title" value="<?php echo htmlspecialchars($image['title']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_display_order_<?php echo $image['id']; ?>" class="form-label">Display Order <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="edit_display_order_<?php echo $image['id']; ?>" name="display_order" value="<?php echo $image['display_order']; ?>" min="1" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="edit_description_<?php echo $image['id']; ?>" class="form-label">Description</label>
                                <textarea class="form-control" id="edit_description_<?php echo $image['id']; ?>" name="description" rows="3"><?php echo htmlspecialchars($image['description']); ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_web_image_<?php echo $image['id']; ?>" class="form-label">Web Image</label>
                                        <input type="file" class="form-control" id="edit_web_image_<?php echo $image['id']; ?>" name="web_image" 
                                               accept="image/*" onchange="previewImage(this, 'editWebPreview<?php echo $image['id']; ?>')">
                                        <img id="editWebPreview<?php echo $image['id']; ?>" 
                                             src="../store/<?php echo htmlspecialchars($image['web_image_path']); ?>" 
                                             class="mt-2 img-thumbnail" style="max-width: 200px;">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_mobile_image_<?php echo $image['id']; ?>" class="form-label">Mobile Image (Optional)</label>
                                        <input type="file" class="form-control" id="edit_mobile_image_<?php echo $image['id']; ?>" name="mobile_image" 
                                               accept="image/*" onchange="previewImage(this, 'editMobilePreview<?php echo $image['id']; ?>')">
                                        <img id="editMobilePreview<?php echo $image['id']; ?>" 
                                             src="../store/<?php echo htmlspecialchars($image['mobile_image_path'] ?: $image['web_image_path']); ?>" 
                                             class="mt-2 img-thumbnail" style="max-width: 200px;">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_button_text_<?php echo $image['id']; ?>" class="form-label">Button Text</label>
                                        <input type="text" class="form-control" id="edit_button_text_<?php echo $image['id']; ?>" name="button_text" value="<?php echo htmlspecialchars($image['button_text']); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_button_url_<?php echo $image['id']; ?>" class="form-label">Button URL</label>
                                        <input type="text" class="form-control" id="edit_button_url_<?php echo $image['id']; ?>" name="button_url" value="<?php echo htmlspecialchars($image['button_url']); ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_is_active_<?php echo $image['id']; ?>" name="is_active" value="1" <?php echo $image['is_active'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="edit_is_active_<?php echo $image['id']; ?>">
                                        Active
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Carousel Image</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php
$content = ob_get_clean();
require_once 'includes/layout.php';
?> 