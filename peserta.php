<?php 
session_start();
require 'config.php'; 
require 'logger.php';
$is_admin = isset($_SESSION['admin_arisan']) && $_SESSION['admin_arisan'] === true;

// Tangani Penambahan Kolom Bulan (Khusus Admin)
if ($is_admin && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'tambah_bulan') {
    $bulan_baru = trim($_POST['bulan_baru']);
    if (!empty($bulan_baru)) {
        // Ambil data lama
        $cek_kolom = $conn->query("SELECT nilai FROM pengaturan WHERE kunci = 'kolom_bulan'");
        $row_kolom = $cek_kolom->fetch_assoc();
        $kolom_lama = $row_kolom['nilai'];
        
        $kolom_baru = empty($kolom_lama) ? $bulan_baru : $kolom_lama . "," . $bulan_baru;
        
        $stmt = $conn->prepare("UPDATE pengaturan SET nilai = ? WHERE kunci = 'kolom_bulan'");
        $stmt->bind_param("s", $kolom_baru);
        $stmt->execute();
        
        catat_log($conn, "Admin menambahkan kolom bulan baru: $bulan_baru");
        header("Location: peserta.php");
        exit;
    }
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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Kas Arisan RT 31</title>
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
        .search-box:focus {
            box-shadow: 0 4px 15px rgba(204, 163, 0, 0.3);
        }
        .table-responsive {
            overflow-x: auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: max-content;
        }
        th, td {
            padding: 12px 15px;
            border: 1px solid #eee;
            text-align: center;
        }
        th {
            background: #cca300;
            color: #111;
            white-space: nowrap;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        td.nama-peserta {
            text-align: left;
            font-weight: bold;
            color: #333;
            position: sticky;
            left: 0;
            background: #fff;
            z-index: 5;
            box-shadow: 2px 0 5px rgba(0,0,0,0.05);
        }
        
        /* Checkbox styling */
        .chk-bayar {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #217346;
        }
        .badge-lunas {
            color: #217346;
            font-size: 1.2rem;
        }
        .badge-belum {
            color: #dc3545;
            font-size: 1.2rem;
            opacity: 0.3;
        }
        
        .form-tambah-bulan {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            background: rgba(204, 163, 0, 0.1);
            padding: 15px;
            border-radius: 8px;
            border: 1px dashed #cca300;
        }
        .form-tambah-bulan input {
            flex: 1;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        .form-tambah-bulan button {
            background: #111;
            color: #cca300;
            border: none;
            padding: 0 15px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }
        
        /* Toast Notification */
        #toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #111;
            color: #cca300;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s;
            z-index: 9999;
        }
        #toast.show {
            transform: translateY(0);
            opacity: 1;
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 1200px;">
        <a href="index.php" class="back-link">🔙 Kembali ke Menu Awal</a>
        <div class="card" style="border-top: 5px solid #cca300; margin-bottom: 20px;">
            <h2 style="color: var(--primary-dark); margin-bottom: 5px;">💰 Buku Kas Arisan RT 31</h2>
            <p style="color: #666;">Matriks pembayaran arisan warga (Tarif: Rp 20.000 / Bulan).</p>
            <?php if($is_admin) echo "<p style='color: #ef4444; font-size: 0.85rem; font-weight: bold; margin-top: 5px;'>🔓 Mode Admin: Anda dapat mencentang pembayaran secara instan.</p>"; ?>
        </div>
        
        <?php if ($is_admin): ?>
        <form method="POST" class="form-tambah-bulan">
            <input type="hidden" name="action" value="tambah_bulan">
            <input type="text" name="bulan_baru" placeholder="Contoh: April 2026" required autocomplete="off">
            <button type="submit">➕ Tambah Kolom Bulan</button>
        </form>
        <?php endif; ?>
        
        <input type="text" id="searchInput" class="search-box" placeholder="🔍 Cari nama warga di sini..." autocomplete="off">
        
        <div class="table-responsive">
            <table id="tabelArisan">
                <thead>
                    <tr>
                        <th>No</th>
                        <th style="min-width: 200px;">Nama Warga</th>
                        <?php 
                        foreach ($list_bulan as $bln) {
                            echo "<th>" . htmlspecialchars($bln) . "</th>";
                        }
                        ?>
                        <?php if($is_admin): ?><th>Aksi</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Hanya tampilkan peserta asli
                    $sql = "SELECT id, nama, no_wa FROM peserta WHERE is_deleted = 0 AND no_wa != '-' AND no_wa != '' ORDER BY nama ASC";
                    $result = $conn->query($sql);
                    
                    if ($result && $result->num_rows > 0) {
                        $no = 1;
                        while($row = $result->fetch_assoc()) {
                            $pid = $row['id'];
                            echo "<tr>";
                            echo "<td>" . $no++ . "</td>";
                            echo "<td class='nama-peserta'>" . htmlspecialchars($row['nama']) . "</td>";
                            
                            foreach ($list_bulan as $bln) {
                                $lunas = isset($pembayaran[$pid][$bln]);
                                echo "<td>";
                                if ($is_admin) {
                                    $checked = $lunas ? "checked" : "";
                                    echo "<input type='checkbox' class='chk-bayar' data-id='$pid' data-bulan='" . htmlspecialchars($bln, ENT_QUOTES) . "' $checked>";
                                } else {
                                    echo $lunas ? "<span class='badge-lunas'>✅</span>" : "<span class='badge-belum'>✖</span>";
                                }
                                echo "</td>";
                            }
                            
                            if ($is_admin) {
                                echo "<td>
                                    <a href='edit.php?id=$pid' class='btn-aksi btn-edit' style='font-size:0.75rem; padding: 2px 5px;'>Edit</a>
                                </td>";
                            }
                            echo "</tr>";
                        }
                    } else {
                        $colspan = 2 + count($list_bulan) + ($is_admin ? 1 : 0);
                        echo "<tr><td colspan='$colspan' style='padding:20px; color:#888;'>Belum ada warga terdaftar.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
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
    
    <div id="toast">✅ Disimpan!</div>

<script>
// Live Search Logic
document.getElementById('searchInput').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('#tabelArisan tbody tr');
    
    rows.forEach(row => {
        let nameCell = row.querySelector('.nama-peserta');
        if (nameCell) {
            let txtValue = nameCell.textContent || nameCell.innerText;
            if (txtValue.toLowerCase().indexOf(filter) > -1) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        }
    });
});

// AJAX Checkbox Logic (Khusus Admin)
<?php if ($is_admin): ?>
const toast = document.getElementById('toast');
let toastTimeout;

function showToast(msg) {
    toast.textContent = msg;
    toast.classList.add('show');
    clearTimeout(toastTimeout);
    toastTimeout = setTimeout(() => {
        toast.classList.remove('show');
    }, 2500);
}

document.querySelectorAll('.chk-bayar').forEach(chk => {
    chk.addEventListener('change', function() {
        let pid = this.getAttribute('data-id');
        let bulan = this.getAttribute('data-bulan');
        let action = this.checked ? 'check' : 'uncheck';
        
        let formData = new FormData();
        formData.append('peserta_id', pid);
        formData.append('bulan', bulan);
        formData.append('action', action);
        
        fetch('proses_bayar.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                if(action === 'check') {
                    showToast('✅ Lunas: ' + bulan);
                } else {
                    showToast('✖ Batal Lunas: ' + bulan);
                }
            } else {
                alert('Gagal: ' + data.message);
                this.checked = !this.checked; // Kembalikan state
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan jaringan.');
            this.checked = !this.checked;
        });
    });
});
<?php endif; ?>
</script>
<script src="sound.js"></script>
<script src="screensaver.js"></script>
</body>
</html>
