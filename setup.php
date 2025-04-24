<?php
// Check if the database exists
$dbExists = false;
$dbName = 'q4_mysql_task_db';

try {
    $conn = new mysqli('localhost', 'root', '');
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Check if database exists
    $result = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbName'");
    $dbExists = ($result && $result->num_rows > 0);
    
    $conn->close();
} catch (Exception $e) {
    $connectionError = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .setup-step {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #0d6efd;
        }
        .step-number {
            display: inline-block;
            width: 30px;
            height: 30px;
            background-color: #0d6efd;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 30px;
            margin-right: 10px;
        }
        .completed-step {
            border-left-color: #28a745;
        }
        .completed-step .step-number {
            background-color: #28a745;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">Database Setup</h1>
        
        <?php if (isset($connectionError)): ?>
            <div class="alert alert-danger">
                <strong>Error:</strong> <?php echo htmlspecialchars($connectionError); ?>
            </div>
        <?php else: ?>
            
            <!-- Step 1: Create Database -->
            <div class="setup-step <?php echo $dbExists ? 'completed-step' : ''; ?>">
                <h3><span class="step-number">1</span> Create Database</h3>
                <p>Create the MySQL database for the application.</p>
                
                <?php if ($dbExists): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Database '<?php echo htmlspecialchars($dbName); ?>' already exists.
                    </div>
                <?php else: ?>
                    <form method="post" action="create_database.php">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-database"></i> Create Database
                        </button>
                    </form>
                <?php endif; ?>
            </div>
            
            <!-- Step 2: Create Tables -->
            <div class="setup-step">
                <h3><span class="step-number">2</span> Create Tables</h3>
                <p>Create the necessary tables for the application.</p>
                
                <?php if (!$dbExists): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Please complete Step 1 first.
                    </div>
                <?php else: ?>
                    <a href="setup_database.php" class="btn btn-primary">
                        <i class="fas fa-table"></i> Create Tables
                    </a>
                <?php endif; ?>
            </div>
            
            <!-- Step 3: Insert Sample Data -->
            <div class="setup-step">
                <h3><span class="step-number">3</span> Insert Sample Orders</h3>
                <p>Insert sample orders and order items for testing.</p>
                
                <?php if (!$dbExists): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Please complete Steps 1 and 2 first.
                    </div>
                <?php else: ?>
                    <a href="insert_sample_orders.php" class="btn btn-primary">
                        <i class="fas fa-shopping-cart"></i> Insert Sample Orders
                    </a>
                <?php endif; ?>
            </div>
            
            <!-- Step 4: Go to Application -->
            <div class="setup-step">
                <h3><span class="step-number">4</span> Start Using the Application</h3>
                <p>Once all setup steps are completed, you can start using the application.</p>
                
                <a href="index.php" class="btn btn-success">
                    <i class="fas fa-home"></i> Go to Dashboard
                </a>
            </div>
            
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>