<?php
session_start();

require('connect.php'); 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

  
    $stmt = $db->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);

    header("Location: admin_dashboard.php");
    exit();
}
?>
