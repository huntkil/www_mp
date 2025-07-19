<?php
$current_page = basename($_SERVER['PHP_SELF']);
$nav = NavigationHelper::getInstance();

// 인증 상태 확인
$auth = new \System\Includes\Auth();
$isLoggedIn = $auth->isLoggedIn();
$currentUser = $isLoggedIn ? $auth->getCurrentUser() : null;
?>

<nav class="border-b bg-white shadow-sm">
    <div class="container mx-auto px-4">
        <div class="flex h-16 items-center justify-between">
            <a href="<?php echo $nav->getHomeUrl(); ?>" class="text-xl font-bold text-gray-900">MP Learning</a>
            
            <!-- Mobile menu button -->
            <button type="button" class="md:hidden p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100" 
                    onclick="document.getElementById('mobile-menu').classList.toggle('hidden')">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-menu">
                    <line x1="4" x2="20" y1="12" y2="12"/>
                    <line x1="4" x2="20" y1="6" y2="6"/>
                    <line x1="4" x2="20" y1="18" y2="18"/>
                </svg>
            </button>

            <!-- Desktop menu -->
            <div class="hidden md:flex md:items-center md:space-x-6">
                <a href="<?php echo $nav->getHomeUrl(); ?>" 
                   class="text-sm font-medium <?php echo $current_page === 'index.php' ? 'text-indigo-600' : 'text-gray-600 hover:text-indigo-600'; ?> transition-colors">
                    홈
                </a>
                <a href="<?php echo $nav->getModuleUrl('learning/card/slideshow.php'); ?>" 
                   class="text-sm font-medium <?php echo strpos($current_page, 'slideshow') !== false ? 'text-indigo-600' : 'text-gray-600 hover:text-indigo-600'; ?> transition-colors">
                    학습
                </a>
                <a href="<?php echo $nav->getModuleUrl('management/crud/data_list.php'); ?>" 
                   class="text-sm font-medium <?php echo strpos($current_page, 'crud') !== false ? 'text-indigo-600' : 'text-gray-600 hover:text-indigo-600'; ?> transition-colors">
                    관리
                </a>
                
                <!-- 인증 상태에 따른 메뉴 -->
                <?php if($isLoggedIn && $currentUser): ?>
                    <!-- 사용자 드롭다운 메뉴 -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center space-x-2 text-sm font-medium text-gray-600 hover:text-indigo-600 transition-colors">
                            <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                                <span class="text-indigo-600 font-semibold text-sm">
                                    <?php echo strtoupper(substr($currentUser['username'], 0, 1)); ?>
                                </span>
                            </div>
                            <span><?php echo htmlspecialchars($currentUser['username']); ?></span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" 
                             class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                            <a href="/auth/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                프로필
                            </a>
                            <a href="/auth/change-password" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                비밀번호 변경
                            </a>
                            <hr class="my-1">
                            <a href="/auth/logout" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                로그아웃
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="flex items-center space-x-4">
                        <a href="/auth/login" class="text-sm font-medium text-gray-600 hover:text-indigo-600 transition-colors">
                            로그인
                        </a>
                        <a href="/auth/register" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700 transition-colors">
                            회원가입
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Mobile menu -->
        <div id="mobile-menu" class="hidden md:hidden py-4 space-y-4 border-t">
            <a href="<?php echo $nav->getHomeUrl(); ?>" class="block text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">홈</a>
            <a href="<?php echo $nav->getModuleUrl('learning/card/slideshow.php'); ?>" class="block text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">학습</a>
            <a href="<?php echo $nav->getModuleUrl('management/crud/data_list.php'); ?>" class="block text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">관리</a>
            
            <?php if($isLoggedIn && $currentUser): ?>
                <hr class="border-gray-200">
                <div class="px-4 py-2">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                            <span class="text-indigo-600 font-semibold text-sm">
                                <?php echo strtoupper(substr($currentUser['username'], 0, 1)); ?>
                            </span>
                        </div>
                        <span class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($currentUser['username']); ?></span>
                    </div>
                </div>
                <a href="/auth/profile" class="block px-4 py-2 text-sm text-gray-600 hover:text-gray-900 transition-colors">프로필</a>
                <a href="/auth/change-password" class="block px-4 py-2 text-sm text-gray-600 hover:text-gray-900 transition-colors">비밀번호 변경</a>
                <hr class="border-gray-200">
                <a href="/auth/logout" class="block px-4 py-2 text-sm text-red-600 hover:text-red-700 transition-colors">로그아웃</a>
            <?php else: ?>
                <hr class="border-gray-200">
                <a href="/auth/login" class="block px-4 py-2 text-sm text-gray-600 hover:text-gray-900 transition-colors">로그인</a>
                <a href="/auth/register" class="block px-4 py-2 text-sm text-indigo-600 hover:text-indigo-700 transition-colors">회원가입</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Alpine.js for dropdown functionality -->
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script> 