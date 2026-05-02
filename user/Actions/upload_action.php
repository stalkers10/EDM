<?php
include('../../DB/database.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $user_id = $_SESSION['user_id'];
    $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : "NULL";

    $file = $_FILES['file'];
    $fileName = mysqli_real_escape_string($conn, $file['name']);
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];

    // 1. Check for errors
    if ($fileError === 0) {
        // Create a unique name to prevent overwriting files with the same name
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $newFileName = uniqid('', true) . "." . $fileExt;

        // Define where the file will sit physically
        $uploadDestination = '../../uploads/' . $newFileName;
        // This path is what we store in the DB so the web app can find it later
        $dbRelativePath = 'uploads/' . $newFileName;

        // 2. Move file to the uploads folder
        if (move_uploaded_file($fileTmpName, $uploadDestination)) {

            // 3. Insert into user_documents
            $sql = "INSERT INTO user_documents (user_id, parent_id, name, type, file_path) 
                    VALUES ($user_id, $parent_id, '$fileName', 'file', '$dbRelativePath')";

            if (mysqli_query($conn, $sql)) {
                // Success! Redirect back to the Documents page
                $redirect = "../Documents.php";
                if ($parent_id !== "NULL") {
                    $redirect .= "?folder_id=" . $parent_id;
                }
                header("Location: " . $redirect . (strpos($redirect, '?') === false ? '?' : '&') . "status=success");
            } else {
                echo "Database Error: " . mysqli_error($conn);
            }
        } else {
            echo "Failed to move uploaded file.";
        }
    } else {
        echo "There was an error uploading your file. Error Code: " . $fileError;
    }
}
?>
