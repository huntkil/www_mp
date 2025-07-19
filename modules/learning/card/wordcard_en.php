<?php
session_start();
$pageTitle = "Word Card (EN)";
require "../../../system/includes/header.php";
require_once 'components/WordCard.php';

$wordCard = new WordCard('en');
echo $wordCard->render();

require "../../../system/includes/footer.php";
?> 