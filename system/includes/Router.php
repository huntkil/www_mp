<?php
/**
 * API Router
 * RESTful API 라우팅 처리
 */

class Router {
    private $routes = [];
    private $middleware = [];
    
    /**
     * GET 라우트 등록
     */
    public function get($path, $handler) {
        $this->addRoute('GET', $path, $handler);
        return $this;
    }
    
    /**
     * POST 라우트 등록
     */
    public function post($path, $handler) {
        $this->addRoute('POST', $path, $handler);
        return $this;
    }
    
    /**
     * PUT 라우트 등록
     */
    public function put($path, $handler) {
        $this->addRoute('PUT', $path, $handler);
        return $this;
    }
    
    /**
     * DELETE 라우트 등록
     */
    public function delete($path, $handler) {
        $this->addRoute('DELETE', $path, $handler);
        return $this;
    }
    
    /**
     * PATCH 라우트 등록
     */
    public function patch($path, $handler) {
        $this->addRoute('PATCH', $path, $handler);
        return $this;
    }
    
    /**
     * 모든 HTTP 메소드에 대한 라우트 등록
     */
    public function any($path, $handler) {
        $methods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'];
        foreach ($methods as $method) {
            $this->addRoute($method, $path, $handler);
        }
        return $this;
    }
    
    /**
     * 라우트 그룹 생성
     */
    public function group($prefix, $callback) {
        $previousPrefix = $this->getGroupPrefix();
        $this->setGroupPrefix($previousPrefix . $prefix);
        
        $callback($this);
        
        $this->setGroupPrefix($previousPrefix);
        return $this;
    }
    
    /**
     * 미들웨어 등록
     */
    public function middleware($middleware) {
        $this->middleware[] = $middleware;
        return $this;
    }
    
    /**
     * 라우트 추가
     */
    private function addRoute($method, $path, $handler) {
        $fullPath = $this->getGroupPrefix() . $path;
        $pattern = $this->pathToPattern($fullPath);
        
        $this->routes[] = [
            'method' => $method,
            'path' => $fullPath,
            'pattern' => $pattern,
            'handler' => $handler,
            'middleware' => $this->middleware
        ];
    }
    
    /**
     * 경로를 정규식 패턴으로 변환
     */
    private function pathToPattern($path) {
        return '#^' . preg_replace('#\{([a-zA-Z0-9_]+)\}#', '([^/]+)', $path) . '$#';
    }
    
    /**
     * 라우트 매칭 및 실행
     */
    public function dispatch($method, $uri) {
        $uri = parse_url($uri, PHP_URL_PATH);
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $uri, $matches)) {
                // 파라미터 추출
                $params = $this->extractParams($route['path'], $matches);
                
                // 미들웨어 실행
                foreach ($route['middleware'] as $middleware) {
                    $this->executeMiddleware($middleware);
                }
                
                // 핸들러 실행
                return $this->executeHandler($route['handler'], $params);
            }
        }
        
        // 404 처리
        $this->handleNotFound();
    }
    
    /**
     * 파라미터 추출
     */
    private function extractParams($path, $matches) {
        $params = [];
        preg_match_all('#\{([a-zA-Z0-9_]+)\}#', $path, $paramNames);
        
        for ($i = 0; $i < count($paramNames[1]); $i++) {
            $params[$paramNames[1][$i]] = $matches[$i + 1] ?? null;
        }
        
        return $params;
    }
    
    /**
     * 핸들러 실행
     */
    private function executeHandler($handler, $params) {
        if (is_callable($handler)) {
            return call_user_func_array($handler, $params);
        }
        
        if (is_string($handler)) {
            $parts = explode('@', $handler);
            if (count($parts) === 2) {
                $controller = new $parts[0]();
                $method = $parts[1];
                
                if (method_exists($controller, $method)) {
                    return call_user_func_array([$controller, $method], $params);
                }
            }
        }
        
        throw new Exception('Invalid handler');
    }
    
    /**
     * 미들웨어 실행
     */
    private function executeMiddleware($middleware) {
        if (is_callable($middleware)) {
            call_user_func($middleware);
        } elseif (is_string($middleware)) {
            $instance = new $middleware();
            if (method_exists($instance, 'handle')) {
                $instance->handle();
            }
        }
    }
    
    /**
     * 404 처리
     */
    private function handleNotFound() {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => [
                'message' => 'Route not found',
                'code' => 404
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * 그룹 프리픽스 관리
     */
    private $groupPrefix = '';
    
    private function getGroupPrefix() {
        return $this->groupPrefix;
    }
    
    private function setGroupPrefix($prefix) {
        $this->groupPrefix = $prefix;
    }
    
    /**
     * 현재 요청 정보 가져오기
     */
    public static function getCurrentMethod() {
        return $_SERVER['REQUEST_METHOD'];
    }
    
    public static function getCurrentUri() {
        return $_SERVER['REQUEST_URI'];
    }
    
    /**
     * URL 생성
     */
    public function url($path, $params = []) {
        $url = $path;
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        return $url;
    }
    
    /**
     * 리다이렉트
     */
    public function redirect($path, $statusCode = 302) {
        http_response_code($statusCode);
        header("Location: {$path}");
        exit;
    }
}

// 전역 라우터 인스턴스
$router = new Router();

// 헬퍼 함수들
function route($method, $path, $handler) {
    global $router;
    return $router->$method($path, $handler);
}

function get($path, $handler) {
    return route('get', $path, $handler);
}

function post($path, $handler) {
    return route('post', $path, $handler);
}

function put($path, $handler) {
    return route('put', $path, $handler);
}

function delete($path, $handler) {
    return route('delete', $path, $handler);
}

function patch($path, $handler) {
    return route('patch', $path, $handler);
}

function group($prefix, $callback) {
    global $router;
    return $router->group($prefix, $callback);
}

function middleware($middleware) {
    global $router;
    return $router->middleware($middleware);
} 