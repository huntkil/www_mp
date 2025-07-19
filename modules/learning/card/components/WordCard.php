<?php
class WordCard {
    private $language;
    private $sentences;
    private $displayDuration;
    private $isRunning;

    public function __construct($language = 'en') {
        $this->language = $language;
        $this->displayDuration = 2000;
        $this->isRunning = true;
        $this->loadSentences();
    }

    private function loadSentences() {
        if ($this->language === 'ko') {
            $this->sentences = [
                "꽃이 피었다.",
                "새가 날았다.",
                "비가 온다.",
                "바람이 분다.",
                "해가 떴다.",
                "달이 떴다.",
                "별이 빛난다.",
                "구름이 낀다.",
                "눈이 온다.",
                "안개가 낀다."
            ];
        } else {
            $this->sentences = [
                "Birds sing.",
                "Rain falls.",
                "Sun shines.",
                "Wind blows.",
                "Flowers bloom.",
                "Stars twinkle.",
                "Clouds drift.",
                "Moon rises.",
                "Snow falls.",
                "Fog rolls in."
            ];
        }
    }

    public function render() {
        $html = '<div class="container mx-auto px-4 py-8">';
        $html .= '<div class="max-w-2xl mx-auto space-y-8">';
        
        // Back button - Calculate relative path to index.php
        $html .= '<a href="../../../index.php" class="inline-flex items-center gap-2 text-sm font-medium hover:text-primary transition-colors">';
        $html .= '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>';
        $html .= 'Back to Home';
        $html .= '</a>';

        // Word card container
        $html .= '<div class="bg-card text-card-foreground rounded-lg border p-6 space-y-6">';
        $html .= '<div id="sentence" class="text-2xl font-medium text-center min-h-[2.5rem]">Loading...</div>';
        $html .= '<div id="speedDisplay" class="text-sm text-muted-foreground text-center">Speed: ' . $this->displayDuration . 'ms</div>';
        
        // Control buttons
        $html .= '<div class="flex items-center justify-center gap-4">';
        $html .= '<button id="slowerButton" class="p-2 rounded-lg hover:bg-accent transition-colors">';
        $html .= '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-minus"><line x1="5" x2="19" y1="12" y2="12"/></svg>';
        $html .= '</button>';
        $html .= '<button id="controlButton" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors">';
        $html .= $this->language === 'ko' ? '정지' : 'Stop';
        $html .= '</button>';
        $html .= '<button id="fasterButton" class="p-2 rounded-lg hover:bg-accent transition-colors">';
        $html .= '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>';
        $html .= '</button>';
        $html .= '</div>';
        
        $html .= '</div>'; // End word card container
        $html .= '</div>'; // End container
        $html .= '</div>'; // End main container

        // Add JavaScript
        $html .= $this->getJavaScript();

        return $html;
    }

    private function getJavaScript() {
        $sentences = json_encode($this->sentences);
        $startText = $this->language === 'ko' ? '시작' : 'Start';
        $stopText = $this->language === 'ko' ? '정지' : 'Stop';
        $maxSpeedText = $this->language === 'ko' ? '이미 최대 속도입니다!' : 'Speed is already at maximum!';
        $displayDuration = $this->displayDuration;

        return "
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sentences = {$sentences};
            let displayDuration = {$displayDuration};
            let index = 0;
            let intervalId;
            let isRunning = true;

            const sentenceElement = document.getElementById('sentence');
            const controlButton = document.getElementById('controlButton');
            const fasterButton = document.getElementById('fasterButton');
            const slowerButton = document.getElementById('slowerButton');
            const speedDisplay = document.getElementById('speedDisplay');

            function updateSpeedDisplay() {
                speedDisplay.textContent = `Speed: {$displayDuration}ms`;
            }

            function showNextSentence() {
                sentenceElement.textContent = sentences[index];
                index = (index + 1) % sentences.length;
            }

            function startRotation() {
                intervalId = setInterval(showNextSentence, displayDuration);
                showNextSentence();
            }

            function stopRotation() {
                clearInterval(intervalId);
            }

            function toggleRotation() {
                if (isRunning) {
                    stopRotation();
                    controlButton.textContent = '{$startText}';
                } else {
                    startRotation();
                    controlButton.textContent = '{$stopText}';
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
                    alert('{$maxSpeedText}');
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