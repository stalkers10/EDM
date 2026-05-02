<?php

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login_logout/Login.php");
    exit();
}

require_once __DIR__ . '/../../DB/document_search.php';

ensure_document_search_schema($conn);

$current_folder = isset($_GET['folder_id']) ? (int) $_GET['folder_id'] : null;
$search_state = edm_get_document_search_state($_GET);
$has_search_filters = edm_document_search_has_filters($search_state);
$search_summary = edm_get_document_search_summary($search_state);
$file_types = edm_get_admin_storage_extensions($conn);
$documents = edm_fetch_admin_storage_documents($conn, $search_state, $current_folder, true);

$simple_clear_url = edm_build_url('admin_dash.php', [
    'page' => 'manage_docs',
    'folder_id' => $current_folder,
]);

$advanced_reset_url = edm_build_url('admin_dash.php', [
    'page' => 'manage_docs',
    'folder_id' => $current_folder,
    'search_mode' => 'advanced',
]);
?>

<div class="content-card">

    <h2 class="welcome-heading">Document Manager</h2>
    <p class="welcome-sub">Create folders, capture document metadata, and search resources with precision.</p>

    <div class="toolbar-card">
        <div class="toolbar-top">
            <div class="btn-group">
                <button onclick="openModal('folderModal')" class="btn-primary" type="button">
                    <span class="material-icons-outlined">create_new_folder</span> New Folder
                </button>
                <button onclick="openModal('uploadModal')" class="btn-secondary" type="button">
                    <span class="material-icons-outlined">upload_file</span> Upload File
                </button>
                <button id="downloadBtn" class="btn-secondary" disabled onclick="downloadSelectedFile()" type="button">
                    <span class="material-icons-outlined">file_download</span> Download
                </button>
                <button id="shareBtn" class="btn-secondary" disabled onclick="generateShareLink()" type="button">
                    <span class="material-icons-outlined">share</span> Share
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
            action="admin_dash.php"
            class="search-panel simple-search-form <?= $search_state['mode'] === 'simple' ? 'is-active' : '' ?>"
            data-search-panel="simple">
            <input type="hidden" name="page" value="manage_docs">
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
            action="admin_dash.php"
            class="search-panel advanced-search-form <?= $search_state['mode'] === 'advanced' ? 'is-active' : '' ?>"
            data-search-panel="advanced">
            <input type="hidden" name="page" value="manage_docs">
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
                    <textarea name="description" rows="3" placeholder="Search inside the saved description"><?= htmlspecialchars($search_state['description']) ?></textarea>
                </label>

                <label class="filter-field filter-field-wide">
                    <span>Keywords</span>
                    <input type="text" name="keywords" value="<?= htmlspecialchars($search_state['keywords']) ?>" placeholder="Comma-separated tags or keywords">
                </label>
            </div>

            <div class="filter-actions">
                <a href="<?= htmlspecialchars($advanced_reset_url) ?>" class="btn-secondary">
                    <span class="material-icons-outlined">restart_alt</span> Reset
                </a>
            </div>
        </form>
    </div>

    <div class="breadcrumbs">
        <span class="material-icons-outlined" style="font-size:15px;">home</span>
        <a href="admin_dash.php?page=manage_docs">Root</a>

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
                echo '<span>/</span>';
                if ($index === count($trail) - 1) {
                    echo '<span style="color: var(--text-dark); font-weight:500;">' . htmlspecialchars($folder['name']) . '</span>';
                } else {
                    echo '<a href="admin_dash.php?page=manage_docs&folder_id=' . $folder['id'] . '">' . htmlspecialchars($folder['name']) . '</a>';
                }
            }
        }
        ?>

        <?php if ($has_search_filters): ?>
            <span>/</span>
            <span style="color: var(--text-dark);">Search results</span>
        <?php endif; ?>
    </div>

    <?php if ($has_search_filters): ?>
        <div class="search-label">
            <span class="material-icons-outlined" style="font-size:15px;">info</span>
            <strong><?= htmlspecialchars($search_summary) ?></strong>
            <?php if ($current_folder): ?> in the current folder<?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="table-shell">
        <table class="user-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>File Type</th>
                    <th>Author</th>
                    <th>Date Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($documents) === 0): ?>
                    <tr>
                        <td colspan="5" class="table-message-cell">
                            <div class="empty-state">
                                <div class="material-icons-outlined">
                                    <?= $has_search_filters ? 'search_off' : 'folder_open' ?>
                                </div>
                                <p>
                                    <?= $has_search_filters
                                        ? 'No files or folders matched the current search filters.'
                                        : 'This folder is empty. Create a folder or upload a file.' ?>
                                </p>
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
                        <tr data-id="<?= $row['id'] ?>">
                            <td data-label="Name">
                                <div class="item-name">
                                    <?php if ($row['type'] === 'folder'): ?>
                                        <span class="material-icons-outlined icon-folder">folder</span>
                                        <a href="admin_dash.php?page=manage_docs&folder_id=<?= $row['id'] ?>">
                                            <?= htmlspecialchars($row['name']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="material-icons-outlined icon-file">description</span>
                                        <a href="../<?= htmlspecialchars($row['file_path']) ?>" target="_blank" rel="noopener noreferrer">
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
                            <td data-label="Date Created"><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                            <td data-label="Actions">
                                <a href="Actions/delete_action.php?id=<?= $row['id'] ?>"
                                    class="text-danger"
                                    onclick="return confirm('Delete this item?')">
                                    Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<div id="folderModal" class="modal-overlay">
    <div class="modal-box">
        <h3>Create New Folder</h3>
        <form action="Actions/folder_action.php" method="POST">
            <input type="hidden" name="parent_id" value="<?= $current_folder ?>">
            <div class="modal-field">
                <label for="adminFolderName">Folder Name</label>
                <input id="adminFolderName" type="text" name="folder_name" placeholder="Folder name..." required>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeModal('folderModal')">Cancel</button>
                <button type="submit" name="create" class="btn-primary">
                    <span class="material-icons-outlined">create_new_folder</span> Create
                </button>
            </div>
        </form>
    </div>
</div>

<div id="uploadModal" class="modal-overlay">
    <div class="modal-box">
        <h3>Upload File</h3>
        <form action="Actions/upload_action.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="parent_id" value="<?= $current_folder ?>">

            <div class="modal-field">
                <label for="adminUploadFile">Document File</label>
                <input id="adminUploadFile" type="file" name="file" required>
            </div>

            <div class="modal-field">
                <label for="adminUploadAuthor">Author</label>
                <input
                    id="adminUploadAuthor"
                    type="text"
                    name="author"
                    placeholder="Leave blank to use your admin username">
            </div>

            <div class="modal-field">
                <label for="adminUploadDescription">Description</label>
                <textarea
                    id="adminUploadDescription"
                    name="description"
                    rows="3"
                    placeholder="Short summary of what this file contains"></textarea>
            </div>

            <div class="modal-field">
                <label for="adminUploadKeywords">Keywords</label>
                <input
                    id="adminUploadKeywords"
                    type="text"
                    name="keywords"
                    placeholder="exam, syllabus, policy, semester 2">
            </div>

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
    function openModal(id) {
        document.getElementById(id).classList.add('open');
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('open');
    }

    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function (e) {
            if (e.target === this) {
                closeModal(this.id);
            }
        });
    });

    (function () {
        const modeButtons = document.querySelectorAll('[data-search-mode]');
        const modePanels = document.querySelectorAll('[data-search-panel]');
        const searchInput = document.querySelector('[data-simple-search-input]');
        const advancedForm = document.querySelector('.advanced-search-form');
        const advancedFields = advancedForm
            ? advancedForm.querySelectorAll('input:not([type="hidden"]):not([type="submit"]):not([type="button"]), select, textarea')
            : [];
        let simpleSearchTimer;
        let advancedSearchTimer;

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

    let selectedFileUrl = null;
    let selectedFileName = null;
    let selectedFileId = null;

    document.addEventListener('DOMContentLoaded', function () {
        const rows = document.querySelectorAll('.user-table tbody tr[data-id]');
        const downloadBtn = document.getElementById('downloadBtn');
        const shareBtn = document.getElementById('shareBtn');

        rows.forEach(row => {
            row.addEventListener('click', function () {
                const isFile = this.querySelector('.icon-file') !== null;
                const fileLink = this.querySelector('.item-name a');

                rows.forEach(item => item.classList.remove('selected-row'));

                if (isFile && fileLink) {
                    this.classList.add('selected-row');
                    selectedFileUrl = fileLink.getAttribute('href');
                    selectedFileName = fileLink.textContent.trim();
                    selectedFileId = this.getAttribute('data-id');
                    if (downloadBtn) downloadBtn.disabled = false;
                    if (shareBtn) shareBtn.disabled = false;
                } else {
                    selectedFileUrl = null;
                    selectedFileName = null;
                    selectedFileId = null;
                    if (downloadBtn) downloadBtn.disabled = true;
                    if (shareBtn) shareBtn.disabled = true;
                }
            });
        });
    });

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
