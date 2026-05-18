<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include 'db.php';

// Fetch repair schedules
$repairResult = mysqli_query($conn, "SELECT * FROM repair_schedules ORDER BY submitted_at DESC");

// Fetch maintenance schedules
$maintenanceResult = mysqli_query($conn, "SELECT * FROM maintenance_schedules ORDER BY submitted_at DESC");

// Fetch recent sales (completed or all orders)
$salesResult = mysqli_query($conn, "
    SELECT 
        orders.order_id,
        orders.total_amount,
        orders.status,
        orders.created_at,
        customers.full_name
    FROM orders
    INNER JOIN customers ON orders.user_id = customers.user_id
    ORDER BY orders.created_at DESC
    LIMIT 20
");

// Fetch activity log
$logResult = mysqli_query($conn, "
    SELECT * FROM activity_log ORDER BY created_at DESC LIMIT 20
");

// Summary counts
$totalRepair      = mysqli_num_rows($repairResult);
$totalMaintenance = mysqli_num_rows($maintenanceResult);

$totalSalesRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_amount) AS total FROM orders WHERE status='Completed'"));
$totalSales    = $totalSalesRow['total'] ?? 0;

$totalOrdersRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM orders"));
$totalOrders    = $totalOrdersRow['total'];

$totalCustomersRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM customers"));
$totalCustomers    = $totalCustomersRow['total'];

$pendingOrdersRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM orders WHERE status='Pending'"));
$pendingOrders    = $pendingOrdersRow['total'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Reports</title>
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
            <li><a href="adminOrders.php">Orders</a></li>
            <li><a href="adminCustomers.php">Customers</a></li>
            <li><a href="adminReports.php" class="active">Reports</a></li>
            <li><a href="#" onclick="openLogoutModal()">Logout</a></li>
        </ul>
    </div>

    <div class="main-content">

        <div class="header-row">
            <h1>Reports</h1>
        </div>

        <!-- SUMMARY CARDS -->
        <div class="report-grid">
            <div class="card stat-card">
                <h3>Total Sales</h3>
                <p>₱<?php echo number_format($totalSales); ?></p>
            </div>
            <div class="card stat-card">
                <h3>Total Orders</h3>
                <p><?php echo $totalOrders; ?></p>
            </div>
            <div class="card stat-card">
                <h3>Total Customers</h3>
                <p><?php echo $totalCustomers; ?></p>
            </div>
            <div class="card stat-card">
                <h3>Pending Orders</h3>
                <p><?php echo $pendingOrders; ?></p>
            </div>
            <div class="card stat-card">
                <h3>Total Repair Requests</h3>
                <p><?php echo $totalRepair; ?></p>
            </div>
            <div class="card stat-card">
                <h3>Total Maintenance Requests</h3>
                <p><?php echo $totalMaintenance; ?></p>
            </div>
        </div>

        <!-- RECENT SALES REPORT -->
        <h2 class="reportLogs">Recent Sales Report</h2>
        <div class="table-wrapper">
            <table>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
                <?php if (mysqli_num_rows($salesResult) > 0):
                    while ($row = mysqli_fetch_assoc($salesResult)): ?>
                        <tr>
                            <td>#<?php echo $row['order_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['full_name']?? ''); ?></td>
                            <td>₱<?php echo number_format($row['total_amount']); ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                            <td><?php echo $row['created_at']?? ''; ?></td>
                        </tr>
                    <?php endwhile;
                else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center;">No orders yet.</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>

        <!-- REPAIR SCHEDULES -->
        <h2 class="reportLogs">Repair Schedules</h2>
        <div class="table-wrapper">
            <table>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Date</th>
                    <th>Branch</th>
                    <th>Problem</th>
                    <th>Submitted At</th>
                </tr>
                <?php
                mysqli_data_seek($repairResult, 0);
                if (mysqli_num_rows($repairResult) > 0):
                    while ($row = mysqli_fetch_assoc($repairResult)): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['contact'] ?? ''); ?></td>
                            <td><?php echo $row['date']; ?></td>
                            <td><?php echo htmlspecialchars($row['branch'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['problem'] ?? ''); ?></td>
                            <td><?php echo $row['submitted_at']; ?></td>
                        </tr>
                    <?php endwhile;
                else: ?>
                    <tr>
                        <td colspan="7" style="text-align:center;">No repair requests yet.</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>

        <!-- MAINTENANCE SCHEDULES -->
        <h2 class="reportLogs">Maintenance Schedules</h2>
        <div class="table-wrapper">
            <table>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Date</th>
                    <th>Branch</th>
                    <th>Description</th>
                    <th>Submitted At</th>
                </tr>
                <?php
                mysqli_data_seek($maintenanceResult, 0);
                if (mysqli_num_rows($maintenanceResult) > 0):
                    while ($row = mysqli_fetch_assoc($maintenanceResult)): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['contact'] ?? ''); ?></td>
                            <td><?php echo $row['date']; ?></td>
                            <td><?php echo htmlspecialchars($row['branch'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['description'] ?? ''); ?></td>
                            <td><?php echo $row['submitted_at']; ?></td>
                        </tr>
                    <?php endwhile;
                else: ?>
                    <tr>
                        <td colspan="7" style="text-align:center;">No maintenance requests yet.</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>

        <!-- ACTIVITY LOG -->
        <h2 class="reportLogs">System Activity Log</h2>
        <div class="table-wrapper">
            <table>
                <tr>
                    <th>Admin</th>
                    <th>Action</th>
                    <th>Description</th>
                    <th>Date & Time</th>
                </tr>
                <?php if (mysqli_num_rows($logResult) > 0):
                    while ($row = mysqli_fetch_assoc($logResult)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['admin_user']?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['action']?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['description']?? ''); ?></td>
                            <td><?php echo $row['created_at']; ?></td>
                        </tr>
                    <?php endwhile;
                else: ?>
                    <tr>
                        <td colspan="4" style="text-align:center;">No activity yet.</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>

    </div>

    <div id="logoutModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeLogoutModal()">&times;</span>
            <h2>Confirm Logout</h2>
            <p>Are you sure you want to logout?</p>
            <div class="logout-actions">
                <button onclick="closeLogoutModal()">Cancel</button>
                <a href="adminLogout.php"><button>Yes</button></a>
            </div>
        </div>
    </div>

    <script src="adminscript.js"></script>
</body>

</html>