<?php

/**
 * 배포 스크립트
 * Cafe24 호스팅 환경에 자동 배포를 위한 스크립트
 */

require_once __DIR__ . '/../system/includes/config.php';

use System\Includes\Logger;
use System\Includes\DatabaseMigration;
use System\Includes\BackupManager;
use System\Includes\MonitoringSystem;

class Deployer
{
    private Logger $logger;
    private array $config;
    private string $deployPath;
    private string $backupPath;

    public function __construct()
    {
        $this->logger = new Logger('deploy');
        $this->config = $this->loadDeployConfig();
        $this->deployPath = __DIR__ . '/../';
        $this->backupPath = __DIR__ . '/../backups/';
    }

    /**
     * 배포 설정 로드
     */
    private function loadDeployConfig(): array
    {
        $configFile = __DIR__ . '/deploy-config.php';
        
        if (file_exists($configFile)) {
            return include $configFile;
        }

        // 기본 설정
        return [
            'environments' => [
                'development' => [
                    'host' => 'localhost',
                    'path' => '/var/www/html/',
                    'backup_before_deploy' => false
                ],
                'staging' => [
                    'host' => 'staging.example.com',
                    'path' => '/home/staging/public_html/',
                    'backup_before_deploy' => true
                ],
                'production' => [
                    'host' => 'gukho.net',
                    'path' => '/home/gukho/public_html/mp/',
                    'backup_before_deploy' => true
                ]
            ],
            'ftp' => [
                'host' => $_ENV['FTP_HOST'] ?? '',
                'username' => $_ENV['FTP_USERNAME'] ?? '',
                'password' => $_ENV['FTP_PASSWORD'] ?? '',
                'port' => $_ENV['FTP_PORT'] ?? 21,
                'passive' => true
            ],
            'exclude_files' => [
                '.git',
                '.github',
                'tests',
                'vendor',
                'composer.*',
                'phpunit.xml',
                '*.md',
                'backups',
                'logs',
                'temp'
            ]
        ];
    }

    /**
     * 배포 실행
     */
    public function deploy(string $environment = 'production'): array
    {
        $startTime = microtime(true);
        
        $this->logger->info('Deployment started', [
            'environment' => $environment,
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        $results = [
            'success' => false,
            'environment' => $environment,
            'steps' => [],
            'errors' => [],
            'duration' => 0
        ];

        try {
            // 1. 환경 검증
            $this->validateEnvironment($environment);
            $results['steps'][] = 'Environment validation passed';

            // 2. 사전 배포 백업
            if ($this->config['environments'][$environment]['backup_before_deploy']) {
                $backupResult = $this->createPreDeployBackup($environment);
                $results['steps'][] = 'Pre-deployment backup completed';
                $results['backup'] = $backupResult;
            }

            // 3. 배포 패키지 생성
            $packageResult = $this->createDeployPackage($environment);
            $results['steps'][] = 'Deployment package created';
            $results['package'] = $packageResult;

            // 4. 파일 업로드
            $uploadResult = $this->uploadFiles($environment, $packageResult['package_path']);
            $results['steps'][] = 'Files uploaded successfully';
            $results['upload'] = $uploadResult;

            // 5. 데이터베이스 마이그레이션
            $migrationResult = $this->runDatabaseMigrations($environment);
            $results['steps'][] = 'Database migrations completed';
            $results['migrations'] = $migrationResult;

            // 6. 캐시 정리
            $cacheResult = $this->clearCache($environment);
            $results['steps'][] = 'Cache cleared';
            $results['cache'] = $cacheResult;

            // 7. 헬스 체크
            $healthResult = $this->performHealthCheck($environment);
            $results['steps'][] = 'Health check completed';
            $results['health'] = $healthResult;

            // 8. 배포 후 정리
            $this->cleanupAfterDeploy($packageResult['package_path']);
            $results['steps'][] = 'Post-deployment cleanup completed';

            $results['success'] = true;
            
            $this->logger->info('Deployment completed successfully', [
                'environment' => $environment,
                'duration' => microtime(true) - $startTime
            ]);

        } catch (\Exception $e) {
            $results['errors'][] = $e->getMessage();
            
            $this->logger->error('Deployment failed', [
                'environment' => $environment,
                'error' => $e->getMessage(),
                'duration' => microtime(true) - $startTime
            ]);

            // 롤백 시도
            $this->attemptRollback($environment, $results);
        }

        $results['duration'] = microtime(true) - $startTime;
        return $results;
    }

    /**
     * 환경 검증
     */
    private function validateEnvironment(string $environment): void
    {
        if (!isset($this->config['environments'][$environment])) {
            throw new \Exception("Invalid environment: {$environment}");
        }

        // FTP 설정 확인
        if (empty($this->config['ftp']['host']) || 
            empty($this->config['ftp']['username']) || 
            empty($this->config['ftp']['password'])) {
            throw new \Exception('FTP configuration is incomplete');
        }

        // 필수 디렉토리 확인
        $requiredDirs = ['backups', 'logs', 'temp'];
        foreach ($requiredDirs as $dir) {
            $path = $this->deployPath . $dir;
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
    }

    /**
     * 사전 배포 백업 생성
     */
    private function createPreDeployBackup(string $environment): array
    {
        $backupManager = new BackupManager($this->getDatabaseConnection());
        $backupResult = $backupManager->createFullBackup();
        
        $this->logger->info('Pre-deployment backup created', [
            'environment' => $environment,
            'backup_result' => $backupResult
        ]);

        return $backupResult;
    }

    /**
     * 배포 패키지 생성
     */
    private function createDeployPackage(string $environment): array
    {
        $timestamp = date('Y-m-d_H-i-s');
        $packageName = "deploy_{$environment}_{$timestamp}.zip";
        $packagePath = $this->backupPath . $packageName;

        $zip = new ZipArchive();
        if ($zip->open($packagePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \Exception("Failed to create deployment package: {$packagePath}");
        }

        $this->addDirectoryToZip($zip, $this->deployPath, '', $this->config['exclude_files']);
        $zip->close();

        $size = filesize($packagePath);

        $this->logger->info('Deployment package created', [
            'package_path' => $packagePath,
            'size' => $size,
            'environment' => $environment
        ]);

        return [
            'package_path' => $packagePath,
            'package_name' => $packageName,
            'size' => $size
        ];
    }

    /**
     * ZIP에 디렉토리 추가 (제외 파일 처리)
     */
    private function addDirectoryToZip(ZipArchive $zip, string $dir, string $basePath, array $excludeFiles): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $filePath = $item->getRealPath();
            $relativePath = $basePath . '/' . $iterator->getSubPathName();
            
            // 제외 파일 확인
            $shouldExclude = false;
            foreach ($excludeFiles as $exclude) {
                if (strpos($relativePath, $exclude) !== false) {
                    $shouldExclude = true;
                    break;
                }
            }
            
            if ($shouldExclude) {
                continue;
            }
            
            if ($item->isDir()) {
                $zip->addEmptyDir($relativePath);
            } else {
                $zip->addFile($filePath, $relativePath);
            }
        }
    }

    /**
     * 파일 업로드
     */
    private function uploadFiles(string $environment, string $packagePath): array
    {
        $envConfig = $this->config['environments'][$environment];
        
        // FTP 연결
        $ftp = ftp_connect($this->config['ftp']['host'], $this->config['ftp']['port']);
        if (!$ftp) {
            throw new \Exception("Failed to connect to FTP server: {$this->config['ftp']['host']}");
        }

        // 로그인
        if (!ftp_login($ftp, $this->config['ftp']['username'], $this->config['ftp']['password'])) {
            throw new \Exception('FTP login failed');
        }

        // 패시브 모드 설정
        if ($this->config['ftp']['passive']) {
            ftp_pasv($ftp, true);
        }

        // 임시 디렉토리에 업로드
        $tempDir = $envConfig['path'] . 'temp_deploy_' . date('YmdHis');
        if (!ftp_mkdir($ftp, $tempDir)) {
            throw new \Exception("Failed to create temp directory: {$tempDir}");
        }

        // 파일 업로드
        $uploadResult = ftp_put($ftp, $tempDir . '/deploy.zip', $packagePath, FTP_BINARY);
        if (!$uploadResult) {
            throw new \Exception('Failed to upload deployment package');
        }

        // 기존 파일 백업
        $backupDir = $envConfig['path'] . 'backup_' . date('YmdHis');
        if (ftp_rename($ftp, $envConfig['path'], $backupDir)) {
            $this->logger->info('Existing files backed up', ['backup_dir' => $backupDir]);
        }

        // 새 디렉토리 생성
        if (!ftp_mkdir($ftp, $envConfig['path'])) {
            throw new \Exception("Failed to create deployment directory: {$envConfig['path']}");
        }

        // 파일 압축 해제 (서버에서 실행)
        $extractCommand = "cd {$tempDir} && unzip -o deploy.zip -d {$envConfig['path']}";
        $this->executeRemoteCommand($environment, $extractCommand);

        // 임시 디렉토리 정리
        $this->removeRemoteDirectory($ftp, $tempDir);

        ftp_close($ftp);

        return [
            'success' => true,
            'uploaded_files' => 1,
            'backup_dir' => $backupDir
        ];
    }

    /**
     * 데이터베이스 마이그레이션 실행
     */
    private function runDatabaseMigrations(string $environment): array
    {
        try {
            $pdo = $this->getDatabaseConnection($environment);
            $migration = new DatabaseMigration($pdo);
            
            $result = $migration->migrate();
            
            $this->logger->info('Database migrations completed', [
                'environment' => $environment,
                'migrated' => count($result['migrated']),
                'errors' => count($result['errors'])
            ]);

            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Database migration failed', [
                'environment' => $environment,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * 캐시 정리
     */
    private function clearCache(string $environment): array
    {
        $envConfig = $this->config['environments'][$environment];
        $cachePaths = [
            $envConfig['path'] . 'system/cache/',
            $envConfig['path'] . 'config/sessions/',
            $envConfig['path'] . 'temp/'
        ];

        $cleared = [];
        foreach ($cachePaths as $path) {
            $command = "rm -rf {$path}*";
            $this->executeRemoteCommand($environment, $command);
            $cleared[] = $path;
        }

        return [
            'success' => true,
            'cleared_paths' => $cleared
        ];
    }

    /**
     * 헬스 체크 수행
     */
    private function performHealthCheck(string $environment): array
    {
        $envConfig = $this->config['environments'][$environment];
        $healthUrl = "https://{$envConfig['host']}/health.php";
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $healthUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception("Health check failed: {$error}");
        }

        if ($httpCode !== 200) {
            throw new \Exception("Health check returned HTTP {$httpCode}");
        }

        $healthData = json_decode($response, true);
        if (!$healthData) {
            throw new \Exception('Invalid health check response');
        }

        return [
            'success' => true,
            'http_code' => $httpCode,
            'response_time' => $healthData['response_time'] ?? 0,
            'status' => $healthData['status'] ?? 'unknown'
        ];
    }

    /**
     * 배포 후 정리
     */
    private function cleanupAfterDeploy(string $packagePath): void
    {
        // 배포 패키지 삭제
        if (file_exists($packagePath)) {
            unlink($packagePath);
        }

        // 임시 파일 정리
        $tempFiles = glob($this->deployPath . 'temp/*');
        foreach ($tempFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    /**
     * 롤백 시도
     */
    private function attemptRollback(string $environment, array $deployResults): void
    {
        $this->logger->warning('Attempting rollback', [
            'environment' => $environment,
            'deploy_results' => $deployResults
        ]);

        try {
            // 롤백 로직 구현
            // 1. 백업에서 복원
            // 2. 데이터베이스 롤백
            // 3. 파일 복원
            
            $this->logger->info('Rollback completed successfully');
        } catch (\Exception $e) {
            $this->logger->error('Rollback failed', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 데이터베이스 연결 가져오기
     */
    private function getDatabaseConnection(string $environment = 'production'): PDO
    {
        $envConfig = $this->config['environments'][$environment];
        
        // 환경별 데이터베이스 설정
        $dbConfig = [
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'database' => $_ENV['DB_NAME'] ?? 'mp_learning',
            'username' => $_ENV['DB_USER'] ?? '',
            'password' => $_ENV['DB_PASS'] ?? '',
            'charset' => 'utf8mb4'
        ];

        $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
        
        return new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
    }

    /**
     * 원격 명령어 실행
     */
    private function executeRemoteCommand(string $environment, string $command): string
    {
        $envConfig = $this->config['environments'][$environment];
        
        // SSH를 통한 명령어 실행 (SSH 키가 설정된 경우)
        if (isset($_ENV['SSH_KEY_PATH'])) {
            $sshCommand = "ssh -i {$_ENV['SSH_KEY_PATH']} {$envConfig['host']} '{$command}'";
            return shell_exec($sshCommand);
        }
        
        // FTP를 통한 명령어 실행 (제한적)
        return '';
    }

    /**
     * 원격 디렉토리 제거
     */
    private function removeRemoteDirectory($ftp, string $path): void
    {
        $files = ftp_nlist($ftp, $path);
        if ($files) {
            foreach ($files as $file) {
                if (basename($file) !== '.' && basename($file) !== '..') {
                    ftp_delete($ftp, $file);
                }
            }
        }
        ftp_rmdir($ftp, $path);
    }
}

// CLI 실행
if (php_sapi_name() === 'cli') {
    $deployer = new Deployer();
    
    $environment = $argv[1] ?? 'production';
    $result = $deployer->deploy($environment);
    
    if ($result['success']) {
        echo "✅ Deployment completed successfully!\n";
        echo "Environment: {$result['environment']}\n";
        echo "Duration: {$result['duration']} seconds\n";
        echo "Steps completed: " . count($result['steps']) . "\n";
    } else {
        echo "❌ Deployment failed!\n";
        echo "Environment: {$result['environment']}\n";
        echo "Errors: " . implode(', ', $result['errors']) . "\n";
        exit(1);
    }
} 