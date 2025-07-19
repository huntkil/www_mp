<?php
// Load credentials
require_once __DIR__ . '/../../config/credentials/loader.php';

// Database connection using credentials
$host = CREDENTIALS_DB_HOST;
$username = CREDENTIALS_DB_USER;
$password = CREDENTIALS_DB_PASS;
$database = CREDENTIALS_DB_NAME;

// Only connect if MySQL is configured
if (CREDENTIALS_DB_TYPE === 'mysql') {
    $conn = mysqli_connect($host, $username, $password, $database);
    
    if (!$conn) {
        die('Connection failed: ' . mysqli_connect_error());
    }
} else {
    // For SQLite, use the main Database class instead
    $conn = null;
}
?> 

