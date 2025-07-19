<?php
require_once '../includes/config.php';

// 로그아웃 로그
if (isset($_SESSION['id'])) {
    error_log("User logout: {$_SESSION['id']} from IP: {$_SERVER['REMOTE_ADDR']}");
}

// 모든 세션 변수 제거
$_SESSION = array();

// 세션 쿠키 삭제
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// 세션 파괴
session_destroy();

// 로그아웃 후 메인 페이지로 리다이렉트
header('Location: ../../index.php');
exit;