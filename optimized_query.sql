-- Optimized query to find users with at least 3 purchases in last 6 months
-- with their total spending, sorted by highest spenders

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
GROUP BY 
    u.id, u.name, u.email
HAVING 
    COUNT(DISTINCT o.id) >= 3
ORDER BY 
    total_spending DESC;

-- EXPLAIN ANALYZE for the query
EXPLAIN ANALYZE
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
GROUP BY 
    u.id, u.name, u.email
HAVING 
    COUNT(DISTINCT o.id) >= 3
ORDER BY 
    total_spending DESC;