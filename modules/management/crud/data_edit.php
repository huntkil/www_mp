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
    
    if ($result['success']) {
        Utils::setFlashMessage('success', $result['message']);
        Utils::redirect('data_list.php');
    } else {
        $errors = $result['errors'] ?? [];
        $data = $result['data'] ?? $data;
    }
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="space-y-8">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl sm:text-3xl font-bold">Edit Record</h1>
            <a href="data_list.php" 
               class="inline-flex items-center justify-center rounded-md border border-border bg-background px-4 py-2 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Back to List
            </a>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="p-4 rounded-lg bg-red-100 text-red-800 border border-red-200">
                <ul class="list-disc list-inside space-y-1">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="bg-card text-card-foreground rounded-lg border shadow-sm">
            <div class="p-6">
                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="name" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Name</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($data['name']); ?>" 
                                   class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" required>
                        </div>

                        <div class="space-y-2">
                            <label for="email" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($data['email']); ?>" 
                                   class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" required>
                        </div>

                        <div class="space-y-2">
                            <label for="phone" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Phone</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($data['phone']); ?>" 
                                   class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" required>
                        </div>

                        <div class="space-y-2">
                            <label for="age" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Age</label>
                            <input type="number" id="age" name="age" value="<?php echo htmlspecialchars($data['age']); ?>" 
                                   class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                        </div>

                        <div class="space-y-2">
                            <label for="birthday" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Birthday</label>
                            <input type="date" id="birthday" name="birthday" value="<?php echo htmlspecialchars($data['birthday']); ?>" 
                                   class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                        </div>

                        <div class="space-y-2">
                            <label for="height" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Height (cm)</label>
                            <input type="number" id="height" name="height" value="<?php echo htmlspecialchars($data['height']); ?>" 
                                   class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                        </div>

                        <div class="space-y-2">
                            <label for="weight" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Weight (kg)</label>
                            <input type="number" id="weight" name="weight" value="<?php echo htmlspecialchars($data['weight']); ?>" 
                                   class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                        </div>
                    </div>

                    <div class="flex justify-end space-x-4 pt-6 border-t">
                        <a href="data_list.php" 
                           class="inline-flex items-center justify-center rounded-md border border-border bg-background px-4 py-2 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground ring-offset-background transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Update Record
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require "../../../system/includes/footer.php"; ?> 