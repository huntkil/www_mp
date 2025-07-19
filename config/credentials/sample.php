<?php
/**
 * Sample Credentials File
 * 샘플 credentials 파일
 * 
 * 이 파일을 복사하여 development.php 또는 production.php로 이름을 바꾸고
 * 실제 값들을 설정하세요.
 * 
 * 복사 방법:
 * cp config/credentials/sample.php config/credentials/development.php
 * cp config/credentials/sample.php config/credentials/production.php
 */

// API Keys
define('CREDENTIALS_NEWS_API_KEY', 'your_news_api_key_here');
define('CREDENTIALS_OPENAI_API_KEY', 'your_openai_api_key_here');

// Database Credentials
define('CREDENTIALS_DB_TYPE', 'sqlite'); // 'sqlite' or 'mysql'
define('CREDENTIALS_DB_HOST', 'localhost');
define('CREDENTIALS_DB_USER', 'your_username');
define('CREDENTIALS_DB_PASS', 'your_password');
define('CREDENTIALS_DB_NAME', 'your_database_name');
define('CREDENTIALS_DB_FILE', __DIR__ . '/../database.sqlite');

// Default Admin Credentials
define('CREDENTIALS_ADMIN_USERNAME', 'admin');
define('CREDENTIALS_ADMIN_PASSWORD', 'change_this_password');

// Session Security
define('CREDENTIALS_SESSION_NAME', 'MY_PLAYGROUND_SESSION');
define('CREDENTIALS_HASH_COST', 12);

// Security Keys (Generate random values for production)
define('CREDENTIALS_ENCRYPTION_KEY', 'your_32_character_encryption_key');
define('CREDENTIALS_CSRF_SECRET', 'your_csrf_secret_key');

// External Service API Keys
define('CREDENTIALS_WEATHER_API_KEY', 'your_weather_api_key_here');
define('CREDENTIALS_GOOGLE_API_KEY', 'your_google_api_key_here');

// File Upload Settings
define('CREDENTIALS_MAX_UPLOAD_SIZE', 5242880); // 5MB
define('CREDENTIALS_UPLOAD_DIR', __DIR__ . '/../../uploads/');

// Email Configuration
define('CREDENTIALS_SMTP_HOST', 'smtp.your-provider.com');
define('CREDENTIALS_SMTP_PORT', 587);
define('CREDENTIALS_SMTP_USER', 'your_email@example.com');
define('CREDENTIALS_SMTP_PASS', 'your_email_password');
define('CREDENTIALS_SMTP_FROM', 'noreply@example.com');

// Debug Settings
define('CREDENTIALS_DEBUG_MODE', true);
define('CREDENTIALS_LOG_LEVEL', 'debug');

?> 