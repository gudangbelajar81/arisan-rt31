<?php
session_start();
require 'config.php';

$is_admin = isset($_SESSION['admin_arisan']) && $_SESSION['admin_arisan'] === true;

// Ambil opsi bulan
$cek_kolom = $conn->query("SELECT nilai FROM pengaturan WHERE kunci = 'kolom_bulan'");
$row_kolom = $cek_kolom->fetch_assoc();
$list_bulan = array_filter(array_map('trim', explode(',', $row_kolom['nilai'])));

// Ambil kandidat (peserta yang belum pernah menang)
$sql_kandidat = "SELECT id, nama FROM peserta WHERE is_deleted = 0 AND no_wa != '-' AND no_wa != '' AND id NOT IN (SELECT peserta_id FROM pemenang_arisan) ORDER BY RAND()";
$res_kandidat = $conn->query($sql_kandidat);
$kandidat = [];
if ($res_kandidat) {
    while($row = $res_kandidat->fetch_assoc()) {
        $kandidat[] = $row;
    }
}
$kandidat_json = json_encode($kandidat);

// Ambil daftar pemenang
$sql_pemenang = "SELECT w.id, p.nama, w.bulan_menang, w.tanggal_kocok FROM pemenang_arisan w JOIN peserta p ON w.peserta_id = p.id ORDER BY w.id DESC";
$res_pemenang = $conn->query($sql_pemenang);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesin Kocokan Arisan RT 31</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .arena-kocokan {
            background: linear-gradient(135deg, #111, #222);
            border: 5px solid #cca300;
            border-radius: 15px;
            padding: 40px 20px;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(204, 163, 0, 0.2), inset 0 0 50px rgba(0,0,0,0.8);
            position: relative;
            overflow: hidden;
        }
        .arena-kocokan::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(circle, rgba(204,163,0,0.1) 0%, rgba(0,0,0,0) 70%);
            z-index: 1;
        }
        
        #slot-display {
            font-size: 3.5rem;
            font-weight: 900;
            color: #cca300;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 20px 0;
            min-height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-shadow: 0 0 10px rgba(204, 163, 0, 0.5), 0 0 20px rgba(204, 163, 0, 0.3);
            position: relative;
            z-index: 2;
            transition: all 0.1s;
        }
        
        .winner-mode {
            color: #fff !important;
            text-shadow: 0 0 20px #cca300, 0 0 40px #cca300, 0 0 60px #e6b800 !important;
            transform: scale(1.1);
            animation: pulse 1s infinite alternate;
        }
        
        @keyframes pulse {
            0% { transform: scale(1.1); }
            100% { transform: scale(1.15); }
        }
        
        .btn-spin {
            background: linear-gradient(145deg, #dc3545, #a71d2a);
            color: white;
            border: 2px solid #ff4d4d;
            padding: 20px 50px;
            font-size: 1.5rem;
            font-weight: 900;
            border-radius: 50px;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 2px;
            box-shadow: 0 10px 20px rgba(220, 53, 69, 0.4), inset 0 5px 10px rgba(255,255,255,0.2);
            transition: all 0.2s;
            position: relative;
            z-index: 2;
        }
        .btn-spin:active {
            transform: translateY(5px);
            box-shadow: 0 5px 10px rgba(220, 53, 69, 0.4), inset 0 5px 10px rgba(0,0,0,0.5);
        }
        .btn-spin:disabled {
            background: #555;
            border-color: #444;
            color: #888;
            cursor: not-allowed;
            box-shadow: none;
            transform: none;
        }
        
        .control-panel {
            background: rgba(255,255,255,0.9);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border-left: 5px solid #cca300;
        }
        .control-panel select {
            padding: 12px;
            font-size: 1.1rem;
            border: 2px solid #cca300;
            border-radius: 8px;
            outline: none;
            min-width: 200px;
        }
        
        .hall-of-fame {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .hall-of-fame table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .hall-of-fame th, .hall-of-fame td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .hall-of-fame th {
            background: linear-gradient(145deg, #e6b800, #cca300);
            color: #111;
            font-weight: bold;
        }
        .medal-icon {
            font-size: 1.5rem;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 800px;">
        <a href="index.php" class="back-link">🔙 Kembali ke Menu Awal</a>
        
        <div class="card" style="text-align: center; border-top: 3px solid #cca300; margin-bottom: 20px; padding: 15px;">
            <h1 style="color: var(--primary-dark); font-size: 2rem; margin:0;">🎰 Mesin Kocokan Arisan</h1>
            <p style="color: #666; margin-top: 5px;">Sistem pengacak digital pintar (Sistem Gugur Otomatis).</p>
        </div>
        
        <?php if($is_admin): ?>
        <div class="control-panel">
            <label style="font-weight: bold; color: #333;">Pilih Putaran Bulan:</label>
            <select id="bulanSelect">
                <?php foreach($list_bulan as $bln): ?>
                    <option value="<?= htmlspecialchars(trim($bln)) ?>"><?= htmlspecialchars(trim($bln)) ?></option>
                <?php endforeach; ?>
            </select>
            <small style="color: #666; width: 100%; text-align: center; margin-top: 5px;">Hanya Admin yang dapat memutar mesin dan mencatat hasil.</small>
        </div>
        <?php endif; ?>
        
        <div class="arena-kocokan">
            <h3 style="color: #888; font-weight: normal; margin:0; text-transform: uppercase; letter-spacing: 3px; position: relative; z-index: 2;">Kandidat Tersisa: <span id="kandidat-count"><?= count($kandidat) ?></span> Orang</h3>
            <div id="slot-display">???</div>
            
            <?php if($is_admin): ?>
                <button id="btnSpin" class="btn-spin">🎲 PUTAR MESIN!</button>
            <?php else: ?>
                <div style="color: #cca300; font-style: italic; position: relative; z-index: 2;">Menunggu Admin memutar mesin...</div>
            <?php endif; ?>
        </div>
        
        <div class="hall-of-fame">
            <h2 style="color: var(--primary-dark); margin:0; border-bottom: 2px solid #cca300; padding-bottom: 10px; display:inline-block;">🏆 Papan Pemenang (Hall of Fame)</h2>
            <table id="tabelPemenang">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Bulan Putaran</th>
                        <th>Nama Pemenang</th>
                        <th>Waktu Kocokan</th>
                        <?php if($is_admin): ?><th>Aksi</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if($res_pemenang && $res_pemenang->num_rows > 0) {
                        $no = 1;
                        while($row = $res_pemenang->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $no++ . "</td>";
                            echo "<td><strong>" . htmlspecialchars($row['bulan_menang']) . "</strong></td>";
                            echo "<td style='font-weight:bold; color:#217346;'><span class='medal-icon'>🥇</span>" . htmlspecialchars($row['nama']) . "</td>";
                            echo "<td style='font-size:0.85rem; color:#666;'>" . date('d M Y, H:i', strtotime($row['tanggal_kocok'])) . "</td>";
                            if($is_admin) {
                                echo "<td><button class='btn-aksi btn-hapus' onclick='hapusPemenang(" . $row['id'] . ")' style='font-size:0.8rem; padding: 5px 10px;'>Batalkan</button></td>";
                            }
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' style='text-align:center; color:#999; padding: 30px;'>Belum ada pemenang yang tercatat.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
        const kandidat = <?= $kandidat_json ?>;
        const slotDisplay = document.getElementById('slot-display');
        const btnSpin = document.getElementById('btnSpin');
        const bulanSelect = document.getElementById('bulanSelect');
        const kandidatCount = document.getElementById('kandidat-count');
        
        let isSpinning = false;
        
        if (btnSpin) {
            btnSpin.addEventListener('click', function() {
                if (kandidat.length === 0) {
                    alert("Semua peserta sudah menang! Silakan hapus riwayat pemenang untuk mengulang siklus.");
                    return;
                }
                
                if (isSpinning) return;
                isSpinning = true;
                btnSpin.disabled = true;
                btnSpin.innerText = "MENGACAK...";
                slotDisplay.classList.remove('winner-mode');
                
                const bulanMenang = bulanSelect.value;
                
                // Durasi animasi
                const duration = 4000; // 4 detik
                const startTime = Date.now();
                let speed = 50; // kecepatan awal sangat cepat
                let timerId;
                
                // Audio (opsional)
                // const audioClick = new Audio('data:audio/wav;base64,...');
                
                function spin() {
                    const now = Date.now();
                    const elapsed = now - startTime;
                    
                    // Pilih nama acak untuk ditampilkan efek berkedip
                    const randomIndex = Math.floor(Math.random() * kandidat.length);
                    slotDisplay.innerText = kandidat[randomIndex].nama;
                    
                    // Percepat perlambatan (friction)
                    if (elapsed < duration * 0.5) {
                        speed = 50;
                    } else if (elapsed < duration * 0.8) {
                        speed = 150;
                    } else {
                        speed = 300;
                    }
                    
                    if (elapsed < duration) {
                        timerId = setTimeout(spin, speed);
                    } else {
                        // BERHENTI! Tentukan Pemenang Sebenarnya
                        const winnerIndex = Math.floor(Math.random() * kandidat.length);
                        const winner = kandidat[winnerIndex];
                        
                        slotDisplay.innerText = winner.nama;
                        slotDisplay.classList.add('winner-mode');
                        btnSpin.innerText = "🎲 PUTAR MESIN!";
                        btnSpin.disabled = false;
                        isSpinning = false;
                        
                        // Kirim ke server
                        simpanPemenang(winner.id, bulanMenang);
                        
                        // Hapus dari array kandidat agar tidak ganda di sesi ini
                        kandidat.splice(winnerIndex, 1);
                        kandidatCount.innerText = kandidat.length;
                    }
                }
                
                spin();
            });
        }
        
        function simpanPemenang(peserta_id, bulan_menang) {
            const formData = new FormData();
            formData.append('peserta_id', peserta_id);
            formData.append('bulan_menang', bulan_menang);
            
            fetch('proses_kocok.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    // Reload halaman setelah 3 detik untuk memperbarui tabel
                    setTimeout(() => {
                        window.location.reload();
                    }, 3000);
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(err => alert("Terjadi kesalahan koneksi."));
        }
        
        function hapusPemenang(id) {
            if(confirm("Apakah Anda yakin ingin membatalkan/menghapus riwayat kemenangan ini? Peserta akan dimasukkan kembali ke mesin kocokan.")) {
                const formData = new FormData();
                formData.append('action', 'hapus');
                formData.append('id', id);
                
                fetch('proses_kocok.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'success') {
                        window.location.reload();
                    } else {
                        alert("Error: " + data.message);
                    }
                });
            }
        }
    </script>
</body>
</html>
