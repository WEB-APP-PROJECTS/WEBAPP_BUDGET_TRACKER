<?php
session_start();
$conn = new mysqli("localhost", "root", "", "budgettracker");
if ($conn->connect_error) die("DB connection failed");

$full_name        = trim($_POST['full_name'] ?? '');
$email            = trim($_POST['email'] ?? '');
$password         = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (!$full_name || !$email || !$password) {
    header("Location: signup.php?error=All+fields+except+avatar+are+required");
    exit;
}

if ($password !== $confirm_password) {
    header("Location: signup.php?error=Passwords+do+not+match");
    exit;
}

$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    header("Location: signup.php?error=Email+already+taken");
    exit;
}
$stmt->close();

$avatar_url = null;
if (!empty($_FILES['avatar']['name'])) {
    $allowed = ['image/png', 'image/jpeg', 'image/gif'];
    if (in_array($_FILES['avatar']['type'], $allowed)) {
        $ext      = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $newName  = uniqid('avatar_') . '.' . $ext;
        $destDir  = __DIR__ . '/uploads/avatars/';
        if (!is_dir($destDir)) mkdir($destDir, 0755, true);
        $destPath = $destDir . $newName;
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $destPath)) {
            $avatar_url = 'uploads/avatars/' . $newName;
        }
    }
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$role = 'normal'; 

$stmt = $conn->prepare(
    "INSERT INTO users (full_name, email, password_hash, avatar_url, user_type)
     VALUES (?, ?, ?, ?, ?)"
);
$stmt->bind_param("sssss", $full_name, $email, $hash, $avatar_url, $role);

if ($stmt->execute()) {
    $_SESSION['user_id']    = $stmt->insert_id;
    $_SESSION['name']       = $full_name;
    $_SESSION['avatar_url'] = $avatar_url;
    $_SESSION['user_type']  = $role;

    header("Location: dashboard.php");
    exit;
} else {
    header("Location: signup.php?error=Registration+failed");
    exit;
}
