<?php
// Database name
$dbName = 'q4_mysql_task_db';

// Connect to MySQL server
try {
    $conn = new mysqli('localhost', 'root', '');
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Check if database already exists
    $result = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbName'");
    $dbExists = ($result && $result->num_rows > 0);
    
    if ($dbExists) {
        $message = "Database '$dbName' already exists.";
        $messageType = "info";
    } else {
        // Create the database
        if ($conn->query("CREATE DATABASE $dbName")) {
            $message = "Database '$dbName' created successfully!";
            $messageType = "success";
        } else {
            throw new Exception("Error creating database: " . $conn->error);
        }
    }
    
    $conn->close();
} catch (Exception $e) {
    $message = $e->getMessage();
    $messageType = "danger";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Database</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">Create Database</h1>
        
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
        
        <a href="setup.php" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Back to Setup
        </a>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>