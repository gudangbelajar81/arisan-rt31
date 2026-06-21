<?php 
session_start();
require 'config.php'; 
require 'logger.php';

$is_admin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$pesan = "";

// Tangani aksi Admin
if ($is_admin) {
    if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
        $id_remove = (int)$_GET['id'];
        $stmt = $conn->prepare("DELETE FROM pertemuan_rutin WHERE id = ?");
        $stmt->bind_param("i", $id_remove);
        if ($stmt->execute()) {
            catat_log($conn, "Admin membatalkan pertemuan ID $id_remove");
            header("Location: pertemuan.php?msg=removed");
            exit;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
        $tanggal_waktu = $_POST['tanggal_waktu'];
        $lokasi = trim($_POST['lokasi']);
        $agenda = trim($_POST['agenda']);
        
        $stmt = $conn->prepare("INSERT INTO pertemuan_rutin (tanggal_waktu, lokasi, agenda) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $tanggal_waktu, $lokasi, $agenda);
        if ($stmt->execute()) {
            catat_log($conn, "Admin membuat pengumuman pertemuan baru di $lokasi");
            header("Location: pertemuan.php?msg=added");
            exit;
        }
    }
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'removed') $pesan = "<div class='alert success'>✅ Jadwal pertemuan berhasil dibatalkan.</div>";
    if ($_GET['msg'] == 'added') $pesan = "<div class='alert success'>✅ Jadwal pertemuan berhasil ditambahkan.</div>";
}

// Ambil data pertemuan yang akan datang (urut dari yang terdekat)
// Menggunakan CURDATE() - INTERVAL 1 DAY agar acara hari ini tidak langsung hilang sebelum malam
$sql = "SELECT * FROM pertemuan_rutin WHERE tanggal_waktu >= (CURDATE() - INTERVAL 1 DAY) ORDER BY tanggal_waktu ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Pertemuan RT 31</title>
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#cca300">
    <link rel="apple-touch-icon" href="logo_m.png">
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('sw.js');
        }
    </script>
    <link rel="stylesheet" href="style.css">
    <style>
        .timeline {
            position: relative;
            max-width: 800px;
            margin: 30px auto;
        }
        .timeline-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 20px;
            box-shadow: var(--shadow-soft);
            border-left: 5px solid #cca300;
            margin-bottom: 20px;
            transition: transform 0.3s ease;
            position: relative;
        }
        .timeline-card:hover {
            transform: translateX(5px);
        }
        .tgl-badge {
            background: #1e293b;
            color: #cca300;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }
        .lokasi {
            font-size: 1.1rem;
            font-weight: bold;
            color: var(--primary-dark);
            margin-bottom: 5px;
        }
        .agenda {
            color: #555;
            line-height: 1.5;
            font-size: 0.95rem;
        }
        .btn-batal {
            position: absolute;
            top: 20px;
            right: 20px;
            color: #ef4444;
            text-decoration: none;
            background: rgba(239, 68, 68, 0.1);
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 0.85rem;
            transition: all 0.2s;
        }
        .btn-batal:hover {
            background: #ef4444;
            color: white;
        }
        .form-add-rapat {
            background: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 15px;
            border: 2px dashed #cca300;
            margin-bottom: 30px;
        }
        .form-add-rapat input, .form-add-rapat textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-family: inherit;
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 900px;">
        <a href="index.php" class="back-link">🔙 Kembali ke Menu Awal</a>
        <div class="card" style="margin-bottom: 20px;">
            <h2 style="color: var(--primary-dark); margin-bottom: 5px;"><span class="shine-text">📅 Jadwal Pertemuan Rutin</span></h2>
            <p style="color: #666;">Informasi rapat, kumpulan, dan acara lingkungan RT 31.</p>
            <?php if ($is_admin) echo "<p style='color: #ef4444; font-size: 0.85rem; font-weight: bold; margin-top: 5px;'>🔓 Mode Admin: Anda dapat membuat atau membatalkan pengumuman rapat.</p>"; ?>
        </div>
        
        <?php echo $pesan; ?>

        <?php if ($is_admin): ?>
        <div class="form-add-rapat">
            <h3 style="margin-bottom: 15px; color: #1e293b;">➕ Buat Pengumuman Baru</h3>
            <form method="POST" action="pertemuan.php">
                <input type="hidden" name="action" value="add">
                <label>Tanggal & Jam:</label>
                <input type="datetime-local" name="tanggal_waktu" required>
                <label>Lokasi / Tempat:</label>
                <input type="text" name="lokasi" placeholder="Contoh: Rumah Pak RT / Pos Kamling" required>
                <label>Agenda / Pembahasan:</label>
                <textarea name="agenda" rows="3" placeholder="Contoh: Membahas persiapan lomba 17 Agustus..." required></textarea>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Terbitkan Pengumuman</button>
            </form>
        </div>
        <?php endif; ?>
        
        <div class="timeline">
            <?php
            if ($result && $result->num_rows > 0) {
                $delay = 0.1;
                while($row = $result->fetch_assoc()) {
                    $tgl = date('d F Y - H:i', strtotime($row['tanggal_waktu'])) . " WIB";
                    echo "<div class='timeline-card animated-row' style='animation-delay: {$delay}s;'>";
                    echo "<div class='tgl-badge'>🕒 $tgl</div>";
                    echo "<div class='lokasi'>📍 " . htmlspecialchars($row['lokasi']) . "</div>";
                    echo "<div class='agenda'>" . nl2br(htmlspecialchars($row['agenda'])) . "</div>";
                    
                    if ($is_admin) {
                        echo "<a href='pertemuan.php?action=remove&id={$row['id']}' class='btn-batal' onclick='return confirm(\"Batalkan dan hapus jadwal ini?\")'>❌ Batalkan</a>";
                    }
                    echo "</div>";
                    $delay += 0.1;
                }
            } else {
                echo "<div class='card' style='text-align:center; padding: 40px;'><h3 style='color:#888;'>Belum ada jadwal pertemuan dalam waktu dekat.</h3></div>";
            }
            ?>
        </div>
    </div>
<script src="sound.js"></script>
<script src="screensaver.js"></script>
</body>
</html>
