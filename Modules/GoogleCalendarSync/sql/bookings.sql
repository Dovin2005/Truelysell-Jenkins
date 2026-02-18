/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

START TRANSACTION;

-- -------------------------------------------
-- Add column google_calendar_event_id to bookings table
-- -------------------------------------------
SET @sql = (
    SELECT IF(
        (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
         WHERE TABLE_NAME='bookings' AND COLUMN_NAME='google_calendar_event_id') = 0,
        'ALTER TABLE `bookings` ADD `google_calendar_event_id` VARCHAR(255) NULL AFTER `bank_transfer_proof`;',
        'SELECT 1;'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- -------------------------------------------
-- Add column google_calendar_link to bookings table
-- -------------------------------------------
SET @sql = (
    SELECT IF(
        (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
         WHERE TABLE_NAME='bookings' AND COLUMN_NAME='google_calendar_link') = 0,
        'ALTER TABLE `bookings` ADD `google_calendar_link` TEXT NULL AFTER `google_calendar_event_id`;',
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


