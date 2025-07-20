<?php
/**
 * 개발 환경 설정 스크립트
 * 
 * 이 스크립트는 개발 환경을 설정하고 필요한 의존성을 설치합니다.
 * 
 * 사용법:
 * php scripts/setup_dev_environment.php
 */

echo "🚀 MP Learning Platform 개발 환경 설정을 시작합니다...\n\n";

// 1. PHP 버전 확인
echo "1. PHP 버전 확인 중...\n";
if (version_compare(PHP_VERSION, '8.0.0', '<')) {
    echo "❌ PHP 8.0.0 이상이 필요합니다. 현재 버전: " . PHP_VERSION . "\n";
    echo "   XAMPP 또는 PHP를 설치해주세요.\n";
    echo "   - XAMPP: https://www.apachefriends.org/download.html\n";
    echo "   - PHP: https://windows.php.net/download/\n\n";
    exit(1);
} else {
    echo "✅ PHP 버전 확인 완료: " . PHP_VERSION . "\n\n";
}

// 2. 필요한 PHP 확장 확인
echo "2. PHP 확장 확인 중...\n";
$required_extensions = [
    'pdo',
    'pdo_sqlite',
    'mbstring',
    'json',
    'openssl',
    'session'
];

$missing_extensions = [];
foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $missing_extensions[] = $ext;
    }
}

if (!empty($missing_extensions)) {
    echo "❌ 다음 PHP 확장이 필요합니다: " . implode(', ', $missing_extensions) . "\n";
    echo "   php.ini 파일에서 해당 확장을 활성화해주세요.\n\n";
    exit(1);
} else {
    echo "✅ 모든 필수 PHP 확장이 설치되어 있습니다.\n\n";
}

// 3. 디렉토리 권한 확인
echo "3. 디렉토리 권한 확인 중...\n";
$directories = [
    'config/logs',
    'resources/uploads',
    'system/logs',
    'system/sessions'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "📁 디렉토리 생성: $dir\n";
    }
    
    if (!is_writable($dir)) {
        echo "⚠️  쓰기 권한 필요: $dir\n";
    } else {
        echo "✅ 권한 확인: $dir\n";
    }
}
echo "\n";

// 4. 데이터베이스 초기화
echo "4. 데이터베이스 초기화 중...\n";
$db_file = 'config/database.sqlite';
if (!file_exists($db_file)) {
    echo "📁 SQLite 데이터베이스 파일이 없습니다. 생성 중...\n";
    touch($db_file);
    chmod($db_file, 0666);
}

try {
    $pdo = new PDO("sqlite:$db_file");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ 데이터베이스 연결 성공\n";
    
    // 테이블 존재 확인
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    echo "📊 현재 테이블 수: " . count($tables) . "\n";
    
} catch (Exception $e) {
    echo "❌ 데이터베이스 연결 실패: " . $e->getMessage() . "\n";
    exit(1);
}
echo "\n";

// 5. Composer 의존성 확인
echo "5. Composer 의존성 확인 중...\n";
if (file_exists('composer.json')) {
    if (!file_exists('vendor/autoload.php')) {
        echo "📦 Composer 의존성 설치가 필요합니다.\n";
        echo "   다음 명령어를 실행하세요:\n";
        echo "   composer install\n\n";
    } else {
        echo "✅ Composer 의존성이 설치되어 있습니다.\n\n";
    }
} else {
    echo "⚠️  composer.json 파일이 없습니다.\n\n";
}

// 6. 환경 설정 확인
echo "6. 환경 설정 확인 중...\n";
if (file_exists('config/credentials/development.php')) {
    echo "✅ 개발 환경 설정 파일이 존재합니다.\n";
} else {
    echo "❌ 개발 환경 설정 파일이 없습니다.\n";
    echo "   config/credentials/sample.php를 development.php로 복사해주세요.\n";
    exit(1);
}

// 7. 테스트 실행
echo "7. 기본 테스트 실행 중...\n";
try {
    // 간단한 테스트
    $test_result = true;
    
    // 설정 파일 로드 테스트
    require_once 'system/includes/config.php';
    echo "✅ 설정 파일 로드 성공\n";
    
    // 데이터베이스 테스트
    if (defined('DB_FILE') && file_exists(DB_FILE)) {
        echo "✅ 데이터베이스 파일 접근 가능\n";
    } else {
        echo "⚠️  데이터베이스 파일 접근 불가\n";
        $test_result = false;
    }
    
    if ($test_result) {
        echo "✅ 모든 기본 테스트 통과\n";
    } else {
        echo "⚠️  일부 테스트 실패\n";
    }
    
} catch (Exception $e) {
    echo "❌ 테스트 실패: " . $e->getMessage() . "\n";
}
echo "\n";

// 8. 개발 서버 실행 안내
echo "8. 개발 서버 실행 안내\n";
echo "✅ 개발 환경 설정이 완료되었습니다!\n\n";
echo "🚀 개발 서버를 시작하려면 다음 명령어를 실행하세요:\n";
echo "   php -S localhost:8080\n\n";
echo "🌐 브라우저에서 다음 주소로 접속하세요:\n";
echo "   http://localhost:8080\n\n";
echo "📚 추가 정보:\n";
echo "   - 관리자 계정: admin / admin123\n";
echo "   - 로그 파일: config/logs/\n";
echo "   - 업로드 디렉토리: resources/uploads/\n\n";

echo "🎉 개발 환경 설정이 완료되었습니다!\n";
?> 