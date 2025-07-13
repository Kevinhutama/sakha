-- Notifications Table DDL
-- Database: admin_portal

-- Drop table if exists (for clean setup)
DROP TABLE IF EXISTS notifications;

-- Create notifications table
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'danger') DEFAULT 'info',
    status ENUM('unread', 'read') DEFAULT 'unread',
    action_url VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    INDEX idx_admin_id (admin_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (admin_id) REFERENCES admin(id) ON DELETE CASCADE
);

-- Insert sample notifications (assuming admin IDs from admin table)
INSERT INTO notifications (admin_id, title, message, type, status, action_url) VALUES
(1, 'Welcome to Admin Portal', 'Welcome to your new admin dashboard. Get started by exploring the features.', 'success', 'unread', 'index.php'),
(1, 'System Update Available', 'A new system update is available. Please review and install when convenient.', 'info', 'unread', null),
(1, 'Security Alert', 'Multiple failed login attempts detected. Please review your security settings.', 'warning', 'unread', null),
(1, 'Backup Completed', 'Your daily backup has been completed successfully at 2:00 AM.', 'success', 'unread', null),
(1, 'Database Maintenance', 'Scheduled database maintenance is planned for this weekend.', 'info', 'unread', null),
(2, 'New User Registration', 'A new user has registered and is awaiting approval.', 'info', 'unread', null),
(2, 'Server Performance', 'Server performance is optimal. All systems running smoothly.', 'success', 'unread', null),
(2, 'Storage Warning', 'Storage space is running low. Please free up space or upgrade your plan.', 'warning', 'unread', null),
(3, 'Content Moderation', 'New content submissions are pending your review and approval.', 'info', 'unread', null),
(3, 'User Report', 'A user has submitted a report that requires moderator attention.', 'warning', 'unread', null);

-- Insert some read notifications for variety
INSERT INTO notifications (admin_id, title, message, type, status, action_url, read_at) VALUES
(1, 'Profile Updated', 'Your profile information has been successfully updated.', 'success', 'read', null, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(1, 'Password Changed', 'Your password was changed successfully.', 'success', 'read', null, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(2, 'Email Verified', 'Your email address has been verified successfully.', 'success', 'read', null, DATE_SUB(NOW(), INTERVAL 3 DAY));

-- Show the created table structure
DESCRIBE notifications;

-- Show sample data
SELECT id, admin_id, title, LEFT(message, 50) as message_preview, type, status, created_at 
FROM notifications 
ORDER BY created_at DESC 
LIMIT 10; 