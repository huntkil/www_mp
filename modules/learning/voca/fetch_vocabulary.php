<?php
// Prevent any output before JSON
ob_clean();

// Set JSON headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Prevent direct access
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Load production config if exists, otherwise development
    $config_prod = __DIR__ . '/../../../system/includes/config_production.php';
    $config_dev = __DIR__ . '/../../../system/includes/config.php';
    
    if (file_exists($config_prod)) {
        require_once $config_prod;
    } else {
        require_once $config_dev;
    }

    // Direct PDO connection for API
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    // Check if vocabulary table exists
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'vocabulary'");
    $stmt->execute();
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        // Create vocabulary table
        $createTableSQL = "
        CREATE TABLE IF NOT EXISTS vocabulary (
            id INT AUTO_INCREMENT PRIMARY KEY,
            word VARCHAR(255) NOT NULL,
            meaning TEXT NOT NULL,
            example TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $pdo->exec($createTableSQL);
        
        // Return empty array for new table
        echo json_encode(['success' => true, 'data' => []]);
        exit;
    }
    
    // Fetch all vocabulary
    $stmt = $pdo->prepare("SELECT * FROM vocabulary ORDER BY id DESC");
    $stmt->execute();
    $words = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'data' => $words]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error occurred',
        'message' => 'Please try again later'
    ]);
}
?>
