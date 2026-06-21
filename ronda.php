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
        $stmt = $conn->prepare("UPDATE peserta SET hari_ronda = NULL WHERE id = ?");
        $stmt->bind_param("i", $id_remove);
        if ($stmt->execute()) {
            catat_log($conn, "Admin menghapus peserta ID $id_remove dari jadwal ronda");
            header("Location: ronda.php?msg=removed");
            exit;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
        $nama_add = ucwords(strtolower(trim($_POST['nama_peserta'])));
        $hari_add = $_POST['hari'];
        
        // Cari apakah nama sudah ada di database
        $stmt = $conn->prepare("SELECT id FROM peserta WHERE nama = ? AND is_deleted = 0 LIMIT 1");
        $stmt->bind_param("s", $nama_add);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res->num_rows > 0) {
            // Warga sudah terdaftar, update hari rondanya
            $row = $res->fetch_assoc();
            $id_add = $row['id'];
            $stmt2 = $conn->prepare("UPDATE peserta SET hari_ronda = ? WHERE id = ?");
            $stmt2->bind_param("si", $hari_add, $id_add);
            $stmt2->execute();
        } else {
            // Warga baru (tidak ada di daftar arisan), masukkan ke database sebagai peserta khusus ronda
            $no_wa_dummy = "-";
            $stmt2 = $conn->prepare("INSERT INTO peserta (nama, no_wa, hari_ronda) VALUES (?, ?, ?)");
            $stmt2->bind_param("sss", $nama_add, $no_wa_dummy, $hari_add);
            $stmt2->execute();
        }
        
        catat_log($conn, "Admin merekrut $nama_add ke piket $hari_add");
        header("Location: ronda.php?msg=added");
        exit;
    }
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'removed') $pesan = "<div class='alert success'>✅ Petugas berhasil dihapus dari jadwal.</div>";
    if ($_GET['msg'] == 'added') $pesan = "<div class='alert success'>✅ Petugas berhasil ditambahkan ke jadwal.</div>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dasbor Siskamling RT 31</title>
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
        .grid-ronda {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .card-ronda {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 20px;
            box-shadow: var(--shadow-soft);
            border-top: 5px solid #cca300;
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        .card-ronda:hover {
            transform: translateY(-5px);
        }
        .hari-title {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--primary-dark);
            margin-bottom: 15px;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .person-item {
            padding: 10px 0;
            border-bottom: 1px dashed #eee;
            font-size: 0.95rem;
            color: #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .person-item:last-child {
            border-bottom: none;
        }
        .badge-count {
            background: #1e293b;
            color: #cca300;
            font-size: 0.75rem;
            padding: 4px 10px;
            border-radius: 12px;
        }
        .btn-hapus {
            color: #ef4444;
            text-decoration: none;
            background: rgba(239, 68, 68, 0.1);
            padding: 2px 6px;
            border-radius: 6px;
            font-size: 0.8rem;
            transition: all 0.2s;
        }
        .btn-hapus:hover {
            background: #ef4444;
            color: white;
        }
        .form-add {
            margin-top: auto;
            padding-top: 15px;
            border-top: 1px solid #eee;
            display: flex;
            gap: 5px;
        }
        .form-add input[type="text"] {
            flex: 1;
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 0.9rem;
            background: rgba(255,255,255,0.8);
            outline: none;
            transition: border 0.3s;
        }
        .form-add input[type="text"]:focus {
            border: 1px solid #cca300;
        }
        .form-add button {
            background: #1e293b;
            color: #cca300;
            border: none;
            border-radius: 8px;
            padding: 0 12px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.2s;
        }
        .form-add button:hover {
            background: #0f172a;
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 1000px;">
        <a href="index.php" class="back-link">🔙 Kembali ke Menu Awal</a>
        <div class="card" style="margin-bottom: 20px;">
            <h2 style="color: var(--primary-dark); margin-bottom: 5px;"><span class="shine-text">🛡️ Dasbor Siskamling</span></h2>
            <p style="color: #666;">Daftar piket keamanan lingkungan RT 31 dari hari Senin hingga Minggu.</p>
            <?php if ($is_admin) echo "<p style='color: #ef4444; font-size: 0.85rem; font-weight: bold; margin-top: 5px;'>🔓 Mode Admin Aktif: Anda dapat menambah atau menghapus petugas.</p>"; ?>
        </div>
        
        <?php echo $pesan; ?>
        
        <div class="grid-ronda">
            <?php
            $hari_list = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
            
            $sql = "SELECT id, nama, hari_ronda FROM peserta WHERE is_deleted = 0 ORDER BY nama ASC";
            $result = $conn->query($sql);
            
            $jadwal = [];
            foreach ($hari_list as $h) {
                $jadwal[$h] = [];
            }
            $unassigned = [];
            
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $h = $row['hari_ronda'];
                    if (!empty($h) && isset($jadwal[$h])) {
                        $jadwal[$h][] = $row;
                    } else {
                        $unassigned[] = $row;
                    }
                }
            }
            
            $delay = 0.1;
            foreach ($hari_list as $hari) {
                $orang = $jadwal[$hari];
                $jumlah = count($orang);
                
                echo "<div class='card-ronda animated-row' style='animation-delay: {$delay}s;'>";
                echo "<div>"; // Wrapper untuk bagian atas kartu
                echo "<div class='hari-title'>$hari <span class='badge-count'>$jumlah Orang</span></div>";
                
                if ($jumlah > 0) {
                    foreach ($orang as $p) {
                        echo "<div class='person-item'>";
                        echo "<span>💂‍♂️ " . htmlspecialchars($p['nama']) . "</span>";
                        if ($is_admin) {
                            echo "<a href='ronda.php?action=remove&id={$p['id']}' class='btn-hapus' title='Hapus dari jadwal' onclick='return confirm(\"Coret {$p['nama']} dari hari {$hari}?\")'>❌</a>";
                        }
                        echo "</div>";
                    }
                } else {
                    echo "<div class='person-item' style='color: #aaa; font-style: italic; border:none;'>Belum ada petugas</div>";
                }
                echo "</div>"; // Tutup wrapper atas
                
                // Form Tambah Warga (Hanya Admin)
                if ($is_admin) {
                    echo "<form method='POST' action='ronda.php' class='form-add'>";
                    echo "<input type='hidden' name='action' value='add'>";
                    echo "<input type='hidden' name='hari' value='$hari'>";
                    echo "<input type='text' name='nama_peserta' list='warga-list' placeholder='Ketik nama warga...' required autocomplete='off'>";
                    echo "<button type='submit' title='Tambah ke jadwal'>➕</button>";
                    echo "</form>";
                }
                
                echo "</div>"; // Tutup card
                $delay += 0.1;
            }
            ?>
        </div>
        
        <?php if ($is_admin): ?>
        <datalist id="warga-list">
            <?php foreach ($unassigned as $u) {
                echo "<option value=\"" . htmlspecialchars($u['nama']) . "\">";
            } ?>
        </datalist>
        <?php endif; ?>
    </div>
<script src="sound.js"></script>
<script src="screensaver.js"></script>
</body>
</html>
