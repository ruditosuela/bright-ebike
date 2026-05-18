<?php
session_start();
include 'db.php';

// GET ALL CUSTOMERS WITH TOTAL ORDER COUNT
$sql = "
    SELECT 
        customers.user_id,
        customers.full_name,
        customers.email,
        customers.contact_no,
        customers.address,
        COUNT(orders.order_id) AS total_orders
    FROM customers
    LEFT JOIN orders ON customers.user_id = orders.user_id
    GROUP BY customers.user_id
    ORDER BY customers.created_at DESC
";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Customers</title>
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
            <li><a href="adminCustomers.php" class="active">Customers</a></li>
            <li><a href="adminReports.php">Reports</a></li>
            <li><a href="#" onclick="openLogoutModal()">Logout</a></li>
        </ul>
    </div>

    <div class="main-content">

        <div class="header-row">
            <h1>Customer Management</h1>
        </div>
        <div class="table-wrapper">
            <table>
                <tr>
                    <th>Customer ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Contact</th>
                    <th>Address</th>
                    <th>Total Orders</th>
                    <th>View</th>
                </tr>

                <?php while ($customer = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td>#<?php echo $customer['user_id']; ?></td>
                        <td><?php echo htmlspecialchars($customer['full_name'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($customer['email'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($customer['contact_no'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($customer['address'] ?? ''); ?></td>
                        <td><?php echo $customer['total_orders']; ?></td>
                        <td>
                            <button
                                class="view-btn"
                                onclick='openViewCustomer(
                                <?php echo json_encode($customer["user_id"]); ?>,
                                <?php echo json_encode($customer["full_name"]); ?>,
                                <?php echo json_encode($customer["email"]); ?>,
                                <?php echo json_encode($customer["contact_no"]); ?>,
                                <?php echo json_encode($customer["address"]); ?>,
                                <?php echo json_encode($customer["total_orders"]); ?>
                            )'>
                                View
                            </button>
                        </td>

                    </tr>
                <?php } ?>

            </table>
        </div>
    </div>

    <!-- VIEW CUSTOMER MODAL -->
    <div id="customerModal" class="customermodal">
        <div class="customermodal-content">

            <div class="customermodalHeader">
                <h2>Customer Profile</h2>
                <span class="close" onclick="closeCustomerModal()">&times;</span>
            </div>

            <div class="customer-info">
                <p><strong>Customer ID:</strong> <span id="customerId"></span></p>
                <p><strong>Name:</strong> <span id="customerName"></span></p>
                <p><strong>Email:</strong> <span id="customerEmail"></span></p>
                <p><strong>Contact:</strong> <span id="customerContact"></span></p>
                <p><strong>Address:</strong> <span id="customerAddress"></span></p>
                <p><strong>Total Orders:</strong> <span id="customerOrders"></span></p>
            </div>

            <hr>

            <h3>Order History</h3>
    <div class="table-wrapper">
            <table>
                <tr>
                    <th>Order ID</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
                <tbody id="customerOrderHistory"></tbody>
            </table>
</div>
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