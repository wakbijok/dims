<?php
// src/api/index.php

// Allow CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Include database configuration
require_once '../config/database.php';

// Parse request URI
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri_parts = explode('/', trim($request_uri, '/'));

// Remove 'api' from the beginning if present
if ($uri_parts[0] === 'api') {
    array_shift($uri_parts);
}

// Get the resource type and ID
$resource = $uri_parts[0] ?? null;
$id = $uri_parts[1] ?? null;

// Create database connection
$database = new Database();
$db = $database->getConnection();

try {
    switch ($resource) {
        case 'servers':
            require_once '../includes/api/ServerApi.php';
            $controller = new ServerApi($db, $_SERVER['REQUEST_METHOD'], $id);
            break;
            
        case 'locations':
            require_once '../includes/api/LocationApi.php';
            $controller = new LocationApi($db, $_SERVER['REQUEST_METHOD'], $id);
            break;
            
        case 'environments':
            require_once '../includes/api/EnvironmentApi.php';
            $controller = new EnvironmentApi($db, $_SERVER['REQUEST_METHOD'], $id);
            break;
            
        case 'services':
            require_once '../includes/api/ServiceApi.php';
            $controller = new ServiceApi($db, $_SERVER['REQUEST_METHOD'], $id);
            break;
            
        // hardware endpoint removed - hardware specs now integrated into servers
            
        case 'backups':
            require_once '../includes/api/BackupConfigApi.php';
            $controller = new BackupConfigApi($db, $_SERVER['REQUEST_METHOD'], $id);
            break;
            
        case 'licenses':
            require_once '../includes/api/LicenseApi.php';
            $controller = new LicenseApi($db, $_SERVER['REQUEST_METHOD'], $id);
            break;
            
        default:
            throw new Exception('Invalid resource type');
    }
    
    $controller->processRequest();
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}