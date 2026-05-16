<?php
session_start();
require_once '../database.php';

$isAdmin = ($_SESSION['role'] === 'admin');

$sql = "SELECT 
    u.Users_ID, 
    u.First_Name, 
    u.Last_Name, 
    u.Users_Email, 
    u.TR_Code, -- Add this line
    u.Roles_ID, 
    u.Department_ID,
    CASE WHEN u.Roles_ID = 1 THEN 'admin' ELSE 'faculty' END AS Role,
    d.Department_Name
FROM Users u
LEFT JOIN department d ON u.Department_ID = d.Department_ID
WHERE u.Department_ID = {$_SESSION['department']}
  AND u.is_deleted = 0;
";


$members = $conn->query($sql);
?>


<?php if ($isAdmin): ?>
    <div class="members-container">
        <!-- 1. Floating Action Button -->
        <button id="toggle-add-form" class="fab-button">
            <span class="material-icons">person_add</span>
        </button>
    </div>
<?php endif; ?>

<!-- 2. Registration Modal Overlay -->
<!-- Edit Member Modal -->
<!-- 1. Registration Modal Overlay (Targeted by FAB Button) -->
<div id="modal-overlay" class="modal-overlay">
    <div id="add-member-section" class="container-1 modal-content">
        <div class="modal-header">
            <h1 class="title">Register Member</h1>
            <span id="close-modal" class="material-icons close-btn">close</span>
        </div>

        <form id="register-form" class="ajax-form" action="login_register.php" method="post">
            <label>First Name:</label>
            <input type="text" name="first_name" required>

            <label>Last Name:</label>
            <input type="text" name="last_name" required>

            <label>TR Code:</label>
            <input type="text" name="tr_code" required>

            <label>Password:</label>
            <input type="password" name="password" required>

            <label>Role:</label>
            <select name="role" required>
                <option value="1">Admin</option>
                <option value="2">Faculty</option>
            </select>

            <label>Department:</label>
            <select name="department" required>
                <option value="<?= $_SESSION['department'] ?>"><?= $_SESSION['departmentDisplay'] ?></option>
            </select>

            <br><br>
            <input type="hidden" name="register" value="true">
            <input type="submit" value="Register Now" style="width: 100%;">
        </form>
    </div>
</div>

<!-- 2. Edit Member Modal (Updated with TR Code) -->
<div id="edit-member-modal-overlay" class="modal-overlay">
    <div class="container-1 modal-content">
        <div class="modal-header">
            <h1 class="title">Edit Member</h1>
            <span id="close-edit-member-modal" class="material-icons close-btn">close</span>
        </div>
        <form class="ajax-form" action="views/update_member.php" method="post">
            <input type="hidden" name="user_id" id="edit-member-id">

            <label>First Name:</label>
            <input type="text" name="first_name" id="edit-first-name" required>

            <label>Last Name:</label>
            <input type="text" name="last_name" id="edit-last-name" required>

            <label>TR Code:</label>
            <input type="text" name="tr_code" id="edit-tr-code" required>

            <label>Email:</label>
            <input type="email" name="email" id="edit-email" required>

            <label>Role:</label>
            <select name="role" id="edit-role">
                <option value="1">Admin</option>
                <option value="2">Faculty</option>
            </select>

            <input type="submit" value="Update Member" style="width: 100%; margin-top: 10px;">
        </form>
    </div>
</div>

<div class="resources" id="members">
    <?php if ($members && $members->num_rows > 0): ?>
        <table class="members-table">
            <thead>
                <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>TR Code</th> <!-- New Header -->
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($member = $members->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($member['First_Name']) ?></td>
                        <td><?= htmlspecialchars($member['Last_Name']) ?></td>
                        <td><?= htmlspecialchars($member['TR_Code'] ?? 'N/A') ?></td> <!-- New Data Cell -->
                        <td><?= htmlspecialchars($member['Users_Email']) ?></td>
                        <td><?= htmlspecialchars($member['Role']) ?></td>
                        <td>
                            <div class="admin-controls">
                                <button type="button"
                                    class="reserve-use open-edit-member-btn"
                                    data-id="<?= $member['Users_ID'] ?>"
                                    data-fname="<?= htmlspecialchars($member['First_Name']) ?>"
                                    data-lname="<?= htmlspecialchars($member['Last_Name']) ?>"
                                    data-email="<?= htmlspecialchars($member['Users_Email']) ?>"
                                    data-trcode="<?= htmlspecialchars($member['TR_Code'] ?? '') ?>"
                                    data-role="<?= $member['Roles_ID'] ?>">
                                    <span class="material-icons">edit</span> Edit
                                </button>

                                <form class="ajax-form delete-form" method="post" action="views/delete_member.php" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?= $member['Users_ID'] ?>">
                                    <button type="submit" class="reserve-use" id="delete-btn" onclick="return confirm('Are you sure you want to delete this member?')">
                                        <span class="material-icons">delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No members found</p>
    <?php endif; ?>
</div>
</div>