<?php
/**
 * 성능 모니터링 시스템
 * 요청 처리 시간, 메모리 사용량, 데이터베이스 쿼리 등을 추적
 */

class PerformanceMonitor {
    private static $instance = null;
    private $startTime;
    private $startMemory;
    private $queries = [];
    private $apiCalls = [];
    private $slowQueryThreshold = 1.0; // 1초 이상
    private $slowApiThreshold = 2.0; // 2초 이상
    
    private function __construct() {
        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 요청 시작 시 호출
     */
    public function startRequest() {
        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage();
        $this->queries = [];
        $this->apiCalls = [];
        
        log_info('Request started', [
            'url' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    }
    
    /**
     * 요청 종료 시 호출
     */
    public function endRequest() {
        $duration = microtime(true) - $this->startTime;
        $memoryUsed = memory_get_usage() - $this->startMemory;
        $peakMemory = memory_get_peak_usage();
        
        $metrics = [
            'duration_ms' => round($duration * 1000, 2),
            'memory_used_kb' => round($memoryUsed / 1024, 2),
            'peak_memory_kb' => round($peakMemory / 1024, 2),
            'query_count' => count($this->queries),
            'api_call_count' => count($this->apiCalls),
            'slow_queries' => count(array_filter($this->queries, fn($q) => $q['duration'] > $this->slowQueryThreshold)),
            'slow_api_calls' => count(array_filter($this->apiCalls, fn($a) => $a['duration'] > $this->slowApiThreshold))
        ];
        
        // 성능 로깅
        log_performance('Request completed', $duration, $metrics);
        
        // 느린 요청 경고
        if ($duration > 5.0) {
            log_warning('Slow request detected', $metrics);
        }
        
        // 메모리 사용량 경고
        if ($peakMemory > 50 * 1024 * 1024) { // 50MB
            log_warning('High memory usage detected', $metrics);
        }
        
        return $metrics;
    }
    
    /**
     * 데이터베이스 쿼리 기록
     */
    public function recordQuery($sql, $params, $duration, $rows = null) {
        $query = [
            'sql' => $sql,
            'params' => $params,
            'duration' => $duration,
            'rows' => $rows,
            'timestamp' => microtime(true)
        ];
        
        $this->queries[] = $query;
        
        // 쿼리 로깅
        log_query($sql, $params, $duration, $rows);
        
        // 느린 쿼리 경고
        if ($duration > $this->slowQueryThreshold) {
            log_warning('Slow query detected', $query);
        }
    }
    
    /**
     * API 호출 기록
     */
    public function recordApiCall($url, $method, $params, $response, $duration) {
        $apiCall = [
            'url' => $url,
            'method' => $method,
            'params' => $params,
            'response_size' => strlen($response),
            'duration' => $duration,
            'timestamp' => microtime(true)
        ];
        
        $this->apiCalls[] = $apiCall;
        
        // API 호출 로깅
        log_api_request($url, $method, $params, $response, $duration);
        
        // 느린 API 호출 경고
        if ($duration > $this->slowApiThreshold) {
            log_warning('Slow API call detected', $apiCall);
        }
    }
    
    /**
     * 성능 통계 생성
     */
    public function getStats() {
        $duration = microtime(true) - $this->startTime;
        $memoryUsed = memory_get_usage() - $this->startMemory;
        
        $queryStats = $this->analyzeQueries();
        $apiStats = $this->analyzeApiCalls();
        
        return [
            'request' => [
                'duration_ms' => round($duration * 1000, 2),
                'memory_used_kb' => round($memoryUsed / 1024, 2),
                'peak_memory_kb' => round(memory_get_peak_usage() / 1024, 2)
            ],
            'queries' => $queryStats,
            'api_calls' => $apiStats
        ];
    }
    
    /**
     * 쿼리 분석
     */
    private function analyzeQueries() {
        if (empty($this->queries)) {
            return [
                'count' => 0,
                'total_duration' => 0,
                'average_duration' => 0,
                'slow_queries' => 0
            ];
        }
        
        $totalDuration = array_sum(array_column($this->queries, 'duration'));
        $slowQueries = count(array_filter($this->queries, fn($q) => $q['duration'] > $this->slowQueryThreshold));
        
        return [
            'count' => count($this->queries),
            'total_duration' => round($totalDuration, 4),
            'average_duration' => round($totalDuration / count($this->queries), 4),
            'slow_queries' => $slowQueries,
            'slowest_query' => max(array_column($this->queries, 'duration'))
        ];
    }
    
    /**
     * API 호출 분석
     */
    private function analyzeApiCalls() {
        if (empty($this->apiCalls)) {
            return [
                'count' => 0,
                'total_duration' => 0,
                'average_duration' => 0,
                'slow_calls' => 0
            ];
        }
        
        $totalDuration = array_sum(array_column($this->apiCalls, 'duration'));
        $slowCalls = count(array_filter($this->apiCalls, fn($a) => $a['duration'] > $this->slowApiThreshold));
        
        return [
            'count' => count($this->apiCalls),
            'total_duration' => round($totalDuration, 4),
            'average_duration' => round($totalDuration / count($this->apiCalls), 4),
            'slow_calls' => $slowCalls,
            'slowest_call' => max(array_column($this->apiCalls, 'duration'))
        ];
    }
    
    /**
     * 성능 대시보드 데이터 생성
     */
    public function getDashboardData($hours = 24) {
        $stats = [
            'requests' => [
                'total' => 0,
                'average_duration' => 0,
                'slow_requests' => 0,
                'errors' => 0
            ],
            'queries' => [
                'total' => 0,
                'average_duration' => 0,
                'slow_queries' => 0
            ],
            'memory' => [
                'average_usage' => 0,
                'peak_usage' => 0
            ],
            'hourly_stats' => []
        ];
        
        // 로그 파일에서 데이터 수집
        $logFiles = glob(__DIR__ . '/../../config/logs/*.log');
        $cutoff = time() - ($hours * 3600);
        
        foreach ($logFiles as $file) {
            if (filemtime($file) < $cutoff) {
                continue;
            }
            
            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            foreach ($lines as $line) {
                $data = json_decode($line, true);
                if (!$data) continue;
                
                $logTime = strtotime($data['timestamp']);
                if ($logTime < $cutoff) continue;
                
                $hour = date('H', $logTime);
                
                // 시간별 통계 초기화
                if (!isset($stats['hourly_stats'][$hour])) {
                    $stats['hourly_stats'][$hour] = [
                        'requests' => 0,
                        'errors' => 0,
                        'avg_duration' => 0
                    ];
                }
                
                $stats['hourly_stats'][$hour]['requests']++;
                
                // 성능 로그 분석
                if (isset($data['context']['duration_ms'])) {
                    $duration = $data['context']['duration_ms'] / 1000;
                    $stats['requests']['total']++;
                    $stats['requests']['average_duration'] += $duration;
                    
                    if ($duration > 5.0) {
                        $stats['requests']['slow_requests']++;
                    }
                    
                    $stats['hourly_stats'][$hour]['avg_duration'] += $duration;
                }
                
                // 에러 로그 분석
                if (in_array(strtolower($data['level']), ['error', 'critical', 'emergency'])) {
                    $stats['requests']['errors']++;
                    $stats['hourly_stats'][$hour]['errors']++;
                }
                
                // 메모리 사용량 분석
                if (isset($data['context']['memory_used_kb'])) {
                    $memory = $data['context']['memory_used_kb'];
                    $stats['memory']['average_usage'] += $memory;
                    $stats['memory']['peak_usage'] = max($stats['memory']['peak_usage'], $memory);
                }
                
                // 쿼리 통계 분석
                if (isset($data['context']['query_count'])) {
                    $stats['queries']['total'] += $data['context']['query_count'];
                    if (isset($data['context']['slow_queries'])) {
                        $stats['queries']['slow_queries'] += $data['context']['slow_queries'];
                    }
                }
            }
        }
        
        // 평균 계산
        if ($stats['requests']['total'] > 0) {
            $stats['requests']['average_duration'] /= $stats['requests']['total'];
            $stats['memory']['average_usage'] /= $stats['requests']['total'];
        }
        
        // 시간별 평균 계산
        foreach ($stats['hourly_stats'] as $hour => &$hourStats) {
            if ($hourStats['requests'] > 0) {
                $hourStats['avg_duration'] /= $hourStats['requests'];
            }
        }
        
        return $stats;
    }
    
    /**
     * 성능 알림 설정
     */
    public function setThresholds($slowQuery = 1.0, $slowApi = 2.0) {
        $this->slowQueryThreshold = $slowQuery;
        $this->slowApiThreshold = $slowApi;
    }
    
    /**
     * 메모리 사용량 확인
     */
    public function getMemoryUsage() {
        return [
            'current' => memory_get_usage(),
            'peak' => memory_get_peak_usage(),
            'limit' => ini_get('memory_limit')
        ];
    }
    
    /**
     * 실행 시간 확인
     */
    public function getExecutionTime() {
        return microtime(true) - $this->startTime;
    }
}

// 전역 헬퍼 함수들
function start_performance_monitoring() {
    PerformanceMonitor::getInstance()->startRequest();
}

function end_performance_monitoring() {
    return PerformanceMonitor::getInstance()->endRequest();
}

function record_query($sql, $params, $duration, $rows = null) {
    PerformanceMonitor::getInstance()->recordQuery($sql, $params, $duration, $rows);
}

function record_api_call($url, $method, $params, $response, $duration) {
    PerformanceMonitor::getInstance()->recordApiCall($url, $method, $params, $response, $duration);
}

function get_performance_stats() {
    return PerformanceMonitor::getInstance()->getStats();
} 