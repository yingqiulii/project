<?php
session_start();

require('connect.php');

function getCategoryName($db, $categoryId) {
    $stmt = $db->prepare("SELECT category_name FROM categories WHERE category_id = ?");
    $stmt->execute([$categoryId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result ? $result['category_name'] : 'Uncategorized';
}

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// Use prepared statements consistently
$stmtCategories = $db->prepare("SELECT * FROM categories");
$stmtCategories->execute();
$categories = $stmtCategories->fetchAll(PDO::FETCH_ASSOC);

$searchKeyword = isset($_GET['search']) ? $_GET['search'] : '';
$current_category = isset($_GET['category']) ? $_GET['category'] : null;

$query = "SELECT p.*, c.comment
          FROM products p
          LEFT JOIN (
              SELECT product_id, comment
              FROM comments
              ORDER BY date DESC
              LIMIT 1
          ) c ON p.product_id = c.product_id
          WHERE (p.product_name LIKE ? OR p.description LIKE ?)";

$params = ["%$searchKeyword%", "%$searchKeyword%"];

if ($current_category) {
    $query .= " AND p.category_id = ?";
    $params[] = $current_category;
}

$productsPerPage = 5;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $productsPerPage;
$query .= " LIMIT $offset, $productsPerPage";

$stmt = $db->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch total products for pagination
$totalProducts = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalPages = ceil($totalProducts / $productsPerPage);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Product List</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
<!-- Navigation bar -->
<div class="navbar">
    <a href="index.php">Home</a>
    <?php if (isset($_SESSION['user_id'])) : ?>
        <a href="logout.php">Logout</a>
        <?php if ($_SESSION['role'] == 'admin') : ?>
            <div class="add-product-link">
                <a href="edit_product.php">Add New Product</a>
            </div>
        <?php endif; ?>
        <div class="edit-product-link">
            <a href="user_dashboard.php">Edit Product</a>
        </div>
    <?php else : ?>
        <a href="login.php">Login</a>
    <?php endif; ?>
</div>

<h1>Product List</h1>

<form method="get" action="index.php" id="searchForm">
    <label for="search">Search:</label>
    <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($searchKeyword); ?>">

    <label for="category">Category:</label>
    <select id="category" name="category" onchange="document.getElementById('searchForm').submit()">
        <option value="">All</option>
        <?php
        foreach ($categories as $category) {
            echo "<option value='{$category['category_id']}'";
            if ($category['category_id'] == $current_category) {
                echo " selected";
            }
            echo ">{$category['category_name']}</option>";
        }
        ?>
    </select>

    <button type="submit">Search</button>
</form>

<table>
    <tr>
        <th>Product Name</th>
        <th>Description</th>
        <th>Price</th>
        <th>Date</th>
        <th>Category</th>
        <th>Image</th>
        <th>Latest Comment</th>
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
            echo "<td>";
            if (!empty($row['image_path'])) {
                echo "<img src='" . $row['image_path'] . "' alt='Product Image' style='max-width: 100px;'>";
            }
            echo "</td>";
            echo "<td>";
            echo $row['comment'] ? $row['comment'] : 'No comments yet.';
            echo "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='7'>No products found.</td></tr>";
    }
    ?>
</table>

<?php if (isset($_SESSION['user_id'])) : ?>
    <div class="add-product-link">
        <a href="edit_product.php">Add New Product</a>
    </div>
<?php else : ?>
    <div class="login-link">
        <?php
        if (isset($_SESSION['user_id'])) {
            echo "<a href='logout.php'>Logout</a>";
        } else {
            echo "<a href='login.php'>Login to access additional features</a>";
        }
        ?>
    </div>
<?php endif; ?>
<?php
if ($current_category) {
    $totalProducts = $db->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
    $totalProducts->execute([$current_category]);
    $totalProducts = $totalProducts->fetchColumn();
}

if ($totalProducts > $productsPerPage) {
    echo "<div class='pagination'>";
    if ($page > 1) {
        echo "<a href='index.php?page=" . ($page - 1) . "&category=$current_category'>Previous</a>";
    }
    for ($i = 1; $i <= $totalPages; $i++) {
        echo "<a href='index.php?page=$i&category=$current_category'";
        if ($i == $page) {
            echo " class='active'";
        }
        echo ">$i</a>";
    }
    if ($page < $totalPages) {
        echo "<a href='index.php?page=" . ($page + 1) . "&category=$current_category'>Next</a>";
    }
    echo "</div>";
}
?>
<!-- Navigation bar -->
<div class="navbar">
    <a href="index.php">Home</a>
    <?php if (isset($_SESSION['user_id'])) : ?>
        <a href="logout.php">Logout</a>
        <?php if ($_SESSION['role'] == 'admin') : ?>
            <!-- Admin login link -->
            <a href="admin_login.php">Admin Login</a>
        <?php endif; ?>
    <?php else : ?>
        <a href="login.php">Login</a>
        <!-- Admin login link -->
        <a href="admin_login.php">Admin Login</a>
    <?php endif; ?>
</div>

</body>
</html>