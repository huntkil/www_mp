<?php
header('Content-Type: application/json');
require_once "../../../system/includes/config.php";

try {
    // Get JSON data from request body
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Validate input data
    if (!isset($data['id']) || !isset($data['word']) || !isset($data['meaning']) || !isset($data['example'])) {
        throw new Exception('Missing required fields');
    }

    // Update using Database class
    $db = Database::getInstance();
    $result = $db->update('vocabulary', [
        'word' => $data['word'],
        'meaning' => $data['meaning'],
        'example' => $data['example']
    ], 'id = ?', [$data['id']]);

    if ($result === 0) {
        throw new Exception('Word not found');
    }

    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 