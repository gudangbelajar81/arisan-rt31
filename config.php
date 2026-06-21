<?php
// Tampilkan semua error untuk debugging 500
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

mysqli_report(MYSQLI_REPORT_OFF);

// Fungsi cerdas untuk menangkap Environment Variables dari server Railway
function get_env_var($key, $default = "") {
    $val = getenv($key);
    if ($val !== false && $val !== "") return $val;
    if (isset($_ENV[$key]) && $_ENV[$key] !== "") return $_ENV[$key];
    if (isset($_SERVER[$key]) && $_SERVER[$key] !== "") return $_SERVER[$key];
    return $default;
}

$host = get_env_var("MYSQLHOST", "localhost");
$user = get_env_var("MYSQLUSER", "root");
$pass = get_env_var("MYSQLPASSWORD", "");
$dbname = get_env_var("MYSQLDATABASE", "arisan_rt31");
$port = (int) get_env_var("MYSQLPORT", 3306);

// 1. Mencoba koneksi langsung
$conn = @new mysqli($host, $user, $pass, $dbname, $port);

if ($conn->connect_error) {
    // Jika gagal, coba tanpa DB Name (Untuk setup awal XAMPP lokal)
    $conn_setup = @new mysqli($host, $user, $pass, "", $port);
    if ($conn_setup->connect_error) {
        die("<div style='background:#111; color:#ff4444; padding:30px; font-family:sans-serif; text-align:center; border-radius:10px; margin:50px;'>
                <h2>🚨 SISTEM DATABASE TERPUTUS 🚨</h2>
                <p><strong>Pesan Server:</strong> " . $conn_setup->connect_error . "</p>
                <p style='color:#ccc'><strong>Bos Musyafa:</strong> Jika Anda melihat pesan ini di Railway, berarti Anda belum menghubungkan/menyuntikkan Variables Database MySQL ke Aplikasi Web Anda. Silakan ikuti panduan Altair di chat.</p>
             </div>");
    }
    
    // Buat database otomatis
    $sql_create_db = "CREATE DATABASE IF NOT EXISTS `$dbname`";
    if ($conn_setup->query($sql_create_db) === TRUE) {
        $conn = @new mysqli($host, $user, $pass, $dbname, $port);
    } else {
        die("Gagal membuat database otomatis: " . $conn_setup->error);
    }
}

// 2. Buat Tabel Peserta secara otomatis
$sql_create_table = "CREATE TABLE IF NOT EXISTS peserta (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    no_wa VARCHAR(20) NOT NULL,
    blok_rumah VARCHAR(50) NOT NULL,
    tanggal_daftar TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
if (!$conn->query($sql_create_table)) {
    die("Gagal membuat tabel: " . $conn->error);
}

// 3. Migrasi Database Aman (Soft-Delete)
// Tambahkan kolom is_deleted jika belum ada di tabel peserta
$check_column = $conn->query("SHOW COLUMNS FROM peserta LIKE 'is_deleted'");
if ($check_column && $check_column->num_rows == 0) {
    $conn->query("ALTER TABLE peserta ADD COLUMN is_deleted TINYINT(1) DEFAULT 0 AFTER blok_rumah");
}
?>
