<?php
/**
 * Database Configuration
 * 
 * This file contains the database connection settings.
 * Include this file in any PHP script that needs to connect to the database.
 */

// Database connection settings
$config = [
    'host' => 'localhost',      // Database host
    'username' => 'root',       // Database username
    'password' => '',           // Database password
    'database' => 'q4_mysql_task_db'   // Database name
];

/**
 * Creates a database connection
 * 
 * @return mysqli|null Returns a mysqli connection object or null on failure
 */
function getDbConnection() {
    global $config;
    
    try {
        $conn = new mysqli(
            $config['host'],
            $config['username'],
            $config['password'],
            $config['database']
        );
        
        if ($conn->connect_error) {
            error_log("Database connection failed: " . $conn->connect_error);
            return null;
        }
        
        // Set character set to utf8mb4
        $conn->set_charset("utf8mb4");
        
        return $conn;
    } catch (Exception $e) {
        error_log("Database connection exception: " . $e->getMessage());
        return null;
    }
}

/**
 * Displays a formatted error message
 * 
 * @param string $message The error message to display
 * @return void
 */
function displayError($message) {
    echo '<div class="alert alert-danger">';
    echo '<strong>Error:</strong> ' . htmlspecialchars($message);
    echo '</div>';
}
?>