<?php
require_once '../../system/includes/config.php';
$page_title = "Learning Modules";
require_once '../../system/includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold mb-4">Learning Modules</h1>
        <p class="text-muted-foreground text-lg">Interactive learning tools and vocabulary management</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Card Slideshow -->
        <a href="card/slideshow.php" class="group bg-card border rounded-lg p-6 hover:shadow-lg transition-all duration-200 hover:scale-105">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-blue-500/10 rounded-lg flex items-center justify-center group-hover:bg-blue-500/20 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blue-500">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <path d="M21 15l-5-5L5 21"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-semibold group-hover:text-blue-600 transition-colors">Card Slideshow</h3>
                    <p class="text-muted-foreground">Interactive card-based learning</p>
                </div>
            </div>
            <p class="text-sm text-muted-foreground">Learn with interactive card slideshows featuring images and text.</p>
        </a>

        <!-- Word Cards (EN) -->
        <a href="card/wordcard_en.php" class="group bg-card border rounded-lg p-6 hover:shadow-lg transition-all duration-200 hover:scale-105">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-green-500/10 rounded-lg flex items-center justify-center group-hover:bg-green-500/20 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-green-500">
                        <path d="M2 19V6a2 2 0 0 1 2-2h7v17H4a2 2 0 0 1-2-2Zm18 2h-7V4h7a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2Z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-semibold group-hover:text-green-600 transition-colors">Word Cards (EN)</h3>
                    <p class="text-muted-foreground">English word cards</p>
                </div>
            </div>
            <p class="text-sm text-muted-foreground">Practice English vocabulary with interactive word cards.</p>
        </a>

        <!-- Word Cards (KR) -->
        <a href="card/wordcard_ko.php" class="group bg-card border rounded-lg p-6 hover:shadow-lg transition-all duration-200 hover:scale-105">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-orange-500/10 rounded-lg flex items-center justify-center group-hover:bg-orange-500/20 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-orange-500">
                        <path d="M2 19V6a2 2 0 0 1 2-2h7v17H4a2 2 0 0 1-2-2Zm18 2h-7V4h7a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2Z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-semibold group-hover:text-orange-600 transition-colors">Word Cards (KR)</h3>
                    <p class="text-muted-foreground">Korean word cards</p>
                </div>
            </div>
            <p class="text-sm text-muted-foreground">Practice Korean vocabulary with interactive word cards.</p>
        </a>

        <!-- Vocabulary Manager -->
        <a href="voca/voca.php" class="group bg-card border rounded-lg p-6 hover:shadow-lg transition-all duration-200 hover:scale-105">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-purple-500/10 rounded-lg flex items-center justify-center group-hover:bg-purple-500/20 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-purple-500">
                        <path d="M9 12l2 2 4-4"/>
                        <path d="M21 12c-1 0-4-1-4-4s3-4 4-4"/>
                        <path d="M3 12c1 0 4-1 4-4s-3-4-4-4"/>
                        <path d="M12 21c0-1-1-4-4-4s-4-3-4-4"/>
                        <path d="M12 3c0 1 1 4 4 4s4 3 4 4"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-semibold group-hover:text-purple-600 transition-colors">Vocabulary Manager</h3>
                    <p class="text-muted-foreground">Manage your vocabulary</p>
                </div>
            </div>
            <p class="text-sm text-muted-foreground">Create, edit, and organize your personal vocabulary collection.</p>
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