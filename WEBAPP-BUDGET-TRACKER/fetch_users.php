<?php
session_start();
require 'connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit();
}

$result = $conn->query("SELECT id, username, email FROM users ORDER BY id");

$users = [];
while ($row = $result->fetch_assoc()) {
  $users[] = $row;
}

echo json_encode(['success' => true, 'users' => $users]);
?>
