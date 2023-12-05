<?php
require('connect.php');

function getCategoryName($db, $category_id) {
    $stmt = $db->prepare("SELECT category_name FROM categories WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result ? $result['category_name'] : 'Uncategorized';
}

if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    $stmt = $db->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        exit("Product not found.");
    }
} else {
    header("Location: index.php");
    exit;
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['comment']) && isset($_POST['user_name'])) {
        $comment_text = $_POST['comment'];
        $user_name = $_POST['user_name'];

        // Insert the comment into the comments table
        $insertComment = $db->prepare("INSERT INTO comments (product_id, user_name, comment, date) VALUES (?, ?, ?, NOW())");
        $insertComment->execute([$product_id, $user_name, $comment_text]);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Product Details</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
    <h1>Product Details</h1>

    <div>
        <p><strong>Product Name:</strong> <?php echo $product['product_name']; ?></p>
        <p><strong>Description:</strong> <?php echo $product['description']; ?></p>
        <p><strong>Price:</strong> $<?php echo $product['price']; ?></p>
        <p><strong>Category:</strong> <?php echo getCategoryName($db, $product['category_id']); ?></p>

        <?php
        if (!empty($product['image_path'])) {
            echo "<div class='product-image'>";
            echo "<img src='" . $product['image_path'] . "' alt='Product Image' style='max-width: 100%; height: auto;'>";
            echo "</div>";
        }
        ?>

    </div>
    <h2>Comments</h2>

    <?php
    $stmt = $db->prepare("SELECT * FROM comments WHERE product_id = ? ORDER BY date DESC");
    $stmt->execute([$product_id]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($comments) {
        echo "<ul>";
        foreach ($comments as $comment) {
            echo "<li>";
            echo "<strong>{$comment['user_name']}</strong> ({$comment['date']}): {$comment['comment']}";
            echo "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No comments yet.</p>";
    }
    ?>

    <!-- Comment form -->
    <h3>Add a Comment</h3>
    <form method="post" action="product_details.php?id=<?php echo $product_id; ?>">
        <label for="user_name">Your Name:</label>
        <input type="text" id="user_name" name="user_name" required>
        <br>
        <label for="comment">Your Comment:</label>
        <textarea id="comment" name="comment" rows="4" cols="50" required></textarea>
        <br>
        <button type="submit">Submit Comment</button>
    </form>

    <a class="back-link" href="index.php">Back to Product List</a>
</body>
</html>
