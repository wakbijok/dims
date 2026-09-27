<?php
// src/includes/models/License.php

require_once __DIR__ . '/BaseModel.php';

class License extends BaseModel {
    protected $table_name = "licenses";
    
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . "
                (server_id, license_type, expiry_date, support_level)
                VALUES (?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        // Clean and bind data
        $server_id = htmlspecialchars(strip_tags($data['server_id']));
        $license_type = !empty($data['license_type']) ? htmlspecialchars(strip_tags($data['license_type'])) : null;
        $expiry_date = !empty($data['expiry_date']) ? htmlspecialchars(strip_tags($data['expiry_date'])) : null;
        $support_level = !empty($data['support_level']) ? htmlspecialchars(strip_tags($data['support_level'])) : null;
        
        $stmt->bindParam(1, $server_id);
        $stmt->bindParam(2, $license_type);
        $stmt->bindParam(3, $expiry_date);
        $stmt->bindParam(4, $support_level);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
    
    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . "
                SET server_id = ?,
                    license_type = ?,
                    expiry_date = ?,
                    support_level = ?
                WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        // Clean and bind data
        $server_id = htmlspecialchars(strip_tags($data['server_id']));
        $license_type = !empty($data['license_type']) ? htmlspecialchars(strip_tags($data['license_type'])) : null;
        $expiry_date = !empty($data['expiry_date']) ? htmlspecialchars(strip_tags($data['expiry_date'])) : null;
        $support_level = !empty($data['support_level']) ? htmlspecialchars(strip_tags($data['support_level'])) : null;
        
        $stmt->bindParam(1, $server_id);
        $stmt->bindParam(2, $license_type);
        $stmt->bindParam(3, $expiry_date);
        $stmt->bindParam(4, $support_level);
        $stmt->bindParam(5, $id);
        
        return $stmt->execute();
    }
    
    public function getByServerId($server_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE server_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $server_id);
        $stmt->execute();
        return $stmt;
    }
    
    public function getAllWithServerDetails() {
        $query = "SELECT l.*, 
                        s.hostname as server_hostname,
                        s.ip_address as server_ip
                 FROM " . $this->table_name . " l
                 LEFT JOIN servers s ON l.server_id = s.id
                 ORDER BY l.expiry_date ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    public function getExpiringLicenses($days = 30) {
        $query = "SELECT l.*, 
                        s.hostname as server_hostname,
                        s.ip_address as server_ip
                 FROM " . $this->table_name . " l
                 LEFT JOIN servers s ON l.server_id = s.id
                 WHERE l.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                 ORDER BY l.expiry_date ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $days);
        $stmt->execute();
        return $stmt;
    }
    
    public function getExpiredLicenses() {
        $query = "SELECT l.*, 
                        s.hostname as server_hostname,
                        s.ip_address as server_ip
                 FROM " . $this->table_name . " l
                 LEFT JOIN servers s ON l.server_id = s.id
                 WHERE l.expiry_date < CURDATE()
                 ORDER BY l.expiry_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}