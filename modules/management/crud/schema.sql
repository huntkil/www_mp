-- Create myinfo table
CREATE TABLE IF NOT EXISTS `myinfo` (
    `no` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `age` INT,
    `birthday` DATE,
    `height` DECIMAL(5,2),
    `weight` DECIMAL(5,2)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 