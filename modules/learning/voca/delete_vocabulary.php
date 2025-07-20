<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../../system/includes/config.php';

try {
    // Get ID from different sources depending on request method
    $id = null;
    
    $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    
    if ($requestMethod === 'DELETE') {
        // For DELETE requests, get from URL parameters or input stream
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            $id = $data['id'] ?? null;
        }
    } else if ($requestMethod === 'POST') {
        // For POST requests (fallback)
        $id = $_POST['id'] ?? $_GET['id'] ?? null;
    } else {
        // For GET requests (fallback for testing)
        $id = $_GET['id'] ?? null;
    }
    
    if (!$id) {
        throw new Exception('ID is required');
    }

    // Validate ID is numeric
    if (!is_numeric($id)) {
        throw new Exception('Invalid ID format');
    }

    $db = Database::getInstance();
    
    // Check if the record exists before deleting
    $existing = $db->selectOne("SELECT id FROM vocabulary WHERE id = ?", [$id]);
    if (!$existing) {
        throw new Exception('Word not found');
    }
    
    // Delete the record
    $result = $db->delete("DELETE FROM vocabulary WHERE id = ?", [$id]);
    
    if ($result === 0) {
        throw new Exception('Failed to delete word');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Word deleted successfully',
        'deleted_id' => $id
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
        'received_id' => $id ?? 'none'
    ]);
}
?> 