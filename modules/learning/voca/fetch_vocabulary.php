<?php
header('Content-Type: application/json');
require_once "../../../system/includes/config.php";

try {
    $db = Database::getInstance();
    
    // Fetch all vocabulary
    $sql = "SELECT * FROM vocabulary ORDER BY id DESC";
    $words = $db->select($sql);
    
    echo json_encode(['success' => true, 'data' => $words]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
