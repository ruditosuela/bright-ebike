<?php
session_start();
include 'db.php';

if (isset($_POST['LoginBtn'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Check admins table first
    $adminSql = "SELECT * FROM admins WHERE username='$username' AND password='$password'";
    $adminResult = mysqli_query($conn, $adminSql);

    if (mysqli_num_rows($adminResult) == 1) {
        $_SESSION['admin'] = $username;
        header("Location: adminDashboard.php");
        exit();
    }

    // Check customers table
    $sql = "SELECT * FROM customers WHERE username='$username'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {

        $row = mysqli_fetch_assoc($result);

        // Verify hashed password
        if (password_verify($password, $row['password'])) {

            $_SESSION['username'] = $row['username'];
            $_SESSION['user_id'] = $row['user_id'];

            header("Location: index.php");
            exit();
        }
    }

    echo "<script>alert('Invalid username or password. Please try again.');</script>";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="shortcut icon" href="assets/bright logo2 no bg.png">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <main>
        <header>
            <a href="#"><img src="assets/bright logo2 no bg.png" alt="Bright E-Bike Logo"></a>
            <button class="hamburger" id="hamburger">&#9776;</button>
            <nav class="navbar">
                <div id="navLinks" class="navLinks">
                    <ul>
                        <li><a href="index.php">HOME</a></li>
                        <li><a href="e-bikes.php">E-BIKES</a></li>
                        <li><a href="services.php">SERVICES</a></li>
                        <li><a href="aboutUs.php">ABOUT US</a></li>
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
                <a href="checkout.php" target="_blank"><button class="checkOutBtn">Proceed To Checkout</button></a>
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

        <section>
            <div class="Login">
                <h1>Login</h1>

                <form method="POST">
                    <div class="userName">
                        <input type="text" name="username" placeholder="Username" required>
                    </div>

                    <div class="Password">
                        <input type="password" name="password" minlength="6" placeholder="Password" required>
                    </div>

                    <div class="checkboxPassword">
                        <input type="checkbox" id="rememberMe">
                        <label for="rememberMe">Remember Me</label>
                    </div>

                    <div>
                        <button class="LoginBTN" name="LoginBtn" type="submit">Login</button>
                    </div>
                </form>

                <div class="Register">
                    <p>Don't have an account yet?
                        <span><a href="signup.php">Register</a></span>
                    </p>
                </div>
            </div>
        </section>

    </main>
    <script src="script.js"></script>
</body>

</html>