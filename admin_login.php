<?php
// admin.php
require('connect.php');
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>

<h1>Admin Login</h1>

<form method="post" action="admin_login_handler.php">
    <label for="username">Username:</label>
    <input type="text" id="username" name="username" required>

    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required>

    <button type="submit">Login</button>
    
    <!-- Display error message -->
    <div class="error-message">
        <?php if (isset($_SESSION['error_message'])) {
            echo $_SESSION['error_message'];
            unset($_SESSION['error_message']); // clear error message
        } ?>
    </div>
</form>
</body>
</html>
