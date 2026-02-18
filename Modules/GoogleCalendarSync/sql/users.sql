-- phpMyAdmin SQL Dump
-- version 5.2.1
-- Host: 127.0.0.1
-- Generation Time: Nov 11, 2025
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
    /*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
    /*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
    /*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------
-- Add columns to `users` table if not already existing
-- --------------------------------------------------------

-- -------------------------------------------
-- Add column google_access_token
-- -------------------------------------------
SET @sql = (
    SELECT IF(
        (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
         WHERE TABLE_NAME='users' AND COLUMN_NAME='google_access_token') = 0,
        'ALTER TABLE `users` ADD `google_access_token` LONGTEXT NULL AFTER `remember_token`;',
        'SELECT 1;'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;


-- -------------------------------------------
-- Add column google_refresh_token
-- -------------------------------------------
SET @sql = (
    SELECT IF(
        (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
         WHERE TABLE_NAME='users' AND COLUMN_NAME='google_refresh_token') = 0,
        'ALTER TABLE `users` ADD `google_refresh_token` LONGTEXT NULL AFTER `google_access_token`;',
        'SELECT 1;'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;


-- -------------------------------------------
-- Add column google_calendar_email
-- -------------------------------------------
SET @sql = (
    SELECT IF(
        (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
         WHERE TABLE_NAME='users' AND COLUMN_NAME='google_calendar_email') = 0,
        'ALTER TABLE `users` ADD `google_calendar_email` VARCHAR(255) NULL AFTER `google_refresh_token`;',
        'SELECT 1;'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;


-- -------------------------------------------
-- Add column google_client_id
-- -------------------------------------------
SET @sql = (
    SELECT IF(
        (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
         WHERE TABLE_NAME='users' AND COLUMN_NAME='google_client_id') = 0,
        'ALTER TABLE `users` ADD `google_client_id` VARCHAR(255) NULL AFTER `google_calendar_email`;',
        'SELECT 1;'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;


-- -------------------------------------------
-- Add column google_client_secret
-- -------------------------------------------
SET @sql = (
    SELECT IF(
        (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
         WHERE TABLE_NAME='users' AND COLUMN_NAME='google_client_secret') = 0,
        'ALTER TABLE `users` ADD `google_client_secret` VARCHAR(255) NULL AFTER `google_client_id`;',
        'SELECT 1;'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;


-- -------------------------------------------
-- Add column privacy_policy
-- -------------------------------------------
SET @sql = (
    SELECT IF(
        (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
         WHERE TABLE_NAME='users' AND COLUMN_NAME='privacy_policy') = 0,
        'ALTER TABLE `users` ADD `privacy_policy` TEXT NULL AFTER `deleted_at`;',
        'SELECT 1;'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;


-- -------------------------------------------
-- Add column google_calendar_id
-- -------------------------------------------
SET @sql = (
    SELECT IF(
        (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
         WHERE TABLE_NAME='users' AND COLUMN_NAME='google_calendar_id') = 0,
        'ALTER TABLE `users` ADD `google_calendar_id` VARCHAR(255) NULL AFTER `privacy_policy`;',
        'SELECT 1;'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;


-- -------------------------------------------
-- Add column google_calendar_link
-- -------------------------------------------
SET @sql = (
    SELECT IF(
        (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
         WHERE TABLE_NAME='users' AND COLUMN_NAME='google_calendar_link') = 0,
        'ALTER TABLE `users` ADD `google_calendar_link` VARCHAR(255) NULL AFTER `google_calendar_id`;',
        'SELECT 1;'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;


-- -------------------------------------------
-- Add column calendar_sync_status
-- -------------------------------------------
SET @sql = (
    SELECT IF(
        (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
         WHERE TABLE_NAME='users' AND COLUMN_NAME='calendar_sync_status') = 0,
        'ALTER TABLE `users` ADD `calendar_sync_status` TINYINT(1) DEFAULT 0 AFTER `google_calendar_link`;',
        'SELECT 1;'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
    /*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
    /*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
