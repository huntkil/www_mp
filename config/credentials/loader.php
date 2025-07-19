<?php
/**
 * Credentials Loader
 * 환경에 따라 적절한 credentials 파일을 로드합니다.
 * 
 * Usage:
 * require_once __DIR__ . '/config/credentials/loader.php';
 */

// Prevent multiple includes
if (defined('CREDENTIALS_LOADED')) {
    return;
}
define('CREDENTIALS_LOADED', true);

// Environment Detection
function detectEnvironment() {
    // Check for explicit environment variable
    if (isset($_ENV['APP_ENV'])) {
        return $_ENV['APP_ENV'];
    }
    
    // Check for local development indicators
    $serverName = $_SERVER['SERVER_NAME'] ?? '';
    $serverPort = $_SERVER['SERVER_PORT'] ?? '';
    
    $isLocal = (
        $serverName === 'localhost' ||
        $serverName === '127.0.0.1' ||
        strpos($serverName, 'localhost') !== false ||
        $serverPort === '8080' ||
        $serverPort === '3000' ||
        php_sapi_name() === 'cli' // CLI 환경에서는 개발 환경으로 간주
    );
    
    return $isLocal ? 'development' : 'production';
}

// Load credentials based on environment
$environment = detectEnvironment();
$credentialsFile = __DIR__ . '/' . $environment . '.php';

if (!file_exists($credentialsFile)) {
    // Fallback to sample file for first setup
    $credentialsFile = __DIR__ . '/sample.php';
    
    if (!file_exists($credentialsFile)) {
        throw new Exception("Credentials file not found. Please copy sample.php to {$environment}.php and configure it.");
    }
    
    // Log warning in development
    if ($environment === 'development') {
        error_log("Warning: Using sample credentials. Please copy sample.php to development.php and configure it.");
    }
}

try {
    require_once $credentialsFile;
    
    // Log successful loading in development
    if ($environment === 'development') {
        error_log("Credentials loaded successfully from: " . basename($credentialsFile));
    }
} catch (Exception $e) {
    throw new Exception("Failed to load credentials: " . $e->getMessage());
}

// Validation functions
function validateCredentials() {
    $required = [
        'CREDENTIALS_NEWS_API_KEY',
        'CREDENTIALS_DB_TYPE',
        'CREDENTIALS_SESSION_NAME',
        'CREDENTIALS_HASH_COST'
    ];
    
    $missing = [];
    foreach ($required as $constant) {
        if (!defined($constant) || empty(constant($constant))) {
            $missing[] = $constant;
        }
    }
    
    if (!empty($missing)) {
        $message = "Missing required credentials: " . implode(', ', $missing);
        error_log($message);
        
        if (detectEnvironment() === 'development') {
            throw new Exception($message);
        }
    }
}

// Validate loaded credentials
validateCredentials();

// Set environment flag
define('CREDENTIALS_ENV', $environment);

?> 