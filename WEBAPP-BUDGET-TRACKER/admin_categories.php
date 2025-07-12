<?php
session_start();
require 'connection.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Join transactions, users, and categories to get all needed info
$query = "
    SELECT t.id as transaction_id, u.username, c.name AS category_name, t.amount, t.description, t.date
    FROM transactions t
    JOIN users u ON t.user_id = u.id
    JOIN categories c ON t.category_id = c.id
    ORDER BY t.date DESC
";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Admin Categories & Transactions</title>
<link rel="stylesheet" href="admin_style.css" />
<style>
    table { border-collapse: collapse; width: 95%; margin: 20px auto; }
    th, td { border: 1px solid #ddd; padding: 8px; }
    th { background-color: #f2f2f2; }
</style>
</head>
<body>
<h2 style="text-align:center;">All User Transactions</h2>

<table>
    <thead>
        <tr>
            <th>Transaction ID</th>
            <th>Username</th>
            <th>Category</th>
            <th>Amount</th>
            <th>Description</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['transaction_id']) ?></td>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= htmlspecialchars($row['category_name']) ?></td>
            <td><?= htmlspecialchars($row['amount']) ?></td>
            <td><?= htmlspecialchars($row['description']) ?></td>
            <td><?= htmlspecialchars($row['date']) ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>
