<?php
/**
 * Vocabulary API
 * 단어장 RESTful API 엔드포인트
 */

require_once __DIR__ . '/../system/includes/config.php';
require_once __DIR__ . '/../system/includes/Router.php';
require_once __DIR__ . '/../modules/learning/voca/controllers/VocabularyController.php';

// CORS 헤더 설정
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN');
header('Content-Type: application/json; charset=utf-8');

// OPTIONS 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 라우터 설정
$router = new Router();

// Vocabulary API 라우트
group('/api/vocabulary', function($router) {
    // 단어 목록 조회
    $router->get('/', function() {
        $controller = new VocabularyController();
        return $controller->index();
    });
    
    // 단어 추가
    $router->post('/', function() {
        $controller = new VocabularyController();
        return $controller->store();
    });
    
    // 단어 수정
    $router->put('/{id}', function($id) {
        $controller = new VocabularyController();
        return $controller->update($id);
    });
    
    // 단어 삭제
    $router->delete('/{id}', function($id) {
        $controller = new VocabularyController();
        return $controller->destroy($id);
    });
    
    // 학습 상태 토글
    $router->patch('/{id}/toggle', function($id) {
        $controller = new VocabularyController();
        return $controller->toggleLearned($id);
    });
    
    // 통계 조회
    $router->get('/stats', function() {
        $controller = new VocabularyController();
        return $controller->stats();
    });
    
    // 단어 내보내기
    $router->get('/export', function() {
        $controller = new VocabularyController();
        return $controller->export();
    });
});

// 라우터 실행
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// /mp/api/vocabulary 경로에서 /api/vocabulary로 변환
$uri = preg_replace('#^/mp#', '', $uri);

try {
    $router->dispatch($method, $uri);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => [
            'message' => $e->getMessage(),
            'code' => 500
        ]
    ], JSON_UNESCAPED_UNICODE);
} 