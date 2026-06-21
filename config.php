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
        header("Location: maintenance.php");
        exit;
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
    // Abaikan gagal pembuatan tabel agar tidak memecah layout jika koneksi db aman tapi privilese kurang
}

// 3. Migrasi Database Aman (Soft-Delete & Jadwal Ronda)
$check_column = $conn->query("SHOW COLUMNS FROM peserta LIKE 'is_deleted'");
if ($check_column && $check_column->num_rows == 0) {
    $conn->query("ALTER TABLE peserta ADD COLUMN is_deleted TINYINT(1) DEFAULT 0 AFTER blok_rumah");
}

$check_ronda = $conn->query("SHOW COLUMNS FROM peserta LIKE 'hari_ronda'");
if ($check_ronda && $check_ronda->num_rows == 0) {
    $conn->query("ALTER TABLE peserta ADD COLUMN hari_ronda VARCHAR(20) NULL AFTER is_deleted");
}

// 4. Buat Tabel Log Aktivitas (Jejak Rekam Digital)
$sql_log_table = "CREATE TABLE IF NOT EXISTS log_aktivitas (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    aksi VARCHAR(255) NOT NULL,
    ip_address VARCHAR(50) NOT NULL,
    waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql_log_table);
?>
