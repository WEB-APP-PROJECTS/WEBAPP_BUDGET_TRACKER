<?php
session_start();
header('Content-Type: application/json');
require 'connection.php'; // mysqli $conn

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get JSON data
$data = json_decode(file_get_contents("php://input"), true);

$user_id = isset($data['id']) ? (int)$data['id'] : 0;
$new_username = trim($data['username'] ?? '');

if (!$user_id || !$new_username) {
    echo json_encode(['success' => false, 'message' => 'Missing user ID or username']);
    exit;
}

$new_username = strip_tags($new_username);

if (strlen($new_username) < 3) {
    echo json_encode(['success' => false, 'message' => 'Username too short']);
    exit;
}

$stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param('si', $new_username, $user_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Username updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Execute failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
