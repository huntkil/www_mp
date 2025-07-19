<?php
// DB 상태 확인 스크립트
// 호스팅에 업로드 후 실행하여 DB 연결 및 테이블 상태 확인

// Production config 사용
require_once 'system/includes/config_production.php';

echo "<h1>DB 상태 확인</h1>";

try {
    // DB 연결 테스트
    echo "<h2>1. DB 연결 테스트</h2>";
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    echo "✅ DB 연결 성공<br>";
    echo "DB 서버 버전: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "<br>";
    
    // 기존 테이블 목록 확인
    echo "<h2>2. 기존 테이블 목록</h2>";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "❌ 테이블이 없습니다.<br>";
    } else {
        echo "✅ 발견된 테이블:<br>";
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
    }
    
    // 필요한 테이블들 확인
    echo "<h2>3. 필요한 테이블 확인</h2>";
    $required_tables = [
        'myUser',           // 사용자 테이블
        'myInfo',           // CRUD 테이블
        'health_records'    // 건강 기록 테이블
    ];
    
    foreach ($required_tables as $table) {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        if ($stmt->rowCount() > 0) {
            echo "✅ $table 테이블 존재<br>";
            
            // 테이블 구조 확인
            $stmt2 = $pdo->query("DESCRIBE `$table`");
            $columns = $stmt2->fetchAll();
            echo "<details><summary>$table 테이블 구조</summary><ul>";
            foreach ($columns as $col) {
                echo "<li>{$col['Field']} - {$col['Type']} - {$col['Null']} - {$col['Key']}</li>";
            }
            echo "</ul></details>";
        } else {
            echo "❌ $table 테이블 없음<br>";
        }
    }
    
    // 샘플 데이터 확인
    echo "<h2>4. 샘플 데이터 확인</h2>";
    foreach ($required_tables as $table) {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        if ($stmt->rowCount() > 0) {
            $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            echo "$table 테이블: $count 개 레코드<br>";
            
            if ($count > 0) {
                $sample = $pdo->query("SELECT * FROM `$table` LIMIT 3")->fetchAll();
                echo "<details><summary>$table 샘플 데이터</summary><pre>";
                print_r($sample);
                echo "</pre></details>";
            }
        }
    }
    
} catch (PDOException $e) {
    echo "❌ DB 오류: " . $e->getMessage() . "<br>";
    echo "오류 코드: " . $e->getCode() . "<br>";
}

echo "<h2>5. 다음 단계</h2>";
echo "<ul>";
echo "<li>테이블이 없으면: <a href='db_setup.php'>DB 설정</a></li>";
echo "<li>테이블이 있으면: <a href='index.php'>메인 페이지</a></li>";
echo "</ul>";
?> 