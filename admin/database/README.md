# Database Authentication Setup

This document explains how to set up the database-driven authentication system for the MaterialM Admin Portal.

## Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- XAMPP or similar local development environment

## Database Setup

### 1. Create Database and Tables

1. Open your MySQL client (phpMyAdmin, MySQL Workbench, or command line)
2. Run the SQL script located in `database/admin.sql`
3. Run the SQL script located in `database/notifications.sql`
4. This will create:
   - `admin_portal` database
   - `admin` table with proper structure
   - `notifications` table with notification system
   - Sample admin accounts and notifications for testing

### 2. Configure Database Connection

1. Edit `includes/config.php` and update the database credentials:
   ```php
   define('DB_HOST', 'localhost');     // Your database host
   define('DB_NAME', 'admin_portal');  // Database name
   define('DB_USER', 'root');          // Your MySQL username
   define('DB_PASS', '');              // Your MySQL password
   ```

### 3. Test Database Connection

1. Try accessing the login page: `authentication-login.php`
2. If there are connection errors, check your database credentials and ensure MySQL is running

## Default Test Accounts

The system comes with three pre-configured admin accounts:

| Email | Password | Role | Status |
|-------|----------|------|--------|
| admin@growthive.com | password | super_admin | active |
| manager@growthive.com | password | admin | active |
| moderator@growthive.com | password | moderator | active |

## Features

### Secure Authentication
- Passwords are hashed using PHP's `password_hash()` function
- Protection against SQL injection using prepared statements
- Session management with secure session handling
- "Remember Me" functionality with secure cookies

### Admin Management
- Admin roles (super_admin, admin, moderator)
- Admin status management (active, inactive, suspended)
- Last login tracking
- Input validation and sanitization

### Notification System
- Real-time notification display in header
- Unread notification count badge
- Multiple notification types (info, success, warning, danger)
- Mark notifications as read individually or all at once
- Full notifications page with filtering
- Notification deletion functionality
- Action URLs for clickable notifications

### Security Features
- Session regeneration for security
- Secure cookie handling
- Input sanitization
- Error logging
- CSRF protection ready

## File Structure

```
includes/
├── config.php          # Database configuration and authentication classes
├── auth_layout.php     # Layout for authentication pages
├── header.php          # Header component with notification system
├── sidebar.php         # Sidebar component
└── layout.php          # Main layout component

database/
├── admin.sql           # Admin table schema and sample data
├── notifications.sql   # Notifications table schema and sample data
└── README.md           # This file

notifications-handler.php  # AJAX handler for notification operations
notifications.php          # Full notifications management page

utils/
└── password_generator.php  # Utility to generate password hashes
```

## Usage

### Login Process
1. Admin enters email and password
2. System validates credentials against database
3. If valid, admin session is created
4. Admin is redirected to dashboard
5. If invalid, error message is shown

### Protected Pages
- All pages that require authentication should include:
  ```php
  require_once 'includes/config.php';
  requireLogin();
  ```

### Logout
- Access `logout.php` to end user session
- Or call the `logout()` function directly

### Notification System Usage
1. **View Notifications**: Click the bell icon in the header
2. **Notification Count**: Red badge shows unread count
3. **Mark as Read**: Click on individual notifications
4. **Mark All Read**: Use the button in dropdown or notifications page
5. **Full View**: Click "View All Notifications" for complete list
6. **Filter**: View all, unread only, or read only notifications
7. **Delete**: Remove notifications using the dropdown menu

## Troubleshooting

### Database Connection Issues
- Verify MySQL is running
- Check database credentials in `config.php`
- Ensure `admin_portal` database exists
- Verify user has proper permissions

### Login Issues
- Check if admin table exists and has data
- Verify passwords are properly hashed
- Check PHP error logs for detailed error messages

### Session Issues
- Ensure sessions are enabled in PHP
- Check file permissions for session storage
- Verify session cookie settings

## Security Considerations

1. **Change Default Credentials**: Update default passwords before production
2. **Database Permissions**: Use dedicated database user with minimal permissions
3. **HTTPS**: Always use HTTPS in production
4. **Error Logging**: Monitor error logs for security issues
5. **Password Policy**: Implement strong password requirements
6. **Rate Limiting**: Consider implementing login rate limiting

## Adding New Admins

### Via Database
```sql
INSERT INTO admin (email, password, first_name, last_name, role, status) 
VALUES ('newadmin@growthive.com', '$2y$10$hashed_password_here', 'First', 'Last', 'admin', 'active');
```

## Creating Notifications

### Via Database
```sql
INSERT INTO notifications (admin_id, title, message, type, status, action_url) 
VALUES (1, 'New Notification', 'This is a test notification', 'info', 'unread', 'index.php');
```

### Via PHP Code
```php
// Get database connection
$database = new Database();
$db = $database->getConnection();
$notificationManager = new NotificationManager($db);

// Add notification
$notificationManager->addNotification(
    $admin_id,           // Admin ID
    'Notification Title', // Title
    'Notification message content', // Message
    'success',           // Type: info, success, warning, danger
    'index.php'          // Optional action URL
);
```

### Via Password Generator
1. Access `utils/password_generator.php` in your browser
2. Enter the desired password
3. Copy the generated hash
4. Use the hash in your SQL INSERT statement

## Customization

### Adding New Admin Fields
1. Add columns to the `admin` table
2. Update the `AdminAuth` class methods
3. Modify the login form as needed

### Custom Authentication Logic
- Modify the `authenticate()` method in the `AdminAuth` class
- Add additional validation rules
- Implement custom session handling

## Support

For issues or questions regarding the authentication system, please check:
1. PHP error logs
2. MySQL error logs
3. Browser developer console
4. This documentation 