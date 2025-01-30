<?php
// Prevent direct access to this file
if (!defined('BASE_PATH')) {
    die('Direct access to this file is not allowed');
}

/**
 * Database connection function
 */
function db_connect() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        $conn->set_charset("utf8mb4");
        return $conn;
    } catch (Exception $e) {
        error_log("Database connection error: " . $e->getMessage());
        die("Error: Unable to connect to database. Please check the configuration.");
    }
}

/**
 * Check if user is logged in
 */
function check_login() {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

/**
 * Get color class for status badges
 */
function get_status_color($status) {
    switch ($status) {
        case 'Active':
            return 'success';
        case 'Inactive':
            return 'secondary';
        case 'Maintenance':
            return 'warning';
        case 'Retired':
            return 'danger';
        case 'Reserved':
            return 'info';
        default:
            return 'secondary';
    }
}

/**
 * Sanitize user input
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Log user activity
 */
function log_activity($user_id, $action, $details) {
    try {
        $conn = db_connect();
        $stmt = $conn->prepare("INSERT INTO asset_history (changed_by, change_type, changes) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $action, $details);
        $stmt->execute();
        $stmt->close();
    } catch (Exception $e) {
        error_log("Error logging activity: " . $e->getMessage());
    }
}

/**
 * Get user information
 */
function get_user_info($user_id) {
    $conn = db_connect();
    $stmt = $conn->prepare("SELECT id, username, email, is_admin FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    return $user;
}

/**
 * Check if user is admin
 */
function is_admin($user_id) {
    $user = get_user_info($user_id);
    return isset($user['is_admin']) && $user['is_admin'] == 1;
}

/**
 * Format date and time
 */
function format_datetime($datetime) {
    return date('Y-m-d H:i', strtotime($datetime));
}

/**
 * Get list of environments
 */
function get_environments() {
    $conn = db_connect();
    $result = $conn->query("SELECT * FROM environments ORDER BY name");
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get list of locations
 */
function get_locations() {
    $conn = db_connect();
    $result = $conn->query("SELECT * FROM locations ORDER BY name");
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get list of projects
 */
function get_projects() {
    $conn = db_connect();
    $result = $conn->query("SELECT * FROM projects ORDER BY name");
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Display error message
 */
function display_error($message) {
    return '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                ' . $message . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
}

/**
 * Display success message
 */
function display_success($message) {
    return '<div class="alert alert-success alert-dismissible fade show" role="alert">
                ' . $message . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
}

/**
 * Generate password hash
 */
function generate_password_hash($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => HASH_COST]);
}

/**
 * Verify password
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}