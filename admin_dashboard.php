<?php
session_start();

require('connect.php'); // Include the file with your database connection

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Fetch user comments from the database
$stmt = $db->query("SELECT user_id, user_name, comment FROM comments");
$userComments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>

<h1>Welcome to the Admin Dashboard!</h1>

<!-- Display User Comments -->
<h2>User Comments:</h2>
<table border="1">
    <tr>
        <th>User ID</th>
        <th>Username</th>
        <th>Comment</th>
        <th>Action</th>
    </tr>
    <?php foreach ($userComments as $comment) : ?>
        <tr>
            <td><?= $comment['user_id'] ?></td>
            <td><?= $comment['user_name'] ?></td>
            <td><?= $comment['comment'] ?></td>
            <td><a href="edit_comment.php?user_id=<?= $comment['user_id'] ?>">Edit</a></td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
