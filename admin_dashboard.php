<?php
session_start();

require('connect.php'); // Include the file with your database connection

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Fetch user accounts from the database
$stmt = $db->query("SELECT user_id, username, email FROM users");
$userAccounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>

<h1>Welcome to the Admin Dashboard!</h1>

<!-- Display User Accounts -->
<h2>User Accounts:</h2>
<table border="1">
    <tr>
        <th>User ID</th>
        <th>Username</th>
        <th>Email</th>
        <th>Action</th>
    </tr>
    <?php foreach ($userAccounts as $user) : ?>
        <tr>
            <td><?= $user['user_id'] ?></td>
            <td><?= $user['username'] ?></td>
            <td><?= $user['email'] ?></td>
            <td>
                <a href="edit_user.php?user_id=<?= $user['user_id'] ?>">Edit</a>
                <a href="delete_user.php?user_id=<?= $user['user_id'] ?>">Delete</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
