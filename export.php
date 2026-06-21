<?php
session_start();
require 'config.php';

// Cek keamanan akses admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    die("Akses Ditolak! Fitur ini khusus untuk Admin Eksekutif.");
}

// Nama file download
$filename = "Backup_Data_Arisan_RT31_" . date('Y-m-d') . ".csv";

// Atur header HTTP agar mendownload file CSV
header("Content-Description: File Transfer");
header("Content-Disposition: attachment; filename=$filename");
header("Content-Type: application/csv; "); 

// Buka output file stream
$file = fopen('php://output', 'w');

// Tulis header kolom ke CSV
$header = array("ID", "Nama Lengkap", "Nomor WhatsApp", "Tanggal Daftar");
fputcsv($file, $header);

// Ambil semua data peserta yang tidak dihapus (is_deleted = 0)
$query = "SELECT id, nama, no_wa, tanggal_daftar FROM peserta WHERE is_deleted = 0 ORDER BY nama ASC";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        fputcsv($file, $row);
    }
}

fclose($file);
exit;
?>
