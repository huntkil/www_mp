<?php
require_once __DIR__ . '/system/includes/config.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "Creating SQLite tables...\n";
    
    // myinfo 테이블 생성 (기존 테이블 삭제 후 재생성)
    $pdo->exec("DROP TABLE IF EXISTS myinfo");
    $sql = "CREATE TABLE myinfo (
        no INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        age INTEGER,
        birthday TEXT,
        height REAL,
        weight REAL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    echo "✓ myinfo table created\n";
    
    // myUser 테이블 생성
    $sql = "CREATE TABLE IF NOT EXISTS myUser (
        id TEXT PRIMARY KEY,
        password TEXT NOT NULL,
        email TEXT,
        name TEXT,
        status TEXT DEFAULT 'active',
        login_attempts INTEGER DEFAULT 0,
        last_attempt DATETIME,
        last_login DATETIME,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    echo "✓ myUser table created\n";
    
    // myhealth 테이블 생성
    $sql = "CREATE TABLE IF NOT EXISTS myhealth (
        no INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id TEXT,
        year INTEGER NOT NULL,
        month INTEGER NOT NULL,
        day INTEGER NOT NULL,
        dayofweek TEXT NOT NULL,
        running_time INTEGER,
        running_speed_start REAL,
        running_speed_end REAL,
        calories INTEGER,
        distance REAL,
        notes TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    echo "✓ myhealth table created\n";
    
    // vocabulary 테이블 생성
    $sql = "CREATE TABLE IF NOT EXISTS vocabulary (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id TEXT,
        word TEXT NOT NULL,
        meaning TEXT NOT NULL,
        example TEXT,
        language TEXT DEFAULT 'en',
        difficulty TEXT DEFAULT 'medium',
        learned INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    echo "✓ vocabulary table created\n";
    
    // 샘플 데이터 삽입 (myinfo)
    $sql = "INSERT OR IGNORE INTO myinfo (name, age, birthday, height, weight) VALUES 
        ('홍길동', 25, '1998-05-15', 175.5, 70.2),
        ('김철수', 30, '1993-03-22', 180.0, 75.5),
        ('이영희', 28, '1995-08-10', 165.3, 58.7)";
    
    $pdo->exec($sql);
    echo "✓ Sample data inserted\n";
    
    // 기본 관리자 계정 생성
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $sql = "INSERT OR IGNORE INTO myUser (id, password, email, name, status) VALUES 
        ('admin', ?, 'admin@example.com', 'Administrator', 'active')";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$adminPassword]);
    echo "✓ Admin account created (admin/admin123)\n";
    
    echo "\n✅ All tables created successfully!\n";
    echo "Database file: " . DB_FILE . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?> 