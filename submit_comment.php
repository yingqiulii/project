<?php
session_start();
require('connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['comment'])) {
    $product_id = $_POST['product_id'];
    $user_id = $_SESSION['user_id']; // Assuming users are logged in

    $comment = $_POST['comment'];

    $stmt = $db->prepare("INSERT INTO comments (product_id, user_id, comment, date) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$product_id, $user_id, $comment]);

    // Redirect back to the product details page
    header("Location: product_details.php?id=$product_id");
    exit;
} else {
    // Invalid request, redirect to home or an error page
    header("Location: index.php");
    exit;
}