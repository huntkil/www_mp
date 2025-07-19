<?php

namespace System\Includes;

use PDO;
use ZipArchive;

/**
 * 백업 관리 시스템
 */
class BackupManager
{
    private PDO $pdo;
    private string $backupPath;
    private string $maxBackups;
    private Logger $logger;

    public function __construct(PDO $pdo, string $backupPath = null, int $maxBackups = 10)
    {
        $this->pdo = $pdo;
        $this->backupPath = $backupPath ?? __DIR__ . '/../../backups';
        $this->maxBackups = $maxBackups;
        $this->logger = new Logger('backup');
        
        // 백업 디렉토리 생성
        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
    }

    /**
     * 전체 백업 생성 (데이터베이스 + 파일)
     */
    public function createFullBackup(): array
    {
        $timestamp = date('Y-m-d_H-i-s');
        $backupName = "full_backup_{$timestamp}";
        $backupDir = $this->backupPath . '/' . $backupName;
        
        try {
            // 백업 디렉토리 생성
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            $results = [
                'backup_name' => $backupName,
                'database' => null,
                'files' => null,
                'archive' => null,
                'errors' => []
            ];

            // 데이터베이스 백업
            $dbResult = $this->backupDatabase($backupDir);
            $results['database'] = $dbResult;

            // 파일 백업
            $filesResult = $this->backupFiles($backupDir);
            $results['files'] = $filesResult;

            // 압축 파일 생성
            $archiveResult = $this->createArchive($backupName, $backupDir);
            $results['archive'] = $archiveResult;

            // 백업 디렉토리 정리
            $this->cleanupBackupDir($backupDir);

            // 오래된 백업 정리
            $this->cleanupOldBackups();

            $this->logger->info('Full backup created successfully', [
                'backup_name' => $backupName,
                'size' => $archiveResult['size'] ?? 0
            ]);

            return $results;
        } catch (\Exception $e) {
            $this->logger->error('Full backup failed', [
                'error' => $e->getMessage(),
                'backup_name' => $backupName
            ]);

            return [
                'backup_name' => $backupName,
                'database' => null,
                'files' => null,
                'archive' => null,
                'errors' => [$e->getMessage()]
            ];
        }
    }

    /**
     * 데이터베이스 백업
     */
    public function backupDatabase(string $backupDir): array
    {
        $timestamp = date('Y-m-d_H-i-s');
        $dbFile = $backupDir . '/database_' . $timestamp . '.sql';
        
        try {
            // SQLite 데이터베이스 파일 복사
            $dbPath = $this->pdo->query("PRAGMA database_list")->fetch(PDO::FETCH_ASSOC);
            
            if (isset($dbPath['file'])) {
                copy($dbPath['file'], $dbFile);
                
                return [
                    'success' => true,
                    'file' => $dbFile,
                    'size' => filesize($dbFile),
                    'tables' => $this->getTableCount()
                ];
            } else {
                // MySQL 데이터베이스 덤프
                return $this->dumpMySQLDatabase($dbFile);
            }
        } catch (\Exception $e) {
            $this->logger->error('Database backup failed', [
                'error' => $e->getMessage(),
                'file' => $dbFile
            ]);

            return [
                'success' => false,
                'file' => $dbFile,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * MySQL 데이터베이스 덤프
     */
    private function dumpMySQLDatabase(string $dbFile): array
    {
        try {
            // 데이터베이스 정보 가져오기
            $dsn = $this->pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS);
            
            // mysqldump 명령어 실행
            $command = "mysqldump --single-transaction --routines --triggers " .
                      "--host={$this->getDbHost()} " .
                      "--user={$this->getDbUser()} " .
                      "--password={$this->getDbPassword()} " .
                      "{$this->getDbName()} > {$dbFile}";
            
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0 && file_exists($dbFile)) {
                return [
                    'success' => true,
                    'file' => $dbFile,
                    'size' => filesize($dbFile),
                    'tables' => $this->getTableCount()
                ];
            } else {
                throw new \Exception("mysqldump failed with return code: {$returnCode}");
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'file' => $dbFile,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * 파일 백업
     */
    public function backupFiles(string $backupDir): array
    {
        $timestamp = date('Y-m-d_H-i-s');
        $filesDir = $backupDir . '/files_' . $timestamp;
        
        try {
            if (!is_dir($filesDir)) {
                mkdir($filesDir, 0755, true);
            }

            $backupPaths = [
                'uploads' => __DIR__ . '/../../system/uploads',
                'logs' => __DIR__ . '/../../config/logs',
                'sessions' => __DIR__ . '/../../config/sessions'
            ];

            $results = [
                'success' => true,
                'files_dir' => $filesDir,
                'backed_up' => [],
                'errors' => []
            ];

            foreach ($backupPaths as $name => $path) {
                if (is_dir($path)) {
                    $destPath = $filesDir . '/' . $name;
                    $this->copyDirectory($path, $destPath);
                    $results['backed_up'][$name] = [
                        'source' => $path,
                        'destination' => $destPath,
                        'size' => $this->getDirectorySize($destPath)
                    ];
                }
            }

            return $results;
        } catch (\Exception $e) {
            $this->logger->error('Files backup failed', [
                'error' => $e->getMessage(),
                'files_dir' => $filesDir
            ]);

            return [
                'success' => false,
                'files_dir' => $filesDir,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * 압축 파일 생성
     */
    private function createArchive(string $backupName, string $backupDir): array
    {
        $archiveFile = $this->backupPath . '/' . $backupName . '.zip';
        
        try {
            $zip = new ZipArchive();
            
            if ($zip->open($archiveFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \Exception("Failed to create ZIP archive: {$archiveFile}");
            }

            $this->addDirectoryToZip($zip, $backupDir, basename($backupDir));
            $zip->close();

            return [
                'success' => true,
                'file' => $archiveFile,
                'size' => filesize($archiveFile),
                'compression_ratio' => $this->calculateCompressionRatio($backupDir, $archiveFile)
            ];
        } catch (\Exception $e) {
            $this->logger->error('Archive creation failed', [
                'error' => $e->getMessage(),
                'archive_file' => $archiveFile
            ]);

            return [
                'success' => false,
                'file' => $archiveFile,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * 백업 복원
     */
    public function restoreBackup(string $backupFile): array
    {
        try {
            $tempDir = $this->backupPath . '/temp_restore_' . uniqid();
            
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            // 압축 파일 해제
            $zip = new ZipArchive();
            if ($zip->open($backupFile) !== true) {
                throw new \Exception("Failed to open backup file: {$backupFile}");
            }

            $zip->extractTo($tempDir);
            $zip->close();

            $results = [
                'success' => true,
                'restored' => [],
                'errors' => []
            ];

            // 데이터베이스 복원
            $dbFiles = glob($tempDir . '/*/database_*.sql');
            if (!empty($dbFiles)) {
                $dbResult = $this->restoreDatabase($dbFiles[0]);
                $results['restored']['database'] = $dbResult;
            }

            // 파일 복원
            $filesDirs = glob($tempDir . '/*/files_*', GLOB_ONLYDIR);
            if (!empty($filesDirs)) {
                $filesResult = $this->restoreFiles($filesDirs[0]);
                $results['restored']['files'] = $filesResult;
            }

            // 임시 디렉토리 정리
            $this->cleanupBackupDir($tempDir);

            $this->logger->info('Backup restored successfully', [
                'backup_file' => $backupFile
            ]);

            return $results;
        } catch (\Exception $e) {
            $this->logger->error('Backup restore failed', [
                'error' => $e->getMessage(),
                'backup_file' => $backupFile
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * 데이터베이스 복원
     */
    private function restoreDatabase(string $dbFile): array
    {
        try {
            // 현재 데이터베이스 백업
            $currentBackup = $this->backupPath . '/pre_restore_' . date('Y-m-d_H-i-s') . '.sql';
            $this->backupDatabase($this->backupPath);

            // SQLite 데이터베이스 복원
            $dbPath = $this->pdo->query("PRAGMA database_list")->fetch(PDO::FETCH_ASSOC);
            
            if (isset($dbPath['file'])) {
                copy($dbFile, $dbPath['file']);
                
                return [
                    'success' => true,
                    'file' => $dbFile,
                    'current_backup' => $currentBackup
                ];
            } else {
                // MySQL 데이터베이스 복원
                return $this->restoreMySQLDatabase($dbFile);
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'file' => $dbFile,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * MySQL 데이터베이스 복원
     */
    private function restoreMySQLDatabase(string $dbFile): array
    {
        try {
            $command = "mysql --host={$this->getDbHost()} " .
                      "--user={$this->getDbUser()} " .
                      "--password={$this->getDbPassword()} " .
                      "{$this->getDbName()} < {$dbFile}";
            
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0) {
                return [
                    'success' => true,
                    'file' => $dbFile
                ];
            } else {
                throw new \Exception("MySQL restore failed with return code: {$returnCode}");
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'file' => $dbFile,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * 파일 복원
     */
    private function restoreFiles(string $filesDir): array
    {
        try {
            $results = [
                'success' => true,
                'restored' => [],
                'errors' => []
            ];

            $restorePaths = [
                'uploads' => __DIR__ . '/../../system/uploads',
                'logs' => __DIR__ . '/../../config/logs',
                'sessions' => __DIR__ . '/../../config/sessions'
            ];

            foreach ($restorePaths as $name => $path) {
                $sourcePath = $filesDir . '/' . $name;
                if (is_dir($sourcePath)) {
                    // 기존 디렉토리 백업
                    $backupPath = $path . '_backup_' . date('Y-m-d_H-i-s');
                    if (is_dir($path)) {
                        rename($path, $backupPath);
                    }
                    
                    // 파일 복원
                    $this->copyDirectory($sourcePath, $path);
                    $results['restored'][$name] = [
                        'source' => $sourcePath,
                        'destination' => $path,
                        'backup' => $backupPath
                    ];
                }
            }

            return $results;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * 백업 목록 조회
     */
    public function getBackupList(): array
    {
        $backups = [];
        $files = glob($this->backupPath . '/*.zip');
        
        foreach ($files as $file) {
            $backups[] = [
                'name' => basename($file, '.zip'),
                'file' => $file,
                'size' => filesize($file),
                'created_at' => date('Y-m-d H:i:s', filemtime($file)),
                'age_days' => floor((time() - filemtime($file)) / 86400)
            ];
        }
        
        // 생성일 기준 내림차순 정렬
        usort($backups, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return $backups;
    }

    /**
     * 백업 삭제
     */
    public function deleteBackup(string $backupName): bool
    {
        $backupFile = $this->backupPath . '/' . $backupName . '.zip';
        
        if (file_exists($backupFile)) {
            $deleted = unlink($backupFile);
            
            if ($deleted) {
                $this->logger->info('Backup deleted', [
                    'backup_name' => $backupName
                ]);
            }
            
            return $deleted;
        }
        
        return false;
    }

    /**
     * 오래된 백업 정리
     */
    private function cleanupOldBackups(): void
    {
        $backups = $this->getBackupList();
        
        if (count($backups) > $this->maxBackups) {
            $toDelete = array_slice($backups, $this->maxBackups);
            
            foreach ($toDelete as $backup) {
                $this->deleteBackup($backup['name']);
            }
        }
    }

    /**
     * 백업 디렉토리 정리
     */
    private function cleanupBackupDir(string $dir): void
    {
        if (is_dir($dir)) {
            $this->removeDirectory($dir);
        }
    }

    /**
     * 디렉토리 복사
     */
    private function copyDirectory(string $source, string $destination): void
    {
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                mkdir($destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            } else {
                copy($item, $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            }
        }
    }

    /**
     * 디렉토리 제거
     */
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getRealPath());
            } else {
                unlink($item->getRealPath());
            }
        }

        rmdir($dir);
    }

    /**
     * ZIP에 디렉토리 추가
     */
    private function addDirectoryToZip(ZipArchive $zip, string $dir, string $basePath): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $filePath = $item->getRealPath();
            $relativePath = $basePath . '/' . $iterator->getSubPathName();
            
            if ($item->isDir()) {
                $zip->addEmptyDir($relativePath);
            } else {
                $zip->addFile($filePath, $relativePath);
            }
        }
    }

    /**
     * 디렉토리 크기 계산
     */
    private function getDirectorySize(string $dir): int
    {
        $size = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $item) {
            if ($item->isFile()) {
                $size += $item->getSize();
            }
        }

        return $size;
    }

    /**
     * 압축률 계산
     */
    private function calculateCompressionRatio(string $sourceDir, string $archiveFile): float
    {
        $sourceSize = $this->getDirectorySize($sourceDir);
        $archiveSize = filesize($archiveFile);
        
        if ($sourceSize > 0) {
            return round((1 - ($archiveSize / $sourceSize)) * 100, 2);
        }
        
        return 0;
    }

    /**
     * 테이블 개수 가져오기
     */
    private function getTableCount(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM sqlite_master WHERE type='table'");
        return (int) $stmt->fetchColumn();
    }

    /**
     * 데이터베이스 호스트 가져오기
     */
    private function getDbHost(): string
    {
        return $_ENV['DB_HOST'] ?? 'localhost';
    }

    /**
     * 데이터베이스 사용자 가져오기
     */
    private function getDbUser(): string
    {
        return $_ENV['DB_USER'] ?? '';
    }

    /**
     * 데이터베이스 비밀번호 가져오기
     */
    private function getDbPassword(): string
    {
        return $_ENV['DB_PASSWORD'] ?? '';
    }

    /**
     * 데이터베이스 이름 가져오기
     */
    private function getDbName(): string
    {
        return $_ENV['DB_NAME'] ?? '';
    }
} 