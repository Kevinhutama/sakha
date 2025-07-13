-- Admin Authentication Table DDL
-- Database: admin_portal

-- Drop table if exists (for clean setup)
DROP TABLE IF EXISTS admin;

-- Create admin table for authentication
CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    role ENUM('admin', 'super_admin', 'moderator') DEFAULT 'admin',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_status (status)
);

-- Insert sample admin accounts
-- Password: 'password' (hashed using PHP password_hash())
INSERT INTO admin (email, password, first_name, last_name, role, status) VALUES
('admin@growthive.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'super_admin', 'active'),
('manager@growthive.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Manager', 'User', 'admin', 'active'),
('moderator@growthive.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Moderator', 'User', 'moderator', 'active');

-- Show the created table structure
DESCRIBE admin;

-- Show inserted data
SELECT id, email, first_name, last_name, role, status, created_at FROM admin; 