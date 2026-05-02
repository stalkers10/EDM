<?php
require_once __DIR__ . '/../DB/database.php';

if (!isset($_SESSION['user_id']) || (($_SESSION['role'] ?? null) !== 'admin')) {
    header('Location: ../login_logout/Login.php');
    exit;
}

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$allowed_pages = ['dashboard', 'users', 'manage_docs', 'settings'];
if (!in_array($page, $allowed_pages, true)) {
    $page = 'dashboard';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>EDM Admin - <?= ucfirst($page) ?></title>

  <link rel="stylesheet" href="./admin_styles/admin_style.css?v=<?= filemtime(__DIR__ . '/admin_styles/admin_style.css') ?>"/>
  <?php if ($page === 'manage_docs'): ?>
    <link rel="stylesheet" href="./Manage_docs/manage_docs.css?v=<?= filemtime(__DIR__ . '/Manage_docs/manage_docs.css') ?>"/>
  <?php endif; ?>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet"/>
</head>
<body class="page-<?= htmlspecialchars($page) ?>">

  <?php include 'sidebar.php'; ?>
  <div class="sidebar-overlay" data-sidebar-overlay></div>

  <main class="main-content">
    <div class="mobile-toolbar">
      <button type="button" class="menu-toggle" data-sidebar-toggle aria-label="Open navigation menu">
        <span class="material-icons-outlined">menu</span>
      </button>
      <div class="mobile-toolbar-copy">
        <div class="mobile-toolbar-title">EDM Admin</div>
        <div class="mobile-toolbar-subtitle"><?= ucwords(str_replace('_', ' ', $page)) ?></div>
      </div>
    </div>

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

  <script>
    (function () {
      const body = document.body;
      const toggleButtons = document.querySelectorAll('[data-sidebar-toggle]');
      const overlay = document.querySelector('[data-sidebar-overlay]');
      const navLinks = document.querySelectorAll('.sidebar a');

      function setSidebar(open) {
        body.classList.toggle('sidebar-open', open);
      }

      toggleButtons.forEach(button => {
        button.addEventListener('click', () => {
          setSidebar(!body.classList.contains('sidebar-open'));
        });
      });

      if (overlay) {
        overlay.addEventListener('click', () => setSidebar(false));
      }

      navLinks.forEach(link => {
        link.addEventListener('click', () => {
          if (window.innerWidth <= 1024) {
            setSidebar(false);
          }
        });
      });

      window.addEventListener('resize', () => {
        if (window.innerWidth > 1024) {
          setSidebar(false);
        }
      });
    })();
  </script>
</body>
</html>
