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
            try {
                // SentenceManager 사용
                require_once __DIR__ . '/SentenceManager.php';
                $manager = new SentenceManager('ko');
                $sentences = $manager->getAllSentences();
                
                // 문장 텍스트만 추출
                $this->sentences = array_column($sentences, 'text');
            } catch (Exception $e) {
                // 오류 발생 시 기본 문장 사용
                error_log("SentenceManager 오류: " . $e->getMessage());
                $this->sentences = [
                    "꽃이 핀다",
                    "바람이 분다",
                    "비가 온다",
                    "눈이 온다",
                    "해가 뜬다"
                ];
            }
        } else {
            try {
                // SentenceManager 사용 (영어)
                require_once __DIR__ . '/SentenceManager.php';
                $manager = new SentenceManager('en');
                $sentences = $manager->getAllSentences();
                
                // 문장 텍스트만 추출
                $this->sentences = array_column($sentences, 'text');
            } catch (Exception $e) {
                // 오류 발생 시 기본 문장 사용
                error_log("SentenceManager 오류: " . $e->getMessage());
                $this->sentences = [
                    "Rain falls",
                    "Snow melts",
                    "Wind blows",
                    "Sun shines",
                    "Moon rises"
                ];
            }
        }
    }

    public function render() {
        $html = '<main class="flex-1">';
        $html .= '<div class="container mx-auto px-4 py-8">';
        $html .= '<div class="max-w-2xl mx-auto space-y-8">';
        
        // Header with back button and manage button
        $html .= '<div class="flex items-center justify-between mb-6">';
        $html .= '<a href="../../../index.php" class="inline-flex items-center gap-2 text-sm font-medium hover:text-primary transition-colors">';
        $html .= '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>';
        $html .= 'Back to Home';
        $html .= '</a>';
        
        // Language toggle and manage button
        $html .= '<div class="flex items-center gap-3">';
        
        // Language toggle button
        $otherLang = $this->language === 'ko' ? 'en' : 'ko';
        $otherLangText = $this->language === 'ko' ? 'English' : '한국어';
        $otherLangFile = $this->language === 'ko' ? 'wordcard_en.php' : 'wordcard_ko.php';
        $html .= '<a href="' . $otherLangFile . '" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium bg-secondary text-secondary-foreground rounded-lg hover:bg-secondary/80 transition-colors">';
        $html .= '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-languages"><path d="m5 8 6 6"/><path d="m4 14 6-6 2-3"/><path d="M2 5h12"/><path d="M7 2h1"/><path d="m22 22-5-10-5 10"/><path d="M14 18h6"/></svg>';
        $html .= $otherLangText;
        $html .= '</a>';
        
        // Statistics
        $html .= '<div class="text-sm text-muted-foreground">';
        $html .= '<span class="font-medium">' . count($this->sentences) . '</span>' . ($this->language === 'ko' ? '개 문장' : ' sentences');
        $html .= '</div>';
        
        // Manage button
        $html .= '<a href="sentence_manager.php?lang=' . $this->language . '" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors">';
        $html .= '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-settings"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>';
        $html .= ($this->language === 'ko' ? '문장 관리' : 'Manage Sentences');
        $html .= '</a>';
        $html .= '</div>';
        $html .= '</div>';

        // Word card container
        $html .= '<div class="bg-card text-card-foreground rounded-lg border p-6 space-y-6">';
        $html .= '<div id="sentence" class="text-2xl font-medium text-center min-h-[2.5rem]">Loading...</div>';
        $html .= '<div id="counter" class="text-sm text-muted-foreground text-center">1 / ' . count($this->sentences) . '</div>';
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
        $html .= '</div>'; // End max-w-2xl container
        $html .= '</div>'; // End container
        $html .= '</main>'; // End main

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
            const counterElement = document.getElementById('counter');
            const controlButton = document.getElementById('controlButton');
            const fasterButton = document.getElementById('fasterButton');
            const slowerButton = document.getElementById('slowerButton');
            const speedDisplay = document.getElementById('speedDisplay');

            function updateSpeedDisplay() {
                speedDisplay.textContent = `Speed: {$displayDuration}ms`;
            }

            function showNextSentence() {
                sentenceElement.textContent = sentences[index];
                counterElement.textContent = (index + 1) + ' / ' + sentences.length;
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