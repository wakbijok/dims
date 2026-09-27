<?php
// src/includes/api/ServerApi.php

require_once __DIR__ . '/BaseApi.php';
require_once __DIR__ . '/../models/Server.php';

class ServerApi extends BaseApi {
    private $model;
    
    public function __construct($db, $requestMethod, $id = null) {
        parent::__construct($db, $requestMethod, $id);
        $this->model = new Server($db);
    }
    
    protected function getAll() {
        try {
            $searchTerm = $_GET['search'] ?? null;
            $locationId = $_GET['location_id'] ?? null;
            $environmentId = $_GET['environment_id'] ?? null;
            
            if ($searchTerm || $locationId || $environmentId) {
                $result = $this->model->searchAndFilter($searchTerm, $locationId, $environmentId);
            } else {
                $result = $this->model->getAllWithDetails();
            }
            
            $servers = $result->fetchAll(PDO::FETCH_ASSOC);
            $this->sendResponse(['data' => $servers]);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
    
    protected function getOne($id) {
        try {
            $result = $this->model->getById($id);
            $server = $result->fetch(PDO::FETCH_ASSOC);
            
            if (!$server) {
                $this->sendError("Server not found", 404);
                return;
            }
            
            $this->sendResponse(['data' => $server]);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
    
    protected function create() {
        try {
            $data = $this->getJSONBody();
            $this->validateRequired($data, ['location_id', 'environment_id']);
            
            $id = $this->model->create($data);
            if ($id) {
                $this->sendResponse(['message' => 'Server created successfully', 'id' => $id], 201);
            } else {
                $this->sendError('Failed to create server');
            }
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
    
    protected function update($id) {
        try {
            $data = $this->getJSONBody();
            
            // For status-only updates (bulk operations), don't require all fields
            $isStatusOnlyUpdate = isset($data['status']) && count($data) <= 3 && 
                                 (!isset($data['location_id']) || !isset($data['environment_id']));
            
            if (!$isStatusOnlyUpdate) {
                $this->validateRequired($data, ['location_id', 'environment_id']);
            }
            
            if ($this->model->update($id, $data)) {
                $this->sendResponse(['message' => 'Server updated successfully']);
            } else {
                $this->sendError('Failed to update server');
            }
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
    
    protected function delete($id) {
        try {
            if ($this->model->delete($id)) {
                $this->sendResponse(['message' => 'Server deleted successfully']);
            } else {
                $this->sendError('Failed to delete server');
            }
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
}