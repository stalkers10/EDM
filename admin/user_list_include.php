
<div class="topbar">
  <div>
    <div class="topbar-title">User Management</div>
    <div class="topbar-subtitle">Select a row to enable actions.</div>
  </div>
</div>

<div class="content-card">

  <div class="card-header">
    <h2>Management Panel</h2>
    <a href="add_user.php" class="btn-add">
      <span class="material-icons-outlined">add</span>
      Add New User
    </a>
  </div>

  <div class="action-bar">
    <button id="btn-toggle" class="btn btn-toggle btn-disabled">
      <span class="material-icons-outlined">toggle_on</span> Update Status
    </button>
    <button id="btn-edit" class="btn btn-edit btn-disabled">
      <span class="material-icons-outlined">edit</span> Edit User
    </button>
    <button id="btn-delete" class="btn btn-delete btn-disabled"
            onclick="confirmDelete()">
      <span class="material-icons-outlined">delete</span> Delete
    </button>
  </div>

  <!-- Users Table -->
  <table class="user-table" id="user-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>User Details</th>
        <th>Phone</th>
        <th>Role</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody id="user-tbody">

      <?php
        $result = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");

        if (!$result) {
            echo "<tr><td colspan='5'>Error loading users: " . mysqli_error($conn) . "</td></tr>";
        } else {
            while ($row = mysqli_fetch_assoc($result)):
              // Normalize status: works whether DB stores 'active'/'inactive' OR 1/0
              $is_active = ($row['status'] === 'active' || $row['status'] == 1);
      ?>
      <tr data-id="<?= $row['id'] ?>"
          data-edit-url="edit_user.php?id=<?= $row['id'] ?>"
          data-toggle-url="process_admin.php?task=toggle&id=<?= $row['id'] ?>"
          data-delete-url="process_admin.php?task=delete&id=<?= $row['id'] ?>">

        <td><?= htmlspecialchars($row['id']) ?></td>
        <td>
          <div class="user-name"><?= htmlspecialchars($row['username']) ?></div>
          <div class="user-email"><?= htmlspecialchars($row['email']) ?></div>
        </td>
        <td><?= htmlspecialchars($row['phone_num']) ?></td>
        <td><span class="role-badge"><?= htmlspecialchars($row['role']) ?></span></td>
        <td>
          <span class="status-pill <?= $is_active ? 'active' : 'inactive' ?>">
            <?= $is_active ? 'Active' : 'Inactive' ?>
          </span>
        </td>

      </tr>
      <?php
            endwhile;
            mysqli_free_result($result);
        }
      ?>

    </tbody>
  </table>

</div>

<script>
  (function () {
    const tbody     = document.getElementById('user-tbody');
    const btnToggle = document.getElementById('btn-toggle');
    const btnEdit   = document.getElementById('btn-edit');
    const btnDelete = document.getElementById('btn-delete');
    let   selected  = null;

    tbody.addEventListener('click', function (e) {
      const row = e.target.closest('tr');
      if (!row) return;

      if (selected === row) {
        row.classList.remove('selected');
        selected = null;
        setButtons(false);
      } else {
        if (selected) selected.classList.remove('selected');
        row.classList.add('selected');
        selected = row;
        setButtons(true);
      }
    });

    function setButtons(on) {
      [btnToggle, btnEdit, btnDelete].forEach(btn => {
        if (on) btn.classList.remove('btn-disabled');
        else    btn.classList.add('btn-disabled');
      });
    }

    btnEdit.addEventListener('click', function () {
      if (selected) window.location.href = selected.dataset.editUrl;
    });

    btnToggle.addEventListener('click', function () {
      if (selected) window.location.href = selected.dataset.toggleUrl;
    });

    window.confirmDelete = function () {
      if (!selected) return;
      if (confirm('Are you sure you want to delete this user?')) {
        window.location.href = selected.dataset.deleteUrl;
      }
    };
  })();
</script>