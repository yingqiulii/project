<?php
session_start();
require('connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['login'])) {
        // Login Logic
        $username = $_POST['username'];
        $password = $_POST['password'];

        $stmt = $db->prepare("SELECT user_id, username, password, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && password_verify($password, $result['password'])) {
            // Authentication successful
            $_SESSION['user_id'] = $result['user_id'];
            $_SESSION['username'] = $result['username'];
            $_SESSION['role'] = $result['role'];

            // Regenerate session ID for security
            session_regenerate_id();

            // Redirect based on user role
            if ($result['role'] == 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: user_dashboard.php");
            }
            exit();
        } else {
            // Authentication failed
            $error_message = "Invalid username or password. Please try again.";
        }
    } elseif (isset($_POST['register'])) {
        // Registration Logic
        $username = $_POST['username'];
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];
        $role = 'user';

        // Check if passwords match during registration
        if ($password !== $confirmPassword) {
            $error_message = "Passwords do not match. Please try again.";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user into the database
            $stmt = $db->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->execute([$username, $hashed_password, $role]);

            header("Location: login.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Register</title>
</head>
<body>
    <h2>Login or Register</h2>
    <?php
    if (isset($error_message)) {
        echo '<p style="color: red;">' . $error_message . '</p>';
    }
    ?>
    <form action="login.php" method="post">
        <label for="username">Username:</label>
        <input type="text" name="username" required><br>

        <label for="password">Password:</label>
        <input type="password" name="password" required><br>

        <!-- Only include confirm password for registration -->
        <label for="confirm_password">Confirm Password:</label>
        <input type="password" name="confirm_password"><br>

        <button type="submit" name="login">Login</button>
        <button type="submit" name="register">Register</button>
    </form>
</body>
</html>
