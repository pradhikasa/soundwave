<?php
$host = 'localhost';
$dbname = 'soundwave_db';
$user = 'root'; // User default XAMPP
$pass = '';     // Password default XAMPP kosong

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi ke database gagal: " . $e->getMessage());
}
?>