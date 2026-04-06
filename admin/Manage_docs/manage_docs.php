<?php
/*
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login_logout/Login.php");
    exit();
}
*/
// Current folder level
$current_folder = isset($_GET['folder_id']) ? (int)$_GET['folder_id'] : null;

// Search query
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Documents | EDM Admin</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined">
    <style>
        /* ── Search bar ── */
        .search-bar {
            display: flex;
            align-items: center;
            gap: 0;
            background: #fafafa;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            overflow: hidden;
            max-width: 380px;
            width: 100%;
            transition: border-color 0.2s;
        }

        .search-bar:focus-within {
            border-color: var(--burgundy);
        }

        .search-bar .material-icons-outlined {
            padding: 0 12px;
            color: var(--text-light);
            font-size: 20px;
            pointer-events: none;
        }

        .search-bar input {
            flex: 1;
            border: none;
            background: transparent;
            padding: 10px 0;
            font-size: 13.5px;
            font-family: 'Poppins', sans-serif;
            color: var(--text-dark);
            outline: none;
        }

        .search-bar input::placeholder { color: var(--text-light); }

        .search-bar .btn-clear {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0 10px;
            color: var(--text-light);
            display: flex;
            align-items: center;
        }

        .search-bar .btn-clear:hover { color: var(--burgundy); }
        .search-bar .btn-clear .material-icons-outlined { font-size: 16px; padding: 0; }

        /* ── Top action row ── */
        .action-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
        }

        .action-row .btn-group {
            display: flex;
            gap: 10px;
        }

        /* ── Buttons ── */
        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 16px;
            background: var(--burgundy);
            color: white;
            border: none;
            border-radius: 7px;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, transform 0.15s;
        }

        .btn-primary:hover { background: var(--burgundy-dark); transform: translateY(-1px); }
        .btn-primary .material-icons-outlined { font-size: 17px; }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 16px;
            background: #f0e8ea;
            color: var(--burgundy);
            border: 1px solid var(--burgundy-pill);
            border-radius: 7px;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-secondary:hover { background: var(--burgundy-pill); }
        .btn-secondary .material-icons-outlined { font-size: 17px; }

        /* ── Breadcrumb ── */
        .breadcrumbs {
            font-size: 13px;
            color: var(--text-light);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .breadcrumbs a {
            color: var(--burgundy);
            text-decoration: none;
            font-weight: 500;
        }

        .breadcrumbs a:hover { text-decoration: underline; }

        /* ── Search results label ── */
        .search-label {
            font-size: 12.5px;
            color: var(--text-light);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .search-label strong { color: var(--burgundy); }

        /* ── Empty state ── */
        .empty-state {
            text-align: center;
            padding: 48px 20px;
            color: var(--text-light);
        }

        .empty-state .material-icons-outlined {
            font-size: 48px;
            color: var(--border);
            margin-bottom: 12px;
        }

        .empty-state p { font-size: 14px; }

        /* ── File/folder icons in table ── */
        .item-name {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .icon-folder { color: #f6a623; }
        .icon-file   { color: var(--burgundy); }

        /* ── Delete link ── */
        .text-danger {
            color: #e53e3e;
            font-size: 12.5px;
            font-weight: 600;
            text-decoration: none;
        }

        .text-danger:hover { text-decoration: underline; }

        /* ── Modal ── */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: 100;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.open { display: flex; }

        .modal-box {
            background: white;
            border-radius: 14px;
            padding: 28px 30px;
            width: 360px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        }

        .modal-box h3 {
            font-size: 15px;
            font-weight: 700;
            color: var(--burgundy);
            margin-bottom: 18px;
        }

        .modal-box input[type="text"] {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid var(--border);
            border-radius: 7px;
            font-size: 13.5px;
            font-family: 'Poppins', sans-serif;
            outline: none;
            margin-bottom: 14px;
        }

        .modal-box input[type="text"]:focus { border-color: var(--burgundy); }

        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .btn-cancel {
            padding: 8px 16px;
            border-radius: 7px;
            border: 1px solid var(--border);
            background: transparent;
            font-size: 13px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            color: var(--text-mid);
        }

        .btn-cancel:hover { background: #f5f5f5; }
    </style>
</head>
<body>

<main class="main-content">
    <div class="content-card">

        <h2 class="welcome-heading">Document Manager</h2>
        <p class="welcome-sub">Create folders and organize university resources.</p>

        <!-- ── ACTION ROW: buttons + search ── -->
        <div class="action-row">

            <div class="btn-group">
                <button onclick="openModal('folderModal')" class="btn-primary">
                    <span class="material-icons-outlined">create_new_folder</span> New Folder
                </button>
                <button onclick="openModal('uploadModal')" class="btn-secondary">
                    <span class="material-icons-outlined">upload_file</span> Upload File
                </button>
            </div>

            <!-- Search form -->
            <form method="GET" action="manage_docs.php" style="display:flex; align-items:center;">
                <?php if ($current_folder): ?>
                    <input type="hidden" name="folder_id" value="<?= $current_folder ?>">
                <?php endif; ?>

                <div class="search-bar">
                    <span class="material-icons-outlined">search</span>
                    <input
                        type="text"
                        name="search"
                        placeholder="Search files and folders…"
                        value="<?= htmlspecialchars($search) ?>"
                        autocomplete="off"
                    >
                    <?php if ($search): ?>
                        <a href="manage_docs.php<?= $current_folder ? '?folder_id='.$current_folder : '' ?>" class="btn-clear">
                            <span class="material-icons-outlined">close</span>
                        </a>
                    <?php endif; ?>
                </div>
            </form>

        </div>

        <!-- ── BREADCRUMB ── -->
        <div class="breadcrumbs">
            <span class="material-icons-outlined" style="font-size:15px;">home</span>
            <a href="manage_docs.php">Root</a>
            <?php if ($current_folder): ?>
                <?php
                  // Fetch folder name for breadcrumb
                  $bq  = mysqli_query($conn, "SELECT name FROM admin_storage WHERE id = $current_folder LIMIT 1");
                  $brow = mysqli_fetch_assoc($bq);
                ?>
                <span>/</span>
                <span style="color: var(--text-dark); font-weight:500;">
                    <?= htmlspecialchars($brow['name'] ?? "Folder #$current_folder") ?>
                </span>
            <?php endif; ?>
            <?php if ($search): ?>
                <span>/</span>
                <span style="color: var(--text-dark);">Search results</span>
            <?php endif; ?>
        </div>

        <!-- ── SEARCH RESULTS LABEL ── -->
        <?php if ($search): ?>
            <div class="search-label">
                <span class="material-icons-outlined" style="font-size:15px;">info</span>
                Showing results for <strong>"<?= htmlspecialchars($search) ?>"</strong>
                <?php if ($current_folder): ?> in current folder<?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- ── TABLE ── -->
        <table class="user-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Date Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Build query — search OR normal folder browse
                if ($search !== '') {
                    $safe_search = mysqli_real_escape_string($conn, $search);

                    if ($current_folder) {
                        // Search within current folder only
                        $sql = "SELECT * FROM admin_storage
                                WHERE parent_id = $current_folder
                                AND name LIKE '%$safe_search%'
                                ORDER BY type DESC, name ASC";
                    } else {
                        // Global search across all items
                        $sql = "SELECT * FROM admin_storage
                                WHERE name LIKE '%$safe_search%'
                                ORDER BY type DESC, name ASC";
                    }
                } else {
                    // Normal browse
                    $sql = $current_folder
                        ? "SELECT * FROM admin_storage WHERE parent_id = $current_folder ORDER BY type DESC, name ASC"
                        : "SELECT * FROM admin_storage WHERE parent_id IS NULL ORDER BY type DESC, name ASC";
                }

                $result = mysqli_query($conn, $sql);
                $count  = mysqli_num_rows($result);

                if ($count === 0):
                ?>
                <tr>
                    <td colspan="4">
                        <div class="empty-state">
                            <div class="material-icons-outlined">
                                <?= $search ? 'search_off' : 'folder_open' ?>
                            </div>
                            <p>
                                <?= $search
                                    ? 'No files or folders match "<strong>' . htmlspecialchars($search) . '</strong>"'
                                    : 'This folder is empty. Create a folder or upload a file.' ?>
                            </p>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td>    
                        <div class="item-name">
                            <?php if ($row['type'] == 'folder'): ?>
                                <span class="material-icons-outlined icon-folder">folder</span>
                                <a href="Manage_docs/manage_docs.php?folder_id=<?= $row['id'] ?>">
                                    <?= htmlspecialchars($row['name']) ?>
                                </a>
                            <?php else: ?>
                                <span class="material-icons-outlined icon-file">description</span>
                                <?= htmlspecialchars($row['name']) ?>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td><?= ucfirst($row['type']) ?></td>
                    <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                    <td>
                        <a href="delete_action.php?id=<?= $row['id'] ?>"
                           class="text-danger"
                           onclick="return confirm('Delete this item?')">
                           Delete
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>

    </div>
</main>

<!-- ── CREATE FOLDER MODAL ── -->
<div id="folderModal" class="modal-overlay">
    <div class="modal-box">
        <h3>Create New Folder</h3>
        <form action="Manage_docs/folder_action.php" method="POST">
            <input type="hidden" name="parent_id" value="<?= $current_folder ?>">
            <input type="text" name="folder_name" placeholder="Folder name…" required>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeModal('folderModal')">Cancel</button>
                <button type="submit" name="create" class="btn-primary">
                    <span class="material-icons-outlined">create_new_folder</span> Create
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ── UPLOAD FILE MODAL ── -->
<div id="uploadModal" class="modal-overlay">
    <div class="modal-box">
        <h3>Upload File</h3>
        <form action="Manage_docs/upload_action.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="parent_id" value="<?= $current_folder ?>">
            <input type="file" name="file" required style="margin-bottom:14px; font-size:13px;">
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeModal('uploadModal')">Cancel</button>
                <button type="submit" class="btn-primary">
                    <span class="material-icons-outlined">upload_file</span> Upload
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id)  { document.getElementById(id).classList.add('open'); }
    function closeModal(id) { document.getElementById(id).classList.remove('open'); }

    // Close modal when clicking outside the box
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) closeModal(this.id);
        });
    });

    // Auto-submit search form on typing (with debounce)
    const searchInput = document.querySelector('.search-bar input');
    let debounceTimer;

    searchInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            searchInput.closest('form').submit();
        }, 400); // 400ms after user stops typing
    });
</script>

</body>
</html>