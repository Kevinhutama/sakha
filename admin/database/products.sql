-- Products Management System DDL
-- Created for Sakha E-commerce Platform

-- ========================================
-- DROP STATEMENTS FOR ROLLBACK
-- ========================================
-- Run these statements to completely remove all product-related tables and data
-- WARNING: This will permanently delete all product data!

-- Drop view first
DROP VIEW IF EXISTS product_details;

-- Drop tables with foreign key dependencies first (child tables)
DROP TABLE IF EXISTS related_products;
DROP TABLE IF EXISTS product_images;
DROP TABLE IF EXISTS product_sizes;
DROP TABLE IF EXISTS product_colors;
DROP TABLE IF EXISTS product_categories;

-- Drop main tables last (parent tables)
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;

-- ========================================
-- CREATE STATEMENTS
-- ========================================

-- Categories Table
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    slug VARCHAR(100) UNIQUE NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Products Table
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    short_description TEXT,
    price DECIMAL(10,2) NOT NULL,
    discounted_price DECIMAL(10,2) DEFAULT NULL,
    custom_name_enabled BOOLEAN DEFAULT FALSE,
    pouch_custom_price DECIMAL(10,2) DEFAULT 5000.00,
    sajadah_custom_price DECIMAL(10,2) DEFAULT 5000.00,
    status ENUM('active', 'inactive') DEFAULT 'active',
    featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Product Categories (Many-to-Many relationship)
CREATE TABLE product_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    category_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    UNIQUE KEY unique_product_category (product_id, category_id)
);

-- Product Colors
CREATE TABLE product_colors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    color_name VARCHAR(100) NOT NULL,
    color_code VARCHAR(7) NOT NULL, -- RGB hex code (#FFFFFF)
    status ENUM('active', 'inactive') DEFAULT 'active',
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Product Sizes
CREATE TABLE product_sizes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    size_name VARCHAR(50) NOT NULL,
    size_value VARCHAR(10) NOT NULL, -- S, M, L, XL, etc.
    status ENUM('active', 'inactive') DEFAULT 'active',
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Product Images
CREATE TABLE product_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    color_id INT DEFAULT NULL,
    image_path VARCHAR(500) NOT NULL,
    alt_text VARCHAR(255),
    is_primary BOOLEAN DEFAULT FALSE,
    sort_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (color_id) REFERENCES product_colors(id) ON DELETE CASCADE
);

-- Related Products (Self-referencing)
CREATE TABLE related_products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    related_product_id INT NOT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (related_product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_related (product_id, related_product_id)
);

-- Indexes for better performance
CREATE INDEX idx_products_status ON products(status);
CREATE INDEX idx_product_colors_status ON product_colors(product_id, status);
CREATE INDEX idx_product_images_primary ON product_images(product_id, is_primary);
CREATE INDEX idx_categories_status ON categories(status);
CREATE INDEX idx_categories_slug ON categories(slug);

-- Views for easier querying
CREATE VIEW product_details AS
SELECT 
    p.*,
    GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ') as category_names,
    GROUP_CONCAT(DISTINCT pc.color_name ORDER BY pc.sort_order SEPARATOR ', ') as available_colors,
    COUNT(DISTINCT pi.id) as total_images
FROM products p
LEFT JOIN product_categories pc_rel ON p.id = pc_rel.product_id
LEFT JOIN categories c ON pc_rel.category_id = c.id AND c.status = 'active'
LEFT JOIN product_colors pc ON p.id = pc.product_id AND pc.status = 'active'
LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.status = 'active'
WHERE p.status = 'active'
GROUP BY p.id; 