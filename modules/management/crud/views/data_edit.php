<?php
require "../../../system/includes/config.php";
require "../../../system/includes/components/Layout.php";
require "../../../system/includes/components/Form.php";
require_once __DIR__ . '/../controllers/MyInfoController.php';

$controller = new MyInfoController();
$result = $controller->edit($_GET['id'] ?? null);

if (!$result['success']) {
    $_SESSION['flash_message'] = $result['message'];
    $_SESSION['flash_type'] = 'error';
    header('Location: data_list.php');
    exit;
}

$form = new Form();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Record</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-background min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <?php
        echo Layout::header('Edit Record', 'data_list.php');
        
        if (isset($_SESSION['flash_message'])) {
            echo Layout::alert($_SESSION['flash_type'], $_SESSION['flash_message']);
            unset($_SESSION['flash_message']);
            unset($_SESSION['flash_type']);
        }
        
        echo Layout::card(
            $form->open('data_edit.php?id=' . $result['data']['no'], 'POST') .
            $form->input('name', 'Name', 'text', [
                'value' => $result['data']['name'],
                'required' => true
            ]) .
            $form->input('age', 'Age', 'number', [
                'value' => $result['data']['age']
            ]) .
            $form->input('birthday', 'Birthday', 'date', [
                'value' => $result['data']['birthday']
            ]) .
            $form->input('height', 'Height (cm)', 'number', [
                'value' => $result['data']['height'],
                'step' => '0.01'
            ]) .
            $form->input('weight', 'Weight (kg)', 'number', [
                'value' => $result['data']['weight'],
                'step' => '0.01'
            ]) .
            $form->submit('Update') .
            $form->cancel('data_list.php') .
            $form->close()
        );
        ?>
    </div>
</body>
</html> 