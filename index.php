<?php

session_start();

/* STORES ERROR MESSAGES FROM SESSION */
$errors = [
  'login' => $_SESSION['login_error'] ?? '',
  'register' => $_SESSION['register_error'] ?? '',
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

  <div class="container">
    <div class="form-box <?= isActiveForm('login', $activeForm) ?>" id="login-form">
      <form action="login_register.php" method="post">
        <h2 class="welcome">Login</h2>
        <!-- shows error via html element using showError Function -->
        <?= showError($errors['login']) ?>
        <?= showMessage($successMessage) ?>
        <input type="text" name="tr_code" placeholder="TR-Code" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" class="login-register" name="login">Login</button>
        <p class="reminder"> Don't have an account? <a href="#" onclick="showForm('register-form')">Register</a></p>
      </form>
    </div>

    <div class="form-box <?= isActiveForm('register', $activeForm) ?>" id="register-form">
      <form action="login_register.php" method="post">
        <h2 class="welcome">Register</h2>
        <!-- shows error via html element using showError Function -->
        <?= showError($errors['register']) ?>
        <input type="text" name="first_name" placeholder="First Name" required>
        <input type="text" name="last_name" placeholder="Last Name" required>
        <input type="text" name="tr_code" placeholder="TR-code" required>
        <input type="password" name="password" placeholder="Password" required>


        <select name="role" required>
          <option value="">Select Role</option>
          <option value="1">Admin</option>
          <option value="2">Faculty</option>
        </select>

        <select name="department" required>
          <option value="">Select Department</option>
          <option value="1">CICS<!--   College of Information and Computing Sciences --></option>
          <option value="2">CET <!-- College of Engineering and Technology --></option>
          <option value="3">CAS <!-- College of Arts and Sciences --></option>
          <option value="4">CABEIHM <!-- College of Accountancy, Business, Economics & International Hospitality Management --></option>
        </select>

        <button type="submit" class="login-register" name="register">Register</button>
        <p class="reminder"> Already have an account? <a href="#" onclick="showForm('login-form')">Login</a></p>
      </form>
    </div>
  </div>

  <script src="script.js"></script>
</body>

</html>