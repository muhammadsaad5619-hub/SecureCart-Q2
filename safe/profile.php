<?php
require 'config.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$message = "";
$error = "";

/* ============================================================
   FIXED: CSRF protection.
   A random token is embedded as a hidden field in the form
   (get_csrf_token(), defined in config.php). On submit, the
   token sent back must match the one stored server-side in
   $_SESSION -- a value the attacker's external page has no way
   to read or guess. A forged cross-site request (like
   csrf_attack.html) submits no token at all, so it is rejected
   here before any update happens.
   ============================================================ */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['new_email'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Request blocked: invalid or missing CSRF token.";
    } else {
        $new_email = $_POST['new_email'];
        $stmt = mysqli_prepare($conn, "UPDATE users SET email = ? WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "ss", $new_email, $username);
        mysqli_stmt_execute($stmt);
        $message = "Email updated successfully.";
    }
}

$stmt = mysqli_prepare($conn, "SELECT email FROM users WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$token = get_csrf_token();
?>
<!DOCTYPE html>
<html>
<head>
    <title>SecureCart - Profile (Safe)</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>SecureCart</h1>
        <p class="banner safe">SAFE VERSION</p>

        <div class="nav">
            <a href="index.php">Home</a> | <a href="search.php">Search</a> | <a href="logout.php">Logout</a>
        </div>

        <h2>My Profile</h2>
        <p>Username: <strong><?php echo htmlspecialchars($username); ?></strong></p>
        <p>Current email: <strong><?php echo htmlspecialchars($user['email']); ?></strong></p>

        <?php if ($message): ?>
            <div class="success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="profile.php">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token); ?>">
            <label>New email</label>
            <input type="email" name="new_email" required>
            <button type="submit">Update Email</button>
        </form>
    </div>
</body>
</html>
