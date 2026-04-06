<?php
include('../../DB/database.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../mailer/PHPMailer/src/Exception.php';
require '../../mailer/PHPMailer/src/PHPMailer.php';
require '../../mailer/PHPMailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    // 1. Check if email exists
    $stmt = mysqli_prepare($conn, "SELECT id, username FROM users WHERE email = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
        $reset_otp = rand(100000, 999999);
        $_SESSION['reset_data'] = [
            'user_id' => $user['id'],
            'otp' => $reset_otp,
            'expiry' => time() + (5 * 60) // 5 minutes for reset
        ];

        // 3. Send Email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'sobfred30@gmail.com'; 
            $mail->Password = 'icvctnrqadoznulr'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('security@edm-platform.com', 'EDM Security');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Code';
            $mail->Body = "Your password reset code is: <b>$reset_otp</b>. It expires in 5 minutes.";
            
            $mail->send();
            header("Location: reset_password.php");
            exit();
        } catch (Exception $e) {
            echo "<script>alert('Error sending email');</script>";
        }
    } else {
        echo "<script>alert('Email not found in our system');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../Login.css"> <title>Forgot Password - EDM</title>
</head>
<body>
    <div class="body-div">
        <div class="Login-div">
            <p class="Login-pgh">Forgot Password</p>
            <form action="forgot-password.php" method="post">
                <div class="login-form">
                    <label>Enter your Registered Email</label>
                    <input type="email" name="email" required style="width:100%; padding:10px; margin-top:10px; border:1px solid #ccc; border-radius:5px;">
                </div>
                <button id="btn-Login" type="submit" style="margin-top:20px;">Send Reset Code</button>
                <a href="../Login.php" style="display:block; margin-top:15px; font-size:13px; color:#666;">Back to Login</a>
            </form>
        </div>
    </div>
</body>
</html>