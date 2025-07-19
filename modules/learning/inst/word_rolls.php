<?php
session_start();

// 로그인 체크
if (!isset($_SESSION['id'])) {
    echo "로그인 후 이용하세요.";
    header("Refresh: 2; url=../index.html");
    exit;
}

// CSS 파일 추가
$additional_css = '<link rel="stylesheet" href="../../../resources/css/word-rolls.css">';

// Include header
include "../../../system/includes/header.php";
?>

<main class="feed-container">
    <div id="post" class="post">Loading...</div>
    <div class="progress-bar">
        <div id="progress"></div>
    </div>
</main>

<script src="js/word-rolls.js"></script>

<?php
// Include footer
include "../../../system/includes/footer.php";
?>
