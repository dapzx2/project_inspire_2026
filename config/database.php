<?php
// konfigurasi database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_inspire_project');

// buat koneksi dengan mysqli OOP
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// cek koneksi
if ($conn->connect_error) {
    // jangan expose detail error ke user di production
    error_log('Database connection failed: ' . $conn->connect_error);
    die('Koneksi database gagal. Silakan hubungi administrator.');
}

// set charset biar aman dari encoding issues
$conn->set_charset('utf8mb4');
