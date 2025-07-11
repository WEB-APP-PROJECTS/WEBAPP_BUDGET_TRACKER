<?php
session_start();
require 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_budget'])) {
  $start = $_POST['start'] ?? null;
  $end = $_POST['end'] ?? null;
  $category = $_POST['category'] ?? null;
  $amount = $_POST['amount'] ?? null;

  if (!$start || !$end || !$category || !$amount) {
    die("Missing required fields.");
  }

  $user_id = $_SESSION['user_id'] ?? 1; // Replace with actual session logic
  $month = date('Y-m', strtotime($start)); // Group budgets by month

  // --- DB connection ---
  $conn = new mysqli("localhost", "root", "", "budget_tracker");
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  // --- Get or create category ---
  $stmt = $conn->prepare("SELECT id FROM categories WHERE name = ? AND user_id = ?");
  $stmt->bind_param("si", $category, $user_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($row = $result->fetch_assoc()) {
    $category_id = $row['id'];
  } else {
    $type = 'expense';
    $insert = $conn->prepare("INSERT INTO categories (type, name, user_id) VALUES (?, ?, ?)");
    $insert->bind_param("ssi", $type, $category, $user_id);
    $insert->execute();
    $category_id = $insert->insert_id;
    $insert->close();
  }
  $stmt->close();

  // --- Insert budget ---
  $stmt = $conn->prepare("INSERT INTO budgets (user_id, category_id, month, amount_limit, start, end) VALUES (?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("iisdss", $user_id, $category_id, $month, $amount, $start, $end);

  if ($stmt->execute()) {
    echo "<script>alert('Budget saved successfully.'); window.location.href='budget.html';</script>";
  } else {
    echo "Error saving budget: " . $stmt->error;
  }

  $stmt->close();
  $conn->close();
}
?>
