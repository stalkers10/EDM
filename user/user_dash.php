<?php

include('../../DB/database.php'); 

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login_logout/Login.php");
    exit();
}
  
$username = htmlspecialchars($_SESSION['username'] ?? 'User');
$email    = htmlspecialchars($_SESSION['email']    ?? 'No email provided');
$role     = htmlspecialchars($_SESSION['role']     ?? 'User');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EDM | User Dashboard</title>
    
    <link rel="stylesheet" href="user_dash.css">
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>

    <div class="sidebar">
        <div class="brand-name">EDM PLATFORM</div>
        
        <div class="user-profile-brief">
            <span class="u-name"><?= $username ?></span>
            <span class="u-email"><?= $email ?></span>
        </div>

        <nav class="nav-menu">
            <a href="user_dash.php" class="nav-item active">
                <span class="material-icons-outlined">dashboard</span> Dashboard
            </a>
            <a href="Documents.php" class="nav-item">
                <span class="material-icons-outlined">account_circle</span> Documents
            </a>
            <a href="profile.php" class="nav-item">
                <span class="material-icons-outlined">account_circle</span> Profile
            </a>

        </nav>

        <a href="../login_logout/logout.php" class="nav-item logout-btn">
            <span class="material-icons-outlined">logout</span> Logout
        </a>
    </div>

    <main class="content">
        <div class="welcome-header">
            <h1>Welcome back, <?= $username ?> 👋</h1>
            <p>Here’s the current status of your account.</p>
        </div>

        <div class="management-card">
            <div class="card-header">
                <h2>Account Summary</h2>
            </div>
            <div class="card-body">
                <div class="stats-grid">
                    
                    <div class="stat-box">
                        <div class="stat-icon"><span class="material-icons-outlined">verified_user</span></div>
                        <div class="stat-details">
                            <span class="label">Account Status</span>
                            <div class="status-pill">Active</div>
                        </div>
                    </div>

                    <div class="stat-box">
                        <div class="stat-icon"><span class="material-icons-outlined">badge</span></div>
                        <div class="stat-details">
                            <span class="label">Your Role</span>
                            <span class="value"><?= $role ?></span>
                        </div>
                    </div>

                </div>

                <div class="info-callout">
                    <p>
                        <strong>Note:</strong> You are currently in the standard user dashboard. 
                        If you require elevated permissions or need to modify system settings, 
                        please contact your administrator.
                    </p>
                </div>
            </div>
        </div>
    </main>

</body>
</html>