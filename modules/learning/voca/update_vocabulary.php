<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Prevent direct access
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PUT') {
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
    if (!isset($data['id']) || !isset($data['word']) || !isset($data['meaning'])) {
        throw new Exception('Missing required fields: id, word, and meaning are required');
    }

    // Validate ID is numeric
    if (!is_numeric($data['id'])) {
        throw new Exception('Invalid ID format');
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
    $id = (int)$data['id'];
    $word = trim($data['word']);
    $meaning = trim($data['meaning']);
    $example = isset($data['example']) ? trim($data['example']) : '';

    // Update using Database class
    $db = Database::getInstance();
    
    // Check if vocabulary table exists
    if (!$db->tableExists('vocabulary')) {
        throw new Exception('Vocabulary table does not exist');
    }
    
    // Check if the record exists before updating
    $existing = $db->selectOne("SELECT id FROM vocabulary WHERE id = ?", [$id]);
    if (!$existing) {
        throw new Exception('Word not found');
    }
    
    $sql = "UPDATE vocabulary SET word = ?, meaning = ?, example = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
    $result = $db->update($sql, [$word, $meaning, $example, $id]);

    if ($result === 0) {
        throw new Exception('Failed to update word');
    }

    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    error_log("Vocabulary update error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to update word',
        'message' => IS_LOCAL ? $e->getMessage() : 'Please try again later'
    ]);
}
?> 