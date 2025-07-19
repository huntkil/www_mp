<?php
// 세션 시작
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Main application entry point for /mp
// 호스팅 환경에서는 production config 사용
if (file_exists(__DIR__ . '/system/includes/config_production.php')) {
    require_once __DIR__ . '/system/includes/config_production.php';
} else {
    require_once __DIR__ . '/system/includes/config.php';
}

// Include header
require_once __DIR__ . '/system/includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <!-- Hero Section -->
    <div class="text-center mb-12">
        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold mb-4">My Playground</h1>
        <p class="text-lg text-muted-foreground max-w-2xl mx-auto">
            A comprehensive PHP learning environment with tools for learning, management, and productivity
        </p>
    </div>

    <!-- Learning Section -->
    <section class="mb-12">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-8 h-8 bg-blue-500/10 rounded-lg flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="text-blue-500">
                    <path d="M2 19V6a2 2 0 0 1 2-2h7v17H4a2 2 0 0 1-2-2Zm18 2h-7V4h7a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2Z"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold">Learning</h2>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Card Slideshow -->
            <a href="modules/learning/card/slideshow.php" class="group bg-card border rounded-lg p-6 hover:shadow-lg transition-all duration-200 hover:scale-105">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-blue-500/10 rounded-lg flex items-center justify-center group-hover:bg-blue-500/20 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="text-blue-500">
                            <path d="M2 19V6a2 2 0 0 1 2-2h7v17H4a2 2 0 0 1-2-2Zm18 2h-7V4h7a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2Z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold">Card Slideshow</h3>
                </div>
                <p class="text-sm text-muted-foreground">Interactive image slideshow for learning</p>
            </a>

            <!-- Word Cards EN -->
            <a href="modules/learning/card/wordcard_en.php" class="group bg-card border rounded-lg p-6 hover:shadow-lg transition-all duration-200 hover:scale-105">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-green-500/10 rounded-lg flex items-center justify-center group-hover:bg-green-500/20 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="text-green-500">
                            <path d="M3 5h6M3 9h6M3 13h6M3 17h6M15 19l2 2 4-4M15 11l2 2 4-4M15 5l2 2 4-4"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold">Word Cards (EN)</h3>
                </div>
                <p class="text-sm text-muted-foreground">English vocabulary flashcards</p>
            </a>

            <!-- Word Cards KR -->
            <a href="modules/learning/card/wordcard_ko.php" class="group bg-card border rounded-lg p-6 hover:shadow-lg transition-all duration-200 hover:scale-105">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-purple-500/10 rounded-lg flex items-center justify-center group-hover:bg-purple-500/20 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="text-purple-500">
                            <path d="M3 5h6M3 9h6M3 13h6M3 17h6M15 19l2 2 4-4M15 11l2 2 4-4M15 5l2 2 4-4"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold">Word Cards (KR)</h3>
                </div>
                <p class="text-sm text-muted-foreground">Korean vocabulary flashcards</p>
            </a>

            <!-- Vocabulary -->
            <a href="modules/learning/voca/voca.php" class="group bg-card border rounded-lg p-6 hover:shadow-lg transition-all duration-200 hover:scale-105">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-orange-500/10 rounded-lg flex items-center justify-center group-hover:bg-orange-500/20 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="text-orange-500">
                            <path d="M3 5h6M3 9h6M3 13h6M3 17h6M15 19l2 2 4-4M15 11l2 2 4-4M15 5l2 2 4-4"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold">Vocabulary</h3>
                </div>
                <p class="text-sm text-muted-foreground">Personal vocabulary management</p>
            </a>
        </div>
    </section>

    <!-- Tools Section -->
    <section class="mb-12">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-8 h-8 bg-green-500/10 rounded-lg flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="text-green-500">
                    <path d="M14.7 6.3a5 5 0 0 0-6.6 6.6l-5.1 5.1a2 2 0 1 0 2.8 2.8l5.1-5.1a5 5 0 0 0 6.6-6.6ZM16 7l1.5-1.5M19 4l1 1"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold">Tools</h2>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- News Search -->
            <a href="modules/tools/news/search_news_form.php" class="group bg-card border rounded-lg p-6 hover:shadow-lg transition-all duration-200 hover:scale-105">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-blue-500/10 rounded-lg flex items-center justify-center group-hover:bg-blue-500/20 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="text-blue-500">
                            <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold">News Search</h3>
                </div>
                <p class="text-sm text-muted-foreground">Search latest news from around the world</p>
            </a>

            <!-- Family Tour -->
            <a href="modules/tools/tour/familytour.php" class="group bg-card border rounded-lg p-6 hover:shadow-lg transition-all duration-200 hover:scale-105">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-green-500/10 rounded-lg flex items-center justify-center group-hover:bg-green-500/20 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="text-green-500">
                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold">Family Tour</h3>
                </div>
                <p class="text-sm text-muted-foreground">4-day Gyeongju family tour plan</p>
            </a>

            <!-- Box Breathing -->
            <a href="modules/tools/box/boxbreathe.php" class="group bg-card border rounded-lg p-6 hover:shadow-lg transition-all duration-200 hover:scale-105">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-purple-500/10 rounded-lg flex items-center justify-center group-hover:bg-purple-500/20 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="text-purple-500">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 6v6l4 2"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold">Box Breathing</h3>
                </div>
                <p class="text-sm text-muted-foreground">Interactive breathing exercise trainer</p>
            </a>
        </div>
    </section>

    <!-- Management Section -->
    <section class="mb-12">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-8 h-8 bg-orange-500/10 rounded-lg flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="text-orange-500">
                    <ellipse cx="12" cy="5" rx="9" ry="3"/>
                    <path d="M3 5v14c0 1.7 4 3 9 3s9-1.3 9-3V5M3 12c0 1.7 4 3 9 3s9-1.3 9-3M3 17c0 1.7 4 3 9 3s9-1.3 9-3"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold">Management</h2>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- CRUD Demo -->
            <a href="modules/management/crud/data_list.php" class="group bg-card border rounded-lg p-6 hover:shadow-lg transition-all duration-200 hover:scale-105">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-blue-500/10 rounded-lg flex items-center justify-center group-hover:bg-blue-500/20 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="text-blue-500">
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                            <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold">CRUD Demo</h3>
                </div>
                <p class="text-sm text-muted-foreground">Create, Read, Update, Delete operations</p>
            </a>

            <!-- My Health -->
            <a href="modules/management/myhealth/health_list.php" class="group bg-card border rounded-lg p-6 hover:shadow-lg transition-all duration-200 hover:scale-105">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-green-500/10 rounded-lg flex items-center justify-center group-hover:bg-green-500/20 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="text-green-500">
                            <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold">My Health</h3>
                </div>
                <p class="text-sm text-muted-foreground">Track your daily health activities</p>
            </a>
        </div>
    </section>


    </section>
</div>

<?php require_once __DIR__ . '/system/includes/footer.php'; ?>
