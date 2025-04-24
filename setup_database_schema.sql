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
CREATE INDEX IF NOT EXISTS idx_orders_user_id ON orders(user_id);
CREATE INDEX IF NOT EXISTS idx_orders_order_date ON orders(order_date);
CREATE INDEX IF NOT EXISTS idx_order_items_order_id ON order_items(order_id);
CREATE INDEX IF NOT EXISTS idx_order_items_product_id ON order_items(product_id);
CREATE INDEX IF NOT EXISTS idx_product_name ON products(name);

-- Insert sample users if the table is empty
INSERT INTO users (name, email)
SELECT * FROM (
    SELECT 'John Doe', 'john.doe@example.com' UNION ALL
    SELECT 'Jane Smith', 'jane.smith@example.com' UNION ALL
    SELECT 'Robert Johnson', 'robert.johnson@example.com' UNION ALL
    SELECT 'Emily Davis', 'emily.davis@example.com' UNION ALL
    SELECT 'Michael Wilson', 'michael.wilson@example.com' UNION ALL
    SELECT 'Sarah Brown', 'sarah.brown@example.com' UNION ALL
    SELECT 'David Miller', 'david.miller@example.com' UNION ALL
    SELECT 'Jennifer Taylor', 'jennifer.taylor@example.com' UNION ALL
    SELECT 'James Anderson', 'james.anderson@example.com' UNION ALL
    SELECT 'Lisa Thomas', 'lisa.thomas@example.com'
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

-- Insert sample orders and order items (only if tables are empty)
-- First, check if orders table is empty
SET @orders_empty = (SELECT COUNT(*) = 0 FROM orders);

-- If orders table is empty, insert sample data
SET @order_date_1 = DATE_SUB(CURRENT_DATE(), INTERVAL 2 MONTH);
SET @order_date_2 = DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH);
SET @order_date_3 = DATE_SUB(CURRENT_DATE(), INTERVAL 2 WEEK);
SET @order_date_4 = DATE_SUB(CURRENT_DATE(), INTERVAL 1 WEEK);

-- Create a procedure to insert sample orders and items
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS insert_sample_orders()
BEGIN
    DECLARE user_count INT;
    DECLARE i INT DEFAULT 1;
    DECLARE new_order_id INT;
    
    -- Get user count
    SELECT COUNT(*) INTO user_count FROM users;
    
    -- Only proceed if orders table is empty
    IF (SELECT COUNT(*) = 0 FROM orders) THEN
        -- For each user
        WHILE i <= user_count DO
            -- Create 3-5 orders for each user (to ensure they meet the 3+ purchase criteria)
            -- Order 1 (2 months ago)
            INSERT INTO orders (user_id, order_date) VALUES (i, DATE_SUB(CURRENT_DATE(), INTERVAL 2 MONTH));
            SET new_order_id = LAST_INSERT_ID();
            -- Add 1-3 items to this order
            INSERT INTO order_items (order_id, product_id, quantity, price)
            VALUES 
                (new_order_id, 1, 1, 999.99),
                (new_order_id, 3, 2, 99.99);
                
            -- Order 2 (1 month ago)
            INSERT INTO orders (user_id, order_date) VALUES (i, DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH));
            SET new_order_id = LAST_INSERT_ID();
            -- Add 1-3 items to this order
            INSERT INTO order_items (order_id, product_id, quantity, price)
            VALUES 
                (new_order_id, 2, 1, 499.99),
                (new_order_id, 5, 1, 199.99);
                
            -- Order 3 (2 weeks ago)
            INSERT INTO orders (user_id, order_date) VALUES (i, DATE_SUB(CURRENT_DATE(), INTERVAL 2 WEEK));
            SET new_order_id = LAST_INSERT_ID();
            -- Add 1-3 items to this order
            INSERT INTO order_items (order_id, product_id, quantity, price)
            VALUES 
                (new_order_id, 4, 1, 349.99),
                (new_order_id, 9, 2, 29.99),
                (new_order_id, 10, 1, 69.99);
                
            -- For some users, add more recent orders
            IF i <= 5 THEN
                -- Order 4 (1 week ago)
                INSERT INTO orders (user_id, order_date) VALUES (i, DATE_SUB(CURRENT_DATE(), INTERVAL 1 WEEK));
                SET new_order_id = LAST_INSERT_ID();
                -- Add items to this order
                INSERT INTO order_items (order_id, product_id, quantity, price)
                VALUES 
                    (new_order_id, 6, 1, 599.99),
                    (new_order_id, 8, 1, 79.99);
            END IF;
            
            SET i = i + 1;
        END WHILE;
    END IF;
END //
DELIMITER ;

-- Call the procedure to insert sample data
CALL insert_sample_orders();

-- Drop the procedure after use
DROP PROCEDURE IF EXISTS insert_sample_orders;