<?php
session_start();
require_once '../database.php';

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    echo "Unauthorized";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);

    // 1. Check if the user has any reservation history
    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM reservation WHERE Users_ID = ?");
    $checkStmt->bind_param("i", $user_id);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($count > 0) {
        // 2. User has history: Soft Delete (deactivate account)
        $stmt = $conn->prepare("UPDATE Users SET is_deleted = 1 WHERE Users_ID = ?");
        $message = "User has history; account deactivated (soft deleted).";
    } else {
        // 3. User has no history: Hard Delete
        $stmt = $conn->prepare("DELETE FROM Users WHERE Users_ID = ?");
        $message = "User deleted successfully.";
    }

    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        echo "Success: " . $message;
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt->close();
    exit();
} else {
    echo "Invalid request";
    exit();
}
