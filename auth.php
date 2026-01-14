<?php
session_start();
include 'config/database.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Get POST data
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    
    // Query to check user credentials (get user by NIM first)
    $query = "SELECT * FROM users WHERE nim = '$username'";
    $result = mysqli_query($conn, $query);
    
    // Check if user exists and verify password
    if ($result && mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Verify hashed password
        if (password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['status'] = "login";
            $_SESSION['nim'] = $username;
            $_SESSION['nama'] = isset($user['nama']) ? $user['nama'] : $username;
            
            // Redirect to dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            // Password incorrect
            header("Location: index.php?pesan=gagal");
            exit();
        }
    } else {
        // Login failed
        header("Location: index.php?pesan=gagal");
        exit();
    }
    
} else {
    // If not POST request, redirect to login
    header("Location: index.php");
    exit();
}
?>
