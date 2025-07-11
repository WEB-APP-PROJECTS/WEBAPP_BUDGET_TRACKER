<?php
// dashboard.php
session_start();
require 'connection.php';

if (empty($_SESSION['user_id'])) {
  header("Location: index.php?error=Please+login+first");
  exit;
}

// Set default avatar if none provided
$avatar = !empty($_SESSION['avatar_url']) ? $_SESSION['avatar_url'] : 'uploads/avatars/default.png';
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <header style="display:flex; align-items:center; padding:10px; background:#0ef; color:#1f293a;">
    <img src="<?= htmlspecialchars($avatar) ?>" 
         alt="Avatar" 
         style="width:40px;height:40px;border-radius:50%;margin-right:10px;">
    <div>
      <strong><?= htmlspecialchars($_SESSION['name']) ?></strong><br>
      <em><?= htmlspecialchars($_SESSION['user_type']) ?> user</em>
    </div>
    <a href="logout.php" style="margin-left:auto; color:#1f293a; font-weight:600; text-decoration:none;">Logout</a>
  </header>

  <main style="padding:20px;">
    <?php
    // Route by user_type
    switch ($_SESSION['user_type']) {
      case 'admin':
        include 'admin-dashboard.php';
        break;
      default:
        include 'user-dashboard.php';
        break;
    }
    ?>
  </main>
</body>
</html>
