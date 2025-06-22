<?php
session_start();
require '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    die("Akses ditolak.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $full_name = $_POST['full_name'];
    $username = $_POST['username'];
    $bio = $_POST['bio'];

    // Handle update foto profil
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $profile_dir = '../assets/uploads/profiles/';
        $profile_pic_filename = uniqid() . '_' . basename($_FILES['profile_picture']['name']);
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], $profile_dir . $profile_pic_filename);
        
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, username = ?, bio = ?, profile_picture = ? WHERE id = ?");
        $stmt->execute([$full_name, $username, $bio, $profile_pic_filename, $user_id]);
    } else {
        // Update tanpa ganti foto
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, username = ?, bio = ? WHERE id = ?");
        $stmt->execute([$full_name, $username, $bio, $user_id]);
    }

    // Update session username jika berubah
    $_SESSION['username'] = $username;
    header('Location: ../dashboard.php');
}
?>