<?php
// src/includes/models/BaseModel.php

require_once __DIR__ . '/Logger.php';

abstract class BaseModel {
    protected $conn;
    protected $table_name;
    protected $logger;
    
    public function __construct($db) {
        $this->conn = $db;
        $this->logger = new Logger($db);
    }
    
    // Get all records
    public function getAll($orderBy = 'id', $order = 'ASC') {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY " . $orderBy . " " . $order;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    // Get single record by ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt;
    }
    
    // Delete record
    public function delete($id) {
        // Get the current data before deletion
        $currentData = $this->getById($id)->fetch(PDO::FETCH_ASSOC);
        
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        
        if ($stmt->execute()) {
            // Log the deletion
            $this->logger->logAction(
                'DELETE',
                $this->table_name,
                $id,
                ['deleted_data' => $currentData]
            );
            return true;
        }
        return false;
    }
    
    // Check if field value exists
    public function valueExists($field, $value, $excludeId = null) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . 
                " WHERE " . $field . " = ?";
        $params = [$value];
        
        if ($excludeId !== null) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] > 0;
    }
    
    // Protected method for logging changes
    protected function logCreation($id, $data) {
        $this->logger->logAction(
            'CREATE',
            $this->table_name,
            $id,
            ['created_data' => $data]
        );
    }
    
    protected function logUpdate($id, $oldData, $newData) {
        $changes = $this->logger->getChanges($oldData, $newData);
        if (!empty($changes['after'])) {
            $this->logger->logAction(
                'UPDATE',
                $this->table_name,
                $id,
                $changes
            );
        }
    }
}