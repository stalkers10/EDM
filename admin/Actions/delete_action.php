<?php
session_start();
include('../../DB/database.php');

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // 1. Get info about the item to know where to redirect back to
    $info_q = mysqli_query($conn, "SELECT parent_id, type, file_path FROM admin_storage WHERE id = $id");
    $item = mysqli_fetch_assoc($info_q);

    if ($item) {
        $parent_redirect = $item['parent_id'] ? "?folder_id=" . $item['parent_id'] : "";

        // 2. If it's a file, delete the physical file from the server
        if ($item['type'] === 'file' && !empty($item['file_path'])) {
            $physical_path = "../../" . $item['file_path'];
            if (file_exists($physical_path)) {
                unlink($physical_path);
            }
        }
        
        $delete_sql = "DELETE FROM admin_storage WHERE id = $id";
        
        if (mysqli_query($conn, $delete_sql)) {
            header("Location: ../admin_dash.php?page=manage_docs" . $parent_redirect);
            exit();
        } else {
            echo "Error deleting record: " . mysqli_error($conn);
        }
    }
} else {
    header("Location: ../admin_dash.php?page=manage_docs");
}