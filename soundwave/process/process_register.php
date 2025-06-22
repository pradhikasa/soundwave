<?php
session_start();
require '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validasi dasar
    if (empty($full_name) || empty($username) || empty($email) || empty($password)) {
        die("Semua field harus diisi!");
    }

    // Cek jika username atau email sudah ada
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->rowCount() > 0) {
        die("Username atau email sudah terdaftar!");
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert ke database
    $stmt = $pdo->prepare("INSERT INTO users (full_name, username, email, password) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$full_name, $username, $email, $hashed_password])) {
        // Login otomatis setelah daftar
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['username'] = $username;
        header('Location: ../dashboard.php');
    } else {
        die("Gagal mendaftar. Silakan coba lagi.");
    }
}
?>