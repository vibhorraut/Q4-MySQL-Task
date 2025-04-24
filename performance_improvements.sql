-- Performance Improvements for the Query

-- 1. Add indexes to improve query performance
-- Index on orders.order_date for date range filtering
CREATE INDEX idx_orders_order_date ON orders(order_date);

-- Index on orders.user_id for joining with users table
CREATE INDEX idx_orders_user_id ON orders(user_id);

-- Index on order_items.order_id for joining with orders table
CREATE INDEX idx_order_items_order_id ON order_items(order_id);

-- 2. Alternative query approach using temporary tables for large datasets
-- This can be more efficient for very large datasets by breaking down the query

-- Step 1: Create temporary table with recent orders
CREATE TEMPORARY TABLE recent_orders AS
SELECT user_id, id AS order_id
FROM orders
WHERE order_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH);

-- Step 2: Find users with at least 3 orders
CREATE TEMPORARY TABLE qualified_users AS
SELECT user_id
FROM recent_orders
GROUP BY user_id
HAVING COUNT(order_id) >= 3;

-- Step 3: Calculate spending for qualified users
SELECT 
    u.id AS user_id,
    u.name AS user_name,
    u.email AS user_email,
    COUNT(DISTINCT o.id) AS purchase_count,
    SUM(oi.quantity * oi.price) AS total_spending
FROM 
    users u
JOIN 
    orders o ON u.id = o.user_id
JOIN 
    order_items oi ON o.id = oi.order_id
WHERE 
    o.order_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
    AND u.id IN (SELECT user_id FROM qualified_users)
GROUP BY 
    u.id, u.name, u.email
ORDER BY 
    total_spending DESC;

-- 3. Additional optimization strategies:

-- Consider partitioning the orders table by date range if your database supports it
-- This can dramatically improve performance for date-based queries
-- Example: PARTITION BY RANGE (YEAR(order_date))

-- For extremely large datasets, consider materialized views that pre-aggregate data
-- and refresh on a schedule (if supported by your MySQL version)

-- If this query runs frequently, consider caching the results

-- Monitor query performance in production and adjust based on actual usage patterns