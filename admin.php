<?php
require('connect.php');

// 获取当前页码
$current_page = isset($_GET['page']) ? $_GET['page'] : 1;

// 每页显示的产品数量
$products_per_page = 5;

// 计算偏移量
$offset = ($current_page - 1) * $products_per_page;

// 获取类别 ID（如果设置）
$category_id = isset($_GET['category']) ? $_GET['category'] : null;

// 构建查询语句
$query = "SELECT * FROM products";
if ($category_id) {
    $query .= " WHERE category_id = :category_id";
}

// 添加分页限制
$query .= " LIMIT :offset, :limit";

// 执行查询
$stmt = $db->prepare($query);
if ($category_id) {
    $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
}
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':limit', $products_per_page, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 获取总产品数（用于计算总页数）
$total_products = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_pages = ceil($total_products / $products_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
</head>
<body>
    <h1>Product List</h1>

    <!-- 分页导航 -->
    <div>
        <?php
        for ($i = 1; $i <= $total_pages; $i++) {
            echo "<a href='index.php?page=$i'>$i</a> ";
        }
        ?>
    </div>

    <!-- 类别导航 -->
    <div>
        <?php
        $categories = $db->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($categories as $category) {
            echo "<a href='index.php?category={$category['category_id']}'>{$category['category_name']}</a> ";
        }
        ?>
    </div>

    <!-- 产品列表 -->
    <table border="1">
        <tr>
            <th>Product Name</th>
            <th>Description</th>
            <th>Price</th>
            <th>Category</th>
        </tr>
        <?php foreach ($products as $product) : ?>
            <tr>
                <td><?= $product['product_name']; ?></td>
                <td><?= $product['description']; ?></td>
                <td><?= $product['price']; ?></td>
                <td><?= $product['category_id']; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
