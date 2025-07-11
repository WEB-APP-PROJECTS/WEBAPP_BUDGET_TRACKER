<?php
session_start();
require 'connection.php';

$conn = new mysqli("localhost", "root", "", "budgettracker");
if ($conn->connect_error) die("DB error");

$email    = $conn->real_escape_string($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
  header("Location: index.php?error=Please+enter+both+fields");
  exit;
}

$stmt = $conn->prepare(
  "SELECT id, full_name, avatar_url, password_hash, user_type 
     FROM users 
    WHERE email = ? 
    LIMIT 1"
);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 1) {
  $stmt->bind_result($id, $full_name, $avatar, $hash, $type);
  $stmt->fetch();

  if (password_verify($password, $hash)) {
    $_SESSION['user_id']    = $id;
    $_SESSION['name']       = $full_name; 
    $_SESSION['avatar_url'] = $avatar;
    $_SESSION['user_type']  = $type;

    header("Location: dashboard.php");
    exit;
  }
}

header("Location: index.php?error=Invalid+credentials");
exit;
