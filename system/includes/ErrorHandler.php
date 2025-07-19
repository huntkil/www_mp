<?php
/**
 * 향상된 에러 처리 시스템
 * 개발/프로덕션 환경에 따른 적절한 에러 처리
 */

class ErrorHandler {
    private static $instance = null;
    private $logFile;
    private $isDevelopment;
    
    private function __construct() {
        $this->logFile = __DIR__ . '/../../config/logs/error.log';
        $this->isDevelopment = IS_LOCAL;
        
        // 에러 핸들러 설정
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleFatalError']);
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 일반 에러 처리
     */
    public function handleError($errno, $errstr, $errfile, $errline) {
        $error = [
            'type' => 'Error',
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
            'level' => $this->getErrorLevel($errno),
            'timestamp' => date('Y-m-d H:i:s'),
            'url' => $_SERVER['REQUEST_URI'] ?? 'CLI',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        $this->logError($error);
        
        if ($this->isDevelopment) {
            $this->displayError($error);
        } else {
            $this->displayUserFriendlyError();
        }
        
        return true; // 기본 에러 핸들러 실행 방지
    }
    
    /**
     * 예외 처리
     */
    public function handleException($exception) {
        $error = [
            'type' => 'Exception',
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'class' => get_class($exception),
            'timestamp' => date('Y-m-d H:i:s'),
            'url' => $_SERVER['REQUEST_URI'] ?? 'CLI',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        $this->logError($error);
        
        if ($this->isDevelopment) {
            $this->displayException($error);
        } else {
            $this->displayUserFriendlyError();
        }
    }
    
    /**
     * 치명적 에러 처리
     */
    public function handleFatalError() {
        $error = error_get_last();
        
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $errorData = [
                'type' => 'Fatal Error',
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line'],
                'timestamp' => date('Y-m-d H:i:s'),
                'url' => $_SERVER['REQUEST_URI'] ?? 'CLI',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ];
            
            $this->logError($errorData);
            
            if ($this->isDevelopment) {
                $this->displayError($errorData);
            } else {
                $this->displayUserFriendlyError();
            }
        }
    }
    
    /**
     * 에러 로그 기록
     */
    private function logError($error) {
        // 로그 디렉토리 생성
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logEntry = json_encode($error, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n---\n";
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * 개발 환경 에러 표시
     */
    private function displayError($error) {
        if (headers_sent()) {
            echo "<h1>Error</h1>";
            echo "<p><strong>Message:</strong> " . htmlspecialchars($error['message']) . "</p>";
            echo "<p><strong>File:</strong> " . htmlspecialchars($error['file']) . "</p>";
            echo "<p><strong>Line:</strong> " . htmlspecialchars($error['line']) . "</p>";
            echo "<p><strong>Time:</strong> " . htmlspecialchars($error['timestamp']) . "</p>";
        } else {
            http_response_code(500);
            include __DIR__ . '/../views/error_dev.php';
        }
    }
    
    /**
     * 개발 환경 예외 표시
     */
    private function displayException($error) {
        if (headers_sent()) {
            echo "<h1>Exception</h1>";
            echo "<p><strong>Type:</strong> " . htmlspecialchars($error['class']) . "</p>";
            echo "<p><strong>Message:</strong> " . htmlspecialchars($error['message']) . "</p>";
            echo "<p><strong>File:</strong> " . htmlspecialchars($error['file']) . "</p>";
            echo "<p><strong>Line:</strong> " . htmlspecialchars($error['line']) . "</p>";
            echo "<p><strong>Time:</strong> " . htmlspecialchars($error['timestamp']) . "</p>";
            echo "<h3>Stack Trace:</h3>";
            echo "<pre>" . htmlspecialchars($error['trace']) . "</pre>";
        } else {
            http_response_code(500);
            include __DIR__ . '/../views/exception_dev.php';
        }
    }
    
    /**
     * 사용자 친화적 에러 표시 (프로덕션)
     */
    private function displayUserFriendlyError() {
        if (!headers_sent()) {
            http_response_code(500);
        }
        include __DIR__ . '/../views/error_prod.php';
    }
    
    /**
     * 에러 레벨 변환
     */
    private function getErrorLevel($errno) {
        switch ($errno) {
            case E_ERROR: return 'E_ERROR';
            case E_WARNING: return 'E_WARNING';
            case E_PARSE: return 'E_PARSE';
            case E_NOTICE: return 'E_NOTICE';
            case E_CORE_ERROR: return 'E_CORE_ERROR';
            case E_CORE_WARNING: return 'E_CORE_WARNING';
            case E_COMPILE_ERROR: return 'E_COMPILE_ERROR';
            case E_COMPILE_WARNING: return 'E_COMPILE_WARNING';
            case E_USER_ERROR: return 'E_USER_ERROR';
            case E_USER_WARNING: return 'E_USER_WARNING';
            case E_USER_NOTICE: return 'E_USER_NOTICE';
            case E_STRICT: return 'E_STRICT';
            case E_RECOVERABLE_ERROR: return 'E_RECOVERABLE_ERROR';
            case E_DEPRECATED: return 'E_DEPRECATED';
            case E_USER_DEPRECATED: return 'E_USER_DEPRECATED';
            default: return 'UNKNOWN';
        }
    }
    
    /**
     * 사용자 정의 에러 발생
     */
    public static function throwError($message, $code = 0, $context = []) {
        $error = new Exception($message, $code);
        // context를 에러 메시지에 포함
        if (!empty($context)) {
            $error = new Exception($message . ' Context: ' . json_encode($context), $code);
        }
        throw $error;
    }
    
    /**
     * 데이터베이스 에러 처리
     */
    public static function handleDatabaseError($exception, $query = '') {
        $error = [
            'type' => 'Database Error',
            'message' => $exception->getMessage(),
            'query' => $query,
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $handler = self::getInstance();
        $handler->logError($error);
        
        if ($handler->isDevelopment) {
            $handler->displayError($error);
        } else {
            $handler->displayUserFriendlyError();
        }
    }
    
    /**
     * API 에러 응답
     */
    public static function apiError($message, $code = 400, $details = []) {
        http_response_code($code);
        header('Content-Type: application/json');
        
        $response = [
            'success' => false,
            'error' => [
                'message' => $message,
                'code' => $code
            ]
        ];
        
        if (!empty($details)) {
            $response['error']['details'] = $details;
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * API 성공 응답
     */
    public static function apiSuccess($data = null, $message = 'Success') {
        header('Content-Type: application/json');
        
        $response = [
            'success' => true,
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// 전역 함수로 사용할 수 있도록 헬퍼 함수들
function throwError($message, $code = 0, $context = []) {
    ErrorHandler::throwError($message, $code, $context);
}

function apiError($message, $code = 400, $details = []) {
    ErrorHandler::apiError($message, $code, $details);
}

function apiSuccess($data = null, $message = 'Success') {
    ErrorHandler::apiSuccess($data, $message);
} 