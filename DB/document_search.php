<?php

function edm_table_exists(mysqli $conn, string $table): bool
{
    $stmt = $conn->prepare("
        SELECT 1
        FROM information_schema.tables
        WHERE table_schema = DATABASE() AND table_name = ?
        LIMIT 1
    ");
    $stmt->bind_param("s", $table);
    $stmt->execute();
    $exists = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    return $exists;
}

function edm_column_exists(mysqli $conn, string $table, string $column): bool
{
    $stmt = $conn->prepare("
        SELECT 1
        FROM information_schema.columns
        WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?
        LIMIT 1
    ");
    $stmt->bind_param("ss", $table, $column);
    $stmt->execute();
    $exists = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    return $exists;
}

function edm_add_column_if_missing(mysqli $conn, string $table, string $column, string $definition): void
{
    if (!edm_table_exists($conn, $table) || edm_column_exists($conn, $table, $column)) {
        return;
    }

    mysqli_query($conn, "ALTER TABLE `$table` ADD COLUMN $definition");
}

function ensure_document_search_schema(mysqli $conn): void
{
    foreach (['admin_storage', 'user_documents'] as $table) {
        if (!edm_table_exists($conn, $table)) {
            continue;
        }

        edm_add_column_if_missing($conn, $table, 'file_extension', "`file_extension` VARCHAR(20) NULL AFTER `file_path`");
        edm_add_column_if_missing($conn, $table, 'author', "`author` VARCHAR(255) NULL AFTER `file_extension`");
        edm_add_column_if_missing($conn, $table, 'description', "`description` TEXT NULL AFTER `author`");
        edm_add_column_if_missing($conn, $table, 'keywords', "`keywords` TEXT NULL AFTER `description`");

        mysqli_query(
            $conn,
            "UPDATE `$table`
             SET `file_extension` = LOWER(SUBSTRING_INDEX(`name`, '.', -1))
             WHERE `type` = 'file'
               AND (`file_extension` IS NULL OR `file_extension` = '')
               AND `name` LIKE '%.%'"
        );
    }
}

function edm_normalize_document_date(?string $value): string
{
    $value = trim((string) $value);
    if ($value === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        return '';
    }

    return $value;
}

function edm_get_document_search_state(array $source): array
{
    $mode = ($source['search_mode'] ?? 'simple') === 'advanced' ? 'advanced' : 'simple';
    $item_type = trim((string) ($source['item_type'] ?? ''));
    if (!in_array($item_type, ['file', 'folder'], true)) {
        $item_type = '';
    }

    return [
        'mode' => $mode,
        'q' => trim((string) ($source['q'] ?? '')),
        'name' => trim((string) ($source['name'] ?? '')),
        'item_type' => $item_type,
        'file_type' => ltrim(strtolower(trim((string) ($source['file_type'] ?? ''))), '.'),
        'author' => trim((string) ($source['author'] ?? '')),
        'description' => trim((string) ($source['description'] ?? '')),
        'keywords' => trim((string) ($source['keywords'] ?? '')),
        'date_from' => edm_normalize_document_date($source['date_from'] ?? ''),
        'date_to' => edm_normalize_document_date($source['date_to'] ?? ''),
    ];
}

function edm_document_search_has_filters(array $state): bool
{
    foreach (['q', 'name', 'item_type', 'file_type', 'author', 'description', 'keywords', 'date_from', 'date_to'] as $field) {
        if (($state[$field] ?? '') !== '') {
            return true;
        }
    }

    return false;
}

function edm_build_url(string $base, array $params = []): string
{
    $filtered = [];
    foreach ($params as $key => $value) {
        if ($value === null || $value === '') {
            continue;
        }
        $filtered[$key] = $value;
    }

    return empty($filtered) ? $base : $base . '?' . http_build_query($filtered);
}

function edm_get_admin_storage_extensions(mysqli $conn): array
{
    ensure_document_search_schema($conn);

    if (!edm_table_exists($conn, 'admin_storage')) {
        return [];
    }

    $extensions = [];
    $sql = "
        SELECT DISTINCT file_extension
        FROM admin_storage
        WHERE type = 'file'
          AND file_extension IS NOT NULL
          AND file_extension <> ''
        ORDER BY file_extension ASC
    ";
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        return [];
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $extensions[] = strtolower($row['file_extension']);
    }

    return $extensions;
}

function edm_get_document_search_summary(array $state): string
{
    if (!edm_document_search_has_filters($state)) {
        return '';
    }

    if ($state['mode'] === 'simple') {
        return 'Showing results for "' . $state['q'] . '"';
    }

    $parts = [];
    if ($state['name'] !== '') {
        $parts[] = 'Name: ' . $state['name'];
    }
    if ($state['item_type'] !== '') {
        $parts[] = 'Item type: ' . ucfirst($state['item_type']);
    }
    if ($state['file_type'] !== '') {
        $parts[] = 'File type: ' . strtoupper($state['file_type']);
    }
    if ($state['author'] !== '') {
        $parts[] = 'Author: ' . $state['author'];
    }
    if ($state['description'] !== '') {
        $parts[] = 'Description: ' . $state['description'];
    }
    if ($state['keywords'] !== '') {
        $parts[] = 'Keywords: ' . $state['keywords'];
    }
    if ($state['date_from'] !== '') {
        $parts[] = 'From: ' . $state['date_from'];
    }
    if ($state['date_to'] !== '') {
        $parts[] = 'To: ' . $state['date_to'];
    }

    return implode(' | ', $parts);
}

function edm_fetch_admin_storage_documents(mysqli $conn, array $state, ?int $current_folder, bool $restrict_search_to_current_folder = false): array
{
    ensure_document_search_schema($conn);

    $has_filters = edm_document_search_has_filters($state);
    $conditions = [];
    $types = '';
    $params = [];

    $sql = "
        SELECT
            s.*,
            u.username AS uploader_name,
            COALESCE(NULLIF(s.author, ''), u.username) AS author_display
        FROM admin_storage s
        LEFT JOIN users u ON u.id = s.admin_id
    ";

    if ($has_filters) {
        if ($restrict_search_to_current_folder && $current_folder !== null) {
            $conditions[] = "s.parent_id = ?";
            $types .= "i";
            $params[] = $current_folder;
        }

        if ($state['mode'] === 'advanced') {
            if ($state['name'] !== '') {
                $conditions[] = "s.name LIKE ?";
                $types .= "s";
                $params[] = '%' . $state['name'] . '%';
            }

            if ($state['item_type'] !== '') {
                $conditions[] = "s.type = ?";
                $types .= "s";
                $params[] = $state['item_type'];
            }

            if ($state['file_type'] !== '') {
                $conditions[] = "s.type = 'file'";
                $conditions[] = "LOWER(COALESCE(s.file_extension, '')) = ?";
                $types .= "s";
                $params[] = $state['file_type'];
            }

            if ($state['author'] !== '') {
                $conditions[] = "COALESCE(NULLIF(s.author, ''), u.username, '') LIKE ?";
                $types .= "s";
                $params[] = '%' . $state['author'] . '%';
            }

            if ($state['description'] !== '') {
                $conditions[] = "COALESCE(s.description, '') LIKE ?";
                $types .= "s";
                $params[] = '%' . $state['description'] . '%';
            }

            if ($state['keywords'] !== '') {
                $conditions[] = "COALESCE(s.keywords, '') LIKE ?";
                $types .= "s";
                $params[] = '%' . $state['keywords'] . '%';
            }

            if ($state['date_from'] !== '') {
                $conditions[] = "DATE(s.created_at) >= ?";
                $types .= "s";
                $params[] = $state['date_from'];
            }

            if ($state['date_to'] !== '') {
                $conditions[] = "DATE(s.created_at) <= ?";
                $types .= "s";
                $params[] = $state['date_to'];
            }
        } else {
            $conditions[] = "(
                s.name LIKE ?
                OR COALESCE(NULLIF(s.author, ''), u.username, '') LIKE ?
                OR COALESCE(s.description, '') LIKE ?
                OR COALESCE(s.keywords, '') LIKE ?
                OR COALESCE(s.file_extension, '') LIKE ?
            )";

            $pattern = '%' . $state['q'] . '%';
            $types .= "sssss";
            $params[] = $pattern;
            $params[] = $pattern;
            $params[] = $pattern;
            $params[] = $pattern;
            $params[] = strtolower($pattern);
        }
    } else {
        if ($current_folder !== null) {
            $conditions[] = "s.parent_id = ?";
            $types .= "i";
            $params[] = $current_folder;
        } else {
            $conditions[] = "s.parent_id IS NULL";
        }
    }

    if ($conditions) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    $sql .= " ORDER BY s.type DESC, s.name ASC";

    $stmt = $conn->prepare($sql);
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();

    return $rows;
}
