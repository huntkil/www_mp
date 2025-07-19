<?php
require "../../../system/includes/config.php";
require_once __DIR__ . '/controllers/MyInfoController.php';

$controller = new MyInfoController();
$controller->delete($_GET['id'] ?? null);
?> 