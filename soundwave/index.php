<?php
session_start();
// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang di SoundWave</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    
    <link href="https://fonts.googleapis.com/css2?family=Audiowide&family=Major+Mono+Display&family=Monoton&family=Outfit:wght@700&family=Russo+One&family=Syncopate:wght@700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <video autoplay muted loop class="video-background">
        <source src="assets/videos/background.mp4" type="video/mp4">
    </video>

    <div id="landingPage" class="landing-container">
    <h1>SOUNDWAVE</h1>
    <p>Nikmati pengalaman mendengar musik tanpa iklan dengan kualitas tinggi dan bersih</p>
    
    <div class="landing-actions">
        <a href="#" id="showRegisterBtn" class="shiny-gradient-btn">Daftar Akun</a>
        <a href="#" id="showLoginBtn" class="shiny-gradient-btn">Masuk</a>
    </div>
</div>

    <div id="loginPage" class="form-container">
        <div class="form-box">
            <h2>Masuk ke SoundWave</h2>
            <form action="process/process_login.php" method="POST">
                <div class="input-group">
                    <label for="login-username">Username atau Email</label>
                    <input type="text" id="login-username" name="username" required>
                </div>
                <div class="input-group">
                    <label for="login-password">Password</label>
                    <input type="password" id="login-password" name="password" required>
                </div>
                <button type="submit" class="shiny-gradient-btn">Masuk</button>
            </form>
            <p class="switch-form">Belum punya akun? <a id="switchToRegister">Daftar di sini</a></p>
        </div>
    </div>

    <div id="registerPage" class="form-container">
        <div class="form-box">
            <h2>Buat Akun Baru</h2>
            <form action="process/process_register.php" method="POST">
                <div class="input-group">
                    <label for="reg-fullname">Nama Lengkap</label>
                    <input type="text" id="reg-fullname" name="full_name" required>
                </div>
                <div class="input-group">
                    <label for="reg-username">Username</label>
                    <input type="text" id="reg-username" name="username" required>
                </div>
                <div class="input-group">
                    <label for="reg-email">Email</label>
                    <input type="email" id="reg-email" name="email" required>
                </div>
                <div class="input-group">
                    <label for="reg-password">Password</label>
                    <input type="password" id="reg-password" name="password" required>
                </div>
                <button type="submit" class="shiny-gradient-btn">Daftar</button>
            </form>
            <p class="switch-form">Sudah punya akun? <a id="switchToLogin">Masuk di sini</a></p>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html>