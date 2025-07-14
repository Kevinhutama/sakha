-- Product Thumbnails Table DDL
-- Database: admin_portal
-- Created for Sakha E-commerce Platform

-- Drop table if exists (for clean setup)
DROP TABLE IF EXISTS product_thumbnails;

-- Create product_thumbnails table
CREATE TABLE product_thumbnails (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    primary_image VARCHAR(500) NOT NULL,
    secondary_image VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_product_thumbnail (product_id),
    INDEX idx_product_id (product_id)
);

-- Show the created table structure
DESCRIBE product_thumbnails;

-- Sample query to verify table creation
SELECT COUNT(*) as table_exists FROM information_schema.tables 
WHERE table_schema = 'admin_portal' AND table_name = 'product_thumbnails'; 