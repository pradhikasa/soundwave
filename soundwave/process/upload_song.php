<?php
session_start();
header('Content-Type: application/json'); // Penting: Selalu kirim respons sebagai JSON
require '../config/database.php';

// Atur timezone default untuk konsistensi waktu
date_default_timezone_set('Asia/Makassar');

// Siapkan kerangka respons default
$response = ['success' => false, 'message' => 'Terjadi kesalahan yang tidak diketahui.'];

// Pastikan hanya metode POST yang diterima
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Metode request tidak valid.';
    echo json_encode($response);
    exit;
}

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Akses ditolak. Anda harus login untuk mengunggah lagu.';
    echo json_encode($response);
    exit;
}

// Gunakan try-catch untuk menangani semua kemungkinan error dengan rapi
try {
    $uploader_id = $_SESSION['user_id'];

    // 1. Validasi Input Teks
    $title = trim($_POST['title'] ?? '');
    $artist = trim($_POST['artist'] ?? '');
    if (empty($title) || empty($artist)) {
        throw new Exception("Judul dan Artis wajib diisi.");
    }
    $schedule_time = $_POST['schedule_time'] ?? '';

    // 2. Validasi File Lagu (Sangat Penting!)
    if (!isset($_FILES['song_file']) || $_FILES['song_file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("File lagu wajib diunggah dan tidak boleh ada error.");
    }
    $song_file = $_FILES['song_file'];
    $allowed_song_types = ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/ogg'];
    $max_song_size = 20 * 1024 * 1024; // 20 MB
    if (!in_array($song_file['type'], $allowed_song_types)) {
        throw new Exception("Tipe file lagu tidak valid. Hanya MP3, WAV, atau OGG.");
    }
    if ($song_file['size'] > $max_song_size) {
        throw new Exception("Ukuran file lagu terlalu besar. Maksimal 20 MB.");
    }

    // 3. Validasi File Artwork (Opsional, tapi jika ada harus valid)
    $artwork_filename = 'default_artwork.png'; // Nama default
    $artwork_file_to_upload = null;
    if (isset($_FILES['artwork_file']) && $_FILES['artwork_file']['error'] === UPLOAD_ERR_OK) {
        $artwork_file_to_upload = $_FILES['artwork_file'];
        $allowed_artwork_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_artwork_size = 2 * 1024 * 1024; // 2 MB
        if (!in_array($artwork_file_to_upload['type'], $allowed_artwork_types)) {
            throw new Exception("Tipe file artwork tidak valid. Hanya JPG, PNG, atau GIF.");
        }
        if ($artwork_file_to_upload['size'] > $max_artwork_size) {
            throw new Exception("Ukuran file artwork terlalu besar. Maksimal 2 MB.");
        }
    }
    
    // 4. Proses Pemindahan File (setelah semua validasi lolos)
    $music_dir = '../assets/uploads/music/';
    $artwork_dir = '../assets/uploads/artwork/';
    
    // Pastikan nama file unik dan aman
    $song_filename = uniqid('song_', true) . '_' . str_replace(' ', '_', basename($song_file['name']));
    
    // Pindahkan file lagu
    if (!move_uploaded_file($song_file['tmp_name'], $music_dir . $song_filename)) {
        throw new Exception("Gagal menyimpan file lagu di server.");
    }

    // Pindahkan file artwork jika ada
    if ($artwork_file_to_upload) {
        $artwork_filename = uniqid('art_', true) . '_' . str_replace(' ', '_', basename($artwork_file_to_upload['name']));
        if (!move_uploaded_file($artwork_file_to_upload['tmp_name'], $artwork_dir . $artwork_filename)) {
            // Jika artwork gagal, hapus lagu yang sudah terunggah agar data konsisten
            unlink($music_dir . $song_filename);
            throw new Exception("Gagal menyimpan file artwork di server.");
        }
    }

    // 5. Tentukan Status Jadwal
    $status = 'published';
    $scheduled_for = null;
    if (!empty($schedule_time)) {
        $status = 'scheduled';
        $scheduled_for = date('Y-m-d H:i:s', strtotime($schedule_time));
    }

    // 6. Insert ke Database
    $stmt = $pdo->prepare(
        "INSERT INTO songs (title, artist, file_path, artwork_path, uploader_id, status, scheduled_for) 
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    
    if ($stmt->execute([$title, $artist, $song_filename, $artwork_filename, $uploader_id, $status, $scheduled_for])) {
        $response['success'] = true;
        $response['message'] = 'Lagu berhasil diunggah!';
    } else {
        throw new Exception("Gagal menyimpan data lagu ke database.");
    }

} catch (PDOException $e) {
    // Menangkap error spesifik dari database
    $response['message'] = "Terjadi kesalahan pada database.";
    // Untuk development, Anda bisa log error asli: error_log($e->getMessage());
} catch (Exception $e) {
    // Menangkap semua error lain yang kita lempar (throw)
    $response['message'] = $e->getMessage();
}

// Kirimkan respons final dalam format JSON
echo json_encode($response);
?>