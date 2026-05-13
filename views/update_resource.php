<?php
session_start();
require_once '../database.php';

if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    http_response_code(403);
    echo "Unauthorized access.";
    exit;
}

// 1. Added 'status_id' to the validation check
if (isset($_POST['resource_id'], $_POST['resource_name'], $_POST['categories_id'], $_POST['status_id'])) {

    $resourceId = $_POST['resource_id'];
    $resourceName = trim($_POST['resource_name']);
    $categoryId = $_POST['categories_id'];
    $statusId = $_POST['status_id']; // New variable

    // 2. Updated the SQL to include Status_ID
    $sql = "UPDATE resource 
            SET Resource_Name = ?, Categories_ID = ?, Status_ID = ? 
            WHERE Resource_ID = ?";

    if ($stmt = $conn->prepare($sql)) {
        // Updated to "siii" (string, int, int, int)
        $stmt->bind_param("siii", $resourceName, $categoryId, $statusId, $resourceId);

        if ($stmt->execute()) {
            echo "Resource updated successfully.";
        } else {
            http_response_code(500);
            echo "Error updating record: " . $conn->error;
        }
        $stmt->close();
    } else {
        http_response_code(500);
        echo "Database preparation error.";
    }
} else {
    http_response_code(400);
    echo "Missing required fields.";
}

$conn->close();
