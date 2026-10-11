<?php
require 'config.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    /* ============================================================
       VULNERABLE: user input concatenated directly into SQL string.
       No parameterized query, no escaping, no input validation.
       This is what allows authentication bypass via SQL injection.
       ============================================================ */
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = $user['is_admin'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid username or password.";
        if (mysqli_error($conn)) {
            /* Exposing raw DB errors to the user --
               this is itself a vulnerability (error-based SQLi aid). */
            $error .= "<br><small>DB error: " . mysqli_error($conn) . "</small>";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>SecureCart - Login (Vulnerable)</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>SecureCart</h1>
        <p class="banner">VULNERABLE VERSION</p>
        <h2>Login</h2>

        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <label>Username</label>
            <input type="text" name="username" required>
            <label>Password</label>
            <input type="password" name="password" required>
            <button type="submit">Login</button>
        </form>

        <p class="hint">Try: admin / admin123 — or try breaking in without the password.</p>
    </div>
</body>
</html>
