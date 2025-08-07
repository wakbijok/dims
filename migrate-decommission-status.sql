-- Migration script to add decommission status fields to servers table
-- Run this to add status and decommission_date fields

USE dims_db;

-- Check and add status column
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'dims_db' AND TABLE_NAME = 'servers' AND COLUMN_NAME = 'status';
SET @sql = IF(@col_exists = 0, 'ALTER TABLE servers ADD COLUMN status ENUM(''Active'', ''Decommissioned'') DEFAULT ''Active'' AFTER server_type', 'SELECT ''status column already exists'' AS message');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Check and add decommission_date column
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'dims_db' AND TABLE_NAME = 'servers' AND COLUMN_NAME = 'decommission_date';
SET @sql = IF(@col_exists = 0, 'ALTER TABLE servers ADD COLUMN decommission_date DATE NULL AFTER status', 'SELECT ''decommission_date column already exists'' AS message');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Drop existing unique constraint on ip_address
ALTER TABLE servers DROP INDEX ip_address;

-- Add new constraint that allows duplicate IPs for decommissioned servers
-- This will fail silently if constraint already exists
ALTER TABLE servers ADD CONSTRAINT unique_active_ip UNIQUE (ip_address, status);

-- Show updated table structure
DESCRIBE servers;