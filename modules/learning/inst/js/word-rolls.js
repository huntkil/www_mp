class WordRolls {
    constructor() {
        this.currentPostIndex = 0;
        this.posts = [];
        this.autoAdvanceTimeout = null;
        this.startY = 0;
        this.startX = 0;

        this.init();
    }

    async init() {
        await this.fetchPosts();
        this.setupEventListeners();
    }

    async fetchPosts() {
        try {
            const response = await fetch('fetch_posts.php');
            this.posts = await response.json();
            if (this.posts.length > 0) {
                this.displayPost(0);
            } else {
                document.getElementById('post').innerHTML = '<div class="post-content"><h2>No posts available.</h2></div>';
            }
        } catch (error) {
            document.getElementById('post').innerHTML = '<div class="post-content"><h2>Error loading posts.</h2></div>';
        }
    }

    displayPost(index) {
        const postElement = document.getElementById('post');
        const progressElement = document.getElementById('progress');

        postElement.style.transform = 'translateY(0)';
        postElement.style.opacity = '0';
        setTimeout(() => {
            const post = this.posts[index];
            const firstLetter = post.word.charAt(0).toUpperCase();
            
            postElement.innerHTML = `
                <div class="post-header">
                    <div class="avatar">${firstLetter}</div>
                    <div class="username">Vocabulary Card</div>
                </div>
                <div class="post-content">
                    <h2>${post.word}</h2>
                    <p>${post.meaning}</p>
                </div>
                <div class="post-footer">
                    <button class="action-button" onclick="this.parentElement.parentElement.style.transform = 'translateY(-100%)'">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 5v14M5 12h14"/>
                        </svg>
                    </button>
                    <button class="action-button" onclick="this.parentElement.parentElement.style.transform = 'translateY(100%)'">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 19V5M5 12h14"/>
                        </svg>
                    </button>
                </div>
            `;
            postElement.style.transform = 'translateY(0)';
            postElement.style.opacity = '1';
        }, 300);

        // Reset progress bar
        progressElement.style.transition = 'none';
        progressElement.style.width = '0';
        setTimeout(() => {
            progressElement.style.transition = 'width 3s linear';
            progressElement.style.width = '100%';
        }, 10);

        // Reset auto-advance timer
        clearTimeout(this.autoAdvanceTimeout);
        this.autoAdvanceTimeout = setTimeout(() => {
            if (this.currentPostIndex < this.posts.length - 1) {
                this.currentPostIndex++;
            } else {
                this.currentPostIndex = 0;
            }
            this.displayPost(this.currentPostIndex);
        }, 3000);
    }

    handleSwipe(endY, endX) {
        const deltaY = endY - this.startY;
        const deltaX = endX - this.startX;

        if (Math.abs(deltaY) > Math.abs(deltaX)) {
            const postElement = document.getElementById('post');
            if (deltaY > 50 && this.currentPostIndex > 0) {
                postElement.style.transform = 'translateY(100%)';
                postElement.style.opacity = '0';
                setTimeout(() => {
                    this.currentPostIndex--;
                    this.displayPost(this.currentPostIndex);
                }, 300);
            } else if (deltaY < -50 && this.currentPostIndex < this.posts.length - 1) {
                postElement.style.transform = 'translateY(-100%)';
                postElement.style.opacity = '0';
                setTimeout(() => {
                    this.currentPostIndex++;
                    this.displayPost(this.currentPostIndex);
                }, 300);
            }
        }
    }

    setupEventListeners() {
        // Touch events
        document.addEventListener('touchstart', (e) => {
            this.startY = e.touches[0].clientY;
            this.startX = e.touches[0].clientX;
        });

        document.addEventListener('touchend', (e) => {
            this.handleSwipe(
                e.changedTouches[0].clientY,
                e.changedTouches[0].clientX
            );
        });

        // Mouse events
        document.addEventListener('mousedown', (e) => {
            this.startY = e.clientY;
            this.startX = e.clientX;
        });

        document.addEventListener('mouseup', (e) => {
            this.handleSwipe(e.clientY, e.clientX);
        });
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new WordRolls();
}); 