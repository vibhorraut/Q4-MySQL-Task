<?php
// Include database configuration
require_once 'config.php';

// Connect to the database
$conn = getDbConnection();
if (!$conn) {
    die("Failed to connect to the database. Please check your configuration.");
}

// Display a message
echo "<h1>Setting up database tables</h1>";

// Read the SQL file content
$sqlFile = file_get_contents('setup_simple_schema.sql');
if (!$sqlFile) {
    die("Failed to read SQL file.");
}

// Split SQL statements
$sqlStatements = explode(';', $sqlFile);

// Execute each statement
$successCount = 0;
$errorCount = 0;

foreach ($sqlStatements as $sql) {
    $sql = trim($sql);
    if (empty($sql)) continue;
    
    echo "<p>Executing: " . htmlspecialchars(substr($sql, 0, 100)) . "...</p>";
    
    if ($conn->query($sql)) {
        echo "<p style='color: green;'>Success!</p>";
        $successCount++;
    } else {
        echo "<p style='color: red;'>Error: " . $conn->error . "</p>";
        $errorCount++;
    }
}

// Display summary
echo "<h2>Setup Complete</h2>";
echo "<p>Successfully executed $successCount statements.</p>";
if ($errorCount > 0) {
    echo "<p>Failed to execute $errorCount statements.</p>";
}

echo "<p><a href='index.php'>Return to Dashboard</a></p>";

// Close the connection
$conn->close();
?>