<?php
session_start();
$page_title = "Data List";

// Load production config if exists, otherwise development
$config_prod = __DIR__ . '/../../../system/includes/config_production.php';
$config_dev = __DIR__ . '/../../../system/includes/config.php';

if (file_exists($config_prod)) {
    require_once $config_prod;
} else {
    require_once $config_dev;
}

require "../../../system/includes/header.php";
require_once 'controllers/MyInfoController.php';

$controller = new MyInfoController();
$result = $controller->index();

if (!$result['success']) {
    $_SESSION['flash_message'] = 'Failed to load data';
    $_SESSION['flash_type'] = 'error';
    header('Location: index.php');
    exit;
}

$items = $result['data'];
$current_page = $result['current_page'];
$total_pages = $result['total_pages'];
?>

<div class="container mx-auto px-4 py-8">
    <div class="space-y-8">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl sm:text-3xl font-bold">Data List</h1>
            <a href="data_create.php" 
               class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground ring-offset-background transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                Add New
            </a>
        </div>

        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="p-4 rounded-lg <?php echo $_SESSION['flash_type'] === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200'; ?>">
                <?php echo htmlspecialchars($_SESSION['flash_message']); ?>
            </div>
            <?php 
            unset($_SESSION['flash_message']);
            unset($_SESSION['flash_type']);
            ?>
        <?php endif; ?>

        <div class="bg-card text-card-foreground rounded-lg border shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b bg-muted/50">
                            <th class="px-4 py-3 text-left font-medium">No</th>
                            <th class="px-4 py-3 text-left font-medium">Name</th>
                            <th class="px-4 py-3 text-left font-medium">Age</th>
                            <th class="px-4 py-3 text-left font-medium">Birthday</th>
                            <th class="px-4 py-3 text-left font-medium">Height</th>
                            <th class="px-4 py-3 text-left font-medium">Weight</th>
                            <th class="px-4 py-3 text-left font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="7" class="px-4 py-3 text-center text-muted-foreground">
                                    No records found
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($items as $item): ?>
                                <tr class="border-b hover:bg-muted/50">
                                    <td class="px-4 py-3"><?php echo htmlspecialchars($item['no']); ?></td>
                                    <td class="px-4 py-3"><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td class="px-4 py-3"><?php echo htmlspecialchars($item['age']); ?></td>
                                    <td class="px-4 py-3"><?php echo htmlspecialchars($item['birthday']); ?></td>
                                    <td class="px-4 py-3"><?php echo htmlspecialchars($item['height']); ?></td>
                                    <td class="px-4 py-3"><?php echo htmlspecialchars($item['weight']); ?></td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <a href="data_edit.php?id=<?php echo $item['no']; ?>" 
                                               class="text-primary hover:text-primary/90">
                                                Edit
                                            </a>
                                            <a href="data_delete.php?id=<?php echo $item['no']; ?>" 
                                               class="text-destructive hover:text-destructive/90"
                                               onclick="return confirm('Are you sure you want to delete this item?');">
                                                Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="flex justify-center gap-2">
                <?php if ($current_page > 1): ?>
                    <a href="?page=<?php echo $current_page - 1; ?>" 
                       class="px-4 py-2 border border-border rounded-lg hover:bg-accent transition-colors">
                        Previous
                    </a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" 
                       class="px-4 py-2 rounded-lg <?php echo $i === $current_page ? 'bg-primary text-primary-foreground' : 'border border-border hover:bg-accent'; ?> transition-colors">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($current_page < $total_pages): ?>
                    <a href="?page=<?php echo $current_page + 1; ?>" 
                       class="px-4 py-2 border border-border rounded-lg hover:bg-accent transition-colors">
                        Next
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require "../../../system/includes/footer.php"; ?>