<?php

/**
 * 환경별 설정 관리
 */
class EnvironmentConfig
{
    private static array $environments = [
        'development' => [
            'debug' => true,
            'error_reporting' => E_ALL,
            'display_errors' => true,
            'log_errors' => true,
            'database' => [
                'type' => 'sqlite',
                'path' => __DIR__ . '/../../../config/database.sqlite',
                'options' => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            ],
            'session' => [
                'lifetime' => 3600,
                'path' => '/',
                'domain' => '',
                'secure' => false,
                'httponly' => true,
                'samesite' => 'Lax'
            ],
            'cache' => [
                'enabled' => false,
                'driver' => 'file',
                'path' => __DIR__ . '/../../../system/cache'
            ],
            'mail' => [
                'driver' => 'log',
                'log_path' => __DIR__ . '/../../../config/logs/mail.log'
            ]
        ],
        'staging' => [
            'debug' => false,
            'error_reporting' => E_ALL & ~E_DEPRECATED & ~E_STRICT,
            'display_errors' => false,
            'log_errors' => true,
            'database' => [
                'type' => 'mysql',
                'host' => 'localhost',
                'port' => 3306,
                'database' => 'staging_db',
                'username' => 'staging_user',
                'password' => 'staging_pass',
                'charset' => 'utf8mb4',
                'options' => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            ],
            'session' => [
                'lifetime' => 3600,
                'path' => '/',
                'domain' => '.staging.example.com',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ],
            'cache' => [
                'enabled' => true,
                'driver' => 'redis',
                'host' => 'localhost',
                'port' => 6379,
                'database' => 1
            ],
            'mail' => [
                'driver' => 'smtp',
                'host' => 'smtp.mailtrap.io',
                'port' => 2525,
                'username' => 'staging_user',
                'password' => 'staging_pass',
                'encryption' => 'tls'
            ]
        ],
        'production' => [
            'debug' => false,
            'error_reporting' => E_ALL & ~E_DEPRECATED & ~E_STRICT,
            'display_errors' => false,
            'log_errors' => true,
            'database' => [
                'type' => 'mysql',
                'host' => 'localhost',
                'port' => 3306,
                'database' => 'production_db',
                'username' => 'production_user',
                'password' => 'production_pass',
                'charset' => 'utf8mb4',
                'options' => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            ],
            'session' => [
                'lifetime' => 3600,
                'path' => '/',
                'domain' => '.gukho.net',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ],
            'cache' => [
                'enabled' => true,
                'driver' => 'redis',
                'host' => 'localhost',
                'port' => 6379,
                'database' => 0
            ],
            'mail' => [
                'driver' => 'smtp',
                'host' => 'smtp.gmail.com',
                'port' => 587,
                'username' => 'noreply@gukho.net',
                'password' => 'app_password',
                'encryption' => 'tls'
            ]
        ]
    ];

    /**
     * 현재 환경 가져오기
     */
    public static function getCurrentEnvironment(): string
    {
        // 환경 변수에서 가져오기
        $env = $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'development';
        
        // 유효한 환경인지 확인
        if (!array_key_exists($env, self::$environments)) {
            throw new InvalidArgumentException("Invalid environment: {$env}");
        }
        
        return $env;
    }

    /**
     * 환경별 설정 가져오기
     */
    public static function get(string $key = null, $default = null)
    {
        $environment = self::getCurrentEnvironment();
        $config = self::$environments[$environment];
        
        if ($key === null) {
            return $config;
        }
        
        return self::getNestedValue($config, $key, $default);
    }

    /**
     * 중첩된 배열에서 값 가져오기
     */
    private static function getNestedValue(array $array, string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $array;
        
        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return $default;
            }
            $value = $value[$k];
        }
        
        return $value;
    }

    /**
     * 데이터베이스 설정 가져오기
     */
    public static function getDatabaseConfig(): array
    {
        return self::get('database');
    }

    /**
     * 세션 설정 가져오기
     */
    public static function getSessionConfig(): array
    {
        return self::get('session');
    }

    /**
     * 캐시 설정 가져오기
     */
    public static function getCacheConfig(): array
    {
        return self::get('cache');
    }

    /**
     * 메일 설정 가져오기
     */
    public static function getMailConfig(): array
    {
        return self::get('mail');
    }

    /**
     * 디버그 모드 확인
     */
    public static function isDebug(): bool
    {
        return self::get('debug', false);
    }

    /**
     * 프로덕션 환경 확인
     */
    public static function isProduction(): bool
    {
        return self::getCurrentEnvironment() === 'production';
    }

    /**
     * 개발 환경 확인
     */
    public static function isDevelopment(): bool
    {
        return self::getCurrentEnvironment() === 'development';
    }

    /**
     * 스테이징 환경 확인
     */
    public static function isStaging(): bool
    {
        return self::getCurrentEnvironment() === 'staging';
    }
} 