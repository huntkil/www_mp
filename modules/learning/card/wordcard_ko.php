<?php
session_start();
$pageTitle = "단어 카드 (KR)";
require "../../../system/includes/header.php";
require_once 'components/WordCard.php';

$wordCard = new WordCard('ko');
echo $wordCard->render();

require "../../../system/includes/footer.php";
?> 