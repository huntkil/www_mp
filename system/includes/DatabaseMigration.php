<?php

namespace System\Includes;

use PDO;
use PDOException;

/**
 * 데이터베이스 마이그레이션 시스템
 */
class DatabaseMigration
{
    private PDO $pdo;
    private string $migrationsTable = 'migrations';
    private string $migrationsPath;

    public function __construct(PDO $pdo, string $migrationsPath = null)
    {
        $this->pdo = $pdo;
        $this->migrationsPath = $migrationsPath ?? __DIR__ . '/../../database/migrations';
        
        // 마이그레이션 테이블 생성
        $this->createMigrationsTable();
    }

    /**
     * 마이그레이션 테이블 생성
     */
    private function createMigrationsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->migrationsTable} (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            migration VARCHAR(255) NOT NULL,
            batch INTEGER NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $this->pdo->exec($sql);
    }

    /**
     * 모든 마이그레이션 실행
     */
    public function migrate(): array
    {
        $results = [
            'migrated' => [],
            'errors' => []
        ];

        try {
            // 마이그레이션 파일 목록 가져오기
            $files = $this->getMigrationFiles();
            
            // 이미 실행된 마이그레이션 확인
            $executed = $this->getExecutedMigrations();
            
            // 실행할 마이그레이션 필터링
            $pending = array_diff($files, $executed);
            
            if (empty($pending)) {
                return $results;
            }

            // 배치 번호 생성
            $batch = $this->getNextBatchNumber();
            
            foreach ($pending as $migration) {
                try {
                    $this->runMigration($migration, $batch);
                    $results['migrated'][] = $migration;
                } catch (Exception $e) {
                    $results['errors'][] = [
                        'migration' => $migration,
                        'error' => $e->getMessage()
                    ];
                }
            }
        } catch (Exception $e) {
            $results['errors'][] = [
                'migration' => 'general',
                'error' => $e->getMessage()
            ];
        }

        return $results;
    }

    /**
     * 마이그레이션 롤백
     */
    public function rollback(int $steps = 1): array
    {
        $results = [
            'rolled_back' => [],
            'errors' => []
        ];

        try {
            // 최근 배치의 마이그레이션 가져오기
            $recentBatches = $this->getRecentBatches($steps);
            
            foreach ($recentBatches as $batch) {
                $migrations = $this->getMigrationsByBatch($batch);
                
                // 역순으로 롤백
                $migrations = array_reverse($migrations);
                
                foreach ($migrations as $migration) {
                    try {
                        $this->rollbackMigration($migration);
                        $results['rolled_back'][] = $migration;
                    } catch (Exception $e) {
                        $results['errors'][] = [
                            'migration' => $migration,
                            'error' => $e->getMessage()
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            $results['errors'][] = [
                'migration' => 'general',
                'error' => $e->getMessage()
            ];
        }

        return $results;
    }

    /**
     * 마이그레이션 상태 확인
     */
    public function status(): array
    {
        $files = $this->getMigrationFiles();
        $executed = $this->getExecutedMigrations();
        
        $status = [];
        
        foreach ($files as $file) {
            $status[$file] = [
                'file' => $file,
                'executed' => in_array($file, $executed),
                'batch' => $this->getMigrationBatch($file)
            ];
        }
        
        return $status;
    }

    /**
     * 마이그레이션 파일 목록 가져오기
     */
    private function getMigrationFiles(): array
    {
        if (!is_dir($this->migrationsPath)) {
            return [];
        }

        $files = glob($this->migrationsPath . '/*.sql');
        $migrations = [];
        
        foreach ($files as $file) {
            $migrations[] = basename($file, '.sql');
        }
        
        sort($migrations);
        return $migrations;
    }

    /**
     * 실행된 마이그레이션 목록 가져오기
     */
    private function getExecutedMigrations(): array
    {
        $stmt = $this->pdo->query("SELECT migration FROM {$this->migrationsTable} ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * 다음 배치 번호 가져오기
     */
    private function getNextBatchNumber(): int
    {
        $stmt = $this->pdo->query("SELECT MAX(batch) FROM {$this->migrationsTable}");
        $maxBatch = $stmt->fetchColumn();
        return ($maxBatch ?? 0) + 1;
    }

    /**
     * 최근 배치 번호들 가져오기
     */
    private function getRecentBatches(int $steps): array
    {
        $stmt = $this->pdo->prepare("
            SELECT DISTINCT batch 
            FROM {$this->migrationsTable} 
            ORDER BY batch DESC 
            LIMIT ?
        ");
        $stmt->execute([$steps]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * 배치별 마이그레이션 가져오기
     */
    private function getMigrationsByBatch(int $batch): array
    {
        $stmt = $this->pdo->prepare("
            SELECT migration 
            FROM {$this->migrationsTable} 
            WHERE batch = ? 
            ORDER BY id
        ");
        $stmt->execute([$batch]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * 마이그레이션 배치 번호 가져오기
     */
    private function getMigrationBatch(string $migration): ?int
    {
        $stmt = $this->pdo->prepare("
            SELECT batch 
            FROM {$this->migrationsTable} 
            WHERE migration = ?
        ");
        $stmt->execute([$migration]);
        return $stmt->fetchColumn() ?: null;
    }

    /**
     * 마이그레이션 실행
     */
    private function runMigration(string $migration, int $batch): void
    {
        $file = $this->migrationsPath . '/' . $migration . '.sql';
        
        if (!file_exists($file)) {
            throw new Exception("Migration file not found: {$file}");
        }

        $sql = file_get_contents($file);
        
        // 트랜잭션 시작
        $this->pdo->beginTransaction();
        
        try {
            // SQL 실행
            $this->pdo->exec($sql);
            
            // 마이그레이션 기록
            $stmt = $this->pdo->prepare("
                INSERT INTO {$this->migrationsTable} (migration, batch) 
                VALUES (?, ?)
            ");
            $stmt->execute([$migration, $batch]);
            
            // 트랜잭션 커밋
            $this->pdo->commit();
        } catch (Exception $e) {
            // 트랜잭션 롤백
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * 마이그레이션 롤백
     */
    private function rollbackMigration(string $migration): void
    {
        $file = $this->migrationsPath . '/' . $migration . '_rollback.sql';
        
        if (!file_exists($file)) {
            throw new Exception("Rollback file not found: {$file}");
        }

        $sql = file_get_contents($file);
        
        // 트랜잭션 시작
        $this->pdo->beginTransaction();
        
        try {
            // SQL 실행
            $this->pdo->exec($sql);
            
            // 마이그레이션 기록 삭제
            $stmt = $this->pdo->prepare("
                DELETE FROM {$this->migrationsTable} 
                WHERE migration = ?
            ");
            $stmt->execute([$migration]);
            
            // 트랜잭션 커밋
            $this->pdo->commit();
        } catch (Exception $e) {
            // 트랜잭션 롤백
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * 마이그레이션 파일 생성
     */
    public function createMigration(string $name): string
    {
        $timestamp = date('Y_m_d_His');
        $filename = $timestamp . '_' . $name;
        
        $migrationFile = $this->migrationsPath . '/' . $filename . '.sql';
        $rollbackFile = $this->migrationsPath . '/' . $filename . '_rollback.sql';
        
        // 디렉토리 생성
        if (!is_dir($this->migrationsPath)) {
            mkdir($this->migrationsPath, 0755, true);
        }
        
        // 마이그레이션 파일 생성
        $migrationContent = "-- Migration: {$name}\n";
        $migrationContent .= "-- Created: " . date('Y-m-d H:i:s') . "\n\n";
        $migrationContent .= "-- Add your SQL here\n";
        $migrationContent .= "-- Example:\n";
        $migrationContent .= "-- CREATE TABLE users (\n";
        $migrationContent .= "--     id INTEGER PRIMARY KEY AUTOINCREMENT,\n";
        $migrationContent .= "--     username VARCHAR(255) NOT NULL,\n";
        $migrationContent .= "--     email VARCHAR(255) NOT NULL,\n";
        $migrationContent .= "--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n";
        $migrationContent .= "-- );\n";
        
        file_put_contents($migrationFile, $migrationContent);
        
        // 롤백 파일 생성
        $rollbackContent = "-- Rollback: {$name}\n";
        $rollbackContent .= "-- Created: " . date('Y-m-d H:i:s') . "\n\n";
        $rollbackContent .= "-- Add your rollback SQL here\n";
        $rollbackContent .= "-- Example:\n";
        $rollbackContent .= "-- DROP TABLE IF EXISTS users;\n";
        
        file_put_contents($rollbackFile, $rollbackContent);
        
        return $filename;
    }

    /**
     * 마이그레이션 새로고침 (모든 마이그레이션 롤백 후 재실행)
     */
    public function refresh(): array
    {
        $results = [
            'rolled_back' => [],
            'migrated' => [],
            'errors' => []
        ];

        try {
            // 모든 마이그레이션 롤백
            $rollbackResults = $this->rollbackAll();
            $results['rolled_back'] = $rollbackResults['rolled_back'];
            $results['errors'] = array_merge($results['errors'], $rollbackResults['errors']);
            
            // 모든 마이그레이션 재실행
            $migrateResults = $this->migrate();
            $results['migrated'] = $migrateResults['migrated'];
            $results['errors'] = array_merge($results['errors'], $migrateResults['errors']);
        } catch (Exception $e) {
            $results['errors'][] = [
                'migration' => 'refresh',
                'error' => $e->getMessage()
            ];
        }

        return $results;
    }

    /**
     * 모든 마이그레이션 롤백
     */
    private function rollbackAll(): array
    {
        $results = [
            'rolled_back' => [],
            'errors' => []
        ];

        try {
            $executed = $this->getExecutedMigrations();
            
            if (empty($executed)) {
                return $results;
            }

            // 역순으로 롤백
            $executed = array_reverse($executed);
            
            foreach ($executed as $migration) {
                try {
                    $this->rollbackMigration($migration);
                    $results['rolled_back'][] = $migration;
                } catch (Exception $e) {
                    $results['errors'][] = [
                        'migration' => $migration,
                        'error' => $e->getMessage()
                    ];
                }
            }
        } catch (Exception $e) {
            $results['errors'][] = [
                'migration' => 'rollback_all',
                'error' => $e->getMessage()
            ];
        }

        return $results;
    }
} 