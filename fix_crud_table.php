<?php
// CRUD 테이블 구조 수정 스크립트
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>CRUD Table Structure Fix</h1>";

// Load production config
$config_prod = __DIR__ . '/system/includes/config_production.php';
require_once $config_prod;

try {
    // Direct PDO connection
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    echo "✅ Database connection successful<br>";
    
    // Check current table structure
    echo "<h2>Current Table Structure:</h2>";
    $stmt = $pdo->prepare("DESCRIBE myinfo");
    $stmt->execute();
    $columns = $stmt->fetchAll();
    
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
    
    // Drop and recreate table with correct structure
    echo "<h2>Recreating Table with Correct Structure:</h2>";
    
    // Drop existing table
    $pdo->exec("DROP TABLE IF EXISTS myinfo");
    echo "✅ Old table dropped<br>";
    
    // Create new table with correct structure
    $createTableSQL = "
    CREATE TABLE `myinfo` (
        `no` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `email` VARCHAR(255) NOT NULL,
        `phone` VARCHAR(20) NOT NULL,
        `age` INT NULL,
        `birthday` DATE NULL,
        `height` DECIMAL(5,2) NULL,
        `weight` DECIMAL(5,2) NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($createTableSQL);
    echo "✅ New table created with correct structure<br>";
    
    // Verify new structure
    echo "<h2>New Table Structure:</h2>";
    $stmt = $pdo->prepare("DESCRIBE myinfo");
    $stmt->execute();
    $columns = $stmt->fetchAll();
    
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
    
    // Insert sample data
    echo "<h2>Inserting Sample Data:</h2>";
    $sampleData = [
        ['name' => 'John Doe', 'email' => 'john@example.com', 'phone' => '010-1234-5678', 'age' => 30, 'birthday' => '1994-01-15', 'height' => 175.5, 'weight' => 70.2],
        ['name' => 'Jane Smith', 'email' => 'jane@example.com', 'phone' => '010-9876-5432', 'age' => 25, 'birthday' => '1999-05-20', 'height' => 165.0, 'weight' => 55.8],
        ['name' => 'Mike Johnson', 'email' => 'mike@example.com', 'phone' => '010-5555-1234', 'age' => 35, 'birthday' => '1989-12-10', 'height' => 180.0, 'weight' => 80.5]
    ];
    
    $insertSQL = "INSERT INTO myinfo (name, email, phone, age, birthday, height, weight) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($insertSQL);
    
    foreach ($sampleData as $data) {
        $stmt->execute([
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['age'],
            $data['birthday'],
            $data['height'],
            $data['weight']
        ]);
        echo "✅ Inserted: " . $data['name'] . "<br>";
    }
    
    // Verify data
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM myinfo");
    $stmt->execute();
    $count = $stmt->fetch()['count'];
    echo "<br>Total records: " . $count . "<br>";
    
    echo "<h2>✅ Table Fix Complete!</h2>";
    echo "<a href='modules/management/crud/data_list.php'>Go to CRUD List</a>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}
?> 