<?php
$host = '192.168.0.30';
$dbname = 'inventory_db'; 
$username = 'db_user';
$password = 'db_pass';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
  case 'GET':
    $page = $_GET['page'] ?? 1;
    $limit = $_GET['limit'] ?? 10;
    $offset = ($page - 1) * $limit;
    
    $stmt = $db->prepare("SELECT * FROM inventory LIMIT ? OFFSET ?");
    $stmt->execute([$limit, $offset]);
    
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($items);
    break;
    
  case 'POST':
    $item = json_decode(file_get_contents('php://input'), true);
    
    $stmt = $db->prepare("INSERT INTO inventory (description, dc_drc_lb, environment, url, ip_address, protocol, port, username, password, new_ip, new_port, remarks, serial_number) 
                          VALUES (:description, :dc_drc_lb, :environment, :url, :ip_address, :protocol, :port, :username, :password, :new_ip, :new_port, :remarks, :serial_number)");
    $stmt->execute($item);
    
    echo json_encode(['id' => $db->lastInsertId()]);
    break;
    
  case 'PUT':
    $item = json_decode(file_get_contents('php://input'), true);
    $id = $item['id'];
    
    $stmt = $db->prepare("UPDATE inventory SET description = :description, dc_drc_lb = :dc_drc_lb, environment = :environment, url = :url, ip_address = :ip_address, 
                          protocol = :protocol, port = :port, username = :username, password = :password, new_ip = :new_ip, new_port = :new_port, remarks = :remarks, serial_number = :serial_number 
                          WHERE id = :id");
    $item['id'] = $id;
    $stmt->execute($item);
    
    echo json_encode(['updated' => true]);
    break;
    
  case 'DELETE':
    $id = $_GET['id'];
    
    $stmt = $db->prepare("DELETE FROM inventory WHERE id = ?");
    $stmt->execute([$id]);
    
    echo json_encode(['deleted' => true]);
    break;
    
  default:
    http_response_code(405); // Method Not Allowed
    break;
}