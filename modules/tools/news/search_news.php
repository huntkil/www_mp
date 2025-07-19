<?php
session_start();
require "../../../system/includes/config.php";

$page_title = "News Search Results";
include "../../../system/includes/header.php";

// 샘플 뉴스 데이터 생성 함수
function createSampleNewsData($query = '', $action = '') {
    $sampleArticles = [
        (object)[
            'title' => '인공지능 기술 발전, 일상에 새로운 변화 가져와',
            'description' => '최근 AI 기술의 급속한 발전이 우리 일상생활에 큰 변화를 가져오고 있습니다. 의료, 교육, 교통 등 다양한 분야에서 AI가 활용되면서...',
            'url' => '#',
            'urlToImage' => 'https://images.unsplash.com/photo-1677442136019-21780ecad995?w=800&h=400&fit=crop',
            'publishedAt' => date('c', strtotime('-2 hours')),
            'source' => (object)['name' => 'Tech News']
        ],
        (object)[
            'title' => '친환경 에너지 전환, 글로벌 트렌드로 확산',
            'description' => '전 세계적으로 재생에너지로의 전환이 가속화되고 있습니다. 태양광, 풍력 등 친환경 에너지원에 대한 투자가 크게 증가하면서...',
            'url' => '#',
            'urlToImage' => 'https://images.unsplash.com/photo-1473341304170-971dccb5ac1e?w=800&h=400&fit=crop',
            'publishedAt' => date('c', strtotime('-4 hours')),
            'source' => (object)['name' => 'Green Today']
        ],
        (object)[
            'title' => '스마트 시티 구축, 미래 도시의 새로운 모습',
            'description' => 'IoT와 빅데이터를 활용한 스마트 시티 프로젝트가 전 세계적으로 확산되고 있습니다. 교통, 에너지, 안전 등 모든 분야에서...',
            'url' => '#',
            'urlToImage' => 'https://images.unsplash.com/photo-1480714378408-67cf0d13bc1f?w=800&h=400&fit=crop',
            'publishedAt' => date('c', strtotime('-6 hours')),
            'source' => (object)['name' => 'City Life']
        ],
        (object)[
            'title' => '우주 탐사 기술 혁신, 화성 여행 현실로',
            'description' => '민간 우주 기업들의 기술 혁신으로 화성 탐사가 점점 현실에 가까워지고 있습니다. 재사용 로켓 기술과 우주 정거장 건설...',
            'url' => '#',
            'urlToImage' => 'https://images.unsplash.com/photo-1446776653964-20c1d3a81b06?w=800&h=400&fit=crop',
            'publishedAt' => date('c', strtotime('-8 hours')),
            'source' => (object)['name' => 'Space Explorer']
        ],
        (object)[
            'title' => '바이오테크놀로지 혁신, 의료 패러다임 변화',
            'description' => '유전자 치료와 정밀 의학의 발전으로 난치병 치료에 새로운 희망이 보이고 있습니다. 개인 맞춤형 치료가 현실화되면서...',
            'url' => '#',
            'urlToImage' => 'https://images.unsplash.com/photo-1559757148-5c350d0d3c56?w=800&h=400&fit=crop',
            'publishedAt' => date('c', strtotime('-10 hours')),
            'source' => (object)['name' => 'Medical Times']
        ]
    ];

    // 검색어가 있으면 관련 기사만 필터링
    if (!empty($query) && $action !== '전체 기사') {
        $filteredArticles = [];
        foreach ($sampleArticles as $article) {
            if (stripos($article->title, $query) !== false || stripos($article->description, $query) !== false) {
                $filteredArticles[] = $article;
            }
        }
        $sampleArticles = $filteredArticles ?: [$sampleArticles[0]]; // 결과가 없으면 첫 번째 기사라도 보여줌
    }

    return (object)[
        'status' => 'ok',
        'totalResults' => count($sampleArticles),
        'articles' => $sampleArticles
    ];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // 입력값 검증 및 정리
        $query = trim($_POST['query'] ?? '');
        $country = trim($_POST['country'] ?? 'kr');
        $category = trim($_POST['category'] ?? '');
        $sortBy = trim($_POST['sortBy'] ?? 'publishedAt');
        $action = trim($_POST['action'] ?? '');
        
        // 입력값 검증
        if (empty($action)) {
            throw new Exception("Invalid action parameter.");
        }
        
        // API 키 확인 및 데이터 처리
        if (empty(NEWS_API_KEY) || NEWS_API_KEY === 'your_news_api_key_here') {
            // API 키가 없을 때 샘플 데이터 사용
            $newsData = createSampleNewsData($query, $action);
            $isDemo = true;
        } else {
            // 실제 API 사용
            $isDemo = false;
            
            // 허용된 값들 검증
            $allowed_countries = ['', 'kr', 'us', 'jp', 'cn', 'gb', 'uk', 'de', 'fr', 'ca', 'au', 'in'];
            $allowed_categories = ['', 'general', 'business', 'entertainment', 'health', 'science', 'sports', 'technology'];
            $allowed_sortBy = ['publishedAt', 'relevancy', 'popularity'];
            
            if (!in_array($country, $allowed_countries)) {
                $country = 'kr';
            }
            
            if (!in_array($category, $allowed_categories)) {
                $category = '';
            }
            
            if (!in_array($sortBy, $allowed_sortBy)) {
                $sortBy = 'publishedAt';
            }
            
            // API URL 생성
            $apiKey = NEWS_API_KEY;
            
            if ($action == '전체 기사') {
                $url = "https://newsapi.org/v2/top-headlines?";
                if (!empty($country)) {
                    $url .= "country=" . $country . "&";
                }
                if (!empty($category)) {
                    $url .= "category=" . $category . "&";
                }
                $url .= "sortBy=" . $sortBy . "&apiKey=" . $apiKey;
            } else {
                if (empty($query)) {
                    throw new Exception("Search query is required.");
                }
                
                if (strlen($query) > 100) {
                    throw new Exception("Search query is too long.");
                }
                
                if (empty($country)) {
                    $url = "https://newsapi.org/v2/everything?q=" . urlencode($query) . "&sortBy=" . $sortBy . "&apiKey=" . $apiKey;
                } else {
                    $url = "https://newsapi.org/v2/top-headlines?country=" . $country . "&q=" . urlencode($query);
                    if (!empty($category)) {
                        $url .= "&category=" . $category;
                    }
                    $url .= "&sortBy=" . $sortBy . "&apiKey=" . $apiKey;
                }
            }

            // cURL 요청
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_USERAGENT, 'My Playground News Reader 1.0');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 3);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if (curl_errno($ch)) {
                $error_msg = curl_error($ch);
                curl_close($ch);
                throw new Exception("Network error: " . $error_msg);
            }
            
            curl_close($ch);
            
            if ($httpCode !== 200) {
                throw new Exception("API request failed with status code: " . $httpCode);
            }

            $newsData = json_decode($response);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Invalid JSON response: " . json_last_error_msg());
            }
            
            if (isset($newsData->status) && $newsData->status === 'error') {
                throw new Exception("API Error: " . ($newsData->message ?? 'Unknown error'));
            }
        }
        
        // 로그 기록
        if (IS_LOCAL) {
            error_log("News search: Query='{$query}', Country='{$country}', Results=" . count($newsData->articles ?? []) . ", Demo=" . ($isDemo ? 'Yes' : 'No'));
        }
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
        error_log("News search error: " . $error_message);
        
        if (!IS_LOCAL) {
            $error_message = "뉴스를 불러오는 중 오류가 발생했습니다. 나중에 다시 시도해주세요.";
        }
    }
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-5xl mx-auto space-y-8">
        <?php if (isset($error_message)): ?>
            <div class="bg-card text-card-foreground rounded-lg border p-6">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-10 h-10 bg-red-500/10 rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-red-500">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" x2="12" y1="8" y2="12"/>
                            <line x1="12" x2="12.01" y1="16" y2="16"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-lg mb-2">오류 발생</h3>
                        <p class="text-muted-foreground mb-4"><?php echo htmlspecialchars($error_message); ?></p>
                        <a href="search_news_form.php" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors font-medium">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="m12 19-7-7 7-7"/>
                                <path d="M19 12H5"/>
                            </svg>
                            검색 페이지로 돌아가기
                        </a>
                    </div>
                </div>
            </div>
        <?php elseif (!empty($newsData->articles)): ?>
            <?php if (isset($isDemo) && $isDemo): ?>
                <div class="bg-card text-card-foreground rounded-lg border p-6">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-10 h-10 bg-blue-500/10 rounded-lg flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blue-500">
                                <circle cx="12" cy="12" r="10"/>
                                <path d="M12 16v-4"/>
                                <path d="M12 8h.01"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-lg mb-2">📰 데모 모드</h3>
                            <p class="text-muted-foreground">
                                News API 키가 설정되지 않아 샘플 데이터를 표시합니다. 
                                실제 뉴스 데이터를 보려면 <a href="https://newsapi.org/register" target="_blank" class="underline font-medium hover:text-blue-600">News API</a>에서 무료 API 키를 받아 config.php에 설정하세요.
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- 검색 결과 헤더 -->
            <div class="bg-card text-card-foreground rounded-lg border p-6">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div class="flex-1">
                        <h1 class="text-2xl font-bold mb-2">
                            <?php if ($action == '전체 기사'): ?>
                                🌍 전체 기사 <span class="text-blue-500">(<?php echo strtoupper($country); ?>)</span>
                            <?php else: ?>
                                🔍 검색 결과: <span class="text-blue-500">"<?php echo htmlspecialchars($query); ?>"</span>
                            <?php endif; ?>
                            <?php if (isset($isDemo) && $isDemo): ?>
                                <span class="text-sm font-normal text-blue-500 ml-2">(데모 데이터)</span>
                            <?php endif; ?>
                        </h1>
                        <p class="text-muted-foreground flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <?php echo count($newsData->articles); ?>개의 기사를 찾았습니다
                        </p>
                    </div>
                    <a href="search_news_form.php" 
                       class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        새로운 검색
                    </a>
                </div>
            </div>

            <!-- 뉴스 기사 그리드 -->
            <div class="grid gap-6">
                <?php foreach ($newsData->articles as $index => $article): 
                    $publishedAt = date('Y-m-d H:i', strtotime($article->publishedAt));
                    $sourceName = htmlspecialchars($article->source->name ?? 'Unknown');
                    $imageUrl = filter_var($article->urlToImage ?? '', FILTER_VALIDATE_URL);
                    $title = htmlspecialchars($article->title ?? '');
                    $description = htmlspecialchars($article->description ?? '');
                    $url = filter_var($article->url ?? '', FILTER_VALIDATE_URL);
                    $isFirstArticle = $index === 0;
                ?>
                    <article class="group bg-card text-card-foreground rounded-lg border overflow-hidden hover:shadow-lg transition-all duration-200 hover:scale-[1.01]">
                        <?php if ($imageUrl): ?>
                            <div class="relative overflow-hidden h-48 md:h-64">
                                <img src="<?php echo $imageUrl; ?>" 
                                     alt="<?php echo $title; ?>"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                     loading="lazy"
                                     onerror="this.parentElement.style.display='none'">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                                <div class="absolute bottom-4 left-4 right-4">
                                    <div class="flex items-center gap-2 text-white text-sm font-medium">
                                        <span class="bg-white/20 backdrop-blur-sm rounded-full px-3 py-1">
                                            📰 <?php echo $sourceName; ?>
                                        </span>
                                        <span class="bg-white/20 backdrop-blur-sm rounded-full px-3 py-1">
                                            🕒 <?php echo $publishedAt; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="p-6 space-y-4">
                            <div class="space-y-3">
                                <h2 class="text-xl font-bold leading-tight group-hover:text-blue-500 transition-colors duration-200">
                                    <?php echo $title; ?>
                                </h2>
                                
                                <?php if ($description): ?>
                                    <p class="text-muted-foreground leading-relaxed">
                                        <?php echo $description; ?>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <?php if (!$imageUrl): ?>
                                <div class="flex items-center gap-4 pt-2 border-t">
                                    <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2ZM12 6a4 4 0 1 1-4 4 4 4 0 0 1 4-4Z"/>
                                        </svg>
                                        <time datetime="<?php echo $article->publishedAt; ?>">
                                            <?php echo $publishedAt; ?>
                                        </time>
                                    </div>
                                    <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M8 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2h-3"/>
                                            <path d="M9 3h6v4H9V3Z"/>
                                        </svg>
                                        <span><?php echo $sourceName; ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="flex items-center justify-between pt-4">
                                <?php if ($url && $url !== '#'): ?>
                                    <a href="<?php echo $url; ?>" 
                                       target="_blank" 
                                       rel="noopener noreferrer"
                                       class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors font-medium">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                                            <path d="M15 3h6v6"/>
                                            <path d="M10 14L21 3"/>
                                        </svg>
                                        기사 읽기
                                    </a>
                                <?php else: ?>
                                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-muted text-muted-foreground rounded-lg font-medium">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"/>
                                            <path d="M12 16v-4"/>
                                            <path d="M12 8h.01"/>
                                        </svg>
                                        데모 데이터
                                    </div>
                                <?php endif; ?>
                                
                                <div class="flex items-center gap-2">
                                    <button class="p-2 text-muted-foreground hover:text-red-500 hover:bg-red-500/10 rounded-lg transition-colors" title="좋아요">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.29 1.51 4.04 3 5.5l7 7Z"/>
                                        </svg>
                                    </button>
                                    <button class="p-2 text-muted-foreground hover:text-blue-500 hover:bg-blue-500/10 rounded-lg transition-colors" title="공유">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/>
                                            <polyline points="16,6 12,2 8,6"/>
                                            <line x1="12" x2="12" y1="2" y2="15"/>
                                        </svg>
                                    </button>
                                    <button class="p-2 text-muted-foreground hover:text-green-500 hover:bg-green-500/10 rounded-lg transition-colors" title="북마크">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2Z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            
            <!-- 하단 액션 버튼 -->
            <div class="bg-card text-card-foreground rounded-lg border p-6">
                <div class="flex flex-col sm:flex-row items-center gap-4">
                    <div class="text-center sm:text-left">
                        <h3 class="font-semibold mb-1">더 많은 뉴스를 찾고 계신가요?</h3>
                        <p class="text-sm text-muted-foreground">다른 검색어로 최신 뉴스를 찾아보세요</p>
                    </div>
                    <a href="search_news_form.php" 
                       class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        새로운 검색
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-card text-card-foreground rounded-lg border p-8 text-center">
                <div class="max-w-md mx-auto space-y-6">
                    <div class="mx-auto w-20 h-20 bg-blue-500/10 rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blue-500">
                            <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <div class="space-y-2">
                        <h2 class="text-2xl font-bold">검색 결과가 없습니다</h2>
                        <p class="text-muted-foreground leading-relaxed">
                            검색 조건을 조정하거나 다른 키워드로 검색해보세요.<br>
                            더 광범위한 검색을 위해 다른 카테고리나 국가를 선택해보실 수도 있습니다.
                        </p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <a href="search_news_form.php" 
                           class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors font-medium">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            다시 검색하기
                        </a>
                        <a href="/" 
                           class="inline-flex items-center gap-2 px-4 py-2 bg-secondary text-secondary-foreground rounded-lg hover:bg-secondary/90 transition-colors font-medium">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                <polyline points="9,22 9,12 15,12 15,22"/>
                            </svg>
                            홈으로
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
} else {
    // POST 요청이 아닌 경우 검색 페이지로 리다이렉트
    header('Location: search_news_form.php');
    exit;
}

include "../../../system/includes/footer.php";
?>