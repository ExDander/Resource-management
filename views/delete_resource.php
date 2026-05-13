<?php
session_start();
require_once '../database.php'; // Adjusted path to match your structure

header('Content-Type: application/json');

// Security check (Optional but recommended)
if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resource_id'])) {
    $resourceId = intval($_POST['resource_id']);

    // 1. Check if the resource is used in the reservations table
    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM reservation WHERE Resource_ID = ?");
    $checkStmt->bind_param("i", $resourceId);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($count > 0) {
        // 2. Resource IS in use: Perform a Soft Delete
        $stmt = $conn->prepare("UPDATE resource SET is_deleted = 1 WHERE Resource_ID = ?");
        $message = 'Resource marked as inactive (soft deleted) due to existing reservations.';
    } else {
        // 3. Resource NOT in use: Perform a Hard Delete
        $stmt = $conn->prepare("DELETE FROM resource WHERE Resource_ID = ?");
        $message = 'Resource permanently deleted.';
    }

    $stmt->bind_param("i", $resourceId);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => $message]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
    }

    $stmt->close();
    exit;
}
