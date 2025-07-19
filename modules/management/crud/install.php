<?php
require_once 'config/database.php';

try {
    // Read and execute the schema.sql file
    $sql = file_get_contents('schema.sql');
    $pdo->exec($sql);
    
    echo "Database tables created successfully!";
} catch(PDOException $e) {
    die("Error creating tables: " . $e->getMessage());
}
?> 