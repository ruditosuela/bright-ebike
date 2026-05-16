<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// UPLOAD PROFILE PICTURE
if (isset($_POST['uploadPicture'])) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
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

// UPDATE PROFILE INFO
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

// GET USER DATA
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM customers WHERE username='$username'"));

// PROFILE PICTURE PATH
$picPath = __DIR__ . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "profiles" . DIRECTORY_SEPARATOR . $user['profile_picture'];

$profilePic = (!empty($user['profile_picture']) && file_exists($picPath))
    ? "uploads/profiles/" . $user['profile_picture']
    : "assets/profileIcon.png";
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
    <div class="accountContainer">
        <h1>Account Details</h1>

        <!-- PROFILE PICTURE SECTION -->
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

        <!-- PROFILE INFO SECTION -->
        <form method="POST">
            <div class="accountInfo">
                <p>
                    <strong>Username:</strong>
                    <?php echo $user['username']; ?>
                </p>
                <p>
                    <strong>Email:</strong>
                    <?php echo $user['email']; ?>
                </p>
                <p class="editableField">
                    <strong>Full Name:</strong>
                    <input type="text" name="full_name" 
                        value="<?php echo htmlspecialchars($user['full_name']); ?>"
                        oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')">
                </p>
                <p class="editableField">
                    <strong>Contact No:</strong>
                    <input type="text" name="contact_no" 
                        value="<?php echo htmlspecialchars($user['contact_no']); ?>"
                        maxlength="11" pattern="[0-9]{11}" inputmode="numeric"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,11);" required>
                </p>
                <p class="editableField">
                    <strong>Address:</strong>
                    <input type="text" name="address" 
                        value="<?php echo $user['address']; ?>" required>
                </p>
            </div>
            <button type="submit" name="updateProfile" class="saveBtn">Save Changes</button>
            <a href="index.php" class="backBtn">Back to Home</a>
        </form>
    </div>

    <script>
    function previewAndUpload(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('profilePicPreview').src = e.target.result;
            };
            reader.readAsDataURL(input.files[0]);

            // Show upload button instead of auto-submitting
            document.getElementById('uploadBtn').style.display = 'block';
        }
    }
    </script>
    <script src="script.js"></script>
</body>
</html>