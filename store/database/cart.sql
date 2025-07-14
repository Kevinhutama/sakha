-- Simplified Single Table Cart Design
-- Database: admin_portal (same as products database)
-- Created for Sakha E-commerce Platform

-- ========================================
-- DROP STATEMENTS FOR ROLLBACK
-- ========================================
DROP TABLE IF EXISTS cart_items;

-- ========================================
-- SINGLE TABLE DESIGN
-- ========================================

-- Cart Items Table (all-in-one design)
CREATE TABLE cart_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    
    -- User and Session Information
    user_id INT DEFAULT NULL,
    session_id VARCHAR(255) NOT NULL,
    
    -- Product Information (for navigation and display)
    product_id INT NOT NULL,
    product_slug VARCHAR(255) NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    
    -- Product Variants
    color_id INT DEFAULT NULL,
    color_name VARCHAR(100) DEFAULT NULL,
    color_code VARCHAR(7) DEFAULT NULL,
    size_id INT DEFAULT NULL,
    size_name VARCHAR(50) DEFAULT NULL,
    size_value VARCHAR(10) DEFAULT NULL,
    
    -- Quantity
    quantity INT NOT NULL DEFAULT 1,
    
    -- Base Pricing (stored at time of adding to cart)
    base_price DECIMAL(10,2) NOT NULL,
    discounted_price DECIMAL(10,2) DEFAULT NULL,
    
    -- Custom Name Options
    pouch_custom_enabled BOOLEAN DEFAULT FALSE,
    pouch_custom_name VARCHAR(255) DEFAULT NULL,
    pouch_custom_price DECIMAL(10,2) DEFAULT 0.00,
    
    sajadah_custom_enabled BOOLEAN DEFAULT FALSE,
    sajadah_custom_name VARCHAR(255) DEFAULT NULL,
    sajadah_custom_price DECIMAL(10,2) DEFAULT 0.00,
    
    -- Font Style Selection (for custom names)
    font_style VARCHAR(50) DEFAULT NULL,
    
    -- Calculated Pricing
    item_subtotal DECIMAL(10,2) NOT NULL,
    custom_additions_total DECIMAL(10,2) DEFAULT 0.00,
    item_total DECIMAL(10,2) NOT NULL,
    
    -- Cart Management
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expires_at TIMESTAMP DEFAULT (CURRENT_TIMESTAMP + INTERVAL 30 DAY),
    
    -- Foreign Keys
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (color_id) REFERENCES product_colors(id) ON DELETE SET NULL,
    FOREIGN KEY (size_id) REFERENCES product_sizes(id) ON DELETE SET NULL,
    
    -- Indexes for Performance
    INDEX idx_user_id (user_id),
    INDEX idx_session_id (session_id),
    INDEX idx_product_id (product_id),
    INDEX idx_is_active (is_active),
    INDEX idx_expires_at (expires_at),
    INDEX idx_created_at (created_at),
    INDEX idx_product_variant (product_id, color_id, size_id),
    
    -- Composite index for cart queries
    INDEX idx_cart_lookup (user_id, session_id, is_active),
    
    -- Unique constraint to prevent duplicate items in cart
    UNIQUE KEY unique_cart_item (user_id, session_id, product_id, color_id, size_id, pouch_custom_enabled, pouch_custom_name, sajadah_custom_enabled, sajadah_custom_name, font_style)
);


CREATE VIEW cart_summary AS
SELECT 
    ci.user_id,
    ci.session_id,
    COUNT(ci.id) as total_items,
    SUM(ci.quantity) as total_quantity,
    SUM(ci.item_subtotal) as subtotal,
    SUM(ci.custom_additions_total) as custom_additions,
    SUM(ci.item_total) as total_amount
FROM cart_items ci
WHERE ci.is_active = TRUE AND ci.expires_at > NOW()
GROUP BY ci.user_id, ci.session_id;

-- View for cart items with product details
CREATE VIEW cart_details AS
SELECT 
    ci.*,
    p.description as product_description,
    p.short_description as product_short_description,
    p.status as product_status,
    pc.color_name as color_display_name,
    pc.color_code as color_hex,
    ps.size_name as size_display_name,
    ps.size_value as size_code,
    -- Calculate if prices have changed
    CASE 
        WHEN ci.base_price != p.price THEN TRUE
        WHEN ci.discounted_price != p.discounted_price THEN TRUE
        WHEN ci.pouch_custom_price != p.pouch_custom_price THEN TRUE
        WHEN ci.sajadah_custom_price != p.sajadah_custom_price THEN TRUE
        ELSE FALSE
    END as price_changed,
    -- Get primary product image
    (SELECT pi.image_path 
     FROM product_images pi 
     WHERE pi.product_id = ci.product_id 
     AND pi.is_primary = TRUE 
     AND pi.status = 'active'
     LIMIT 1) as primary_image
FROM cart_items ci
LEFT JOIN products p ON ci.product_id = p.id
LEFT JOIN product_colors pc ON ci.color_id = pc.id
LEFT JOIN product_sizes ps ON ci.size_id = ps.id
WHERE ci.is_active = TRUE AND ci.expires_at > NOW();

