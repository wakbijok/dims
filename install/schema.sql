-- Create users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create projects table
CREATE TABLE projects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create locations table
CREATE TABLE locations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    type VARCHAR(50), -- DC, DR, Cloud, etc.
    address TEXT,
    contact_info TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create environments table
CREATE TABLE environments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    description TEXT
);

-- Create assets table
CREATE TABLE assets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT,
    location_id INT,
    environment_id INT,
    asset_type VARCHAR(50), -- Server, Storage, Network, etc.
    name VARCHAR(255),
    description TEXT,
    url VARCHAR(255),
    ip_address VARCHAR(255),
    protocol VARCHAR(50),
    port INT,
    username VARCHAR(100),
    password VARCHAR(255),
    alternate_ip VARCHAR(255),
    alternate_port INT,
    specifications TEXT,
    remarks TEXT,
    status VARCHAR(50),
    created_by INT,
    updated_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id),
    FOREIGN KEY (location_id) REFERENCES locations(id),
    FOREIGN KEY (environment_id) REFERENCES environments(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id)
);

-- Create asset_history table for tracking changes
CREATE TABLE asset_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    asset_id INT,
    changed_by INT,
    change_type VARCHAR(50), -- CREATE, UPDATE, DELETE
    changes TEXT, -- JSON format of changes
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (asset_id) REFERENCES assets(id),
    FOREIGN KEY (changed_by) REFERENCES users(id)
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, is_admin) 
VALUES ('admin', '$2y$10$dxeDWcqpvydxz0qCw5YZm.AkbzFGW8.PFcD8RnbzRXhGc7hRHHcFO', TRUE);

-- Insert default environments
INSERT INTO environments (name, description) VALUES 
('Production', 'Production Environment'),
('Staging', 'Staging Environment'),
('Development', 'Development Environment'),
('Testing', 'Testing Environment');

-- Insert sample locations
INSERT INTO locations (name, type) VALUES 
('Main DC', 'Primary'),
('DR Site', 'Disaster Recovery'),
('Cloud', 'Cloud Infrastructure');