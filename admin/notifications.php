<?php
require_once 'includes/config.php';

// Require authentication
requireLogin();

$page_title = "Notifications - Admin Portal";

// Get notifications data
$notifications = [];
$unreadCount = 0;
$totalCount = 0;

try {
    $database = new Database();
    $db = $database->getConnection();
    $notificationManager = new NotificationManager($db);
    
    $admin_id = $_SESSION['admin_id'];
    
    // Get filter parameter
    $filter = $_GET['filter'] ?? 'all'; // all, unread, read
    
    // Build query based on filter
    $query = "SELECT id, title, message, type, status, action_url, created_at 
              FROM notifications 
              WHERE admin_id = :admin_id";
    
    if ($filter === 'unread') {
        $query .= " AND status = 'unread'";
    } elseif ($filter === 'read') {
        $query .= " AND status = 'read'";
    }
    
    $query .= " ORDER BY created_at DESC LIMIT 50";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
    $stmt->execute();
    $notifications = $stmt->fetchAll();
    
    // Get counts
    $unreadCount = $notificationManager->getUnreadCount($admin_id);
    $totalQuery = "SELECT COUNT(*) as total FROM notifications WHERE admin_id = :admin_id";
    $totalStmt = $db->prepare($totalQuery);
    $totalStmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
    $totalStmt->execute();
    $totalResult = $totalStmt->fetch();
    $totalCount = $totalResult['total'] ?? 0;
    
} catch (Exception $e) {
    error_log('Notifications page error: ' . $e->getMessage());
    $error_message = 'Unable to load notifications.';
}

ob_start();
?>

<style>
/* Override container-fluid max-width */
.container-fluid {
    max-width: none !important;
    width: 100% !important;
}

.notifications-container {
    height: 100vh;
    overflow-y: auto;
}

.notification-item {
    border-left: 3px solid transparent;
    transition: all 0.3s ease;
}

.notification-item:hover {
    background-color: #f8f9fa !important;
}

.notification-item.unread {
    border-left-color: #5d87ff !important;
    background-color: #f8f9fa;
}
</style>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-semibold mb-1">
            <iconify-icon icon="solar:bell-bold" class="me-2"></iconify-icon>
            Notifications
        </h4>
        <p class="text-muted mb-0">
            <?php echo $unreadCount; ?> unread of <?php echo $totalCount; ?> total notifications
        </p>
    </div>
    <div class="d-flex gap-2">
        <?php if ($unreadCount > 0): ?>
            <button class="btn btn-primary btn-sm" onclick="markAllAsRead()">
                <iconify-icon icon="solar:check-circle-bold" class="me-1"></iconify-icon>
                Mark All Read
            </button>
        <?php endif; ?>
        <div class="btn-group" role="group">
            <a href="?filter=all" class="btn btn-outline-secondary btn-sm <?php echo $filter === 'all' ? 'active' : ''; ?>">
                All
            </a>
            <a href="?filter=unread" class="btn btn-outline-secondary btn-sm <?php echo $filter === 'unread' ? 'active' : ''; ?>">
                Unread (<?php echo $unreadCount; ?>)
            </a>
            <a href="?filter=read" class="btn btn-outline-secondary btn-sm <?php echo $filter === 'read' ? 'active' : ''; ?>">
                Read
            </a>
        </div>
    </div>
</div>

<!-- Notifications Container -->
<div class="notifications-container bg-white rounded-3 border">
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger m-3">
            <iconify-icon icon="solar:danger-circle-bold" class="me-2"></iconify-icon>
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php elseif (empty($notifications)): ?>
        <div class="text-center py-5">
            <iconify-icon icon="solar:bell-off-linear" class="display-4 text-muted"></iconify-icon>
            <h5 class="mt-3 text-muted">
                <?php if ($filter === 'unread'): ?>
                    No unread notifications
                <?php elseif ($filter === 'read'): ?>
                    No read notifications
                <?php else: ?>
                    No notifications yet
                <?php endif; ?>
            </h5>
            <p class="text-muted">You're all caught up!</p>
        </div>
    <?php else: ?>
        <div class="list-group list-group-flush">
                        <?php foreach ($notifications as $notification): ?>
                            <div class="list-group-item list-group-item-action notification-item <?php echo $notification['status'] === 'unread' ? 'unread' : ''; ?>"
                                 data-notification-id="<?php echo $notification['id']; ?>"
                                 onclick="handleNotificationClick(<?php echo $notification['id']; ?>, '<?php echo $notification['action_url']; ?>')"
                                 style="cursor: pointer;">
                                <div class="d-flex w-100 justify-content-between align-items-start">
                                    <div class="d-flex align-items-start flex-grow-1">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="rounded-circle bg-<?php echo $notificationManager->getTypeColor($notification['type']); ?> 
                                                        bg-opacity-10 p-2 d-flex align-items-center justify-content-center"
                                                 style="width: 40px; height: 40px;">
                                                <i class="ti <?php echo $notificationManager->getTypeIcon($notification['type']); ?> 
                                                   text-<?php echo $notificationManager->getTypeColor($notification['type']); ?> fs-5"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 <?php echo $notification['status'] === 'unread' ? 'fw-bold' : ''; ?>">
                                                <?php echo htmlspecialchars($notification['title']); ?>
                                                <?php if ($notification['status'] === 'unread'): ?>
                                                    <span class="badge bg-primary ms-2">New</span>
                                                <?php endif; ?>
                                            </h6>
                                            <p class="mb-2 text-muted">
                                                <?php echo htmlspecialchars($notification['message']); ?>
                                            </p>
                                            <small class="text-muted">
                                                <i class="ti ti-clock me-1"></i>
                                                <?php echo date('M j, Y \a\t g:i A', strtotime($notification['created_at'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-link text-muted p-1" type="button" 
                                                data-bs-toggle="dropdown" aria-expanded="false"
                                                onclick="event.stopPropagation();">
                                            <i class="ti ti-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <?php if ($notification['status'] === 'unread'): ?>
                                                <li>
                                                    <button class="dropdown-item" 
                                                            onclick="event.stopPropagation(); markAsRead(<?php echo $notification['id']; ?>)">
                                                        <i class="ti ti-check me-2"></i>Mark as Read
                                                    </button>
                                                </li>
                                            <?php endif; ?>
                                            <li>
                                                <button class="dropdown-item text-danger" 
                                                        onclick="event.stopPropagation(); deleteNotification(<?php echo $notification['id']; ?>)">
                                                    <i class="ti ti-trash me-2"></i>Delete
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
</div>

<script>
// Handle notification click
function handleNotificationClick(notificationId, actionUrl) {
    // Mark notification as read via AJAX
    fetch('notifications-handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=mark_read&notification_id=' + notificationId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update the notification item styling
            const notificationItem = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (notificationItem) {
                notificationItem.classList.remove('bg-light', 'border-start', 'border-primary', 'border-3');
                const newBadge = notificationItem.querySelector('.badge');
                if (newBadge) newBadge.remove();
                const title = notificationItem.querySelector('h6');
                if (title) title.classList.remove('fw-bold');
            }
            
            // Navigate to action URL if provided
            if (actionUrl && actionUrl !== 'null' && actionUrl !== null) {
                window.location.href = actionUrl;
            }
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
    });
}

// Mark single notification as read
function markAsRead(notificationId) {
    fetch('notifications-handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=mark_read&notification_id=' + notificationId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Refresh to update the display
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
    });
}

// Mark all notifications as read
function markAllAsRead() {
    if (confirm('Mark all notifications as read?')) {
        fetch('notifications-handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=mark_all_read'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Refresh to update the display
            }
        })
        .catch(error => {
            console.error('Error marking all notifications as read:', error);
        });
    }
}

// Delete notification
function deleteNotification(notificationId) {
    if (confirm('Delete this notification?')) {
        fetch('notifications-handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=delete_notification&notification_id=' + notificationId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Refresh to update the display
            }
        })
        .catch(error => {
            console.error('Error deleting notification:', error);
        });
    }
}
</script>

<?php
$content = ob_get_clean();
include 'includes/layout.php';
?> 