<?php
// Deteksi otomatis Environment Railway (Cloud) atau XAMPP (Lokal)
$host = getenv("MYSQLHOST") ?: "localhost";
$user = getenv("MYSQLUSER") ?: "root";
$pass = getenv("MYSQLPASSWORD") ?: "";
$dbname = getenv("MYSQLDATABASE") ?: "arisan_rt31";
$port = getenv("MYSQLPORT") ?: 3306;

// 1. Mencoba koneksi langsung ke MySQL dengan port yang tepat
$conn = new mysqli($host, $user, $pass, $dbname, $port);

// Jika database belum ada (Biasanya terjadi saat pertama kali jalan di lokal XAMPP)
if ($conn->connect_error) {
    // Coba konek tanpa nama database untuk proses setup awal
    $conn_setup = new mysqli($host, $user, $pass, "", $port);
    if ($conn_setup->connect_error) {
        die("Koneksi gagal: " . $conn_setup->connect_error);
    }
    
    // Buat database otomatis
    $sql_create_db = "CREATE DATABASE IF NOT EXISTS `$dbname`";
    if ($conn_setup->query($sql_create_db) === TRUE) {
        // Sambung ulang ke database yang baru saja dibuat
        $conn = new mysqli($host, $user, $pass, $dbname, $port);
    } else {
        die("Gagal membuat database otomatis: " . $conn_setup->error);
    }
}

// 2. Buat Tabel Peserta secara otomatis jika belum ada di dalam server
$sql_create_table = "CREATE TABLE IF NOT EXISTS peserta (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    no_wa VARCHAR(20) NOT NULL,
    blok_rumah VARCHAR(50) NOT NULL,
    tanggal_daftar TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
$conn->query($sql_create_table);
?>
