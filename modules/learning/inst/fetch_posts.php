<?php
session_start();

// 로그인 체크
if (!isset($_SESSION['id'])) {
    header('Content-Type: application/json', true, 401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Database connection parameters
require_once __DIR__ . '/../../../config/credentials/loader.php';

$host = CREDENTIALS_DB_HOST;
$dbname = CREDENTIALS_DB_NAME;
$user = CREDENTIALS_DB_USER;
$password = CREDENTIALS_DB_PASS;

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Query to fetch id, word, and meaning
    $stmt = $pdo->prepare("SELECT id, word, meaning FROM voca ORDER BY id ASC");
    $stmt->execute();

    // Fetch all rows
    $posts = $stmt->fetchAll();

    // Return posts as JSON
    header('Content-Type: application/json');
    echo json_encode($posts);

} catch (PDOException $e) {
    // Log error (in production, use proper logging)
    error_log("Database error: " . $e->getMessage());
    
    // Return error response
    header('Content-Type: application/json', true, 500);
    echo json_encode(['error' => 'Database error occurred']);
}
?>
