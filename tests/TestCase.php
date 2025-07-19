<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use PDO;

/**
 * 기본 테스트 케이스 클래스
 * 모든 테스트에서 공통으로 사용하는 기능들을 제공
 */
abstract class TestCase extends BaseTestCase
{
    protected PDO $pdo;
    protected array $testData = [];
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTestDatabase();
        $this->loadTestData();
    }
    
    protected function tearDown(): void
    {
        $this->cleanupTestData();
        parent::tearDown();
    }
    
    /**
     * 테스트용 데이터베이스 설정
     */
    protected function setupTestDatabase(): void
    {
        // 메모리 SQLite 데이터베이스 사용
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 테스트용 테이블 생성
        $this->createTestTables();
    }
    
    /**
     * 테스트용 테이블 생성
     */
    protected function createTestTables(): void
    {
        // vocabulary 테이블
        $this->pdo->exec("
            CREATE TABLE vocabulary (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id VARCHAR(50) NOT NULL,
                word VARCHAR(255) NOT NULL,
                meaning TEXT NOT NULL,
                example TEXT,
                language VARCHAR(10) DEFAULT 'en',
                difficulty VARCHAR(20) DEFAULT 'medium',
                learned BOOLEAN DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // users 테이블
        $this->pdo->exec("
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username VARCHAR(50) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                email VARCHAR(100),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // personal_info 테이블
        $this->pdo->exec("
            CREATE TABLE personal_info (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id VARCHAR(50) NOT NULL,
                name VARCHAR(100),
                age INTEGER,
                email VARCHAR(100),
                phone VARCHAR(20),
                address TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // health_records 테이블
        $this->pdo->exec("
            CREATE TABLE health_records (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id VARCHAR(50) NOT NULL,
                date DATE NOT NULL,
                weight DECIMAL(5,2),
                steps INTEGER,
                sleep_hours DECIMAL(3,1),
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }
    
    /**
     * 테스트 데이터 로드
     */
    protected function loadTestData(): void
    {
        $this->testData = [
            'users' => [
                [
                    'username' => 'testuser',
                    'password' => password_hash('password123', PASSWORD_DEFAULT),
                    'email' => 'test@example.com'
                ],
                [
                    'username' => 'admin',
                    'password' => password_hash('admin123', PASSWORD_DEFAULT),
                    'email' => 'admin@example.com'
                ]
            ],
            'vocabulary' => [
                [
                    'user_id' => 'testuser',
                    'word' => 'serendipity',
                    'meaning' => '뜻밖의 발견',
                    'example' => 'Finding that book was pure serendipity.',
                    'language' => 'en',
                    'difficulty' => 'hard',
                    'learned' => 0
                ],
                [
                    'user_id' => 'testuser',
                    'word' => 'hello',
                    'meaning' => '안녕하세요',
                    'example' => 'Hello, how are you?',
                    'language' => 'en',
                    'difficulty' => 'easy',
                    'learned' => 1
                ]
            ],
            'personal_info' => [
                [
                    'user_id' => 'testuser',
                    'name' => 'Test User',
                    'age' => 25,
                    'email' => 'test@example.com',
                    'phone' => '010-1234-5678'
                ]
            ],
            'health_records' => [
                [
                    'user_id' => 'testuser',
                    'date' => '2024-01-01',
                    'weight' => 70.5,
                    'steps' => 8000,
                    'sleep_hours' => 7.5
                ]
            ]
        ];
        
        // 테스트 데이터 삽입
        $this->insertTestData();
    }
    
    /**
     * 테스트 데이터 삽입
     */
    protected function insertTestData(): void
    {
        foreach ($this->testData as $table => $records) {
            foreach ($records as $record) {
                $fields = array_keys($record);
                $placeholders = ':' . implode(', :', $fields);
                $sql = "INSERT INTO {$table} (" . implode(', ', $fields) . ") VALUES ({$placeholders})";
                
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($record);
            }
        }
    }
    
    /**
     * 테스트 데이터 정리
     */
    protected function cleanupTestData(): void
    {
        $tables = ['vocabulary', 'users', 'personal_info', 'health_records'];
        
        foreach ($tables as $table) {
            $this->pdo->exec("DELETE FROM {$table}");
        }
    }
    
    /**
     * HTTP 요청 시뮬레이션
     */
    protected function simulateRequest(string $method = 'GET', string $uri = '/', array $data = []): void
    {
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI'] = $uri;
        $_GET = [];
        $_POST = [];
        
        if ($method === 'GET' && !empty($data)) {
            $_GET = $data;
        } elseif (in_array($method, ['POST', 'PUT', 'PATCH']) && !empty($data)) {
            $_POST = $data;
        }
    }
    
    /**
     * AJAX 요청 시뮬레이션
     */
    protected function simulateAjaxRequest(string $method = 'GET', string $uri = '/', array $data = []): void
    {
        $this->simulateRequest($method, $uri, $data);
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
    }
    
    /**
     * 세션 시뮬레이션
     */
    protected function simulateSession(array $sessionData = []): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        foreach ($sessionData as $key => $value) {
            $_SESSION[$key] = $value;
        }
    }
    
    /**
     * 로그인된 사용자 시뮬레이션
     */
    protected function simulateLoggedInUser(string $userId = 'testuser'): void
    {
        $this->simulateSession([
            'id' => $userId,
            'logged_in' => true,
            'login_time' => time(),
            'last_activity' => time()
        ]);
    }
    
    /**
     * JSON 응답 검증
     */
    protected function assertJsonResponse(array $expected, string $actual): void
    {
        $decoded = json_decode($actual, true);
        $this->assertNotNull($decoded, 'Response should be valid JSON');
        $this->assertEquals($expected, $decoded);
    }
    
    /**
     * JSON 성공 응답 검증
     */
    protected function assertJsonSuccess(string $actual, $data = null, string $message = 'Success'): void
    {
        $decoded = json_decode($actual, true);
        $this->assertNotNull($decoded, 'Response should be valid JSON');
        $this->assertTrue($decoded['success'], 'Response should indicate success');
        $this->assertEquals($message, $decoded['message']);
        
        if ($data !== null) {
            $this->assertEquals($data, $decoded['data']);
        }
    }
    
    /**
     * JSON 에러 응답 검증
     */
    protected function assertJsonError(string $actual, string $message, int $code = 400): void
    {
        $decoded = json_decode($actual, true);
        $this->assertNotNull($decoded, 'Response should be valid JSON');
        $this->assertFalse($decoded['success'], 'Response should indicate error');
        $this->assertEquals($message, $decoded['error']['message']);
        $this->assertEquals($code, $decoded['error']['code']);
    }
    
    /**
     * 데이터베이스 레코드 존재 확인
     */
    protected function assertDatabaseHas(string $table, array $data): void
    {
        $whereClauses = [];
        $params = [];
        
        foreach ($data as $field => $value) {
            $whereClauses[] = "{$field} = ?";
            $params[] = $value;
        }
        
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE " . implode(' AND ', $whereClauses);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->assertGreaterThan(0, $result['count'], "Record should exist in {$table}");
    }
    
    /**
     * 데이터베이스 레코드 부재 확인
     */
    protected function assertDatabaseMissing(string $table, array $data): void
    {
        $whereClauses = [];
        $params = [];
        
        foreach ($data as $field => $value) {
            $whereClauses[] = "{$field} = ?";
            $params[] = $value;
        }
        
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE " . implode(' AND ', $whereClauses);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->assertEquals(0, $result['count'], "Record should not exist in {$table}");
    }
    
    /**
     * 파일 존재 확인
     */
    protected function assertFileExists(string $path): void
    {
        $this->assertTrue(file_exists($path), "File should exist: {$path}");
    }
    
    /**
     * 파일 내용 확인
     */
    protected function assertFileContains(string $path, string $content): void
    {
        $this->assertFileExists($path);
        $fileContent = file_get_contents($path);
        $this->assertStringContainsString($content, $fileContent);
    }
} 