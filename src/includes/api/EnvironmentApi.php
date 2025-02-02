<?php
// src/includes/api/EnvironmentApi.php

require_once __DIR__ . '/BaseApi.php';
require_once __DIR__ . '/../models/Environment.php';

class EnvironmentApi extends BaseApi {
    private $model;
    
    public function __construct($db, $requestMethod, $id = null) {
        parent::__construct($db, $requestMethod, $id);
        $this->model = new Environment($db);
    }
    
    protected function getAll() {
        try {
            $result = $this->model->getAll();
            $environments = $result->fetchAll(PDO::FETCH_ASSOC);
            $this->sendResponse(['data' => $environments]);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
    
    protected function getOne($id) {
        try {
            $result = $this->model->getById($id);
            $environment = $result->fetch(PDO::FETCH_ASSOC);
            
            if (!$environment) {
                $this->sendError("Environment not found", 404);
                return;
            }
            
            $this->sendResponse(['data' => $environment]);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
    
    protected function create() {
        try {
            $data = $this->getJSONBody();
            $this->validateRequired($data, ['name']);
            
            $id = $this->model->create($data);
            if ($id) {
                $this->sendResponse(['message' => 'Environment created successfully', 'id' => $id], 201);
            } else {
                $this->sendError('Failed to create environment');
            }
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
    
    protected function update($id) {
        try {
            $data = $this->getJSONBody();
            $this->validateRequired($data, ['name']);
            
            if ($this->model->update($id, $data)) {
                $this->sendResponse(['message' => 'Environment updated successfully']);
            } else {
                $this->sendError('Failed to update environment');
            }
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
    
    protected function delete($id) {
        try {
            if ($this->model->delete($id)) {
                $this->sendResponse(['message' => 'Environment deleted successfully']);
            } else {
                $this->sendError('Failed to delete environment');
            }
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
}