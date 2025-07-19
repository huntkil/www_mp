<?php
session_start();
$page_title = "Delete Health Record";

// 데이터베이스 연결은 config.php에서 이미 포함됨

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    try {
        include "../../../system/includes/config.php";
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("DELETE FROM myhealth WHERE no = ?");
        
        if ($stmt->execute([$id])) {
            header("Location: health_list.php");
            exit;
        } else {
            throw new Exception("Failed to delete record");
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    } finally {
        if (isset($stmt)) {
            $stmt->closeCursor();
        }
    }
} else {
    $error_message = "Invalid record ID";
}

include "../../../system/includes/header.php";
include "../../../system/includes/config.php";

if(!isset($_SESSION['id'])){
    echo '<div class="container mx-auto px-4 py-8">';
    echo '<div class="bg-destructive/15 text-destructive rounded-lg p-4 text-center">';
    echo "Please log in to access this page.";
    echo '</div></div>';
    echo '<script>setTimeout(function(){ window.location.href = "../../../system/auth/login.php"; }, 2000);</script>';
    echo '</body></html>';
    exit;
}

// 에러 메시지가 있으면 표시
if (isset($error_message)) {
    echo '<div class="container mx-auto px-4 py-8">';
    echo '<div class="bg-destructive/15 text-destructive rounded-lg p-4">';
    echo "Error: " . htmlspecialchars($error_message);
    echo '</div></div>';
    echo '<script>setTimeout(function(){ window.location.href = "health_list.php"; }, 2000);</script>';
    echo '</body></html>';
    exit;
}

include "../../../system/includes/footer.php";
?> 