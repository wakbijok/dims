-- Fix IP constraint for decommission functionality
-- This script handles cases where constraints might already exist

USE dims_db;

-- First, let's see what constraints exist
SELECT CONSTRAINT_NAME, CONSTRAINT_TYPE 
FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS 
WHERE TABLE_NAME = 'servers' AND TABLE_SCHEMA = 'dims_db';

-- Drop the old ip_address unique constraint if it exists
SET @sql = (SELECT IF(
    EXISTS(
        SELECT * FROM INFORMATION_SCHEMA.STATISTICS 
        WHERE TABLE_NAME = 'servers' 
        AND TABLE_SCHEMA = 'dims_db' 
        AND INDEX_NAME = 'ip_address'
    ),
    'ALTER TABLE servers DROP INDEX ip_address',
    'SELECT "ip_address index does not exist" as message'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Drop the unique_active_ip constraint if it exists (to recreate it properly)
SET @sql = (SELECT IF(
    EXISTS(
        SELECT * FROM INFORMATION_SCHEMA.STATISTICS 
        WHERE TABLE_NAME = 'servers' 
        AND TABLE_SCHEMA = 'dims_db' 
        AND INDEX_NAME = 'unique_active_ip'
    ),
    'ALTER TABLE servers DROP INDEX unique_active_ip',
    'SELECT "unique_active_ip index does not exist" as message'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Now add the correct constraint
ALTER TABLE servers ADD CONSTRAINT unique_active_ip UNIQUE (ip_address, status);

-- Show final table structure
SHOW CREATE TABLE servers;

-- Test the constraint works - this should show all unique constraints
SELECT CONSTRAINT_NAME, CONSTRAINT_TYPE, COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
WHERE TABLE_NAME = 'servers' AND TABLE_SCHEMA = 'dims_db';