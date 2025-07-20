<?php

class Slideshow {
    private $images = [];
    private $displayDuration = 2000;

    public function __construct($useExternalImages = false) {
        $this->loadImages();
    }

    private function loadImages() {
        // 기본 이미지들 (로컬 이미지)
        $localImages = [
            'images/갈매기.jpeg', 'images/개.jpeg', 'images/개구리.jpeg', 'images/거북이.jpeg', 'images/거위.jpeg',
            'images/고래.jpeg', 'images/고슴도치.jpeg', 'images/고양이.jpeg', 'images/곰.jpeg', 'images/금붕어.jpeg',
            'images/기린.jpeg', 'images/낙타.jpeg', 'images/너구리.jpeg', 'images/다람쥐.jpeg', 'images/닭.jpeg',
            'images/독수리.jpeg', 'images/돌고래.jpeg', 'images/돼지.jpeg', 'images/말.jpeg', 'images/뱀.jpeg',
            'images/병아리.jpeg', 'images/부엉이.jpeg', 'images/불가사리.jpeg', 'images/사자.jpeg', 'images/상어.jpeg',
            'images/소.jpeg', 'images/악어.jpeg', 'images/앵무새.jpeg', 'images/양.jpeg', 'images/얼룩말.jpeg',
            'images/여우.jpeg', 'images/염소.jpeg', 'images/오리.jpeg', 'images/오징어.jpeg', 'images/원숭이.jpeg',
            'images/참새.jpeg', 'images/침팬치.jpeg', 'images/캥거루.jpeg', 'images/코끼리.jpeg', 'images/코브라.jpeg',
            'images/코뿔소.jpeg', 'images/코알라.jpeg', 'images/타조.jpeg', 'images/토끼.jpeg', 'images/팬더.jpeg',
            'images/팽귄.jpeg', 'images/표범.jpeg', 'images/하마.jpeg', 'images/호랑이.jpeg'
        ];

        // 로컬 이미지들을 실제 존재하는 것만 필터링
        $this->images = array_filter($localImages, function($image) {
            return file_exists($image);
        });

        // 추가 이미지들 (Picsum Photos 사용)
        $additionalImages = [
            'https://picsum.photos/400/300?random=1', // 랜덤 이미지 1
            'https://picsum.photos/400/300?random=2', // 랜덤 이미지 2
            'https://picsum.photos/400/300?random=3', // 랜덤 이미지 3
            'https://picsum.photos/400/300?random=4', // 랜덤 이미지 4
            'https://picsum.photos/400/300?random=5', // 랜덤 이미지 5
            'https://picsum.photos/400/300?random=6', // 랜덤 이미지 6
            'https://picsum.photos/400/300?random=7', // 랜덤 이미지 7
            'https://picsum.photos/400/300?random=8', // 랜덤 이미지 8
            'https://picsum.photos/400/300?random=9', // 랜덤 이미지 9
            'https://picsum.photos/400/300?random=10', // 랜덤 이미지 10
            'https://picsum.photos/400/300?random=11', // 랜덤 이미지 11
            'https://picsum.photos/400/300?random=12', // 랜덤 이미지 12
            'https://picsum.photos/400/300?random=13', // 랜덤 이미지 13
            'https://picsum.photos/400/300?random=14', // 랜덤 이미지 14
            'https://picsum.photos/400/300?random=15', // 랜덤 이미지 15
            'https://picsum.photos/400/300?random=16', // 랜덤 이미지 16
            'https://picsum.photos/400/300?random=17', // 랜덤 이미지 17
            'https://picsum.photos/400/300?random=18', // 랜덤 이미지 18
            'https://picsum.photos/400/300?random=19', // 랜덤 이미지 19
            'https://picsum.photos/400/300?random=20', // 랜덤 이미지 20
            'https://picsum.photos/400/300?random=21', // 랜덤 이미지 21
            'https://picsum.photos/400/300?random=22', // 랜덤 이미지 22
            'https://picsum.photos/400/300?random=23', // 랜덤 이미지 23
            'https://picsum.photos/400/300?random=24', // 랜덤 이미지 24
            'https://picsum.photos/400/300?random=25', // 랜덤 이미지 25
            'https://picsum.photos/400/300?random=26', // 랜덤 이미지 26
            'https://picsum.photos/400/300?random=27', // 랜덤 이미지 27
            'https://picsum.photos/400/300?random=28', // 랜덤 이미지 28
            'https://picsum.photos/400/300?random=29', // 랜덤 이미지 29
            'https://picsum.photos/400/300?random=30', // 랜덤 이미지 30
            'https://picsum.photos/400/300?random=31', // 랜덤 이미지 31
            'https://picsum.photos/400/300?random=32', // 랜덤 이미지 32
            'https://picsum.photos/400/300?random=33', // 랜덤 이미지 33
            'https://picsum.photos/400/300?random=34', // 랜덤 이미지 34
            'https://picsum.photos/400/300?random=35', // 랜덤 이미지 35
            'https://picsum.photos/400/300?random=36', // 랜덤 이미지 36
            'https://picsum.photos/400/300?random=37', // 랜덤 이미지 37
            'https://picsum.photos/400/300?random=38', // 랜덤 이미지 38
            'https://picsum.photos/400/300?random=39', // 랜덤 이미지 39
            'https://picsum.photos/400/300?random=40', // 랜덤 이미지 40
            'https://picsum.photos/400/300?random=41', // 랜덤 이미지 41
            'https://picsum.photos/400/300?random=42', // 랜덤 이미지 42
            'https://picsum.photos/400/300?random=43', // 랜덤 이미지 43
            'https://picsum.photos/400/300?random=44', // 랜덤 이미지 44
            'https://picsum.photos/400/300?random=45', // 랜덤 이미지 45
            'https://picsum.photos/400/300?random=46', // 랜덤 이미지 46
            'https://picsum.photos/400/300?random=47', // 랜덤 이미지 47
            'https://picsum.photos/400/300?random=48', // 랜덤 이미지 48
            'https://picsum.photos/400/300?random=49', // 랜덤 이미지 49
            'https://picsum.photos/400/300?random=50', // 랜덤 이미지 50
            'https://picsum.photos/400/300?random=51', // 랜덤 이미지 51
            'https://picsum.photos/400/300?random=52', // 랜덤 이미지 52
            'https://picsum.photos/400/300?random=53', // 랜덤 이미지 53
            'https://picsum.photos/400/300?random=54', // 랜덤 이미지 54
            'https://picsum.photos/400/300?random=55', // 랜덤 이미지 55
            'https://picsum.photos/400/300?random=56', // 랜덤 이미지 56
            'https://picsum.photos/400/300?random=57', // 랜덤 이미지 57
            'https://picsum.photos/400/300?random=58', // 랜덤 이미지 58
            'https://picsum.photos/400/300?random=59', // 랜덤 이미지 59
            'https://picsum.photos/400/300?random=60', // 랜덤 이미지 60
            'https://picsum.photos/400/300?random=61', // 랜덤 이미지 61
            'https://picsum.photos/400/300?random=62', // 랜덤 이미지 62
            'https://picsum.photos/400/300?random=63', // 랜덤 이미지 63
            'https://picsum.photos/400/300?random=64', // 랜덤 이미지 64
            'https://picsum.photos/400/300?random=65', // 랜덤 이미지 65
            'https://picsum.photos/400/300?random=66', // 랜덤 이미지 66
            'https://picsum.photos/400/300?random=67', // 랜덤 이미지 67
            'https://picsum.photos/400/300?random=68', // 랜덤 이미지 68
            'https://picsum.photos/400/300?random=69', // 랜덤 이미지 69
            'https://picsum.photos/400/300?random=70', // 랜덤 이미지 70
            'https://picsum.photos/400/300?random=71', // 랜덤 이미지 71
            'https://picsum.photos/400/300?random=72', // 랜덤 이미지 72
            'https://picsum.photos/400/300?random=73', // 랜덤 이미지 73
            'https://picsum.photos/400/300?random=74', // 랜덤 이미지 74
            'https://picsum.photos/400/300?random=75', // 랜덤 이미지 75
            'https://picsum.photos/400/300?random=76', // 랜덤 이미지 76
            'https://picsum.photos/400/300?random=77', // 랜덤 이미지 77
            'https://picsum.photos/400/300?random=78', // 랜덤 이미지 78
            'https://picsum.photos/400/300?random=79', // 랜덤 이미지 79
            'https://picsum.photos/400/300?random=80', // 랜덤 이미지 80
            'https://picsum.photos/400/300?random=81', // 랜덤 이미지 81
            'https://picsum.photos/400/300?random=82', // 랜덤 이미지 82
            'https://picsum.photos/400/300?random=83', // 랜덤 이미지 83
            'https://picsum.photos/400/300?random=84', // 랜덤 이미지 84
            'https://picsum.photos/400/300?random=85', // 랜덤 이미지 85
            'https://picsum.photos/400/300?random=86', // 랜덤 이미지 86
            'https://picsum.photos/400/300?random=87', // 랜덤 이미지 87
            'https://picsum.photos/400/300?random=88', // 랜덤 이미지 88
            'https://picsum.photos/400/300?random=89', // 랜덤 이미지 89
            'https://picsum.photos/400/300?random=90', // 랜덤 이미지 90
            'https://picsum.photos/400/300?random=91', // 랜덤 이미지 91
            'https://picsum.photos/400/300?random=92', // 랜덤 이미지 92
            'https://picsum.photos/400/300?random=93', // 랜덤 이미지 93
            'https://picsum.photos/400/300?random=94', // 랜덤 이미지 94
            'https://picsum.photos/400/300?random=95', // 랜덤 이미지 95
            'https://picsum.photos/400/300?random=96', // 랜덤 이미지 96
            'https://picsum.photos/400/300?random=97', // 랜덤 이미지 97
            'https://picsum.photos/400/300?random=98', // 랜덤 이미지 98
            'https://picsum.photos/400/300?random=99', // 랜덤 이미지 99
            'https://picsum.photos/400/300?random=100', // 랜덤 이미지 100
        ];
        
        // 로컬 이미지와 외부 이미지를 합침
        $this->images = array_merge($this->images, $additionalImages);
        
        // 이미지가 없을 경우 기본 이미지 추가
        if (empty($this->images)) {
            $this->images = ['images/default.jpg'];
        }
    }

    private function loadLocalImages() {
        // 로컬 이미지 디렉토리에서 이미지들을 로드
        $imageDir = 'images/';
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (is_dir($imageDir)) {
            $files = scandir($imageDir);
            foreach ($files as $file) {
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($extension, $allowedExtensions)) {
                    $this->images[] = $imageDir . $file;
                }
            }
        }
    }

    private function loadExternalImages() {
        // Unsplash API를 사용해서 더 많은 이미지를 가져옴
        $categories = ['nature', 'animals', 'landscape', 'flowers', 'trees', 'mountains', 'ocean', 'forest', 'sky', 'clouds'];
        $this->images = [];
        
        foreach ($categories as $category) {
            for ($i = 0; $i < 10; $i++) {
                $this->images[] = "https://source.unsplash.com/400x300/?{$category}&sig=" . ($i + 1);
            }
        }
    }

    public function render() {
        $html = '<main class="flex-1">';
        $html .= '<div class="container mx-auto px-4 py-8">';
        $html .= '<div class="max-w-2xl mx-auto space-y-8">';
        
        // Header with back button and toggle
        $html .= '<div class="flex items-center justify-between mb-6">';
        $html .= '<a href="../../../index.php" class="inline-flex items-center gap-2 text-sm font-medium hover:text-primary transition-colors">';
        $html .= '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>';
        $html .= 'Back to Home';
        $html .= '</a>';
        
        // Toggle button for external images
        $html .= '<button id="toggleImages" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium bg-secondary text-secondary-foreground rounded-lg hover:bg-secondary/80 transition-colors">';
        $html .= '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-cw"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M3 21v-5h5"/></svg>';
        $html .= 'Load More Images';
        $html .= '</button>';
        $html .= '</div>';

        // Slideshow container
        $html .= '<div class="bg-card text-card-foreground rounded-lg border p-6 space-y-6">';
        $html .= '<div class="relative aspect-video overflow-hidden rounded-lg bg-muted flex items-center justify-center">';
        $html .= '<img id="slideImage" src="' . $this->images[0] . '" alt="Slideshow" class="max-w-full max-h-full w-auto h-auto object-contain transition-opacity duration-500" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';">';
        $html .= '<div id="imageError" class="hidden absolute inset-0 flex items-center justify-center text-muted-foreground">';
        $html .= '<div class="text-center">';
        $html .= '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mb-2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>';
        $html .= '<p class="text-sm">이미지를 불러올 수 없습니다</p>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $localCount = count(array_filter($this->images, function($img) { return !str_starts_with($img, 'http'); }));
        $onlineCount = count($this->images) - $localCount;
        $html .= '<div id="speedDisplay" class="text-sm text-muted-foreground text-center">Speed: ' . $this->displayDuration . 'ms</div>';
        $html .= '<div id="imageCount" class="text-sm text-muted-foreground text-center font-semibold">📸 Total Images: <span class="text-primary">' . count($this->images) . '</span> | Local: <span class="text-green-600">' . $localCount . '</span> | Online: <span class="text-blue-600">' . $onlineCount . '</span></div>';
        $html .= '<div id="loadingStatus" class="text-sm text-muted-foreground text-center">🔄 Loading images... | Total: ' . count($this->images) . ' images</div>';
        
        // Control buttons
        $html .= '<div class="flex items-center justify-center gap-4">';
        $html .= '<button id="slowerButton" class="p-2 rounded-lg hover:bg-accent transition-colors">';
        $html .= '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-minus"><line x1="5" x2="19" y1="12" y2="12"/></svg>';
        $html .= '</button>';
        $html .= '<button id="controlButton" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors">Stop</button>';
        $html .= '<button id="fasterButton" class="p-2 rounded-lg hover:bg-accent transition-colors">';
        $html .= '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>';
        $html .= '</button>';
        $html .= '</div>';
        
        $html .= '</div>'; // End slideshow container
        $html .= '</div>'; // End max-w-2xl container
        $html .= '</div>'; // End container
        $html .= '</main>'; // End main

        // Add JavaScript
        $html .= $this->getJavaScript();

        return $html;
    }

    private function getJavaScript() {
        $images = json_encode($this->images);
        $displayDuration = $this->displayDuration;

        return "
        <script>
        // 전역 변수로 이미지와 설정 전달
        window.slideshowImages = {$images};
        window.slideshowDuration = {$displayDuration};
        </script>
        <script src=\"js/slideshow.js\"></script>";
    }
} 