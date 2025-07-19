<?php
require "../../../system/includes/config.php";

try {
    $db = Database::getInstance();
    
    // SQLite doesn't support ALTER TABLE ADD COLUMN easily, so we'll recreate the table
    // First, backup existing data
    $existingData = $db->query("SELECT * FROM myinfo")->fetchAll();
    
    // Drop the old table
    $db->query("DROP TABLE IF EXISTS myinfo");
    
    // Create new table with email and phone columns
    $sql = "CREATE TABLE myinfo (
        no INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL,
        phone TEXT NOT NULL,
        age INTEGER,
        birthday TEXT,
        height REAL,
        weight REAL
    )";
    
    $db->query($sql);
    echo "Recreated table with email and phone columns\n";
    
    // Reinsert existing data with default values for new columns
    if (!empty($existingData)) {
        foreach ($existingData as $row) {
            $sql = "INSERT INTO myinfo (name, email, phone, age, birthday, height, weight) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $db->query($sql, [
                $row['name'],
                'default@example.com', // Default email
                '000-000-0000',        // Default phone
                $row['age'],
                $row['birthday'],
                $row['height'],
                $row['weight']
            ]);
        }
        echo "Restored " . count($existingData) . " existing records\n";
    }
    
    echo "Table update completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 