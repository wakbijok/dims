<?php
// src/includes/models/HardwareSpec.php

require_once __DIR__ . '/BaseModel.php';

class HardwareSpec extends BaseModel {
    protected $table_name = "hardware_specs";
    
    public function create($data) {
        // Check if serial number is unique if provided
        if (!empty($data['serial_number'])) {
            if ($this->valueExists('serial_number', $data['serial_number'])) {
                throw new Exception("Serial number already exists");
            }
        }
        
        $query = "INSERT INTO " . $this->table_name . "
                (server_id, cpu, memory, storage, serial_number)
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        // Clean and bind data
        $server_id = htmlspecialchars(strip_tags($data['server_id']));
        $cpu = !empty($data['cpu']) ? htmlspecialchars(strip_tags($data['cpu'])) : null;
        $memory = !empty($data['memory']) ? htmlspecialchars(strip_tags($data['memory'])) : null;
        $storage = !empty($data['storage']) ? htmlspecialchars(strip_tags($data['storage'])) : null;
        $serial_number = !empty($data['serial_number']) ? htmlspecialchars(strip_tags($data['serial_number'])) : null;
        
        $stmt->bindParam(1, $server_id);
        $stmt->bindParam(2, $cpu);
        $stmt->bindParam(3, $memory);
        $stmt->bindParam(4, $storage);
        $stmt->bindParam(5, $serial_number);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
    
    public function update($id, $data) {
        // Check if serial number is unique if provided
        if (!empty($data['serial_number'])) {
            if ($this->valueExists('serial_number', $data['serial_number'], $id)) {
                throw new Exception("Serial number already exists");
            }
        }
        
        $query = "UPDATE " . $this->table_name . "
                SET server_id = ?,
                    cpu = ?,
                    memory = ?,
                    storage = ?,
                    serial_number = ?
                WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        // Clean and bind data
        $server_id = htmlspecialchars(strip_tags($data['server_id']));
        $cpu = !empty($data['cpu']) ? htmlspecialchars(strip_tags($data['cpu'])) : null;
        $memory = !empty($data['memory']) ? htmlspecialchars(strip_tags($data['memory'])) : null;
        $storage = !empty($data['storage']) ? htmlspecialchars(strip_tags($data['storage'])) : null;
        $serial_number = !empty($data['serial_number']) ? htmlspecialchars(strip_tags($data['serial_number'])) : null;
        
        $stmt->bindParam(1, $server_id);
        $stmt->bindParam(2, $cpu);
        $stmt->bindParam(3, $memory);
        $stmt->bindParam(4, $storage);
        $stmt->bindParam(5, $serial_number);
        $stmt->bindParam(6, $id);
        
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
        $query = "SELECT hs.*, 
                        s.hostname as server_hostname,
                        s.ip_address as server_ip
                 FROM " . $this->table_name . " hs
                 LEFT JOIN servers s ON hs.server_id = s.id
                 ORDER BY hs.id ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    public function search($keyword) {
        $query = "SELECT hs.*, 
                        s.hostname as server_hostname,
                        s.ip_address as server_ip
                 FROM " . $this->table_name . " hs
                 LEFT JOIN servers s ON hs.server_id = s.id
                 WHERE s.hostname LIKE ? OR s.ip_address LIKE ? OR hs.cpu LIKE ? OR hs.memory LIKE ? OR hs.storage LIKE ? OR hs.serial_number LIKE ?
                 ORDER BY hs.id ASC";
        
        $keyword = "%{$keyword}%";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $keyword);
        $stmt->bindParam(2, $keyword);
        $stmt->bindParam(3, $keyword);
        $stmt->bindParam(4, $keyword);
        $stmt->bindParam(5, $keyword);
        $stmt->bindParam(6, $keyword);
        $stmt->execute();
        return $stmt;
    }
}