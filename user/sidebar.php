<div class="sidebar">
    <div class="brand-section">
        <div class="brand-top">
            <div class="brand-name">EDM PLATFORM</div>
            <button type="button" class="sidebar-close" data-sidebar-toggle aria-label="Close navigation menu">
                <span class="material-icons-outlined">close</span>
            </button>
        </div>
        <div class="user-profile-brief">
            <span class="u-name"><?= $username ?></span>
            <span class="u-email"><?= $email ?></span>
        </div>
    </div>
    
    <nav class="nav-menu">
        <a href="Documents.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'Documents.php' ? 'active' : '' ?>">
            <span class="material-icons-outlined">description</span>
            <span>Documents</span>
        </a>
        <a href="profile.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : '' ?>">
            <span class="material-icons-outlined">account_circle</span>
            <span>Profile</span>
        </a>
    </nav>

    <a href="../login_logout/logout.php" class="nav-item logout-btn">
        <span class="material-icons-outlined">logout</span>
        <span>Logout</span>
    </a>
</div>
