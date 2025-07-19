<?php
header('Content-Type: application/json');
require_once "../../../system/includes/config.php";

try {
    if (!isset($_GET['id'])) {
        throw new Exception('ID is required');
    }

    $db = Database::getInstance();
    $result = $db->delete('vocabulary', 'id = ?', [$_GET['id']]);
    
    if ($result === 0) {
        throw new Exception('Word not found');
    }

    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 