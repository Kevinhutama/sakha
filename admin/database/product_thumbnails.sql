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

-- Insert dummy thumbnail data for existing products
INSERT INTO product_thumbnails (product_id, primary_image, secondary_image) VALUES
(1, 'images/products/blossom - 1.webp', 'images/products/bolossom - 2.webp'),
(2, 'images/products/premium - 1.webp', 'images/products/premium - 2.webp'),
(3, 'images/products/azhara - 1.webp', NULL),
(4, 'images/products/premium - 2.webp', NULL),
(5, 'images/products/bolossom - 2.webp', 'images/products/blossom - 1.webp'),
(6, 'images/products/blossom - 1.webp', NULL);

