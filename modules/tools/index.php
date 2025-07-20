<?php
require_once '../../system/includes/config.php';
$page_title = "Tools";
require_once '../../system/includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold mb-4">Tools</h1>
        <p class="text-muted-foreground text-lg">Utility tools and helpful applications</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- News Search -->
        <a href="news/search_news_form.php" class="group bg-card border rounded-lg p-6 hover:shadow-lg transition-all duration-200 hover:scale-105">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-blue-500/10 rounded-lg flex items-center justify-center group-hover:bg-blue-500/20 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blue-500">
                        <path d="M21 21l-4.35-4.35"/>
                        <circle cx="11" cy="11" r="8"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-semibold group-hover:text-blue-600 transition-colors">News Search</h3>
                    <p class="text-muted-foreground">Search and browse news</p>
                </div>
            </div>
            <p class="text-sm text-muted-foreground">Search for the latest news articles from various sources.</p>
        </a>

        <!-- Family Tour -->
        <a href="tour/familytour.php" class="group bg-card border rounded-lg p-6 hover:shadow-lg transition-all duration-200 hover:scale-105">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-green-500/10 rounded-lg flex items-center justify-center group-hover:bg-green-500/20 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-green-500">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9,22 9,12 15,12 15,22"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-semibold group-hover:text-green-600 transition-colors">Family Tour</h3>
                    <p class="text-muted-foreground">Tour planning tool</p>
                </div>
            </div>
            <p class="text-sm text-muted-foreground">Plan and organize family tours with interactive maps.</p>
        </a>

        <!-- Box Breathing -->
        <a href="box/boxbreathe.php" class="group bg-card border rounded-lg p-6 hover:shadow-lg transition-all duration-200 hover:scale-105">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-purple-500/10 rounded-lg flex items-center justify-center group-hover:bg-purple-500/20 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-purple-500">
                        <path d="M12 2v20M2 12h20"/>
                        <circle cx="12" cy="12" r="10"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-semibold group-hover:text-purple-600 transition-colors">Box Breathing</h3>
                    <p class="text-muted-foreground">Breathing exercise tool</p>
                </div>
            </div>
            <p class="text-sm text-muted-foreground">Practice box breathing technique for relaxation and focus.</p>
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