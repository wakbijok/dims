<?php
// src/includes/api/HardwareSpecApi.php

require_once __DIR__ . '/BaseApi.php';
require_once __DIR__ . '/../models/HardwareSpec.php';

class HardwareSpecApi extends BaseApi {
    private $model;
    
    public function __construct($db, $requestMethod, $id = null) {
        parent::__construct($db, $requestMethod, $id);
        $this->model = new HardwareSpec($db);
    }
    
    protected function getAll() {
        try {
            if (isset($_GET['server_id'])) {
                $result = $this->model->getByServerId($_GET['server_id']);
            } elseif (isset($_GET['search'])) {
                $result = $this->model->search($_GET['search']);
            } else {
                $result = $this->model->getAllWithServerDetails();
            }
            
            $specs = $result->fetchAll(PDO::FETCH_ASSOC);
            $this->sendResponse(['data' => $specs]);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
    
    protected function getOne($id) {
        try {
            $result = $this->model->getById($id);
            $spec = $result->fetch(PDO::FETCH_ASSOC);
            
            if (!$spec) {
                $this->sendError("Hardware specification not found", 404);
                return;
            }
            
            $this->sendResponse(['data' => $spec]);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
    
    protected function create() {
        try {
            $data = $this->getJSONBody();
            $this->validateRequired($data, ['server_id']);
            
            if (!empty($data['serial_number'])) {
                if ($this->model->valueExists('serial_number', $data['serial_number'])) {
                    throw new Exception("Serial number already exists");
                }
            }
            
            $id = $this->model->create($data);
            if ($id) {
                $this->sendResponse(['message' => 'Hardware specification created successfully', 'id' => $id], 201);
            } else {
                $this->sendError('Failed to create hardware specification');
            }
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
    
    protected function update($id) {
        try {
            $data = $this->getJSONBody();
            $this->validateRequired($data, ['server_id']);
            
            if (!empty($data['serial_number'])) {
                if ($this->model->valueExists('serial_number', $data['serial_number'], $id)) {
                    throw new Exception("Serial number already exists");
                }
            }
            
            if ($this->model->update($id, $data)) {
                $this->sendResponse(['message' => 'Hardware specification updated successfully']);
            } else {
                $this->sendError('Failed to update hardware specification');
            }
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
    
    protected function delete($id) {
        try {
            if ($this->model->delete($id)) {
                $this->sendResponse(['message' => 'Hardware specification deleted successfully']);
            } else {
                $this->sendError('Failed to delete hardware specification');
            }
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
}