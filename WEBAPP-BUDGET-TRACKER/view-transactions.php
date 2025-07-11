<?php
session_start();
require 'connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?error=Please+log+in+first");
    exit;
}

$user_id = $_SESSION['user_id'];


$conn = new mysqli("localhost", "root", "", "budgettracker");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id'], $_POST['amount'], $_POST['description'])) {
    $id          = $_POST['id'];
    $amount      = $_POST['amount'];
    $description = $_POST['description'];
    $stmt = $conn->prepare("
        UPDATE transactions
           SET amount = ?, description = ?
         WHERE id = ? AND user_id = ?
    ");
    $stmt->bind_param("dsii", $amount, $description, $id, $user_id);
    $stmt->execute();
}

if (isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    $stmt = $conn->prepare("
        DELETE FROM transactions
         WHERE id = ? AND user_id = ?
    ");
    $stmt->bind_param("ii", $del_id, $user_id);
    $stmt->execute();
}

$category_result = $conn->query("
    SELECT id, type
      FROM categories
     WHERE user_id = $user_id
");

$filter_cat = isset($_GET['category']) ? (int)$_GET['category'] : 0;

$sql    = "
    SELECT t.id, t.date, c.type AS category_type, t.amount, t.description
      FROM transactions t
 LEFT JOIN categories c ON t.category_id = c.id
     WHERE t.user_id = ?
";
$params = [ $user_id ];
$types  = "i";

if ($filter_cat) {
    $sql    .= " AND c.id = ?";
    $params[] = $filter_cat;
    $types    .= "i";
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"  
    integrity="sha512-…(use the integrity hash from CDN)…"
    crossorigin="anonymous"
    referrerpolicy="no-referrer">
    <link rel="stylesheet" href="view-transactions.css?v=2">
</head>
<body>
    <div class="wrapper">
        <button class="btn-primary">VIEW TRANSACTIONS</button>

        <form method="GET" class="filters">
            <div class="filter-group">
                <label for="category">Category:</label>
                <select id="category" name="category">
                    <option value="0">All Categories</option>
                    <?php while ($row = $category_result->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>"
                            <?= ($row['id'] === $filter_cat) ? 'selected' : '' ?>>
                            <?= ucfirst(htmlspecialchars($row['type'])) ?> #<?= $row['id'] ?>
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
                    <th>Type</th>
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
                                <td><?= htmlspecialchars($row['category_type']) ?></td>
                                <td>
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <input type="number"
                                           name="amount"
                                           step="0.01"
                                           value="<?= htmlspecialchars($row['amount']) ?>"
                                           required>
                                </td>
                                <td>
                                    <input type="text"
                                           name="description"
                                           value="<?= htmlspecialchars($row['description']) ?>"
                                           required>
                                </td>
                                <td>
                                    <button type="submit" class="action-btn save-btn">Save</button>
                                    <a href="?delete_id=<?= $row['id'] ?>&category=<?= $filter_cat ?>"
                                       onclick="return confirm('Delete this transaction?');">
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
    <a href="Add_Transactions.html" title="Add Transaction"><i class="fas fa-plus-circle"></i></a>
    <a href="view-transactions.php" title="View Transactions"><i class="fas fa-receipt"></i></a>
    <a href="profile.html" title="User Profile"><i class="fas fa-user-circle"></i></a>
</footer>

</body>
</html>
