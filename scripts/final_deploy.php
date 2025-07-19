<?php

/**
 * 최종 배포 스크립트
 * 모든 배포 단계를 자동화하여 실행합니다.
 */

require_once __DIR__ . '/../system/includes/config.php';

use System\Includes\Database;
use System\Includes\BackupManager;
use System\Includes\DatabaseMigration;
use System\Includes\MonitoringSystem;

// CLI 실행 확인
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from command line.');
}

class FinalDeployer
{
    private array $config;
    private array $results = [];
    private float $startTime;
    private Logger $logger;

    public function __construct()
    {
        $this->startTime = microtime(true);
        $this->logger = new Logger('final_deploy');
        
        $this->config = [
            'backup_before_deploy' => true,
            'run_tests' => true,
            'migrate_database' => true,
            'clear_cache' => true,
            'health_check' => true,
            'notify_on_completion' => true
        ];
        
        echo "🚀 최종 배포 시작...\n\n";
    }

    /**
     * 배포 실행
     */
    public function deploy(): void
    {
        try {
            $this->preDeploymentChecks();
            $this->createBackup();
            $this->runTests();
            $this->migrateDatabase();
            $this->clearCache();
            $this->healthCheck();
            $this->postDeploymentTasks();
            
            $this->printResults();
            
        } catch (Exception $e) {
            $this->logger->error('배포 실패', ['error' => $e->getMessage()]);
            echo "❌ 배포 실패: {$e->getMessage()}\n";
            exit(1);
        }
    }

    /**
     * 배포 전 검사
     */
    private function preDeploymentChecks(): void
    {
        echo "🔍 배포 전 검사...\n";
        
        // 환경 확인
        $this->checkEnvironment();
        
        // 데이터베이스 연결 확인
        $this->checkDatabase();
        
        // 파일 권한 확인
        $this->checkFilePermissions();
        
        // 디스크 공간 확인
        $this->checkDiskSpace();
        
        echo "✅ 배포 전 검사 완료\n\n";
    }

    /**
     * 환경 확인
     */
    private function checkEnvironment(): void
    {
        // PHP 버전 확인
        $phpVersion = PHP_VERSION;
        if (version_compare($phpVersion, '8.0.0', '<')) {
            throw new Exception("PHP 8.0.0 이상이 필요합니다. 현재 버전: {$phpVersion}");
        }
        
        // 필수 확장 확인
        $requiredExtensions = ['pdo', 'pdo_sqlite', 'json', 'curl', 'zip'];
        foreach ($requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                throw new Exception("필수 PHP 확장이 없습니다: {$ext}");
            }
        }
        
        // 환경 변수 확인
        if (!defined('APP_ENV') || APP_ENV !== 'production') {
            echo "⚠️ 경고: 프로덕션 환경이 아닙니다. (APP_ENV: " . (defined('APP_ENV') ? APP_ENV : 'undefined') . ")\n";
        }
        
        $this->results['environment'] = 'passed';
    }

    /**
     * 데이터베이스 확인
     */
    private function checkDatabase(): void
    {
        try {
            $db = new Database();
            
            if (!$db->isConnected()) {
                throw new Exception('데이터베이스 연결 실패');
            }
            
            // 테이블 존재 확인
            $tables = ['users', 'vocabulary', 'health_records'];
            foreach ($tables as $table) {
                $stmt = $db->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name=?");
                $stmt->execute([$table]);
                if (!$stmt->fetch()) {
                    throw new Exception("필수 테이블이 없습니다: {$table}");
                }
            }
            
            $this->results['database'] = 'passed';
            
        } catch (Exception $e) {
            throw new Exception('데이터베이스 검사 실패: ' . $e->getMessage());
        }
    }

    /**
     * 파일 권한 확인
     */
    private function checkFilePermissions(): void
    {
        $directories = [
            'system/uploads' => 0755,
            'system/cache' => 0755,
            'system/logs' => 0755,
            'config' => 0755
        ];
        
        foreach ($directories as $dir => $permission) {
            $path = __DIR__ . '/../' . $dir;
            
            if (!is_dir($path)) {
                if (!mkdir($path, $permission, true)) {
                    throw new Exception("디렉토리를 생성할 수 없습니다: {$dir}");
                }
            }
            
            if (!is_writable($path)) {
                throw new Exception("디렉토리에 쓰기 권한이 없습니다: {$dir}");
            }
        }
        
        $this->results['permissions'] = 'passed';
    }

    /**
     * 디스크 공간 확인
     */
    private function checkDiskSpace(): void
    {
        $freeSpace = disk_free_space(__DIR__);
        $totalSpace = disk_total_space(__DIR__);
        $usedSpace = $totalSpace - $freeSpace;
        $usagePercent = ($usedSpace / $totalSpace) * 100;
        
        if ($usagePercent > 90) {
            throw new Exception("디스크 공간이 부족합니다. 사용률: " . round($usagePercent, 2) . "%");
        }
        
        echo "  💾 디스크 사용률: " . round($usagePercent, 2) . "%\n";
        $this->results['disk_space'] = 'passed';
    }

    /**
     * 백업 생성
     */
    private function createBackup(): void
    {
        if (!$this->config['backup_before_deploy']) {
            echo "⏭️ 백업 건너뛰기\n\n";
            return;
        }
        
        echo "💿 백업 생성...\n";
        
        try {
            $backup = new BackupManager();
            $result = $backup->createBackup('pre_deploy_' . date('Y-m-d_H-i-s'));
            
            if ($result['success']) {
                echo "✅ 백업 생성 완료: {$result['backup_file']}\n";
                $this->results['backup'] = 'passed';
            } else {
                throw new Exception('백업 생성 실패');
            }
            
        } catch (Exception $e) {
            throw new Exception('백업 생성 실패: ' . $e->getMessage());
        }
        
        echo "\n";
    }

    /**
     * 테스트 실행
     */
    private function runTests(): void
    {
        if (!$this->config['run_tests']) {
            echo "⏭️ 테스트 건너뛰기\n\n";
            return;
        }
        
        echo "🧪 테스트 실행...\n";
        
        try {
            $testScript = __DIR__ . '/test_suite.php';
            
            if (!file_exists($testScript)) {
                throw new Exception('테스트 스크립트를 찾을 수 없습니다');
            }
            
            $output = [];
            $returnCode = 0;
            
            exec("php {$testScript} 2>&1", $output, $returnCode);
            
            if ($returnCode !== 0) {
                throw new Exception('테스트 실패: ' . implode("\n", $output));
            }
            
            echo "✅ 모든 테스트 통과\n";
            $this->results['tests'] = 'passed';
            
        } catch (Exception $e) {
            throw new Exception('테스트 실행 실패: ' . $e->getMessage());
        }
        
        echo "\n";
    }

    /**
     * 데이터베이스 마이그레이션
     */
    private function migrateDatabase(): void
    {
        if (!$this->config['migrate_database']) {
            echo "⏭️ 마이그레이션 건너뛰기\n\n";
            return;
        }
        
        echo "🔄 데이터베이스 마이그레이션...\n";
        
        try {
            $migration = new DatabaseMigration();
            
            // 마이그레이션 상태 확인
            $status = $migration->getStatus();
            echo "  📊 현재 마이그레이션 상태: " . count($status['migrations']) . "개 실행됨\n";
            
            // 새로운 마이그레이션 실행
            $result = $migration->migrate();
            
            if ($result['success']) {
                echo "✅ 마이그레이션 완료: {$result['migrated_count']}개 실행됨\n";
                $this->results['migration'] = 'passed';
            } else {
                throw new Exception('마이그레이션 실패: ' . $result['error']);
            }
            
        } catch (Exception $e) {
            throw new Exception('마이그레이션 실패: ' . $e->getMessage());
        }
        
        echo "\n";
    }

    /**
     * 캐시 정리
     */
    private function clearCache(): void
    {
        if (!$this->config['clear_cache']) {
            echo "⏭️ 캐시 정리 건너뛰기\n\n";
            return;
        }
        
        echo "🧹 캐시 정리...\n";
        
        try {
            $cacheDirs = [
                'system/cache',
                'system/uploads/temp',
                'config/cache'
            ];
            
            foreach ($cacheDirs as $dir) {
                $path = __DIR__ . '/../' . $dir;
                if (is_dir($path)) {
                    $this->clearDirectory($path);
                    echo "  ✅ {$dir} 정리 완료\n";
                }
            }
            
            $this->results['cache_clear'] = 'passed';
            
        } catch (Exception $e) {
            throw new Exception('캐시 정리 실패: ' . $e->getMessage());
        }
        
        echo "\n";
    }

    /**
     * 디렉토리 정리
     */
    private function clearDirectory(string $path): void
    {
        $files = glob($path . '/*');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            } elseif (is_dir($file)) {
                $this->clearDirectory($file);
                rmdir($file);
            }
        }
    }

    /**
     * 헬스 체크
     */
    private function healthCheck(): void
    {
        if (!$this->config['health_check']) {
            echo "⏭️ 헬스 체크 건너뛰기\n\n";
            return;
        }
        
        echo "🏥 헬스 체크...\n";
        
        try {
            $healthScript = __DIR__ . '/../health.php';
            
            if (!file_exists($healthScript)) {
                throw new Exception('헬스 체크 스크립트를 찾을 수 없습니다');
            }
            
            $output = [];
            $returnCode = 0;
            
            exec("php {$healthScript} 2>&1", $output, $returnCode);
            
            if ($returnCode !== 0) {
                throw new Exception('헬스 체크 실패: ' . implode("\n", $output));
            }
            
            echo "✅ 시스템 상태 정상\n";
            $this->results['health_check'] = 'passed';
            
        } catch (Exception $e) {
            throw new Exception('헬스 체크 실패: ' . $e->getMessage());
        }
        
        echo "\n";
    }

    /**
     * 배포 후 작업
     */
    private function postDeploymentTasks(): void
    {
        echo "🔧 배포 후 작업...\n";
        
        // 로그 파일 정리
        $this->cleanupLogs();
        
        // 임시 파일 정리
        $this->cleanupTempFiles();
        
        // 권한 재설정
        $this->setPermissions();
        
        echo "✅ 배포 후 작업 완료\n\n";
    }

    /**
     * 로그 파일 정리
     */
    private function cleanupLogs(): void
    {
        $logDir = __DIR__ . '/../system/logs';
        
        if (is_dir($logDir)) {
            $files = glob($logDir . '/*.log');
            
            foreach ($files as $file) {
                $fileSize = filesize($file);
                if ($fileSize > 10 * 1024 * 1024) { // 10MB 이상
                    $backupFile = $file . '.backup';
                    rename($file, $backupFile);
                    file_put_contents($file, ''); // 빈 파일 생성
                    echo "  📝 로그 파일 정리: " . basename($file) . "\n";
                }
            }
        }
    }

    /**
     * 임시 파일 정리
     */
    private function cleanupTempFiles(): void
    {
        $tempDirs = [
            __DIR__ . '/../system/uploads/temp',
            __DIR__ . '/../system/cache'
        ];
        
        foreach ($tempDirs as $dir) {
            if (is_dir($dir)) {
                $files = glob($dir . '/*');
                $count = 0;
                
                foreach ($files as $file) {
                    if (is_file($file) && time() - filemtime($file) > 86400) { // 24시간 이상
                        unlink($file);
                        $count++;
                    }
                }
                
                if ($count > 0) {
                    echo "  🗑️ 임시 파일 정리: {$count}개 삭제됨\n";
                }
            }
        }
    }

    /**
     * 권한 설정
     */
    private function setPermissions(): void
    {
        $permissions = [
            'system/uploads' => 0755,
            'system/cache' => 0755,
            'system/logs' => 0755,
            'config' => 0755
        ];
        
        foreach ($permissions as $dir => $permission) {
            $path = __DIR__ . '/../' . $dir;
            if (is_dir($path)) {
                chmod($path, $permission);
            }
        }
        
        echo "  🔐 파일 권한 설정 완료\n";
    }

    /**
     * 결과 출력
     */
    private function printResults(): void
    {
        $totalTime = microtime(true) - $this->startTime;
        
        echo str_repeat("=", 50) . "\n";
        echo "🎉 배포 완료!\n";
        echo str_repeat("=", 50) . "\n";
        
        foreach ($this->results as $step => $status) {
            $icon = $status === 'passed' ? '✅' : '❌';
            echo "{$icon} {$step}: {$status}\n";
        }
        
        echo "\n⏱️ 총 소요 시간: " . round($totalTime, 2) . "초\n";
        echo "📅 배포 완료 시간: " . date('Y-m-d H:i:s') . "\n";
        
        if ($this->config['notify_on_completion']) {
            $this->sendNotification();
        }
        
        echo "\n🚀 배포가 성공적으로 완료되었습니다!\n";
        echo "🌐 사이트 URL: https://gukho.net/mp/\n";
        echo "📊 모니터링: https://gukho.net/mp/health.php\n";
    }

    /**
     * 알림 전송
     */
    private function sendNotification(): void
    {
        try {
            $message = "배포가 완료되었습니다.\n";
            $message .= "시간: " . date('Y-m-d H:i:s') . "\n";
            $message .= "URL: https://gukho.net/mp/\n";
            
            // 이메일 알림 (설정된 경우)
            if (defined('ADMIN_EMAIL')) {
                mail(ADMIN_EMAIL, '배포 완료 알림', $message);
            }
            
            echo "📧 알림 전송 완료\n";
            
        } catch (Exception $e) {
            echo "⚠️ 알림 전송 실패: {$e->getMessage()}\n";
        }
    }
}

// 명령행 인수 처리
$options = getopt('', ['skip-backup', 'skip-tests', 'skip-migration', 'skip-cache', 'skip-health']);

$deployer = new FinalDeployer();

// 옵션 적용
if (isset($options['skip-backup'])) {
    $deployer->config['backup_before_deploy'] = false;
}
if (isset($options['skip-tests'])) {
    $deployer->config['run_tests'] = false;
}
if (isset($options['skip-migration'])) {
    $deployer->config['migrate_database'] = false;
}
if (isset($options['skip-cache'])) {
    $deployer->config['clear_cache'] = false;
}
if (isset($options['skip-health'])) {
    $deployer->config['health_check'] = false;
}

// 배포 실행
$deployer->deploy(); 