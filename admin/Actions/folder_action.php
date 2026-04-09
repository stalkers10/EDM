<?php
include('../../DB/database.php');

if (isset($_POST['create'])) {
    $folder_name = mysqli_real_escape_string($conn, $_POST['folder_name']);
    $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : "NULL";
    $admin_id = $_SESSION['user_id'];

    // Notice: parent_id doesn't get quotes if it is NULL
    $sql = "INSERT INTO admin_storage (admin_id, parent_id, name, type) 
            VALUES ($admin_id, $parent_id, '$folder_name', 'folder')";

    if (mysqli_query($conn, $sql)) {
        // Redirect back to the same folder level
        $redirect = "../admin_dash.php?page=manage_docs";
        if ($parent_id !== "NULL") {
            $redirect .= "&folder_id=" . $parent_id;
        }
        header("Location: " . $redirect);
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>