<?php
// src/includes/api/ServiceApi.php

require_once __DIR__ . '/BaseApi.php';
require_once __DIR__ . '/../models/Service.php';

class ServiceApi extends BaseApi {
    private $model;
    
    public function __construct($db, $requestMethod, $id = null) {
        parent::__construct($db, $requestMethod, $id);
        $this->model = new Service($db);
    }
    
    protected function getAll() {
        try {
            if (isset($_GET['server_id'])) {
                $result = $this->model->getByServerId($_GET['server_id']);
            } else {
                $result = $this->model->getAllWithServerDetails();
            }
            
            $services = $result->fetchAll(PDO::FETCH_ASSOC);
            $this->sendResponse(['data' => $services]);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
    
    protected function getOne($id) {
        try {
            $result = $this->model->getById($id);
            $service = $result->fetch(PDO::FETCH_ASSOC);
            
            if (!$service) {
                $this->sendError("Service not found", 404);
                return;
            }
            
            $this->sendResponse(['data' => $service]);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
    
    protected function create() {
        try {
            $data = $this->getJSONBody();
            $this->validateRequired($data, ['server_id']);
            
            $id = $this->model->create($data);
            if ($id) {
                $this->sendResponse(['message' => 'Service created successfully', 'id' => $id], 201);
            } else {
                $this->sendError('Failed to create service');
            }
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
    
    protected function update($id) {
        try {
            $data = $this->getJSONBody();
            $this->validateRequired($data, ['server_id']);
            
            if ($this->model->update($id, $data)) {
                $this->sendResponse(['message' => 'Service updated successfully']);
            } else {
                $this->sendError('Failed to update service');
            }
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
    
    protected function delete($id) {
        try {
            if ($this->model->delete($id)) {
                $this->sendResponse(['message' => 'Service deleted successfully']);
            } else {
                $this->sendError('Failed to delete service');
            }
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
}