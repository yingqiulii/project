<?php
require('connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $category_id = $_POST['category'];

    // Insert new page into the database
    $stmt = $db->prepare("INSERT INTO pages (title, content, category_id) VALUES (?, ?, ?)");
    $stmt->execute([$title, $content, $category_id]);

    // Check if the insertion was successful before redirecting
    if ($stmt->rowCount() > 0) {
        header("Location: page_list.php");
        exit;
    } else {
        // Log or display an error message
        echo "Error creating the page.";
    }
}

?>
