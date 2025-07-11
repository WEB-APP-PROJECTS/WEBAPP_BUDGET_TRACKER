<?php
session_start();

$conn = new mysqli("localhost", "root", "", "budget_tracker");
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Database connection failed. Please try again later.");
}

// Input validation and sanitization
$username        = trim($_POST['username'] ?? '');
$email            = trim($_POST['email'] ?? '');
$password         = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validate required fields
if (!$username || !$email || !$password) {
    header("Location: index.php?error=" . urlencode("All fields are required"));
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: index.php?error=" . urlencode("Invalid email format"));
    exit;
}

// Password confirmation check
if ($password !== $confirm_password) {
    header("Location: index.php?error=" . urlencode("Passwords do not match"));
    exit;
}

// Password strength validation
if (strlen($password) < 8) {
    header("Location: index.php?error=" . urlencode("Password must be at least 8 characters long"));
    exit;
}

// Check for existing email
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    header("Location: index.php?error=" . urlencode("Registration failed. Please try again."));
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    header("Location: index.php?error=" . urlencode("Email already taken"));
    exit;
}
$stmt->close();

// Hash password securely
$hash = password_hash($password, PASSWORD_DEFAULT);
$role = 'user'; // Matches your database schema

// Insert new user with existing database structure
$stmt = $conn->prepare(
    "INSERT INTO users (username, email, password_hash, role)
     VALUES (?, ?, ?, ?)"
);

if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    header("Location: signup.php?error=" . urlencode("Prepare failed: " . $conn->error));
    exit;
}

$stmt->bind_param("ssss", $username, $email, $hash, $role);

error_log("Parameters bound successfully, executing...");

if ($stmt->execute()) {
    // Set session variables
    $_SESSION['user_id']    = $stmt->insert_id;
    $_SESSION['username']       = $username;  // Fixed: use $username instead of undefined $full_name
    $_SESSION['role']       = $role;

    // Regenerate session ID for security
    session_regenerate_id(true);

    $stmt->close();
    $conn->close();

    header("Location: homepage.php");
    exit;
} else {
    $error_msg = "Execute failed: " . $stmt->error . " | MySQL Error: " . $conn->error . " | Errno: " . $conn->errno;
    error_log($error_msg);
    $stmt->close();
    header("Location: signup.php?error=" . urlencode($error_msg));
    exit;
}
?>