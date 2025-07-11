<?php
session_start();
require 'connection.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Queries
$users = mysqli_query($conn, "SELECT id, username, email FROM users WHERE role = 'user'");
if (!$users) {
    die("Query failed: " . mysqli_error($conn));
}

$categories = mysqli_query($conn, "SELECT * FROM categories");
if (!$categories) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>

<div class="admin-header">
  <div class="welcome-text">Welcome, <?= htmlspecialchars($_SESSION['username']) ?></div>
  <div class="admin-buttons">
    <a href="admin_categories.php" class="btn">Manage Categories</a>
    <a href="index.php" class="btn">Logout</a>
  </div>
</div>

<div class="section">
    <h2>Registered Users</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($users)): ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<div class="section">
    <h2>Categories</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($categories)): ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>
