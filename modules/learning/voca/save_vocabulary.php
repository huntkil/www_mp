<?php
header('Content-Type: application/json');
require_once "../../../system/includes/config.php";

try {
    // Get JSON data from request body
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Validate input data
    if (!isset($data['word']) || !isset($data['meaning']) || !isset($data['example'])) {
        throw new Exception('Missing required fields');
    }

    // Insert using Database class
    $db = Database::getInstance();
    $sql = "INSERT INTO vocabulary (word, meaning, example) VALUES (?, ?, ?)";
    $id = $db->insert($sql, [
        $data['word'],
        $data['meaning'],
        $data['example']
    ]);

    echo json_encode(['success' => true, 'id' => $id]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
