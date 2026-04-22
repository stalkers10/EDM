<?php
include 'DB/database.php';

$hash = isset($_GET['h']) ? mysqli_real_escape_string($conn, $_GET['h']) : '';
$sql = "SELECT s.* FROM admin_storage s 
        JOIN public_shares p ON s.id = p.file_id 
        WHERE p.share_hash = '$hash' LIMIT 1";

$result = mysqli_query($conn, $sql);
$file = mysqli_fetch_assoc($result);

// If not found in admin_storage, check user_documents
if (!$file) {
    $sql = "SELECT s.* FROM user_documents s 
            JOIN public_shares p ON s.id = p.file_id 
            WHERE p.share_hash = '$hash' LIMIT 1";
    $result = mysqli_query($conn, $sql);
    $file = mysqli_fetch_assoc($result);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shared Resource | EDM</title>
   
    <style>
        .share-container { 
            display: flex; flex-direction: column; align-items: center; 
            justify-content: center; height: 100vh; background: #f4f4f4; 
        }
        .share-card { 
            background: #fff; padding: 40px; border-radius: 12px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.1); text-align: center;
            border-top: 5px solid #800020; /* Your Burgundy */
        }
        .btn-download {
            background: #800020; color: white; padding: 12px 25px;
            border-radius: 6px; text-decoration: none; display: inline-block;
            margin-top: 20px; font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="share-container">
        <div class="share-card">
            <span class="material-icons-outlined" style="font-size: 48px; color: #800020;">description</span>
            <?php if ($file): ?>
                <h2><?= htmlspecialchars($file['name']) ?></h2>
                <p>A university resource has been shared with you.</p>
                <a href="<?= $file['file_path'] ?>" class="btn-download" download>Download File</a>
            <?php else: ?>
                <h2>Link Invalid</h2>
                <p>This share link has expired or does not exist.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>