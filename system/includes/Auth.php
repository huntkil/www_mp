<?php
/**
 * 사용자 인증 시스템
 * 로그인, 로그아웃, 권한 관리 기능
 */

class Auth {
    private static $instance = null;
    private $db;
    private $session;
    private $logger;
    
    private function __construct() {
        $this->db = Database::getInstance();
        $this->session = getSessionSecurity();
        $this->logger = Logger::getInstance();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 사용자 로그인
     */
    public function login($username, $password) {
        try {
            // 입력 검증
            if (empty($username) || empty($password)) {
                throw new Exception('사용자명과 비밀번호를 입력해주세요.');
            }
            
            // 사용자 조회
            $user = $this->getUserByUsername($username);
            if (!$user) {
                $this->logFailedLogin($username, 'User not found');
                throw new Exception('사용자명 또는 비밀번호가 올바르지 않습니다.');
            }
            
            // 비밀번호 검증
            if (!password_verify($password, $user['password'])) {
                $this->logFailedLogin($username, 'Invalid password');
                throw new Exception('사용자명 또는 비밀번호가 올바르지 않습니다.');
            }
            
            // 계정 상태 확인
            if (isset($user['status']) && $user['status'] !== 'active') {
                $this->logFailedLogin($username, 'Account inactive');
                throw new Exception('계정이 비활성화되었습니다.');
            }
            
            // 로그인 성공
            $this->session->login($user['id'], [
                'username' => $user['username'],
                'email' => $user['email'] ?? null,
                'role' => $user['role'] ?? 'user'
            ]);
            
            // 로그인 기록
            $this->logSuccessfulLogin($user['id'], $username);
            
            // 마지막 로그인 시간 업데이트
            $this->updateLastLogin($user['id']);
            
            return [
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'] ?? null,
                    'role' => $user['role'] ?? 'user'
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 사용자 로그아웃
     */
    public function logout() {
        $userId = $this->session->getUserId();
        $this->session->logout();
        
        if ($userId) {
            $this->logger->info('User logged out', ['user_id' => $userId]);
        }
        
        return ['success' => true];
    }
    
    /**
     * 사용자 등록
     */
    public function register($data) {
        try {
            // 입력 검증
            $validator = new Validator($data);
            $validator->required('username')
                     ->minLength('username', 3)
                     ->maxLength('username', 50)
                     ->required('password')
                     ->minLength('password', 8)
                     ->email('email');
            
            if ($validator->fails()) {
                throw new Exception('입력 데이터가 올바르지 않습니다.');
            }
            
            // 사용자명 중복 확인
            if ($this->getUserByUsername($data['username'])) {
                throw new Exception('이미 사용 중인 사용자명입니다.');
            }
            
            // 이메일 중복 확인
            if ($this->getUserByEmail($data['email'])) {
                throw new Exception('이미 사용 중인 이메일입니다.');
            }
            
            // 비밀번호 해시
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // 사용자 생성
            $userId = $this->db->insert(
                'users',
                [
                    'username' => $data['username'],
                    'password' => $hashedPassword,
                    'email' => $data['email'],
                    'role' => 'user',
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s')
                ]
            );
            
            if (!$userId) {
                throw new Exception('사용자 등록에 실패했습니다.');
            }
            
            $this->logger->info('User registered', ['user_id' => $userId, 'username' => $data['username']]);
            
            return [
                'success' => true,
                'user_id' => $userId
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 비밀번호 변경
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            // 현재 사용자 조회
            $user = $this->getUserById($userId);
            if (!$user) {
                throw new Exception('사용자를 찾을 수 없습니다.');
            }
            
            // 현재 비밀번호 검증
            if (!password_verify($currentPassword, $user['password'])) {
                throw new Exception('현재 비밀번호가 올바르지 않습니다.');
            }
            
            // 새 비밀번호 검증
            if (strlen($newPassword) < 8) {
                throw new Exception('새 비밀번호는 8자 이상이어야 합니다.');
            }
            
            // 비밀번호 업데이트
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $this->db->update(
                'users',
                ['password' => $hashedPassword],
                'id = ?',
                [$userId]
            );
            
            $this->logger->info('Password changed', ['user_id' => $userId]);
            
            return ['success' => true];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 사용자명으로 사용자 조회
     */
    private function getUserByUsername($username) {
        $sql = "SELECT * FROM users WHERE username = ?";
        return $this->db->selectOne($sql, [$username]);
    }
    
    /**
     * 이메일로 사용자 조회
     */
    private function getUserByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ?";
        return $this->db->selectOne($sql, [$email]);
    }
    
    /**
     * ID로 사용자 조회
     */
    private function getUserById($id) {
        $sql = "SELECT * FROM users WHERE id = ?";
        return $this->db->selectOne($sql, [$id]);
    }
    
    /**
     * 마지막 로그인 시간 업데이트
     */
    private function updateLastLogin($userId) {
        $sql = "UPDATE users SET last_login = ? WHERE id = ?";
        $this->db->update($sql, [date('Y-m-d H:i:s'), $userId]);
    }
    
    /**
     * 실패한 로그인 기록
     */
    private function logFailedLogin($username, $reason) {
        $this->logger->warning('Failed login attempt', [
            'username' => $username,
            'reason' => $reason,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    }
    
    /**
     * 성공한 로그인 기록
     */
    private function logSuccessfulLogin($userId, $username) {
        $this->logger->info('User logged in', [
            'user_id' => $userId,
            'username' => $username,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    }
    
    /**
     * 현재 로그인된 사용자 정보 반환
     */
    public function user() {
        if (!$this->session->isLoggedIn()) {
            return null;
        }
        
        $userId = $this->session->getUserId();
        return $this->getUserById($userId);
    }
    
    /**
     * 로그인 상태 확인
     */
    public function check() {
        return $this->session->isLoggedIn();
    }
    
    /**
     * 관리자 권한 확인
     */
    public function isAdmin() {
        if (!$this->check()) {
            return false;
        }
        
        $user = $this->user();
        return $user && ($user['role'] === 'admin' || $user['username'] === 'admin');
    }
    
    /**
     * 권한 확인
     */
    public function hasRole($role) {
        if (!$this->check()) {
            return false;
        }
        
        $user = $this->user();
        return $user && $user['role'] === $role;
    }
    
    /**
     * 사용자 목록 조회 (관리자용)
     */
    public function getUsers($page = 1, $perPage = 20) {
        if (!$this->isAdmin()) {
            throw new Exception('관리자 권한이 필요합니다.');
        }
        
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT id, username, email, role, status, created_at, last_login 
                FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?";
        
        $users = $this->db->select($sql, [$perPage, $offset]);
        
        // 총 사용자 수 조회
        $countSql = "SELECT COUNT(*) as count FROM users";
        $total = $this->db->selectOne($countSql)['count'];
        
        return [
            'users' => $users,
            'total' => $total,
            'current_page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }
    
    /**
     * 사용자 상태 변경 (관리자용)
     */
    public function updateUserStatus($userId, $status) {
        if (!$this->isAdmin()) {
            throw new Exception('관리자 권한이 필요합니다.');
        }
        
        if (!in_array($status, ['active', 'inactive', 'suspended'])) {
            throw new Exception('유효하지 않은 상태입니다.');
        }
        
        $sql = "UPDATE users SET status = ? WHERE id = ?";
        $result = $this->db->update($sql, [$status, $userId]);
        
        if ($result) {
            $this->logger->info('User status updated', [
                'user_id' => $userId,
                'status' => $status,
                'updated_by' => $this->session->getUserId()
            ]);
        }
        
        return $result;
    }
    
    /**
     * 사용자 역할 변경 (관리자용)
     */
    public function updateUserRole($userId, $role) {
        if (!$this->isAdmin()) {
            throw new Exception('관리자 권한이 필요합니다.');
        }
        
        if (!in_array($role, ['user', 'moderator', 'admin'])) {
            throw new Exception('유효하지 않은 역할입니다.');
        }
        
        $sql = "UPDATE users SET role = ? WHERE id = ?";
        $result = $this->db->update($sql, [$role, $userId]);
        
        if ($result) {
            $this->logger->info('User role updated', [
                'user_id' => $userId,
                'role' => $role,
                'updated_by' => $this->session->getUserId()
            ]);
        }
        
        return $result;
    }
    
    /**
     * 사용자 삭제 (관리자용)
     */
    public function deleteUser($userId) {
        if (!$this->isAdmin()) {
            throw new Exception('관리자 권한이 필요합니다.');
        }
        
        // 자신을 삭제하려는 경우 방지
        if ($userId == $this->session->getUserId()) {
            throw new Exception('자신의 계정을 삭제할 수 없습니다.');
        }
        
        $sql = "DELETE FROM users WHERE id = ?";
        $result = $this->db->delete($sql, [$userId]);
        
        if ($result) {
            $this->logger->info('User deleted', [
                'user_id' => $userId,
                'deleted_by' => $this->session->getUserId()
            ]);
        }
        
        return $result;
    }
}

// 전역 헬퍼 함수들
function auth() {
    return Auth::getInstance();
}

function user() {
    return auth()->user();
}

function is_logged_in() {
    return auth()->check();
}

function is_admin() {
    return auth()->isAdmin();
}

function has_role($role) {
    return auth()->hasRole($role);
} 