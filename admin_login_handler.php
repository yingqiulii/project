<?php
session_start();
require('connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validate credentials using a prepared statement
    $stmt = $db->prepare("SELECT user_id, password, role FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password']) && $user['role'] === 'admin') {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = 'admin';
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Invalid username or password";
        header("Location: admin_login.php");
        exit();
    }
}
?>
