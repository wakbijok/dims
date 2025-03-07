<?php
// src/includes/models/BackupConfig.php

require_once __DIR__ . '/BaseModel.php';

class BackupConfig extends BaseModel {
    protected $table_name = "backup_configs";
    
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . "
                (server_id, backup_type, schedule, retention_period)
                VALUES (?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        // Clean and bind data
        $server_id = htmlspecialchars(strip_tags($data['server_id']));
        $backup_type = !empty($data['backup_type']) ? htmlspecialchars(strip_tags($data['backup_type'])) : null;
        $schedule = !empty($data['schedule']) ? htmlspecialchars(strip_tags($data['schedule'])) : null;
        $retention_period = !empty($data['retention_period']) ? htmlspecialchars(strip_tags($data['retention_period'])) : null;
        
        $stmt->bindParam(1, $server_id);
        $stmt->bindParam(2, $backup_type);
        $stmt->bindParam(3, $schedule);
        $stmt->bindParam(4, $retention_period);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
    
    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . "
                SET server_id = ?,
                    backup_type = ?,
                    schedule = ?,
                    retention_period = ?
                WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        // Clean and bind data
        $server_id = htmlspecialchars(strip_tags($data['server_id']));
        $backup_type = !empty($data['backup_type']) ? htmlspecialchars(strip_tags($data['backup_type'])) : null;
        $schedule = !empty($data['schedule']) ? htmlspecialchars(strip_tags($data['schedule'])) : null;
        $retention_period = !empty($data['retention_period']) ? htmlspecialchars(strip_tags($data['retention_period'])) : null;
        
        $stmt->bindParam(1, $server_id);
        $stmt->bindParam(2, $backup_type);
        $stmt->bindParam(3, $schedule);
        $stmt->bindParam(4, $retention_period);
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
        $query = "SELECT bc.*, 
                        s.hostname as server_hostname,
                        s.ip_address as server_ip
                 FROM " . $this->table_name . " bc
                 LEFT JOIN servers s ON bc.server_id = s.id
                 ORDER BY bc.id ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    public function getOverdueBackups() {
        $query = "SELECT bc.*, 
                        s.hostname as server_hostname,
                        s.ip_address as server_ip
                 FROM " . $this->table_name . " bc
                 LEFT JOIN servers s ON bc.server_id = s.id
                 WHERE bc.updated_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)
                 ORDER BY bc.updated_at ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}