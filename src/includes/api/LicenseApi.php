<?php
// src/includes/api/LicenseApi.php

require_once __DIR__ . '/BaseApi.php';
require_once __DIR__ . '/../models/License.php';

class LicenseApi extends BaseApi {
    private $model;
    
    public function __construct($db, $requestMethod, $id = null) {
        parent::__construct($db, $requestMethod, $id);
        $this->model = new License($db);
    }
    
    protected function getAll() {
        try {
            if (isset($_GET['expiring'])) {
                $days = isset($_GET['days']) ? (int)$_GET['days'] : 30;
                $result = $this->model->getExpiringLicenses($days);
            } else if (isset($_GET['expired'])) {
                $result = $this->model->getExpiredLicenses();
            } else if (isset($_GET['server_id'])) {
                $result = $this->model->getByServerId($_GET['server_id']);
            } else {
                $result = $this->model->getAllWithServerDetails();
            }
            
            $licenses = $result->fetchAll(PDO::FETCH_ASSOC);
            $this->sendResponse(['data' => $licenses]);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
    
    protected function getOne($id) {
        try {
            $result = $this->model->getById($id);
            $license = $result->fetch(PDO::FETCH_ASSOC);
            
            if (!$license) {
                $this->sendError("License not found", 404);
                return;
            }
            
            $this->sendResponse(['data' => $license]);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
    
    protected function create() {
        try {
            $data = $this->getJSONBody();
            $this->validateRequired($data, ['server_id', 'license_type', 'expiry_date']);
            
            // Validate expiry date format
            if (!strtotime($data['expiry_date'])) {
                throw new Exception("Invalid expiry date format");
            }
            
            $id = $this->model->create($data);
            if ($id) {
                $this->sendResponse(['message' => 'License created successfully', 'id' => $id], 201);
            } else {
                $this->sendError('Failed to create license');
            }
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
    
    protected function update($id) {
        try {
            $data = $this->getJSONBody();
            $this->validateRequired($data, ['server_id', 'license_type', 'expiry_date']);
            
            // Validate expiry date format
            if (!strtotime($data['expiry_date'])) {
                throw new Exception("Invalid expiry date format");
            }
            
            if ($this->model->update($id, $data)) {
                $this->sendResponse(['message' => 'License updated successfully']);
            } else {
                $this->sendError('Failed to update license');
            }
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
    
    protected function delete($id) {
        try {
            if ($this->model->delete($id)) {
                $this->sendResponse(['message' => 'License deleted successfully']);
            } else {
                $this->sendError('Failed to delete license');
            }
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
}