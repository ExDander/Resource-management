<?php

session_start();

/* STORES ERROR MESSAGES FROM SESSION */
$errors = [
  'login' => $_SESSION['login_error'] ?? '',
  'register' => $_SESSION['register_error'] ?? '',
  'tos' => $_SESSION['tos_error'] ?? '', // For tracking backend validation errors
];

/* DETERMINE WHICH FORM IS ACTIVE */
$activeForm = $_SESSION['active_form'] ?? 'login';

$successMessage = $_SESSION['register_success'] ?? '';

session_unset();

function showError($error)
{
  return !empty($error) ? "<p class='error-message'>$error</p>" : '';
}

function isActiveForm($formName, $activeForm)
{
  return $formName === $activeForm ? 'active' : '';
}

function showMessage($message)
{
  return !empty($message) ? "<p class='success-message'>$message</p>" : '';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <title>Login/Register</title>
</head>

<body class="indexpage">

  <!-- 1. HEADER ADDED HERE -->
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

  <!-- YOUR INITIAL ELEMENTS UNCHANGED -->
  <div class="container">
    <div class="form-box <?= isActiveForm('login', $activeForm) ?>" id="login-form">
      <form action="login_register.php" method="post">
        <h2 class="welcome">Login</h2>
        <!-- shows error via html element using showError Function -->
        <?= showError($errors['login']) ?>
        <?= showError($errors['tos']) ?>
        <?= showMessage($successMessage) ?>
        <input class="input" name="tr_code" placeholder="TR-Code" required>
        <input class="input" type="password" name="password" placeholder="Password" required>

        <!-- Terms of Service Checkbox Section -->
        <div id="tos-container">
          <input type="checkbox" id="tos" name="tos" value="1" required>
          <label for="tos">I agree to the <a href="tos.php">Terms of Service</a></label>
        </div>

        <button type="submit" class="login-register" name="login">Login</button>
      </form>
      <hr>
      <p style="margin-top: 20px;">Forgot password? <a href="admin-contacts.php">Contact your administrator.</a></p>
    </div>
  </div>


  <script src="script.js"></script>
</body>

</html>