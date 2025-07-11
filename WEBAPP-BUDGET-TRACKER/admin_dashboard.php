<?php
session_start();
require 'connection.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Queries
$users = mysqli_query($conn, "SELECT id, fullname, email FROM users WHERE role = 'user'");
$categories = mysqli_query($conn, "SELECT * FROM categories");
// $feedback = mysqli_query($conn, "SELECT f.message, u.username FROM feedback f JOIN users u ON f.user_id = u.id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel = "stylesheet" href = "admin_style.css">
</head>
<body>

<div class="admin-header">
  <div class="welcome-text">Welcome, <?= $_SESSION['admin_name'] ?></div>
  <div class="admin-buttons">
    <a href="admin_categories.php" class="btn">Manage Categories</a>
    <a href="admin_logout.php" class="btn">Logout</a>
  </div>
</div>

<div class="section">
    <h2>Registered Users</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Email</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($users)): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['fullname'] ?></td>
                <td><?= $row['email'] ?></td>
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
                <td><?= $row['id'] ?></td>
                <td><?= $row['name'] ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<!-- <div class="section">
    <h2>User Feedback</h2>
    <table>
        <tr>
            <th>User</th>
            <th>Message</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($feedback)): ?>
            <tr>
                <td><?= $row['fullname'] ?></td>
                <td><?= $row['message'] ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</div> -->

</body>
</html>
