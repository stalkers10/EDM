<?php
include('../../DB/database.php');
require_once '../../DB/document_search.php';

ensure_document_search_schema($conn);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $admin_id = $_SESSION['user_id'];
    $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;

    $file = $_FILES['file'];
    $fileName = trim($file['name']);
    $fileTmpName = $file['tmp_name'];
    $fileError = $file['error'];
    $author = trim($_POST['author'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $keywords = trim($_POST['keywords'] ?? '');

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

            // 3. Insert into admin_storage with searchable metadata
            $stmt = $conn->prepare("
                INSERT INTO admin_storage (
                    admin_id,
                    parent_id,
                    name,
                    type,
                    file_path,
                    file_extension,
                    author,
                    description,
                    keywords
                )
                VALUES (?, ?, ?, 'file', ?, ?, ?, ?, ?)
            ");
            $normalizedExt = $fileExt !== '' ? $fileExt : null;
            $normalizedAuthor = $author !== '' ? $author : null;
            $normalizedDescription = $description !== '' ? $description : null;
            $normalizedKeywords = $keywords !== '' ? $keywords : null;
            $stmt->bind_param(
                "iissssss",
                $admin_id,
                $parent_id,
                $fileName,
                $dbRelativePath,
                $normalizedExt,
                $normalizedAuthor,
                $normalizedDescription,
                $normalizedKeywords
            );

            if ($stmt->execute()) {
                // Success! Redirect back to the folder you were in
                $redirect = "../admin_dash.php?page=manage_docs";
                if ($parent_id !== null) {
                    $redirect .= "&folder_id=" . $parent_id;
                }
                header("Location: " . $redirect . "&status=success");
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
