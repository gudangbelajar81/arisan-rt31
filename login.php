<?php
session_start();
require 'config.php';
require 'logger.php';

$modul = isset($_GET['modul']) ? $_GET['modul'] : 'arisan';

$title = "Admin Arisan";
$target_pin = $pin_arisan;
$redirect = "peserta.php";

if ($modul === 'ronda') {
    $title = "Admin Siskamling";
    $target_pin = $pin_ronda;
    $redirect = "ronda.php";
} elseif ($modul === 'pertemuan') {
    $title = "Admin Tuan Rumah";
    $target_pin = $pin_pertemuan;
    $redirect = "pertemuan.php";
} elseif ($modul === 'master') {
    $title = "Admin Master";
    $target_pin = $pin_master;
    $redirect = "pengaturan.php";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pin_input = $_POST['pin'];
    if ($pin_input === $target_pin) {
        $_SESSION['admin_' . $modul] = true;
        catat_log($conn, "Login sukses untuk modul $modul");
        header("Location: $redirect");
        exit;
    } elseif ($pin_input === $pin_master) {
        $_SESSION['admin_arisan'] = true;
        $_SESSION['admin_ronda'] = true;
        $_SESSION['admin_pertemuan'] = true;
        $_SESSION['admin_master'] = true;
        catat_log($conn, "Login Master sukses (akses penuh)");
        header("Location: $redirect");
        exit;
    } else {
        $error = "PIN salah! Silakan coba lagi.";
        catat_log($conn, "Gagal login modul $modul");
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login <?php echo $title; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="apple-touch-icon" href="logo_m.png">
</head>
<body style="display: flex; justify-content: center; align-items: center; height: 100vh; background: #f0f2f5;">
    <div class="card" style="width: 100%; max-width: 400px; text-align: center;">
        <h2 style="color: var(--primary-dark);">🔒 Login <?php echo $title; ?></h2>
        <p style="color: #666; margin-bottom: 20px;">Masukkan PIN khusus untuk mengakses kendali <?php echo ucfirst($modul); ?>.</p>
        
        <?php if(isset($error)) echo "<div class='alert' style='background: #fee2e2; color: #ef4444; padding: 10px; border-radius: 8px; margin-bottom: 15px;'>$error</div>"; ?>
        
        <form method="POST" action="">
            <input type="password" name="pin" placeholder="Masukkan 6 Digit PIN" required style="width: 100%; padding: 15px; font-size: 1.5rem; text-align: center; letter-spacing: 5px; border: 2px solid #ddd; border-radius: 10px; margin-bottom: 20px;">
            <button type="submit" class="btn btn-primary" style="width: 100%;">Buka Gembok</button>
        </form>
        <br>
        <a href="<?php echo $redirect; ?>" style="color: #888; text-decoration: none; font-size: 0.9rem;">🔙 Kembali ke Halaman Sebelumnya</a>
    </div>
<script src="sound.js"></script><script src="screensaver.js"></script></body>
</html>
