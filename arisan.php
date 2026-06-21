<?php
session_start();
require 'config.php';
require 'logger.php';

$is_admin = isset($_SESSION['admin_arisan']) && $_SESSION['admin_arisan'] === true;

// ==========================================
// LOGIKA PESERTA & BUKU KAS
// ==========================================

// Tambah Kolom Bulan Manual
if ($is_admin && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'tambah_bulan') {
    $bulan_baru = trim($_POST['bulan_baru']);
    if (!empty($bulan_baru)) {
        $cek_kolom = $conn->query("SELECT nilai FROM pengaturan WHERE kunci = 'kolom_bulan'");
        $row_kolom = $cek_kolom->fetch_assoc();
        $kolom_lama = $row_kolom['nilai'];
        $kolom_baru = empty($kolom_lama) ? $bulan_baru : $kolom_lama . "," . $bulan_baru;
        $stmt = $conn->prepare("UPDATE pengaturan SET nilai = ? WHERE kunci = 'kolom_bulan'");
        $stmt->bind_param("s", $kolom_baru);
        $stmt->execute();
        catat_log($conn, "Admin menambahkan kolom bulan baru: $bulan_baru");
        header("Location: arisan.php");
        exit;
    }
}

// Tambah Bulan Otomatis
if ($is_admin && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'tambah_bulan_otomatis') {
    $cek_kolom = $conn->query("SELECT nilai FROM pengaturan WHERE kunci = 'kolom_bulan'");
    $row_kolom = $cek_kolom->fetch_assoc();
    $list_sekarang = array_filter(array_map('trim', explode(',', $row_kolom['nilai'])));
    
    if (empty($list_sekarang)) {
        $bulan_baru = "Januari " . date('Y');
        $kolom_baru = $bulan_baru;
        $stmt = $conn->prepare("UPDATE pengaturan SET nilai = ? WHERE kunci = 'kolom_bulan'");
        $stmt->bind_param("s", $kolom_baru);
        $stmt->execute();
        catat_log($conn, "Admin menambahkan kolom bulan otomatis pertama: $bulan_baru");
    } else {
        $bulan_terakhir = end($list_sekarang);
        $arr_nama_bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $parts = explode(" ", trim($bulan_terakhir));
        $nama_bulan_lama = ucfirst(strtolower(trim($parts[0])));
        $tahun_lama = isset($parts[1]) ? (int)$parts[1] : (int)date('Y');
        $index = array_search($nama_bulan_lama, $arr_nama_bulan);
        if ($index !== false) {
            $index_baru = $index + 1;
            $tahun_baru = $tahun_lama;
            if ($index_baru > 11) {
                $index_baru = 0;
                $tahun_baru++;
            }
            $bulan_baru = $arr_nama_bulan[$index_baru] . " " . $tahun_baru;
            $kolom_baru = $row_kolom['nilai'] . "," . $bulan_baru;
            $stmt = $conn->prepare("UPDATE pengaturan SET nilai = ? WHERE kunci = 'kolom_bulan'");
            $stmt->bind_param("s", $kolom_baru);
            $stmt->execute();
            catat_log($conn, "Admin menambahkan kolom bulan otomatis: $bulan_baru");
        }
    }
    header("Location: arisan.php");
    exit;
}

// Ambil daftar bulan
$cek_kolom = $conn->query("SELECT nilai FROM pengaturan WHERE kunci = 'kolom_bulan'");
$row_kolom = $cek_kolom->fetch_assoc();
$list_bulan = array_filter(array_map('trim', explode(',', $row_kolom['nilai'])));

// Ambil data pembayaran
$pembayaran = [];
$res_bayar = $conn->query("SELECT peserta_id, bulan FROM pembayaran_arisan");
if ($res_bayar) {
    while($row = $res_bayar->fetch_assoc()) {
        $pembayaran[$row['peserta_id']][$row['bulan']] = true;
    }
}

// ==========================================
// LOGIKA KOCOKAN ROULETTE
// ==========================================
$sql_kandidat = "SELECT id, nama FROM peserta WHERE is_deleted = 0 AND no_wa != '-' AND no_wa != '' AND id NOT IN (SELECT peserta_id FROM pemenang_arisan) ORDER BY RAND()";
$res_kandidat = $conn->query($sql_kandidat);
$kandidat = [];
if ($res_kandidat) {
    while($row = $res_kandidat->fetch_assoc()) {
        $kandidat[] = $row;
    }
}
$kandidat_json = json_encode($kandidat);

$sql_pemenang = "SELECT w.id, p.nama, w.bulan_menang, w.tanggal_kocok FROM pemenang_arisan w JOIN peserta p ON w.peserta_id = p.id ORDER BY w.id DESC";
$res_pemenang = $conn->query($sql_pemenang);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ruang Kendali Arisan RT 31</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: 60% 38%;
            gap: 2%;
            align-items: start;
        }
        @media (max-width: 900px) {
            .dashboard-grid {
                grid-template-columns: 100%;
                gap: 20px;
            }
        }
        
        /* Styles Buku Kas */
        .search-box {
            width: 100%;
            padding: 12px 15px;
            border-radius: 8px;
            border: 2px solid #cca300;
            font-size: 1rem;
            margin-bottom: 20px;
            background: rgba(255,255,255,0.9);
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            outline: none;
        }
        .table-responsive {
            overflow-x: auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            max-height: 70vh;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: max-content;
        }
        th, td {
            padding: 12px 10px;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }
        th {
            position: sticky;
            top: 0;
            background: linear-gradient(145deg, #e6b800, #cca300);
            color: #111;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            z-index: 10;
        }
        
        .no-peserta, .nama-peserta {
            text-align: left;
            position: sticky;
            background: #fff;
            z-index: 5;
            box-shadow: 2px 0 5px rgba(0,0,0,0.05);
        }
        .no-peserta { left: 0; }
        .nama-peserta { left: 40px; font-weight: bold; }
        
        /* Checkbox Khusus */
        input[type="checkbox"].chk-bayar {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #217346;
        }
        .badge-lunas { background: #d4edda; color: #155724; padding: 3px 8px; border-radius: 12px; font-size: 0.8rem; font-weight: bold; }
        .badge-belum { background: #f8d7da; color: #721c24; padding: 3px 8px; border-radius: 12px; font-size: 0.8rem; }
        
        .btn-3d {
            background: linear-gradient(145deg, #e6b800, #cca300);
            color: #111;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: bold;
            box-shadow: 0 4px 0 #997a00, 0 5px 10px rgba(0,0,0,0.2);
            cursor: pointer;
            transition: all 0.1s;
            width: 100%;
        }
        .btn-3d:active {
            transform: translateY(4px);
            box-shadow: 0 0 0 #997a00, 0 2px 5px rgba(0,0,0,0.2);
        }
        
        /* Styles Mesin Kocokan */
        .arena-kocokan {
            background: linear-gradient(135deg, #111, #222);
            border: 5px solid #cca300;
            border-radius: 15px;
            padding: 30px 20px;
            text-align: center;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(204, 163, 0, 0.2), inset 0 0 50px rgba(0,0,0,0.8);
            position: relative;
        }
        #slot-display {
            font-size: 3rem;
            font-weight: 900;
            color: #cca300;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 20px 0;
            min-height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-shadow: 0 0 10px rgba(204, 163, 0, 0.5);
            transition: all 0.1s;
        }
        .winner-mode {
            color: #fff !important;
            text-shadow: 0 0 20px #cca300, 0 0 40px #cca300, 0 0 60px #e6b800 !important;
            transform: scale(1.1);
        }
        .btn-spin {
            background: linear-gradient(145deg, #dc3545, #a71d2a);
            color: white;
            border: 2px solid #ff4d4d;
            padding: 15px 30px;
            font-size: 1.2rem;
            font-weight: 900;
            border-radius: 50px;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 2px;
            box-shadow: 0 10px 20px rgba(220, 53, 69, 0.4);
            width: 100%;
        }
        .hall-of-fame {
            background: white;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .hall-of-fame th {
            position: static;
        }
    </style>
</head>
<body style="background: #f4f6f9; padding: 20px;">
    <div style="max-width: 1400px; margin: 0 auto;">
        <a href="index.php" class="back-link" style="margin-bottom: 15px; display:inline-block;">🔙 Kembali ke Menu Awal</a>
        
        <div class="card" style="text-align: center; border-top: 4px solid #cca300; margin-bottom: 20px; padding: 15px;">
            <h1 style="color: var(--primary-dark); font-size: 2rem; margin:0;">💰 Ruang Kendali Arisan RT 31</h1>
            <p style="color: #666; margin-top: 5px;">Manajemen Buku Kas & Pengundian Pemenang Otomatis.</p>
        </div>

        <div class="dashboard-grid">
            
            <!-- SEKTOR KIRI: BUKU KAS -->
            <div class="panel-kiri">
                <?php if ($is_admin): ?>
                <div style="display: flex; gap: 10px; margin-bottom: 15px; flex-wrap: wrap;">
                    <form method="POST" style="flex: 1; min-width: 250px;">
                        <input type="hidden" name="action" value="tambah_bulan_otomatis">
                        <button type="submit" class="btn-3d">⏩ Lanjut Bulan Berikutnya</button>
                    </form>
                </div>
                <?php endif; ?>
                
                <input type="text" id="searchInput" class="search-box" placeholder="🔍 Cari nama warga di sini..." autocomplete="off">
                
                <div class="table-responsive">
                    <table id="tabelArisan">
                        <thead>
                            <tr>
                                <th style="min-width: 40px; position: sticky; left: 0; z-index: 15;">No</th>
                                <th style="min-width: 150px; position: sticky; left: 40px; z-index: 15;">Nama Warga</th>
                                <?php 
                                foreach ($list_bulan as $bln) {
                                    $parts = explode(" ", trim($bln));
                                    echo "<th style='min-width: 80px;'>";
                                    echo "<div style='font-size: 0.9rem;'>" . htmlspecialchars($parts[0]) . "</div>";
                                    if (isset($parts[1])) echo "<div style='font-size: 0.75rem; opacity: 0.8; font-weight: normal;'>" . htmlspecialchars($parts[1]) . "</div>";
                                    echo "</th>";
                                }
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT id, nama FROM peserta WHERE is_deleted = 0 AND no_wa != '-' AND no_wa != '' ORDER BY nama ASC";
                            $result = $conn->query($sql);
                            if ($result && $result->num_rows > 0) {
                                $no = 1;
                                while($row = $result->fetch_assoc()) {
                                    $pid = $row['id'];
                                    echo "<tr>";
                                    echo "<td class='no-peserta'>" . $no++ . "</td>";
                                    echo "<td class='nama-peserta'>" . htmlspecialchars($row['nama']) . "</td>";
                                    
                                    foreach ($list_bulan as $bln) {
                                        $lunas = isset($pembayaran[$pid][$bln]);
                                        echo "<td>";
                                        if ($is_admin) {
                                            $checked = $lunas ? "checked" : "";
                                            echo "<input type='checkbox' class='chk-bayar' data-id='$pid' data-bulan='" . htmlspecialchars($bln, ENT_QUOTES) . "' $checked>";
                                        } else {
                                            echo $lunas ? "<span class='badge-lunas'>✔️</span>" : "<span class='badge-belum'>❌</span>";
                                        }
                                        echo "</td>";
                                    }
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='10' style='padding:20px; color:#888;'>Belum ada warga terdaftar.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- SEKTOR KANAN: ROULETTE & PEMENANG -->
            <div class="panel-kanan">
                <?php if($is_admin): ?>
                <div style="background: white; padding: 10px 15px; border-radius: 10px; margin-bottom: 15px; display:flex; align-items:center; gap:10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                    <label style="font-weight: bold; font-size:0.9rem;">Putaran:</label>
                    <select id="bulanSelect" style="flex:1; padding: 8px; border-radius: 5px; border: 1px solid #ccc;">
                        <?php foreach($list_bulan as $bln): ?>
                            <option value="<?= htmlspecialchars(trim($bln)) ?>"><?= htmlspecialchars(trim($bln)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <div class="arena-kocokan">
                    <div style="color: #888; font-size:0.9rem; letter-spacing: 2px;">KANDIDAT TERSISA: <b id="kandidat-count"><?= count($kandidat) ?></b></div>
                    <div id="slot-display">???</div>
                    
                    <?php if($is_admin): ?>
                        <button id="btnSpin" class="btn-spin">🎲 PUTAR MESIN!</button>
                        <div id="action-buttons" style="display: none; flex-direction: column; gap: 10px; margin-top: 15px;">
                            <button id="btnSah" class="btn-spin" style="background: linear-gradient(145deg, #2ea84b, #217346); padding: 10px; font-size: 1rem;">✅ SAHKAN PEMENANG</button>
                            <button id="btnBatal" class="btn-spin" style="background: linear-gradient(145deg, #6c757d, #495057); padding: 10px; font-size: 1rem;">❌ PUTAR ULANG</button>
                        </div>
                    <?php else: ?>
                        <div style="color: #cca300; font-style: italic;">Hanya Admin yang dapat memutar mesin.</div>
                    <?php endif; ?>
                </div>
                
                <div class="hall-of-fame">
                    <h3 style="color: var(--primary-dark); margin-top:0; border-bottom: 2px solid #cca300; padding-bottom: 5px;">🏆 Hall of Fame</h3>
                    <div style="overflow-x: auto;">
                        <table>
                            <thead><tr><th>Bulan</th><th>Nama Pemenang</th><?php if($is_admin): ?><th>Aksi</th><?php endif; ?></tr></thead>
                            <tbody>
                                <?php 
                                if($res_pemenang && $res_pemenang->num_rows > 0) {
                                    while($row = $res_pemenang->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td style='font-size:0.85rem;'>" . htmlspecialchars($row['bulan_menang']) . "</td>";
                                        echo "<td style='font-weight:bold; color:#217346;'>🥇 " . htmlspecialchars($row['nama']) . "</td>";
                                        if($is_admin) echo "<td><button onclick='hapusPemenang(" . $row['id'] . ")' style='background:#dc3545; color:white; border:none; padding:4px 8px; border-radius:4px; cursor:pointer;'>Batal</button></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='3' style='text-align:center; color:#999;'>Belum ada pemenang.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        <div style="margin-top: 20px; text-align: right;">
            <?php if($is_admin): ?>
                <a href="logout.php?modul=arisan" style="color: #dc3545; text-decoration: none; font-weight: bold;">🔴 Logout Admin</a>
            <?php else: ?>
                <a href="login.php?modul=arisan" style="color: #ccc; text-decoration: none; font-size: 0.8rem;">🔒 Login Admin</a>
            <?php endif; ?>
        </div>
    </div>
    
    <div id="toast" style="visibility: hidden; min-width: 250px; background-color: #333; color: #fff; text-align: center; border-radius: 2px; padding: 16px; position: fixed; z-index: 100; left: 50%; bottom: 30px; transform: translateX(-50%);">Notifikasi</div>

<script>
// --- LOGIKA BUKU KAS ---
document.getElementById('searchInput').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('#tabelArisan tbody tr');
    rows.forEach(row => {
        let nameCell = row.querySelector('.nama-peserta');
        if (nameCell) {
            let txtValue = nameCell.textContent || nameCell.innerText;
            row.style.display = txtValue.toLowerCase().indexOf(filter) > -1 ? "" : "none";
        }
    });
});

<?php if ($is_admin): ?>
const toast = document.getElementById('toast');
let toastTimeout;
function showToast(msg) {
    toast.textContent = msg;
    toast.style.visibility = 'visible';
    clearTimeout(toastTimeout);
    toastTimeout = setTimeout(() => { toast.style.visibility = 'hidden'; }, 2500);
}

document.querySelectorAll('.chk-bayar').forEach(chk => {
    chk.addEventListener('change', function() {
        let pid = this.getAttribute('data-id');
        let bulan = this.getAttribute('data-bulan');
        let action = this.checked ? 'check' : 'uncheck';
        let formData = new FormData();
        formData.append('peserta_id', pid); formData.append('bulan', bulan); formData.append('action', action);
        
        fetch('proses_bayar.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                showToast(action === 'check' ? '✔️ Lunas: ' + bulan : '❌ Batal Lunas: ' + bulan);
            } else {
                alert('Gagal: ' + data.message);
                this.checked = !this.checked;
            }
        }).catch(err => {
            alert('Terjadi kesalahan jaringan.');
            this.checked = !this.checked;
        });
    });
});

// --- LOGIKA ROULETTE ---
const kandidat = <?= $kandidat_json ?>;
const slotDisplay = document.getElementById('slot-display');
const btnSpin = document.getElementById('btnSpin');
const actionButtons = document.getElementById('action-buttons');
const btnSah = document.getElementById('btnSah');
const btnBatal = document.getElementById('btnBatal');
const bulanSelect = document.getElementById('bulanSelect');

let isSpinning = false;
let selectedWinner = null;
let audioCtx = null;

function initAudio() {
    if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    if(audioCtx.state === 'suspended') audioCtx.resume();
}

function playTick() {
    if(!audioCtx) return;
    const osc = audioCtx.createOscillator();
    const gain = audioCtx.createGain();
    osc.type = 'triangle';
    osc.frequency.setValueAtTime(800, audioCtx.currentTime);
    osc.frequency.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.1);
    gain.gain.setValueAtTime(0.1, audioCtx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.1);
    osc.connect(gain); gain.connect(audioCtx.destination);
    osc.start(); osc.stop(audioCtx.currentTime + 0.1);
}

function playTada() {
    if(!audioCtx) return;
    [523.25, 659.25, 783.99, 1046.50].forEach((freq, i) => {
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.frequency.setValueAtTime(freq, audioCtx.currentTime + (i*0.1));
        gain.gain.setValueAtTime(0, audioCtx.currentTime);
        gain.gain.linearRampToValueAtTime(0.3, audioCtx.currentTime + (i*0.1) + 0.05);
        gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + (i*0.1) + 1.5);
        osc.connect(gain); gain.connect(audioCtx.destination);
        osc.start(audioCtx.currentTime + (i*0.1)); osc.stop(audioCtx.currentTime + (i*0.1) + 1.5);
    });
}

function speakWinner(name) {
    if ('speechSynthesis' in window) {
        window.speechSynthesis.cancel();
        const utterance = new SpeechSynthesisUtterance("Selamat! Pemenang bulan ini adalah, " + name);
        utterance.lang = 'id-ID';
        window.speechSynthesis.speak(utterance);
    }
}

if (btnSpin) {
    btnSpin.addEventListener('click', function() {
        if (kandidat.length === 0) return alert("Semua peserta sudah menang!");
        if (isSpinning) return;
        initAudio();
        
        isSpinning = true;
        btnSpin.style.display = 'none';
        actionButtons.style.display = 'none';
        slotDisplay.classList.remove('winner-mode');
        
        const duration = 5000;
        const startTime = Date.now();
        let speed = 50; 
        
        function spin() {
            const elapsed = Date.now() - startTime;
            const randomIndex = Math.floor(Math.random() * kandidat.length);
            slotDisplay.innerText = kandidat[randomIndex].nama;
            playTick();
            
            if (elapsed < duration * 0.4) speed = 50;
            else if (elapsed < duration * 0.7) speed = 150;
            else if (elapsed < duration * 0.9) speed = 300;
            else speed = 500;
            
            if (elapsed < duration) {
                setTimeout(spin, speed);
            } else {
                selectedWinner = kandidat[Math.floor(Math.random() * kandidat.length)];
                slotDisplay.innerText = selectedWinner.nama;
                slotDisplay.classList.add('winner-mode');
                playTada();
                setTimeout(() => speakWinner(selectedWinner.nama), 500);
                actionButtons.style.display = 'flex';
                isSpinning = false;
            }
        }
        spin();
    });
    
    btnSah.addEventListener('click', function() {
        if(!selectedWinner) return;
        btnSah.innerText = "MENYIMPAN..."; btnSah.disabled = true; btnBatal.disabled = true;
        let formData = new FormData();
        formData.append('peserta_id', selectedWinner.id);
        formData.append('bulan_menang', bulanSelect.value);
        fetch('proses_kocok.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') window.location.reload();
            else { alert("Error: " + data.message); btnSah.innerText = "✅ SAHKAN PEMENANG"; btnSah.disabled = false; btnBatal.disabled = false; }
        });
    });
    
    btnBatal.addEventListener('click', function() {
        actionButtons.style.display = 'none';
        btnSpin.style.display = 'inline-block';
        slotDisplay.classList.remove('winner-mode');
        slotDisplay.innerText = "???";
        selectedWinner = null;
        window.speechSynthesis.cancel();
    });
}

function hapusPemenang(id) {
    if(confirm("Apakah Anda yakin ingin membatalkan kemenangan ini?")) {
        let formData = new FormData();
        formData.append('action', 'hapus'); formData.append('id', id);
        fetch('proses_kocok.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => { if(data.status === 'success') window.location.reload(); });
    }
}
<?php endif; ?>
</script>
</body>
</html>
