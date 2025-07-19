<?php
/**
 * 구조화된 로깅 시스템
 * 다양한 로그 레벨과 컨텍스트 정보를 지원
 */

class Logger {
    private static $instance = null;
    private $logDir;
    private $logLevel;
    private $logLevels = [
        'emergency' => 0,
        'alert'     => 1,
        'critical'  => 2,
        'error'     => 3,
        'warning'   => 4,
        'notice'    => 5,
        'info'      => 6,
        'debug'     => 7
    ];
    
    private function __construct() {
        $this->logDir = __DIR__ . '/../../config/logs/';
        $this->logLevel = $this->logLevels[IS_LOCAL ? 'debug' : 'info'];
        
        // 로그 디렉토리 생성
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 긴급 로그
     */
    public function emergency($message, array $context = []) {
        $this->log('emergency', $message, $context);
    }
    
    /**
     * 경고 로그
     */
    public function alert($message, array $context = []) {
        $this->log('alert', $message, $context);
    }
    
    /**
     * 치명적 오류 로그
     */
    public function critical($message, array $context = []) {
        $this->log('critical', $message, $context);
    }
    
    /**
     * 오류 로그
     */
    public function error($message, array $context = []) {
        $this->log('error', $message, $context);
    }
    
    /**
     * 경고 로그
     */
    public function warning($message, array $context = []) {
        $this->log('warning', $message, $context);
    }
    
    /**
     * 알림 로그
     */
    public function notice($message, array $context = []) {
        $this->log('notice', $message, $context);
    }
    
    /**
     * 정보 로그
     */
    public function info($message, array $context = []) {
        $this->log('info', $message, $context);
    }
    
    /**
     * 디버그 로그
     */
    public function debug($message, array $context = []) {
        $this->log('debug', $message, $context);
    }
    
    /**
     * 로그 기록
     */
    private function log($level, $message, array $context = []) {
        if ($this->logLevels[$level] > $this->logLevel) {
            return;
        }
        
        $logEntry = $this->formatLogEntry($level, $message, $context);
        $logFile = $this->getLogFile($level);
        
        file_put_contents($logFile, $logEntry . PHP_EOL, FILE_APPEND | LOCK_EX);
        
        // 에러 레벨 이상은 에러 로그에도 기록
        if ($this->logLevels[$level] <= $this->logLevels['error']) {
            $errorLogFile = $this->logDir . 'error.log';
            file_put_contents($errorLogFile, $logEntry . PHP_EOL, FILE_APPEND | LOCK_EX);
        }
    }
    
    /**
     * 로그 엔트리 포맷
     */
    private function formatLogEntry($level, $message, array $context = []) {
        $timestamp = date('Y-m-d H:i:s');
        $requestId = $this->getRequestId();
        $userId = $this->getUserId();
        $ip = $this->getClientIp();
        $userAgent = $this->getUserAgent();
        $url = $this->getRequestUrl();
        $method = $this->getRequestMethod();
        
        $logData = [
            'timestamp' => $timestamp,
            'level' => strtoupper($level),
            'request_id' => $requestId,
            'user_id' => $userId,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'method' => $method,
            'url' => $url,
            'message' => $message,
            'context' => $context
        ];
        
        return json_encode($logData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    
    /**
     * 로그 파일 경로 생성
     */
    private function getLogFile($level) {
        $date = date('Y-m-d');
        return $this->logDir . "{$level}_{$date}.log";
    }
    
    /**
     * 요청 ID 생성
     */
    private function getRequestId() {
        if (!isset($_SESSION['request_id'])) {
            $_SESSION['request_id'] = uniqid('req_', true);
        }
        return $_SESSION['request_id'];
    }
    
    /**
     * 사용자 ID 가져오기
     */
    private function getUserId() {
        return $_SESSION['id'] ?? 'guest';
    }
    
    /**
     * 클라이언트 IP 가져오기
     */
    private function getClientIp() {
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * User Agent 가져오기
     */
    private function getUserAgent() {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    }
    
    /**
     * 요청 URL 가져오기
     */
    private function getRequestUrl() {
        return $_SERVER['REQUEST_URI'] ?? 'unknown';
    }
    
    /**
     * 요청 메소드 가져오기
     */
    private function getRequestMethod() {
        return $_SERVER['REQUEST_METHOD'] ?? 'unknown';
    }
    
    /**
     * 성능 메트릭 로깅
     */
    public function performance($operation, $duration, array $context = []) {
        $context['duration_ms'] = round($duration * 1000, 2);
        $context['operation'] = $operation;
        
        $this->info("Performance: {$operation} completed in {$context['duration_ms']}ms", $context);
    }
    
    /**
     * 데이터베이스 쿼리 로깅
     */
    public function query($sql, $params, $duration, $rows = null) {
        $context = [
            'sql' => $sql,
            'params' => $params,
            'duration_ms' => round($duration * 1000, 2),
            'rows' => $rows
        ];
        
        $this->debug("Database query executed", $context);
    }
    
    /**
     * API 요청 로깅
     */
    public function apiRequest($url, $method, $params, $response, $duration) {
        $context = [
            'url' => $url,
            'method' => $method,
            'params' => $params,
            'response_size' => strlen($response),
            'duration_ms' => round($duration * 1000, 2)
        ];
        
        $this->info("API request completed", $context);
    }
    
    /**
     * 보안 이벤트 로깅
     */
    public function security($event, $details, $severity = 'warning') {
        $context = [
            'event' => $event,
            'details' => $details,
            'severity' => $severity
        ];
        
        $this->warning("Security event: {$event}", $context);
    }
    
    /**
     * 사용자 활동 로깅
     */
    public function userActivity($action, $details = []) {
        $context = [
            'action' => $action,
            'details' => $details
        ];
        
        $this->info("User activity: {$action}", $context);
    }
    
    /**
     * 로그 파일 로테이션
     */
    public function rotateLogs($maxDays = 30) {
        $files = glob($this->logDir . '*.log');
        $cutoff = time() - ($maxDays * 24 * 60 * 60);
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
                $this->info("Rotated log file: {$file}");
            }
        }
    }
    
    /**
     * 로그 통계 생성
     */
    public function getLogStats($days = 7) {
        $stats = [
            'total_entries' => 0,
            'by_level' => [],
            'by_hour' => [],
            'errors' => 0,
            'warnings' => 0
        ];
        
        $files = glob($this->logDir . '*.log');
        $cutoff = time() - ($days * 24 * 60 * 60);
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                continue;
            }
            
            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            foreach ($lines as $line) {
                $data = json_decode($line, true);
                if (!$data) continue;
                
                $stats['total_entries']++;
                
                // 레벨별 통계
                $level = strtolower($data['level']);
                $stats['by_level'][$level] = ($stats['by_level'][$level] ?? 0) + 1;
                
                // 시간별 통계
                $hour = date('H', strtotime($data['timestamp']));
                $stats['by_hour'][$hour] = ($stats['by_hour'][$hour] ?? 0) + 1;
                
                // 에러/경고 카운트
                if (in_array($level, ['error', 'critical', 'emergency'])) {
                    $stats['errors']++;
                } elseif ($level === 'warning') {
                    $stats['warnings']++;
                }
            }
        }
        
        return $stats;
    }
}

// 전역 헬퍼 함수들
function log_emergency($message, array $context = []) {
    Logger::getInstance()->emergency($message, $context);
}

function log_alert($message, array $context = []) {
    Logger::getInstance()->alert($message, $context);
}

function log_critical($message, array $context = []) {
    Logger::getInstance()->critical($message, $context);
}

function log_error($message, array $context = []) {
    Logger::getInstance()->error($message, $context);
}

function log_warning($message, array $context = []) {
    Logger::getInstance()->warning($message, $context);
}

function log_notice($message, array $context = []) {
    Logger::getInstance()->notice($message, $context);
}

function log_info($message, array $context = []) {
    Logger::getInstance()->info($message, $context);
}

function log_debug($message, array $context = []) {
    Logger::getInstance()->debug($message, $context);
}

function log_performance($operation, $duration, array $context = []) {
    Logger::getInstance()->performance($operation, $duration, $context);
}

function log_query($sql, $params, $duration, $rows = null) {
    Logger::getInstance()->query($sql, $params, $duration, $rows);
}

function log_api_request($url, $method, $params, $response, $duration) {
    Logger::getInstance()->apiRequest($url, $method, $params, $response, $duration);
}

function log_security($event, $details, $severity = 'warning') {
    Logger::getInstance()->security($event, $details, $severity);
}

function log_user_activity($action, $details = []) {
    Logger::getInstance()->userActivity($action, $details);
} 