<?php
// Prevent any PHP warnings from messing up our JSON output
error_reporting(0); 
header('Content-Type: application/json');

include '../../DB/database.php';

$response = ['success' => false, 'hash' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file_id'])) {
    $file_id = (int)$_POST['file_id'];
    $hash = bin2hex(random_bytes(16));

    $sql = "INSERT INTO public_shares (file_id, share_hash) VALUES ($file_id, '$hash')";
    
    if (mysqli_query($conn, $sql)) {
        $response['success'] = true;
        $response['hash'] = $hash;
    } else {
        $response['message'] = mysqli_error($conn);
    }
} else {
    $response['message'] = 'Invalid request';
}

echo json_encode($response);
exit();