<?php
session_start();
require 'config.php';
require 'logger.php';

$pin_rahasia = "111080"; // PIN Admin khusus CEO Avelza
$pesan = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pin_input = $_POST['pin'];
    if ($pin_input === $pin_rahasia) {
        $_SESSION['admin_logged_in'] = true;
        catat_log($conn, "Admin berhasil Login menggunakan PIN Rahasia");
        header("Location: peserta.php");
        exit();
    } else {
        $pesan = "<div class='alert error'>❌ PIN Salah! Akses ditolak.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Rahasia - Avelza Group</title>
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#cca300">
    <link rel="apple-touch-icon" href="logo_m.png">
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('sw.js');
        }
    </script>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <a href="peserta.php" class="back-link">🔙 Kembali ke Daftar Peserta</a>
        <div class="card">
            <h2 style="color: var(--primary-dark); margin-bottom: 5px;">Pintu Rahasia Admin</h2>
            <p style="color: #666; margin-bottom: 20px;">Masukkan PIN Rahasia Anda untuk mengelola data warga.</p>
            
            <?php echo $pesan; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>PIN Rahasia:</label>
                    <input type="password" name="pin" required placeholder="Masukkan PIN Anda">
                </div>
                <button type="submit" class="btn btn-primary btn-block">Buka Kunci Keamanan</button>
            </form>
        </div>
    </div>
<script src="sound.js"></script><script src="screensaver.js"></script></body>
</html>
