<?php
class Layout {
    private $pageTitle;
    private $additionalCSS;
    private $additionalJS;
    private $showHeader;
    private $showFooter;
    private $showNavigation;
    private $customBackground;
    
    public function __construct($options = []) {
        $this->pageTitle = $options['pageTitle'] ?? 'My Playground';
        $this->additionalCSS = $options['additionalCSS'] ?? '';
        $this->additionalJS = $options['additionalJS'] ?? '';
        $this->showHeader = $options['showHeader'] ?? true;
        $this->showFooter = $options['showFooter'] ?? true;
        $this->showNavigation = $options['showNavigation'] ?? true;
        $this->customBackground = $options['customBackground'] ?? '';
    }
    
    public function renderHeader() {
        $html = '<!DOCTYPE html>';
        $html .= '<html lang="ko" class="h-full">';
        $html .= '<head>';
        $html .= '<meta charset="UTF-8">';
        $html .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        $html .= '<title>' . htmlspecialchars($this->pageTitle) . ' - My Playground</title>';
        $html .= '<link href="/mp/resources/css/tailwind.output.css" rel="stylesheet">';
        $html .= '<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">';
        $html .= '<script>';
        $html .= '(function() {';
        $html .= '  const userTheme = localStorage.getItem("theme");';
        $html .= '  if (userTheme === "dark" || (!userTheme && window.matchMedia("(prefers-color-scheme: dark)").matches)) {';
        $html .= '    document.documentElement.classList.add("dark");';
        $html .= '    const icon = document.getElementById("themeIcon");';
        $html .= '    if (icon) { icon.className = "fas fa-sun"; }';
        $html .= '  } else {';
        $html .= '    document.documentElement.classList.remove("dark");';
        $html .= '    const icon = document.getElementById("themeIcon");';
        $html .= '    if (icon) { icon.className = "fas fa-moon"; }';
        $html .= '  }';
        $html .= '  window.toggleTheme = function() {';
        $html .= '    const isDark = document.documentElement.classList.toggle("dark");';
        $html .= '    localStorage.setItem("theme", isDark ? "dark" : "light");';
        $html .= '    const icon = document.getElementById("themeIcon");';
        $html .= '    if (icon) {';
        $html .= '      icon.className = isDark ? "fas fa-sun" : "fas fa-moon";';
        $html .= '    }';
        $html .= '  };';
        $html .= '})();';
        $html .= '</script>';
        $html .= '<style>';
        $html .= ':root {';
        $html .= '  --background: 0 0% 100%;';
        $html .= '  --foreground: 222.2 84% 4.9%;';
        $html .= '  --card: 0 0% 100%;';
        $html .= '  --card-foreground: 222.2 84% 4.9%;';
        $html .= '  --popover: 0 0% 100%;';
        $html .= '  --popover-foreground: 222.2 84% 4.9%;';
        $html .= '  --primary: 222.2 47.4% 11.2%;';
        $html .= '  --primary-foreground: 210 40% 98%;';
        $html .= '  --secondary: 210 40% 96%;';
        $html .= '  --secondary-foreground: 222.2 84% 4.9%;';
        $html .= '  --muted: 210 40% 96%;';
        $html .= '  --muted-foreground: 215.4 16.3% 46.9%;';
        $html .= '  --accent: 210 40% 96%;';
        $html .= '  --accent-foreground: 222.2 84% 4.9%;';
        $html .= '  --destructive: 0 84.2% 60.2%;';
        $html .= '  --destructive-foreground: 210 40% 98%;';
        $html .= '  --border: 214.3 31.8% 91.4%;';
        $html .= '  --input: 214.3 31.8% 91.4%;';
        $html .= '  --ring: 222.2 84% 4.9%;';
        $html .= '}';
        $html .= '.dark {';
        $html .= '  --background: 222.2 84% 4.9%;';
        $html .= '  --foreground: 210 40% 98%;';
        $html .= '  --card: 222.2 84% 4.9%;';
        $html .= '  --card-foreground: 210 40% 98%;';
        $html .= '  --popover: 222.2 84% 4.9%;';
        $html .= '  --popover-foreground: 210 40% 98%;';
        $html .= '  --primary: 210 40% 98%;';
        $html .= '  --primary-foreground: 222.2 47.4% 11.2%;';
        $html .= '  --secondary: 217.2 32.6% 17.5%;';
        $html .= '  --secondary-foreground: 210 40% 98%;';
        $html .= '  --muted: 217.2 32.6% 17.5%;';
        $html .= '  --muted-foreground: 215 20.2% 65.1%;';
        $html .= '  --accent: 217.2 32.6% 17.5%;';
        $html .= '  --accent-foreground: 210 40% 98%;';
        $html .= '  --destructive: 0 62.8% 30.6%;';
        $html .= '  --destructive-foreground: 210 40% 98%;';
        $html .= '  --border: 217.2 32.6% 17.5%;';
        $html .= '  --input: 217.2 32.6% 17.5%;';
        $html .= '  --ring: 212.7 26.8% 83.9%;';
        $html .= '}';
        $html .= '.gradient-bg {';
        $html .= '  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);';
        $html .= '}';
        $html .= '.dark .gradient-bg {';
        $html .= '  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);';
        $html .= '}';
        $html .= '.card-hover {';
        $html .= '  transition: all 0.3s ease;';
        $html .= '}';
        $html .= '.card-hover:hover {';
        $html .= '  transform: translateY(-5px);';
        $html .= '  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);';
        $html .= '}';
        $html .= '.mobile-menu-hidden {';
        $html .= '  transform: translateX(-100%);';
        $html .= '}';
        $html .= '.mobile-menu-visible {';
        $html .= '  transform: translateX(0);';
        $html .= '}';
        $html .= '.breadcrumb-separator::before {';
        $html .= '  content: "/";';
        $html .= '  margin: 0 0.5rem;';
        $html .= '  color: hsl(var(--muted-foreground));';
        $html .= '}';
        $html .= '</style>';
        if ($this->additionalCSS) {
            $html .= $this->additionalCSS;
        }
        $html .= '</head>';
        
        // Body with custom background if specified
        $bodyClass = 'bg-background text-foreground min-h-screen flex flex-col';
        if ($this->customBackground) {
            $bodyClass .= ' ' . $this->customBackground;
        }
        $html .= '<body class="' . $bodyClass . '">';
        
        return $html;
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
        $html .= '<a href="modules/learning/card/slideshow.php" class="text-sm font-medium hover:text-primary transition-colors">Learning</a>';
        $html .= '<a href="modules/tools/news/search_news_form.php" class="text-sm font-medium hover:text-primary transition-colors">Tools</a>';
        $html .= '<a href="modules/management/crud/data_list.php" class="text-sm font-medium hover:text-primary transition-colors">Management</a>';
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
        $html .= '<a href="modules/learning/voca/voca.php" class="hover:text-foreground transition-colors">Vocabulary</a>';
        $html .= '<a href="modules/learning/card/slideshow.php" class="hover:text-foreground transition-colors">Cards</a>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</footer>';
        
        return $html;
    }
    
    public function renderThemeScript() {
        $html = '<script>';
        $html .= '(function() {';
        $html .= '  const userTheme = localStorage.getItem("theme");';
        $html .= '  if (userTheme === "dark" || (!userTheme && window.matchMedia("(prefers-color-scheme: dark)").matches)) {';
        $html .= '    document.documentElement.classList.add("dark");';
        $html .= '    const icon = document.getElementById("themeIcon");';
        $html .= '    if (icon) { icon.className = "fas fa-sun"; }';
        $html .= '  } else {';
        $html .= '    document.documentElement.classList.remove("dark");';
        $html .= '    const icon = document.getElementById("themeIcon");';
        $html .= '    if (icon) { icon.className = "fas fa-moon"; }';
        $html .= '  }';
        $html .= '  window.toggleTheme = function() {';
        $html .= '    const isDark = document.documentElement.classList.toggle("dark");';
        $html .= '    localStorage.setItem("theme", isDark ? "dark" : "light");';
        $html .= '    const icon = document.getElementById("themeIcon");';
        $html .= '    if (icon) {';
        $html .= '      icon.className = isDark ? "fas fa-sun" : "fas fa-moon";';
        $html .= '    }';
        $html .= '  };';
        $html .= '})();';
        $html .= '</script>';
        
        return $html;
    }
    
    public function renderClosing() {
        $html = $this->renderThemeScript();
        $html .= '</body>';
        $html .= '</html>';
        return $html;
    }
    
    public function render($content) {
        $html = $this->renderHeader();
        $html .= $this->renderNavigation();
        $html .= '<main class="flex-1">';
        $html .= $content;
        $html .= '</main>';
        $html .= $this->renderFooter();
        $html .= $this->renderClosing();
        echo $html;
    }
} 