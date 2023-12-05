<?php
require('connect.php');
session_start();

$product = [
    'product_name' => '',
    'description' => '',
    'price' => '',
    'category_id' => ''
];

$errors = [];
$product_id = isset($_GET['id']) ? $_GET['id'] : null;

if ($product_id !== null) {
    $stmt = $db->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        exit("Product not found.");
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product['product_name'] = $_POST['product_name'];
    $product['description'] = $_POST['description'];
    $product['price'] = $_POST['price'];
    $product['category_id'] = isset($_POST['category']) ? $_POST['category'] : '';

    if (empty($product['product_name']) || empty($product['description']) || empty($product['price']) || empty($product['category_id'])) {
        $errors[] = "Please complete all fields.";
    }

    if (!is_numeric($product['price'])) {
        $errors[] = "Price should be a number.";
    }

    if (empty($errors)) {
        if (isset($product_id)) {
            $stmt = $db->prepare("UPDATE products SET product_name = ?, description = ?, price = ?, category_id = ? WHERE product_id = ?");
            $stmt->execute([$product['product_name'], $product['description'], $product['price'], $product['category_id'], $product_id]);
        } else {
            $stmt = $db->prepare("INSERT INTO products (product_name, description, price, category_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$product['product_name'], $product['description'], $product['price'], $product['category_id']]);
            $product_id = $db->lastInsertId();
        }

        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            // Check if the uploaded file is an image
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $_FILES['image']['tmp_name']);

            if (strpos($mime, 'image') === 0) {
                $targetDir = "uploads/images/";
                $imageFilename = uniqid() . '.' . strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $targetFile = $targetDir . $imageFilename;

                // Move the image to the uploads folder
                move_uploaded_file($_FILES['image']['tmp_name'], $targetFile);

                // Display the resized image directly on the website
                echo "<img src='" . $targetFile . "' alt='Product Image' style='max-width: 300px;'><br>";

                // Resize the uploaded image
                resizeAndMoveImage($targetFile, $targetFile, 300, 300);

                // Update the database with the original image path
                $stmt = $db->prepare("UPDATE products SET image_path = ? WHERE product_id = ?");
                $stmt->execute([$targetFile, $product_id]);
            } else {
                // Invalid image file
                $errors[] = "Invalid image file type. Please upload a valid image.";
            }

            finfo_close($finfo);
        }

        if (isset($_POST['remove_image']) && $_POST['remove_image'] == 'on') {
            if (isset($product['image_path']) && !empty($product['image_path'])) {
                if (unlink($product['image_path'])) {
                    $stmt = $db->prepare("UPDATE products SET image_path = NULL WHERE product_id = ?");
                    $stmt->execute([$product_id]);
                } else {
                    $errors[] = "Error: Unable to delete the image.";
                }
            }
        }

        if (empty($errors)) {
            header("Location: index.php");
            exit;
        }

        if (isset($_POST['comment']) && !empty($_POST['comment'])) {
            $commentText = $_POST['comment'];
    
            // Insert the comment into the comments table
            $insertCommentStmt = $db->prepare("INSERT INTO comments (product_id, user_id, comment, date) VALUES (?, ?, ?, NOW())");
            $insertCommentStmt->execute([$product_id, $_SESSION['user_id'], $commentText]);
    
            // Reload the page to avoid form resubmission on page refresh
            header("Location: {$_SERVER['PHP_SELF']}?id=$product_id");
            exit;
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['remove_image']) && $_POST['remove_image'] == 'on') {
        if (isset($product['image_path']) && !empty($product['image_path'])) {
            unlink($product['image_path']);
        }

        $stmt = $db->prepare("UPDATE products SET image_path = NULL WHERE product_id = ?");
        $stmt->execute([$product_id]);
    }

    if (isset($_POST['delete_product'])) {
        $stmt = $db->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->execute([$product_id]);

        header("Location: index.php");
        exit;
    }

    if (isset($_POST['comment']) && !empty($_POST['comment'])) {
        $commentText = $_POST['comment'];

        // Insert the comment into the comments table
        $insertCommentStmt = $db->prepare("INSERT INTO comments (product_id, user_name, comment, date) VALUES (?, ?, ?, NOW())");
        $insertCommentStmt->execute([$product_id, $_SESSION['user_name'], $commentText]);

        // Reload the page to avoid form resubmission on page refresh
        header("Location: {$_SERVER['PHP_SELF']}?id=$product_id");
        exit;
    
        
    }
    
}

$stmt = $db->query("SELECT * FROM categories");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$commentsStmt = $db->prepare("SELECT * FROM comments WHERE product_id = ? ORDER BY date DESC");
$commentsStmt->execute([$product_id]);
$existingComments = $commentsStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo isset($product_id) ? 'Edit Product' : 'Add New Product'; ?></title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
    <h1><?php echo isset($product_id) ? 'Edit Product' : 'Add New Product'; ?></h1>

    <?php if (!empty($errors)) { ?>
        <div style="color: red;">
            <?php foreach ($errors as $error) { echo $error . "<br>"; } ?>
        </div>
    <?php } ?>

    <form method="post" enctype="multipart/form-data">
        <label for="product_name">Product Name:</label>
        <input type="text" id="product_name" name="product_name" value="<?php echo $product['product_name']; ?>">

        <label for="description">Description:</label>
        <textarea id="description" name="description"><?php echo $product['description']; ?></textarea>

        <label for="price">Price:</label>
        <input type="text" id="price" name="price" value="<?php echo $product['price']; ?>">

        <label for="category">Category:</label>
        <select id='category' name='category'>
            <?php
            foreach ($categories as $category) {
                echo "<option value='" . $category["category_id"] . "'";
                if ($product['category_id'] == $category["category_id"]) {
                    echo " selected";
                }
                echo ">" . $category["category_name"] . "</option>";
            }
            ?>
        </select>

        <label for="image">Image:</label>
        <input type="file" id="image" name="image">

        <?php
        if (isset($product['image_path']) && !empty($product['image_path'])) {
            echo "<img src='" . $product['image_path'] . "' alt='Product Image' style='max-width: 300px;'><br>";

            echo "<label for='remove_image'>Remove Image:</label>";
            echo "<input type='checkbox' id='remove_image' name='remove_image'>";
        }
        ?>
<label for="comment">Comment:</label>
<textarea id="comment" name="comment"></textarea>

<input type="submit" value="Submit Comment">

        <input type="submit" value="Save Product">
    </form>

</body>
</html>

<?php
function resizeAndMoveImage($sourcePath, $targetPath, $newWidth, $newHeight) {
    list($width, $height) = getimagesize($sourcePath);

    $aspectRatio = $width / $height;


    if ($newWidth / $newHeight > $aspectRatio) {
        $newWidth = $newHeight * $aspectRatio;
    } else {
        $newHeight = $newWidth / $aspectRatio;
    }

    $src = imagecreatefromjpeg($sourcePath);
    $dst = imagecreatetruecolor($newWidth, $newHeight);

    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    $success = imagejpeg($dst, $targetPath);

    imagedestroy($src);
    imagedestroy($dst);

    return $success;
}

?>
<?php
if (!empty($existingComments)) {
    echo "<h2>Comments</h2>";
    echo "<ul>";
    foreach ($existingComments as $comment) {
        echo "<li><strong>{$comment['user_name']}</strong> - {$comment['date']}<br>{$comment['comment']}</li>";
    }
    echo "</ul>";
}
?>
