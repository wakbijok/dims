<?php
// src/includes/api/LocationApi.php

require_once __DIR__ . '/BaseApi.php';
require_once __DIR__ . '/../models/Location.php';

class LocationApi extends BaseApi {
    private $model;
    
    public function __construct($db, $requestMethod, $id = null) {
        parent::__construct($db, $requestMethod, $id);
        $this->model = new Location($db);
    }
    
    protected function getAll() {
        try {
            $result = $this->model->getAll();
            $locations = $result->fetchAll(PDO::FETCH_ASSOC);
            $this->sendResponse(['data' => $locations]);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
    
    protected function getOne($id) {
        try {
            $result = $this->model->getById($id);
            $location = $result->fetch(PDO::FETCH_ASSOC);
            
            if (!$location) {
                $this->sendError("Location not found", 404);
                return;
            }
            
            $this->sendResponse(['data' => $location]);
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
                $this->sendResponse(['message' => 'Location created successfully', 'id' => $id], 201);
            } else {
                $this->sendError('Failed to create location');
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
                $this->sendResponse(['message' => 'Location updated successfully']);
            } else {
                $this->sendError('Failed to update location');
            }
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
    
    protected function delete($id) {
        try {
            if ($this->model->delete($id)) {
                $this->sendResponse(['message' => 'Location deleted successfully']);
            } else {
                $this->sendError('Failed to delete location');
            }
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
}