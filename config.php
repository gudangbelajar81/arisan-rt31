<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "arisan_rt31";

// 1. Buat koneksi awal ke MySQL XAMPP (tanpa database spesifik)
$conn_setup = new mysqli($host, $user, $pass);

if ($conn_setup->connect_error) {
    die("Koneksi XAMPP gagal: " . $conn_setup->connect_error);
}

// 2. Buat Database jika belum ada (Trik Rahasia Harun agar CEO tidak pusing)
$sql_create_db = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn_setup->query($sql_create_db) === TRUE) {
    
    // 3. Pindah koneksi ke database yang baru dibuat
    $conn = new mysqli($host, $user, $pass, $dbname);
    
    // 4. Buat Tabel Peserta jika belum ada
    $sql_create_table = "CREATE TABLE IF NOT EXISTS peserta (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(100) NOT NULL,
        no_wa VARCHAR(20) NOT NULL,
        blok_rumah VARCHAR(50) NOT NULL,
        tanggal_daftar TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->query($sql_create_table);
} else {
    die("Gagal membuat database otomatis: " . $conn_setup->error);
}
?>
