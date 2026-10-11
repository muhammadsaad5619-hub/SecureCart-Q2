<?php
require 'config.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$results = [];
$query_text = "";

if (isset($_GET['q'])) {
    $query_text = $_GET['q'];

    /* ============================================================
       FIXED: prepared statement with a bound parameter.
       The search term is sent as DATA to MySQL, never concatenated
       into the SQL text. This closes BOTH the Intermediate
       (blind boolean) and Advanced (UNION-based) SQL injection
       paths entirely -- there is no SQL syntax left for an
       attacker's input to break out into.
       ============================================================ */
    $stmt = mysqli_prepare($conn, "SELECT id, name, description, price FROM products WHERE name LIKE ?");
    $like_param = "%" . $query_text . "%";
    mysqli_stmt_bind_param($stmt, "s", $like_param);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $results[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>SecureCart - Search (Safe)</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>SecureCart</h1>
        <p class="banner safe">SAFE VERSION</p>

        <div class="nav">
            <a href="index.php">Home</a> | <a href="profile.php">My Profile</a> | <a href="logout.php">Logout</a>
        </div>

        <h2>Search Products</h2>
        <form method="GET" action="search.php">
            <input type="text" name="q" placeholder="Search by product name..." value="<?php echo htmlspecialchars($query_text); ?>">
            <button type="submit">Search</button>
        </form>

        <?php if ($query_text !== ""): ?>
            <!-- FIXED: htmlspecialchars() encodes <, >, ", ', & before
                 output, so <script> tags render as inert text instead
                 of executing. This closes the Reflected XSS sink. -->
            <p class="reflected">Showing results for: <?php echo htmlspecialchars($query_text); ?></p>
        <?php endif; ?>

        <div class="product-grid">
            <?php foreach ($results as $row): ?>
                <div class="product-card">
                    <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                    <p><?php echo htmlspecialchars($row['description']); ?></p>
                    <p class="price">$<?php echo htmlspecialchars($row['price']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($query_text !== "" && count($results) === 0): ?>
            <p class="hint">No products found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
