<?php
// Admin Contacts Page
session_start();
require_once 'database.php';
$sql = "SELECT 
    u.Users_ID, 
    u.First_Name, 
    u.Last_Name, 
    u.Users_Email, 
    u.TR_Code, 
    d.Department_Name
FROM Users u
JOIN Department d ON u.Department_ID = d.Department_ID
WHERE u.Roles_ID = 1
    and is_deleted = 0";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="dashboard.css">
    <title>Admin Contacts</title>
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

            <h1>Admin Contacts</h1>
            <div class="resources">
                <table class="resource-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Department</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['First_Name'] . ' ' . $row['Last_Name']) ?></td>
                                <td><?= htmlspecialchars($row['Users_Email']) ?></td>
                                <td><?= htmlspecialchars($row['Department_Name']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>