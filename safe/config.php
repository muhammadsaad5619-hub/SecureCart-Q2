<?php
session_start();

$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "Root@12345";
$DB_NAME = "securecart";

$conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

/* CSRF token helper: generates one random token per session and
   reuses it for every form, so every state-changing request can
   be checked against the value stored server-side in $_SESSION. */
function get_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}
?>
