<?php
require 'config.php';

$pesan = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Format nama agar selalu diawali huruf besar setiap kata (Title Case)
    $nama = ucwords(strtolower(trim($_POST['nama'])));
    $no_wa = $_POST['no_wa'];
    $blok = ""; // Dikosongkan agar tidak perlu merombak struktur database
    
    // Simpan ke database
    $stmt = $conn->prepare("INSERT INTO peserta (nama, no_wa, blok_rumah) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nama, $no_wa, $blok);
    
    if ($stmt->execute()) {
        $pesan = "<div class='alert success'>✅ Mantap! Data Bapak/Ibu <strong>$nama</strong> sudah berhasil dicatat di buku arisan.</div>";
    } else {
        $pesan = "<div class='alert error'>❌ Waduh, ada yang salah: " . $stmt->error . "</div>";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Arisan RT 31</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-link">🔙 Kembali ke Menu Awal</a>
        <div class="card">
            <h2 style="color: var(--primary-dark); margin-bottom: 5px;">Pendaftaran Arisan</h2>
            <p style="color: #666; margin-bottom: 20px;">Silakan isi data diri dengan lengkap.</p>
            
            <?php echo $pesan; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Nama Lengkap:</label>
                    <input type="text" name="nama" required placeholder="Contoh: Pak Budi Santoso">
                </div>
                <div class="form-group">
                    <label>Nomor WhatsApp (Aktif):</label>
                    <input type="number" name="no_wa" required placeholder="Contoh: 08123456789">
                </div>
                <button type="submit" class="btn btn-primary btn-block">Kirim Data Saya</button>
            </form>
        </div>
    </div>
<script src="sound.js"></script><script src="screensaver.js"></script></body>
</html>
