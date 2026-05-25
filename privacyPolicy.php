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
$checkoutUser = null;
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $sql = "SELECT * FROM customers WHERE username='$username'";
    $result = mysqli_query($conn, $sql);
    $checkoutUser = mysqli_fetch_assoc($result);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy</title>
    <link rel="shortcut icon" href="assets/bright logo2 no bg.png">
    <link rel="stylesheet" href="style.css">
</head>

<body>
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
                <span class="cartCount" id="cartCount">

                </span>
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
                            <option value="gcash">Gcash</option>
                        </select>

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
    <div class="privacy-container">
        <h1 class="privacy-title">Privacy Policy</h1>
        <p class="privacy-intro">
            Welcome to <strong>Bright Electric Bike Shop</strong>.
            Your privacy is important to us. This Privacy Policy explains
            how we collect, use, store, and protect your information
            when using our website and services.
        </p>
        <div class="privacy-section">
            <h2 class="privacy-heading">1. Information We Collect</h2>
            <p class="privacy-text">
                Bright Electric Bike Shop may collect the following
                information from users:
            </p>
            <h3 class="privacy-subheading">Personal Information</h3>
            <ul class="privacy-list">
                <li>Full Name</li>
                <li>Email Address</li>
                <li>Contact Number</li>
                <li>Home Address</li>
                <li>Billing and Shipping Information</li>
                <li>Account Login Details</li>
            </ul>
            <h3 class="privacy-subheading">Transaction Information</h3>
            <ul class="privacy-list">
                <li>Products Purchased</li>
                <li>Payment Details</li>
                <li>Order History</li>
            </ul>
            <h3 class="privacy-subheading">Technical Information</h3>
            <ul class="privacy-list">
                <li>IP Address</li>
                <li>Browser Type</li>
                <li>Device Information</li>
                <li>Cookies and Usage Data</li>
            </ul>
        </div>
        <div class="privacy-section">
            <h2 class="privacy-heading">2. How We Use Your Information</h2>
            <p class="privacy-text">
                Bright Electric Bike Shop uses collected information
                for the following purposes:
            </p>
            <ul class="privacy-list">
                <li>To process orders and payments</li>
                <li>To deliver purchased products</li>
                <li>To provide customer support</li>
                <li>To improve website functionality and user experience</li>
                <li>To send order updates and promotional messages</li>
                <li>To maintain website security and prevent fraudulent activities</li>
            </ul>
        </div>
        <div class="privacy-section">
            <h2 class="privacy-heading">3. Cookies and Tracking Technologies</h2>
            <p class="privacy-text">
                Bright Electric Bike Shop may use cookies and similar
                technologies to enhance user experience, remember
                customer preferences, and analyze website traffic.
            </p>
            <p class="privacy-text">
                Users may choose to disable cookies through their
                browser settings; however, some website features
                may not function properly.
            </p>
        </div>

        <div class="privacy-section">
            <h2 class="privacy-heading">4. Sharing of Information</h2>
            <p class="privacy-text">
                Bright Electric Bike Shop does not sell or rent
                personal information to third parties.
            </p>
            <p class="privacy-text">
                Information may only be shared with:
            </p>
            <ul class="privacy-list">
                <li>Payment service providers for transaction processing</li>
                <li>Delivery partners for shipping orders</li>
                <li>Government authorities when required by law</li>
            </ul>
        </div>
        <div class="privacy-section">
            <h2 class="privacy-heading">5. Data Protection and Security</h2>
            <p class="privacy-text">
                Bright Electric Bike Shop implements appropriate
                security measures to protect personal information
                against unauthorized access, alteration, disclosure,
                or destruction.
            </p>
            <p class="privacy-text">
                Despite our efforts, no online platform can guarantee
                complete security of data transmitted through the internet.
            </p>
        </div>
        <div class="privacy-section">
            <h2 class="privacy-heading">6. User Rights</h2>

            <p class="privacy-text">Users have the right to:</p>

            <ul class="privacy-list">
                <li>Access their personal information</li>
                <li>Request correction of inaccurate data</li>
                <li>Request deletion of personal information</li>
                <li>Withdraw consent for marketing communications</li>
            </ul>

            <p class="privacy-text">
                Requests may be submitted through our customer support channels.
            </p>
        </div>
        <div class="privacy-section">
            <h2 class="privacy-heading">7. Retention of Data</h2>

            <p class="privacy-text">
                Bright Electric Bike Shop retains customer information
                only for as long as necessary to fulfill business,
                legal, and security purposes.
            </p>
        </div>
        <div class="privacy-section">
            <h2 class="privacy-heading">8. Third-Party Links</h2>

            <p class="privacy-text">
                Our website may contain links to third-party websites.
                Bright Electric Bike Shop is not responsible for the
                privacy practices or content of external websites.
            </p>
        </div>
        <div class="privacy-section">
            <h2 class="privacy-heading">9. Changes to This Privacy Policy</h2>

            <p class="privacy-text">
                Bright Electric Bike Shop reserves the right to update
                or modify this Privacy Policy at any time. Changes
                will be posted on this page with the updated effective date.
            </p>
        </div>
        <div class="privacy-section">
            <h2 class="privacy-heading">10. Contact Information</h2>
            <p class="privacy-text">
                If you have questions or concerns regarding this
                Privacy Policy, you may contact:
            </p>
            <div class="privacy-contact-box">
                <p><strong>Bright Electric Bike Shop</strong></p>
                <p>Email: brightelectricbike.malanday@gmail.com</p>
                <p>Phone: +63 970 810 1973</p>
            </div>
        </div>
        <div class="privacy-section">
            <h2 class="privacy-heading">Consent</h2>
            <p class="privacy-text">
                By using the Bright Electric Bike Shop website,
                you acknowledge that you have read and understood
                this Privacy Policy and agree to its terms and conditions.
            </p>
        </div>
    </div>
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