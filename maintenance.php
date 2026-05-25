<?php
session_start();
include 'db.php';

$profilePic = "assets/profileIcon.png"; // default
if (isset($_SESSION['username'])) {
    $u = mysqli_fetch_assoc(mysqli_query($conn, "SELECT profile_picture FROM customers WHERE username='" . $_SESSION['username'] . "'"));
    if (!empty($u['profile_picture']) && file_exists("uploads/profiles/" . $u['profile_picture'])) {
        $profilePic = "uploads/profiles/" . $u['profile_picture'];
    }
}

if (isset($_POST['submitMaintenance'])) {
    $first_name  = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name   = mysqli_real_escape_string($conn, $_POST['last_name']);
    $contact     = mysqli_real_escape_string($conn, $_POST['contact']);
    $date        = mysqli_real_escape_string($conn, $_POST['date']);
    $branch      = mysqli_real_escape_string($conn, $_POST['branch']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    $sql = "INSERT INTO maintenance_schedules (first_name, last_name, contact, date, branch, description)
            VALUES ('$first_name', '$last_name', '$contact', '$date', '$branch', '$description')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Maintenance schedule submitted successfully!'); window.location.href='maintenance.php';</script>";
    } else {
        echo "<script>alert('Error submitting. Please try again.');</script>";
    }
}

$checkoutUser = null;
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $sqlUser = "SELECT * FROM customers WHERE username='$username'";
    $resultUser = mysqli_query($conn, $sqlUser);
    $checkoutUser = mysqli_fetch_assoc($resultUser);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="keywords" content="E-Bikez">
    <meta name="author" content="">
    <title>Bright E-Bike</title>
    <link rel="shortcut icon" href="assets/bright logo2 no bg.png">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

</head>

<body>
    <main>
        <header>
            <a href="#"><img src="assets/bright logo2.jpg" alt="Bright E-Bike Logo"></a>
            <button class="hamburger" id="hamburger">&#9776;</button>
            <nav class="navbar">
                <div id="navLinks" class="navLinks">
                    <ul>
                        <li><a href="index.php">HOME</a></li>
                        <li><a href="e-bikes.php">E-BIKES</a></li>
                        <li><a href="services.php">SERVICES</a></li>
                        <li><a href="aboutUs.php">ABOUT US</a></li>
                        <li><a href="reviews.php">REVIEWS</a></li>
                    </ul>
            </nav>
            <div class="Icons">
                <div class="searchBar">
                    <img src="assets/searchIcon.png" alt="Search Icon">
                    <input type="text" id="searchInput" placeholder="Search...">
                    <div class="searchResults" id="searchResults"></div>
                </div>

                <div class="shoppingCart" id="cartBtn">
                    <img src="assets/shoppingCartIcon.png" alt="Shopping Cart Icon">
                    <span class="cartCount" id="cartCount">0</span>
                </div>

                <div class="profileIcon" id="profileBtn">
                    <img src="<?php echo $profilePic; ?>" alt="Profile Icon"
                        style="width:40px; height:40px; border-radius:50%; object-fit:cover;">
                </div>
            </div>
            <div class="cartOverlay" id="cartOverlay"></div>

            <div class="cartWindow" id="cartWindow">
                <div class="cartTitle">
                    <h3>Your Cart</h3>
                    <span class="cartCloseBtn" id="cartCloseBtn">&times;</span>
                </div>
                <div class="cartContent" id="cartContent">

                </div>

                <div class="cartTotalCheckout">
                    <div class="total"></div>
                    <p>Total: </p>
                    <span>₱0.00</span>
                </div>
                <button class="checkOutBtn" onclick="goToCheckout()">Proceed To Checkout</button>
            </div>

            <div class="profileOverlay" id="profileOverlay"></div>
            <div class="profileWindow" id="profileWindow">
                <div class="profileTitle">
                    <h3>Account</h3>
                    <span class="profileCloseBtn" id="profileCloseBtn">&times;</span>
                </div>

                <div class="profileContent">
                    <?php if (isset($_SESSION['username'])): ?>
                        <p class="welcomeText">
                            Welcome, <?php echo $_SESSION['username']; ?>!
                        </p>
                        <a href="accountDetails.php" class="loginBtn">
                            Account Details
                        </a>
                        <a href="customerLogout.php" class="signupBtn">
                            Logout
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="loginBtn">
                            Login
                        </a>
                        <a href="signup.php" class="signupBtn">
                            Sign up
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <div class="checkoutModalOverlay" id="checkoutModalOverlay"></div>
        <div class="checkoutModal" id="checkoutModal">
            <div class="checkoutModalInner">
                <div class="checkoutModalHeader">
                    <h2>Checkout</h2>
                    <span class="checkoutModalClose" id="checkoutModalClose">&times;</span>
                </div>

                <div class="checkoutModalBody">

                    <div class="checkoutModalLeft">
                        <?php if ($checkoutUser): ?>
                            <div class="checkoutSection userInfo">
                                <h3>Customer Information</h3>
                                <p><strong>Full Name:</strong>
                                    <span id="checkoutFullName"><?php echo htmlspecialchars($checkoutUser['full_name'] ?? ''); ?></span>
                                </p>
                                <p><strong>Contact No.:</strong>
                                    <span id="checkoutContact"><?php echo htmlspecialchars($checkoutUser['contact_no'] ?? ''); ?></span>
                                </p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($checkoutUser['email'] ?? ''); ?></p>
                                <p><strong>Address:</strong>
                                    <span id="checkoutAddress"><?php echo htmlspecialchars($checkoutUser['address'] ?? ''); ?></span>
                                </p>
                            </div>
                        <?php else: ?>
                            <div class="checkoutSection userInfo">
                                <p>Please <a href="login.php">log in</a> to continue.</p>
                            </div>
                        <?php endif; ?>

                        <div class="checkoutSection paymentMethod">
                            <h3>Payment Method</h3>
                            <select id="paymentSelect" class="paymentSelect">
                                <option value="cod">Cash on Delivery</option>
                                <option value="gcash">GCash</option>
                            </select>

                            <!-- GCASH PANEL -->
                            <div class="gcashPanel" id="gcashPanel">
                                <div class="gcashTitle">
                                    GCash Payment Details
                                </div>
                                <div class="gcashRefBox">
                                    Send payment to:<strong>+63 970 810 1973</strong><br>
                                    Account Name: <strong>Bright E-Bikes</strong>
                                </div>
                                <label for="gcashNumber">Your GCash Number</label>
                                <input
                                    type="tel"
                                    id="gcashNumber"
                                    class="gcashNumberInput"
                                    placeholder="e.g. 09XX XXX XXXX"
                                    maxlength="11"
                                    pattern="[0-9]*"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                <button type="button" id="openGcashBtn" class="openGcashBtn">
                                    Open GCash App to Pay
                                </button>
                                <p class="gcashNote">Make sure to send the exact total amount. Screenshot of payment will be required upon delivery.</p>
                            </div>
                        </div>
                    </div>

                    <div class="checkoutModalRight">
                        <div class="checkoutSection orderSummary">
                            <h3>Order Summary</h3>
                            <div class="checkoutHeader">
                                <span>Product</span>
                                <span>Qty</span>
                                <span>Price</span>
                            </div>
                            <div id="checkoutModalItems"></div>
                        </div>

                        <div class="checkoutSection summary">
                            <p>Subtotal: <span id="checkoutSubtotal">₱0</span></p>
                            <p>Shipping Fee: <span id="checkoutShipping">₱50</span></p>
                            <p><strong>Total: <span id="checkoutTotal">₱0</span></strong></p>
                        </div>

                        <button id="placeOrderBtn">Place Order</button>
                    </div>
                </div>
            </div>
        </div>
        <section class="servicesBackground">
            <div class="repairContainer">
                <div class="repair">
                    <div class="repMain-Btn">
                        <button class="repMainBtnn" style="background-color: #fff; color: #6b0303;" onclick="window.location.href='repair.php'">REPAIR</button>
                        <button class="repMainBtn" style="background-color: #6b0303; color: #fff;">MAINTENANCE</button>
                    </div>
                    <div class="repairContent">
                        <div class="repairCard">
                            <p>Regular Tune-Ups</p>
                            <img class="repImg" src="assets/regular.png" alt="">
                            <p class="p2">Thorough inspections and adjustments to keep all bike components
                                running smoothly, including gears, brakes, and lubrication.</p>
                        </div>

                        <div class="repairCard">
                            <p>Drivetrain and Gear Maintenance</p>
                            <img class="repImg" src="assets/drivetrain.png" alt="">
                            <p class="p2">Cleans, lubricates, and checks the chain, replaces oil if necessary,
                                and adjusts gears for smooth shifting and efficient performance.</p>
                        </div>

                        <div class="repairCard">
                            <p>Tire and Wheel Maintenance</p>
                            <img class="repImg" src="assets/tire.png" alt="">
                            <p class="p2">Inspects tire pressure, checks for wear, and ensures
                                wheels are balanced and true for a smooth ride.</p>
                        </div>
                    </div>
                    <div class="schedContainer">
                        <button class="schedBtn">Schedule a Maintenance</button>
                    </div>
                </div>
            </div>

            <div class="modalOverlay" id="modalOverlay"></div>

            <!-- Modal Container -->
            <div class="scheduleModal" id="scheduleModal">
                <span class="closeModal" id="closeModal">&times;</span>

                <h2>Customer's Information</h2>

                <form class="scheduleForm" method="POST">
                    <div class="formRow">
                        <input type="text" name="first_name" placeholder="First Name" required>
                        <input type="text" name="last_name" placeholder="Last Name" required>
                    </div>
                    <input type="text" name="contact" maxlength="11" pattern="[0-9]{11}" placeholder="Contact Number" required>
                    <div class="datePicker">
                        <input type="date" name="date" required>
                    </div>
                    <select name="branch" required>
                        <option value="">Select Branch</option>
                        <option>Valenzuela - Gen. De Leon</option>
                        <option>Valenzuela - Malanday</option>
                        <option>Bulacan - Obando</option>
                    </select>
                    <textarea name="description" placeholder="Describe the problem..." rows="4" required></textarea>
                    <button type="submit" name="submitMaintenance" class="submitBtn">Submit</button>
                </form>
            </div>

        </section>
    </main>
    <script src="script.js"></script>
</body>
<footer>
    <div class="footerContainer">
        <div class="footerSocials">
            <img src="assets/bright logo2 text.png">

            <div class="iconRow">
                <a href="https://web.facebook.com/profile.php?id=100081527322607" target="_blank">
                    <img src="assets/facebookIcon-removebg-preview.png" alt="FaceBook">
                </a>
                <a href="https://www.messenger.com/t/103672045608123" target="_blank">
                    <img src="assets/messengerIcon-removebg-preview.png" alt="Messenger">
                </a>
                <a href="mailto:brightelectricbike.malanday@gmail.com" target="_blank">
                    <img src="assets/mailIcon-removebg-preview.png" alt="Email">
                </a>
            </div>

        </div>
        <div class="contactInfo">
            <h3>CONTACT INFO</h3>
            <p>Address: 3549 M. Delos Reyes St. Gen. De Leon Valenzuela City,<br>
                69 M.H. Del Pilar road, Malanday, Valenzuela City,<br>
                Lot 126B along provincial road Brgy. Paco, Obando, Bulacan</p>
            <p>Contact No.: +63 970 810 1973</p>
            <p>Email: brightelectricbike.malanday@gmail.com</p>
        </div>

        <div class="shareThoughts">
            <h3>Share Your Thoughts With Us</h3>
            <p>If you have feedback or questions, kindly submit them through our <a href="https://forms.gle/HBkXMi3DqTymjnb66" target="_blank">Feedback
                    Form.</a></p>
            <div class="footer-links">
                <a href="privacyPolicy.php">PRIVACY POLICY</a>
                <a href="terms&Condition.php">TERMS & CONDITION</a>
            </div>
        </div>
    </div>
</footer>

</html>