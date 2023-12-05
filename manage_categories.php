<?php
require('connect.php');


function getAllCategories($db) {
    $stmt = $db->query("SELECT * FROM categories");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function addCategory($db, $categoryName) {
    $stmt = $db->prepare("INSERT INTO categories (category_name) VALUES (?)");
    $stmt->execute([$categoryName]);
}


function updateCategory($db, $categoryId, $categoryName) {
    $stmt = $db->prepare("UPDATE categories SET category_name = ? WHERE category_id = ?");
    $stmt->execute([$categoryName, $categoryId]);
}


function deleteCategory($db, $categoryId) {
    $stmt = $db->prepare("DELETE FROM categories WHERE category_id = ?");
    $stmt->execute([$categoryId]);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['add_category'])) {
        $newCategoryName = $_POST['new_category'];
        addCategory($db, $newCategoryName);
    } elseif (isset($_POST['edit_category'])) {
        $editCategoryId = $_POST['edit_category'];
        $editedCategoryName = $_POST['edited_category'];
        updateCategory($db, $editCategoryId, $editedCategoryName);
    } elseif (isset($_POST['delete_category'])) {
        $deleteCategoryId = $_POST['delete_category'];
        deleteCategory($db, $deleteCategoryId);
    }
}


$categories = getAllCategories($db);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Categories</title>
</head>
<body>
    <h1>Manage Categories</h1>

    <h2>Add New Category</h2>
    <form method="post">
        <label for="new_category">Category Name:</label>
        <input type="text" id="new_category" name="new_category" required>
        <button type="submit" name="add_category">Add Category</button>
    </form>

    <h2>Existing Categories</h2>
    <ul>
        <?php foreach ($categories as $category): ?>
            <li>
                <?php echo $category['category_name']; ?>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="edit_category" value="<?php echo $category['category_id']; ?>">
                    <input type="text" name="edited_category" placeholder="Edit category name" required>
                    <button type="submit">Edit</button>
                </form>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="delete_category" value="<?php echo $category['category_id']; ?>">
                    <button type="submit">Delete</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
