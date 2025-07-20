<?php
session_start();
$pageTitle = "Image Slideshow";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!-- Debug: Starting slideshow.php -->";

require "../../../system/includes/header.php";
echo "<!-- Debug: Header included -->";

require_once 'components/Slideshow.php';
echo "<!-- Debug: Slideshow component loaded -->";

// Check if images directory exists
$imageDir = __DIR__ . '/images/';
echo "<!-- Debug: Image directory: " . $imageDir . " -->";
echo "<!-- Debug: Image directory exists: " . (is_dir($imageDir) ? 'Yes' : 'No') . " -->";

if (is_dir($imageDir)) {
    $files = scandir($imageDir);
    $imageFiles = array_filter($files, function($file) {
        return in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif']);
    });
    echo "<!-- Debug: Found " . count($imageFiles) . " image files -->";
}

// 더 많은 이미지를 사용하도록 설정
$slideshow = new Slideshow(true); // true = 외부 이미지도 포함
echo "<!-- Debug: Slideshow object created -->";

$renderedContent = $slideshow->render();
echo "<!-- Debug: Slideshow rendered -->";

echo $renderedContent;

echo "<!-- Debug: About to include footer -->";
require "../../../system/includes/footer.php";
echo "<!-- Debug: Footer included -->";
?> 