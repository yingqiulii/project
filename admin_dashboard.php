<?php
session_start();

require('connect.php'); // Include the file with your database connection

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Fetch user comments from the database
$stmt = $db->prepare("SELECT user_id, username, comment FROM user_comments");
$stmt->execute();
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>

<h1>Welcome to the Admin Dashboard!</h1>

<h2>User Comments</h2>

<table border="1">
    <tr>
        <th>User</th>
        <th>Comment</th>
        <th>Action</th>
    </tr>
    <?php foreach ($comments as $comment) : ?>
        <tr>
            <td><?= $comment['username']; ?></td>
            <td><?= $comment['comment']; ?></td>
            <td>
                <!-- Add edit and delete links or buttons -->
                <a href="edit_comment.php?comment_id=<?= $comment['comment_id']; ?>">Edit</a>
                <a href="delete_comment.php?comment_id=<?= $comment['comment_id']; ?>">Delete</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
