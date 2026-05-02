<?php 
include '../../DB/database.php'; 

if (!isset($_GET['id'])) {
    header("Location: ../admin_dash.php");
    exit();
}

$id = intval($_GET['id']);
$result = $conn->query("SELECT * FROM users WHERE id = $id");
$user = $result->fetch_assoc();

if (!$user) {
    die("User not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - <?= htmlspecialchars($user['username']) ?></title>
    <link rel="stylesheet" href="../admin_styles/admin_style.css">
    <style>
        body { padding: 20px; }
        .edit-form-container {
            width: min(100%, 600px);
            margin: 24px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #800020; }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn-save { background: #800020; color: white; border: none; width: 100%; max-width: 220px; padding: 10px; cursor: pointer; font-size: 16px; margin-top: 10px; border-radius: 5px; }
        .btn-cancel { display: block; text-align: center; margin-top: 15px; color: #800020; text-decoration: none; }
        @media (max-width: 480px) {
            body { padding: 12px; }
            .edit-form-container { padding: 22px 16px; }
            .btn-save { max-width: none; }
        }
    </style>
</head>
<body>

<div class="edit-form-container">
    <h2>Update User Information</h2>
    <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
    
    <form action="process_admin.php?task=update" method="POST">
        <input type="hidden" name="id" value="<?= $user['id'] ?>">

        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
        </div>

        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>

        <div class="form-group">
            <label>Phone Number</label>
            <input type="text" name="phone_num" value="<?= htmlspecialchars($user['phone_num']) ?>">
        </div>

        <div class="form-group">
            <label>Role</label>
            <select name="role">
                <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>User</option>
            </select>
        </div>

        <button type="submit" class="btn-save">Save Changes</button>
        <a href="../admin_dash.php" class="btn-cancel">Cancel</a>
    </form>
</div>

</body>
</html>
