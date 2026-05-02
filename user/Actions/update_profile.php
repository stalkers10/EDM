<?php
session_start();
include '../../DB/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_stmt = $conn->prepare("SELECT username, email, phone_num FROM users WHERE id = ?");
    $current_stmt->bind_param("i", $user_id);
    $current_stmt->execute();
    $current_user = $current_stmt->get_result()->fetch_assoc();

    if (!$current_user) {
        $response['message'] = 'User not found.';
        echo json_encode($response);
        exit();
    }

    $posted_username = isset($_POST['username']) ? trim($_POST['username']) : $current_user['username'];
    $posted_email    = isset($_POST['email']) ? trim($_POST['email']) : $current_user['email'];
    $posted_phone    = isset($_POST['phone']) ? trim($_POST['phone']) : ($current_user['phone_num'] ?? '');
    $new_password    = $_POST['password'] ?? '';

    $new_username = mysqli_real_escape_string($conn, $posted_username);
    $new_email    = mysqli_real_escape_string($conn, $posted_email);
    $new_phone    = mysqli_real_escape_string($conn, $posted_phone);

    // 1. Basic Validation
    if ($posted_username === '' || $posted_email === '') {
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
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['profile_pic'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $response['message'] = 'Image upload failed. Please try again.';
            echo json_encode($response);
            exit();
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($ext, $allowed, true) || getimagesize($file['tmp_name']) === false) {
            $response['message'] = 'Please upload a valid image file (JPG, PNG, GIF, or WEBP).';
            echo json_encode($response);
            exit();
        }

        $newName = "profile_" . $user_id . "_" . time() . "." . $ext;
        $project_root = dirname(__DIR__, 2);
        $upload_dir = $project_root . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'profiles';
        $upload_path = $upload_dir . DIRECTORY_SEPARATOR . $newName;
        $dbPath = "uploads/profiles/" . $newName;

        if (!is_dir($upload_dir) && !mkdir($upload_dir, 0777, true)) {
            $response['message'] = 'Could not create the profile image folder.';
            echo json_encode($response);
            exit();
        }

        if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
            $response['message'] = 'Could not save the uploaded profile image.';
            echo json_encode($response);
            exit();
        }

        $profile_pic_path = $dbPath;
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
        if ($profile_pic_path) {
            $response['profile_pic_url'] = '../' . $profile_pic_path . '?v=' . time();
        }
    } else {
        $response['message'] = "Database Error: " . mysqli_error($conn);
    }
} else {
    $response['message'] = "Invalid Request Method";
}

echo json_encode($response);
exit();
?>
