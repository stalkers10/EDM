<?php
include('../../DB/database.php');
require_once '../../DB/document_search.php';

ensure_document_search_schema($conn);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    if (!edm_table_exists($conn, 'user_documents')) {
        echo "The user documents table is not available in this environment.";
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;

    $file = $_FILES['file'];
    $fileName = trim($file['name']);
    $fileTmpName = $file['tmp_name'];
    $fileError = $file['error'];
    $fallbackAuthor = trim($_SESSION['username'] ?? '');

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

            // 3. Insert into user_documents if that table exists in this environment
            $stmt = $conn->prepare("
                INSERT INTO user_documents (
                    user_id,
                    parent_id,
                    name,
                    type,
                    file_path,
                    file_extension,
                    author
                )
                VALUES (?, ?, ?, 'file', ?, ?, ?)
            ");
            $normalizedExt = $fileExt !== '' ? $fileExt : null;
            $normalizedAuthor = $fallbackAuthor !== '' ? $fallbackAuthor : null;
            $stmt->bind_param(
                "iissss",
                $user_id,
                $parent_id,
                $fileName,
                $dbRelativePath,
                $normalizedExt,
                $normalizedAuthor
            );

            if ($stmt->execute()) {
                // Success! Redirect back to the Documents page
                $redirect = "../Documents.php";
                if ($parent_id !== null) {
                    $redirect .= "?folder_id=" . $parent_id;
                }
                header("Location: " . $redirect . (strpos($redirect, '?') === false ? '?' : '&') . "status=success");
            } else {
                echo "Database Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Failed to move uploaded file.";
        }
    } else {
        echo "There was an error uploading your file. Error Code: " . $fileError;
    }
}
?>
