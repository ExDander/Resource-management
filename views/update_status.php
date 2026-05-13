<?php
session_start();
require_once '../database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $res_id = $_POST['resource_id'] ?? null;
  $start = $_POST['start_datetime'] ?? null;
  $end = $_POST['end_datetime'] ?? null;


  $status_to_set = $_POST['status'] ?? 2;

  $user_id = $_SESSION['userid'];
  $dept_id = $_SESSION['department'];

  if (!$res_id || !$start || !$end) {
    die('Error: Missing required fields.');
  }


  $sql = "INSERT INTO reservation (Users_ID, Resource_ID, Reservation_Date, Return_Date, Department_ID) 
          VALUES ('$user_id', '$res_id', '$start', '$end', '$dept_id')";

  if ($conn->query($sql)) {
    $update_sql = "UPDATE resource SET Status_ID = '$status_to_set' WHERE Resource_ID = '$res_id'";
    $conn->query($update_sql);

    echo 'Success';
    exit();
  } else {
    http_response_code(500);
    echo 'Database Error: ' . $conn->error;
  }
}
