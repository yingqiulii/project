<?php
session_start();

require('connect.php');

function getCategoryName($db, $categoryId) {
    $stmt = $db->prepare("SELECT category_name FROM categories WHERE category_id = ?");
    $stmt->execute([$categoryId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result ? $result['category_name'] : 'Uncategorized';
}

// 获取排序方式和列（如果已设置）
$sortColumn = isset($_GET['sort']) ? $_GET['sort'] : 'date';
$sortOrder = isset($_GET['order']) && strtolower($_GET['order']) == 'desc' ? 'DESC' : 'ASC';

// 查询数据库，按照指定的列和顺序排序
$stmt = $db->prepare("SELECT * FROM products ORDER BY $sortColumn $sortOrder, category_id");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
    <!-- Navigation bar -->
    <div class="navbar">
        <a href="index.php">Home</a>
        <a href="logout.php">Logout</a>
    </div>

    <h1>Product List</h1>

    <table>
        <tr>
            <!-- 添加排序链接 -->
            <th><a href="?sort=product_name&order=<?php echo $sortColumn == 'product_name' ? ($sortOrder == 'ASC' ? 'DESC' : 'ASC') : 'ASC'; ?>">Product Name <?php echo $sortColumn == 'product_name' ? ($sortOrder == 'ASC' ? '▲' : '▼') : ''; ?></a></th>
            <th><a href="?sort=description&order=<?php echo $sortColumn == 'description' ? ($sortOrder == 'ASC' ? 'DESC' : 'ASC') : 'ASC'; ?>">Description <?php echo $sortColumn == 'description' ? ($sortOrder == 'ASC' ? '▲' : '▼') : ''; ?></a></th>
            <th><a href="?sort=price&order=<?php echo $sortColumn == 'price' ? ($sortOrder == 'ASC' ? 'DESC' : 'ASC') : 'ASC'; ?>">Price <?php echo $sortColumn == 'price' ? ($sortOrder == 'ASC' ? '▲' : '▼') : ''; ?></a></th>
            <th><a href="?sort=date&order=<?php echo $sortColumn == 'date' ? ($sortOrder == 'ASC' ? 'DESC' : 'ASC') : 'ASC'; ?>">Date <?php echo $sortColumn == 'date' ? ($sortOrder == 'ASC' ? '▲' : '▼') : ''; ?></a></th>
            <th><a href="?sort=category_id&order=<?php echo $sortColumn == 'category_id' ? ($sortOrder == 'ASC' ? 'DESC' : 'ASC') : 'ASC'; ?>">Category <?php echo $sortColumn == 'category_id' ? ($sortOrder == 'ASC' ? '▲' : '▼') : ''; ?></a></th> 
            <th>Action</th>
        </tr>

    <?php
    if (isset($products) && $products) {
        foreach ($products as $row) {
            echo "<tr>";
            echo "<td><a href='product_details.php?id=" . $row["product_id"] . "'>" . $row["product_name"] . "</a></td>";
            echo "<td>" . $row["description"] . "</td>";
            echo "<td>$" . $row["price"] . "</td>";
            echo "<td>" . $row["date"] . "</td>";
            echo "<td>" . getCategoryName($db, $row["category_id"]) . "</td>";
            echo "<td>
                    <a href='edit_product.php?id=" . $row["product_id"] . "'>Edit</a>
                    <form method='post' onsubmit='return confirm(\"Are you sure you want to delete this product?\");'>
                        <input type='hidden' name='delete_product' value='" . $row["product_id"] . "'>
                        <button type='submit'>Delete</button>
                    </form>
                </td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='6'>No products found.</td></tr>";
    }
    ?>
    </table>

    <div class="add-product-link">
        <a href="edit_product.php">Add New Product</a>
    </div>
</body>
</html>
