<?php
require 'config.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$results = [];
$query_text = "";
$db_error = "";

if (isset($_GET['q'])) {
    $query_text = $_GET['q'];

    /* ============================================================
       VULNERABLE: raw string concatenation into the SQL query.
       Exploitable for reflected XSS (output below) and for
       Intermediate (blind boolean) and Advanced (UNION) SQLi.
       The DB error is shown on failure, which also demonstrates
       error-based information disclosure.
       ============================================================ */
    $sql = "SELECT id, name, description, price FROM products WHERE name LIKE '%$query_text%'";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $results[] = $row;
        }
    } else {
        $db_error = mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>SecureCart - Search (Vulnerable)</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>SecureCart</h1>
        <p class="banner">VULNERABLE VERSION</p>

        <div class="nav">
            <a href="index.php">Home</a> | <a href="profile.php">My Profile</a> | <a href="logout.php">Logout</a>
        </div>

        <h2>Search Products</h2>
        <form method="GET" action="search.php">
            <input type="text" name="q" placeholder="Search by product name..." value="<?php echo $query_text; ?>">
            <button type="submit">Search</button>
        </form>

        <?php if ($query_text !== ""): ?>
            <!-- VULNERABLE: reflected XSS sink, no htmlspecialchars(). -->
            <p class="reflected">Showing results for: <?php echo $query_text; ?></p>
        <?php endif; ?>

        <?php if ($db_error): ?>
            <div class="error">DB error: <?php echo $db_error; ?></div>
        <?php endif; ?>

        <div class="product-grid">
            <?php foreach ($results as $row): ?>
                <div class="product-card">
                    <h3><?php echo $row['name']; ?></h3>
                    <p><?php echo $row['description']; ?></p>
                    <p class="price">$<?php echo $row['price']; ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($query_text !== "" && count($results) === 0 && !$db_error): ?>
            <p class="hint">No products found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
