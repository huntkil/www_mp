<?php
session_start();
$pageTitle = "Image Slideshow";
require "../../../system/includes/header.php";
require_once 'components/Slideshow.php';

// 더 많은 이미지를 사용하도록 설정
$slideshow = new Slideshow(false); // false = 로컬 이미지 + 추가 이미지 사용
echo $slideshow->render();

require "../../../system/includes/footer.php";
?> 