<?php

namespace System\Includes\Middleware;

use System\Includes\Auth;

/**
 * 인증 미들웨어
 * 로그인이 필요한 페이지를 보호합니다.
 */
class AuthMiddleware
{
    private Auth $auth;

    public function __construct()
    {
        $this->auth = new Auth();
    }

    /**
     * 미들웨어 실행
     * 
     * @param callable $next 다음 미들웨어 또는 컨트롤러
     * @return mixed
     */
    public function handle(callable $next)
    {
        // 로그인 상태 확인
        if (!$this->auth->isLoggedIn()) {
            // AJAX 요청인 경우 JSON 응답
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => '로그인이 필요합니다.',
                    'redirect' => '/auth/login'
                ]);
                return;
            }

            // 일반 요청인 경우 로그인 페이지로 리다이렉트
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: /auth/login');
            exit;
        }

        // 사용자 상태 확인 (활성화된 사용자만)
        $user = $this->auth->getCurrentUser();
        if ($user && $user['status'] !== 'active') {
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => '계정이 비활성화되었습니다. 관리자에게 문의하세요.'
                ]);
                return;
            }

            $this->auth->logout();
            header('Location: /auth/login?error=account_disabled');
            exit;
        }

        // 다음 미들웨어 또는 컨트롤러 실행
        return $next();
    }

    /**
     * AJAX 요청인지 확인
     * 
     * @return bool
     */
    private function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}

/**
 * 역할 기반 접근 제어 미들웨어
 */
class RoleMiddleware
{
    private Auth $auth;
    private array $allowedRoles;

    public function __construct(array $allowedRoles)
    {
        $this->auth = new Auth();
        $this->allowedRoles = $allowedRoles;
    }

    /**
     * 미들웨어 실행
     * 
     * @param callable $next 다음 미들웨어 또는 컨트롤러
     * @return mixed
     */
    public function handle(callable $next)
    {
        // 먼저 로그인 확인
        if (!$this->auth->isLoggedIn()) {
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => '로그인이 필요합니다.',
                    'redirect' => '/auth/login'
                ]);
                return;
            }

            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: /auth/login');
            exit;
        }

        // 역할 확인
        $user = $this->auth->getCurrentUser();
        if (!$user || !in_array($user['role'], $this->allowedRoles)) {
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => '접근 권한이 없습니다.'
                ]);
                return;
            }

            header('Location: /error/403');
            exit;
        }

        return $next();
    }

    /**
     * AJAX 요청인지 확인
     * 
     * @return bool
     */
    private function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}

/**
 * 게스트 전용 미들웨어
 * 로그인한 사용자는 접근할 수 없습니다.
 */
class GuestMiddleware
{
    private Auth $auth;

    public function __construct()
    {
        $this->auth = new Auth();
    }

    /**
     * 미들웨어 실행
     * 
     * @param callable $next 다음 미들웨어 또는 컨트롤러
     * @return mixed
     */
    public function handle(callable $next)
    {
        // 이미 로그인된 경우 홈으로 리다이렉트
        if ($this->auth->isLoggedIn()) {
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => '이미 로그인되어 있습니다.',
                    'redirect' => '/'
                ]);
                return;
            }

            header('Location: /');
            exit;
        }

        return $next();
    }

    /**
     * AJAX 요청인지 확인
     * 
     * @return bool
     */
    private function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
} 