<?php

namespace System\Includes;

/**
 * 모바일 최적화 시스템
 * 반응형 디자인과 모바일 특화 기능을 제공하는 시스템
 */
class MobileOptimizer
{
    private array $config;
    private Logger $logger;
    private bool $isMobile;
    private string $userAgent;
    private array $deviceInfo;

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'mobile_breakpoint' => 768,
            'tablet_breakpoint' => 1024,
            'enable_touch_gestures' => true,
            'enable_swipe_navigation' => true,
            'enable_offline_support' => true,
            'image_optimization' => [
                'enabled' => true,
                'webp_support' => true,
                'lazy_loading' => true,
                'responsive_images' => true
            ],
            'performance' => [
                'minify_css' => true,
                'minify_js' => true,
                'combine_files' => true,
                'cache_headers' => true
            ]
        ], $config);

        $this->logger = new Logger('mobile_optimizer');
        $this->userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $this->isMobile = $this->detectMobile();
        $this->deviceInfo = $this->getDeviceInfo();
    }

    /**
     * 모바일 기기 감지
     */
    private function detectMobile(): bool
    {
        $mobileKeywords = [
            'Mobile', 'Android', 'iPhone', 'iPad', 'Windows Phone',
            'BlackBerry', 'Opera Mini', 'IEMobile'
        ];

        foreach ($mobileKeywords as $keyword) {
            if (stripos($this->userAgent, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }



    /**
     * 모바일 최적화된 HTML 생성
     */
    public function optimizeHtml(string $html): string
    {
        if (!$this->isMobile) {
            return $html;
        }

        $html = $this->addMobileMetaTags($html);
        $html = $this->optimizeImages($html);
        $html = $this->addTouchSupport($html);
        $html = $this->optimizeForms($html);
        $html = $this->addMobileNavigation($html);

        return $html;
    }

    /**
     * 모바일 메타 태그 추가
     */
    private function addMobileMetaTags(string $html): string
    {
        $metaTags = [
            '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">',
            '<meta name="mobile-web-app-capable" content="yes">',
            '<meta name="apple-mobile-web-app-capable" content="yes">',
            '<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">',
            '<meta name="format-detection" content="telephone=no">',
            '<meta name="theme-color" content="#2c3e50">'
        ];

        $headPos = strpos($html, '</head>');
        if ($headPos !== false) {
            $metaString = "\n    " . implode("\n    ", $metaTags) . "\n";
            $html = substr_replace($html, $metaString, $headPos, 0);
        }

        return $html;
    }

    /**
     * 이미지 최적화
     */
    private function optimizeImages(string $html): string
    {
        if (!$this->config['image_optimization']['enabled']) {
            return $html;
        }

        // lazy loading 추가
        if ($this->config['image_optimization']['lazy_loading']) {
            $html = preg_replace(
                '/<img([^>]+)>/i',
                '<img$1 loading="lazy">',
                $html
            );
        }

        // WebP 지원 확인 및 변환
        if ($this->config['image_optimization']['webp_support'] && $this->supportsWebP()) {
            $html = $this->addWebPSupport($html);
        }

        // 반응형 이미지 추가
        if ($this->config['image_optimization']['responsive_images']) {
            $html = $this->addResponsiveImages($html);
        }

        return $html;
    }

    /**
     * WebP 지원 확인
     */
    private function supportsWebP(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return strpos($accept, 'image/webp') !== false;
    }

    /**
     * WebP 지원 추가
     */
    private function addWebPSupport(string $html): string
    {
        return preg_replace_callback(
            '/<img([^>]+src=["\']([^"\']+)\.(jpg|jpeg|png)["\'][^>]*)>/i',
            function($matches) {
                $originalSrc = $matches[2] . '.' . $matches[3];
                $webpSrc = $matches[2] . '.webp';
                
                return '<picture>' .
                       '<source srcset="' . $webpSrc . '" type="image/webp">' .
                       '<img' . $matches[1] . '>' .
                       '</picture>';
            },
            $html
        );
    }

    /**
     * 반응형 이미지 추가
     */
    private function addResponsiveImages(string $html): string
    {
        return preg_replace_callback(
            '/<img([^>]+src=["\']([^"\']+)\.(jpg|jpeg|png)["\'][^>]*)>/i',
            function($matches) {
                $originalSrc = $matches[2] . '.' . $matches[3];
                $baseName = $matches[2];
                $extension = $matches[3];
                
                return '<img' . $matches[1] . ' ' .
                       'srcset="' . $baseName . '-small.' . $extension . ' 480w, ' .
                       $baseName . '-medium.' . $extension . ' 768w, ' .
                       $originalSrc . ' 1200w" ' .
                       'sizes="(max-width: 480px) 100vw, (max-width: 768px) 50vw, 33vw">';
            },
            $html
        );
    }

    /**
     * 터치 지원 추가
     */
    private function addTouchSupport(string $html): string
    {
        if (!$this->config['enable_touch_gestures']) {
            return $html;
        }

        // 터치 이벤트 추가
        $touchScript = '
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            // 터치 제스처 지원
            let startX, startY, startTime;
            
            document.addEventListener("touchstart", function(e) {
                startX = e.touches[0].clientX;
                startY = e.touches[0].clientY;
                startTime = Date.now();
            });
            
            document.addEventListener("touchend", function(e) {
                if (!startX || !startY) return;
                
                const endX = e.changedTouches[0].clientX;
                const endY = e.changedTouches[0].clientY;
                const deltaX = endX - startX;
                const deltaY = endY - startY;
                const deltaTime = Date.now() - startTime;
                
                // 스와이프 감지
                if (deltaTime < 300 && Math.abs(deltaX) > 50 && Math.abs(deltaY) < 100) {
                    if (deltaX > 0) {
                        // 오른쪽 스와이프
                        document.dispatchEvent(new CustomEvent("swipeRight"));
                    } else {
                        // 왼쪽 스와이프
                        document.dispatchEvent(new CustomEvent("swipeLeft"));
                    }
                }
                
                startX = startY = startTime = null;
            });
        });
        </script>';

        $bodyPos = strpos($html, '</body>');
        if ($bodyPos !== false) {
            $html = substr_replace($html, $touchScript, $bodyPos, 0);
        }

        return $html;
    }

    /**
     * 폼 최적화
     */
    private function optimizeForms(string $html): string
    {
        // 입력 필드 최적화
        $html = preg_replace(
            '/<input([^>]+type=["\']text["\'][^>]*)>/i',
            '<input$1 autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">',
            $html
        );

        // 숫자 입력 필드 최적화
        $html = preg_replace(
            '/<input([^>]+type=["\']number["\'][^>]*)>/i',
            '<input$1 inputmode="numeric" pattern="[0-9]*">',
            $html
        );

        // 이메일 입력 필드 최적화
        $html = preg_replace(
            '/<input([^>]+type=["\']email["\'][^>]*)>/i',
            '<input$1 inputmode="email" autocorrect="off" autocapitalize="off">',
            $html
        );

        // 전화번호 입력 필드 최적화
        $html = preg_replace(
            '/<input([^>]+type=["\']tel["\'][^>]*)>/i',
            '<input$1 inputmode="tel" pattern="[0-9]*">',
            $html
        );

        return $html;
    }

    /**
     * 모바일 네비게이션 추가
     */
    private function addMobileNavigation(string $html): string
    {
        if (!$this->config['enable_swipe_navigation']) {
            return $html;
        }

        $mobileNav = '
        <div id="mobile-nav" class="mobile-navigation" style="display: none;">
            <div class="mobile-nav-header">
                <button class="mobile-nav-close">&times;</button>
                <h3>메뉴</h3>
            </div>
            <nav class="mobile-nav-menu">
                <ul>
                    <li><a href="/">홈</a></li>
                    <li><a href="/modules/learning/">학습</a></li>
                    <li><a href="/modules/tools/">도구</a></li>
                    <li><a href="/modules/management/">관리</a></li>
                </ul>
            </nav>
        </div>
        
        <style>
        .mobile-navigation {
            position: fixed;
            top: 0;
            left: -100%;
            width: 80%;
            height: 100%;
            background: #fff;
            z-index: 1000;
            transition: left 0.3s ease;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        
        .mobile-navigation.active {
            left: 0;
        }
        
        .mobile-nav-header {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .mobile-nav-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
        }
        
        .mobile-nav-menu ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .mobile-nav-menu li {
            border-bottom: 1px solid #eee;
        }
        
        .mobile-nav-menu a {
            display: block;
            padding: 1rem;
            text-decoration: none;
            color: #333;
        }
        
        .mobile-nav-menu a:hover {
            background: #f5f5f5;
        }
        
        @media (max-width: 768px) {
            .mobile-navigation {
                width: 100%;
            }
        }
        </style>
        
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            const mobileNav = document.getElementById("mobile-nav");
            const closeBtn = document.querySelector(".mobile-nav-close");
            
            // 스와이프로 네비게이션 열기/닫기
            document.addEventListener("swipeRight", function() {
                mobileNav.classList.add("active");
            });
            
            document.addEventListener("swipeLeft", function() {
                mobileNav.classList.remove("active");
            });
            
            // 닫기 버튼
            closeBtn.addEventListener("click", function() {
                mobileNav.classList.remove("active");
            });
            
            // 배경 클릭으로 닫기
            mobileNav.addEventListener("click", function(e) {
                if (e.target === mobileNav) {
                    mobileNav.classList.remove("active");
                }
            });
        });
        </script>';

        $bodyPos = strpos($html, '</body>');
        if ($bodyPos !== false) {
            $html = substr_replace($html, $mobileNav, $bodyPos, 0);
        }

        return $html;
    }

    /**
     * 모바일 최적화된 CSS 생성
     */
    public function generateMobileCSS(): string
    {
        $css = '
        /* 모바일 최적화 CSS */
        
        /* 기본 반응형 설정 */
        * {
            box-sizing: border-box;
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            line-height: 1.6;
            -webkit-text-size-adjust: 100%;
            -webkit-tap-highlight-color: transparent;
        }
        
        /* 터치 최적화 */
        button, a, input, select, textarea {
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
        }
        
        /* 모바일 네비게이션 */
        @media (max-width: 768px) {
            .desktop-nav {
                display: none;
            }
            
            .mobile-nav-toggle {
                display: block;
            }
            
            /* 컨테이너 최적화 */
            .container {
                padding: 0 1rem;
                max-width: 100%;
            }
            
            /* 폼 최적화 */
            input, select, textarea {
                font-size: 16px; /* iOS 줌 방지 */
                padding: 0.75rem;
                border: 1px solid #ddd;
                border-radius: 4px;
                width: 100%;
            }
            
            /* 버튼 최적화 */
            button {
                min-height: 44px; /* 터치 최소 크기 */
                padding: 0.75rem 1rem;
                font-size: 16px;
            }
            
            /* 카드 레이아웃 */
            .card {
                margin-bottom: 1rem;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            
            /* 테이블 반응형 */
            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            
            /* 이미지 최적화 */
            img {
                max-width: 100%;
                height: auto;
            }
        }
        
        /* 태블릿 최적화 */
        @media (min-width: 769px) and (max-width: 1024px) {
            .container {
                padding: 0 2rem;
            }
            
            .card {
                margin-bottom: 1.5rem;
            }
        }
        
        /* 터치 제스처 */
        .swipeable {
            touch-action: pan-y;
        }
        
        /* 로딩 상태 */
        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }
        
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* 오프라인 상태 */
        .offline {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #e74c3c;
            color: white;
            text-align: center;
            padding: 0.5rem;
            z-index: 1000;
        }
        
        /* 터치 피드백 */
        .touch-feedback {
            transition: transform 0.1s ease;
        }
        
        .touch-feedback:active {
            transform: scale(0.95);
        }';

        return $css;
    }

    /**
     * 모바일 최적화된 JavaScript 생성
     */
    public function generateMobileJS(): string
    {
        $js = '
        // 모바일 최적화 JavaScript
        
        document.addEventListener("DOMContentLoaded", function() {
            // 터치 피드백 추가
            const touchElements = document.querySelectorAll("button, a, .touch-feedback");
            touchElements.forEach(function(element) {
                element.addEventListener("touchstart", function() {
                    this.style.transform = "scale(0.95)";
                });
                
                element.addEventListener("touchend", function() {
                    this.style.transform = "scale(1)";
                });
            });
            
            // 스크롤 성능 최적화
            let ticking = false;
            function updateScroll() {
                // 스크롤 관련 업데이트
                ticking = false;
            }
            
            function requestTick() {
                if (!ticking) {
                    requestAnimationFrame(updateScroll);
                    ticking = true;
                }
            }
            
            window.addEventListener("scroll", requestTick);
            
            // 오프라인 상태 감지
            window.addEventListener("online", function() {
                document.body.classList.remove("offline");
                const offlineNotice = document.querySelector(".offline-notice");
                if (offlineNotice) {
                    offlineNotice.remove();
                }
            });
            
            window.addEventListener("offline", function() {
                document.body.classList.add("offline");
                const notice = document.createElement("div");
                notice.className = "offline-notice";
                notice.textContent = "오프라인 상태입니다. 인터넷 연결을 확인해주세요.";
                document.body.insertBefore(notice, document.body.firstChild);
            });
            
            // 이미지 지연 로딩
            if ("IntersectionObserver" in window) {
                const imageObserver = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            img.src = img.dataset.src;
                            img.classList.remove("lazy");
                            imageObserver.unobserve(img);
                        }
                    });
                });
                
                document.querySelectorAll("img[data-src]").forEach(function(img) {
                    imageObserver.observe(img);
                });
            }
            
            // 터치 제스처 처리
            let startX, startY;
            
            document.addEventListener("touchstart", function(e) {
                startX = e.touches[0].clientX;
                startY = e.touches[0].clientY;
            });
            
            document.addEventListener("touchend", function(e) {
                if (!startX || !startY) return;
                
                const endX = e.changedTouches[0].clientX;
                const endY = e.changedTouches[0].clientY;
                const deltaX = endX - startX;
                const deltaY = endY - startY;
                
                // 수평 스와이프 감지
                if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > 50) {
                    if (deltaX > 0) {
                        // 오른쪽 스와이프
                        handleSwipeRight();
                    } else {
                        // 왼쪽 스와이프
                        handleSwipeLeft();
                    }
                }
                
                startX = startY = null;
            });
            
            function handleSwipeRight() {
                // 네비게이션 열기
                const mobileNav = document.getElementById("mobile-nav");
                if (mobileNav) {
                    mobileNav.classList.add("active");
                }
            }
            
            function handleSwipeLeft() {
                // 네비게이션 닫기
                const mobileNav = document.getElementById("mobile-nav");
                if (mobileNav) {
                    mobileNav.classList.remove("active");
                }
            }
        });';

        return $js;
    }

    /**
     * 기기 정보 가져오기 (public)
     */
    public function getDeviceInfo(): array
    {
        $info = [
            'is_mobile' => $this->isMobile,
            'is_tablet' => false,
            'is_desktop' => !$this->isMobile,
            'os' => 'unknown',
            'browser' => 'unknown',
            'screen_width' => 0,
            'screen_height' => 0,
            'pixel_ratio' => 1,
            'touch_support' => false
        ];

        // OS 감지
        if (stripos($this->userAgent, 'Android') !== false) {
            $info['os'] = 'Android';
        } elseif (stripos($this->userAgent, 'iPhone') !== false) {
            $info['os'] = 'iOS';
        } elseif (stripos($this->userAgent, 'iPad') !== false) {
            $info['os'] = 'iOS';
            $info['is_tablet'] = true;
        } elseif (stripos($this->userAgent, 'Windows') !== false) {
            $info['os'] = 'Windows';
        }

        // 브라우저 감지
        if (stripos($this->userAgent, 'Chrome') !== false) {
            $info['browser'] = 'Chrome';
        } elseif (stripos($this->userAgent, 'Safari') !== false) {
            $info['browser'] = 'Safari';
        } elseif (stripos($this->userAgent, 'Firefox') !== false) {
            $info['browser'] = 'Firefox';
        }

        // 터치 지원 확인
        $info['touch_support'] = $this->isMobile || isset($_SERVER['HTTP_ACCEPT']) && 
            strpos($_SERVER['HTTP_ACCEPT'], 'application/vnd.wap.xhtml+xml') !== false;

        return $info;
    }

    /**
     * 모바일 기기인지 확인
     */
    public function isMobile(): bool
    {
        return $this->isMobile;
    }

    /**
     * 태블릿 기기인지 확인
     */
    public function isTablet(): bool
    {
        return $this->deviceInfo['is_tablet'];
    }

    /**
     * 터치 지원 여부 확인
     */
    public function hasTouchSupport(): bool
    {
        return $this->deviceInfo['touch_support'];
    }

    /**
     * 성능 최적화 헤더 설정
     */
    public function setPerformanceHeaders(): void
    {
        if ($this->config['performance']['cache_headers']) {
            header('Cache-Control: public, max-age=31536000');
            header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000));
        }
    }
} 