<?php
session_start();
require 'connection.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Add Category
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_category'])) {
    $name = $_POST['name'];
    $type = $_POST['type'];
    $query = "INSERT INTO categories (name, type, user_id) VALUES (?, ?, 0)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $name, $type);
    $stmt->execute();
}

// Delete Category
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ? AND user_id = 0");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

$categories = mysqli_query($conn, "SELECT * FROM categories WHERE user_id = 0");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Categories</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>

<div class="admin-header">
    <div class="left">Hello,<?= $_SESSION['admin_name'] ?></div>
    <div class="admin-buttons">
        <a href="admin_dashboard.php"><button class="btn">Dashboard</button></a>
        <a href="admin_logout.php"><button class="btn">Logout</button></a>
    </div>
</div>

<h2>Manage Global Categories</h2>

<div class="create-budget-btn">
    <form method="POST" class="category-form">
        <input type="text" name="name" placeholder="Category Name" required>
        <select name="type" required>
            <option value="income">Income</option>
            <option value="expense">Expense</option>
        </select>
        <button type="submit" name="add_category" class="btn">Add Category</button>
    </form>
</div>

<div class="section">
    <h3>Current Categories</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Type</th>
            <th>Action</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($categories)): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['name'] ?></td>
            <td><?= ucfirst($row['type']) ?></td>
            <td><a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>
