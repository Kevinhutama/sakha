<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'admin_portal');
define('DB_USER', 'root'); // Change this to your database username
define('DB_PASS', '');     // Change this to your database password

// Database Connection Class
class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                )
            );
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
            die();
        }
        
        return $this->conn;
    }
}

// Admin Authentication Class
class AdminAuth {
    private $conn;
    private $table = "admin";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Authenticate admin
    public function authenticate($email, $password) {
        $query = "SELECT id, email, password, first_name, last_name, role, status 
                  FROM " . $this->table . " 
                  WHERE email = :email AND status = 'active' 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $admin = $stmt->fetch();
            
            // Verify password
            if (password_verify($password, $admin['password'])) {
                // Update last login
                $this->updateLastLogin($admin['id']);
                
                // Return admin data (without password)
                unset($admin['password']);
                return $admin;
            }
        }
        
        return false;
    }

    // Update last login timestamp
    private function updateLastLogin($admin_id) {
        $query = "UPDATE " . $this->table . " 
                  SET last_login = CURRENT_TIMESTAMP 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $admin_id);
        $stmt->execute();
    }

    // Check if admin exists
    public function adminExists($email) {
        $query = "SELECT id FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    // Create new admin (for registration)
    public function createAdmin($email, $password, $first_name, $last_name, $role = 'admin') {
        if ($this->adminExists($email)) {
            return false;
        }

        $query = "INSERT INTO " . $this->table . " 
                  (email, password, first_name, last_name, role) 
                  VALUES (:email, :password, :first_name, :last_name, :role)";
        
        $stmt = $this->conn->prepare($query);
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':role', $role);
        
        return $stmt->execute();
    }
}

// Session Management Functions
function startSecureSession() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
        
        // Regenerate session ID for security
        if (!isset($_SESSION['initiated'])) {
            session_regenerate_id(true);
            $_SESSION['initiated'] = true;
        }
    }
}

function isLoggedIn() {
    startSecureSession();
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_email']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: authentication-login.php');
        exit();
    }
}

function logout() {
    startSecureSession();
    $_SESSION = array();
    session_destroy();
    header('Location: authentication-login.php');
    exit();
}

// Notification Management Class
class NotificationManager {
    private $conn;
    private $table = "notifications";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get unread notifications count for admin
    public function getUnreadCount($admin_id) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " 
                  WHERE admin_id = :admin_id AND status = 'unread'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }

    // Get recent notifications for admin
    public function getRecentNotifications($admin_id, $limit = 10) {
        $query = "SELECT id, title, message, type, status, action_url, created_at 
                  FROM " . $this->table . " 
                  WHERE admin_id = :admin_id 
                  ORDER BY created_at DESC 
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    // Mark notification as read
    public function markAsRead($notification_id, $admin_id) {
        $query = "UPDATE " . $this->table . " 
                  SET status = 'read', read_at = CURRENT_TIMESTAMP 
                  WHERE id = :id AND admin_id = :admin_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $notification_id, PDO::PARAM_INT);
        $stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    // Mark all notifications as read for admin
    public function markAllAsRead($admin_id) {
        $query = "UPDATE " . $this->table . " 
                  SET status = 'read', read_at = CURRENT_TIMESTAMP 
                  WHERE admin_id = :admin_id AND status = 'unread'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    // Add new notification
    public function addNotification($admin_id, $title, $message, $type = 'info', $action_url = null) {
        $query = "INSERT INTO " . $this->table . " 
                  (admin_id, title, message, type, action_url) 
                  VALUES (:admin_id, :title, :message, :type, :action_url)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':message', $message);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':action_url', $action_url);
        
        return $stmt->execute();
    }

    // Delete notification
    public function deleteNotification($notification_id, $admin_id) {
        $query = "DELETE FROM " . $this->table . " 
                  WHERE id = :id AND admin_id = :admin_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $notification_id, PDO::PARAM_INT);
        $stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    // Get notification type icon
    public function getTypeIcon($type) {
        $icons = [
            'info' => 'ti-info-circle',
            'success' => 'ti-check-circle',
            'warning' => 'ti-alert-triangle',
            'danger' => 'ti-alert-circle'
        ];
        
        return $icons[$type] ?? 'ti-bell';
    }

    // Get notification type color
    public function getTypeColor($type) {
        $colors = [
            'info' => 'primary',
            'success' => 'success',
            'warning' => 'warning',
            'danger' => 'danger'
        ];
        
        return $colors[$type] ?? 'primary';
    }
}

// Utility function to sanitize input
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?> 