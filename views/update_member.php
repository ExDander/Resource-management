<?php
session_start();
require_once '../database.php';

/* 1. CHECK AUTHENTICATION */
if (!isset($_SESSION['email'])) {
    http_response_code(403);
    echo "Unauthorized access.";
    exit();
}

/* 2. HANDLE THE POST REQUEST */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get ID from POST
    $user_id = $_POST['user_id'] ?? null;

    if (!$user_id) {
        http_response_code(400);
        echo "User ID is missing.";
        exit();
    }

    // Sanitize inputs
    $firstName  = $conn->real_escape_string($_POST['first_name']);
    $lastName   = $conn->real_escape_string($_POST['last_name']);
    $email      = $conn->real_escape_string($_POST['email']);
    $trCode     = $conn->real_escape_string($_POST['tr_code']); // Added TR Code
    $role       = $conn->real_escape_string($_POST['role']);

    // Prepare the update query including TR_Code
    $updateSql = "UPDATE Users SET 
                    First_Name = '$firstName', 
                    Last_Name = '$lastName', 
                    Users_Email = '$email',
                    TR_Code = '$trCode', 
                    Roles_ID = '$role' 
                  WHERE Users_ID = '$user_id'";

    if ($conn->query($updateSql)) {
        // Success!
        echo "Member updated successfully";
    } else {
        // Error
        http_response_code(500);
        echo "Error updating user: " . $conn->error;
    }
    exit();
}
