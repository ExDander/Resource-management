<div class="container-1">
    <h2 class="title">Change Password</h2>

    <!-- Status message box for AJAX or session responses -->
    <div id="password-message" style="margin-bottom: 15px; font-weight: bold;"></div>

    <!-- REMOVED: onsubmit attribute to let the main script handle it cleanly -->
    <form id="change-password-form" class="ajax-form" action="views/update_password.php" method="post">

        <label for="new-password">New Password:</label>
        <input type="password" id="new-password" name="new_password" required minlength="6" style="width: 100%; margin-bottom: 15px;">

        <label for="confirm-password">Confirm New Password:</label>
        <input type="password" id="confirm-password" name="confirm_password" required minlength="6" style="width: 100%; margin-bottom: 15px;">

        <input type="submit" value="Update Password" style="width: 100%; padding: 10px;">
    </form>
</div>