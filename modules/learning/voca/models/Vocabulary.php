<?php
/**
 * Vocabulary Model
 * 단어장 데이터 관리
 */

class Vocabulary extends Model {
    protected $table = 'vocabulary';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id', 'word', 'meaning', 'example', 'language', 'difficulty', 'learned'
    ];
    protected $casts = [
        'learned' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * 사용자별 단어 목록 조회
     */
    public function getByUserId($userId, $page = 1, $perPage = 25, $orderBy = 'created_at DESC') {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY {$orderBy} LIMIT ? OFFSET ?";
        $data = $this->db->select($sql, [$userId, $perPage, $offset]);
        
        $total = $this->getTotalByUserId($userId);
        
        return [
            'data' => $data,
            'total' => $total,
            'current_page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }
    
    /**
     * 사용자별 총 단어 수 조회
     */
    public function getTotalByUserId($userId) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE user_id = ?";
        $result = $this->db->selectOne($sql, [$userId]);
        return $result['count'] ?? 0;
    }
    
    /**
     * 사용자별 학습된 단어 수 조회
     */
    public function getLearnedCountByUserId($userId) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE user_id = ? AND learned = 1";
        $result = $this->db->selectOne($sql, [$userId]);
        return $result['count'] ?? 0;
    }
    
    /**
     * 사용자별 이번 주 추가된 단어 수 조회
     */
    public function getThisWeekCountByUserId($userId) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE user_id = ? AND created_at >= DATE('now', 'weekday 0', '-6 days')";
        $result = $this->db->selectOne($sql, [$userId]);
        return $result['count'] ?? 0;
    }
    
    /**
     * 사용자별 학습 연속일수 조회
     */
    public function getLearningStreakByUserId($userId) {
        $sql = "SELECT COUNT(DISTINCT DATE(created_at)) as streak FROM {$this->table} 
                WHERE user_id = ? AND created_at >= DATE('now', '-30 days')";
        $result = $this->db->selectOne($sql, [$userId]);
        return $result['streak'] ?? 0;
    }
    
    /**
     * 사용자별 검색
     */
    public function searchByUserId($userId, $query, $page = 1, $perPage = 25) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = ? AND (word LIKE ? OR meaning LIKE ? OR example LIKE ?)
                ORDER BY created_at DESC LIMIT ? OFFSET ?";
        
        $searchTerm = "%{$query}%";
        $data = $this->db->select($sql, [$userId, $searchTerm, $searchTerm, $searchTerm, $perPage, $offset]);
        
        // 총 검색 결과 수 조회
        $countSql = "SELECT COUNT(*) as count FROM {$this->table} 
                     WHERE user_id = ? AND (word LIKE ? OR meaning LIKE ? OR example LIKE ?)";
        $totalResult = $this->db->selectOne($countSql, [$userId, $searchTerm, $searchTerm, $searchTerm]);
        $total = $totalResult['count'] ?? 0;
        
        return [
            'data' => $data,
            'total' => $total,
            'current_page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }
    
    /**
     * 학습 상태 토글
     */
    public function toggleLearned($id, $userId) {
        $sql = "UPDATE {$this->table} SET learned = NOT learned WHERE id = ? AND user_id = ?";
        $result = $this->db->update($sql, [$id, $userId]);
        
        if ($result) {
            return $this->getById($id);
        }
        
        return false;
    }
    
    /**
     * 난이도별 통계
     */
    public function getDifficultyStatsByUserId($userId) {
        $sql = "SELECT difficulty, COUNT(*) as count FROM {$this->table} 
                WHERE user_id = ? GROUP BY difficulty";
        return $this->db->select($sql, [$userId]);
    }
    
    /**
     * 언어별 통계
     */
    public function getLanguageStatsByUserId($userId) {
        $sql = "SELECT language, COUNT(*) as count FROM {$this->table} 
                WHERE user_id = ? GROUP BY language";
        return $this->db->select($sql, [$userId]);
    }
    
    /**
     * 월별 추가 통계
     */
    public function getMonthlyStatsByUserId($userId, $year = null) {
        $year = $year ?: date('Y');
        $sql = "SELECT strftime('%m', created_at) as month, COUNT(*) as count 
                FROM {$this->table} 
                WHERE user_id = ? AND strftime('%Y', created_at) = ?
                GROUP BY month ORDER BY month";
        return $this->db->select($sql, [$userId, $year]);
    }
    
    /**
     * 중복 단어 확인
     */
    public function isDuplicate($userId, $word, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE user_id = ? AND word = ?";
        $params = [$userId, $word];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->selectOne($sql, $params);
        return ($result['count'] ?? 0) > 0;
    }
    
    /**
     * 단어 삭제 (사용자 확인)
     */
    public function deleteByIdAndUserId($id, $userId) {
        $sql = "DELETE FROM {$this->table} WHERE id = ? AND user_id = ?";
        return $this->db->delete($sql, [$id, $userId]);
    }
    
    /**
     * 단어 업데이트 (사용자 확인)
     */
    public function updateByIdAndUserId($id, $userId, $data) {
        $data = $this->filterFillable($data);
        $data = $this->applyCasts($data);
        
        $fields = array_keys($data);
        $setClause = implode(' = ?, ', $fields) . ' = ?';
        
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE id = ? AND user_id = ?";
        
        $values = array_values($data);
        $values[] = $id;
        $values[] = $userId;
        
        $result = $this->db->update($sql, $values);
        
        if ($result) {
            return $this->getById($id);
        }
        
        return false;
    }
} 