<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_order'])) {
    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
    $status   = mysqli_real_escape_string($conn, $_POST['status']);
    $admin    = $_SESSION['admin'];

    // Update the order
    $sql = "UPDATE orders SET status='$status' WHERE order_id='$order_id'";
    mysqli_query($conn, $sql);

    // Log the activity
    $description = "Order #$order_id marked as $status";
    $logSql = "INSERT INTO activity_log (admin_user, action, description)
               VALUES ('$admin', 'Updated Order', '$description')";
    mysqli_query($conn, $logSql);

    header("Location: adminOrders.php");
    exit();
}

// GET ALL ORDERS
$sql = "
SELECT
    orders.order_id,
    orders.total_amount,
    orders.payment_method,
    orders.status,
    orders.created_at,
    customers.full_name,
    customers.email
FROM orders
INNER JOIN customers ON orders.user_id = customers.user_id
ORDER BY orders.created_at DESC
";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Order Management</title>
    <link rel="shortcut icon" href="assets/bright logo2 no bg.png">
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
            <li><a href="adminProducts.php">Products</a></li>
            <li><a href="adminOrders.php" class="active">Orders</a></li>
            <li><a href="adminCustomers.php">Customers</a></li>
            <li><a href="adminReports.php">Reports</a></li>
            <li><a href="#" onclick="openLogoutModal()">Logout</a></li>
        </ul>
    </div>
    <div class="main-content">

        <div class="header-row">
            <h1>Order Management</h1>
        </div>
        <div class="table-wrapper">
            <table>
                <tr>
                    <th>Order ID</th>
                    <th>Customer Name</th>
                    <th>Email</th>
                    <th>Total Price</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>View</th>
                    <th>Update</th>

                </tr>
                <?php while ($order = mysqli_fetch_assoc($result)) { ?>
                    <?php

                    $order_id = $order['order_id'];

                    ?>
                    <tr data-status="<?php echo $order['status']; ?>">
                        <td>#<?php echo $order['order_id']; ?></td>
                        <td><?php echo htmlspecialchars($order['full_name']?? ''); ?></td>
                        <td><?php echo htmlspecialchars($order['email']?? ''); ?></td>
                        <td>
                            ₱<?php echo number_format($order['total_amount']); ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($order['payment_method']?? ''); ?>
                        </td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                <select name="status" class="statusSelect">
                                    <option value="Pending" <?php if ($order['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                                    <option value="Processing" <?php if ($order['status'] == 'Processing') echo 'selected'; ?>>Processing</option>
                                    <option value="Shipped" <?php if ($order['status'] == 'Shipped') echo 'selected'; ?>>Shipped</option>
                                    <option value="Completed" <?php if ($order['status'] == 'Completed') echo 'selected'; ?>>Completed</option>
                                    <option value="Cancelled" <?php if ($order['status'] == 'Cancelled') echo 'selected'; ?>>Cancelled</option>
                                </select>
                        </td>
                        <td>
                            <?php echo $order['created_at']; ?>
                        </td>
                        <td>
                            <button
                                type="button"
                                class="view-btn"
                                onclick='openViewOrder(
                            <?php echo json_encode($order["order_id"]); ?>,
                            <?php echo json_encode($order["full_name"]); ?>,
                            <?php echo json_encode($order["email"]); ?>,
                            <?php echo json_encode($order["payment_method"]); ?>,
                            <?php echo json_encode($order["status"]); ?>,
                            <?php echo json_encode($order["created_at"]); ?>,
                            <?php echo json_encode($order["total_amount"]); ?>
                        )'>View
                            </button>
                        </td>
                        <td>
                            <form method="POST">
                                <input
                                    type="hidden"
                                    name="order_id"
                                    value="<?php echo $order['order_id']; ?>">
                                <input
                                    type="hidden"
                                    name="status"
                                    class="hiddenStatus">
                                <button
                                    type="submit"
                                    name="update_order"
                                    class="update-btn"
                                    onclick="setStatus(this)">Update
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>

                </tr>
            </table>
        </div>
    </div>

    <div id="viewOrderModal" class="modal">

        <div class="modal-content">

            <span class="close" id="closeViewModal">&times;</span>

            <h2>Order Details</h2>

            <div class="order-info" id="orderInfo"></div>

            <hr>

            <h3>Items</h3>
            <div class="table-wrapper">
                <table>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Price</th>
                    </tr>

                    <tbody id="orderItemsBody"></tbody>
                </table>
            </div>

            <hr>

            <div class="order-summary">
                <p id="subtotalLine"></p>
                <p id="shippingLine"></p>
                <h3 class="total" id="totalLine"></h3>
            </div>
        </div>

    </div>

    <div id="logoutModal" class="modal">
        <div class="modal-content">

            <span class="close" onclick="closeLogoutModal()">&times;</span>

            <h2>Confirm Logout</h2>
            <p>Are you sure you want to logout?</p>

            <div class="logout-actions">
                <button onclick="closeLogoutModal()">Cancel</button>

                <a href="adminLogout.php">
                    <button>Yes</button>
                </a>
            </div>

        </div>
    </div>

    <script src="adminscript.js"></script>
</body>

</html>