<?php

namespace System\Controllers;

use System\Includes\Controller;
use System\Includes\Auth;
use System\Includes\Validator;
use System\Includes\Logger;

/**
 * 인증 관련 컨트롤러
 */
class AuthController extends Controller
{
    private Auth $auth;
    private Logger $logger;

    public function __construct()
    {
        parent::__construct();
        $this->auth = new Auth();
        $this->logger = new Logger('auth');
    }

    /**
     * 로그인 페이지 표시
     */
    public function showLogin(): void
    {
        // 이미 로그인된 경우 홈으로 리다이렉트
        if ($this->auth->isLoggedIn()) {
            $this->redirect('/');
            return;
        }

        $this->render('auth/login');
    }

    /**
     * 로그인 처리
     */
    public function login(): void
    {
        try {
            // CSRF 토큰 검증
            if (!$this->validateCSRF()) {
                $this->jsonResponse(['success' => false, 'message' => '보안 토큰이 유효하지 않습니다.']);
                return;
            }

            // 입력 데이터 검증
            $validator = new Validator();
            $data = $validator->validate($_POST, [
                'username' => 'required|min:3|max:50',
                'password' => 'required|min:6',
                'remember_me' => 'boolean'
            ]);

            if (!$data) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => '입력 데이터가 유효하지 않습니다.',
                    'errors' => $validator->getErrors()
                ]);
                return;
            }

            // 로그인 시도
            $result = $this->auth->login(
                $data['username'],
                $data['password'],
                $data['remember_me'] ?? false
            );

            if ($result['success']) {
                $this->logger->info('User logged in successfully', [
                    'username' => $data['username'],
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]);

                $this->jsonResponse([
                    'success' => true,
                    'message' => '로그인되었습니다.',
                    'redirect' => $_SESSION['redirect_after_login'] ?? '/'
                ]);

                // 리다이렉트 URL 초기화
                unset($_SESSION['redirect_after_login']);
            } else {
                $this->logger->warning('Login failed', [
                    'username' => $data['username'],
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    'reason' => $result['message']
                ]);

                $this->jsonResponse([
                    'success' => false,
                    'message' => $result['message']
                ]);
            }
        } catch (\Exception $e) {
            $this->logger->error('Login error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->jsonResponse([
                'success' => false,
                'message' => '로그인 처리 중 오류가 발생했습니다.'
            ]);
        }
    }

    /**
     * 회원가입 페이지 표시
     */
    public function showRegister(): void
    {
        // 이미 로그인된 경우 홈으로 리다이렉트
        if ($this->auth->isLoggedIn()) {
            $this->redirect('/');
            return;
        }

        $this->render('auth/register');
    }

    /**
     * 회원가입 처리
     */
    public function register(): void
    {
        try {
            // CSRF 토큰 검증
            if (!$this->validateCSRF()) {
                $this->jsonResponse(['success' => false, 'message' => '보안 토큰이 유효하지 않습니다.']);
                return;
            }

            // 입력 데이터 검증
            $validator = new Validator();
            $data = $validator->validate($_POST, [
                'username' => 'required|min:3|max:50|alpha_dash',
                'email' => 'required|email|max:100',
                'password' => 'required|min:8|max:255',
                'confirm_password' => 'required|same:password',
                'full_name' => 'optional|max:100',
                'agree_terms' => 'required|boolean'
            ]);

            if (!$data) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => '입력 데이터가 유효하지 않습니다.',
                    'errors' => $validator->getErrors()
                ]);
                return;
            }

            // 이용약관 동의 확인
            if (!$data['agree_terms']) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => '이용약관에 동의해야 합니다.'
                ]);
                return;
            }

            // 회원가입 처리
            $result = $this->auth->register([
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $data['password'],
                'full_name' => $data['full_name'] ?? null
            ]);

            if ($result['success']) {
                $this->logger->info('User registered successfully', [
                    'username' => $data['username'],
                    'email' => $data['email'],
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]);

                $this->jsonResponse([
                    'success' => true,
                    'message' => '회원가입이 완료되었습니다. 로그인해주세요.',
                    'redirect' => '/auth/login?registered=1'
                ]);
            } else {
                $this->logger->warning('Registration failed', [
                    'username' => $data['username'],
                    'email' => $data['email'],
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    'reason' => $result['message']
                ]);

                $this->jsonResponse([
                    'success' => false,
                    'message' => $result['message'],
                    'errors' => $result['errors'] ?? null
                ]);
            }
        } catch (\Exception $e) {
            $this->logger->error('Registration error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->jsonResponse([
                'success' => false,
                'message' => '회원가입 처리 중 오류가 발생했습니다.'
            ]);
        }
    }

    /**
     * 로그아웃 처리
     */
    public function logout(): void
    {
        try {
            $user = $this->auth->getCurrentUser();
            
            $this->auth->logout();

            if ($user) {
                $this->logger->info('User logged out', [
                    'username' => $user['username'],
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]);
            }

            $this->redirect('/auth/login?logout=1');
        } catch (\Exception $e) {
            $this->logger->error('Logout error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->redirect('/auth/login');
        }
    }

    /**
     * 비밀번호 변경 페이지 표시
     */
    public function showChangePassword(): void
    {
        if (!$this->auth->isLoggedIn()) {
            $this->redirect('/auth/login');
            return;
        }

        $this->render('auth/change_password');
    }

    /**
     * 비밀번호 변경 처리
     */
    public function changePassword(): void
    {
        try {
            if (!$this->auth->isLoggedIn()) {
                $this->jsonResponse(['success' => false, 'message' => '로그인이 필요합니다.']);
                return;
            }

            // CSRF 토큰 검증
            if (!$this->validateCSRF()) {
                $this->jsonResponse(['success' => false, 'message' => '보안 토큰이 유효하지 않습니다.']);
                return;
            }

            // 입력 데이터 검증
            $validator = new Validator();
            $data = $validator->validate($_POST, [
                'current_password' => 'required',
                'new_password' => 'required|min:8|max:255',
                'confirm_password' => 'required|same:new_password'
            ]);

            if (!$data) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => '입력 데이터가 유효하지 않습니다.',
                    'errors' => $validator->getErrors()
                ]);
                return;
            }

            // 비밀번호 변경 처리
            $result = $this->auth->changePassword(
                $this->auth->getCurrentUser()['id'],
                $data['current_password'],
                $data['new_password']
            );

            if ($result['success']) {
                $this->logger->info('Password changed successfully', [
                    'user_id' => $this->auth->getCurrentUser()['id'],
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]);

                $this->jsonResponse([
                    'success' => true,
                    'message' => '비밀번호가 변경되었습니다.'
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => $result['message']
                ]);
            }
        } catch (\Exception $e) {
            $this->logger->error('Password change error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->jsonResponse([
                'success' => false,
                'message' => '비밀번호 변경 중 오류가 발생했습니다.'
            ]);
        }
    }

    /**
     * 프로필 페이지 표시
     */
    public function showProfile(): void
    {
        if (!$this->auth->isLoggedIn()) {
            $this->redirect('/auth/login');
            return;
        }

        $user = $this->auth->getCurrentUser();
        $this->render('auth/profile', ['user' => $user]);
    }

    /**
     * 프로필 업데이트 처리
     */
    public function updateProfile(): void
    {
        try {
            if (!$this->auth->isLoggedIn()) {
                $this->jsonResponse(['success' => false, 'message' => '로그인이 필요합니다.']);
                return;
            }

            // CSRF 토큰 검증
            if (!$this->validateCSRF()) {
                $this->jsonResponse(['success' => false, 'message' => '보안 토큰이 유효하지 않습니다.']);
                return;
            }

            // 입력 데이터 검증
            $validator = new Validator();
            $data = $validator->validate($_POST, [
                'email' => 'required|email|max:100',
                'full_name' => 'optional|max:100'
            ]);

            if (!$data) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => '입력 데이터가 유효하지 않습니다.',
                    'errors' => $validator->getErrors()
                ]);
                return;
            }

            // 프로필 업데이트 처리
            $result = $this->auth->updateProfile(
                $this->auth->getCurrentUser()['id'],
                $data
            );

            if ($result['success']) {
                $this->logger->info('Profile updated successfully', [
                    'user_id' => $this->auth->getCurrentUser()['id'],
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]);

                $this->jsonResponse([
                    'success' => true,
                    'message' => '프로필이 업데이트되었습니다.',
                    'user' => $result['user']
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => $result['message']
                ]);
            }
        } catch (\Exception $e) {
            $this->logger->error('Profile update error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->jsonResponse([
                'success' => false,
                'message' => '프로필 업데이트 중 오류가 발생했습니다.'
            ]);
        }
    }
} 