<?php
session_start();
$page_title = "Create New Record";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!-- Debug: Starting data_create.php -->";

// Load production config if exists, otherwise development
$config_prod = __DIR__ . '/../../../system/includes/config_production.php';
$config_dev = __DIR__ . '/../../../system/includes/config.php';

if (file_exists($config_prod)) {
    require_once $config_prod;
    echo "<!-- Debug: Production config loaded -->";
} else {
    require_once $config_dev;
    echo "<!-- Debug: Development config loaded -->";
}

echo "<!-- Debug: About to include header -->";
require "../../../system/includes/header.php";
echo "<!-- Debug: Header included -->";

$errors = [];
$data = [];

// Handle POST request before any output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<!-- Debug: Processing POST request -->";
    echo "<div style='background: yellow; padding: 10px; margin: 10px;'>";
    echo "<h3>Processing Form Submission...</h3>";
    
    // Simple data sanitization
    $data = array_map('trim', $_POST);
    $data = array_map('htmlspecialchars', $data);
    
    echo "<p>Received data:</p>";
    echo "<ul>";
    foreach ($data as $key => $value) {
        echo "<li><strong>$key:</strong> $value</li>";
    }
    echo "</ul>";
    
    // Validate required fields
    $errors = [];
    if (empty($data['name'])) {
        $errors[] = 'Name is required';
    }
    if (empty($data['email'])) {
        $errors[] = 'Email is required';
    }
    if (empty($data['phone'])) {
        $errors[] = 'Phone is required';
    }
    
    if (empty($errors)) {
        echo "<p>✅ Validation passed. Attempting to save to database...</p>";
        
        try {
            // Direct database insertion
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
            
            echo "<p>✅ Database connection successful</p>";
            
            $sql = "INSERT INTO myinfo (name, email, phone, age, birthday, height, weight) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $data['name'],
                $data['email'],
                $data['phone'],
                $data['age'] ?: null,
                $data['birthday'] ?: null,
                $data['height'] ?: null,
                $data['weight'] ?: null
            ]);
            
            echo "<p>✅ Record inserted successfully!</p>";
            echo "<!-- Debug: Record created successfully -->";
            $_SESSION['flash_message'] = 'Record created successfully';
            $_SESSION['flash_type'] = 'success';
            
            // Use JavaScript redirect instead of PHP header
            echo "<script>window.location.href = 'data_list.php';</script>";
            echo "<p>Record created successfully! Redirecting...</p>";
            echo "</div>";
            exit;
            
        } catch (Exception $e) {
            echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
            echo "<!-- Debug: Error creating record: " . $e->getMessage() . " -->";
            $errors[] = 'Failed to create record: ' . $e->getMessage();
        }
    } else {
        echo "<p>❌ Validation errors:</p>";
        echo "<ul>";
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo "</ul>";
    }
    echo "</div>";
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="space-y-8">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl sm:text-3xl font-bold">Create New Record</h1>
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
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($data['name'] ?? ''); ?>" 
                                   class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" required>
                        </div>

                        <div class="space-y-2">
                            <label for="email" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($data['email'] ?? ''); ?>" 
                                   class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" required>
                        </div>

                        <div class="space-y-2">
                            <label for="phone" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Phone</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($data['phone'] ?? ''); ?>" 
                                   class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" required>
                        </div>

                        <div class="space-y-2">
                            <label for="age" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Age</label>
                            <input type="number" id="age" name="age" value="<?php echo htmlspecialchars($data['age'] ?? ''); ?>" 
                                   class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                        </div>

                        <div class="space-y-2">
                            <label for="birthday" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Birthday</label>
                            <input type="date" id="birthday" name="birthday" value="<?php echo htmlspecialchars($data['birthday'] ?? ''); ?>" 
                                   class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                        </div>

                        <div class="space-y-2">
                            <label for="height" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Height (cm)</label>
                            <input type="number" id="height" name="height" value="<?php echo htmlspecialchars($data['height'] ?? ''); ?>" 
                                   class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                        </div>

                        <div class="space-y-2">
                            <label for="weight" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Weight (kg)</label>
                            <input type="number" id="weight" name="weight" value="<?php echo htmlspecialchars($data['weight'] ?? ''); ?>" 
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
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Create Record
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require "../../../system/includes/footer.php"; ?> 