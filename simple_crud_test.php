<?php
// Simple CRUD Test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Simple CRUD Test</h1>";

// 1. Load config
echo "<h2>1. Loading Config</h2>";
$config_prod = __DIR__ . '/system/includes/config_production.php';
$config_dev = __DIR__ . '/system/includes/config.php';

if (file_exists($config_prod)) {
    require_once $config_prod;
    echo "✅ Production config loaded<br>";
} else {
    require_once $config_dev;
    echo "✅ Development config loaded<br>";
}

// 2. Test database connection
echo "<h2>2. Database Connection</h2>";
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

// 3. Test table query directly
echo "<h2>3. Direct Table Query</h2>";
try {
    $sql = "SELECT * FROM myinfo ORDER BY no DESC LIMIT 10";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll();
    
    echo "✅ Query successful<br>";
    echo "Total records: " . count($data) . "<br>";
    
    if (count($data) > 0) {
        echo "<h3>Data:</h3>";
        echo "<table border='1'>";
        echo "<tr><th>No</th><th>Name</th><th>Email</th><th>Phone</th><th>Age</th><th>Birthday</th><th>Height</th><th>Weight</th></tr>";
        foreach ($data as $row) {
            echo "<tr>";
            echo "<td>" . $row['no'] . "</td>";
            echo "<td>" . $row['name'] . "</td>";
            echo "<td>" . $row['email'] . "</td>";
            echo "<td>" . $row['phone'] . "</td>";
            echo "<td>" . $row['age'] . "</td>";
            echo "<td>" . $row['birthday'] . "</td>";
            echo "<td>" . $row['height'] . "</td>";
            echo "<td>" . $row['weight'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No data found<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Query failed: " . $e->getMessage() . "<br>";
}

// 4. Test MyInfo model directly
echo "<h2>4. MyInfo Model Test</h2>";
try {
    require_once __DIR__ . '/modules/management/crud/models/MyInfo.php';
    
    // Create model with direct PDO
    $model = new MyInfo($pdo);
    echo "✅ MyInfo model created<br>";
    
    // Test getAll method
    $data = $model->getAll(0, 10);
    echo "✅ getAll method successful<br>";
    echo "Records returned: " . count($data) . "<br>";
    
    // Test getTotal method
    $total = $model->getTotal();
    echo "✅ getTotal method successful<br>";
    echo "Total records: " . $total . "<br>";
    
} catch (Exception $e) {
    echo "❌ MyInfo model test failed: " . $e->getMessage() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}

// 5. Test MyInfoController
echo "<h2>5. MyInfoController Test</h2>";
try {
    require_once __DIR__ . '/modules/management/crud/controllers/MyInfoController.php';
    
    $controller = new MyInfoController();
    echo "✅ MyInfoController created<br>";
    
    $result = $controller->index();
    echo "✅ Controller index method successful<br>";
    echo "Result: <pre>" . print_r($result, true) . "</pre>";
    
} catch (Exception $e) {
    echo "❌ MyInfoController test failed: " . $e->getMessage() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h2>✅ Test Complete</h2>";
echo "<a href='modules/management/crud/data_list.php'>Go to CRUD List</a>";
?> 