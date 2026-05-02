<?php
session_start();

$otp_notice = $_SESSION['otp_notice'] ?? '';

// If there is no temporary session, kick them back to login
if (!isset($_SESSION['temp_user'])) {
    unset($_SESSION['otp_notice']);
    header("Location: Login.php");
    exit();
}

$error_msg = "";
$time_remaining = $_SESSION['temp_user']['expiry'] - time();

// If time is already up before they even submit
if ($time_remaining <= 0) {
    unset($_SESSION['temp_user']); // Destroy temp session
    unset($_SESSION['otp_notice']);
    echo "<script>alert('Your OTP has expired. Please log in again.'); window.location.href='Login.php';</script>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered_otp = trim($_POST['otp']);

    // Check if expired again at the moment of submission
    if (time() > $_SESSION['temp_user']['expiry']) {
        unset($_SESSION['temp_user']);
        unset($_SESSION['otp_notice']);
        echo "<script>alert('OTP expired.'); window.location.href='Login.php';</script>";
        exit();
    }

    // Verify OTP
    if ($entered_otp == $_SESSION['temp_user']['otp']) {
        // SUCCESS: Move temp data to actual login session variables
        $_SESSION['user_id']  = $_SESSION['temp_user']['id'];
        $_SESSION['username'] = $_SESSION['temp_user']['username'];
        $_SESSION['email']    = $_SESSION['temp_user']['email'];
        $_SESSION['role']     = $_SESSION['temp_user']['role'];

        // Clean up temp session
        unset($_SESSION['temp_user']);
        unset($_SESSION['otp_notice']);

        // Redirect based on role
        if ($_SESSION['role'] === 'admin') {
            header("Location: ../admin/admin_dash.php");
        } else {
            header("Location: ../user/user_dash.php");
        }
        exit();
    } else {
        $error_msg = "Invalid verification code.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - EDM</title>
    <style>
        :root {
            --burgundy: #800020;
            --main-bg: #f6f4f5;
            --card-bg: #ffffff;
            --border: #ede0e4;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: var(--main-bg);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 16px;
        }

        .otp-container {
            background: var(--card-bg);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border-top: 5px solid var(--burgundy);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        h2 {
            color: var(--burgundy);
            margin-bottom: 10px;
        }

        p {
            color: #5a4050;
            font-size: 14px;
            margin-bottom: 25px;
            line-height: 1.5;
        }

        input[type="text"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 18px;
            text-align: center;
            letter-spacing: 4px;
        }

        button {
            width: 100%;
            padding: 12px;
            background: var(--burgundy);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: #5a0016;
        }

        .timer {
            font-weight: bold;
            color: #c53030;
            font-size: 18px;
            margin-top: 15px;
        }

        .error {
            color: red;
            font-size: 13px;
            margin-bottom: 15px;
        }

        @media (max-width: 480px) {
            .otp-container {
                padding: 24px 18px;
                border-radius: 14px;
            }

            input[type="text"] {
                font-size: 16px;
                letter-spacing: 3px;
            }
        }
    </style>
</head>

<body>

    <div class="otp-container">
        <h2>Verification Required</h2>
        <p>We've sent a 6-digit code to &nbsp;<strong><?= htmlspecialchars($_SESSION['temp_user']['email']) ?></strong>.</p>

        <?php if (!empty($otp_notice)): ?>
            <div style="background: #fff3cd; color: #856404; padding: 10px; margin-bottom: 15px; border: 1px solid #ffeeba; border-radius: 4px;">
                <?= htmlspecialchars($otp_notice) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div class="error"><?= $error_msg ?></div>
        <?php endif; ?>

        <form action="otp.php" method="post">
            <input type="text" name="otp" maxlength="6" placeholder="000000" required autocomplete="off">
            <button type="submit">Verify & Login</button>
        </form>

        <?php if (!empty($otp_notice)): ?>
            <div style="background: #fff3cd; color: #856404; padding: 10px; margin-bottom: 15px; border: 1px solid #ffeeba; border-radius: 4px;">
                <strong>TESTING OTP: </strong> <?= htmlspecialchars((string) $_SESSION['temp_user']['otp']) ?>
            </div>
        <?php endif; ?>

        <div class="timer">Time left: <span id="countdown">03:00</span></div>
    </div>

    <script>
        // Get the remaining time from PHP
        let timeLeft = <?= $time_remaining ?>;
        let countdownElement = document.getElementById('countdown');

        let timer = setInterval(function() {
            if (timeLeft <= 0) {
                clearInterval(timer);
                countdownElement.innerHTML = "Expired";
                // Optionally auto-redirect to login when time runs out
                window.location.href = 'Login.php';
            } else {
                let minutes = Math.floor(timeLeft / 60);
                let seconds = timeLeft % 60;

                // Add leading zero to seconds if needed
                seconds = seconds < 10 ? '0' + seconds : seconds;
                minutes = minutes < 10 ? '0' + minutes : minutes;

                countdownElement.innerHTML = minutes + ":" + seconds;
                timeLeft--;
            }
        }, 1000);
    </script>

</body>

</html>
