-- My Playground Database Schema
-- 카페24 호스팅 환경 (MariaDB) 용

-- 데이터베이스 설정
SET sql_mode = 'TRADITIONAL';
SET time_zone = '+09:00';
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 사용자 테이블 (보안 강화)
DROP TABLE IF EXISTS `myUser`;
CREATE TABLE `myUser` (
  `id` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `name` VARCHAR(100) DEFAULT NULL,
  `status` ENUM('active', 'inactive', 'banned') DEFAULT 'active',
  `login_attempts` INT DEFAULT 0,
  `last_attempt` TIMESTAMP NULL DEFAULT NULL,
  `last_login` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_status` (`status`),
  KEY `idx_last_login` (`last_login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 기본 관리자 계정 (비밀번호는 'admin123'의 해시)
INSERT INTO `myUser` (`id`, `password`, `email`, `name`, `status`) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com', 'Administrator', 'active');

-- 개인정보 테이블 (CRUD 데모용)
DROP TABLE IF EXISTS `myinfo`;
CREATE TABLE `myinfo` (
  `no` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `age` INT NOT NULL,
  `birthday` DATE NOT NULL,
  `height` DECIMAL(5,2) DEFAULT NULL,
  `weight` DECIMAL(5,2) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_name` (`name`),
  KEY `idx_age` (`age`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 샘플 데이터
INSERT INTO `myinfo` (`name`, `age`, `birthday`, `height`, `weight`) VALUES 
('홍길동', 25, '1998-05-15', 175.5, 70.2),
('김철수', 30, '1993-03-22', 180.0, 75.5),
('이영희', 28, '1995-08-10', 165.3, 58.7);

-- 건강 관리 테이블
DROP TABLE IF EXISTS `myhealth`;
CREATE TABLE `myhealth` (
  `no` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` VARCHAR(50) DEFAULT NULL,
  `year` INT NOT NULL,
  `month` INT NOT NULL,
  `day` INT NOT NULL,
  `dayofweek` VARCHAR(10) NOT NULL,
  `running_time` INT DEFAULT NULL COMMENT '러닝 시간 (분)',
  `running_speed_start` DECIMAL(4,2) DEFAULT NULL COMMENT '시작 속도 (km/h)',
  `running_speed_end` DECIMAL(4,2) DEFAULT NULL COMMENT '종료 속도 (km/h)',
  `calories` INT DEFAULT NULL COMMENT '소모 칼로리',
  `distance` DECIMAL(5,2) DEFAULT NULL COMMENT '거리 (km)',
  `notes` TEXT DEFAULT NULL COMMENT '메모',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_user_id` (`user_id`),
  KEY `idx_date` (`year`, `month`, `day`),
  KEY `idx_dayofweek` (`dayofweek`),
  FOREIGN KEY (`user_id`) REFERENCES `myUser`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 샘플 건강 데이터
INSERT INTO `myhealth` (`user_id`, `year`, `month`, `day`, `dayofweek`, `running_time`, `running_speed_start`, `running_speed_end`, `calories`, `distance`) VALUES 
('admin', 2024, 1, 15, '월요일', 30, 8.5, 9.2, 320, 4.5),
('admin', 2024, 1, 17, '수요일', 45, 9.0, 9.8, 480, 7.2),
('admin', 2024, 1, 19, '금요일', 25, 8.0, 8.5, 280, 3.8);

-- 단어장 테이블
DROP TABLE IF EXISTS `vocabulary`;
CREATE TABLE `vocabulary` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` VARCHAR(50) DEFAULT NULL,
  `word` VARCHAR(255) NOT NULL,
  `meaning` TEXT NOT NULL,
  `example` TEXT DEFAULT NULL,
  `language` VARCHAR(10) DEFAULT 'en',
  `difficulty` ENUM('easy', 'medium', 'hard') DEFAULT 'medium',
  `learned` BOOLEAN DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_user_id` (`user_id`),
  KEY `idx_word` (`word`),
  KEY `idx_language` (`language`),
  KEY `idx_difficulty` (`difficulty`),
  KEY `idx_learned` (`learned`),
  FOREIGN KEY (`user_id`) REFERENCES `myUser`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 샘플 단어장 데이터
INSERT INTO `vocabulary` (`user_id`, `word`, `meaning`, `example`, `language`, `difficulty`) VALUES 
('admin', 'serendipity', '뜻밖의 발견, 우연한 행운', 'Finding that book was pure serendipity.', 'en', 'hard'),
('admin', 'ubiquitous', '어디에나 있는, 편재하는', 'Smartphones are ubiquitous in modern society.', 'en', 'medium'),
('admin', 'ephemeral', '일시적인, 덧없는', 'The beauty of cherry blossoms is ephemeral.', 'en', 'medium');

-- 로그 테이블 (선택사항)
DROP TABLE IF EXISTS `system_logs`;
CREATE TABLE `system_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` VARCHAR(50) DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `details` JSON DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_ip` (`ip_address`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 인덱스 최적화
ANALYZE TABLE `myUser`, `myinfo`, `myhealth`, `vocabulary`, `system_logs`;

-- 권한 설정 (카페24 환경에서는 자동으로 설정됨)
-- GRANT SELECT, INSERT, UPDATE, DELETE ON huntkil.* TO 'huntkil'@'localhost';
-- FLUSH PRIVILEGES; 