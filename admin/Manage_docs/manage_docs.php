<?php

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login_logout/Login.php");
    exit();
}

// Current folder level
$current_folder = isset($_GET['folder_id']) ? (int)$_GET['folder_id'] : null;

// Search query - Check both POST and GET
$search = '';
if (isset($_POST['search'])) {
    $search = trim($_POST['search']);
} elseif (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Documents | EDM Admin</title>
    <link rel="stylesheet" href="admin_styles/admin_style.css">
    <link rel="stylesheet" href="Manage_docs/manage_docs.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined">
</head>

<body>

    <main class="main-content">
        <div class="content-card">

            <h2 class="welcome-heading">Document Manager</h2>
            <p class="welcome-sub">Create folders and organize university resources.</p>

            <!-- ── ACTION ROW: buttons + search ── -->
            <div class="action-row" style="background: #fff; padding: 15px; border-radius: 8px; border: 1px solid #eee; margin-bottom: 20px;">

                <div class="btn-group">
                    <button onclick="openModal('folderModal')" class="btn-primary">
                        <span class="material-icons-outlined">create_new_folder</span> New Folder
                    </button>
                    <button onclick="openModal('uploadModal')" class="btn-secondary">
                        <span class="material-icons-outlined">upload_file</span> Upload File
                    </button>
                    <button id="downloadBtn" class="btn-secondary" disabled onclick="downloadSelectedFile()">
                        <span class="material-icons-outlined">file_download</span> Download
                    </button>
                    <button id="shareBtn" class="btn-secondary" disabled onclick="generateShareLink()">
                        <span class="material-icons-outlined">share</span> Share
                    </button>
                </div>

                <!-- Search form -->
                <form method="post" action="admin_dash.php?page=manage_docs" style="display:flex; align-items:center;">
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
                            autocomplete="on">
                        <?php if ($search): ?>
                            <a href="admin_dash.php?page=manage_docs<?= $current_folder ? '&folder_id=' . $current_folder : '' ?>" class="btn-clear">
                                <span class="material-icons-outlined">close</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>

            </div>

            <!-- Breadcrumbs -->

            <div class="breadcrumbs">
                <span class="material-icons-outlined" style="font-size:15px;">home</span>
                <a href="admin_dash.php?page=manage_docs">Root</a>

                <?php
                if ($current_folder) {
                    $trail = [];
                    $temp_id = $current_folder;

                    // Climb the tree to get all parent names
                    while ($temp_id != null) {
                        $query = mysqli_query($conn, "SELECT id, name, parent_id FROM admin_storage WHERE id = $temp_id LIMIT 1");
                        if ($row = mysqli_fetch_assoc($query)) {
                            array_unshift($trail, $row); // Add to the beginning of the array
                            $temp_id = $row['parent_id'];
                        } else {
                            break;
                        }
                    }

                    // Loop through the trail and print links
                    foreach ($trail as $index => $folder) {
                        echo '<span>/</span>';
                        // If it's the last item, don't make it a link
                        if ($index === count($trail) - 1) {
                            echo '<span style="color: var(--text-dark); font-weight:500;">' . htmlspecialchars($folder['name']) . '</span>';
                        } else {
                            echo '<a href="admin_dash.php?page=manage_docs&folder_id=' . $folder['id'] . '">' . htmlspecialchars($folder['name']) . '</a>';
                        }
                    }
                }
                ?>

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
                            // Search ONLY inside this specific folder
                            $sql = "SELECT * FROM admin_storage 
                                    WHERE parent_id = $current_folder 
                                    AND name LIKE '%$safe_search%' 
                                    ORDER BY type DESC, name ASC";
                        } else {
                            // Global search: Find this file anywhere in the system
                            $sql = "SELECT * FROM admin_storage 
                                    WHERE name LIKE '%$safe_search%' 
                                    ORDER BY type DESC, name ASC";
                        }
                    } else {
                        // Normal browse (No search active)
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
                            <tr data-id="<?= $row['id'] ?>">
                                <td>
                                    <div class="item-name">
                                        <?php if ($row['type'] == 'folder'): ?>
                                            <span class="material-icons-outlined icon-folder">folder</span>
                                            <a href="admin_dash.php?page=manage_docs&folder_id=<?= $row['id'] ?>">
                                                <?= htmlspecialchars($row['name']) ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="material-icons-outlined icon-file">description</span>
                                            <a href="../<?= $row['file_path'] ?>" target="_blank">
                                                <?= htmlspecialchars($row['name']) ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><?= ucfirst($row['type']) ?></td>
                                <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                                <td>
                                    <a href="Actions/delete_action.php?id=<?= $row['id'] ?>"
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
            <form action="Actions/folder_action.php" method="POST">
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
            <form action="Actions/upload_action.php" method="POST" enctype="multipart/form-data">
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
        // --- Modal Logic ---
        function openModal(id) {
            document.getElementById(id).classList.add('open');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('open');
        }

        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', function(e) {
                if (e.target === this) closeModal(this.id);
            });
        });

        // --- Search Logic (with Debounce) ---
        const searchInput = document.querySelector('.search-bar input');
        let debounceTimer;

        if (searchInput) {
            searchInput.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    searchInput.closest('form').submit();
                }, 400);
            });

            // Maintain focus after reload
            if ("<?= $search ?>" !== "") {
                searchInput.focus();
                const val = searchInput.value;
                searchInput.value = '';
                searchInput.value = val;
            }
        }

        // --- File Selection & Action Logic ---
        let selectedFileUrl = null;
        let selectedFileName = null;
        let selectedFileId = null;

        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('.user-table tbody tr');
            const downloadBtn = document.getElementById('downloadBtn');
            const shareBtn = document.getElementById('shareBtn');

            rows.forEach(row => {
                row.addEventListener('click', function() {
                    const isFile = this.querySelector('.icon-file') !== null;
                    const fileLink = this.querySelector('a');

                    // Clear previous selection
                    rows.forEach(r => r.classList.remove('selected-row'));

                    if (isFile && fileLink) {
                        // Apply visual selection
                        this.classList.add('selected-row');

                        // Capture data
                        selectedFileUrl = fileLink.getAttribute('href');
                        selectedFileName = fileLink.textContent.trim();
                        selectedFileId = this.getAttribute('data-id');

                        // Enable buttons
                        if (downloadBtn) downloadBtn.disabled = false;
                        if (shareBtn) shareBtn.disabled = false;
                    } else {
                        // Reset selection
                        selectedFileUrl = null;
                        selectedFileName = null;
                        selectedFileId = null;
                        if (downloadBtn) downloadBtn.disabled = true;
                        if (shareBtn) shareBtn.disabled = true;
                    }
                });
            });
        });

        // --- Action: Download ---
        function downloadSelectedFile() {
            if (selectedFileUrl) {
                const link = document.createElement('a');
                link.href = selectedFileUrl;
                link.download = selectedFileName;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        }

        //Action: Share
        function generateShareLink() {
            if (!selectedFileId) {
                alert("Please select a file first.");
                return;
            }

            fetch('Actions/generate_share.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'file_id=' + encodeURIComponent(selectedFileId)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const fullUrl = window.location.origin + '/web/share.php?h=' + data.hash;

                        // Create a temporary hidden input to copy the text automatically
                        const el = document.createElement('textarea');
                        el.value = fullUrl;
                        document.body.appendChild(el);
                        el.select();
                        document.execCommand('copy');
                        document.body.removeChild(el);

                        alert("Link copied to clipboard!\n\n" + fullUrl);
                    } else {
                        alert("Error generating link: " + (data.message || "Unknown error"));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert("An error occurred while generating the link.");
                });
        }
    </script>


</body>

</html>