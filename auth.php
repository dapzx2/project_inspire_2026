<?php
session_start();
include 'config/database.php';

// hanya terima POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// validasi CSRF token
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) 
    || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    // CSRF token invalid, mungkin serangan
    header('Location: index.php?pesan=gagal');
    exit;
}

// hapus CSRF token setelah dipakai (one-time use)
unset($_SESSION['csrf_token']);

// validasi input
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    header('Location: index.php?pesan=gagal');
    exit;
}

// cari user dengan prepared statement (prevent SQL injection)
$stmt = $conn->prepare("SELECT nim, nama, password FROM users WHERE nim = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
    // verify password hash
    if (password_verify($password, $user['password'])) {
        // regenerate session ID untuk prevent session fixation
        session_regenerate_id(true);
        
        // set session
        $_SESSION['status'] = 'login';
        $_SESSION['nim'] = $user['nim'];
        $_SESSION['nama'] = $user['nama'] ?? $username;
        
        $stmt->close();
        header('Location: dashboard.php');
        exit;
    }
}

$stmt->close();

// login gagal - delay sedikit untuk prevent brute force timing attack
usleep(500000); // 0.5 detik

header('Location: index.php?pesan=gagal');
exit;
