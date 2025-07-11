<?php
require 'connection.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION['user_id'] ?? 1;

    $category_name = trim($_POST['category']);
    $amount        = $_POST['amount'];
    $description   = $_POST['description'];
    $date          = $_POST['date'];

    // Check if category exists
    $stmt = $conn->prepare("SELECT id FROM categories WHERE name = ? AND user_id = ?");
    $stmt->bind_param("si", $category_name, $user_id);
    $stmt->execute();
    $stmt->bind_result($category_id);

    if ($stmt->fetch()) {
        // Category found
        $stmt->close();
    } else {
        // Category not found, insert new
        $stmt->close();
        $type = 'expense';
        $stmt = $conn->prepare("INSERT INTO categories (type, name, user_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $type, $category_name, $user_id);
        $stmt->execute();
        $category_id = $stmt->insert_id;
        $stmt->close();
    }

    // Insert transaction
    $stmt = $conn->prepare("INSERT INTO transactions (user_id, category_id, amount, description, date)
                            VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iidss", $user_id, $category_id, $amount, $description, $date);

    if ($stmt->execute()) {
        $stmt->close();
        echo "<script>alert('Transaction saved successfully!'); window.location.href='Add_Transactions.php';</script>";
    } else {
        echo "Error saving transaction: " . $stmt->error;
    }

    $conn->close();
}
?>
