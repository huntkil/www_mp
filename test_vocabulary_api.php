<?php
// Simple test file to diagnose vocabulary API issues
echo "<h1>Vocabulary API Test</h1>";

// Test 1: Check if config files exist
echo "<h2>1. Config Files Check</h2>";
$config_dev = __DIR__ . '/system/includes/config.php';
$config_prod = __DIR__ . '/system/includes/config_production.php';

echo "Development config exists: " . (file_exists($config_dev) ? "YES" : "NO") . "<br>";
echo "Production config exists: " . (file_exists($config_prod) ? "YES" : "NO") . "<br>";

// Test 2: Try to load config
echo "<h2>2. Config Loading Test</h2>";
try {
    if (file_exists($config_prod)) {
        require_once $config_prod;
        echo "Loaded: Production config<br>";
    } else {
        require_once $config_dev;
        echo "Loaded: Development config<br>";
    }
    
    echo "DB_TYPE: " . (defined('DB_TYPE') ? DB_TYPE : 'NOT DEFINED') . "<br>";
    echo "DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'NOT DEFINED') . "<br>";
    echo "DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'NOT DEFINED') . "<br>";
    echo "IS_LOCAL: " . (defined('IS_LOCAL') ? (IS_LOCAL ? 'TRUE' : 'FALSE') : 'NOT DEFINED') . "<br>";
    
} catch (Exception $e) {
    echo "Config loading error: " . $e->getMessage() . "<br>";
}

// Test 3: Direct PDO connection test
echo "<h2>3. Direct PDO Connection Test</h2>";
try {
    // Direct PDO connection
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    echo "Direct PDO connection: SUCCESS<br>";
    
    // Test table existence
    $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
    $stmt->execute(['vocabulary']);
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "Vocabulary table: EXISTS<br>";
        
        // Try to fetch data
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM vocabulary");
        $stmt->execute();
        $result = $stmt->fetch();
        echo "Vocabulary count: " . $result['count'] . "<br>";
        
    } else {
        echo "Vocabulary table: DOES NOT EXIST<br>";
        
        // Try to create table
        echo "Attempting to create vocabulary table...<br>";
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
        
        $pdo->exec($createTableSQL);
        echo "Vocabulary table created successfully<br>";
    }
    
} catch (Exception $e) {
    echo "Direct PDO connection error: " . $e->getMessage() . "<br>";
    echo "Error trace: <pre>" . $e->getTraceAsString() . "</pre>";
}

// Test 4: Direct API test
echo "<h2>4. Direct API Test</h2>";
echo "<p>Testing fetch_vocabulary.php directly:</p>";

$api_url = __DIR__ . '/modules/learning/voca/fetch_vocabulary.php';
if (file_exists($api_url)) {
    echo "API file exists<br>";
    
    // Capture output
    ob_start();
    try {
        include $api_url;
        $output = ob_get_contents();
        ob_end_clean();
        
        echo "API Output:<br>";
        echo "<pre>" . htmlspecialchars($output) . "</pre>";
        
        // Try to decode JSON
        $json_data = json_decode($output, true);
        if ($json_data !== null) {
            echo "JSON parsing: SUCCESS<br>";
            echo "Response structure: " . print_r($json_data, true) . "<br>";
        } else {
            echo "JSON parsing: FAILED<br>";
            echo "JSON error: " . json_last_error_msg() . "<br>";
        }
        
    } catch (Exception $e) {
        ob_end_clean();
        echo "API execution error: " . $e->getMessage() . "<br>";
    }
} else {
    echo "API file not found<br>";
}

echo "<h2>5. PHP Info</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Error Reporting: " . (error_reporting() ? "ON" : "OFF") . "<br>";
echo "Display Errors: " . (ini_get('display_errors') ? "ON" : "OFF") . "<br>";
echo "Log Errors: " . (ini_get('log_errors') ? "ON" : "OFF") . "<br>";
echo "Error Log: " . ini_get('error_log') . "<br>";

// Test 6: Check for syntax errors
echo "<h2>6. Syntax Check</h2>";
$files_to_check = [
    'system/includes/config_production.php',
    'modules/learning/voca/fetch_vocabulary.php',
    'modules/learning/voca/save_vocabulary.php',
    'modules/learning/voca/delete_vocabulary.php',
    'modules/learning/voca/update_vocabulary.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        $output = shell_exec("php -l " . escapeshellarg($file) . " 2>&1");
        echo "Syntax check for $file: " . (strpos($output, 'No syntax errors') !== false ? "PASS" : "FAIL") . "<br>";
        if (strpos($output, 'No syntax errors') === false) {
            echo "Error: " . htmlspecialchars($output) . "<br>";
        }
    } else {
        echo "File not found: $file<br>";
    }
}
?> 