<?php
include '../DB/database.php';
require_once '../DB/document_search.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login_logout/Login.php");
    exit();
}

ensure_document_search_schema($conn);

$user_id = $_SESSION['user_id'];

$u_stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$u_stmt->bind_param("i", $user_id);
$u_stmt->execute();
$u_res = $u_stmt->get_result()->fetch_assoc();

$username = htmlspecialchars($u_res['username'] ?? 'User');
$email    = htmlspecialchars($u_res['email'] ?? 'No email provided');

$current_folder = isset($_GET['folder_id']) ? (int) $_GET['folder_id'] : null;
$search_state = edm_get_document_search_state($_GET);
$has_search_filters = edm_document_search_has_filters($search_state);
$search_summary = edm_get_document_search_summary($search_state);
$file_types = edm_get_admin_storage_extensions($conn);
$documents = edm_fetch_admin_storage_documents($conn, $search_state, $current_folder, false);

$simple_clear_url = edm_build_url('Documents.php', [
    'folder_id' => $current_folder,
]);

$advanced_reset_url = edm_build_url('Documents.php', [
    'folder_id' => $current_folder,
    'search_mode' => 'advanced',
]);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Resources | EDM</title>
    <link rel="stylesheet" href="user_style.css?v=<?= filemtime(__DIR__ . '/user_style.css') ?>">
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
            <p class="welcome-sub">Browse, filter, and download educational materials shared by administrators.</p>

            <div class="search-card">
                <div class="search-card-head">
                    <div class="btn-group">
                        <button id="downloadBtn" class="btn-secondary" disabled onclick="downloadSelectedFile()" type="button">
                            <span class="material-icons-outlined">file_download</span>
                            <span>Download</span>
                        </button>
                    </div>

                    <div class="search-mode-switch" data-search-switch>
                        <button
                            type="button"
                            class="search-mode-btn <?= $search_state['mode'] === 'simple' ? 'is-active' : '' ?>"
                            data-search-mode="simple"
                            aria-pressed="<?= $search_state['mode'] === 'simple' ? 'true' : 'false' ?>">
                            Simple Search
                        </button>
                        <button
                            type="button"
                            class="search-mode-btn <?= $search_state['mode'] === 'advanced' ? 'is-active' : '' ?>"
                            data-search-mode="advanced"
                            aria-pressed="<?= $search_state['mode'] === 'advanced' ? 'true' : 'false' ?>">
                            Advanced Search
                        </button>
                    </div>
                </div>

                <form
                    method="get"
                    action="Documents.php"
                    class="search-panel simple-search-form <?= $search_state['mode'] === 'simple' ? 'is-active' : '' ?>"
                    data-search-panel="simple">
                    <?php if ($current_folder): ?>
                        <input type="hidden" name="folder_id" value="<?= $current_folder ?>">
                    <?php endif; ?>
                    <input type="hidden" name="search_mode" value="simple">

                    <div class="search-bar">
                        <span class="material-icons-outlined">search</span>
                        <input
                            type="text"
                            name="q"
                            value="<?= htmlspecialchars($search_state['q']) ?>"
                            placeholder="Search by name, author, description, keywords, or file type..."
                            autocomplete="on"
                            data-simple-search-input>
                        <?php if ($search_state['q'] !== ''): ?>
                            <a href="<?= htmlspecialchars($simple_clear_url) ?>" class="btn-clear">
                                <span class="material-icons-outlined">close</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>

                <form
                    method="get"
                    action="Documents.php"
                    class="search-panel advanced-search-form <?= $search_state['mode'] === 'advanced' ? 'is-active' : '' ?>"
                    data-search-panel="advanced">
                    <?php if ($current_folder): ?>
                        <input type="hidden" name="folder_id" value="<?= $current_folder ?>">
                    <?php endif; ?>
                    <input type="hidden" name="search_mode" value="advanced">

                    <div class="advanced-fields">
                        <label class="filter-field">
                            <span>Name</span>
                            <input type="text" name="name" value="<?= htmlspecialchars($search_state['name']) ?>" placeholder="Document or folder name">
                        </label>

                        <label class="filter-field">
                            <span>Item Type</span>
                            <select name="item_type">
                                <option value="">All items</option>
                                <option value="file" <?= $search_state['item_type'] === 'file' ? 'selected' : '' ?>>Files only</option>
                                <option value="folder" <?= $search_state['item_type'] === 'folder' ? 'selected' : '' ?>>Folders only</option>
                            </select>
                        </label>

                        <label class="filter-field">
                            <span>File Type</span>
                            <select name="file_type">
                                <option value="">Any file type</option>
                                <?php foreach ($file_types as $extension): ?>
                                    <option value="<?= htmlspecialchars($extension) ?>" <?= $search_state['file_type'] === $extension ? 'selected' : '' ?>>
                                        <?= strtoupper(htmlspecialchars($extension)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <label class="filter-field">
                            <span>Author</span>
                            <input type="text" name="author" value="<?= htmlspecialchars($search_state['author']) ?>" placeholder="Uploader or document author">
                        </label>

                        <label class="filter-field">
                            <span>Date Uploaded From</span>
                            <input type="date" name="date_from" value="<?= htmlspecialchars($search_state['date_from']) ?>">
                        </label>

                        <label class="filter-field">
                            <span>Date Uploaded To</span>
                            <input type="date" name="date_to" value="<?= htmlspecialchars($search_state['date_to']) ?>">
                        </label>

                        <label class="filter-field filter-field-wide">
                            <span>Description</span>
                            <textarea name="description" rows="3" placeholder="Search inside resource descriptions"><?= htmlspecialchars($search_state['description']) ?></textarea>
                        </label>

                        <label class="filter-field filter-field-wide">
                            <span>Keywords</span>
                            <input type="text" name="keywords" value="<?= htmlspecialchars($search_state['keywords']) ?>" placeholder="policy, engineering, semester 1">
                        </label>
                    </div>

                    <div class="filter-actions">
                        <a href="<?= htmlspecialchars($advanced_reset_url) ?>" class="btn-secondary filter-reset-link">
                            <span class="material-icons-outlined">restart_alt</span>
                            <span>Reset</span>
                        </a>
                    </div>
                </form>
            </div>

            <div class="breadcrumbs">
                <span class="material-icons-outlined" style="font-size:16px; color: var(--burgundy);">home</span>
                <a href="Documents.php">Root</a>

                <?php
                if ($current_folder) {
                    $trail = [];
                    $temp_id = $current_folder;

                    while ($temp_id !== null) {
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

                <?php if ($has_search_filters): ?>
                    <span class="separator">/</span>
                    <span style="color: var(--text-dark); font-weight:600;">Search results</span>
                <?php endif; ?>
            </div>

            <?php if ($has_search_filters): ?>
                <div class="search-label">
                    <span class="material-icons-outlined" style="font-size:15px;">info</span>
                    <strong><?= htmlspecialchars($search_summary) ?></strong>
                </div>
            <?php endif; ?>

            <div class="table-shell">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>File Type</th>
                            <th>Author</th>
                            <th>Date Added</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($documents) === 0): ?>
                            <tr>
                                <td colspan="4" class="table-message-cell">
                                    <div class="empty-state">
                                        <span class="material-icons-outlined" style="font-size: 48px; opacity: 0.2; margin-bottom: 10px;">
                                            <?= $has_search_filters ? 'search_off' : 'folder_open' ?>
                                        </span>
                                        <p><?= $has_search_filters ? 'No resources matched the current search filters.' : 'This folder is empty.' ?></p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($documents as $row): ?>
                                <?php
                                $is_file = $row['type'] === 'file';
                                $file_type_label = $is_file
                                    ? strtoupper($row['file_extension'] ?: (pathinfo($row['name'], PATHINFO_EXTENSION) ?: 'FILE'))
                                    : 'FOLDER';
                                $author_display = $row['author_display'] ?: 'Unknown';
                                $description = trim((string) ($row['description'] ?? ''));
                                $keywords = trim((string) ($row['keywords'] ?? ''));
                                ?>
                                <tr data-id="<?= $row['id'] ?>" onclick="selectRow(this, '<?= $row['type'] ?>', '<?= isset($row['file_path']) ? '../' . htmlspecialchars($row['file_path']) : '' ?>')">
                                    <td data-label="Name">
                                        <div class="item-name">
                                            <?php if ($row['type'] == 'folder'): ?>
                                                <span class="material-icons-outlined icon-folder">folder</span>
                                                <a href="Documents.php?folder_id=<?= $row['id'] ?>" onclick="event.stopPropagation();">
                                                    <?= htmlspecialchars($row['name']) ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="material-icons-outlined icon-file">description</span>
                                                <a href="../<?= htmlspecialchars($row['file_path']) ?>" target="_blank" rel="noopener noreferrer" onclick="event.stopPropagation();">
                                                    <?= htmlspecialchars($row['name']) ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($description !== '' || $keywords !== ''): ?>
                                            <div class="item-meta">
                                                <?php if ($description !== ''): ?>
                                                    <span>Description: <?= htmlspecialchars($description) ?></span>
                                                <?php endif; ?>
                                                <?php if ($keywords !== ''): ?>
                                                    <span>Keywords: <?= htmlspecialchars($keywords) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="File Type">
                                        <span class="type-badge"><?= htmlspecialchars($file_type_label) ?></span>
                                    </td>
                                    <td data-label="Author">
                                        <span class="author-text"><?= htmlspecialchars($author_display) ?></span>
                                    </td>
                                    <td data-label="Date Added" style="color: var(--text-light); font-size: 13px;">
                                        <?= date('d M Y', strtotime($row['created_at'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </main>

    <script>
        let selectedFileUrl = null;
        let selectedFileName = null;

        function selectRow(row, type, path) {
            document.querySelectorAll('.user-table tr[data-id]').forEach(r => r.classList.remove('selected-row'));

            if (type === 'file') {
                row.classList.add('selected-row');
                selectedFileUrl = path;
                selectedFileName = row.querySelector('.item-name a').textContent.trim();
                document.getElementById('downloadBtn').disabled = false;
            } else {
                selectedFileUrl = null;
                selectedFileName = null;
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
            const modeButtons = document.querySelectorAll('[data-search-mode]');
            const modePanels = document.querySelectorAll('[data-search-panel]');
            const searchInput = document.querySelector('[data-simple-search-input]');
            const advancedForm = document.querySelector('.advanced-search-form');
            const advancedFields = advancedForm
                ? advancedForm.querySelectorAll('input:not([type="hidden"]):not([type="submit"]):not([type="button"]), select, textarea')
                : [];
            let simpleSearchTimer;
            let advancedSearchTimer;

            function setSidebar(open) {
                body.classList.toggle('sidebar-open', open);
            }

            function activateMode(mode) {
                modeButtons.forEach(button => {
                    const isActive = button.dataset.searchMode === mode;
                    button.classList.toggle('is-active', isActive);
                    button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                });

                modePanels.forEach(panel => {
                    panel.classList.toggle('is-active', panel.dataset.searchPanel === mode);
                });
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

            modeButtons.forEach(button => {
                button.addEventListener('click', () => activateMode(button.dataset.searchMode));
            });

            activateMode('<?= $search_state['mode'] ?>');

            function submitForm(form) {
                if (!form) {
                    return;
                }

                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                    return;
                }

                form.submit();
            }

            if (searchInput) {
                searchInput.addEventListener('input', () => {
                    clearTimeout(simpleSearchTimer);
                    simpleSearchTimer = setTimeout(() => {
                        submitForm(searchInput.closest('form'));
                    }, 400);
                });
            }

            advancedFields.forEach(field => {
                const queueImmediateSubmit = () => {
                    clearTimeout(advancedSearchTimer);
                    submitForm(advancedForm);
                };

                const queueDebouncedSubmit = () => {
                    clearTimeout(advancedSearchTimer);
                    advancedSearchTimer = setTimeout(() => {
                        submitForm(advancedForm);
                    }, 400);
                };

                if (field.tagName === 'SELECT' || field.type === 'date') {
                    field.addEventListener('change', queueImmediateSubmit);
                    return;
                }

                field.addEventListener('input', queueDebouncedSubmit);
                field.addEventListener('change', queueImmediateSubmit);
            });
        })();
    </script>
</body>

</html>
