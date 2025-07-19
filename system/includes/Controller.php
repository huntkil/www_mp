<?php
/**
 * 기본 Controller 클래스
 * 모든 컨트롤러의 공통 기능을 제공
 */

abstract class Controller {
    protected $db;
    protected $session;
    protected $validator;
    protected $viewData = [];
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->session = getSessionSecurity();
        $this->validator = new Validator();
    }
    
    /**
     * 뷰 데이터 설정
     */
    protected function setViewData($key, $value = null) {
        if (is_array($key)) {
            $this->viewData = array_merge($this->viewData, $key);
        } else {
            $this->viewData[$key] = $value;
        }
    }
    
    /**
     * 뷰 데이터 가져오기
     */
    protected function getViewData($key = null) {
        if ($key === null) {
            return $this->viewData;
        }
        return $this->viewData[$key] ?? null;
    }
    
    /**
     * 뷰 렌더링
     */
    protected function render($view, $data = []) {
        $this->setViewData($data);
        extract($this->viewData);
        
        $viewPath = $this->getViewPath($view);
        
        if (!file_exists($viewPath)) {
            throw new Exception("View file not found: {$viewPath}");
        }
        
        ob_start();
        include $viewPath;
        $content = ob_get_clean();
        
        return $content;
    }
    
    /**
     * 뷰 파일 경로 생성
     */
    protected function getViewPath($view) {
        $controllerName = $this->getControllerName();
        return __DIR__ . "/../views/{$controllerName}/{$view}.php";
    }
    
    /**
     * 컨트롤러 이름 가져오기
     */
    protected function getControllerName() {
        $className = get_class($this);
        return strtolower(str_replace('Controller', '', $className));
    }
    
    /**
     * JSON 응답
     */
    protected function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * 성공 응답
     */
    protected function successResponse($data = null, $message = 'Success') {
        $response = [
            'success' => true,
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        $this->jsonResponse($response);
    }
    
    /**
     * 에러 응답
     */
    protected function errorResponse($message, $statusCode = 400, $details = []) {
        $response = [
            'success' => false,
            'error' => [
                'message' => $message,
                'code' => $statusCode
            ]
        ];
        
        if (!empty($details)) {
            $response['error']['details'] = $details;
        }
        
        $this->jsonResponse($response, $statusCode);
    }
    
    /**
     * 리다이렉트
     */
    protected function redirect($url, $message = null, $type = 'success') {
        if ($message) {
            $_SESSION['flash_message'] = $message;
            $_SESSION['flash_type'] = $type;
        }
        
        header("Location: {$url}");
        exit;
    }
    
    /**
     * 입력 데이터 검증
     */
    protected function validate($data, $rules) {
        $this->validator->setData($data);
        
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
                        $this->validator->required($field);
                        break;
                    case 'email':
                        $this->validator->email($field);
                        break;
                    case 'numeric':
                        $this->validator->numeric($field);
                        break;
                    case 'integer':
                        $this->validator->integer($field);
                        break;
                    case 'min':
                        if (isset($params[0])) {
                            $this->validator->min($field, $params[0]);
                        }
                        break;
                    case 'max':
                        if (isset($params[0])) {
                            $this->validator->max($field, $params[0]);
                        }
                        break;
                    case 'min_length':
                        if (isset($params[0])) {
                            $this->validator->minLength($field, $params[0]);
                        }
                        break;
                    case 'max_length':
                        if (isset($params[0])) {
                            $this->validator->maxLength($field, $params[0]);
                        }
                        break;
                }
            }
        }
        
        return $this->validator;
    }
    
    /**
     * POST 데이터 가져오기
     */
    protected function getPostData() {
        return $_POST;
    }
    
    /**
     * GET 데이터 가져오기
     */
    protected function getGetData() {
        return $_GET;
    }
    
    /**
     * 요청 메소드 확인
     */
    protected function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    /**
     * 요청 메소드 확인
     */
    protected function isGet() {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }
    
    /**
     * AJAX 요청 확인
     */
    protected function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * CSRF 토큰 검증
     */
    protected function validateCSRF() {
        if ($this->isPost()) {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
            
            if (!$token || !$this->session->validateCSRFToken($token)) {
                $this->errorResponse('CSRF token validation failed', 403);
            }
        }
    }
    
    /**
     * 로그인 필요 확인
     */
    protected function requireLogin() {
        if (!$this->session->isLoggedIn()) {
            if ($this->isAjax()) {
                $this->errorResponse('Authentication required', 401);
            } else {
                $this->redirect('/mp/system/auth/login.php', 'Please login to continue');
            }
        }
    }
    
    /**
     * 관리자 권한 확인
     */
    protected function requireAdmin() {
        $this->requireLogin();
        
        if (!$this->session->isAdmin()) {
            if ($this->isAjax()) {
                $this->errorResponse('Admin access required', 403);
            } else {
                $this->redirect('/mp/', 'Admin access required');
            }
        }
    }
    
    /**
     * 페이지네이션 데이터 생성
     */
    protected function paginate($total, $perPage = 10, $currentPage = 1) {
        $totalPages = ceil($total / $perPage);
        $currentPage = max(1, min($currentPage, $totalPages));
        $offset = ($currentPage - 1) * $perPage;
        
        return [
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'per_page' => $perPage,
            'total' => $total,
            'offset' => $offset,
            'has_previous' => $currentPage > 1,
            'has_next' => $currentPage < $totalPages,
            'previous_page' => $currentPage - 1,
            'next_page' => $currentPage + 1
        ];
    }
    
    /**
     * 파일 업로드 처리
     */
    protected function handleFileUpload($file, $allowedTypes = [], $maxSize = null) {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload failed');
        }
        
        $maxSize = $maxSize ?: UPLOAD_MAX_SIZE;
        
        if ($file['size'] > $maxSize) {
            throw new Exception('File size exceeds limit');
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!empty($allowedTypes) && !in_array($extension, $allowedTypes)) {
            throw new Exception('File type not allowed');
        }
        
        $uploadDir = UPLOAD_DIR;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $filename = uniqid() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception('Failed to save uploaded file');
        }
        
        return $filename;
    }
} 