-- Migration script to add server_type column to existing servers table
-- Run this on existing database to add the new column

USE dims_db;

-- Check if server_type column exists, if not add it
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'dims_db' 
AND TABLE_NAME = 'servers' 
AND COLUMN_NAME = 'server_type';

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE servers ADD COLUMN server_type ENUM(''VM'', ''Physical'') DEFAULT ''VM'' AFTER ip_address', 
    'SELECT ''server_type column already exists'' AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Make ip_address NOT NULL if not already
ALTER TABLE servers 
MODIFY COLUMN ip_address VARCHAR(255) UNIQUE NOT NULL;

-- Show updated table structure
DESCRIBE servers;