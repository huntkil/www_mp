<?php
// CRUD 모듈 디버깅 파일
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>CRUD Module Debug</h1>";

// 1. 환경 감지 테스트
echo "<h2>1. Environment Detection</h2>";
$config_prod = __DIR__ . '/system/includes/config_production.php';
$config_dev = __DIR__ . '/system/includes/config.php';

echo "Production config exists: " . (file_exists($config_prod) ? 'YES' : 'NO') . "<br>";
echo "Development config exists: " . (file_exists($config_dev) ? 'YES' : 'NO') . "<br>";

if (file_exists($config_prod)) {
    echo "Loading production config...<br>";
    require_once $config_prod;
} else {
    echo "Loading development config...<br>";
    require_once $config_dev;
}

echo "DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'NOT DEFINED') . "<br>";
echo "DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'NOT DEFINED') . "<br>";
echo "DB_USER: " . (defined('DB_USER') ? DB_USER : 'NOT DEFINED') . "<br>";

// 2. 데이터베이스 연결 테스트
echo "<h2>2. Database Connection Test</h2>";
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    echo "✅ Database connection successful<br>";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
    exit;
}

// 3. myinfo 테이블 존재 확인
echo "<h2>3. Table Existence Check</h2>";
try {
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'myinfo'");
    $stmt->execute();
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "✅ myinfo table exists<br>";
        
        // 테이블 구조 확인
        $stmt = $pdo->prepare("DESCRIBE myinfo");
        $stmt->execute();
        $columns = $stmt->fetchAll();
        
        echo "<h3>Table Structure:</h3>";
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>" . $column['Field'] . "</td>";
            echo "<td>" . $column['Type'] . "</td>";
            echo "<td>" . $column['Null'] . "</td>";
            echo "<td>" . $column['Key'] . "</td>";
            echo "<td>" . $column['Default'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // 데이터 개수 확인
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM myinfo");
        $stmt->execute();
        $count = $stmt->fetch()['count'];
        echo "Total records: " . $count . "<br>";
        
    } else {
        echo "❌ myinfo table does not exist<br>";
        echo "Creating table...<br>";
        
        $createTableSQL = "
        CREATE TABLE IF NOT EXISTS `myinfo` (
            `no` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `email` VARCHAR(255) NOT NULL,
            `phone` VARCHAR(20) NOT NULL,
            `age` INT,
            `birthday` DATE,
            `height` DECIMAL(5,2),
            `weight` DECIMAL(5,2)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $pdo->exec($createTableSQL);
        echo "✅ myinfo table created successfully<br>";
    }
} catch (Exception $e) {
    echo "❌ Table check failed: " . $e->getMessage() . "<br>";
}

// 4. MyInfoController 테스트
echo "<h2>4. MyInfoController Test</h2>";
try {
    require_once __DIR__ . '/modules/management/crud/controllers/MyInfoController.php';
    $controller = new MyInfoController();
    echo "✅ MyInfoController loaded successfully<br>";
    
    // index 메서드 테스트
    $result = $controller->index();
    echo "Controller index result: <pre>" . print_r($result, true) . "</pre>";
    
} catch (Exception $e) {
    echo "❌ MyInfoController test failed: " . $e->getMessage() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}

// 5. 파일 존재 확인
echo "<h2>5. File Existence Check</h2>";
$files = [
    'system/includes/header.php',
    'system/includes/footer.php',
    'system/includes/config.php',
    'system/includes/config_production.php',
    'modules/management/crud/controllers/MyInfoController.php',
    'modules/management/crud/models/MyInfo.php',
    'modules/management/crud/data_list.php'
];

foreach ($files as $file) {
    echo $file . ": " . (file_exists($file) ? '✅ EXISTS' : '❌ MISSING') . "<br>";
}

echo "<h2>6. Test Complete</h2>";
echo "<a href='modules/management/crud/data_list.php'>Go to CRUD List</a>";
?> 