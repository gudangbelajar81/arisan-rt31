<?php 
session_start();
require 'config.php'; 
$is_admin = isset($_SESSION['admin_arisan']) && $_SESSION['admin_arisan'] === true;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Peserta Arisan RT 31</title>
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
        <a href="index.php" class="back-link">🔙 Kembali ke Menu Awal</a>
        <div class="card card-peserta">
            <h2 style="color: var(--primary-dark); margin-bottom: 5px;"><span class="shine-text">Buku Catatan Peserta</span></h2>
            <p style="color: #666; margin-bottom: 20px;">Daftar warga RT 31 yang sudah resmi terdaftar di sistem arisan.</p>
            
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Lengkap</th>
                            <th>Piket Ronda</th>
                            <th>Waktu Daftar</th>
                            <?php if($is_admin): ?>
                            <th>Aksi Admin</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Hanya tampilkan peserta arisan asli (yang punya nomor WA, bukan warga selipan otomatis)
                        $sql = "SELECT * FROM peserta WHERE is_deleted = 0 AND no_wa != '-' AND no_wa != '' ORDER BY nama ASC";
                        $result = $conn->query($sql);
                        
                        if ($result->num_rows > 0) {
                            $no = 1;
                            $delay = 0.1; // Animasi mulai setelah 0.1 detik
                            while($row = $result->fetch_assoc()) {
                                // Menyuntikkan animasi staggered (beruntun)
                                echo "<tr class='animated-row' style='animation-delay: {$delay}s;'>";
                                echo "<td>" . $no++ . "</td>";
                                echo "<td><strong>" . htmlspecialchars($row['nama']) . "</strong><br><small style='color: #888;'>WA: " . htmlspecialchars($row['no_wa']) . "</small></td>";
                                
                                $ronda_badge = !empty($row['hari_ronda']) ? "<span style='background:#cca300; color:#111; padding:3px 10px; border-radius:15px; font-size:0.8rem; font-weight:bold;'>".htmlspecialchars($row['hari_ronda'])."</span>" : "<span style='color:#ccc; font-size:0.8rem;'>-</span>";
                                echo "<td>$ronda_badge</td>";
                                
                                echo "<td>" . date('d M Y', strtotime($row['tanggal_daftar'])) . "</td>";
                                if ($is_admin) {
                                    echo "<td>
                                        <a href='edit.php?id=" . $row['id'] . "' class='btn-aksi btn-edit'>Edit</a>
                                        <a href='hapus.php?id=" . $row['id'] . "' class='btn-aksi btn-hapus' onclick='return confirm(\"Yakin ingin menghapus data ini?\")'>Hapus</a>
                                    </td>";
                                }
                                echo "</tr>";
                                $delay += 0.08; // Baris berikutnya telat 0.08 detik
                            }
                        } else {
                            $cols = $is_admin ? 5 : 4;
                            echo "<tr><td colspan='$cols' class='text-center' style='padding: 20px;'>Belum ada warga yang mendaftar. Ayo daftar duluan!</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div style="margin-top: 20px; text-align: right;">
            <?php if($is_admin): ?>
                <a href="export.php" style="color: #217346; text-decoration: none; font-weight: bold; margin-right: 15px; border: 1px solid #217346; padding: 5px 10px; border-radius: 5px;">📊 Download Excel</a>
                <a href="logout.php?modul=arisan" style="color: #dc3545; text-decoration: none; font-weight: bold;">🔓 Logout Admin</a>
            <?php else: ?>
                <a href="login.php?modul=arisan" style="color: #ccc; text-decoration: none; font-size: 0.8rem;">🔒 Login Admin Rahasia</a>
            <?php endif; ?>
        </div>
    </div>
<script src="sound.js"></script><script src="screensaver.js"></script></body>
</html>
