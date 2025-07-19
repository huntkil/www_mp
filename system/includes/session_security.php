<?php
/**
 * 세션 보안 관리 클래스
 * 세션 하이재킹 방지 및 보안 강화
 */

class SessionSecurity {
    private static $instance = null;
    private $session_timeout = 3600; // 1시간
    private $regenerate_interval = 300; // 5분마다 세션 ID 재생성
    
    private function __construct() {
        $this->initializeSession();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 세션 초기화 및 보안 설정
     */
    private function initializeSession() {
        // 세션이 이미 시작되었는지 확인
        if (session_status() === PHP_SESSION_NONE) {
            // 세션 보안 설정
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', 0); // HTTP 환경에서는 false
            ini_set('session.cookie_samesite', 'Strict');
            ini_set('session.use_strict_mode', 1);
            ini_set('session.gc_maxlifetime', $this->session_timeout);
            
            // 세션 이름 변경 (기본값 PHPSESSID 대신)
            session_name('MY_PLAYGROUND_SESSION');
            
            // 세션 시작
            session_start();
        }
        
        // 세션 보안 검사
        $this->validateSession();
    }
    
    /**
     * 세션 유효성 검사
     */
    private function validateSession() {
        // 세션 하이재킹 방지: IP 주소 확인
        if (isset($_SESSION['ip_address'])) {
            if ($_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
                $this->destroySession();
                throw new Exception("세션이 유효하지 않습니다. 다시 로그인해주세요.");
            }
        } else {
            $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
        }
        
        // 세션 하이재킹 방지: User Agent 확인
        if (isset($_SESSION['user_agent'])) {
            if ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
                $this->destroySession();
                throw new Exception("세션이 유효하지 않습니다. 다시 로그인해주세요.");
            }
        } else {
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        }
        
        // 세션 타임아웃 확인
        if (isset($_SESSION['last_activity'])) {
            if (time() - $_SESSION['last_activity'] > $this->session_timeout) {
                $this->destroySession();
                throw new Exception("세션이 만료되었습니다. 다시 로그인해주세요.");
            }
        }
        
        // 세션 ID 재생성 (일정 시간마다)
        if (isset($_SESSION['last_regeneration'])) {
            if (time() - $_SESSION['last_regeneration'] > $this->regenerate_interval) {
                $this->regenerateSession();
            }
        } else {
            $_SESSION['last_regeneration'] = time();
        }
        
        // 마지막 활동 시간 업데이트
        $_SESSION['last_activity'] = time();
    }
    
    /**
     * 세션 ID 재생성
     */
    private function regenerateSession() {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
    
    /**
     * 로그인 처리
     */
    public function login($user_id, $additional_data = []) {
        // 세션 재생성
        session_regenerate_id(true);
        
        // 기본 세션 데이터 설정
        $_SESSION['id'] = $user_id;
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['last_regeneration'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        
        // 추가 데이터 설정
        foreach ($additional_data as $key => $value) {
            $_SESSION[$key] = $value;
        }
        
        // 로그인 성공 로그
        $this->logActivity('login_success', [
            'user_id' => $user_id,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT']
        ]);
    }
    
    /**
     * 로그아웃 처리
     */
    public function logout() {
        $user_id = $_SESSION['id'] ?? 'unknown';
        
        // 로그아웃 로그
        $this->logActivity('logout', [
            'user_id' => $user_id,
            'ip' => $_SERVER['REMOTE_ADDR']
        ]);
        
        // 세션 제거
        $this->destroySession();
    }
    
    /**
     * 세션 완전 제거
     */
    private function destroySession() {
        // 세션 데이터 삭제
        $_SESSION = [];
        
        // 세션 쿠키 삭제
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        
        // 세션 파괴
        session_destroy();
    }
    
    /**
     * 로그인 상태 확인
     */
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    /**
     * 관리자 권한 확인
     */
    public function isAdmin() {
        return $this->isLoggedIn() && isset($_SESSION['id']) && $_SESSION['id'] === 'admin';
    }
    
    /**
     * 사용자 ID 반환
     */
    public function getUserId() {
        return $_SESSION['id'] ?? null;
    }
    
    /**
     * 세션 데이터 반환
     */
    public function getSessionData($key = null) {
        if ($key === null) {
            return $_SESSION;
        }
        return $_SESSION[$key] ?? null;
    }
    
    /**
     * 세션 데이터 설정
     */
    public function setSessionData($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    /**
     * 로그인 필수 페이지 접근 제한
     */
    public function requireLogin($redirect_url = '/') {
        if (!$this->isLoggedIn()) {
            header("Location: {$redirect_url}");
            exit;
        }
    }
    
    /**
     * 관리자 필수 페이지 접근 제한
     */
    public function requireAdmin($redirect_url = '/') {
        if (!$this->isAdmin()) {
            header("Location: {$redirect_url}");
            exit;
        }
    }
    
    /**
     * CSRF 토큰 생성
     */
    public function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * CSRF 토큰 검증
     */
    public function validateCSRFToken($token) {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * 활동 로그 기록
     */
    private function logActivity($action, $data = []) {
        $log_data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'action' => $action,
            'data' => $data
        ];
        
        // 로그 파일에 기록
        $log_message = json_encode($log_data, JSON_UNESCAPED_UNICODE);
        error_log("SESSION: {$log_message}");
        
        // 데이터베이스에 기록 (선택사항)
        if (class_exists('Database')) {
            try {
                $db = Database::getInstance();
                $db->insert('system_logs', [
                    'user_id' => $data['user_id'] ?? null,
                    'action' => $action,
                    'ip_address' => $data['ip'] ?? null,
                    'user_agent' => $data['user_agent'] ?? null,
                    'details' => json_encode($data)
                ]);
            } catch (Exception $e) {
                error_log("Failed to log activity to database: " . $e->getMessage());
            }
        }
    }
    
    /**
     * 세션 정보 반환 (디버깅용)
     */
    public function getSessionInfo() {
        if (!IS_LOCAL) {
            return ['error' => 'Session info only available in local environment'];
        }
        
        return [
            'session_id' => session_id(),
            'user_id' => $this->getUserId(),
            'logged_in' => $this->isLoggedIn(),
            'login_time' => $_SESSION['login_time'] ?? null,
            'last_activity' => $_SESSION['last_activity'] ?? null,
            'last_regeneration' => $_SESSION['last_regeneration'] ?? null,
            'ip_address' => $_SESSION['ip_address'] ?? null,
            'remaining_time' => $this->session_timeout - (time() - ($_SESSION['last_activity'] ?? time()))
        ];
    }
}

// 전역 함수로 쉽게 접근할 수 있도록 래퍼 함수 제공
function getSessionSecurity() {
    return SessionSecurity::getInstance();
}

// 자동으로 세션 보안 초기화
$sessionSecurity = SessionSecurity::getInstance(); 