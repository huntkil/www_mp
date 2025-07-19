<?php

/**
 * 통합 테스트 스위트
 * 모든 시스템 컴포넌트의 기능을 테스트합니다.
 */

// 테스트 환경 설정
define('APP_ENV', 'test');
define('APP_DEBUG', true);

// 설정 파일 로드
$configFile = __DIR__ . '/../config/credentials/test.php';
if (file_exists($configFile)) {
    $config = require $configFile;
    // 테스트용 데이터베이스 설정
    $config['database']['database'] = __DIR__ . '/../tests/database/test.sqlite';
} else {
    // 기본 설정
    $config = [
        'database' => [
            'driver' => 'sqlite',
            'database' => __DIR__ . '/../tests/database/test.sqlite',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ]
    ];
}

// 에러 핸들러 설정
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// CLI 실행 확인
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from command line.');
}

class TestSuite
{
    private array $results = [];
    private int $totalTests = 0;
    private int $passedTests = 0;
    private int $failedTests = 0;
    private float $startTime;

    public function __construct()
    {
        $this->startTime = microtime(true);
        echo "🧪 통합 테스트 시작...\n\n";
    }

    /**
     * 테스트 실행
     */
    public function run(): void
    {
        $this->testEnvironment();
        $this->testBasicFunctions();
        $this->testFileSystem();
        $this->testConfiguration();
        $this->testDatabase();
        $this->testSecurity();
        $this->testPerformance();

        $this->printResults();
    }

    /**
     * 환경 테스트
     */
    private function testEnvironment(): void
    {
        echo "🌍 환경 테스트...\n";
        
        // PHP 버전 확인
        $phpVersion = PHP_VERSION;
        $this->assert(version_compare($phpVersion, '8.0.0', '>='), 'PHP 8.0.0 이상');
        
        // 필수 확장 확인
        $requiredExtensions = ['pdo', 'pdo_sqlite', 'json', 'curl', 'mbstring'];
        foreach ($requiredExtensions as $ext) {
            $this->assert(extension_loaded($ext), "확장: {$ext}");
        }
        
        echo "✅ 환경 테스트 완료\n\n";
    }

    /**
     * 기본 함수 테스트
     */
    private function testBasicFunctions(): void
    {
        echo "⚡ 기본 함수 테스트...\n";
        
        // JSON 함수
        $testData = ['test' => 'value'];
        $json = json_encode($testData);
        $decoded = json_decode($json, true);
        $this->assert($decoded === $testData, 'JSON 인코딩/디코딩');
        
        // 문자열 함수
        $test = '테스트';
        $this->assert(mb_strlen($test) === 3, 'MBString 함수');
        
        // 배열 함수
        $array = [1, 2, 3];
        $this->assert(count($array) === 3, '배열 함수');
        
        echo "✅ 기본 함수 테스트 완료\n\n";
    }

    /**
     * 파일 시스템 테스트
     */
    private function testFileSystem(): void
    {
        echo "📁 파일 시스템 테스트...\n";
        
        // 디렉토리 존재 확인
        $directories = [
            'system/uploads',
            'system/cache',
            'system/logs',
            'config',
            'tests/database'
        ];
        
        foreach ($directories as $dir) {
            $path = __DIR__ . '/../' . $dir;
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
            $this->assert(is_dir($path), "디렉토리: {$dir}");
            $this->assert(is_writable($path), "쓰기 권한: {$dir}");
        }
        
        // 파일 존재 확인
        $files = [
            'index.php',
            'health.php',
            'system/includes/config.php'
        ];
        
        foreach ($files as $file) {
            $path = __DIR__ . '/../' . $file;
            $this->assert(file_exists($path), "파일: {$file}");
        }
        
        echo "✅ 파일 시스템 테스트 완료\n\n";
    }

    /**
     * 설정 테스트
     */
    private function testConfiguration(): void
    {
        echo "⚙️ 설정 테스트...\n";
        
        $configFiles = [
            'config/credentials/test.php',
            'config/credentials/development.php'
        ];
        
        foreach ($configFiles as $file) {
            $path = __DIR__ . '/../' . $file;
            if (file_exists($path)) {
                try {
                    $config = require $path;
                    $this->assert(is_array($config), "설정 파일: {$file}");
                } catch (Exception $e) {
                    $this->assert(false, "설정 파일 로드 실패: {$file}");
                }
            }
        }
        
        echo "✅ 설정 테스트 완료\n\n";
    }

    /**
     * 데이터베이스 테스트
     */
    private function testDatabase(): void
    {
        echo "📊 데이터베이스 테스트...\n";
        
        try {
            // SQLite 데이터베이스 생성
            $dbPath = __DIR__ . '/../tests/database/test.sqlite';
            $pdo = new PDO("sqlite:{$dbPath}");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $this->assert($pdo instanceof PDO, 'PDO 연결');
            
            // 테이블 생성 테스트
            $sql = "CREATE TABLE IF NOT EXISTS test_table (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $this->assert($pdo->exec($sql) !== false, '테이블 생성');
            
            // 데이터 삽입 테스트
            $stmt = $pdo->prepare("INSERT INTO test_table (name) VALUES (?)");
            $this->assert($stmt->execute(['Test Data']), '데이터 삽입');
            
            // 데이터 조회 테스트
            $stmt = $pdo->prepare("SELECT * FROM test_table WHERE name = ?");
            $stmt->execute(['Test Data']);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->assert($result && $result['name'] === 'Test Data', '데이터 조회');
            
            // 테이블 정리
            $pdo->exec("DROP TABLE test_table");
            
            echo "✅ 데이터베이스 테스트 완료\n\n";
            
        } catch (Exception $e) {
            $this->assert(false, '데이터베이스 테스트: ' . $e->getMessage());
        }
    }

    /**
     * 보안 테스트
     */
    private function testSecurity(): void
    {
        echo "🔒 보안 테스트...\n";
        
        // CSRF 토큰 생성
        $token = bin2hex(random_bytes(32));
        $this->assert(strlen($token) === 64, 'CSRF 토큰 생성');
        
        // 비밀번호 해싱
        $password = 'test_password';
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $this->assert(password_verify($password, $hash), '비밀번호 해싱');
        
        // XSS 방지 테스트
        $input = '<script>alert("xss")</script>';
        $clean = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        $this->assert(strpos($clean, '<script>') === false, 'XSS 방지');
        
        echo "✅ 보안 테스트 완료\n\n";
    }

    /**
     * 성능 테스트
     */
    private function testPerformance(): void
    {
        echo "⚡ 성능 테스트...\n";
        
        try {
            // 데이터베이스 성능
            $dbPath = __DIR__ . '/../tests/database/test.sqlite';
            $pdo = new PDO("sqlite:{$dbPath}");
            
            $start = microtime(true);
            for ($i = 0; $i < 100; $i++) {
                $stmt = $pdo->prepare("SELECT 1");
                $stmt->execute();
            }
            $dbTime = microtime(true) - $start;
            $this->assert($dbTime < 1.0, '데이터베이스 성능');
            
            // 메모리 사용량 확인
            $memoryUsage = memory_get_usage(true);
            $this->assert($memoryUsage < 50 * 1024 * 1024, '메모리 사용량'); // 50MB 이하
            
            echo "✅ 성능 테스트 완료\n\n";
            
        } catch (Exception $e) {
            $this->assert(false, '성능 테스트: ' . $e->getMessage());
        }
    }

    /**
     * 테스트 결과 확인
     */
    private function assert(bool $condition, string $testName): void
    {
        $this->totalTests++;
        
        if ($condition) {
            $this->passedTests++;
            echo "  ✅ {$testName}\n";
        } else {
            $this->failedTests++;
            echo "  ❌ {$testName}\n";
        }
        
        $this->results[] = [
            'test' => $testName,
            'passed' => $condition
        ];
    }

    /**
     * 결과 출력
     */
    private function printResults(): void
    {
        $totalTime = microtime(true) - $this->startTime;
        
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "🧪 테스트 결과\n";
        echo str_repeat("=", 50) . "\n";
        echo "총 테스트: {$this->totalTests}\n";
        echo "성공: {$this->passedTests}\n";
        echo "실패: {$this->failedTests}\n";
        echo "성공률: " . round(($this->passedTests / $this->totalTests) * 100, 2) . "%\n";
        echo "소요 시간: " . round($totalTime, 2) . "초\n";
        
        if ($this->failedTests > 0) {
            echo "\n❌ 실패한 테스트:\n";
            foreach ($this->results as $result) {
                if (!$result['passed']) {
                    echo "  - {$result['test']}\n";
                }
            }
        }
        
        echo "\n" . ($this->failedTests === 0 ? "🎉 모든 테스트 통과!" : "⚠️ 일부 테스트 실패") . "\n";
    }
}

// 테스트 실행
$testSuite = new TestSuite();
$testSuite->run(); 