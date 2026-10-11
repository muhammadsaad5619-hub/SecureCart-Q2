<?php
require 'config.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$result = mysqli_query($conn, "SELECT * FROM products");
?>
<!DOCTYPE html>
<html>
<head>
    <title>SecureCart - Shop (Safe)</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>SecureCart</h1>
        <p class="banner safe">SAFE VERSION</p>

        <div class="nav">
            Logged in as <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
            | <a href="search.php">Search Products</a>
            | <a href="profile.php">My Profile</a>
            | <a href="logout.php">Logout</a>
        </div>

        <h2>Products</h2>
        <div class="product-grid">
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <div class="product-card">
                    <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                    <p><?php echo htmlspecialchars($row['description']); ?></p>
                    <p class="price">$<?php echo htmlspecialchars($row['price']); ?></p>
                    <a href="product.php?id=<?php echo (int)$row['id']; ?>">View &amp; Reviews</a>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>
