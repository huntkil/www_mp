<?php
require_once 'components/WordCard.php';

// 한국어 문장을 위한 WordCard 인스턴스 생성
$wordCard = new WordCard('ko');

// HTML 출력
?>
<?php
session_start();

// 페이지 제목 설정
$pageTitle = "한국어 단어 카드";

// 헤더 포함
require "../../../system/includes/header.php";

// WordCard 컴포넌트 포함
require_once 'components/WordCard.php';

// 한국어 WordCard 인스턴스 생성
$wordCard = new WordCard('ko');
?>
    <?php echo $wordCard->render(); ?>
    
    <?php require_once "../../../system/includes/footer.php"; ?> 