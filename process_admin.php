<?php
include 'database.php';

// Safety check: Ensure the session is started and user is an admin

/*if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo ("<script> alert('You are not authorized to access this page')</script>");
    echo ("<script> window.location.href = 'Login.php'</script>");
    exit();
}*/

// Determine if we are getting a task via GET (links) or POST (edit form)
$task = isset($_GET['task']) ? $_GET['task'] : (isset($_POST['task']) ? $_POST['task'] : '');
$id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0);

if ($id <= 0 && $task !== 'create') {
    header("Location: admin_dash.php?error=invalid_id");
    exit();
}

switch ($task) {

    // 1. TOGGLE USER STATUS (Activate/Deactivate)
    case 'toggle':
        $sql = "UPDATE users SET status = 1 - status WHERE id = $id";
        if ($conn->query($sql)) {
            header("Location: admin_dash.php?page=users");
        } else {
            die("Error updating status: " . $conn->error);
        }
        break;

    // 2. DELETE USER
    case 'delete':
       // $sql = "UPDATE users SET status = 0 WHERE id = $id"; // Optional: Logical delete
        // OR a physical delete:
        $sql = "DELETE FROM users WHERE id = $id";

        if ($conn->query($sql)) {
            header("Location: admin_dash.php?page=users");
        } else {
            die("Error deleting user: " . $conn->error);
        }
        break;

    // 3. UPDATE USER DETAILS (From the Edit Form)
    case 'update':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $conn->real_escape_string($_POST['username']);
            $email    = $conn->real_escape_string($_POST['email']);
            $phone    = $conn->real_escape_string($_POST['phone_num']);
            $role     = $conn->real_escape_string($_POST['role']);

            $sql = "UPDATE users SET 
                    username = '$username', 
                    email = '$email', 
                    phone_num = '$phone', 
                    role = '$role',
                    updated_at = CURRENT_TIMESTAMP 
                    WHERE id = $id";

            if ($conn->query($sql)) {
                header("Location: admin_dash.php?page=users");
            } else {
                die("Error updating profile: " . $conn->error);
            }
        }
        break;

    case 'reset':
        $new_pass = "Reset@123";
        $hashed_pass = password_hash($new_pass, PASSWORD_BCRYPT);

        $sql = "UPDATE users SET passwd = '$hashed_pass' WHERE id = $id";
        if ($conn->query($sql)) {
            header("Location: admin_dash.php?msg=password_reset");
        }
        break;

    case 'create':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $conn->real_escape_string($_POST['username']);
            $email = $conn->real_escape_string($_POST['email']);
            $phone = $conn->real_escape_string($_POST['phone_num']);
            $role = $conn->real_escape_string($_POST['role']);
            $pass = password_hash($_POST['password'], PASSWORD_BCRYPT);

            // 1. Check if username or email already exists
            $check_sql = "SELECT id FROM users WHERE username = '$username' OR email = '$email' LIMIT 1";
            $check_result = $conn->query($check_sql);

            if ($check_result->num_rows > 0) {
                // 2. If a row is found, stop and send an error message
                header("Location: add_user.php?error=exists");
                exit();
            } else {
                // 3. If no row found, proceed with insertion
                $sql = "INSERT INTO users (email, username, passwd, phone_num, role, status) 
                    VALUES ('$email', '$username', '$pass', '$phone', '$role', 1)";

                if ($conn->query($sql)) {
                    header("Location: admin_dash.php?page=users");
                } else {
                    die("Error creating user: " . $conn->error);
                }
            }
        }
        break;
    default:
        header("Location: admin_dash.php");
        break;
}

$conn->close();
exit();
