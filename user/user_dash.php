<?php

include('../DB/database.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login_logout/Login.php");
    exit();
}

if ($_SESSION['role'] === 'admin') {
    header("Location: ../admin/admin_dash.php");
    exit();
}

$username = htmlspecialchars($_SESSION['username'] ?? 'User');
$email    = htmlspecialchars($_SESSION['email']    ?? '');
$role     = htmlspecialchars($_SESSION['role']     ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>EDM – User Dashboard</title>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet"/>
  <link rel="stylesheet" href="user_dash.css">
  
</head>
<body>

  <nav class="topnav">
    <div class="topnav-brand">EDM</div>
    <div class="topnav-user">
      <div class="user-info">
        <div class="user-name"><?= $username ?></div>
        <div class="user-email"><?= $email ?></div>
      </div>
      <div class="avatar"><?= strtoupper(substr($username, 0, 1)) ?></div>
      <a href="../login_logout/logout.php" class="btn-logout">
        <span class="material-icons-outlined">logout</span>
        Logout
      </a>
    </div>
  </nav>

  <main class="main">
    <div class="welcome-card">

      <div class="deco-line"></div>

      <h1 class="greeting">
        Welcome back,<br><em><?= $username ?></em>
      </h1>

      <p class="sub">
        You're logged in to the EDM platform.<br>
        More features are on their way.
      </p>

      <div class="cards">
        <div class="card">
          <div class="card-icon"><span class="material-icons-outlined">person</span></div>
          <div class="card-label">Role</div>
          <div class="card-value" style="font-size:16px; text-transform:capitalize;">
            <?= htmlspecialchars($role) ?>
          </div>
        </div>
        <div class="card">
          <div class="card-icon"><span class="material-icons-outlined">verified_user</span></div>
          <div class="card-label">Status</div>
          <div class="card-value" style="font-size:16px; color: #6fcf97;">Active</div>
        </div>
        <div class="card">
          <div class="card-icon"><span class="material-icons-outlined">mail_outline</span></div>
          <div class="card-label">Email</div>
          <div class="card-value" style="font-size:11px; color: var(--muted); font-weight:400; word-break:break-all;">
            <?= $email ?>
          </div>
        </div>
      </div>

      <div class="notice">
        <span class="material-icons-outlined">info</span>
        <span>This is your personal dashboard. Contact an administrator if you need to update your account details.</span>
      </div>

    </div>
  </main>

  <footer>
    &copy; <?= date('Y') ?> IUSJC EDM Platform. All rights reserved.
  </footer>

</body>
</html>