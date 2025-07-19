<?php
session_start();
require "../../../system/includes/config.php";
require_once 'controllers/MyInfoController.php';

$controller = new MyInfoController();
$errors = [];
$data = [];

// Handle POST request before any output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = Utils::sanitize($_POST);
    $result = $controller->create($data);
    
    if ($result['success']) {
        // Redirect on success
        header('Location: data_list.php');
        exit;
    } else {
        $errors = $result['errors'] ?? [$result['message'] ?? 'An error occurred'];
        $data = $result['data'] ?? $data;
    }
}

$page_title = "Create New Record";
require "../../../system/includes/header.php";
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto space-y-8">
        <!-- Back to Home Link -->
        <div class="flex justify-start">
            <a href="/mp/" class="inline-flex items-center gap-2 px-4 py-2 bg-secondary text-secondary-foreground rounded-lg hover:bg-secondary/90 transition-colors font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m12 19-7-7 7-7"/>
                    <path d="M19 12H5"/>
                </svg>
                Back to Home
            </a>
        </div>

        <!-- Page Header -->
        <div class="flex justify-between items-center">
            <div class="space-y-2">
                <h1 class="text-3xl font-bold">Create New Record</h1>
                <p class="text-muted-foreground">Add a new record to the database</p>
            </div>
            <a href="data_list.php" class="inline-flex items-center gap-2 px-4 py-2 bg-secondary text-secondary-foreground rounded-lg hover:bg-secondary/90 transition-colors font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 14l6-6-6-6"/>
                </svg>
                Back to List
            </a>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="bg-card text-card-foreground rounded-lg border p-6">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-10 h-10 bg-red-500/10 rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-red-500">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" x2="12" y1="8" y2="12"/>
                            <line x1="12" x2="12.01" y1="16" y2="16"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-lg mb-2">Please fix the following errors:</h3>
                        <ul class="space-y-1 text-muted-foreground">
                            <?php foreach ($errors as $error): ?>
                                <li class="flex items-center gap-2">
                                    <span class="w-1 h-1 bg-red-500 rounded-full"></span>
                                    <?php echo htmlspecialchars($error); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Form Card -->
        <div class="bg-card text-card-foreground rounded-lg border p-6 space-y-6">
            <h2 class="text-xl font-semibold">Record Information</h2>
            
            <form method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label for="name" class="text-sm font-medium">Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($data['name'] ?? ''); ?>" 
                               class="w-full px-3 py-2 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-ring" required>
                    </div>

                    <div class="space-y-2">
                        <label for="email" class="text-sm font-medium">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($data['email'] ?? ''); ?>" 
                               class="w-full px-3 py-2 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-ring" required>
                    </div>

                    <div class="space-y-2">
                        <label for="phone" class="text-sm font-medium">Phone</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($data['phone'] ?? ''); ?>" 
                               class="w-full px-3 py-2 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-ring" required>
                    </div>

                    <div class="space-y-2">
                        <label for="age" class="text-sm font-medium">Age</label>
                        <input type="number" id="age" name="age" value="<?php echo htmlspecialchars($data['age'] ?? ''); ?>" 
                               class="w-full px-3 py-2 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                    </div>

                    <div class="space-y-2">
                        <label for="birthday" class="text-sm font-medium">Birthday</label>
                        <input type="date" id="birthday" name="birthday" value="<?php echo htmlspecialchars($data['birthday'] ?? ''); ?>" 
                               class="w-full px-3 py-2 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                    </div>

                    <div class="space-y-2">
                        <label for="height" class="text-sm font-medium">Height (cm)</label>
                        <input type="number" id="height" name="height" value="<?php echo htmlspecialchars($data['height'] ?? ''); ?>" 
                               class="w-full px-3 py-2 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                    </div>

                    <div class="space-y-2">
                        <label for="weight" class="text-sm font-medium">Weight (kg)</label>
                        <input type="number" id="weight" name="weight" value="<?php echo htmlspecialchars($data['weight'] ?? ''); ?>" 
                               class="w-full px-3 py-2 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                    </div>
                </div>

                <div class="flex justify-end gap-4 pt-6 border-t">
                    <a href="data_list.php" class="px-6 py-2 bg-secondary text-secondary-foreground rounded-lg hover:bg-secondary/90 transition-colors font-medium">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M5 12h14"/>
                            <path d="M12 5v14"/>
                        </svg>
                        Create Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require "../../../system/includes/footer.php"; ?> 