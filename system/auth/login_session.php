<?php
session_start(); // 세션 시작
?>

<!DOCTYPE html>
<html>
<head>
    <title>세션 유지</title>
</head>
<body>
    <h1>세션 유지 페이지</h1>

    <?php
    if(!isset($_SESSION['id'])){
        echo "로그인 후 이용하세요.";
        header("Refresh: 2; url=../index.html");
        exit;
    }
    else{
    ?>
        <p>당신의 세션은 <?php echo $_SESSION['id'];?>로 유지됩니다.</p>
        <a href="logout.php">로그아웃</a>
    <?php
    }  
    ?>
</body>
</html>