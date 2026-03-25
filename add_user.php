<?php include 'database.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New User</title>
    
    <style>
        .form-container { max-width: 500px; margin: 50px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; color: #800020; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        .submit-btn { background: #800020; color: white; border: none; padding: 12px; width: 100%; border-radius: 4px; cursor: pointer; font-size: 16px; }
    </style>
</head>
<body>

<?php
if (isset($_GET['error']) && $_GET['error'] == 'exists') {
    echo "<script>alert('Error: Username or Email already exists. Please choose different credentials.');</script>";
}
?>

<div class="form-container">
    <h2 style="color: #800020; margin-bottom: 20px;">Register New User</h2>
    <form action="process_admin.php?task=create" method="POST">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" placeholder="e.g. sobfred" required>
        </div>
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="e.g npm@gmail.com" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <div class="form-group">
            <label>Phone Number</label>
            <input type="text" name="phone_num">
        </div>
        <div class="form-group">
            <label>Role</label>
            <select name="role">
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <button type="submit" class="submit-btn">Create Account</button>
        <a href="admin_dash.php" style="display:block; text-align:center; margin-top:15px; color:#666; text-decoration:none;">Cancel</a>
    </form>
</div>

</body>
</html>