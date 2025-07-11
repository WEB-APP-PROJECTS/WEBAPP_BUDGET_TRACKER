<?php
session_start();
require 'connection.php'; // Must return a working $conn (PDO or MySQLi)

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?error=Please+log+in+first");
    exit;
}

$user_id = $_SESSION['user_id'];

// --- UPDATE transaction ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id'], $_POST['amount'], $_POST['description'])) {
    $id = $_POST['id'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("UPDATE transactions SET amount = ?, description = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("dsii", $amount, $description, $id, $user_id);
    $stmt->execute();
    $stmt->close();
}

// --- DELETE transaction ---
if (isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM transactions WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $del_id, $user_id);
    $stmt->execute();
    $stmt->close();
}

// --- Get categories for filter dropdown ---
$category_result = $conn->prepare("SELECT id, name FROM categories WHERE user_id = ?");
$category_result->bind_param("i", $user_id);
$category_result->execute();
$cat_result = $category_result->get_result();

// --- Filter logic ---
$filter_cat = isset($_GET['category']) ? (int)$_GET['category'] : 0;

$sql = "
    SELECT t.id, t.date, c.name AS category_name, t.amount, t.description
    FROM transactions t
    LEFT JOIN categories c ON t.category_id = c.id
    WHERE t.user_id = ?
";
$params = [$user_id];
$types = "i";

if ($filter_cat) {
    $sql .= " AND c.id = ?";
    $params[] = $filter_cat;
    $types .= "i";
}

$sql .= " ORDER BY t.date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Transactions</title>
    <link rel="stylesheet" href="view-transactions.css?v=2">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
          integrity="sha512-..."
          crossorigin="anonymous"
          referrerpolicy="no-referrer">
</head>
<body>
    <div class="wrapper">
        <h2 class="title">View Transactions</h2>

        <form method="GET" class="filters">
            <div class="filter-group">
                <label for="category">Category:</label>
                <select id="category" name="category">
                    <option value="0">All Categories</option>
                    <?php while ($row = $cat_result->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>" <?= ($row['id'] === $filter_cat) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($row['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" class="action-btn">Filter</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <form method="POST" action="">
                                <td><?= htmlspecialchars($row['date']) ?></td>
                                <td><?= htmlspecialchars($row['category_name']) ?></td>
                                <td>
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <input type="number" step="0.01" name="amount" value="<?= htmlspecialchars($row['amount']) ?>" required>
                                </td>
                                <td>
                                    <input type="text" name="description" value="<?= htmlspecialchars($row['description']) ?>" required>
                                </td>
                                <td>
                                    <button type="submit" class="action-btn save-btn">Save</button>
                                    <a href="?delete_id=<?= $row['id'] ?>&category=<?= $filter_cat ?>" onclick="return confirm('Delete this transaction?');">
                                        <button type="button" class="action-btn delete-btn">Delete</button>
                                    </a>
                                </td>
                            </form>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5">No transactions found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <footer class="nav-bar">
        <a href="homepage.php" title="Home"><i class="fas fa-home"></i></a>
        <a href="Add_Transactions.php" title="Add Transaction"><i class="fas fa-plus-circle"></i></a>
        <a href="view-transactions.php" title="View Transactions"><i class="fas fa-receipt"></i></a>
        <a href="profile.php" title="User Profile"><i class="fas fa-user-circle"></i></a>
    </footer>
</body>
</html>
