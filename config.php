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

// PIN akan diambil dari Database (Tabel Pengaturan)

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

// 4. Tambah Kolom Hari Ronda jika belum ada
$sql_alter = "ALTER TABLE peserta ADD COLUMN hari_ronda VARCHAR(20) NULL";
if ($conn->query($sql_alter) === FALSE) {
    // Abaikan jika sudah ada
}

// 5. Tambah Kolom Bulan Pertemuan (Arisan) jika belum ada
$sql_alter_bulan = "ALTER TABLE peserta ADD COLUMN bulan_pertemuan VARCHAR(20) NULL";
if ($conn->query($sql_alter_bulan) === FALSE) {
    // Abaikan jika sudah ada
}

// 6. Buat Tabel Log Aktivitas (Jejak Rekam Digital)
$sql_log_table = "CREATE TABLE IF NOT EXISTS log_aktivitas (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    aksi VARCHAR(255) NOT NULL,
    ip_address VARCHAR(50) NOT NULL,
    waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql_log_table);
// 7. Buat Tabel Pengaturan (Untuk PIN Dinamis)
$sql_pengaturan = "CREATE TABLE IF NOT EXISTS pengaturan (
    kunci VARCHAR(50) PRIMARY KEY,
    nilai VARCHAR(255) NOT NULL
)";
$conn->query($sql_pengaturan);

// 8. Masukkan PIN Default jika tabel kosong
$cek_pengaturan = $conn->query("SELECT COUNT(*) as total FROM pengaturan");
$row_pengaturan = $cek_pengaturan->fetch_assoc();
if ($row_pengaturan['total'] == 0) {
    $conn->query("INSERT INTO pengaturan (kunci, nilai) VALUES 
        ('pin_master', '111080'),
        ('pin_arisan', '111080'),
        ('pin_ronda', '123'),
        ('pin_pertemuan', '123')
    ");
}

// 9. Muat PIN ke dalam variabel global
$pin_master = "111080";
$pin_arisan = "111080";
$pin_ronda = "123";
$pin_pertemuan = "123";

$res_pin = $conn->query("SELECT * FROM pengaturan");
if ($res_pin && $res_pin->num_rows > 0) {
    while($row = $res_pin->fetch_assoc()) {
        if ($row['kunci'] == 'pin_master') $pin_master = $row['nilai'];
        if ($row['kunci'] == 'pin_arisan') $pin_arisan = $row['nilai'];
        if ($row['kunci'] == 'pin_ronda') $pin_ronda = $row['nilai'];
        if ($row['kunci'] == 'pin_pertemuan') $pin_pertemuan = $row['nilai'];
    }
}
?>
