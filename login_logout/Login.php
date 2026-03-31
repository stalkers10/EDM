<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - EDM</title>
  <link rel="stylesheet" href="Login.css">
</head>

<body>
  <nav>
    <h2 class="header-name">EDM login</h2>
  </nav>

  <div class="body-div">
    <form action="Login.php" method="post">
      <div class="Login-div">
        <p class="Login-pgh">Login</p>

        <div class="login-form">
          <label for="username">Username</label>
          <input type="text" name="username" id="log-username">

          <label for="password">Password</label>
          <input type="password" name="password" id="log-password">
        </div>

        <button id="btn-Login" name="login" type="submit">Login</button>

        <div class="selection-btm">
          <p>Don't have an account yet?</p>
          <a href="../signup/sign_up.php"><button type="button">Sign up</button></a>
        </div>

      </div>
    </form>
  </div>
</body>

<?php
include('../DB/database.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  $username = trim($_POST["username"]);
  $pswd     = $_POST["password"];

  // ── 1. Validate inputs ──
  if (empty($username)) {
    echo "<script>alert('Please enter your username'); window.location.href='Login.php';</script>";
    exit();
  }

  if (empty($pswd)) {
    echo "<script>alert('Please enter your password'); window.location.href='Login.php';</script>";
    exit();
  }

  // ── 2. Fetch user from DB ──
  $stmt = mysqli_prepare($conn, "SELECT id, username, email, passwd, role, status FROM users WHERE username = ? LIMIT 1");
  mysqli_stmt_bind_param($stmt, "s", $username);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $user   = mysqli_fetch_assoc($result);
  mysqli_stmt_close($stmt);

  // ── 3. Check user exists ──
  if (!$user) {
    echo "<script>alert('Username not found'); window.location.href='Login.php';</script>";
    exit();
  }

  // ── 4. Verify password ──
  if (!password_verify($pswd, $user['passwd'])) {
    echo "<script>alert('Incorrect password'); window.location.href='Login.php';</script>";
    exit();
  }

  // ── 5. Check account is active ──
  $is_active = ($user['status'] === 'active' || $user['status'] == 1);
  if (!$is_active) {
    echo "<script>alert('Your account is inactive. Contact the administrator.'); window.location.href='Login.php';</script>";
    exit();
  }

  // ── 6. Save common session data ──
  $_SESSION['user_id']  = $user['id'];
  $_SESSION['username'] = $user['username'];
  $_SESSION['email']    = $user['email'];
  $_SESSION['role']     = $user['role'];

  // ── 7. Redirect based on role ──
  if ($user['role'] === 'admin') {
    header("Location: ../admin/admin_dash.php");
  } else {
    header("Location: ../user/user_dash.php");
  }
  exit();
}
?>

</html>