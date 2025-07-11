<?php
require 'connection.php'; // make sure this file connects to your database

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = 1; // replace with $_SESSION['user_id'] if using login system

    $category_name = trim($_POST['category']);
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    $date = $_POST['date'];

    try {
        // Check if category exists for the user
        $stmt = $conn->prepare("SELECT id FROM categories WHERE name = ? AND user_id = ?");
        $stmt->execute([$category_name, $user_id]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($category) {
            $category_id = $category['id'];
        } else {
            // Add new category
            $type = 'expense'; // You can customize this later
            $stmt = $conn->prepare("INSERT INTO categories (type, name, user_id) VALUES (?, ?, ?)");
            $stmt->execute([$type, $category_name, $user_id]);
            $category_id = $conn->lastInsertId();
        }

        // Insert transaction
        $stmt = $conn->prepare("INSERT INTO transactions (user_id, category_id, amount, description, date)
                                VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $category_id, $amount, $description, $date]);

        echo "<script>alert('Transaction saved successfully!'); window.location.href='add_transaction.html';</script>";

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
