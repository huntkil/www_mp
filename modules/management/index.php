<?php
require_once '../../system/includes/config.php';
$page_title = "Management";
require_once '../../system/includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold mb-4">Management</h1>
        <p class="text-muted-foreground text-lg">Data management and health tracking</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- CRUD Demo -->
        <a href="crud/data_list.php" class="group bg-card border rounded-lg p-6 hover:shadow-lg transition-all duration-200 hover:scale-105">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-blue-500/10 rounded-lg flex items-center justify-center group-hover:bg-blue-500/20 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blue-500">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14,2 14,8 20,8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10,9 9,9 8,9"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-semibold group-hover:text-blue-600 transition-colors">CRUD Demo</h3>
                    <p class="text-muted-foreground">Data management demo</p>
                </div>
            </div>
            <p class="text-sm text-muted-foreground">Demonstrate Create, Read, Update, Delete operations with a sample database.</p>
        </a>

        <!-- Health Management -->
        <a href="myhealth/health_list.php" class="group bg-card border rounded-lg p-6 hover:shadow-lg transition-all duration-200 hover:scale-105">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-green-500/10 rounded-lg flex items-center justify-center group-hover:bg-green-500/20 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-green-500">
                        <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-semibold group-hover:text-green-600 transition-colors">Health Management</h3>
                    <p class="text-muted-foreground">Track your health data</p>
                </div>
            </div>
            <p class="text-sm text-muted-foreground">Monitor and track your health metrics and activities.</p>
        </a>
    </div>

    <!-- Back to Home -->
    <div class="text-center mt-12">
        <a href="/index.php" class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="m15 18-6-6 6-6"/>
            </svg>
            Back to Home
        </a>
    </div>
</div>

<?php require_once '../../system/includes/footer.php'; ?> 