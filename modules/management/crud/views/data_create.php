<?php
require "../../../system/includes/config.php";
require "../../../system/includes/components/Layout.php";
require "../../../system/includes/components/Form.php";
require_once __DIR__ . '/../controllers/MyInfoController.php';

$controller = new MyInfoController();
$result = $controller->create();

$form = new Form();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Record</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-background min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <?php
        echo Layout::header('Create New Record', 'data_list.php');
        
        if (isset($_SESSION['flash_message'])) {
            echo Layout::alert($_SESSION['flash_type'], $_SESSION['flash_message']);
            unset($_SESSION['flash_message']);
            unset($_SESSION['flash_type']);
        }
        
        echo Layout::card(
            $form->open('data_create.php', 'POST') .
            $form->input('name', 'Name', 'text', ['required' => true]) .
            $form->input('age', 'Age', 'number') .
            $form->input('birthday', 'Birthday', 'date') .
            $form->input('height', 'Height (cm)', 'number', ['step' => '0.01']) .
            $form->input('weight', 'Weight (kg)', 'number', ['step' => '0.01']) .
            $form->submit('Create') .
            $form->cancel('data_list.php') .
            $form->close()
        );
        ?>
    </div>
</body>
</html> 