<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include 'db.php';

$admin = $_SESSION['admin'];

// ADD PRODUCT
if (isset($_POST['addProduct'])) {
    $name     = mysqli_real_escape_string($conn, $_POST['product_name']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $price    = $_POST['price'];
    $stock    = $_POST['stock'];

    $imageName = $_FILES['image']['name'];
    $imageTmp  = $_FILES['image']['tmp_name'];
    move_uploaded_file($imageTmp, "assets/" . $imageName);

    $productTable = "INSERT INTO product (product_name, price, category, image)
                     VALUES ('$name', '$price', '$category', '$imageName')";

    if (mysqli_query($conn, $productTable)) {
        $product_id = mysqli_insert_id($conn);

        $stocksTable = "INSERT INTO stocks (product_id, quantity)
                        VALUES ('$product_id', '$stock')";
        mysqli_query($conn, $stocksTable);

        // LOG
        $desc = "Added new product: $name (Category: $category, Price: ₱$price, Stock: $stock)";
        mysqli_query($conn, "INSERT INTO activity_log (admin_user, action, description)
                             VALUES ('$admin', 'Added Product', '$desc')");
    }
}

// UPDATE PRODUCT
if (isset($_POST['updateProduct'])) {
    $id       = $_POST['product_id'];
    $name     = mysqli_real_escape_string($conn, $_POST['product_name']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $price    = $_POST['price'];
    $stock    = $_POST['stock'];

    // GET OLD VALUES for comparison
    $oldRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM product WHERE product_id='$id'"));
    $oldStock = mysqli_fetch_assoc(mysqli_query($conn, "SELECT quantity FROM stocks WHERE product_id='$id'"));

    if (!empty($_FILES['image']['name'])) {
        $imageName = $_FILES['image']['name'];
        $imageTmp  = $_FILES['image']['tmp_name'];
        move_uploaded_file($imageTmp, "assets/" . $imageName);

        mysqli_query($conn, "UPDATE product 
            SET product_name='$name', category='$category', price='$price', image='$imageName'
            WHERE product_id='$id'");
    } else {
        mysqli_query($conn, "UPDATE product 
            SET product_name='$name', category='$category', price='$price'
            WHERE product_id='$id'");
    }

    mysqli_query($conn, "UPDATE stocks SET quantity='$stock' WHERE product_id='$id'");

    // Build specific log description showing what changed
    $changes = [];
    if ($oldRow['product_name'] != $name)     $changes[] = "Name: {$oldRow['product_name']} → $name";
    if ($oldRow['category'] != $category)     $changes[] = "Category: {$oldRow['category']} → $category";
    if ($oldRow['price'] != $price)           $changes[] = "Price: ₱{$oldRow['price']} → ₱$price";
    if ($oldStock['quantity'] != $stock)      $changes[] = "Stock: {$oldStock['quantity']} → $stock";

    $changeText = !empty($changes) ? implode(', ', $changes) : 'No changes detected';
    $desc = "Updated product ID #$id ($name): $changeText";

    mysqli_query($conn, "INSERT INTO activity_log (admin_user, action, description)
                         VALUES ('$admin', 'Updated Product', '$desc')");
}


// FETCH PRODUCTS
$query = "
SELECT p.product_id, p.product_name, p.category, p.price, p.image, s.quantity
FROM product p
LEFT JOIN stocks s ON p.product_id = s.product_id
";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Product Management</title>
    <link rel="stylesheet" href="admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>

<body>

    <button class="hamburger" id="hamburgerBtn" onclick="toggleSidebar()" aria-label="Toggle navigation">
        <span></span>
        <span></span>
        <span></span>
    </button>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <div class="sidebar" id="sidebar">
        <h2>Admin Dashboard</h2>
        <div class="Icon">
            <div class="profileIcon" id="profileBtn">
                <img src="assets/bright logo2.jpg" alt="Profile Icon">
                <p></p>
            </div>
        </div>
        <ul>
            <li><a href="adminDashboard.php">Home</a></li>
            <li><a href="adminProducts.php" class="active">Products</a></li>
            <li><a href="adminOrders.php">Orders</a></li>
            <li><a href="adminCustomers.php">Customers</a></li>
            <li><a href="adminReports.php">Reports</a></li>
            <li><a href="#" onclick="openLogoutModal()">Logout</a></li>
        </ul>
    </div>

    <div class="main-content">

        <div class="header-row">
            <h1>Product Management</h1>
            <button type="button" onclick="openModal()">Add New Product</button>
        </div>
        <div class="table-wrapper">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Edit</th>
                </tr>

                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?= $row['product_id'] ?></td>
                        <td><?= $row['product_name'] ?></td>
                        <td><?= $row['category'] ?></td>
                        <td>₱<?= number_format($row['price'], 2) ?></td>
                        <td><?= $row['quantity'] ?></td>

                        <td>
                            <button class="edit-btn"
                                onclick="editProduct(
                            '<?= $row['product_id'] ?>',
                            '<?= $row['product_name'] ?>',
                            '<?= $row['category'] ?>',
                            '<?= $row['price'] ?>',
                            '<?= $row['quantity'] ?>'
                        )">
                                Edit
                            </button>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </div>

    <div id="productModal" class="modal">
        <div class="modal-content">

            <span class="close" onclick="closeModal()">&times;</span>

            <h2>Add New Product</h2>

            <form method="POST" enctype="multipart/form-data">

                <input type="hidden" name="product_id" id="product_id">

                <label>Product Name:</label>
                <input type="text" name="product_name" id="product_name">

                <label>Product Image:</label>
                <input type="file" name="image" accept="image/*" required>

                <label>Category:</label>
                <select name="category" id="category">
                    <option>Two Wheeler</option>
                    <option>Three Wheeler</option>
                    <option>Four Wheeler</option>
                </select>

                <label>Price:</label>
                <input type="number" name="price" id="price">

                <label>Stock:</label>
                <input type="number" name="stock" id="stock">

                <button type="submit" name="addProduct" class="addProductBtn">Add Product</button>
            </form>

        </div>
    </div>

    <div id="editProductModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>

            <h2>Edit Product</h2>

            <form method="POST" enctype="multipart/form-data">

                <input type="hidden" name="product_id" id="edit_product_id">

                <label>Product Name:</label>
                <input type="text" name="product_name" id="edit_product_name">

                <label>Category:</label>
                <select name="category" id="edit_category">
                    <option>Two Wheeler</option>
                    <option>Three Wheeler</option>
                    <option>Four Wheeler</option>
                </select>

                <label>Price:</label>
                <input type="number" name="price" id="edit_price">

                <label>Stock:</label>
                <input type="number" name="stock" id="edit_stock">

                <button type="submit" name="updateProduct" class="updateProductBtn">Update Product</button>
            </form>
        </div>
    </div>
    <div id="logoutModal" class="modal">
        <div class="modal-content">

            <span class="close" onclick="closeLogoutModal()">&times;</span>

            <h2>Confirm Logout</h2>
            <p>Are you sure you want to logout?</p>

            <div class="logout-actions">
                <button class="cancel-btn" onclick="closeLogoutModal()">Cancel</button>

                <a href="adminLogout.php">
                    <button class="confirm-btn">Yes</button>
                </a>
            </div>

        </div>
    </div>
    <script src="adminscript.js"></script>
</body>

</html>