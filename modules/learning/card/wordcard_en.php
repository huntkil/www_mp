<?php
require_once 'components/WordCard.php';

// 영어 문장을 위한 WordCard 인스턴스 생성
$wordCard = new WordCard('en');

// HTML 출력
?>
<?php
session_start();

// 페이지 제목 설정
$pageTitle = "English Word Cards";

// 헤더 포함
require "../../../system/includes/header.php";

// WordCard 컴포넌트 포함
require_once 'components/WordCard.php';

// 영어 WordCard 인스턴스 생성
$wordCard = new WordCard('en');
?>
    <?php echo $wordCard->render(); ?>
    
    <?php require_once "../../../system/includes/footer.php"; ?> 