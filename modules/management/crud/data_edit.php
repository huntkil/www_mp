<?php
session_start();
$page_title = "Edit Record";
require "../../../system/includes/header.php";
require "../../../system/includes/config.php";
require_once 'controllers/MyInfoController.php';

$controller = new MyInfoController();
$errors = [];

// Get record ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    Utils::setFlashMessage('error', 'Invalid record ID');
    Utils::redirect('data_list.php');
}

// Get record data
$data = $controller->show($id);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postData = Utils::sanitize($_POST);
    $result = $controller->update($id, $postData);
    
    if (!$result['success']) {
        $errors = $result['errors'];
        $data = $result['data'];
    }
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Edit Record</h1>
        <a href="data_list.php" class="btn btn-outline">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            Back to List
        </a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error mb-6">
            <ul class="list-disc list-inside">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-content">
            <form method="POST" class="space-y-6">
                <div class="space-y-2">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($data['name']); ?>" class="form-input" required>
                </div>

                <div class="space-y-2">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($data['email']); ?>" class="form-input" required>
                </div>

                <div class="space-y-2">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($data['phone']); ?>" class="form-input" required>
                </div>

                <div class="space-y-2">
                    <label for="age" class="form-label">Age</label>
                    <input type="number" id="age" name="age" value="<?php echo htmlspecialchars($data['age']); ?>" class="form-input">
                </div>

                <div class="space-y-2">
                    <label for="birthday" class="form-label">Birthday</label>
                    <input type="date" id="birthday" name="birthday" value="<?php echo htmlspecialchars($data['birthday']); ?>" class="form-input">
                </div>

                <div class="space-y-2">
                    <label for="height" class="form-label">Height (cm)</label>
                    <input type="number" id="height" name="height" value="<?php echo htmlspecialchars($data['height']); ?>" class="form-input">
                </div>

                <div class="space-y-2">
                    <label for="weight" class="form-label">Weight (kg)</label>
                    <input type="number" id="weight" name="weight" value="<?php echo htmlspecialchars($data['weight']); ?>" class="form-input">
                </div>

                <div class="flex justify-end space-x-4">
                    <a href="data_list.php" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Update Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require "../../../system/includes/footer.php"; ?> 