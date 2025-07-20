<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// OPTIONS 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// PUT 요청만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method Not Allowed',
        'message' => 'PUT 메서드만 허용됩니다.'
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
    
    // 필수 필드 검증
    if (empty($input['id'])) {
        throw new Exception('문장 ID는 필수입니다.');
    }
    
    if (empty($input['text'])) {
        throw new Exception('문장 내용은 필수입니다.');
    }
    
    $id = (int)$input['id'];
    $text = trim($input['text']);
    $category = isset($input['category']) ? trim($input['category']) : null;
    $difficulty = isset($input['difficulty']) ? trim($input['difficulty']) : null;
    
    // 입력 길이 검증
    if (mb_strlen($text) > 200) {
        throw new Exception('문장은 200자를 초과할 수 없습니다.');
    }
    
    // 언어 파라미터 처리
    $language = $_GET['lang'] ?? 'ko';
    $manager = new SentenceManager($language);
    
    // 문장 수정
    $updatedSentence = $manager->updateSentence($id, $text, $category, $difficulty);
    
    // 응답 데이터 구성
    $response = [
        'success' => true,
        'data' => [
            'sentence' => $updatedSentence,
            'statistics' => $manager->getStatistics()
        ],
        'message' => '문장이 성공적으로 수정되었습니다.'
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'message' => '문장 수정 중 오류가 발생했습니다.'
    ], JSON_UNESCAPED_UNICODE);
} 