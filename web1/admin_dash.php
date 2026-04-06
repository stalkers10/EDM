<?php

session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: Login.php');
    exit;
}

include 'database.php'; 

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$allowed_pages = ['dashboard', 'users', 'analytics', 'settings'];
if (!in_array($page, $allowed_pages)) {
    $page = 'dashboard';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>EDM Admin – <?= ucfirst($page) ?></title>

  <!-- Stylesheet -->
  <link rel="stylesheet" href="admin_style.css"/>

  <!-- Google Material Icons -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet"/>
</head>
<body>

  <!-- ── SIDEBAR (left navigation) ── -->
  <?php include 'sidebar.php'; ?>

  <!-- ── MAIN CONTENT ── -->
  <main class="main-content">
    <?php
      if ($page === 'dashboard') {
          include 'dashboard.php';
      } elseif ($page === 'users') {
          include 'user_list_include.php';
      } elseif ($page === 'analytics') {
          echo '<div class="content-card"><h2 class="welcome-heading">Analytics</h2>
                <p class="welcome-sub">Coming soon.</p></div>';
      } elseif ($page === 'settings') {
          echo '<div class="content-card"><h2 class="welcome-heading">Settings</h2>
                <p class="welcome-sub">Coming soon.</p></div>';
      }
    ?>
  </main>

</body>
</html>