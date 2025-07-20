<?php
// Prevent any output before JSON
ob_clean();

// Set JSON headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT');
header('Access-Control-Allow-Headers: Content-Type');

// Prevent direct access
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
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

    // Get PUT data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON data']);
        exit;
    }

    $id = $input['id'] ?? null;
    $word = trim($input['word'] ?? '');
    $meaning = trim($input['meaning'] ?? '');
    $example = trim($input['example'] ?? '');

    // Validate input
    if (!$id || !is_numeric($id)) {
        http_response_code(400);
        echo json_encode(['error' => 'Valid ID is required']);
        exit;
    }

    if (empty($word) || empty($meaning)) {
        http_response_code(400);
        echo json_encode(['error' => 'Word and meaning are required']);
        exit;
    }

    // Direct PDO connection for API
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    // Check if vocabulary table exists
    $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
    $stmt->execute(['vocabulary']);
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        http_response_code(404);
        echo json_encode(['error' => 'Vocabulary table not found']);
        exit;
    }

    // Update vocabulary
    $stmt = $pdo->prepare("UPDATE vocabulary SET word = ?, meaning = ?, example = ? WHERE id = ?");
    $stmt->execute([$word, $meaning, $example, $id]);
    $affected = $stmt->rowCount();

    if ($affected > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Vocabulary updated successfully'
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Vocabulary not found']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error occurred',
        'message' => 'Please try again later'
    ]);
}
?> 