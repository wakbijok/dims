<?php
// src/includes/Logger.php

class Logger {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function logAction($actionType, $resourceType, $resourceId, $changes = null) {
        try {
            $query = "INSERT INTO system_logs 
                    (action_type, resource_type, resource_id, changes, ip_address) 
                    VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($query);
            
            // Get client IP address
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
            
            // Convert changes array to JSON if it exists
            $changesJson = $changes ? json_encode($changes) : null;
            
            $stmt->bindParam(1, $actionType);
            $stmt->bindParam(2, $resourceType);
            $stmt->bindParam(3, $resourceId);
            $stmt->bindParam(4, $changesJson);
            $stmt->bindParam(5, $ipAddress);
            
            return $stmt->execute();
        } catch (Exception $e) {
            // Log to PHP error log if database logging fails
            error_log("Failed to log action: " . $e->getMessage());
            return false;
        }
    }
    
    public function getChanges($oldData, $newData) {
        $changes = [
            'before' => [],
            'after' => []
        ];
        
        foreach ($newData as $key => $value) {
            if (!isset($oldData[$key]) || $oldData[$key] !== $value) {
                $changes['before'][$key] = $oldData[$key] ?? null;
                $changes['after'][$key] = $value;
            }
        }
        
        return $changes;
    }
}