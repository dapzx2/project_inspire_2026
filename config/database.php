<?php
/**
 * Database Config
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_inspire_project');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    error_log('Database connection failed: ' . $conn->connect_error);
    die('Koneksi database gagal. Silakan hubungi administrator.');
}

$conn->set_charset('utf8mb4');
