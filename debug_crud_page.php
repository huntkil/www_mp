<?php
// CRUD Page Debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>CRUD Page Debug</h1>";

// 1. Basic test
echo "<h2>1. Basic PHP Test</h2>";
echo "✅ PHP is working<br>";

// 2. Session test
echo "<h2>2. Session Test</h2>";
session_start();
echo "✅ Session started<br>";

// 3. Config loading test
echo "<h2>3. Config Loading Test</h2>";
$config_prod = __DIR__ . '/system/includes/config_production.php';
$config_dev = __DIR__ . '/system/includes/config.php';

if (file_exists($config_prod)) {
    require_once $config_prod;
    echo "✅ Production config loaded<br>";
} else {
    require_once $config_dev;
    echo "✅ Development config loaded<br>";
}

echo "DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'NOT DEFINED') . "<br>";

// 4. Database connection test
echo "<h2>4. Database Connection Test</h2>";
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

// 5. Data query test
echo "<h2>5. Data Query Test</h2>";
try {
    $sql = "SELECT * FROM myinfo ORDER BY no DESC LIMIT 10";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $items = $stmt->fetchAll();
    
    echo "✅ Query successful<br>";
    echo "Total records: " . count($items) . "<br>";
    
    if (count($items) > 0) {
        echo "<h3>Data:</h3>";
        echo "<table border='1'>";
        echo "<tr><th>No</th><th>Name</th><th>Email</th><th>Phone</th><th>Age</th><th>Birthday</th><th>Height</th><th>Weight</th></tr>";
        foreach ($items as $row) {
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

// 6. Header include test
echo "<h2>6. Header Include Test</h2>";
try {
    ob_start();
    require __DIR__ . '/system/includes/header.php';
    $header_content = ob_get_clean();
    echo "✅ Header included successfully<br>";
    echo "Header length: " . strlen($header_content) . " characters<br>";
} catch (Exception $e) {
    echo "❌ Header include failed: " . $e->getMessage() . "<br>";
}

// 7. Simple CRUD page test
echo "<h2>7. Simple CRUD Page Test</h2>";
echo "<div style='border: 1px solid #ccc; padding: 20px; margin: 20px;'>";
echo "<h1>Data List</h1>";

if (count($items) > 0) {
    echo "<table border='1' style='width: 100%;'>";
    echo "<thead>";
    echo "<tr>";
    echo "<th>No</th>";
    echo "<th>Name</th>";
    echo "<th>Age</th>";
    echo "<th>Birthday</th>";
    echo "<th>Height</th>";
    echo "<th>Weight</th>";
    echo "<th>Actions</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
    
    foreach ($items as $item) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($item['no']) . "</td>";
        echo "<td>" . htmlspecialchars($item['name']) . "</td>";
        echo "<td>" . htmlspecialchars($item['age']) . "</td>";
        echo "<td>" . htmlspecialchars($item['birthday']) . "</td>";
        echo "<td>" . htmlspecialchars($item['height']) . "</td>";
        echo "<td>" . htmlspecialchars($item['weight']) . "</td>";
        echo "<td>";
        echo "<a href='data_edit.php?id=" . $item['no'] . "'>Edit</a> ";
        echo "<a href='data_delete.php?id=" . $item['no'] . "' onclick='return confirm(\"Are you sure?\")'>Delete</a>";
        echo "</td>";
        echo "</tr>";
    }
    
    echo "</tbody>";
    echo "</table>";
} else {
    echo "<p>No records found</p>";
}

echo "<p><a href='data_create.php'>Add New</a></p>";
echo "</div>";

echo "<h2>✅ Debug Complete</h2>";
echo "<a href='modules/management/crud/data_list.php'>Go to Original CRUD Page</a>";
?> 