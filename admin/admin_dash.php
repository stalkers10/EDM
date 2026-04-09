<?php
// Auth: redirect to login if not logged in
/*if (!isset($_SESSION['admin'])) {
    header('Location: ../login_logout/login.php');
    exit;
}*/

include '../DB/database.php';  

//(default page is dashboard)
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$allowed_pages = ['dashboard', 'users', 'manage_docs', 'settings'];
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

  <link rel="stylesheet" href="./admin_styles/admin_style.css"/>

  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet"/>
</head>
<body>

  <?php include 'sidebar.php'; ?>

  <main class="main-content">
    <?php
      if ($page === 'dashboard') {
          include 'dashboard.php';
      } elseif ($page === 'users') {
          include 'Manage_users/user_list_include.php';
      } elseif ($page === 'manage_docs') {
          include 'Manage_docs/manage_docs.php';
      } elseif ($page === 'settings') {
          echo '<div class="content-card"><h2 class="welcome-heading">Settings</h2>
                <p class="welcome-sub">Coming soon.</p></div>';
      }
    ?>
  </main>
</body>
</html>