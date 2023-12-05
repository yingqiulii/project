<?php
session_start();

require('connect.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$comment_id = $_GET['comment_id'];

$stmt = $db->prepare("SELECT user_id, username, comment FROM user_comments WHERE comment_id = ?");
$stmt->execute([$comment_id]);
$comment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$comment) {
    header("Location: error.php");
    exit();
}
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
    <input type="hidden" name="comment_id" value="<?= $comment_id; ?>">
    
    <label for="username">Username:</label>
    <input type="text" name="username" value="<?= $comment['username']; ?>" readonly>

    <label for="comment">Comment:</label>
    <textarea name="comment"><?= $comment['comment']; ?></textarea>

    <button type="submit">Update Comment</button>
</form>

</body>
</html>
