<?php
/**
 * 기본 Model 클래스
 * 모든 모델의 공통 기능을 제공
 */

abstract class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $hidden = [];
    protected $casts = [];
    
    public function __construct($db = null) {
        $this->db = $db ?: Database::getInstance();
    }
    
    /**
     * 모든 레코드 조회
     */
    public function getAll($orderBy = null, $limit = null) {
        $sql = "SELECT * FROM {$this->table}";
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return $this->db->select($sql);
    }
    
    /**
     * 페이지네이션과 함께 조회
     */
    public function getPaginated($page = 1, $perPage = 10, $orderBy = null) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM {$this->table}";
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        $sql .= " LIMIT {$perPage} OFFSET {$offset}";
        
        $data = $this->db->select($sql);
        $total = $this->getTotal();
        
        return [
            'data' => $data,
            'total' => $total,
            'current_page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }
    
    /**
     * 총 레코드 수 조회
     */
    public function getTotal() {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $result = $this->db->selectOne($sql);
        return $result['count'] ?? 0;
    }
    
    /**
     * ID로 조회
     */
    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->selectOne($sql, [$id]);
    }
    
    /**
     * 조건으로 조회
     */
    public function getWhere($conditions, $orderBy = null, $limit = null) {
        $sql = "SELECT * FROM {$this->table} WHERE ";
        $params = [];
        
        $whereClauses = [];
        foreach ($conditions as $field => $value) {
            $whereClauses[] = "{$field} = ?";
            $params[] = $value;
        }
        
        $sql .= implode(' AND ', $whereClauses);
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * 조건으로 단일 레코드 조회
     */
    public function getWhereOne($conditions) {
        $sql = "SELECT * FROM {$this->table} WHERE ";
        $params = [];
        
        $whereClauses = [];
        foreach ($conditions as $field => $value) {
            $whereClauses[] = "{$field} = ?";
            $params[] = $value;
        }
        
        $sql .= implode(' AND ', $whereClauses) . " LIMIT 1";
        
        return $this->db->selectOne($sql, $params);
    }
    
    /**
     * 검색
     */
    public function search($query, $fields, $orderBy = null, $limit = null) {
        $sql = "SELECT * FROM {$this->table} WHERE ";
        $params = [];
        
        $searchClauses = [];
        foreach ($fields as $field) {
            $searchClauses[] = "{$field} LIKE ?";
            $params[] = "%{$query}%";
        }
        
        $sql .= "(" . implode(' OR ', $searchClauses) . ")";
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * 레코드 생성
     */
    public function create($data) {
        $data = $this->filterFillable($data);
        $data = $this->applyCasts($data);
        
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        $id = $this->db->insert($sql, array_values($data));
        
        if ($id) {
            return $this->getById($id);
        }
        
        return false;
    }
    
    /**
     * 레코드 업데이트
     */
    public function update($id, $data) {
        $data = $this->filterFillable($data);
        $data = $this->applyCasts($data);
        
        $fields = array_keys($data);
        $setClause = implode(' = ?, ', $fields) . ' = ?';
        
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = ?";
        
        $values = array_values($data);
        $values[] = $id;
        
        $result = $this->db->update($sql, $values);
        
        if ($result) {
            return $this->getById($id);
        }
        
        return false;
    }
    
    /**
     * 레코드 삭제
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->delete($sql, [$id]);
    }
    
    /**
     * 조건으로 삭제
     */
    public function deleteWhere($conditions) {
        $sql = "DELETE FROM {$this->table} WHERE ";
        $params = [];
        
        $whereClauses = [];
        foreach ($conditions as $field => $value) {
            $whereClauses[] = "{$field} = ?";
            $params[] = $value;
        }
        
        $sql .= implode(' AND ', $whereClauses);
        
        return $this->db->delete($sql, $params);
    }
    
    /**
     * fillable 필드만 필터링
     */
    protected function filterFillable($data) {
        if (empty($this->fillable)) {
            return $data;
        }
        
        return array_intersect_key($data, array_flip($this->fillable));
    }
    
    /**
     * 데이터 타입 캐스팅 적용
     */
    protected function applyCasts($data) {
        foreach ($this->casts as $field => $cast) {
            if (isset($data[$field])) {
                switch ($cast) {
                    case 'int':
                    case 'integer':
                        $data[$field] = (int) $data[$field];
                        break;
                    case 'float':
                    case 'double':
                        $data[$field] = (float) $data[$field];
                        break;
                    case 'bool':
                    case 'boolean':
                        $data[$field] = (bool) $data[$field];
                        break;
                    case 'string':
                        $data[$field] = (string) $data[$field];
                        break;
                    case 'array':
                        $data[$field] = is_string($data[$field]) ? json_decode($data[$field], true) : $data[$field];
                        break;
                    case 'json':
                        $data[$field] = is_string($data[$field]) ? $data[$field] : json_encode($data[$field]);
                        break;
                }
            }
        }
        
        return $data;
    }
    
    /**
     * 숨겨진 필드 제거
     */
    protected function hideFields($data) {
        if (empty($this->hidden)) {
            return $data;
        }
        
        return array_diff_key($data, array_flip($this->hidden));
    }
    
    /**
     * 레코드 존재 여부 확인
     */
    public function exists($id) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $result = $this->db->selectOne($sql, [$id]);
        return ($result['count'] ?? 0) > 0;
    }
    
    /**
     * 조건으로 존재 여부 확인
     */
    public function existsWhere($conditions) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE ";
        $params = [];
        
        $whereClauses = [];
        foreach ($conditions as $field => $value) {
            $whereClauses[] = "{$field} = ?";
            $params[] = $value;
        }
        
        $sql .= implode(' AND ', $whereClauses);
        
        $result = $this->db->selectOne($sql, $params);
        return ($result['count'] ?? 0) > 0;
    }
    
    /**
     * 최대값 조회
     */
    public function getMax($field) {
        $sql = "SELECT MAX({$field}) as max_value FROM {$this->table}";
        $result = $this->db->selectOne($sql);
        return $result['max_value'] ?? null;
    }
    
    /**
     * 최소값 조회
     */
    public function getMin($field) {
        $sql = "SELECT MIN({$field}) as min_value FROM {$this->table}";
        $result = $this->db->selectOne($sql);
        return $result['min_value'] ?? null;
    }
    
    /**
     * 평균값 조회
     */
    public function getAvg($field) {
        $sql = "SELECT AVG({$field}) as avg_value FROM {$this->table}";
        $result = $this->db->selectOne($sql);
        return $result['avg_value'] ?? null;
    }
    
    /**
     * 합계 조회
     */
    public function getSum($field) {
        $sql = "SELECT SUM({$field}) as sum_value FROM {$this->table}";
        $result = $this->db->selectOne($sql);
        return $result['sum_value'] ?? null;
    }
    
    // 기존 메서드들과의 호환성을 위한 별칭들
    public function find($id) {
        return $this->getById($id);
    }
    
    public function all() {
        return $this->getAll();
    }
    
    public function where($conditions, $params = []) {
        return $this->getWhere($conditions);
    }
    
    public function paginate($page = 1, $perPage = 10) {
        $result = $this->getPaginated($page, $perPage);
        return [
            'items' => $result['data'],
            'pagination' => [
                'current_page' => $result['current_page'],
                'total_pages' => $result['total_pages'],
                'per_page' => $result['per_page'],
                'total' => $result['total']
            ]
        ];
    }
} 