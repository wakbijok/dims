<?php
define('BASE_PATH', __DIR__);
require_once 'config.php';
require_once 'functions.php';
check_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$id) {
    header("Location: index.php?error=" . urlencode("Invalid asset ID"));
    exit();
}

$conn = db_connect();

try {
    // Start transaction
    $conn->begin_transaction();

    // Get asset details before deletion for logging
    $stmt = $conn->prepare("SELECT * FROM assets WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $asset = $stmt->get_result()->fetch_assoc();

    if (!$asset) {
        throw new Exception("Asset not found");
    }

    // Delete asset history records first (due to foreign key constraint)
    $stmt = $conn->prepare("DELETE FROM asset_history WHERE asset_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Delete the asset
    $stmt = $conn->prepare("DELETE FROM assets WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Log the deletion
    log_activity(
        $_SESSION['user_id'], 
        'DELETE', 
        json_encode([
            'asset_id' => $id,
            'asset_name' => $asset['name'],
            'asset_type' => $asset['asset_type'],
            'deleted_at' => date('Y-m-d H:i:s')
        ])
    );

    // Commit transaction
    $conn->commit();

    header("Location: index.php?success=1");
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    header("Location: index.php?error=" . urlencode("Error deleting asset: " . $e->getMessage()));
    exit();
}