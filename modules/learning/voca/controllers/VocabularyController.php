<?php
/**
 * Vocabulary Controller
 * 단어장 관리 기능
 */

require_once __DIR__ . '/../../../../system/includes/config.php';
require_once __DIR__ . '/../models/Vocabulary.php';

class VocabularyController extends Controller {
    private $vocabularyModel;
    
    public function __construct() {
        parent::__construct();
        $this->vocabularyModel = new Vocabulary();
    }
    
    /**
     * 단어 목록 조회
     */
    public function index() {
        try {
            $userId = $this->session->getUserId() ?: 'admin'; // 임시로 admin 사용
            $page = (int)($_GET['page'] ?? 1);
            $perPage = (int)($_GET['per_page'] ?? 25);
            $search = $_GET['search'] ?? '';
            $sortBy = $_GET['sort'] ?? 'created_at DESC';
            
            if ($search) {
                $result = $this->vocabularyModel->searchByUserId($userId, $search, $page, $perPage);
            } else {
                $result = $this->vocabularyModel->getByUserId($userId, $page, $perPage, $sortBy);
            }
            
            if ($this->isAjax()) {
                $this->successResponse($result);
            }
            
            return $result;
            
        } catch (Exception $e) {
            if ($this->isAjax()) {
                $this->errorResponse($e->getMessage());
            }
            throw $e;
        }
    }
    
    /**
     * 단어 추가
     */
    public function store() {
        try {
            $this->validateCSRF();
            
            $data = $this->getPostData();
            $userId = $this->session->getUserId() ?: 'admin';
            
            // 입력 검증
            $validator = $this->validate($data, [
                'word' => 'required|max_length:255',
                'meaning' => 'required|max_length:1000',
                'example' => 'max_length:2000',
                'language' => 'in:en,ko,jp,cn,fr,de,es',
                'difficulty' => 'in:easy,medium,hard'
            ]);
            
            if ($validator->fails()) {
                if ($this->isAjax()) {
                    $this->errorResponse('Validation failed', 422, $validator->getErrors());
                }
                return ['success' => false, 'errors' => $validator->getErrors()];
            }
            
            // 중복 확인
            if ($this->vocabularyModel->isDuplicate($userId, $data['word'])) {
                if ($this->isAjax()) {
                    $this->errorResponse('Word already exists');
                }
                return ['success' => false, 'message' => 'Word already exists'];
            }
            
            // 데이터 준비
            $vocabularyData = [
                'user_id' => $userId,
                'word' => trim($data['word']),
                'meaning' => trim($data['meaning']),
                'example' => trim($data['example'] ?? ''),
                'language' => $data['language'] ?? 'en',
                'difficulty' => $data['difficulty'] ?? 'medium',
                'learned' => false
            ];
            
            $result = $this->vocabularyModel->create($vocabularyData);
            
            if ($result) {
                if ($this->isAjax()) {
                    $this->successResponse($result, 'Word added successfully');
                }
                return ['success' => true, 'data' => $result];
            } else {
                if ($this->isAjax()) {
                    $this->errorResponse('Failed to add word');
                }
                return ['success' => false, 'message' => 'Failed to add word'];
            }
            
        } catch (Exception $e) {
            if ($this->isAjax()) {
                $this->errorResponse($e->getMessage());
            }
            throw $e;
        }
    }
    
    /**
     * 단어 수정
     */
    public function update($id) {
        try {
            $this->validateCSRF();
            
            $data = $this->getPostData();
            $userId = $this->session->getUserId() ?: 'admin';
            
            // 입력 검증
            $validator = $this->validate($data, [
                'word' => 'required|max_length:255',
                'meaning' => 'required|max_length:1000',
                'example' => 'max_length:2000',
                'language' => 'in:en,ko,jp,cn,fr,de,es',
                'difficulty' => 'in:easy,medium,hard'
            ]);
            
            if ($validator->fails()) {
                if ($this->isAjax()) {
                    $this->errorResponse('Validation failed', 422, $validator->getErrors());
                }
                return ['success' => false, 'errors' => $validator->getErrors()];
            }
            
            // 중복 확인 (자신 제외)
            if ($this->vocabularyModel->isDuplicate($userId, $data['word'], $id)) {
                if ($this->isAjax()) {
                    $this->errorResponse('Word already exists');
                }
                return ['success' => false, 'message' => 'Word already exists'];
            }
            
            // 데이터 준비
            $vocabularyData = [
                'word' => trim($data['word']),
                'meaning' => trim($data['meaning']),
                'example' => trim($data['example'] ?? ''),
                'language' => $data['language'] ?? 'en',
                'difficulty' => $data['difficulty'] ?? 'medium'
            ];
            
            $result = $this->vocabularyModel->updateByIdAndUserId($id, $userId, $vocabularyData);
            
            if ($result) {
                if ($this->isAjax()) {
                    $this->successResponse($result, 'Word updated successfully');
                }
                return ['success' => true, 'data' => $result];
            } else {
                if ($this->isAjax()) {
                    $this->errorResponse('Failed to update word');
                }
                return ['success' => false, 'message' => 'Failed to update word'];
            }
            
        } catch (Exception $e) {
            if ($this->isAjax()) {
                $this->errorResponse($e->getMessage());
            }
            throw $e;
        }
    }
    
    /**
     * 단어 삭제
     */
    public function destroy($id) {
        try {
            $this->validateCSRF();
            
            $userId = $this->session->getUserId() ?: 'admin';
            
            $result = $this->vocabularyModel->deleteByIdAndUserId($id, $userId);
            
            if ($result) {
                if ($this->isAjax()) {
                    $this->successResponse(null, 'Word deleted successfully');
                }
                return ['success' => true];
            } else {
                if ($this->isAjax()) {
                    $this->errorResponse('Failed to delete word');
                }
                return ['success' => false, 'message' => 'Failed to delete word'];
            }
            
        } catch (Exception $e) {
            if ($this->isAjax()) {
                $this->errorResponse($e->getMessage());
            }
            throw $e;
        }
    }
    
    /**
     * 학습 상태 토글
     */
    public function toggleLearned($id) {
        try {
            $userId = $this->session->getUserId() ?: 'admin';
            
            $result = $this->vocabularyModel->toggleLearned($id, $userId);
            
            if ($result) {
                if ($this->isAjax()) {
                    $this->successResponse($result, 'Learning status updated');
                }
                return ['success' => true, 'data' => $result];
            } else {
                if ($this->isAjax()) {
                    $this->errorResponse('Failed to update learning status');
                }
                return ['success' => false, 'message' => 'Failed to update learning status'];
            }
            
        } catch (Exception $e) {
            if ($this->isAjax()) {
                $this->errorResponse($e->getMessage());
            }
            throw $e;
        }
    }
    
    /**
     * 통계 조회
     */
    public function stats() {
        try {
            $userId = $this->session->getUserId() ?: 'admin';
            
            $stats = [
                'total' => $this->vocabularyModel->getTotalByUserId($userId),
                'learned' => $this->vocabularyModel->getLearnedCountByUserId($userId),
                'this_week' => $this->vocabularyModel->getThisWeekCountByUserId($userId),
                'streak' => $this->vocabularyModel->getLearningStreakByUserId($userId),
                'difficulty_stats' => $this->vocabularyModel->getDifficultyStatsByUserId($userId),
                'language_stats' => $this->vocabularyModel->getLanguageStatsByUserId($userId),
                'monthly_stats' => $this->vocabularyModel->getMonthlyStatsByUserId($userId)
            ];
            
            if ($this->isAjax()) {
                $this->successResponse($stats);
            }
            
            return ['success' => true, 'data' => $stats];
            
        } catch (Exception $e) {
            if ($this->isAjax()) {
                $this->errorResponse($e->getMessage());
            }
            throw $e;
        }
    }
    
    /**
     * 단어 내보내기
     */
    public function export() {
        try {
            $userId = $this->session->getUserId() ?: 'admin';
            $format = $_GET['format'] ?? 'json';
            
            $vocabulary = $this->vocabularyModel->getByUserId($userId, 1, 10000); // 모든 단어
            
            switch ($format) {
                case 'csv':
                    $this->exportToCSV($vocabulary['data']);
                    break;
                case 'json':
                default:
                    $this->exportToJSON($vocabulary['data']);
                    break;
            }
            
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
    
    /**
     * CSV 내보내기
     */
    private function exportToCSV($data) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="vocabulary_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // BOM for UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // 헤더
        fputcsv($output, ['Word', 'Meaning', 'Example', 'Language', 'Difficulty', 'Learned', 'Created At']);
        
        // 데이터
        foreach ($data as $row) {
            fputcsv($output, [
                $row['word'],
                $row['meaning'],
                $row['example'],
                $row['language'],
                $row['difficulty'],
                $row['learned'] ? 'Yes' : 'No',
                $row['created_at']
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * JSON 내보내기
     */
    private function exportToJSON($data) {
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="vocabulary_' . date('Y-m-d') . '.json"');
        
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
} 