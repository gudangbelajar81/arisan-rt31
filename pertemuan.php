<?php 
session_start();
require 'config.php'; 
require 'logger.php';

$is_admin = isset($_SESSION['admin_pertemuan']) && $_SESSION['admin_pertemuan'] === true;
$pesan = "";

// Tangani aksi Admin
if ($is_admin) {
    if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
        $id_remove = (int)$_GET['id'];
        $stmt = $conn->prepare("UPDATE peserta SET bulan_pertemuan = NULL WHERE id = ?");
        $stmt->bind_param("i", $id_remove);
        if ($stmt->execute()) {
            catat_log($conn, "Admin menghapus tuan rumah ID $id_remove dari jadwal pertemuan");
            header("Location: pertemuan.php?msg=removed");
            exit;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
        $nama_add = ucwords(strtolower(trim($_POST['nama_peserta'])));
        $bulan_add = $_POST['bulan'];
        
        // Cari apakah nama sudah ada di database
        $stmt = $conn->prepare("SELECT id FROM peserta WHERE nama = ? AND is_deleted = 0 LIMIT 1");
        $stmt->bind_param("s", $nama_add);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $id_add = $row['id'];
            $stmt2 = $conn->prepare("UPDATE peserta SET bulan_pertemuan = ? WHERE id = ?");
            $stmt2->bind_param("si", $bulan_add, $id_add);
            $stmt2->execute();
        } else {
            // Warga baru (tidak ada di daftar arisan)
            $no_wa_dummy = "-";
            $stmt2 = $conn->prepare("INSERT INTO peserta (nama, no_wa, bulan_pertemuan) VALUES (?, ?, ?)");
            $stmt2->bind_param("sss", $nama_add, $no_wa_dummy, $bulan_add);
            $stmt2->execute();
        }
        
        catat_log($conn, "Admin menugaskan $nama_add sebagai tuan rumah bulan $bulan_add");
        header("Location: pertemuan.php?msg=added");
        exit;
    }
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'removed') $pesan = "<div class='alert success' style='background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe;'>✅ Petugas berhasil dihapus dari jadwal bulan tersebut.</div>";
    if ($_GET['msg'] == 'added') $pesan = "<div class='alert success' style='background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe;'>✅ Tuan rumah berhasil ditambahkan ke jadwal.</div>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Tuan Rumah Pertemuan RT 31</title>
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#2563eb">
    <link rel="apple-touch-icon" href="logo_m.png">
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('sw.js');
        }
    </script>
    <link rel="stylesheet" href="style.css">
    <style>
        .grid-pertemuan {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .card-pertemuan {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.1);
            border-top: 5px solid #2563eb;
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        .card-pertemuan:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.15);
        }
        .bulan-title {
            font-size: 1.2rem;
            font-weight: bold;
            color: #1e3a8a;
            margin-bottom: 15px;
            border-bottom: 2px solid #e0e7ff;
            padding-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .person-item {
            padding: 10px 0;
            border-bottom: 1px dashed #e0e7ff;
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
            background: #dbeafe;
            color: #1d4ed8;
            font-size: 0.75rem;
            padding: 4px 10px;
            border-radius: 12px;
            font-weight: bold;
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
            border-top: 1px solid #e0e7ff;
            display: flex;
            gap: 5px;
        }
        .form-add input[type="text"] {
            flex: 1;
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid #93c5fd;
            font-size: 0.9rem;
            background: rgba(255,255,255,0.8);
            outline: none;
            transition: border 0.3s;
        }
        .form-add input[type="text"]:focus {
            border: 1px solid #2563eb;
        }
        .form-add button {
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0 12px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.2s;
        }
        .form-add button:hover {
            background: #1d4ed8;
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 1000px;">
        <a href="index.php" class="back-link" style="color: #2563eb;">🔙 Kembali ke Menu Awal</a>
        <div class="card" style="margin-bottom: 20px; border-left: 5px solid #2563eb;">
            <h2 style="color: #1e3a8a; margin-bottom: 5px;">📅 Jadwal Tuan Rumah Pertemuan</h2>
            <p style="color: #666;">Daftar petugas / tuan rumah arisan RT 31 dari Januari hingga Desember.</p>
            <?php if($is_admin): ?>
                <div style="margin-top: 15px;">
                    <a href="export.php?jenis=pertemuan" style="background: #2563eb; color: white; text-decoration: none; font-weight: bold; padding: 6px 12px; border-radius: 5px; font-size: 0.9rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: inline-block;">📅 Download Excel Pertemuan</a>
                </div>
            <?php endif; ?>
            <?php if ($is_admin) echo "<p style='color: #ef4444; font-size: 0.85rem; font-weight: bold; margin-top: 5px;'>🔓 Mode Admin: Anda dapat menambah atau menghapus petugas bulanan.</p>"; ?>
        </div>
        
        <?php echo $pesan; ?>
        
        <div class="grid-pertemuan">
            <?php
            $bulan_list = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            
            $sql = "SELECT id, nama, bulan_pertemuan FROM peserta WHERE is_deleted = 0 ORDER BY nama ASC";
            $result = $conn->query($sql);
            
            $jadwal = [];
            foreach ($bulan_list as $b) {
                $jadwal[$b] = [];
            }
            $unassigned = [];
            
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $b = $row['bulan_pertemuan'];
                    if (!empty($b) && isset($jadwal[$b])) {
                        $jadwal[$b][] = $row;
                    } else {
                        $unassigned[] = $row;
                    }
                }
            }
            
            $delay = 0.1;
            foreach ($bulan_list as $bulan) {
                $orang = $jadwal[$bulan];
                $jumlah = count($orang);
                
                echo "<div class='card-pertemuan animated-row' style='animation-delay: {$delay}s;'>";
                echo "<div>";
                echo "<div class='bulan-title'>$bulan <span class='badge-count'>$jumlah Orang</span></div>";
                
                if ($jumlah > 0) {
                    foreach ($orang as $p) {
                        echo "<div class='person-item'>";
                        echo "<span>🏠 " . htmlspecialchars($p['nama']) . "</span>";
                        if ($is_admin) {
                            echo "<a href='pertemuan.php?action=remove&id={$p['id']}' class='btn-hapus' title='Hapus dari bulan ini' onclick='return confirm(\"Coret {$p['nama']} dari bulan {$bulan}?\")'>❌</a>";
                        }
                        echo "</div>";
                    }
                } else {
                    echo "<div class='person-item' style='color: #aaa; font-style: italic; border:none;'>Belum ada tuan rumah</div>";
                }
                echo "</div>";
                
                if ($is_admin) {
                    echo "<form method='POST' action='pertemuan.php' class='form-add'>";
                    echo "<input type='hidden' name='action' value='add'>";
                    echo "<input type='hidden' name='bulan' value='$bulan'>";
                    echo "<input type='text' name='nama_peserta' list='warga-list-pertemuan' placeholder='Ketik nama warga...' required autocomplete='off'>";
                    echo "<button type='submit' title='Tambah tuan rumah'>➕</button>";
                    echo "</form>";
                }
                
                echo "</div>";
                $delay += 0.1;
            }
            ?>
        </div>
        
        <?php if ($is_admin): ?>
        <datalist id="warga-list-pertemuan">
            <?php foreach ($unassigned as $u) {
                echo "<option value=\"" . htmlspecialchars($u['nama']) . "\">";
            } ?>
        </datalist>
        <?php endif; ?>
        
        <div style="margin-top: 20px; text-align: right;">
            <?php if($is_admin): ?>
                <a href="logout.php?modul=pertemuan" style="color: #dc3545; text-decoration: none; font-weight: bold;">🔓 Logout Admin</a>
            <?php else: ?>
                <a href="login.php?modul=pertemuan" style="color: #ccc; text-decoration: none; font-size: 0.8rem;">🔒 Login Admin Rahasia</a>
            <?php endif; ?>
        </div>
    </div>
<script src="sound.js"></script>
<script src="screensaver.js"></script>
</body>
</html>
