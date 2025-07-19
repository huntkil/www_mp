<?php

require_once 'system/includes/config.php';

use System\Includes\Router;
use System\Controllers\AuthController;

// 인증 라우터 생성
$router = new Router();

// 인증 관련 라우트
$router->group('/auth', function($router) {
    // 로그인
    $router->get('/login', [AuthController::class, 'showLogin']);
    $router->post('/login', [AuthController::class, 'login']);
    
    // 회원가입
    $router->get('/register', [AuthController::class, 'showRegister']);
    $router->post('/register', [AuthController::class, 'register']);
    
    // 로그아웃
    $router->get('/logout', [AuthController::class, 'logout']);
    
    // 비밀번호 변경
    $router->get('/change-password', [AuthController::class, 'showChangePassword']);
    $router->post('/change-password', [AuthController::class, 'changePassword']);
    
    // 프로필
    $router->get('/profile', [AuthController::class, 'showProfile']);
    $router->post('/profile', [AuthController::class, 'updateProfile']);
});

// 라우터 실행
$router->dispatch(); 