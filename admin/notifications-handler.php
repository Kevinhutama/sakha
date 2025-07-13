<?php
require_once 'includes/config.php';

// Start session and check if user is logged in
startSecureSession();

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$response = ['success' => false, 'message' => 'Invalid request'];

try {
    // Create database connection
    $database = new Database();
    $db = $database->getConnection();
    $notificationManager = new NotificationManager($db);
    
    $admin_id = $_SESSION['admin_id'];
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'mark_read':
            $notification_id = $_POST['notification_id'] ?? 0;
            
            if ($notification_id > 0) {
                if ($notificationManager->markAsRead($notification_id, $admin_id)) {
                    $response = [
                        'success' => true, 
                        'message' => 'Notification marked as read',
                        'count' => $notificationManager->getUnreadCount($admin_id)
                    ];
                } else {
                    $response = ['success' => false, 'message' => 'Failed to mark notification as read'];
                }
            } else {
                $response = ['success' => false, 'message' => 'Invalid notification ID'];
            }
            break;
            
        case 'mark_all_read':
            if ($notificationManager->markAllAsRead($admin_id)) {
                $response = [
                    'success' => true, 
                    'message' => 'All notifications marked as read',
                    'count' => 0
                ];
            } else {
                $response = ['success' => false, 'message' => 'Failed to mark all notifications as read'];
            }
            break;
            
        case 'get_count':
            $count = $notificationManager->getUnreadCount($admin_id);
            $response = [
                'success' => true, 
                'count' => $count,
                'message' => 'Count retrieved successfully'
            ];
            break;
            
        case 'get_notifications':
            $limit = $_POST['limit'] ?? 10;
            $notifications = $notificationManager->getRecentNotifications($admin_id, $limit);
            $response = [
                'success' => true, 
                'notifications' => $notifications,
                'count' => $notificationManager->getUnreadCount($admin_id),
                'message' => 'Notifications retrieved successfully'
            ];
            break;
            
        case 'delete_notification':
            $notification_id = $_POST['notification_id'] ?? 0;
            
            if ($notification_id > 0) {
                if ($notificationManager->deleteNotification($notification_id, $admin_id)) {
                    $response = [
                        'success' => true, 
                        'message' => 'Notification deleted',
                        'count' => $notificationManager->getUnreadCount($admin_id)
                    ];
                } else {
                    $response = ['success' => false, 'message' => 'Failed to delete notification'];
                }
            } else {
                $response = ['success' => false, 'message' => 'Invalid notification ID'];
            }
            break;
            
        default:
            $response = ['success' => false, 'message' => 'Unknown action'];
            break;
    }
    
} catch (Exception $e) {
    error_log('Notification handler error: ' . $e->getMessage());
    $response = ['success' => false, 'message' => 'Server error occurred'];
}

echo json_encode($response);
?> 