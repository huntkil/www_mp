<?php

class SentenceManager {
    private $dataFile;
    private $data;
    private $language;

    public function __construct($language = 'ko') {
        $this->language = $language;
        $this->dataFile = __DIR__ . "/../data/sentences_{$language}.json";
        $this->loadData();
    }

    /**
     * JSON 파일에서 데이터 로드
     */
    private function loadData() {
        if (!file_exists($this->dataFile)) {
            // 파일이 없으면 기본 구조 생성
            $this->data = [
                'sentences' => [],
                'categories' => $this->language === 'ko' ? 
                    ['자연', '동물', '식물', '일상', '사람', '물리', '신체', '감정', '계절', '시간', '전자기기', '음식'] :
                    ['nature', 'animals', 'objects', 'people', 'food', 'technology', 'emotions', 'time', 'seasons', 'body'],
                'difficulties' => $this->language === 'ko' ? 
                    ['초급', '중급', '고급'] :
                    ['easy', 'medium', 'hard'],
                'metadata' => [
                    'total_sentences' => 0,
                    'last_updated' => date('Y-m-d H:i:s'),
                    'version' => '1.0'
                ]
            ];
            $this->saveData();
        } else {
            $jsonContent = file_get_contents($this->dataFile);
            $jsonData = json_decode($jsonContent, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('JSON 파일 파싱 오류: ' . json_last_error_msg());
            }
            
            // 영어 파일은 직접 배열 형태, 한국어 파일은 sentences 키가 있는 형태
            if ($this->language === 'en' && is_array($jsonData) && !isset($jsonData['sentences'])) {
                // 영어 파일: 직접 배열 형태를 기존 구조로 변환
                $this->data = [
                    'sentences' => array_map(function($item) {
                        return [
                            'id' => $item['id'],
                            'text' => $item['sentence'], // sentence를 text로 변환
                            'category' => $item['category'],
                            'difficulty' => $item['difficulty'],
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ];
                    }, $jsonData),
                    'categories' => ['nature', 'animals', 'objects', 'people', 'food', 'technology', 'emotions', 'time', 'seasons', 'body'],
                    'difficulties' => ['easy', 'medium', 'hard'],
                    'metadata' => [
                        'total_sentences' => count($jsonData),
                        'last_updated' => date('Y-m-d H:i:s'),
                        'version' => '1.0'
                    ]
                ];
            } else {
                // 한국어 파일: 기존 구조 그대로 사용
                $this->data = $jsonData;
            }
        }
    }

    /**
     * 데이터를 JSON 파일에 저장
     */
    private function saveData() {
        $this->data['metadata']['last_updated'] = date('Y-m-d H:i:s');
        $this->data['metadata']['total_sentences'] = count($this->data['sentences']);
        
        $jsonContent = json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON 인코딩 오류: ' . json_last_error_msg());
        }
        
        if (file_put_contents($this->dataFile, $jsonContent) === false) {
            throw new Exception('파일 저장 실패');
        }
    }

    /**
     * 모든 문장 조회
     */
    public function getAllSentences($filters = []) {
        $sentences = $this->data['sentences'];
        
        // 필터 적용
        if (!empty($filters['category'])) {
            $sentences = array_filter($sentences, function($sentence) use ($filters) {
                return $sentence['category'] === $filters['category'];
            });
        }
        
        if (!empty($filters['difficulty'])) {
            $sentences = array_filter($sentences, function($sentence) use ($filters) {
                return $sentence['difficulty'] === $filters['difficulty'];
            });
        }
        
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $sentences = array_filter($sentences, function($sentence) use ($search) {
                return stripos($sentence['text'], $search) !== false;
            });
        }
        
        return array_values($sentences);
    }

    /**
     * ID로 문장 조회
     */
    public function getSentenceById($id) {
        foreach ($this->data['sentences'] as $sentence) {
            if ($sentence['id'] == $id) {
                return $sentence;
            }
        }
        return null;
    }

    /**
     * 문장 추가
     */
    public function addSentence($text, $category = '일반', $difficulty = '초급') {
        // 입력 검증
        if (empty(trim($text))) {
            throw new Exception('문장 내용을 입력해주세요.');
        }
        
        if (!in_array($category, $this->data['categories'])) {
            throw new Exception('유효하지 않은 카테고리입니다.');
        }
        
        if (!in_array($difficulty, $this->data['difficulties'])) {
            throw new Exception('유효하지 않은 난이도입니다.');
        }
        
        // 새 ID 생성
        $maxId = 0;
        foreach ($this->data['sentences'] as $sentence) {
            if ($sentence['id'] > $maxId) {
                $maxId = $sentence['id'];
            }
        }
        
        $newSentence = [
            'id' => $maxId + 1,
            'text' => trim($text),
            'category' => $category,
            'difficulty' => $difficulty,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $this->data['sentences'][] = $newSentence;
        $this->saveData();
        
        return $newSentence;
    }

    /**
     * 문장 수정
     */
    public function updateSentence($id, $text, $category = null, $difficulty = null) {
        $sentence = $this->getSentenceById($id);
        if (!$sentence) {
            throw new Exception('문장을 찾을 수 없습니다.');
        }
        
        // 입력 검증
        if (empty(trim($text))) {
            throw new Exception('문장 내용을 입력해주세요.');
        }
        
        if ($category && !in_array($category, $this->data['categories'])) {
            throw new Exception('유효하지 않은 카테고리입니다.');
        }
        
        if ($difficulty && !in_array($difficulty, $this->data['difficulties'])) {
            throw new Exception('유효하지 않은 난이도입니다.');
        }
        
        // 문장 업데이트
        foreach ($this->data['sentences'] as &$s) {
            if ($s['id'] == $id) {
                $s['text'] = trim($text);
                if ($category) $s['category'] = $category;
                if ($difficulty) $s['difficulty'] = $difficulty;
                $s['updated_at'] = date('Y-m-d H:i:s');
                break;
            }
        }
        
        $this->saveData();
        
        return $this->getSentenceById($id);
    }

    /**
     * 문장 삭제
     */
    public function deleteSentence($id) {
        $sentence = $this->getSentenceById($id);
        if (!$sentence) {
            throw new Exception('문장을 찾을 수 없습니다.');
        }
        
        $this->data['sentences'] = array_filter($this->data['sentences'], function($s) use ($id) {
            return $s['id'] != $id;
        });
        
        $this->saveData();
        
        return true;
    }

    /**
     * 여러 문장 삭제
     */
    public function deleteMultipleSentences($ids) {
        $deletedCount = 0;
        
        foreach ($ids as $id) {
            try {
                $this->deleteSentence($id);
                $deletedCount++;
            } catch (Exception $e) {
                // 개별 삭제 실패는 로그만 남기고 계속 진행
                error_log("문장 삭제 실패 (ID: $id): " . $e->getMessage());
            }
        }
        
        return $deletedCount;
    }

    /**
     * 카테고리 목록 조회
     */
    public function getCategories() {
        return $this->data['categories'];
    }

    /**
     * 난이도 목록 조회
     */
    public function getDifficulties() {
        return $this->data['difficulties'];
    }

    /**
     * 통계 정보 조회
     */
    public function getStatistics() {
        $stats = [
            'total' => count($this->data['sentences']),
            'by_category' => [],
            'by_difficulty' => []
        ];
        
        foreach ($this->data['categories'] as $category) {
            $stats['by_category'][$category] = 0;
        }
        
        foreach ($this->data['difficulties'] as $difficulty) {
            $stats['by_difficulty'][$difficulty] = 0;
        }
        
        foreach ($this->data['sentences'] as $sentence) {
            $stats['by_category'][$sentence['category']]++;
            $stats['by_difficulty'][$sentence['difficulty']]++;
        }
        
        return $stats;
    }

    /**
     * 데이터 백업
     */
    public function backup() {
        $backupFile = $this->dataFile . '.backup.' . date('Y-m-d-H-i-s');
        return copy($this->dataFile, $backupFile);
    }

    /**
     * 데이터 복원
     */
    public function restore($backupFile) {
        if (!file_exists($backupFile)) {
            throw new Exception('백업 파일을 찾을 수 없습니다.');
        }
        
        $backupData = json_decode(file_get_contents($backupFile), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('백업 파일이 손상되었습니다.');
        }
        
        $this->data = $backupData;
        $this->saveData();
        
        return true;
    }

    /**
     * 데이터 내보내기
     */
    public function export($format = 'json') {
        switch ($format) {
            case 'json':
                return json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            case 'csv':
                $csv = "ID,문장,카테고리,난이도,생성일,수정일\n";
                foreach ($this->data['sentences'] as $sentence) {
                    $csv .= sprintf(
                        "%d,\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
                        $sentence['id'],
                        $sentence['text'],
                        $sentence['category'],
                        $sentence['difficulty'],
                        $sentence['created_at'],
                        $sentence['updated_at']
                    );
                }
                return $csv;
            default:
                throw new Exception('지원하지 않는 형식입니다.');
        }
    }

    /**
     * 데이터 가져오기
     */
    public function import($data, $format = 'json') {
        switch ($format) {
            case 'json':
                $importData = json_decode($data, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('JSON 형식이 올바르지 않습니다.');
                }
                break;
            case 'csv':
                $importData = $this->parseCsv($data);
                break;
            default:
                throw new Exception('지원하지 않는 형식입니다.');
        }
        
        // 백업 생성
        $this->backup();
        
        // 데이터 병합
        $this->mergeData($importData);
        
        return true;
    }

    /**
     * CSV 파싱
     */
    private function parseCsv($csvData) {
        $lines = explode("\n", trim($csvData));
        $headers = str_getcsv(array_shift($lines));
        
        $sentences = [];
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            
            $row = str_getcsv($line);
            if (count($row) >= 6) {
                $sentences[] = [
                    'id' => (int)$row[0],
                    'text' => $row[1],
                    'category' => $row[2],
                    'difficulty' => $row[3],
                    'created_at' => $row[4],
                    'updated_at' => $row[5]
                ];
            }
        }
        
        return ['sentences' => $sentences];
    }

    /**
     * 데이터 병합
     */
    private function mergeData($importData) {
        if (isset($importData['sentences'])) {
            $existingIds = array_column($this->data['sentences'], 'id');
            
            foreach ($importData['sentences'] as $sentence) {
                if (!in_array($sentence['id'], $existingIds)) {
                    $this->data['sentences'][] = $sentence;
                }
            }
        }
        
        if (isset($importData['categories'])) {
            $this->data['categories'] = array_unique(array_merge($this->data['categories'], $importData['categories']));
        }
        
        if (isset($importData['difficulties'])) {
            $this->data['difficulties'] = array_unique(array_merge($this->data['difficulties'], $importData['difficulties']));
        }
        
        $this->saveData();
    }
} 