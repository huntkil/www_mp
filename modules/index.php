<?php
// Load appropriate config based on environment
if (file_exists(__DIR__ . '/../system/includes/config_production.php')) {
    require_once __DIR__ . '/../system/includes/config_production.php';
} else {
    require_once __DIR__ . '/../system/includes/config.php';
}
$page_title = "Modules";
require_once '../system/includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold mb-4">My Playground Modules</h1>
        <p class="text-muted-foreground text-lg">Choose a module to explore</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Learning Modules -->
        <div class="bg-card border rounded-lg p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-blue-500/10 rounded-lg flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blue-500">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-semibold">Learning</h2>
            </div>
            <p class="text-muted-foreground mb-6">Interactive learning tools and vocabulary management</p>
            <div class="space-y-3">
                <a href="learning/card/slideshow.php" class="block p-3 rounded-lg hover:bg-accent transition-colors">
                    <div class="font-medium">Card Slideshow</div>
                    <div class="text-sm text-muted-foreground">Interactive card-based learning</div>
                </a>
                <a href="learning/card/wordcard_en.php" class="block p-3 rounded-lg hover:bg-accent transition-colors">
                    <div class="font-medium">Word Cards (EN)</div>
                    <div class="text-sm text-muted-foreground">English word cards</div>
                </a>
                <a href="learning/card/wordcard_ko.php" class="block p-3 rounded-lg hover:bg-accent transition-colors">
                    <div class="font-medium">Word Cards (KR)</div>
                    <div class="text-sm text-muted-foreground">Korean word cards</div>
                </a>
                <a href="learning/voca/voca.php" class="block p-3 rounded-lg hover:bg-accent transition-colors">
                    <div class="font-medium">Vocabulary Manager</div>
                    <div class="text-sm text-muted-foreground">Manage your vocabulary</div>
                </a>
            </div>
        </div>

        <!-- Tools Modules -->
        <div class="bg-card border rounded-lg p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-green-500/10 rounded-lg flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-green-500">
                        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-semibold">Tools</h2>
            </div>
            <p class="text-muted-foreground mb-6">Utility tools and helpful applications</p>
            <div class="space-y-3">
                <a href="tools/news/search_news_form.php" class="block p-3 rounded-lg hover:bg-accent transition-colors">
                    <div class="font-medium">News Search</div>
                    <div class="text-sm text-muted-foreground">Search and browse news</div>
                </a>
                <a href="tools/tour/familytour.php" class="block p-3 rounded-lg hover:bg-accent transition-colors">
                    <div class="font-medium">Family Tour</div>
                    <div class="text-sm text-muted-foreground">Tour planning tool</div>
                </a>
                <a href="tools/box/boxbreathe.php" class="block p-3 rounded-lg hover:bg-accent transition-colors">
                    <div class="font-medium">Box Breathing</div>
                    <div class="text-sm text-muted-foreground">Breathing exercise tool</div>
                </a>
            </div>
        </div>

        <!-- Management Modules -->
        <div class="bg-card border rounded-lg p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-purple-500/10 rounded-lg flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-purple-500">
                        <ellipse cx="12" cy="5" rx="9" ry="3"/>
                        <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/>
                        <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-semibold">Management</h2>
            </div>
            <p class="text-muted-foreground mb-6">Data management and health tracking</p>
            <div class="space-y-3">
                <a href="management/crud/data_list.php" class="block p-3 rounded-lg hover:bg-accent transition-colors">
                    <div class="font-medium">CRUD Demo</div>
                    <div class="text-sm text-muted-foreground">Data management demo</div>
                </a>
                <a href="management/myhealth/health_list.php" class="block p-3 rounded-lg hover:bg-accent transition-colors">
                    <div class="font-medium">Health Management</div>
                    <div class="text-sm text-muted-foreground">Track your health data</div>
                </a>
            </div>
        </div>
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

<?php require_once '../system/includes/footer.php'; ?> 