<?php
session_start();
require 'config/database.php';

// 1. OTENTIKASI & PENGAMBILAN DATA AWAL (Kode ini sudah sangat baik)
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil info user
$stmt_user = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

// Ambil SEMUA playlist milik user SEKALI SAJA (Sangat efisien!)
$playlists_stmt = $pdo->prepare("SELECT id, name FROM playlists WHERE creator_id = ? ORDER BY name ASC");
$playlists_stmt->execute([$user_id]);
$user_playlists = $playlists_stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil SEMUA ID lagu favorit user SEKALI SAJA (Sangat efisien!)
$fav_stmt = $pdo->prepare("SELECT song_id FROM favorites WHERE user_id = ?");
$fav_stmt->execute([$user_id]);
$favorited_song_ids = $fav_stmt->fetchAll(PDO::FETCH_COLUMN, 0);

$page = isset($_GET['page']) ? $_GET['page'] : 'beranda';

// 2. FUNGSI HELPER UNTUK MERENDER KARTU LAGU (Refactoring)
// Fungsi ini akan kita gunakan berulang kali untuk menampilkan lagu
function renderSongCard($song, $user_playlists, $favorited_song_ids, $current_user_id) {
    // Menyiapkan variabel untuk kemudahan
    $is_favorited = in_array($song['id'], $favorited_song_ids);
    $is_owner = ($song['uploader_id'] == $current_user_id);
    
    // Keamanan: Selalu gunakan htmlspecialchars untuk data yang akan dicetak
    $safe_title = htmlspecialchars($song['title'], ENT_QUOTES, 'UTF-8');
    $safe_artist = htmlspecialchars($song['artist'], ENT_QUOTES, 'UTF-8');
    $safe_file_path = htmlspecialchars($song['file_path'], ENT_QUOTES, 'UTF-8');
    $safe_artwork_path = htmlspecialchars($song['artwork_path'], ENT_QUOTES, 'UTF-8');
    
    // Data untuk modal edit, di-encode sebagai JSON agar aman dan mudah dibaca JS
    $song_data_json = htmlspecialchars(json_encode([
        'id' => $song['id'],
        'title' => $song['title'],
        'artist' => $song['artist']
    ]), ENT_QUOTES, 'UTF-8');

    // Membangun string HTML untuk kartu lagu
    $html = "<div class='song-card' data-src='assets/uploads/music/{$safe_file_path}' data-title='{$safe_title}' data-artist='{$safe_artist}' data-artwork='assets/uploads/artwork/{$safe_artwork_path}'>";
    $html .= "<img src='assets/uploads/artwork/{$safe_artwork_path}' alt='Artwork' class='artwork'>";
    $html .= "<div class='title'>{$safe_title}</div>";
    $html .= "<div class='artist'>{$safe_artist}</div>";
    
    $html .= "<div class='song-interactions'>";
    $html .= "<button class='action-btn favorite-btn ".($is_favorited ? 'active' : '')."' data-song-id='{$song['id']}'><i class='".($is_favorited ? 'fas' : 'far')." fa-heart'></i></button>";
    
    $html .= "<div class='more-options'>";
    $html .= "<button class='action-btn more-options-btn'><i class='fas fa-ellipsis-v'></i></button>";
    $html .= "<div class='options-dropdown'>";
    $html .= "<div class='dropdown-header'>Tambah ke Playlist</div>";
    if (count($user_playlists) > 0) {
        foreach ($user_playlists as $playlist) {
            $html .= "<a href='#' class='add-to-playlist-link' data-song-id='{$song['id']}' data-playlist-id='{$playlist['id']}'>".htmlspecialchars($playlist['name'], ENT_QUOTES, 'UTF-8')."</a>";
        }
    } else {
        $html .= "<span class='no-playlist'>Buat playlist dulu!</span>";
    }

    if ($is_owner) {
        $html .= "<div class='dropdown-divider'></div>";
        $html .= "<a href='#' class='edit-song-link' data-song-info='{$song_data_json}'>Edit Lagu</a>";
        $html .= "<a href='#' class='delete-song-link' data-song-id='{$song['id']}'>Hapus Lagu</a>";
    }
    $html .= "</div></div></div></div>";

    return $html;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SoundWave</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Audiowide&family=Major+Mono+Display&family=Monoton&family=Outfit:wght@700&family=Russo+One&family=Syncopate:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div id="uploadModal" class="modal"></div>
    <div id="editSongModal" class="modal"></div>
    <div id="playlistModal" class="modal"></div>

    <div class="dashboard-grid">
        <aside class="sidebar">
            <div class="logo shiny-text">SoundWave</div>
            <nav>
                <ul>
                    <li><a href="?page=beranda" class="<?= $page == 'beranda' ? 'active' : '' ?>">Beranda</a></li>
                    <li><a href="?page=musik_saya" class="<?= $page == 'musik_saya' ? 'active' : '' ?>">Musik Saya</a></li>
                    <li><a href="?page=favorit" class="<?= $page == 'favorit' ? 'active' : '' ?>">Favorit</a></li>
                    <li><a href="?page=playlist" class="<?= $page == 'playlist' ? 'active' : '' ?>">Playlist</a></li>
                </ul>
            </nav>
            <button id="openUploadModalBtn" class="shiny-gradient-btn upload-btn-sidebar">
                <i class="fas fa-upload"></i><span>Upload Musik</span>
            </button>
        </aside>

        <main class="main-content">
            <header class="main-header">
                 <div class="search-bar-placeholder"></div>
                 <div class="profile-menu">
                     <img id="profileMenuBtn" src="assets/uploads/profiles/<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile">
                     <div id="dropdownMenu" class="dropdown-menu">
                         <a href="#" id="openEditProfileBtn">Edit Profil</a>
                         <a href="logout.php">Logout</a>
                     </div>
                 </div>
            </header>

            <div class="content-area">
                <?php
                // 3. LOGIKA ROUTING DAN MENAMPILKAN KONTEN HALAMAN
                $songs_to_display = [];
                
                switch ($page) {
                    case 'musik_saya':
                        echo "<h2>Musik Saya</h2><p>Semua lagu yang telah Anda unggah.</p>";
                        $stmt = $pdo->prepare("SELECT * FROM songs WHERE uploader_id = ? ORDER BY upload_date DESC");
                        $stmt->execute([$user_id]);
                        $songs_to_display = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        break;

                    case 'favorit':
                        echo "<h2>Lagu Favorit</h2><p>Koleksi lagu yang Anda sukai.</p>";
                        $stmt = $pdo->prepare(
                            "SELECT s.* FROM songs s 
                             JOIN favorites f ON s.id = f.song_id 
                             WHERE f.user_id = ? 
                             ORDER BY f.favorited_at DESC"
                        );
                        $stmt->execute([$user_id]);
                        $songs_to_display = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        break;
                    
                    case 'playlist':
                        // Logika untuk halaman playlist akan lebih kompleks, bisa dikembangkan lebih lanjut
                        echo "<h2>Playlist Saya</h2><p>Kelola semua playlist Anda.</p>";
                        // Di sini Anda akan menampilkan daftar $user_playlists
                        // Dan jika ada ?id=..., tampilkan lagu di dalam playlist itu.
                        break;

                    case 'beranda':
                    default:
                        echo "<h2>Beranda</h2><p>Temukan musik baru yang sedang tren.</p>";
                        $stmt = $pdo->prepare("SELECT * FROM songs WHERE status = 'published' AND (scheduled_for IS NULL OR scheduled_for <= NOW()) ORDER BY upload_date DESC");
                        $stmt->execute();
                        $songs_to_display = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        break;
                }

                // Bagian ini sekarang berlaku untuk semua halaman yang menampilkan lagu
                if (!empty($songs_to_display)) {
                    echo '<div class="song-list">';
                    foreach ($songs_to_display as $song) {
                        // Memanggil fungsi yang sudah kita buat. Jauh lebih bersih!
                        echo renderSongCard($song, $user_playlists, $favorited_song_ids, $user_id);
                    }
                    echo '</div>';
                } elseif ($page != 'playlist') { // Jangan tampilkan pesan ini di halaman utama playlist
                    echo "<p>Belum ada musik di sini.</p>";
                }
                ?>
            </div>
        </main>
    </div>
    
    <script src="assets/js/script.js"></script>
    </body>
</html>