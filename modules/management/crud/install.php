<?php
// Load production config if exists, otherwise development
$config_prod = __DIR__ . '/../../../system/includes/config_production.php';
$config_dev = __DIR__ . '/../../../system/includes/config.php';

if (file_exists($config_prod)) {
    require_once $config_prod;
} else {
    require_once $config_dev;
}

try {
    // Direct PDO connection for table creation
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    // Check if myinfo table exists
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'myinfo'");
    $stmt->execute();
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        // Create myinfo table
        $createTableSQL = "
        CREATE TABLE IF NOT EXISTS `myinfo` (
            `no` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `email` VARCHAR(255) NOT NULL,
            `phone` VARCHAR(20) NOT NULL,
            `age` INT,
            `birthday` DATE,
            `height` DECIMAL(5,2),
            `weight` DECIMAL(5,2)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $pdo->exec($createTableSQL);
        echo "myinfo table created successfully!";
    } else {
        echo "myinfo table already exists!";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?> 