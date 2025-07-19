<?php
require "../../../system/includes/config.php";
require "../../../system/includes/components/Layout.php";
require_once __DIR__ . '/../controllers/MyInfoController.php';

$controller = new MyInfoController();
$result = $controller->index();

$headers = ['No', 'Name', 'Age', 'Birthday', 'Height', 'Weight'];
$actions = [
    [
        'url' => 'data_edit.php?id=:no',
        'text' => 'Edit',
        'class' => 'btn btn-sm btn-outline'
    ],
    [
        'url' => 'data_delete.php?id=:no',
        'text' => 'Delete',
        'class' => 'btn btn-sm btn-error'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data List</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-background min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <?php
        echo Layout::header('Data List', null);
        
        if (isset($_SESSION['flash_message'])) {
            echo Layout::alert($_SESSION['flash_type'], $_SESSION['flash_message']);
            unset($_SESSION['flash_message']);
            unset($_SESSION['flash_type']);
        }
        
        echo "<div class='mb-4'>";
        echo "<a href='data_create.php' class='btn btn-primary'>Create New Record</a>";
        echo "</div>";
        
        echo Layout::card(
            Layout::table($headers, $result['data'], $actions)
        );
        
        if ($result['total_pages'] > 1) {
            echo Layout::pagination($result['current_page'], $result['total_pages'], 'data_list.php');
        }
        ?>
    </div>
</body>
</html> 