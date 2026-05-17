<?php
session_start();
include 'db.php';

$sql = "SELECT product_name, price, category FROM product ORDER BY category, price ASC";
$result = mysqli_query($conn, $sql);

$products = ['Two Wheeler' => [], 'Three Wheeler' => [], 'Four Wheeler' => []];

while ($row = mysqli_fetch_assoc($result)) {
    $cat = $row['category'];
    if (isset($products[$cat])) {
        $products[$cat][] = $row;
    }
}

$profilePic = "assets/profileIcon.png";
if (isset($_SESSION['username'])) {
    $u = mysqli_fetch_assoc(mysqli_query($conn, "SELECT profile_picture FROM customers WHERE username='" . $_SESSION['username'] . "'"));
    if (!empty($u['profile_picture']) && file_exists("uploads/profiles/" . $u['profile_picture'])) {
        $profilePic = "uploads/profiles/" . $u['profile_picture'];
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
    <title>Price List</title>
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
            <div class="cartContent" id="cartContent"></div>
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
                    <p class="welcomeText">Welcome, <?php echo $_SESSION['username']; ?>!</p>
                    <a href="accountDetails.php" class="loginBtn">Account Details</a>
                    <a href="customerLogout.php" class="signupBtn">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="loginBtn">Login</a>
                    <a href="signup.php" class="signupBtn">Sign up</a>
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
                <option value="gcash">GCash</option>
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


    <div class="priceListTitle">
        <h1>Bright E-Bike Price List</h1>
        <p>Find the latest prices of our electric bikes</p>
    </div>

    <section class="priceContainer">

        <?php foreach ($products as $category => $items): ?>
            <h2><?php echo $category; ?></h2>
            <table class="priceTable">
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                </tr>
                <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="2">No products available.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td>₱<?php echo number_format($item['price'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </table>
        <?php endforeach; ?>

    </section>

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
            <p>If you have feedback or questions, kindly submit them through our 
                <a href="https://forms.gle/HBkXMi3DqTymjnb66" target="_blank">Feedback Form.</a>
            </p>
            <div class="footer-links">
                <a href="privacyPolicy.php">PRIVACY POLICY</a>
                <a href="terms&Condition.php">TERMS & CONDITION</a>
            </div>
        </div>
    </div>
</footer>

</html>