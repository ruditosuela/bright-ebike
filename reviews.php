<?php
session_start();
include 'db.php';

// Profile picture
$profilePic = "assets/profileIcon.png";
if (isset($_SESSION['username'])) {
    $u = mysqli_fetch_assoc(mysqli_query($conn, "SELECT profile_picture FROM customers WHERE username='" . $_SESSION['username'] . "'"));
    if (!empty($u['profile_picture']) && file_exists("uploads/profiles/" . $u['profile_picture'])) {
        $profilePic = "uploads/profiles/" . $u['profile_picture'];
    }
}

// Get logged-in user info
$loggedInUser = null;
if (isset($_SESSION['username'])) {
    $username = mysqli_real_escape_string($conn, $_SESSION['username']);
    $r = mysqli_query($conn, "SELECT * FROM customers WHERE username='$username'");
    $loggedInUser = mysqli_fetch_assoc($r);
}

// Fetch all products for the review form dropdown
$productsResult = mysqli_query($conn, "SELECT product_id, product_name FROM product ORDER BY product_name ASC");
$products = [];
while ($row = mysqli_fetch_assoc($productsResult)) {
    $products[] = $row;
}

$reviewsResult = mysqli_query($conn, "
    SELECT r.review_id, r.rating, r.review_text, r.created_at,
           c.username, c.full_name, c.profile_picture,
           p.product_name, p.product_id
    FROM reviews r
    JOIN customers c ON r.user_id = c.user_id
    JOIN product p ON r.product_id = p.product_id
    ORDER BY r.created_at DESC
");
$reviews = [];
while ($row = mysqli_fetch_assoc($reviewsResult)) {
    $reviews[] = $row;
}

// Check which products the logged-in user has already reviewed
$reviewedProductIds = [];
if ($loggedInUser) {
    $uid = (int)$loggedInUser['user_id'];
    $rCheck = mysqli_query($conn, "SELECT product_id FROM reviews WHERE user_id=$uid");
    while ($rc = mysqli_fetch_assoc($rCheck)) {
        $reviewedProductIds[] = (int)$rc['product_id'];
    }
}

$avgResult = mysqli_query($conn, "
    SELECT p.product_id, p.product_name,
           ROUND(AVG(r.rating), 1) AS avg_rating,
           COUNT(r.review_id) AS total_reviews
    FROM product p
    LEFT JOIN reviews r ON p.product_id = r.product_id
    GROUP BY p.product_id, p.product_name
    HAVING total_reviews > 0
    ORDER BY avg_rating DESC
");
$productRatings = [];
while ($row = mysqli_fetch_assoc($avgResult)) {
    $productRatings[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Reviews – Bright E-Bike</title>
    <link rel="shortcut icon" href="assets/bright logo2 no bg.png">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
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
                        <li><a href="reviews.php" class="active">REVIEWS</a></li>
                    </ul>
                </div>
            </nav>
            <div class="Icons">
                <div class="searchBar">
                    <img src="assets/searchIcon.png" alt="Search Icon">
                    <input type="text" id="searchInput" placeholder="Search...">
                </div>
                <div class="shoppingCart" id="cartBtn">
                    <img src="assets/shoppingCartIcon.png" alt="Shopping Cart Icon">
                    <span class="cartCount" id="cartCount">0</span>
                </div>
                <div class="profileIcon" id="profileBtn">
                    <img src="<?php echo $profilePic; ?>" alt="Profile Icon"
                        style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
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
                    <p>Total: </p><span>₱0.00</span>
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
        <div class="reviewsHero">
            <h1>Customer Reviews</h1>
            <p>Real feedback from real riders. Share your Bright E-Bike experience!</p>
        </div>

        <div class="reviewsLayout">
            <div class="reviewFormCard">
                <div class="reviewFormHeader">
                    <span class="icon"></span>
                    Write a Review
                </div>

                <?php if ($loggedInUser): ?>
                    <div class="reviewFormBody">
                        <label for="reviewProduct">Select E-Bike</label>
                        <select id="reviewProduct">
                            <option value="">Choose a product</option>
                            <?php foreach ($products as $p): ?>
                                <option value="<?php echo $p['product_id']; ?>"
                                    <?php echo in_array((int)$p['product_id'], $reviewedProductIds) ? 'disabled title="Already reviewed"' : ''; ?>>
                                    <?php echo htmlspecialchars($p['product_name']); ?>
                                    <?php echo in_array((int)$p['product_id'], $reviewedProductIds) ? ' ✓ Reviewed' : ''; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <label>Your Rating</label>
                        <div class="starRatingInput" id="starRatingInput">
                            <input type="radio" name="rating" id="star5" value="5"><label for="star5">★</label>
                            <input type="radio" name="rating" id="star4" value="4"><label for="star4">★</label>
                            <input type="radio" name="rating" id="star3" value="3"><label for="star3">★</label>
                            <input type="radio" name="rating" id="star2" value="2"><label for="star2">★</label>
                            <input type="radio" name="rating" id="star1" value="1"><label for="star1">★</label>
                        </div>

                        <label for="reviewText">Your Review</label>
                        <textarea id="reviewText" placeholder="Share your experience with this e-bike..."></textarea>

                        <button class="reviewSubmitBtn" id="submitReviewBtn">Submit Review</button>
                        <div class="reviewMsg" id="reviewMsg"></div>
                    </div>
                <?php else: ?>
                    <div class="loginPrompt">
                        <p>Please <a href="login.php">log in</a> to write a review.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="reviewsListSection">
                <div class="reviewsListHeader">
                    <h2>All Reviews</h2>
                    <span class="reviewsCount"><?php echo count($reviews); ?> review<?php echo count($reviews) != 1 ? 's' : ''; ?></span>
                </div>

                <div class="reviewFilterBar">
                    <button class="reviewFilterBtn active" data-filter="all">All</button>
                    <button class="reviewFilterBtn" data-filter="5">★★★★★</button>
                    <button class="reviewFilterBtn" data-filter="4">★★★★</button>
                    <button class="reviewFilterBtn" data-filter="3">★★★</button>
                    <button class="reviewFilterBtn" data-filter="2">★★</button>
                    <button class="reviewFilterBtn" data-filter="1">★</button>
                </div>

                <div id="reviewsList">
                    <?php if (empty($reviews)): ?>
                        <div class="noReviews">
                            <div class="no-icon"></div>
                            <p>No reviews yet. Be the first to share your experience!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($reviews as $rev): ?>
                            <?php
                            $avPath = (!empty($rev['profile_picture']) && file_exists("uploads/profiles/" . $rev['profile_picture']))
                                ? "uploads/profiles/" . $rev['profile_picture']
                                : "assets/profileIcon.png";
                            $stars = (int)$rev['rating'];
                            $date  = date('M d, Y', strtotime($rev['created_at']));
                            ?>
                            <div class="reviewCard" data-rating="<?php echo $stars; ?>">
                                <div class="reviewCardTop">
                                    <img src="<?php echo $avPath; ?>" alt="avatar" class="reviewAvatar">
                                    <div class="reviewMeta">
                                        <div class="reviewer-name"><?php echo htmlspecialchars($rev['full_name'] ?: $rev['username']); ?></div>
                                        <div class="review-product"> <?php echo htmlspecialchars($rev['product_name']); ?></div>
                                        <div class="review-date"><?php echo $date; ?></div>
                                    </div>
                                    <div class="reviewStars">
                                        <?php echo str_repeat('★', $stars) . str_repeat('☆', 5 - $stars); ?>
                                    </div>
                                </div>
                                <p class="reviewText"><?php echo nl2br(htmlspecialchars($rev['review_text'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if (!empty($productRatings)): ?>
            <div class="topRatedSection">
                <h2>Top Rated E-Bikes</h2>
                <div class="topRatedCards">
                    <?php foreach ($productRatings as $pr): ?>
                        <div class="topRatedCard">
                            <div class="trc-name"><?php echo htmlspecialchars($pr['product_name']); ?></div>
                            <div class="trc-stars">
                                <?php
                                $avg = round($pr['avg_rating']);
                                echo str_repeat('★', $avg) . str_repeat('☆', 5 - $avg);
                                ?>
                                <span style="color:#333;font-size:12px;"> <?php echo $pr['avg_rating']; ?></span>
                            </div>
                            <div class="trc-count"><?php echo $pr['total_reviews']; ?> review<?php echo $pr['total_reviews'] != 1 ? 's' : ''; ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
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
            <p>If you have feedback, kindly submit through our <a href="https://forms.gle/HBkXMi3DqTymjnb66" target="_blank">Feedback Form.</a></p>
            <div class="footer-links">
                <a href="privacyPolicy.php">PRIVACY POLICY</a>
                <a href="terms&Condition.php">TERMS & CONDITION</a>
            </div>
        </div>
    </div>
</footer>

</html>