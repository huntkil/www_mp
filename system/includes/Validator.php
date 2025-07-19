<?php
/**
 * 입력 검증 시스템
 * 사용자 입력의 유효성을 검사하고 안전성을 보장
 */

class Validator {
    private $errors = [];
    private $data = [];
    
    public function __construct($data = []) {
        $this->data = $data;
    }
    
    /**
     * 필수 필드 검증
     */
    public function required($field, $message = null) {
        $value = $this->getValue($field);
        if (empty($value) && $value !== '0') {
            $this->addError($field, $message ?? "The {$field} field is required.");
        }
        return $this;
    }
    
    /**
     * 이메일 검증
     */
    public function email($field, $message = null) {
        $value = $this->getValue($field);
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, $message ?? "The {$field} must be a valid email address.");
        }
        return $this;
    }
    
    /**
     * 최소 길이 검증
     */
    public function minLength($field, $length, $message = null) {
        $value = $this->getValue($field);
        if (!empty($value) && strlen($value) < $length) {
            $this->addError($field, $message ?? "The {$field} must be at least {$length} characters.");
        }
        return $this;
    }
    
    /**
     * 최대 길이 검증
     */
    public function maxLength($field, $length, $message = null) {
        $value = $this->getValue($field);
        if (!empty($value) && strlen($value) > $length) {
            $this->addError($field, $message ?? "The {$field} must not exceed {$length} characters.");
        }
        return $this;
    }
    
    /**
     * 숫자 검증
     */
    public function numeric($field, $message = null) {
        $value = $this->getValue($field);
        if (!empty($value) && !is_numeric($value)) {
            $this->addError($field, $message ?? "The {$field} must be a number.");
        }
        return $this;
    }
    
    /**
     * 정수 검증
     */
    public function integer($field, $message = null) {
        $value = $this->getValue($field);
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
            $this->addError($field, $message ?? "The {$field} must be an integer.");
        }
        return $this;
    }
    
    /**
     * 최소값 검증
     */
    public function min($field, $min, $message = null) {
        $value = $this->getValue($field);
        if (!empty($value) && is_numeric($value) && $value < $min) {
            $this->addError($field, $message ?? "The {$field} must be at least {$min}.");
        }
        return $this;
    }
    
    /**
     * 최대값 검증
     */
    public function max($field, $max, $message = null) {
        $value = $this->getValue($field);
        if (!empty($value) && is_numeric($value) && $value > $max) {
            $this->addError($field, $message ?? "The {$field} must not exceed {$max}.");
        }
        return $this;
    }
    
    /**
     * URL 검증
     */
    public function url($field, $message = null) {
        $value = $this->getValue($field);
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addError($field, $message ?? "The {$field} must be a valid URL.");
        }
        return $this;
    }
    
    /**
     * 날짜 검증
     */
    public function date($field, $format = 'Y-m-d', $message = null) {
        $value = $this->getValue($field);
        if (!empty($value)) {
            $date = DateTime::createFromFormat($format, $value);
            if (!$date || $date->format($format) !== $value) {
                $this->addError($field, $message ?? "The {$field} must be a valid date.");
            }
        }
        return $this;
    }
    
    /**
     * 정규식 검증
     */
    public function regex($field, $pattern, $message = null) {
        $value = $this->getValue($field);
        if (!empty($value) && !preg_match($pattern, $value)) {
            $this->addError($field, $message ?? "The {$field} format is invalid.");
        }
        return $this;
    }
    
    /**
     * 허용된 값 검증
     */
    public function in($field, $allowedValues, $message = null) {
        $value = $this->getValue($field);
        if (!empty($value) && !in_array($value, $allowedValues)) {
            $this->addError($field, $message ?? "The {$field} must be one of: " . implode(', ', $allowedValues));
        }
        return $this;
    }
    
    /**
     * 고유값 검증 (데이터베이스)
     */
    public function unique($field, $table, $column = null, $excludeId = null, $message = null) {
        $value = $this->getValue($field);
        if (!empty($value)) {
            $column = $column ?: $field;
            $db = Database::getInstance();
            
            $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ?";
            $params = [$value];
            
            if ($excludeId) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }
            
            $result = $db->selectOne($sql, $params);
            
            if ($result['count'] > 0) {
                $this->addError($field, $message ?? "The {$field} already exists.");
            }
        }
        return $this;
    }
    
    /**
     * XSS 방지를 위한 HTML 태그 제거
     */
    public function sanitize($field) {
        $value = $this->getValue($field);
        if (!empty($value)) {
            $this->data[$field] = strip_tags($value);
        }
        return $this;
    }
    
    /**
     * SQL Injection 방지를 위한 특수문자 이스케이프
     */
    public function escape($field) {
        $value = $this->getValue($field);
        if (!empty($value)) {
            $this->data[$field] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
        return $this;
    }
    
    /**
     * 모든 필드 이스케이프
     */
    public function escapeAll() {
        foreach ($this->data as $field => $value) {
            if (is_string($value)) {
                $this->data[$field] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        }
        return $this;
    }
    
    /**
     * 필드값 가져오기
     */
    private function getValue($field) {
        return $this->data[$field] ?? null;
    }
    
    /**
     * 에러 추가
     */
    private function addError($field, $message) {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }
    
    /**
     * 검증 실패 여부 확인
     */
    public function fails() {
        return !empty($this->errors);
    }
    
    /**
     * 검증 성공 여부 확인
     */
    public function passes() {
        return empty($this->errors);
    }
    
    /**
     * 모든 에러 반환
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * 특정 필드의 에러 반환
     */
    public function getError($field) {
        return $this->errors[$field] ?? [];
    }
    
    /**
     * 첫 번째 에러 메시지 반환
     */
    public function getFirstError($field = null) {
        if ($field) {
            return $this->errors[$field][0] ?? null;
        }
        
        foreach ($this->errors as $fieldErrors) {
            if (!empty($fieldErrors)) {
                return $fieldErrors[0];
            }
        }
        
        return null;
    }
    
    /**
     * 검증된 데이터 반환
     */
    public function getData() {
        return $this->data;
    }
    
    /**
     * 특정 필드의 검증된 값 반환
     */
    public function get($field, $default = null) {
        return $this->data[$field] ?? $default;
    }
    
    /**
     * 데이터 설정
     */
    public function setData($data) {
        $this->data = $data;
        return $this;
    }
    
    /**
     * 에러 초기화
     */
    public function reset() {
        $this->errors = [];
        return $this;
    }
}

// 전역 헬퍼 함수들
function validate($data, $rules) {
    $validator = new Validator($data);
    
    foreach ($rules as $field => $fieldRules) {
        $fieldRules = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;
        
        foreach ($fieldRules as $rule) {
            $params = [];
            
            if (strpos($rule, ':') !== false) {
                list($rule, $param) = explode(':', $rule, 2);
                $params = explode(',', $param);
            }
            
            switch ($rule) {
                case 'required':
                    $validator->required($field);
                    break;
                case 'email':
                    $validator->email($field);
                    break;
                case 'numeric':
                    $validator->numeric($field);
                    break;
                case 'integer':
                    $validator->integer($field);
                    break;
                case 'url':
                    $validator->url($field);
                    break;
                case 'min':
                    if (isset($params[0])) {
                        $validator->min($field, $params[0]);
                    }
                    break;
                case 'max':
                    if (isset($params[0])) {
                        $validator->max($field, $params[0]);
                    }
                    break;
                case 'min_length':
                    if (isset($params[0])) {
                        $validator->minLength($field, $params[0]);
                    }
                    break;
                case 'max_length':
                    if (isset($params[0])) {
                        $validator->maxLength($field, $params[0]);
                    }
                    break;
            }
        }
    }
    
    return $validator;
} 