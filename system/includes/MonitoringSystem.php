<?php

namespace System\Includes;

use PDO;

/**
 * 모니터링 및 알림 시스템
 */
class MonitoringSystem
{
    private PDO $pdo;
    private Logger $logger;
    private array $config;
    private string $metricsTable = 'system_metrics';
    private string $alertsTable = 'system_alerts';

    public function __construct(PDO $pdo, array $config = [])
    {
        $this->pdo = $pdo;
        $this->logger = new Logger('monitoring');
        $this->config = array_merge([
            'metrics_retention_days' => 30,
            'alert_thresholds' => [
                'cpu_usage' => 80,
                'memory_usage' => 85,
                'disk_usage' => 90,
                'error_rate' => 5,
                'response_time' => 2000
            ],
            'notification_channels' => [
                'email' => true,
                'slack' => false,
                'webhook' => false
            ]
        ], $config);

        $this->initializeTables();
    }

    /**
     * 모니터링 테이블 초기화
     */
    private function initializeTables(): void
    {
        // 메트릭 테이블
        $sql = "CREATE TABLE IF NOT EXISTS {$this->metricsTable} (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            metric_name VARCHAR(100) NOT NULL,
            metric_value REAL NOT NULL,
            metric_unit VARCHAR(20),
            tags TEXT,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $this->pdo->exec($sql);

        // 알림 테이블
        $sql = "CREATE TABLE IF NOT EXISTS {$this->alertsTable} (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            alert_type VARCHAR(100) NOT NULL,
            alert_message TEXT NOT NULL,
            severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
            status ENUM('active', 'resolved', 'acknowledged') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            resolved_at TIMESTAMP NULL,
            acknowledged_by VARCHAR(100) NULL,
            acknowledged_at TIMESTAMP NULL
        )";
        $this->pdo->exec($sql);

        // 인덱스 생성
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_metrics_name_time ON {$this->metricsTable} (metric_name, timestamp)");
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_alerts_status_time ON {$this->alertsTable} (status, created_at)");
    }

    /**
     * 시스템 메트릭 수집
     */
    public function collectMetrics(): array
    {
        $metrics = [];

        try {
            // CPU 사용률
            $metrics['cpu_usage'] = $this->getCpuUsage();
            
            // 메모리 사용률
            $metrics['memory_usage'] = $this->getMemoryUsage();
            
            // 디스크 사용률
            $metrics['disk_usage'] = $this->getDiskUsage();
            
            // 데이터베이스 연결 상태
            $metrics['database_status'] = $this->getDatabaseStatus();
            
            // 애플리케이션 응답 시간
            $metrics['response_time'] = $this->getResponseTime();
            
            // 에러율
            $metrics['error_rate'] = $this->getErrorRate();
            
            // 활성 세션 수
            $metrics['active_sessions'] = $this->getActiveSessions();
            
            // 로그 파일 크기
            $metrics['log_file_size'] = $this->getLogFileSize();

            // 메트릭 저장
            foreach ($metrics as $name => $value) {
                $this->saveMetric($name, $value);
            }

            // 임계값 확인 및 알림 생성
            $this->checkThresholds($metrics);

            $this->logger->info('Metrics collected successfully', [
                'metrics_count' => count($metrics)
            ]);

            return $metrics;
        } catch (\Exception $e) {
            $this->logger->error('Metrics collection failed', [
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    /**
     * CPU 사용률 가져오기
     */
    private function getCpuUsage(): float
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return $load[0] * 100; // 1분 평균 로드
        }

        // Windows 환경
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $cmd = 'wmic cpu get loadpercentage /value';
            $output = shell_exec($cmd);
            if (preg_match('/LoadPercentage=(\d+)/', $output, $matches)) {
                return (float) $matches[1];
            }
        }

        return 0.0;
    }

    /**
     * 메모리 사용률 가져오기
     */
    private function getMemoryUsage(): float
    {
        $memoryLimit = ini_get('memory_limit');
        $memoryUsage = memory_get_usage(true);
        
        if ($memoryLimit !== '-1') {
            $memoryLimitBytes = $this->convertToBytes($memoryLimit);
            return ($memoryUsage / $memoryLimitBytes) * 100;
        }

        return 0.0;
    }

    /**
     * 디스크 사용률 가져오기
     */
    private function getDiskUsage(): float
    {
        $path = __DIR__ . '/../../';
        $totalSpace = disk_total_space($path);
        $freeSpace = disk_free_space($path);
        
        if ($totalSpace > 0) {
            return (($totalSpace - $freeSpace) / $totalSpace) * 100;
        }

        return 0.0;
    }

    /**
     * 데이터베이스 상태 확인
     */
    private function getDatabaseStatus(): int
    {
        try {
            $this->pdo->query('SELECT 1');
            return 1; // 정상
        } catch (\Exception $e) {
            return 0; // 오류
        }
    }

    /**
     * 응답 시간 측정
     */
    private function getResponseTime(): float
    {
        $startTime = microtime(true);
        
        // 간단한 데이터베이스 쿼리로 응답 시간 측정
        try {
            $this->pdo->query('SELECT 1');
            $endTime = microtime(true);
            return ($endTime - $startTime) * 1000; // 밀리초 단위
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    /**
     * 에러율 계산
     */
    private function getErrorRate(): float
    {
        $now = date('Y-m-d H:i:s');
        $fiveMinutesAgo = date('Y-m-d H:i:s', strtotime('-5 minutes'));
        
        try {
            // 최근 5분간의 에러 로그 수
            $errorLogPath = __DIR__ . '/../../config/logs/error.log';
            if (file_exists($errorLogPath)) {
                $errorCount = 0;
                $totalRequests = 0;
                
                $handle = fopen($errorLogPath, 'r');
                while (($line = fgets($handle)) !== false) {
                    if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $line, $matches)) {
                        $logTime = $matches[1];
                        if ($logTime >= $fiveMinutesAgo && $logTime <= $now) {
                            $errorCount++;
                        }
                    }
                }
                fclose($handle);
                
                // 전체 요청 수 (세션 파일 수로 추정)
                $sessionPath = __DIR__ . '/../../config/sessions/';
                if (is_dir($sessionPath)) {
                    $totalRequests = count(glob($sessionPath . 'sess_*'));
                }
                
                if ($totalRequests > 0) {
                    return ($errorCount / $totalRequests) * 100;
                }
            }
        } catch (\Exception $e) {
            // 에러 무시
        }
        
        return 0.0;
    }

    /**
     * 활성 세션 수 가져오기
     */
    private function getActiveSessions(): int
    {
        $sessionPath = __DIR__ . '/../../config/sessions/';
        if (is_dir($sessionPath)) {
            $sessions = glob($sessionPath . 'sess_*');
            $activeCount = 0;
            
            foreach ($sessions as $session) {
                if (filemtime($session) > time() - 3600) { // 1시간 이내
                    $activeCount++;
                }
            }
            
            return $activeCount;
        }
        
        return 0;
    }

    /**
     * 로그 파일 크기 가져오기
     */
    private function getLogFileSize(): float
    {
        $logPath = __DIR__ . '/../../config/logs/';
        $totalSize = 0;
        
        if (is_dir($logPath)) {
            $files = glob($logPath . '*.log');
            foreach ($files as $file) {
                $totalSize += filesize($file);
            }
        }
        
        return $totalSize / 1024 / 1024; // MB 단위
    }

    /**
     * 메트릭 저장
     */
    private function saveMetric(string $name, float $value, string $unit = '', array $tags = []): void
    {
        $sql = "INSERT INTO {$this->metricsTable} (metric_name, metric_value, metric_unit, tags) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $name,
            $value,
            $unit,
            json_encode($tags)
        ]);
    }

    /**
     * 임계값 확인 및 알림 생성
     */
    private function checkThresholds(array $metrics): void
    {
        $thresholds = $this->config['alert_thresholds'];
        
        foreach ($metrics as $metric => $value) {
            if (isset($thresholds[$metric]) && $value > $thresholds[$metric]) {
                $this->createAlert(
                    $metric,
                    "{$metric} threshold exceeded: {$value} (threshold: {$thresholds[$metric]})",
                    $this->getSeverity($metric, $value, $thresholds[$metric])
                );
            }
        }
    }

    /**
     * 알림 심각도 결정
     */
    private function getSeverity(string $metric, float $value, float $threshold): string
    {
        $ratio = $value / $threshold;
        
        if ($ratio >= 1.5) {
            return 'critical';
        } elseif ($ratio >= 1.2) {
            return 'high';
        } elseif ($ratio >= 1.1) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * 알림 생성
     */
    public function createAlert(string $type, string $message, string $severity = 'medium'): bool
    {
        try {
            // 중복 알림 확인
            $existingAlert = $this->getActiveAlert($type);
            if ($existingAlert) {
                return false; // 이미 활성 알림이 있음
            }

            $sql = "INSERT INTO {$this->alertsTable} (alert_type, alert_message, severity) VALUES (?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([$type, $message, $severity]);

            if ($result) {
                $this->sendNotification($type, $message, $severity);
                
                $this->logger->warning('Alert created', [
                    'type' => $type,
                    'message' => $message,
                    'severity' => $severity
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Alert creation failed', [
                'error' => $e->getMessage(),
                'type' => $type,
                'message' => $message
            ]);

            return false;
        }
    }

    /**
     * 활성 알림 가져오기
     */
    private function getActiveAlert(string $type): ?array
    {
        $sql = "SELECT * FROM {$this->alertsTable} WHERE alert_type = ? AND status = 'active' ORDER BY created_at DESC LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$type]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * 알림 해결
     */
    public function resolveAlert(int $alertId, string $resolvedBy = null): bool
    {
        try {
            $sql = "UPDATE {$this->alertsTable} SET status = 'resolved', resolved_at = CURRENT_TIMESTAMP WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([$alertId]);

            if ($result && $resolvedBy) {
                $sql = "UPDATE {$this->alertsTable} SET acknowledged_by = ?, acknowledged_at = CURRENT_TIMESTAMP WHERE id = ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$resolvedBy, $alertId]);
            }

            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Alert resolution failed', [
                'error' => $e->getMessage(),
                'alert_id' => $alertId
            ]);

            return false;
        }
    }

    /**
     * 알림 목록 가져오기
     */
    public function getAlerts(string $status = null, int $limit = 50): array
    {
        $sql = "SELECT * FROM {$this->alertsTable}";
        $params = [];

        if ($status) {
            $sql .= " WHERE status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 메트릭 통계 가져오기
     */
    public function getMetricsStats(string $metric, int $hours = 24): array
    {
        $sql = "SELECT 
                    AVG(metric_value) as avg_value,
                    MIN(metric_value) as min_value,
                    MAX(metric_value) as max_value,
                    COUNT(*) as count
                FROM {$this->metricsTable} 
                WHERE metric_name = ? 
                AND timestamp >= datetime('now', '-{$hours} hours')";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$metric]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * 알림 전송
     */
    private function sendNotification(string $type, string $message, string $severity): void
    {
        $channels = $this->config['notification_channels'];
        
        if ($channels['email']) {
            $this->sendEmailNotification($type, $message, $severity);
        }
        
        if ($channels['slack']) {
            $this->sendSlackNotification($type, $message, $severity);
        }
        
        if ($channels['webhook']) {
            $this->sendWebhookNotification($type, $message, $severity);
        }
    }

    /**
     * 이메일 알림 전송
     */
    private function sendEmailNotification(string $type, string $message, string $severity): void
    {
        $to = $_ENV['ALERT_EMAIL'] ?? 'admin@example.com';
        $subject = "[{$severity}] System Alert: {$type}";
        $body = "Alert Type: {$type}\nSeverity: {$severity}\nMessage: {$message}\nTime: " . date('Y-m-d H:i:s');
        
        mail($to, $subject, $body);
    }

    /**
     * Slack 알림 전송
     */
    private function sendSlackNotification(string $type, string $message, string $severity): void
    {
        $webhookUrl = $_ENV['SLACK_WEBHOOK_URL'] ?? '';
        if (!$webhookUrl) {
            return;
        }

        $data = [
            'text' => "[{$severity}] System Alert: {$type}\n{$message}",
            'color' => $this->getSeverityColor($severity)
        ];

        $this->sendHttpRequest($webhookUrl, json_encode($data));
    }

    /**
     * Webhook 알림 전송
     */
    private function sendWebhookNotification(string $type, string $message, string $severity): void
    {
        $webhookUrl = $_ENV['WEBHOOK_URL'] ?? '';
        if (!$webhookUrl) {
            return;
        }

        $data = [
            'type' => $type,
            'message' => $message,
            'severity' => $severity,
            'timestamp' => date('c')
        ];

        $this->sendHttpRequest($webhookUrl, json_encode($data));
    }

    /**
     * HTTP 요청 전송
     */
    private function sendHttpRequest(string $url, string $data): void
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => $data
            ]
        ]);

        file_get_contents($url, false, $context);
    }

    /**
     * 심각도별 색상 가져오기
     */
    private function getSeverityColor(string $severity): string
    {
        return match($severity) {
            'critical' => '#ff0000',
            'high' => '#ff6600',
            'medium' => '#ffcc00',
            'low' => '#00cc00',
            default => '#cccccc'
        };
    }

    /**
     * 바이트 단위 변환
     */
    private function convertToBytes(string $value): int
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

    /**
     * 오래된 메트릭 정리
     */
    public function cleanupOldMetrics(): int
    {
        $retentionDays = $this->config['metrics_retention_days'];
        $sql = "DELETE FROM {$this->metricsTable} WHERE timestamp < datetime('now', '-{$retentionDays} days')";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->rowCount();
    }

    /**
     * 시스템 상태 요약
     */
    public function getSystemStatus(): array
    {
        $metrics = $this->collectMetrics();
        $alerts = $this->getAlerts('active', 10);
        
        return [
            'status' => empty($alerts) ? 'healthy' : 'warning',
            'metrics' => $metrics,
            'active_alerts' => count($alerts),
            'last_check' => date('Y-m-d H:i:s')
        ];
    }
} 