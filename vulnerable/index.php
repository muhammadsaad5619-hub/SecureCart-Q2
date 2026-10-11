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
    <title>SecureCart - Shop (Vulnerable)</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>SecureCart</h1>
        <p class="banner">VULNERABLE VERSION</p>

        <div class="nav">
            Logged in as <strong><?php echo $_SESSION['username']; ?></strong>
            | <a href="search.php">Search Products</a>
            | <a href="profile.php">My Profile</a>
            | <a href="logout.php">Logout</a>
        </div>

        <h2>Products</h2>
        <div class="product-grid">
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <div class="product-card">
                    <h3><?php echo $row['name']; ?></h3>
                    <p><?php echo $row['description']; ?></p>
                    <p class="price">$<?php echo $row['price']; ?></p>
                    <a href="product.php?id=<?php echo $row['id']; ?>">View &amp; Reviews</a>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>
