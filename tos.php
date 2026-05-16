<?php
// Terms of Service Page
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="dashboard.css">
    <title>Terms of Service</title>
</head>

<body class="indexpage">
    <header class="main-header">
        <div class="header-content">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ0k98o10a-aETn-9WbEtua2omE1OqJ7HnD5g&amp;s" alt="Logo" class="header-logo">
            <h1>Resource Management System</h1>
            <nav>
                <a href="admin-contacts.php">Contacts</a>
                <a href="index.php">Login</a>
            </nav>
        </div>
    </header>

    <div class="wrapper" style="padding-top: 1.4rem; 
        padding-right: 2rem; 
        padding-left: 2.5rem; 
        overflow-y: auto;
      ">

        <div class="container-1">
            <h1>Terms of Service</h1>

            <div class="resources" style="line-height: 1.6; color: #333;">
                <p>Welcome to the Resource Management System. By accessing or using our platform, you agree to comply with and be bound by the following terms and conditions.</p>

                <h3 style="margin-top: 1.5rem;">1. Acceptable Use</h3>
                <p>Users must utilize system resources solely for authorized institutional and educational purposes. Unauthorized access, data modification, or sharing of official credentials is strictly prohibited.</p>

                <h3 style="margin-top: 1.5rem;">2. Account Responsibility</h3>
                <p>You are solely responsible for maintaining the confidentiality of your account credentials. Any activities performed under your session will be attributed to your account.</p>

                <h3 style="margin-top: 1.5rem;">3. Data Privacy</h3>
                <p>All data processed within this platform complies with standard institutional guidelines. Collected data will only be utilized for proper resource allocation and identity management functions.</p>

                <h3 style="margin-top: 1.5rem;">4. Modifications to Terms</h3>
                <p>The administration reserves the right to modify these terms at any given time. Continued use of the system following modifications constitutes acceptance of the revised terms.</p>
            </div>
        </div>
    </div>
</body>

</html>