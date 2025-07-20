<?php

class Layout {
    private $pageTitle;
    private $additionalCSS;
    private $showHeader;
    private $showFooter;
    private $showNavigation;
    private $customBackground;
    
    public function __construct($options = []) {
        $this->pageTitle = $options['pageTitle'] ?? 'Welcome';
        $this->additionalCSS = $options['additionalCSS'] ?? '';
        $this->showHeader = $options['showHeader'] ?? true;
        $this->showFooter = $options['showFooter'] ?? true;
        $this->showNavigation = $options['showNavigation'] ?? true;
        $this->customBackground = $options['customBackground'] ?? '';
    }
    
    public function renderHeader() {
        $html = $this->renderDoctype();
        $html .= $this->renderHead();
        $html .= $this->renderBodyStart();
        
        return $html;
    }

    private function renderDoctype(): string {
        return '<!DOCTYPE html><html lang="ko" class="h-full">';
    }

    private function renderHead(): string {
        $html = '<head>';
        $html .= $this->renderMetaTags();
        $html .= $this->renderTitle();
        $html .= $this->renderStylesheets();
        $html .= $this->renderThemeScript();
        $html .= $this->renderCustomStyles();
        $html .= $this->renderAdditionalCSS();
        $html .= '</head>';
        
        return $html;
    }

    private function renderMetaTags(): string {
        return '<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">';
    }

    private function renderTitle(): string {
        return '<title>' . htmlspecialchars($this->pageTitle) . ' - My Playground</title>';
    }

    private function renderStylesheets(): string {
        global $nav;
        $rootUrl = $nav ? $nav->getRootUrl() : '';
        return '<link href="' . $rootUrl . 'resources/css/tailwind.output.css" rel="stylesheet">' .
               '<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">';
    }

    private function renderThemeScript(): string {
        return '<script>' .
               '(function() {' .
               '  const userTheme = localStorage.getItem("theme");' .
               '  if (userTheme === "dark" || (!userTheme && window.matchMedia("(prefers-color-scheme: dark)").matches)) {' .
               '    document.documentElement.classList.add("dark");' .
               '    const icon = document.getElementById("themeIcon");' .
               '    if (icon) { icon.className = "fas fa-sun"; }' .
               '  } else {' .
               '    document.documentElement.classList.remove("dark");' .
               '    const icon = document.getElementById("themeIcon");' .
               '    if (icon) { icon.className = "fas fa-moon"; }' .
               '  }' .
               '  window.toggleTheme = function() {' .
               '    const isDark = document.documentElement.classList.toggle("dark");' .
               '    localStorage.setItem("theme", isDark ? "dark" : "light");' .
               '    const icon = document.getElementById("themeIcon");' .
               '    if (icon) {' .
               '      icon.className = isDark ? "fas fa-sun" : "fas fa-moon";' .
               '    }' .
               '  };' .
               '})();' .
               '</script>';
    }

    private function renderCustomStyles(): string {
        return '<style>' .
               $this->renderCSSVariables() .
               $this->renderDarkModeCSS() .
               $this->renderUtilityClasses() .
               '</style>';
    }

    private function renderCSSVariables(): string {
        return ':root {' .
               '  --background: 0 0% 100%;' .
               '  --foreground: 222.2 84% 4.9%;' .
               '  --card: 0 0% 100%;' .
               '  --card-foreground: 222.2 84% 4.9%;' .
               '  --popover: 0 0% 100%;' .
               '  --popover-foreground: 222.2 84% 4.9%;' .
               '  --primary: 222.2 47.4% 11.2%;' .
               '  --primary-foreground: 210 40% 98%;' .
               '  --secondary: 210 40% 96%;' .
               '  --secondary-foreground: 222.2 84% 4.9%;' .
               '  --muted: 210 40% 96%;' .
               '  --muted-foreground: 215.4 16.3% 46.9%;' .
               '  --accent: 210 40% 96%;' .
               '  --accent-foreground: 222.2 84% 4.9%;' .
               '  --destructive: 0 84.2% 60.2%;' .
               '  --destructive-foreground: 210 40% 98%;' .
               '  --border: 214.3 31.8% 91.4%;' .
               '  --input: 214.3 31.8% 91.4%;' .
               '  --ring: 222.2 84% 4.9%;' .
               '}';
    }

    private function renderDarkModeCSS(): string {
        return '.dark {' .
               '  --background: 222.2 84% 4.9%;' .
               '  --foreground: 210 40% 98%;' .
               '  --card: 222.2 84% 4.9%;' .
               '  --card-foreground: 210 40% 98%;' .
               '  --popover: 222.2 84% 4.9%;' .
               '  --popover-foreground: 210 40% 98%;' .
               '  --primary: 210 40% 98%;' .
               '  --primary-foreground: 222.2 47.4% 11.2%;' .
               '  --secondary: 217.2 32.6% 17.5%;' .
               '  --secondary-foreground: 210 40% 98%;' .
               '  --muted: 217.2 32.6% 17.5%;' .
               '  --muted-foreground: 215 20.2% 65.1%;' .
               '  --accent: 217.2 32.6% 17.5%;' .
               '  --accent-foreground: 210 40% 98%;' .
               '  --destructive: 0 62.8% 30.6%;' .
               '  --destructive-foreground: 210 40% 98%;' .
               '  --border: 217.2 32.6% 17.5%;' .
               '  --input: 217.2 32.6% 17.5%;' .
               '  --ring: 212.7 26.8% 83.9%;' .
               '}';
    }

    private function renderUtilityClasses(): string {
        return '.gradient-bg {' .
               '  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);' .
               '}' .
               '.dark .gradient-bg {' .
               '  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);' .
               '}' .
               '.card-hover {' .
               '  transition: all 0.3s ease;' .
               '}' .
               '.card-hover:hover {' .
               '  transform: translateY(-5px);' .
               '  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);' .
               '}' .
               '.mobile-menu-hidden {' .
               '  transform: translateX(-100%);' .
               '}' .
               '.mobile-menu-visible {' .
               '  transform: translateX(0);' .
               '}' .
               '.breadcrumb-separator::before {' .
               '  content: "/";' .
               '  margin: 0 0.5rem;' .
               '  color: hsl(var(--muted-foreground));' .
               '}';
    }

    private function renderAdditionalCSS(): string {
        return $this->additionalCSS;
    }

    private function renderBodyStart(): string {
        $bodyClass = 'bg-background text-foreground min-h-screen flex flex-col';
        if ($this->customBackground) {
            $bodyClass .= ' ' . $this->customBackground;
        }
        return '<body class="' . $bodyClass . '">';
    }
    
    public function renderNavigation() {
        if (!$this->showNavigation) {
            return '';
        }
        
        $html = '<nav class="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">';
        $html .= '<div class="container mx-auto px-4">';
        $html .= '<div class="flex h-16 items-center justify-between">';
        
        // Logo
        $html .= '<div class="flex items-center gap-4">';
        $html .= '<a href="index.php" class="font-bold text-xl">My Playground</a>';
        $html .= '</div>';
        
        // Desktop Navigation
        $html .= '<div class="hidden md:flex items-center gap-6">';
        global $nav;
        $html .= '<a href="' . $nav->getModuleUrl('learning/card/slideshow.php') . '" class="text-sm font-medium hover:text-primary transition-colors">Learning</a>';
        $html .= '<a href="' . $nav->getModuleUrl('tools/news/search_news_form.php') . '" class="text-sm font-medium hover:text-primary transition-colors">Tools</a>';
        $html .= '<a href="' . $nav->getModuleUrl('management/crud/data_list.php') . '" class="text-sm font-medium hover:text-primary transition-colors">Management</a>';
        $html .= '</div>';
        
        // Action Buttons
        $html .= '<div class="flex items-center gap-2">';
        $html .= '<button id="theme-toggle" onclick="toggleTheme()" class="p-2 rounded-lg hover:bg-accent transition-colors" title="Toggle theme">';
        $html .= '<i id="themeIcon" class="fas fa-moon text-lg"></i>';
        $html .= '</button>';
        $html .= '<a href="index.php" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors text-sm font-medium">Home</a>';
        $html .= '</div>';
        
        $html .= '</div>'; // End flex container
        $html .= '</div>'; // End container
        $html .= '</nav>';
        
        return $html;
    }
    
    public function renderFooter() {
        if (!$this->showFooter) {
            return '';
        }
        
        $html = '<footer class="border-t mt-auto">';
        $html .= '<div class="container mx-auto px-4 py-6">';
        $html .= '<div class="flex flex-col md:flex-row justify-between items-center gap-4">';
        $html .= '<p class="text-sm text-muted-foreground">&copy; ' . date('Y') . ' My Playground. All rights reserved.</p>';
        $html .= '<div class="flex items-center gap-4 text-sm text-muted-foreground">';
        $html .= '<a href="index.php" class="hover:text-foreground transition-colors">Home</a>';
        global $nav;
        $html .= '<a href="' . $nav->getModuleUrl('learning/voca/voca.php') . '" class="hover:text-foreground transition-colors">Vocabulary</a>';
        $html .= '<a href="' . $nav->getModuleUrl('learning/card/slideshow.php') . '" class="hover:text-foreground transition-colors">Cards</a>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</footer>';
        
        return $html;
    }
    

    
    public function renderClosing() {
        $html = '</body>';
        $html .= '</html>';
        
        return $html;
    }
    
    public function render($content) {
        $html = '';
        
        if ($this->showHeader) {
            $html .= $this->renderHeader();
        }
        
        if ($this->showNavigation) {
            $html .= $this->renderNavigation();
        }
        
        $html .= '<main class="flex-1">';
        $html .= $content;
        $html .= '</main>';
        
        if ($this->showFooter) {
            $html .= $this->renderFooter();
        }
        
        $html .= $this->renderClosing();
        
        return $html;
    }
} 