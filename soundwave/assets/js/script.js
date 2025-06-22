document.addEventListener('DOMContentLoaded', () => {

    // ===== BAGIAN 1: FUNGSI HELPER & LOGIKA UMUM =====

    // --- Fungsi untuk membuka & menutup semua modal ---
    const openModal = (modal) => {
        if (modal) modal.style.display = 'flex';
    };
    const closeModal = (modal) => {
        if (modal) modal.style.display = 'none';
    };

    // --- Menambahkan event listener ke semua modal untuk bisa ditutup ---
    document.querySelectorAll('.modal').forEach(modal => {
        const closeBtn = modal.querySelector('.close-btn');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => closeModal(modal));
        }
        // Menutup modal jika klik di area luar konten modal
        modal.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                closeModal(modal);
            }
        });
    });

    // ===== BAGIAN 2: LOGIKA UNTUK HALAMAN index.php (LOGIN & REGISTER) =====

    const landingPage = document.getElementById('landingPage');
    const loginPage = document.getElementById('loginPage');
    const registerPage = document.getElementById('registerPage');
    const showLoginBtn = document.getElementById('showLoginBtn');
    const showRegisterBtn = document.getElementById('showRegisterBtn');
    const switchToRegisterLink = document.getElementById('switchToRegister');
    const switchToLoginLink = document.getElementById('switchToLogin');
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');

    // --- Logika untuk berpindah-pindah tampilan (landing, login, register) ---
    if (showLoginBtn) {
        showLoginBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (landingPage) landingPage.style.display = 'none';
            if (registerPage) registerPage.style.display = 'none';
            if (loginPage) loginPage.style.display = 'flex';
        });
    }
    if (showRegisterBtn) {
        showRegisterBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (landingPage) landingPage.style.display = 'none';
            if (loginPage) loginPage.style.display = 'none';
            if (registerPage) registerPage.style.display = 'flex';
        });
    }
    if (switchToRegisterLink) {
        switchToRegisterLink.addEventListener('click', (e) => {
            e.preventDefault();
            if (loginPage) loginPage.style.display = 'none';
            if (registerPage) registerPage.style.display = 'flex';
        });
    }
    if (switchToLoginLink) {
        switchToLoginLink.addEventListener('click', (e) => {
            e.preventDefault();
            if (registerPage) registerPage.style.display = 'none';
            if (loginPage) loginPage.style.display = 'flex';
        });
    }

    // --- Penanganan Form Login dengan AJAX ---
    if (loginForm) {
        const loginErrorMessage = document.getElementById('loginErrorMessage');
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            loginErrorMessage.style.display = 'none';
            const formData = new FormData(loginForm);
            const response = await fetch('process/process_login.php', { method: 'POST', body: formData });
            const data = await response.json();
            if (data.success) {
                window.location.href = data.redirect_url;
            } else {
                loginErrorMessage.textContent = data.message;
                loginErrorMessage.style.display = 'block';
            }
        });
    }

    // --- Penanganan Form Register dengan AJAX ---
    if (registerForm) {
        const registerErrorMessage = document.getElementById('registerErrorMessage');
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            registerErrorMessage.style.display = 'none';
            const formData = new FormData(registerForm);
            const response = await fetch('process/process_register.php', { method: 'POST', body: formData });
            const data = await response.json();
            if (data.success) {
                window.location.href = data.redirect_url;
            } else {
                registerErrorMessage.textContent = data.message;
                registerErrorMessage.style.display = 'block';
            }
        });
    }


    // ===== BAGIAN 3: LOGIKA UNTUK HALAMAN dashboard.php =====

    // --- Menu Dropdown Profil ---
    const profileMenuBtn = document.getElementById('profileMenuBtn');
    const dropdownMenu = document.getElementById('dropdownMenu');
    if (profileMenuBtn) {
        profileMenuBtn.addEventListener('click', () => {
            dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
        });
        // Menutup dropdown jika klik di luar
        window.addEventListener('click', (e) => {
            if (dropdownMenu && !profileMenuBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.style.display = 'none';
            }
        });
    }

    // --- Tombol-tombol untuk membuka modal ---
    const openUploadModalBtn = document.getElementById('openUploadModalBtn');
    if (openUploadModalBtn) {
        openUploadModalBtn.addEventListener('click', () => openModal(document.getElementById('uploadModal')));
    }
    const openEditProfileBtn = document.getElementById('openEditProfileBtn');
    if (openEditProfileBtn) {
        openEditProfileBtn.addEventListener('click', (e) => {
            e.preventDefault();
            openModal(document.getElementById('editProfileModal'));
            if(dropdownMenu) dropdownMenu.style.display = 'none';
        });
    }

    // --- Penanganan Form Unggah Lagu dengan AJAX ---
    const uploadSongForm = document.getElementById('uploadSongForm');
    if (uploadSongForm) {
        const uploadErrorMessage = document.getElementById('uploadErrorMessage');
        uploadSongForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            uploadErrorMessage.style.display = 'none';
            const submitButton = uploadSongForm.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.textContent;
            submitButton.disabled = true;
            submitButton.textContent = 'Mengunggah...';

            const formData = new FormData(uploadSongForm);
            try {
                const response = await fetch('process/upload_song.php', { method: 'POST', body: formData });
                const data = await response.json();
                alert(data.message);
                if (data.success) {
                    location.reload(); // Reload untuk melihat lagu baru
                } else {
                    uploadErrorMessage.textContent = data.message;
                    uploadErrorMessage.style.display = 'block';
                }
            } catch (error) {
                console.error('Fetch Error:', error);
                uploadErrorMessage.textContent = 'Terjadi kesalahan koneksi.';
                uploadErrorMessage.style.display = 'block';
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = originalButtonText;
            }
        });
    }
    
    // --- EVENT DELEGATION: Satu event listener untuk semua aksi dinamis ---
    document.body.addEventListener('click', async function(e) {
        // --- Aksi: Toggle Suka/Favorit ---
        const favoriteBtn = e.target.closest('.favorite-btn');
        if (favoriteBtn) {
            const songId = favoriteBtn.dataset.songId;
            const heartIcon = favoriteBtn.querySelector('i');
            const formData = new FormData();
            formData.append('action', 'toggle_favorite');
            formData.append('song_id', songId);

            const response = await fetch('process/manage_song_interaction.php', { method: 'POST', body: formData });
            const data = await response.json();
            if (data.success) {
                if (data.status === 'liked') {
                    heartIcon.classList.replace('far', 'fas');
                    favoriteBtn.classList.add('active');
                } else {
                    heartIcon.classList.replace('fas', 'far');
                    favoriteBtn.classList.remove('active');
                }
            } else {
                alert('Error: ' + data.message);
            }
        }
        
        // --- Aksi: Tambah Lagu ke Playlist ---
        const addToPlaylistLink = e.target.closest('.add-to-playlist-link');
        if (addToPlaylistLink) {
            e.preventDefault();
            const songId = addToPlaylistLink.dataset.songId;
            const playlistId = addToPlaylistLink.dataset.playlistId;
            const formData = new FormData();
            formData.append('action', 'add_to_playlist');
            formData.append('song_id', songId);
            formData.append('playlist_id', playlistId);
            
            const response = await fetch('process/manage_song_interaction.php', { method: 'POST', body: formData });
            const data = await response.json();
            alert(data.message); // Pesan singkat sudah cukup di sini
            // Tutup dropdown setelah memilih
            const dropdown = addToPlaylistLink.closest('.options-dropdown');
            if (dropdown) dropdown.classList.remove('show');
        }

        // --- Aksi: Hapus Lagu ---
        const deleteSongLink = e.target.closest('.delete-song-link');
        if (deleteSongLink) {
            e.preventDefault();
            if (confirm('Anda yakin ingin menghapus lagu ini secara permanen?')) {
                const songId = deleteSongLink.dataset.songId;
                const formData = new FormData();
                formData.append('song_id', songId);

                const response = await fetch('process/delete_song.php', { method: 'POST', body: formData });
                const data = await response.json();
                alert(data.message);
                if (data.success) {
                    // Hapus kartu lagu dari tampilan tanpa reload
                    deleteSongLink.closest('.song-card').remove();
                }
            }
        }

        // --- Aksi: Buka Modal Edit Lagu ---
        const editSongLink = e.target.closest('.edit-song-link');
        if (editSongLink) {
            e.preventDefault();
            const songData = JSON.parse(editSongLink.dataset.songInfo);
            // Anda perlu membuat modal edit lagu di HTML dengan ID-ID ini
            document.getElementById('editSongId').value = songData.id;
            document.getElementById('editSongTitle').value = songData.title;
            document.getElementById('editSongArtist').value = songData.artist;
            openModal(document.getElementById('editSongModal'));
        }
        
        // --- Aksi: Menampilkan/Menyembunyikan Dropdown Opsi Lagu ---
        const moreOptionsBtn = e.target.closest('.more-options-btn');
        // Tutup semua dropdown lain yang mungkin terbuka
        document.querySelectorAll('.options-dropdown.show').forEach(dropdown => {
            if (!dropdown.parentElement.contains(moreOptionsBtn)) {
                dropdown.classList.remove('show');
            }
        });
        // Toggle dropdown yang diklik
        if (moreOptionsBtn) {
            const dropdown = moreOptionsBtn.nextElementSibling;
            if (dropdown) dropdown.classList.toggle('show');
        } else if (!e.target.closest('.more-options')) {
            // Tutup semua dropdown jika klik di luar area ".more-options"
            document.querySelectorAll('.options-dropdown.show').forEach(d => d.classList.remove('show'));
        }
    });


    // ===== BAGIAN 4: LOGIKA PEMUTAR MUSIK (PLAYER) =====
    const audioPlayer = document.getElementById('audio-player');
    if (audioPlayer) {
        const playPauseBtn = document.getElementById('play-pause-btn');
        const playPauseIcon = playPauseBtn ? playPauseBtn.querySelector('i') : null;
        const playerArtwork = document.getElementById('player-artwork');
        const playerTitle = document.getElementById('player-title');
        const playerArtist = document.getElementById('player-artist');
        const progressBar = document.querySelector('.progress-bar');
        const progressWrapper = document.querySelector('.progress-bar-wrapper');
        const currentTimeEl = document.getElementById('current-time');
        const durationEl = document.getElementById('duration');
        let currentSongCard = null;

        const formatTime = (seconds) => {
            if (isNaN(seconds) || seconds < 0) return "0:00";
            const minutes = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return `${minutes}:${secs < 10 ? '0' : ''}${secs}`;
        };

        // Event listener untuk memutar lagu dari kartu lagu (menggunakan event delegation)
        document.querySelector('.song-list')?.addEventListener('click', (e) => {
            const card = e.target.closest('.song-card');
            if (!card || e.target.closest('.song-interactions')) {
                return; // Jangan lakukan apa-apa jika klik bukan di kartu atau di area tombol interaksi
            }
            
            if (currentSongCard) currentSongCard.classList.remove('playing');
            currentSongCard = card;
            currentSongCard.classList.add('playing');

            audioPlayer.src = card.dataset.src;
            if(playerArtwork) playerArtwork.src = card.dataset.artwork;
            if(playerTitle) playerTitle.textContent = card.dataset.title;
            if(playerArtist) playerArtist.textContent = card.dataset.artist;
            
            audioPlayer.play();
        });

        // Kontrol pemutar
        if(playPauseBtn) playPauseBtn.addEventListener('click', () => {
            if (!audioPlayer.src) return; // Jangan lakukan apa-apa jika belum ada lagu
            audioPlayer.paused ? audioPlayer.play() : audioPlayer.pause();
        });
        
        audioPlayer.addEventListener('play', () => playPauseIcon?.classList.replace('fa-play', 'fa-pause'));
        audioPlayer.addEventListener('pause', () => playPauseIcon?.classList.replace('fa-pause', 'fa-play'));
        audioPlayer.addEventListener('loadedmetadata', () => {
            if(durationEl) durationEl.textContent = formatTime(audioPlayer.duration);
        });
        audioPlayer.addEventListener('timeupdate', () => {
            const { currentTime, duration } = audioPlayer;
            if (duration) {
                if(progressBar) progressBar.style.width = `${(currentTime / duration) * 100}%`;
                if(currentTimeEl) currentTimeEl.textContent = formatTime(currentTime);
            }
        });

        // Kontrol progress bar dengan klik
        if(progressWrapper) progressWrapper.addEventListener('click', (e) => {
            const { duration } = audioPlayer;
            if (duration) {
                audioPlayer.currentTime = (e.offsetX / progressWrapper.offsetWidth) * duration;
            }
        });
    }
});