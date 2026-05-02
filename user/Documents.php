<?php
include '../DB/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login_logout/Login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get fresh user data for the sidebar
$u_stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$u_stmt->bind_param("i", $user_id);
$u_stmt->execute();
$u_res = $u_stmt->get_result()->fetch_assoc();

$username = htmlspecialchars($u_res['username'] ?? 'User');
$email    = htmlspecialchars($u_res['email']    ?? 'No email provided');

// Current folder level
$current_folder = isset($_GET['folder_id']) ? (int)$_GET['folder_id'] : null;

// Search query
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Resources | EDM</title>
    <link rel="stylesheet" href="user_style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined">
</head>

<body>

    <?php include 'sidebar.php'; ?>
    <div class="sidebar-overlay" data-sidebar-overlay></div>

    <main class="main-content">
        <div class="mobile-toolbar">
            <button type="button" class="menu-toggle" data-sidebar-toggle aria-label="Open navigation menu">
                <span class="material-icons-outlined">menu</span>
            </button>
            <div class="mobile-toolbar-copy">
                <div class="mobile-toolbar-title">EDM Platform</div>
                <div class="mobile-toolbar-subtitle">University resources</div>
            </div>
        </div>
        <div class="content-card">

            <h2 class="welcome-heading">University Resources</h2>
            <p class="welcome-sub">Browse and download educational materials provided by administrators.</p>

            <!-- ── ACTION ROW ── -->
            <div class="action-row">
                <div class="btn-group">
                    <button id="downloadBtn" class="btn-secondary" disabled onclick="downloadSelectedFile()">
                        <span class="material-icons-outlined">file_download</span> 
                        <span>Download</span>
                    </button>
                </div>

                <!-- Search form -->
                <form method="post" action="Documents.php" class="search-bar">
                    <span class="material-icons-outlined">search</span>
                    <?php if ($current_folder): ?>
                        <input type="hidden" name="folder_id" value="<?= $current_folder ?>">
                    <?php endif; ?>
                    <input type="text" name="search" placeholder="Search resources…" value="<?= htmlspecialchars($search) ?>">
                    <?php if ($search): ?>
                        <a href="Documents.php<?= $current_folder ? '?folder_id=' . $current_folder : '' ?>" style="color: var(--text-light); display: flex;">
                            <span class="material-icons-outlined" style="font-size:18px;">close</span>
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Breadcrumbs -->
            <div class="breadcrumbs">
                <span class="material-icons-outlined" style="font-size:16px; color: var(--burgundy);">home</span>
                <a href="Documents.php">Root</a>

                <?php
                if ($current_folder) {
                    $trail = [];
                    $temp_id = $current_folder;

                    while ($temp_id != null) {
                        $query = mysqli_query($conn, "SELECT id, name, parent_id FROM admin_storage WHERE id = $temp_id LIMIT 1");
                        if ($row = mysqli_fetch_assoc($query)) {
                            array_unshift($trail, $row);
                            $temp_id = $row['parent_id'];
                        } else {
                            break;
                        }
                    }

                    foreach ($trail as $index => $folder) {
                        echo '<span class="separator">/</span>';
                        if ($index === count($trail) - 1) {
                            echo '<span style="color: var(--text-dark); font-weight:600;">' . htmlspecialchars($folder['name']) . '</span>';
                        } else {
                            echo '<a href="Documents.php?folder_id=' . $folder['id'] . '">' . htmlspecialchars($folder['name']) . '</a>';
                        }
                    }
                }
                ?>
            </div>

            <!-- Table -->
            <div class="table-shell">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Date Added</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $safe_search = mysqli_real_escape_string($conn, $search);
                        if ($search !== '') {
                            $sql = "SELECT * FROM admin_storage WHERE name LIKE '%$safe_search%' ORDER BY type DESC, name ASC";
                        } else {
                            $sql = $current_folder
                                ? "SELECT * FROM admin_storage WHERE parent_id = $current_folder ORDER BY type DESC, name ASC"
                                : "SELECT * FROM admin_storage WHERE parent_id IS NULL ORDER BY type DESC, name ASC";
                        }
                        
                        $result = mysqli_query($conn, $sql);
                        if (mysqli_num_rows($result) === 0):
                        ?>
                            <tr>
                                <td colspan="3" class="table-message-cell">
                                    <div class="empty-state">
                                        <span class="material-icons-outlined" style="font-size: 48px; opacity: 0.2; margin-bottom: 10px;">folder_open</span>
                                        <p><?= $search ? 'No results found for "' . htmlspecialchars($search) . '".' : 'This folder is empty.' ?></p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr data-id="<?= $row['id'] ?>" onclick="selectRow(this, '<?= $row['type'] ?>', '<?= isset($row['file_path']) ? '../' . $row['file_path'] : '' ?>')">
                                <td data-label="Name">
                                    <div class="item-name">
                                        <?php if ($row['type'] == 'folder'): ?>
                                            <span class="material-icons-outlined icon-folder">folder</span>
                                            <a href="Documents.php?folder_id=<?= $row['id'] ?>" onclick="event.stopPropagation();">
                                                <?= htmlspecialchars($row['name']) ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="material-icons-outlined icon-file">description</span>
                                            <a href="../<?= $row['file_path'] ?>" target="_blank" onclick="event.stopPropagation();">
                                                <?= htmlspecialchars($row['name']) ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td data-label="Type">
                                    <span style="font-size: 12px; color: var(--text-light); text-transform: capitalize; background: #f0f0f0; padding: 2px 8px; border-radius: 4px;">
                                        <?= $row['type'] ?>
                                    </span>
                                </td>
                                <td data-label="Date Added" style="color: var(--text-light); font-size: 13px;">
                                    <?= date('d M Y', strtotime($row['created_at'])) ?>
                                </td>
                            </tr>
                        <?php endwhile; endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </main>

    <script>
        let selectedFileUrl = null;
        let selectedFileName = null;

        function selectRow(row, type, path) {
            document.querySelectorAll('.user-table tr').forEach(r => r.classList.remove('selected-row'));
            
            if (type === 'file') {
                row.classList.add('selected-row');
                selectedFileUrl = path;
                selectedFileName = row.querySelector('.item-name a').textContent.trim();
                document.getElementById('downloadBtn').disabled = false;
            } else {
                selectedFileUrl = null;
                document.getElementById('downloadBtn').disabled = true;
            }
        }

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
