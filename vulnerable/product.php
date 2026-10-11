<?php
require 'config.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);

$product_result = mysqli_query($conn, "SELECT * FROM products WHERE id = $id");
$product = mysqli_fetch_assoc($product_result);

if (!$product) {
    die("Product not found.");
}

/* Handle new review submission */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
    $username = $_SESSION['username'];
    $comment = $_POST['comment'];

    /* This insert IS parameterized (storage itself isn't the bug) --
       the vulnerability is on the OUTPUT side below, where the
       stored comment is rendered without encoding. That is what
       makes this genuine Stored XSS: the payload is saved exactly
       as submitted, and executes for EVERY user who later views it. */
    $stmt = mysqli_prepare($conn, "INSERT INTO reviews (product_id, username, comment) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iss", $id, $username, $comment);
    mysqli_stmt_execute($stmt);
    header("Location: product.php?id=$id");
    exit;
}

$reviews_result = mysqli_query($conn, "SELECT * FROM reviews WHERE product_id = $id ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>SecureCart - <?php echo $product['name']; ?> (Vulnerable)</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>SecureCart</h1>
        <p class="banner">VULNERABLE VERSION</p>

        <div class="nav">
            <a href="index.php">Home</a> | <a href="search.php">Search</a> | <a href="profile.php">My Profile</a> | <a href="logout.php">Logout</a>
        </div>

        <!-- DOM XSS sink: this banner is filled entirely client-side,
             using the URL hash, and never touches the PHP backend. -->
        <div id="welcome-banner"></div>

        <h2><?php echo $product['name']; ?></h2>
        <p><?php echo $product['description']; ?></p>
        <p class="price">$<?php echo $product['price']; ?></p>

        <h3>Reviews</h3>
        <form method="POST" action="product.php?id=<?php echo $id; ?>">
            <textarea name="comment" placeholder="Write a review..." required></textarea>
            <button type="submit">Submit Review</button>
        </form>

        <div class="reviews">
            <?php while ($r = mysqli_fetch_assoc($reviews_result)): ?>
                <div class="review">
                    <strong><?php echo $r['username']; ?>:</strong>
                    <!-- VULNERABLE: comment printed raw, no htmlspecialchars().
                         This is the Stored XSS sink -- runs for every visitor. -->
                    <span><?php echo $r['comment']; ?></span>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script>
        /* ============================================================
           VULNERABLE: DOM-based XSS.
           Reads location.hash (e.g. #name after a /product.php?id=1#name)
           directly into innerHTML with no sanitization. The payload
           never touches the server at all -- it is entirely a
           client-side JavaScript bug.
           Try visiting:
           product.php?id=1#<img src=x onerror=alert('DOM-XSS')>
           ============================================================ */
        var hash = decodeURIComponent(window.location.hash.substring(1));
        if (hash) {
            document.getElementById('welcome-banner').innerHTML = "Welcome back! Last viewed: " + hash;
        }
    </script>
</body>
</html>
