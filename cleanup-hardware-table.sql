-- Cleanup script to remove hardware_specs table since hardware specs are now in servers table
-- Run this to clean up the old hardware table

USE dims_db;

-- Drop hardware_specs table if it exists
DROP TABLE IF EXISTS hardware_specs;

-- Show remaining tables
SHOW TABLES;