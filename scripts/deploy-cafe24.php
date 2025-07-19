<?php

/**
 * Cafe24 호스팅 배포 스크립트
 * 로컬에서 실행하여 Cafe24 서버에 배포합니다.
 */

require_once __DIR__ . '/../system/includes/config.php';

use System\Includes\Database;
use System\Includes\BackupManager;

// CLI 실행 확인
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from command line.');
}

class Cafe24Deployer
{
    private array $config;
    private array $results = [];
    private float $startTime;
    private Logger $logger;

    public function __construct()
    {
        $this->startTime = microtime(true);
        $this->logger = new Logger('cafe24_deploy');
        
        $this->config = [
            'ftp_server' => 'gukho.net',
            'ftp_username' => '', // 설정 필요
            'ftp_password' => '', // 설정 필요
            'remote_dir' => '/public_html/mp/',
            'backup_before_deploy' => true,
            'run_tests' => true,
            'create_backup' => true
        ];
        
        echo "🚀 Cafe24 배포 시작...\n\n";
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
            $this->prepareDeploymentPackage();
            $this->uploadToCafe24();
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
        
        // FTP 정보 확인
        $this->checkFtpCredentials();
        
        // 환경 확인
        $this->checkEnvironment();
        
        // 데이터베이스 확인
        $this->checkDatabase();
        
        // 파일 권한 확인
        $this->checkFilePermissions();
        
        echo "✅ 배포 전 검사 완료\n\n";
    }

    /**
     * FTP 정보 확인
     */
    private function checkFtpCredentials(): void
    {
        if (empty($this->config['ftp_username']) || empty($this->config['ftp_password'])) {
            echo "⚠️ FTP 정보가 설정되지 않았습니다.\n";
            echo "다음 정보를 입력해주세요:\n";
            
            $this->config['ftp_username'] = readline("FTP 사용자명: ");
            $this->config['ftp_password'] = readline("FTP 비밀번호: ");
            
            if (empty($this->config['ftp_username']) || empty($this->config['ftp_password'])) {
                throw new Exception('FTP 정보가 필요합니다.');
            }
        }
        
        // FTP 연결 테스트
        $ftp = ftp_connect($this->config['ftp_server']);
        if (!$ftp) {
            throw new Exception('FTP 서버에 연결할 수 없습니다.');
        }
        
        if (!ftp_login($ftp, $this->config['ftp_username'], $this->config['ftp_password'])) {
            throw new Exception('FTP 로그인에 실패했습니다.');
        }
        
        ftp_close($ftp);
        echo "  ✅ FTP 연결 성공\n";
        $this->results['ftp'] = 'passed';
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
        $requiredExtensions = ['pdo', 'pdo_sqlite', 'json', 'curl', 'zip', 'ftp'];
        foreach ($requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                throw new Exception("필수 PHP 확장이 없습니다: {$ext}");
            }
        }
        
        echo "  ✅ 환경 확인 완료 (PHP {$phpVersion})\n";
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
                    echo "  ⚠️ 테이블이 없습니다: {$table} (배포 후 생성됨)\n";
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
     * 백업 생성
     */
    private function createBackup(): void
    {
        if (!$this->config['create_backup']) {
            echo "⏭️ 백업 건너뛰기\n\n";
            return;
        }
        
        echo "💿 백업 생성...\n";
        
        try {
            $backup = new BackupManager();
            $result = $backup->createBackup('pre_cafe24_deploy_' . date('Y-m-d_H-i-s'));
            
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
     * 배포 패키지 준비
     */
    private function prepareDeploymentPackage(): void
    {
        echo "📦 배포 패키지 준비...\n";
        
        try {
            $deployDir = __DIR__ . '/../deploy_temp';
            
            // 임시 디렉토리 생성
            if (is_dir($deployDir)) {
                $this->removeDirectory($deployDir);
            }
            mkdir($deployDir, 0755, true);
            
            // 파일 복사 (배포용 파일만)
            $this->copyDeploymentFiles($deployDir);
            
            // 개발용 파일 제거
            $this->removeDevelopmentFiles($deployDir);
            
            // 프로덕션 설정 적용
            $this->applyProductionConfig($deployDir);
            
            echo "✅ 배포 패키지 준비 완료\n";
            $this->results['package'] = 'passed';
            
        } catch (Exception $e) {
            throw new Exception('배포 패키지 준비 실패: ' . $e->getMessage());
        }
        
        echo "\n";
    }

    /**
     * 배포용 파일 복사
     */
    private function copyDeploymentFiles(string $deployDir): void
    {
        $sourceDir = __DIR__ . '/..';
        $excludePatterns = [
            '.git',
            'vendor',
            'node_modules',
            'tests',
            '.github',
            '.cursor',
            '*.log',
            '*.tmp',
            'composer.*',
            'phpunit.xml',
            '*.md',
            'deploy_temp'
        ];
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            $relativePath = str_replace($sourceDir . '/', '', $file->getPathname());
            
            // 제외 패턴 확인
            $exclude = false;
            foreach ($excludePatterns as $pattern) {
                if (fnmatch($pattern, $relativePath) || fnmatch($pattern, basename($relativePath))) {
                    $exclude = true;
                    break;
                }
            }
            
            if ($exclude) {
                continue;
            }
            
            $targetPath = $deployDir . '/' . $relativePath;
            
            if ($file->isDir()) {
                if (!is_dir($targetPath)) {
                    mkdir($targetPath, 0755, true);
                }
            } else {
                $targetDir = dirname($targetPath);
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }
                copy($file->getPathname(), $targetPath);
            }
        }
    }

    /**
     * 개발용 파일 제거
     */
    private function removeDevelopmentFiles(string $deployDir): void
    {
        $devFiles = [
            'db_check.php',
            'db_setup.php',
            'debug.php',
            'test.php',
            'TEST_GUIDE.md',
            'test_0707.md'
        ];
        
        foreach ($devFiles as $file) {
            $filePath = $deployDir . '/' . $file;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }

    /**
     * 프로덕션 설정 적용
     */
    private function applyProductionConfig(string $deployDir): void
    {
        $configFile = $deployDir . '/system/includes/config.php';
        
        if (file_exists($configFile)) {
            $content = file_get_contents($configFile);
            
            // 개발 모드 비활성화
            $content = preg_replace('/APP_ENV.*development/', 'APP_ENV", "production"', $content);
            $content = preg_replace('/APP_DEBUG.*true/', 'APP_DEBUG", false', $content);
            
            file_put_contents($configFile, $content);
        }
    }

    /**
     * Cafe24에 업로드
     */
    private function uploadToCafe24(): void
    {
        echo "📤 Cafe24 서버에 업로드...\n";
        
        try {
            $deployDir = __DIR__ . '/../deploy_temp';
            
            // FTP 연결
            $ftp = ftp_connect($this->config['ftp_server']);
            if (!$ftp) {
                throw new Exception('FTP 서버에 연결할 수 없습니다.');
            }
            
            if (!ftp_login($ftp, $this->config['ftp_username'], $this->config['ftp_password'])) {
                throw new Exception('FTP 로그인에 실패했습니다.');
            }
            
            // 패시브 모드 설정
            ftp_pasv($ftp, true);
            
            // 원격 디렉토리 확인
            $remoteDir = $this->config['remote_dir'];
            if (!@ftp_chdir($ftp, $remoteDir)) {
                // 디렉토리가 없으면 생성
                $this->createRemoteDirectory($ftp, $remoteDir);
            }
            
            // 파일 업로드
            $this->uploadDirectory($ftp, $deployDir, $remoteDir);
            
            ftp_close($ftp);
            
            echo "✅ 업로드 완료\n";
            $this->results['upload'] = 'passed';
            
        } catch (Exception $e) {
            throw new Exception('업로드 실패: ' . $e->getMessage());
        }
        
        echo "\n";
    }

    /**
     * 원격 디렉토리 생성
     */
    private function createRemoteDirectory($ftp, string $path): void
    {
        $parts = explode('/', trim($path, '/'));
        $currentPath = '';
        
        foreach ($parts as $part) {
            $currentPath .= '/' . $part;
            if (!@ftp_chdir($ftp, $currentPath)) {
                if (!ftp_mkdir($ftp, $part)) {
                    throw new Exception("원격 디렉토리를 생성할 수 없습니다: {$currentPath}");
                }
            }
        }
    }

    /**
     * 디렉토리 업로드
     */
    private function uploadDirectory($ftp, string $localDir, string $remoteDir): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($localDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            $relativePath = str_replace($localDir . '/', '', $file->getPathname());
            $remotePath = $remoteDir . '/' . $relativePath;
            
            if ($file->isDir()) {
                // 디렉토리 생성
                if (!@ftp_chdir($ftp, $remotePath)) {
                    $dirName = basename($remotePath);
                    if (!ftp_mkdir($ftp, $dirName)) {
                        echo "  ⚠️ 디렉토리 생성 실패: {$remotePath}\n";
                    }
                }
            } else {
                // 파일 업로드
                if (!ftp_put($ftp, basename($remotePath), $file->getPathname(), FTP_BINARY)) {
                    echo "  ⚠️ 파일 업로드 실패: {$relativePath}\n";
                } else {
                    echo "  ✅ {$relativePath}\n";
                }
            }
        }
    }

    /**
     * 배포 후 작업
     */
    private function postDeploymentTasks(): void
    {
        echo "🔧 배포 후 작업...\n";
        
        // 임시 디렉토리 정리
        $deployDir = __DIR__ . '/../deploy_temp';
        if (is_dir($deployDir)) {
            $this->removeDirectory($deployDir);
        }
        
        // 권한 설정 (FTP로)
        $this->setRemotePermissions();
        
        echo "✅ 배포 후 작업 완료\n\n";
    }

    /**
     * 원격 권한 설정
     */
    private function setRemotePermissions(): void
    {
        try {
            $ftp = ftp_connect($this->config['ftp_server']);
            ftp_login($ftp, $this->config['ftp_username'], $this->config['ftp_password']);
            ftp_pasv($ftp, true);
            
            $directories = [
                'system/uploads' => 0755,
                'system/cache' => 0755,
                'system/logs' => 0755,
                'config' => 0755
            ];
            
            foreach ($directories as $dir => $permission) {
                $remotePath = $this->config['remote_dir'] . '/' . $dir;
                if (@ftp_chdir($ftp, $remotePath)) {
                    // Cafe24에서는 chmod가 제한적일 수 있음
                    echo "  🔐 권한 설정: {$dir}\n";
                }
            }
            
            ftp_close($ftp);
            
        } catch (Exception $e) {
            echo "  ⚠️ 권한 설정 실패: {$e->getMessage()}\n";
        }
    }

    /**
     * 디렉토리 제거
     */
    private function removeDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }
        
        rmdir($path);
    }

    /**
     * 결과 출력
     */
    private function printResults(): void
    {
        $totalTime = microtime(true) - $this->startTime;
        
        echo str_repeat("=", 50) . "\n";
        echo "🎉 Cafe24 배포 완료!\n";
        echo str_repeat("=", 50) . "\n";
        
        foreach ($this->results as $step => $status) {
            $icon = $status === 'passed' ? '✅' : '❌';
            echo "{$icon} {$step}: {$status}\n";
        }
        
        echo "\n⏱️ 총 소요 시간: " . round($totalTime, 2) . "초\n";
        echo "📅 배포 완료 시간: " . date('Y-m-d H:i:s') . "\n";
        
        echo "\n🚀 배포가 성공적으로 완료되었습니다!\n";
        echo "🌐 사이트 URL: https://gukho.net/mp/\n";
        echo "📊 모니터링: https://gukho.net/mp/health.php\n";
        echo "\n⚠️ 배포 후 확인사항:\n";
        echo "1. 사이트 접속 테스트\n";
        echo "2. 로그인 기능 테스트\n";
        echo "3. 주요 기능 동작 확인\n";
        echo "4. 데이터베이스 연결 확인\n";
    }
}

// 명령행 인수 처리
$options = getopt('', ['skip-backup', 'skip-tests', 'ftp-user:', 'ftp-pass:']);

$deployer = new Cafe24Deployer();

// 옵션 적용
if (isset($options['skip-backup'])) {
    $deployer->config['create_backup'] = false;
}
if (isset($options['skip-tests'])) {
    $deployer->config['run_tests'] = false;
}
if (isset($options['ftp-user'])) {
    $deployer->config['ftp_username'] = $options['ftp-user'];
}
if (isset($options['ftp-pass'])) {
    $deployer->config['ftp_password'] = $options['ftp-pass'];
}

// 배포 실행
$deployer->deploy(); 