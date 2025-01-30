<?php
define('BASE_PATH', __DIR__);
require_once 'config.php';
require_once 'functions.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Log the logout activity if user was logged in
if (isset($_SESSION['user_id'])) {
    log_activity($_SESSION['user_id'], 'LOGOUT', 'User logged out');
}

// Destroy all session data
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: login.php");
exit();