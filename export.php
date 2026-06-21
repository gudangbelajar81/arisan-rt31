<?php
session_start();
require 'config.php';

// Cek keamanan akses admin (menggunakan session admin_arisan yang benar)
if (!isset($_SESSION['admin_arisan']) || $_SESSION['admin_arisan'] !== true) {
    die("Akses Ditolak! Fitur ini khusus untuk Admin Eksekutif.");
}

$jenis = isset($_GET['jenis']) ? $_GET['jenis'] : 'buku_kas';

if ($jenis == 'pemenang') {
    // ==== EXPORT PEMENANG ARISAN ====
    $filename = "Backup_Pemenang_Arisan_RT31_" . date('Y-m-d_H-i') . ".csv";
    header("Content-Description: File Transfer");
    header("Content-Disposition: attachment; filename=$filename");
    header("Content-Type: text/csv; charset=UTF-8"); 

    $file = fopen('php://output', 'w');
    fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

    fputcsv($file, array("Bulan Menang", "ID Warga", "Nama Pemenang", "Nomor WhatsApp"));

    $query = "SELECT pa.bulan_menang, p.id, p.nama, p.no_wa 
              FROM pemenang_arisan pa 
              JOIN peserta p ON pa.peserta_id = p.id 
              ORDER BY pa.id ASC";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            fputcsv($file, array(
                $row['bulan_menang'],
                $row['id'],
                $row['nama'],
                "'" . $row['no_wa']
            ));
        }
    } else {
        fputcsv($file, array("Belum ada data pemenang"));
    }

    fclose($file);
    exit;

} elseif ($jenis == 'ronda') {
    // ==== EXPORT JADWAL RONDA ====
    $filename = "Backup_Jadwal_Ronda_RT31_" . date('Y-m-d_H-i') . ".csv";
    header("Content-Description: File Transfer");
    header("Content-Disposition: attachment; filename=$filename");
    header("Content-Type: text/csv; charset=UTF-8"); 

    $file = fopen('php://output', 'w');
    fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

    fputcsv($file, array("Hari Ronda", "Nama Warga", "Nomor WhatsApp"));

    $query = "SELECT hari_ronda, nama, no_wa FROM peserta WHERE is_deleted = 0 AND hari_ronda IS NOT NULL AND hari_ronda != '' ORDER BY FIELD(hari_ronda, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'), nama ASC";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            fputcsv($file, array(
                $row['hari_ronda'],
                $row['nama'],
                "'" . $row['no_wa']
            ));
        }
    } else {
        fputcsv($file, array("Belum ada data jadwal ronda"));
    }

    fclose($file);
    exit;

} elseif ($jenis == 'pertemuan') {
    // ==== EXPORT JADWAL PERTEMUAN ====
    $filename = "Backup_Jadwal_Pertemuan_RT31_" . date('Y-m-d_H-i') . ".csv";
    header("Content-Description: File Transfer");
    header("Content-Disposition: attachment; filename=$filename");
    header("Content-Type: text/csv; charset=UTF-8"); 

    $file = fopen('php://output', 'w');
    fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

    fputcsv($file, array("Bulan Pertemuan", "Nama Tuan Rumah", "Nomor WhatsApp"));

    $query = "SELECT bulan_pertemuan, nama, no_wa FROM peserta WHERE is_deleted = 0 AND bulan_pertemuan IS NOT NULL AND bulan_pertemuan != '' ORDER BY bulan_pertemuan ASC";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            fputcsv($file, array(
                $row['bulan_pertemuan'],
                $row['nama'],
                "'" . $row['no_wa']
            ));
        }
    } else {
        fputcsv($file, array("Belum ada data jadwal pertemuan"));
    }

    fclose($file);
    exit;

} else {
    // ==== EXPORT BUKU KAS ====
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

    $filename = "Backup_Buku_Kas_RT31_" . date('Y-m-d_H-i') . ".csv";

    header("Content-Description: File Transfer");
    header("Content-Disposition: attachment; filename=$filename");
    header("Content-Type: text/csv; charset=UTF-8"); 

    $file = fopen('php://output', 'w');
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

    if ($result && $result->num_rows > 0) {
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
}
?>
