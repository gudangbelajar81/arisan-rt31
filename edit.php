<?php
session_start();
require 'config.php';
require 'logger.php';

if (!isset($_SESSION['admin_arisan']) || $_SESSION['admin_arisan'] !== true) {
    die("Akses Ditolak! Anda bukan Admin Arisan.");
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
    $hari_ronda = !empty($_POST['hari_ronda']) ? $_POST['hari_ronda'] : NULL;
    
    $stmt = $conn->prepare("UPDATE peserta SET nama=?, no_wa=?, hari_ronda=? WHERE id=?");
    $stmt->bind_param("sssi", $nama, $no_wa, $hari_ronda, $id);
    
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
                <div class="form-group" style="margin-bottom: 25px;">
                    <label>Piket Ronda Malam:</label>
                    <select name="hari_ronda" style="width: 100%; padding: 12px 15px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 1rem; margin-top: 5px; background: rgba(255,255,255,0.8);">
                        <option value="">-- Belum Ada Jadwal --</option>
                        <?php 
                        $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                        foreach($hari as $h) {
                            $selected = (isset($data['hari_ronda']) && $data['hari_ronda'] == $h) ? "selected" : "";
                            echo "<option value='$h' $selected>$h</option>";
                        }
                        ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Simpan Perubahan</button>
            </form>
        </div>
    </div>
<script src="sound.js"></script><script src="screensaver.js"></script></body>
</html>
