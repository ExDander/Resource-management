<?php
session_start();
require_once '../database.php'; // Updated to match your dashboard's include

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resource_id'])) {
    $resource_id = intval($_POST['resource_id']);

    // 1. Update the RESOURCE table to set status back to 'Returned' (ID 5)
    // IMPORTANT: Check if your table name is 'resource' or 'resources'
    $query = "UPDATE resource SET Status_ID = 5 WHERE Resource_ID = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $resource_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            http_response_code(200);
            echo "Success";
        } else {
            // This happens if the ID doesn't exist or is already Status 5
            http_response_code(400);
            echo "No changes made. Verify Resource ID.";
        }
    } else {
        http_response_code(500);
        echo "DB Error: " . $conn->error;
    }
}
