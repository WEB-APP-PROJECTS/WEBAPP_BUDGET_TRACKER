<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'connection.php';

$conn = new mysqli("localhost", "root", "", "budget_tracker");
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Database connection failed. Please try again later.");
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    header("Location: index.php?error=Please+enter+both+fields");
    exit();
}

$stmt = $conn->prepare("SELECT id, username, password_hash, role FROM users WHERE email = ? LIMIT 1");
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    header("Location: index.php?error=Prepare+failed");
    exit();
}

$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows !== 1) {
    $stmt->close();
    $conn->close();
    header("Location: index.php?error=Invalid+email+or+password");
    exit();
}

$stmt->bind_result($id, $username, $hash, $role);
$stmt->fetch();

if (!password_verify($password, $hash)) {
    $stmt->close();
    $conn->close();
    header("Location: index.php?error=Invalid+email+or+password");
    exit();
}

// Debug output to confirm role and username before redirect
error_log("Login success: user_id=$id, username=$username, role=$role");

$_SESSION['user_id'] = $id;
$_SESSION['username'] = $username;
$_SESSION['role'] = $role;

if ($role === 'admin') {
    $_SESSION['admin_logged_in'] = true;   // <-- Added this for admin access
    $_SESSION['admin_name'] = $username;   // <-- Added this for admin welcome text
}

session_regenerate_id(true);

$stmt->close();
$conn->close();

if ($role === 'admin') {
    header("Location: admin_dashboard.php");
} elseif ($role === 'user') {
    header("Location: homepage.php");
} else {
    error_log("Unknown role: $role");
    header("Location: homepage.php");
}

exit();
?>
