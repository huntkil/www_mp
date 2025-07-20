<?php
// 세션 시작
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Config 사용
require_once __DIR__ . '/../includes/config.php';

// 중복 로그인 방지
if (isset($_SESSION['id'])) {
    header("Location: ../../index.php");
    exit;
}

try {
    // 입력값 검증
    $id = trim($_POST['id'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    // 디버깅: 입력된 값들 로그
    error_log("Login attempt - ID: " . $id . ", Password length: " . strlen($password));
    error_log("POST data: " . print_r($_POST, true));
    
    if (empty($id) || empty($password)) {
        throw new Exception("아이디와 비밀번호를 모두 입력해주세요.");
    }
    
    // 아이디 길이 제한
    if (strlen($id) > 50) {
        throw new Exception("아이디가 너무 깁니다.");
    }
    
    // 로컬 환경에서는 JSON 파일 사용, 프로덕션에서는 데이터베이스 사용
    error_log("IS_LOCAL: " . (IS_LOCAL ? 'true' : 'false'));
    
    if (IS_LOCAL) {
        // 로컬 환경: JSON 파일 기반 인증
        $users_file = __DIR__ . '/users.json';
        error_log("Users file path: " . $users_file);
        error_log("Users file exists: " . (file_exists($users_file) ? 'yes' : 'no'));
        
        if (!file_exists($users_file)) {
            throw new Exception("사용자 데이터 파일을 찾을 수 없습니다.");
        }
        
        $users_data = json_decode(file_get_contents($users_file), true);
        if (!$users_data) {
            throw new Exception("사용자 데이터를 읽을 수 없습니다.");
        }
        
        if (!isset($users_data[$id])) {
            throw new Exception("아이디 또는 비밀번호가 일치하지 않습니다.");
        }
        
        $user = $users_data[$id];
        
        // 계정 상태 확인
        if ($user['status'] !== 'active') {
            throw new Exception("비활성화된 계정입니다.");
        }
        
        // 계정 잠금 확인 (5회 실패 시 30분 잠금)
        $login_attempts = $user['login_attempts'] ?? 0;
        if ($login_attempts >= 5) {
            throw new Exception("계정이 잠겨있습니다. 관리자에게 문의하세요.");
        }
        
        // 비밀번호 확인
        if (!password_verify($password, $user['password'])) {
            // 로그인 실패 횟수 증가
            $users_data[$id]['login_attempts'] = $login_attempts + 1;
            file_put_contents($users_file, json_encode($users_data, JSON_PRETTY_PRINT));
            
            throw new Exception("아이디 또는 비밀번호가 일치하지 않습니다.");
        }
        
        // 로그인 성공 - 실패 횟수 초기화 및 마지막 로그인 시간 업데이트
        $users_data[$id]['login_attempts'] = 0;
        $users_data[$id]['last_login'] = date('Y-m-d H:i:s');
        file_put_contents($users_file, json_encode($users_data, JSON_PRETTY_PRINT));
        
        // 세션 설정
        $_SESSION['id'] = $id;
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['logged_in'] = true;
        
    } else {
        // 프로덕션 환경: 데이터베이스 기반 인증
        require_once '../includes/Database.php';
        $db = Database::getInstance();
        
        // SQL 인젝션 방지를 위한 Prepared Statement 사용
        $sql = "SELECT id, password, name, role, email, login_attempts, last_attempt FROM myUser WHERE id = ? AND status = 'active'";
        $result = $db->query($sql, [$id])->fetch();
        
        if (!$result) {
            throw new Exception("아이디 또는 비밀번호가 일치하지 않습니다.");
        }
        
        // 계정 잠금 확인 (5회 실패 시 30분 잠금)
        $login_attempts = $result['login_attempts'] ?? 0;
        $last_attempt = $result['last_attempt'] ?? null;
        
        if ($login_attempts >= 5) {
            $lock_time = strtotime($last_attempt) + (30 * 60); // 30분
            if (time() < $lock_time) {
                $remaining_time = ceil(($lock_time - time()) / 60);
                throw new Exception("계정이 잠겨있습니다. {$remaining_time}분 후 다시 시도해주세요.");
            }
        }
        
        // 비밀번호 확인
        $password_verified = false;
        
        // 해시된 비밀번호 확인 (새로운 방식)
        if (password_verify($password, $result['password'])) {
            $password_verified = true;
        }
        // 기존 평문 비밀번호와의 호환성 (임시)
        elseif ($result['password'] === $password) {
            $password_verified = true;
            
            // 평문 비밀번호를 해시로 업데이트
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update_sql = "UPDATE myUser SET password = ? WHERE id = ?";
            $db->query($update_sql, [$hashed_password, $id]);
        }
        
        if (!$password_verified) {
            // 로그인 실패 횟수 증가
            $attempts = $login_attempts + 1;
            $update_sql = "UPDATE myUser SET login_attempts = ?, last_attempt = NOW() WHERE id = ?";
            $db->query($update_sql, [$attempts, $id]);
            
            throw new Exception("아이디 또는 비밀번호가 일치하지 않습니다.");
        }
        
        // 로그인 성공 - 실패 횟수 초기화
        $update_sql = "UPDATE myUser SET login_attempts = 0, last_login = NOW() WHERE id = ?";
        $db->query($update_sql, [$id]);
        
        // 세션 설정
        $_SESSION['id'] = $id;
        $_SESSION['name'] = $result['name'];
        $_SESSION['role'] = $result['role'];
        $_SESSION['email'] = $result['email'];
        $_SESSION['logged_in'] = true;
    }
    
    // 로그인 성공 로그
    error_log("Login successful for user: {$id} from IP: {$_SERVER['REMOTE_ADDR']}");
    error_log("Session data after login: " . print_r($_SESSION, true));
    
    // 성공 페이지로 리다이렉트
    header("Location: ../../index.php");
    exit;
    
} catch (Exception $e) {
    // 로그인 실패 로그
    $error_msg = $e->getMessage();
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    error_log("Login failed: {$error_msg} | IP: {$ip} | User Agent: {$user_agent}");
    
    // 에러 메시지를 세션에 저장하고 로그인 페이지로 리다이렉트
    $_SESSION['login_error'] = $error_msg;
    header("Location: login.php");
    exit;
} catch (Error $e) {
    // PHP 오류 처리
    $error_msg = $e->getMessage();
    error_log("Login PHP error: {$error_msg}");
    
    $_SESSION['login_error'] = "시스템 오류가 발생했습니다: " . $error_msg;
    header("Location: login.php");
    exit;
}
?>
