-- Carousel Images Table DDL
-- Database: admin_portal

-- Drop table if exists (for clean setup)
DROP TABLE IF EXISTS carousel_images;

-- Create carousel_images table
CREATE TABLE carousel_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    web_image_path VARCHAR(500) NOT NULL,
    mobile_image_path VARCHAR(500) NULL,
    button_text VARCHAR(100) DEFAULT 'Shop Now',
    button_url VARCHAR(500) DEFAULT '#',
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_display_order (display_order),
    INDEX idx_is_active (is_active)
);

-- Insert initial carousel data based on current images
INSERT INTO carousel_images (title, description, web_image_path, mobile_image_path, button_text, button_url, display_order, is_active) VALUES
('Sajadah with Alquran', 'Sacred bundle for spiritual practice', 'images/carousel/banner-image.jpg', 'images/carousel/banner-image.jpg', 'Shop Now', 'single-product.html', 1, TRUE),
('Sajadah with Perfume', 'Prayer mat with divine fragrance & spiritual comfort with blessed scents', 'images/carousel/banner-image1.jpg', 'images/carousel/banner-image1.jpg', 'Shop Now', 'single-product.html', 2, TRUE),
('Shell Shape Decor', 'Buy this beautiful unique pieces of shell shape vase decors for your plants of room.', 'images/carousel/banner-image2.jpg', 'images/carousel/banner-image2.jpg', 'Shop Now', 'single-product.html', 3, TRUE);

-- Show the created table structure
DESCRIBE carousel_images;

-- Show inserted data
SELECT id, title, description, web_image_path, mobile_image_path, button_text, button_url, display_order, is_active, created_at FROM carousel_images ORDER BY display_order; 