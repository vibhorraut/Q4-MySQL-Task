<?php
// Check if the database and tables exist
$dbName = 'q4_mysql_task_db';
$setupNeeded = false;

try {
    // Try to connect to the database
    $conn = new mysqli('localhost', 'root', '', $dbName);
    
    if ($conn->connect_error) {
        // Database doesn't exist or connection failed
        $setupNeeded = true;
    } else {
        // Check if products table exists
        $result = $conn->query("SHOW TABLES LIKE 'products'");
        if (!$result || $result->num_rows === 0) {
            $setupNeeded = true;
        }
        
        $conn->close();
    }
} catch (Exception $e) {
    $setupNeeded = true;
}

// Redirect to setup page if needed, otherwise to the main view
if ($setupNeeded) {
    header("Location: setup.php");
} else {
    header("Location: view_top_customers.php");
}
exit;
?>