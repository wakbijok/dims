<?php
// src/includes/models/Service.php

require_once __DIR__ . '/BaseModel.php';

class Service extends BaseModel {
    protected $table_name = "services";
    
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . "
                (server_id, url, protocol, port, username, password, remarks)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        // Clean and bind data
        $server_id = htmlspecialchars(strip_tags($data['server_id']));
        $url = !empty($data['url']) ? htmlspecialchars(strip_tags($data['url'])) : null;
        $protocol = !empty($data['protocol']) ? htmlspecialchars(strip_tags($data['protocol'])) : null;
        $port = !empty($data['port']) ? htmlspecialchars(strip_tags($data['port'])) : null;
        $username = !empty($data['username']) ? htmlspecialchars(strip_tags($data['username'])) : null;
        $password = !empty($data['password']) ? htmlspecialchars(strip_tags($data['password'])) : null;
        $remarks = !empty($data['remarks']) ? htmlspecialchars(strip_tags($data['remarks'])) : null;
        
        $stmt->bindParam(1, $server_id);
        $stmt->bindParam(2, $url);
        $stmt->bindParam(3, $protocol);
        $stmt->bindParam(4, $port);
        $stmt->bindParam(5, $username);
        $stmt->bindParam(6, $password);
        $stmt->bindParam(7, $remarks);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
    
    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . "
                SET server_id = ?,
                    url = ?,
                    protocol = ?,
                    port = ?,
                    username = ?,
                    password = ?,
                    remarks = ?
                WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        // Clean and bind data
        $server_id = htmlspecialchars(strip_tags($data['server_id']));
        $url = !empty($data['url']) ? htmlspecialchars(strip_tags($data['url'])) : null;
        $protocol = !empty($data['protocol']) ? htmlspecialchars(strip_tags($data['protocol'])) : null;
        $port = !empty($data['port']) ? htmlspecialchars(strip_tags($data['port'])) : null;
        $username = !empty($data['username']) ? htmlspecialchars(strip_tags($data['username'])) : null;
        $password = !empty($data['password']) ? htmlspecialchars(strip_tags($data['password'])) : null;
        $remarks = !empty($data['remarks']) ? htmlspecialchars(strip_tags($data['remarks'])) : null;
        
        $stmt->bindParam(1, $server_id);
        $stmt->bindParam(2, $url);
        $stmt->bindParam(3, $protocol);
        $stmt->bindParam(4, $port);
        $stmt->bindParam(5, $username);
        $stmt->bindParam(6, $password);
        $stmt->bindParam(7, $remarks);
        $stmt->bindParam(8, $id);
        
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
        $query = "SELECT s.*, 
                        srv.hostname as server_hostname,
                        srv.ip_address as server_ip
                 FROM " . $this->table_name . " s
                 LEFT JOIN servers srv ON s.server_id = srv.id
                 ORDER BY s.id ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}