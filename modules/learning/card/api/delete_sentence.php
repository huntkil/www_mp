<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// OPTIONS 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// DELETE 요청만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method Not Allowed',
        'message' => 'DELETE 메서드만 허용됩니다.'
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

require_once '../components/SentenceManager.php';

try {
    // JSON 입력 읽기
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('잘못된 JSON 형식입니다.');
    }
    
    // 언어 파라미터 처리
    $language = $_GET['lang'] ?? 'ko';
    $manager = new SentenceManager($language);
    
    // 단일 삭제 또는 다중 삭제 처리
    if (isset($input['ids']) && is_array($input['ids'])) {
        // 다중 삭제
        if (empty($input['ids'])) {
            throw new Exception('삭제할 문장 ID를 지정해주세요.');
        }
        
        $deletedCount = $manager->deleteMultipleSentences($input['ids']);
        
        $response = [
            'success' => true,
            'data' => [
                'deleted_count' => $deletedCount,
                'requested_count' => count($input['ids']),
                'statistics' => $manager->getStatistics()
            ],
            'message' => "{$deletedCount}개의 문장이 성공적으로 삭제되었습니다."
        ];
        
    } elseif (isset($input['id'])) {
        // 단일 삭제
        $id = (int)$input['id'];
        
        if ($id <= 0) {
            throw new Exception('유효하지 않은 문장 ID입니다.');
        }
        
        $manager->deleteSentence($id);
        
        $response = [
            'success' => true,
            'data' => [
                'deleted_id' => $id,
                'statistics' => $manager->getStatistics()
            ],
            'message' => '문장이 성공적으로 삭제되었습니다.'
        ];
        
    } else {
        throw new Exception('삭제할 문장 ID를 지정해주세요.');
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'message' => '문장 삭제 중 오류가 발생했습니다.'
    ], JSON_UNESCAPED_UNICODE);
} 