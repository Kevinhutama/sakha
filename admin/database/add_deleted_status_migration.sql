-- Migration to add 'deleted' status to products table
-- Run this SQL to update the products table status column

-- Add 'deleted' to the ENUM values for products.status
ALTER TABLE products 
MODIFY COLUMN status ENUM('active', 'inactive', 'deleted') DEFAULT 'active';

-- Optional: Add index for better performance when filtering deleted products
CREATE INDEX idx_products_status_deleted ON products(status, id);

-- Update any existing records if needed (this is just a safety check)
-- In case there are any records with invalid status values, this will show them
SELECT id, name, status FROM products WHERE status NOT IN ('active', 'inactive', 'deleted');

-- Verify the change
DESCRIBE products; 