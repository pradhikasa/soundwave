<?php
session_start();
require '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    die("Akses ditolak.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $song_id = $_POST['song_id'];
    $user_id = $_SESSION['user_id'];

    // Ambil info lagu untuk hapus file
    $stmt = $pdo->prepare("SELECT * FROM songs WHERE id = ? AND uploader_id = ?");
    $stmt->execute([$song_id, $user_id]);
    $song = $stmt->fetch();

    if ($song) {
        // Hapus file fisik
        unlink('../assets/uploads/music/' . $song['file_path']);
        if ($song['artwork_path'] != 'default_artwork.png') {
            unlink('../assets/uploads/artwork/' . $song['artwork_path']);
        }

        // Hapus dari database
        $stmt = $pdo->prepare("DELETE FROM songs WHERE id = ?");
        $stmt->execute([$song_id]);

        header('Location: ../dashboard.php?page=musik_saya');
    } else {
        die("Anda tidak punya izin untuk menghapus lagu ini.");
    }
}
?>