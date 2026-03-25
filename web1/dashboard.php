<!-- ============================================================
     dashboard.php  –  Welcome / overview page
     Included by admin_dash.php when $page === 'dashboard'
     Uses mysqli (matches database.php)
     ============================================================ -->

<div class="topbar">
  <div>
    <div class="topbar-title">Welcome back 👋</div>
    <div class="topbar-subtitle">Here's what's happening in your system today.</div>
  </div>
</div>

<div class="content-card">

  <h2 class="welcome-heading">System Overview</h2>
  <p class="welcome-sub">Track, manage, and monitor your users from this panel.</p>

  <!-- ── STAT CARDS ── -->
  <?php
    $total    = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users"))[0];
    $active   = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE status = 'active'"))[0];
    $inactive = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE status = 'inactive'"))[0];
    $admins   = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE role = 'admin'"))[0];
  ?>

  <div class="stats-grid">

    <div class="stat-card">
      <div class="stat-icon">
        <span class="material-icons-outlined">people_alt</span>
      </div>
      <div>
        <div class="stat-value"><?= $total ?></div>
        <div class="stat-label">Total Users</div>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-icon">
        <span class="material-icons-outlined">check_circle</span>
      </div>
      <div>
        <div class="stat-value"><?= $active ?></div>
        <div class="stat-label">Active Users</div>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-icon">
        <span class="material-icons-outlined">cancel</span>
      </div>
      <div>
        <div class="stat-value"><?= $inactive ?></div>
        <div class="stat-label">Inactive Users</div>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-icon">
        <span class="material-icons-outlined">admin_panel_settings</span>
      </div>
      <div>
        <div class="stat-value"><?= $admins ?></div>
        <div class="stat-label">Admins</div>
      </div>
    </div>

  </div><!-- /stats-grid -->

  <!-- ── RECENT USERS PREVIEW TABLE ── -->
  <p class="recent-label">Recent Users</p>

  <table class="user-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>User Details</th>
        <th>Role</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php
        $recent = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC LIMIT 5");
        while ($row = mysqli_fetch_assoc($recent)):
      ?>
      <tr>
        <td><?= htmlspecialchars($row['id']) ?></td>
        <td>
          <div class="user-name"><?= htmlspecialchars($row['username']) ?></div>
          <div class="user-email"><?= htmlspecialchars($row['email']) ?></div>
        </td>
        <td><span class="role-badge"><?= htmlspecialchars($row['role']) ?></span></td>
        <td>
          <span class="status-pill <?= $row['status'] === 'active' ? 'active' : 'inactive' ?>">
            <?= ucfirst($row['status']) ?>
          </span>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

</div><!-- /content-card -->