<?php
session_start();
require 'config.php';
require 'logger.php';

if (!isset($_SESSION['admin_master']) || $_SESSION['admin_master'] !== true) {
    die("Akses Ditolak! Anda bukan Admin Master.");
}

$pesan = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_pin') {
    $new_master = trim($_POST['pin_master']);
    $new_arisan = trim($_POST['pin_arisan']);
    $new_ronda = trim($_POST['pin_ronda']);
    $new_pertemuan = trim($_POST['pin_pertemuan']);
    
    // Update ke database
    $stmt = $conn->prepare("UPDATE pengaturan SET nilai = ? WHERE kunci = ?");
    
    $kunci = 'pin_master'; $stmt->bind_param("ss", $new_master, $kunci); $stmt->execute();
    $kunci = 'pin_arisan'; $stmt->bind_param("ss", $new_arisan, $kunci); $stmt->execute();
    $kunci = 'pin_ronda'; $stmt->bind_param("ss", $new_ronda, $kunci); $stmt->execute();
    $kunci = 'pin_pertemuan'; $stmt->bind_param("ss", $new_pertemuan, $kunci); $stmt->execute();
    
    catat_log($conn, "Admin Master mengubah konfigurasi PIN");
    
    // Refresh variabel global
    $pin_master = $new_master;
    $pin_arisan = $new_arisan;
    $pin_ronda = $new_ronda;
    $pin_pertemuan = $new_pertemuan;
    
    $pesan = "<div class='alert' style='background: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold;'>✅ Semua pengaturan PIN berhasil diperbarui!</div>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Keamanan</title>
    <link rel="stylesheet" href="style.css?v=2.0">
    <style>
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #334155;
        }
        .form-group .input-wrapper {
            position: relative;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            padding-right: 45px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            font-size: 1.2rem;
            letter-spacing: 2px;
            font-family: monospace;
            background: #f8fafc;
        }
        .toggle-btn {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            color: #64748b;
        }
        .form-group input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background: white;
        }
    </style>
</head>
<body style="background: #f1f5f9; padding: 20px;">
    <div class="container" style="max-width: 600px; margin: 0 auto;">
        <a href="index.php" class="back-link">🔙 Kembali ke Menu Awal</a>
        <div class="card" style="border-top: 5px solid #0f172a;">
            <h2 style="color: #0f172a; margin-bottom: 5px;">⚙️ Pengaturan Keamanan (Master)</h2>
            <p style="color: #64748b; margin-bottom: 20px;">Di halaman ini Anda dapat mengubah kunci sandi untuk seluruh modul aplikasi kapan saja tanpa perlu mengedit kode.</p>
            
            <?php echo $pesan; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="action" value="update_pin">
                
                <div class="form-group">
                    <label>👑 PIN Master (Sapu Jagat)</label>
                    <div class="input-wrapper">
                        <input type="password" name="pin_master" value="<?php echo htmlspecialchars($pin_master); ?>" required>
                        <button type="button" class="toggle-btn" onclick="togglePin(this)">👁️</button>
                    </div>
                    <small style="color: #ef4444; font-weight: bold;">Hati-hati! PIN ini bisa membuka semua halaman & pengaturan.</small>
                </div>
                
                <hr style="border: 0; border-top: 1px dashed #cbd5e1; margin: 25px 0;">
                
                <div class="form-group">
                    <label>💰 PIN Peserta Arisan</label>
                    <div class="input-wrapper">
                        <input type="password" name="pin_arisan" value="<?php echo htmlspecialchars($pin_arisan); ?>" required>
                        <button type="button" class="toggle-btn" onclick="togglePin(this)">👁️</button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>🛡️ PIN Siskamling (Ronda)</label>
                    <div class="input-wrapper">
                        <input type="password" name="pin_ronda" value="<?php echo htmlspecialchars($pin_ronda); ?>" required>
                        <button type="button" class="toggle-btn" onclick="togglePin(this)">👁️</button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>📅 PIN Jadwal Pertemuan</label>
                    <div class="input-wrapper">
                        <input type="password" name="pin_pertemuan" value="<?php echo htmlspecialchars($pin_pertemuan); ?>" required>
                        <button type="button" class="toggle-btn" onclick="togglePin(this)">👁️</button>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 1.1rem; padding: 15px; background: #0f172a;">💾 Simpan Pengaturan PIN</button>
            </form>
        </div>
    </div>
    <script>
        function togglePin(btn) {
            const input = btn.previousElementSibling;
            if (input.type === "password") {
                input.type = "text";
                btn.innerText = "🙈";
            } else {
                input.type = "password";
                btn.innerText = "👁️";
            }
        }
    </script>
</body>
</html>
