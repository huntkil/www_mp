<?php
class Slideshow {
    private $images;
    private $displayDuration;
    private $isRunning;

    public function __construct() {
        $this->displayDuration = 2000;
        $this->isRunning = true;
        $this->loadImages();
    }

    private function loadImages() {
        // images 디렉토리의 모든 이미지 파일을 가져옴
        $imageDir = __DIR__ . '/../images/';
        $files = scandir($imageDir);
        $this->images = [];
        
        foreach ($files as $file) {
            // .jpeg, .jpg, .png 파일만 포함
            if (preg_match('/\.(jpeg|jpg|png)$/i', $file)) {
                $this->images[] = 'images/' . $file;
            }
        }
        
        // 이미지가 없을 경우 기본 이미지 추가
        if (empty($this->images)) {
            $this->images = ['images/default.jpg'];
        }
    }

    public function render() {
        $html = '<div class="container mx-auto px-4 py-8">';
        $html .= '<div class="max-w-2xl mx-auto space-y-8">';
        
        // Header with back button
        $html .= '<div class="flex items-center justify-between mb-6">';
        $html .= '<a href="../../../index.php" class="inline-flex items-center gap-2 text-sm font-medium hover:text-primary transition-colors">';
        $html .= '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>';
        $html .= 'Back to Home';
        $html .= '</a>';
        $html .= '</div>';

        // Slideshow container
        $html .= '<div class="bg-card text-card-foreground rounded-lg border p-6 space-y-6">';
        $html .= '<div class="relative aspect-video overflow-hidden rounded-lg bg-muted flex items-center justify-center">';
        $html .= '<img id="slideImage" src="' . $this->images[0] . '" alt="Slideshow" class="max-w-full max-h-full w-auto h-auto object-contain transition-opacity duration-500">';
        $html .= '</div>';
        $html .= '<div id="speedDisplay" class="text-sm text-muted-foreground text-center">Speed: ' . $this->displayDuration . 'ms</div>';
        $html .= '<div id="imageCount" class="text-sm text-muted-foreground text-center">Total Images: ' . count($this->images) . '</div>';
        
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
        $html .= '</div>'; // End container
        $html .= '</div>'; // End main container

        // Add JavaScript
        $html .= $this->getJavaScript();

        return $html;
    }

    private function getJavaScript() {
        $images = json_encode($this->images);
        $displayDuration = $this->displayDuration;

        return "
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const images = {$images};
            let displayDuration = {$displayDuration};
            let index = 0;
            let intervalId;
            let isRunning = true;

            const slideImage = document.getElementById('slideImage');
            const controlButton = document.getElementById('controlButton');
            const fasterButton = document.getElementById('fasterButton');
            const slowerButton = document.getElementById('slowerButton');
            const speedDisplay = document.getElementById('speedDisplay');

            function updateSpeedDisplay() {
                speedDisplay.textContent = `Speed: {$displayDuration}ms`;
            }

            function showNextImage() {
                slideImage.style.opacity = '0';
                setTimeout(() => {
                    slideImage.src = images[index];
                    slideImage.style.opacity = '1';
                    index = (index + 1) % images.length;
                }, 500);
            }

            function startRotation() {
                intervalId = setInterval(showNextImage, displayDuration);
                showNextImage();
            }

            function stopRotation() {
                clearInterval(intervalId);
            }

            function toggleRotation() {
                if (isRunning) {
                    stopRotation();
                    controlButton.textContent = 'Start';
                } else {
                    startRotation();
                    controlButton.textContent = 'Stop';
                }
                isRunning = !isRunning;
            }

            function increaseSpeed() {
                if (displayDuration > 250) {
                    displayDuration -= 250;
                    stopRotation();
                    if (isRunning) startRotation();
                    updateSpeedDisplay();
                } else {
                    alert('Speed is already at maximum!');
                }
            }

            function decreaseSpeed() {
                displayDuration += 250;
                stopRotation();
                if (isRunning) startRotation();
                updateSpeedDisplay();
            }

            controlButton.addEventListener('click', toggleRotation);
            fasterButton.addEventListener('click', increaseSpeed);
            slowerButton.addEventListener('click', decreaseSpeed);

            updateSpeedDisplay();
            startRotation();
        });
        </script>";
    }
} 