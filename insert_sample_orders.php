<?php
// Include database configuration
require_once 'config.php';

// Connect to the database
$conn = getDbConnection();
if (!$conn) {
    die("Failed to connect to the database. Please check your configuration.");
}

// Display a message
echo "<h1>Inserting Sample Orders</h1>";

// Check if orders table is empty
$result = $conn->query("SELECT COUNT(*) as count FROM orders");
$row = $result->fetch_assoc();
if ($row['count'] > 0) {
    echo "<p>Orders table already has data. Skipping sample data insertion.</p>";
    echo "<p><a href='index.php'>Return to Dashboard</a></p>";
    $conn->close();
    exit;
}

// Get all users
$users = [];
$result = $conn->query("SELECT id FROM users");
while ($row = $result->fetch_assoc()) {
    $users[] = $row['id'];
}

// Get all products
$products = [];
$result = $conn->query("SELECT id, price FROM products");
while ($row = $result->fetch_assoc()) {
    $products[$row['id']] = $row['price'];
}

// Insert sample orders for each user
$successCount = 0;
$errorCount = 0;

foreach ($users as $userId) {
    // Create 3-5 orders for each user (to ensure they meet the 3+ purchase criteria)
    
    // Order 1 (2 months ago)
    $sql = "INSERT INTO orders (user_id, order_date) VALUES (?, DATE_SUB(CURRENT_DATE(), INTERVAL 2 MONTH))";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    
    if ($stmt->execute()) {
        $orderId = $conn->insert_id;
        echo "<p>Created order #$orderId for user #$userId (2 months ago)</p>";
        
        // Add 1-2 items to this order
        $productIds = array_rand($products, min(2, count($products)));
        if (!is_array($productIds)) {
            $productIds = [$productIds];
        }
        
        foreach ($productIds as $productId) {
            $quantity = rand(1, 3);
            $price = $products[$productId];
            
            $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiid", $orderId, $productId, $quantity, $price);
            
            if ($stmt->execute()) {
                echo "<p style='margin-left: 20px;'>Added product #$productId (qty: $quantity) to order #$orderId</p>";
            } else {
                echo "<p style='color: red; margin-left: 20px;'>Error adding product to order: " . $stmt->error . "</p>";
                $errorCount++;
            }
        }
        
        $successCount++;
    } else {
        echo "<p style='color: red;'>Error creating order: " . $stmt->error . "</p>";
        $errorCount++;
    }
    
    // Order 2 (1 month ago)
    $sql = "INSERT INTO orders (user_id, order_date) VALUES (?, DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    
    if ($stmt->execute()) {
        $orderId = $conn->insert_id;
        echo "<p>Created order #$orderId for user #$userId (1 month ago)</p>";
        
        // Add 1-2 items to this order
        $productIds = array_rand($products, min(2, count($products)));
        if (!is_array($productIds)) {
            $productIds = [$productIds];
        }
        
        foreach ($productIds as $productId) {
            $quantity = rand(1, 3);
            $price = $products[$productId];
            
            $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiid", $orderId, $productId, $quantity, $price);
            
            if ($stmt->execute()) {
                echo "<p style='margin-left: 20px;'>Added product #$productId (qty: $quantity) to order #$orderId</p>";
            } else {
                echo "<p style='color: red; margin-left: 20px;'>Error adding product to order: " . $stmt->error . "</p>";
                $errorCount++;
            }
        }
        
        $successCount++;
    } else {
        echo "<p style='color: red;'>Error creating order: " . $stmt->error . "</p>";
        $errorCount++;
    }
    
    // Order 3 (2 weeks ago)
    $sql = "INSERT INTO orders (user_id, order_date) VALUES (?, DATE_SUB(CURRENT_DATE(), INTERVAL 2 WEEK))";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    
    if ($stmt->execute()) {
        $orderId = $conn->insert_id;
        echo "<p>Created order #$orderId for user #$userId (2 weeks ago)</p>";
        
        // Add 1-3 items to this order
        $productIds = array_rand($products, min(3, count($products)));
        if (!is_array($productIds)) {
            $productIds = [$productIds];
        }
        
        foreach ($productIds as $productId) {
            $quantity = rand(1, 3);
            $price = $products[$productId];
            
            $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiid", $orderId, $productId, $quantity, $price);
            
            if ($stmt->execute()) {
                echo "<p style='margin-left: 20px;'>Added product #$productId (qty: $quantity) to order #$orderId</p>";
            } else {
                echo "<p style='color: red; margin-left: 20px;'>Error adding product to order: " . $stmt->error . "</p>";
                $errorCount++;
            }
        }
        
        $successCount++;
    } else {
        echo "<p style='color: red;'>Error creating order: " . $stmt->error . "</p>";
        $errorCount++;
    }
    
    // For some users, add a more recent order
    if (rand(0, 1) == 1) {
        $sql = "INSERT INTO orders (user_id, order_date) VALUES (?, DATE_SUB(CURRENT_DATE(), INTERVAL " . rand(1, 7) . " DAY))";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        
        if ($stmt->execute()) {
            $orderId = $conn->insert_id;
            echo "<p>Created order #$orderId for user #$userId (recent)</p>";
            
            // Add 1-3 items to this order
            $productIds = array_rand($products, min(3, count($products)));
            if (!is_array($productIds)) {
                $productIds = [$productIds];
            }
            
            foreach ($productIds as $productId) {
                $quantity = rand(1, 3);
                $price = $products[$productId];
                
                $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iiid", $orderId, $productId, $quantity, $price);
                
                if ($stmt->execute()) {
                    echo "<p style='margin-left: 20px;'>Added product #$productId (qty: $quantity) to order #$orderId</p>";
                } else {
                    echo "<p style='color: red; margin-left: 20px;'>Error adding product to order: " . $stmt->error . "</p>";
                    $errorCount++;
                }
            }
            
            $successCount++;
        } else {
            echo "<p style='color: red;'>Error creating order: " . $stmt->error . "</p>";
            $errorCount++;
        }
    }
}

// Display summary
echo "<h2>Sample Data Insertion Complete</h2>";
echo "<p>Successfully created $successCount orders.</p>";
if ($errorCount > 0) {
    echo "<p>Encountered $errorCount errors.</p>";
}

echo "<p><a href='index.php'>Return to Dashboard</a></p>";

// Close the connection
$conn->close();
?>