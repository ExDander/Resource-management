<?php
session_start();
require_once '../database.php';

/* 1. CHECK AUTHENTICATION */
// Adjust 'user_id' to match the exact session key where you store the logged-in user's ID
if (!isset($_SESSION['userid'])) {
    http_response_code(403);
    echo "Unauthorized access. Please log in.";
    exit();
}

/* 2. HANDLE THE POST REQUEST */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Server-side validation check
    if (empty($newPassword) || empty($confirmPassword)) {
        http_response_code(400);
        echo "Both password fields are required.";
        exit();
    }

    if ($newPassword !== $confirmPassword) {
        http_response_code(400);
        echo "Passwords do not match.";
        exit();
    }

    // Get the User ID safely from the session variables (prevents tampering)
    $session_user_id = $conn->real_escape_string($_SESSION['userid']);

    // Securely hash the password string
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    $escapedPassword = $conn->real_escape_string($hashedPassword);

    // Update query statement matching the current session ID
    // Note: Change 'Password' to your exact database column name if different
    $updateSql = "UPDATE Users SET 
                    Users_Password = '$escapedPassword' 
                  WHERE Users_ID = '$session_user_id'";

    if ($conn->query($updateSql)) {
        echo "Password updated successfully.";
    } else {
        http_response_code(500);
        echo "Error updating password: " . $conn->error;
    }
    exit();
}
