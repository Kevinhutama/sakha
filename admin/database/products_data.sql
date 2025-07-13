-- Product Management System DML - Dummy Data
-- Created for Sakha E-commerce Platform

-- Insert Categories
INSERT INTO categories (name, description, slug, status) VALUES
('Sajadah', 'Traditional Islamic prayer mats', 'sajadah', 'active'),
('Prayer Mat', 'Premium prayer mats for daily use', 'prayer-mat', 'active'),
('Islamic', 'Islamic religious accessories', 'islamic', 'active'),
('Premium Collection', 'High-end prayer accessories', 'premium-collection', 'active'),
('Blossom Series', 'Floral pattern prayer mats', 'blossom-series', 'active'),
('Travel Size', 'Compact prayer mats for travel', 'travel-size', 'active');

-- Insert Products
INSERT INTO products (name, slug, description, short_description, sku, price, discounted_price, stock_quantity, custom_name_enabled, pouch_custom_price, sajadah_custom_price, status, featured, meta_title, meta_description) VALUES
('Blossom Prayer Collection', 'blossom-prayer-collection', 'Beautiful handcrafted prayer mat made with premium materials. Perfect for daily prayers with elegant floral patterns that bring peace and tranquility to your spiritual moments. The soft texture and durable construction ensure comfort and longevity for daily use.', 'Beautiful handcrafted prayer mat made with premium materials. Perfect for daily prayers with elegant floral patterns.', 'BPC001', 150000.00, 100000.00, 15, TRUE, 5000.00, 5000.00, 'active', TRUE, 'Blossom Prayer Collection - Premium Islamic Prayer Mat', 'Handcrafted prayer mat with elegant floral patterns. Premium materials for comfort and durability.'),

('Premium Prayer Mat', 'premium-prayer-mat', 'Luxurious prayer mat crafted from the finest materials. Features intricate designs and superior comfort for enhanced spiritual experience. Durable and easy to maintain.', 'Luxurious prayer mat crafted from the finest materials with intricate designs.', 'PPM001', 125000.00, NULL, 10, FALSE, 0.00, 0.00, 'active', TRUE, 'Premium Prayer Mat - Luxury Islamic Sajadah', 'Luxurious prayer mat with intricate designs and superior comfort.'),

('Azhara Sacred Mat', 'azhara-sacred-mat', 'Sacred prayer mat with traditional Islamic patterns. Made with high-quality materials that provide comfort during prayer. Perfect for personal use or as a gift.', 'Sacred prayer mat with traditional Islamic patterns and high-quality materials.', 'ASM001', 135000.00, NULL, 8, TRUE, 7500.00, 7500.00, 'active', FALSE, 'Azhara Sacred Mat - Traditional Islamic Prayer Mat', 'Sacred prayer mat with traditional Islamic patterns and premium comfort.'),

('Premium Black Mat', 'premium-black-mat', 'Elegant black prayer mat with subtle patterns. Perfect for those who prefer minimalist design. Made with premium materials for durability and comfort.', 'Elegant black prayer mat with subtle patterns and minimalist design.', 'PBM001', 150000.00, NULL, 5, FALSE, 0.00, 0.00, 'active', FALSE, 'Premium Black Mat - Elegant Islamic Prayer Mat', 'Elegant black prayer mat with subtle patterns and minimalist design.'),

('Blossom Sajadah Set', 'blossom-sajadah-set', 'Complete sajadah set with matching accessories. Includes prayer mat, pouch, and prayer beads. Perfect for complete spiritual experience.', 'Complete sajadah set with matching accessories including prayer mat and pouch.', 'BSS001', 115000.00, NULL, 12, TRUE, 5000.00, 5000.00, 'active', TRUE, 'Blossom Sajadah Set - Complete Islamic Prayer Set', 'Complete sajadah set with matching accessories for spiritual experience.'),

('Travel Prayer Mat', 'travel-prayer-mat', 'Compact and lightweight prayer mat perfect for travel. Folds easily and fits in carry-on luggage. Durable material that maintains quality even with frequent use.', 'Compact and lightweight prayer mat perfect for travel.', 'TPM001', 85000.00, 75000.00, 20, FALSE, 0.00, 0.00, 'active', FALSE, 'Travel Prayer Mat - Compact Islamic Sajadah', 'Compact and lightweight prayer mat perfect for travel and portability.');

-- Insert Product Categories Relationships
INSERT INTO product_categories (product_id, category_id) VALUES
(1, 1), (1, 2), (1, 3), (1, 5), -- Blossom Prayer Collection
(2, 1), (2, 2), (2, 4), -- Premium Prayer Mat
(3, 1), (3, 2), (3, 3), -- Azhara Sacred Mat
(4, 1), (4, 2), (4, 4), -- Premium Black Mat
(5, 1), (5, 2), (5, 3), (5, 5), -- Blossom Sajadah Set
(6, 1), (6, 2), (6, 6); -- Travel Prayer Mat

-- Insert Product Colors
INSERT INTO product_colors (product_id, color_name, color_code, status, sort_order) VALUES
(1, 'Pink', '#FF69B4', 'active', 1),
(1, 'Blue', '#4169E1', 'active', 2),
(1, 'Green', '#228B22', 'active', 3),
(2, 'Burgundy', '#800020', 'active', 1),
(2, 'Navy Blue', '#000080', 'active', 2),
(2, 'Forest Green', '#013220', 'active', 3),
(3, 'Turquoise', '#40E0D0', 'active', 1),
(3, 'Gold', '#FFD700', 'active', 2),
(3, 'Silver', '#C0C0C0', 'active', 3),
(4, 'Black', '#000000', 'active', 1),
(4, 'Dark Gray', '#2F4F4F', 'active', 2),
(5, 'Pink', '#FF69B4', 'active', 1),
(5, 'Purple', '#800080', 'active', 2),
(5, 'Lavender', '#E6E6FA', 'active', 3),
(6, 'Brown', '#8B4513', 'active', 1),
(6, 'Tan', '#D2B48C', 'active', 2),
(6, 'Beige', '#F5F5DC', 'active', 3);

-- Insert Product Sizes
INSERT INTO product_sizes (product_id, size_name, size_value, status, sort_order) VALUES
(1, 'Small', 'S', 'active', 1),
(1, 'Medium', 'M', 'active', 2),
(1, 'Large', 'L', 'active', 3),
(2, 'Small', 'S', 'active', 1),
(2, 'Medium', 'M', 'active', 2),
(2, 'Large', 'L', 'active', 3),
(3, 'Small', 'S', 'active', 1),
(3, 'Medium', 'M', 'active', 2),
(3, 'Large', 'L', 'active', 3),
(4, 'Medium', 'M', 'active', 1),
(4, 'Large', 'L', 'active', 2),
(5, 'Small', 'S', 'active', 1),
(5, 'Medium', 'M', 'active', 2),
(5, 'Large', 'L', 'active', 3),
(6, 'One Size', 'OS', 'active', 1);

-- Insert Product Images
INSERT INTO product_images (product_id, color_id, image_path, alt_text, is_primary, sort_order, status) VALUES
-- Blossom Prayer Collection (Pink)
(1, 1, 'images/products/blossom - 1.webp', 'Blossom Prayer Collection Pink', TRUE, 1, 'active'),
(1, 1, 'images/products/bolossom - 2.webp', 'Blossom Prayer Collection Pink Detail', FALSE, 2, 'active'),
(1, 1, 'images/products/premium - 1.webp', 'Blossom Prayer Collection Pink Side', FALSE, 3, 'active'),

-- Premium Prayer Mat (Burgundy)
(2, 4, 'images/products/premium - 1.webp', 'Premium Prayer Mat Burgundy', TRUE, 1, 'active'),
(2, 4, 'images/products/premium - 2.webp', 'Premium Prayer Mat Burgundy Detail', FALSE, 2, 'active'),

-- Azhara Sacred Mat (Turquoise)
(3, 7, 'images/products/azhara - 1.webp', 'Azhara Sacred Mat Turquoise', TRUE, 1, 'active'),

-- Premium Black Mat (Black)
(4, 10, 'images/products/premium - 2.webp', 'Premium Black Mat', TRUE, 1, 'active'),

-- Blossom Sajadah Set (Pink)
(5, 12, 'images/products/bolossom - 2.webp', 'Blossom Sajadah Set Pink', TRUE, 1, 'active'),

-- Travel Prayer Mat (Brown)
(6, 15, 'images/products/blossom - 1.webp', 'Travel Prayer Mat Brown', TRUE, 1, 'active');

-- Insert Related Products
INSERT INTO related_products (product_id, related_product_id, sort_order) VALUES
(1, 2, 1), -- Blossom Prayer Collection -> Premium Prayer Mat
(1, 3, 2), -- Blossom Prayer Collection -> Azhara Sacred Mat
(1, 5, 3), -- Blossom Prayer Collection -> Blossom Sajadah Set
(2, 1, 1), -- Premium Prayer Mat -> Blossom Prayer Collection
(2, 4, 2), -- Premium Prayer Mat -> Premium Black Mat
(2, 3, 3), -- Premium Prayer Mat -> Azhara Sacred Mat
(3, 1, 1), -- Azhara Sacred Mat -> Blossom Prayer Collection
(3, 2, 2), -- Azhara Sacred Mat -> Premium Prayer Mat
(3, 5, 3), -- Azhara Sacred Mat -> Blossom Sajadah Set
(4, 2, 1), -- Premium Black Mat -> Premium Prayer Mat
(4, 3, 2), -- Premium Black Mat -> Azhara Sacred Mat
(4, 1, 3), -- Premium Black Mat -> Blossom Prayer Collection
(5, 1, 1), -- Blossom Sajadah Set -> Blossom Prayer Collection
(5, 3, 2), -- Blossom Sajadah Set -> Azhara Sacred Mat
(5, 6, 3), -- Blossom Sajadah Set -> Travel Prayer Mat
(6, 5, 1), -- Travel Prayer Mat -> Blossom Sajadah Set
(6, 1, 2), -- Travel Prayer Mat -> Blossom Prayer Collection
(6, 2, 3); -- Travel Prayer Mat -> Premium Prayer Mat 