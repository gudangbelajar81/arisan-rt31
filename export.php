<?php
session_start();
require 'config.php';

// Cek keamanan akses admin (menggunakan session admin_arisan yang benar)
if (!isset($_SESSION['admin_arisan']) || $_SESSION['admin_arisan'] !== true) {
    die("Akses Ditolak! Fitur ini khusus untuk Admin Eksekutif.");
}

// Ambil daftar bulan (kolom)
$cek_kolom = $conn->query("SELECT nilai FROM pengaturan WHERE kunci = 'kolom_bulan'");
$row_kolom = $cek_kolom->fetch_assoc();
$list_bulan = $row_kolom ? array_filter(array_map('trim', explode(',', $row_kolom['nilai']))) : [];

// Ambil data pembayaran
$pembayaran = [];
$res_bayar = $conn->query("SELECT peserta_id, bulan FROM pembayaran_arisan");
if ($res_bayar) {
    while($row = $res_bayar->fetch_assoc()) {
        $pembayaran[$row['peserta_id']][$row['bulan']] = true;
    }
}

// Nama file download
$filename = "Backup_Buku_Kas_RT31_" . date('Y-m-d_H-i') . ".csv";

header("Content-Description: File Transfer");
header("Content-Disposition: attachment; filename=$filename");
header("Content-Type: text/csv; charset=UTF-8"); 

$file = fopen('php://output', 'w');
// Tambahkan BOM untuk excel agar terbaca UTF-8
fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

// Buat Header Dinamis
$header = array("ID", "Nama Lengkap", "Nomor WhatsApp", "Piket Ronda");
foreach ($list_bulan as $bln) {
    $header[] = "Bln: " . $bln;
}
$header[] = "Status Nunggak";
fputcsv($file, $header);

// Ambil data warga
$query = "SELECT id, nama, no_wa, hari_ronda FROM peserta WHERE is_deleted = 0 ORDER BY nama ASC";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $pid = $row['id'];
        
        $baris = array(
            $row['id'],
            $row['nama'],
            "'" . $row['no_wa'], // Tanda kutip agar excel tidak merusak format nomor HP
            $row['hari_ronda'] ? $row['hari_ronda'] : '-'
        );
        
        $total_lunas = 0;
        foreach ($list_bulan as $bln) {
            if (isset($pembayaran[$pid][$bln])) {
                $baris[] = "LUNAS";
                $total_lunas++;
            } else {
                $baris[] = "BELUM";
            }
        }
        
        $nunggak = ($total_lunas < count($list_bulan)) ? "YA (" . (count($list_bulan) - $total_lunas) . " bln)" : "LENGKAP";
        $baris[] = $nunggak;
        
        fputcsv($file, $baris);
    }
}

fclose($file);
exit;
?>
