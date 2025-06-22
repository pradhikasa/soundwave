<?php
session_start();
require '../config/database.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Silakan login.']);
    exit();
}

// Pastikan ini adalah request POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode request tidak valid.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'create':
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if (empty($name)) {
                throw new Exception('Nama playlist tidak boleh kosong.');
            }

            $stmt = $pdo->prepare("INSERT INTO playlists (name, description, creator_id) VALUES (?, ?, ?)");
            $stmt->execute([$name, $description, $user_id]);
            echo json_encode(['success' => true, 'message' => 'Playlist berhasil dibuat!']);
            break;

        case 'update':
            $playlist_id = $_POST['playlist_id'] ?? 0;
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if (empty($name) || empty($playlist_id)) {
                throw new Exception('Data tidak lengkap untuk memperbarui playlist.');
            }
            
            // Verifikasi kepemilikan playlist sebelum update
            $stmt = $pdo->prepare("SELECT id FROM playlists WHERE id = ? AND creator_id = ?");
            $stmt->execute([$playlist_id, $user_id]);
            if ($stmt->rowCount() === 0) {
                throw new Exception('Anda tidak memiliki izin untuk mengedit playlist ini.');
            }

            $stmt = $pdo->prepare("UPDATE playlists SET name = ?, description = ? WHERE id = ?");
            $stmt->execute([$name, $description, $playlist_id]);
            echo json_encode(['success' => true, 'message' => 'Playlist berhasil diperbarui!']);
            break;

        case 'delete':
            $playlist_id = $_POST['playlist_id'] ?? 0;
             if (empty($playlist_id)) {
                throw new Exception('ID Playlist tidak ditemukan.');
            }

            // Verifikasi kepemilikan playlist sebelum hapus
            $stmt = $pdo->prepare("SELECT id FROM playlists WHERE id = ? AND creator_id = ?");
            $stmt->execute([$playlist_id, $user_id]);
            if ($stmt->rowCount() === 0) {
                throw new Exception('Anda tidak memiliki izin untuk menghapus playlist ini.');
            }

            $stmt = $pdo->prepare("DELETE FROM playlists WHERE id = ?");
            $stmt->execute([$playlist_id]);
            echo json_encode(['success' => true, 'message' => 'Playlist berhasil dihapus!']);
            break;

        default:
            throw new Exception('Aksi tidak dikenal.');
    }
} catch (Exception $e) {
    // Kirim pesan error dalam format JSON
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>