<?php
// Simple session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize navigation helper first
class NavigationHelper {
    private static $instance = null;
    private $currentPath;
    private $rootPath;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->currentPath = $_SERVER['REQUEST_URI'];
        $this->rootPath = $this->calculateRootPath();
    }
    
    private function calculateRootPath() {
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $pathInfo = pathinfo($scriptName);
        $depth = substr_count($pathInfo['dirname'], '/') - 1;
        return str_repeat('../', $depth);
    }
    
    public function getHomeUrl() {
        return $this->rootPath . 'index.php';
    }
    
    public function getModuleUrl($path) {
        return $this->rootPath . 'modules/' . $path;
    }
    
    public function getSystemUrl($path) {
        return $this->rootPath . 'system/' . $path;
    }
    
    public function renderBreadcrumb() {
        $pathParts = explode('/', trim($this->currentPath, '/'));
        $breadcrumbs = [];
        
        // Home
        $breadcrumbs[] = '<a href="' . $this->getHomeUrl() . '" class="hover:text-foreground transition-colors">Home</a>';
        
        // Add breadcrumb logic based on current path
        if (in_array('modules', $pathParts)) {
            $moduleIndex = array_search('modules', $pathParts);
            if (isset($pathParts[$moduleIndex + 1])) {
                $moduleType = $pathParts[$moduleIndex + 1];
                $moduleNames = [
                    'learning' => 'Learning',
                    'management' => 'Management', 
                    'tools' => 'Tools'
                ];
                if (isset($moduleNames[$moduleType])) {
                    $breadcrumbs[] = '<span class="breadcrumb-separator"></span>';
                    $breadcrumbs[] = '<span class="hover:text-foreground transition-colors">' . $moduleNames[$moduleType] . '</span>';
                }
                
                // Add specific module if available
                if (isset($pathParts[$moduleIndex + 2])) {
                    $specificModule = $pathParts[$moduleIndex + 2];
                    $moduleSpecificNames = [
                        'card' => 'Word Cards',
                        'voca' => 'Vocabulary',
                        'inst' => 'Word Rolls',
                        'crud' => 'CRUD Demo',
                        'myhealth' => 'Health Management',
                        'news' => 'News Search',
                        'tour' => 'Family Tour',
                        'box' => 'Box Breathing'
                    ];
                    if (isset($moduleSpecificNames[$specificModule])) {
                        $breadcrumbs[] = '<span class="breadcrumb-separator"></span>';
                        $breadcrumbs[] = '<span class="text-foreground">' . $moduleSpecificNames[$specificModule] . '</span>';
                    }
                }
            }
        }
        
        echo implode('', $breadcrumbs);
    }
}

// Initialize navigation helper
$nav = NavigationHelper::getInstance();

// Page title fallback
if (!isset($page_title)) {
    $page_title = "My Playground";
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : ''; ?>My Playground</title>
    <link href="/mp/resources/css/tailwind.output.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="/mp/favicon.ico">
    <script>
        // Development environment - CDN usage is acceptable
        // tailwind.config = { ... } 전체 삭제
    </script>
    <style>
        :root {
            --background: 0 0% 100%;
            --foreground: 222.2 84% 4.9%;
            --card: 0 0% 100%;
            --card-foreground: 222.2 84% 4.9%;
            --popover: 0 0% 100%;
            --popover-foreground: 222.2 84% 4.9%;
            --primary: 222.2 47.4% 11.2%;
            --primary-foreground: 210 40% 98%;
            --secondary: 210 40% 96%;
            --secondary-foreground: 222.2 84% 4.9%;
            --muted: 210 40% 96%;
            --muted-foreground: 215.4 16.3% 46.9%;
            --accent: 210 40% 96%;
            --accent-foreground: 222.2 84% 4.9%;
            --destructive: 0 84.2% 60.2%;
            --destructive-foreground: 210 40% 98%;
            --border: 214.3 31.8% 91.4%;
            --input: 214.3 31.8% 91.4%;
            --ring: 222.2 84% 4.9%;
        }

        .dark {
            --background: 222.2 84% 4.9%;
            --foreground: 210 40% 98%;
            --card: 222.2 84% 4.9%;
            --card-foreground: 210 40% 98%;
            --popover: 222.2 84% 4.9%;
            --popover-foreground: 210 40% 98%;
            --primary: 210 40% 98%;
            --primary-foreground: 222.2 47.4% 11.2%;
            --secondary: 217.2 32.6% 17.5%;
            --secondary-foreground: 210 40% 98%;
            --muted: 217.2 32.6% 17.5%;
            --muted-foreground: 215 20.2% 65.1%;
            --accent: 217.2 32.6% 17.5%;
            --accent-foreground: 210 40% 98%;
            --destructive: 0 62.8% 30.6%;
            --destructive-foreground: 210 40% 98%;
            --border: 217.2 32.6% 17.5%;
            --input: 217.2 32.6% 17.5%;
            --ring: 212.7 26.8% 83.9%;
        }

        /* CSS 변수를 실제 색상으로 변환 */
        .bg-background { background-color: hsl(var(--background)); }
        .text-foreground { color: hsl(var(--foreground)); }
        .bg-card { background-color: hsl(var(--card)); }
        .text-card-foreground { color: hsl(var(--card-foreground)); }
        .bg-popover { background-color: hsl(var(--popover)); }
        .text-popover-foreground { color: hsl(var(--popover-foreground)); }
        .bg-primary { background-color: hsl(var(--primary)); }
        .text-primary-foreground { color: hsl(var(--primary-foreground)); }
        .bg-secondary { background-color: hsl(var(--secondary)); }
        .text-secondary-foreground { color: hsl(var(--secondary-foreground)); }
        .bg-muted { background-color: hsl(var(--muted)); }
        .text-muted-foreground { color: hsl(var(--muted-foreground)); }
        .bg-accent { background-color: hsl(var(--accent)); }
        .text-accent-foreground { color: hsl(var(--accent-foreground)); }
        .bg-destructive { background-color: hsl(var(--destructive)); }
        .text-destructive-foreground { color: hsl(var(--destructive-foreground)); }
        .border-border { border-color: hsl(var(--border)); }
        .border-input { border-color: hsl(var(--input)); }
        .ring-ring { --tw-ring-color: hsl(var(--ring)); }

        .mobile-menu-hidden {
            transform: translateX(-100%);
        }

        .mobile-menu-visible {
            transform: translateX(0);
        }

        .breadcrumb-separator::before {
            content: "/";
            margin: 0 0.5rem;
            color: hsl(var(--muted-foreground));
        }
    </style>
    <?php if (isset($additional_css)) echo $additional_css; ?>
</head>
<body class="bg-background text-foreground">
    <nav class="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
        <div class="container mx-auto px-4">
            <div class="flex h-16 items-center justify-between">
                <!-- Logo -->
                <div class="flex items-center gap-4">
                    <a href="<?php echo $nav->getHomeUrl(); ?>" class="font-bold text-xl">
                        My Playground
                    </a>
                    
                    <!-- Breadcrumb Navigation -->
                    <nav class="hidden md:flex items-center text-sm text-muted-foreground" aria-label="breadcrumb">
                        <?php $nav->renderBreadcrumb(); ?>
                    </nav>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center gap-6">
                    <div class="flex items-center gap-1">
                        <button class="p-2 rounded-lg hover:bg-accent transition-colors" title="Learning Modules" onclick="toggleDropdown('learning-dropdown')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                            </svg>
                            <span>Learning</span>
                        </button>
                        <div id="learning-dropdown" class="absolute top-full mt-2 w-48 bg-popover border rounded-lg shadow-lg hidden">
                            <a href="<?php echo $nav->getModuleUrl('learning/card/slideshow.php'); ?>" class="block px-4 py-2 hover:bg-accent text-sm">Card Slideshow</a>
                            <a href="<?php echo $nav->getModuleUrl('learning/card/wordcard_en.php'); ?>" class="block px-4 py-2 hover:bg-accent text-sm">Word Cards (EN)</a>
                            <a href="<?php echo $nav->getModuleUrl('learning/card/wordcard_ko.php'); ?>" class="block px-4 py-2 hover:bg-accent text-sm">Word Cards (KR)</a>
                            <a href="<?php echo $nav->getModuleUrl('learning/voca/voca.php'); ?>" class="block px-4 py-2 hover:bg-accent text-sm">Vocabulary</a>
                        </div>
                    </div>

                    <div class="flex items-center gap-1">
                        <button class="p-2 rounded-lg hover:bg-accent transition-colors" title="Tools" onclick="toggleDropdown('tools-dropdown')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                            </svg>
                            <span>Tools</span>
                        </button>
                        <div id="tools-dropdown" class="absolute top-full mt-2 w-48 bg-popover border rounded-lg shadow-lg hidden">
                                                            <a href="<?php echo $nav->getModuleUrl('tools/news/search_news_form.php'); ?>" class="block px-4 py-2 hover:bg-accent text-sm">News Search</a>
                            <a href="<?php echo $nav->getModuleUrl('tools/tour/familytour.php'); ?>" class="block px-4 py-2 hover:bg-accent text-sm">Family Tour</a>
                            <a href="<?php echo $nav->getModuleUrl('tools/box/boxbreathe.php'); ?>" class="block px-4 py-2 hover:bg-accent text-sm">Box Breathing</a>
                        </div>
                    </div>

                    <div class="flex items-center gap-1">
                        <button class="p-2 rounded-lg hover:bg-accent transition-colors" title="Management" onclick="toggleDropdown('management-dropdown')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <ellipse cx="12" cy="5" rx="9" ry="3"/>
                                <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/>
                                <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>
                            </svg>
                            <span>Management</span>
                        </button>
                        <div id="management-dropdown" class="absolute top-full mt-2 w-48 bg-popover border rounded-lg shadow-lg hidden">
                            <a href="<?php echo $nav->getModuleUrl('management/crud/data_list.php'); ?>" class="block px-4 py-2 hover:bg-accent text-sm">CRUD Demo</a>
                            <a href="<?php echo $nav->getModuleUrl('management/myhealth/health_list.php'); ?>" class="block px-4 py-2 hover:bg-accent text-sm">My Health</a>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-2">
                    <!-- Theme Toggle -->
                    <button id="theme-toggle" onclick="toggleTheme()" class="p-2 rounded-lg hover:bg-accent transition-colors" title="Toggle theme">
                        <i id="themeIcon" class="fas fa-moon text-lg"></i>
                    </button>

                                    <!-- User Menu -->
                <?php if (isset($_SESSION['id'])): ?>
                        <div class="relative">
                            <button class="flex items-center gap-2 p-2 rounded-lg hover:bg-accent transition-colors" onclick="toggleDropdown('user-dropdown')">
                                <div class="w-8 h-8 bg-primary text-primary-foreground rounded-full flex items-center justify-center text-sm font-medium">
                                    <?php echo strtoupper(substr($_SESSION['id'], 0, 1)); ?>
                                </div>
                                <span class="hidden sm:block"><?php echo htmlspecialchars($_SESSION['id']); ?></span>
                            </button>
                            <div id="user-dropdown" class="absolute right-0 top-full mt-2 w-48 bg-popover border rounded-lg shadow-lg hidden">
                                <?php if ($_SESSION['id'] === 'admin'): ?>
                                    <a href="<?php echo $nav->getSystemUrl('admin/system_check.php'); ?>" class="block px-4 py-2 hover:bg-accent text-sm">System Check</a>
                                    <hr class="my-1 border-border">
                                <?php endif; ?>
                                <a href="<?php echo $nav->getSystemUrl('auth/logout.php'); ?>" class="block px-4 py-2 hover:bg-accent text-sm text-destructive">Logout</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo $nav->getSystemUrl('auth/login.php'); ?>" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors text-sm font-medium">
                            Login
                        </a>
                    <?php endif; ?>

                    <!-- Mobile Menu Button -->
                    <button id="mobile-menu-button" class="md:hidden p-2 rounded-lg hover:bg-accent transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="md:hidden fixed inset-y-0 left-0 z-50 w-64 bg-background border-r transform mobile-menu-hidden transition-transform duration-300 ease-in-out">
            <div class="flex items-center justify-between p-4 border-b">
                <span class="font-bold text-lg">My Playground</span>
                <button id="mobile-menu-close" class="p-2 rounded-lg hover:bg-accent transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <nav class="p-4 space-y-4">
                <div>
                    <h3 class="font-medium text-sm text-muted-foreground mb-2">Learning</h3>
                    <div class="space-y-1 ml-4">
                        <a href="<?php echo $nav->getModuleUrl('learning/card/slideshow.php'); ?>" class="block p-2 rounded hover:bg-accent text-sm">Card Slideshow</a>
                        <a href="<?php echo $nav->getModuleUrl('learning/card/wordcard_en.php'); ?>" class="block p-2 rounded hover:bg-accent text-sm">Word Cards (EN)</a>
                        <a href="<?php echo $nav->getModuleUrl('learning/card/wordcard_ko.php'); ?>" class="block p-2 rounded hover:bg-accent text-sm">Word Cards (KR)</a>
                        <a href="<?php echo $nav->getModuleUrl('learning/voca/voca.php'); ?>" class="block p-2 rounded hover:bg-accent text-sm">Vocabulary</a>
                    </div>
                </div>
                <div>
                    <h3 class="font-medium text-sm text-muted-foreground mb-2">Tools</h3>
                    <div class="space-y-1 ml-4">
                        <a href="<?php echo $nav->getModuleUrl('tools/news/search_news_form.php'); ?>" class="block p-2 rounded hover:bg-accent text-sm">News Search</a>
                        <a href="<?php echo $nav->getModuleUrl('tools/tour/familytour.php'); ?>" class="block p-2 rounded hover:bg-accent text-sm">Family Tour</a>
                        <a href="<?php echo $nav->getModuleUrl('tools/box/boxbreathe.php'); ?>" class="block p-2 rounded hover:bg-accent text-sm">Box Breathing</a>
                    </div>
                </div>
                <div>
                    <h3 class="font-medium text-sm text-muted-foreground mb-2">Management</h3>
                    <div class="space-y-1 ml-4">
                        <a href="<?php echo $nav->getModuleUrl('management/crud/data_list.php'); ?>" class="block p-2 rounded hover:bg-accent text-sm">CRUD Demo</a>
                        <a href="<?php echo $nav->getModuleUrl('management/myhealth/health_list.php'); ?>" class="block p-2 rounded hover:bg-accent text-sm">My Health</a>
                    </div>
                </div>
            </nav>
        </div>

        <!-- Mobile Menu Overlay -->
        <div id="mobile-menu-overlay" class="fixed inset-0 bg-background/80 backdrop-blur-sm z-40 hidden md:hidden"></div>
    </nav>

    <script>
    (function() {
      const userTheme = localStorage.getItem('theme');
      if (userTheme === 'dark' || (!userTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
        const icon = document.getElementById('themeIcon');
        if (icon) { icon.className = 'fas fa-sun'; }
      } else {
        document.documentElement.classList.remove('dark');
        const icon = document.getElementById('themeIcon');
        if (icon) { icon.className = 'fas fa-moon'; }
      }
      window.toggleTheme = function() {
        const isDark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        const icon = document.getElementById('themeIcon');
        if (icon) {
          icon.className = isDark ? 'fas fa-sun' : 'fas fa-moon';
        }
      };
    })();
    </script>

    <script>
        // Dropdown functionality
        function toggleDropdown(dropdownId) {
            const dropdown = document.getElementById(dropdownId);
            const allDropdowns = document.querySelectorAll('[id$="-dropdown"]');
            
            // Close all other dropdowns
            allDropdowns.forEach(dd => {
                if (dd.id !== dropdownId) {
                    dd.classList.add('hidden');
                }
            });
            
            dropdown.classList.toggle('hidden');
        }

        // Mobile menu functionality
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        const mobileMenuClose = document.getElementById('mobile-menu-close');
        const mobileMenuOverlay = document.getElementById('mobile-menu-overlay');

        function openMobileMenu() {
            mobileMenu.classList.remove('mobile-menu-hidden');
            mobileMenu.classList.add('mobile-menu-visible');
            mobileMenuOverlay.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeMobileMenu() {
            mobileMenu.classList.remove('mobile-menu-visible');
            mobileMenu.classList.add('mobile-menu-hidden');
            mobileMenuOverlay.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        mobileMenuButton.addEventListener('click', openMobileMenu);
        mobileMenuClose.addEventListener('click', closeMobileMenu);
        mobileMenuOverlay.addEventListener('click', closeMobileMenu);

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            const isDropdownButton = event.target.closest('[onclick*="toggleDropdown"]');
            const isDropdownContent = event.target.closest('[id$="-dropdown"]');
            
            if (!isDropdownButton && !isDropdownContent) {
                document.querySelectorAll('[id$="-dropdown"]').forEach(dropdown => {
                    dropdown.classList.add('hidden');
                });
            }
        });
    </script>
</body>
</html> 