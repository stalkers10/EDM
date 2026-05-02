<?php
session_start();
include '../../DB/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $new_email    = mysqli_real_escape_string($conn, trim($_POST['email']));
    $new_phone    = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $new_password = $_POST['password'];

    // 1. Basic Validation
    if (empty($new_username) || empty($new_email)) {
        echo json_encode(['success' => false, 'message' => 'Username and Email are required.']);
        exit();
    }

    // 2. Check for duplicate username/email
    $check_q = "SELECT id FROM users WHERE (username = '$new_username' OR email = '$new_email') AND id != $user_id LIMIT 1";
    $check_res = mysqli_query($conn, $check_q);
    if (mysqli_num_rows($check_res) > 0) {
        echo json_encode(['success' => false, 'message' => 'Username or Email already in use.']);
        exit();
    }

    // 3. Handle Profile Picture Upload
    $profile_pic_path = null;
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
        $file = $_FILES['profile_pic'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($ext, $allowed)) {
            $newName = "profile_" . $user_id . "_" . time() . "." . $ext;
            $uploadPath = "../../uploads/profiles/" . $newName;
            $dbPath = "uploads/profiles/" . $newName;

            // Ensure directory exists
            if (!is_dir('../../uploads/profiles/')) {
                mkdir('../../uploads/profiles/', 0777, true);
            }

            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $profile_pic_path = $dbPath;
            }
        }
    }

    // 4. Build Update Query
    $update_parts = [
        "username = '$new_username'",
        "email = '$new_email'",
        "phone_num = '$new_phone'"
    ];

    if ($profile_pic_path) {
        $update_parts[] = "profile_pic = '$profile_pic_path'";
    }

    if (!empty($new_password)) {
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        $update_parts[] = "passwd = '$hash'";
    }

    $sql = "UPDATE users SET " . implode(", ", $update_parts) . " WHERE id = $user_id";

    if (mysqli_query($conn, $sql)) {
        // Update session variables
        $_SESSION['username'] = $new_username;
        $_SESSION['email'] = $new_email;
        
        $response['success'] = true;
    } else {
        $response['message'] = "Database Error: " . mysqli_error($conn);
    }
} else {
    $response['message'] = "Invalid Request Method";
}

echo json_encode($response);
exit();
?>
