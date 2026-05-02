<?php
session_start();
include('../../DB/database.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login_logout/Login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $user_id = $_SESSION['user_id'];

    // 1. Get info about the item to know where to redirect back to, and ensure ownership
    $info_q = mysqli_query($conn, "SELECT parent_id, type, file_path FROM user_documents WHERE id = $id AND user_id = $user_id");
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
        
        // 3. Delete from database
        $delete_sql = "DELETE FROM user_documents WHERE id = $id AND user_id = $user_id";
        
        if (mysqli_query($conn, $delete_sql)) {
            header("Location: ../Documents.php" . $parent_redirect);
            exit();
        } else {
            echo "Error deleting record: " . mysqli_error($conn);
        }
    } else {
        echo "Error: Item not found or you don't have permission to delete it.";
    }
} else {
    header("Location: ../Documents.php");
}
?>
