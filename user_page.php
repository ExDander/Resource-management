<?php

session_start();
include 'database.php';

/* USER MUST LOG IN FIRST */
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Page</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Sharp" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="dashboard.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>

<body class="userpage">
    <div class="dashcontainer">
        <aside>
            <div class="top">
                <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ0k98o10a-aETn-9WbEtua2omE1OqJ7HnD5g&amp;s" alt="Logo" class="header-logo">
                <div style="display: flex; flex-direction: column;" gap="10px" align-items="center">
                    <h1 style="color: white; font-size: 1.5rem;"><?= $_SESSION['departmentDisplay'] ?> <?= $_SESSION['role'] ?></h1>
                    <h2 style="color: white; font-size: 1rem;"><?= $_SESSION['name'] ?></h2>
                </div>
            </div>
            <div class="sidebar">
                <a href="#" class="sidebar-link active" data-section="dashboard">
                    <span class="material-symbols-sharp"> space_dashboard </span>
                    <h3>Dashboard</h3>
                </a>
                <a href="#" class="sidebar-link" data-section="resources">
                    <span class="material-symbols-sharp">library_add</span>
                    <h3>Resources</h3>
                </a>
                <a href="#" class="sidebar-link" data-section="reservations">
                    <span class="material-symbols-sharp"> edit_calendar </span>
                    <h3>Reservations</h3>
                </a>
                <!-- Logout stays a noral link -->
                <a href="logout.php">
                    <span class="material-symbols-sharp">logout</span>
                    <h3>Logout</h3>
                </a>
            </div>

        </aside>
        <main id="main-content">
        </main>
    </div>

    <script src="jquery.js"></script>
</body>



</html>