# High-Value Customer Dashboard

This application provides a user interface to view the results of our optimized MySQL query that identifies high-value customers who have made at least 3 purchases in the last 6 months.

## Features

- **Dashboard View**: Displays a list of high-value customers sorted by total spending
- **Customer Details**: Provides detailed information about individual customers
- **Performance Optimized**: Uses the optimized SQL queries with proper indexing

## Files

- `index.php` - Entry point that redirects to the main dashboard
- `view_top_customers.php` - Main dashboard showing all high-value customers
- `customer_details.php` - Detailed view for individual customers
- `config.php` - Database configuration settings
- `optimized_query.sql` - The optimized SQL query
- `performance_improvements.sql` - SQL for indexes and performance improvements

## Setup Instructions

1. Place all files in your web server directory (e.g., htdocs for XAMPP)
2. Create a MySQL database named `retail_db` (or update the name in `config.php`)
3. Import your database schema and data
4. Update database credentials in `config.php` if needed
5. Access the application through your web browser at `http://localhost/path-to-folder/`

## Database Schema

The application works with the following database schema:

```sql
-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Order items table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id)
);
```

## Performance Optimization

For optimal performance, run the index creation statements in `performance_improvements.sql`:

```sql
CREATE INDEX idx_orders_order_date ON orders(order_date);
CREATE INDEX idx_orders_user_id ON orders(user_id);
CREATE INDEX idx_order_items_order_id ON order_items(order_id);
```

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache, Nginx, etc.)