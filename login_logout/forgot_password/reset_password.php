<?php
session_start();
include('../../DB/database.php');

if (!isset($_SESSION['reset_data'])) {
    header("Location: forgot_password.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered_otp = $_POST['otp'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    // 1. Verify OTP and Expiry
    if ($entered_otp != $_SESSION['reset_data']['otp'] || time() > $_SESSION['reset_data']['expiry']) {
        echo "<script>alert('Invalid or expired code');</script>";
    } 
    // 2. Check if passwords match
    elseif ($new_pass !== $confirm_pass) {
        echo "<script>alert('Passwords do not match');</script>";
    } 
    else {
        // 3. Update Database
        $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
        $user_id = $_SESSION['reset_data']['user_id'];

        $stmt = mysqli_prepare($conn, "UPDATE users SET passwd = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $hashed_pass, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            unset($_SESSION['reset_data']);
            echo "<script>alert('Password updated successfully!'); window.location.href='../Login.php';</script>";
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../Login.css">
    <title>Reset Password - EDM</title> 
</head>
<body>
    <div class="body-div">
        <div class="Login-div">
            <p class="Login-pgh">Reset Password</p>
            <form action="reset_password.php" method="post">
                <div class="login-form">
                    <label>6-Digit Code</label>
                    <input type="text" name="otp" required maxlength="6">
                    
                    <label>New Password</label>
                    <input type="password" name="new_password" required>
                    
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <button id="btn-Login" type="submit" style="margin-top:20px;">Update Password</button>
            </form>
        </div>
    </div>
</body>
</html>