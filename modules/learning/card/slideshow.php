<?php
session_start();
$pageTitle = "Image Slideshow";
require "../../../system/includes/header.php";
require_once 'components/Slideshow.php';

$slideshow = new Slideshow();
echo $slideshow->render();

require "../../../system/includes/footer.php";
?> 