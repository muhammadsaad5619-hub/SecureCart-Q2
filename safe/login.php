<?php
require 'config.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    /* ============================================================
       FIXED: prepared statement with bound parameters.
       The username/password are sent to MySQL as DATA, never as
       part of the SQL text, so a payload like admin'-- is treated
       as a literal string to match against, not as SQL syntax.
       This closes the SQL Injection Easy vulnerability entirely.
       ============================================================ */
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ? AND password = ?");
    mysqli_stmt_bind_param($stmt, "ss", $username, $password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = $user['is_admin'];
        header("Location: index.php");
        exit;
    } else {
        /* FIXED: generic error only -- no raw DB error ever shown
           to the user, so there is nothing to aid error-based SQLi. */
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>SecureCart - Login (Safe)</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>SecureCart</h1>
        <p class="banner safe">SAFE VERSION</p>
        <h2>Login</h2>

        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <label>Username</label>
            <input type="text" name="username" required>
            <label>Password</label>
            <input type="password" name="password" required>
            <button type="submit">Login</button>
        </form>

        <p class="hint">Try: admin / admin123 — try the same injection payloads from the vulnerable version; they'll fail here.</p>
    </div>
</body>
</html>
