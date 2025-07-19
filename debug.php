<?php
// 간단한 디버깅 파일
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>PHP 디버깅 정보</h1>";

echo "<h2>1. PHP 버전</h2>";
echo "PHP 버전: " . phpversion() . "<br>";

echo "<h2>2. 서버 정보</h2>";
echo "서버 이름: " . ($_SERVER['SERVER_NAME'] ?? 'N/A') . "<br>";
echo "문서 루트: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "<br>";

echo "<h2>3. 파일 경로 테스트</h2>";
$config_path = __DIR__ . '/system/includes/config_production.php';
echo "Config 파일 경로: $config_path<br>";
echo "Config 파일 존재: " . (file_exists($config_path) ? '✅ 존재' : '❌ 없음') . "<br>";

echo "<h2>4. 디렉토리 권한 테스트</h2>";
$current_dir = __DIR__;
echo "현재 디렉토리: $current_dir<br>";
echo "읽기 권한: " . (is_readable($current_dir) ? '✅ 읽기 가능' : '❌ 읽기 불가') . "<br>";
echo "실행 권한: " . (is_executable($current_dir) ? '✅ 실행 가능' : '❌ 실행 불가') . "<br>";

echo "<h2>5. PDO 확장 확인</h2>";
echo "PDO 확장: " . (extension_loaded('pdo') ? '✅ 로드됨' : '❌ 로드 안됨') . "<br>";
echo "PDO MySQL 확장: " . (extension_loaded('pdo_mysql') ? '✅ 로드됨' : '❌ 로드 안됨') . "<br>";

echo "<h2>6. 파일 포함 테스트</h2>";
try {
    if (file_exists($config_path)) {
        include_once $config_path;
        echo "✅ Config 파일 포함 성공<br>";
        
        // DB 상수 확인
        echo "DB_HOST: " . (defined('DB_HOST') ? DB_HOST : '정의되지 않음') . "<br>";
        echo "DB_NAME: " . (defined('DB_NAME') ? DB_NAME : '정의되지 않음') . "<br>";
        echo "DB_USER: " . (defined('DB_USER') ? DB_USER : '정의되지 않음') . "<br>";
    } else {
        echo "❌ Config 파일을 찾을 수 없음<br>";
    }
} catch (Exception $e) {
    echo "❌ Config 파일 포함 오류: " . $e->getMessage() . "<br>";
}

echo "<h2>7. 간단한 DB 연결 테스트</h2>";
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=huntkil;charset=utf8mb4",
        "huntkil",
        "kil7310k4!",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ DB 연결 성공<br>";
    echo "DB 버전: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "<br>";
} catch (PDOException $e) {
    echo "❌ DB 연결 실패: " . $e->getMessage() . "<br>";
    echo "오류 코드: " . $e->getCode() . "<br>";
}
?> 