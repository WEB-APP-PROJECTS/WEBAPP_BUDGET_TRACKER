<?php
session_start();
require 'connection.php';

// You must set this in your login logic
$user_id = $_SESSION['user_id'] ?? 1; // Fallback to user_id = 1 for testing

// Get budget per category
$budgetQuery = mysqli_query($conn, "SELECT c.name, b.amount_limit FROM budgets b JOIN categories c ON b.category_id = c.id WHERE b.user_id = $user_id");
$budgetData = [];
$budgetLabels = [];
while ($row = mysqli_fetch_assoc($budgetQuery)) {
    $budgetLabels[] = $row['name'];
    $budgetData[] = (float)$row['amount_limit'];
}

// Get expenses per category
$expensesQuery = mysqli_query($conn, "SELECT c.name, SUM(t.amount) as total FROM transactions t JOIN categories c ON t.category_id = c.id WHERE t.user_id = $user_id GROUP BY c.name");
$expenseData = [];
$expenseLabels = [];
$categoryTotals = [];
while ($row = mysqli_fetch_assoc($expensesQuery)) {
    $expenseLabels[] = $row['name'];
    $expenseData[] = (float)$row['total'];
    $categoryTotals[$row['name']] = (float)$row['total'];
}

// Calculate balances
$balanceData = [];
foreach ($budgetLabels as $index => $name) {
    $budget = $budgetData[$index];
    $spent = $categoryTotals[$name] ?? 0;
    $balanceData[] = $budget - $spent;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Budget Tracker | Home</title>
  <link rel="stylesheet" href="homepage.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

  <div class="header">
    <div class="left">Hello, <?php echo $_SESSION['username'] ?? 'Username'; ?>!</div>
    <div class="right">
      <a href="budgets.php"><button class="btn">View Budget</button></a>
      <a href="logout.php"><button class="btn">Logout</button></a>
    </div>
  </div>

  <h2>Welcome to Budget Tracker</h2>

  <div class="circle-layout">
    <div class="circle" id="budget-circle">
      <h3>Budget</h3>
      <canvas id="budgetChart"></canvas>
    </div>

    <div class="circle" id="expenses-circle">
      <h3>Expenses</h3>
      <canvas id="expensesChart"></canvas>
    </div>

    <div class="circle" id="balance-circle">
      <h3>Balance</h3>
      <canvas id="balanceChart"></canvas>
    </div>
  </div>

  <div class="create-budget-btn">
    <a href="budgets.php"><button class="btn">Create Budget</button></a>
  </div>

  <footer class="nav-bar">
    <a href="home.php" title="Home"><i class="fas fa-home"></i></a>
    <a href="add_transaction.php" title="Add Transaction"><i class="fas fa-plus-circle"></i></a>
    <a href="view_transactions.php" title="View Transactions"><i class="fas fa-receipt"></i></a>
    <a href="profile.php" title="User Profile"><i class="fas fa-user-circle"></i></a>
  </footer>

  <script>
    const budgetData = {
      labels: <?php echo json_encode($category_id); ?>,
      datasets: [{
        label: 'Budget',
        data: <?php echo json_encode($balance); ?>,
        backgroundColor: ['#9534db', '#ff6384', '#f3ea46', '#ffcd56', '#4bc0c0']
      }]
    };

    const expensesData = {
      labels: <?php echo json_encode($category_id); ?>,
      datasets: [{
        label: 'Expenses',
        data: <?php echo json_encode($amount); ?>,
        backgroundColor: ['#9534db', '#ff6384', '#f3ea46', '#ffcd56', '#4bc0c0']
      }]
    };

    const balanceData = {
      labels: <?php echo json_encode($budgetLabels); ?>,
      datasets: [{
        label: 'Balance',
        data: <?php echo json_encode($balanceData); ?>,
        backgroundColor: ['#9534db', '#ff6384', '#f3ea46', '#ffcd56', '#4bc0c0']
      }]
    };

    const config = (data) => ({
      type: 'pie',
      data: data,
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'bottom',
            labels: { color: '#fff' }
          }
        }
      }
    });

    new Chart(document.getElementById('budgetChart'), config(budgetData));
    new Chart(document.getElementById('expensesChart'), config(expensesData));
    new Chart(document.getElementById('balanceChart'), config(balanceData));
  </script>
</body>
</html>
