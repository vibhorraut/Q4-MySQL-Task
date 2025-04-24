-- Create users table if it doesn't exist
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create orders table if it doesn't exist
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Create order_items table if it doesn't exist
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

-- Create products table if it doesn't exist
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock_quantity INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add indexes for better performance
CREATE INDEX idx_orders_user_id ON orders(user_id);
CREATE INDEX idx_orders_order_date ON orders(order_date);
CREATE INDEX idx_order_items_order_id ON order_items(order_id);
CREATE INDEX idx_order_items_product_id ON order_items(product_id);
CREATE INDEX idx_product_name ON products(name);

-- Insert sample users if the table is empty
INSERT INTO users (name, email)
SELECT * FROM (
    SELECT 'John Doe', 'john.doe@example.com' UNION ALL
    SELECT 'Jane Smith', 'jane.smith@example.com' UNION ALL
    SELECT 'Robert Johnson', 'robert.johnson@example.com' UNION ALL
    SELECT 'Emily Davis', 'emily.davis@example.com' UNION ALL
    SELECT 'Michael Wilson', 'michael.wilson@example.com'
) AS tmp
WHERE NOT EXISTS (
    SELECT 1 FROM users LIMIT 1
);

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