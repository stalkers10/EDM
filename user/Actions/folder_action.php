<?php
include('../../DB/database.php');

if (isset($_POST['create'])) {
    $folder_name = mysqli_real_escape_string($conn, $_POST['folder_name']);
    $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : "NULL";
    $user_id = $_SESSION['user_id'];

    $sql = "INSERT INTO user_documents (user_id, parent_id, name, type) 
            VALUES ($user_id, $parent_id, '$folder_name', 'folder')";

    if (mysqli_query($conn, $sql)) {
        // Redirect back to the Documents page
        $redirect = "../Documents.php";
        if ($parent_id !== "NULL") {
            $redirect .= "?folder_id=" . $parent_id;
        }
        header("Location: " . $redirect);
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
