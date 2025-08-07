<?php
// src/includes/models/Server.php

require_once __DIR__ . '/BaseModel.php';

class Server extends BaseModel {
    protected $table_name = "servers";
    
    public function create($data) {
        // Check if IP is unique for active servers only
        if (!empty($data['ip_address'])) {
            $status = !empty($data['status']) ? $data['status'] : 'Active';
            if ($status === 'Active' && $this->ipExistsForActiveServer($data['ip_address'])) {
                throw new Exception("IP address already exists for an active server");
            }
        }
        
        $query = "INSERT INTO " . $this->table_name . "
                 (location_id, environment_id, hostname, ip_address, server_type, status, decommission_date, description, 
                  cpu_type, cpu_cores, memory_gb, storage_details, serial_number)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        // Clean and bind data
        $location_id = htmlspecialchars(strip_tags($data['location_id']));
        $environment_id = htmlspecialchars(strip_tags($data['environment_id']));
        $hostname = htmlspecialchars(strip_tags($data['hostname']));
        $ip_address = !empty($data['ip_address']) ? htmlspecialchars(strip_tags($data['ip_address'])) : null;
        $server_type = !empty($data['server_type']) ? htmlspecialchars(strip_tags($data['server_type'])) : 'VM';
        $status = !empty($data['status']) ? htmlspecialchars(strip_tags($data['status'])) : 'Active';
        $decommission_date = !empty($data['decommission_date']) ? $data['decommission_date'] : null;
        $description = htmlspecialchars(strip_tags($data['description']));
        
        // Hardware fields
        $cpu_type = !empty($data['cpu_type']) ? htmlspecialchars(strip_tags($data['cpu_type'])) : null;
        $cpu_cores = !empty($data['cpu_cores']) ? intval($data['cpu_cores']) : null;
        $memory_gb = !empty($data['memory_gb']) ? intval($data['memory_gb']) : null;
        $storage_details = !empty($data['storage_details']) ? htmlspecialchars(strip_tags($data['storage_details'])) : null;
        $serial_number = !empty($data['serial_number']) ? htmlspecialchars(strip_tags($data['serial_number'])) : null;
        
        $stmt->bindParam(1, $location_id);
        $stmt->bindParam(2, $environment_id);
        $stmt->bindParam(3, $hostname);
        $stmt->bindParam(4, $ip_address);
        $stmt->bindParam(5, $server_type);
        $stmt->bindParam(6, $status);
        $stmt->bindParam(7, $decommission_date);
        $stmt->bindParam(8, $description);
        $stmt->bindParam(9, $cpu_type);
        $stmt->bindParam(10, $cpu_cores);
        $stmt->bindParam(11, $memory_gb);
        $stmt->bindParam(12, $storage_details);
        $stmt->bindParam(13, $serial_number);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
    
    public function update($id, $data) {
        // Check if IP is unique for active servers only
        if (!empty($data['ip_address'])) {
            $status = !empty($data['status']) ? $data['status'] : 'Active';
            if ($status === 'Active' && $this->ipExistsForActiveServer($data['ip_address'], $id)) {
                throw new Exception("IP address already exists for an active server");
            }
        }
        
        // Build dynamic update query based on provided fields
        $updateFields = [];
        $params = [];
        
        $allowedFields = [
            'location_id', 'environment_id', 'hostname', 'ip_address', 'server_type',
            'status', 'decommission_date', 'description', 'cpu_type', 'cpu_cores',
            'memory_gb', 'storage_details', 'serial_number'
        ];
        
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $updateFields[] = "{$field} = ?";
                
                if ($field === 'cpu_cores' || $field === 'memory_gb') {
                    $params[] = !empty($data[$field]) ? intval($data[$field]) : null;
                } elseif ($field === 'decommission_date') {
                    $params[] = !empty($data[$field]) ? $data[$field] : null;
                } else {
                    $params[] = !empty($data[$field]) ? htmlspecialchars(strip_tags($data[$field])) : null;
                }
            }
        }
        
        if (empty($updateFields)) {
            throw new Exception("No valid fields to update");
        }
        
        $query = "UPDATE " . $this->table_name . " SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $params[] = $id;
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($params);
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
    
    public function searchAndFilter($searchTerm = null, $locationId = null, $environmentId = null) {
        $query = "SELECT s.*, 
                        l.name as location_name,
                        e.name as environment_name
                 FROM " . $this->table_name . " s
                 LEFT JOIN locations l ON s.location_id = l.id
                 LEFT JOIN environments e ON s.environment_id = e.id
                 WHERE 1=1";
        
        $params = [];
        
        if ($searchTerm) {
            $query .= " AND (s.hostname LIKE ? OR s.ip_address LIKE ? OR s.description LIKE ? OR s.cpu_type LIKE ? OR s.serial_number LIKE ?)";
            $searchWildcard = "%{$searchTerm}%";
            $params = array_merge($params, [$searchWildcard, $searchWildcard, $searchWildcard, $searchWildcard, $searchWildcard]);
        }
        
        if ($locationId) {
            $query .= " AND s.location_id = ?";
            $params[] = $locationId;
        }
        
        if ($environmentId) {
            $query .= " AND s.environment_id = ?";
            $params[] = $environmentId;
        }
        
        $query .= " ORDER BY s.id ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }
    
    /**
     * Check if IP address exists for active servers
     * @param string $ip_address
     * @param int $exclude_id Optional ID to exclude from check (for updates)
     * @return bool
     */
    private function ipExistsForActiveServer($ip_address, $exclude_id = null) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                 WHERE ip_address = ? AND status = 'Active'";
        
        $params = [$ip_address];
        
        if ($exclude_id) {
            $query .= " AND id != ?";
            $params[] = $exclude_id;
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] > 0;
    }
}