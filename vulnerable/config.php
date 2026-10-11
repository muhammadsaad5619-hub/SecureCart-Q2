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
?>
