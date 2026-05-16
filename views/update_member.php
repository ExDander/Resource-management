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
    $trCode     = $conn->real_escape_string($_POST['tr_code']);
    $role       = $conn->real_escape_string($_POST['role']);
    $password   = $_POST['password'] ?? ''; // Get password string safely

    // Base query update template
    $updateSql = "UPDATE Users SET 
                    First_Name = '$firstName', 
                    Last_Name = '$lastName', 
                    Users_Email = '$email',
                    TR_Code = '$trCode', 
                    Roles_ID = '$role'";

    // Check if a new password was provided in the input form field
    if (!empty($password)) {
        // Securely hash the password string
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $escapedPassword = $conn->real_escape_string($hashedPassword);

        // Append password column name to update query statement
        // Note: Change 'Password' to your exact database password column name if different
        $updateSql .= ", Users_Password = '$escapedPassword'";
    }

    // Conclude query syntax with targeted user ID matching rule
    $updateSql .= " WHERE Users_ID = '$user_id'";

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
