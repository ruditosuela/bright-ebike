<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include 'db.php';

// ── SUMMARY CARD COUNTS

$totalProducts  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM product"))['cnt'];
$totalOrders    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM orders"))['cnt'];
$totalCustomers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM customers"))['cnt'];
$totalSales     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(total_amount), 0) AS total FROM orders WHERE status = 'Completed'"))['total'];

// ── RECENT ORDERS (latest 5)

$recentOrdersResult = mysqli_query($conn, "
    SELECT
        orders.order_id,
        orders.total_amount,
        orders.status,
        orders.created_at,
        customers.full_name
    FROM orders
    INNER JOIN customers ON orders.user_id = customers.user_id
    ORDER BY orders.created_at DESC
    LIMIT 5
");

// ── RECENT PRODUCTS (latest 5)

$recentProductsResult = mysqli_query($conn, "
    SELECT p.product_id, p.product_name, p.category, p.price, s.quantity
    FROM product p
    LEFT JOIN stocks s ON p.product_id = s.product_id
    ORDER BY p.product_id DESC
    LIMIT 5
");

// ── RECENT CUSTOMERS (latest 5)

$recentCustomersResult = mysqli_query($conn, "
    SELECT user_id, full_name, email, contact_no, address
    FROM customers
    ORDER BY created_at DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Dashboard</title>
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
    <!-- SIDEBAR -->
    <div class="sidebar" id="sidebar">
        <h2>Admin Dashboard</h2>
        <div class="Icon">
            <div class="profileIcon" id="profileBtn">
                <img src="assets/bright logo2.jpg" alt="Profile Icon">
                <p></p>
            </div>
        </div>
        <ul>
            <li><a href="adminDashboard.php" class="active">Home</a></li>
            <li><a href="adminProducts.php">Products</a></li>
            <li><a href="adminOrders.php">Orders</a></li>
            <li><a href="adminCustomers.php">Customers</a></li>
            <li><a href="adminReports.php">Reports</a></li>
            <li><a href="#" onclick="openLogoutModal()">Logout</a></li>
        </ul>
    </div>

    <div class="notif-wrapper" id="notifWrapper">
        <button class="notif-bell-btn" id="notifBellBtn" onclick="toggleNotifDropdown()" aria-label="Notifications">
            <img src="assets/notification.png" alt="Notifications" style="width:26px;height:26px;object-fit:contain;">
            <span class="notif-badge" id="notifBadge">0</span>
        </button>

        <div class="notif-dropdown" id="notifDropdown">
            <div class="notif-header">
                <h4>Notifications</h4>
                <button class="notif-mark-all" onclick="markAllRead()">Mark all as read</button>
            </div>

            <div class="notif-tabs">
                <button class="notif-tab active" onclick="filterNotif(this,'all')">All</button>
                <button class="notif-tab" onclick="filterNotif(this,'order')">Orders</button>
                <button class="notif-tab" onclick="filterNotif(this,'repair')">Repair</button>
                <button class="notif-tab" onclick="filterNotif(this,'maintenance')">Maintenance</button>
            </div>

            <div class="notif-list" id="notifList">
                <div class="notif-empty"><span></span>Loading notifications…</div>
            </div>

            <div class="notif-footer">
                <a href="adminOrders.php">View all orders</a>
            </div>
        </div>
    </div>

    <div class="main-content">

        <!-- DASHBOARD HEADER -->
        <h1>Dashboard</h1>

        <!-- SUMMARY CARDS -->
        <div class="cards">
            <div class="card">
                <h3>Total Products</h3>
                <p><?php echo $totalProducts; ?></p>
            </div>

            <div class="card">
                <h3>Total Orders</h3>
                <p><?php echo $totalOrders; ?></p>
            </div>

            <div class="card">
                <h3>Total Customers</h3>
                <p><?php echo $totalCustomers; ?></p>
            </div>

            <div class="card">
                <h3>Total Sales</h3>
                <p>₱<?php echo number_format($totalSales, 2); ?></p>
            </div>
        </div>

        <!-- RECENT ORDERS -->
        <div class="header-row">
            <h2>Recent Orders</h2>
            <a href="adminOrders.php"><button>View All</button></a>
        </div>
        <div class="table-wrapper">
            <table>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
                <?php if (mysqli_num_rows($recentOrdersResult) > 0): ?>
                    <?php while ($order = mysqli_fetch_assoc($recentOrdersResult)): ?>
                        <tr data-status="<?php echo $order['status']; ?>">
                            <td>#<?php echo $order['order_id']; ?></td>
                            <td><?php echo htmlspecialchars($order['full_name'] ?? ''); ?></td>
                            <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($order['status'] ?? ''); ?></td>
                            <td><?php echo $order['created_at']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center;">No orders yet.</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>

        <!-- RECENT PRODUCTS -->
        <div class="header-row">
            <h2>Recent Products</h2>
            <a href="adminProducts.php"><button>View All</button></a>
        </div>
        <div class="table-wrapper">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                </tr>
                <?php if (mysqli_num_rows($recentProductsResult) > 0): ?>
                    <?php while ($product = mysqli_fetch_assoc($recentProductsResult)): ?>
                        <tr>
                            <td><?php echo $product['product_id']; ?></td>
                            <td><?php echo htmlspecialchars($product['product_name'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($product['category'] ?? ''); ?></td>
                            <td>₱<?php echo number_format($product['price'], 2); ?></td>
                            <td><?php echo $product['quantity']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center;">No products yet.</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
        <!-- RECENT CUSTOMERS -->
        <div class="header-row">
            <h2>Recent Customers</h2>
            <a href="adminCustomers.php"><button>View All</button></a>
        </div>
        <div class="table-wrapper">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Contact</th>
                    <th>Address</th>
                </tr>
                <?php if (mysqli_num_rows($recentCustomersResult) > 0): ?>
                    <?php while ($customer = mysqli_fetch_assoc($recentCustomersResult)): ?>
                        <tr>
                            <td>#<?php echo $customer['user_id']; ?></td>
                            <td><?php echo htmlspecialchars($customer['full_name'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($customer['email'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($customer['contact_no'] ?? '') ?: 'N/A'; ?></td>
                            <td><?php echo htmlspecialchars($customer['address'] ?? '') ?: 'N/A'; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center;">No customers yet.</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <!-- LOGOUT MODAL -->
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