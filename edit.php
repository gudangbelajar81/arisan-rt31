<?php
session_start();
require 'config.php';
require 'logger.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    die("Akses Ditolak! Anda bukan Admin.");
}

if (!isset($_GET['id'])) {
    header("Location: peserta.php");
    exit();
}

$id = $_GET['id'];
$pesan = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = ucwords(strtolower(trim($_POST['nama'])));
    $no_wa = $_POST['no_wa'];
    
    $stmt = $conn->prepare("UPDATE peserta SET nama=?, no_wa=? WHERE id=?");
    $stmt->bind_param("ssi", $nama, $no_wa, $id);
    
    if ($stmt->execute()) {
        catat_log($conn, "Admin mengedit profil peserta ID: $id ($nama)");
        $pesan = "<div class='alert success'>✅ Data berhasil diperbarui! <a href='peserta.php'>Kembali ke daftar.</a></div>";
    } else {
        $pesan = "<div class='alert error'>❌ Gagal memperbarui data.</div>";
    }
    $stmt->close();
}

// Ambil data saat ini
$stmt = $conn->prepare("SELECT * FROM peserta WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();

if (!$data) {
    die("Data tidak ditemukan.");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Peserta</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <a href="peserta.php" class="back-link">🔙 Kembali ke Daftar Peserta</a>
        <div class="card">
            <h2 style="color: var(--primary-dark); margin-bottom: 5px;">Edit Data Warga</h2>
            
            <?php echo $pesan; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Nama Lengkap:</label>
                    <input type="text" name="nama" value="<?php echo htmlspecialchars($data['nama']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Nomor WhatsApp:</label>
                    <input type="number" name="no_wa" value="<?php echo htmlspecialchars($data['no_wa']); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Simpan Perubahan</button>
            </form>
        </div>
    </div>
<script src="sound.js"></script><script src="screensaver.js"></script></body>
</html>
