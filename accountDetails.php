<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// ── UPLOAD PROFILE PICTURE ────────────────────────────────
if (isset($_POST['uploadPicture'])) {
    $allowed  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $fileName = $_FILES['profile_picture']['name'];
    $fileTmp  = $_FILES['profile_picture']['tmp_name'];
    $fileSize = $_FILES['profile_picture']['size'];
    $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (!in_array($fileExt, $allowed)) {
        echo "<script>alert('Invalid file type. Only JPG, PNG, GIF, WEBP allowed.');</script>";
    } elseif ($fileSize > 5000000) {
        echo "<script>alert('File too large. Max 5MB.');</script>";
    } else {
        $newFileName = $username . '_' . time() . '.' . $fileExt;
        $destination = __DIR__ . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "profiles" . DIRECTORY_SEPARATOR . $newFileName;

        if (move_uploaded_file($fileTmp, $destination)) {
            mysqli_query($conn, "UPDATE customers SET profile_picture='$newFileName' WHERE username='$username'");
            echo "<script>alert('Profile picture updated!'); window.location.href='accountDetails.php';</script>";
        } else {
            echo "<script>alert('Upload failed. Please try again.');</script>";
        }
    }
}

// ── UPDATE PROFILE INFO ───────────────────────────────────
if (isset($_POST['updateProfile'])) {
    $fullName  = mysqli_real_escape_string($conn, $_POST['full_name']);
    $contactNo = mysqli_real_escape_string($conn, $_POST['contact_no']);
    $address   = mysqli_real_escape_string($conn, $_POST['address']);

    $updateSql = "UPDATE customers 
                  SET full_name='$fullName', contact_no='$contactNo', address='$address'
                  WHERE username='$username'";

    if (mysqli_query($conn, $updateSql)) {
        echo "<script>alert('Profile updated successfully!'); window.location.href='accountDetails.php';</script>";
    } else {
        echo "<script>alert('Update failed.');</script>";
    }
}

// ── GET USER DATA ─────────────────────────────────────────
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM customers WHERE username='$username'"));

// ── PROFILE PICTURE PATH ──────────────────────────────────
$picPath = __DIR__ . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "profiles" . DIRECTORY_SEPARATOR . $user['profile_picture'];

$profilePic = (!empty($user['profile_picture']) && file_exists($picPath))
    ? "uploads/profiles/" . $user['profile_picture']
    : "assets/profileIcon.png";

// ── FETCH PURCHASE HISTORY ────────────────────────────────
$userId = (int) $user['user_id'];

$ordersResult = mysqli_query($conn, "
    SELECT o.order_id, o.total_amount, o.payment_method, o.status, o.created_at
    FROM orders o
    WHERE o.user_id = $userId
    ORDER BY o.created_at DESC
");

$orders = [];
while ($row = mysqli_fetch_assoc($ordersResult)) {
    $oid = (int) $row['order_id'];
    $detailsResult = mysqli_query($conn, "
        SELECT od.quantity, od.price, p.product_name
        FROM order_details od
        JOIN product p ON p.product_id = od.product_id
        WHERE od.order_id = $oid
    ");

    $items = [];
    while ($item = mysqli_fetch_assoc($detailsResult)) {
        $items[] = $item;
    }

    $row['items'] = $items;
    $orders[]     = $row;
}

// ── STATUS BADGE HELPER ───────────────────────────────────
function statusBadge(string $status): string {
    $map = [
        'pending'    => 'pending',
        'processing' => 'processing',
        'shipped'    => 'shipped',
        'completed'  => 'completed',
        'cancelled'  => 'cancelled',
    ];
    $labelMap = [
        'pending'    => 'Pending',
        'processing' => 'Processing',
        'shipped'    => 'Shipped',
        'completed'  => 'Completed',
        'cancelled'  => 'Cancelled',
    ];
    $key   = strtolower($status);
    $cls   = $map[$key]      ?? 'pending';
    $label = $labelMap[$key] ?? ucfirst($key);
    return "<span class=\"statusBadge $cls\">$label</span>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Details</title>
    <link rel="shortcut icon" href="assets/bright logo2 no bg.png">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="accountPageWrapper">

        <div class="tabNav">
            <button class="tabBtn active" data-tab="profile">Account Details</button>
            <button class="tabBtn" data-tab="history">Purchase History</button>
        </div>
        <div class="tabPane active" id="tab-profile">
            <div class="accountContainer">
                <h1>Account Details</h1>
                <div class="profilePicSection">
                    <img
                        src="<?php echo $profilePic; ?>"
                        alt="Profile Picture"
                        class="profilePicPreview"
                        id="profilePicPreview">

                    <form method="POST" enctype="multipart/form-data" id="uploadForm">
                        <label for="profile_picture" class="changePicBtn">Change Photo</label>
                        <input
                            type="file"
                            name="profile_picture"
                            id="profile_picture"
                            accept="image/*"
                            style="display:none;"
                            onchange="previewAndUpload(this)">
                        <button type="submit" name="uploadPicture" id="uploadBtn" style="display:none;" class="saveBtn">Save Photo</button>
                    </form>
                </div>
                <form method="POST">
                    <div class="accountInfo">
                        <p>
                            <strong>Username:</strong>
                            <?php echo htmlspecialchars($user['username']); ?>
                        </p>
                        <p>
                            <strong>Email:</strong>
                            <?php echo htmlspecialchars($user['email']); ?>
                        </p>
                        <p class="editableField">
                            <strong>Full Name:</strong>
                            <input type="text" name="full_name"
                                value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>"
                                oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')">
                        </p>
                        <p class="editableField">
                            <strong>Contact No:</strong>
                            <input type="text" name="contact_no"
                                value="<?php echo htmlspecialchars($user['contact_no'] ?? ''); ?>"
                                maxlength="11" pattern="[0-9]{11}" inputmode="numeric"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,11);" required>
                        </p>
                        <p class="editableField">
                            <strong>Address:</strong>
                            <input type="text" name="address"
                                value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" required>
                        </p>
                    </div>
                    <button type="submit" name="updateProfile" class="saveBtn">Save Changes</button>
                    <a href="index.php" class="backBtn">Back to Home</a>
                </form>
            </div>
        </div>
        <div class="tabPane" id="tab-history">
            <div class="purchaseHistorySection">
                <h2>Purchase History</h2>

                <?php if (empty($orders)): ?>
                    <div class="noOrders">
                        <div class="noOrdersIcon">&#x1F6F5;</div>
                        <p>You haven't placed any orders yet.</p>
                    </div>
                <?php else: ?>

                    <?php
                    $totalOrders = count($orders);
                    foreach ($orders as $i => $order):
                        $orderNum  = $totalOrders - $i;
                        $statusKey = strtolower($order['status']);
                    ?>
                    <div class="orderCard" onclick="openOrderModal(<?php echo $i; ?>)">
                        <div class="orderCardHeader">
                            <span class="orderNum">Order <?php echo $orderNum; ?></span>
                            <span class="orderDate"><?php echo date('M d, Y  h:i A', strtotime($order['created_at'])); ?></span>
                            <?php echo statusBadge($order['status']); ?>
                        </div>
                        <div class="orderCardBody">
                            <table class="orderItemsTable">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Qty</th>
                                        <th>Unit Price</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order['items'] as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                        <td><?php echo (int)$item['quantity']; ?></td>
                                        <td>&#8369;<?php echo number_format($item['price'], 2); ?></td>
                                        <td>&#8369;<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="orderCardFooter">
                            <span class="orderTotal">
                                Total: &#8369;<?php echo number_format($order['total_amount'], 2); ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>

                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="modalOverlay" id="orderModal">
        <div class="modalBox">
            <button class="modalClose" onclick="closeOrderModal()">&times;</button>
            <h2>Order Details</h2>
            <div class="modalDetail" id="modalDetail"></div>
        </div>
    </div>

    <script>
        const ordersData    = <?php echo json_encode(array_values($orders)); ?>;
        const totalOrders   = <?php echo count($orders); ?>;
        const customerName  = <?php echo json_encode(htmlspecialchars($user['full_name'] ?? $user['username'])); ?>;
        const customerEmail = <?php echo json_encode(htmlspecialchars($user['email'])); ?>;

        function previewAndUpload(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    document.getElementById('profilePicPreview').src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
                document.getElementById('uploadBtn').style.display = 'block';
            }
        }
    </script>
    <script src="script.js"></script>

</body>
</html>