<?php
// Production Configuration for cafe24 hosting
// Prevent multiple includes
if (defined('CONFIG_LOADED')) {
    return;
}
define('CONFIG_LOADED', true);

// Environment Detection
$serverName = $_SERVER['SERVER_NAME'] ?? '';
$isLocal = false; // Always false for production

// Load production credentials
require_once __DIR__ . '/../../config/credentials/production.php';

// Database Configuration from credentials
define('DB_TYPE', CREDENTIALS_DB_TYPE);
define('DB_HOST', CREDENTIALS_DB_HOST);
define('DB_USER', CREDENTIALS_DB_USER);
define('DB_PASS', CREDENTIALS_DB_PASS);
define('DB_NAME', CREDENTIALS_DB_NAME);
define('DB_FILE', CREDENTIALS_DB_FILE);

// Application URL
define('APP_URL', 'https://gukho.net/mp');

// Application Configuration
define('APP_NAME', 'My Playground');
define('APP_VERSION', '1.0.0');
define('IS_LOCAL', $isLocal);

// Security Configuration
define('HASH_COST', 12);

// API Keys (if needed)
define('OPENAI_API_KEY', ''); // Add if needed
define('NEWS_API_KEY', ''); // Add if needed

// Upload Configuration
define('UPLOAD_MAX_SIZE', 5242880); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'txt', 'doc', 'docx']);
define('UPLOAD_DIR', __DIR__ . '/../../resources/uploads/');

// Error Handling - Production
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../config/logs/error.log');

// Database Settings
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', 'utf8mb4_unicode_ci');

// Auto-load essential classes
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/ErrorHandler.php';
require_once __DIR__ . '/Utils.php';

// Load constants
require_once __DIR__ . '/constants.php';

// Security Headers (PHP에서 설정)
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

// PHP 설정 (cafe24 호환)
ini_set('memory_limit', '128M');
ini_set('max_execution_time', 30);
ini_set('post_max_size', '8M');
ini_set('upload_max_filesize', '5M');
ini_set('max_file_uploads', 10);
?> 