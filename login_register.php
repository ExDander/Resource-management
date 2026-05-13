<?php
session_start();
require_once 'database.php';

// Helper function to handle AJAX responses
function send_response($message, $success = true)
{
  if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    if (!$success) http_response_code(400);
    echo $message;
    exit();
  }
}

/* REGISTER PAGE BACKEND */
if (isset($_POST['register'])) {
  $firstName = $_POST['first_name'];
  $lastName = $_POST['last_name'];
  $trCode = $_POST['tr_code'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $role = $_POST['role'];
  $department = $_POST['department'];

  // 1. Check if TR-Code exists
  $checkStmt = $conn->prepare('SELECT is_deleted FROM Users WHERE TR_Code = ?');
  $checkStmt->bind_param('s', $trCode);
  $checkStmt->execute();
  $result = $checkStmt->get_result();

  if ($user = $result->fetch_assoc()) {
    $errorMsg = ($user['is_deleted'] == 1)
      ? 'This TR-code is deactivated. Please contact an admin.'
      : 'TR-code is already registered';

    send_response($errorMsg, false); // Handle AJAX error

    $_SESSION['register_error'] = $errorMsg;
    $_SESSION['active_form'] = 'register';
  } else {
    $email = $trCode . '@g.batstate-u.edu.ph';

    $insertSql = "INSERT INTO Users (First_Name, Last_Name, TR_Code, Users_Email, Users_Password, Roles_ID, Department_ID)
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param('sssssii', $firstName, $lastName, $trCode, $email, $password, $role, $department);

    if ($insertStmt->execute()) {
      send_response('Successfully registered!'); // Handle AJAX success
      $_SESSION['register_success'] = 'Successfully registered!';
    } else {
      send_response('Registration failed: ' . $conn->error, false);
      $_SESSION['register_error'] = 'Registration failed: ' . $conn->error;
    }
    $insertStmt->close();
  }

  $checkStmt->close();
  header('Location: index.php');
  exit();
}

/* LOGIN PAGE BACKEND */
if (isset($_POST['login'])) {
  $trCode = $_POST['tr_code'];
  $password = $_POST['password'];

  $loginStmt = $conn->prepare('SELECT * FROM Users WHERE TR_Code = ?');
  $loginStmt->bind_param('s', $trCode);
  $loginStmt->execute();
  $result = $loginStmt->get_result();

  if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    if ((int)$user['is_deleted'] === 1) {
      $_SESSION['login_error'] = 'Your account has been deactivated. Please contact an admin.';
      $_SESSION['active_form'] = 'login';
      header('Location: index.php');
      exit();
    }

    if (password_verify($password, $user['Users_Password'])) {
      $_SESSION['name'] = $user['First_Name'] . ' ' . $user['Last_Name'];
      $_SESSION['userid'] = $user['Users_ID'];
      $_SESSION['email'] = $user['Users_Email'];
      $_SESSION['department'] = $user['Department_ID'];

      $_SESSION['departmentDisplay'] = match ((int) $user['Department_ID']) {
        1 => 'CICS',
        2 => 'CET',
        3 => 'CAS',
        4 => 'CABEIHM',
        default => 'UNKNOWN',
      };

      $_SESSION['role'] = ((int)$user['Roles_ID'] === 1) ? 'admin' : 'faculty';

      $redirect = ((int)$user['Roles_ID'] === 1) ? 'admin_page.php' : 'user_page.php';
      header("Location: $redirect");
      exit();
    }
  }

  $_SESSION['login_error'] = 'Incorrect TR-code or password';
  $_SESSION['active_form'] = 'login';
  header('Location: index.php');
  exit();
}
