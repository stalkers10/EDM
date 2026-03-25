

<nav class="sidebar">

  <!-- Brand / Logo -->
  <div class="brand">
    EDM Admin
    <span>Management System</span>
  </div>

  <!-- Add User Button -->
  <a href="add_user.php" class="btn-compose">
    <span class="material-icons-outlined">person_add</span>
    Add User
  </a>

  <!-- Navigation Links -->
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
      <a href="admin_dash.php?page=analytics"
         class="<?= ($page === 'analytics') ? 'active' : '' ?>">
        <span class="material-icons-outlined">bar_chart</span>
        Analytics
      </a>
    </li>

    <li>
      <a href="admin_dash.php?page=settings"
         class="<?= ($page === 'settings') ? 'active' : '' ?>">
        <span class="material-icons-outlined">settings</span>
        Settings
      </a>
    </li>

    <!-- Divider -->
    <div class="nav-separator"></div>

    <li>
      <a href="logout.php" class="logout-link">
        <span class="material-icons-outlined">logout</span>
        Logout
      </a>
    </li>

  </ul>
</nav>