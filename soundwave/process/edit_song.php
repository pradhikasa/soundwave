<?php
session_start();
require '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    die("Akses ditolak.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $song_id = $_POST['song_id'];
    $title = $_POST['title'];
    $artist = $_POST['artist'];
    $user_id = $_SESSION['user_id'];

    // Verifikasi kepemilikan
    $stmt = $pdo->prepare("SELECT * FROM songs WHERE id = ? AND uploader_id = ?");
    $stmt->execute([$song_id, $user_id]);
    $song = $stmt->fetch();

    if (!$song) {
        die("Anda tidak punya izin untuk mengedit lagu ini.");
    }

    // Handle update artwork jika ada
    if (isset($_FILES['artwork_file']) && $_FILES['artwork_file']['error'] == 0) {
        $artwork_dir = '../assets/uploads/artwork/';
        // Hapus artwork lama jika bukan default
        if ($song['artwork_path'] != 'default_artwork.png') {
            unlink($artwork_dir . $song['artwork_path']);
        }
        $artwork_filename = uniqid() . '_' . basename($_FILES['artwork_file']['name']);
        move_uploaded_file($_FILES['artwork_file']['tmp_name'], $artwork_dir . $artwork_filename);
        
        $stmt = $pdo->prepare("UPDATE songs SET title = ?, artist = ?, artwork_path = ? WHERE id = ?");
        $stmt->execute([$title, $artist, $artwork_filename, $song_id]);
    } else {
        // Update tanpa ganti artwork
        $stmt = $pdo->prepare("UPDATE songs SET title = ?, artist = ? WHERE id = ?");
        $stmt->execute([$title, $artist, $song_id]);
    }

    header('Location: ../dashboard.php?page=musik_saya');
}
?>