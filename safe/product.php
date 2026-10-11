<?php
require 'config.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);

$stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$product = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$product) {
    die("Product not found.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
    $username = $_SESSION['username'];
    $comment = $_POST['comment'];

    $stmt = mysqli_prepare($conn, "INSERT INTO reviews (product_id, username, comment) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iss", $id, $username, $comment);
    mysqli_stmt_execute($stmt);
    header("Location: product.php?id=$id");
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT * FROM reviews WHERE product_id = ? ORDER BY created_at DESC");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$reviews_result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html>
<head>
    <title>SecureCart - <?php echo htmlspecialchars($product['name']); ?> (Safe)</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>SecureCart</h1>
        <p class="banner safe">SAFE VERSION</p>

        <div class="nav">
            <a href="index.php">Home</a> | <a href="search.php">Search</a> | <a href="profile.php">My Profile</a> | <a href="logout.php">Logout</a>
        </div>

        <div id="welcome-banner"></div>

        <h2><?php echo htmlspecialchars($product['name']); ?></h2>
        <p><?php echo htmlspecialchars($product['description']); ?></p>
        <p class="price">$<?php echo htmlspecialchars($product['price']); ?></p>

        <h3>Reviews</h3>
        <form method="POST" action="product.php?id=<?php echo (int)$id; ?>">
            <textarea name="comment" placeholder="Write a review..." required></textarea>
            <button type="submit">Submit Review</button>
        </form>

        <div class="reviews">
            <?php while ($r = mysqli_fetch_assoc($reviews_result)): ?>
                <div class="review">
                    <strong><?php echo htmlspecialchars($r['username']); ?>:</strong>
                    <!-- FIXED: htmlspecialchars() on the stored comment.
                         A payload like <script>alert(1)</script> saved
                         earlier now renders as literal visible text,
                         not executable HTML. This closes Stored XSS. -->
                    <span><?php echo htmlspecialchars($r['comment']); ?></span>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script>
        /* ============================================================
           FIXED: DOM-based XSS.
           textContent (not innerHTML) is used, so the browser never
           parses the hash value as HTML -- an <img onerror=...>
           payload just becomes inert visible text instead of
           executing. This closes the DOM XSS sink entirely.
           ============================================================ */
        var hash = decodeURIComponent(window.location.hash.substring(1));
        if (hash) {
            document.getElementById('welcome-banner').textContent = "Welcome back! Last viewed: " + hash;
        }
    </script>
</body>
</html>
