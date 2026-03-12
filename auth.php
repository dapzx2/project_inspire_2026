<?php
/**
 * Auth - Proses Login
 */

session_start();
include 'config/database.php';

// harus POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// cek CSRF token
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) 
    || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    header('Location: index.php?pesan=gagal');
    exit;
}

unset($_SESSION['csrf_token']);

// ambil input
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    header('Location: index.php?pesan=gagal');
    exit;
}

// cari user di database
$stmt = $conn->prepare("SELECT nim, nama, password FROM users WHERE nim = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
    if (password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        
        $_SESSION['status'] = 'login';
        $_SESSION['nim'] = $user['nim'];
        $_SESSION['nama'] = $user['nama'] ?? $username;
        
        $stmt->close();
        header('Location: dashboard.php');
        exit;
    }
}

$stmt->close();

// delay biar gak bisa brute force
usleep(500000);

header('Location: index.php?pesan=gagal');
exit;
