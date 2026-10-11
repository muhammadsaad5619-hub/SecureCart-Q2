<?php
require 'config.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$message = "";

/* ============================================================
   VULNERABLE: state-changing action (email change) with:
     - no CSRF token generated or checked
     - no verification of the request's origin
   Any page on the internet can submit this form on the victim's
   behalf, as long as the victim is logged in (has a valid session
   cookie), because the browser attaches cookies automatically.
   ============================================================ */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['new_email'])) {
    $new_email = $_POST['new_email'];
    $stmt = mysqli_prepare($conn, "UPDATE users SET email = ? WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "ss", $new_email, $username);
    mysqli_stmt_execute($stmt);
    $message = "Email updated successfully.";
}

$result = mysqli_query($conn, "SELECT email FROM users WHERE username = '$username'");
$user = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html>
<head>
    <title>SecureCart - Profile (Vulnerable)</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>SecureCart</h1>
        <p class="banner">VULNERABLE VERSION</p>

        <div class="nav">
            <a href="index.php">Home</a> | <a href="search.php">Search</a> | <a href="logout.php">Logout</a>
        </div>

        <h2>My Profile</h2>
        <p>Username: <strong><?php echo $username; ?></strong></p>
        <p>Current email: <strong><?php echo $user['email']; ?></strong></p>

        <?php if ($message): ?>
            <div class="success"><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- No CSRF token field anywhere in this form -->
        <form method="POST" action="profile.php">
            <label>New email</label>
            <input type="email" name="new_email" required>
            <button type="submit">Update Email</button>
        </form>
    </div>
</body>
</html>
