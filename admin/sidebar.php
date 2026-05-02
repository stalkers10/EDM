

<nav class="sidebar">

  <div class="brand">
    <div class="brand-top">
      <div class="brand-name">EDM ADMIN</div>
      <button type="button" class="sidebar-close" data-sidebar-toggle aria-label="Close navigation menu">
        <span class="material-icons-outlined">close</span>
      </button>
    </div>
    <div class="brand-username">
      <span class="material-icons-outlined" style="font-size:15px; vertical-align:middle;">account_circle</span>
      <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?>
    </div>
    <div class="brand-email">
      <span class="material-icons-outlined" style="font-size:12px; vertical-align:middle;">mail_outline</span>
      <?= htmlspecialchars($_SESSION['email'] ?? '') ?>
    </div>
  </div>

 
  <a href="Actions/add_user.php" class="btn-compose">
    <span class="material-icons-outlined">person_add</span>
    Add User
  </a>

  <p class="nav-section-label">Main Menu</p>
  <ul class="nav-menu">

    <li>
      <a href="admin_dash.php?page=dashboard"
         class="<?= ($page === 'dashboard') ? 'active' : '' ?>">
        <span class="material-icons-outlined">dashboard</span>
        Dashboard
      </a>
    </li>

    <li>
      <a href="admin_dash.php?page=users"
         class="<?= ($page === 'users') ? 'active' : '' ?>">
        <span class="material-icons-outlined">manage_accounts</span>
        User Management
      </a>
    </li>

    <li>
      <a href="admin_dash.php?page=manage_docs"
         class="<?= ($page === 'manage_docs') ? 'active' : '' ?>">
        <span id="docs" class="material-icons-outlined">manage_documents</span>
        Manage Documents
      </a>
    </li>

    <li>
      <a href="admin_dash.php?page=settings"
         class="<?= ($page === 'settings') ? 'active' : '' ?>">
        <span class="material-icons-outlined">settings</span>
        Settings
      </a>
    </li>

    <div class="nav-separator"></div>

    <li>
      <a href="../login_logout/logout.php" class="logout-link">
        <span class="material-icons-outlined">logout</span>
        Logout
      </a>
    </li>

  </ul>
</nav>
