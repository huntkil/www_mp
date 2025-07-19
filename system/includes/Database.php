<?php
// Prevent multiple includes
if (defined('DATABASE_CLASS_LOADED')) {
    return;
}
define('DATABASE_CLASS_LOADED', true);

require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $connection = null;
    private $dbType;
    private $host;
    private $username;
    private $password;
    private $database;
    private $dbFile;
    
    private function __construct() {
        $this->dbType = DB_TYPE;
        $this->host = DB_HOST;
        $this->username = DB_USER;
        $this->password = DB_PASS;
        $this->database = DB_NAME;
        $this->dbFile = DB_FILE;
        
        $this->connect();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function connect() {
        try {
            if ($this->dbType === 'sqlite') {
                // SQLite connection
                $dsn = "sqlite:" . $this->dbFile;
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ];
                
                // Create directory if it doesn't exist
                $dir = dirname($this->dbFile);
                if (!file_exists($dir)) {
                    mkdir($dir, 0755, true);
                }
                
                $this->connection = new PDO($dsn, null, null, $options);
                
                // Enable foreign keys for SQLite
                $this->connection->exec("PRAGMA foreign_keys = ON");
                
                // Initialize tables if needed
                $this->initializeTables();
                
                if (IS_LOCAL) {
                    error_log("Database connected successfully to SQLite: " . $this->dbFile);
                }
            } else {
                // MySQL connection
                $dsn = "mysql:host={$this->host};dbname={$this->database};charset=" . DB_CHARSET;
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . " COLLATE " . DB_COLLATE
                ];
                
                $this->connection = new PDO($dsn, $this->username, $this->password, $options);
                
                if (IS_LOCAL) {
                    error_log("Database connected successfully to MySQL: " . $this->database);
                }
            }
        } catch (PDOException $e) {
            $error_message = "Database connection failed: " . $e->getMessage();
            error_log($error_message);
            
            if (IS_LOCAL) {
                throw new Exception($error_message);
            } else {
                throw new Exception("Database connection failed. Please try again later.");
            }
        }
    }
    
    public function getConnection() {
        if ($this->connection === null) {
            $this->connect();
        }
        return $this->connection;
    }
    
    public function isConnected() {
        return $this->connection !== null;
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            ErrorHandler::handleDatabaseError($e, $sql);
        }
    }
    
    public function select($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    public function selectOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    public function insert($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $this->connection->lastInsertId();
    }
    
    public function update($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    public function delete($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    public function tableExists($tableName) {
        try {
            if ($this->dbType === 'sqlite') {
                $sql = "SELECT name FROM sqlite_master WHERE type='table' AND name = :table_name";
            } else {
                $sql = "SHOW TABLES LIKE :table_name";
            }
            $stmt = $this->query($sql, [':table_name' => $tableName]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    public function commit() {
        return $this->connection->commit();
    }
    
    public function rollback() {
        return $this->connection->rollback();
    }
    
    public function getLastError() {
        return $this->connection->errorInfo();
    }
    
    public function escapeString($string) {
        return $this->connection->quote($string);
    }
    
    private function initializeTables() {
        if ($this->dbType !== 'sqlite') {
            return;
        }
        
        // Create tables for SQLite if they don't exist
        $tables = [
            'users' => "CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                name TEXT NOT NULL,
                role TEXT DEFAULT 'user',
                email TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                last_login DATETIME,
                login_attempts INTEGER DEFAULT 0,
                last_attempt DATETIME,
                status TEXT DEFAULT 'active'
            )",
            'myhealth' => "CREATE TABLE IF NOT EXISTS myhealth (
                no INTEGER PRIMARY KEY AUTOINCREMENT,
                year INTEGER NOT NULL,
                month INTEGER NOT NULL,
                day INTEGER NOT NULL,
                dayofweek TEXT NOT NULL,
                running_time REAL NOT NULL,
                running_speed_start REAL NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            'myinfo' => "CREATE TABLE IF NOT EXISTS myinfo (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT,
                phone TEXT,
                address TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            'vocabulary' => "CREATE TABLE IF NOT EXISTS vocabulary (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                word TEXT NOT NULL,
                meaning TEXT NOT NULL,
                example TEXT,
                category TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            'system_logs' => "CREATE TABLE IF NOT EXISTS system_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                level TEXT NOT NULL,
                message TEXT NOT NULL,
                context TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )"
        ];
        
        foreach ($tables as $tableName => $sql) {
            if (!$this->tableExists($tableName)) {
                $this->connection->exec($sql);
                if (IS_LOCAL) {
                    error_log("Created table: " . $tableName);
                }
            }
        }
        
        // Insert default admin user if not exists
        if (!$this->selectOne("SELECT id FROM users WHERE username = ?", [CREDENTIALS_ADMIN_USERNAME])) {
            $adminPassword = password_hash(CREDENTIALS_ADMIN_PASSWORD, PASSWORD_DEFAULT);
            $this->query("INSERT INTO users (username, password, name, role, email) VALUES (?, ?, ?, ?, ?)", [
                CREDENTIALS_ADMIN_USERNAME,
                $adminPassword,
                'Administrator',
                'admin',
                'admin@example.com'
            ]);
            if (IS_LOCAL) {
                error_log("Created default admin user: " . CREDENTIALS_ADMIN_USERNAME . "/" . CREDENTIALS_ADMIN_PASSWORD);
            }
        }
    }
    
    // Prevent cloning
    public function __clone() {
        throw new Exception("Cannot clone singleton");
    }
    
    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
} 