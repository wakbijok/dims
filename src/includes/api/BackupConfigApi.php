<?php
// src/includes/api/BackupConfigApi.php

require_once __DIR__ . '/BaseApi.php';
require_once __DIR__ . '/../models/BackupConfig.php';

class BackupConfigApi extends BaseApi {
    private $model;
    
    public function __construct($db, $requestMethod, $id = null) {
        parent::__construct($db, $requestMethod, $id);
        $this->model = new BackupConfig($db);
    }
    
    protected function getAll() {
        try {
            $params = $_GET;
            
            if (isset($params['overdue'])) {
                $result = $this->model->getOverdueBackups();
            } elseif (isset($params['search']) || isset($params['backup_type']) || isset($params['server_id'])) {
                $result = $this->model->searchWithFilters($params);
            } else {
                $result = $this->model->getAllWithServerDetails();
            }
            
            $configs = $result->fetchAll(PDO::FETCH_ASSOC);
            $this->sendResponse(['data' => $configs]);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
    
    protected function getOne($id) {
        try {
            $result = $this->model->getById($id);
            $config = $result->fetch(PDO::FETCH_ASSOC);
            
            if (!$config) {
                $this->sendError("Backup configuration not found", 404);
                return;
            }
            
            $this->sendResponse(['data' => $config]);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
    
    protected function create() {
        try {
            $data = $this->getJSONBody();
            $this->validateRequired($data, ['server_id', 'backup_type', 'schedule']);
            
            $id = $this->model->create($data);
            if ($id) {
                $this->sendResponse(['message' => 'Backup configuration created successfully', 'id' => $id], 201);
            } else {
                $this->sendError('Failed to create backup configuration');
            }
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
    
    protected function update($id) {
        try {
            $data = $this->getJSONBody();
            $this->validateRequired($data, ['server_id', 'backup_type', 'schedule']);
            
            if ($this->model->update($id, $data)) {
                $this->sendResponse(['message' => 'Backup configuration updated successfully']);
            } else {
                $this->sendError('Failed to update backup configuration');
            }
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
    
    protected function delete($id) {
        try {
            if ($this->model->delete($id)) {
                $this->sendResponse(['message' => 'Backup configuration deleted successfully']);
            } else {
                $this->sendError('Failed to delete backup configuration');
            }
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
}