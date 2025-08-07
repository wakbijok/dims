<?php
// src/includes/models/Server.php

require_once __DIR__ . '/BaseModel.php';

class Server extends BaseModel {
    protected $table_name = "servers";
    
    public function create($data) {
        // Check if IP is unique if provided
        if (!empty($data['ip_address'])) {
            if ($this->valueExists('ip_address', $data['ip_address'])) {
                throw new Exception("IP address already exists");
            }
        }
        
        $query = "INSERT INTO " . $this->table_name . "
                 (location_id, environment_id, hostname, ip_address, server_type, description)
                 VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        // Clean and bind data
        $location_id = htmlspecialchars(strip_tags($data['location_id']));
        $environment_id = htmlspecialchars(strip_tags($data['environment_id']));
        $hostname = htmlspecialchars(strip_tags($data['hostname']));
        $ip_address = !empty($data['ip_address']) ? htmlspecialchars(strip_tags($data['ip_address'])) : null;
        $server_type = !empty($data['server_type']) ? htmlspecialchars(strip_tags($data['server_type'])) : 'VM';
        $description = htmlspecialchars(strip_tags($data['description']));
        
        $stmt->bindParam(1, $location_id);
        $stmt->bindParam(2, $environment_id);
        $stmt->bindParam(3, $hostname);
        $stmt->bindParam(4, $ip_address);
        $stmt->bindParam(5, $server_type);
        $stmt->bindParam(6, $description);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
    
    public function update($id, $data) {
        // Check if IP is unique if provided
        if (!empty($data['ip_address'])) {
            if ($this->valueExists('ip_address', $data['ip_address'], $id)) {
                throw new Exception("IP address already exists");
            }
        }
        
        $query = "UPDATE " . $this->table_name . "
                 SET location_id = ?,
                     environment_id = ?,
                     hostname = ?,
                     ip_address = ?,
                     server_type = ?,
                     description = ?
                 WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        // Clean and bind data
        $location_id = htmlspecialchars(strip_tags($data['location_id']));
        $environment_id = htmlspecialchars(strip_tags($data['environment_id']));
        $hostname = htmlspecialchars(strip_tags($data['hostname']));
        $ip_address = !empty($data['ip_address']) ? htmlspecialchars(strip_tags($data['ip_address'])) : null;
        $server_type = !empty($data['server_type']) ? htmlspecialchars(strip_tags($data['server_type'])) : 'VM';
        $description = htmlspecialchars(strip_tags($data['description']));
        
        $stmt->bindParam(1, $location_id);
        $stmt->bindParam(2, $environment_id);
        $stmt->bindParam(3, $hostname);
        $stmt->bindParam(4, $ip_address);
        $stmt->bindParam(5, $server_type);
        $stmt->bindParam(6, $description);
        $stmt->bindParam(7, $id);
        
        return $stmt->execute();
    }
    
    public function getAllWithDetails() {
        $query = "SELECT s.*, 
                        l.name as location_name,
                        e.name as environment_name
                 FROM " . $this->table_name . " s
                 LEFT JOIN locations l ON s.location_id = l.id
                 LEFT JOIN environments e ON s.environment_id = e.id
                 ORDER BY s.id ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    public function search($keyword) {
        $query = "SELECT s.*, 
                        l.name as location_name,
                        e.name as environment_name
                 FROM " . $this->table_name . " s
                 LEFT JOIN locations l ON s.location_id = l.id
                 LEFT JOIN environments e ON s.environment_id = e.id
                 WHERE s.hostname LIKE ? OR s.ip_address LIKE ? OR s.description LIKE ?
                 ORDER BY s.id ASC";
        
        $keyword = "%{$keyword}%";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $keyword);
        $stmt->bindParam(2, $keyword);
        $stmt->bindParam(3, $keyword);
        $stmt->execute();
        return $stmt;
    }
}