<?php

/**
 * 헬스 체크 엔드포인트
 * 모니터링 시스템에서 애플리케이션 상태를 확인하기 위한 엔드포인트
 */

require_once 'system/includes/config.php';

use System\Includes\MonitoringSystem;
use System\Includes\DatabaseMigration;

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

$startTime = microtime(true);

try {
    // 기본 상태 정보
    $health = [
        'status' => 'healthy',
        'timestamp' => date('c'),
        'version' => '1.0.0',
        'environment' => $_ENV['APP_ENV'] ?? 'production',
        'checks' => []
    ];

    // 1. 데이터베이스 연결 확인
    $dbCheck = checkDatabase();
    $health['checks']['database'] = $dbCheck;
    if (!$dbCheck['healthy']) {
        $health['status'] = 'unhealthy';
    }

    // 2. 파일 시스템 확인
    $fsCheck = checkFileSystem();
    $health['checks']['filesystem'] = $fsCheck;
    if (!$fsCheck['healthy']) {
        $health['status'] = 'unhealthy';
    }

    // 3. 메모리 사용량 확인
    $memoryCheck = checkMemory();
    $health['checks']['memory'] = $memoryCheck;
    if (!$memoryCheck['healthy']) {
        $health['status'] = 'warning';
    }

    // 4. 디스크 사용량 확인
    $diskCheck = checkDisk();
    $health['checks']['disk'] = $diskCheck;
    if (!$diskCheck['healthy']) {
        $health['status'] = 'warning';
    }

    // 5. 세션 상태 확인
    $sessionCheck = checkSessions();
    $health['checks']['sessions'] = $sessionCheck;

    // 6. 로그 파일 확인
    $logCheck = checkLogs();
    $health['checks']['logs'] = $logCheck;

    // 7. 마이그레이션 상태 확인
    $migrationCheck = checkMigrations();
    $health['checks']['migrations'] = $migrationCheck;

    // 8. 외부 서비스 확인
    $externalCheck = checkExternalServices();
    $health['checks']['external'] = $externalCheck;

    // 응답 시간 계산
    $health['response_time'] = round((microtime(true) - $startTime) * 1000, 2);

    // HTTP 상태 코드 설정
    $statusCode = match($health['status']) {
        'healthy' => 200,
        'warning' => 200,
        'unhealthy' => 503,
        default => 500
    };

    http_response_code($statusCode);

    echo json_encode($health, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'timestamp' => date('c'),
        'error' => $e->getMessage(),
        'response_time' => round((microtime(true) - $startTime) * 1000, 2)
    ], JSON_PRETTY_PRINT);
}

/**
 * 데이터베이스 연결 확인
 */
function checkDatabase(): array
{
    try {
        $pdo = new PDO(
            'sqlite:' . __DIR__ . '/config/database.sqlite',
            null,
            null,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        // 연결 테스트
        $stmt = $pdo->query('SELECT 1');
        $result = $stmt->fetch();

        // 테이블 존재 확인
        $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
        
        // 데이터베이스 크기 확인
        $dbSize = filesize(__DIR__ . '/config/database.sqlite');

        return [
            'healthy' => true,
            'connected' => true,
            'tables_count' => count($tables),
            'size_mb' => round($dbSize / 1024 / 1024, 2),
            'tables' => $tables
        ];
    } catch (Exception $e) {
        return [
            'healthy' => false,
            'connected' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * 파일 시스템 확인
 */
function checkFileSystem(): array
{
    $paths = [
        'config' => __DIR__ . '/config/',
        'system' => __DIR__ . '/system/',
        'modules' => __DIR__ . '/modules/',
        'resources' => __DIR__ . '/resources/',
        'backups' => __DIR__ . '/backups/',
        'logs' => __DIR__ . '/config/logs/'
    ];

    $results = [];
    $allHealthy = true;

    foreach ($paths as $name => $path) {
        $exists = is_dir($path);
        $writable = is_writable($path);
        $results[$name] = [
            'exists' => $exists,
            'writable' => $writable,
            'healthy' => $exists && $writable
        ];

        if (!$exists || !$writable) {
            $allHealthy = false;
        }
    }

    return [
        'healthy' => $allHealthy,
        'paths' => $results
    ];
}

/**
 * 메모리 사용량 확인
 */
function checkMemory(): array
{
    $memoryLimit = ini_get('memory_limit');
    $memoryUsage = memory_get_usage(true);
    $peakUsage = memory_get_peak_usage(true);

    $limitBytes = convertToBytes($memoryLimit);
    $usagePercent = $limitBytes > 0 ? ($memoryUsage / $limitBytes) * 100 : 0;

    return [
        'healthy' => $usagePercent < 80,
        'limit' => $memoryLimit,
        'usage_mb' => round($memoryUsage / 1024 / 1024, 2),
        'peak_mb' => round($peakUsage / 1024 / 1024, 2),
        'usage_percent' => round($usagePercent, 2),
        'warning' => $usagePercent > 70
    ];
}

/**
 * 디스크 사용량 확인
 */
function checkDisk(): array
{
    $path = __DIR__;
    $totalSpace = disk_total_space($path);
    $freeSpace = disk_free_space($path);
    $usedSpace = $totalSpace - $freeSpace;
    $usagePercent = ($usedSpace / $totalSpace) * 100;

    return [
        'healthy' => $usagePercent < 90,
        'total_gb' => round($totalSpace / 1024 / 1024 / 1024, 2),
        'free_gb' => round($freeSpace / 1024 / 1024 / 1024, 2),
        'used_gb' => round($usedSpace / 1024 / 1024 / 1024, 2),
        'usage_percent' => round($usagePercent, 2),
        'warning' => $usagePercent > 80
    ];
}

/**
 * 세션 상태 확인
 */
function checkSessions(): array
{
    $sessionPath = __DIR__ . '/config/sessions/';
    $sessions = [];
    $activeCount = 0;
    $totalSize = 0;

    if (is_dir($sessionPath)) {
        $files = glob($sessionPath . 'sess_*');
        
        foreach ($files as $file) {
            $mtime = filemtime($file);
            $size = filesize($file);
            $age = time() - $mtime;
            
            $sessions[] = [
                'file' => basename($file),
                'size' => $size,
                'age_seconds' => $age,
                'active' => $age < 3600 // 1시간 이내
            ];

            $totalSize += $size;
            if ($age < 3600) {
                $activeCount++;
            }
        }
    }

    return [
        'healthy' => true,
        'total_sessions' => count($sessions),
        'active_sessions' => $activeCount,
        'total_size_mb' => round($totalSize / 1024 / 1024, 2),
        'sessions' => array_slice($sessions, 0, 10) // 최대 10개만 반환
    ];
}

/**
 * 로그 파일 확인
 */
function checkLogs(): array
{
    $logPath = __DIR__ . '/config/logs/';
    $logs = [];
    $totalSize = 0;

    if (is_dir($logPath)) {
        $files = glob($logPath . '*.log');
        
        foreach ($files as $file) {
            $size = filesize($file);
            $mtime = filemtime($file);
            
            $logs[] = [
                'file' => basename($file),
                'size_mb' => round($size / 1024 / 1024, 2),
                'last_modified' => date('Y-m-d H:i:s', $mtime),
                'age_hours' => round((time() - $mtime) / 3600, 1)
            ];

            $totalSize += $size;
        }
    }

    return [
        'healthy' => $totalSize < 100 * 1024 * 1024, // 100MB 미만
        'total_files' => count($logs),
        'total_size_mb' => round($totalSize / 1024 / 1024, 2),
        'logs' => $logs
    ];
}

/**
 * 마이그레이션 상태 확인
 */
function checkMigrations(): array
{
    try {
        $pdo = new PDO(
            'sqlite:' . __DIR__ . '/config/database.sqlite',
            null,
            null,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        // 마이그레이션 테이블 존재 확인
        $migrationsTable = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='migrations'")->fetch();
        
        if (!$migrationsTable) {
            return [
                'healthy' => true,
                'migrations_table_exists' => false,
                'executed_migrations' => 0,
                'pending_migrations' => 0
            ];
        }

        // 실행된 마이그레이션 수
        $executedCount = $pdo->query("SELECT COUNT(*) FROM migrations")->fetchColumn();

        // 마이그레이션 파일 수
        $migrationFiles = glob(__DIR__ . '/database/migrations/*.sql');
        $totalMigrations = count($migrationFiles);

        return [
            'healthy' => true,
            'migrations_table_exists' => true,
            'executed_migrations' => $executedCount,
            'total_migrations' => $totalMigrations,
            'pending_migrations' => max(0, $totalMigrations - $executedCount)
        ];
    } catch (Exception $e) {
        return [
            'healthy' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * 외부 서비스 확인
 */
function checkExternalServices(): array
{
    $services = [
        'github' => 'https://api.github.com',
        'newsapi' => 'https://newsapi.org/v2/top-headlines?country=kr&apiKey=test'
    ];

    $results = [];
    $allHealthy = true;

    foreach ($services as $name => $url) {
        $startTime = microtime(true);
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_NOBODY => true
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $responseTime = (microtime(true) - $startTime) * 1000;
        curl_close($ch);

        $healthy = !$error && $httpCode < 500;
        $results[$name] = [
            'healthy' => $healthy,
            'http_code' => $httpCode,
            'response_time_ms' => round($responseTime, 2),
            'error' => $error ?: null
        ];

        if (!$healthy) {
            $allHealthy = false;
        }
    }

    return [
        'healthy' => $allHealthy,
        'services' => $results
    ];
}

/**
 * 바이트 단위 변환
 */
function convertToBytes(string $value): int
{
    $value = trim($value);
    $last = strtolower($value[strlen($value) - 1]);
    $value = (int) $value;
    
    return match($last) {
        'g' => $value * 1024 * 1024 * 1024,
        'm' => $value * 1024 * 1024,
        'k' => $value * 1024,
        default => $value
    };
} 