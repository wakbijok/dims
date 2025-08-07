-- Migration script to add hardware specification fields to servers table
-- Run this to add hardware fields for server details

USE dims_db;

-- Check and add cpu_type column
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'dims_db' AND TABLE_NAME = 'servers' AND COLUMN_NAME = 'cpu_type';
SET @sql = IF(@col_exists = 0, 'ALTER TABLE servers ADD COLUMN cpu_type VARCHAR(100) AFTER description', 'SELECT ''cpu_type column already exists'' AS message');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Check and add cpu_cores column
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'dims_db' AND TABLE_NAME = 'servers' AND COLUMN_NAME = 'cpu_cores';
SET @sql = IF(@col_exists = 0, 'ALTER TABLE servers ADD COLUMN cpu_cores INT AFTER cpu_type', 'SELECT ''cpu_cores column already exists'' AS message');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Check and add memory_gb column
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'dims_db' AND TABLE_NAME = 'servers' AND COLUMN_NAME = 'memory_gb';
SET @sql = IF(@col_exists = 0, 'ALTER TABLE servers ADD COLUMN memory_gb INT AFTER cpu_cores', 'SELECT ''memory_gb column already exists'' AS message');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Check and add storage_details column
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'dims_db' AND TABLE_NAME = 'servers' AND COLUMN_NAME = 'storage_details';
SET @sql = IF(@col_exists = 0, 'ALTER TABLE servers ADD COLUMN storage_details TEXT AFTER memory_gb', 'SELECT ''storage_details column already exists'' AS message');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Check and add serial_number column
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'dims_db' AND TABLE_NAME = 'servers' AND COLUMN_NAME = 'serial_number';
SET @sql = IF(@col_exists = 0, 'ALTER TABLE servers ADD COLUMN serial_number VARCHAR(100) UNIQUE AFTER storage_details', 'SELECT ''serial_number column already exists'' AS message');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Show updated table structure
DESCRIBE servers;