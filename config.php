<?php
// Prevent direct access to this file
if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__);
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'dcims_db');
define('DB_USER', 'dcims_user');
define('DB_PASS', 'your_password_here'); // This will be updated by install script

// Site configuration
define('SITE_NAME', 'Data Center Inventory Management System');
define('SITE_SHORT_NAME', 'DCIMS');
define('SITE_URL', 'http://inventory.local');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);

// Timezone
date_default_timezone_set('Asia/Kuala_Lumpur');

// Asset types
define('ASSET_TYPES', [
    'Server' => 'Physical or Virtual Server',
    'Storage' => 'Storage System',
    'Network' => 'Network Equipment',
    'Security' => 'Security Appliance',
    'Application' => 'Application Service',
    'Database' => 'Database System',
    'Other' => 'Other Equipment'
]);

// Status options
define('STATUS_OPTIONS', [
    'Active' => 'Currently in use',
    'Inactive' => 'Not in use',
    'Maintenance' => 'Under maintenance',
    'Retired' => 'No longer in service',
    'Reserved' => 'Reserved for future use'
]);

// Version
define('DCIMS_VERSION', '1.0.0');

// Security
define('HASH_COST', 10); // for password hashing