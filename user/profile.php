<?php
include '../DB/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login_logout/Login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch current user data
$stmt = $conn->prepare("SELECT username, email, phone_num, profile_pic FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$username = htmlspecialchars($user['username'] ?? 'User');
$email    = htmlspecialchars($user['email']    ?? 'No email');
$phone    = htmlspecialchars($user['phone_num'] ?? '');

$profile_pic = '../assets/default_avatar.png';
if (!empty($user['profile_pic'])) {
    $stored_profile_path = ltrim(str_replace('\\', '/', $user['profile_pic']), '/');
    $profile_pic_file = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $stored_profile_path);

    if (is_file($profile_pic_file)) {
        $profile_pic = '../' . $stored_profile_path;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | EDM</title>
    <link rel="stylesheet" href="user_style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined">
</head>

<body>

    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <div class="content-card profile-card">

            <div class="profile-header">
                <div class="avatar-section">
                    <img src="<?= $profile_pic ?>" alt="Profile Picture" class="profile-avatar" id="avatarPreview">
                    <label for="fileInput" class="change-photo-btn">
                        <span class="material-icons-outlined" style="font-size: 18px;">photo_camera</span>
                    </label>
                </div>
                <div class="header-info">
                    <h1 style="font-size: 24px; font-weight: 700; color: #fff;"><?= $username ?></h1>
                    <p style="font-size: 13px; color: var(--sidebar-muted);"><?= $email ?></p>
                </div>
            </div>

            <div id="statusAlert" class="alert"></div>

            <form id="profileForm" action="Actions/update_profile.php" method="POST" enctype="multipart/form-data">
                <input type="file" name="profile_pic" id="fileInput" accept="image/*" style="display: none;">
                
                <div class="settings-card">
                    <h3>Personal Information</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" name="username" id="username" value="<?= $username ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" name="email" id="email" value="<?= $email ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="text" name="phone" id="phone" value="<?= $phone ?>">
                        </div>
                    </div>
                </div>

                <div class="settings-card">
                    <h3>Security</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input type="password" name="password" id="password" placeholder="Leave blank to keep current">
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" name="confirm_password" id="confirm_password">
                        </div>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; margin-top: 20px;">
                    <button type="submit" class="submit-btn">
                        <span class="material-icons-outlined">save</span>
                        <span>Save Changes</span>
                    </button>
                </div>
            </form>

        </div>
    </main>

    <script>
        const profileForm = document.getElementById('profileForm');
        const fileInput = document.getElementById('fileInput');
        const avatarPreview = document.getElementById('avatarPreview');
        const alert = document.getElementById('statusAlert');

        function submitProfileUpdate(formData, successMessage) {
            fetch('Actions/update_profile.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert.className = 'alert alert-success';
                    alert.textContent = successMessage;

                    if (data.profile_pic_url) {
                        avatarPreview.src = data.profile_pic_url;
                    }

                    document.querySelector('.header-info h1').textContent = document.getElementById('username').value;
                    document.querySelector('.header-info p').textContent = document.getElementById('email').value;

                    const sidebarName = document.querySelector('.u-name');
                    if (sidebarName) sidebarName.textContent = document.getElementById('username').value;

                    const sidebarEmail = document.querySelector('.u-email');
                    if (sidebarEmail) sidebarEmail.textContent = document.getElementById('email').value;
                } else {
                    alert.className = 'alert alert-error';
                    alert.textContent = data.message || "An error occurred.";
                }

                alert.style.display = 'block';
                window.scrollTo(0, 0);
            })
            .catch(() => {
                alert.className = 'alert alert-error';
                alert.textContent = "Server error. Please try again.";
                alert.style.display = 'block';
            });
        }

        fileInput.onchange = function () {
            const [file] = this.files;
            if (!file) {
                return;
            }

            avatarPreview.src = URL.createObjectURL(file);

            const photoData = new FormData();
            photoData.append('profile_pic', file);
            alert.className = 'alert alert-success';
            alert.textContent = "Uploading profile photo...";
            alert.style.display = 'block';

            submitProfileUpdate(photoData, "Profile photo updated successfully!");
        };

        profileForm.onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            const pass = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;
            
            if (pass !== "" && pass !== confirm) {
                alert.className = 'alert alert-error';
                alert.textContent = "Passwords do not match.";
                alert.style.display = 'block';
                return;
            }

            submitProfileUpdate(formData, "Profile updated successfully!");
        };
    </script>
</body>

</html>
