<?php
session_start();

require('connect.php'); // Include the file with your database connection

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Initialize $comment with empty values
$comment = array('user_id' => '', 'username' => '', 'comment' => '');

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['comment_id'])) {
    $comment_id = $_GET['comment_id'];

    // Fetch the comment details
    $stmt = $db->prepare("SELECT user_id, username, comment FROM user_comments WHERE comment_id = ?");
    $stmt->execute([$comment_id]);
    $comment = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the comment exists
    if (!$comment) {
        // Handle the case where the comment with the specified ID is not found
        // For example, redirect the user to an error page or display an error message
        header("Location: error.php");
        exit();
    }
}

// Implement the HTML form for editing the comment
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Comment</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>

<h1>Edit Comment</h1>

<form method="post" action="update_comment.php">
    <input type="hidden" name="comment_id" value="<?= $comment['comment_id']; ?>">
    
    <label for="username">Username:</label>
    <input type="text" name="username" value="<?= $comment['username']; ?>" readonly>

    <label for="comment">Comment:</label>
    <textarea name="comment"><?= $comment['comment']; ?></textarea>

    <button type="submit">Update Comment</button>
</form>

</body>
</html>
