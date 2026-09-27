-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS dims_db;
USE dims_db;

-- Create locations table
CREATE TABLE IF NOT EXISTS locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create environments table
CREATE TABLE IF NOT EXISTS environments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create servers table
CREATE TABLE IF NOT EXISTS servers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    location_id INT,
    environment_id INT,
    hostname VARCHAR(255),
    ip_address VARCHAR(255) NOT NULL,
    server_type ENUM('VM', 'Physical') DEFAULT 'VM',
    status ENUM('Active', 'Decommissioned') DEFAULT 'Active',
    decommission_date DATE NULL,
    description TEXT,
    -- Hardware specifications (for Physical servers)
    cpu_type VARCHAR(100),
    cpu_cores INT,
    memory_gb INT,
    storage_details TEXT,
    serial_number VARCHAR(100) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (location_id) REFERENCES locations(id),
    FOREIGN KEY (environment_id) REFERENCES environments(id),
    -- Unique IP constraint only for Active servers
    UNIQUE KEY unique_active_ip (ip_address, status)
);

-- Create services table
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    server_id INT,
    url VARCHAR(255),
    protocol VARCHAR(50),
    port INT,
    username VARCHAR(255),
    password VARCHAR(255),
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (server_id) REFERENCES servers(id)
);

-- Hardware specs table removed - now integrated into servers table

-- Create backup_configs table
CREATE TABLE IF NOT EXISTS backup_configs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    server_id INT,
    backup_type VARCHAR(50),
    schedule VARCHAR(100),
    retention_period VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (server_id) REFERENCES servers(id)
);

-- Create licenses table
CREATE TABLE IF NOT EXISTS licenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    server_id INT,
    license_type VARCHAR(100),
    expiry_date DATE,
    support_level VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (server_id) REFERENCES servers(id)
);

-- Create system_logs table
CREATE TABLE IF NOT EXISTS system_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action_type ENUM('CREATE', 'UPDATE', 'DELETE') NOT NULL,
    resource_type VARCHAR(50) NOT NULL,
    resource_id INT NOT NULL,
    changes JSON,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default locations
INSERT INTO locations (name) VALUES 
('DC'),
('DRC'),
('Staging DRC');

-- Insert default environments
INSERT INTO environments (name) VALUES 
('Production'),
('Development'),
('Testing'),
('Staging');