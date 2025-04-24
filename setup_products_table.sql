-- Create products table if it doesn't exist
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock_quantity INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add index on product name for faster searches
CREATE INDEX idx_product_name ON products(name);

-- Insert sample products if the table is empty
INSERT INTO products (name, description, price, stock_quantity)
SELECT * FROM (
    SELECT 'Laptop', 'High-performance laptop with 16GB RAM and 512GB SSD', 999.99, 50 UNION ALL
    SELECT 'Smartphone', 'Latest model with 128GB storage and 5G capability', 499.99, 100 UNION ALL
    SELECT 'Headphones', 'Noise-cancelling wireless headphones', 99.99, 200 UNION ALL
    SELECT 'Tablet', '10-inch tablet with 64GB storage', 349.99, 75 UNION ALL
    SELECT 'Smartwatch', 'Fitness tracking and notifications', 199.99, 120 UNION ALL
    SELECT 'Camera', 'Digital camera with 24MP sensor', 599.99, 30 UNION ALL
    SELECT 'Printer', 'Color laser printer with wireless connectivity', 149.99, 45 UNION ALL
    SELECT 'External Hard Drive', '2TB portable storage device', 79.99, 150 UNION ALL
    SELECT 'Wireless Mouse', 'Ergonomic design with long battery life', 29.99, 200 UNION ALL
    SELECT 'Bluetooth Speaker', 'Waterproof portable speaker', 69.99, 80
) AS tmp
WHERE NOT EXISTS (
    SELECT 1 FROM products LIMIT 1
);