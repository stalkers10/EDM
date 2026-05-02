<?php
session_start();
include('../../DB/database.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login_logout/Login.php');
    exit();
}

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file_id']) && isset($_POST['share_with_id'])) {
    $file_id = (int)$_POST['file_id'];
    $share_with_id = (int)$_POST['share_with_id'];
    $shared_by_id = $_SESSION['user_id'];
    
    // We assume the source is 'user' for now, but we could make it dynamic
    $file_source = 'user';

    // Prevent sharing with oneself
    if ($share_with_id === $shared_by_id) {
        $response['message'] = "You cannot share a file with yourself.";
        echo json_encode($response);
        exit();
    }

    // Check if already shared
    $check_q = mysqli_query($conn, "SELECT id FROM shared_with_users WHERE file_id = $file_id AND shared_with_id = $share_with_id AND file_source = '$file_source'");
    if (mysqli_num_rows($check_q) > 0) {
        $response['message'] = "This file is already shared with this user.";
        echo json_encode($response);
        exit();
    }

    $sql = "INSERT INTO shared_with_users (file_id, file_source, shared_with_id, shared_by_id) 
            VALUES ($file_id, '$file_source', $share_with_id, $shared_by_id)";
    
    if (mysqli_query($conn, $sql)) {
        $response['success'] = true;
    } else {
        $response['message'] = mysqli_error($conn);
    }
} else {
    $response['message'] = 'Invalid request';
}

echo json_encode($response);
exit();
?>
