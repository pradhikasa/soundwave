<?php
session_start();
require '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode tidak valid.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'toggle_favorite':
            $song_id = $_POST['song_id'] ?? 0;
            if (empty($song_id)) throw new Exception('ID Lagu tidak valid.');

            // Cek apakah lagu sudah difavoritkan
            $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND song_id = ?");
            $stmt->execute([$user_id, $song_id]);

            if ($stmt->rowCount() > 0) {
                // Jika sudah ada, hapus (unlike)
                $delete_stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND song_id = ?");
                $delete_stmt->execute([$user_id, $song_id]);
                echo json_encode(['success' => true, 'status' => 'unliked', 'message' => 'Dihapus dari favorit.']);
            } else {
                // Jika belum ada, tambahkan (like)
                $insert_stmt = $pdo->prepare("INSERT INTO favorites (user_id, song_id) VALUES (?, ?)");
                $insert_stmt->execute([$user_id, $song_id]);
                echo json_encode(['success' => true, 'status' => 'liked', 'message' => 'Ditambahkan ke favorit!']);
            }
            break;

        case 'add_to_playlist':
            $song_id = $_POST['song_id'] ?? 0;
            $playlist_id = $_POST['playlist_id'] ?? 0;
            if (empty($song_id) || empty($playlist_id)) throw new Exception('ID Lagu atau Playlist tidak valid.');

            // Verifikasi kepemilikan playlist
            $stmt = $pdo->prepare("SELECT id FROM playlists WHERE id = ? AND creator_id = ?");
            $stmt->execute([$playlist_id, $user_id]);
            if ($stmt->rowCount() === 0) throw new Exception('Anda tidak memiliki izin untuk playlist ini.');

            // Cek duplikat lagu di playlist
            $stmt_check = $pdo->prepare("SELECT id FROM playlist_songs WHERE playlist_id = ? AND song_id = ?");
            $stmt_check->execute([$playlist_id, $song_id]);
            if ($stmt_check->rowCount() > 0) throw new Exception('Lagu ini sudah ada di dalam playlist.');

            // Tambahkan lagu ke playlist
            $stmt_add = $pdo->prepare("INSERT INTO playlist_songs (playlist_id, song_id) VALUES (?, ?)");
            $stmt_add->execute([$playlist_id, $song_id]);
            echo json_encode(['success' => true, 'message' => 'Lagu berhasil ditambahkan ke playlist!']);
            break;

        default:
            throw new Exception('Aksi tidak dikenal.');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>