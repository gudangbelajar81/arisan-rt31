<?php 
session_start();
require 'config.php'; 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Ronda RT 31</title>
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
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .card-ronda {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            padding: 20px;
            box-shadow: var(--shadow-soft);
            border-top: 5px solid #cca300;
            transition: transform 0.3s ease;
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
            padding: 8px 0;
            border-bottom: 1px dashed #eee;
            font-size: 0.95rem;
            color: #333;
        }
        .person-item:last-child {
            border-bottom: none;
        }
        .badge-count {
            background: #1e293b;
            color: #cca300;
            font-size: 0.75rem;
            padding: 3px 8px;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 1000px;">
        <a href="index.php" class="back-link">🔙 Kembali ke Menu Awal</a>
        <div class="card" style="margin-bottom: 30px;">
            <h2 style="color: var(--primary-dark); margin-bottom: 5px;"><span class="shine-text">🛡️ Jadwal Siskamling (Ronda)</span></h2>
            <p style="color: #666;">Daftar piket keamanan lingkungan RT 31 dari hari Senin hingga Minggu.</p>
        </div>
        
        <div class="grid-ronda">
            <?php
            $hari_list = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
            
            // Ambil semua data peserta yang belum dihapus dan ada hari rondanya
            $sql = "SELECT nama, hari_ronda FROM peserta WHERE is_deleted = 0 AND hari_ronda IS NOT NULL AND hari_ronda != '' ORDER BY nama ASC";
            $result = $conn->query($sql);
            
            // Kelompokkan berdasarkan hari
            $jadwal = [];
            foreach ($hari_list as $h) {
                $jadwal[$h] = [];
            }
            
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $h = $row['hari_ronda'];
                    if (isset($jadwal[$h])) {
                        $jadwal[$h][] = $row['nama'];
                    }
                }
            }
            
            // Tampilkan kartu untuk setiap hari
            $delay = 0.1;
            foreach ($hari_list as $hari) {
                $orang = $jadwal[$hari];
                $jumlah = count($orang);
                
                echo "<div class='card-ronda animated-row' style='animation-delay: {$delay}s;'>";
                echo "<div class='hari-title'>$hari <span class='badge-count'>$jumlah Orang</span></div>";
                
                if ($jumlah > 0) {
                    foreach ($orang as $nama) {
                        echo "<div class='person-item'>💂‍♂️ " . htmlspecialchars($nama) . "</div>";
                    }
                } else {
                    echo "<div class='person-item' style='color: #aaa; font-style: italic;'>Belum ada petugas</div>";
                }
                echo "</div>";
                $delay += 0.1;
            }
            ?>
        </div>
    </div>
<script src="sound.js"></script><script src="screensaver.js"></script></body>
</html>
