<?php
// DB 설정 스크립트
// 필요한 테이블들을 생성합니다

// Production config 사용
require_once 'system/includes/config_production.php';

echo "<h1>DB 설정</h1>";

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "✅ DB 연결 성공<br><br>";
    
    // 1. myUser 테이블 생성 (사용자 관리)
    echo "<h2>1. myUser 테이블 생성</h2>";
    $sql = "
    CREATE TABLE IF NOT EXISTS `myUser` (
        `id` varchar(50) NOT NULL,
        `password` varchar(255) NOT NULL,
        `name` varchar(100) NOT NULL,
        `email` varchar(100) DEFAULT NULL,
        `role` enum('admin','user') DEFAULT 'user',
        `status` enum('active','inactive') DEFAULT 'active',
        `login_attempts` int(11) DEFAULT 0,
        `last_login` datetime DEFAULT NULL,
        `last_attempt` datetime DEFAULT NULL,
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($sql);
    echo "✅ myUser 테이블 생성 완료<br>";
    
    // 2. myInfo 테이블 생성 (CRUD 데모)
    echo "<h2>2. myInfo 테이블 생성</h2>";
    $sql = "
    CREATE TABLE IF NOT EXISTS `myInfo` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL,
        `email` varchar(100) DEFAULT NULL,
        `phone` varchar(20) DEFAULT NULL,
        `address` text DEFAULT NULL,
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($sql);
    echo "✅ myInfo 테이블 생성 완료<br>";
    
    // 3. health_records 테이블 생성 (건강 기록)
    echo "<h2>3. health_records 테이블 생성</h2>";
    $sql = "
    CREATE TABLE IF NOT EXISTS `health_records` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` varchar(50) NOT NULL,
        `activity_type` varchar(50) NOT NULL,
        `duration_minutes` int(11) DEFAULT NULL,
        `distance_km` decimal(5,2) DEFAULT NULL,
        `calories_burned` int(11) DEFAULT NULL,
        `notes` text DEFAULT NULL,
        `activity_date` date NOT NULL,
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`),
        KEY `activity_date` (`activity_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($sql);
    echo "✅ health_records 테이블 생성 완료<br>";
    
    // 4. 기본 관리자 계정 생성
    echo "<h2>4. 기본 관리자 계정 생성</h2>";
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    
    $sql = "
    INSERT IGNORE INTO `myUser` (`id`, `password`, `name`, `email`, `role`, `status`) 
    VALUES ('admin', ?, 'Administrator', 'admin@example.com', 'admin', 'active')
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$admin_password]);
    
    if ($stmt->rowCount() > 0) {
        echo "✅ 관리자 계정 생성 완료 (ID: admin, PW: admin123)<br>";
    } else {
        echo "ℹ️ 관리자 계정이 이미 존재합니다<br>";
    }
    
    // 5. 샘플 데이터 생성
    echo "<h2>5. 샘플 데이터 생성</h2>";
    
    // myInfo 샘플 데이터
    $sql = "
    INSERT IGNORE INTO `myInfo` (`name`, `email`, `phone`, `address`) VALUES
    ('홍길동', 'hong@example.com', '010-1234-5678', '서울시 강남구'),
    ('김철수', 'kim@example.com', '010-2345-6789', '부산시 해운대구'),
    ('이영희', 'lee@example.com', '010-3456-7890', '대구시 수성구')
    ";
    
    $pdo->exec($sql);
    echo "✅ myInfo 샘플 데이터 생성 완료<br>";
    
    // health_records 샘플 데이터
    $sql = "
    INSERT IGNORE INTO `health_records` (`user_id`, `activity_type`, `duration_minutes`, `distance_km`, `calories_burned`, `activity_date`) VALUES
    ('admin', 'running', 30, 5.0, 300, CURDATE()),
    ('admin', 'walking', 45, 3.5, 180, DATE_SUB(CURDATE(), INTERVAL 1 DAY)),
    ('admin', 'cycling', 60, 15.0, 400, DATE_SUB(CURDATE(), INTERVAL 2 DAY))
    ";
    
    $pdo->exec($sql);
    echo "✅ health_records 샘플 데이터 생성 완료<br>";
    
    echo "<h2>✅ DB 설정 완료!</h2>";
    echo "<p>이제 <a href='index.php'>메인 페이지</a>로 이동하여 서비스를 이용할 수 있습니다.</p>";
    echo "<p>관리자 로그인: ID: admin, PW: admin123</p>";
    
} catch (PDOException $e) {
    echo "❌ DB 오류: " . $e->getMessage() . "<br>";
    echo "오류 코드: " . $e->getCode() . "<br>";
}
?> 