<?php
// Database Configuration
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_inspire_project";

// Create connection
$conn = mysqli_connect($host, $user, $pass, $db);

// Check connection
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
