<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// OPTIONS 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../components/SentenceManager.php';

try {
    // 언어 파라미터 처리
    $language = $_GET['lang'] ?? 'ko';
    $manager = new SentenceManager($language);
    
    // 필터 파라미터 처리
    $filters = [];
    
    if (isset($_GET['category']) && !empty($_GET['category'])) {
        $filters['category'] = $_GET['category'];
    }
    
    if (isset($_GET['difficulty']) && !empty($_GET['difficulty'])) {
        $filters['difficulty'] = $_GET['difficulty'];
    }
    
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $filters['search'] = $_GET['search'];
    }
    
    // 페이지네이션 파라미터
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
    
    // 유효성 검사
    if ($page < 1) $page = 1;
    if ($limit < 1 || $limit > 100) $limit = 20;
    
    // 모든 문장 조회
    $allSentences = $manager->getAllSentences($filters);
    $totalCount = count($allSentences);
    
    // 페이지네이션 적용
    $offset = ($page - 1) * $limit;
    $sentences = array_slice($allSentences, $offset, $limit);
    
    // 통계 정보
    $statistics = $manager->getStatistics();
    
    // 응답 데이터 구성
    $response = [
        'success' => true,
        'data' => [
            'sentences' => $sentences,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total_count' => $totalCount,
                'total_pages' => ceil($totalCount / $limit),
                'has_next' => ($page * $limit) < $totalCount,
                'has_prev' => $page > 1
            ],
            'filters' => $filters,
            'statistics' => $statistics,
            'categories' => $manager->getCategories(),
            'difficulties' => $manager->getDifficulties()
        ],
        'message' => '문장 목록을 성공적으로 조회했습니다.'
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'message' => '문장 목록 조회 중 오류가 발생했습니다.'
    ], JSON_UNESCAPED_UNICODE);
} 