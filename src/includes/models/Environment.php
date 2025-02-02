<?php
// src/includes/models/Environment.php

require_once __DIR__ . '/BaseModel.php';

class Environment extends BaseModel {
    protected $table_name = "environments";
    
    public function create($data) {
        // Check if name is unique
        if ($this->valueExists('name', $data['name'])) {
            throw new Exception("Environment name already exists");
        }
        
        $query = "INSERT INTO " . $this->table_name . " (name) VALUES (?)";
        $stmt = $this->conn->prepare($query);
        
        // Clean and bind data
        $name = htmlspecialchars(strip_tags($data['name']));
        $stmt->bindParam(1, $name);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
    
    public function update($id, $data) {
        // Check if name is unique
        if ($this->valueExists('name', $data['name'], $id)) {
            throw new Exception("Environment name already exists");
        }
        
        $query = "UPDATE " . $this->table_name . " SET name = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        
        // Clean and bind data
        $name = htmlspecialchars(strip_tags($data['name']));
        $stmt->bindParam(1, $name);
        $stmt->bindParam(2, $id);
        
        return $stmt->execute();
    }
}