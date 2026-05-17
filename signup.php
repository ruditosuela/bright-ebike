<?php
session_start();
include 'db.php';

if (isset($_POST['signupBTN'])) {

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    if ($password !== $confirmPassword) {
        echo "<script>alert('Passwords do not match');</script>";
        exit();
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $checkQuery = "SELECT * FROM customers WHERE username='$username' OR email='$email'";
    $checkResult = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        echo "<script>alert('Username or Email already exists');</script>";
        exit();
    }

    $sql = "INSERT INTO customers (username, email, password) 
            VALUES ('$username', '$email', '$hashedPassword')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Registration successful!'); window.location.href='login.php';</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
    }

    $checkoutUser = null;
    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
        $sql = "SELECT * FROM customers WHERE username='$username'";
        $result = mysqli_query($conn, $sql);
        $checkoutUser = mysqli_fetch_assoc($result);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <link rel="shortcut icon" href="assets/bright logo2 no bg.png">
    <link rel="stylesheet" href="style.css">
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
                    <img src="assets/profileIcon.png" alt="Profile Icon">
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
                    <a href="login.php" class="loginBtn">Login</a>
                    <a href="signup.php" class="signupBtn">Sign up</a>
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

        <section>
            <div class="Login">
                <h1>Sign Up</h1>

                <form method="POST" action="signup.php">
                    <div class="email">
                        <input type="email" name="email" id="loginInput" placeholder="Email" required>
                    </div>
                    <div class="userName">
                        <input type="text" name="username" id="loginInput" placeholder="Username" required>
                    </div>

                    <div class="Password">
                        <input type="password" name="password" id="password" minlength="6" placeholder="Password" required>
                    </div>

                    <div class="Password">
                        <input type="password" name="confirmPassword" id="confirmPassword" minlength="6" placeholder="Confirm Password"
                            required>
                    </div>

                    <div>
                        <button class="LoginBTN" name="signupBTN" type="submit">Sign Up</button>
                    </div>
                </form>

                <div class="Register">
                    <p>Already have an account?
                        <span><a href="login.php">Login</a></span>
                    </p>
                </div>
            </div>
        </section>
    </main>
</body>
<script src="script.js"></script>

</html>