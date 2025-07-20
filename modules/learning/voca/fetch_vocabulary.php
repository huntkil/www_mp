<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Prevent direct access
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Load appropriate config based on environment
    if (file_exists(__DIR__ . '/../../../system/includes/config_production.php')) {
        require_once __DIR__ . '/../../../system/includes/config_production.php';
        error_log("Vocabulary fetch: Using production config");
    } else {
        require_once __DIR__ . '/../../../system/includes/config.php';
        error_log("Vocabulary fetch: Using development config");
    }

    error_log("Vocabulary fetch: DB_TYPE = " . DB_TYPE);
    error_log("Vocabulary fetch: DB_HOST = " . DB_HOST);
    error_log("Vocabulary fetch: DB_NAME = " . DB_NAME);

    $db = Database::getInstance();
    error_log("Vocabulary fetch: Database instance created");
    
    // Check if vocabulary table exists
    if (!$db->tableExists('vocabulary')) {
        error_log("Vocabulary fetch: Table 'vocabulary' does not exist, creating...");
        // Create vocabulary table if it doesn't exist
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
        
        $db->query($createTableSQL);
        error_log("Vocabulary fetch: Table 'vocabulary' created successfully");
        
        // Return empty array for new table
        echo json_encode(['success' => true, 'data' => []]);
        exit;
    }
    
    error_log("Vocabulary fetch: Table 'vocabulary' exists, fetching data...");
    
    // Fetch all vocabulary
    $sql = "SELECT * FROM vocabulary ORDER BY id DESC";
    $words = $db->select($sql);
    
    error_log("Vocabulary fetch: Retrieved " . count($words) . " words");
    
    echo json_encode(['success' => true, 'data' => $words]);
    
} catch (Exception $e) {
    error_log("Vocabulary fetch error: " . $e->getMessage());
    error_log("Vocabulary fetch error trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error occurred',
        'message' => IS_LOCAL ? $e->getMessage() : 'Please try again later',
        'debug' => IS_LOCAL ? [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ] : null
    ]);
}
?>
