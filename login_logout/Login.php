<?php

require_once __DIR__ . '/../DB/database.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function isLocalEnvironment(): bool
{
  $host = strtolower($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '');
  $host = explode(':', $host)[0];

  return in_array($host, ['localhost', '127.0.0.1', '::1'], true);
}

function loadMailerDependencies(): bool
{
  static $loaded = false;

  if ($loaded) {
    return true;
  }

  $mailerBase = dirname(__DIR__) . '/mailer/PHPMailer/src/';
  $requiredFiles = ['Exception.php', 'PHPMailer.php', 'SMTP.php'];

  foreach ($requiredFiles as $file) {
    $path = $mailerBase . $file;
    if (!is_file($path)) {
      return false;
    }
    require_once $path;
  }

  $loaded = true;
  return true;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  $username = trim($_POST["username"]);
  $pswd     = $_POST["password"];

  if (empty($username) || empty($pswd)) {
    echo "<script>alert('Please fill in all fields'); window.location.href='Login.php';</script>";
    exit();
  }

  $stmt = mysqli_prepare($conn, "SELECT id, username, email, passwd, role, status FROM users WHERE username = ? LIMIT 1");
  mysqli_stmt_bind_param($stmt, "s", $username);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $user   = mysqli_fetch_assoc($result);
  mysqli_stmt_close($stmt);

  if ($user && password_verify($pswd, $user['passwd'])) {

    // Account Status Check
    $is_active = ($user['status'] === 'active' || $user['status'] == 1);
    if (!$is_active) {
      echo "<script>alert('Your account is inactive. Contact the administrator.'); window.location.href='Login.php';</script>";
      exit();
    }

    // --- OTP GENERATION ---
    $otp = rand(100000, 999999);
    $expiry = time() + (3 * 60); // 3 minutes
    unset($_SESSION['otp_notice']);

    // Store in temporary session for verification
    $_SESSION['temp_user'] = [
      'id'       => $user['id'],
      'username' => $user['username'],
      'email'    => $user['email'],
      'role'     => $user['role'],
      'otp'      => $otp,
      'expiry'   => $expiry
    ];

    if (!loadMailerDependencies()) {
      if (isLocalEnvironment()) {
        $_SESSION['otp_notice'] = 'Email delivery is unavailable on this local setup. Use the testing code below to continue logging in.';
        header("Location: otp.php");
        exit();
      }

      echo "<script>alert('The login email service is not configured correctly on the server. Please verify the PHPMailer files were uploaded.'); window.location.href='Login.php';</script>";
      exit();
    }

    // --- SENDING EMAIL VIA PHPMAILER ---
    $mail = new PHPMailer(true);

    try {
      $mail->isSMTP();
      $mail->Host       = 'smtp.gmail.com';
      $mail->SMTPAuth   = true;
      $mail->Username   = 'sobfred30@gmail.com';
      $mail->Password   = 'icvctnrqadoznulr';
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port       = 587;

      $mail->setFrom('sobfred30@gmail.com', 'EDM Security');
      $mail->addAddress($user['email']);

      $mail->isHTML(true);
      $mail->Subject = 'Your EDM Verification Code';
      $mail->Body    = "
                <div style='font-family: Arial; border-top: 4px solid #800020; padding: 20px;'>
                    <h2 style='color: #800020;'>Security Verification</h2>
                    <p>Hello <b>{$user['username']}</b>,</p>
                    <p>Your 6-digit verification code is:</p>
                    <h1 style='letter-spacing: 5px; color: #333;'>$otp</h1>
                    <p>This code will expire in <b>3 minutes</b>.</p>
                </div>";

      $mail->send();
      unset($_SESSION['otp_notice']);
      header("Location: otp.php");
      exit();
    } catch (Exception $e) {
      error_log('EDM login OTP mail failed: ' . $e->getMessage());

      if (isLocalEnvironment()) {
        $_SESSION['otp_notice'] = 'Email delivery is unavailable on this local setup. Use the testing code below to continue logging in.';
        header("Location: otp.php");
        exit();
      }

      echo "<script>alert('Unable to send the verification code right now. Please check your mail server settings.'); window.location.href='Login.php';</script>";
      exit();
    }
  } else {
    echo "<script>alert('Invalid username or password'); window.location.href='Login.php';</script>";
    exit();
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - EDM</title>
  <link rel="stylesheet" href="Login.css?v=<?= filemtime(__DIR__ . '/Login.css') ?>">
</head>

<body>
  <nav>
    <h2 class="header-name">EDM login</h2>
  </nav>

  <div class="body-div">
    <form action="Login.php" method="post">
      <div class="Login-div">
        <p class="Login-pgh">Login</p>

        <div class="login-form">
          <label for="log-username">Username</label>
          <input type="text" name="username" id="log-username" required>

          <label for="log-password">Password</label>
          <input type="password" name="password" id="log-password" required>
        </div>

        <button id="btn-Login" name="login" type="submit">Login</button>

        <div class="selection-btm">
          <a href="forgot_password/forgot-password.php" style="color: var(--burgundy); font-size: 13px; text-decoration: none; display: block; margin: 10px 0;">Forgot Password?</a>

          <p>Don't have an account yet?</p>
          <a href="../signup/sign_up.php" class="secondary-link-btn">Sign up</a>
        </div>
      </div>
    </form>
  </div>
</body>

</html>
