<?php

session_start(); // 세션 시작

// 로그인 체크
if (!isset($_SESSION['id'])) {
    echo "로그인 후 이용하세요.";
    header("Refresh: 2; url=../index.html");
    exit;
}

// CSS 파일 추가
$additional_css = '<link rel="stylesheet" href="css/docs.css">';

require_once 'components/DocsViewer.php';

$docsViewer = new DocsViewer();
$docsViewer->render();