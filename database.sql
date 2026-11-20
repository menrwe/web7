-- Створення бази даних і таблиці вручну (необов'язково: скрипт робить це автоматично).
-- Імпорт: phpMyAdmin -> Імпорт, або  mysql -u root < database.sql
CREATE DATABASE IF NOT EXISTS survey_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE survey_db;

CREATE TABLE IF NOT EXISTS responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    submitted_at DATETIME NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    usage_frequency VARCHAR(100),
    tasks TEXT,
    usefulness VARCHAR(10),
    improvement TEXT,
    file_name VARCHAR(255)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
