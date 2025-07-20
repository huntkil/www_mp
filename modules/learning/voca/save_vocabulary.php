<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Prevent direct access
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Load appropriate config based on environment
    if (file_exists(__DIR__ . '/../../../system/includes/config_production.php')) {
        require_once __DIR__ . '/../../../system/includes/config_production.php';
    } else {
        require_once __DIR__ . '/../../../system/includes/config.php';
    }

    // Get JSON data from request body
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Validate input data
    if (!isset($data['word']) || !isset($data['meaning'])) {
        throw new Exception('Missing required fields: word and meaning are required');
    }

    // Validate word length
    if (strlen(trim($data['word'])) === 0) {
        throw new Exception('Word cannot be empty');
    }

    // Validate meaning length
    if (strlen(trim($data['meaning'])) === 0) {
        throw new Exception('Meaning cannot be empty');
    }

    // Sanitize input
    $word = trim($data['word']);
    $meaning = trim($data['meaning']);
    $example = isset($data['example']) ? trim($data['example']) : '';

    // Insert using Database class
    $db = Database::getInstance();
    
    // Check if vocabulary table exists
    if (!$db->tableExists('vocabulary')) {
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
    }
    
    $sql = "INSERT INTO vocabulary (word, meaning, example) VALUES (?, ?, ?)";
    $id = $db->insert($sql, [$word, $meaning, $example]);

    echo json_encode(['success' => true, 'id' => $id]);
    
} catch (Exception $e) {
    error_log("Vocabulary save error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to save vocabulary',
        'message' => IS_LOCAL ? $e->getMessage() : 'Please try again later'
    ]);
}
?>
