<?php
session_start();
require_once '../database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resource_id'])) {
    $resource_id = intval($_POST['resource_id']);

    // Set Status_ID to 3 (In Use)
    $query = "UPDATE resource SET Status_ID = 3 WHERE Resource_ID = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $resource_id);

    if ($stmt->execute()) {
        http_response_code(200);
        echo "Resource is now in use.";
    } else {
        http_response_code(500);
        echo "Error: " . $conn->error;
    }
}
