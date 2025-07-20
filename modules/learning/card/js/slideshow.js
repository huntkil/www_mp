document.addEventListener('DOMContentLoaded', function() {
    let images = window.slideshowImages || [];
    let displayDuration = window.slideshowDuration || 2000;
    let index = 0;
    let intervalId;
    let isRunning = true;

    const slideImage = document.getElementById('slideImage');
    const controlButton = document.getElementById('controlButton');
    const fasterButton = document.getElementById('fasterButton');
    const slowerButton = document.getElementById('slowerButton');
    const speedDisplay = document.getElementById('speedDisplay');
    const imageCount = document.getElementById('imageCount');
    const toggleButton = document.getElementById('toggleImages');
    const loadingStatus = document.getElementById('loadingStatus');

    function updateSpeedDisplay() {
        speedDisplay.textContent = 'Speed: ' + displayDuration + 'ms';
    }

    function updateImageCount() {
        const localCount = images.filter(img => !img.startsWith('http')).length;
        const onlineCount = images.length - localCount;
        imageCount.innerHTML = '📸 Total Images: <span class="text-primary">' + images.length + '</span> | Local: <span class="text-green-600">' + localCount + '</span> | Online: <span class="text-blue-600">' + onlineCount + '</span>';
    }

    function showNextImage() {
        slideImage.style.opacity = '0';
        setTimeout(() => {
            // 이미지 로드 시도
            const img = new Image();
            img.onload = function() {
                slideImage.src = images[index];
                slideImage.style.opacity = '1';
                slideImage.style.display = 'block';
                const errorDiv = document.getElementById('imageError');
                if (errorDiv) errorDiv.style.display = 'none';
                index = (index + 1) % images.length;
                
                // 로딩 상태 업데이트
                if (loadingStatus) {
                    loadingStatus.textContent = '🖼️ Image ' + index + ' of ' + images.length + ' | ⚡ Speed: ' + displayDuration + 'ms';
                }
            };
            img.onerror = function() {
                // 이미지 로드 실패 시 다음 이미지로 건너뛰기
                console.warn('Failed to load image:', images[index]);
                index = (index + 1) % images.length;
                showNextImage(); // 재귀적으로 다음 이미지 시도
            };
            img.src = images[index];
        }, 200);
    }

    function startRotation() {
        intervalId = setInterval(showNextImage, displayDuration);
        showNextImage();
        
        // 이미지 프리로딩 시작
        preloadImages();
        
        // 로딩 상태 숨기기
        if (loadingStatus) {
            setTimeout(() => {
                loadingStatus.style.display = 'none';
            }, 2000);
        }
    }
    
    function preloadImages() {
        // 다음 10개 이미지를 미리 로드 (오류 처리 포함)
        for (let i = 0; i < Math.min(10, images.length); i++) {
            const img = new Image();
            img.onerror = function() {
                console.warn('Failed to preload image:', images[i]);
            };
            img.src = images[i];
        }
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

    async function loadMoreImages() {
        const newImages = [];
        
        // 50개의 추가 Picsum 이미지 생성
        for (let i = 0; i < 50; i++) {
            const timestamp = Date.now() + i;
            newImages.push('https://picsum.photos/400/300?random=' + timestamp);
        }
        
        // 기존 이미지에 새 이미지 추가
        images = [...images, ...newImages];
        updateImageCount();
        
        // 버튼 텍스트 업데이트
        toggleButton.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check"><path d="M20 6 9 17l-5-5"/></svg>Images Loaded';
        setTimeout(() => {
            toggleButton.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-cw"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M3 21v-5h5"/></svg>Load More Images';
        }, 2000);
    }

    controlButton.addEventListener('click', toggleRotation);
    fasterButton.addEventListener('click', increaseSpeed);
    slowerButton.addEventListener('click', decreaseSpeed);
    toggleButton.addEventListener('click', loadMoreImages);

    updateSpeedDisplay();
    updateImageCount();
    startRotation();
});
