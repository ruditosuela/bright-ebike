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
    <title>Terms & Conditions</title>
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
                            <p><strong>Full Name:</strong> <?php echo htmlspecialchars($checkoutUser['full_name']); ?></p>
                            <p><strong>Contact No.:</strong> <?php echo htmlspecialchars($checkoutUser['contact_no']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($checkoutUser['email']); ?></p>
                            <p><strong>Address:</strong> <?php echo htmlspecialchars($checkoutUser['address']); ?></p>
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
        <h1 class="privacy-title">Terms &amp; Conditions</h1>
        <p class="privacy-intro">
            Welcome to <strong>Bright Electric Bike Shop</strong>.
            These Terms and Conditions govern your use of our e-commerce website,
            products, and services. By accessing or using this website, you agree
            to follow and be bound by these terms.
        </p>

        <div class="privacy-section">
            <h2 class="privacy-heading">1. Acceptance of Terms</h2>
            <p class="privacy-text">
                By using the Bright Electric Bike Shop website, you confirm that you
                agree to these Terms and Conditions. If you do not agree with any part
                of these terms, please do not use our website.
            </p>
        </div>

        <div class="privacy-section">
            <h2 class="privacy-heading">2. About the Website</h2>
            <p class="privacy-text">
                Bright Electric Bike Shop is an online e-commerce platform that sells
                electric bikes, accessories, and related products. The website allows
                users to browse products, create accounts, place orders, and communicate
                with customer support.
            </p>
        </div>

        <div class="privacy-section">
            <h2 class="privacy-heading">3. User Accounts</h2>
            <p class="privacy-text">
                Users may be required to create an account to access certain features
                of the website. You agree to:
            </p>
            <ul class="privacy-list">
                <li>Provide accurate and complete information</li>
                <li>Keep your login credentials secure</li>
                <li>Be responsible for all activities under your account</li>
                <li>Notify us immediately if unauthorized access occurs</li>
            </ul>
            <p class="privacy-text">
                Bright Electric Bike Shop reserves the right to suspend or terminate
                accounts that violate these terms.
            </p>
        </div>

        <div class="privacy-section">
            <h2 class="privacy-heading">4. Products and Pricing</h2>
            <p class="privacy-text">
                All product descriptions, images, specifications, and prices are subject
                to change without prior notice. We strive to ensure that:
            </p>
            <ul class="privacy-list">
                <li>Product information is accurate</li>
                <li>Prices are updated correctly</li>
                <li>Product images represent the actual items</li>
            </ul>
            <p class="privacy-text">
                However, errors may occasionally occur. Bright Electric Bike Shop reserves
                the right to correct any pricing or product information errors and cancel
                affected orders if necessary.
            </p>
        </div>

        <div class="privacy-section">
            <h2 class="privacy-heading">5. Orders and Payments</h2>
            <p class="privacy-text">
                When placing an order, you agree that:
            </p>
            <ul class="privacy-list">
                <li>All information provided is valid and accurate</li>
                <li>You are authorized to use the selected payment method</li>
                <li>Orders may be subject to verification before approval</li>
            </ul>
            <p class="privacy-text">
                Payments must be completed before products are shipped. We reserve the
                right to refuse or cancel orders suspected of fraud or unauthorized activity.
            </p>
        </div>

        <div class="privacy-section">
            <h2 class="privacy-heading">6. Shipping and Delivery</h2>
            <p class="privacy-text">
                Bright Electric Bike Shop will make reasonable efforts to deliver products
                within the estimated delivery period. However, delays may occur due to:
            </p>
            <ul class="privacy-list">
                <li>Weather conditions</li>
                <li>Courier issues</li>
                <li>Product availability</li>
                <li>Unexpected events beyond our control</li>
            </ul>
            <p class="privacy-text">
                Customers are responsible for providing accurate delivery information.
            </p>
        </div>

        <div class="privacy-section">
            <h2 class="privacy-heading">7. Return and Refund Policy</h2>
            <p class="privacy-text">
                Customers may request returns or refunds subject to our Return Policy.
                Returns may be accepted if:
            </p>
            <ul class="privacy-list">
                <li>The product is defective or damaged</li>
                <li>The wrong item was delivered</li>
                <li>The request is made within the allowed return period</li>
            </ul>
            <p class="privacy-text">
                Returned products must be in original condition with complete packaging
                and proof of purchase.
            </p>
        </div>

        <div class="privacy-section">
            <h2 class="privacy-heading">8. User Responsibilities</h2>
            <p class="privacy-text">Users agree not to:</p>
            <ul class="privacy-list">
                <li>Use the website for illegal activities</li>
                <li>Attempt to hack or damage the system</li>
                <li>Upload harmful software or malicious code</li>
                <li>Use false information during transactions</li>
                <li>Copy or misuse website content without permission</li>
            </ul>
            <p class="privacy-text">
                Any violation may result in account suspension or legal action.
            </p>
        </div>

        <div class="privacy-section">
            <h2 class="privacy-heading">9. Intellectual Property</h2>
            <p class="privacy-text">
                All content on the Bright Electric Bike Shop website, including:
            </p>
            <ul class="privacy-list">
                <li>Logos</li>
                <li>Images</li>
                <li>Product descriptions</li>
                <li>Website design</li>
                <li>Text and graphics</li>
            </ul>
            <p class="privacy-text">
                are the property of Bright Electric Bike Shop and are protected by
                intellectual property laws. Unauthorized use or reproduction is prohibited.
            </p>
        </div>

        <div class="privacy-section">
            <h2 class="privacy-heading">10. Privacy</h2>
            <p class="privacy-text">
                Your use of the website is also governed by our
                <a href="privacyPolicy.php" style="color:#6b0303; font-weight:600; text-decoration:none;">Privacy Policy</a>.
                By using our services, you consent to the collection and use of information
                according to our Privacy Policy.
            </p>
        </div>

        <div class="privacy-section">
            <h2 class="privacy-heading">11. Limitation of Liability</h2>
            <p class="privacy-text">
                Bright Electric Bike Shop shall not be held liable for:
            </p>
            <ul class="privacy-list">
                <li>Indirect or incidental damages</li>
                <li>Loss of profits or data</li>
                <li>Delays caused by third-party services</li>
                <li>Technical interruptions or website downtime</li>
            </ul>
            <p class="privacy-text">
                Users access and use the website at their own risk.
            </p>
        </div>

        <div class="privacy-section">
            <h2 class="privacy-heading">12. Modifications to Terms</h2>
            <p class="privacy-text">
                Bright Electric Bike Shop reserves the right to modify or update these
                Terms and Conditions at any time. Changes become effective immediately
                after posting on the website.
            </p>
            <p class="privacy-text">
                Users are encouraged to review this page regularly.
            </p>
        </div>

        <div class="privacy-section">
            <h2 class="privacy-heading">13. Governing Law</h2>
            <p class="privacy-text">
                These Terms and Conditions shall be governed and interpreted in accordance
                with the laws of the Philippines.
            </p>
        </div>

        <div class="privacy-section">
            <h2 class="privacy-heading">14. Contact Information</h2>
            <p class="privacy-text">
                For questions or concerns regarding these Terms and Conditions,
                you may contact:
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
                By using the Bright Electric Bike Shop website, you acknowledge that
                you have read and understood these Terms and Conditions and agree to
                be bound by them.
            </p>
            <p class="privacy-text">
                <em>Last Updated: May 17, 2026</em>
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